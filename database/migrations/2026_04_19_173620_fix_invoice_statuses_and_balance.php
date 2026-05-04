<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off data repair for invoices whose status was left stale by the
 * previous Invoice::markAsPaid() bug, which updated paid_amount without
 * recomputing balance or transitioning status out of 'draft' / 'sent'.
 *
 * Safe to run repeatedly: all clauses are idempotent (they only touch
 * rows whose stored status is clearly wrong given paid_amount vs
 * total_amount).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        $driver = DB::getDriverName();
        $balanceExpr = $driver === 'sqlite'
            ? DB::raw("MAX(0, COALESCE(total_amount,0) - COALESCE(paid_amount,0))")
            : DB::raw("GREATEST(0, COALESCE(total_amount,0) - COALESCE(paid_amount,0))");
        $nowExpr = $driver === 'sqlite'
            ? DB::raw("COALESCE(paid_at, datetime('now'))")
            : DB::raw("COALESCE(paid_at, NOW())");

        // 1. Recompute balance for every row where it drifted from
        //    total_amount - paid_amount (covers rows where paid_amount
        //    was bumped without recomputing the stored balance).
        DB::table('invoices')
            ->whereRaw('ABS(COALESCE(balance,0) - (COALESCE(total_amount,0) - COALESCE(paid_amount,0))) > 0.01')
            ->update([
                'balance' => $balanceExpr,
            ]);

        // 2. Promote draft/sent/overdue rows with a partial payment to
        //    partial_paid.
        DB::table('invoices')
            ->whereIn('status', ['draft', 'sent', 'overdue'])
            ->where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->update(['status' => 'partial_paid']);

        // 3. Promote any non-paid row whose payments cover the total to
        //    fully paid, and stamp paid_at where missing.
        DB::table('invoices')
            ->whereIn('status', ['draft', 'sent', 'overdue', 'partial_paid'])
            ->where('paid_amount', '>', 0)
            ->whereColumn('paid_amount', '>=', 'total_amount')
            ->update([
                'status' => 'paid',
                'paid_at' => $nowExpr,
            ]);
    }

    public function down(): void
    {
        // Data repair; no meaningful rollback.
    }
};
