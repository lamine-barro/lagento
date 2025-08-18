<?php

namespace App\Agents;

use App\Models\User;
use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Models\Opportunite;
use App\Models\Institution;
use App\Models\TexteOfficiel;
use App\Services\MemoryManagerService;
use App\Services\VectorAccessService;
use App\Services\LanguageModelService;
use App\Services\EmbeddingService;
use App\Services\SemanticSearchService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class AgentPrincipal extends BaseAgent
{
    protected MemoryManagerService $memoryManager;

    public function __construct(
        ?LanguageModelService $llm = null,
        ?EmbeddingService $embedding = null,
        ?SemanticSearchService $search = null
    ) {
        // Use service container if no dependencies provided
        $llm = $llm ?: app(LanguageModelService::class);
        $embedding = $embedding ?: app(EmbeddingService::class);
        $search = $search ?: app(SemanticSearchService::class);
        
        parent::__construct($llm, $embedding, $search);
        $this->memoryManager = app(MemoryManagerService::class);
    }

    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-4.1-mini',
            'temperature' => 0.3,
            'max_tokens' => 1500,
            'tools' => [
                'gestion_base_donnees',
                'recherche_semantique', 
                'recherche_vectorielle',
                'generation_fichier',
                'generation_image'
            ]
        ];
    }

    public function execute(array $inputs): array
    {
        $startTime = microtime(true);
        $sessionId = $this->logExecutionStart($inputs);
        
        $userMessage = $inputs['user_message'] ?? '';
        $userId = $inputs['user_id'] ?? null;
        $conversationId = $inputs['conversation_id'] ?? null;

        if (!$userMessage || !$userId) {
            $result = [
                'success' => false,
                'error' => 'Message utilisateur et ID utilisateur requis'
            ];
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }

        $this->logDebug('Getting user context', ['user_id' => $userId]);
        
        // Get user context
        $userContext = $this->getUserAnalyticsContext($userId);
        
        // Prepare system instructions
        $instructions = $this->getSystemInstructions();
        $systemPrompt = $this->prepareSystemPrompt($instructions, $userContext);

        try {
            $this->logDebug('Analyzing message for tools', ['message_length' => strlen($userMessage)]);
            
            // Analyze user message to determine if tools are needed
            $toolsNeeded = $this->analyzeMessageForTools($userMessage);
            
            $this->logDebug('Tools analysis completed', ['tools_needed' => $toolsNeeded]);
            
            $toolResults = [];
            $toolUsageLogs = [];

            // Execute tools if needed
            foreach ($toolsNeeded as $tool) {
                $toolStartTime = microtime(true);
                $this->logDebug("Executing tool: {$tool}");
                
                $result = $this->executeTool($tool, $userMessage, $userId);
                
                $toolDuration = (microtime(true) - $toolStartTime) * 1000;
                
                if ($result) {
                    $toolResults[$tool] = $result;
                    $toolUsageLogs[] = $tool;
                    $this->logToolUsage($tool, [
                        'user_id' => $userId,
                        'duration_ms' => round($toolDuration, 2),
                        'result_size' => strlen(json_encode($result))
                    ]);
                } else {
                    $this->logDebug("Tool {$tool} returned no results", ['duration_ms' => round($toolDuration, 2)]);
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
            
            $this->logDebug('Preparing LLM call', [
                'config' => $config,
                'system_prompt_length' => strlen($systemPrompt)
            ]);
            
            // Injecter contexte conversationnel
            $recent = $inputs['recent_messages'] ?? [];
            $summary = trim((string)($inputs['conversation_summary'] ?? ''));
            $attachedFileContent = trim((string)($inputs['attached_file_content'] ?? ''));

            $contextBlock = '';
            if (!empty($recent)) {
                $contextBlock .= "\n\nContexte récent (4 messages):\n";
                foreach ($recent as $r) {
                    $prefix = $r['role'] === 'user' ? 'Utilisateur' : 'Assistant';
                    $contextBlock .= "- {$prefix}: " . $r['content'] . "\n";
                }
                $this->logDebug('Added recent messages context', ['recent_count' => count($recent)]);
            }
            if ($summary !== '') {
                $contextBlock .= "\nRésumé de la conversation:\n" . $summary . "\n";
                $this->logDebug('Added conversation summary', ['summary_length' => strlen($summary)]);
            }
            if ($attachedFileContent !== '') {
                $contextBlock .= "\n\nFichier attaché par l'utilisateur:\n" . $attachedFileContent . "\n";
                $this->logDebug('Added attached file content', ['content_length' => strlen($attachedFileContent)]);
            }

            $messages = $this->formatMessages($systemPrompt . $contextBlock, $userMessage);
            
            $llmStartTime = microtime(true);
            $webSearchNeeded = (bool) preg_match('/(actualité|récent|nouveau|2024|2025|prix|taux)/i', $userMessage);
            
            $this->logDebug('LLM call parameters', [
                'web_search_needed' => $webSearchNeeded,
                'user_region' => $userContext['region'] ?? 'Abidjan',
                'final_prompt_length' => strlen($systemPrompt . $contextBlock)
            ]);
            
            $response = $this->llm->chat(
                $messages,
                $config['model'],
                $config['temperature'],
                $config['max_tokens'],
                [
                    'web_search' => $webSearchNeeded,
                    'search_context_size' => 'medium',
                    'user_location' => [
                        'country' => 'CI',
                        'city' => $userContext['region'] ?? 'Abidjan',
                        'region' => $userContext['region'] ?? 'Abidjan'
                    ]
                ]
            );
            
            $this->logLLMCall($messages, $config, $llmStartTime);

            // Format response as markdown
            $formattedResponse = $this->formatMarkdownResponse($response, $toolResults);

            $result = [
                'success' => true,
                'response' => $formattedResponse,
                'tools_used' => $toolUsageLogs,
                'metadata' => [
                    'model' => $config['model'],
                    'tokens_estimated' => strlen($response) / 4, // Rough estimation
                    'tools_executed' => count($toolUsageLogs),
                    'web_search_used' => $webSearchNeeded,
                    'context_added' => !empty($recent) || !empty($summary)
                ]
            ];

            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;

        } catch (\Exception $e) {
            $result = [
                'success' => false,
                'error' => 'Erreur lors du traitement de votre demande',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ];
            
            $this->logError($e->getMessage(), [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'user_message' => $userMessage,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
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

STYLE DE RÉPONSE :
- PRIVILÉGIER la simplicité et la clarté
- Commencer TOUJOURS par une réponse directe et concise
- Utiliser un français naturel et bien formulé
- Éviter l'abus de composants visuels
- Maximiser l'impact avec un minimum d'éléments

FORMAT DE SORTIE : Markdown structuré avec composants personnalisés :

ÉLÉMENTS DE BASE (à privilégier) :
- Titres : ## (h2), ### (h3) - seulement si nécessaire
- Formatage : **gras** pour les points clés, *italique* pour l'emphase légère
- Listes : ordonnées (1.) et non-ordonnées (-) - concises
- Paragraphes courts et lisibles

RÈGLES D'USAGE DES COMPOSANTS :

ALERTES (MAXIMUM 1 par réponse) :
- :::info → Informations complémentaires importantes
- :::success → Validation d'une démarche réussie
- :::warning → Attention requise, points de vigilance
- :::danger → Risques majeurs, erreurs à éviter

CARTES PERSONNALISÉES (utiliser avec parcimonie) :
- [carte-institution:Nom|Description|Téléphone|URL] → Institutions officielles uniquement
- [carte-opportunite:Titre|Description|Échéance|URL] → Opportunités concrètes avec deadline
- [carte-texte-officiel:Référence|Description|Source|URL] → Lois, décrets, textes légaux
- [carte-partenaire:Nom projet|Description|Synergie|URL] → Partenaires stratégiques identifiés

QUAND UTILISER LES COMPOSANTS :
- Questions simples → Réponse textuelle uniquement
- Conseils généraux → Texte + 1 alerte si critique
- Opportunités spécifiques → Texte + cartes opportunités/institutions
- Aspects légaux → Texte + cartes textes officiels
- Recherche de partenaires → Texte + cartes partenaires

ÉVITER :
- Multiplication des alertes et cartes
- Composants pour des informations basiques
- Sur-structuration des réponses simples

CONTEXTE IVOIRIEN :
- Connaissance approfondie de l'écosystème entrepreneurial ivoirien
- Maîtrise des lois OHADA et réglementations locales
- Familiarité avec les institutions (CEPICI, CGECI, etc.)
- Compréhension des défis spécifiques aux entrepreneurs locaux

OUTILS ET QUAND LES UTILISER :
- gestion_base_donnees : lorsque l'utilisateur parle d'opportunités, financements, institutions, partenaires, ou de ses projets. Par défaut lecture; si l'utilisateur le demande explicitement, proposer la mise à jour du projet et/ou des analytics.
- recherche_semantique : pour les lois, réglementations, procédures OHADA, textes officiels et références légales.
- recherche_vectorielle : pour conseils personnalisés, exemples, recommandations, expériences similaires, cas d'usage. Accède aux MÉMOIRES VECTORISÉES suivantes (chaque type recherché INDÉPENDAMMENT pour garantir diversité) :
  
  * 'opportunite' (83 entrées) : Opportunités de financement, concours, subventions, fonds d'investissement CI
    → Utiliser pour : recherche de financements, bourses, concours entrepreneuriaux, levées de fonds
    → Mots-clés déclencheurs : financement, subvention, bourse, concours, capital, investissement, aide financière
    
  * 'institution' (189 entrées) : Institutions d'accompagnement entrepreneurial ivoiriennes
    → Utiliser pour : recherche d'incubateurs, accélérateurs, cabinets conseil, associations, espaces coworking
    → Mots-clés déclencheurs : accompagnement, incubateur, accélérateur, mentor, conseil, partenaire, structure
    
  * 'texte_officiel' (1765 entrées) : Corpus juridique complet OHADA et réglementation ivoirienne
    → Utiliser pour : questions légales, procédures administratives, obligations réglementaires
    → Mots-clés déclencheurs : loi, réglementation, juridique, OHADA, procédure, obligation, statut légal
    
  * 'user_project' : Projets entrepreneuriaux spécifiques de l'utilisateur (secteur, maturité, besoins)
    → Utiliser pour : analyse personnalisée du projet, recommandations contextualisées
    → Mots-clés déclencheurs : mon projet, ma startup, mon entreprise, Etudesk, analyser mon activité
    
  * 'user_analytics' : Profil entrepreneurial et diagnostic personnalisé (forces, axes progression)
    → Utiliser pour : conseils basés sur le niveau de maturité, recommandations de formation
    → Mots-clés déclencheurs : mes forces, mes faiblesses, mon profil, diagnostic, développement personnel
    
  * 'presentation' : Documentation LagentO/Horizon-O (fonctionnalités, missions, services)
    → Utiliser pour : questions sur l'outil, ses capacités, son utilisation
    → Mots-clés déclencheurs : LagentO, Agent O, fonctionnalités, comment utiliser, que peux-tu faire
    
  * 'faq' : Questions fréquentes entrepreneuriat CI et utilisation LagentO
    → Utiliser pour : réponses aux questions courantes, guides pratiques
    → Mots-clés déclencheurs : comment créer, étapes de, procédures courantes, questions fréquentes
    
  * 'timeline_gov' : Chronologie des actions gouvernementales et politiques d'appui CI
    → Utiliser pour : contexte politique économique, programmes gouvernementaux
    → Mots-clés déclencheurs : gouvernement, politique, État, programme officiel, initiatives publiques
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

        // Recherche vectorielle pour conseils/exemples/recommandations + mots-clés spécifiques
        if (preg_match('/(conseil|exemple|recommandation|similaire|expérience|comment|aide|inspiration|référence|cas|financement|subvention|bourse|concours|capital|investissement|accompagnement|incubateur|accélérateur|mentor|partenaire|forces|faiblesses|profil|diagnostic)/i', $message)) {
            $tools[] = 'recherche_vectorielle';
        }

        // Recherche vectorielle pour les projets/entreprises spécifiques (noms propres, descriptions, analyses)
        if (preg_match('/(projet|entreprise|startup|etudesk|business|mon projet|ma startup|décris|décrire|présente|analyser|mon entreprise|mon activité)/i', $message)) {
            $tools[] = 'recherche_vectorielle';
            // Aussi chercher dans la base de données pour les projets
            $tools[] = 'gestion_base_donnees';
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

    protected function executeTool(string $tool, string $message, string $userId): ?array
    {
        switch ($tool) {
            case 'gestion_base_donnees':
                return $this->executeDatabase($message, $userId);
            
            case 'recherche_semantique':
                return $this->executeSemanticSearch($message);
            
            case 'recherche_vectorielle':
                return $this->executeVectorSearch($message, $userId);
            
            case 'generation_fichier':
                return $this->executeFileGeneration($message, $userId);
            
            case 'generation_image':
                return $this->executeImageGeneration($message, $userId);
            
            default:
                return null;
        }
    }

    protected function executeDatabase(string $message, string $userId): array
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

    /**
     * Execute vector search across all memories
     */
    protected function executeVectorSearch(string $message, string $userId): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return ['error' => 'Utilisateur non trouvé'];
            }

            // Use VectorAccessService for access-controlled search
            $vectorAccessService = app(VectorAccessService::class);
            
            // Determine relevant memory types based on message content  
            $relevantTypes = $this->determineRelevantMemoryTypes($message);
            
            // Perform access-controlled semantic search
            $results = $vectorAccessService->searchWithAccess(
                $message,
                $user,
                $relevantTypes,
                8 // Limit results
            );

            $accessSummary = $vectorAccessService->getAccessSummary($user);
            
            Log::info('Vector search executed with access control', [
                'user_id' => $userId,
                'access_level' => $accessSummary['access_level'],
                'requested_types' => $relevantTypes,
                'accessible_types' => $accessSummary['accessible_types'],
                'results_count' => count($results),
                'query_preview' => substr($message, 0, 100)
            ]);

            return [
                'vector_results' => $results,
                'searched_types' => array_intersect($relevantTypes, $accessSummary['accessible_types']),
                'access_level' => $accessSummary['access_level'],
                'total_accessible_chunks' => $accessSummary['total_accessible_chunks']
            ];
            
        } catch (\Exception $e) {
            Log::error('Vector search error', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'message' => substr($message, 0, 100)
            ]);
            return [];
        }
    }

    /**
     * Determine relevant memory types based on message content
     */
    private function determineRelevantMemoryTypes(string $message): array
    {
        $message = strtolower($message);
        $types = [];

        // Always include core memories for general queries
        $types[] = 'presentation'; // LagentO info

        // OPPORTUNITÉ : financement, bourses, concours, investissement
        if (preg_match('/(opportunité|financement|bourse|subvention|concours|fonds|capital|investissement|levée|aide financière|crowdfunding|startup boost)/i', $message)) {
            $types[] = 'opportunite';
        }

        // TEXTE_OFFICIEL : lois, réglementation, OHADA, procédures
        if (preg_match('/(loi|réglementation|texte|officiel|juridique|ohada|procédure|obligation|statut légal|décret|arrêté|formalisation)/i', $message)) {
            $types[] = 'texte_officiel';
        }

        // INSTITUTION : accompagnement, incubateurs, mentors, conseils
        if (preg_match('/(institution|organisme|structure|accompagnement|incubateur|accélérateur|cabinet|conseil|association|partenaire|mentor|coworking|coaching)/i', $message)) {
            $types[] = 'institution';
        }

        // TIMELINE_GOV : gouvernement, politiques, programmes officiels
        if (preg_match('/(gouvernement|état|politique|timeline|action|programme officiel|initiative publique|ministère|cepici|cgeci)/i', $message)) {
            $types[] = 'timeline_gov';
        }

        // USER_PROJECT : projets personnels, startup, entreprise spécifique
        if (preg_match('/(projet|entreprise|startup|business|entrepreneuriat|etudesk|décris|décrire|présente|analyser|mon projet|ma startup|mon entreprise|mon activité)/i', $message)) {
            $types[] = 'user_project';
        }
        
        // USER_ANALYTICS : profil personnel, diagnostic, forces/faiblesses
        if (preg_match('/(mes forces|mes faiblesses|mon profil|diagnostic|développement personnel|mes compétences|mon niveau|ma maturité|axes progression)/i', $message)) {
            $types[] = 'user_analytics';
        }
        
        // Inclure user_analytics aussi pour les questions de projet (souvent liées)
        if (in_array('user_project', $types)) {
            $types[] = 'user_analytics';
        }

        // FAQ : questions courantes, guides pratiques
        if (preg_match('/(comment créer|étapes de|procédures courantes|questions fréquentes|guide|comment faire|tutoriel)/i', $message)) {
            $types[] = 'faq';
        }
        
        // PRESENTATION : fonctionnalités LagentO, utilisation de l'outil
        if (preg_match('/(lagento|agent o|fonctionnalités|comment utiliser|que peux-tu faire|tes capacités|horizon-o)/i', $message)) {
            // Remove default presentation if specific LagentO question
            $types = array_diff($types, ['presentation']);
            $types[] = 'presentation';
        }

        if (preg_match('/(document|fichier|upload|pdf)/i', $message)) {
            $types[] = 'documents';
            $types[] = 'attachments';
        }

        if (preg_match('/(conversation|historique|contexte|précédent)/i', $message)) {
            $types[] = 'conversations';
        }

        // If no specific types detected, search all
        if (empty($types) || count($types) == 1) {
            $types = [
                'presentation',
                'opportunite',
                'institution',
                'user_project',
                'user_analytics'
            ];
        }

        return array_unique($types);
    }

    protected function executeFileGeneration(string $message, string $userId): array
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

    protected function executeImageGeneration(string $message, string $userId): array
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

    protected function updateUserAnalytics(string $userId, array $toolsUsed): void
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
        
        // Add custom cards for vector search results
        if (isset($toolResults['recherche_vectorielle'])) {
            $formattedResponse = $this->appendVectorCards($formattedResponse, $toolResults['recherche_vectorielle']);
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

    /**
     * Append vector search results as custom cards
     */
    protected function appendVectorCards(string $response, array $vectorResults): string
    {
        if (!isset($vectorResults['vector_results']) || empty($vectorResults['vector_results'])) {
            return $response;
        }

        $cards = "";
        $processedEntities = []; // Éviter les doublons

        foreach ($vectorResults['vector_results'] as $result) {
            $memoryType = $result['memory_type'];
            $content = $result['content'];
            $metadata = $result['metadata'] ?? [];

            // Créer des cartes selon le type de mémoire
            switch ($memoryType) {
                case 'opportunite':
                    $card = $this->createOpportunityCardFromVector($content, $metadata);
                    if ($card && !in_array($card, $processedEntities)) {
                        $cards .= $card;
                        $processedEntities[] = $card;
                    }
                    break;

                case 'institution':
                    $card = $this->createInstitutionCardFromVector($content, $metadata);
                    if ($card && !in_array($card, $processedEntities)) {
                        $cards .= $card;
                        $processedEntities[] = $card;
                    }
                    break;

                case 'texte_officiel':
                    $card = $this->createOfficialTextCardFromVector($content, $metadata);
                    if ($card && !in_array($card, $processedEntities)) {
                        $cards .= $card;
                        $processedEntities[] = $card;
                    }
                    break;

                case 'user_project':
                    $card = $this->createProjectCardFromVector($content, $metadata);
                    if ($card && !in_array($card, $processedEntities)) {
                        $cards .= $card;
                        $processedEntities[] = $card;
                    }
                    break;

                // Les user_analytics, presentation, faq ne nécessitent pas de cartes
                // car elles sont déjà intégrées dans la réponse textuelle
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

    /**
     * Create opportunity card from vector content
     */
    protected function createOpportunityCardFromVector(string $content, array $metadata): ?string
    {
        // Extraire les informations du contenu vectorisé
        $titre = '';
        $description = '';
        $type = $metadata['type'] ?? '';
        $deadline = $metadata['deadline'] ?? '';
        
        // Parser le contenu pour extraire titre et description
        if (preg_match('/Titre:\s*([^\n]+)/i', $content, $matches)) {
            $titre = trim($matches[1]);
        }
        
        if (preg_match('/Description:\s*([^\n]+)/i', $content, $matches)) {
            $description = trim($matches[1]);
        }
        
        if (empty($titre)) {
            return null; // Pas assez d'informations
        }

        return "\n\n[carte-opportunite:{$titre}|{$description}|{$deadline}|]\n";
    }

    /**
     * Create institution card from vector content
     */
    protected function createInstitutionCardFromVector(string $content, array $metadata): ?string
    {
        $nom = '';
        $description = '';
        $contact = '';
        $site = '';
        
        // Parser le contenu vectorisé
        if (preg_match('/Nom:\s*([^\n]+)/i', $content, $matches)) {
            $nom = trim($matches[1]);
        }
        
        if (preg_match('/Description:\s*([^\n]+)/i', $content, $matches)) {
            $description = trim($matches[1]);
        }
        
        if (preg_match('/Contact:\s*([^\n]+)/i', $content, $matches)) {
            $contact = trim($matches[1]);
        }
        
        if (preg_match('/Site web:\s*([^\n]+)/i', $content, $matches)) {
            $site = trim($matches[1]);
        }
        
        if (empty($nom)) {
            return null;
        }

        return "\n\n[carte-institution:{$nom}|{$description}|{$contact}|{$site}]\n";
    }

    /**
     * Create official text card from vector content
     */
    protected function createOfficialTextCardFromVector(string $content, array $metadata): ?string
    {
        $titre = '';
        $description = '';
        $classification = $metadata['classification'] ?? '';
        $source = 'Textes officiels CI';
        
        if (preg_match('/Titre:\s*([^\n]+)/i', $content, $matches)) {
            $titre = trim($matches[1]);
        }
        
        if (preg_match('/Résumé:\s*([^\n]+)/i', $content, $matches)) {
            $description = trim($matches[1]);
        } elseif (preg_match('/Classification:\s*([^\n]+)/i', $content, $matches)) {
            $description = "Texte " . trim($matches[1]);
        }
        
        if (empty($titre)) {
            return null;
        }

        return "\n\n[carte-texte-officiel:{$titre}|{$description}|{$source}|]\n";
    }

    /**
     * Create project card from vector content
     */
    protected function createProjectCardFromVector(string $content, array $metadata): ?string
    {
        $nom = '';
        $description = '';
        $synergie = 'Projet entrepreneurial similaire';
        
        if (preg_match('/Nom:\s*([^\n]+)/i', $content, $matches)) {
            $nom = trim($matches[1]);
        }
        
        if (preg_match('/Description:\s*([^\n]+)/i', $content, $matches)) {
            $description = trim($matches[1]);
        }
        
        if (empty($nom)) {
            return null;
        }

        return "\n\n[carte-partenaire:{$nom}|{$description}|{$synergie}|]\n";
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