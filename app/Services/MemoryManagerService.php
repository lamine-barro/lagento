<?php

namespace App\Services;

use App\Models\Opportunite;
use App\Models\TexteOfficiel;
use App\Models\Institution;
use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\PdfExtractionService;

class MemoryManagerService
{
    private VoyageVectorService $vectorService;
    private PdfExtractionService $pdfExtractor;

    public function __construct(VoyageVectorService $vectorService, PdfExtractionService $pdfExtractor)
    {
        $this->vectorService = $vectorService;
        $this->pdfExtractor = $pdfExtractor;
    }

    /**
     * Index different types of memories
     */
    public function indexMemory(string $type, $entity): void
    {
        try {
            switch ($type) {
                case 'opportunite':
                    $this->indexOpportunite($entity);
                    break;
                case 'texte_officiel':
                    $this->indexTexteOfficiel($entity);
                    break;
                case 'institution':
                    $this->indexInstitution($entity);
                    break;
                case 'user_project':
                    $this->indexUserProject($entity);
                    break;
                case 'user_analytics':
                    $this->indexUserAnalytics($entity);
                    break;
                case 'timeline_gov':
                    $this->indexTimelineGov();
                    break;
                case 'presentation':
                    $this->indexPresentation();
                    break;
                default:
                    Log::warning("Unknown memory type: $type");
            }
        } catch (\Exception $e) {
            Log::error("Failed to index $type memory", [
                'error' => $e->getMessage(),
                'entity_id' => $entity->id ?? 'unknown'
            ]);
        }
    }

