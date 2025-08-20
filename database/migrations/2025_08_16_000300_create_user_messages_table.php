<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('user_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->longText('text_content')->nullable();
            $table->longText('markdown_content')->nullable();
            $table->json('attachments')->nullable();
            $table->json('executed_tools')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->boolean('is_retried')->default(false);
            $table->boolean('is_copied')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_messages');
    }
};


