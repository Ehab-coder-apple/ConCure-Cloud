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
        Schema::create('ent_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('doctor_id');
            $table->date('visit_date');
            
            // Chief Complaint
            $table->text('chief_complaint')->nullable();
            
            // ENT Examination Findings
            $table->text('ear_examination')->nullable();
            $table->text('nose_examination')->nullable();
            $table->text('throat_examination')->nullable();
            
            // Additional Findings
            $table->text('neck_examination')->nullable();
            $table->text('cranial_nerves')->nullable();
            
            // Diagnosis
            $table->text('diagnosis')->nullable();
            $table->string('icd10_code')->nullable();
            
            // Treatment Plan
            $table->text('treatment_plan')->nullable();
            $table->text('medications')->nullable();
            
            // Follow-up
            $table->date('followup_date')->nullable();
            $table->text('notes')->nullable();
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index('patient_id');
            $table->index('clinic_id');
            $table->index('visit_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ent_records');
    }
};
