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
        Schema::create('diet_plan_weight_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diet_plan_id');
            $table->unsignedBigInteger('patient_id');
            $table->decimal('weight', 5, 2); // Current weight in kg
            $table->decimal('height', 5, 2)->nullable(); // Height in cm (can be updated)
            $table->decimal('bmi', 4, 2)->nullable(); // Calculated BMI
            $table->decimal('weight_change', 5, 2)->nullable(); // Change from previous record
            $table->decimal('weight_change_percentage', 5, 2)->nullable(); // Percentage change
            $table->text('notes')->nullable(); // Visit notes
            $table->text('measurements')->nullable(); // Additional measurements (waist, chest, etc.)
            $table->date('record_date'); // Date of weight record
            $table->unsignedBigInteger('recorded_by'); // User who recorded this
            $table->timestamps();

            $table->index(['diet_plan_id', 'record_date']);
            $table->index(['patient_id', 'record_date']);
            $table->foreign('diet_plan_id')->references('id')->on('diet_plans')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_weight_records');
    }
};
