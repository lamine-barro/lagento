<?php

namespace App\Agents;

use App\Services\LanguageModelService;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;

abstract class BaseAgent
{
    protected LanguageModelService $llm;
    protected EmbeddingService $embedding;
    protected SemanticSearchService $search;

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
     * Execute the agent's main functionality
     */
    abstract public function execute(array $inputs): array;

    /**
     * Get the agent's configuration
     */
    abstract protected function getConfig(): array;

    /**
     * Prepare system prompt for the agent
     */
    protected function prepareSystemPrompt(string $instructions, array $context = []): string
    {
        $systemPrompt = $instructions;
        
        if (!empty($context)) {
            $systemPrompt .= "\n\nContexte :\n";
            foreach ($context as $key => $value) {
                if (is_array($value)) {
                    $systemPrompt .= "- {$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
                } else {
                    $systemPrompt .= "- {$key}: {$value}\n";
                }
            }
        }

        return $systemPrompt;
    }

    /**
     * Format messages for LLM API
     */
    protected function formatMessages(string $systemPrompt, string $userMessage): array
    {
        return [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ];
    }

    /**
     * Log tool usage for analytics
     */
    protected function logToolUsage(string $tool, array $params = []): void
    {
        // Log tool usage for user analytics
        // Implementation depends on your logging strategy
        \Log::info("Agent tool usage", [
            'agent' => static::class,
            'tool' => $tool,
            'params' => $params,
            'timestamp' => now()
        ]);
    }

    /**
     * Get user analytics context (excluding sensitive data)
     */
    protected function getUserAnalyticsContext(int $userId): array
    {
        $user = \App\Models\User::with(['analytics'])->find($userId);
        
        if (!$user) {
            return [];
        }

        $analytics = $user->analytics()->latest()->first();
        
        return [
            'profile_type' => $user->profile_type,
            'company_name' => $user->company_name,
            'business_sector' => $user->business_sector,
            'business_stage' => $user->business_stage,
            'team_size' => $user->team_size,
            'region' => $user->region,
            'messages_sent' => $analytics->messages_sent ?? 0,
            'documents_generated' => $analytics->documents_generated ?? 0,
            'opportunities_matched' => $analytics->opportunities_matched ?? 0,
            'last_activity' => $analytics->updated_at ?? null,
        ];
    }

    /**
     * Generate structured markdown response
     */
    protected function formatMarkdownResponse(string $content): string
    {
        // Ensure proper markdown formatting
        $content = trim($content);
        
        // Add proper spacing for headers
        $content = preg_replace('/^(#{1,3})\s*(.+)$/m', '$1 $2', $content);
        
        // Ensure lists have proper spacing
        $content = preg_replace('/\n(-|\*|\+|\d+\.)\s/', "\n\n$1 ", $content);
        
        return $content;
    }
}