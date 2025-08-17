<?php

namespace App\Observers;

use App\Models\User;
use App\Services\UserAnalyticsService;

class UserActivityObserver
{
    private UserAnalyticsService $analyticsService;

    public function __construct(UserAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    // Chat interaction observers removed per performance and signal focus

    /**
     * Handle the User "updated" event for profile changes.
     */
    public function userUpdated(User $user): void
    {
        // Track profile completion changes
        if ($user->isDirty(['company_name', 'business_sector', 'business_stage', 'team_size', 'monthly_revenue', 'main_challenges', 'objectives', 'preferred_support'])) {
            $profileData = [
                'business_name' => $user->company_name,
                'business_sector' => $user->business_sector,
                'business_stage' => $user->business_stage,
                'team_size' => $user->team_size,
                'monthly_revenue' => $user->monthly_revenue,
                'main_challenges' => json_decode($user->main_challenges, true),
                'objectives' => json_decode($user->objectives, true),
                'preferred_support' => json_decode($user->preferred_support, true),
                'updated_at' => now()->toISOString()
            ];
            
            $this->analyticsService->updateEntrepreneurProfile($user, $profileData);
        }
    }
}