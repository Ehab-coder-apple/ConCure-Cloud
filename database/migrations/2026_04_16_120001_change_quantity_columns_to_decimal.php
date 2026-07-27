<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change quantity columns from integer to decimal to support fractional sales
     * (e.g., 0.5 ml, 1.5 ml for cosmetics, fillers, and injectable products).
     */
    public function up(): void
    {
        // Update medicines table - stock_quantity to decimal
        Schema::table('medicines', function (Blueprint $table) {
            $table->decimal('stock_quantity', 10, 2)->default(0)->change();
        });

        // Update medicine_transactions table - quantity and stock tracking to decimal
        Schema::table('medicine_transactions', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('stock_before', 10, 2)->change();
            $table->decimal('stock_after', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert medicines table
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
        });

        // Revert medicine_transactions table
        Schema::table('medicine_transactions', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('stock_before')->change();
            $table->integer('stock_after')->change();
        });
    }
};
