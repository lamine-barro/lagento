<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAIVectorService
{
    private string $openaiApiKey;
    private string $pineconeApiKey;
    private string $pineconeHost;
    private string $embeddingModel = 'text-embedding-3-large';
    private int $dimensions = 1024;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->pineconeApiKey = config('services.pinecone.api_key');
        $this->pineconeHost = config('services.pinecone.host');

        if (!$this->openaiApiKey || !$this->pineconeApiKey || !$this->pineconeHost) {
            throw new Exception('OpenAI or Pinecone configuration missing');
        }
    }

    /**
     * Create embeddings using OpenAI text-embedding-3-large
     */
    public function createEmbeddings(array $texts): array
    {
        try {
            // Clean texts to ensure proper UTF-8 encoding
            $cleanTexts = array_map(function($text) {
                return mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            }, $texts);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $this->embeddingModel,
                'input' => $cleanTexts,
                'dimensions' => $this->dimensions
            ]);

            if (!$response->successful()) {
                throw new Exception('OpenAI API error: ' . $response->body());
            }

            $data = $response->json();
            
            return array_map(function($item) {
                return $item['embedding'];
            }, $data['data']);

        } catch (Exception $e) {
            Log::error('Failed to create embeddings', [
                'error' => $e->getMessage(),
                'texts_count' => count($texts)
            ]);
            throw $e;
        }
    }

    /**
     * Create embeddings in batches to respect token limits
     */
    public function createEmbeddingsInBatches(array $texts, int $maxTokensPerBatch = 250000): array
    {
        $allEmbeddings = [];
        $currentBatch = [];
        $currentTokens = 0;
        
        foreach ($texts as $text) {
            // Rough token estimate (1 token ≈ 4 characters for text-embedding models)
            $estimatedTokens = strlen($text) / 4;
            
            if ($currentTokens + $estimatedTokens > $maxTokensPerBatch && !empty($currentBatch)) {
                // Process current batch
                $batchEmbeddings = $this->createEmbeddings($currentBatch);
                $allEmbeddings = array_merge($allEmbeddings, $batchEmbeddings);
                
                // Reset for next batch
                $currentBatch = [];
                $currentTokens = 0;
                
                // Add small delay between batches to be nice to API
                sleep(1);
            }
            
            $currentBatch[] = $text;
            $currentTokens += $estimatedTokens;
        }
        
        // Process final batch
        if (!empty($currentBatch)) {
            $batchEmbeddings = $this->createEmbeddings($currentBatch);
            $allEmbeddings = array_merge($allEmbeddings, $batchEmbeddings);
        }
        
        return $allEmbeddings;
    }

    /**
     * Intelligent chunking with overlap
     */
    public function chunkText(string $text, int $maxChunkSize = 1000, float $overlapPercentage = 0.2): array
    {
        if (strlen($text) <= $maxChunkSize) {
            return [$text];
        }

        $chunks = [];
        $overlapSize = (int) ($maxChunkSize * $overlapPercentage);
        $effectiveChunkSize = $maxChunkSize - $overlapSize;
        
        $start = 0;
        while ($start < strlen($text)) {
            $end = $start + $maxChunkSize;
            
            // Try to break at sentence or paragraph boundaries
            if ($end < strlen($text)) {
                $breakPositions = [
                    strrpos(substr($text, $start, $maxChunkSize), "\n\n"), // Paragraph break
                    strrpos(substr($text, $start, $maxChunkSize), ". "),   // Sentence break
                    strrpos(substr($text, $start, $maxChunkSize), "! "),   // Exclamation
                    strrpos(substr($text, $start, $maxChunkSize), "? "),   // Question
                ];
                
                $filteredBreaks = array_filter($breakPositions, function($pos) use ($effectiveChunkSize) {
                    return $pos !== false && $pos > $effectiveChunkSize * 0.7; // At least 70% of chunk
                });
                
                $bestBreak = false;
                if (!empty($filteredBreaks)) {
                    $bestBreak = max($filteredBreaks);
                }
                
                if ($bestBreak !== false) {
                    $end = $start + $bestBreak + 1;
                }
            }
            
            $chunk = trim(substr($text, $start, $end - $start));
            if (!empty($chunk)) {
                // Clean UTF-8 encoding
                $chunk = mb_convert_encoding($chunk, 'UTF-8', 'UTF-8');
                $chunks[] = $chunk;
            }
            
            $start += $effectiveChunkSize;
            
            if ($start >= strlen($text)) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Store vectors in Pinecone
     */
    public function upsertVectors(array $vectors, string $namespace = null): bool
    {
        try {
            $payload = ['vectors' => $vectors];
            
            if ($namespace) {
                $payload['namespace'] = $namespace;
            }
            
            $response = Http::withHeaders([
                'Api-Key' => $this->pineconeApiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post($this->pineconeHost . '/vectors/upsert', $payload);

            if (!$response->successful()) {
                throw new Exception('Pinecone upsert error: ' . $response->body());
            }

            Log::info('Vectors upserted to Pinecone', ['count' => count($vectors)]);
            return true;

        } catch (Exception $e) {
            Log::error('Failed to upsert vectors to Pinecone', [
                'error' => $e->getMessage(),
                'vectors_count' => count($vectors)
            ]);
            return false;
        }
    }

    /**
     * Delete vectors from Pinecone by namespace or filter
     */
    public function deleteVectors(array $ids = null, string $namespace = null, array $filter = null): bool
    {
        try {
            $payload = [];
            
            if ($ids) {
                $payload['ids'] = $ids;
            }
            
            if ($namespace) {
                $payload['namespace'] = $namespace;
            }
            
            if ($filter) {
                $payload['filter'] = $filter;
            }

            // If no specific criteria, delete all in namespace
            if (empty($payload) && $namespace) {
                $payload = ['deleteAll' => true, 'namespace' => $namespace];
            } elseif (empty($payload)) {
                $payload = ['deleteAll' => true];
            }

            $response = Http::withHeaders([
                'Api-Key' => $this->pineconeApiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post($this->pineconeHost . '/vectors/delete', $payload);

            if (!$response->successful()) {
                throw new Exception('Pinecone delete error: ' . $response->body());
            }

            Log::info('Vectors deleted from Pinecone', $payload);
            return true;

        } catch (Exception $e) {
            Log::error('Failed to delete vectors from Pinecone', [
                'error' => $e->getMessage(),
                'payload' => $payload ?? []
            ]);
            return false;
        }
    }

    /**
     * Search similar vectors in Pinecone
     */
    public function searchSimilar(string $query, int $topK = 10, array $filter = null, string $namespace = null): array
    {
        try {
            // First get embedding for the query
            $queryEmbedding = $this->createEmbeddings([$query])[0];

            $payload = [
                'vector' => $queryEmbedding,
                'topK' => $topK,
                'includeMetadata' => true,
                'includeValues' => false
            ];

            if ($filter) {
                $payload['filter'] = $filter;
            }

            if ($namespace) {
                $payload['namespace'] = $namespace;
            }

            $response = Http::withHeaders([
                'Api-Key' => $this->pineconeApiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post($this->pineconeHost . '/query', $payload);

            if (!$response->successful()) {
                throw new Exception('Pinecone search error: ' . $response->body());
            }

            $data = $response->json();
            return $data['matches'] ?? [];

        } catch (Exception $e) {
            Log::error('Failed to search vectors in Pinecone', [
                'error' => $e->getMessage(),
                'query' => substr($query, 0, 100)
            ]);
            return [];
        }
    }

    /**
     * Process and vectorize content with automatic chunking and storage
     */
    public function processAndStore(
        string $content,
        string $vectorId,
        array $metadata = [],
        string $namespace = null,
        int $maxChunkSize = 1000,
        float $overlapPercentage = 0.1
    ): bool {
        try {
            // Delete existing vectors for this ID
            if ($namespace) {
                $this->deleteVectors(null, $namespace, ['vector_id' => $vectorId]);
            } else {
                $this->deleteVectors(null, null, ['vector_id' => $vectorId]);
            }

            // Chunk the content
            $chunks = $this->chunkText($content, $maxChunkSize, $overlapPercentage);
            
            // Create embeddings in batches to respect OpenAI token limits
            $embeddings = $this->createEmbeddingsInBatches($chunks);
            
            // Prepare vectors for Pinecone
            $vectors = [];
            foreach ($chunks as $index => $chunk) {
                $chunkMetadata = array_merge($metadata, [
                    'vector_id' => $vectorId,
                    'chunk_index' => $index,
                    'content' => $chunk,
                    'created_at' => now()->toISOString()
                ]);

                $vector = [
                    'id' => $vectorId . '_chunk_' . $index,
                    'values' => $embeddings[$index],
                    'metadata' => $chunkMetadata
                ];

                if ($namespace) {
                    $vector['namespace'] = $namespace;
                }

                $vectors[] = $vector;
            }

            // Store in Pinecone in batches to respect size limits
            return $this->upsertVectorsInBatches($vectors, $namespace);

        } catch (Exception $e) {
            Log::error('Failed to process and store content', [
                'error' => $e->getMessage(),
                'vector_id' => $vectorId,
                'content_length' => strlen($content)
            ]);
            return false;
        }
    }

    /**
     * Get embedding dimensions
     */
    public function getDimensions(): int
    {
        return $this->dimensions;
    }

    /**
     * Get embedding model name
     */
    public function getModel(): string
    {
        return $this->embeddingModel;
    }

    /**
     * Upsert vectors in batches to respect Pinecone message size limits (4MB)
     */
    public function upsertVectorsInBatches(array $vectors, string $namespace = null, int $batchSize = 100): bool
    {
        try {
            $totalBatches = ceil(count($vectors) / $batchSize);
            $successfulBatches = 0;
            
            for ($i = 0; $i < count($vectors); $i += $batchSize) {
                $batch = array_slice($vectors, $i, $batchSize);
                $batchNumber = floor($i / $batchSize) + 1;
                
                Log::info("Processing batch {$batchNumber}/{$totalBatches}", [
                    'vectors_in_batch' => count($batch)
                ]);
                
                $success = $this->upsertVectors($batch, $namespace);
                
                if ($success) {
                    $successfulBatches++;
                } else {
                    Log::error("Failed to process batch {$batchNumber}");
                }
                
                // Small delay between batches
                if ($i + $batchSize < count($vectors)) {
                    sleep(1);
                }
            }
            
            Log::info("Batch processing completed", [
                'successful_batches' => $successfulBatches,
                'total_batches' => $totalBatches,
                'total_vectors' => count($vectors)
            ]);
            
            return $successfulBatches === $totalBatches;
            
        } catch (Exception $e) {
            Log::error('Failed to upsert vectors in batches', [
                'error' => $e->getMessage(),
                'vectors_count' => count($vectors)
            ]);
            throw $e;
        }
    }
}