<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-shot reconciliation for master_invoices rows whose status drifted
     * out of sync with paid_amount/total_amount. The legacy
     * MasterInvoice::calculateTotals() stamped status='paid' on the initial
     * (empty) save and never demoted it once items were added — leaving
     * fully-unpaid invoices showing "PAID" in the master finance list.
     *
     * This migration applies the same rules as the corrected calculateTotals()
     * directly in SQL, without touching draft / cancelled rows (those are
     * manual states the operator may have set deliberately).
     */
    public function up(): void
    {
        // 1. Demote rows marked 'paid' that haven't actually been fully paid.
        DB::table('master_invoices')
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->where('total_amount', '<=', 0)
                  ->orWhereColumn('paid_amount', '<', 'total_amount');
            })
            ->update(['status' => 'sent']);

        // 2. Promote rows with a partial payment to 'partial'.
        DB::table('master_invoices')
            ->whereNotIn('status', ['draft', 'cancelled', 'paid'])
            ->where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->update(['status' => 'partial']);

        // 3. Confirm rows that genuinely are fully paid.
        DB::table('master_invoices')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('total_amount', '>', 0)
            ->whereColumn('paid_amount', '>=', 'total_amount')
            ->update(['status' => 'paid']);

        // 4. Mark anything past its due date with no payment as 'overdue'.
        DB::table('master_invoices')
            ->whereIn('status', ['sent'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('paid_amount', '<=', 0)
            ->update(['status' => 'overdue']);

        // 5. Recompute balance to match (defensive — previous code paths may
        // have left it stale even when the status column was correct).
        DB::statement('UPDATE master_invoices SET balance = (total_amount - paid_amount)');
    }

    /**
     * No-op down — backfill is idempotent and reversing it would mean
     * restoring incorrect data.
     */
    public function down(): void
    {
    }
};
