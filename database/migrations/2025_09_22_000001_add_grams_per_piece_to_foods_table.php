<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            if (!Schema::hasColumn('foods', 'grams_per_piece')) {
                $table->decimal('grams_per_piece', 8, 2)->nullable()->after('serving_weight');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            if (Schema::hasColumn('foods', 'grams_per_piece')) {
                $table->dropColumn('grams_per_piece');
            }
        });
    }
};

