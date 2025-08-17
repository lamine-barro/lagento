<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Identité
            $table->string('nom_projet');
            $table->string('raison_sociale')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();

            // Formalisation
            $table->enum('formalise', ['oui', 'non'])->default('non');
            $table->year('annee_creation')->nullable();
            $table->string('numero_rccm')->nullable();

            // Activité
            $table->json('secteurs')->nullable();
            $table->json('produits_services')->nullable();
            $table->json('cibles')->nullable();

            // Développement
            $table->string('maturite')->nullable();
            $table->string('stade_financement')->nullable();
            $table->json('modeles_revenus')->nullable();
            $table->string('revenus')->nullable();

            // Localisation
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Contact
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->json('reseaux_sociaux')->nullable();

            // Équipe
            $table->integer('nombre_fondateurs')->nullable();
            $table->integer('nombre_fondatrices')->nullable();
            $table->json('tranches_age_fondateurs')->nullable();
            $table->enum('localisation_fondateurs', ['local', 'diaspora', 'mixte'])->nullable();
            $table->string('taille_equipe')->nullable();

            // Besoins
            $table->json('structures_accompagnement')->nullable();
            $table->json('types_soutien')->nullable();
            $table->text('details_besoins')->nullable();
            $table->boolean('abonne_newsletter')->default(false);

            // Publication
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_updated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};


