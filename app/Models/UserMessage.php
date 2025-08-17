<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMessage extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'user_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'text_content',
        'markdown_content',
        'attachments',
        'executed_tools',
        'tokens_used',
        'is_retried',
        'is_copied',
        'content', // Alias for text_content
    ];

    protected $casts = [
        'attachments' => 'array',
        'executed_tools' => 'array',
        'tokens_used' => 'integer',
        'is_retried' => 'boolean',
        'is_copied' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(UserConversation::class, 'conversation_id');
    }

    // Accessor and Mutator for content alias
    public function getContentAttribute(): ?string
    {
        return $this->text_content;
    }

    public function setContentAttribute(?string $value): void
    {
        $this->attributes['text_content'] = $value;
    }
}


