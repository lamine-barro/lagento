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
        Schema::table('user_messages', function (Blueprint $table) {
            if (Schema::hasColumn('user_messages', 'openai_file_id')) {
                $table->dropColumn('openai_file_id');
            }
            $table->uuid('vector_memory_id')->nullable()->after('executed_tools');
            $table->foreign('vector_memory_id')->references('id')->on('vector_memories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_messages', function (Blueprint $table) {
            $table->dropForeign(['vector_memory_id']);
            $table->dropColumn('vector_memory_id');
            $table->string('openai_file_id')->nullable()->after('executed_tools');
        });
    }
};
