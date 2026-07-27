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
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // File metadata
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('file_type', 10)->nullable(); // doc, docx, xls, xlsx
            $table->unsignedBigInteger('file_size')->nullable(); // bytes

            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true)->index();

            // Multi-tenancy & audit
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('clinic_id');
            $table->index('created_by');
            $table->index(['clinic_id', 'is_active']);
            $table->index(['clinic_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};

