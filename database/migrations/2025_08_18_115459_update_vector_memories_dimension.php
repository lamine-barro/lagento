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
        // Drop the existing vector column and index
        DB::statement('DROP INDEX IF EXISTS vector_memories_embedding_idx');
        DB::statement('ALTER TABLE vector_memories DROP COLUMN IF EXISTS embedding');
        
        // Add new vector column with 1536 dimensions for voyage-large-2
        DB::statement('ALTER TABLE vector_memories ADD COLUMN embedding vector(1536)');
        
        // Recreate HNSW index optimized for Voyage embeddings
        DB::statement('CREATE INDEX ON vector_memories USING hnsw (embedding vector_cosine_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the index and column
        DB::statement('DROP INDEX IF EXISTS vector_memories_embedding_idx');
        DB::statement('ALTER TABLE vector_memories DROP COLUMN IF EXISTS embedding');
        
        // Restore original 1024 dimension column
        DB::statement('ALTER TABLE vector_memories ADD COLUMN embedding vector(1024)');
        DB::statement('CREATE INDEX ON vector_memories USING hnsw (embedding vector_cosine_ops)');
    }
};
