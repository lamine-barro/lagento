<?php

namespace App\Agents;

use App\Models\User;
use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Models\Opportunite;
use App\Models\Institution;
use App\Models\TexteOfficiel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class AgentPrincipal extends BaseAgent
{
    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-mini',
            'temperature' => 0.3,
            'max_tokens' => 1500,
            'tools' => [
                'gestion_base_donnees',
                'recherche_semantique', 
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
            // Injecter contexte conversationnel
            $recent = $inputs['recent_messages'] ?? [];
            $summary = trim((string)($inputs['conversation_summary'] ?? ''));

            $contextBlock = '';
            if (!empty($recent)) {
                $contextBlock .= "\n\nContexte récent (4 messages):\n";
                foreach ($recent as $r) {
                    $prefix = $r['role'] === 'user' ? 'Utilisateur' : 'Assistant';
                    $contextBlock .= "- {$prefix}: " . $r['content'] . "\n";
                }
            }
            if ($summary !== '') {
                $contextBlock .= "\nRésumé de la conversation:\n" . $summary . "\n";
            }

            $messages = $this->formatMessages($systemPrompt . $contextBlock, $userMessage);
            
            $response = $this->llm->chat(
                $messages,
                $config['model'],
                $config['temperature'],
                $config['max_tokens'],
                [
                    'web_search' => (bool) preg_match('/(actualité|récent|nouveau|2024|2025|prix|taux)/i', $userMessage),
                    'search_context_size' => 'medium',
                    'user_location' => [
                        'country' => 'CI',
                        'city' => $userContext['region'] ?? 'Abidjan',
                        'region' => $userContext['region'] ?? 'Abidjan'
                    ]
                ]
            );

            // Format response as markdown
            $formattedResponse = $this->formatMarkdownResponse($response, $toolResults);

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

OUTILS ET QUAND LES UTILISER :
- gestion_base_donnees : lorsque l'utilisateur parle d'opportunités, financements, institutions, partenaires, ou de ses projets. Par défaut lecture; si l'utilisateur le demande explicitement, proposer la mise à jour du projet et/ou des analytics.
- recherche_semantique : pour les lois, réglementations, procédures OHADA, textes officiels et références légales.
- generation_fichier (docx, csv, txt, md) : quand un document est demandé (business plan, CV, rapport, plan, résumé). Tu renvoies uniquement le lien de téléchargement fourni par l'outil.
- generation_image : pour logos/visuels/maquettes. Utilise exclusivement gpt-image-1. Tu renvoies uniquement le lien de téléchargement fourni par l'outil.
- web search (intégré au modèle) : si besoin d'actualités/informations récentes (mots-clés : actualité, récent, 2024, 2025, prix, taux). Le modèle l'activera automatiquement.

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

        // Opportunités (filtre simple sur description/type)
        $keyword = $this->extractKeywords($message);
        $opportunities = Opportunite::where('description', 'like', '%' . $keyword . '%')
            ->orWhere('type', 'like', '%' . $keyword . '%')
            ->limit(5)
            ->get();

        if ($opportunities->count() > 0) {
            $results['opportunities'] = $opportunities->toArray();
        }

        // Institutions (filtre sur nom/description)
        $institutions = Institution::where('nom', 'like', '%' . $keyword . '%')
            ->orWhere('description', 'like', '%' . $keyword . '%')
            ->limit(3)
            ->get();

        if ($institutions->count() > 0) {
            $results['institutions'] = $institutions->toArray();
        }

        // Projets de l'utilisateur visibles (public + vérifiés)
        $projets = Projet::where('user_id', $userId)
            ->public()
            ->verified()
            ->limit(3)
            ->get();

        if ($projets->count() > 0) {
            $results['projets'] = $projets->toArray();
        }

        return $results;
    }

    protected function executeSemanticSearch(string $message): array
    {
        try {
            $results = $this->search->searchSimilar($message, 5);
            return ['semantic_results' => $results];
        } catch (\Exception $e) {
            \Log::error('Semantic search error: ' . $e->getMessage());
            return [];
        }
    }

    protected function executeFileGeneration(string $message, int $userId): array
    {
        $detected = $this->detectFileType($message);
        $ext = 'md';
        switch (true) {
            case str_contains(strtolower($message), 'docx') || $detected === 'business_plan' || $detected === 'cv' || $detected === 'report':
                $ext = 'docx';
                break;
            case str_contains(strtolower($message), 'csv'):
                $ext = 'csv';
                break;
            case str_contains(strtolower($message), 'txt'):
                $ext = 'txt';
                break;
            case str_contains(strtolower($message), 'md'):
                $ext = 'md';
                break;
        }

        $dir = 'chat-attachments';
        $filename = 'doc_' . $userId . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.' . $ext;
        $path = $dir . '/' . $filename;

        try {
            if ($ext === 'docx') {
                $phpWord = new PhpWord();
                $section = $phpWord->addSection();
                $section->addTitle('Document généré par Agent O', 1);
                $section->addText('Date: ' . now()->toDateTimeString());
                $section->addTextBreak(1);
                $section->addText('Contexte utilisateur:', ['bold' => true]);
                $section->addText($message);
                $tempFile = tempnam(sys_get_temp_dir(), 'agento_docx_');
                $writer = IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($tempFile);
                $stream = fopen($tempFile, 'r');
                Storage::disk('public')->put($path, $stream);
                if (is_resource($stream)) fclose($stream);
                @unlink($tempFile);
            } elseif ($ext === 'csv') {
                $lines = [
                    ['Titre', 'Valeur'],
                    ['Date', now()->toDateTimeString()],
                    ['Résumé', mb_substr(preg_replace('/\s+/', ' ', $message), 0, 200)],
                ];
                $csv = '';
                foreach ($lines as $row) {
                    $csv .= implode(',', array_map(fn($c) => '"' . str_replace('"', '""', (string)$c) . '"', $row)) . "\n";
                }
                Storage::disk('public')->put($path, $csv);
            } else {
                // txt or md
                $content = ($ext === 'md')
                    ? ("# Document généré\n\n- Date: " . now()->toDateTimeString() . "\n\n## Contexte\n\n" . $message)
                    : ("Document généré le " . now()->toDateTimeString() . "\n\n" . $message);
                Storage::disk('public')->put($path, $content);
            }

            $url = asset('storage/' . $path);
            return [
                'file_generation' => [
                    'type' => $ext,
                    'status' => 'completed',
                    'download_url' => $url
                ]
            ];
        } catch (\Throwable $e) {
            \Log::error('File generation failed', ['error' => $e->getMessage()]);
            return [
                'file_generation' => [
                    'type' => $ext,
                    'status' => 'failed',
                    'message' => 'Erreur lors de la génération du fichier'
                ]
            ];
        }
    }

    protected function executeImageGeneration(string $message, int $userId): array
    {
        $prompt = $this->extractImagePrompt($message);
        try {
            $b64 = $this->llm->generateImage($prompt, '1024x1024');
            if (!$b64) {
                return [
                    'image_generation' => [
                        'status' => 'failed',
                        'message' => 'Échec de génération d\'image'
                    ]
                ];
            }
            $dir = 'chat-attachments';
            $filename = 'img_' . $userId . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.png';
            $path = $dir . '/' . $filename;
            $binary = base64_decode($b64);
            Storage::disk('public')->put($path, $binary);
            $url = asset('storage/' . $path);
            return [
                'image_generation' => [
                    'status' => 'completed',
                    'prompt' => $prompt,
                    'download_url' => $url
                ]
            ];
        } catch (\Throwable $e) {
            \Log::error('Image generation error', ['error' => $e->getMessage()]);
            return [
                'image_generation' => [
                    'status' => 'failed',
                    'message' => 'Erreur lors de la génération d\'image'
                ]
            ];
        }
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

        // Add project cards
        if (isset($data['projets'])) {
            foreach ($data['projets'] as $projet) {
                $cards .= $this->createProjectCard($projet);
            }
        }

        return $response . "\n\n" . $cards;
    }

    protected function createInstitutionCard(array $institution): string
    {
        $nom = $institution['nom'] ?? ($institution['name'] ?? 'Institution');
        $telephone = $institution['telephone'] ?? ($institution['phone'] ?? 'N/A');
        $site = $institution['site_web'] ?? ($institution['website'] ?? '');
        $region = $institution['region'] ?? '';
        $ville = $institution['ville'] ?? '';

        return "\n\n:::institution\n" .
               "**{$nom}**\n\n" .
               ($institution['description'] ?? '') . "\n\n" .
               "📍 **Localisation:** {$region}" . ($ville !== '' ? ", {$ville}" : '') . "\n" .
               "📞 **Contact:** {$telephone}\n" .
               ($site !== '' ? "🌐 **Site web:** {$site}\n" : '') .
               ":::\n";
    }

    protected function createOpportunityCard(array $opportunity): string
    {
        $titre = $opportunity['titre'] ?? ($opportunity['title'] ?? 'Opportunité');
        $dateLimite = $opportunity['date_limite'] ?? ($opportunity['application_deadline'] ?? null);
        $regions = $opportunity['regions_cibles'] ?? [];

        return "\n\n:::opportunity\n" .
               "**{$titre}**\n\n" .
               ($opportunity['description'] ?? '') . "\n\n" .
               (isset($opportunity['type']) ? "💰 **Type:** {$opportunity['type']}\n" : '') .
               ($dateLimite ? "📅 **Date limite:** {$dateLimite}\n" : '') .
               (!empty($regions) ? "📍 **Régions cibles:** " . implode(', ', $regions) . "\n" : '') .
               ":::\n";
    }

    protected function createOfficialTextCard(array $text): string
    {
        $titre = $text['titre'] ?? ($text['title'] ?? 'Texte officiel');
        $resume = $text['resume'] ?? ($text['summary'] ?? '');
        $classification = $text['classification_juridique'] ?? ($text['legal_classification'] ?? '');
        $publieLe = $text['publie_le'] ?? ($text['publication_date'] ?? '');
        $statut = $text['statut'] ?? ($text['status'] ?? '');

        return "\n\n:::official-text\n" .
               "**{$titre}**\n\n" .
               $resume . "\n\n" .
               ($classification !== '' ? "📜 **Type:** {$classification}\n" : '') .
               ($publieLe !== '' ? "📅 **Date publication:** {$publieLe}\n" : '') .
               ($statut !== '' ? "⚖️ **Statut:** {$statut}\n" : '') .
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

    protected function createProjectCard(array $projet): string
    {
        $nom = $projet['nom_projet'] ?? 'Projet';
        $maturite = $projet['maturite'] ?? '';
        $region = $projet['region'] ?? '';
        $site = $projet['site_web'] ?? '';

        return "\n\n:::project\n" .
               "**{$nom}**\n\n" .
               ($projet['description'] ?? '') . "\n\n" .
               ($maturite !== '' ? "🚀 **Maturité:** {$maturite}\n" : '') .
               ($region !== '' ? "📍 **Région:** {$region}\n" : '') .
               ($site !== '' ? "🌐 **Site:** {$site}\n" : '') .
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