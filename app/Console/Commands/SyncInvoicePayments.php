<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterInvoice;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;

class SyncInvoicePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:sync-invoice-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync historical invoice payments to subscription_payments table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync historical invoice payments...');

        // Get all invoices with paid_amount > 0
        $invoices = MasterInvoice::where('paid_amount', '>', 0)
            ->whereNotNull('payment_date')
            ->get();

        if ($invoices->isEmpty()) {
            $this->warn('No paid invoices found.');
            return 0;
        }

        $this->info("Found {$invoices->count()} paid invoices.");

        $synced = 0;
        $skipped = 0;

        foreach ($invoices as $invoice) {
            // Check if payment already exists for this invoice
            $exists = SubscriptionPayment::where('reference', 'Invoice: ' . $invoice->invoice_number)
                ->exists();

            if ($exists) {
                $this->line("Skipped: {$invoice->invoice_number} (already synced)");
                $skipped++;
                continue;
            }

            try {
                // Create subscription payment record
                SubscriptionPayment::create([
                    'clinic_id' => $invoice->clinic_id,
                    'amount' => $invoice->paid_amount,
                    'currency' => $invoice->currency,
                    'paid_at' => $invoice->payment_date,
                    'method' => $invoice->payment_method ?? 'unknown',
                    'reference' => 'Invoice: ' . $invoice->invoice_number,
                    'notes' => 'Historical payment for invoice ' . $invoice->invoice_number . ' (synced)',
                ]);

                $this->info("Synced: {$invoice->invoice_number} - {$invoice->currency} {$invoice->paid_amount}");
                $synced++;
            } catch (\Exception $e) {
                $this->error("Failed to sync {$invoice->invoice_number}: " . $e->getMessage());
            }
        }

        $this->info("\nSync completed!");
        $this->info("Synced: {$synced}");
        $this->info("Skipped: {$skipped}");

        return 0;
    }
}
