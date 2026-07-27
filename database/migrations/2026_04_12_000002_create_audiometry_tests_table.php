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
        Schema::create('audiometry_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('ent_record_id')->nullable();
            $table->date('test_date');
            $table->enum('test_type', ['pure_tone', 'speech', 'tympanometry', 'other'])->default('pure_tone');
            
            // Right Ear Data (frequencies in Hz, thresholds in dB)
            $table->json('right_ear_data')->nullable(); // {250: 20, 500: 25, 1000: 30, ...}
            
            // Left Ear Data
            $table->json('left_ear_data')->nullable();
            
            // Speech Audiometry
            $table->integer('right_srt')->nullable(); // Speech Reception Threshold
            $table->integer('left_srt')->nullable();
            $table->integer('right_wrs')->nullable(); // Word Recognition Score (%)
            $table->integer('left_wrs')->nullable();
            
            // Tympanometry Results
            $table->string('right_tympanometry')->nullable();
            $table->string('left_tympanometry')->nullable();
            
            // Interpretation
            $table->enum('right_interpretation', ['normal', 'conductive_loss', 'sensorineural_loss', 'mixed_loss'])->nullable();
            $table->enum('left_interpretation', ['normal', 'conductive_loss', 'sensorineural_loss', 'mixed_loss'])->nullable();
            
            // Additional Notes
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            
            $table->unsignedBigInteger('performed_by');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('ent_record_id')->references('id')->on('ent_records')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('patient_id');
            $table->index('clinic_id');
            $table->index('test_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audiometry_tests');
    }
};
