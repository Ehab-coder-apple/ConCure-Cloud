<?php

namespace App\Console\Commands;

use App\Models\ScheduledNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessScheduledNotifications extends Command
{
    protected $signature = 'notifications:process
        {--limit=50 : Maximum notifications to process per run}
        {--clinic= : Only process notifications for a specific clinic ID}';

    protected $description = 'Process pending scheduled notifications (WhatsApp reminders)';

    public function handle(NotificationService $service): int
    {
        $limit = (int) $this->option('limit');
        $clinicFilter = $this->option('clinic');

        $this->info('🔔 Processing scheduled notifications...');
        $this->info('   Time: ' . now()->toDateTimeString());

        // Bypass global scopes — CLI has no auth user.
        // Use the model's scopeDue() for consistent query logic.
        $query = ScheduledNotification::withoutGlobalScopes()
            ->due()
            ->orderBy('scheduled_at')
            ->limit($limit);

        // Optional clinic filter for targeted runs
        if ($clinicFilter) {
            $query->where('clinic_id', (int) $clinicFilter);
            $this->info("   Filtering: clinic_id = {$clinicFilter}");
        }

        $due = $query->get();

        if ($due->isEmpty()) {
            $this->info('✅ No pending notifications to process.');
            return 0;
        }

        // Group by clinic for audit logging
        $byClinic = $due->groupBy('clinic_id');
        $this->info("Found {$due->count()} notification(s) across {$byClinic->count()} clinic(s).");

        $sent = 0;
        $failed = 0;

        foreach ($byClinic as $clinicId => $notifications) {
            $this->line('');
            $this->info("── Clinic #{$clinicId} ({$notifications->count()} notification(s)) ──");

            foreach ($notifications as $notification) {
                $label = "  [#{$notification->id}] {$notification->type} → patient #{$notification->patient_id}";

                $result = $service->send($notification);

                if ($result) {
                    $sent++;
                    $this->line("{$label} ✅ sent");
                } else {
                    $failed++;
                    $error = $notification->fresh()?->last_error ?? 'unknown';
                    $this->warn("{$label} ❌ failed ({$error})");
                }
            }
        }

        $this->newLine();
        $this->info("✅ Done — Sent: {$sent}, Failed: {$failed}, Total: " . ($sent + $failed));

        return 0;
    }
}

