<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ResetSuperAdmin;
use App\Console\Commands\SendSubscriptionRenewalReminders;

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
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send subscription renewal reminders daily at 9:00 AM server time
        $schedule->command('concure:send-subscription-renewal-reminders')->dailyAt('09:00');
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
