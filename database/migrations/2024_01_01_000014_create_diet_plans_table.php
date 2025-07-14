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
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('goal', ['weight_loss', 'weight_gain', 'maintenance', 'muscle_gain', 'health_improvement', 'other']);
            $table->text('goal_description')->nullable();
            $table->integer('duration_days')->nullable(); // Plan duration
            $table->decimal('target_calories', 8, 2)->nullable(); // Daily calorie target
            $table->decimal('target_protein', 8, 2)->nullable(); // Daily protein target (g)
            $table->decimal('target_carbs', 8, 2)->nullable(); // Daily carbs target (g)
            $table->decimal('target_fat', 8, 2)->nullable(); // Daily fat target (g)
            $table->text('instructions')->nullable();
            $table->text('restrictions')->nullable(); // Dietary restrictions
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['patient_id', 'start_date']);
            $table->index('plan_number');
            $table->index(['status', 'goal']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
