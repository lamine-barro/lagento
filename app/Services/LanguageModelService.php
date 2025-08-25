<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class LanguageModelService
{
    public function chat(
        array $messages,
        string $model = 'gpt-4.1-mini',
        ?float $temperature = null,
        int $maxTokens = 2000,
        array $options = []
    ): string
    {
        try {
            // Enable built-in web search via Responses API when requested
            if (!empty($options['web_search'])) {
                $tools = [
                    [
                        'type' => 'web_search_preview',
                    ],
                ];

                if (!empty($options['user_location']) && is_array($options['user_location'])) {
                    $tools[0]['user_location'] = array_merge(['type' => 'approximate'], $options['user_location']);
                }

                if (!empty($options['search_context_size'])) {
                    $tools[0]['search_context_size'] = $options['search_context_size'];
                }

                // Flatten messages to a single input string for Responses API
                $input = $this->flattenMessages($messages);

                $responseParams = [
                    'model' => $model,
                    'tools' => $tools,
                    'input' => $input,
                    'max_output_tokens' => $maxTokens,
                ];

                // Only add temperature for non-GPT-5 models
                if (!str_starts_with($model, 'gpt-5')) {
                    $responseParams['temperature'] = $temperature ?? 0.3;
                }

                $resp = OpenAI::responses()->create($responseParams);

                // Prefer output_text if available
                if (method_exists($resp, 'output_text') && !empty($resp->output_text)) {
                    return (string) $resp->output_text;
                }

                // Fallback: parse first message content text
                if (!empty($resp->output) && is_array($resp->output)) {
                    foreach ($resp->output as $item) {
                        if (($item['type'] ?? '') === 'message') {
                            $content = $item['content'][0]['text'] ?? '';
                            if ($content !== '') {
                                return $content;
                            }
                        }
                    }
                }
            }

            // Prepare chat completion parameters
            $chatParams = [
                'model' => $model,
                'messages' => $messages,
            ];

            // GPT-5 models support reasoning_effort instead of temperature and use max_completion_tokens
            if (str_starts_with($model, 'gpt-5')) {
                $chatParams['max_completion_tokens'] = $maxTokens;
                
                // Add reasoning_effort for GPT-5 models (low, medium, high, or minimal)
                $chatParams['reasoning_effort'] = $options['reasoning_effort'] ?? 'medium';
                
                // Optionally add verbosity parameter for GPT-5
                if (isset($options['verbosity'])) {
                    $chatParams['verbosity'] = $options['verbosity'];
                }

                // Add structured outputs support
                if (isset($options['response_format'])) {
                    $chatParams['response_format'] = $options['response_format'];
                }
            } else {
                // Standard parameters for GPT-4.1 and other models
                $chatParams['max_tokens'] = $maxTokens;
                $chatParams['temperature'] = $temperature ?? 0.3;
                
                // Add JSON response format if requested
                if (isset($options['response_format'])) {
                    $chatParams['response_format'] = $options['response_format'];
                }
            }

            $response = OpenAI::chat()->create($chatParams);

            $content = $response->choices[0]->message->content ?? '';
            
            if (empty($content)) {
                throw new \Exception('Empty response from OpenAI API');
            }
            
            return $content;
        } catch (\Throwable $e) {
            Log::error('LLM chat failed', ['error' => $e->getMessage(), 'model' => $model]);
            throw $e;
        }
    }

    private function flattenMessages(array $messages): string
    {
        $parts = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $content = is_string($m['content'] ?? '') ? $m['content'] : json_encode($m['content']);
            $parts[] = strtoupper($role) . ": " . $content;
        }
        return implode("\n\n", $parts);
    }

    public function generateImage(string $prompt, string $size = '1024x1024'): ?string
    {
        try {
            $resp = OpenAI::images()->create([
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
            ]);

            $b64 = $resp->data[0]->b64_json ?? null;
            return is_string($b64) ? $b64 : null;
        } catch (\Throwable $e) {
            Log::error('Image generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}


