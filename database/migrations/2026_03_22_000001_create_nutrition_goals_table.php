<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nutrition_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->decimal('target_weight', 6, 2)->nullable();
            $table->decimal('target_fat_percentage', 5, 2)->nullable();
            $table->decimal('target_muscle_percentage', 5, 2)->nullable();
            $table->decimal('target_bmi', 5, 2)->nullable();
            $table->decimal('target_waist_cm', 6, 2)->nullable();
            $table->decimal('target_hip_cm', 6, 2)->nullable();
            $table->decimal('target_visceral_fat', 5, 2)->nullable();
            $table->decimal('target_body_water_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['patient_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nutrition_goals');
    }
};

