<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pediatric_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('drug_id')->constrained('pediatric_drugs');
            $table->foreignId('form_id')->constrained('pediatric_drug_forms');
            $table->foreignId('rule_id')->nullable()->constrained('pediatric_dosage_rules')->nullOnDelete();
            $table->foreignId('clinic_id')->constrained('clinics');
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('patient_weight_kg', 6, 2); // weight used for calculation
            $table->integer('patient_age_months'); // age at time of prescription
            $table->decimal('dose_mg', 10, 2); // prescribed dose in mg
            $table->decimal('dose_ml', 10, 2)->nullable(); // dose in ml (for syrups)
            $table->decimal('recommended_dose_min_mg', 10, 2)->nullable();
            $table->decimal('recommended_dose_max_mg', 10, 2)->nullable();
            $table->integer('frequency_per_day');
            $table->integer('duration_days')->nullable();
            $table->string('safety_status'); // safe, warning, danger
            $table->text('safety_message')->nullable();
            $table->text('override_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('drug_id');
            $table->index('clinic_id');
            $table->index('safety_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pediatric_prescriptions');
    }
};

