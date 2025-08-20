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
        
        // Handle file attachment
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            try {
                // Store file using centralized service
                $fileStorage = app(\App\Services\FileStorageService::class);
                $result = $fileStorage->storeChatAttachment($file, $user->id);
                
                $attachmentData = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'path' => $result['path'],
                    'url' => $result['url']
                ];
                
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
    

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'conversation_id' => 'required|string',
            'stream' => 'nullable|string'
        ]);

        $user = Auth::user();
        $conversationId = $request->get('conversation_id');
        $useStream = $request->get('stream') === 'true' || $request->get('stream') === true;
        
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
        
        \Log::info('Chat request', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'message_length' => strlen($userMessageContent),
            'stream_requested' => $useStream,
            'stream_param' => $request->get('stream')
        ]);
        
        // If streaming is requested, use SSE
        if ($useStream) {
            \Log::info('Using streaming response');
            try {
                return $this->streamResponse($userMessageContent, $user, $conversation);
            } catch (\Exception $e) {
                \Log::error('Streaming failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Fallback to standard response
                return $this->standardResponse($userMessageContent, $user, $conversation);
            }
        }
        
        // Standard non-streaming response
        return $this->standardResponse($userMessageContent, $user, $conversation);
    }
    
    private function standardResponse($userMessageContent, $user, $conversation)
    {
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
        
        // Update conversation
        $conversation->increment('message_count', 1);
        $conversation->update(['last_message_at' => now()]);
        
        // Track chat interaction
        $this->analyticsService->trackChatInteraction($user, [
            'agent_type' => 'principal',
            'message_length' => strlen($userMessageContent),
            'tools_used' => $agentResult['tools_used'] ?? [],
            'response_length' => strlen($agentResult['response']),
            'has_attachment' => !empty($attachmentData),
            'conversation_id' => $conversation->id,
            'timestamp' => now()->toISOString()
        ]);
        
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
    
    public function testStream(Request $request)
    {
        return response()->stream(function () {
            // Disable all output buffering
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ini_set('output_buffering', 'off');
            ini_set('zlib.output_compression', false);
            
            $testMessage = "Ceci est un test de streaming. Voici plusieurs mots qui vont arriver par chunks pour tester le streaming en temps réel.";
            $words = explode(' ', $testMessage);
            
            echo "event: start\n";
            echo "data: " . json_encode(['message' => 'Starting test stream']) . "\n\n";
            flush();
            
            foreach ($words as $index => $word) {
                echo "event: chunk\n";
                echo "data: " . json_encode(['chunk' => $word . ' ', 'index' => $index]) . "\n\n";
                flush();
                usleep(200000); // 200ms delay
            }
            
            echo "event: complete\n";
            echo "data: " . json_encode(['message' => 'Stream complete']) . "\n\n";
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ]);
    }
    
    private function streamResponse($userMessageContent, $user, $conversation)
    {
        $headers = [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no'
        ];
        
        return response()->stream(function () use ($userMessageContent, $user, $conversation) {
            try {
                \Log::info('Starting streaming response');
                
                // Generate title if needed
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
                
                \Log::info('Processing message with agent service');
                
                // Process message and simulate streaming
                $agentResult = $this->agentService->processMessage(
                    $userMessageContent,
                    $user->id,
                    $conversation->id
                );
                
                if (!$agentResult['success']) {
                    \Log::error('Agent processing failed', ['error' => $agentResult['error'] ?? 'Unknown error']);
                    echo "event: error\n";
                    echo "data: " . json_encode(['error' => $agentResult['error'] ?? 'Erreur lors du traitement']) . "\n\n";
                    flush();
                    return;
                }
                
                \Log::info('Agent processing successful, starting streaming');
            } catch (\Exception $e) {
                \Log::error('Exception in streaming response', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                echo "event: error\n";
                echo "data: " . json_encode(['error' => 'Erreur interne du serveur']) . "\n\n";
                flush();
                return;
            }
            
            // Create message ID first
            $messageId = \Str::uuid();
            
            // Send metadata first
            echo "event: start\n";
            echo "data: " . json_encode([
                'message_id' => $messageId,
                'conversation_id' => $conversation->id,
                'tools_used' => $agentResult['tools_used'] ?? []
            ]) . "\n\n";
            flush();
            
            // Stream the response in chunks
            $response = $agentResult['response'];
            $chunks = $this->createReadableChunks($response);
            
            foreach ($chunks as $index => $chunk) {
                echo "event: chunk\n";
                echo "data: " . json_encode([
                    'chunk' => $chunk,
                    'index' => $index
                ]) . "\n\n";
                flush();
                usleep(30000); // 30ms delay for natural typing effect
            }
            
            // Save complete message
            $aiMessage = UserMessage::create([
                'id' => $messageId,
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'text_content' => $response
            ]);
            
            // Update conversation
            $conversation->increment('message_count', 1);
            $conversation->update(['last_message_at' => now()]);
            
            // Track interaction
            $this->analyticsService->trackChatInteraction($user, [
                'agent_type' => 'principal',
                'message_length' => strlen($userMessageContent),
                'tools_used' => $agentResult['tools_used'] ?? [],
                'response_length' => strlen($response),
                'has_attachment' => !empty($attachmentData),
                'conversation_id' => $conversation->id,
                'timestamp' => now()->toISOString()
            ]);
            
            // Send completion event
            echo "event: complete\n";
            echo "data: " . json_encode(['message_id' => $messageId]) . "\n\n";
            flush();
        }, 200, $headers);
    }
    
    private function createReadableChunks($text, $chunkSize = 15)
    {
        $chunks = [];
        $words = preg_split('/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $currentChunk = '';
        $wordCount = 0;
        
        foreach ($words as $word) {
            $currentChunk .= $word;
            
            // Count actual words (not whitespace)
            if (!preg_match('/^\s+$/u', $word)) {
                $wordCount++;
            }
            
            // Create chunk at word boundaries
            if ($wordCount >= $chunkSize) {
                // Ensure we don't break markdown structures
                if ($this->isCompleteMarkdownUnit($currentChunk)) {
                    $chunks[] = $currentChunk;
                    $currentChunk = '';
                    $wordCount = 0;
                }
            }
        }
        
        // Add remaining content
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }
        
        return $chunks;
    }
    
    private function isCompleteMarkdownUnit($text)
    {
        // Check if we're not in the middle of a markdown structure
        $openBold = substr_count($text, '**') % 2 === 0;
        $openItalic = substr_count($text, '*') % 2 === 0 || substr_count($text, '_') % 2 === 0;
        $openLink = substr_count($text, '[') === substr_count($text, ']');
        $openCode = substr_count($text, '`') % 2 === 0;
        
        return $openBold && $openItalic && $openLink && $openCode;
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