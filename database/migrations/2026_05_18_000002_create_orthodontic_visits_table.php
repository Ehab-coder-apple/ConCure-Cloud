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
        Schema::create('orthodontic_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orthodontic_case_id')->constrained('orthodontic_cases')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users');
            
            $table->date('visit_date');
            $table->integer('visit_number')->default(1); // Sequential visit number
            
            // Visit details
            $table->string('visit_type')->default('adjustment'); // adjustment, emergency, review, final
            $table->text('procedures_performed')->nullable(); // Wire change, bracket replacement, etc.
            $table->text('observations')->nullable();
            $table->text('patient_concerns')->nullable();
            
            // Clinical findings
            $table->text('oral_hygiene_status')->nullable();
            $table->boolean('broken_brackets')->default(false);
            $table->text('appliance_condition')->nullable();
            
            // Next steps
            $table->date('next_appointment_date')->nullable();
            $table->text('instructions_given')->nullable();
            $table->text('notes')->nullable();
            
            // Tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['orthodontic_case_id', 'visit_date']);
            $table->index(['patient_id']);
            $table->index(['clinic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orthodontic_visits');
    }
};
