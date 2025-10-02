<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update egg nutritional data to correct values
        // 73 cal per 55g egg = 133 cal per 100g
        DB::table('foods')
            ->where(function($query) {
                $query->where('name', 'Egg')
                      ->orWhere('name', 'Eggs')
                      ->orWhere('name', 'LIKE', '%بيض%')
                      ->orWhere('name', 'LIKE', '%هێلکە%');
            })
            ->update([
                'calories' => 133,          // Per 100g (73 cal per 55g egg)
                'protein' => 13,            // Per 100g (7.2g per 55g egg)
                'serving_weight' => 55,     // 55 grams per egg
                'grams_per_piece' => 55,    // 55 grams per piece
                'serving_size' => '1 large egg',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old values
        DB::table('foods')
            ->where(function($query) {
                $query->where('name', 'Egg')
                      ->orWhere('name', 'Eggs')
                      ->orWhere('name', 'LIKE', '%بيض%')
                      ->orWhere('name', 'LIKE', '%هێلکە%');
            })
            ->update([
                'calories' => 155,
                'protein' => 13,
                'serving_weight' => 50,
                'grams_per_piece' => 50,
                'updated_at' => now(),
            ]);
    }
};

