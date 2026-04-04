<?php

namespace App\Console\Commands;

use App\Models\DentalTreatment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDentalFinances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dental:sync-historical-finances
                          {--dry-run : Show what would be synced without making changes}
                          {--clinic= : Only sync treatments for a specific clinic ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing dental treatments with the Finance module (create invoices and receipts)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $clinicId = $this->option('clinic');

        $this->info('========================================');
        $this->info('Dental Treatment Finance Sync');
        $this->info('========================================');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Find all treatments with financial data but no invoice link
        $query = DentalTreatment::whereNull('invoice_id')
            ->where(function ($q) {
                $q->where('actual_cost', '>', 0)
                  ->orWhere('estimated_cost', '>', 0);
            })
            ->with(['patient', 'creator']);

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
            $this->info("Filtering by clinic ID: {$clinicId}");
        }

        $treatments = $query->get();
        $total = $treatments->count();

        if ($total === 0) {
            $this->info('No dental treatments found that need syncing.');
            return 0;
        }

        $this->info("Found {$total} dental treatments to sync.\n");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($treatments as $treatment) {
            try {
                if ($dryRun) {
                    $this->logDryRun($treatment);
                    $synced++;
                } else {
                    $this->syncTreatment($treatment);
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to sync dental treatment finances', [
                    'treatment_id' => $treatment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('========================================');
        $this->info('Sync Complete');
        $this->info('========================================');
        $this->info("Total treatments processed: {$total}");
        $this->info("Successfully synced: {$synced}");
        if ($errors > 0) {
            $this->error("Errors: {$errors}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a DRY RUN. Run without --dry-run to apply changes.');
        }

        return 0;
    }

    /**
     * Sync a single treatment with the Finance module.
     */
    private function syncTreatment(DentalTreatment $treatment): void
    {
        DB::transaction(function () use ($treatment) {
            $totalCost = $treatment->actual_cost ?? $treatment->estimated_cost ?? 0;
            $paidAmount = $treatment->paid_amount ?? 0;

            // Get creator or fallback to first admin
            $creator = $treatment->creator ?? User::where('clinic_id', $treatment->clinic_id)
                ->where('role', 'admin')
                ->first();

            if (!$creator) {
                throw new \Exception("No valid user found for clinic {$treatment->clinic_id}");
            }

            // Create invoice
            $invoice = Invoice::create([
                'patient_id' => $treatment->patient_id,
                'clinic_id' => $treatment->clinic_id,
                'invoice_date' => $treatment->created_at->toDateString(),
                'due_date' => $treatment->scheduled_date ?? $treatment->created_at->addDays(30)->toDateString(),
                'subtotal' => 0,
                'tax_rate' => 0,
                'discount_rate' => 0,
                'discount_amount' => 0,
                'paid_amount' => $paidAmount,
                'status' => 'draft',
                'notes' => "Historical sync: Dental Treatment #{$treatment->treatment_number}",
                'created_by' => $creator->id,
            ]);

            // Add invoice item
            $invoice->addItem([
                'description' => "Dental Treatment: {$treatment->procedure_name}",
                'quantity' => 1,
                'unit_price' => $totalCost,
                'item_type' => 'procedure',
            ]);

            // Update invoice status
            $invoice->updateStatus();
            $invoice->save();

            // Link invoice to treatment
            $treatment->invoice_id = $invoice->id;
            $treatment->saveQuietly();

            // Create receipt if there's a payment
            if ($paidAmount > 0) {
                Receipt::create([
                    'clinic_id' => $treatment->clinic_id,
                    'description' => "Payment for Dental Treatment: {$treatment->procedure_name}",
                    'amount' => $paidAmount,
                    'category' => 'procedure_fee',
                    'receipt_date' => $treatment->created_at->toDateString(),
                    'payment_method' => 'cash',
                    'payer_name' => $treatment->patient ?
                        trim(($treatment->patient->first_name ?? '') . ' ' . ($treatment->patient->last_name ?? '')) : null,
                    'reference_number' => $treatment->treatment_number,
                    'notes' => "Historical sync: Dental treatment payment",
                    'created_by' => $creator->id,
                    'status' => 'approved',
                    'approved_by' => $creator->id,
                    'approved_at' => $treatment->created_at,
                ]);
            }
        });
    }

    /**
     * Log what would be synced in dry-run mode.
     */
    private function logDryRun(DentalTreatment $treatment): void
    {
        $totalCost = $treatment->actual_cost ?? $treatment->estimated_cost ?? 0;
        $paidAmount = $treatment->paid_amount ?? 0;

        // Just count, don't output (to avoid cluttering progress bar)
    }
}

