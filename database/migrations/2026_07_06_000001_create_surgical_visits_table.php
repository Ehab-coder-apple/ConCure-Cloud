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
        Schema::create('surgical_visits', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('surgical_case_id')->constrained('surgical_cases')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            
            // Visit details
            $table->dateTime('visit_date')->nullable();
            $table->integer('visit_number')->nullable();
            
            // Clinical information
            $table->longText('clinical_observations')->nullable();
            $table->string('wound_status')->nullable()
                ->comment('Values: healing_well, delayed, infected, other');
            $table->json('medications_prescribed')->nullable();
            
            // Additional notes
            $table->longText('notes')->nullable();
            
            // Audit columns
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Timestamps and soft deletes
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['surgical_case_id', 'visit_date']);
            $table->index('clinic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_visits');
    }
};
