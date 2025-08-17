<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_type',
        'source_id',
        'content',
        'embedding'
    ];

    protected $casts = [
        'embedding' => 'array'
    ];

    /**
     * Get the source model that this chunk belongs to
     */
    public function source()
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    /**
     * Scope to filter by source type
     */
    public function scopeOfType($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope to filter by source
     */
    public function scopeFromSource($query, string $sourceType, int $sourceId)
    {
        return $query->where('source_type', $sourceType)
                     ->where('source_id', $sourceId);
    }

    /**
     * Scope for semantic search using cosine similarity
     */
    public function scopeSimilarTo($query, array $embedding, float $threshold = 0.7)
    {
        $embeddingString = '[' . implode(',', $embedding) . ']';
        
        return $query->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$embeddingString])
                     ->whereRaw('1 - (embedding <=> ?) > ?', [$embeddingString, $threshold])
                     ->orderByRaw('embedding <=> ?', [$embeddingString]);
    }
}