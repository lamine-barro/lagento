<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MorceauDocument extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'type_source',
        'source_id',
        'contenu',
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
        return $this->morphTo(__FUNCTION__, 'type_source', 'source_id');
    }

    /**
     * Scope to filter by source type
     */
    public function scopeOfType($query, string $sourceType)
    {
        return $query->where('type_source', $sourceType);
    }

    /**
     * Scope to filter by source
     */
    public function scopeFromSource($query, string $sourceType, string $sourceId)
    {
        return $query->where('type_source', $sourceType)
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