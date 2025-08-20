<?php

namespace App\Agents;

use App\Models\User;
use App\Models\Document;
use App\Services\LanguageModelService;
use App\Services\OpenAIVectorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class AgentPrincipal extends BaseAgent
{

    public function __construct(
        ?LanguageModelService $llm = null,
        ?OpenAIVectorService $embedding = null
    ) {
        // Use service container if no dependencies provided
        $llm = $llm ?: app(LanguageModelService::class);
        $embedding = $embedding ?: app(OpenAIVectorService::class);
        
        parent::__construct($llm, $embedding, null);
    }

    protected function getConfig(): array
    {
        return [
            'model' => 'gpt-5-mini',
            'temperature' => 0.3,
            'max_tokens' => 5000,
            'tools' => [
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

        // Check weekly message rate limit
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->canUseFeature('messages')) {
            $remaining = $user ? $user->getRemainingUsage('messages') : 0;
            $result = [
                'success' => false,
                'error' => "Limite hebdomadaire de messages atteinte (100 par semaine). Il vous reste {$remaining} messages."
            ];
            $this->logExecutionEnd($sessionId, $result, $startTime);
            return $result;
        }

        $this->logDebug('Getting user context', ['user_id' => $userId]);
        
        // Get user context
        $userContext = $this->getUserAnalyticsContext($userId);
        
        // Get user documents context
        $userDocuments = $this->getUserDocumentsContext($userId);
        
        // Prepare system instructions
        $instructions = $this->getSystemInstructions();
        $systemPrompt = $this->prepareSystemPrompt($instructions, $userContext);
        
        // Add user documents to context if available
        if (!empty($userDocuments)) {
            $systemPrompt .= "\n\nDOCUMENTS UTILISATEUR :\n" . $userDocuments;
        }

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
            $webSearchNeeded = (bool) preg_match('/(actualité|récent|nouveau|2024|2025|taux)/i', $userMessage);
            
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

            // Use message count for successful execution
            $user->useFeature('messages');

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
        return "Tu es l'Agent O, l'assistant IA dédié aux entrepreneurs ivoiriens. Tu es disponible 24/7 pour accompagner leur parcours entrepreneurial.

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

FORMAT DE SORTIE : Markdown propre et compact en 900 mots maximum:

ÉLÉMENTS DE BASE (seuls autorisés) :
- Titres : ## (h2), ### (h3) - seulement si nécessaire, collés au contenu
- Formatage : **gras** pour les points clés, *italique* pour l'emphase légère
- Listes : ordonnées (1.) et non-ordonnées (-) - format serré, pas d'espaces
- Paragraphes courts et lisibles avec interlignes compacts

LIENS ET URLs (OBLIGATOIRE) :
- Pour CHAQUE opportunité mentionnée : TOUJOURS inclure [Voir détails](url){target=\"_blank\"} si URL existe dans les données
- Pour CHAQUE institution mentionnée : TOUJOURS inclure [Site web](url){target=\"_blank\"} si URL existe
- Format markdown : [texte du lien](url){target=\"_blank\"}
- TOUJOURS utiliser target=\"_blank\" pour ouvrir dans nouvel onglet
- NE JAMAIS inventer d'URLs - utiliser UNIQUEMENT celles retournées par les outils
- Si pas d'URL disponible pour une opportunité, ne pas mentionner de lien

INTERDICTIONS STRICTES :
- AUCUN espacement excessif entre éléments
- AUCUNE sur-structuration
- AUCUNE URL inventée ou fictive
- NE JAMAIS mentionner les sources RAG ou la recherche vectorielle
- NE JAMAIS dire \"selon les données\" ou \"d'après les informations trouvées\"
- NE JAMAIS mentionner les montants, sommes d'argent, ou valeurs financières des opportunités

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

DOCUMENTS UTILISATEUR :
- Tu as accès aux fichiers uploadés par l'utilisateur (nom, résumé, tags, type)
- Utilise ces informations pour personnaliser tes réponses
- Référence les documents pertinents quand approprié
- Ne jamais inventer le contenu des documents

INSTITUTIONS LOCALES :
- Tu as accès aux institutions d'accompagnement de la région de l'utilisateur
- Utilise les coordonnées réelles pour faciliter les contacts
- Oriente vers les services appropriés selon le profil entrepreneurial


OUTILS ET QUAND LES UTILISER :

- recherche_vectorielle : Recherche dans les mémoires vectorisées (max 8 chunks pertinents) :
  
  **MÉMOIRES DE BASE (TOUJOURS INCLUSES) :**
  * 'lagento_context' : Corpus principal Agent O avec conseils entrepreneuriaux, textes officiels (Ohada, code du travail, etc.), FAQ, institutions (ministères, incubateurs, accélérateurs, investisseurs, etc.) avec leurs services, la presentation du Président de la Republique Alassane Ouattara, les initiatives gouvernementale pour la jeunesse et les entrepreneurs.
  * 'user_project' : Projets spécifiques de l'utilisateur uniquement  
  * 'user_analytics' : Diagnostic personnalisé de l'utilisateur uniquement
  
  **MÉMOIRES ADDITIONNELLES (selon demande) :**
  * 'opportunites' : Recherche d'opportunités d'accompagnement (namespace: 'opportunites') - 77 opportunités réelles disponibles de Septembre 2025 à Décembre 2025 (concours, hackathon, incubation, accélération, formation, etc.)
  * 'conversation' : Recherche dans l'historique des conversations (si besoin)
  
  **RÈGLES :**
  - Utilise UNIQUEMENT les chunks retournés par la recherche
  - Si aucun résultat pertinent -> dire 'aucune donnée disponible'
  - Cite les sources trouvées dans les résultats
  
  **PRÉSENTATION DES OPPORTUNITÉS (OBLIGATOIRE) :**
  Pour CHAQUE opportunité trouvée :
  1. Regarde le champ 'lien_externe' dans les données
  2. Si lien_externe existe et non vide : **{titre}** - {description}. [Voir détails](https://{lien_externe}){target=\"_blank\"}
  3. Si lien_externe vide ou null : **{titre}** - {description}
  
  EXEMPLES CONCRETS :
  - Données: {\"titre\":\"Orange Corners CI\",\"lien_externe\":\"orangecorners.com\"}
    → **Orange Corners CI** - [Description]. [Voir détails](https://orangecorners.com){target=\"_blank\"}
  - Données: {\"titre\":\"Programme X\",\"lien_externe\":null}
    → **Programme X** - [Description]
  
  **PRÉSENTATION DES OPPORTUNITÉS (OBLIGATOIRE) :**
  Pour CHAQUE opportunité trouvée :
  1. **{titre}** ({type}) - {description}
  2. Si lien_externe existe : [Voir détails](https://{lien_externe}){target=\"_blank\"}
  3. Si date_limite existe : Date limite: {date_limite}
  
  **PRÉSENTATION DES INSTITUTIONS (OBLIGATOIRE) :**
  Pour CHAQUE institution trouvée :
  1. **{nom}** ({type}) - {description}
  2. Si site_web existe : [Site web](https://{site_web}){target=\"_blank\"}
  3. Si telephone existe : Tel: {telephone}
  4. Région: {region}
  
  RÈGLE ABSOLUE : Utilise EXACTEMENT les valeurs des champs, ajoute juste https:// devant les URLs
  
**UTILISATION SELECTIVE DES OUTILS :**

RÈGLE OPTIMISATION : N'utilise QUE les outils STRICTEMENT nécessaires pour la demande. Évite d'exécuter tous les outils par défaut.

- generation_fichier : UNIQUEMENT si l'utilisateur demande explicitement un document (business plan, CV, rapport, contrat, etc.). 
- generation_image : UNIQUEMENT si l'utilisateur demande explicitement un visuel (logo, affiche, bannière, illustration, etc.). 
- web search (intégré) : UNIQUEMENT si besoin d'actualités/infos récentes (2024, 2025, taux actuels, etc.).

**STRATÉGIE DE DÉCOUPAGE :**
Pour des demandes complexes multi-étapes :
1. Traiter d'abord la partie conseil/information avec recherche vectorielle
2. Proposer ensuite les étapes suivantes (génération de documents/images)
3. Demander confirmation avant d'exécuter les outils coûteux

STYLE :
- Bienveillant et encourageant
- Pragmatique et actionnable
- Respectueux de la culture ivoirienne
- Éviter le jargon technique excessif";
    }

    protected function analyzeMessageForTools(string $message): array
    {
        $tools = [];
        $messageLower = strtolower($message);
        
        // Détecter si le message nécessite des outils (recherche, génération, etc.)
        $needsVectorSearch = $this->messageNeedsVectorSearch($message, $messageLower);
        
        if ($needsVectorSearch) {
            $tools[] = 'recherche_vectorielle';
        }
        
        // Génération de fichier si des mots-clés spécifiques sont détectés
        $fileKeywords = [
            'docx', 'document', 'fichier', 'rapport', 'business plan', 'executive summary',
            'cv', 'lettre', 'contrat', 'pdf', 'word', 'excel', 'genere', 'generer', 
            'crée', 'creer', 'rédige', 'rediger', 'ecrire', 'écrit'
        ];
        
        foreach ($fileKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                $tools[] = 'generation_fichier';
                break;
            }
        }
        
        // Génération d'image UNIQUEMENT si explicitement demandée
        $imageKeywords = [
            'logo', 'image', 'photo', 'dessin', 'illustration', 'banner', 'bannière',
            'affiche', 'visual', 'graphic', 'design', 'schéma', 'schema', 'diagramme'
        ];
        
        foreach ($imageKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                $tools[] = 'generation_image';
                break;
            }
        }
        
        return $tools;
    }

    protected function messageNeedsVectorSearch(string $message, string $messageLower): bool
    {
        // Debug log pour voir le message analysé
        $this->logDebug('Analyzing message for vector search', [
            'message' => $message,
            'length' => strlen($message)
        ]);
        
        // Messages très courts (< 15 caractères) sont probablement simples
        if (strlen(trim($message)) < 15) {
            $this->logDebug('Message too short, skipping vector search');
            return false;
        }
        
        // Messages conversationnels simples - pas besoin de recherche
        $conversationalPatterns = [
            // Salutations seules
            '/^(bonjour|salut|hello|hi|bonsoir|hey)\s*[!?]?$/i',
            
            // Salutations avec questions simples
            '/^(bonjour|salut|hello|hi|bonsoir)\s+(comment tu vas|comment ça va|ça va|comment allez-vous)\s*[?!]?$/i',
            
            // Questions simples seules
            '/^(comment tu vas|comment ça va|ça va|comment allez-vous)\s*[?!]?$/i',
            
            // Remerciements et politesse
            '/^(merci|merci beaucoup|thanks|thank you)\s*[!]?$/i',
            '/^(au revoir|à bientôt|bye|goodbye)\s*[!]?$/i',
            
            // Réponses courtes
            '/^(oui|non|ok|d\'accord|parfait|bien|super|génial)\s*[!]?$/i',
            '/^(je comprends|compris|ça marche|très bien)\s*[!]?$/i',
        ];
        
        foreach ($conversationalPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                $this->logDebug('Message matches conversational pattern, skipping vector search', ['pattern' => $pattern]);
                return false;
            }
        }
        
        // Détecter si le message demande des informations spécifiques
        $searchIndicators = [
            // Questions sur des sujets spécifiques
            'qu\'est-ce que', 'qu\'est ce que', 'c\'est quoi', 'c est quoi',
            'comment', 'pourquoi', 'où', 'quand', 'qui', 'quel', 'quelle',
            
            // Recherche d'informations
            'opportunité', 'projet', 'financement', 'subvention', 'aide',
            'institution', 'organisation', 'entreprise', 'startup',
            'formation', 'éducation', 'école', 'université',
            'gouvernement', 'ministère', 'politique', 'loi',
            
            // Demandes d'aide/conseil
            'aide', 'aidez', 'conseille', 'recommande', 'suggère',
            'peux-tu', 'pouvez-vous', 'pourrais-tu',
            
            // Recherche géographique
            'côte d\'ivoire', 'abidjan', 'bouaké', 'yamoussoukro',
            'afrique', 'ivoirien', 'ivoirienne',
        ];
        
        foreach ($searchIndicators as $indicator) {
            if (strpos($messageLower, $indicator) !== false) {
                return true;
            }
        }
        
        // Si le message contient des mots interrogatifs, il nécessite probablement une recherche
        $questionWords = ['?', 'comment', 'pourquoi', 'où', 'quand', 'qui', 'que', 'quel'];
        foreach ($questionWords as $word) {
            if (strpos($messageLower, $word) !== false) {
                return true;
            }
        }
        
        // Messages longs (> 100 caractères) nécessitent généralement une recherche
        if (strlen($message) > 100) {
            return true;
        }
        
        // Par défaut, les messages courts sans indicateurs spécifiques n'ont pas besoin de recherche
        $this->logDebug('No specific indicators found, skipping vector search');
        return false;
    }

    protected function executeTool(string $tool, string $message, string $userId): ?array
    {
        switch ($tool) {
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

            // Détermine les types de mémoires pertinents
            $relevantTypes = $this->determineRelevantMemoryTypes($message);
            
            // Recherche vectorielle via OpenAIVectorService et Pinecone
            $allResults = [];
            
            // Recherche dans le contexte LagentO (TOUJOURS inclus)
            $contextResults = $this->embedding->searchSimilar(
                query: $message,
                topK: 4,
                filter: [],
                namespace: 'lagento_context'
            );
            $allResults = array_merge($allResults, $contextResults);
            
            // Recherche dans les diagnostics utilisateur
            $diagnosticResults = $this->embedding->searchSimilar(
                query: $message,
                topK: 2,
                filter: ['user_id' => $userId],
                namespace: 'user_diagnostics'
            );
            $allResults = array_merge($allResults, $diagnosticResults);
            
            // Recherche dans les opportunités (namespace global)
            $opportunityResults = $this->embedding->searchSimilar(
                query: $message,
                topK: 2,
                filter: [],
                namespace: 'opportunites'
            );
            $allResults = array_merge($allResults, $opportunityResults);
            
            $results = $allResults;
            
            Log::info('Vector search executed', [
                'user_id' => $userId,
                'searched_types' => $relevantTypes,
                'results_count' => count($results),
                'query_preview' => substr($message, 0, 100)
            ]);

            return [
                'vector_results' => $results,
                'searched_types' => $relevantTypes
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
     * Détermine les types de mémoires à rechercher
     */
    private function determineRelevantMemoryTypes(string $message): array
    {
        // Mémoires de base TOUJOURS incluses + opportunités et conversations
        // L'agent a accès à tout et laisse le LLM décider de la pertinence
        return [
            'lagento_context',  // Corpus principal Agent O
            'user_project',     // Projets de l'utilisateur uniquement
            'user_analytics',   // Analytics de l'utilisateur uniquement
            'opportunites',     // Opportunités de financement (namespace: 'opportunites')
            'conversation'      // Historique des conversations
        ];
    }

    /**
     * Récupère le contexte des documents uploadés par l'utilisateur
     */
    protected function getUserDocumentsContext(string $userId): string
    {
        try {
            $documents = Document::where('user_id', $userId)
                ->processed()
                ->orderBy('created_at', 'desc')
                ->limit(10) // Limite à 10 documents récents
                ->get();

            if ($documents->isEmpty()) {
                return '';
            }

            $context = "Fichiers uploadés par l'utilisateur :\n";
            
            foreach ($documents as $doc) {
                $context .= "• **{$doc->original_name}** ({$doc->file_extension})\n";
                
                if (!empty($doc->ai_summary)) {
                    $context .= "  Résumé : {$doc->ai_summary}\n";
                }
                
                if (!empty($doc->detected_tags)) {
                    $context .= "  Tags : " . implode(', ', $doc->detected_tags) . "\n";
                }
                
                if (!empty($doc->ai_metadata) && isset($doc->ai_metadata['document_type'])) {
                    $context .= "  Type : {$doc->ai_metadata['document_type']}\n";
                }
                
                $context .= "\n";
            }

            return $context;
            
        } catch (\Exception $e) {
            Log::error('Error getting user documents context', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }


    protected function executeFileGeneration(string $message, string $userId): array
    {
        // Check weekly document generation rate limit
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->canUseFeature('documents')) {
            $remaining = $user ? $user->getRemainingUsage('documents') : 0;
            return [
                'file_generation' => [
                    'status' => 'failed',
                    'message' => "Limite hebdomadaire de génération de documents atteinte (3 par semaine). Il vous reste {$remaining} documents."
                ]
            ];
        }
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
                // Use centralized file storage service
                $fileStorage = app(\App\Services\FileStorageService::class);
                $result = $fileStorage->storeGenerated(stream_get_contents($stream), $filename);
                if (is_resource($stream)) fclose($stream);
                @unlink($tempFile);
                $url = $result['url'];
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
                // Use centralized file storage service
                $fileStorage = app(\App\Services\FileStorageService::class);
                $result = $fileStorage->storeGenerated($csv, $filename);
                $url = $result['url'];
            } else {
                // txt or md
                $content = ($ext === 'md')
                    ? ("# Document généré\n\n- Date: " . now()->toDateTimeString() . "\n\n## Contexte\n\n" . $message)
                    : ("Document généré le " . now()->toDateTimeString() . "\n\n" . $message);
                // Use centralized file storage service
                $fileStorage = app(\App\Services\FileStorageService::class);
                $result = $fileStorage->storeGenerated($content, $filename);
                $url = $result['url'];
            }

            // $url is already set by the centralized service above
            
            // Use document generation count
            $user->useFeature('documents');
            
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
        // Check weekly image generation rate limit
        $user = \App\Models\User::find($userId);
        if (!$user || !$user->canUseFeature('images')) {
            $remaining = $user ? $user->getRemainingUsage('images') : 0;
            return [
                'image_generation' => [
                    'status' => 'failed',
                    'message' => "Limite hebdomadaire de génération d'images atteinte (3 par semaine). Il vous reste {$remaining} images."
                ]
            ];
        }
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
            $filename = 'img_' . $userId . '_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.png';
            $binary = base64_decode($b64);
            
            // Use centralized file storage service
            $fileStorage = app(\App\Services\FileStorageService::class);
            $result = $fileStorage->storeGenerated($binary, $filename, 'generated-images');
            $url = $result['url'];
            
            // Use image generation count
            $user->useFeature('images');
            
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


    // Helper methods
    protected function detectFileType(string $message): string
    {
        // Détection simplifiée basée sur les mots-clés principaux
        if (preg_match('/business plan/i', $message)) return 'business_plan';
        if (preg_match('/cv|curriculum/i', $message)) return 'cv';
        if (preg_match('/rapport/i', $message)) return 'report';
        return 'document';
    }

    protected function extractImagePrompt(string $message): string
    {
        // Retourner le message utilisateur directement pour laisser plus de liberté
        return $message;
    }

    protected function formatMarkdownResponse(string $response, array $toolResults = []): string
    {
        // Format response with clean markdown structure
        $formattedResponse = $response;

        // Ensure proper markdown structure with compact formatting
        $formattedResponse = $this->ensureMarkdownStructure($formattedResponse);
        
        return $formattedResponse;
    }

    protected function appendDataCards(string $response, array $data): string
    {
        $cards = "";


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
        
        // Handle numbered lists properly - preserve numbering sequence
        // Split content into lines to handle numbered lists more carefully
        $lines = explode("\n", $content);
        $processedLines = [];
        $inNumberedList = false;
        $currentNumber = 0;
        $emptyLineCount = 0;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            if (empty($trimmedLine)) {
                $emptyLineCount++;
                $processedLines[] = $line;
                // Reset numbered list after 2+ empty lines (indicates new section)
                if ($emptyLineCount >= 2) {
                    $inNumberedList = false;
                }
                continue;
            }
            
            $emptyLineCount = 0;
            
            // Check if this is a numbered list item
            if (preg_match('/^(\d+)\.\s+(.+)/', $trimmedLine, $matches)) {
                $originalNumber = (int)$matches[1];
                
                if (!$inNumberedList || $originalNumber == 1) {
                    // Starting a new numbered list
                    $inNumberedList = true;
                    $currentNumber = 1;
                } else {
                    // Continue numbered list with sequential numbering
                    $currentNumber++;
                }
                $processedLines[] = "{$currentNumber}. {$matches[2]}";
            } elseif (preg_match('/^[*-]\s+/', $trimmedLine)) {
                // Bullet list item - don't reset numbered list context
                $processedLines[] = $line;
            } else {
                // Regular text line - don't reset if it might be continuation
                // Only reset if this looks like a new section (header, paragraph)
                if (preg_match('/^#{1,6}\s+/', $trimmedLine) || strlen($trimmedLine) > 50) {
                    $inNumberedList = false;
                }
                $processedLines[] = $line;
            }
        }
        
        $content = implode("\n", $processedLines);
        
        // Ensure proper list formatting with tight spacing (only bullet lists now)
        $content = preg_replace('/\n\n([*-])\s/', "\n$1 ", $content);
        
        // Remove excessive spacing between list items
        $content = preg_replace('/([*-] .+)\n\n([*-] )/', '$1' . "\n" . '$2', $content);
        
        return trim($content);
    }
}