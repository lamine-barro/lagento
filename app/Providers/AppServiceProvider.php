<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\UserConversation;
use App\Models\UserMessage;
use App\Observers\UserActivityObserver;
use Illuminate\Support\Facades\Auth;
use App\Auth\UuidEloquentUserProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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

        // Bind observers for analytics
        $this->app->resolving(UserActivityObserver::class, function ($observer, $app) {
            // no-op; let container inject dependencies
        });

        User::updated(function (User $user) {
            app(UserActivityObserver::class)->userUpdated($user);
        });

        // Conversation and message analytics hooks are disabled to avoid
        // performance issues and rely on explicit tracking in services.
    }
}
