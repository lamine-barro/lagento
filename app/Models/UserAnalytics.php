<?php

namespace App\Models;

use App\Constants\BusinessConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnalytics extends Model
{
    use HasFactory;

    protected $table = 'user_analytics';

    protected $fillable = [
        'user_id',
        'projet_id',
        'generated_at',
        'expires_at',
        'metadata',
        
        // Profil Entrepreneur
        'niveau_global',
        'score_potentiel',
        'forces',
        'axes_progression',
        'besoins_formation',
        'profil_type',
        
        // Diagnostic Projet
        'score_sante',
        'niveau_maturite',
        'viabilite',
        'statut_formalisation',
        'actions_formalisation',
        'urgence_formalisation',
        'statut_finance',
        'besoin_financement',
        'montant_suggere',
        'equipe_complete',
        'besoins_equipe',
        'position_marche',
        'potentiel_marche',
        'prochaines_etapes',
        
        // Opportunités Matchées
        'nombre_opportunites',
        'top_opportunites',
        'count_financement',
        'count_formation',
        'count_marche',
        'count_accompagnement',
        
        // Insights Marché
        'taille_marche_local',
        'taille_marche_potentiel',
        'croissance_marche',
        'position_concurrentielle',
        'principaux_concurrents',
        'avantage_cle',
        'tendances',
        'zones_opportunites',
        'conseil_strategique',
        
        // Réglementations
        'conformite_globale',
        'urgent_regulations',
        'a_prevoir_regulations',
        'avantages_disponibles',
        
        // Partenaires Suggérés
        'nombre_partenaires',
        'top_partenaires',
        'clients_potentiels',
        'fournisseurs_potentiels',
        'partenaires_complementaires',
        
        // Résumé Exécutif
        'message_principal',
        'trois_actions_cles',
        'opportunite_du_mois',
        'alerte_importante',
        'score_progression',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        
        // JSON fields
        'forces' => 'array',
        'axes_progression' => 'array',
        'besoins_formation' => 'array',
        'actions_formalisation' => 'array',
        'besoins_equipe' => 'array',
        'prochaines_etapes' => 'array',
        'top_opportunites' => 'array',
        'principaux_concurrents' => 'array',
        'tendances' => 'array',
        'zones_opportunites' => 'array',
        'urgent_regulations' => 'array',
        'a_prevoir_regulations' => 'array',
        'avantages_disponibles' => 'array',
        'top_partenaires' => 'array',
        'trois_actions_cles' => 'array',
        
        // Boolean fields
        'besoin_financement' => 'boolean',
        'equipe_complete' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    /**
     * Accessors pour compatibilité avec l'ancienne structure
     */
    public function getEntrepreneurProfileAttribute(): array
    {
        return [
            'niveau_global' => $this->niveau_global,
            'score_potentiel' => $this->score_potentiel,
            'forces' => $this->forces ?? [],
            'axes_progression' => $this->axes_progression ?? [],
            'besoins_formation' => $this->besoins_formation ?? [],
            'profil_type' => $this->profil_type,
        ];
    }

    public function getProjectDiagnosticAttribute(): array
    {
        return [
            'score_sante' => $this->score_sante,
            'niveau_maturite' => $this->niveau_maturite,
            'viabilite' => $this->viabilite,
            'indicateurs_cles' => [
                'formalisation' => [
                    'statut' => $this->statut_formalisation,
                    'actions' => $this->actions_formalisation ?? [],
                    'urgence' => $this->urgence_formalisation,
                ],
                'finance' => [
                    'statut' => $this->statut_finance,
                    'besoin_financement' => $this->besoin_financement,
                    'montant_suggere' => $this->montant_suggere,
                ],
                'equipe' => [
                    'complete' => $this->equipe_complete,
                    'besoins' => $this->besoins_equipe ?? [],
                ],
                'marche' => [
                    'position' => $this->position_marche,
                    'potentiel' => $this->potentiel_marche,
                ],
            ],
            'prochaines_etapes' => $this->prochaines_etapes ?? [],
        ];
    }

    public function getMatchedOpportunitiesAttribute(): array
    {
        return [
            'nombre_total' => $this->nombre_opportunites,
            'top_opportunites' => $this->top_opportunites ?? [],
            'par_categorie' => [
                'financement' => $this->count_financement,
                'formation' => $this->count_formation,
                'marche' => $this->count_marche,
                'accompagnement' => $this->count_accompagnement,
            ],
        ];
    }

    public function getMarketInsightsAttribute(): array
    {
        return [
            'taille_marche' => [
                'local' => $this->taille_marche_local,
                'potentiel' => $this->taille_marche_potentiel,
                'croissance' => $this->croissance_marche,
            ],
            'position_concurrentielle' => [
                'votre_place' => $this->position_concurrentielle,
                'principaux_concurrents' => $this->principaux_concurrents ?? [],
                'avantage_cle' => $this->avantage_cle,
            ],
            'tendances' => $this->tendances ?? [],
            'zones_opportunites' => $this->zones_opportunites ?? [],
            'conseil_strategique' => $this->conseil_strategique,
        ];
    }

    public function getRegulationsAttribute(): array
    {
        return [
            'conformite_globale' => $this->conformite_globale,
            'urgent' => $this->urgent_regulations ?? [],
            'a_prevoir' => $this->a_prevoir_regulations ?? [],
            'avantages_disponibles' => $this->avantages_disponibles ?? [],
        ];
    }

    public function getSuggestedPartnersAttribute(): array
    {
        return [
            'nombre_matches' => $this->nombre_partenaires,
            'top_partenaires' => $this->top_partenaires ?? [],
            'reseau_potentiel' => [
                'clients_potentiels' => $this->clients_potentiels,
                'fournisseurs_potentiels' => $this->fournisseurs_potentiels,
                'partenaires_complementaires' => $this->partenaires_complementaires,
            ],
        ];
    }

    public function getExecutiveSummaryAttribute(): array
    {
        return [
            'message_principal' => $this->message_principal,
            'trois_actions_cles' => $this->trois_actions_cles ?? [],
            'opportunite_du_mois' => $this->opportunite_du_mois,
            'alerte_importante' => $this->alerte_importante,
            'score_progression' => $this->score_progression,
        ];
    }

    /**
     * Mutators pour faciliter l'assignation
     */
    public function setEntrepreneurProfileAttribute(array $value): void
    {
        $this->niveau_global = $value['niveau_global'] ?? null;
        $this->score_potentiel = $value['score_potentiel'] ?? null;
        $this->forces = $value['forces'] ?? [];
        $this->axes_progression = $value['axes_progression'] ?? [];
        $this->besoins_formation = $value['besoins_formation'] ?? [];
        $this->profil_type = $value['profil_type'] ?? null;
    }

    /**
     * Scope pour les analytics valides
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope pour les analytics récentes
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Vérifie si les analytics ont expiré
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Obtient le niveau de progression global (0-100)
     */
    public function getProgressLevel(): int
    {
        $scores = [
            $this->score_potentiel ?? 0,
            $this->score_sante ?? 0,
            $this->score_progression ?? 0,
        ];

        return (int) round(array_sum($scores) / count($scores));
    }
}


