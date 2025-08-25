<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAnalytics;
use App\Models\Projet;
use App\Models\UserMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserAnalyticsService
{
    protected OpenAIVectorService $vectorService;
    protected AutoVectorizationService $autoVectorService;
    protected DiagnosticCacheService $cacheService;

    public function __construct(
        OpenAIVectorService $vectorService, 
        AutoVectorizationService $autoVectorService,
        DiagnosticCacheService $cacheService
    ) {
        $this->vectorService = $vectorService;
        $this->autoVectorService = $autoVectorService;
        $this->cacheService = $cacheService;
    }

    /**
     * Update entrepreneur profile analytics based on onboarding data
     */
    public function updateEntrepreneurProfile(User $user, array $onboardingData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            // Vérifier le cache pour le résumé business
            $lmSummary = $this->cacheService->getCachedProfileSummary($user, $onboardingData);
            
            if (!$lmSummary) {
                // Cache miss - générer avec LLM et mettre en cache
                $lmSummary = $this->summarizeBusinessData($onboardingData, $user);
                if (!empty($lmSummary)) {
                    $this->cacheService->cacheProfileSummary($user, $onboardingData, $lmSummary);
                }
            }

            $profile = [
                'niveau_global' => $lmSummary['level'] ?? null,
                'score_potentiel' => $lmSummary['potential_score'] ?? null,
                'forces' => $lmSummary['strengths'] ?? [],
                'axes_progression' => $lmSummary['improvements'] ?? [],
                'besoins_formation' => $lmSummary['training_needs'] ?? [],
                'profil_type' => $lmSummary['profile_type'] ?? null,
                'basic_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profile_type' => $user->profile_type,
                    'verification_status' => $user->verification_status,
                    'updated_at' => now()->toISOString()
                ],
                'business_info' => array_merge($onboardingData, [
                    'llm_summary' => $lmSummary['summary'] ?? null,
                    'keywords' => $lmSummary['keywords'] ?? [],
                    'risk_flags' => $lmSummary['risks'] ?? [],
                ]),
                'completion_score' => $this->calculateProfileCompletion($user, $onboardingData),
                'engagement_level' => $this->calculateEngagementLevel($user),
                'last_activity' => now()->toISOString()
            ];

            // derive nombre_fondateurs total si possible
            $male = data_get($onboardingData, 'num_founders_male');
            $female = data_get($onboardingData, 'num_founders_female');
            if (is_numeric($male) || is_numeric($female)) {
                $profile['business_info']['nombre_fondateurs'] = (int)max(0, (int)$male) + (int)max(0, (int)$female);
            }

            // Map to existing table structure
            $executive_summary = [
                'niveau_global' => $lmSummary['level'] ?? null,
                'score_potentiel' => $lmSummary['potential_score'] ?? null,
                'profil_type' => $lmSummary['profile_type'] ?? null,
                'summary' => $lmSummary['summary'] ?? null,
                'keywords' => $lmSummary['keywords'] ?? [],
                'risks' => $lmSummary['risks'] ?? []
            ];
            
            $project_diagnostic = [
                'forces' => $lmSummary['strengths'] ?? [],
                'axes_progression' => $lmSummary['improvements'] ?? [],
                'besoins_formation' => $lmSummary['training_needs'] ?? []
            ];
            
            $analytics->update([
                'niveau_maturite' => $lmSummary['level'] ?? 'débutant',
                'score_global' => $lmSummary['potential_score'] ?? 0,
                'executive_summary' => $executive_summary,
                'project_diagnostic' => $project_diagnostic,
                'derniere_analyse' => now()
            ]);

            Log::info("Entrepreneur profile updated for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to update entrepreneur profile for user {$user->id}: " . $e->getMessage());
        }
    }

    private function summarizeBusinessData(array $data, User $user): array
    {
        try {
            $text = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (!$text || strlen($text) < 10) {
                Log::warning('UserAnalyticsService: summarizeBusinessData - insufficient data', ['data_length' => strlen($text ?? '')]);
                return [];
            }
            
            Log::info('UserAnalyticsService: Starting enhanced business data summarization', ['data_size' => strlen($text), 'user_id' => $user->id]);

            // Get user project context directly (no more vectorization)
            $projectContext = $this->getUserProjectContext($user);
            $contextualInfo = $this->formatProjectContextForPrompt($projectContext);
            
            $systemPrompt = "Tu es un analyste business expert de l'écosystème entrepreneurial ivoirien. 

CONTEXTE DISPONIBLE:
" . $contextualInfo . "

Sur la base des données de projet ET du contexte ci-dessus, génère un JSON STRICT avec:
- summary (3 phrases max avec insights contextuels)
- keywords (5 mots-clés français)
- risks (≤3 risques spécifiques identifiés)
- level (débutant|confirmé|expert)
- potential_score (0-100 basé sur le contexte écosystème)
- strengths[{domaine,description}] (forces identifiées vs écosystème)
- improvements[{domaine,action_suggeree,impact,resources}] (avec ressources recommandées)
- training_needs[string] (besoins formation spécifiques)
- profile_type (innovateur|gestionnaire|commercial|artisan|commerçant)
- opportunities_match (nombre d'opportunités potentiellement pertinentes)
- ecosystem_position (positionnement dans l'écosystème CI)

Réponds UNIQUEMENT ce JSON.";

            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ];

            $lm = app(\App\Services\LanguageModelService::class);
            
            try {
                $raw = $lm->chat($messages, 'gpt-4.1-mini', null, 8000, [
                    'response_format' => ['type' => 'json_object']
                ]);
                
                if (empty($raw)) {
                    throw new \Exception('Empty response from LLM service');
                }
                
                Log::info('UserAnalyticsService: Enhanced LLM response received', [
                    'raw_length' => strlen($raw), 
                    'raw_preview' => substr($raw, 0, 200),
                    'vector_context_length' => strlen($contextualInfo)
                ]);
                
                $parsed = json_decode($raw, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
                }
                
                if (is_array($parsed)) {
                    Log::info('UserAnalyticsService: Enhanced JSON parsing successful', ['keys' => array_keys($parsed)]);
                    return $parsed;
                }
                
                throw new \Exception('Parsed response is not an array');
                
            } catch (\Exception $llmError) {
                Log::error('UserAnalyticsService: LLM call failed', ['error' => $llmError->getMessage()]);
                // Fallback to basic analysis without LLM
                return $this->createBasicBusinessSummary($data, $user);
            }
        } catch (\Throwable $e) {
            Log::error('UserAnalyticsService: summarizeBusinessData exception', ['error' => $e->getMessage()]);
        }
        return [];
    }

    /**
     * Create a basic business summary without LLM when OpenAI fails
     */
    private function createBasicBusinessSummary(array $data, User $user): array
    {
        // Fallback analysis basée sur des règles simples
        $businessName = $data['business_name'] ?? 'Entreprise';
        $description = $data['description'] ?? '';
        $sector = $data['business_sector'] ?? 'Non spécifié';
        $stage = $data['business_stage'] ?? 'débutant';
        
        // Score de base selon les données disponibles
        $score = 30; // Score de base
        if (!empty($description) && strlen($description) > 50) $score += 15;
        if (!empty($data['target_market'])) $score += 10;
        if (!empty($data['revenue_model'])) $score += 15;
        if ($data['formalized'] ?? false) $score += 10;
        if (!empty($data['team_size'])) $score += 10;
        
        $level = match(true) {
            $score >= 70 => 'confirmé',
            $score >= 50 => 'intermédiaire',
            default => 'débutant'
        };
        
        return [
            'summary' => "Entreprise {$businessName} dans le secteur {$sector}, au stade {$stage}. Analyse de base générée sans IA.",
            'keywords' => [$sector, $stage, 'entreprenariat', 'côte d\'ivoire', 'business'],
            'risks' => ['Données limitées pour l\'analyse'],
            'level' => $level,
            'potential_score' => $score,
            'strengths' => [
                ['domaine' => 'Motivation', 'description' => 'Initiative entrepreneuriale engagée']
            ],
            'improvements' => [
                ['domaine' => 'Profil', 'action_suggeree' => 'Compléter les informations de profil', 'impact' => 'élevé', 'resources' => ['Documentation', 'Formation']]
            ],
            'training_needs' => ['Développement entrepreneurial'],
            'profile_type' => 'entrepreneur',
            'opportunities_match' => 5,
            'ecosystem_position' => 'émergent'
        ];
    }

    /**
     * Track user interaction with chat/agents
     */
    public function trackChatInteraction(User $user, array $interactionData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $currentMetadata = $analytics->metadata ?? [];
            
            // Update interaction stats
            $interactions = $currentMetadata['chat_interactions'] ?? [
                'total_messages' => 0,
                'total_sessions' => 0,
                'agent_usage' => [
                    'principal' => 0,
                    'suggestions' => 0,
                    'title_generation' => 0
                ],
                'topics_discussed' => [],
                'avg_session_length' => 0,
                'last_interaction' => null,
                'user_satisfaction' => []
            ];

            $interactions['total_messages']++;
            $interactions['last_interaction'] = now()->toISOString();
            
            // Track agent usage
            if (isset($interactionData['agent_type'])) {
                $interactions['agent_usage'][$interactionData['agent_type']] = 
                    ($interactions['agent_usage'][$interactionData['agent_type']] ?? 0) + 1;
            }

            // Extract and track topics
            if (isset($interactionData['tools_used'])) {
                foreach ($interactionData['tools_used'] as $tool) {
                    if (!in_array($tool, $interactions['topics_discussed'])) {
                        $interactions['topics_discussed'][] = $tool;
                    }
                }
            }

            $analytics->update([
                'metadata' => array_merge($currentMetadata, [
                    'chat_interactions' => $interactions,
                    'last_activity' => now()->toISOString()
                ])
            ]);

            Log::info("Chat interaction tracked for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to track chat interaction for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Update analytics when user uploads data sources
     */
    public function trackDataSourceUpload(User $user, array $uploadData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $currentMetadata = $analytics->metadata ?? [];
            
            $dataSources = $currentMetadata['data_sources'] ?? [
                'total_uploads' => 0,
                'file_types' => [],
                'total_size_mb' => 0,
                'upload_history' => [],
                'categories' => []
            ];

            $dataSources['total_uploads']++;
            $dataSources['total_size_mb'] += $uploadData['size_mb'] ?? 0;
            
            // Track file types
            if (isset($uploadData['file_type'])) {
                $fileType = $uploadData['file_type'];
                $dataSources['file_types'][$fileType] = ($dataSources['file_types'][$fileType] ?? 0) + 1;
            }

            // Add to upload history
            $dataSources['upload_history'][] = [
                'filename' => $uploadData['filename'] ?? 'unknown',
                'type' => $uploadData['file_type'] ?? 'unknown',
                'size_mb' => $uploadData['size_mb'] ?? 0,
                'uploaded_at' => now()->toISOString(),
                'category' => $uploadData['category'] ?? 'general'
            ];

            // Keep only last 50 uploads in history
            if (count($dataSources['upload_history']) > 50) {
                $dataSources['upload_history'] = array_slice($dataSources['upload_history'], -50);
            }

            $analytics->update([
                'metadata' => array_merge($currentMetadata, [
                    'data_sources' => $dataSources,
                    'last_activity' => now()->toISOString()
                ])
            ]);

            Log::info("Data source upload tracked for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to track data source upload for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Generate complete dashboard analytics structure
     */
    public function generateDashboardAnalytics(User $user): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            // Vérifier le cache pour le dashboard
            $cachedDashboard = $this->cacheService->getCachedDashboard($user);
            if ($cachedDashboard) {
                // Mettre à jour avec les données en cache
                $this->mapDashboardStructureToAnalytics($analytics, $cachedDashboard);
                return;
            }
            
            $profile = $analytics->entrepreneur_profile ?? [];
            
            // Récupérer le projet de l'utilisateur pour l'analyse
            $projet = \App\Models\Projet::where('user_id', $user->id)->first();
            
            if (!$projet) {
                Log::warning("No project found for user {$user->id}, cannot generate analytics");
                return;
            }
            
            // Préparer les données complètes pour le LLM
            $llmInput = [
                'user_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'registration_date' => $user->created_at->toDateString(),
                    'profile_type' => $user->profile_type,
                    'verification_status' => $user->verification_status,
                ],
                'projet_data' => [
                    'nom_projet' => $projet->nom_projet,
                    'raison_sociale' => $projet->raison_sociale,
                    'description' => $projet->description,
                    'secteurs' => $projet->secteurs,
                    'produits_services' => $projet->produits_services,
                    'cibles' => $projet->cibles,
                    'maturite' => $projet->maturite,
                    'stade_financement' => $projet->stade_financement,
                    'modeles_revenus' => $projet->modeles_revenus,
                    'revenus' => $projet->revenus,
                    'region' => $projet->region,
                    'formalise' => $projet->formalise,
                    'annee_creation' => $projet->annee_creation,
                    'numero_rccm' => $projet->numero_rccm,
                    'nombre_fondateurs' => $projet->nombre_fondateurs,
                    'nombre_fondatrices' => $projet->nombre_fondatrices,
                    'tranches_age_fondateurs' => $projet->tranches_age_fondateurs,
                    'localisation_fondateurs' => $projet->localisation_fondateurs,
                    'taille_equipe' => $projet->taille_equipe,
                    'types_soutien' => $projet->types_soutien,
                    'structures_accompagnement' => $projet->structures_accompagnement,
                    'details_besoins' => $projet->details_besoins,
                    'telephone' => $projet->telephone,
                    'email' => $projet->email,
                    'site_web' => $projet->site_web,
                    'reseaux_sociaux' => $projet->reseaux_sociaux,
                    'is_public' => $projet->is_public,
                    'created_at' => $projet->created_at->toDateString(),
                    'updated_at' => $projet->updated_at->toDateString(),
                ],
                'existing_profile' => $profile,
                'analytics_metadata' => [
                    'last_generated' => $analytics->generated_at?->toDateString(),
                    'user_engagement' => $this->calculateEngagementLevel($user),
                    'profile_completion' => $projet->isOnboardingComplete() ? 100 : 75,
                ]
            ];
            
            $dashboardStructure = $this->generateDashboardStructureWithLLM($llmInput);
            
            // Mettre en cache le dashboard généré
            $this->cacheService->cacheDashboard($user, $dashboardStructure);
            
            // Mappage vers la nouvelle structure ACID
            $this->mapDashboardStructureToAnalytics($analytics, $dashboardStructure);
            
            // Assigner le projet_id pour la relation
            $analytics->update(['projet_id' => $projet->id]);
            
            Log::info("Dashboard analytics generated for user {$user->id} with project {$projet->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to generate dashboard analytics for user {$user->id}: " . $e->getMessage());
        }
    }

    /**
     * Helper function to filter out "non disponible" values
     */
    private function filterNonDisponible($value) 
    {
        if (is_array($value)) {
            return array_filter($value, function($item) {
                return $item !== 'non disponible' && !empty($item);
            });
        }
        
        if ($value === 'non disponible' || empty($value)) {
            return null;
        }
        
        // Map common LLM responses to valid statut_formalisation values
        if ($value === 'à_faire' || $value === 'a_faire') {
            return 'non_formalise';
        }
        
        return $value;
    }
    
    private function mapDashboardStructureToAnalytics(UserAnalytics $analytics, array $structure): void
    {
        $updateData = [
            'derniere_analyse' => now(),
            'version_algorithme' => '2.0-weekly-limits',
            'confidence_score' => 0.85
        ];
        
        // Map data to existing JSON columns only
        if (isset($structure['resume_executif'])) {
            $updateData['executive_summary'] = $structure['resume_executif'];
            
            // Also map individual columns for compatibility
            if (isset($structure['resume_executif']['score_progression'])) {
                $updateData['score_progression'] = $structure['resume_executif']['score_progression'];
            }
            if (isset($structure['resume_executif']['message_principal'])) {
                $updateData['message_principal'] = $structure['resume_executif']['message_principal'];
            }
            if (isset($structure['resume_executif']['trois_actions_cles'])) {
                $updateData['trois_actions_cles'] = $structure['resume_executif']['trois_actions_cles'];
            }
            if (isset($structure['resume_executif']['opportunite_du_mois'])) {
                $updateData['opportunite_du_mois'] = $structure['resume_executif']['opportunite_du_mois'];
            }
            if (isset($structure['resume_executif']['alerte_importante'])) {
                $updateData['alerte_importante'] = $structure['resume_executif']['alerte_importante'];
            }
        }
        
        if (isset($structure['profil_entrepreneur'])) {
            $updateData['entrepreneur_profile'] = $structure['profil_entrepreneur'];
            
            // Also map individual columns for compatibility
            if (isset($structure['profil_entrepreneur']['score_potentiel'])) {
                $updateData['score_potentiel'] = $structure['profil_entrepreneur']['score_potentiel'];
            }
            if (isset($structure['profil_entrepreneur']['niveau_global'])) {
                $updateData['niveau_global'] = $structure['profil_entrepreneur']['niveau_global'];
            }
            if (isset($structure['profil_entrepreneur']['forces'])) {
                $updateData['forces'] = $structure['profil_entrepreneur']['forces'];
            }
            if (isset($structure['profil_entrepreneur']['axes_progression'])) {
                $updateData['axes_progression'] = $structure['profil_entrepreneur']['axes_progression'];
            }
        }

        if (isset($structure['diagnostic_projet'])) {
            $updateData['project_diagnostic'] = $structure['diagnostic_projet'];
            
            // Also map individual columns for compatibility
            if (isset($structure['diagnostic_projet']['score_sante'])) {
                $updateData['score_sante'] = $structure['diagnostic_projet']['score_sante'];
            }
            if (isset($structure['diagnostic_projet']['prochaines_etapes'])) {
                $updateData['prochaines_etapes'] = $structure['diagnostic_projet']['prochaines_etapes'];
            }
        }
        
        if (isset($structure['opportunites_matchees'])) {
            // Map the complete structure for view compatibility
            $updateData['matched_opportunities'] = $structure['opportunites_matchees'];
            
            // Map individual columns (the actual database columns)
            if (isset($structure['opportunites_matchees']['nombre_total'])) {
                $updateData['nombre_opportunites'] = $structure['opportunites_matchees']['nombre_total'];
            }
            if (isset($structure['opportunites_matchees']['top_opportunites'])) {
                $updateData['top_opportunites'] = $structure['opportunites_matchees']['top_opportunites'];
            }
            if (isset($structure['opportunites_matchees']['par_categorie'])) {
                $categories = $structure['opportunites_matchees']['par_categorie'];
                $updateData['count_financement'] = $categories['financement'] ?? 0;
                $updateData['count_formation'] = $categories['formation'] ?? 0;
                $updateData['count_marche'] = $categories['marche'] ?? 0;
                $updateData['count_accompagnement'] = $categories['accompagnement'] ?? 0;
            }
        }
        
        if (isset($structure['regulations'])) {
            // Map individual columns (the actual database columns)
            if (isset($structure['regulations']['conformite_globale'])) {
                $updateData['conformite_globale'] = $structure['regulations']['conformite_globale'];
            }
            if (isset($structure['regulations']['urgent'])) {
                $updateData['urgent_regulations'] = $structure['regulations']['urgent'];
            }
            if (isset($structure['regulations']['a_prevoir'])) {
                $updateData['a_prevoir_regulations'] = $structure['regulations']['a_prevoir'];
            }
            if (isset($structure['regulations']['avantages_disponibles'])) {
                $updateData['avantages_disponibles'] = $structure['regulations']['avantages_disponibles'];
            }
        }
        
        if (isset($structure['partenaires_suggeres'])) {
            // Map individual columns (the actual database columns)
            if (isset($structure['partenaires_suggeres']['nombre_matches'])) {
                $updateData['nombre_partenaires'] = $structure['partenaires_suggeres']['nombre_matches'];
            }
            if (isset($structure['partenaires_suggeres']['top_partenaires'])) {
                $updateData['top_partenaires'] = $structure['partenaires_suggeres']['top_partenaires'];
            }
            if (isset($structure['partenaires_suggeres']['reseau_potentiel'])) {
                $reseau = $structure['partenaires_suggeres']['reseau_potentiel'];
                $updateData['clients_potentiels'] = $reseau['clients_potentiels'] ?? 0;
                $updateData['fournisseurs_potentiels'] = $reseau['fournisseurs_potentiels'] ?? 0;
                $updateData['partenaires_complementaires'] = $reseau['partenaires_complementaires'] ?? 0;
            }
        }
        
        if (isset($structure['insights_marche'])) {
            // Map the complete structure for view compatibility
            $updateData['market_insights'] = $structure['insights_marche'];
            
            // Map individual columns (the actual database columns)
            if (isset($structure['insights_marche']['taille_marche'])) {
                $taille = $structure['insights_marche']['taille_marche'];
                $updateData['taille_marche_local'] = $taille['local'] ?? null;
                $updateData['taille_marche_potentiel'] = $taille['potentiel'] ?? null;
                $updateData['croissance_marche'] = $taille['croissance'] ?? null;
            }
            if (isset($structure['insights_marche']['position_concurrentielle'])) {
                $position = $structure['insights_marche']['position_concurrentielle'];
                $updateData['position_concurrentielle'] = $position['votre_place'] ?? null;
                $updateData['principaux_concurrents'] = $position['principaux_concurrents'] ?? [];
                $updateData['avantage_cle'] = $position['avantage_cle'] ?? null;
            }
            if (isset($structure['insights_marche']['zones_opportunites'])) {
                $updateData['zones_opportunites'] = $structure['insights_marche']['zones_opportunites'];
            }
            if (isset($structure['insights_marche']['conseil_strategique'])) {
                $updateData['conseil_strategique'] = $structure['insights_marche']['conseil_strategique'];
            }
            if (isset($structure['insights_marche']['tendances'])) {
                $updateData['tendances'] = $structure['insights_marche']['tendances'];
            }
        }
        
        // Update niveau_maturite if available
        if (isset($structure['diagnostic_projet']['niveau_maturite'])) {
            $niveauMaturite = $structure['diagnostic_projet']['niveau_maturite'];
            if (!in_array($niveauMaturite, ['non disponible', 'a completer', ''])) {
                $updateData['niveau_maturite'] = $niveauMaturite;
            }
        }
        
        // Update statut_formalisation if available  
        if (isset($structure['diagnostic_projet']['indicateurs_cles']['formalisation']['statut'])) {
            $statutForm = $structure['diagnostic_projet']['indicateurs_cles']['formalisation']['statut'];
            if (!in_array($statutForm, ['non disponible', 'a completer', ''])) {
                // Apply the filter to handle LLM responses
                $statutForm = $this->filterNonDisponible($statutForm);
                
                // Map LLM values to valid enum values
                $statusMapping = [
                    'ok' => 'formalise_basic',
                    'à_faire' => 'non_formalise',
                    'en_cours' => 'en_cours',
                    'partiel' => 'en_cours',
                    'partiellement_formalise' => 'en_cours',
                    'non_complet' => 'en_cours',
                    'incomplet' => 'en_cours',
                    'non disponible' => null,
                    'a completer' => null,
                    '' => null
                ];
                
                if (isset($statusMapping[$statutForm])) {
                    $statutForm = $statusMapping[$statutForm];
                }
                
                if ($statutForm) {
                    $updateData['statut_formalisation'] = $statutForm;
                }
            }
        }
        
        // Attempt to update with cleaned data
        try {
            $analytics->update($updateData);
            Log::info("Dashboard analytics successfully updated for user {$analytics->user_id}");
        } catch (\Exception $e) {
            Log::error("Failed to update analytics for user {$analytics->user_id}: " . $e->getMessage());
        }
    }


    
    /**
     * Récupérer les projets partenaires potentiels de la plateforme
     */
    private function getPartnerProjectsForDiagnostic(array $data, $userModel = null): array
    {
        try {
            $currentUserId = null;
            
            // Identifier l'utilisateur actuel pour l'exclure
            if ($userModel instanceof User) {
                $currentUserId = $userModel->id;
            } elseif (isset($data['user_info']['id'])) {
                $currentUserId = $data['user_info']['id'];
            } elseif (isset($data['projet_data']['user_id'])) {
                $currentUserId = $data['projet_data']['user_id'];
            }
            
            // Extraire les informations du projet actuel pour le matching
            $currentSecteurs = [];
            $currentRegion = null;
            $currentMaturite = null;
            
            if (isset($data['projet_data'])) {
                $projet = $data['projet_data'];
                $currentSecteurs = $projet['secteurs'] ?? [];
                $currentRegion = $projet['region'] ?? null;
                $currentMaturite = $projet['maturite'] ?? null;
            }
            
            // Rechercher les autres projets visibles de la plateforme
            $query = Projet::where('visibilite', true)
                ->whereNotNull('nom_projet')
                ->where('nom_projet', '!=', '');
            
            // Exclure le projet de l'utilisateur actuel
            if ($currentUserId) {
                $query->where('user_id', '!=', $currentUserId);
            }
            
            $projets = $query->with('user')->get();
            
            $partnerProjects = [];
            
            foreach ($projets as $projet) {
                // Calculer le score de synergie
                $score = $this->calculateProjectSynergy($currentSecteurs, $currentRegion, $currentMaturite, $projet);
                
                if ($score > 30) { // Seuil minimum de compatibilité
                    $synergies = $this->identifyPossibleSynergies($currentSecteurs, $currentMaturite, $projet);
                    
                    $partnerProjects[] = [
                        'id' => $projet->id,
                        'nom_projet' => $projet->nom_projet,
                        'raison_sociale' => $projet->raison_sociale,
                        'secteurs' => $projet->secteurs ?? [],
                        'region' => $projet->region,
                        'maturite' => $projet->maturite,
                        'description' => substr($projet->description ?? '', 0, 200) . '...',
                        'contact_email' => $projet->user->email ?? null,
                        'contact_nom' => $projet->user->name ?? null,
                        'score_synergie' => $score,
                        'synergies_possibles' => $synergies,
                        'type_synergie' => $this->determinePartnershipType($synergies)
                    ];
                }
            }
            
            // Trier par score de synergie et prendre les 8 meilleurs
            usort($partnerProjects, function($a, $b) {
                return $b['score_synergie'] <=> $a['score_synergie'];
            });
            
            return array_slice($partnerProjects, 0, 8);
            
        } catch (\Exception $e) {
            Log::error('Error getting partner projects for diagnostic', [
                'error' => $e->getMessage(),
                'user_id' => $currentUserId
            ]);
            
            return [];
        }
    }
    
    /**
     * Calculer le score de synergie entre deux projets
     */
    private function calculateProjectSynergy($currentSecteurs, $currentRegion, $currentMaturite, $projet): int
    {
        $score = 0;
        
        // Score basé sur les secteurs (40% du score total)
        $projetSecteurs = $projet->secteurs ?? [];
        if (!empty($currentSecteurs) && !empty($projetSecteurs)) {
            $intersection = array_intersect($currentSecteurs, $projetSecteurs);
            $union = array_unique(array_merge($currentSecteurs, $projetSecteurs));
            
            if (!empty($intersection)) {
                $score += 40; // Secteurs communs
            } elseif (count($union) <= 4) {
                $score += 25; // Secteurs complémentaires
            }
        }
        
        // Score basé sur la région (25% du score total)
        if ($currentRegion && $projet->region) {
            if ($currentRegion === $projet->region) {
                $score += 25; // Même région
            } else {
                $score += 10; // Régions différentes mais synergie possible
            }
        }
        
        // Score basé sur la maturité (35% du score total)
        if ($currentMaturite && $projet->maturite) {
            $maturiteScores = [
                'idée' => 1,
                'lancement' => 2,
                'croissance' => 3,
                'expansion' => 4
            ];
            
            $currentScore = $maturiteScores[$currentMaturite] ?? 2;
            $projetScore = $maturiteScores[$projet->maturite] ?? 2;
            $diff = abs($currentScore - $projetScore);
            
            if ($diff === 0) {
                $score += 35; // Même niveau de maturité
            } elseif ($diff === 1) {
                $score += 25; // Niveaux adjacents
            } elseif ($diff === 2) {
                $score += 15; // Complémentarité possible
            }
        }
        
        return min($score, 100);
    }
    
    /**
     * Identifier les synergies possibles entre projets
     */
    private function identifyPossibleSynergies($currentSecteurs, $currentMaturite, $projet): array
    {
        $synergies = [];
        
        $projetSecteurs = $projet->secteurs ?? [];
        
        // Synergies sectorielles
        $intersection = array_intersect($currentSecteurs, $projetSecteurs);
        if (!empty($intersection)) {
            $synergies[] = "Collaboration sectorielle en " . implode(', ', $intersection);
        }
        
        // Synergies technologiques
        $techSectors = ['Technologie', 'FinTech', 'EdTech', 'HealthTech', 'AgriTech'];
        $currentTech = array_intersect($currentSecteurs, $techSectors);
        $projetTech = array_intersect($projetSecteurs, $techSectors);
        
        if (!empty($currentTech) && !empty($projetTech)) {
            $synergies[] = "Partage d'expertise technologique";
        }
        
        // Synergies commerciales
        if ($currentMaturite === 'lancement' && $projet->maturite === 'croissance') {
            $synergies[] = "Mentoring et accompagnement expérience marché";
        } elseif ($currentMaturite === 'croissance' && $projet->maturite === 'lancement') {
            $synergies[] = "Opportunité de mentoring et développement réseau";
        }
        
        // Synergies géographiques
        if ($projet->region) {
            $synergies[] = "Développement réseau régional " . $projet->region;
        }
        
        // Synergies de ressources
        $resourceSectors = ['Finance', 'Logistique', 'Marketing', 'Ressources humaines'];
        $complementaryResources = array_diff($resourceSectors, $currentSecteurs);
        $projetResources = array_intersect($projetSecteurs, $complementaryResources);
        
        if (!empty($projetResources)) {
            $synergies[] = "Échange de compétences : " . implode(', ', $projetResources);
        }
        
        return array_slice($synergies, 0, 3); // Maximum 3 synergies
    }
    
    /**
     * Déterminer le type de partenariat principal
     */
    private function determinePartnershipType($synergies): string
    {
        $synergyText = implode(' ', $synergies);
        
        if (strpos($synergyText, 'technologique') !== false) {
            return 'strategique';
        } elseif (strpos($synergyText, 'commercial') !== false || strpos($synergyText, 'marché') !== false) {
            return 'commerciale';
        } elseif (strpos($synergyText, 'mentoring') !== false || strpos($synergyText, 'accompagnement') !== false) {
            return 'operationnelle';
        } else {
            return 'strategique';
        }
    }
    
    /**
     * Formater le contexte vectoriel pour le prompt LLM
     */
    private function formatVectorContextForPrompt(array $vectorContext): string
    {
        $contextualInfo = "🏛️ INSTITUTIONS ACCOMPAGNEMENT DISPONIBLES :\n";
        
        if (!empty($vectorContext['institutions'])) {
            foreach ($vectorContext['institutions'] as $institution) {
                $contextualInfo .= "• {$institution['nom']} ({$institution['type']})\n";
                $contextualInfo .= "  📍 {$institution['region']}\n";
                $contextualInfo .= "  📝 {$institution['description']}\n";
                if (!empty($institution['services'])) {
                    $contextualInfo .= "  🎯 Services: {$institution['services']}\n";
                }
                if (!empty($institution['contact'])) {
                    $contextualInfo .= "  📞 Contact: {$institution['contact']}\n";
                }
                $contextualInfo .= "  ⭐ Score de pertinence: {$institution['similarity_score']}%\n\n";
            }
        } else {
            $contextualInfo .= "Aucune institution trouvée dans la base de données.\n\n";
        }
        
        $contextualInfo .= "PROJETS PARTENAIRES POTENTIELS DE LA PLATEFORME :\n";
        
        if (!empty($vectorContext['partner_projects'])) {
            foreach ($vectorContext['partner_projects'] as $projet) {
                $contextualInfo .= "• **{$projet['nom_projet']}**";
                if (!empty($projet['raison_sociale'])) {
                    $contextualInfo .= " ({$projet['raison_sociale']})";
                }
                $contextualInfo .= "\n";
                $contextualInfo .= "  📍 Région: {$projet['region']}\n";
                $contextualInfo .= "  🏭 Secteurs: " . implode(', ', $projet['secteurs']) . "\n";
                $contextualInfo .= "  📈 Maturité: {$projet['maturite']}\n";
                $contextualInfo .= "  📝 Description: {$projet['description']}\n";
                $contextualInfo .= "  📧 Contact: {$projet['contact_nom']} ({$projet['contact_email']})\n";
                $contextualInfo .= "  🎯 Synergies possibles: " . implode(', ', $projet['synergies_possibles']) . "\n";
                $contextualInfo .= "  💼 Type partenariat: {$projet['type_synergie']}\n";
                $contextualInfo .= "  ⭐ Score de synergie: {$projet['score_synergie']}%\n\n";
            }
        } else {
            $contextualInfo .= "Aucun projet partenaire compatible trouvé sur la plateforme.\n\n";
        }
        
        $contextualInfo .= "🎯 OPPORTUNITÉS DISPONIBLES :\n";
        
        if (!empty($vectorContext['opportunities'])) {
            foreach ($vectorContext['opportunities'] as $opportunity) {
                $contextualInfo .= "• {$opportunity['titre']}\n";
                $contextualInfo .= "  🏷️ Type: {$opportunity['type']}\n";
                $contextualInfo .= "  📝 Description: {$opportunity['description']}\n";
                if (!empty($opportunity['montant'])) {
                    $contextualInfo .= "  💰 Montant: {$opportunity['montant']}\n";
                }
                if (!empty($opportunity['deadline'])) {
                    $contextualInfo .= "  📅 Deadline: {$opportunity['deadline']}\n";
                }
                $contextualInfo .= "  ⭐ Score de pertinence: {$opportunity['similarity_score']}%\n\n";
            }
        } else {
            $contextualInfo .= "Aucune opportunité trouvée dans la base de données.\n\n";
        }
        
        $contextualInfo .= "📋 TEXTES OFFICIELS PERTINENTS :\n";
        
        if (!empty($vectorContext['official_texts'])) {
            foreach ($vectorContext['official_texts'] as $text) {
                $contextualInfo .= "• {$text['titre']}\n";
                $contextualInfo .= "  🏷️ Classification: {$text['classification']}\n";
                if (!empty($text['date_publication'])) {
                    $contextualInfo .= "  📅 Date: {$text['date_publication']}\n";
                }
                if (!empty($text['statut'])) {
                    $contextualInfo .= "  📊 Statut: {$text['statut']}\n";
                }
                $contextualInfo .= "  📄 Contenu: {$text['contenu']}\n";
                $contextualInfo .= "  ⭐ Score de pertinence: {$text['similarity_score']}%\n\n";
            }
        } else {
            $contextualInfo .= "Aucun texte officiel trouvé dans la base de données.\n\n";
        }
        
        return $contextualInfo;
    }
    
    private function generateDashboardStructureWithLLM(array $data): array
    {
        try {
            // Get user project context directly (no more vectorization)
            $user = $data['user_info'] ?? null;
            $userModel = null;
            if ($user instanceof User) {
                $userModel = $user;
            } elseif (is_array($user) && isset($user['id'])) {
                $userModel = User::find($user['id']);
            }
            
            $projectContext = $userModel ? $this->getUserProjectContext($userModel) : [];
            
            // Récupérer les opportunités depuis la recherche vectorielle
            $vectorOpportunities = $this->getOpportunitiesFromVector($data);
            
            // Formater le contexte projet pour le prompt
            $contextualInfo = $this->formatProjectContextForPrompt($projectContext);
            
            // Ajouter les opportunités vectorielles au contexte
            if (!empty($vectorOpportunities)) {
                $contextualInfo .= "\n=== OPPORTUNITÉS DISPONIBLES (NAMESPACE 'opportunites') ===\n";
                foreach ($vectorOpportunities as $opp) {
                    // Use raw content which contains all opportunity details
                    $contextualInfo .= $opp['raw_content'] . "\n\n";
                    $contextualInfo .= "Score de compatibilité: {$opp['similarity_score']}%\n\n";
                    $contextualInfo .= "---\n\n";
                }
            }
            
            $prompt = "DIAGNOSTIC ENTREPRENEURIAL - EXPERT IVOIRIEN

Tu es un consultant expert de l'écosystème entrepreneurial ivoirien.

RÈGLES OPPORTUNITÉS :
- Utilise UNIQUEMENT les opportunités dans === OPPORTUNITÉS DISPONIBLES ===
- Titres et descriptions EXACTS depuis la base de données
- NE PAS inventer d'opportunités fictives

🎯 MISSION : Diagnostic complet basé sur les données disponibles.

📊 DONNÉES DISPONIBLES :
{$contextualInfo}

🚨 RÈGLES STRICTES :
- Utilise UNIQUEMENT les institutions, les textes officiels, opportunités vectorisées et données du projet dans le contexte ci-dessus
- AUCUNE invention d'opportunités, montants, ou partenaires fictifs  
- Si pas de données disponibles -> indique 'non disponible' ou 'à compléter'
- Reste factuel et base toutes recommandations sur le contexte fourni
- Focus sur l'analyse du projet utilisateur avec les vraies ressources disponibles
- Limite les opportunités aux 77 réelles vectorisées dans le namespace 'opportunites'
- Références juridiques précises (utilise UNIQUEMENT les textes officiels du contexte ci-dessus)

📋 CONTRAINTES ÉNUMÉRATIONS (RESPECT STRICT) :
NIVEAU_ENTREPRENEUR: débutant, confirmé, expert
PROFIL_TYPE: innovateur, gestionnaire, commercial, artisan, commerçant  
NIVEAU_MATURITE: idée, lancement, croissance, expansion
CONFORMITE_GLOBALE: conforme, partiel, non_conforme
STATUT_INDICATEUR: ok, à_faire, en_cours
STATUT_FINANCE: sain, stable, fragile
URGENCE_NIVEAU: immédiate, sous_30j, sous_90j
URGENCE_OPPORTUNITE: candidater_avant_7j, candidater_avant_14j, ce_mois, trimestre
VIABILITE: très_forte, forte, moyenne, à_renforcer
POSITION_MARCHE: leader, bien_placé, nouveau, difficile
POTENTIEL_MARCHE: très_élevé, élevé, moyen, faible
TYPE_SYNERGIE: strategique, operationnelle, commerciale

🏗️ STRUCTURE JSON OPTIMISÉE :

{
  \"resume_executif\": {
    \"score_progression\": 75,
    \"message_principal\": \"Projet à fort potentiel avec 3 axes d'amélioration prioritaires. Marché addressable de 150M FCFA identifié.\",
    \"trois_actions_cles\": [\"Finaliser formalisation RCCM (30j - 25K FCFA)\", \"Structurer pitch investisseurs (14j)\", \"Lancer pilot client (45j)\"],
    \"opportunite_du_mois\": \"[Sélectionne LA MEILLEURE opportunité depuis === OPPORTUNITÉS DISPONIBLES === avec son titre EXACT et deadline]\",
    \"alerte_importante\": \"Conformité OHADA requise avant candidature aux financements publics\"
  },
  \"profil_entrepreneur\": {
    \"niveau_global\": \"confirmé\",
    \"score_potentiel\": 80,
    \"forces\": [{\"domaine\": \"Innovation\", \"description\": \"...\"}],
    \"axes_progression\": [{\"domaine\": \"Finance\", \"action_suggeree\": \"...\", \"impact\": \"court_terme\"}],
    \"besoins_formation\": [\"Formation 1\"],
    \"profil_type\": \"innovateur\"
  },
  \"diagnostic_projet\": {
    \"score_sante\": 75,
    \"niveau_maturite\": \"lancement\",
    \"viabilite\": \"forte\",
    \"indicateurs_cles\": {
      \"formalisation\": {\"statut\": \"ok\", \"urgence\": \"sous_90j\"},
      \"finance\": {\"statut\": \"stable\", \"besoin_financement\": true, \"montant_suggere\": \"50000\"},
      \"equipe\": {\"complete\": false, \"besoins\": [\"CTO\"]},
      \"marche\": {\"position\": \"nouveau\", \"potentiel\": \"élevé\"}
    },
    \"prochaines_etapes\": [{\"priorite\": 1, \"action\": \"...\", \"delai\": \"1 mois\"}]
  },
  \"opportunites_matchees\": {
    \"nombre_total\": 8,
    \"top_opportunites\": [
      {\"titre\": \"[titre exact depuis === OPPORTUNITÉS DISPONIBLES ===]\", \"institution\": \"...\", \"score_compatibilite\": 85, \"pourquoi_vous\": \"...\", \"montant_ou_valeur\": \"...\", \"urgence\": \"...\", \"lien\": \"...\"}
    ]
  },
  \"insights_marche\": {
    \"taille_marche\": {\"local\": \"...\", \"potentiel\": \"...\", \"croissance\": \"...\"},
    \"position_concurrentielle\": {\"votre_place\": \"...\", \"principaux_concurrents\": [], \"avantage_cle\": \"...\"},
    \"zones_opportunites\": []
  },
  \"regulations\": {
    \"conformite_globale\": \"partiel\",
    \"urgent\": [{\"obligation\": \"...\", \"deadline\": \"...\", \"cout\": \"...\"}],
    \"a_prevoir\": [{\"obligation\": \"...\", \"echeance\": \"...\"}]
  },
  \"partenaires_suggeres\": {
    \"nombre_matches\": 3,
    \"top_partenaires\": [{\"nom_projet\": \"...\", \"secteurs\": [], \"region\": \"...\", \"contact_nom\": \"...\", \"contact_email\": \"...\", \"proposition_collaboration\": \"...\", \"score_pertinence\": 85}],
    \"reseau_potentiel\": {\"clients_potentiels\": 45, \"fournisseurs_potentiels\": 12, \"partenaires_complementaires\": 8}
  }
}

