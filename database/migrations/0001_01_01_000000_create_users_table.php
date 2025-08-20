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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            // Pas de mot de passe (OTP only)
            // Profile extended fields
            $table->string('phone')->nullable();
            $table->string('verification_status')->default('unverified');
            // Onboarding snapshot fields (main data lives in projects)
            $table->json('main_challenges')->nullable();
            $table->json('objectives')->nullable();
            $table->json('preferred_support')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            // Profile settings
            $table->boolean('is_public')->default(true);
            $table->boolean('email_notifications')->default(true);
            // Weekly rate limits
            $table->integer('diagnostics_used_this_week')->default(0);
            $table->integer('messages_used_this_week')->default(0);
            $table->integer('images_used_this_week')->default(0);
            $table->integer('documents_used_this_week')->default(0);
            $table->date('rate_limits_week_start')->nullable();
            $table->timestamp('last_diagnostic_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Password reset tokens table intentionally omitted (no password flow)

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
