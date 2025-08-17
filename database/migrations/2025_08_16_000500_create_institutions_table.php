<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            // Identification
            $table->string('type')->nullable();
            $table->string('statut')->nullable();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->json('services')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('tags')->nullable();
            // Localisation
            $table->string('pays')->nullable();
            $table->string('region')->nullable();
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            // Contact
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};


