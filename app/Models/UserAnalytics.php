<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAnalytics extends Model
{
    use HasFactory;

    protected $table = 'user_analytics';

    protected $fillable = [
        'user_id',
        'projet_id',
        'generated_at',
        'expires_at',
        'entrepreneur_profile',
        'project_diagnostic',
        'matched_opportunities',
        'market_insights',
        'regulations',
        'suggested_partners',
        'executive_summary',
        'metadata',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'entrepreneur_profile' => 'array',
        'project_diagnostic' => 'array',
        'matched_opportunities' => 'array',
        'market_insights' => 'array',
        'regulations' => 'array',
        'suggested_partners' => 'array',
        'executive_summary' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }
}


