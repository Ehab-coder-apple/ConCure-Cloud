<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\AestheticInvoice;
use App\Models\Receipt;
use Carbon\Carbon;

class DiagnoseRevenue extends Command
{
    protected $signature = 'dashboard:diagnose-revenue {clinic_id?}';
    protected $description = 'Diagnose revenue data for the dashboard chart';

    public function handle()
    {
        $clinicId = $this->argument('clinic_id');

        if (!$clinicId) {
            $this->error('Please provide a clinic ID: php artisan dashboard:diagnose-revenue {clinic_id}');
            return 1;
        }

        $this->info("=== Revenue Diagnosis for Clinic ID: {$clinicId} ===\n");

        // Check last 6 months (default dashboard view)
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('M Y');

            $this->info("--- {$key} ---");

            // Invoices
            $invoiceRevenue = Invoice::where('clinic_id', $clinicId)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->sum('total_amount');
            $invoiceCount = Invoice::where('clinic_id', $clinicId)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->count();
            $this->line("  Invoices: {$invoiceCount} invoices, Total: {$invoiceRevenue}");

            // Aesthetic Invoices
            $aestheticRevenue = AestheticInvoice::where('clinic_id', $clinicId)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->sum('total_amount');
            $aestheticCount = AestheticInvoice::where('clinic_id', $clinicId)
                ->whereMonth('invoice_date', $month->month)
                ->whereYear('invoice_date', $month->year)
                ->count();
            $this->line("  Aesthetic Invoices: {$aestheticCount} invoices, Total: {$aestheticRevenue}");

            // Receipts (approved only)
            $receiptRevenue = Receipt::where('clinic_id', $clinicId)
                ->where('status', 'approved')
                ->whereMonth('receipt_date', $month->month)
                ->whereYear('receipt_date', $month->year)
                ->sum('amount');
            $receiptCount = Receipt::where('clinic_id', $clinicId)
                ->where('status', 'approved')
                ->whereMonth('receipt_date', $month->month)
                ->whereYear('receipt_date', $month->year)
                ->count();
            $this->line("  Receipts (approved): {$receiptCount} receipts, Total: {$receiptRevenue}");

            // Total Revenue
            $totalRevenue = $invoiceRevenue + $aestheticRevenue + $receiptRevenue;
            $this->line("  TOTAL REVENUE: {$totalRevenue}\n");
        }

        // Show some sample data
        $this->info("\n=== Sample Recent Invoices ===");
        $recentInvoices = Invoice::where('clinic_id', $clinicId)
            ->orderBy('invoice_date', 'desc')
            ->limit(5)
            ->get(['invoice_number', 'invoice_date', 'total_amount', 'status']);
        if ($recentInvoices->count() > 0) {
            foreach ($recentInvoices as $inv) {
                $this->line("  #{$inv->invoice_number} | {$inv->invoice_date} | {$inv->total_amount} | {$inv->status}");
            }
        } else {
            $this->warn("  No invoices found");
        }

        $this->info("\n=== Sample Recent Receipts (Approved) ===");
        $recentReceipts = Receipt::where('clinic_id', $clinicId)
            ->where('status', 'approved')
            ->orderBy('receipt_date', 'desc')
            ->limit(5)
            ->get(['receipt_number', 'receipt_date', 'amount', 'category']);
        if ($recentReceipts->count() > 0) {
            foreach ($recentReceipts as $rec) {
                $this->line("  #{$rec->receipt_number} | {$rec->receipt_date} | {$rec->amount} | {$rec->category}");
            }
        } else {
            $this->warn("  No approved receipts found");
        }

        $this->info("\n=== Diagnosis Complete ===");
        return 0;
    }
}
