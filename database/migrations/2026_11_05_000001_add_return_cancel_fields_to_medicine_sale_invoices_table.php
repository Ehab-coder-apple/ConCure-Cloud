<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds lifecycle/status + cancellation audit fields to medicine_sale_invoices.
     *
     * `status` tracks the invoice lifecycle: completed -> edited -> partially_returned
     * -> returned -> cancelled. The original_* snapshot columns preserve the
     * as-sold totals for audit/reporting even after returns/cancellation mutate
     * the live subtotal/total/paid_amount fields (which is what every existing
     * revenue/ledger sum() query already reads from).
     */
    public function up(): void
    {
        Schema::table('medicine_sale_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('medicine_sale_invoices', 'status')) {
                $table->string('status', 30)->default('completed')->after('paid_amount');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'original_subtotal')) {
                $table->decimal('original_subtotal', 12, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'original_discount')) {
                $table->decimal('original_discount', 12, 2)->nullable()->after('original_subtotal');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'original_tax')) {
                $table->decimal('original_tax', 12, 2)->nullable()->after('original_discount');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'original_total')) {
                $table->decimal('original_total', 12, 2)->nullable()->after('original_tax');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'original_paid_amount')) {
                $table->decimal('original_paid_amount', 12, 2)->nullable()->after('original_total');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('original_paid_amount');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'cancel_reason_code')) {
                $table->string('cancel_reason_code', 50)->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'cancel_notes')) {
                $table->text('cancel_notes')->nullable()->after('cancel_reason_code');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('cancel_notes');
            }
            if (!Schema::hasColumn('medicine_sale_invoices', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->after('edited_at')
                    ->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('medicine_sale_invoices', 'status')) {
            \Illuminate\Support\Facades\DB::table('medicine_sale_invoices')
                ->whereNull('status')
                ->update(['status' => 'completed']);
        }
    }

    public function down(): void
    {
        Schema::table('medicine_sale_invoices', function (Blueprint $table) {
            foreach (['edited_by', 'edited_at', 'cancel_notes', 'cancel_reason_code'] as $col) {
                if (Schema::hasColumn('medicine_sale_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('medicine_sale_invoices', 'cancelled_by')) {
                try { $table->dropForeign(['cancelled_by']); } catch (\Throwable $e) {}
                $table->dropColumn('cancelled_by');
            }
            if (Schema::hasColumn('medicine_sale_invoices', 'edited_by')) {
                try { $table->dropForeign(['edited_by']); } catch (\Throwable $e) {}
                $table->dropColumn('edited_by');
            }
            foreach (['cancelled_at', 'original_paid_amount', 'original_total', 'original_tax', 'original_discount', 'original_subtotal', 'status'] as $col) {
                if (Schema::hasColumn('medicine_sale_invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
