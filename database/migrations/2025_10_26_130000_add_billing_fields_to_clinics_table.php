<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'billing_user_price')) {
                $table->decimal('billing_user_price', 10, 2)->nullable()->after('max_users');
            }
            if (!Schema::hasColumn('clinics', 'billing_user_count')) {
                $table->integer('billing_user_count')->nullable()->after('billing_user_price');
            }
            if (!Schema::hasColumn('clinics', 'service_charge_amount')) {
                $table->decimal('service_charge_amount', 10, 2)->nullable()->after('billing_user_count');
            }
            if (!Schema::hasColumn('clinics', 'service_charge_date')) {
                $table->date('service_charge_date')->nullable()->after('service_charge_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'service_charge_date')) {
                $table->dropColumn('service_charge_date');
            }
            if (Schema::hasColumn('clinics', 'service_charge_amount')) {
                $table->dropColumn('service_charge_amount');
            }
            if (Schema::hasColumn('clinics', 'billing_user_count')) {
                $table->dropColumn('billing_user_count');
            }
            if (Schema::hasColumn('clinics', 'billing_user_price')) {
                $table->dropColumn('billing_user_price');
            }
        });
    }
};

