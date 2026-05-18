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
        Schema::create('orthodontic_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orthodontic_case_id')->constrained('orthodontic_cases')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('orthodontic_visit_id')->nullable()->constrained('orthodontic_visits')->cascadeOnDelete();
            
            $table->string('photo_type'); // intraoral, extraoral, xray, scan
            $table->string('view_type'); // frontal, lateral, occlusal, smile, profile
            $table->string('stage'); // before, during, after
            $table->date('photo_date');
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('uploaded_by')->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['orthodontic_case_id', 'stage']);
            $table->index(['patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orthodontic_photos');
    }
};
