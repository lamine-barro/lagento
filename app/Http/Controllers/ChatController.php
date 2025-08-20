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
                $attachment = null;
                if ($message->attachments && is_array($message->attachments) && count($message->attachments) > 0) {
                    $attachment = $message->attachments[0]; // Take the first attachment
                }
                
                \Log::info('Loading message with attachment', [
                    'message_id' => $message->id,
                    'role' => $message->role,
                    'has_attachment' => !is_null($attachment),
                    'raw_attachments' => $message->attachments
                ]);
                
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->text_content, // Ensure content is properly mapped
                    'created_at' => $message->created_at->toISOString(),
                    'attachment' => $attachment,
                    'executed_tools' => $message->executed_tools ?? []
                ];
            });
        
        return view('chat', compact('messages', 'conversation', 'conversations'));
    }
    
    public function saveUserMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_id' => 'nullable|string',
            'file' => 'nullable|file|max:32768' // 32MB max (OpenAI limit)
        ]);

        // Message is already validated by the request validation above

        $user = Auth::user();
        $conversationId = $request->get('conversation_id');
        $userMessageContent = $request->get('message') ?? '';
        
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

        $attachmentData = null;
        $vectorMemoryId = null;
        
        // Handle file attachment
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            try {
                // Store file locally
                $filePath = $file->store('chat-attachments', 'public');
                
                $attachmentData = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $filePath,
                    'url' => asset('storage/' . $filePath)
                ];
                
                // Vectoriser le fichier pour le contexte
                try {
                    $vectorMemoryId = $this->vectorizeFileContent($file, $user->id, $conversation->id);
                } catch (\Exception $e) {
                    \Log::error('Vectorisation failed, continuing without it: ' . $e->getMessage());
                    $vectorMemoryId = null;
                }
                
            } catch (\Exception $e) {
                \Log::error('Erreur traitement fichier: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors du traitement du fichier'
                ], 500);
            }
        }

        // Save user message
        $userMessage = UserMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'text_content' => $userMessageContent,
            'attachments' => $attachmentData ? [$attachmentData] : null,
            'vector_memory_id' => $vectorMemoryId
        ]);
        
        // Update conversation
        $conversation->increment('message_count', 1);
        $conversation->update(['last_message_at' => now()]);
        
        \Log::info('User message saved with attachment', [
            'message_id' => $userMessage->id,
            'has_attachment' => !is_null($attachmentData),
            'attachment_data' => $attachmentData
        ]);

        return response()->json([
            'success' => true,
            'user_message_id' => $userMessage->id,
            'conversation_id' => $conversation->id,
            'vector_memory_id' => $vectorMemoryId,
            'attachment' => $attachmentData,
            'message' => [
                'id' => $userMessage->id,
                'role' => $userMessage->role,
                'content' => $userMessage->text_content,
                'created_at' => $userMessage->created_at->toISOString(),
                'attachment' => $attachmentData,
                'executed_tools' => $userMessage->executed_tools ?? []
            ]
        ]);
    }
    
    private function vectorizeFileContent($file, $userId, $conversationId)
    {
        // Utiliser le nouveau service d'auto-vectorisation
        $autoVectorService = app(\App\Services\AutoVectorizationService::class);
        
        try {
            // Store the file temporarily
            $tempPath = $file->store('temp_uploads');
            $fullPath = storage_path('app/' . $tempPath);
            
            // Use the auto-vectorization service
            $success = $autoVectorService->vectorizeAttachment($fullPath, [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_at' => now()->toISOString()
            ]);
            
            // Clean up temp file
            \Storage::delete($tempPath);
            
            if (!$success) {
                throw new \Exception("Impossible de vectoriser le fichier");
            }
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error('Erreur vectorisation fichier: ' . $e->getMessage());
            throw $e;
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'conversation_id' => 'required|string',
            'vector_memory_id' => 'nullable|string'
        ]);

        $user = Auth::user();
        $conversationId = $request->get('conversation_id');
        $vectorMemoryId = $request->get('vector_memory_id');
        
        // Get conversation
        $conversation = UserConversation::where('user_id', $user->id)
            ->where('id', $conversationId)
            ->first();
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'error' => 'Conversation non trouvée'
            ], 404);
        }

        $userMessageContent = $request->message ?? '';
        
        // Note: User message should already be saved by saveUserMessage endpoint
        // This endpoint only processes the AI response
        
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
            $conversation->id,
            $vectorMemoryId
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
            'has_attachment' => !empty($vectorMemoryId),
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