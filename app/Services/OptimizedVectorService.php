<?php

namespace App\Services;

use App\Services\OpenAIVectorService;
use App\Services\AgentCacheService;
use Illuminate\Support\Facades\Log;

class OptimizedVectorService
{
    private OpenAIVectorService $embedding;
    private AgentCacheService $cache;
    
    /**
     * Namespaces spécialisés pour différents types de données
     */
    private const NAMESPACES = [
        'knowledge_base' => 'knowledge',      // Faits généraux, Q&A simples
        'user_projects' => 'projects',        // Projets utilisateur
        'user_analytics' => 'analytics',      // Métriques et analytics
        'user_documents' => 'documents',      // Documents uploadés
        'conversations' => 'conversations',   // Historique conversations
        'opportunities' => 'opportunites',    // Opportunités commerciales
        'lagento_context' => 'lagento_context', // Contexte LagentO
        'technical_docs' => 'technical'       // Documentation technique
    ];
    
    /**
     * Configuration de recherche optimisée par type
     */
    private const SEARCH_CONFIG = [
        'simple_facts' => [
            'namespaces' => ['knowledge_base'],
            'topK' => 3,
            'threshold' => 0.8
        ],
        'strategic_business' => [
            'namespaces' => ['opportunities', 'lagento_context', 'user_projects'],
            'topK' => 3,
            'threshold' => 0.8
        ],
        'user_context' => [
            'namespaces' => ['user_projects', 'user_analytics', 'lagento_context'],
            'topK' => 3, // Réduit de 5 à 3
            'threshold' => 0.75 // Augmenté de 0.7 à 0.75
        ],
        'analytics' => [
            'namespaces' => ['user_analytics', 'user_projects'],
            'topK' => 5, // Réduit de 10 à 5
            'threshold' => 0.8 // Augmenté de 0.75 à 0.8
        ],
        'technical' => [
            'namespaces' => ['technical_docs', 'lagento_context'],
            'topK' => 5,
            'threshold' => 0.7
        ],
        'comprehensive' => [
            'namespaces' => ['lagento_context', 'user_projects', 'user_analytics', 'opportunities'],
            'topK' => 10,
            'threshold' => 0.65
        ]
    ];
    
    public function __construct(
        OpenAIVectorService $embedding,
        AgentCacheService $cache
    ) {
        $this->embedding = $embedding;
        $this->cache = $cache;
    }
    
