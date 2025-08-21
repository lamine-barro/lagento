<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AgentCacheService
{
    /**
     * Préfixes de cache par type
     */
    private const CACHE_PREFIXES = [
        'response' => 'agent:response:',
        'context' => 'agent:context:',
        'search' => 'agent:search:',
        'embedding' => 'agent:embedding:',
        'analytics' => 'agent:analytics:',
    ];
    
    /**
     * Durées de cache par défaut (en secondes)
     */
    private const DEFAULT_TTL = [
        'simple_facts' => 3600,      // 1 heure
        'calculations' => 86400,     // 24 heures
        'user_context' => 300,       // 5 minutes
        'analytics' => 600,          // 10 minutes
        'search_results' => 1800,    // 30 minutes
        'embeddings' => 7200,        // 2 heures
    ];
    
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0
    ];

    /**
     * Récupère une réponse mise en cache
     */
    public function getCachedResponse(string $message, string $userId, string $type = 'general'): ?array
    {
        $cacheKey = $this->generateResponseKey($message, $userId, $type);
        
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached !== null) {
                $this->stats['hits']++;
                
                Log::debug('Cache HIT', [
                    'key' => $cacheKey,
                    'type' => $type,
                    'size' => strlen(json_encode($cached))
                ]);
                
                // Ajouter un indicateur que c'est du cache
                if (is_array($cached)) {
                    $cached['from_cache'] = true;
                    $cached['cache_key'] = $cacheKey;
                    $cached['cached_at'] = $cached['cached_at'] ?? now()->toISOString();
                }
                
                return $cached;
            }
            
            $this->stats['misses']++;
            
        } catch (\Exception $e) {
            Log::error('Cache read error', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
    
    /**
     * Met en cache une réponse
     */
    public function cacheResponse(
        string $message, 
        string $userId, 
        array $response, 
        string $type = 'general',
        ?int $ttl = null
    ): bool {
        // Ne pas cacher les erreurs
        if (!($response['success'] ?? false)) {
            return false;
        }
        
        $cacheKey = $this->generateResponseKey($message, $userId, $type);
        $ttl = $ttl ?? $this->getTTLForType($type);
        
        // Ajouter metadata de cache
        $response['cached_at'] = now()->toISOString();
        $response['cache_ttl'] = $ttl;
        
        try {
            Cache::put($cacheKey, $response, $ttl);
            $this->stats['writes']++;
            
            Log::debug('Cache WRITE', [
                'key' => $cacheKey,
                'type' => $type,
                'ttl' => $ttl,
                'size' => strlen(json_encode($response))
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Cache write error', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Cache pour le contexte utilisateur
     */
    public function getUserContext(string $userId): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['context'] . $userId;
        
        return Cache::remember($cacheKey, 300, function() use ($userId) {
            return null; // Sera rempli par le caller
        });
    }
    
    public function setUserContext(string $userId, array $context, int $ttl = 300): void
    {
        $cacheKey = self::CACHE_PREFIXES['context'] . $userId;
        Cache::put($cacheKey, $context, $ttl);
    }
    
    /**
     * Cache pour les résultats de recherche vectorielle
     */
    public function getSearchResults(string $query, string $namespace = 'default'): ?array
    {
        $cacheKey = $this->generateSearchKey($query, $namespace);
        
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            $this->stats['hits']++;
            Log::debug('Search cache HIT', ['query' => substr($query, 0, 50)]);
        } else {
            $this->stats['misses']++;
        }
        
        return $cached;
    }
    
    public function cacheSearchResults(
        string $query, 
        array $results, 
        string $namespace = 'default',
        int $ttl = 1800
    ): void {
        $cacheKey = $this->generateSearchKey($query, $namespace);
        
        Cache::put($cacheKey, $results, $ttl);
        $this->stats['writes']++;
        
        Log::debug('Search cache WRITE', [
            'query' => substr($query, 0, 50),
            'results_count' => count($results),
            'ttl' => $ttl
        ]);
    }
    
    /**
     * Cache pour les embeddings
     */
    public function getEmbedding(string $text): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['embedding'] . md5($text);
        
        return Cache::get($cacheKey);
    }
    
    public function cacheEmbedding(string $text, array $embedding, int $ttl = 7200): void
    {
        $cacheKey = self::CACHE_PREFIXES['embedding'] . md5($text);
        
        Cache::put($cacheKey, $embedding, $ttl);
    }
    
    /**
     * Cache pour les analytics pré-calculées
     */
    public function getAnalytics(string $userId, string $metric): ?array
    {
        $cacheKey = self::CACHE_PREFIXES['analytics'] . "{$userId}:{$metric}";
        
        return Cache::get($cacheKey);
    }
    
    public function cacheAnalytics(string $userId, string $metric, array $data, int $ttl = 600): void
    {
        $cacheKey = self::CACHE_PREFIXES['analytics'] . "{$userId}:{$metric}";
        
        Cache::put($cacheKey, $data, $ttl);
    }
    
    /**
     * Invalidation du cache
     */
    public function invalidateUserCache(string $userId): void
    {
        // Invalider le contexte utilisateur
        Cache::forget(self::CACHE_PREFIXES['context'] . $userId);
        
        // Invalider les analytics
        $pattern = self::CACHE_PREFIXES['analytics'] . $userId . ':*';
        $this->forgetPattern($pattern);
        
        Log::info('User cache invalidated', ['user_id' => $userId]);
    }
    
    public function invalidateSearchCache(string $namespace = 'default'): void
    {
        $pattern = self::CACHE_PREFIXES['search'] . $namespace . ':*';
        $this->forgetPattern($pattern);
        
        Log::info('Search cache invalidated', ['namespace' => $namespace]);
    }
    
    /**
     * Warmup du cache (pré-chargement)
     */
    public function warmupCache(string $userId): void
    {
        Log::info('Cache warmup started', ['user_id' => $userId]);
        
        // Pré-charger les questions fréquentes
        $frequentQuestions = $this->getFrequentQuestions($userId);
        
        foreach ($frequentQuestions as $question) {
            // Le service principal s'occupera de remplir le cache
            // lors du prochain appel
        }
        
        Log::info('Cache warmup completed', [
            'user_id' => $userId,
            'questions_warmed' => count($frequentQuestions)
        ]);
    }
    
    /**
     * Statistiques du cache
     */
    public function getStats(): array
    {
        $hitRate = ($this->stats['hits'] + $this->stats['misses']) > 0
            ? ($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses'])) * 100
            : 0;
        
        return [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'writes' => $this->stats['writes'],
            'hit_rate' => round($hitRate, 2) . '%',
            'memory_usage' => $this->getCacheMemoryUsage()
        ];
    }
    
    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'writes' => 0
        ];
    }
    
    /**
     * Helpers privés
     */
    private function generateResponseKey(string $message, string $userId, string $type): string
    {
        $normalizedMessage = $this->normalizeMessage($message);
        $hash = substr(md5($normalizedMessage . $userId . $type), 0, 16);
        
        return self::CACHE_PREFIXES['response'] . "{$type}:{$hash}";
    }
    
    private function generateSearchKey(string $query, string $namespace): string
    {
        $normalizedQuery = $this->normalizeMessage($query);
        $hash = substr(md5($normalizedQuery), 0, 16);
        
        return self::CACHE_PREFIXES['search'] . "{$namespace}:{$hash}";
    }
    
    private function normalizeMessage(string $message): string
    {
        // Normaliser pour améliorer le hit rate du cache
        $normalized = mb_strtolower(trim($message));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = preg_replace('/[^\w\s]/u', '', $normalized);
        
        return $normalized;
    }
    
    private function getTTLForType(string $type): int
    {
        return self::DEFAULT_TTL[$type] ?? 600;
    }
    
    private function forgetPattern(string $pattern): void
    {
        // Pour Redis
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        // Pour d'autres stores, implémenter selon le besoin
    }
    
    private function getCacheMemoryUsage(): string
    {
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            try {
                $info = Redis::info('memory');
                if (preg_match('/used_memory_human:(.+)/', $info, $matches)) {
                    return trim($matches[1]);
                }
            } catch (\Exception $e) {
                // Ignorer
            }
        }
        
        return 'N/A';
    }
    
    private function getFrequentQuestions(string $userId): array
    {
        // Questions fréquentes à pré-charger
        return [
            'Quels sont mes projets en cours ?',
            'Résume mon activité récente',
            'Quelles sont mes tâches prioritaires ?',
            'Montre mes prochains rendez-vous',
        ];
    }
    
    /**
     * Cache avec compression pour grandes données
     */
    public function cacheCompressed(string $key, $data, int $ttl = 3600): bool
    {
        try {
            $serialized = serialize($data);
            
            // Compresser si > 1KB
            if (strlen($serialized) > 1024) {
                $compressed = gzcompress($serialized, 9);
                $payload = [
                    'compressed' => true,
                    'data' => base64_encode($compressed),
                    'original_size' => strlen($serialized),
                    'compressed_size' => strlen($compressed)
                ];
            } else {
                $payload = [
                    'compressed' => false,
                    'data' => $data
                ];
            }
            
            return Cache::put($key, $payload, $ttl);
            
        } catch (\Exception $e) {
            Log::error('Compression cache error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    public function getCompressed(string $key)
    {
        $payload = Cache::get($key);
        
        if (!$payload) {
            return null;
        }
        
        try {
            if ($payload['compressed'] ?? false) {
                $compressed = base64_decode($payload['data']);
                $serialized = gzuncompress($compressed);
                return unserialize($serialized);
            } else {
                return $payload['data'];
            }
        } catch (\Exception $e) {
            Log::error('Decompression cache error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
}