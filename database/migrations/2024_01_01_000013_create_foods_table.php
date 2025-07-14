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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('name_translations'); // Multilingual support
            $table->unsignedBigInteger('food_group_id');
            $table->text('description')->nullable();
            $table->json('description_translations')->nullable();
            
            // Nutritional information per 100g
            $table->decimal('calories', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0); // grams
            $table->decimal('carbohydrates', 8, 2)->default(0); // grams
            $table->decimal('fat', 8, 2)->default(0); // grams
            $table->decimal('fiber', 8, 2)->default(0); // grams
            $table->decimal('sugar', 8, 2)->default(0); // grams
            $table->decimal('sodium', 8, 2)->default(0); // mg
            $table->decimal('potassium', 8, 2)->default(0); // mg
            $table->decimal('calcium', 8, 2)->default(0); // mg
            $table->decimal('iron', 8, 2)->default(0); // mg
            $table->decimal('vitamin_c', 8, 2)->default(0); // mg
            $table->decimal('vitamin_a', 8, 2)->default(0); // IU
            
            $table->string('serving_size')->nullable(); // e.g., "1 cup", "1 medium"
            $table->decimal('serving_weight', 8, 2)->nullable(); // grams
            
            $table->boolean('is_custom')->default(false); // Custom foods added by clinic
            $table->unsignedBigInteger('clinic_id')->nullable(); // For custom foods
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['food_group_id', 'is_active']);
            $table->index(['clinic_id', 'is_custom']);
            $table->index('name');
            $table->foreign('food_group_id')->references('id')->on('food_groups')->onDelete('restrict');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
