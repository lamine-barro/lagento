<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            // Index pour les filtres de la page Intelligence
            $table->index('is_public', 'projets_is_public_index');
            $table->index('formalise', 'projets_formalise_index');
            $table->index('region', 'projets_region_index');
            $table->index('maturite', 'projets_maturite_index');
            $table->index('localisation_fondateurs', 'projets_localisation_fondateurs_index');
            
            // Index composé pour les requêtes de base (onboarding complet + public)
            $table->index(['nom_projet', 'formalise', 'region', 'is_public'], 'projets_onboarding_complete_index');
            
            // Index pour les tris
            $table->index(['created_at', 'is_public'], 'projets_created_at_public_index');
            $table->index(['nom_projet', 'is_public'], 'projets_nom_public_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            // Suppression des index dans l'ordre inverse
            $table->dropIndex('projets_nom_public_index');
            $table->dropIndex('projets_created_at_public_index');
            $table->dropIndex('projets_onboarding_complete_index');
            $table->dropIndex('projets_localisation_fondateurs_index');
            $table->dropIndex('projets_maturite_index');
            $table->dropIndex('projets_region_index');
            $table->dropIndex('projets_formalise_index');
            $table->dropIndex('projets_is_public_index');
        });
    }
};
