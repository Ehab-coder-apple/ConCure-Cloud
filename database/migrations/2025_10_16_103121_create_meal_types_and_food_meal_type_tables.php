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
        // Create meal_types lookup table
        Schema::create('meal_types', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // breakfast, lunch, dinner, snack
            $table->string('name'); // Display label
            $table->timestamps();
        });

        // Seed default meal types
        DB::table('meal_types')->insert([
            ['key' => 'breakfast', 'name' => 'Breakfast', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lunch', 'name' => 'Lunch', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'dinner', 'name' => 'Dinner', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'snack', 'name' => 'Snack', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Pivot table between foods and meal_types
        Schema::create('food_meal_type', function (Blueprint $table) {
            $table->unsignedBigInteger('food_id');
            $table->unsignedBigInteger('meal_type_id');
            $table->timestamps();

            $table->primary(['food_id', 'meal_type_id']);
            $table->index('meal_type_id');

            $table->foreign('food_id')->references('id')->on('foods')->onDelete('cascade');
            $table->foreign('meal_type_id')->references('id')->on('meal_types')->onDelete('cascade');
        });

        // Backfill from legacy foods.meal_type
        $types = DB::table('meal_types')->pluck('id', 'key');
        $typeIds = [
            'breakfast' => $types['breakfast'] ?? null,
            'lunch' => $types['lunch'] ?? null,
            'dinner' => $types['dinner'] ?? null,
            'snack' => $types['snack'] ?? null,
        ];

        $foods = DB::table('foods')->select('id', 'meal_type')->get();
        foreach ($foods as $food) {
            $mt = strtolower((string)($food->meal_type ?? 'any'));
            if ($mt === 'snacks') { $mt = 'snack'; }
            $assignAll = ($mt === '' || $mt === 'any' || $mt === null);
            $keys = $assignAll ? ['breakfast','lunch','dinner','snack'] : [$mt];
            foreach ($keys as $k) {
                $mtid = $typeIds[$k] ?? null;
                if ($mtid) {
                    DB::table('food_meal_type')->updateOrInsert(
                        ['food_id' => $food->id, 'meal_type_id' => $mtid],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_meal_type');
        Schema::dropIfExists('meal_types');
    }
};
