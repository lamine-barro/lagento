<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('projet_id')->nullable();
            $table->foreign('projet_id')->references('id')->on('projets')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('entrepreneur_profile')->nullable();
            $table->json('project_diagnostic')->nullable();
            $table->json('matched_opportunities')->nullable();
            $table->json('market_insights')->nullable();
            $table->json('regulations')->nullable();
            $table->json('suggested_partners')->nullable();
            $table->json('executive_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_analytics');
    }
};


