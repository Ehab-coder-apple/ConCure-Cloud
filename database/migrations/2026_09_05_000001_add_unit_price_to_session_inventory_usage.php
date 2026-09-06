<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('session_inventory_usage', 'unit_price')) {
            Schema::table('session_inventory_usage', function (Blueprint $table) {
                $table->decimal('unit_price', 15, 2)->default(0)->after('quantity_used');
            });
        }

        // Backfill existing rows with the product's current selling price so
        // historical reports remain consistent until new sales overwrite it
        // with a true point-in-time snapshot. Done per-product (rather than a
        // cross-table UPDATE...JOIN) so this works identically on MySQL and
        // SQLite.
        DB::table('aesthetic_inventory')
            ->select('id', 'selling_price')
            ->orderBy('id')
            ->chunk(200, function ($products) {
                foreach ($products as $product) {
                    DB::table('session_inventory_usage')
                        ->where('product_id', $product->id)
                        ->where('unit_price', 0)
                        ->update(['unit_price' => $product->selling_price]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('session_inventory_usage', 'unit_price')) {
            Schema::table('session_inventory_usage', function (Blueprint $table) {
                $table->dropColumn('unit_price');
            });
        }
    }
};
