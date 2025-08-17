<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserConversation extends Model
{
    use HasFactory;

    protected $table = 'user_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'context',
        'status',
        'satisfaction_score',
        'last_message_at',
        'message_count',
        'is_pinned',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'metadata' => 'array',
        'satisfaction_score' => 'integer',
        'message_count' => 'integer',
        'is_pinned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(UserMessage::class, 'conversation_id');
    }
}


