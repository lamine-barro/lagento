<?php

namespace App\Agents;

use App\Services\LanguageModelService;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent
{
    protected LanguageModelService $llm;
    protected EmbeddingService $embedding;
    protected SemanticSearchService $search;
    protected string $agentName;

    public function __construct(
        LanguageModelService $llm,
        EmbeddingService $embedding,
        SemanticSearchService $search
    ) {
        $this->llm = $llm;
        $this->embedding = $embedding;
        $this->search = $search;
        $this->agentName = class_basename(static::class);
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
     * Get user analytics context (excluding sensitive data)
     */
    protected function getUserAnalyticsContext(string $userId): array
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

    /**
     * Log agent execution start
     */
    protected function logExecutionStart(array $inputs): string
    {
        $sessionId = uniqid($this->agentName . '_');
        
        Log::info("🤖 [{$this->agentName}] EXECUTION START", [
            'session_id' => $sessionId,
            'agent' => $this->agentName,
            'inputs' => [
                'user_id' => $inputs['user_id'] ?? 'N/A',
                'conversation_id' => $inputs['conversation_id'] ?? 'N/A',
                'user_message' => $inputs['user_message'] ?? $inputs['message'] ?? 'N/A',
                'input_size' => strlen(json_encode($inputs)),
                'all_inputs' => $inputs
            ],
            'timestamp' => now()->toISOString()
        ]);
        
        return $sessionId;
    }

    /**
     * Log tool usage
     */
    protected function logToolUsage(string $tool, array $data = []): void
    {
        Log::info("🔧 [{$this->agentName}] TOOL USED", [
            'agent' => $this->agentName,
            'tool' => $tool,
            'data_size' => strlen(json_encode($data)),
            'timestamp' => now()->toISOString(),
            'data' => $data
        ]);
    }

    /**
     * Log LLM API call
     */
    protected function logLLMCall(array $messages, array $config, float $startTime): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // in ms
        
        $inputTokens = 0;
        foreach ($messages as $message) {
            $inputTokens += str_word_count($message['content'] ?? '') * 1.3; // approximation
        }
        
        Log::info("🧠 [{$this->agentName}] LLM CALL", [
            'agent' => $this->agentName,
            'model' => $config['model'] ?? 'unknown',
            'temperature' => $config['temperature'] ?? 'unknown',
            'max_tokens' => $config['max_tokens'] ?? 'unknown',
            'estimated_input_tokens' => round($inputTokens),
            'duration_ms' => round($duration, 2),
            'messages_count' => count($messages),
            'system_prompt_length' => strlen($messages[0]['content'] ?? ''),
            'user_message_length' => strlen($messages[1]['content'] ?? ''),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log agent execution end
     */
    protected function logExecutionEnd(string $sessionId, array $result, float $startTime): void
    {
        $duration = (microtime(true) - $startTime) * 1000; // in ms
        
        Log::info("✅ [{$this->agentName}] EXECUTION END", [
            'session_id' => $sessionId,
            'agent' => $this->agentName,
            'success' => $result['success'] ?? false,
            'duration_ms' => round($duration, 2),
            'response_length' => $this->calculateResponseLength($result),
            'output_size' => strlen(json_encode($result)),
            'tools_used' => $result['tools_used'] ?? [],
            'metadata' => $result['metadata'] ?? [],
            'timestamp' => now()->toISOString()
        ]);
        
        if (!($result['success'] ?? false)) {
            Log::error("❌ [{$this->agentName}] EXECUTION FAILED", [
                'session_id' => $sessionId,
                'agent' => $this->agentName,
                'error' => $result['error'] ?? 'Unknown error',
                'duration_ms' => round($duration, 2),
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Log error with context
     */
    protected function logError(string $error, array $context = []): void
    {
        Log::error("💥 [{$this->agentName}] ERROR", [
            'agent' => $this->agentName,
            'error' => $error,
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log debug information
     */
    protected function logDebug(string $message, array $data = []): void
    {
        Log::debug("🔍 [{$this->agentName}] DEBUG", [
            'agent' => $this->agentName,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Calculate response length for different result types
     */
    private function calculateResponseLength(array $result): int
    {
        // Pour les réponses textuelles
        if (isset($result['response']) && is_string($result['response'])) {
            return strlen($result['response']);
        }
        
        // Pour les titres
        if (isset($result['title']) && is_string($result['title'])) {
            return strlen($result['title']);
        }
        
        // Pour les suggestions (array)
        if (isset($result['suggestions']) && is_array($result['suggestions'])) {
            return array_reduce($result['suggestions'], function($carry, $suggestion) {
                return $carry + strlen(is_string($suggestion) ? $suggestion : '');
            }, 0);
        }
        
        return 0;
    }
}