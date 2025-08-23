<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Opportunite extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution',
        'institution_type',
        'statut',
        'titre',
        'description',
        'type',
        'pays',
        'regions_ciblees',
        'date_limite_candidature',
        'date_debut',
        'duree',
        'remuneration',
        'nombre_places',
        'secteurs',
        'criteres_eligibilite',
        'contact_email_enrichi',
        'lien_externe',
        'origine_initiative'
    ];

    protected $casts = [
        // Pas de cast automatique pour les dates car elles sont dans des formats variés
    ];

    /**
     * Scope pour filtrer par type d'institution
     */
    public function scopeByInstitutionType(Builder $query, string $type): Builder
    {
        return $query->where('institution_type', $type);
    }

    /**
     * Scope pour filtrer par type d'opportunité
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeByStatut(Builder $query, string $statut): Builder
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour filtrer par secteur d'activité
     */
    public function scopeBySecteur(Builder $query, string $secteur): Builder
    {
        return $query->where('secteurs', 'LIKE', "%{$secteur}%");
    }

    /**
     * Scope pour les opportunités ouvertes
     */
    public function scopeOuvertes(Builder $query): Builder
    {
        return $query->where('statut', 'Ouvert')
                    ->where(function ($q) {
                        $q->whereNull('date_limite_candidature')
                          ->orWhere('date_limite_candidature', '>', now())
                          ->orWhere('date_limite_candidature', 'Continu');
                    });
    }

    /**
     * Accessor pour les secteurs sous forme de tableau
     */
    public function getSecteursArrayAttribute(): array
    {
        if (empty($this->secteurs)) {
            return [];
        }
        
        return array_map('trim', explode(';', $this->secteurs));
    }

    /**
     * Accessor pour les régions ciblees sous forme de tableau
     */
    public function getRegionsCibleesArrayAttribute(): array
    {
        if (empty($this->regions_ciblees)) {
            return ['National'];
        }
        
        return array_map('trim', explode(';', $this->regions_ciblees));
    }

    /**
     * Accessor pour formater la rémunération
     */
    public function getRemunerationFormatteeAttribute(): string
    {
        if (empty($this->remuneration)) {
            return 'Non spécifiée';
        }

        // Si c'est un montant en FCFA
        if (strpos($this->remuneration, 'FCFA') !== false) {
            return $this->remuneration;
        }

        // Si c'est un montant en devises étrangères
        if (preg_match('/\d+/', $this->remuneration)) {
            return $this->remuneration;
        }

        return $this->remuneration;
    }

    /**
     * Accessor pour formater la date limite
     */
    public function getDateLimiteFormatteeAttribute(): string
    {
        if (empty($this->date_limite_candidature)) {
            return 'Non définie';
        }

        if ($this->date_limite_candidature === 'Continu') {
            return 'Candidature continue';
        }

        try {
            return $this->date_limite_candidature->format('d/m/Y');
        } catch (\Exception $e) {
            return $this->date_limite_candidature;
        }
    }

    /**
     * Accessor pour déterminer la couleur du badge de statut
     */
    public function getStatutBadgeColorAttribute(): string
    {
        return match($this->statut) {
            'Ouvert' => 'success',
            'À venir' => 'warning',
            'Fermé' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Accessor pour déterminer la couleur du badge de type
     */
    public function getTypeBadgeColorAttribute(): string
    {
        return match($this->type) {
            'FINANCEMENT' => 'primary',
            'INCUBATION' => 'info', 
            'FORMATION' => 'success',
            'CONCOURS' => 'warning',
            'ACCELERATION' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Scope pour recherche textuelle
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('titre', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('institution', 'LIKE', "%{$search}%")
              ->orWhere('secteurs', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Obtenir les statistiques par type
     */
    public static function getTypeStats(): array
    {
        return static::selectRaw('type, COUNT(*) as count')
                     ->where('statut', 'Ouvert')
                     ->groupBy('type')
                     ->orderBy('count', 'desc')
                     ->pluck('count', 'type')
                     ->toArray();
    }

    /**
     * Obtenir les statistiques par type d'institution
     */
    public static function getInstitutionTypeStats(): array
    {
        return static::selectRaw('institution_type, COUNT(*) as count')
                     ->where('statut', 'Ouvert')
                     ->groupBy('institution_type')
                     ->orderBy('count', 'desc')
                     ->pluck('count', 'institution_type')
                     ->toArray();
    }
}