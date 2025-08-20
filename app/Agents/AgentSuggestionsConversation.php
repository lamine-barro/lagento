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
            
            // User project data is now included directly in userContext from BaseAgent
            
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
                'user_context' => $userContext
            ]);

            // Generate suggestions prompt
            $userMessage = $this->buildSuggestionsPrompt($previousPage, $activePage, $userContext);

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
        return "Tu génères des suggestions de questions business concrètes pour entrepreneurs ivoiriens.

MISSION :
Générer 5 questions BUSINESS PRATIQUES que l'entrepreneur va poser à Agent O.

CONTRAINTES :
- Chaque question : MAXIMUM 12 mots
- Questions directes, business-oriented
- Contexte Côte d'Ivoire quand pertinent

FORMAT DE SORTIE :
5 questions exactement, une par ligne, sans numérotation.

EXEMPLES OBLIGATOIRES À VARIER :
- \"Génère mon business plan synthétique\"
- \"Crée un logo pour mon entreprise\"
- \"Quelles initiatives culturelles ivoiriennes disponibles ?\" 
- \"Qui est ma cible idéale ?\"
- \"Comment obtenir un RDV avec un corporate ?\"
- \"Quel montage juridique OHADA pour ma SAS ?\"
- \"Comment pitcher en 60 secondes ?\"
- \"Quelles opportunités de financement disponibles maintenant ?\"
- \"Comment calculer mon prix de vente optimal ?\"
- \"Quelle stratégie pour mes premiers 10 clients ?\"
- \"Comment valider mon product-market fit ?\"
- \"Quel modèle de revenus récurrents adopter ?\"

STYLE OBLIGATOIRE :
- Questions courtes et percutantes
- Vocabulaire business moderne
- Orienté action et résultats
- Mix stratégique/opérationnel/financier";
    }

    protected function buildSuggestionsPrompt(string $previousPage, string $activePage, array $userContext): string
    {
        $prompt = "Génère 5 questions business concrètes.\n\n";
        
        // Context entrepreneurial
        $entrepreneurContext = [];
        
        if (!empty($userContext['business_sector'])) {
            $sector = config('constants.SECTEURS')[$userContext['business_sector']] ?? $userContext['business_sector'];
            $entrepreneurContext[] = "Secteur: {$sector}";
        }

        if (!empty($userContext['business_stage'])) {
            $stage = config('constants.STADES_MATURITE')[$userContext['business_stage']] ?? $userContext['business_stage'];
            $entrepreneurContext[] = "Stade: {$stage}";
        }
        
        if (!empty($userContext['nom_entreprise'])) {
            $entrepreneurContext[] = "Entreprise: {$userContext['nom_entreprise']}";
        }
        
        if (!empty($entrepreneurContext)) {
            $prompt .= "Contexte entrepreneur: " . implode(', ', $entrepreneurContext) . "\n";
        }

        // Page context for relevance
        $prompt .= $this->getPageSpecificContext($activePage);
        
        // Project insights if available
        if (!empty($userContext['project'])) {
            $project = $userContext['project'];
            if (!empty($project['support_types'])) {
                $topNeeds = array_slice($project['support_types'], 0, 2);
                $prompt .= "Besoins identifiés: " . implode(', ', $topNeeds) . "\n";
            }
        }

        $prompt .= "\nGénère 5 questions business percutantes, variées (canvas, cible, vente, financement, growth) :";

        return $prompt;
    }

    protected function getPageSpecificContext(string $activePage): string
    {
        switch ($activePage) {
            case 'diagnostic':
                return "Focus: Diagnostic réalisé - questions sur canvas, KPIs, optimisation\n";
            
            case 'chat':
                return "Focus: Discussion ouverte - mix stratégie, financement, growth\n";
            
            case 'conversations':
                return "Focus: Historique conversations - questions approfondissement, nouveaux angles\n";
            
            case 'profile':
                return "Focus: Profil entrepreneur - questions sur parcours, objectifs, croissance\n";
            
            case 'opportunities':
                return "Focus: Recherche opportunités - questions sur financement, programmes, deadlines\n";
            
            default:
                return "Focus: Général - mix opportunités, stratégie, financement, growth\n";
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
            "Donne-moi mon business lean canvas",
            "Qui est ma cible idéale ?",
            "Comment obtenir un RDV avec un corporate ?",
            "Quel montage juridique OHADA pour ma SAS ?",
            "Comment pitcher en 60 secondes ?"
        ];

        // Customize based on user context
        if (!empty($userContext['business_stage'])) {
            $stageSuggestions = [
                'IDEE' => [
                    "Comment valider mon idée rapidement ?",
                    "Quel MVP développer en premier ?",
                    "Comment tester mon marché cible ?"
                ],
                'STARTUP' => [
                    "Comment lever mes premiers fonds ?",
                    "Quelle stratégie d'acquisition clients ?",
                    "Comment structurer mon équipe ?"
                ],
                'CROISSANCE' => [
                    "Comment scaler mes opérations ?",
                    "Quelle stratégie internationale adopter ?",
                    "Comment optimiser ma rentabilité ?"
                ]
            ];
            
            $suggestions = $stageSuggestions[$userContext['business_stage']] ?? $baseSuggestions;
            if ($index < count($suggestions)) {
                return $suggestions[$index];
            }
        }

        return $baseSuggestions[$index] ?? "Quelles opportunités disponibles maintenant ?";
    }

    protected function getFallbackSuggestions(array $userContext): array
    {
        $fallbacks = [
            "Donne-moi mon business lean canvas",
            "Qui est ma cible idéale ?",
            "Comment calculer mon prix de vente ?",
            "Quelle stratégie pour mes premiers clients ?",
            "Comment pitcher en 60 secondes ?"
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

}