📊 DONNÉES PROJET À ANALYSER:
" . json_encode($data, JSON_UNESCAPED_UNICODE) . "

🎯 CONSIGNES DE GÉNÉRATION DÉTAILLÉE:

1. RÉSUMÉ EXÉCUTIF - Sois percutant et spécifique:
   - Message principal: 2-3 phrases analysant les forces/faiblesses avec recommandation stratégique claire
   - Actions clés: Actions concrètes, mesurables, avec timeline implicite
   - Opportunité du mois: Opportunité réelle, bien documentée avec deadlines précises
   - Alerte: Identifier les vrais risques business critiques

2. PROFIL ENTREPRENEUR - Analyse psychographique approfondie:
   - Forces: Minimum 3 forces avec descriptions détaillées (2-3 lignes chacune)
   - Axes progression: Minimum 3 axes avec actions suggérées précises et impact timeline
   - Besoins formation: Formations spécifiques disponibles en CI (institutions réelles)

3. DIAGNOSTIC PROJET - Évaluation technique rigoureuse:
   - Prochaines étapes: Minimum 5 étapes avec priorités 1-5, délais réalistes, coûts estimés
   - Indicateurs clés: Analyser TOUS les indicateurs (formalisation, finance, équipe, marché)

4. OPPORTUNITÉS - STRICTEMENT du contexte fourni:
   - OBLIGATOIRE: Utilise UNIQUEMENT les opportunités listées dans le CONTEXTE TEMPS RÉEL ci-dessus
   - Si aucune opportunité dans le contexte: marque nombre_total à 0 et top_opportunites comme tableau vide []
   - INTERDIT de créer des opportunités fictives ou d'exemples
   - Score de compatibilité basé sur le similarity_score fourni dans le contexte
   - Titres, institutions, montants, deadlines : reprendre EXACTEMENT du contexte

