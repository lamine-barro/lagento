<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VoyageVectorService
{
    private string $apiKey;
    private string $model = 'voyage-large-2';
    private int $defaultDimension = 1536; // voyage-large-2 default dimension

    public function __construct()
    {
        $this->apiKey = config('services.voyage.api_key');
        
        if (!$this->apiKey) {
            throw new \Exception('VOYAGE_API_KEY not configured');
        }
    }

    /**
     * Embed texts with contextualized chunks using voyage-context-3
     */
    public function embedWithContext(array $texts, string $documentContext = '', int $dimension = null): array
    {
        try {
            $dimension = $dimension ?? $this->defaultDimension;
            
            // For now, use regular embeddings API with contextualized texts
            $contextualizedTexts = array_map(function($text) use ($documentContext) {
                return $documentContext ? "$documentContext\n\n$text" : $text;
            }, $texts);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.voyageai.com/v1/embeddings', [
                'model' => $this->model,
                'input' => $contextualizedTexts,
                'input_type' => 'document'
            ]);

            if (!$response->successful()) {
                Log::error('Voyage API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Voyage API request failed: ' . $response->body());
            }

            $data = $response->json();
            
            Log::info('Voyage embeddings generated', [
                'input_count' => count($texts),
                'embedding_count' => count($data['data'] ?? []),
                'dimension' => $dimension
            ]);

            return $data['data'] ?? [];
            
        } catch (\Exception $e) {
            Log::error('VoyageVectorService::embedWithContext failed', [
                'error' => $e->getMessage(),
                'texts_count' => count($texts),
                'context' => $documentContext
            ]);
            throw $e;
        }
    }

    /**
     * Embed single query for search
     */
    public function embedQuery(string $query, int $dimension = null): array
    {
        try {
            $dimension = $dimension ?? $this->defaultDimension;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.voyageai.com/v1/embeddings', [
                'model' => $this->model,
                'input' => [$query],
                'input_type' => 'query',
                'output_dimension' => $dimension,
                'output_dtype' => 'float'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Voyage API request failed: ' . $response->body());
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? [];
            
        } catch (\Exception $e) {
            Log::error('VoyageVectorService::embedQuery failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            throw $e;
        }
    }

    /**
     * Semantic search across vector memories
     */
    public function semanticSearch(
        string $query, 
        array $memoryTypes = [], 
        array $filters = [], 
        int $limit = 10,
        float $threshold = 0.7
    ): array {
        try {
            // Generate query embedding
            $queryEmbedding = $this->embedQuery($query);
            
            if (empty($queryEmbedding)) {
                return [];
            }

            // Build search query
            $sql = "
                SELECT 
                    id,
                    memory_type,
                    source_id,
                    chunk_content,
                    metadata,
                    (embedding <=> ?) as distance,
                    (1 - (embedding <=> ?)) as similarity
                FROM vector_memories 
                WHERE 1=1
            ";
            
            $params = [
                json_encode($queryEmbedding),
                json_encode($queryEmbedding)
            ];

            // Filter by memory types
            if (!empty($memoryTypes)) {
                $placeholders = str_repeat('?,', count($memoryTypes) - 1) . '?';
                $sql .= " AND memory_type IN ($placeholders)";
                $params = array_merge($params, $memoryTypes);
            }

            // Apply metadata filters
            foreach ($filters as $key => $value) {
                $sql .= " AND metadata->>'$key' = ?";
                $params[] = $value;
            }

            // Add similarity threshold and ordering
            $sql .= " AND (1 - (embedding <=> ?)) >= ?";
            $sql .= " ORDER BY embedding <=> ?";
            $sql .= " LIMIT ?";
            
            $params[] = json_encode($queryEmbedding);
            $params[] = $threshold;
            $params[] = json_encode($queryEmbedding);
            $params[] = $limit;

            $results = DB::select($sql, $params);

            Log::info('Vector search completed', [
                'query' => substr($query, 0, 100),
                'memory_types' => $memoryTypes,
                'results_count' => count($results),
                'threshold' => $threshold
            ]);

            return array_map(function($result) {
                return [
                    'id' => $result->id,
                    'memory_type' => $result->memory_type,
                    'source_id' => $result->source_id,
                    'content' => $result->chunk_content,
                    'metadata' => json_decode($result->metadata, true),
                    'similarity' => round($result->similarity, 4)
                ];
            }, $results);

        } catch (\Exception $e) {
            Log::error('VoyageVectorService::semanticSearch failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'memory_types' => $memoryTypes
            ]);
            return [];
        }
    }

    /**
     * Intelligent chunking with context preservation
     */
    public function intelligentChunk(string $content, string $context = '', int $maxChunkSize = 500): array
    {
        // Simple sentence-based chunking with overlap
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            $testChunk = trim($currentChunk . ' ' . $sentence);
            
            if (strlen($testChunk) > $maxChunkSize && !empty($currentChunk)) {
                // Add context prefix to chunk
                $contextualChunk = $context ? "$context\n\n$currentChunk" : $currentChunk;
                $chunks[] = trim($contextualChunk);
                
                // Start new chunk with overlap (last sentence)
                $currentChunk = $sentence;
            } else {
                $currentChunk = $testChunk;
            }
        }
        
        // Add final chunk
        if (!empty($currentChunk)) {
            $contextualChunk = $context ? "$context\n\n$currentChunk" : $currentChunk;
            $chunks[] = trim($contextualChunk);
        }
        
        return array_filter($chunks, fn($chunk) => strlen(trim($chunk)) > 50);
    }

    /**
     * Get embedding statistics
     */
    public function getStats(): array
    {
        try {
            $stats = DB::select("
                SELECT 
                    memory_type,
                    COUNT(*) as total_vectors,
                    AVG(array_length(embedding, 1)) as avg_dimension,
                    MIN(created_at) as oldest,
                    MAX(updated_at) as newest
                FROM vector_memories 
                GROUP BY memory_type
                ORDER BY total_vectors DESC
            ");

            return array_map(function($stat) {
                return [
                    'type' => $stat->memory_type,
                    'count' => $stat->total_vectors,
                    'dimension' => round($stat->avg_dimension),
                    'oldest' => $stat->oldest,
                    'newest' => $stat->newest
                ];
            }, $stats);
            
        } catch (\Exception $e) {
            Log::error('VoyageVectorService::getStats failed', ['error' => $e->getMessage()]);
            return [];
        }
    }
}