<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->uuid('projet_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('projet_id')->references('id')->on('projets')->cascadeOnDelete();
            
            // Métadonnées d'analyse
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            
            // Profil Entrepreneur
            $table->string('niveau_global')->nullable();
            $table->integer('score_potentiel')->nullable();
            $table->json('forces')->nullable();
            $table->json('axes_progression')->nullable();
            $table->json('besoins_formation')->nullable();
            $table->string('profil_type')->nullable();
            
            // Diagnostic Projet
            $table->integer('score_sante')->nullable();
            $table->string('niveau_maturite')->default('idee');
            $table->string('viabilite')->nullable();
            $table->enum('statut_formalisation', [
                'non_formalise',
                'en_cours',
                'formalise_basic',
                'formalise_complet',
                'sarl',
                'sa',
                'sci',
                'sas',
                'eurl',
                'association',
                'auto_entrepreneur',
                'micro_entreprise',
                'ei',
                'autre'
            ])->default('non_formalise');
            $table->json('actions_formalisation')->nullable();
            $table->string('urgence_formalisation')->nullable();
            $table->string('statut_finance')->nullable();
            $table->boolean('besoin_financement')->default(false);
            $table->string('montant_suggere')->nullable();
            $table->boolean('equipe_complete')->default(false);
            $table->json('besoins_equipe')->nullable();
            $table->string('position_marche')->nullable();
            $table->string('potentiel_marche')->nullable();
            $table->json('prochaines_etapes')->nullable();
            
            // Opportunités Matchées
            $table->integer('nombre_opportunites')->default(0);
            $table->json('top_opportunites')->nullable();
            $table->integer('count_financement')->default(0);
            $table->integer('count_formation')->default(0);
            $table->integer('count_marche')->default(0);
            $table->integer('count_accompagnement')->default(0);
            
            // Insights Marché
            $table->string('taille_marche_local')->nullable();
            $table->string('taille_marche_potentiel')->nullable();
            $table->string('croissance_marche')->nullable();
            $table->string('position_concurrentielle')->nullable();
            $table->json('principaux_concurrents')->nullable();
            $table->text('avantage_cle')->nullable();
            $table->json('tendances')->nullable();
            $table->json('zones_opportunites')->nullable();
            $table->text('conseil_strategique')->nullable();
            
            // Réglementations
            $table->string('conformite_globale')->nullable();
            $table->json('urgent_regulations')->nullable();
            $table->json('a_prevoir_regulations')->nullable();
            $table->json('avantages_disponibles')->nullable();
            
            // Partenaires Suggérés
            $table->integer('nombre_partenaires')->default(0);
            $table->json('top_partenaires')->nullable();
            $table->text('clients_potentiels')->nullable();
            $table->text('fournisseurs_potentiels')->nullable();
            $table->text('partenaires_complementaires')->nullable();
            
            // Résumé Exécutif
            $table->text('message_principal')->nullable();
            $table->json('trois_actions_cles')->nullable();
            $table->text('opportunite_du_mois')->nullable();
            $table->text('alerte_importante')->nullable();
            $table->integer('score_progression')->nullable();
            
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index(['user_id', 'generated_at']);
            $table->index('niveau_global');
            $table->index('niveau_maturite');
            $table->index('statut_formalisation');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_analytics');
    }
};