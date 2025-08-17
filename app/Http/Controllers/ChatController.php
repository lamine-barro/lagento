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
        
        // Get specific conversation or create default one
        if ($conversationId) {
            $conversation = UserConversation::where('user_id', $user->id)
                ->where('id', $conversationId)
                ->firstOrFail();
        } else {
            $conversation = UserConversation::where('user_id', $user->id)
                ->latest('last_message_at')
                ->first();
                
            if (!$conversation) {
                $conversation = UserConversation::create([
                    'user_id' => $user->id,
                    'title' => 'Nouvelle conversation',
                    'last_message_at' => now()
                ]);
            }
        }
        
        // Get messages
        $messages = UserMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at')
            ->get();
        
        return view('chat', compact('messages', 'conversation'));
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
        
        // Save user message
        $userMessage = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessageContent
        ]);
        
        // Generate title for new conversation
        $isNewConversation = $conversation->message_count === 0;
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
            'content' => $agentResult['response']
        ]);
        
        // Update conversation
        $conversation->increment('message_count', 2);
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
}