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
        // Register observers for automatic analytics tracking
        $observer = $this->app->make(UserActivityObserver::class);
        // Only observe User updates; we disable message and conversation observers per request
        User::observe($observer);

        // Quand un projet est créé/mis à jour, rafraîchir l'analytics entrepreneur
        Projet::saved(function (Projet $projet) {
            $user = $projet->user;
            if (!$user) return;
            $data = [
                'raison_sociale' => $projet->raison_sociale,
                'nom_projet' => $projet->nom_projet,
                'secteurs' => $projet->secteurs,
                'maturite' => $projet->maturite,
                'region' => $projet->region,
                'cibles' => $projet->cibles,
                'modeles_revenus' => $projet->modeles_revenus,
                'revenus' => $projet->revenus,
                'taille_equipe' => $projet->taille_equipe,
                'types_soutien' => $projet->types_soutien,
                'updated_at' => now()->toISOString(),
            ];
            app(\App\Services\UserAnalyticsService::class)->updateEntrepreneurProfile($user, $data);
        });
    }
}