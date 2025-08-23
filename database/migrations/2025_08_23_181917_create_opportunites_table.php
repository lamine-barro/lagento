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
        Schema::create('opportunites', function (Blueprint $table) {
            $table->id();
            $table->string('institution');
            $table->string('institution_type');
            $table->string('statut');
            $table->string('titre');
            $table->longText('description');
            $table->string('type');
            $table->string('pays');
            $table->string('regions_ciblees')->nullable();
            $table->string('date_limite_candidature')->nullable();
            $table->string('date_debut')->nullable();
            $table->string('duree')->nullable();
            $table->text('remuneration')->nullable();
            $table->string('nombre_places')->nullable();
            $table->text('secteurs')->nullable();
            $table->longText('criteres_eligibilite')->nullable();
            $table->string('contact_email_enrichi')->nullable();
            $table->text('lien_externe')->nullable();
            $table->string('origine_initiative');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index(['statut', 'type']);
            $table->index(['institution_type']);
            $table->index(['type']);
            $table->index(['statut']);
            $table->fullText(['titre', 'description', 'institution']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunites');
    }
};
