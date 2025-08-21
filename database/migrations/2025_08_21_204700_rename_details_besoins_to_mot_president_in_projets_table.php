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
            // Renommer la colonne details_besoins en mot_president
            $table->renameColumn('details_besoins', 'mot_president');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            // Remettre l'ancien nom
            $table->renameColumn('mot_president', 'details_besoins');
        });
    }
};
