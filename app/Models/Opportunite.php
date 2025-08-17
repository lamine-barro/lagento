<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunite extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        // Identification
        'institution_id',
        'statut',
        'titre',
        'type',
        'description',
        'illustration_url',
        // Localisation
        'pays',
        'regions_cibles',
        'ville',
        'adresse',
        // Temps
        'date_limite',
        'date_debut',
        'date_fin',
        'duree',
        // Détails
        'remuneration',
        'places',
        'criteres_eligibilite',
        'documents_requis',
        // Contact
        'email_contact',
        'telephone_contact',
        'lien_externe',
    ];

    protected $casts = [
        'institution_id' => 'string',
        'regions_cibles' => 'array',
        'date_limite' => 'datetime',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'places' => 'integer',
        'criteres_eligibilite' => 'array',
        'documents_requis' => 'array',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Constants helpers
    public static function typeOptions(): array
    {
        return config('constants.TYPES_OPPORTUNITES', []);
    }

    public static function regionOptions(): array
    {
        return array_keys(config('constants.REGIONS', []));
    }
}


