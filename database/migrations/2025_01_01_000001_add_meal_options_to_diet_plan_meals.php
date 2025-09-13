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
        Schema::table('diet_plan_meals', function (Blueprint $table) {
            // Add option_number field for flexible meal options
            $table->integer('option_number')->default(1)->after('meal_type');
            
            // Make day_number nullable since we're moving to option-based system
            $table->integer('day_number')->nullable()->change();
            
            // Add is_option_based flag to distinguish between old day-based and new option-based plans
            $table->boolean('is_option_based')->default(false)->after('option_number');
            
            // Add option description for better UX
            $table->string('option_description')->nullable()->after('is_option_based');
            
            // Update indexes
            $table->index(['diet_plan_id', 'meal_type', 'option_number'], 'diet_plan_meal_options_idx');
            $table->index(['diet_plan_id', 'is_option_based'], 'diet_plan_option_based_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_plan_meals', function (Blueprint $table) {
            $table->dropIndex('diet_plan_meal_options_idx');
            $table->dropIndex('diet_plan_option_based_idx');
            
            $table->dropColumn(['option_number', 'is_option_based', 'option_description']);
            
            // Restore day_number as not nullable
            $table->integer('day_number')->nullable(false)->change();
        });
    }
};
