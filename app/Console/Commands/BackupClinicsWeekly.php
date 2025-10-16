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
        $this->info('Starting weekly clinic backups...');

        $retention = (int) $this->option('retention');
        $count = 0; $ok = 0; $fail = 0;

        $clinics = Clinic::query()->active()->activated()->get(['id','name']);
        foreach ($clinics as $clinic) {
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

