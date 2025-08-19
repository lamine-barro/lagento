<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable the pgvector extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the pgvector extension
        DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};