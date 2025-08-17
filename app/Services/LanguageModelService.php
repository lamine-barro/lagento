<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class LanguageModelService
{
    public function chat(
        array $messages,
        string $model = 'gpt-5-mini',
        float $temperature = 0.3,
        int $maxTokens = 800,
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

                $resp = OpenAI::responses()->create([
                    'model' => $model,
                    'tools' => $tools,
                    'input' => $input,
                    'temperature' => $temperature,
                    'max_output_tokens' => $maxTokens,
                ]);

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

            // Default: classic Chat Completions
            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            Log::error('LLM chat failed', ['error' => $e->getMessage()]);
            return '';
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
                // gpt-image-1 always returns base64; keep explicit for clarity
                'response_format' => 'b64_json',
            ]);

            $b64 = $resp->data[0]->b64_json ?? null;
            return is_string($b64) ? $b64 : null;
        } catch (\Throwable $e) {
            Log::error('Image generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}


