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
        Schema::create('dental_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('dental_chart_id')->nullable();
            $table->unsignedBigInteger('dental_treatment_id')->nullable(); // Link to specific treatment
            $table->string('tooth_number', 10)->nullable(); // Can be null for panoramic X-rays
            $table->json('tooth_numbers')->nullable(); // For images showing multiple teeth
            $table->enum('image_type', [
                'panoramic',
                'periapical',
                'bitewing',
                'occlusal',
                'cephalometric',
                'intraoral_photo',
                'extraoral_photo',
                'cbct',
                'other'
            ])->default('intraoral_photo');
            $table->string('file_path'); // Storage path
            $table->string('filename'); // Original filename
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->default(0); // In bytes
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('image_date')->nullable(); // Date when image was taken
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('dental_chart_id')->references('id')->on('dental_charts')->onDelete('set null');
            $table->foreign('dental_treatment_id')->references('id')->on('dental_treatments')->onDelete('set null');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['patient_id', 'created_at']);
            $table->index(['clinic_id', 'created_at']);
            $table->index('image_type');
            $table->index('tooth_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_images');
    }
};

