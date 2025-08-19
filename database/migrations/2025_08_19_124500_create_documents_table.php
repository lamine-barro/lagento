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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('category')->default('other');
            
            // Contenu extrait
            $table->text('extracted_content')->nullable();
            
            // Métadonnées enrichies par IA
            $table->json('ai_metadata')->nullable(); // Résumé, tags, etc.
            $table->text('ai_summary')->nullable(); // Résumé 300 tokens max
            $table->json('detected_tags')->nullable(); // RCCM, DFE, etc.
            $table->json('extraction_metadata')->nullable(); // Infos PDF originales
            
            // Status de traitement
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['user_id', 'is_processed']);
            $table->index(['category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
