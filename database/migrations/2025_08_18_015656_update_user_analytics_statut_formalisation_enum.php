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
        // Supprimer la contrainte CHECK existante et la recréer avec les bonnes valeurs
        DB::statement("ALTER TABLE user_analytics DROP CONSTRAINT IF EXISTS user_analytics_statut_formalisation_check");
        
        // Recréer la contrainte avec les valeurs correctes incluant 'partiel'
        DB::statement("ALTER TABLE user_analytics ADD CONSTRAINT user_analytics_statut_formalisation_check CHECK (statut_formalisation IN ('ok', 'à_faire', 'en_cours', 'partiel'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retour à l'ancienne contrainte
        DB::statement("ALTER TABLE user_analytics DROP CONSTRAINT IF EXISTS user_analytics_statut_formalisation_check");
        DB::statement("ALTER TABLE user_analytics ADD CONSTRAINT user_analytics_statut_formalisation_check CHECK (statut_formalisation IN ('ok', 'à_faire', 'en_cours'))");
    }
};
