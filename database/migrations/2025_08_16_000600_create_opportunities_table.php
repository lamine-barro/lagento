<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            // Identification
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('status')->nullable();
            $table->string('title');
            $table->string('type')->nullable();
            $table->longText('description')->nullable();
            $table->string('illustration_url')->nullable();
            // Location
            $table->string('country')->nullable();
            $table->json('target_regions')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            // Time
            $table->timestamp('application_deadline')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('duration')->nullable();
            // Details
            $table->string('compensation')->nullable();
            $table->unsignedInteger('seats')->nullable();
            $table->json('eligibility_criteria')->nullable();
            $table->json('required_documents')->nullable();
            // Contact
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('external_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};


