<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clinical canal treatment data for endodontic procedures.
     */
    public function up(): void
    {
        Schema::create('canal_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_treatment_id')->constrained('dental_treatments')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics')->cascadeOnDelete();
            $table->string('tooth_number', 10);        // FDI notation
            $table->string('canal_name', 50);           // e.g., MB1, MB2, DB, P
            $table->decimal('working_length', 5, 2)->nullable();  // in mm
            $table->string('master_apical_file', 20)->nullable(); // MAF size, e.g., "25", "30", "35"
            $table->string('master_cone_size', 20)->nullable();   // Gutta-percha cone size
            $table->string('taper', 10)->nullable();              // e.g., ".04", ".06"
            $table->string('irrigation_protocol', 100)->nullable(); // e.g., "NaOCl 5.25% + EDTA 17%"
            $table->string('obturation_technique', 100)->nullable(); // e.g., "Lateral condensation", "Warm vertical"
            $table->string('sealer_type', 100)->nullable();       // e.g., "AH Plus", "BioRoot RCS"
            $table->enum('status', ['not_started', 'located', 'instrumented', 'obturated', 'completed'])->default('not_started');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['dental_treatment_id', 'tooth_number']);
            $table->index('patient_id');
            $table->index('clinic_id');
            $table->unique(['dental_treatment_id', 'tooth_number', 'canal_name'], 'unique_canal_per_treatment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canal_treatments');
    }
};

