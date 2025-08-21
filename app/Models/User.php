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
        'id',
        'name',
        'email',
        'email_verified_at',
        'phone',
        'verification_status',
        'main_challenges',
        'objectives',
        'preferred_support',
        'onboarding_completed',
        'is_public',
        'email_notifications',
        'diagnostics_used_this_week',
        'messages_used_this_week', 
        'images_used_this_week',
        'documents_used_this_week',
        'rate_limits_week_start',
        'last_diagnostic_at',
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
            'is_public' => 'boolean',
            'email_notifications' => 'boolean',
            'rate_limits_week_start' => 'date',
            'last_diagnostic_at' => 'datetime',
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

    // Weekly rate limits constants
    const WEEKLY_LIMITS = [
        'diagnostics' => 5,
        'messages' => 100,
        'images' => 3,
        'documents' => 3,
    ];

    /**
     * Get remaining usage for a specific feature this week
     */
    public function getRemainingUsage(string $feature): int
    {
        $this->resetWeeklyLimitsIfNewWeek();
        
        $used = match($feature) {
            'diagnostics' => $this->diagnostics_used_this_week,
            'messages' => $this->messages_used_this_week,
            'images' => $this->images_used_this_week,
            'documents' => $this->documents_used_this_week,
            default => 0,
        };
        
        return max(0, self::WEEKLY_LIMITS[$feature] - $used);
    }

    /**
     * Check if user can use a feature
     */
    public function canUseFeature(string $feature): bool
    {
        return $this->getRemainingUsage($feature) > 0;
    }

    /**
     * Use a feature (increment counter)
     */
    public function useFeature(string $feature): bool
    {
        if (!$this->canUseFeature($feature)) {
            return false;
        }

        $this->resetWeeklyLimitsIfNewWeek();
        
        $field = match($feature) {
            'diagnostics' => 'diagnostics_used_this_week',
            'messages' => 'messages_used_this_week',
            'images' => 'images_used_this_week',
            'documents' => 'documents_used_this_week',
            default => null,
        };
        
        if ($field) {
            $this->increment($field);
            
            if ($feature === 'diagnostics') {
                $this->update(['last_diagnostic_at' => now()]);
            }
        }
        
        return true;
    }

    /**
     * Legacy methods for backward compatibility
     */
    public function getRemainingDiagnostics(): int
    {
        return $this->getRemainingUsage('diagnostics');
    }

    public function canRunDiagnostic(): bool
    {
        return $this->canUseFeature('diagnostics');
    }

    public function useDiagnostic(): bool
    {
        return $this->useFeature('diagnostics');
    }

    /**
     * Reset weekly counters if new week
     */
    private function resetWeeklyLimitsIfNewWeek(): void
    {
        $currentWeekStart = now()->startOfWeek()->format('Y-m-d');
        $resetWeekStart = $this->rate_limits_week_start?->format('Y-m-d');

        if ($resetWeekStart !== $currentWeekStart) {
            $this->update([
                'diagnostics_used_this_week' => 0,
                'messages_used_this_week' => 0,
                'images_used_this_week' => 0,
                'documents_used_this_week' => 0,
                'rate_limits_week_start' => now()->startOfWeek(),
            ]);
        }
    }
}
