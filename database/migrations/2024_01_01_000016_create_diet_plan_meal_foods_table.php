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
        Schema::create('diet_plan_meal_foods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diet_plan_meal_id');
            $table->unsignedBigInteger('food_id')->nullable(); // Null for custom foods
            $table->string('food_name'); // For custom foods or override
            $table->decimal('quantity', 8, 2); // Amount in grams or serving size
            $table->string('unit')->default('g'); // g, cup, piece, etc.
            $table->text('preparation_notes')->nullable();
            $table->timestamps();
            
            $table->index('diet_plan_meal_id');
            $table->foreign('diet_plan_meal_id')->references('id')->on('diet_plan_meals')->onDelete('cascade');
            $table->foreign('food_id')->references('id')->on('foods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_meal_foods');
    }
};
