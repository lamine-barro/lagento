<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TexteOfficiel extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        // Identification
        'institution_id',
        'categorie',
        'classification_juridique',
        'statut',
        // Fichier
        'chemin_fichier',
        'nom_original',
        'mime_type',
        'taille_fichier',
        'texte_brut',
        'nombre_pages',
        // Contenu
        'titre',
        'resume',
        'tags',
        // Source
        'source',
        'url_source',
        'version_document',
        'langue',
        // Dates
        'publie_le',
        'entre_en_vigueur_le',
        'abroge_le',
        'date_decision',
        // Relations
        'parent_id',
        'remplace_document_id',
        'documents_associes_ids',
    ];

    protected $casts = [
        'institution_id' => 'string',
        'categorie' => 'string',
        'taille_fichier' => 'integer',
        'nombre_pages' => 'integer',
        'tags' => 'array',
        'publie_le' => 'date',
        'entre_en_vigueur_le' => 'date',
        'abroge_le' => 'date',
        'date_decision' => 'date',
        'parent_id' => 'string',
        'remplace_document_id' => 'string',
        'documents_associes_ids' => 'array',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Constants helpers
    public static function categoriesOptions(): array
    {
        return config('constants.CATEGORIES_TEXTES_OFFICIELS', []);
    }

    public static function legalClassificationOptions(): array
    {
        return config('constants.CLASSIFICATIONS_JURIDIQUES', []);
    }

    public static function statusOptions(): array
    {
        return config('constants.STATUTS_DOCUMENTS', []);
    }
}


