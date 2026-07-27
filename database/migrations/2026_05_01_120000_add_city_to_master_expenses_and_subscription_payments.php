<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an optional city column to master_expenses and subscription_payments
     * so the master finance dashboard can filter platform expenses and
     * subscription payments by city. Indexed to keep filter queries cheap.
     *
     * For payments, the value is auto-populated from the selected clinic's
     * city when the operator records the payment, but stored on the row so
     * the filter doesn't need a join (and so historical filters keep working
     * even if the clinic later changes city).
     */
    public function up(): void
    {
        Schema::table('master_expenses', function (Blueprint $table) {
            $table->string('city', 80)->nullable()->after('payment_method');
            $table->index('city');
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('city', 80)->nullable()->after('method');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::table('master_expenses', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropColumn('city');
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropColumn('city');
        });
    }
};
