<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class FixInvoiceStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:fix-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix invoice statuses based on payment amounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Fixing Invoice Statuses...');
        $this->info('============================');
        $this->newLine();

        // Get all invoices with paid_amount > 0 and balance > 0 but status != 'partial_paid'
        $invoicesToFix = Invoice::where('paid_amount', '>', 0)
            ->where('balance', '>', 0)
            ->where('status', '!=', 'partial_paid')
            ->get();

        if ($invoicesToFix->isEmpty()) {
            $this->info('✅ No invoices need fixing. All statuses are correct!');
            return 0;
        }

        $this->info("Found {$invoicesToFix->count()} invoice(s) to fix:");
        $this->newLine();

        $bar = $this->output->createProgressBar($invoicesToFix->count());
        $bar->start();

        foreach ($invoicesToFix as $invoice) {
            $oldStatus = $invoice->status;
            
            $invoice->updateStatus();
            $invoice->save();
            
            $this->newLine();
            $this->line("  ✓ {$invoice->invoice_number}: {$oldStatus} → {$invoice->status}");
            $this->line("    Paid: \${$invoice->paid_amount}, Balance: \${$invoice->balance}");
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Done! Fixed ' . $invoicesToFix->count() . ' invoice(s).');

        return 0;
    }
}

