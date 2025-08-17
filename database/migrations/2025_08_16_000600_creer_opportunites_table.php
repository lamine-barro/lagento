<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunites', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            // Identification
            $table->uuid('institution_id')->nullable();
            $table->foreign('institution_id')->references('id')->on('institutions')->nullOnDelete();
            $table->string('statut')->nullable();
            $table->string('titre');
            $table->string('type')->nullable();
            $table->longText('description')->nullable();
            $table->string('illustration_url')->nullable();
            // Localisation
            $table->string('pays')->nullable();
            $table->json('regions_cibles')->nullable();
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();
            // Temps
            $table->timestamp('date_limite')->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->string('duree')->nullable();
            // Détails
            $table->string('remuneration')->nullable();
            $table->unsignedInteger('places')->nullable();
            $table->json('criteres_eligibilite')->nullable();
            $table->json('documents_requis')->nullable();
            // Contact
            $table->string('email_contact')->nullable();
            $table->string('telephone_contact')->nullable();
            $table->string('lien_externe')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};


