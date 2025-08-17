<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->vector('summary_embedding', 1024)->nullable();
            $table->string('status')->default('active'); // 'active' | 'archivée' | 'en_attente'
            $table->unsignedTinyInteger('satisfaction_score')->nullable(); // 1-5
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        // Index vectoriel sur le résumé
        DB::statement('CREATE INDEX user_conversations_summary_embedding_ivfflat ON user_conversations USING ivfflat (summary_embedding vector_cosine_ops) WITH (lists = 100);');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_conversations');
    }
};


