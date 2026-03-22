<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_progress_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');

            $table->date('measurement_date');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('height_cm', 6, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->decimal('fat_percentage', 5, 2)->nullable();
            $table->decimal('muscle_percentage', 5, 2)->nullable();
            $table->decimal('waist_cm', 6, 2)->nullable();
            $table->decimal('hip_cm', 6, 2)->nullable();
            $table->decimal('waist_to_hip_ratio', 4, 3)->nullable();
            $table->decimal('visceral_fat', 5, 2)->nullable();
            $table->decimal('body_water_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['patient_id', 'measurement_date']);
            $table->index('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_progress_measurements');
    }
};

