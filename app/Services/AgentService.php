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
    public function processMessage(string $message, string $userId, ?string $conversationId = null): array
    {
        $agent = new AgentPrincipal($this->llm, $this->embedding, $this->search);

        // Injecter le contexte: 4 derniers messages + résumé de conversation
        $contextMessages = [];
        $conversationSummary = '';
        if ($conversationId) {
            $conv = \App\Models\UserConversation::find($conversationId);
            if ($conv) {
                $conversationSummary = (string) ($conv->summary ?? '');
                $contextMessages = \App\Models\UserMessage::where('conversation_id', $conversationId)
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->sortBy('created_at')
                    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                    ->values()
                    ->toArray();
            }
        }

        return $agent->execute([
            'user_message' => $message,
            'user_id' => $userId,
            'conversation_id' => $conversationId,
            'recent_messages' => $contextMessages,
            'conversation_summary' => $conversationSummary,
        ]);
    }

    /**
     * Generate conversation suggestions with AgentSuggestionsConversation
     */
    public function generateSuggestions(string $userId, string $previousPage = '', string $activePage = ''): array
    {
        // Clé de cache unique par utilisateur et page
        $cacheKey = "suggestions:{$userId}:" . md5($previousPage . ':' . $activePage);
        
        // Vérifier le cache (30 minutes = 1800 secondes)
        $cachedSuggestions = \Cache::get($cacheKey);
        
        if ($cachedSuggestions !== null) {
            return [
                'success' => true,
                'suggestions' => $cachedSuggestions,
                'cached' => true,
                'cache_key' => $cacheKey
            ];
        }
        
        // Générer nouvelles suggestions si pas en cache
        $agent = new AgentSuggestionsConversation($this->llm, $this->embedding, $this->search);
        
        $result = $agent->execute([
            'user_id' => $userId,
            'previous_page' => $previousPage,
            'active_page' => $activePage
        ]);
        
        // Mettre en cache si la génération a réussi
        if (isset($result['success']) && $result['success'] && isset($result['suggestions'])) {
            \Cache::put($cacheKey, $result['suggestions'], 1800); // 30 minutes
        }
        
        // Ajouter des métadonnées sur le cache
        $result['cached'] = false;
        $result['cache_key'] = $cacheKey;
        
        return $result;
    }

    /**
     * Generate conversation title with AgentTitreConversation
     */
    public function generateTitle(
        string $userFirstMessage, 
        string $activePage = '', 
        ?string $conversationId = null,
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
    public function updateConversationTitleIfNeeded(string $conversationId): ?string
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

                // Générer le résumé (500 tokens max) via gpt-5-nano et indexer l'embedding
                $summary = $this->generateConversationSummary($conversation);
                if ($summary) {
                    $conversation->summary = $summary;
                    $emb = $this->embedding->embed([$summary]);
                    $conversation->summary_embedding = $emb[0] ?? null;
                    $conversation->save();
                }

                return $result['title'];
            }
        }

        return null;
    }

    /**
     * Generate conversation summary using a lightweight model (gpt-5-nano)
     */
    private function generateConversationSummary(\App\Models\UserConversation $conversation): ?string
    {
        try {
            $lastMessages = $conversation->messages()
                ->latest()
                ->limit(50)
                ->get()
                ->sortBy('created_at')
                ->map(fn($m) => ($m->role === 'user' ? 'Utilisateur: ' : 'Assistant: ') . $m->content)
                ->implode("\n\n");

            $prompt = "Résume cette conversation en français en 500 tokens maximum. Conserve le contexte clé, sujets, décisions, prochaines étapes. Pas de détails inutiles.\n\nConversation:\n" . $lastMessages;
            $messages = [
                ['role' => 'system', 'content' => 'Tu es un assistant qui produit des résumés concis et factuels en français.'],
                ['role' => 'user', 'content' => $prompt],
            ];
            $raw = $this->llm->chat($messages, 'gpt-4.1-nano', 0.2, 1200);
            return trim($raw);
        } catch (\Throwable $e) {
            \Log::error('Conversation summary generation failed', ['id' => $conversation->id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Refresh suggestions (for the refresh button in tooltip)
     * Force la régénération en ignorant le cache
     */
    public function refreshSuggestions(string $userId, string $activePage = ''): array
    {
        // Add some randomness to get different suggestions
        $previousPages = ['diagnostic', 'conversations', 'profile', 'opportunities'];
        $randomPreviousPage = $previousPages[array_rand($previousPages)];
        
        // Supprimer le cache existant pour cette combinaison
        $cacheKey = "suggestions:{$userId}:" . md5($randomPreviousPage . ':' . $activePage);
        \Cache::forget($cacheKey);
        
        // Générer de nouvelles suggestions
        $agent = new AgentSuggestionsConversation($this->llm, $this->embedding, $this->search);
        
        $result = $agent->execute([
            'user_id' => $userId,
            'previous_page' => $randomPreviousPage,
            'active_page' => $activePage
        ]);
        
        // Mettre en cache les nouvelles suggestions
        if (isset($result['success']) && $result['success'] && isset($result['suggestions'])) {
            \Cache::put($cacheKey, $result['suggestions'], 1800); // 30 minutes
        }
        
        // Ajouter des métadonnées
        $result['cached'] = false;
        $result['refreshed'] = true;
        $result['cache_key'] = $cacheKey;
        
        return $result;
    }

    /**
     * Clear all cached suggestions for a specific user
     */
    public function clearUserSuggestionsCache(string $userId): void
    {
        // Pattern pour toutes les clés de cache de suggestions de cet utilisateur
        $pattern = "suggestions:{$userId}:*";
        
        try {
            // Si on utilise Redis, on peut utiliser le pattern
            if (\Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = \Cache::getStore()->getRedis();
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // Pour les autres stores, on peut pas faire de pattern matching facilement
                // On pourrait maintenir une liste des clés mais pour simplifier on ne fait rien
                \Log::info("Cache pattern deletion not supported for current cache store");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to clear user suggestions cache: " . $e->getMessage());
        }
    }

    /**
     * Get cache info for debugging
     */
    public function getSuggestionsCacheInfo(string $userId, string $previousPage = '', string $activePage = ''): array
    {
        $cacheKey = "suggestions:{$userId}:" . md5($previousPage . ':' . $activePage);
        $exists = \Cache::has($cacheKey);
        
        return [
            'cache_key' => $cacheKey,
            'exists' => $exists,
            'ttl' => $exists ? \Cache::getStore()->getRedis()->ttl($cacheKey) : 0
        ];
    }
}