<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\User;
use App\Services\EmailService;
use App\Services\StorageQuotaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckClinicStorageQuotas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concure:check-clinic-storage-quotas
        {--warning-threshold=90 : Percentage at/above which a clinic is included as a warning}
        {--sync : Recalculate storage_used from source tables for every clinic before checking (slower, authoritative)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all clinics for storage quota usage and alert super admins (email + Slack) about clinics at/over the warning threshold or hard-blocked at 100%+.';

    public function handle(StorageQuotaService $storageService, EmailService $emailService): int
    {
        $warningThreshold = (float) $this->option('warning-threshold');
        $sync = (bool) $this->option('sync');

        $this->info("Scanning clinics for storage usage >= {$warningThreshold}%" . ($sync ? ' (with recalculation)' : '') . '...');

        $warnings = [];
        $critical = [];

        Clinic::query()->orderBy('id')->chunk(100, function ($clinics) use ($storageService, $sync, $warningThreshold, &$warnings, &$critical) {
            foreach ($clinics as $clinic) {
                if ($sync) {
                    $storageService->syncStorageUsed($clinic->id);
                }

                $info = $storageService->getStorageInfo($clinic->id);

                if ($info['percentage_used'] < $warningThreshold) {
                    continue;
                }

                $row = [
                    'clinic' => $clinic,
                    'info' => $info,
                ];

                if ($info['percentage_used'] >= 100) {
                    $critical[] = $row;
                } else {
                    $warnings[] = $row;
                }
            }
        });

        $totalFlagged = count($critical) + count($warnings);

        if ($totalFlagged === 0) {
            $this->info('No clinics at or above the warning threshold. Nothing to report.');
            return Command::SUCCESS;
        }

        $this->warn(sprintf(
            'Found %d clinic(s) over the threshold: %d critical (>=100%%), %d warning (>=%s%%).',
            $totalFlagged,
            count($critical),
            count($warnings),
            $warningThreshold
        ));

        $message = $this->buildDigestMessage($critical, $warnings, $warningThreshold);

        $this->emailSuperAdmins($emailService, $message, count($critical));
        $this->postToSlack($message);

        Log::info('Clinic storage quota digest sent.', [
            'critical_count' => count($critical),
            'warning_count' => count($warnings),
        ]);

        return Command::SUCCESS;
    }

    /**
     * Build a single human-readable digest covering both critical
     * (hard-blocked, >=100%) and warning (>=threshold%, <100%) clinics.
     */
    protected function buildDigestMessage(array $critical, array $warnings, float $warningThreshold): string
    {
        $lines = [];

        if (!empty($critical)) {
            $lines[] = "CRITICAL - clinics at/over 100% (uploads are hard-blocked):";
            foreach ($critical as $row) {
                $lines[] = $this->formatClinicLine($row['clinic'], $row['info']);
            }
            $lines[] = '';
        }

        if (!empty($warnings)) {
            $lines[] = "WARNING - clinics at/over {$warningThreshold}% (not yet blocked):";
            foreach ($warnings as $row) {
                $lines[] = $this->formatClinicLine($row['clinic'], $row['info']);
            }
            $lines[] = '';
        }

        $lines[] = 'Use "Quick Change Limit" on the clinic\'s Master dashboard page to raise its storage limit.';

        return implode("\n", $lines);
    }

    protected function formatClinicLine(Clinic $clinic, array $info): string
    {
        return sprintf(
            '- %s (ID %d): %.2f%% used - %s GB / %s GB',
            $clinic->name,
            $clinic->id,
            $info['percentage_used'],
            $info['used_gb'],
            $info['limit_gb']
        );
    }

    /**
     * Email every super_admin user a single digest (not one email per
     * clinic) so this doesn't spam the inbox as more clinics are added.
     */
    protected function emailSuperAdmins(EmailService $emailService, string $message, int $criticalCount): void
    {
        if (!config('concure.notifications.email_enabled', true)) {
            $this->warn('Email notifications are disabled (concure.notifications.email_enabled) - skipping email digest.');
            return;
        }

        $superAdmins = User::where('role', 'super_admin')
            ->whereNotNull('email')
            ->get();

        if ($superAdmins->isEmpty()) {
            $this->warn('No super_admin users with an email address found - skipping email digest.');
            return;
        }

        $subject = $criticalCount > 0
            ? "[ConCure] URGENT: {$criticalCount} clinic(s) hard-blocked on storage"
            : '[ConCure] Clinic storage usage warning';

        foreach ($superAdmins as $admin) {
            $emailService->sendEmail($admin->email, $subject, $message);
        }

        $this->info("Emailed storage digest to {$superAdmins->count()} super admin(s).");
    }

    /**
     * Post the same digest to Slack via incoming webhook, if configured.
     * Silently skipped (with a log entry) if no webhook URL is set.
     */
    protected function postToSlack(string $message): void
    {
        $webhookUrl = config('services.slack.storage_alerts_webhook_url');

        if (!$webhookUrl) {
            $this->line('Slack webhook not configured (services.slack.storage_alerts_webhook_url) - skipping Slack post.');
            return;
        }

        try {
            $response = Http::timeout(10)->post($webhookUrl, [
                'text' => "*ConCure Storage Quota Report*\n```{$message}```",
            ]);

            if ($response->failed()) {
                Log::warning('Slack storage alert post failed.', ['status' => $response->status()]);
            } else {
                $this->info('Posted storage digest to Slack.');
            }
        } catch (\Throwable $e) {
            Log::warning('Slack storage alert post threw an exception.', ['error' => $e->getMessage()]);
        }
    }
}
