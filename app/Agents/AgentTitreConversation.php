<?php

namespace App\Agents;

class AgentTitreConversation extends BaseAgent
{
    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-nano',
            'strategy' => 'fast',
            'reasoning_effort' => 'minimal',
            'max_tokens' => 50,
            'tools' => []
        ];
    }

    public function execute(array $inputs): array
    {
        $startTime = microtime(true);
        $sessionId = $this->logExecutionStart($inputs);
        
        $userFirstMessage = $inputs['user_first_message'] ?? '';
        $activePage = $inputs['active_page'] ?? '';
        $conversationId = $inputs['conversation_id'] ?? null;
        $messageCount = $inputs['message_count'] ?? 0;
        $recentMessages = $inputs['recent_messages'] ?? [];

        if (!$userFirstMessage && empty($recentMessages)) {
            $result = [
                'success' => false,
                'error' => 'Premier message utilisateur ou messages récents requis'
            ];
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }

        try {
            // Determine if this is initial title generation or update
            $isUpdate = $messageCount >= 10 && !empty($recentMessages);
            
            $this->logDebug('Title generation mode', [
                'is_update' => $isUpdate,
                'message_count' => $messageCount,
                'active_page' => $activePage,
                'first_message_length' => strlen($userFirstMessage),
                'recent_messages_count' => count($recentMessages)
            ]);
            
            // Prepare system instructions
            $instructions = $this->getSystemInstructions($isUpdate);
            $systemPrompt = $this->prepareSystemPrompt($instructions, [
                'active_page' => $activePage,
                'message_count' => $messageCount,
                'is_update' => $isUpdate
            ]);

            // Build prompt based on context
            $userMessage = $isUpdate 
                ? $this->buildUpdatePrompt($recentMessages, $activePage)
                : $this->buildInitialPrompt($userFirstMessage, $activePage);

            $this->logDebug('Prompt built', [
                'prompt_type' => $isUpdate ? 'update' : 'initial',
                'prompt_length' => strlen($userMessage)
            ]);

            // Generate title using nano model for speed
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

            $this->logDebug('Raw title response', ['raw_response' => $response]);

            // Clean and validate title
            $title = $this->cleanTitle($response);
            
            // Ensure title meets requirements (max 7 words)
            $title = $this->validateTitle($title, $userFirstMessage);

            $result = [
                'success' => true,
                'title' => $title,
                'metadata' => [
                    'model' => $config['model'],
                    'is_update' => $isUpdate,
                    'message_count' => $messageCount,
                    'word_count' => str_word_count($title),
                    'final_title' => $title
                ]
            ];

            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;

        } catch (\Exception $e) {
            $this->logError($e->getMessage(), [
                'session_id' => $sessionId,
                'conversation_id' => $conversationId,
                'message_count' => $messageCount,
                'active_page' => $activePage,
                'trace' => $e->getTraceAsString()
            ]);

            // Return fallback title
            $fallbackTitle = $this->generateFallbackTitle($userFirstMessage, $activePage);
            
            $result = [
                'success' => true,
                'title' => $fallbackTitle,
                'metadata' => [
                    'fallback' => true,
                    'error' => app()->environment('local') ? $e->getMessage() : null
                ]
            ];
            
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }
    }

    protected function getSystemInstructions(bool $isUpdate = false): string
    {
        $baseInstructions = "Tu es un générateur de titres pour les conversations d'Agent O, l'assistant IA des entrepreneurs ivoiriens.

MISSION :
Générer un titre concis et descriptif pour la conversation en français.

CONTRAINTES STRICTES :
- MAXIMUM 7 mots
- Descriptif et précis
- Sans ponctuation finale
- Style professionnel mais accessible
- Éviter les mots vagues comme 'aide', 'conseil', 'question'

FORMAT DE SORTIE :
Retourne uniquement le titre, sans guillemets ni ponctuation finale.";

        if ($isUpdate) {
            $baseInstructions .= "\n\nCONTEXTE SPÉCIAL : MISE À JOUR DE TITRE
La conversation a évolué avec plus de contexte. Génère un titre plus précis qui reflète le sujet principal abordé dans les messages récents.";
        }

        return $baseInstructions;
    }

    protected function buildInitialPrompt(string $userFirstMessage, string $activePage): string
    {
        // Clean UTF-8 characters to prevent encoding issues
        $userFirstMessage = mb_convert_encoding($userFirstMessage, 'UTF-8', 'UTF-8');
        
        $prompt = "Génère un titre pour cette conversation basé sur le premier message :\n\n";
        $prompt .= "Message : {$userFirstMessage}\n";
        
        if ($activePage) {
            $prompt .= "Page : {$activePage}\n";
        }

        $prompt .= "\nTitre (max 7 mots) :";

        return $prompt;
    }

    protected function buildUpdatePrompt(array $recentMessages, string $activePage): string
    {
        $prompt = "Génère un titre mis à jour pour cette conversation basé sur les messages récents :\n\n";
        
        // Include last 5 messages for context
        $lastMessages = array_slice($recentMessages, -5);
        foreach ($lastMessages as $i => $message) {
            $role = $message['role'] === 'user' ? 'Utilisateur' : 'Agent';
            // Clean UTF-8 characters to prevent encoding issues
            $content = mb_convert_encoding($message['content'], 'UTF-8', 'UTF-8');
            $content = substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '');
            $prompt .= "{$role} : {$content}\n";
        }

        if ($activePage) {
            $prompt .= "\nPage actuelle : {$activePage}\n";
        }

        $prompt .= "\nTitre mis à jour (max 7 mots) :";

        return $prompt;
    }

    protected function cleanTitle(string $response): string
    {
        // Remove quotes, punctuation, and extra whitespace
        $title = trim($response);
        $title = trim($title, '"\'');
        $title = rtrim($title, '.!?:');
        $title = preg_replace('/\s+/', ' ', $title);
        
        // Remove common prefixes
        $title = preg_replace('/^(titre\s*:?\s*|conversation\s*:?\s*)/i', '', $title);
        
        return trim($title);
    }

    protected function validateTitle(string $title, string $fallbackSource): string
    {
        $words = str_word_count($title);
        
        // If title is too long, truncate intelligently
        if ($words > 7) {
            $wordsArray = explode(' ', $title);
            $title = implode(' ', array_slice($wordsArray, 0, 7));
        }
        
        // If title is too short or empty, generate from source
        if ($words < 2 || empty(trim($title))) {
            $title = $this->generateFromSource($fallbackSource);
        }

        // Ensure title is not generic
        if ($this->isTitleGeneric($title)) {
            $title = $this->makeMoreSpecific($title, $fallbackSource);
        }

        return $title;
    }

    protected function generateFromSource(string $source): string
    {
        // Extract key terms from the source message
        $source = strtolower($source);
        
        // Business-specific patterns
        if (preg_match('/(créer|création|formaliser)/i', $source)) {
            return 'Création entreprise Côte Ivoire';
        }
        
        if (preg_match('/(financement|subvention|prêt)/i', $source)) {
            return 'Financement startup entreprise';
        }
        
        if (preg_match('/(juridique|légal|droit)/i', $source)) {
            return 'Aspects juridiques entreprise';
        }
        
        if (preg_match('/(partenaire|réseau|contact)/i', $source)) {
            return 'Networking partenariats stratégiques';
        }
        
        if (preg_match('/(export|international)/i', $source)) {
            return 'Expansion internationale export';
        }

        // Extract business sector if mentioned
        $sectors = config('constants.SECTEURS', []);
        foreach ($sectors as $key => $value) {
            if (stripos($source, strtolower($value)) !== false) {
                return 'Entreprise ' . explode(' ', $value)[0];
            }
        }

        return 'Conversation entrepreneuriale';
    }

    protected function isTitleGeneric(string $title): bool
    {
        $genericTerms = [
            'conversation',
            'aide',
            'conseil',
            'question',
            'demande',
            'information',
            'discussion'
        ];

        $titleLower = strtolower($title);
        foreach ($genericTerms as $term) {
            if (strpos($titleLower, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function makeMoreSpecific(string $title, string $source): string
    {
        // Try to add specific context from source
        $source = strtolower($source);
        
        if (preg_match('/(sarl|sa|sas|entreprise individuelle)/i', $source)) {
            return 'Formalisation entreprise Côte Ivoire';
        }
        
        if (preg_match('/(agriculture|agro)/i', $source)) {
            return 'Projet agricole entrepreneur';
        }
        
        if (preg_match('/(tech|technologie|numérique)/i', $source)) {
            return 'Startup tech Côte Ivoire';
        }

        // Default to more specific version
        return str_replace(['conversation', 'aide', 'conseil'], 'projet', strtolower($title));
    }

    protected function generateFallbackTitle(string $userFirstMessage, string $activePage): string
    {
        // Quick fallback based on page context
        switch ($activePage) {
            case 'diagnostic':
                return 'Analyse diagnostic';
            case 'opportunities':
                return 'Recherche opportunités financement';
            case 'profile':
                return 'Optimisation profil entreprise';
            default:
                // Generate from first message
                if (!empty($userFirstMessage)) {
                    return $this->generateFromSource($userFirstMessage);
                }
                return 'Nouvelle conversation Agent O';
        }
    }
}