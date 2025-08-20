<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\UserConversation;
use App\Models\UserMessage;
use App\Models\Projet;
use App\Models\UserAnalytics;
use App\Observers\UserActivityObserver;
use App\Observers\ProjetObserver;
use App\Observers\UserAnalyticsObserver;
use App\Services\OpenAIVectorService;
use App\Services\AutoVectorizationService;
use App\Services\PdfExtractionService;
use App\Services\DocumentAnalysisService;
use Illuminate\Support\Facades\Auth;
use App\Auth\UuidEloquentUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register vector services as singletons
        $this->app->singleton(OpenAIVectorService::class, function ($app) {
            return new OpenAIVectorService();
        });

        $this->app->singleton(PdfExtractionService::class, function ($app) {
            return new PdfExtractionService();
        });

        $this->app->singleton(AutoVectorizationService::class, function ($app) {
            return new AutoVectorizationService(
                $app->make(OpenAIVectorService::class)
            );
        });

        $this->app->singleton(DocumentAnalysisService::class, function ($app) {
            return new DocumentAnalysisService(
                $app->make(PdfExtractionService::class),
                $app->make(\App\Services\LanguageModelService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register custom UUID-safe user provider
        Auth::provider('uuid-eloquent', function ($app, array $config) {
            return new UuidEloquentUserProvider($app['hash'], $config['model']);
        });

        // Register observers for automatic vector indexation
        Projet::observe(ProjetObserver::class);
        UserAnalytics::observe(UserAnalyticsObserver::class);
        
        // Note: Conversation and message analytics hooks are disabled to avoid
        // performance issues and rely on explicit tracking in services.
    }
}
