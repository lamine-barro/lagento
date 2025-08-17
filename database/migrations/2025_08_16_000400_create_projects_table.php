<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Identity
            $table->string('project_name');
            $table->string('company_name')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            // Formalisation
            $table->enum('formalized', ['oui', 'non'])->default('non');
            $table->unsignedSmallInteger('incorporation_year')->nullable();
            $table->string('rccm_number')->nullable();
            // Activity
            $table->json('sectors')->nullable();
            $table->json('products_services')->nullable();
            $table->json('targets')->nullable();
            // Development
            $table->string('maturity')->nullable();
            $table->string('funding_stage')->nullable();
            $table->json('revenue_models')->nullable();
            $table->string('revenue_range')->nullable();
            // Location
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            // Team
            $table->unsignedSmallInteger('num_founders_male')->default(0);
            $table->unsignedSmallInteger('num_founders_female')->default(0);
            $table->json('founder_age_ranges')->nullable();
            $table->enum('founder_location', ['local', 'diaspora', 'mixte'])->nullable();
            $table->string('team_size')->nullable();
            // Needs
            $table->json('support_structures')->nullable();
            $table->json('support_types')->nullable();
            $table->text('needs_details')->nullable();
            $table->boolean('newsletter_opt_in')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};


