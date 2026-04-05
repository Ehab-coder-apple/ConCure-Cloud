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
        Schema::table('master_invoices', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD')->after('clinic_id');
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_invoices', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