5. INSIGHTS MARCHÉ - Données macro-économiques:
   - Chiffres réalistes du marché ivoirien (PIB numérique, nombre startups, investissements)
   - Concurrents réels identifiables
   - Zones géographiques avec justification économique détaillée

6. RÉGULATIONS - STRICTEMENT basé sur les textes officiels fournis:
   - OBLIGATOIRE: Référence les textes officiels du CONTEXTE TEMPS RÉEL ci-dessus
   - Citations directes des textes officiels pertinents avec leur titre
   - Obligations basées sur le contenu réel des PDFs officiels fournis
   - Si pas de texte officiel pertinent dans le contexte: reste générique
   - Coûts et délais : uniquement s'ils sont mentionnés dans les textes officiels

7. PARTENAIRES - PROJETS DE LA PLATEFORME:
   - OBLIGATOIRE: Utilise UNIQUEMENT les projets partenaires listés dans la section PROJETS PARTENAIRES POTENTIELS ci-dessus
   - Si aucun projet partenaire dans le contexte: marque nombre_matches à 0 et top_partenaires comme tableau vide []
   - INTERDIT de créer des projets fictifs - utilise seulement les vrais projets de la plateforme
   - Noms projets, secteurs, régions, contacts : reprendre EXACTEMENT du contexte fourni
   - Score de pertinence basé sur le score_synergie fourni dans le contexte
   - Inclure les informations de contact réelles (nom et email) des porteurs de projet
   - proposition_collaboration basée sur les synergies_possibles identifiées

