<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growth_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->date('measurement_date');
            $table->decimal('weight_kg', 6, 3)->nullable()->comment('Weight in kilograms');
            $table->decimal('length_height_cm', 6, 2)->nullable()->comment('Length/height in centimeters');
            $table->decimal('head_circumference_cm', 6, 2)->nullable()->comment('Head circumference in centimeters');
            $table->decimal('bmi', 5, 2)->nullable()->comment('Calculated BMI');
            $table->decimal('age_months', 6, 2)->nullable()->comment('Calculated age in months at measurement');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'measurement_date']);
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growth_measurements');
    }
};

