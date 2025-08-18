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
        // Enable pgvector extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        
        Schema::create('vector_memories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('memory_type', 50)->index(); // 'opportunite', 'texte_officiel', etc.
            $table->string('source_id')->index(); // ID de l'entité source
            $table->text('chunk_content'); // Contenu du chunk
            $table->jsonb('metadata')->nullable(); // Contexte et filtres
            $table->timestamps();
            
            // Indexes
            $table->index(['memory_type', 'source_id']);
            $table->index('created_at');
        });
        
        // Add vector column after table creation
        DB::statement('ALTER TABLE vector_memories ADD COLUMN embedding vector(1024)');
        
        // Create HNSW index optimized for Voyage embeddings (cosine similarity)
        DB::statement('CREATE INDEX ON vector_memories USING hnsw (embedding vector_cosine_ops)');
        
        // Create additional indexes for common queries
        DB::statement('CREATE INDEX vector_memories_metadata_gin ON vector_memories USING gin (metadata jsonb_path_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vector_memories');
    }
};
