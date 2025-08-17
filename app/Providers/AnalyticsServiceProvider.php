<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Project;
use App\Observers\UserActivityObserver;
use App\Services\UserAnalyticsService;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserAnalyticsService::class, function ($app) {
            return new UserAnalyticsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register observers for automatic analytics tracking
        $observer = $this->app->make(UserActivityObserver::class);
        // Only observe User updates; we disable message and conversation observers per request
        User::observe($observer);

        // When a project is created/updated, refresh entrepreneur analytics
        Project::saved(function (Project $project) {
            $user = $project->user;
            if (!$user) return;
            $data = [
                'business_name' => $project->company_name,
                'project_name' => $project->project_name,
                'business_sector' => $project->sectors,
                'business_stage' => $project->maturity,
                'region' => $project->region,
                'targets' => $project->targets,
                'revenue_models' => $project->revenue_models,
                'revenue_range' => $project->revenue_range,
                'team_size' => $project->team_size,
                'support_types' => $project->support_types,
                'updated_at' => now()->toISOString(),
            ];
            app(\App\Services\UserAnalyticsService::class)->updateEntrepreneurProfile($user, $data);
        });
    }
}