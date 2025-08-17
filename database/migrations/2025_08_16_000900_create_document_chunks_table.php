<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('source_type'); // e.g., 'official_text'
            $table->uuid('source_id');
            $table->text('content');
            $table->vector('embedding', 1024); // adjust dimension to Voyage model
            $table->timestamps();
        });

        DB::statement('CREATE INDEX document_chunks_embedding_ivfflat ON document_chunks USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100);');
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};


