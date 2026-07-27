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
        Schema::table('aesthetic_invoices', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->after('tenant_id')
                  ->constrained('clinics')
                  ->onDelete('set null');
            $table->index(['clinic_id', 'status']);
            $table->index(['clinic_id', 'invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aesthetic_invoices', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropIndex(['clinic_id', 'status']);
            $table->dropIndex(['clinic_id', 'invoice_date']);
            $table->dropColumn('clinic_id');
        });
    }
};
