<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiagnosticCacheService
{
    private const CACHE_PREFIX = 'diagnostic_cache:';
    private const TTL_PROFILE_SUMMARY = 3600; // 1 heure
    private const TTL_DASHBOARD = 1800; // 30 minutes  
    private const TTL_INSIGHTS = 900; // 15 minutes
    private const TTL_VECTOR_SEARCH = 7200; // 2 heures

    public function getCachedProfileSummary(User $user, array $data): ?array
    {
        $key = $this->generateProfileKey($user, $data);
        $cached = Cache::get($key);
        
        if ($cached) {
            Log::info("DiagnosticCache: Profile summary cache HIT", ['user_id' => $user->id]);
            return $cached;
        }
        
        Log::info("DiagnosticCache: Profile summary cache MISS", ['user_id' => $user->id]);
        return null;
    }

    public function cacheProfileSummary(User $user, array $data, array $summary): void
    {
        $key = $this->generateProfileKey($user, $data);
        Cache::put($key, $summary, self::TTL_PROFILE_SUMMARY);
        Log::info("DiagnosticCache: Profile summary cached", ['user_id' => $user->id, 'key' => $key]);
    }

    public function getCachedDashboard(User $user): ?array
    {
        $key = self::CACHE_PREFIX . "dashboard:{$user->id}";
        $cached = Cache::get($key);
        
        if ($cached) {
            // Vérifier si les données ne sont pas trop anciennes
            if (isset($cached['generated_at']) && 
                strtotime($cached['generated_at']) > strtotime('-1 hour')) {
                Log::info("DiagnosticCache: Dashboard cache HIT", ['user_id' => $user->id]);
                return $cached;
            }
        }
        
        Log::info("DiagnosticCache: Dashboard cache MISS", ['user_id' => $user->id]);
        return null;
    }

    public function cacheDashboard(User $user, array $dashboard): void
    {
        $key = self::CACHE_PREFIX . "dashboard:{$user->id}";
        $dashboard['generated_at'] = now()->toISOString();
        Cache::put($key, $dashboard, self::TTL_DASHBOARD);
        Log::info("DiagnosticCache: Dashboard cached", ['user_id' => $user->id]);
    }

    public function getCachedInsights(User $user): ?array
    {
        $key = self::CACHE_PREFIX . "insights:{$user->id}";
        $cached = Cache::get($key);
        
        if ($cached) {
            Log::info("DiagnosticCache: Insights cache HIT", ['user_id' => $user->id]);
            return $cached;
        }
        
        Log::info("DiagnosticCache: Insights cache MISS", ['user_id' => $user->id]);
        return null;
    }

    public function cacheInsights(User $user, array $insights): void
    {
        $key = self::CACHE_PREFIX . "insights:{$user->id}";
        Cache::put($key, $insights, self::TTL_INSIGHTS);
        Log::info("DiagnosticCache: Insights cached", ['user_id' => $user->id]);
    }

    public function getCachedVectorSearch(User $user, string $query): ?array
    {
        $key = $this->generateVectorKey($user, $query);
        $cached = Cache::get($key);
        
        if ($cached) {
            Log::info("DiagnosticCache: Vector search cache HIT", ['user_id' => $user->id, 'query_hash' => substr(md5($query), 0, 8)]);
            return $cached;
        }
        
        Log::info("DiagnosticCache: Vector search cache MISS", ['user_id' => $user->id, 'query_hash' => substr(md5($query), 0, 8)]);
        return null;
    }

    public function cacheVectorSearch(User $user, string $query, array $results): void
    {
        $key = $this->generateVectorKey($user, $query);
        Cache::put($key, $results, self::TTL_VECTOR_SEARCH);
        Log::info("DiagnosticCache: Vector search cached", [
            'user_id' => $user->id, 
            'query_hash' => substr(md5($query), 0, 8),
            'results_count' => count($results)
        ]);
    }

    public function invalidateUserCache(User $user): void
    {
        $keys = [
            self::CACHE_PREFIX . "dashboard:{$user->id}",
            self::CACHE_PREFIX . "insights:{$user->id}"
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
            Log::info("DiagnosticCache: Cache invalidated", ['user_id' => $user->id, 'key' => $key]);
        }
    }

    private function generateProfileKey(User $user, array $data): string
    {
        // Créer une clé basée sur le hash des données importantes
        $relevantData = [
            'business_name' => $data['business_name'] ?? '',
            'business_sector' => $data['business_sector'] ?? '',
            'business_stage' => $data['business_stage'] ?? '',
            'description' => $data['description'] ?? '',
            'target_market' => $data['target_market'] ?? [],
            'revenue_model' => $data['revenue_model'] ?? []
        ];
        
        $hash = md5(json_encode($relevantData));
        return self::CACHE_PREFIX . "profile:{$hash}:{$user->id}";
    }

    private function generateVectorKey(User $user, string $query): string
    {
        $hash = md5($query);
        return self::CACHE_PREFIX . "vector:{$hash}:{$user->id}";
    }

    public function getCacheStats(): array
    {
        // Cache façade doesn't provide key enumeration, return basic stats
        return [
            'cache_driver' => config('cache.default'),
            'prefix' => self::CACHE_PREFIX,
            'ttl_profile' => self::TTL_PROFILE_SUMMARY,
            'ttl_dashboard' => self::TTL_DASHBOARD,
            'ttl_insights' => self::TTL_INSIGHTS,
            'ttl_vector' => self::TTL_VECTOR_SEARCH
        ];
    }
}