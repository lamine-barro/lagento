<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialText extends Model
{
    use HasFactory;

    protected $fillable = [
        // Identification
        'institution_id',
        'category',
        'legal_classification',
        'status',
        // File
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'raw_text',
        'page_count',
        // Content
        'title',
        'summary',
        'tags',
        // Source
        'source',
        'source_url',
        'document_version',
        'language',
        // Dates
        'published_at',
        'effective_at',
        'repealed_at',
        'decision_date',
        // Relations
        'parent_id',
        'replaces_document_id',
        'associated_document_ids',
    ];

    protected $casts = [
        'institution_id' => 'integer',
        'category' => 'string',
        'file_size' => 'integer',
        'page_count' => 'integer',
        'tags' => 'array',
        'published_at' => 'date',
        'effective_at' => 'date',
        'repealed_at' => 'date',
        'decision_date' => 'date',
        'parent_id' => 'integer',
        'replaces_document_id' => 'integer',
        'associated_document_ids' => 'array',
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


