<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmartToolRouter
{
    /**
     * Patterns pour identifier rapidement le type de question
     */
    private array $patterns = [
        'simple_facts' => [
            'patterns' => [
                '/^(quelle?|quel|qu\'est-ce que?|what|who|when|where|comment s\'appelle)/i',
                '/capitale|pays|ville|président|date|année/i',
                '/défini(tion|r)|signifie|veut dire/i',
            ],
            'examples' => ['Quelle est la capitale de la France?', 'Qui est le président?'],
            'needs_search' => false,
            'cache_duration' => 3600, // 1 hour
        ],
        
        'calculations' => [
            'patterns' => [
                '/calcul(e|er)?|combien|addition|soustraction|multiplication|division/i',
                '/\d+\s*[\+\-\*\/\%]\s*\d+/i',
                '/somme|produit|différence|quotient|reste/i',
            ],
            'examples' => ['Calcule 25 * 17', 'Combien font 100 + 250?'],
            'needs_search' => false,
            'cache_duration' => 86400, // 24 hours
        ],
        
        'user_context' => [
            'patterns' => [
                '/mes?\s+(projet|tâche|activité|rendez-vous|meeting|document)/i',
                '/mon\s+(entreprise|business|startup|équipe|collaborateur)/i',
                '/ma\s+(société|boîte|organisation|structure)/i',
                '/je\s+(dois|vais|ai|suis)/i',
            ],
            'examples' => ['Mes projets en cours', 'Mon activité récente'],
            'needs_search' => true,
            'search_type' => 'user_specific',
            'cache_duration' => 300, // 5 minutes
        ],
        
        'analytics' => [
            'patterns' => [
                '/analys(e|er)|rapport|synthèse|résumé|bilan/i',
                '/statisti(que|cs)|métrique|indicateur|KPI|performance/i',
                '/évolution|progression|tendance|croissance/i',
                '/opportunité|recommandation|suggestion|conseil/i',
            ],
            'examples' => ['Analyse mes opportunités', 'Rapport sur mes ventes'],
            'needs_search' => true,
            'search_type' => 'analytics',
            'cache_duration' => 600, // 10 minutes
        ],
        
        'strategic_business' => [
            'patterns' => [
                '/financement|investisseur|capital|levée|fonds/i',
                '/opportunité.*(financement|investissement|business)/i',
                '/meilleures?.*(opportunité|stratégie|solution)/i',
                '/pour\s+(etudesk|ci20|mon entreprise|ma startup)/i',
            ],
            'examples' => ['Financement pour Etudesk', 'Opportunités business'],
            'needs_search' => true,
            'search_type' => 'comprehensive',
            'cache_duration' => 600, // 10 minutes
        ],
        
        'complex_generation' => [
            'patterns' => [
                '/génère?|crée?|rédige|écri(s|t)|prépare|élabore/i',
                '/plan\s+d\'action|stratégie|roadmap|planning/i',
                '/email|lettre|courrier|message|communication/i',
                '/présentation|slide|pitch|document/i',
            ],
            'examples' => ['Génère un rapport', 'Crée un plan d\'action'],
            'needs_search' => true,
            'search_type' => 'comprehensive',
            'cache_duration' => 0, // No cache for generation
        ],
        
        'technical' => [
            'patterns' => [
                '/code|programme|script|fonction|classe|méthode/i',
                '/bug|erreur|problème|issue|fix|debug/i',
                '/optimi(ser|sation)|performance|améliorer|accélérer/i',
                '/architecture|design|pattern|structure|framework/i',
            ],
            'examples' => ['Optimise ce code', 'Debug cette erreur'],
            'needs_search' => true,
            'search_type' => 'technical',
            'cache_duration' => 1800, // 30 minutes
        ],
    ];
    
    /**
     * Keywords indiquant qu'une recherche approfondie est nécessaire
     */
    private array $deepSearchKeywords = [
        'détaillé', 'complet', 'approfondi', 'exhaustif', 'précis',
        'tous', 'toutes', 'liste', 'ensemble', 'intégralité'
    ];
    
    /**
     * Keywords indiquant une réponse rapide suffisante
     */
    private array $quickResponseKeywords = [
        'rapidement', 'vite', 'simple', 'bref', 'court',
        'juste', 'seulement', 'uniquement'
    ];

    /**
     * Analyse un message et détermine la meilleure route
     */
    public function route(string $message): array
    {
        $startTime = microtime(true);
        $messageLower = mb_strtolower($message);
        
        // Log pour debug
        Log::debug('SmartToolRouter analyzing message', [
            'message_length' => strlen($message),
            'first_50_chars' => substr($message, 0, 50)
        ]);
        
        // Détection du type de question
        $questionType = $this->detectQuestionType($message, $messageLower);
        
        // Détection de la profondeur requise
        $depth = $this->detectRequiredDepth($messageLower);
        
        // Détection des entités mentionnées
        $entities = $this->extractEntities($message);
        
        // Construction de la route optimale
        $route = $this->buildRoute($questionType, $depth, $entities, $message);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        Log::info('SmartToolRouter decision', [
            'type' => $questionType,
            'depth' => $depth,
            'needs_search' => $route['needs_search'],
            'tools' => $route['tools'],
            'duration_ms' => round($duration, 2)
        ]);
        
        return $route;
    }
    
    /**
     * Détecte le type de question
     */
    private function detectQuestionType(string $message, string $messageLower): string
    {
        $scores = [];
        
        foreach ($this->patterns as $type => $config) {
            $score = 0;
            
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $message)) {
                    $score += 10;
                }
            }
            
            // Bonus pour mots-clés spécifiques
            if ($type === 'user_context' && preg_match('/\b(je|mon|ma|mes)\b/i', $message)) {
                $score += 5;
            }
            
            // Bonus pour questions business stratégiques
            if (preg_match('/financement|investisseur|capital|opportunité.*business/i', $message)) {
                if ($type === 'strategic_business') {
                    $score += 15; // Priorité élevée
                } elseif ($type === 'simple_facts') {
                    $score -= 10; // Pénalité pour éviter classification simple
                }
            }
            
            $scores[$type] = $score;
        }
        
        // Retourner le type avec le score le plus élevé
        arsort($scores);
        $bestType = array_key_first($scores);
        
        // Si aucun pattern ne match, considérer comme complexe
        if ($scores[$bestType] === 0) {
            return 'complex_generation';
        }
        
        return $bestType;
    }
    
    /**
     * Détecte la profondeur de réponse requise
     */
    private function detectRequiredDepth(string $messageLower): string
    {
        foreach ($this->deepSearchKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return 'deep';
            }
        }
        
        foreach ($this->quickResponseKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return 'quick';
            }
        }
        
        return 'normal';
    }
    
    /**
     * Extrait les entités mentionnées (projets, dates, etc.)
     */
    private function extractEntities(string $message): array
    {
        $entities = [];
        
        // Dates
        if (preg_match_all('/\b(\d{4}|\d{1,2}\/\d{1,2}\/\d{2,4}|janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\b/i', $message, $matches)) {
            $entities['dates'] = $matches[0];
        }
        
        // Projets connus (à personnaliser selon l'utilisateur)
        $knownProjects = ['Etudesk', 'Ci20', 'LagentO'];
        foreach ($knownProjects as $project) {
            if (stripos($message, $project) !== false) {
                $entities['projects'][] = $project;
            }
        }
        
        // Nombres
        if (preg_match_all('/\b\d+\b/', $message, $matches)) {
            $entities['numbers'] = $matches[0];
        }
        
        return $entities;
    }
    
    /**
     * Construit la route optimale basée sur l'analyse
     */
    private function buildRoute(string $type, string $depth, array $entities, string $message): array
    {
        $config = $this->patterns[$type];
        
        $route = [
            'type' => $type,
            'depth' => $depth,
            'needs_search' => $config['needs_search'] ?? false,
            'search_type' => $config['search_type'] ?? null,
            'cache_duration' => $config['cache_duration'] ?? 0,
            'tools' => [],
            'context_needed' => [],
            'optimization_hints' => [],
            'entities' => $entities
        ];
        
        // Déterminer les outils nécessaires
        switch ($type) {
            case 'simple_facts':
                if ($depth === 'deep') {
                    $route['tools'][] = 'recherche_vectorielle';
                    $route['optimization_hints'][] = 'use_knowledge_base_only';
                }
                $route['optimization_hints'][] = 'direct_llm_response';
                break;
                
            case 'calculations':
                $route['tools'] = [];
                $route['optimization_hints'][] = 'use_code_interpreter';
                $route['optimization_hints'][] = 'no_context_needed';
                break;
                
            case 'user_context':
                $route['tools'][] = 'recherche_vectorielle';
                $route['context_needed'][] = 'user_profile';
                $route['context_needed'][] = 'recent_activities';
                
                if (!empty($entities['projects'])) {
                    $route['search_filters'] = ['projects' => $entities['projects']];
                    $route['optimization_hints'][] = 'filter_by_project';
                }
                break;
                
            case 'strategic_business':
                $route['tools'][] = 'recherche_vectorielle';
                $route['context_needed'][] = 'user_profile';
                $route['context_needed'][] = 'business_context';
                $route['search_type'] = 'opportunities';
                $route['optimization_hints'][] = 'focus_on_actionable_insights';
                break;
                
            case 'analytics':
                $route['tools'][] = 'recherche_vectorielle';
                $route['tools'][] = 'user_analytics';
                $route['context_needed'][] = 'user_metrics';
                $route['context_needed'][] = 'historical_data';
                
                if ($depth === 'quick') {
                    $route['optimization_hints'][] = 'use_cached_metrics';
                }
                break;
                
            case 'complex_generation':
                $route['tools'][] = 'recherche_vectorielle';
                if (preg_match('/email|lettre|message/i', $message)) {
                    $route['tools'][] = 'generation_email';
                }
                if (preg_match('/document|rapport|présentation/i', $message)) {
                    $route['tools'][] = 'generation_fichier';
                }
                $route['context_needed'][] = 'full_context';
                break;
                
            case 'technical':
                $route['tools'][] = 'recherche_vectorielle';
                $route['search_type'] = 'code_documentation';
                $route['optimization_hints'][] = 'search_technical_docs';
                break;
        }
        
        // Optimisations basées sur la profondeur
        if ($depth === 'quick') {
            $route['optimization_hints'][] = 'limit_search_results';
            $route['optimization_hints'][] = 'use_summary_mode';
            $route['max_search_results'] = 3;
        } elseif ($depth === 'deep') {
            $route['optimization_hints'][] = 'comprehensive_search';
            $route['max_search_results'] = 10;
        } else {
            $route['max_search_results'] = 5;
        }
        
        // Déterminer si on peut utiliser le cache
        if ($route['cache_duration'] > 0 && empty($entities['dates'])) {
            $route['cacheable'] = true;
            $route['cache_key'] = $this->generateCacheKey($type, $message, $entities);
        } else {
            $route['cacheable'] = false;
        }
        
        return $route;
    }
    
    /**
     * Génère une clé de cache unique
     */
    private function generateCacheKey(string $type, string $message, array $entities): string
    {
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', mb_strtolower($message));
        $entitiesHash = md5(json_encode($entities));
        
        return "agent:route:{$type}:" . substr(md5($normalized . $entitiesHash), 0, 16);
    }
    
    /**
     * Vérifie si une question est simple et factuelle
     */
    public function isSimpleFactualQuestion(string $message): bool
    {
        $route = $this->route($message);
        
        return $route['type'] === 'simple_facts' && 
               $route['depth'] !== 'deep' &&
               !$route['needs_search'];
    }
    
    /**
     * Détermine si une recherche vectorielle est vraiment nécessaire
     */
    public function needsVectorSearch(string $message): bool
    {
        $route = $this->route($message);
        
        return $route['needs_search'] && 
               in_array('recherche_vectorielle', $route['tools']);
    }
    
    /**
     * Retourne les hints d'optimisation pour un message
     */
    public function getOptimizationHints(string $message): array
    {
        $route = $this->route($message);
        
        return $route['optimization_hints'] ?? [];
    }
}