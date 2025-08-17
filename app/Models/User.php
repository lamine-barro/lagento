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
        'verification_status',
        'main_challenges',
        'objectives',
        'preferred_support',
        'onboarding_completed',
        'is_public',
        'email_notifications',
        'diagnostics_used_this_month',
        'diagnostics_month_reset',
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
            'diagnostics_month_reset' => 'date',
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

    /**
     * Get remaining diagnostics for this month
     */
    public function getRemainingDiagnostics(): int
    {
        $this->resetDiagnosticsIfNewMonth();
        return max(0, 3 - $this->diagnostics_used_this_month);
    }

    /**
     * Check if user can run a diagnostic
     */
    public function canRunDiagnostic(): bool
    {
        return $this->getRemainingDiagnostics() > 0;
    }

    /**
     * Use a diagnostic (increment counter)
     */
    public function useDiagnostic(): bool
    {
        if (!$this->canRunDiagnostic()) {
            return false;
        }

        $this->resetDiagnosticsIfNewMonth();
        $this->increment('diagnostics_used_this_month');
        $this->update(['last_diagnostic_at' => now()]);
        
        return true;
    }

    /**
     * Reset diagnostics counter if new month
     */
    private function resetDiagnosticsIfNewMonth(): void
    {
        $currentMonth = now()->format('Y-m');
        $resetMonth = $this->diagnostics_month_reset?->format('Y-m');

        if ($resetMonth !== $currentMonth) {
            $this->update([
                'diagnostics_used_this_month' => 0,
                'diagnostics_month_reset' => now()->startOfMonth(),
            ]);
        }
    }
}
