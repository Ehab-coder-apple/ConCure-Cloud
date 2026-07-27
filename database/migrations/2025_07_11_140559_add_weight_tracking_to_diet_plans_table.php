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
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->decimal('initial_weight', 5, 2)->nullable()->after('target_fat');
            $table->decimal('target_weight', 5, 2)->nullable()->after('initial_weight');
            $table->decimal('current_weight', 5, 2)->nullable()->after('target_weight');
            $table->decimal('initial_height', 5, 2)->nullable()->after('current_weight');
            $table->decimal('initial_bmi', 4, 2)->nullable()->after('initial_height');
            $table->decimal('current_bmi', 4, 2)->nullable()->after('initial_bmi');
            $table->decimal('target_bmi', 4, 2)->nullable()->after('current_bmi');
            $table->decimal('weight_goal_kg', 5, 2)->nullable()->after('target_bmi'); // How much to lose/gain
            $table->decimal('weekly_weight_goal', 5, 2)->nullable()->after('weight_goal_kg'); // Weekly target
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->dropColumn([
                'initial_weight',
                'target_weight',
                'current_weight',
                'initial_height',
                'initial_bmi',
                'current_bmi',
                'target_bmi',
                'weight_goal_kg',
                'weekly_weight_goal'
            ]);
        });
    }
};
