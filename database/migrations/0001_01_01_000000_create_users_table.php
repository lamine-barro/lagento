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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            // Pas de mot de passe (OTP only)
            // Profile extended fields
            $table->string('phone')->nullable();
            $table->string('profile_type')->nullable();
            $table->string('verification_status')->default('unverified');
            // Onboarding snapshot fields (legacy; main data lives in projects)
            $table->string('company_name')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('business_stage')->nullable();
            $table->string('team_size')->nullable();
            $table->string('monthly_revenue')->nullable();
            $table->json('main_challenges')->nullable();
            $table->json('objectives')->nullable();
            $table->json('preferred_support')->nullable();
            $table->boolean('onboarding_completed')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
        
        // Password reset tokens table intentionally omitted (no password flow)

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
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
