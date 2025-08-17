<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'phone',
        'profile_type',
        'verification_status',
        'company_name',
        'business_sector',
        'business_stage',
        'team_size',
        'monthly_revenue',
        'main_challenges',
        'objectives',
        'preferred_support',
        'onboarding_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'main_challenges' => 'array',
            'objectives' => 'array', 
            'preferred_support' => 'array',
            'onboarding_completed' => 'boolean',
        ];
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(UserConversation::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(UserAnalytics::class);
    }

    public function projets(): HasMany
    {
        return $this->hasMany(Projet::class);
    }
}
