<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VectorMemory extends Model
{
    use HasUuids;

    protected $fillable = [
        'memory_type',
        'source_id',
        'chunk_content',
        'embedding',
        'metadata'
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}