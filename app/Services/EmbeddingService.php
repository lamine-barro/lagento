<?php

namespace App\Services;

use App\Models\MorceauDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.voyageai.com/v1/',
            'timeout' => 20,
        ]);
    }

    public function contextualChunk(string $text, int $targetChars = 1200, int $overlapChars = 150): array
    {
        $paragraphs = preg_split('/\n{2,}/', trim($text)) ?: [];
        $chunks = [];
        $current = '';

        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            if (mb_strlen($current) + mb_strlen($p) + 2 <= $targetChars) {
                $current = $current === '' ? $p : ($current."\n\n".$p);
            } else {
                if ($current !== '') {
                    $chunks[] = $current;
                }
                $tail = mb_substr($current, max(0, mb_strlen($current) - $overlapChars));
                $current = ($tail !== '' ? $tail."\n\n" : '').$p;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    public function embed(array $inputs, string $model = 'voyage-3'): array
    {
        try {
            $res = $this->client->post('embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer '.env('VOYAGE_API_KEY'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'input' => $inputs,
                ],
            ]);

            $data = json_decode((string) $res->getBody(), true);
            return array_map(fn ($row) => $row['embedding'], $data['data'] ?? []);
        } catch (\Exception $e) {
            Log::error('Embedding generation failed', [
                'error' => $e->getMessage(),
                'inputs_count' => count($inputs)
            ]);
            return [];
        }
    }

    /**
     * Generate single embedding for query
     */
    public function generateQueryEmbedding(string $query): array
    {
        $embeddings = $this->embed([$query]);
        return $embeddings[0] ?? [];
    }

    /**
     * Store document chunk with embedding
     */
    public function storeDocumentChunk(
        string $sourceType,
        int $sourceId,
        string $content
    ): ?MorceauDocument {
        $embeddings = $this->embed([$content]);
        $embedding = $embeddings[0] ?? [];
        
        if (empty($embedding)) {
            Log::error('Failed to generate embedding for document chunk', [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'content_length' => strlen($content)
            ]);
            return null;
        }

        try {
            return MorceauDocument::create([
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'content' => $content,
                'embedding' => $embedding
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store document chunk', [
                'error' => $e->getMessage(),
                'source_type' => $sourceType,
                'source_id' => $sourceId
            ]);
            return null;
        }
    }

    /**
     * Process and store a document with chunking and embedding
     */
    public function processDocument(
        string $sourceType,
        int $sourceId,
        string $content
    ): array {
        $chunks = $this->contextualChunk($content);
        $storedChunks = [];
        
        foreach ($chunks as $chunk) {
            $documentChunk = $this->storeDocumentChunk($sourceType, $sourceId, $chunk);
            if ($documentChunk) {
                $storedChunks[] = $documentChunk;
            }
        }
        
        return $storedChunks;
    }
}


