<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('medicine_transactions', 'medicine_sale_invoice_id')) {
                $table->foreignId('medicine_sale_invoice_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('medicine_sale_invoices')
                    ->nullOnDelete();
                $table->index('medicine_sale_invoice_id', 'med_tx_invoice_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicine_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('medicine_transactions', 'medicine_sale_invoice_id')) {
                try { $table->dropForeign(['medicine_sale_invoice_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex('med_tx_invoice_idx'); } catch (\Throwable $e) {}
                $table->dropColumn('medicine_sale_invoice_id');
            }
        });
    }
};
