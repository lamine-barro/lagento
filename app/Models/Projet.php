<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Constants\BusinessConstants;

class Projet extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'projets';

    protected $fillable = [
        // Identité
        'nom_projet',
        'raison_sociale', 
        'description',
        'logo_url',
        
        // Formalisation
        'formalise',
        'annee_creation',
        'numero_rccm',
        
        // Activité
        'secteurs',
        'produits_services',
        'cibles',
        
        // Développement
        'maturite',
        'stade_financement',
        'modeles_revenus',
        'revenus',
        
        // Localisation
        'region',
        'latitude',
        'longitude',
        
        // Contact
        'telephone',
        'email',
        'site_web',
        'nom_representant',
        'role_representant',
        'reseaux_sociaux',
        
        // Équipe
        'nombre_fondateurs',
        'nombre_fondatrices',
        'tranches_age_fondateurs',
        'localisation_fondateurs',
        'taille_equipe',
        
        // Besoins
        'structures_accompagnement',
        'types_soutien',
        'mot_president',
        'abonne_newsletter',
        
        // Meta
        'user_id',
        'is_public',
        'is_verified',
        'last_updated_at'
    ];

    protected $casts = [
        'secteurs' => 'array',
        'produits_services' => 'array',
        'cibles' => 'array',
        'modeles_revenus' => 'array',
        'reseaux_sociaux' => 'array',
        'tranches_age_fondateurs' => 'array',
        'structures_accompagnement' => 'array',
        'types_soutien' => 'array',
        'abonne_newsletter' => 'boolean',
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_updated_at' => 'datetime'
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accesseurs pour les libellés
    public function getSecteursLabelsAttribute(): array
    {
        $labels = [];
        foreach ($this->secteurs ?? [] as $key) {
            $labels[] = BusinessConstants::SECTEURS[$key] ?? $key;
        }
        return $labels;
    }

    public function getCiblesLabelsAttribute(): array
    {
        $labels = [];
        foreach ($this->cibles ?? [] as $key) {
            $labels[] = BusinessConstants::CIBLES[$key] ?? $key;
        }
        return $labels;
    }

    public function getMaturiteLabel(): string
    {
        return BusinessConstants::STADES_MATURITE[$this->maturite] ?? $this->maturite;
    }

    public function getStadeFinancementLabel(): string
    {
        return BusinessConstants::STADES_FINANCEMENT[$this->stade_financement] ?? $this->stade_financement;
    }

    public function getModelesRevenusLabelsAttribute(): array
    {
        $labels = [];
        foreach ($this->modeles_revenus ?? [] as $key) {
            $labels[] = BusinessConstants::MODELES_REVENUS[$key] ?? $key;
        }
        return $labels;
    }

    public function getRevenusLabel(): string
    {
        return BusinessConstants::TRANCHES_REVENUS[$this->revenus] ?? $this->revenus;
    }

    public function getRegionCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude
            ];
        }
        
        return BusinessConstants::REGIONS[$this->region] ?? null;
    }

    public function getNombreFondateursLabel(): string
    {
        return BusinessConstants::NOMBRE_FONDATEURS_OPTIONS[$this->nombre_fondateurs] ?? $this->nombre_fondateurs;
    }

    public function getNombreFondatricesLabel(): string
    {
        return BusinessConstants::NOMBRE_FONDATRICES_OPTIONS[$this->nombre_fondatrices] ?? $this->nombre_fondatrices;
    }

    public function getLocalisationFondateursLabel(): string
    {
        return BusinessConstants::LOCALISATION_FONDATEURS[$this->localisation_fondateurs] ?? $this->localisation_fondateurs;
    }

    public function getTailleEquipeLabel(): string
    {
        return $this->taille_equipe . ' personnes';
    }

    public function getStructuresAccompagnementLabelsAttribute(): array
    {
        return $this->structures_accompagnement ?? [];
    }

    public function getTypesSoutienLabelsAttribute(): array
    {
        $labels = [];
        foreach ($this->types_soutien ?? [] as $key) {
            $labels[] = BusinessConstants::TYPES_SOUTIEN[$key] ?? $key;
        }
        return $labels;
    }

    // Méthodes de validation
    public function isOnboardingComplete(): bool
    {
        // Step 1: Identité obligatoire
        if (empty($this->nom_projet) || empty($this->formalise) || empty($this->region)) {
            return false;
        }

        // Step 2: Pas de champs obligatoires

        // Step 3: Pas de champs obligatoires

        // Step 4: Fondateurs obligatoire (doit être >= 0, car cast en int)
        if (!isset($this->nombre_fondateurs) || !isset($this->nombre_fondatrices)) {
            return false;
        }

        return true;
    }

    public function getOnboardingProgress(): array
    {
        $steps = [
            'step1' => !empty($this->nom_projet) && !empty($this->formalise) && !empty($this->region),
            'step2' => true, // Pas de champs obligatoires
            'step3' => true, // Pas de champs obligatoires  
            'step4' => isset($this->nombre_fondateurs) && isset($this->nombre_fondatrices)
        ];

        return [
            'steps' => $steps,
            'completed' => array_sum($steps),
            'total' => count($steps),
            'percentage' => round((array_sum($steps) / count($steps)) * 100)
        ];
    }

    // Scopes pour les recherches
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    public function scopeBySecteur($query, string $secteur)
    {
        return $query->whereJsonContains('secteurs', $secteur);
    }

    public function scopeByMaturite($query, string $maturite)
    {
        return $query->where('maturite', $maturite);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['nom_projet', 'description'], $term);
    }

    // Mutateurs pour automatic population
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($projet) {
            // Auto-populate coordinates from region if not set
            if (!$projet->latitude || !$projet->longitude) {
                $coordinates = BusinessConstants::REGIONS[$projet->region] ?? null;
                if ($coordinates) {
                    $projet->latitude = $coordinates['lat'];
                    $projet->longitude = $coordinates['lng'];
                }
            }
            
            // Update last_updated_at
            $projet->last_updated_at = now();
        });
    }
}