    /**
     * Recherche optimisée basée sur le type de question
     */
    public function searchOptimized(
        string $query,
        string $type,
        array $filters = [],
        ?string $userId = null
    ): array {
        $startTime = microtime(true);
        
        // Get search configuration
        $config = self::SEARCH_CONFIG[$type] ?? self::SEARCH_CONFIG['comprehensive'];
        
        // Check cache first
        $cacheKey = $this->generateCacheKey($query, $type, $filters, $userId);
        $cached = $this->cache->getSearchResults($query, $cacheKey);
        
        if ($cached !== null) {
            Log::debug('Optimized search cache HIT', [
                'type' => $type,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            return $cached;
        }
        
        // Get or cache embedding
        $queryEmbedding = $this->getOrCacheEmbedding($query);
        
        // Perform parallel searches across namespaces
        $results = $this->parallelSearch(
            $queryEmbedding,
            $config['namespaces'],
            $config['topK'],
            $filters,
            $userId
        );
        
        // Filter by threshold
        $filteredResults = $this->filterByThreshold($results, $config['threshold']);
        
        // Deduplicate and rank
        $rankedResults = $this->rankResults($filteredResults, $query);
        
        // Cache results
        $this->cache->cacheSearchResults(
            $query,
            $rankedResults,
            $cacheKey,
            $this->getCacheTTL($type)
        );
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        Log::info('Optimized vector search completed', [
            'type' => $type,
            'namespaces' => $config['namespaces'],
            'results_count' => count($rankedResults),
            'duration_ms' => round($duration, 2)
        ]);
        
        return $rankedResults;
    }
    
    /**
     * Batch embedding avec cache
     */
    public function batchEmbed(array $texts): array
    {
        $embeddings = [];
        $toEmbed = [];
        
        // Check cache for each text
        foreach ($texts as $index => $text) {
            $cached = $this->cache->getEmbedding($text);
            if ($cached !== null) {
                $embeddings[$index] = $cached;
            } else {
                $toEmbed[$index] = $text;
            }
        }
        
        // Batch embed uncached texts
        if (!empty($toEmbed)) {
            Log::debug('Batch embedding', [
                'cached_count' => count($embeddings),
                'to_embed_count' => count($toEmbed)
            ]);
            
            try {
                // OpenAI supports up to 2048 embeddings per batch
                $chunks = array_chunk($toEmbed, 100, true);
                
                foreach ($chunks as $chunk) {
                    $response = $this->embedding->embedBatch(array_values($chunk));
                    
                    foreach ($chunk as $index => $text) {
                        if (isset($response[$index])) {
                            $embeddings[$index] = $response[$index];
                            // Cache the embedding
                            $this->cache->cacheEmbedding($text, $response[$index]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Batch embedding error', [
                    'error' => $e->getMessage(),
                    'texts_count' => count($toEmbed)
                ]);
                
                // Fallback to individual embeddings
                foreach ($toEmbed as $index => $text) {
                    $embeddings[$index] = $this->getOrCacheEmbedding($text);
                }
            }
        }
        
        // Sort by original index
        ksort($embeddings);
        
        return array_values($embeddings);
    }
    
    /**
     * Upsert optimisé avec namespace spécifique
     */
    public function upsertToNamespace(
        string $namespace,
        array $vectors,
        array $metadata = []
    ): bool {
        try {
            $mappedNamespace = self::NAMESPACES[$namespace] ?? $namespace;
            
            // Batch upsert for better performance
            $chunks = array_chunk($vectors, 100);
            
            foreach ($chunks as $chunk) {
                $this->embedding->upsert(
                    vectors: $chunk,
                    namespace: $mappedNamespace,
                    metadata: $metadata
                );
            }
            
            // Invalidate search cache for this namespace
            $this->cache->invalidateSearchCache($mappedNamespace);
            
            Log::info('Vectors upserted to namespace', [
                'namespace' => $mappedNamespace,
                'vectors_count' => count($vectors)
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Upsert error', [
                'namespace' => $namespace,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Private helper methods
     */
    private function getOrCacheEmbedding(string $text): array
    {
        $cached = $this->cache->getEmbedding($text);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $embedding = $this->embedding->embed($text);
        $this->cache->cacheEmbedding($text, $embedding);
        
        return $embedding;
    }
    
    private function parallelSearch(
        array $queryEmbedding,
        array $namespaces,
        int $topK,
        array $filters,
        ?string $userId
    ): array {
        $allResults = [];
        
        // Add user filter if provided
        if ($userId) {
            $filters['user_id'] = $userId;
        }
        
        foreach ($namespaces as $namespace) {
            $mappedNamespace = self::NAMESPACES[$namespace] ?? $namespace;
            
            try {
                $results = $this->embedding->searchSimilar(
                    query: '', // We'll use queryVector instead
                    topK: $topK,
                    filter: $filters,
                    namespace: $mappedNamespace,
                    queryVector: $queryEmbedding
                );
                
                // Add namespace info to results
                foreach ($results as &$result) {
                    $result['namespace'] = $namespace;
                }
                
                $allResults = array_merge($allResults, $results);
                
            } catch (\Exception $e) {
                Log::warning('Search failed for namespace', [
                    'namespace' => $mappedNamespace,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $allResults;
    }
    
    private function filterByThreshold(array $results, float $threshold): array
    {
        return array_filter($results, function($result) use ($threshold) {
            return ($result['score'] ?? 0) >= $threshold;
        });
    }
    
    private function rankResults(array $results, string $query): array
    {
        // Sort by score descending
        usort($results, function($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });
        
        // Deduplicate by content hash
        $seen = [];
        $unique = [];
        
        foreach ($results as $result) {
            $contentHash = md5($result['metadata']['content'] ?? '');
            
            if (!isset($seen[$contentHash])) {
                $seen[$contentHash] = true;
                $unique[] = $result;
            }
        }
        
        // Limit to top results
        return array_slice($unique, 0, 20);
    }
    
    private function generateCacheKey(
        string $query,
        string $type,
        array $filters,
        ?string $userId
    ): string {
        $key = $type;
        
        if ($userId) {
            $key .= '_user_' . $userId;
        }
        
        if (!empty($filters)) {
            $key .= '_' . md5(json_encode($filters));
        }
        
        return $key;
    }
    
    private function getCacheTTL(string $type): int
    {
        $ttls = [
            'simple_facts' => 3600,    // 1 hour
            'user_context' => 300,     // 5 minutes
            'analytics' => 600,        // 10 minutes
            'technical' => 1800,       // 30 minutes
            'comprehensive' => 900     // 15 minutes
        ];
        
        return $ttls[$type] ?? 600;
    }
    
    /**
     * Précharge les embeddings fréquents
     */
    public function warmupEmbeddings(array $frequentQueries): void
    {
        Log::info('Warming up embeddings cache', [
            'queries_count' => count($frequentQueries)
        ]);
        
        $this->batchEmbed($frequentQueries);
    }
    
    /**
     * Statistiques d'utilisation
     */
    public function getStats(): array
    {
        return [
            'cache_stats' => $this->cache->getStats(),
            'namespaces' => array_keys(self::NAMESPACES),
            'search_types' => array_keys(self::SEARCH_CONFIG)
        ];
    }
}