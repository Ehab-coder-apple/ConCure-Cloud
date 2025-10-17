<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Clinic;
use App\Services\ClinicBackupService;

class BackupClinicsWeekly extends Command
{
    protected $signature = 'clinic:backup-weekly {--retention=30 : Retention in days}';

    protected $description = 'Generate weekly backups for all active/activated clinics';

    public function handle(ClinicBackupService $service)
    {
        $retention = (int) $this->option('retention');

        // Start; run only for clinics with per-clinic auto backup enabled
        $this->info('Starting weekly clinic backups (per-clinic enabled only)...');
        $count = 0; $ok = 0; $fail = 0;

        $clinics = Clinic::query()->active()->activated()->get(['id','name']);
        foreach ($clinics as $clinic) {
            // Check per-clinic flag
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $this->line("Skipping clinic {$clinic->id}: settings table missing");
                    continue;
                }
                $val = DB::table('settings')->where('clinic_id', $clinic->id)->where('key','auto_backup_enabled')->value('value');
                $enabled = in_array(strtolower((string)$val), ['1','true','yes','on'], true);
                if (!$enabled) {
                    $this->line("Skipping clinic {$clinic->id}: auto backup disabled");
                    continue;
                }
            } catch (\Throwable $e) {
                $this->line("Skipping clinic {$clinic->id}: cannot read setting - " . $e->getMessage());
                continue;
            }

            $count++;
            $this->line("Backing up clinic #{$clinic->id} - {$clinic->name}...");
            try {
                $service->generateBackupForClinic($clinic->id, 'scheduled', null, $retention);
                $ok++;
                $this->info("✔ Success: clinic {$clinic->id}");
            } catch (\Throwable $e) {
                $fail++;
                $this->error("✖ Failed: clinic {$clinic->id} - " . $e->getMessage());
            }
        }

        $this->info("Completed. Total: {$count}, Success: {$ok}, Failed: {$fail}");
        return $fail === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}

