<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->decimal('custom_monthly_price', 10, 2)->nullable()->after('next_billing_at');
            $table->decimal('custom_yearly_price', 10, 2)->nullable()->after('custom_monthly_price');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn(['custom_monthly_price', 'custom_yearly_price']);
        });
    }
};

