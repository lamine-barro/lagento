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
            'max_tokens' => 2500,
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
- Format compact optimisé pour mobile
- Interlignes serrés, pas d'espaces excessifs

FORMAT DE SORTIE : Markdown propre et compact :

ÉLÉMENTS DE BASE (seuls autorisés) :
- Titres : ## (h2), ### (h3) - seulement si nécessaire, collés au contenu
- Formatage : **gras** pour les points clés, *italique* pour l'emphase légère
- Listes : ordonnées (1.) et non-ordonnées (-) - format serré, pas d'espaces
- Paragraphes courts et lisibles avec interlignes compacts

COMPOSANTS AUTORISÉS :

ALERTES UNIQUEMENT (MAXIMUM 1 par réponse) :
- :::info → Informations complémentaires importantes
- :::success → Validation d'une démarche réussie
- :::warning → Attention requise, points de vigilance
- :::danger → Risques majeurs, erreurs à éviter

LIENS ET URLs (OBLIGATOIRE si disponible) :
- Pour CHAQUE opportunité mentionnée : inclure [Voir détails](url){target=\"_blank\"} si URL existe
- Pour CHAQUE institution mentionnée : inclure [Site web](url){target=\"_blank\"} si URL existe
- Format markdown : [texte du lien](url){target=\"_blank\"}
- TOUJOURS utiliser target=\"_blank\" pour ouvrir dans nouvel onglet
- NE JAMAIS inventer d'URLs - utiliser UNIQUEMENT celles retournées par les outils

INTERDICTIONS STRICTES :
- AUCUNE carte personnalisée (opportunités, institutions, textes officiels)
- AUCUN composant [carte-*] dans les réponses
- AUCUN espacement excessif entre éléments
- AUCUNE sur-structuration
- AUCUNE URL inventée ou fictive

FORMATAGE MOBILE :
- Listes avec espacement minimal
- Titres collés au contenu (pas de grandes marges)
- Paragraphes compacts mais lisibles
- Structure claire sans fioritures

CONTEXTE IVOIRIEN :
- Connaissance approfondie de l'écosystème entrepreneurial ivoirien
- Maîtrise des lois OHADA et réglementations locales
- Familiarité avec les institutions (CEPICI, CGECI, etc.)
- Compréhension des défis spécifiques aux entrepreneurs locaux

OUTILS ET QUAND LES UTILISER :
- gestion_base_donnees : Recherche dans les DONNÉES RÉELLES de la plateforme (max 8 résultats par requête) :
  * 77 opportunités réelles importées avec secteurs/régions/URLs
  * Projets utilisateur avec statut public/vérifié uniquement 
  * Institutions partenaires existantes
  → IMPORTANT: Utilise UNIQUEMENT les donnees retournees par cet outil, AUCUNE invention
  → FORMAT OBLIGATOIRE: Pour chaque opportunité/institution avec URL, ajouter [Voir détails](url){target=\"_blank\"}

- recherche_semantique : Recherche dans textes officiels juridiques pour questions OHADA/légales
  
- recherche_vectorielle : Accès aux MÉMOIRES VECTORISÉES suivantes (max 8 chunks pertinents total) :
  
  **MÉMOIRES ACCESSIBLES À TOUS UTILISATEURS :**
  * 'lagento_context' : Corpus vectorisé 173/177 chunks (3.3MB) - Contexte complet Agent O
    → Conseils entrepreneuriaux, meilleures pratiques, exemples concrets CI
    → REGLE : Utilise UNIQUEMENT les chunks retournes, AUCUNE extrapolation
    
  * 'opportunite' : Opportunités vectorisées (backup des 77 en base)
    → Recherche sémantique dans descriptions d'opportunités
    
  * 'institution' : Institutions d'accompagnement CI vectorisées
    → Incubateurs, accélérateurs, structures d'appui réelles
    
  * 'user_project' : Projet spécifique de CET utilisateur seulement
    → Analyse personnalisée du projet Etudesk ou autre
    
  * 'user_analytics' : Diagnostic personnalisé de CET utilisateur
    → Forces, axes progression, recommandations personnelles
    
  **MÉMOIRES DESCRIPTIVES (contexte général) :**
  * 'presentation' : Documentation Agent O/Horizon-O
  * 'faq' : Questions fréquentes utilisation
  * 'timeline_gov' : Actions gouvernementales CI

  **RÈGLES STRICTES RAG :**
  - Retourne UNIQUEMENT le top 8 des chunks les plus pertinents
  - AUCUNE invention d'opportunites, institutions ou donnees
  - Si pas de resultats RAG -> dire 'aucune donnee disponible sur ce sujet'
  - Citer uniquement les sources trouvees dans les chunks
  
  **PRÉSENTATION DES RÉSULTATS (OBLIGATOIRE) :**
  Pour CHAQUE opportunité dans gestion_base_donnees->opportunities :
  1. Regarde le champ 'lien_externe' dans les données
  2. Si lien_externe existe et non vide : **{titre}** - {description}. [Voir détails](https://{lien_externe}){target=\"_blank\"}
  3. Si lien_externe vide ou null : **{titre}** - {description}
  
  EXEMPLES CONCRETS :
  - Données: {\"titre\":\"Orange Corners CI\",\"lien_externe\":\"orangecorners.com\"}
    → **Orange Corners CI** - [Description]. [Voir détails](https://orangecorners.com){target=\"_blank\"}
  - Données: {\"titre\":\"Programme X\",\"lien_externe\":null}
    → **Programme X** - [Description]
  
  RÈGLE ABSOLUE : Utilise EXACTEMENT la valeur de lien_externe, ajoute juste https:// devant
- generation_fichier (docx, csv, txt, md) : quand un document est demande (business plan, CV, rapport, plan, resume). Tu renvoies uniquement le lien de telechargement fourni par l'outil.
- generation_image : pour logos/visuels/maquettes. Utilise exclusivement gpt-image-1. Tu renvoies uniquement le lien de telechargement fourni par l'outil.
- web search (integre au modele) : si besoin d'actualites/informations recentes (mots-cles : actualite, recent, 2024, 2025, prix, taux). Le modele l'activera automatiquement.

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

        // Base de données pour opportunités/institutions - toujours chercher les opportunités
        if (preg_match('/(opportunité|financement|subvention|incubateur|partenaire|institution|cherche|trouve|recherche|aide|besoin)/i', $message) || 
            preg_match('/(opportunites?|chance|possibilite|option)/i', $message)) {
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

        // Opportunités - recherche améliorée avec filtres multiples
        $keyword = $this->extractKeywords($message);
        
        $opportunitiesQuery = Opportunite::query();
        
        // Détecter les requêtes générales sur les opportunités
        $isGeneralOpportunityQuery = preg_match('/(opportunité|opportunites?|financement|aide|subvention|fond|capital)/i', $message);
        
        // Détecter la localisation (Abidjan, régions, etc.)
        $location = $this->extractLocation($message);
        if (!empty($location)) {
            if ($location === 'Abidjan') {
                // Chercher opportunités disponibles à Abidjan (National ou région Abidjan)
                $opportunitiesQuery->where(function($query) {
                    $query->whereJsonContains('regions_cibles', 'National')
                          ->orWhereJsonContains('regions_cibles', 'Abidjan')
                          ->orWhere('ville', 'Abidjan');
                });
            } else {
                // Autres régions
                $opportunitiesQuery->whereJsonContains('regions_cibles', $location);
            }
        }
        
        // Si c'est une recherche générale d'opportunités sans localisation spécifique, montrer toutes
        if ($isGeneralOpportunityQuery && empty($location)) {
            // Pas de filtre supplémentaire, montrer toutes les opportunités
        }
        
        // Recherche par mots-clés spécifiques (autres que "opportunites")
        if (!empty($keyword) && !preg_match('/(opportunité|opportunites?)/i', $keyword)) {
            $opportunitiesQuery->where(function($query) use ($keyword) {
                $query->where('titre', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%')
                      ->orWhere('type', 'like', '%' . $keyword . '%');
            });
        }
        
        // Filtrer par secteur si détecté dans le message
        $secteur = $this->extractSector($message);
        if (!empty($secteur)) {
            $opportunitiesQuery->whereJsonContains('secteurs', $secteur);
        }
        
        // Filtrer les opportunités ouvertes en priorité
        $opportunitiesQuery->orderByRaw("CASE WHEN statut = 'ouvert' THEN 1 WHEN statut = 'en_cours' THEN 2 ELSE 3 END");
        
        $opportunities = $opportunitiesQuery->limit(8)->get();

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

        // LAGENTO_CONTEXT : Toujours inclure - corpus principal avec conseils entrepreneuriaux
        $types[] = 'lagento_context';

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

        // Pour les questions spécifiques sur l'outil Agent O
        if (preg_match('/(lagento|agent o|fonctionnalités|comment utiliser|que peux-tu faire|tes capacités|horizon-o)/i', $message)) {
            // Lagento_context contient déjà ces infos, pas besoin de presentation séparé
        }

        // OHADA/juridique : rechercher dans lagento_context pour conseils sur formalisation
        if (preg_match('/(loi|réglementation|ohada|juridique|formalisation|procédure|statut)/i', $message)) {
            // lagento_context déjà inclus
        }

        // Questions générales entrepreneuriales : lagento_context déjà inclus
        
        // Si aucun type spécifique détecté, utiliser les types disponibles
        if (count($types) == 1) { // Seulement lagento_context
            $types = [
                'lagento_context',
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

    protected function extractLocation(string $message): string
    {
        $message = strtolower($message);
        
        // Vérifier les principales villes/régions de Côte d'Ivoire
        $locations = [
            'abidjan' => 'Abidjan',
            'yamoussoukro' => 'Yamoussoukro', 
            'bouake' => 'Bouaké',
            'daloa' => 'Daloa',
            'korhogo' => 'Korhogo',
            'san pedro' => 'San Pedro',
            'man' => 'Man',
            'bassam' => 'Grand-Bassam',
            'national' => 'National'
        ];
        
        foreach ($locations as $search => $region) {
            if (strpos($message, $search) !== false) {
                return $region;
            }
        }
        
        return '';
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
        // Only preserve alert components, remove all custom cards (opportunities, institutions, official texts)
        $formattedResponse = $response;

        // Ensure proper markdown structure with compact formatting
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
        $url = $opportunity['lien_externe'] ?? '';

        // Utiliser le format carte personnalisée pour contrôler l'affichage du bouton Detail
        if (!empty($url) && strlen(trim($url)) > 0) {
            return "\n\n[carte-opportunite:{$titre}|" . ($opportunity['description'] ?? '') . "|" . ($dateLimite ?? 'Non définie') . "|{$url}]\n";
        } else {
            // Format standard sans bouton Detail
            return "\n\n:::opportunity\n" .
                   "**{$titre}**\n\n" .
                   ($opportunity['description'] ?? '') . "\n\n" .
                   (isset($opportunity['type']) ? "💰 **Type:** {$opportunity['type']}\n" : '') .
                   ($dateLimite ? "📅 **Date limite:** {$dateLimite}\n" : '') .
                   (!empty($regions) ? "📍 **Régions cibles:** " . implode(', ', $regions) . "\n" : '') .
                   ":::\n";
        }
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
        $url = $metadata['url'] ?? '';
        
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

        // Seulement inclure l'URL si elle existe pour afficher le bouton Detail
        if (!empty($url)) {
            return "\n\n[carte-opportunite:{$titre}|{$description}|{$deadline}|{$url}]\n";
        } else {
            return "\n\n[carte-opportunite:{$titre}|{$description}|{$deadline}|]\n";
        }
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
        // Compact formatting for mobile - reduce excessive line breaks
        $content = preg_replace('/\n\n\n+/', "\n\n", $content);
        
        // Compact spacing for headers - no extra line before headers
        $content = preg_replace('/\n\n+(#{1,6})\s/', "\n\n$1 ", $content);
        
        // Ensure proper list formatting with tight spacing
        $content = preg_replace('/\n\n([*-])\s/', "\n$1 ", $content);
        $content = preg_replace('/\n\n(\d+\.)\s/', "\n$1 ", $content);
        
        // Remove excessive spacing between list items
        $content = preg_replace('/([*-] .+)\n\n([*-] )/', '$1' . "\n" . '$2', $content);
        $content = preg_replace('/(\d+\. .+)\n\n(\d+\. )/', '$1' . "\n" . '$2', $content);
        
        return trim($content);
    }
}