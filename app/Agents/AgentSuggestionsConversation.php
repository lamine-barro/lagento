<?php

namespace App\Agents;

use App\Services\VectorAccessService;
use App\Models\User;

class AgentSuggestionsConversation extends BaseAgent
{
    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-nano',
            'strategy' => 'fast',
            'reasoning_effort' => 'minimal',
            'max_tokens' => 200,
            'tools' => []
        ];
    }

    public function execute(array $inputs): array
    {
        $startTime = microtime(true);
        $sessionId = $this->logExecutionStart($inputs);
        
        $previousPage = $inputs['previous_page'] ?? '';
        $activePage = $inputs['active_page'] ?? '';
        $userId = $inputs['user_id'] ?? null;

        if (!$userId) {
            $result = [
                'success' => false,
                'error' => 'ID utilisateur requis'
            ];
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }

        try {
            $this->logDebug('Getting user context for suggestions', ['user_id' => $userId]);
            
            // Get user context for personalization
            $userContext = $this->getUserAnalyticsContext($userId);
            
            // Get user's vectorized data for personalized suggestions
            $vectorContext = $this->getUserVectorContext($userId);
            
            $this->logDebug('Building suggestions prompt', [
                'previous_page' => $previousPage,
                'active_page' => $activePage,
                'user_business_sector' => $userContext['business_sector'] ?? 'N/A'
            ]);
            
            // Prepare system instructions
            $instructions = $this->getSystemInstructions();
            $systemPrompt = $this->prepareSystemPrompt($instructions, [
                'previous_page' => $previousPage,
                'active_page' => $activePage,
                'user_context' => $userContext,
                'vector_context' => $vectorContext
            ]);

            // Generate suggestions prompt
            $userMessage = $this->buildSuggestionsPrompt($previousPage, $activePage, $userContext, $vectorContext);

            // Generate response using nano model for speed
            $config = $this->getConfig();
            $messages = $this->formatMessages($systemPrompt, $userMessage);
            
            $llmStartTime = microtime(true);
            $response = $this->llm->chat(
                $messages,
                $config['model'],
                null,
                $config['max_tokens'],
                ['reasoning_effort' => $config['reasoning_effort']]
            );
            $this->logLLMCall($messages, $config, $llmStartTime);

            $this->logDebug('Parsing suggestions from response', ['response_length' => strlen($response)]);

            // Parse suggestions from response
            $suggestions = $this->parseSuggestions($response);

            // Ensure we have exactly 5 suggestions
            $suggestions = $this->normalizeSuggestions($suggestions, $userContext);

            $result = [
                'success' => true,
                'suggestions' => $suggestions,
                'metadata' => [
                    'model' => $config['model'],
                    'context' => [
                        'previous_page' => $previousPage,
                        'active_page' => $activePage
                    ],
                    'suggestions_count' => count($suggestions)
                ]
            ];

            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;

        } catch (\Exception $e) {
            $this->logError($e->getMessage(), [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'previous_page' => $previousPage,
                'active_page' => $activePage,
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback suggestions
            $userContext = $userContext ?? [];
            $result = [
                'success' => true,
                'suggestions' => $this->getFallbackSuggestions($userContext),
                'metadata' => [
                    'fallback' => true,
                    'error' => app()->environment('local') ? $e->getMessage() : null
                ]
            ];
            
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }
    }

    protected function getSystemInstructions(): string
    {
        return "Tu génères des suggestions de questions que l'entrepreneur va poser à Agent O.

MISSION :
Générer 5 questions pertinentes que l'utilisateur peut poser à Agent O basées sur :
- Son contexte entrepreneurial personnalisé
- Ses projets et diagnostics
- La page où il se trouve
- Ses besoins identifiés

CONTRAINTES :
- Chaque question : MAXIMUM 15 mots
- Questions directes que l'entrepreneur poserait naturellement
- Adaptées à son niveau de maturité et ses défis spécifiques
- Éviter les répétitions
- Mélanger différents aspects : stratégie, financement, opérationnel, légal

FORMAT DE SORTIE :
Retourne exactement 5 questions, une par ligne, sans numérotation ni puces.
Chaque ligne contient une question complète que l'entrepreneur poserait.

EXEMPLES DE STYLE :
- \"Comment optimiser la rentabilité de mon projet EdTech ?\"
- \"Quels financements pour une startup au stade prototype ?\"
- \"Comment structurer mon équipe pour passer à l'échelle ?\"
- \"Quelles démarches prioritaires pour formaliser rapidement ?\"

STYLE DES QUESTIONS :
- À la première personne (\"Comment puis-je...\", \"Que dois-je...\")
- Pratiques et orientées solutions
- Spécifiques au contexte ivoirien quand pertinent
- Reflètent les préoccupations réelles de l'entrepreneur";
    }

    protected function buildSuggestionsPrompt(string $previousPage, string $activePage, array $userContext, array $vectorContext = []): string
    {
        $prompt = "Génère 5 questions que cet entrepreneur ivoirien va poser à Agent O.\n\n";
        
        if ($previousPage) {
            $prompt .= "Page précédente : {$previousPage}\n";
        }
        
        if ($activePage) {
            $prompt .= "Page actuelle : {$activePage}\n";
        }

        // Add specific context based on active page
        $prompt .= $this->getPageSpecificContext($activePage);

        // Add business context if available
        if (!empty($userContext['business_sector'])) {
            $prompt .= "\nSecteur d'activité : " . config('constants.SECTEURS')[$userContext['business_sector']] ?? $userContext['business_sector'];
        }

        if (!empty($userContext['business_stage'])) {
            $prompt .= "\nStade : " . config('constants.STADES_MATURITE')[$userContext['business_stage']] ?? $userContext['business_stage'];
        }

        // Add personalized context from user's projects and analytics
        if (!empty($vectorContext)) {
            $prompt .= "\n\nContexte personnalisé :";
            
            if (!empty($vectorContext['projects'])) {
                $prompt .= "\nProjets : " . implode(', ', array_column($vectorContext['projects'], 'name'));
            }
            
            if (!empty($vectorContext['analytics'])) {
                $analytics = $vectorContext['analytics'];
                if (!empty($analytics['niveau_maturite'])) {
                    $prompt .= "\nNiveau maturité : " . $analytics['niveau_maturite'];
                }
                if (!empty($analytics['forces'])) {
                    $prompt .= "\nForces identifiées : " . implode(', ', array_slice($analytics['forces'], 0, 3));
                }
                if (!empty($analytics['besoins'])) {
                    $prompt .= "\nBesoins prioritaires : " . implode(', ', array_slice($analytics['besoins'], 0, 2));
                }
            }
        }

        $prompt .= "\n\nGénère maintenant 5 questions pertinentes que cet entrepreneur poserait à Agent O :";

        return $prompt;
    }

    protected function getPageSpecificContext(string $activePage): string
    {
        switch ($activePage) {
            case 'diagnostic':
                return "\nContexte : Il vient de consulter son diagnostic. Questions sur optimisation, axes d'amélioration, actions concrètes.";
            
            case 'chat':
                return "\nContexte : Il est prêt à discuter. Questions variées selon ses besoins entrepreneuriaux actuels.";
            
            case 'conversations':
                return "\nContexte : Il revient après avoir consulté ses conversations. Questions d'approfondissement ou nouveaux sujets.";
            
            case 'profile':
                return "\nContexte : Il vient de voir son profil. Questions sur mise à jour, optimisation de ses données.";
            
            case 'opportunities':
                return "\nContexte : Il cherche des opportunités. Questions sur financements, concours, partenariats adaptés.";
            
            default:
                return "\nContexte : Navigation générale. Questions équilibrées sur tous les aspects entrepreneuriaux.";
        }
    }

    protected function parseSuggestions(string $response): array
    {
        $lines = explode("\n", trim($response));
        $suggestions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Clean up formatting
            $line = preg_replace('/^[-•*\d+\.)\s]+/', '', $line); // Remove bullets and numbers
            $line = trim($line, '"\''); // Remove quotes
            
            if (!empty($line) && strlen($line) <= 120) { // Max 20 words ~ 120 chars
                $suggestions[] = $line;
            }
        }

        return array_slice($suggestions, 0, 5); // Ensure max 5 suggestions
    }

    protected function normalizeSuggestions(array $suggestions, array $userContext): array
    {
        // If we don't have 5 suggestions, add contextual ones
        while (count($suggestions) < 5) {
            $suggestions[] = $this->generateContextualSuggestion(count($suggestions), $userContext);
        }

        // Ensure each suggestion is within word limit
        return array_map(function($suggestion) {
            $words = str_word_count($suggestion);
            if ($words > 20) {
                // Truncate to 20 words
                $wordsArray = explode(' ', $suggestion);
                return implode(' ', array_slice($wordsArray, 0, 20)) . '...';
            }
            return $suggestion;
        }, array_slice($suggestions, 0, 5));
    }

    protected function generateContextualSuggestion(int $index, array $userContext): string
    {
        $baseSuggestions = [
            "Comment formaliser mon entreprise en Côte d'Ivoire ?",
            "Quels financements sont disponibles pour mon secteur ?",
            "Comment développer mon réseau professionnel ?",
            "Quelles sont mes obligations légales ?",
            "Comment améliorer ma stratégie marketing ?"
        ];

        // Customize based on user context
        if (!empty($userContext['business_sector'])) {
            $sectorSuggestions = [
                "Opportunités de financement dans mon secteur",
                "Partenaires stratégiques pour mon domaine",
                "Réglementations spécifiques à mon activité"
            ];
            
            if ($index < count($sectorSuggestions)) {
                return $sectorSuggestions[$index];
            }
        }

        return $baseSuggestions[$index] ?? "Comment optimiser mon entreprise ?";
    }

    protected function getFallbackSuggestions(array $userContext): array
    {
        $fallbacks = [
            "Comment créer mon entreprise en Côte d'Ivoire ?",
            "Quels financements pour mon projet ?",
            "Comment trouver des partenaires ?",
            "Aide pour mon business plan",
            "Obligations légales à respecter"
        ];

        // Customize first suggestion based on business stage if available
        if (!empty($userContext['business_stage'])) {
            switch ($userContext['business_stage']) {
                case 'IDEE':
                    $fallbacks[0] = "Comment valider mon idée d'entreprise ?";
                    break;
                case 'LANCEMENT':
                    $fallbacks[0] = "Comment lancer mon entreprise efficacement ?";
                    break;
                case 'CROISSANCE':
                    $fallbacks[0] = "Stratégies pour accélérer ma croissance";
                    break;
            }
        }

        return $fallbacks;
    }

    /**
     * Get vectorized context for user (projects and analytics)
     */
    protected function getUserVectorContext(string $userId): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [];
            }

            $vectorAccessService = app(VectorAccessService::class);
            
            // Search for user's project and analytics data
            $results = $vectorAccessService->searchWithAccess(
                'projet entreprise diagnostic profil', // Generic query to get user data
                $user,
                ['user_project', 'user_analytics'],
                10 // Get more context
            );

            $context = [
                'projects' => [],
                'analytics' => []
            ];

            foreach ($results as $result) {
                if ($result['memory_type'] === 'user_project') {
                    // Extract project name from content
                    if (preg_match('/Nom:\s*([^\n]+)/', $result['content'], $matches)) {
                        $context['projects'][] = [
                            'name' => trim($matches[1]),
                            'content' => $result['content']
                        ];
                    }
                } elseif ($result['memory_type'] === 'user_analytics') {
                    // Extract analytics insights from content
                    $analytics = [];
                    
                    if (preg_match('/Niveau global:\s*([^\n]+)/', $result['content'], $matches)) {
                        $analytics['niveau_maturite'] = trim($matches[1]);
                    }
                    
                    if (preg_match('/Forces:\s*([^\n]+)/', $result['content'], $matches)) {
                        $analytics['forces'] = array_map('trim', explode(',', $matches[1]));
                    }
                    
                    if (preg_match('/Besoins\s+[^\n]*:\s*([^\n]+)/', $result['content'], $matches)) {
                        $analytics['besoins'] = array_map('trim', explode(',', $matches[1]));
                    }
                    
                    if (preg_match('/Axes progression:\s*([^\n]+)/', $result['content'], $matches)) {
                        $analytics['axes_progression'] = array_map('trim', explode(',', $matches[1]));
                    }
                    
                    $context['analytics'] = array_merge($context['analytics'], $analytics);
                }
            }

            return $context;

        } catch (\Exception $e) {
            \Log::error('Error getting user vector context', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}