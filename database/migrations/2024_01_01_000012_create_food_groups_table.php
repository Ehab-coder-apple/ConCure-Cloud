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
        Schema::create('food_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('name_translations'); // {"en": "Vegetables", "ar": "خضروات", "ku": "سەوزە"}
            $table->text('description')->nullable();
            $table->json('description_translations')->nullable();
            $table->string('color')->nullable(); // For UI categorization
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_groups');
    }
};
