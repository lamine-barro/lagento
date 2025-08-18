<?php

namespace App\Http\Controllers;

use App\Models\UserConversation;
use App\Models\UserMessage;
use App\Services\AgentService;
use App\Services\UserAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private AgentService $agentService;
    private UserAnalyticsService $analyticsService;

    public function __construct(AgentService $agentService, UserAnalyticsService $analyticsService)
    {
        $this->agentService = $agentService;
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $conversationId = $request->get('conversation');
        
        // Get all user conversations for tabs
        $conversations = UserConversation::where('user_id', $user->id)
            ->orderBy('last_message_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get specific conversation or create default one
        if ($conversationId) {
            $conversation = UserConversation::where('user_id', $user->id)
                ->where('id', $conversationId)
                ->firstOrFail();
        } else {
            $conversation = $conversations->first();
                
            if (!$conversation) {
                $conversation = UserConversation::create([
                    'user_id' => (string) $user->id,
                    'title' => 'Nouvelle conversation',
                    'last_message_at' => now()
                ]);
                $conversations = collect([$conversation]);
            }
        }
        
        // Get messages with proper formatting for frontend
        $messages = UserMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get()
            ->map(function($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->text_content, // Ensure content is properly mapped
                    'created_at' => $message->created_at->toISOString(),
                    'attachments' => $message->attachments ?? [],
                    'executed_tools' => $message->executed_tools ?? []
                ];
            });
        
        return view('chat', compact('messages', 'conversation', 'conversations'));
    }
    
    public function saveUserMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_id' => 'nullable|string'
        ]);

        $user = Auth::user();
        $conversationId = $request->get('conversation_id');
        $userMessageContent = $request->get('message');
        
        // Get or create conversation
        $conversation = null;
        if ($conversationId) {
            $conversation = UserConversation::where('user_id', $user->id)
                ->where('id', $conversationId)
                ->first();
        }
        
        if (!$conversation) {
            $conversation = UserConversation::create([
                'user_id' => $user->id,
                'title' => 'Nouvelle conversation',
                'last_message_at' => now()
            ]);
        }

        // Save user message
        $userMessage = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'text_content' => $userMessageContent
        ]);
        
        // Update conversation
        $conversation->increment('message_count', 1);
        $conversation->update(['last_message_at' => now()]);
        
        return response()->json([
            'success' => true,
            'user_message_id' => $userMessage->id,
            'conversation_id' => $conversation->id,
            'message' => [
                'id' => $userMessage->id,
                'role' => $userMessage->role,
                'content' => $userMessage->text_content,
                'created_at' => $userMessage->created_at->toISOString(),
                'attachments' => $userMessage->attachments ?? [],
                'executed_tools' => $userMessage->executed_tools ?? []
            ]
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:5120' // 5MB max
        ]);

        // Validate that we have either message or file
        if (!$request->message && !$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'error' => 'Message ou fichier requis'
            ], 400);
        }
        
        $user = Auth::user();
        $conversationId = $request->get('conversation_id');
        
        // Get or create conversation
        $conversation = null;
        if ($conversationId) {
            $conversation = UserConversation::where('user_id', $user->id)
                ->where('id', $conversationId)
                ->first();
        }
        
        if (!$conversation) {
            $conversation = UserConversation::create([
                'user_id' => $user->id,
                'title' => 'Nouvelle conversation',
                'last_message_at' => now()
            ]);
        }

        $userMessageContent = $request->message ?? '';
        
        // Handle file attachment
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('chat-attachments', 'public');
            $userMessageContent .= ($userMessageContent ? "\n\n" : '') . 
                "[Fichier joint: {$file->getClientOriginalName()}]";
        }
        
        // Check if user message was already saved (to avoid duplication)
        $userMessage = null;
        if ($userMessageContent) {
            // Check for recent user message with same content in this conversation
            $recentUserMessage = UserMessage::where('conversation_id', $conversation->id)
                ->where('role', 'user')
                ->where('text_content', $userMessageContent)
                ->where('created_at', '>=', now()->subMinutes(1)) // Within last minute
                ->first();
            
            if ($recentUserMessage) {
                // Message already exists, don't create duplicate
                $userMessage = $recentUserMessage;
            } else {
                // Create new user message only if no recent duplicate found
                $userMessage = UserMessage::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'user',
                    'text_content' => $userMessageContent
                ]);
                
                // Update conversation count only for new messages
                $conversation->increment('message_count', 1);
                $conversation->update(['last_message_at' => now()]);
            }
        }
        
        // Generate title for new conversation
        $isNewConversation = $conversation->message_count === 1;
        if ($isNewConversation && $userMessageContent) {
            $titleResult = $this->agentService->generateTitle(
                $userMessageContent,
                'chat',
                $conversation->id
            );
            
            if ($titleResult['success']) {
                $conversation->update(['title' => $titleResult['title']]);
            }
        }
        
        // Process message with AgentPrincipal
        $agentResult = $this->agentService->processMessage(
            $userMessageContent,
            $user->id,
            $conversation->id
        );
        
        if (!$agentResult['success']) {
            return response()->json([
                'success' => false,
                'error' => $agentResult['error'] ?? 'Erreur lors du traitement'
            ], 500);
        }
        
        // Save AI response
        $aiMessage = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'text_content' => $agentResult['response']
        ]);
        
        // Update conversation (only increment by 1 for AI message, user message already counted)
        $conversation->increment('message_count', 1);
        $conversation->update(['last_message_at' => now()]);
        
        // Track chat interaction
        $interactionData = [
            'agent_type' => 'principal',
            'message_length' => strlen($userMessageContent),
            'tools_used' => $agentResult['tools_used'] ?? [],
            'response_length' => strlen($agentResult['response']),
            'has_attachment' => $request->hasFile('file'),
            'conversation_id' => $conversation->id,
            'timestamp' => now()->toISOString()
        ];
        
        $this->analyticsService->trackChatInteraction($user, $interactionData);
        
        // Check if title needs updating
        $this->agentService->updateConversationTitleIfNeeded($conversation->id);
        
        return response()->json([
            'success' => true,
            'response' => $agentResult['response'],
            'message_id' => $aiMessage->id,
            'conversation_id' => $conversation->id,
            'tools_used' => $agentResult['tools_used'] ?? [],
            'metadata' => $agentResult['metadata'] ?? []
        ]);
    }

    public function getSuggestions(Request $request)
    {
        $user = Auth::user();
        $previousPage = $request->get('previous_page', '');
        $activePage = $request->get('active_page', 'chat');
        
        $result = $this->agentService->generateSuggestions(
            $user->id,
            $previousPage,
            $activePage
        );
        
        return response()->json($result);
    }

    public function refreshSuggestions(Request $request)
    {
        $user = Auth::user();
        $activePage = $request->get('active_page', 'chat');
        
        $result = $this->agentService->refreshSuggestions($user->id, $activePage);
        
        return response()->json($result);
    }

    public function createConversation(Request $request)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::create([
            'user_id' => $user->id,
            'title' => 'Nouvelle conversation',
            'last_message_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'redirect_url' => route('chat.index', ['conversation' => $conversation->id])
        ]);
    }

    public function getConversations(Request $request)
    {
        $user = Auth::user();
        
        $conversations = UserConversation::where('user_id', $user->id)
            ->orderBy('last_message_at', 'desc')
            ->limit(20)
            ->get();
        
        return response()->json([
            'success' => true,
            'conversations' => $conversations->map(function($conv) {
                return [
                    'id' => $conv->id,
                    'title' => $conv->title,
                    'last_message_at' => $conv->last_message_at,
                    'message_count' => $conv->message_count ?? 0
                ];
            })
        ]);
    }

    public function deleteConversation(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::where('user_id', $user->id)
            ->where('id', $conversationId)
            ->first();
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée'
            ], 404);
        }
        
        // Supprimer tous les messages de la conversation
        UserMessage::where('conversation_id', $conversationId)->delete();
        
        // Supprimer la conversation
        $conversation->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Conversation supprimée avec succès'
        ]);
    }

    public function getConversationMessages(Request $request, $conversationId)
    {
        $user = Auth::user();
        
        $conversation = UserConversation::where('user_id', $user->id)
            ->where('id', $conversationId)
            ->first();
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée'
            ], 404);
        }
        
        $messages = UserMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get()
            ->map(function($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->text_content,
                    'created_at' => $message->created_at->toISOString(),
                    'attachments' => $message->attachments ?? [],
                    'executed_tools' => $message->executed_tools ?? []
                ];
            });
        
        return response()->json([
            'success' => true,
            'messages' => $messages,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'message_count' => $conversation->message_count ?? 0
            ]
        ]);
    }
}