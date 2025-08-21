<?php

namespace App\Agents;

use App\Services\LanguageModelService;
use App\Services\OpenAIVectorService;
use Illuminate\Support\Facades\Log;

abstract class BaseAgent
{
    protected LanguageModelService $llm;
    protected OpenAIVectorService $embedding;
    protected ?object $search;
    protected string $agentName;

    public function __construct(
        LanguageModelService $llm,
        OpenAIVectorService $embedding,
        ?object $search = null
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
        
        // Get user project data directly (no more vectorization)
        $projet = \App\Models\Projet::where('user_id', $userId)->latest()->first();
        
        $context = [
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
        
        // Add project information if available
        if ($projet) {
            $context['project'] = [
                'name' => $projet->nom_projet,
                'company_name' => $projet->raison_sociale,
                'description' => $projet->description,
                'sectors' => $projet->secteurs ?? [],
                'products_services' => $projet->produits_services ?? [],
                'targets' => $projet->cibles ?? [],
                'maturity' => $projet->maturite,
                'funding_stage' => $projet->stade_financement,
                'revenue_models' => $projet->modeles_revenus ?? [],
                'region' => $projet->region,
                'team_size' => $projet->taille_equipe,
                'founders_count' => $projet->nombre_fondateurs,
                'female_founders_count' => $projet->nombre_fondatrices,
                'founders_age_ranges' => $projet->tranches_age_fondateurs ?? [],
                'founders_location' => $projet->localisation_fondateurs,
                'support_structures' => $projet->structures_accompagnement ?? [],
                'support_types' => $projet->types_soutien ?? [],
                'additional_needs' => $projet->details_besoins,
                'formalized' => $projet->formalise,
                'creation_year' => $projet->annee_creation,
            ];
        }
        
        return $context;
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
        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        // Standard log
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
        
        // Performance log
        Log::channel('agent_performance')->info('Agent execution completed', [
            'session_id' => $sessionId,
            'agent' => $this->agentName,
            'duration_ms' => round($duration, 2),
            'end_memory' => $endMemory,
            'peak_memory' => $peakMemory,
            'memory_used_mb' => round(($peakMemory - $endMemory) / 1024 / 1024, 2),
            'success' => $result['success'] ?? false,
            'tools_used' => $result['tools_used'] ?? [],
            'response_length' => $this->calculateResponseLength($result),
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