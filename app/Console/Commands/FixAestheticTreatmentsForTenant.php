<?php

namespace App\Console\Commands;

use App\Models\AestheticTreatment;
use App\Models\Clinic;
use Illuminate\Console\Command;

class FixAestheticTreatmentsForTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aesthetic:fix-treatments {tenant_id? : Specific tenant ID to fix, or "all" for all tenants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clone built-in aesthetic treatments for tenants that have none';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantIdArg = $this->argument('tenant_id');

        if ($tenantIdArg === 'all') {
            return $this->fixAllTenants();
        } elseif ($tenantIdArg) {
            return $this->fixSpecificTenant($tenantIdArg);
        } else {
            // Interactive mode - show list of tenants
            return $this->interactiveMode();
        }
    }

    /**
     * Fix all tenants that have no treatments.
     */
    private function fixAllTenants(): int
    {
        $this->info('Finding all tenants with no aesthetic treatments...');

        $tenants = Clinic::select('tenant_id')
            ->whereNotNull('tenant_id')
            ->where('tenant_id', '!=', 'TEN-1') // Exclude built-in tenant
            ->distinct()
            ->pluck('tenant_id');

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found.');
            return 0;
        }

        $this->info('Found ' . $tenants->count() . ' unique tenants.');

        $fixed = 0;
        $skipped = 0;

        foreach ($tenants as $tenantId) {
            $count = AestheticTreatment::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->count();

            if ($count === 0) {
                $this->info("Fixing tenant: {$tenantId}");
                $cloned = AestheticTreatment::cloneBuiltInForTenant($tenantId);
                $this->info("  Cloned {$cloned} treatments");
                $fixed++;
            } else {
                $this->line("  Skipping {$tenantId} - already has {$count} treatments");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Fixed: {$fixed} tenants");
        $this->info("  Skipped: {$skipped} tenants (already have treatments)");

        return 0;
    }

    /**
     * Fix a specific tenant.
     */
    private function fixSpecificTenant(string $tenantId): int
    {
        $this->info("Checking tenant: {$tenantId}");

        // Verify tenant exists
        $clinic = Clinic::where('tenant_id', $tenantId)->first();
        if (!$clinic) {
            $this->error("No clinic found with tenant_id: {$tenantId}");
            return 1;
        }

        $this->info("Clinic: {$clinic->name} (ID: {$clinic->id})");

        $count = AestheticTreatment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->count();

        $this->info("Current treatments: {$count}");

        if ($count > 0) {
            if (!$this->confirm('This tenant already has treatments. Do you want to clone additional built-in treatments?')) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $cloned = AestheticTreatment::cloneBuiltInForTenant($tenantId);
        $this->info("Cloned {$cloned} treatments");

        return 0;
    }

    /**
     * Interactive mode - show list of tenants and let user choose.
     */
    private function interactiveMode(): int
    {
        $this->info('Fetching tenants...');

        $clinics = Clinic::whereNotNull('tenant_id')
            ->where('tenant_id', '!=', 'TEN-1')
            ->get(['id', 'name', 'tenant_id']);

        if ($clinics->isEmpty()) {
            $this->warn('No clinics found.');
            return 0;
        }

        // Show list with treatment counts
        $this->table(
            ['Clinic ID', 'Clinic Name', 'Tenant ID', 'Treatments'],
            $clinics->map(function ($clinic) {
                $count = AestheticTreatment::withoutGlobalScope('tenant')
                    ->where('tenant_id', $clinic->tenant_id)
                    ->count();
                return [
                    $clinic->id,
                    $clinic->name,
                    $clinic->tenant_id,
                    $count
                ];
            })
        );

        return 0;
    }
}
