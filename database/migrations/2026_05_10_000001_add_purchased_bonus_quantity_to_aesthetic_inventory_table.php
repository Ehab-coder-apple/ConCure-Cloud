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
        Schema::table('aesthetic_inventory', function (Blueprint $table) {
            $table->unsignedInteger('purchased_quantity')->default(0)->after('quantity');
            $table->unsignedInteger('bonus_quantity')->default(0)->after('purchased_quantity');
        });

        // Backfill existing stock as "purchased" so totals remain consistent.
        DB::table('aesthetic_inventory')->update([
            'purchased_quantity' => DB::raw('quantity'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aesthetic_inventory', function (Blueprint $table) {
            $table->dropColumn(['purchased_quantity', 'bonus_quantity']);
        });
    }
};
