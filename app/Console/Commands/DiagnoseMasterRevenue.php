<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagnoseMasterRevenue extends Command
{
    protected $signature = 'master:diagnose-revenue';
    protected $description = 'Diagnose Master Finance revenue chart data (subscription payments)';

    public function handle()
    {
        $this->info("=== Master Finance Revenue Diagnosis ===\n");

        // Check subscription_payments table
        $totalPayments = DB::table('subscription_payments')->count();
        $this->info("Total subscription payments in database: {$totalPayments}");

        $nonDemoPayments = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', false)
            ->count();
        $this->info("Payments from non-demo clinics: {$nonDemoPayments}");

        $demoPayments = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->where('clinics.is_demo', true)
            ->count();
        $this->info("Payments from demo clinics (excluded): {$demoPayments}\n");

        // Check for null paid_at dates
        $nullDates = DB::table('subscription_payments')
            ->whereNull('paid_at')
            ->count();
        if ($nullDates > 0) {
            $this->warn("WARNING: {$nullDates} payments have NULL paid_at dates!");
        }

        // Monthly breakdown (last 6 months)
        $this->info("\n=== Monthly Revenue (Last 6 Months) ===");
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = DB::table('subscription_payments')
                ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                ->where('clinics.is_demo', false)
                ->whereBetween('subscription_payments.paid_at', [$monthStart, $monthEnd])
                ->sum('subscription_payments.amount');

            $count = DB::table('subscription_payments')
                ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
                ->where('clinics.is_demo', false)
                ->whereBetween('subscription_payments.paid_at', [$monthStart, $monthEnd])
                ->count();

            $this->line($month->format('M Y') . ": {$count} payments, Total Revenue: {$revenue} IQD");
        }

        // Recent payments sample
        $this->info("\n=== Recent Subscription Payments (Last 10) ===");
        $recent = DB::table('subscription_payments')
            ->join('clinics', 'subscription_payments.clinic_id', '=', 'clinics.id')
            ->select([
                'subscription_payments.id',
                'clinics.name as clinic_name',
                'clinics.is_demo',
                'subscription_payments.amount',
                'subscription_payments.paid_at',
                'subscription_payments.method'
            ])
            ->orderBy('subscription_payments.paid_at', 'desc')
            ->limit(10)
            ->get();

        if ($recent->count() > 0) {
            foreach ($recent as $payment) {
                $demo = $payment->is_demo ? ' [DEMO - EXCLUDED]' : '';
                $this->line(
                    "ID: {$payment->id} | " .
                    "{$payment->clinic_name}{$demo} | " .
                    "{$payment->amount} IQD | " .
                    "{$payment->paid_at} | " .
                    "{$payment->method}"
                );
            }
        } else {
            $this->warn("No subscription payments found in the database!");
        }

        // Check master_expenses
        $this->info("\n=== Master Expenses (Platform Operating Costs) ===");
        $totalExpenses = DB::table('master_expenses')->count();
        $this->info("Total expenses: {$totalExpenses}");

        $recentExpenses = DB::table('master_expenses')
            ->orderBy('expense_date', 'desc')
            ->limit(5)
            ->get(['id', 'description', 'amount', 'expense_date', 'category']);

        if ($recentExpenses->count() > 0) {
            foreach ($recentExpenses as $expense) {
                $this->line(
                    "ID: {$expense->id} | " .
                    "{$expense->description} | " .
                    "{$expense->amount} IQD | " .
                    "{$expense->expense_date} | " .
                    "{$expense->category}"
                );
            }
        } else {
            $this->info("No expenses recorded yet.");
        }

        $this->info("\n=== Diagnosis Complete ===");
        $this->info("If revenue is zero, check:");
        $this->info("1. Are there any subscription payments from non-demo clinics?");
        $this->info("2. Do the payments have valid paid_at dates?");
        $this->info("3. Are the payment dates within the selected period filter?");

        return 0;
    }
}
