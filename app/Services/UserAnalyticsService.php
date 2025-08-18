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
    /**
     * Update entrepreneur profile analytics based on onboarding data
     */
    public function updateEntrepreneurProfile(User $user, array $onboardingData): void
    {
        try {
            $analytics = $this->getOrCreateUserAnalytics($user);
            
            // Enrich with lightweight LLM pass (gpt-5-mini) to extract salient tags and summary
            $lmSummary = $this->summarizeBusinessData($onboardingData);

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

            $analytics->update([
                'entrepreneur_profile' => $profile,
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
                'metadata' => array_merge($analytics->metadata ?? [], [
                    'profile_updates' => ($analytics->metadata['profile_updates'] ?? 0) + 1,
                    'last_profile_update' => now()->toISOString()
                ])
            ]);

            Log::info("Entrepreneur profile updated for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to update entrepreneur profile for user {$user->id}: " . $e->getMessage());
        }
    }

    private function summarizeBusinessData(array $data): array
    {
        try {
            $text = json_encode($data, JSON_UNESCAPED_UNICODE);
            if (!$text || strlen($text) < 10) {
                Log::warning('UserAnalyticsService: summarizeBusinessData - insufficient data', ['data_length' => strlen($text ?? '')]);
                return [];
            }
            
            Log::info('UserAnalyticsService: Starting business data summarization', ['data_size' => strlen($text)]);

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'Tu es un analyste business. Sur la base des données de projet et profil, génère un JSON STRICT avec: summary (3 phrases max), keywords (5 FR), risks (≤3), level (débutant|confirmé|expert), potential_score (0-100), strengths[{domaine,description}], improvements[{domaine,action_suggeree,impact}], training_needs[string], profile_type (innovateur|gestionnaire|commercial|artisan|commerçant). Réponds UNIQUEMENT ce JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ];

            $lm = app(\App\Services\LanguageModelService::class);
            $raw = $lm->chat($messages, 'gpt-4.1-mini', 0.2, 20000, ['response_format' => ['type' => 'json_object']]);
            
            Log::info('UserAnalyticsService: LLM response received', ['raw_length' => strlen($raw), 'raw_preview' => substr($raw, 0, 200)]);
            
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                Log::info('UserAnalyticsService: JSON parsing successful', ['keys' => array_keys($parsed)]);
                return $parsed;
            }
            
            Log::error('UserAnalyticsService: JSON parsing failed', ['raw' => $raw]);
            return [];
        } catch (\Throwable $e) {
            Log::error('UserAnalyticsService: summarizeBusinessData exception', ['error' => $e->getMessage()]);
        }
        return [];
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
            
            // Mappage vers la nouvelle structure ACID
            $this->mapDashboardStructureToAnalytics($analytics, $dashboardStructure);
            
            // Assigner le projet_id pour la relation
            $analytics->update(['projet_id' => $projet->id]);
            
            Log::info("Dashboard analytics generated for user {$user->id} with project {$projet->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to generate dashboard analytics for user {$user->id}: " . $e->getMessage());
        }
    }
    
    private function mapDashboardStructureToAnalytics(UserAnalytics $analytics, array $structure): void
    {
        $updateData = [
            'generated_at' => now(),
            'expires_at' => now()->addDays(30),
            'metadata' => array_merge($analytics->metadata ?? [], [
                'derniere_maj' => now()->format('d/m/Y à H:i'),
                'dashboard_generated_at' => now()->toISOString()
            ])
        ];
        
        // 1. Profil Entrepreneur
        if (isset($structure['profil_entrepreneur'])) {
            $profil = $structure['profil_entrepreneur'];
            $updateData = array_merge($updateData, [
                'niveau_global' => $profil['niveau_global'] ?? null,
                'score_potentiel' => $profil['score_potentiel'] ?? null,
                'forces' => $profil['forces'] ?? [],
                'axes_progression' => $profil['axes_progression'] ?? [],
                'besoins_formation' => $profil['besoins_formation'] ?? [],
                'profil_type' => $profil['profil_type'] ?? null,
            ]);
        }
        
        // 2. Diagnostic Projet
        if (isset($structure['diagnostic_projet'])) {
            $diagnostic = $structure['diagnostic_projet'];
            $updateData = array_merge($updateData, [
                'score_sante' => $diagnostic['score_sante'] ?? null,
                'niveau_maturite' => $diagnostic['niveau_maturite'] ?? null,
                'viabilite' => $diagnostic['viabilite'] ?? null,
                'prochaines_etapes' => $diagnostic['prochaines_etapes'] ?? [],
            ]);
            
            // Indicateurs clés
            if (isset($diagnostic['indicateurs_cles'])) {
                $indicateurs = $diagnostic['indicateurs_cles'];
                
                if (isset($indicateurs['formalisation'])) {
                    $updateData['statut_formalisation'] = $indicateurs['formalisation']['statut'] ?? null;
                    $updateData['actions_formalisation'] = $indicateurs['formalisation']['actions'] ?? [];
                    $updateData['urgence_formalisation'] = $indicateurs['formalisation']['urgence'] ?? null;
                }
                
                if (isset($indicateurs['finance'])) {
                    $updateData['statut_finance'] = $indicateurs['finance']['statut'] ?? null;
                    $updateData['besoin_financement'] = $indicateurs['finance']['besoin_financement'] ?? null;
                    $updateData['montant_suggere'] = $indicateurs['finance']['montant_suggere'] ?? null;
                }
                
                if (isset($indicateurs['equipe'])) {
                    $updateData['equipe_complete'] = $indicateurs['equipe']['complete'] ?? null;
                    $updateData['besoins_equipe'] = $indicateurs['equipe']['besoins'] ?? [];
                }
                
                if (isset($indicateurs['marche'])) {
                    $updateData['position_marche'] = $indicateurs['marche']['position'] ?? null;
                    $updateData['potentiel_marche'] = $indicateurs['marche']['potentiel'] ?? null;
                }
            }
        }
        
        // 3. Opportunités Matchées
        if (isset($structure['opportunites_matchees'])) {
            $opportunites = $structure['opportunites_matchees'];
            $updateData = array_merge($updateData, [
                'nombre_opportunites' => $opportunites['nombre_total'] ?? 0,
                'top_opportunites' => $opportunites['top_opportunites'] ?? [],
                'count_financement' => $opportunites['par_categorie']['financement'] ?? 0,
                'count_formation' => $opportunites['par_categorie']['formation'] ?? 0,
                'count_marche' => $opportunites['par_categorie']['marche'] ?? 0,
                'count_accompagnement' => $opportunites['par_categorie']['accompagnement'] ?? 0,
            ]);
        }
        
        // 4. Insights Marché
        if (isset($structure['insights_marche'])) {
            $marche = $structure['insights_marche'];
            $updateData = array_merge($updateData, [
                'taille_marche_local' => $marche['taille_marche']['local'] ?? null,
                'taille_marche_potentiel' => $marche['taille_marche']['potentiel'] ?? null,
                'croissance_marche' => $marche['taille_marche']['croissance'] ?? null,
                'position_concurrentielle' => $marche['position_concurrentielle']['votre_place'] ?? null,
                'principaux_concurrents' => $marche['position_concurrentielle']['principaux_concurrents'] ?? [],
                'avantage_cle' => $marche['position_concurrentielle']['avantage_cle'] ?? null,
                'tendances' => $marche['tendances'] ?? [],
                'zones_opportunites' => $marche['zones_opportunites'] ?? [],
                'conseil_strategique' => $marche['conseil_strategique'] ?? null,
            ]);
        }
        
        // 5. Réglementations
        if (isset($structure['regulations'])) {
            $regulations = $structure['regulations'];
            $updateData = array_merge($updateData, [
                'conformite_globale' => $regulations['conformite_globale'] ?? null,
                'urgent_regulations' => $regulations['urgent'] ?? [],
                'a_prevoir_regulations' => $regulations['a_prevoir'] ?? [],
                'avantages_disponibles' => $regulations['avantages_disponibles'] ?? [],
            ]);
        }
        
        // 6. Partenaires Suggérés
        if (isset($structure['partenaires_suggeres'])) {
            $partenaires = $structure['partenaires_suggeres'];
            $updateData = array_merge($updateData, [
                'nombre_partenaires' => $partenaires['nombre_matches'] ?? 0,
                'top_partenaires' => $partenaires['top_partenaires'] ?? [],
                'clients_potentiels' => $partenaires['reseau_potentiel']['clients_potentiels'] ?? 0,
                'fournisseurs_potentiels' => $partenaires['reseau_potentiel']['fournisseurs_potentiels'] ?? 0,
                'partenaires_complementaires' => $partenaires['reseau_potentiel']['partenaires_complementaires'] ?? 0,
            ]);
        }
        
        // 7. Résumé Exécutif
        if (isset($structure['resume_executif'])) {
            $resume = $structure['resume_executif'];
            $updateData = array_merge($updateData, [
                'message_principal' => $resume['message_principal'] ?? null,
                'trois_actions_cles' => $resume['trois_actions_cles'] ?? [],
                'opportunite_du_mois' => $resume['opportunite_du_mois'] ?? null,
                'alerte_importante' => $resume['alerte_importante'] ?? null,
                'score_progression' => $resume['score_progression'] ?? null,
            ]);
        }
        
        Log::info('UserAnalyticsService: Mapping dashboard structure to analytics', [
            'keys_to_update' => array_keys($updateData),
            'structure_keys' => array_keys($structure),
            'resume_executif_exists' => isset($structure['resume_executif']),
            'message_principal' => $updateData['message_principal'] ?? 'NOT_SET'
        ]);
        
        $analytics->update($updateData);
        
        Log::info('UserAnalyticsService: Analytics updated successfully', [
            'message_principal_after' => $analytics->fresh()->message_principal ?? 'NULL'
        ]);
    }
    
    private function generateDashboardStructureWithLLM(array $data): array
    {
        try {
            $prompt = "DIAGNOSTIC ENTREPRENEURIAL APPROFONDI - CÔTE D'IVOIRE

Tu es un consultant senior spécialisé en développement entrepreneurial en Côte d'Ivoire, avec 15+ ans d'expérience dans l'écosystème startup africain. Analyse ce projet en profondeur et génère un diagnostic complet, détaillé et actionnable.

🎯 OBJECTIF : Créer un diagnostic stratégique de 8-10k tokens qui transforme les données brutes en insights actionnables, avec des recommandations précises et contextualisées pour le marché ivoirien.

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

🏗️ STRUCTURE JSON COMPLÈTE À GÉNÉRER :

{
  \"resume_executif\": {
    \"score_progression\": 75,
    \"message_principal\": \"Votre startup présente un potentiel prometteur...\",
    \"trois_actions_cles\": [\"Action 1\", \"Action 2\", \"Action 3\"],
    \"opportunite_du_mois\": \"Description opportunité\",
    \"alerte_importante\": \"Alert si nécessaire\"
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
      {\"titre\": \"Programme Innovation Numérique CI 2025\", \"institution\": \"Ministère de l'Économie Numérique\", \"score_compatibilite\": 92, \"pourquoi_vous\": \"Votre expertise en IA et data analytics correspond parfaitement aux priorités gouvernementales de transformation numérique. Votre modèle B2G est un atout majeur.\", \"montant_ou_valeur\": \"75 000 000 FCFA\", \"urgence\": \"candidater_avant_7j\", \"lien\": \"https://min-numerique.gouv.ci/appels-projets\"},
      {\"titre\": \"Fonds d'Amorçage Orange Digital Ventures\", \"institution\": \"Orange Côte d'Ivoire\", \"score_compatibilite\": 88, \"pourquoi_vous\": \"Votre focus EdTech et solutions B2B dans l'écosystème numérique ivoirien aligne avec leur stratégie d'investissement.\", \"montant_ou_valeur\": \"50 000 000 FCFA + mentorat\", \"urgence\": \"candidater_avant_14j\", \"lien\": \"https://orange-ci.com/ventures\"}
    ]
  },
  \"insights_marche\": {
    \"taille_marche\": {\"local\": \"Le marché des solutions numériques B2B en CI représente 180+ milliards FCFA avec 350+ startups actives\", \"potentiel\": \"Croissance projetée de 25% annuel grâce aux initiatives gouvernementales de digitalisation\", \"croissance\": \"Taux de croissance annuel de 22% dans le secteur numérique ivoirien (2020-2024)\"},
    \"position_concurrentielle\": {\"votre_place\": \"Nouveau entrant avec différenciation forte sur l'IA appliquée à l'éducation et au secteur public\", \"principaux_concurrents\": [\"CinetPay (fintech)\", \"Julaya (e-commerce)\", \"Akendewa (EdTech)\"], \"avantage_cle\": \"Spécialisation data/IA unique sur le marché B2G éducatif ivoirien\"},
    \"zones_opportunites\": [
      {\"region\": \"Abidjan (Plateau/Cocody)\", \"raison\": \"Concentration des ministères, directions générales et entreprises tech. Hub économique avec 60% des décideurs publics.\"},
      {\"region\": \"Yamoussoukro\", \"raison\": \"Capitale politique, siège des institutions et universités publiques. Marché B2G prioritaire.\"},
      {\"region\": \"San Pedro\", \"raison\": \"Pôle économique en développement, besoins croissants en solutions numériques pour l'administration portuaire.\"}
    ]
  },
  \"regulations\": {
    \"conformite_globale\": \"partiel\",
    \"urgent\": [
      {\"obligation\": \"Obtention du numéro RCCM pour formalisation juridique complète\", \"deadline\": \"dans 90 jours maximum\", \"cout\": \"25 000 FCFA (frais de dossier + publication)\"},
      {\"obligation\": \"Déclaration CNPS pour couverture sociale équipe\", \"deadline\": \"avant premier recrutement\", \"cout\": \"Variable selon masse salariale\"}
    ],
    \"a_prevoir\": [
      {\"obligation\": \"Mise en conformité RGPD/Protection données personnelles\", \"echeance\": \"6 mois (avant déploiement B2G)\"},
      {\"obligation\": \"Certification ISO 27001 pour sécurité données (optionnel mais recommandé B2G)\", \"echeance\": \"12-18 mois\"},
      {\"obligation\": \"Déclaration fiscale startup numérique (régime spécial disponible)\", \"echeance\": \"Avant fin d'exercice fiscal\"}
    ]
  },
  \"partenaires_suggeres\": {
    \"nombre_matches\": 5,
    \"top_partenaires\": [
      {\"nom_projet\": \"Jokkolabs Abidjan\", \"secteur\": \"Hub d'innovation et incubation\", \"localisation\": \"Abidjan, Plateau\", \"proposition_collaboration\": \"Accès à un écosystème de 200+ startups, événements networking hebdomadaires, mentors experts secteur public, espaces de coworking premium.\", \"score_pertinence\": 91, \"type_synergie\": \"strategique\"},
      {\"nom_projet\": \"Hub Ivoire Tech\", \"secteur\": \"Accélération et financement tech\", \"localisation\": \"Abidjan, Cocody\", \"proposition_collaboration\": \"Programme d'accélération 6 mois, accès aux financements partenaires, mise en relation avec clients B2G, formations spécialisées.\", \"score_pertinence\": 87, \"type_synergie\": \"operationnelle\"}
    ]
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

4. OPPORTUNITÉS - Base de données réelle CI:
   - Minimum 6-8 opportunités diverses (subventions, concours, incubation, financement)
   - Institutions réelles ivoiriennes avec contacts/liens fictifs mais réalistes
   - Scores de compatibilité justifiés, montants en FCFA

5. INSIGHTS MARCHÉ - Données macro-économiques:
   - Chiffres réalistes du marché ivoirien (PIB numérique, nombre startups, investissements)
   - Concurrents réels identifiables
   - Zones géographiques avec justification économique détaillée

6. RÉGULATIONS - Conformité juridique précise:
   - Obligations réelles du droit ivoirien (OHADA, CEPICI, etc.)
   - Coûts en FCFA réels, délais administratifs authentiques
   - Avantages fiscaux disponibles (startup act, zones franches)

7. PARTENAIRES - Écosystème local:
   - Institutions réelles (Jokkolabs, Impact Hub, CGECI, etc.)
   - Propositions de collaboration détaillées et réalistes
   - Synergies stratégiques expliquées

GÉNÈRE LE JSON COMPLET, DÉTAILLÉ ET RICHE:";

            $messages = [
                ['role' => 'system', 'content' => 'Tu es Dr. Kouame N\'Guessan, consultant senior en développement entrepreneurial avec 15 ans d\'expérience dans l\'écosystème startup ivoirien. Ancien directeur de programme chez Jokkolabs Abidjan et expert en financement de startups africaines. 

EXPERTISE: Écosystème CI (CGECI, CEPICI, ministères), réglementation OHADA, financement startup, analyse sectorielle, stratégies B2G/B2B.

MISSION: Générer un diagnostic entrepreneurial de niveau consultant senior (8-10k tokens), avec insights actionnables, données réelles du marché ivoirien, et recommandations stratégiques précises. AUCUNE données génériques - tout doit être contextualisé Côte d\'Ivoire.

OUTPUT: JSON uniquement, détaillé, professionnel, exploitable immédiatement par l\'entrepreneur.'],
                ['role' => 'user', 'content' => $prompt]
            ];

            $lm = app(\App\Services\LanguageModelService::class);
            
            Log::info('UserAnalyticsService: Starting dashboard analytics generation', ['prompt_size' => strlen($prompt)]);
            
            $raw = $lm->chat($messages, 'gpt-4.1-mini', 0.3, 20000, [
                'response_format' => ['type' => 'json_object']
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
                                    'secteur' => ['type' => 'string'],
                                    'localisation' => ['type' => 'string'],
                                    'type_synergie' => ['type' => 'string', 'enum' => ['client', 'fournisseur', 'complémentaire', 'stratégique']],
                                    'score_pertinence' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                    'proposition_collaboration' => ['type' => 'string'],
                                    'benefice_mutuel' => ['type' => 'string']
                                ],
                                'required' => ['id', 'nom_projet', 'secteur', 'localisation', 'type_synergie', 'score_pertinence', 'proposition_collaboration', 'benefice_mutuel'],
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

            // Update analytics with insights
            $analytics->update([
                'executive_summary' => $insights,
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
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
                'metadata' => []
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
            'business_sector' => $onboardingData['business_sector'] ?? null,
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
     * Generate personalized recommendations
     */
    private function generateRecommendations(User $user, UserAnalytics $analytics): array
    {
        $recommendations = [];
        
        $profileCompletion = $analytics->entrepreneur_profile['completion_score'] ?? 0;
        $chatInteractions = $analytics->metadata['chat_interactions']['total_messages'] ?? 0;
        $dataUploads = $analytics->metadata['data_sources']['total_uploads'] ?? 0;

        // Profile completion recommendations
        if ($profileCompletion < 80) {
            $recommendations[] = [
                'type' => 'profile_completion',
                'priority' => 'high',
                'title' => 'Complétez votre profil entrepreneur',
                'description' => 'Votre profil est complété à ' . $profileCompletion . '%. Complétez-le pour de meilleures recommandations.',
                'action' => 'complete_profile'
            ];
        }

        // Engagement recommendations
        if ($chatInteractions < 5) {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'title' => 'Explorez LAgentO davantage',
                'description' => 'Posez des questions sur la création d\'entreprise, les financements ou les réglementations.',
                'action' => 'start_chat'
            ];
        }

        // Data source recommendations
        if ($dataUploads === 0) {
            $recommendations[] = [
                'type' => 'data_sources',
                'priority' => 'medium',
                'title' => 'Ajoutez vos documents business',
                'description' => 'Téléchargez votre business plan ou documents pour des analyses personnalisées.',
                'action' => 'upload_documents'
            ];
        }

        return $recommendations;
    }
}