8. OPTIMISATION RENDU:
   - Messages principaux: max 150 caractères, impact clair
   - Actions clés: format \"Action (délai - coût)\" 
   - Opportunités: titre + institution + deadline + montant
   - Insights marché: chiffres précis, sources identifiables
   - Partenaires: nom + proposition + bénéfice quantifié

GÉNÈRE LE JSON OPTIMISÉ POUR INTERFACE UTILISATEUR:";

            $messages = [
                ['role' => 'system', 'content' => 'Tu es Dr. Kouame N\'Guessan, consultant senior en développement entrepreneurial avec 15 ans d\'expérience dans l\'écosystème startup ivoirien. Ancien directeur de programme chez Jokkolabs Abidjan et expert en financement de startups africaines.

EXPERTISE: Écosystème CI (CGECI, CEPICI, ministères), réglementation OHADA, financement startup, analyse sectorielle, stratégies B2G/B2B.

STYLE DE RENDU OPTIMISÉ:
- Messages clairs et actionnables (éviter le jargon)
- Priorités chiffrées avec impact business quantifié
- Deadlines précises et réalistes
- Montants en FCFA avec sources
- Contacts et liens institutionnels réels
- Recommandations hiérarchisées par ROI/urgence

MISSION: Générer un diagnostic entrepreneurial compact mais riche (6-8k tokens), avec insights immédiatement exploitables. Focus sur l\'actionnable plutôt que la théorie.

OUTPUT: JSON uniquement, structure optimisée pour affichage interface, lisibilité maximale.'],
                ['role' => 'user', 'content' => $prompt]
            ];

            $lm = app(\App\Services\LanguageModelService::class);
            
            Log::info('UserAnalyticsService: Starting dashboard analytics generation', ['prompt_size' => strlen($prompt)]);
            
            $raw = $lm->chat($messages, 'gpt-4.1-mini', null, 6000, [
                'response_format' => ['type' => 'json_object'],
                'reasoning_effort' => 'low',
                'verbosity' => 'medium'
            ]);
            
            Log::info('UserAnalyticsService: Dashboard analytics LLM response', ['raw_length' => strlen($raw), 'raw_preview' => substr($raw, 0, 300)]);
            
            // Log the full resume_executif structure to debug
            $debugParsed = json_decode($raw, true);
            if (isset($debugParsed['resume_executif'])) {
                Log::info('UserAnalyticsService: Resume executif structure', ['resume_executif' => $debugParsed['resume_executif']]);
            }
            
            $parsed = json_decode($raw, true);
            
            if (is_array($parsed)) {
                Log::info('UserAnalyticsService: Dashboard analytics JSON parsing successful', ['top_keys' => array_keys($parsed)]);
                return $parsed;
            }
            
            Log::error('UserAnalyticsService: Dashboard analytics JSON parsing failed', ['raw' => $raw]);
            throw new \Exception('LLM returned invalid JSON structure');
            
        } catch (\Throwable $e) {
            Log::error('Dashboard structure generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getAnalyticsJsonSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'resume_executif' => [
                    'type' => 'object',
                    'properties' => [
                        'score_progression' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'message_principal' => ['type' => 'string'],
                        'trois_actions_cles' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'minItems' => 3,
                            'maxItems' => 3
                        ],
                        'opportunite_du_mois' => ['type' => 'string'],
                        'alerte_importante' => ['type' => ['string', 'null']]
                    ],
                    'required' => ['score_progression', 'message_principal', 'trois_actions_cles', 'opportunite_du_mois', 'alerte_importante'],
                    'additionalProperties' => false
                ],
                'profil_entrepreneur' => [
                    'type' => 'object',
                    'properties' => [
                        'niveau_global' => ['type' => 'string', 'enum' => ['débutant', 'confirmé', 'expert']],
                        'score_potentiel' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'forces' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'domaine' => ['type' => 'string'],
                                    'description' => ['type' => 'string']
                                ],
                                'required' => ['domaine', 'description'],
                                'additionalProperties' => false
                            ]
                        ],
                        'axes_progression' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'domaine' => ['type' => 'string'],
                                    'action_suggeree' => ['type' => 'string'],
                                    'impact' => ['type' => 'string', 'enum' => ['immédiat', 'court_terme', 'long_terme']]
                                ],
                                'required' => ['domaine', 'action_suggeree', 'impact'],
                                'additionalProperties' => false
                            ]
                        ],
                        'besoins_formation' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ],
                        'profil_type' => ['type' => 'string', 'enum' => ['innovateur', 'gestionnaire', 'commercial', 'artisan', 'commerçant']]
                    ],
                    'required' => ['niveau_global', 'score_potentiel', 'forces', 'axes_progression', 'besoins_formation', 'profil_type'],
                    'additionalProperties' => false
                ],
                'diagnostic_projet' => [
                    'type' => 'object',
                    'properties' => [
                        'score_sante' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                        'niveau_maturite' => ['type' => 'string', 'enum' => ['idée', 'lancement', 'croissance', 'expansion']],
                        'viabilite' => ['type' => 'string', 'enum' => ['très_forte', 'forte', 'moyenne', 'à_renforcer']],
                        'indicateurs_cles' => [
                            'type' => 'object',
                            'properties' => [
                                'formalisation' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'statut' => ['type' => 'string', 'enum' => ['ok', 'à_faire', 'en_cours']],
                                        'actions' => ['type' => 'array', 'items' => ['type' => 'string']],
                                        'urgence' => ['type' => 'string', 'enum' => ['immédiate', 'sous_30j', 'sous_90j']]
                                    ],
                                    'required' => ['statut', 'actions', 'urgence'],
                                    'additionalProperties' => false
                                ],
                                'finance' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'statut' => ['type' => 'string', 'enum' => ['sain', 'stable', 'fragile']],
                                        'besoin_financement' => ['type' => 'boolean'],
                                        'montant_suggere' => ['type' => 'string']
                                    ],
                                    'required' => ['statut', 'besoin_financement', 'montant_suggere'],
                                    'additionalProperties' => false
                                ],
                                'equipe' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'complete' => ['type' => 'boolean'],
                                        'besoins' => ['type' => 'array', 'items' => ['type' => 'string']]
                                    ],
                                    'required' => ['complete', 'besoins'],
                                    'additionalProperties' => false
                                ],
                                'marche' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'position' => ['type' => 'string', 'enum' => ['leader', 'bien_placé', 'nouveau', 'difficile']],
                                        'potentiel' => ['type' => 'string', 'enum' => ['très_élevé', 'élevé', 'moyen', 'faible']]
                                    ],
                                    'required' => ['position', 'potentiel'],
                                    'additionalProperties' => false
                                ]
                            ],
                            'required' => ['formalisation', 'finance', 'equipe', 'marche'],
                            'additionalProperties' => false
                        ],
                        'prochaines_etapes' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'priorite' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 5],
                                    'action' => ['type' => 'string'],
                                    'delai' => ['type' => 'string'],
                                    'ressource' => ['type' => 'string']
                                ],
                                'required' => ['priorite', 'action', 'delai', 'ressource'],
                                'additionalProperties' => false
                            ]
                        ]
                    ],
                    'required' => ['score_sante', 'niveau_maturite', 'viabilite', 'indicateurs_cles', 'prochaines_etapes'],
                    'additionalProperties' => false
                ],
                'opportunites_matchees' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre_total' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10],
                        'top_opportunites' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'titre' => ['type' => 'string'],
                                    'type' => ['type' => 'string', 'enum' => ['financement', 'formation', 'marché', 'accompagnement']],
                                    'score_compatibilite' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                    'urgence' => ['type' => 'string', 'enum' => ['candidater_avant_7j', 'ce_mois', 'trimestre']],
                                    'institution' => ['type' => 'string'],
                                    'montant_ou_valeur' => ['type' => 'string'],
                                    'pourquoi_vous' => ['type' => 'string'],
                                    'action_immediate' => ['type' => 'string'],
                                    'lien' => ['type' => ['string', 'null']]
                                ],
                                'required' => ['id', 'titre', 'type', 'score_compatibilite', 'urgence', 'institution', 'montant_ou_valeur', 'pourquoi_vous', 'action_immediate', 'lien'],
                                'additionalProperties' => false
                            ]
                        ],
                        'par_categorie' => [
                            'type' => 'object',
                            'properties' => [
                                'financement' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10],
                                'formation' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10],
                                'marche' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10],
                                'accompagnement' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10]
                            ],
                            'required' => ['financement', 'formation', 'marche', 'accompagnement'],
                            'additionalProperties' => false
                        ]
                    ],
                    'required' => ['nombre_total', 'top_opportunites', 'par_categorie'],
                    'additionalProperties' => false
                ],
                'insights_marche' => [
                    'type' => 'object',
                    'properties' => [
                        'taille_marche' => [
                            'type' => 'object',
                            'properties' => [
                                'local' => ['type' => 'string'],
                                'potentiel' => ['type' => 'string'],
                                'croissance' => ['type' => 'string']
                            ],
                            'required' => ['local', 'potentiel', 'croissance'],
                            'additionalProperties' => false
                        ],
                        'position_concurrentielle' => [
                            'type' => 'object',
                            'properties' => [
                                'votre_place' => ['type' => 'string'],
                                'principaux_concurrents' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'avantage_cle' => ['type' => 'string']
                            ],
                            'required' => ['votre_place', 'principaux_concurrents', 'avantage_cle'],
                            'additionalProperties' => false
                        ],
                        'tendances' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'tendance' => ['type' => 'string'],
                                    'impact_pour_vous' => ['type' => 'string']
                                ],
                                'required' => ['tendance', 'impact_pour_vous'],
                                'additionalProperties' => false
                            ]
                        ],
                        'zones_opportunites' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'region' => ['type' => 'string'],
                                    'potentiel' => ['type' => 'string'],
                                    'raison' => ['type' => 'string']
                                ],
                                'required' => ['region', 'potentiel', 'raison'],
                                'additionalProperties' => false
                            ]
                        ],
                        'conseil_strategique' => ['type' => 'string']
                    ],
                    'required' => ['taille_marche', 'position_concurrentielle', 'tendances', 'zones_opportunites', 'conseil_strategique'],
                    'additionalProperties' => false
                ],
                'regulations' => [
                    'type' => 'object',
                    'properties' => [
                        'conformite_globale' => ['type' => 'string', 'enum' => ['conforme', 'partiel', 'non_conforme']],
                        'urgent' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'obligation' => ['type' => 'string'],
                                    'deadline' => ['type' => 'string'],
                                    'cout' => ['type' => 'string'],
                                    'ou_faire' => ['type' => 'string'],
                                    'contact' => ['type' => 'string']
                                ],
                                'required' => ['obligation', 'deadline', 'cout', 'ou_faire', 'contact'],
                                'additionalProperties' => false
                            ]
                        ],
                        'a_prevoir' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'obligation' => ['type' => 'string'],
                                    'echeance' => ['type' => 'string'],
                                    'description' => ['type' => 'string']
                                ],
                                'required' => ['obligation', 'echeance', 'description'],
                                'additionalProperties' => false
                            ]
                        ],
                        'avantages_disponibles' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['exonération', 'subvention', 'statut_special']],
                                    'description' => ['type' => 'string'],
                                    'conditions' => ['type' => 'string']
                                ],
                                'required' => ['type', 'description', 'conditions'],
                                'additionalProperties' => false
                            ]
                        ]
                    ],
                    'required' => ['conformite_globale', 'urgent', 'a_prevoir', 'avantages_disponibles'],
                    'additionalProperties' => false
                ],
                'partenaires_suggeres' => [
                    'type' => 'object',
                    'properties' => [
                        'nombre_matches' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 5],
                        'top_partenaires' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'nom_projet' => ['type' => 'string'],
                                    'raison_sociale' => ['type' => 'string'],
                                    'secteurs' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    'region' => ['type' => 'string'],
                                    'maturite' => ['type' => 'string'],
                                    'contact_nom' => ['type' => 'string'],
                                    'contact_email' => ['type' => 'string'],
                                    'type_synergie' => ['type' => 'string', 'enum' => ['strategique', 'operationnelle', 'commerciale']],
                                    'score_pertinence' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                    'proposition_collaboration' => ['type' => 'string'],
                                    'synergies_possibles' => ['type' => 'array', 'items' => ['type' => 'string']]
                                ],
                                'required' => ['id', 'nom_projet', 'secteurs', 'region', 'contact_nom', 'contact_email', 'type_synergie', 'score_pertinence', 'proposition_collaboration', 'synergies_possibles'],
                                'additionalProperties' => false
                            ]
                        ],
                        'reseau_potentiel' => [
                            'type' => 'object',
                            'properties' => [
                                'clients_potentiels' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                'fournisseurs_potentiels' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 50],
                                'partenaires_complementaires' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 20]
                            ],
                            'required' => ['clients_potentiels', 'fournisseurs_potentiels', 'partenaires_complementaires'],
                            'additionalProperties' => false
                        ]
                    ],
                    'required' => ['nombre_matches', 'top_partenaires', 'reseau_potentiel'],
                    'additionalProperties' => false
                ]
            ],
            'required' => ['resume_executif', 'profil_entrepreneur', 'diagnostic_projet', 'opportunites_matchees', 'insights_marche', 'regulations', 'partenaires_suggeres'],
            'additionalProperties' => false
        ];
    }

    /**
     * Generate comprehensive user insights
     */
    public function generateUserInsights(User $user): array
    {
        try {
            // Vérifier le cache pour les insights
            $cachedInsights = $this->cacheService->getCachedInsights($user);
            if ($cachedInsights) {
                return $cachedInsights;
            }
            
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            $insights = [
                'user_journey' => [
                    'registration_date' => $user->created_at->toISOString(),
                    'days_since_registration' => $user->created_at->diffInDays(now()),
                    'onboarding_completed' => !empty($analytics->entrepreneur_profile),
                    'profile_completion' => $this->calculateProfileCompletion($user, $analytics->entrepreneur_profile['business_info'] ?? []),
                    'engagement_level' => $this->calculateEngagementLevel($user)
                ],
                'activity_summary' => [
                    'total_chat_messages' => $analytics->metadata['chat_interactions']['total_messages'] ?? 0,
                    'total_uploads' => $analytics->metadata['data_sources']['total_uploads'] ?? 0,
                    'last_activity' => $analytics->metadata['last_activity'] ?? null,
                    'most_used_agent' => $this->getMostUsedAgent($analytics),
                    'favorite_topics' => $this->getTopTopics($analytics)
                ],
                'recommendations' => $this->generateRecommendations($user, $analytics),
                'generated_at' => now()->toISOString()
            ];

            // Mettre en cache les insights générés
            $this->cacheService->cacheInsights($user, $insights);

            // Update analytics with insights
            $analytics->update([
                'metadata' => array_merge($analytics->metadata ?? [], ['insights' => $insights]),
                'generated_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);

            return $insights;
            
        } catch (\Exception $e) {
            Log::error("Failed to generate user insights for user {$user->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get or create user analytics record
     */
    private function getOrCreateUserAnalytics(User $user): UserAnalytics
    {
        return UserAnalytics::firstOrCreate(
            ['user_id' => $user->id],
            [
                'derniere_analyse' => now()
            ]
        );
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion(User $user, array $onboardingData): int
    {
        $requiredFields = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'business_name' => $onboardingData['business_name'] ?? null,
            'business_sector' => isset($onboardingData['business_sector']) ? $onboardingData['business_sector'] : null,
            'business_stage' => $onboardingData['business_stage'] ?? null,
            'target_market' => $onboardingData['target_market'] ?? null,
            'funding_needs' => $onboardingData['funding_needs'] ?? null
        ];

        $completedFields = array_filter($requiredFields, fn($value) => !empty($value));
        
        return round((count($completedFields) / count($requiredFields)) * 100);
    }

    /**
     * Calculate user engagement level
     */
    private function calculateEngagementLevel(User $user): string
    {
        $daysSinceRegistration = $user->created_at->diffInDays(now());
        $totalMessages = UserMessage::whereIn('conversation_id', function($query) use ($user) {
            $query->select('id')->from('user_conversations')->where('user_id', $user->id);
        })->count();

        if ($daysSinceRegistration === 0) {
            return $totalMessages > 0 ? 'high' : 'new';
        }

        $messagesPerDay = $totalMessages / max($daysSinceRegistration, 1);

        if ($messagesPerDay >= 5) return 'high';
        if ($messagesPerDay >= 2) return 'medium';
        if ($messagesPerDay >= 0.5) return 'low';
        
        return 'inactive';
    }

    /**
     * Get most used agent
     */
    private function getMostUsedAgent(UserAnalytics $analytics): ?string
    {
        $agentUsage = $analytics->metadata['chat_interactions']['agent_usage'] ?? [];
        
        if (empty($agentUsage)) return null;
        
        return array_keys($agentUsage, max($agentUsage))[0];
    }

    /**
     * Get top discussion topics
     */
    private function getTopTopics(UserAnalytics $analytics): array
    {
        return array_slice($analytics->metadata['chat_interactions']['topics_discussed'] ?? [], 0, 5);
    }

    /**
     * Generate enhanced personalized recommendations with better UX
     */
    private function generateRecommendations(User $user, UserAnalytics $analytics): array
    {
        $recommendations = [];
        
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        $chatInteractions = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        $dataUploads = $analytics->metadata['data_sources']['total_uploads'] ?? 0;
        $daysSinceReg = $user->created_at->diffInDays(now());
        $engagementLevel = $this->calculateEngagementLevel($user);

        // Prioritized recommendations with actionable steps and estimated impact
        
        // Critical path recommendations (highest impact)
        if ($profileCompletion < 60) {
            $recommendations[] = [
                'type' => 'profile_completion',
                'priority' => 'critical',
                'urgency' => 'immediate',
                'title' => 'Finalisez votre profil entrepreneur',
                'description' => "Profil {$profileCompletion}% complet. +40% de pertinence des recommandations avec profil complet.",
                'action' => 'complete_profile',
                'estimated_time' => '10-15 minutes',
                'impact_score' => 85,
                'next_step' => 'Accédez aux informations projet'
            ];
        }

        // Engagement optimization
        if ($chatInteractions < 3 && $daysSinceReg > 1) {
            $recommendations[] = [
                'type' => 'first_interaction',
                'priority' => 'high',
                'urgency' => 'this_week', 
                'title' => 'Découvrez les capacités IA',
                'description' => 'Testez l\'analyse personnalisée : "Quelles opportunités pour mon secteur ?"',
                'action' => 'start_guided_chat',
                'estimated_time' => '5 minutes',
                'impact_score' => 75,
                'next_step' => 'Commencer une conversation'
            ];
        }

        // Advanced features for engaged users
        if ($chatInteractions >= 5 && $dataUploads === 0) {
            $recommendations[] = [
                'type' => 'data_enhancement',
                'priority' => 'medium',
                'urgency' => 'this_month',
                'title' => 'Consulter l\'agent IA',
                'description' => 'Posez vos questions à l\'agent pour obtenir des conseils personnalisés et recommandations sectorielles.',
                'action' => 'consult_ai_agent',
                'estimated_time' => '2-3 minutes',
                'impact_score' => 85,
                'next_step' => 'Accéder au chat'
            ];
        }

        // Milestone-based recommendations
        if ($profileCompletion >= 80 && $chatInteractions >= 10) {
            $recommendations[] = [
                'type' => 'advanced_features',
                'priority' => 'medium',
                'urgency' => 'this_month',
                'title' => 'Optimisez votre stratégie',
                'description' => 'Lancez un diagnostic complet pour identifier opportunités de financement et partenariats.',
                'action' => 'run_full_diagnostic',
                'estimated_time' => '2-3 minutes',
                'impact_score' => 95,
                'next_step' => 'Diagnostic approfondi'
            ];
        }

        // Inactivity recovery
        if ($engagementLevel === 'inactive' && $daysSinceReg > 7) {
            $recommendations[] = [
                'type' => 'reengagement',
                'priority' => 'low',
                'urgency' => 'flexible',
                'title' => 'Reprenez où vous vous êtes arrêté',
                'description' => 'Nouvelles opportunités disponibles dans votre secteur. Consultez les mises à jour.',
                'action' => 'check_updates',
                'estimated_time' => '2 minutes',
                'impact_score' => 50,
                'next_step' => 'Voir les nouveautés'
            ];
        }

        // Sort by priority and impact
        usort($recommendations, function($a, $b) {
            $priorityWeight = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return ($priorityWeight[$b['priority']] ?? 0) <=> ($priorityWeight[$a['priority']] ?? 0);
        });

        return array_slice($recommendations, 0, 3); // Return top 3 most relevant
    }

    /**
     * Generate enhanced insights with improved structure and metrics
     */
    public function generateEnhancedInsights(User $user): array
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            $daysSinceReg = $user->created_at->diffInDays(now());
            $profileCompletion = $this->calculateProfileCompletion($user, $analytics->entrepreneur_profile['business_info'] ?? []);
            $engagementLevel = $this->calculateEngagementLevel($user);
            
            // Generate contextual insights with optimized rendering
            $insights = [
                'user_journey' => [
                    'registration_date' => $user->created_at->format('d/m/Y'),
                    'days_since_registration' => $daysSinceReg,
                    'journey_stage' => $this->determineJourneyStage($daysSinceReg, $profileCompletion, $engagementLevel),
                    'onboarding_completed' => !empty($analytics->entrepreneur_profile),
                    'profile_completion' => $profileCompletion,
                    'engagement_level' => $engagementLevel,
                    'engagement_score' => $this->calculateEngagementScore($analytics),
                    'milestone_reached' => $this->getLatestMilestone($user, $analytics)
                ],
                'activity_summary' => [
                    'total_interactions' => ($analytics->metadata['chat_interactions']['total_messages'] ?? 0) + ($analytics->metadata['data_sources']['total_uploads'] ?? 0),
                    'chat_activity' => [
                        'total_messages' => $analytics->metadata['chat_interactions']['total_messages'] ?? 0,
                        'avg_messages_per_session' => $this->calculateAvgMessagesPerSession($analytics),
                        'most_active_period' => 'Après-midi' // Simplified for now
                    ],
                    'data_usage' => [
                        'total_uploads' => $analytics->metadata['data_sources']['total_uploads'] ?? 0,
                        'preferred_file_types' => array_keys($analytics->metadata['data_sources']['file_types'] ?? []),
                        'total_data_mb' => $analytics->metadata['data_sources']['total_size_mb'] ?? 0
                    ],
                    'last_activity' => $analytics->metadata['last_activity'] ?? null,
                    'activity_trend' => $this->getActivityTrend($analytics),
                    'most_used_agent' => $this->getMostUsedAgent($analytics),
                    'favorite_topics' => $this->getTopTopics($analytics)
                ],
                'performance_metrics' => [
                    'progression_rate' => $this->calculateProgressionRate($user, $analytics),
                    'goal_completion' => $this->getGoalCompletion($analytics),
                    'next_milestone' => $this->getNextMilestone($profileCompletion, $engagementLevel),
                    'estimated_completion_date' => $this->estimateCompletionDate($user, $analytics)
                ],
                'recommendations' => $this->generateRecommendations($user, $analytics),
                'generated_at' => now()->format('d/m/Y à H:i')
            ];

            return $insights;
            
        } catch (\Exception $e) {
            Log::error("Failed to generate enhanced insights for user {$user->id}: " . $e->getMessage());
            return $this->generateFallbackInsights($user);
        }
    }

    // Helper methods for enhanced insights
    
    private function determineJourneyStage(int $daysSinceReg, int $profileCompletion, string $engagementLevel): string
    {
        if ($daysSinceReg <= 1) return 'onboarding';
        if ($profileCompletion < 60) return 'setup';
        if ($engagementLevel === 'high') return 'active_user';
        if ($engagementLevel === 'medium') return 'exploring';
        return 'dormant';
    }

    private function calculateEngagementScore(UserAnalytics $analytics): int
    {
        $messages = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        $uploads = $analytics->metadata['data_sources']['total_uploads'] ?? 0;
        $sessions = $analytics->metadata['chat_interactions']['total_sessions'] ?? 1;
        
        $baseScore = min(($messages * 2) + ($uploads * 5), 70);
        $sessionBonus = min($sessions * 3, 20);
        $consistencyBonus = $this->getConsistencyBonus($analytics);
        
        return min($baseScore + $sessionBonus + $consistencyBonus, 100);
    }

    private function getConsistencyBonus(UserAnalytics $analytics): int
    {
        $lastActivity = $analytics->metadata['last_activity'] ?? null;
        if (!$lastActivity) return 0;
        
        $daysSinceLastActivity = now()->diffInDays($lastActivity);
        if ($daysSinceLastActivity <= 1) return 10;
        if ($daysSinceLastActivity <= 3) return 5;
        return 0;
    }

    private function getLatestMilestone(User $user, UserAnalytics $analytics): ?string
    {
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        $messages = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        
        if ($messages >= 20) return 'Expert utilisateur';
        if ($profileCompletion >= 90) return 'Profil complet';
        if ($messages >= 10) return 'Utilisateur actif';
        if ($profileCompletion >= 60) return 'Profil configuré';
        if ($messages >= 1) return 'Premier échange';
        return 'Inscription complétée';
    }

    private function calculateAvgMessagesPerSession(UserAnalytics $analytics): float
    {
        $totalMessages = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        $totalSessions = max($analytics->metadata['chat_interactions']['total_sessions'] ?? 1, 1);
        return round($totalMessages / $totalSessions, 1);
    }

    private function getActivityTrend(UserAnalytics $analytics): string
    {
        $lastActivity = $analytics->metadata['last_activity'] ?? null;
        if (!$lastActivity) return 'nouvelle_inscription';
        
        $daysSinceLastActivity = now()->diffInDays($lastActivity);
        if ($daysSinceLastActivity <= 1) return 'très_actif';
        if ($daysSinceLastActivity <= 3) return 'actif';
        if ($daysSinceLastActivity <= 7) return 'modéré';
        return 'inactif';
    }

    private function calculateProgressionRate(User $user, UserAnalytics $analytics): float
    {
        $daysSinceReg = max($user->created_at->diffInDays(now()), 1);
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        return round($profileCompletion / $daysSinceReg, 2);
    }

    private function getGoalCompletion(UserAnalytics $analytics): array
    {
        $profileComplete = ($analytics->entrepreneur_profile['completion_score'] ?? 0) >= 90;
        $activeUser = ($analytics->metadata['chat_interactions']['total_messages'] ?? 0) >= 10;
        $dataUploaded = ($analytics->metadata['data_sources']['total_uploads'] ?? 0) > 0;
        
        return [
            'profile_setup' => $profileComplete,
            'platform_exploration' => $activeUser,
            'data_integration' => $dataUploaded
        ];
    }

    private function getNextMilestone(int $profileCompletion, string $engagementLevel): string
    {
        if ($profileCompletion < 60) return 'Compléter le profil';
        if ($engagementLevel === 'low') return 'Explorer les fonctionnalités';
        if ($engagementLevel === 'medium') return 'Lancer un diagnostic complet';
        return 'Optimiser sa stratégie business';
    }

    private function estimateCompletionDate(User $user, UserAnalytics $analytics): ?string
    {
        $progressionRate = $this->calculateProgressionRate($user, $analytics);
        if ($progressionRate <= 0) return null;
        
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        $remainingProgress = 100 - $profileCompletion;
        $estimatedDays = ceil($remainingProgress / $progressionRate);
        
        return now()->addDays($estimatedDays)->format('d/m/Y');
    }

    private function generateFallbackInsights(User $user): array
    {
        return [
            'user_journey' => [
                'registration_date' => $user->created_at->format('d/m/Y'),
                'journey_stage' => 'onboarding',
                'profile_completion' => 0,
                'engagement_level' => 'new'
            ],
            'recommendations' => [[
                'type' => 'getting_started',
                'priority' => 'high',
                'title' => 'Bienvenue sur LagentO',
                'description' => 'Commencez par compléter votre profil entrepreneur.',
                'action' => 'complete_profile'
            ]],
            'generated_at' => now()->format('d/m/Y à H:i')
        ];
    }

    /**
     * Generate simplified summary data for the main agent
     * Only includes: lagento_context, opportunities, user projects, and analytics
     */
    public function getAgentSummaryData(User $user): array
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            $projet = $user->projets()->latest()->first();

            return [
                // 1. Contexte LagentO (vectorisé)
                'lagento_context' => $this->getLagentOContextSummary(),
                
                // 2. Opportunités pertinentes pour l'utilisateur
                'opportunities' => $this->getRelevantOpportunities($user, $projet),
                
                // 3. Projet utilisateur actuel
                'user_project' => $projet ? [
                    'id' => $projet->id,
                    'nom' => $projet->nom_projet,
                    'secteurs' => $projet->secteurs,
                    'stade' => $projet->stade_financement,
                    'maturite' => $projet->maturite,
                    'region' => $projet->region,
                    'formalise' => $projet->formalise,
                    'revenus' => $projet->revenus
                ] : null,
                
                // 4. Analytics utilisateur essentielles
                'user_analytics' => [
                    'niveau_global' => $analytics->niveau_global,
                    'score_potentiel' => $analytics->score_potentiel,
                    'forces' => $analytics->forces ?? [],
                    'axes_progression' => $analytics->axes_progression ?? [],
                    'message_principal' => $analytics->message_principal,
                    'trois_actions_cles' => $analytics->trois_actions_cles ?? [],
                    'opportunite_du_mois' => $analytics->opportunite_du_mois
                ],
                
                // 5. Métadonnées
                'summary_metadata' => [
                    'user_id' => $user->id,
                    'generated_at' => now()->toISOString(),
                    'context_source' => 'pinecone_lagento_context',
                    'has_project' => !is_null($projet)
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to generate agent summary data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'error' => 'Unable to generate summary data',
                'lagento_context' => $this->getLagentOContextSummary(),
                'opportunities' => [],
                'user_project' => null,
                'user_analytics' => [],
                'summary_metadata' => [
                    'user_id' => $user->id,
                    'generated_at' => now()->toISOString(),
                    'error' => true
                ]
            ];
        }
    }

    /**
     * Get LagentO context summary from vectorized corpus
     */
    private function getLagentOContextSummary(): array
    {
        try {
            // Test search to verify Pinecone context availability
            $searchResults = $this->vectorService->searchSimilar(
                query: "contexte lagento",
                topK: 3,
                namespace: 'lagento_context'
            );

            $hasContent = !empty($searchResults);
            $sampleContent = [];
            
            if ($hasContent) {
                foreach ($searchResults as $result) {
                    if (isset($result['metadata']['content'])) {
                        $sampleContent[] = substr($result['metadata']['content'], 0, 200) . '...';
                    }
                }
            }

            return [
                'status' => $hasContent ? 'available' : 'empty',
                'total_chunks' => count($searchResults),
                'coverage' => $hasContent ? 'complete' : 'unavailable',
                'sample_content' => $sampleContent,
                'description' => 'Contexte vectorisé dans Pinecone avec OpenAI embeddings'
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'total_chunks' => 0,
                'coverage' => 'unavailable',
                'description' => 'Erreur lors de l\'accès au contexte vectorisé Pinecone'
            ];
        }
    }

    /**
     * Get relevant opportunities for the user
     */
    private function getRelevantOpportunities(User $user, ?Projet $projet): array
    {
        try {
            $query = Opportunite::query();
            
            // If no project, get general opportunities (all types)
            if (!$projet) {
                $query->where('statut', 'ouvert')
                      ->orderBy('created_at', 'desc');
            } else {
                // Filter by user's project characteristics
                $query->where('statut', 'ouvert');
                
                // Filter by region if available  
                if ($projet->region) {
                    $query->where(function($q) use ($projet) {
                        $q->whereJsonContains('regions_cibles', $projet->region)
                          ->orWhereJsonContains('regions_cibles', 'National');
                    });
                }
                
                // Filter by sector if available (secteurs is array in projet)
                if ($projet->secteurs && is_array($projet->secteurs)) {
                    $query->where(function($q) use ($projet) {
                        $secteurQuery = $q;
                        foreach ($projet->secteurs as $secteur) {
                            $secteurQuery->orWhereJsonContains('secteurs', $secteur);
                        }
                        $secteurQuery->orWhereJsonContains('secteurs', 'TOUS_SECTEURS')
                                    ->orWhere('description', 'ILIKE', '%NUMERIQUE%')
                                    ->orWhere('titre', 'ILIKE', '%digital%')
                                    ->orWhere('titre', 'ILIKE', '%tech%');
                    });
                }
            }
            
            $opportunities = $query->limit(5)->get()->map(function($opp) {
                return [
                    'id' => $opp->id,
                    'titre' => $opp->titre,
                    'type' => $opp->type,
                    'description' => substr($opp->description, 0, 150) . '...',
                    'remuneration' => $opp->remuneration,
                    'regions_cibles' => $opp->regions_cibles,
                    'secteurs' => $opp->secteurs,
                    'date_limite' => $opp->date_limite?->format('Y-m-d'),
                    'statut' => $opp->statut,
                    'lien_externe' => $opp->lien_externe
                ];
            });

            return $opportunities->toArray();
            
        } catch (\Exception $e) {
            Log::error('Failed to get relevant opportunities', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get user project context directly without vectorization
     */
    private function getUserProjectContext(User $user): array
    {
        try {
            $projet = Projet::where('user_id', $user->id)->latest()->first();
            
            if (!$projet) {
                return [];
            }

            return [
                'project_name' => $projet->nom_projet,
                'company_name' => $projet->raison_sociale,
                'description' => $projet->description,
                'sectors' => $projet->secteurs ?? [],
                'products_services' => $projet->produits_services ?? [],
                'targets' => $projet->cibles ?? [],
                'maturity' => $projet->maturite,
                'funding_stage' => $projet->stade_financement,
                'revenue_models' => $projet->modeles_revenus ?? [],
                'region' => $projet->region,
                'team_size' => $projet->taille_equipe,
                'founders_count' => $projet->nombre_fondateurs,
                'female_founders_count' => $projet->nombre_fondatrices,
                'founders_age_ranges' => $projet->tranches_age_fondateurs ?? [],
                'founders_location' => $projet->localisation_fondateurs,
                'support_structures' => $projet->structures_accompagnement ?? [],
                'support_types' => $projet->types_soutien ?? [],
                'additional_needs' => $projet->details_besoins,
                'formalized' => $projet->formalise,
                'creation_year' => $projet->annee_creation,
                'is_public' => $projet->is_public,
                'contact_phone' => $projet->telephone,
                'contact_email' => $projet->email,
                'website' => $projet->site_web,
                'representative_name' => $projet->nom_representant,
                'representative_role' => $projet->role_representant,
                'social_networks' => $projet->reseaux_sociaux ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get user project context', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Format project context for LLM prompt
     */
    private function formatProjectContextForPrompt(array $projectContext): string
    {
        if (empty($projectContext)) {
            return "Aucune information de projet disponible.";
        }

        $formattedContext = "INFORMATIONS DU PROJET:\n";
        
        if (!empty($projectContext['project_name'])) {
            $formattedContext .= "- Nom du projet: {$projectContext['project_name']}\n";
        }
        
        if (!empty($projectContext['company_name'])) {
            $formattedContext .= "- Raison sociale: {$projectContext['company_name']}\n";
        }
        
        if (!empty($projectContext['description'])) {
            $formattedContext .= "- Description: {$projectContext['description']}\n";
        }
        
        if (!empty($projectContext['sectors'])) {
            $sectors = is_array($projectContext['sectors']) ? implode(', ', $projectContext['sectors']) : $projectContext['sectors'];
            $formattedContext .= "- Secteurs d'activité: {$sectors}\n";
        }
        
        if (!empty($projectContext['maturity'])) {
            $formattedContext .= "- Niveau de maturité: {$projectContext['maturity']}\n";
        }
        
        if (!empty($projectContext['funding_stage'])) {
            $formattedContext .= "- Stade de financement: {$projectContext['funding_stage']}\n";
        }
        
        if (!empty($projectContext['region'])) {
            $formattedContext .= "- Région: {$projectContext['region']}\n";
        }
        
        if (!empty($projectContext['team_size'])) {
            $formattedContext .= "- Taille de l'équipe: {$projectContext['team_size']}\n";
        }
        
        if (isset($projectContext['founders_count']) && isset($projectContext['female_founders_count'])) {
            $formattedContext .= "- Fondateurs: {$projectContext['founders_count']} total dont {$projectContext['female_founders_count']} femmes\n";
        }
        
        if (!empty($projectContext['support_types'])) {
            $support = is_array($projectContext['support_types']) ? implode(', ', $projectContext['support_types']) : $projectContext['support_types'];
            $formattedContext .= "- Types de soutien recherchés: {$support}\n";
        }
        
        return $formattedContext;
    }

    /**
     * Get opportunities from vector search using 'opportunites' namespace
     */
    private function getOpportunitiesFromVector(array $data): array
    {
        try {
            // Build search query based on user data
            $searchQuery = $this->buildOpportunitySearchQuery($data);
            
            // Extraire user pour le cache
            $user = null;
            if (isset($data['user_info']['id'])) {
                $user = \App\Models\User::find($data['user_info']['id']);
            } elseif (isset($data['user_info']) && $data['user_info'] instanceof \App\Models\User) {
                $user = $data['user_info'];
            }
            
            // Vérifier le cache pour la recherche vectorielle
            if ($user) {
                $cachedResults = $this->cacheService->getCachedVectorSearch($user, $searchQuery);
                if ($cachedResults) {
                    return $cachedResults;
                }
            }

            // Search in 'opportunites' namespace
            $searchResults = $this->vectorService->searchSimilar(
                query: $searchQuery,
                topK: 10,
                namespace: 'opportunites',
                filter: []
            );

            $opportunities = [];
            
            foreach ($searchResults as $result) {
                if (isset($result['metadata']['content'])) {
                    // Instead of trying to parse metadata fields that don't exist,
                    // we'll pass the raw content which contains all opportunity details
                    // This content will be processed by the LLM in generateDashboardAnalytics
                    $opportunities[] = [
                        'raw_content' => $result['metadata']['content'],
                        'similarity_score' => round(($result['score'] ?? 0) * 100, 1)
                    ];
                }
            }

            Log::info('Vector opportunities search completed', [
                'query' => $searchQuery,
                'results_count' => count($opportunities),
                'namespace' => 'opportunites'
            ]);

            // Mettre en cache les résultats de recherche vectorielle
            if ($user) {
                $this->cacheService->cacheVectorSearch($user, $searchQuery, $opportunities);
            }

            return $opportunities;
            
        } catch (\Exception $e) {
            Log::error('Failed to get opportunities from vector search', [
                'error' => $e->getMessage(),
                'namespace' => 'opportunites'
            ]);
            
            return [];
        }
    }

    /**
     * Build search query for opportunities based on user data
     */
    private function buildOpportunitySearchQuery(array $data): string
    {
        $queryParts = [];
        
        // Add project sectors if available
        if (isset($data['user_project']['sectors']) && !empty($data['user_project']['sectors'])) {
            $queryParts[] = implode(' ', $data['user_project']['sectors']);
        }
        
        // Add project maturity stage
        if (isset($data['user_project']['maturity'])) {
            $queryParts[] = $data['user_project']['maturity'];
        }
        
        // Add funding stage
        if (isset($data['user_project']['funding_stage'])) {
            $queryParts[] = $data['user_project']['funding_stage'];
        }
        
        // Add region
        if (isset($data['user_project']['region'])) {
            $queryParts[] = $data['user_project']['region'];
        }
        
        // Default search terms for opportunities
        $queryParts[] = 'financement subvention prêt concours incubation accélération entrepreneur startup';
        
        return implode(' ', $queryParts);
    }
}