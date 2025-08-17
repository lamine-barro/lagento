<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Projet;
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
        // Observers supprimés - analytics géré uniquement via le bouton diagnostic
        // La mise à jour analytics entrepreneur est déclenchée uniquement lors du clic sur
        // le bouton diagnostic dans le dashboard.
    }
}