<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SemanticSearchService
{
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Search for similar documents using semantic search
     */
    public function searchSimilar(string $query, int $limit = 5, float $threshold = 0.7): array
    {
        try {
            $queryEmbedding = $this->embeddingService->generateQueryEmbedding($query);
            
            if (empty($queryEmbedding)) {
                Log::warning('Failed to generate query embedding', ['query' => $query]);
                return [];
            }

            return DocumentChunk::similarTo($queryEmbedding, $threshold)
                ->limit($limit)
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Semantic search failed', [
                'error' => $e->getMessage(),
                'query' => $query
            ]);
            return [];
        }
    }

    /**
     * Search with specific source type filter
     */
    public function searchByType(string $query, string $sourceType, int $limit = 5, float $threshold = 0.7): array
    {
        try {
            $queryEmbedding = $this->embeddingService->generateQueryEmbedding($query);
            
            if (empty($queryEmbedding)) {
                return [];
            }

            return DocumentChunk::ofType($sourceType)
                ->similarTo($queryEmbedding, $threshold)
                ->limit($limit)
                ->get()
                ->toArray();

        } catch (\Exception $e) {
            Log::error('Semantic search by type failed', [
                'error' => $e->getMessage(),
                'query' => $query,
                'source_type' => $sourceType
            ]);
            return [];
        }
    }

    /**
     * Legacy method for backward compatibility
     */
    public function searchSimilarLegacy(string $query, EmbeddingService $embeddings, int $limit = 5): array
    {
        $vectors = $embeddings->embed([$query]);
        $vector = $vectors[0] ?? null;
        if ($vector === null) {
            return [];
        }

        $vectorLiteral = '{'.implode(',', $vector).'}';

        $rows = DB::select(
            "SELECT id, source_type, source_id, content, 1 - (embedding <=> ?) AS cosine_similarity\n".
            "FROM document_chunks\n".
            "ORDER BY embedding <=> ? ASC\n".
            "LIMIT ?",
            [$vectorLiteral, $vectorLiteral, $limit]
        );

        return array_map(fn ($r) => (array) $r, $rows);
    }
}


