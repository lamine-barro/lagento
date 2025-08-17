<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_texts', function (Blueprint $table) {
            $table->id();
            // Identification
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->string('category');
            $table->string('legal_classification');
            $table->string('status');
            // File
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->longText('raw_text')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            // Content
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('tags')->nullable();
            // Source
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('document_version')->nullable();
            $table->string('language')->nullable();
            // Dates
            $table->date('published_at')->nullable();
            $table->date('effective_at')->nullable();
            $table->date('repealed_at')->nullable();
            $table->date('decision_date')->nullable();
            // Relations
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('replaces_document_id')->nullable();
            $table->json('associated_document_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_texts');
    }
};


