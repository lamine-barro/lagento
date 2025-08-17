<?php

namespace App\Agents;

use App\Models\User;
use App\Models\Project;
use App\Models\UserAnalytics;
use App\Models\Opportunity;
use App\Models\Institution;
use App\Models\OfficialText;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class AgentPrincipal extends BaseAgent
{
    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-mini',
            'strategy' => 'precision',
            'temperature' => 0.3,
            'max_tokens' => 1500,
            'tools' => [
                'gestion_base_donnees',
                'recherche_semantique', 
                'recherche_web',
                'generation_fichier',
                'generation_image'
            ]
        ];
    }

    public function execute(array $inputs): array
    {
        $userMessage = $inputs['user_message'] ?? '';
        $userId = $inputs['user_id'] ?? null;
        $conversationId = $inputs['conversation_id'] ?? null;

        if (!$userMessage || !$userId) {
            return [
                'success' => false,
                'error' => 'Message utilisateur et ID utilisateur requis'
            ];
        }

        // Get user context
        $userContext = $this->getUserAnalyticsContext($userId);
        
        // Prepare system instructions
        $instructions = $this->getSystemInstructions();
        $systemPrompt = $this->prepareSystemPrompt($instructions, $userContext);

        try {
            // Analyze user message to determine if tools are needed
            $toolsNeeded = $this->analyzeMessageForTools($userMessage);
            
            $toolResults = [];
            $toolUsageLogs = [];

            // Execute tools if needed
            foreach ($toolsNeeded as $tool) {
                $result = $this->executeTool($tool, $userMessage, $userId);
                if ($result) {
                    $toolResults[$tool] = $result;
                    $toolUsageLogs[] = $tool;
                    $this->logToolUsage($tool, ['user_id' => $userId]);
                }
            }

            // Add tool results to context if any
            if (!empty($toolResults)) {
                $systemPrompt .= "\n\nRésultats des outils :\n";
                foreach ($toolResults as $tool => $result) {
                    $systemPrompt .= "- {$tool}: " . json_encode($result, JSON_UNESCAPED_UNICODE) . "\n";
                }
            }

            // Generate response using LLM
            $config = $this->getConfig();
            $messages = $this->formatMessages($systemPrompt, $userMessage);
            
            $response = $this->llm->chat(
                $messages,
                $config['model'],
                $config['temperature'],
                $config['max_tokens']
            );

            // Format response as markdown
            $formattedResponse = $this->formatMarkdownResponse($response, $toolResults);

            // Update user analytics
            $this->updateUserAnalytics($userId, $toolUsageLogs);

            return [
                'success' => true,
                'response' => $formattedResponse,
                'tools_used' => $toolUsageLogs,
                'metadata' => [
                    'model' => $config['model'],
                    'tokens_estimated' => strlen($response) / 4, // Rough estimation
                    'tools_executed' => count($toolUsageLogs)
                ]
            ];

        } catch (\Exception $e) {
            \Log::error('AgentPrincipal execution error', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'message' => $userMessage
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du traitement de votre demande',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ];
        }
    }

    protected function getSystemInstructions(): string
    {
        return "Tu es Agent O, l'assistant IA dédié aux entrepreneurs ivoiriens. Tu es disponible 24/7 pour accompagner leur parcours entrepreneurial.

MISSION :
- Fournir des conseils personnalisés en entrepreneuriat
- Effectuer une veille sur les opportunités de financement
- Orienter vers les programmes gouvernementaux
- Accompagner la formalisation, le financement et l'accélération d'entreprises

PUBLIC CIBLE :
Entrepreneurs ivoiriens digitalement connectés de 18-35 ans : startups tech, PME en croissance, porteurs de projets structurés et diaspora entrepreneuriale.

LANGUE : Exclusivement français

FORMAT DE SORTIE : Markdown structuré avec :
- Titres : h2, h3
- Formatage : gras, italique
- Listes : ordonnées et non-ordonnées
- Alertes contextuelles

CARTES PERSONNALISÉES à utiliser quand approprié :
- Carte institution (pour recommander des organismes)
- Carte opportunité (pour présenter des financements/programmes)
- Carte texte officiel (pour références légales)
- Carte partenaire (pour networking)

CONTEXTE IVOIRIEN :
- Connaissance approfondie de l'écosystème entrepreneurial ivoirien
- Maîtrise des lois OHADA et réglementations locales
- Familiarité avec les institutions (CEPICI, CGECI, etc.)
- Compréhension des défis spécifiques aux entrepreneurs locaux

STYLE :
- Bienveillant et encourageant
- Pragmatique et actionnable
- Respectueux de la culture ivoirienne
- Éviter le jargon technique excessif";
    }

    protected function analyzeMessageForTools(string $message): array
    {
        $message = strtolower($message);
        $tools = [];

        // Recherche sémantique pour questions légales/réglementaires
        if (preg_match('/(loi|légal|réglementation|ohada|juridique|statut|formalisation)/i', $message)) {
            $tools[] = 'recherche_semantique';
        }

        // Base de données pour opportunités/institutions
        if (preg_match('/(opportunité|financement|subvention|incubateur|partenaire|institution)/i', $message)) {
            $tools[] = 'gestion_base_donnees';
        }

        // Recherche web pour informations récentes
        if (preg_match('/(actualité|récent|nouveau|2024|2025|prix|taux)/i', $message)) {
            $tools[] = 'recherche_web';
        }

        // Génération de fichier pour documents/plans
        if (preg_match('/(document|plan|rapport|cv|business plan|étude)/i', $message)) {
            $tools[] = 'generation_fichier';
        }

        // Génération d'image pour logos/visuels
        if (preg_match('/(logo|image|visuel|design|graphique)/i', $message)) {
            $tools[] = 'generation_image';
        }

        return array_unique($tools);
    }

    protected function executeTool(string $tool, string $message, int $userId): ?array
    {
        switch ($tool) {
            case 'gestion_base_donnees':
                return $this->executeDatabase($message, $userId);
            
            case 'recherche_semantique':
                return $this->executeSemanticSearch($message);
            
            case 'recherche_web':
                return $this->executeWebSearch($message);
            
            case 'generation_fichier':
                return $this->executeFileGeneration($message, $userId);
            
            case 'generation_image':
                return $this->executeImageGeneration($message, $userId);
            
            default:
                return null;
        }
    }

    protected function executeDatabase(string $message, int $userId): array
    {
        $results = [];

        // Search opportunities
        $opportunities = Opportunity::where('business_sector', 'like', '%' . $this->extractSector($message) . '%')
            ->orWhere('description', 'like', '%' . $this->extractKeywords($message) . '%')
            ->limit(5)
            ->get();

        if ($opportunities->count() > 0) {
            $results['opportunities'] = $opportunities->toArray();
        }

        // Search institutions
        $institutions = Institution::where('name', 'like', '%' . $this->extractKeywords($message) . '%')
            ->orWhere('description', 'like', '%' . $this->extractKeywords($message) . '%')
            ->limit(3)
            ->get();

        if ($institutions->count() > 0) {
            $results['institutions'] = $institutions->toArray();
        }

        return $results;
    }

    protected function executeSemanticSearch(string $message): array
    {
        try {
            $results = $this->search->searchSimilar($message, $this->embedding, 5);
            return ['semantic_results' => $results];
        } catch (\Exception $e) {
            \Log::error('Semantic search error: ' . $e->getMessage());
            return [];
        }
    }

    protected function executeWebSearch(string $message): array
    {
        try {
            $client = new Client();
            $searchQuery = $this->extractKeywords($message) . ' Côte d\'Ivoire entrepreneur 2025';
            
            // This would integrate with a web search API
            // For now, return placeholder
            return [
                'web_results' => [
                    'query' => $searchQuery,
                    'status' => 'simulated',
                    'message' => 'Recherche web simulée pour: ' . $searchQuery
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Web search error: ' . $e->getMessage());
            return [];
        }
    }

    protected function executeFileGeneration(string $message, int $userId): array
    {
        // Placeholder for file generation logic
        return [
            'file_generation' => [
                'type' => $this->detectFileType($message),
                'status' => 'queued',
                'message' => 'Génération de fichier planifiée'
            ]
        ];
    }

    protected function executeImageGeneration(string $message, int $userId): array
    {
        // Placeholder for image generation logic
        return [
            'image_generation' => [
                'prompt' => $this->extractImagePrompt($message),
                'status' => 'queued',
                'message' => 'Génération d\'image planifiée'
            ]
        ];
    }

    protected function updateUserAnalytics(int $userId, array $toolsUsed): void
    {
        $analytics = UserAnalytics::firstOrCreate(['user_id' => $userId]);
        
        $analytics->increment('messages_sent');
        
        if (in_array('generation_fichier', $toolsUsed)) {
            $analytics->increment('documents_generated');
        }
        
        if (in_array('gestion_base_donnees', $toolsUsed)) {
            $analytics->increment('opportunities_matched');
        }

        $analytics->save();
    }

    // Helper methods
    protected function extractSector(string $message): string
    {
        $sectors = config('constants.SECTEURS');
        foreach ($sectors as $key => $value) {
            if (stripos($message, strtolower($value)) !== false) {
                return $key;
            }
        }
        return '';
    }

    protected function extractKeywords(string $message): string
    {
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'pour', 'avec', 'sur', 'dans'];
        $words = str_word_count(strtolower($message), 1, 'àáâãäçèéêëìíîïñòóôõöùúûüý');
        $keywords = array_diff($words, $stopWords);
        return implode(' ', array_slice($keywords, 0, 3));
    }

    protected function detectFileType(string $message): string
    {
        if (preg_match('/business plan/i', $message)) return 'business_plan';
        if (preg_match('/cv|curriculum/i', $message)) return 'cv';
        if (preg_match('/rapport/i', $message)) return 'report';
        return 'document';
    }

    protected function extractImagePrompt(string $message): string
    {
        return 'Logo professionnel pour entreprise ivoirienne moderne et minimaliste';
    }

    protected function formatMarkdownResponse(string $response, array $toolResults = []): string
    {
        // Ensure response starts with proper formatting
        $formattedResponse = $response;
        
        // Add custom cards based on tool results
        if (isset($toolResults['gestion_base_donnees'])) {
            $formattedResponse = $this->appendDataCards($formattedResponse, $toolResults['gestion_base_donnees']);
        }

        // Ensure proper markdown structure
        $formattedResponse = $this->ensureMarkdownStructure($formattedResponse);
        
        return $formattedResponse;
    }

    protected function appendDataCards(string $response, array $data): string
    {
        $cards = "";

        // Add institution cards
        if (isset($data['institutions'])) {
            foreach ($data['institutions'] as $institution) {
                $cards .= $this->createInstitutionCard($institution);
            }
        }

        // Add opportunity cards
        if (isset($data['opportunities'])) {
            foreach ($data['opportunities'] as $opportunity) {
                $cards .= $this->createOpportunityCard($opportunity);
            }
        }

        return $response . "\n\n" . $cards;
    }

    protected function createInstitutionCard(array $institution): string
    {
        return "\n\n:::institution\n" .
               "**{$institution['name']}**\n\n" .
               "{$institution['description']}\n\n" .
               "📍 **Localisation:** {$institution['region']}, {$institution['ville']}\n" .
               "📞 **Contact:** {$institution['phone']}\n" .
               "🌐 **Site web:** {$institution['website']}\n" .
               ":::\n";
    }

    protected function createOpportunityCard(array $opportunity): string
    {
        return "\n\n:::opportunity\n" .
               "**{$opportunity['title']}**\n\n" .
               "{$opportunity['description']}\n\n" .
               "💰 **Type:** {$opportunity['type']}\n" .
               "📅 **Date limite:** {$opportunity['application_deadline']}\n" .
               "🎯 **Secteurs:** " . implode(', ', $opportunity['target_sectors'] ?? []) . "\n" .
               ":::\n";
    }

    protected function createOfficialTextCard(array $text): string
    {
        return "\n\n:::official-text\n" .
               "**{$text['title']}**\n\n" .
               "{$text['summary']}\n\n" .
               "📜 **Type:** {$text['legal_classification']}\n" .
               "📅 **Date publication:** {$text['publication_date']}\n" .
               "⚖️ **Statut:** {$text['status']}\n" .
               ":::\n";
    }

    protected function createPartnerCard(array $partner): string
    {
        return "\n\n:::partner\n" .
               "**{$partner['project_name']}**\n\n" .
               "👤 **Entrepreneur:** {$partner['founder_name']}\n" .
               "🏢 **Secteur:** {$partner['sector']}\n" .
               "📍 **Région:** {$partner['region']}\n" .
               "🤝 **Synergie:** {$partner['synergy_type']}\n" .
               ":::\n";
    }

    protected function ensureMarkdownStructure(string $content): string
    {
        // Ensure proper spacing between sections
        $content = preg_replace('/\n\n\n+/', "\n\n", $content);
        
        // Ensure headers have proper spacing
        $content = preg_replace('/\n(#{2,3})\s/', "\n\n$1 ", $content);
        
        return trim($content);
    }
}