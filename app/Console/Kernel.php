<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ResetSuperAdmin;
use App\Console\Commands\SendSubscriptionRenewalReminders;
use App\Console\Commands\BackupClinicsWeekly;
use App\Console\Commands\ProcessScheduledNotifications;
use App\Console\Commands\CheckClinicStorageQuotas;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     * Explicitly list commands to ensure availability after deploys.
     *
     * @var array
     */
    protected $commands = [
        ResetSuperAdmin::class,
        SendSubscriptionRenewalReminders::class,
        BackupClinicsWeekly::class,
        ProcessScheduledNotifications::class,
        CheckClinicStorageQuotas::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send subscription renewal reminders daily at 9:00 AM server time
        $schedule->command('concure:send-subscription-renewal-reminders')->dailyAt('09:00');

        // Weekly clinic backups every Sunday 02:30 server time
        $schedule->command('clinic:backup-weekly')->weeklyOn(0, '02:30')->withoutOverlapping()->onOneServer();

        // Process scheduled WhatsApp notifications every minute
        $schedule->command('notifications:process')->everyMinute()->withoutOverlapping();

        // Daily clinic storage quota scan: alerts super admins (email +
        // Slack) about clinics at/over the 90% warning threshold or
        // already hard-blocked at 100%+.
        $schedule->command('concure:check-clinic-storage-quotas')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
