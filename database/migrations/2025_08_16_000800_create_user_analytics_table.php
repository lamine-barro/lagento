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
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            
            // Score et niveau général
            $table->decimal('score_global', 5, 2)->default(0);
            $table->string('niveau_maturite')->default('ideation');
            
            // Résumé exécutif
            $table->json('executive_summary')->nullable();
            
            // Diagnostic projet détaillé
            $table->json('project_diagnostic')->nullable();
            
            // Besoins de financement analysés
            $table->json('funding_needs')->nullable();
            
            // Opportunités matchées avec scores
            $table->json('matched_opportunities')->nullable();
            
            // Conformité réglementaire
            $table->json('regulations')->nullable();
            
            // Partenaires suggérés
            $table->json('suggested_partners')->nullable();
            
            // Recommandations personnalisées
            $table->json('recommendations')->nullable();
            
            // Statut de formalisation avec énumération étendue
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
            
            // Métadonnées d'analyse
            $table->timestamp('derniere_analyse')->nullable();
            $table->string('version_algorithme')->nullable();
            $table->decimal('confidence_score', 4, 3)->nullable();
            
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index(['user_id', 'derniere_analyse']);
            $table->index('score_global');
            $table->index('niveau_maturite');
            $table->index('statut_formalisation');
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