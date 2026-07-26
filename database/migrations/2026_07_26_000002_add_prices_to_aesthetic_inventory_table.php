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
        Schema::table('aesthetic_inventory', function (Blueprint $table) {
            $table->decimal('purchase_price', 12, 2)->default(0)->after('expiry_date');
            $table->decimal('selling_price', 12, 2)->default(0)->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aesthetic_inventory', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'selling_price']);
        });
    }
};
