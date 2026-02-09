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
            // Add unique constraint on name and food_group_id combination
            // This prevents duplicate food names within the same food group
            $table->unique(['name', 'food_group_id'], 'foods_name_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('foods_name_group_unique');
        });
    }
};
