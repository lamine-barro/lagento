<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('textes_officiels', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            // Identification
            $table->uuid('institution_id')->nullable();
            $table->foreign('institution_id')->references('id')->on('institutions')->nullOnDelete();
            $table->string('categorie');
            $table->string('classification_juridique');
            $table->string('statut');
            // Fichier
            $table->string('chemin_fichier');
            $table->string('nom_original');
            $table->string('mime_type');
            $table->unsignedBigInteger('taille_fichier');
            $table->longText('texte_brut')->nullable();
            $table->unsignedInteger('nombre_pages')->nullable();
            // Contenu
            $table->string('titre');
            $table->text('resume')->nullable();
            $table->json('tags')->nullable();
            // Source
            $table->string('source')->nullable();
            $table->string('url_source')->nullable();
            $table->string('version_document')->nullable();
            $table->string('langue')->nullable();
            // Dates
            $table->date('publie_le')->nullable();
            $table->date('entre_en_vigueur_le')->nullable();
            $table->date('abroge_le')->nullable();
            $table->date('date_decision')->nullable();
            // Relations
            $table->uuid('parent_id')->nullable();
            $table->uuid('remplace_document_id')->nullable();
            $table->json('documents_associes_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_texts');
    }
};


