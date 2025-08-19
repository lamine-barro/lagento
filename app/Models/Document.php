<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'category',
        'extracted_content',
        'ai_metadata',
        'ai_summary',
        'detected_tags',
        'extraction_metadata',
        'is_processed',
        'processed_at',
        'processing_error'
    ];

    protected $casts = [
        'ai_metadata' => 'array',
        'detected_tags' => 'array',
        'extraction_metadata' => 'array',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
        'file_size' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFormattedFileSizeAttribute(): string
    {
        if ($this->file_size > 1048576) {
            return round($this->file_size / 1048576, 1) . ' Mo';
        }
        return round($this->file_size / 1024) . ' Ko';
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    // Scopes
    public function scopeProcessed($query)
    {
        return $query->where('is_processed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_processed', false);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}