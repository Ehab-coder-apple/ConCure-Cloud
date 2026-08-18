<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds absolute-unit body composition fields (Kg for Fat/Muscle/Mineral,
     * Litres for Body Water) plus a directly-enterable WHR field, so HCPs
     * using body composition analyzers that output these values directly
     * can record them without converting to percentages or calculating
     * WHR manually from waist/hip. Existing *_percentage columns are left
     * untouched for backward compatibility with historical records.
     */
    public function up(): void
    {
        Schema::table('nutrition_progress_measurements', function (Blueprint $table) {
            $table->decimal('fat_kg', 6, 2)->nullable()->after('fat_percentage');
            $table->decimal('muscle_kg', 6, 2)->nullable()->after('muscle_percentage');
            $table->decimal('body_water_liters', 5, 2)->nullable()->after('body_water_percentage');
            $table->decimal('mineral_kg', 5, 2)->nullable()->after('visceral_fat');
            $table->decimal('whr_direct', 4, 3)->nullable()->after('waist_to_hip_ratio');
        });

        Schema::table('nutrition_goals', function (Blueprint $table) {
            $table->decimal('target_fat_kg', 6, 2)->nullable()->after('target_fat_percentage');
            $table->decimal('target_muscle_kg', 6, 2)->nullable()->after('target_muscle_percentage');
            $table->decimal('target_body_water_liters', 5, 2)->nullable()->after('target_body_water_percentage');
            $table->decimal('target_mineral_kg', 5, 2)->nullable()->after('target_visceral_fat');
            $table->decimal('target_whr', 4, 3)->nullable()->after('target_hip_cm');
        });
    }

    public function down(): void
    {
        Schema::table('nutrition_progress_measurements', function (Blueprint $table) {
            $table->dropColumn(['fat_kg', 'muscle_kg', 'body_water_liters', 'mineral_kg', 'whr_direct']);
        });

        Schema::table('nutrition_goals', function (Blueprint $table) {
            $table->dropColumn(['target_fat_kg', 'target_muscle_kg', 'target_body_water_liters', 'target_mineral_kg', 'target_whr']);
        });
    }
};
