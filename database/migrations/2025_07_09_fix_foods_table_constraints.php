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
        Schema::table('foods', function (Blueprint $table) {
            // Make name_translations nullable
            $table->json('name_translations')->nullable()->change();
            
            // Make food_group_id nullable
            $table->unsignedBigInteger('food_group_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            // Revert changes
            $table->json('name_translations')->nullable(false)->change();
            $table->unsignedBigInteger('food_group_id')->nullable(false)->change();
        });
    }
};
