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
        Schema::create('diet_plan_meals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diet_plan_id');
            $table->integer('day_number'); // Day 1, 2, 3, etc.
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack_1', 'snack_2', 'snack_3']);
            $table->string('meal_name')->nullable();
            $table->text('instructions')->nullable();
            $table->time('suggested_time')->nullable();
            $table->timestamps();
            
            $table->index(['diet_plan_id', 'day_number']);
            $table->index(['diet_plan_id', 'meal_type']);
            $table->foreign('diet_plan_id')->references('id')->on('diet_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_meals');
    }
};
