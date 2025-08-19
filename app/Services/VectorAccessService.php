<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VectorAccessService
{
    private VoyageVectorService $voyageService;

    public function __construct(VoyageVectorService $voyageService)
    {
        $this->voyageService = $voyageService;
    }

    /**
     * Get accessible memory types based on user access level
     */
    public function getAccessibleMemoryTypes(User $user): array
    {
        $accessLevel = $this->determineAccessLevel($user);
        
        return match($accessLevel) {
            'admin' => $this->getAdminMemoryTypes(),
            'premium' => $this->getPremiumMemoryTypes(),
            'standard' => $this->getStandardMemoryTypes(),
            'basic' => $this->getBasicMemoryTypes(),
            default => $this->getBasicMemoryTypes()
        };
    }

    /**
     * Perform vector search with access control
     */
    public function searchWithAccess(
        string $query,
        User $user,
        array $requestedTypes = [],
        int $limit = 10
    ): array {
        $accessibleTypes = $this->getAccessibleMemoryTypes($user);
        
        // Filter requested types by accessible types
        $allowedTypes = empty($requestedTypes) 
            ? $accessibleTypes 
            : array_intersect($requestedTypes, $accessibleTypes);

        if (empty($allowedTypes)) {
            Log::info('Vector search blocked - no accessible types', [
                'user_id' => $user->id,
                'requested_types' => $requestedTypes,
                'accessible_types' => $accessibleTypes
            ]);
            return [];
        }

        // Add user context for user-specific memories
        $userId = in_array('user_project', $allowedTypes) || in_array('user_analytics', $allowedTypes) 
            ? $user->id 
            : null;

        $results = $this->voyageService->semanticSearch(
            $query,
            $allowedTypes,
            $userId ? ['user_id' => $userId] : [],
            $limit
        );

        Log::info('Vector search performed', [
            'user_id' => $user->id,
            'query_length' => strlen($query),
            'types_searched' => $allowedTypes,
            'results_count' => count($results)
        ]);

        return $this->filterResultsByAccess($results, $user);
    }

    /**
     * Determine user access level
     */
    private function determineAccessLevel(User $user): string
    {
        // Admin users (can be configured via database flag or specific emails)
        if ($user->email === 'lamine.barro@etudesk.org' || $user->is_admin === true) {
            return 'admin';
        }

        // Premium users (paid subscription, enterprise accounts)
        if ($user->subscription_type === 'premium' || $user->account_type === 'enterprise') {
            return 'premium';
        }

        // Standard users (basic paid or verified accounts)
        if ($user->subscription_type === 'basic' || $user->email_verified_at !== null) {
            return 'standard';
        }

        // Basic users (free, unverified)
        return 'basic';
    }

    /**
     * Admin access - all memory types
     */
    private function getAdminMemoryTypes(): array
    {
        return [
            'lagento_context',
            'opportunite',
            'texte_officiel', 
            'institution',
            'user_project',
            'user_analytics',
            'timeline_gov',
            'presentation',
            'faq',
            'conversation',
            'document'
        ];
    }

    /**
     * Premium access - most memory types except admin-specific
     */
    private function getPremiumMemoryTypes(): array
    {
        return [
            'lagento_context',
            'opportunite',
            'texte_officiel',
            'institution', 
            'user_project',
            'user_analytics',
            'timeline_gov',
            'presentation',
            'faq'
        ];
    }

    /**
     * Standard access - public content + user's own data
     */
    private function getStandardMemoryTypes(): array
    {
        return [
            'lagento_context',
            'opportunite',
            'institution',
            'user_project',
            'user_analytics', 
            'timeline_gov',
            'presentation',
            'faq'
        ];
    }

    /**
     * Basic access - only public institutional content
     */
    private function getBasicMemoryTypes(): array
    {
        return [
            'lagento_context',
            'opportunite',
            'institution',
            'timeline_gov',
            'presentation',
            'faq'
        ];
    }

    /**
     * Filter search results based on user access
     */
    private function filterResultsByAccess(array $results, User $user): array
    {
        return array_filter($results, function($result) use ($user) {
            // For user-specific content, ensure user can only see their own
            if (in_array($result['memory_type'], ['user_project', 'user_analytics'])) {
                // Handle both string JSON and array metadata
                $metadata = is_string($result['metadata']) 
                    ? json_decode($result['metadata'], true) 
                    : $result['metadata'];
                return isset($metadata['user_id']) && $metadata['user_id'] == $user->id;
            }

            // For textes officiels, premium+ users get full access, others get limited
            if ($result['memory_type'] === 'texte_officiel') {
                $accessLevel = $this->determineAccessLevel($user);
                if (!in_array($accessLevel, ['admin', 'premium'])) {
                    // Basic users might see limited official texts or none
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Get available memory types summary for user
     */
    public function getAccessSummary(User $user): array
    {
        $accessLevel = $this->determineAccessLevel($user);
        $accessibleTypes = $this->getAccessibleMemoryTypes($user);
        
        // Count available vectors by type
        $counts = [];
        foreach ($accessibleTypes as $type) {
            $count = DB::table('vector_memories')
                ->where('memory_type', $type)
                ->count();
            $counts[$type] = $count;
        }

        return [
            'access_level' => $accessLevel,
            'accessible_types' => $accessibleTypes,
            'memory_counts' => $counts,
            'total_accessible_chunks' => array_sum($counts)
        ];
    }

    /**
     * Check if user has access to specific memory type
     */
    public function hasAccessToType(User $user, string $memoryType): bool
    {
        $accessibleTypes = $this->getAccessibleMemoryTypes($user);
        return in_array($memoryType, $accessibleTypes);
    }

    /**
     * Get contextualized search suggestions based on user access
     */
    public function getSearchSuggestions(User $user): array
    {
        $accessLevel = $this->determineAccessLevel($user);
        
        return match($accessLevel) {
            'admin' => [
                'Rechercher toutes les opportunités de financement',
                'Analyser les textes juridiques OHADA',
                'Explorer les diagnostics entrepreneuriaux',
                'Consulter les actions gouvernementales',
                'Rechercher dans les données utilisateurs'
            ],
            'premium' => [
                'Rechercher des opportunités personnalisées',
                'Consulter les textes officiels',
                'Analyser mon profil entrepreneurial',
                'Explorer l\'écosystème institutionnel'
            ],
            'standard' => [
                'Trouver des opportunités de financement',
                'Consulter les institutions d\'appui',
                'Analyser mon projet entrepreneurial',
                'Découvrir les actions gouvernementales'
            ],
            'basic' => [
                'Rechercher des opportunités publiques',
                'Explorer les institutions disponibles',
                'Comprendre l\'écosystème entrepreneurial'
            ]
        };
    }
}