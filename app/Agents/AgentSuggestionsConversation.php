<?php

namespace App\Agents;

class AgentSuggestionsConversation extends BaseAgent
{
    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-nano',
            'strategy' => 'fast',
            'temperature' => 0.7,
            'max_tokens' => 200,
            'tools' => []
        ];
    }

    public function execute(array $inputs): array
    {
        $previousPage = $inputs['previous_page'] ?? '';
        $activePage = $inputs['active_page'] ?? '';
        $userId = $inputs['user_id'] ?? null;

        if (!$userId) {
            return [
                'success' => false,
                'error' => 'ID utilisateur requis'
            ];
        }

        try {
            // Get user context for personalization
            $userContext = $this->getUserAnalyticsContext($userId);
            
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
            
            $response = $this->llm->chat(
                $messages,
                $config['model'],
                $config['temperature'],
                $config['max_tokens']
            );

            // Parse suggestions from response
            $suggestions = $this->parseSuggestions($response);

            // Ensure we have exactly 5 suggestions
            $suggestions = $this->normalizeSuggestions($suggestions, $userContext);

            return [
                'success' => true,
                'suggestions' => $suggestions,
                'metadata' => [
                    'model' => $config['model'],
                    'context' => [
                        'previous_page' => $previousPage,
                        'active_page' => $activePage
                    ]
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('AgentSuggestionsConversation execution error', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'previous_page' => $previousPage,
                'active_page' => $activePage
            ]);

            // Return fallback suggestions
            return [
                'success' => true,
                'suggestions' => $this->getFallbackSuggestions($userContext),
                'metadata' => [
                    'fallback' => true,
                    'error' => app()->environment('local') ? $e->getMessage() : null
                ]
            ];
        }
    }

    protected function getSystemInstructions(): string
    {
        return "Tu es un générateur de suggestions pour Agent O, l'assistant IA des entrepreneurs ivoiriens.

MISSION :
Générer 5 suggestions de messages pertinentes et contextuelles basées sur :
- La page précédente de l'utilisateur
- La page active actuelle
- Le profil entrepreneurial de l'utilisateur

CONTRAINTES :
- Chaque suggestion : MAXIMUM 20 mots
- Suggestions courtes, directes et actionnables
- Adaptées au contexte entrepreneurial ivoirien
- Éviter les répétitions
- Varier les types de questions (conseil, financement, légal, networking, etc.)

FORMAT DE SORTIE :
Retourne exactement 5 suggestions, une par ligne, sans numérotation ni puces.
Chaque ligne contient une suggestion complète.

CONTEXTE ENTREPRENEURIAL :
- Formalisation d'entreprise en Côte d'Ivoire
- Recherche de financement et opportunités
- Accompagnement juridique et réglementaire
- Networking et partenariats
- Stratégies de croissance et d'expansion

STYLE :
- Questions directes et pratiques
- Langage familier mais professionnel
- Orienté action et résultats";
    }

    protected function buildSuggestionsPrompt(string $previousPage, string $activePage, array $userContext): string
    {
        $prompt = "Génère 5 suggestions de messages pour un entrepreneur ivoirien.\n\n";
        
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

        $prompt .= "\n\nGénère maintenant 5 suggestions de messages pertinentes :";

        return $prompt;
    }

    protected function getPageSpecificContext(string $activePage): string
    {
        switch ($activePage) {
            case 'dashboard':
                return "\nContexte : L'utilisateur consulte son tableau de bord. Suggestions sur l'analyse des données, optimisation, opportunités.";
            
            case 'chat':
                return "\nContexte : L'utilisateur est dans l'interface de chat. Suggestions variées sur tous les sujets entrepreneuriaux.";
            
            case 'conversations':
                return "\nContexte : L'utilisateur consulte ses conversations. Suggestions sur nouveaux sujets, approfondissement.";
            
            case 'profile':
                return "\nContexte : L'utilisateur consulte son profil. Suggestions sur optimisation du profil, mise à jour des données.";
            
            case 'opportunities':
                return "\nContexte : L'utilisateur consulte les opportunités. Suggestions sur financement, subventions, concours.";
            
            default:
                return "\nContexte : Navigation générale. Suggestions équilibrées sur tous les aspects entrepreneuriaux.";
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
}