    /**
     * Index opportunité
     */
    private function indexOpportunite(Opportunite $opportunite): void
    {
        $context = "Opportunité {$opportunite->type} - {$opportunite->titre}";
        
        $content = implode("\n", array_filter([
            "Titre: {$opportunite->titre}",
            "Type: {$opportunite->type}",
            "Description: {$opportunite->description}",
            "Critères: " . implode(', ', $opportunite->criteres_eligibilite ?: []),
            "Régions: " . implode(', ', $opportunite->regions_ciblees ?: []),
            "Date limite: {$opportunite->date_limite_candidature}",
            "Rémunération: {$opportunite->remuneration}",
            "Places: {$opportunite->nombre_places}"
        ]));

        $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'opportunite',
                    $opportunite->id,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'type' => $opportunite->type,
                        'regions' => $opportunite->regions_ciblees ?: [],
                        'deadline' => $opportunite->date_limite_candidature,
                        'statut' => $opportunite->statut,
                        'institution_id' => $opportunite->institution_id
                    ]
                );
            }
        }

        Log::info("Indexed opportunité", [
            'id' => $opportunite->id,
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Index texte officiel with PDF content
     */
    private function indexTexteOfficiel(TexteOfficiel $texte): void
    {
        $context = "Texte officiel {$texte->classification_juridique} - {$texte->titre}";
        
        // Get PDF content if available
        $pdfContent = '';
        if ($texte->chemin_fichier && Storage::exists($texte->chemin_fichier)) {
            // For now, use texte_brut, but could implement PDF extraction
            $pdfContent = $texte->texte_brut ?? '';
        }

        $content = implode("\n", array_filter([
            "Titre: {$texte->titre}",
            "Classification: {$texte->classification_juridique}",
            "Résumé: {$texte->resume}",
            "Source: {$texte->source}",
            "Date publication: {$texte->date_publication}",
            "Tags: " . implode(', ', $texte->tags ?: []),
            "Contenu: $pdfContent"
        ]));

        $chunks = $this->vectorService->intelligentChunk($content, $context, 600);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'texte_officiel',
                    $texte->id,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'classification' => $texte->classification_juridique,
                        'statut' => $texte->statut,
                        'date_publication' => $texte->date_publication,
                        'langue' => $texte->langue,
                        'tags' => $texte->tags ?: [],
                        'institution_id' => $texte->institution_id
                    ]
                );
            }
        }

        Log::info("Indexed texte officiel", [
            'id' => $texte->id,
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Index institution
     */
    private function indexInstitution(Institution $institution): void
    {
        $context = "Institution {$institution->type} - {$institution->nom}";
        
        $content = implode("\n", array_filter([
            "Nom: {$institution->nom}",
            "Type: {$institution->type}",
            "Statut: {$institution->statut}",
            "Description: {$institution->description}",
            "Services: " . implode(', ', $institution->services ?: []),
            "Région: {$institution->region}",
            "Ville: {$institution->ville}",
            "Contact: {$institution->telephone}",
            "Email: {$institution->email}",
            "Tags: " . implode(', ', $institution->tags ?: [])
        ]));

        $chunks = $this->vectorService->intelligentChunk($content, $context, 300);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'institution',
                    $institution->id,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'type' => $institution->type,
                        'statut' => $institution->statut,
                        'region' => $institution->region,
                        'ville' => $institution->ville,
                        'services' => $institution->services ?: [],
                        'tags' => $institution->tags ?: []
                    ]
                );
            }
        }

        Log::info("Indexed institution", [
            'id' => $institution->id,
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Index user project
     */
    private function indexUserProject(Projet $projet): void
    {
        $secteur = (isset($projet->secteurs) && !empty($projet->secteurs)) ? $projet->secteurs[0] : 'Non défini';
        $context = "Projet entrepreneurial {$secteur} - {$projet->nom_projet}";
        
        $content = implode("\n", array_filter([
            "Nom: {$projet->nom_projet}",
            "Raison sociale: {$projet->raison_sociale}",
            "Description: {$projet->description}",
            "Secteurs: " . implode(', ', $projet->secteurs ?: []),
            "Maturité: {$projet->maturite}",
            "Stade financement: {$projet->stade_financement}",
            "Région: {$projet->region}",
            "Cibles: " . implode(', ', $projet->cibles ?: []),
            "Modèles revenus: " . implode(', ', $projet->modeles_revenus ?: []),
            "Équipe: {$projet->taille_equipe} personnes",
            "Fondateurs: {$projet->nombre_fondateurs}H + {$projet->nombre_fondatrices}F",
            "Formalisé: {$projet->formalise}",
            "Besoins: {$projet->details_besoins}",
            "Types soutien: " . implode(', ', $projet->types_soutien ?: [])
        ]));

        $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        // Delete existing vectors for this project
        DB::table('vector_memories')
            ->where('memory_type', 'user_project')
            ->where('source_id', $projet->id)
            ->delete();

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'user_project',
                    $projet->id,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'user_id' => $projet->user_id,
                        'secteurs' => $projet->secteurs ?: [],
                        'maturite' => $projet->maturite,
                        'region' => $projet->region,
                        'stade_financement' => $projet->stade_financement,
                        'formalise' => $projet->formalise
                    ]
                );
            }
        }

        Log::info("Indexed user project", [
            'id' => $projet->id,
            'user_id' => $projet->user_id,
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Index user analytics/diagnostic
     */
    private function indexUserAnalytics(UserAnalytics $analytics): void
    {
        $user = User::find($analytics->user_id);
        $context = "Diagnostic entrepreneur - {$user->name}";
        
        $profile = $analytics->entrepreneur_profile ?? [];
        
        // Extract forces
        $forces = [];
        if (isset($profile['forces']) && is_array($profile['forces'])) {
            foreach ($profile['forces'] as $force) {
                if (is_array($force) && isset($force['domaine'])) {
                    $forces[] = $force['domaine'];
                } elseif (is_string($force)) {
                    $forces[] = $force;
                }
            }
        }
        
        // Extract axes progression
        $axes = [];
        if (isset($profile['axes_progression']) && is_array($profile['axes_progression'])) {
            foreach ($profile['axes_progression'] as $axe) {
                if (is_array($axe) && isset($axe['domaine'])) {
                    $axes[] = $axe['domaine'];
                } elseif (is_string($axe)) {
                    $axes[] = $axe;
                }
            }
        }
        
        // Extract besoins formation
        $besoins = [];
        if (isset($profile['besoins_formation']) && is_array($profile['besoins_formation'])) {
            foreach ($profile['besoins_formation'] as $besoin) {
                if (is_string($besoin)) {
                    $besoins[] = $besoin;
                }
            }
        }
        
        $content = implode("\n", array_filter([
            "Entrepreneur: {$user->name}",
            "Niveau global: " . ($profile['niveau_global'] ?? 'Non défini'),
            "Score potentiel: " . ($profile['score_potentiel'] ?? 'Non défini'),
            "Forces: " . (empty($forces) ? 'Non définies' : implode(', ', $forces)),
            "Axes progression: " . (empty($axes) ? 'Non définis' : implode(', ', $axes)),
            "Besoins formation: " . (empty($besoins) ? 'Non définis' : implode(', ', $besoins)),
            "Profil type: " . ($profile['profil_type'] ?? 'Non défini'),
            "Score santé: " . ($analytics->score_sante ?? 'Non défini'),
            "Viabilité: " . ($analytics->viabilite ?? 'Non défini'),
            "Opportunités matchées: " . ($analytics->nombre_opportunites ?? 0),
            "Position marché: " . ($analytics->position_marche ?? 'Non définie'),
            "Message principal: " . ($analytics->message_principal ?? 'Non défini')
        ]));

        $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        // Delete existing vectors for this user analytics
        DB::table('vector_memories')
            ->where('memory_type', 'user_analytics')
            ->where('source_id', $analytics->id)
            ->delete();

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'user_analytics',
                    $analytics->id,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'user_id' => $analytics->user_id,
                        'niveau_global' => $profile['niveau_global'] ?? null,
                        'profil_type' => $profile['profil_type'] ?? null,
                        'score_potentiel' => $profile['score_potentiel'] ?? null,
                        'generated_at' => $analytics->generated_at
                    ]
                );
            }
        }

        Log::info("Indexed user analytics", [
            'id' => $analytics->id,
            'user_id' => $analytics->user_id,
            'chunks' => count($chunks)
        ]);
    }

    /**
     * Index government timeline from markdown file
     */
    private function indexTimelineGov(): void
    {
        $filePath = base_path('data/Timeline_actions_gouvernementales.md');
        
        if (!file_exists($filePath)) {
            Log::warning('Timeline file not found', ['path' => $filePath]);
            return;
        }

        $content = file_get_contents($filePath);
        $context = "Timeline des actions gouvernementales - Côte d'Ivoire";
        
        $chunks = $this->vectorService->intelligentChunk($content, $context, 500);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        // Delete existing timeline vectors
        DB::table('vector_memories')
            ->where('memory_type', 'timeline_gov')
            ->delete();

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'timeline_gov',
                    'timeline_' . $index,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'type' => 'gouvernemental',
                        'pays' => 'CI',
                        'source' => 'markdown'
                    ]
                );
            }
        }

        Log::info("Indexed government timeline", ['chunks' => count($chunks)]);
    }

    /**
     * Index LagentO presentation
     */
    private function indexPresentation(): void
    {
        $content = "
        LagentO - Assistant IA Entrepreneurial pour la Côte d'Ivoire
        
        Mission: Accompagner les entrepreneurs ivoiriens dans leur parcours de création et développement d'entreprise.
        
        Services:
        - Conseils personnalisés en entrepreneuriat
        - Veille sur les opportunités de financement
        - Orientation vers les programmes gouvernementaux
        - Accompagnement formalisation d'entreprises
        - Diagnostic entrepreneurial personnalisé
        - Mise en relation avec l'écosystème
        
        Public cible: Entrepreneurs ivoiriens digitalement connectés de 18-35 ans, startups tech, PME en croissance, porteurs de projets structurés et diaspora entrepreneuriale.
        
        Fonctionnalités:
        - Chat intelligent 24/7
        - Génération de documents (business plans, CV)
        - Recherche d'opportunités personnalisée
        - Analyse réglementaire OHADA
        - Matching avec institutions et partenaires
        
        Horizon-O: Extension de LagentO pour l'accompagnement à l'international et la croissance des entreprises.
        ";

        $context = "Présentation LagentO/Horizon-O - Assistant IA entrepreneurial";
        $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        // Delete existing presentation vectors
        DB::table('vector_memories')
            ->where('memory_type', 'presentation')
            ->delete();

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'presentation',
                    'lagento_' . $index,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'type' => 'presentation',
                        'produit' => 'LagentO',
                        'source' => 'internal'
                    ]
                );
            }
        }

        Log::info("Indexed LagentO presentation", ['chunks' => count($chunks)]);
    }

    /**
     * Index FAQ from markdown file
     */
    private function indexFAQ(): void
    {
        $filePath = base_path('data/FAQ_AgentO.md');
        
        if (!file_exists($filePath)) {
            Log::warning('FAQ file not found', ['path' => $filePath]);
            return;
        }

        $content = file_get_contents($filePath);
        $context = "FAQ LagentO - Questions fréquentes";
        
        $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
        $embeddings = $this->vectorService->embedWithContext($chunks, $context);

        // Delete existing FAQ vectors
        DB::table('vector_memories')
            ->where('memory_type', 'faq')
            ->delete();

        foreach ($chunks as $index => $chunk) {
            if (isset($embeddings[$index])) {
                $this->storeVectorMemory(
                    'faq',
                    'faq_' . $index,
                    $chunk,
                    $embeddings[$index]['embedding'],
                    [
                        'type' => 'documentation',
                        'produit' => 'LagentO',
                        'source' => 'markdown'
                    ]
                );
            }
        }

        Log::info("Indexed FAQ", ['chunks' => count($chunks)]);
    }

    /**
     * Store vector in database
     */
    private function storeVectorMemory(
        string $memoryType,
        ?string $sourceId,
        string $content,
        array $embedding,
        array $metadata = []
    ): void {
        if (empty($sourceId)) {
            Log::warning('Cannot store vector memory: sourceId is null or empty', [
                'memory_type' => $memoryType,
                'metadata' => $metadata
            ]);
            return;
        }
        DB::table('vector_memories')->insert([
            'id' => Str::uuid(),
            'memory_type' => $memoryType,
            'source_id' => $sourceId,
            'chunk_content' => $content,
            'embedding' => json_encode($embedding),
            'metadata' => json_encode($metadata),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Search across multiple memory types
     */
    public function searchAcrossMemories(
        string $query, 
        array $memoryTypes = [], 
        string $userId = null,
        int $limit = 10
    ): array {
        $filters = [];
        
        // Add user context if provided
        if ($userId) {
            $filters['user_id'] = $userId;
        }

        // If multiple memory types, ensure diversity by searching each type independently
        if (count($memoryTypes) > 1) {
            return $this->searchWithDiversity($query, $memoryTypes, $filters, $limit);
        }
        
        return $this->vectorService->semanticSearch(
            $query,
            $memoryTypes,
            $filters,
            $limit,
            0.7 // Similarity threshold
        );
    }
    
    /**
     * Search with diversity across memory types - each type called independently
     */
    private function searchWithDiversity(string $query, array $memoryTypes, array $filters, int $limit): array
    {
        $results = [];
        $perTypeLimit = max(1, ceil($limit / count($memoryTypes))); // Distribute evenly
        
        foreach ($memoryTypes as $type) {
            $typeResults = $this->vectorService->semanticSearch(
                $query,
                [$type],
                $filters,
                $perTypeLimit,
                0.55 // Lower threshold for better diversity and more results
            );
            
            // Log each type search for debugging
            Log::debug("Searched memory type independently", [
                'type' => $type,
                'query_preview' => substr($query, 0, 50),
                'results_count' => count($typeResults),
                'per_type_limit' => $perTypeLimit
            ]);
            
            $results = array_merge($results, $typeResults);
        }
        
        // Sort all results by similarity and limit
        usort($results, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($results, 0, $limit);
    }

    /**
     * Update memory (re-index)
     */
    public function updateMemory(string $type, $entity): void
    {
        // Delete existing vectors
        DB::table('vector_memories')
            ->where('memory_type', $type)
            ->where('source_id', $entity->id)
            ->delete();

        // Re-index
        $this->indexMemory($type, $entity);
    }

    /**
     * Delete memory vectors
     */
    public function deleteMemory(string $type, string $sourceId): void
    {
        DB::table('vector_memories')
            ->where('memory_type', $type)
            ->where('source_id', $sourceId)
            ->delete();

        Log::info("Deleted memory vectors", [
            'type' => $type,
            'source_id' => $sourceId
        ]);
    }

    /**
     * Bulk index from CSV files
     */
    public function bulkIndexFromCSV(): void
    {
        try {
            // Index opportunités
            if (file_exists(base_path('data/opportunites.csv'))) {
                $this->indexOpportunitiesFromCSV();
            }

            // Index institutions
            if (file_exists(base_path('data/institutions.csv'))) {
                $this->indexInstitutionsFromCSV();
            }

            // Index textes officiels
            if (file_exists(base_path('data/textes_officiels.csv'))) {
                $this->indexTextesOfficielsFromCSV();
            }

            // Index timeline
            $this->indexTimelineGov();

            // Index FAQ
            $this->indexFAQ();

            // Index presentation
            $this->indexPresentation();

        } catch (\Exception $e) {
            Log::error('Bulk indexing failed', ['error' => $e->getMessage()]);
        }
    }

    private function indexOpportunitiesFromCSV(): void
    {
        $filePath = base_path('data/opportunites.csv');
        if (!file_exists($filePath)) {
            Log::warning('Opportunities CSV not found', ['path' => $filePath]);
            return;
        }

        $csv = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csv);
        
        // Delete existing CSV opportunities
        DB::table('vector_memories')->where('memory_type', 'opportunite')->where('metadata->source', 'csv')->delete();
        
        $count = 0;
        foreach ($csv as $row) {
            if (count($row) !== count($headers)) continue;
            
            $data = array_combine($headers, $row);
            $type = isset($data['type']) ? $data['type'] : 'Non défini';
            $context = "Opportunité CSV - {$type}";
            
            $content = implode("\n", array_filter([
                "Titre: " . (isset($data['titre']) ? $data['titre'] : ''),
                "Type: " . (isset($data['type']) ? $data['type'] : ''),
                "Description: " . (isset($data['description']) ? $data['description'] : ''),
                "Institution: " . (isset($data['institution']) ? $data['institution'] : ''),
                "Date limite: " . (isset($data['date_limite']) ? $data['date_limite'] : ''),
                "Montant: " . (isset($data['montant']) ? $data['montant'] : ''),
                "Régions: " . (isset($data['regions']) ? $data['regions'] : '')
            ]));

            $chunks = $this->vectorService->intelligentChunk($content, $context, 400);
            $embeddings = $this->vectorService->embedWithContext($chunks, $context);

            foreach ($chunks as $index => $chunk) {
                if (isset($embeddings[$index])) {
                    $this->storeVectorMemory(
                        'opportunite',
                        'csv_' . $count . '_' . $index,
                        $chunk,
                        $embeddings[$index]['embedding'],
                        [
                            'type' => isset($data['type']) ? $data['type'] : null,
                            'institution' => isset($data['institution']) ? $data['institution'] : null,
                            'source' => 'csv',
                            'deadline' => isset($data['date_limite']) ? $data['date_limite'] : null
                        ]
                    );
                }
            }
            $count++;
        }

        Log::info("Indexed opportunities from CSV", ['count' => $count]);
    }

    private function indexInstitutionsFromCSV(): void
    {
        $filePath = base_path('data/institutions.csv');
        if (!file_exists($filePath)) {
            Log::warning('Institutions CSV not found', ['path' => $filePath]);
            return;
        }

        $csv = array_map('str_getcsv', file($filePath));
        $headers = array_shift($csv);
        
        // Delete existing CSV institutions
        DB::table('vector_memories')->where('memory_type', 'institution')->where('metadata->source', 'csv')->delete();
        
        $count = 0;
        foreach ($csv as $row) {
            if (count($row) !== count($headers)) continue;
            
            $data = array_combine($headers, $row);
            $type = isset($data['type']) ? $data['type'] : 'Non défini';
            $context = "Institution CSV - {$type}";
            
            $content = implode("\n", array_filter([
                "Nom: " . (isset($data['nom']) ? $data['nom'] : ''),
                "Type: " . (isset($data['type']) ? $data['type'] : ''),
                "Description: " . (isset($data['description']) ? $data['description'] : ''),
                "Services: " . (isset($data['services']) ? $data['services'] : ''),
                "Région: " . (isset($data['region']) ? $data['region'] : ''),
                "Contact: " . (isset($data['contact']) ? $data['contact'] : ''),
                "Site web: " . (isset($data['site_web']) ? $data['site_web'] : '')
            ]));

            $chunks = $this->vectorService->intelligentChunk($content, $context, 300);
            $embeddings = $this->vectorService->embedWithContext($chunks, $context);

            foreach ($chunks as $index => $chunk) {
                if (isset($embeddings[$index])) {
                    $this->storeVectorMemory(
                        'institution',
                        'csv_' . $count . '_' . $index,
                        $chunk,
                        $embeddings[$index]['embedding'],
                        [
                            'type' => isset($data['type']) ? $data['type'] : null,
                            'region' => isset($data['region']) ? $data['region'] : null,
                            'source' => 'csv'
                        ]
                    );
                }
            }
            $count++;
        }

        Log::info("Indexed institutions from CSV", ['count' => $count]);
    }

    private function indexTextesOfficielsFromCSV(): void
    {
        $csvPath = base_path('data/textes_officiels.csv');
        $pdfDir = base_path('data/textes_officiels_downloads');
        
        if (!file_exists($csvPath)) {
            Log::warning('Textes officiels CSV not found', ['path' => $csvPath]);
            return;
        }

        $csv = array_map('str_getcsv', file($csvPath));
        $headers = array_shift($csv);
        
        // Delete existing CSV textes officiels
        DB::table('vector_memories')->where('memory_type', 'texte_officiel')->where('metadata->source', 'csv')->delete();
        
        $count = 0;
        foreach ($csv as $row) {
            if (count($row) !== count($headers)) continue;
            
            $data = array_combine($headers, $row);
            $classification = isset($data['classification_juridique']) ? $data['classification_juridique'] : 'Non défini';
            $titre = isset($data['titre']) ? $data['titre'] : '';
            $context = "Texte officiel {$classification} - {$titre}";
            
            // CSV metadata content
            $csvContent = implode("\n", array_filter([
                "Titre: " . (isset($data['titre']) ? $data['titre'] : ''),
                "Classification: " . (isset($data['classification_juridique']) ? $data['classification_juridique'] : ''),
                "Statut: " . (isset($data['statut']) ? $data['statut'] : ''),
                "Date publication: " . (isset($data['date_publication']) ? $data['date_publication'] : ''),
                "Taille fichier: " . (isset($data['taille_fichier']) ? $data['taille_fichier'] : ''),
                "Type MIME: " . (isset($data['type_mime']) ? $data['type_mime'] : '')
            ]));

            // Extract PDF content if available
            $pdfContent = '';
            $hasPdf = false;
            if (isset($data['fichier_telecharge']) && !empty($data['fichier_telecharge'])) {
                $pdfPath = $pdfDir . '/' . $data['fichier_telecharge'];
                
                if (file_exists($pdfPath)) {
                    Log::info("Extracting PDF content", ['file' => $data['fichier_telecharge']]);
                    
                    $extractedData = $this->pdfExtractor->extractWithMetadata($pdfPath);
                    if (!empty($extractedData['content']) && strlen($extractedData['content']) > 100) {
                        $pdfContent = "\n\n=== CONTENU PDF ===\n" . $extractedData['content'];
                        $hasPdf = true;
                        
                        Log::info("PDF content extracted successfully", [
                            'file' => $data['fichier_telecharge'],
                            'content_length' => strlen($extractedData['content']),
                            'pages' => $extractedData['metadata']['pages_count'] ?? 'unknown'
                        ]);
                    } else {
                        $pdfContent = "\nFichier PDF présent mais contenu non extractible: " . $data['fichier_telecharge'];
                        
                        Log::warning("PDF content extraction failed", [
                            'file' => $data['fichier_telecharge'],
                            'error' => $extractedData['error'] ?? 'Empty content'
                        ]);
                    }
                } else {
                    $pdfContent = "\nFichier PDF référencé mais non trouvé: " . $data['fichier_telecharge'];
                }
            }

            $fullContent = $csvContent . $pdfContent;
            
            // Use larger chunks for PDF content but process in smaller batches
            $chunkSize = $hasPdf ? 800 : 600;
            $chunks = $this->vectorService->intelligentChunk($fullContent, $context, $chunkSize);
            
            // Process embeddings in small batches to avoid token limits
            $batchSize = 2; // Process 2 chunks at a time for PDFs
            $embeddings = [];
            
            for ($i = 0; $i < count($chunks); $i += $batchSize) {
                $batch = array_slice($chunks, $i, $batchSize);
                $batchEmbeddings = $this->vectorService->embedWithContext($batch, $context);
                
                foreach ($batchEmbeddings as $index => $embedding) {
                    $embeddings[$i + $index] = $embedding;
                }
                
                // Rate limiting between batches
                if ($hasPdf && count($batch) > 1) {
                    sleep(1);
                }
            }

            foreach ($chunks as $index => $chunk) {
                if (isset($embeddings[$index])) {
                    $this->storeVectorMemory(
                        'texte_officiel',
                        'csv_' . $count . '_' . $index,
                        $chunk,
                        $embeddings[$index]['embedding'],
                        [
                            'classification' => isset($data['classification_juridique']) ? $data['classification_juridique'] : null,
                            'date_publication' => isset($data['date_publication']) ? $data['date_publication'] : null,
                            'source' => 'csv',
                            'has_pdf' => $hasPdf,
                            'pdf_extracted' => $hasPdf,
                            'file_name' => isset($data['fichier_telecharge']) ? $data['fichier_telecharge'] : null,
                            'statut' => isset($data['statut']) ? $data['statut'] : null
                        ]
                    );
                }
            }
            $count++;
            
            // Rate limiting for API calls
            if ($count % 3 === 0) {
                sleep(2); // Pause between batches
            }
        }

        Log::info("Indexed textes officiels from CSV", ['count' => $count]);
    }
}