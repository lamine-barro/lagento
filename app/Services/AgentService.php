<?php

namespace App\Services;

use App\Agents\AgentPrincipal;
use App\Agents\AgentSuggestionsConversation;
use App\Agents\AgentTitreConversation;

class AgentService
{
    private LanguageModelService $llm;
    private EmbeddingService $embedding;
    private SemanticSearchService $search;

    public function __construct(
        LanguageModelService $llm,
        EmbeddingService $embedding,
        SemanticSearchService $search
    ) {
        $this->llm = $llm;
        $this->embedding = $embedding;
        $this->search = $search;
    }

    /**
     * Process user message with AgentPrincipal
     */
    public function processMessage(string $message, int $userId, ?int $conversationId = null): array
    {
        $agent = new AgentPrincipal($this->llm, $this->embedding, $this->search);
        
        return $agent->execute([
            'user_message' => $message,
            'user_id' => $userId,
            'conversation_id' => $conversationId
        ]);
    }

    /**
     * Generate conversation suggestions with AgentSuggestionsConversation
     */
    public function generateSuggestions(int $userId, string $previousPage = '', string $activePage = ''): array
    {
        $agent = new AgentSuggestionsConversation($this->llm, $this->embedding, $this->search);
        
        return $agent->execute([
            'user_id' => $userId,
            'previous_page' => $previousPage,
            'active_page' => $activePage
        ]);
    }

    /**
     * Generate conversation title with AgentTitreConversation
     */
    public function generateTitle(
        string $userFirstMessage, 
        string $activePage = '', 
        ?int $conversationId = null,
        array $recentMessages = [],
        int $messageCount = 0
    ): array {
        $agent = new AgentTitreConversation($this->llm, $this->embedding, $this->search);
        
        return $agent->execute([
            'user_first_message' => $userFirstMessage,
            'active_page' => $activePage,
            'conversation_id' => $conversationId,
            'recent_messages' => $recentMessages,
            'message_count' => $messageCount
        ]);
    }

    /**
     * Update conversation title automatically (every 10 messages)
     */
    public function updateConversationTitleIfNeeded(int $conversationId): ?string
    {
        $conversation = \App\Models\UserConversation::with('messages')->find($conversationId);
        
        if (!$conversation) {
            return null;
        }

        $messageCount = $conversation->message_count ?? $conversation->messages->count();
        
        // Update title every 10 messages
        if ($messageCount > 0 && $messageCount % 10 === 0) {
            $recentMessages = $conversation->messages()
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content
                    ];
                })
                ->toArray();

            $firstMessage = $conversation->messages()->oldest()->first();
            
            $result = $this->generateTitle(
                $firstMessage->content ?? '',
                'chat',
                $conversationId,
                $recentMessages,
                $messageCount
            );

            if ($result['success'] && !empty($result['title'])) {
                $conversation->update(['title' => $result['title']]);
                return $result['title'];
            }
        }

        return null;
    }

    /**
     * Refresh suggestions (for the refresh button in tooltip)
     */
    public function refreshSuggestions(int $userId, string $activePage = ''): array
    {
        // Add some randomness to get different suggestions
        $previousPages = ['dashboard', 'conversations', 'profile', 'opportunities'];
        $randomPreviousPage = $previousPages[array_rand($previousPages)];
        
        return $this->generateSuggestions($userId, $randomPreviousPage, $activePage);
    }
}