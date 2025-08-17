<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class LanguageModelService
{
    public function chat(array $messages, string $model = 'gpt-5-mini', float $temperature = 0.3, int $maxTokens = 800): string
    {
        $response = OpenAI::chat()->create([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        return $response->choices[0]->message->content ?? '';
    }
}


