<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add inventory management fields to medicines table.
     * Allows pharmacists to track stock, pricing, expiry dates, and batch numbers.
     */
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->after('is_frequent');
            $table->decimal('purchase_price', 10, 2)->nullable()->after('stock_quantity');
            $table->decimal('selling_price', 10, 2)->nullable()->after('purchase_price');
            $table->date('expiry_date')->nullable()->after('selling_price');
            $table->string('batch_number', 100)->nullable()->after('expiry_date');
            
            // Add index for expiry date to quickly find expiring medicines
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex(['expiry_date']);
            $table->dropColumn([
                'stock_quantity',
                'purchase_price',
                'selling_price',
                'expiry_date',
                'batch_number',
            ]);
        });
    }
};
