<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clinic;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendSubscriptionRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concure:send-subscription-renewal-reminders {--days=7 : Days before due date to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder emails to clinics N days before next_billing_at (default 7 days).';

    public function handle(EmailService $emailService)
    {
        $days = (int) $this->option('days');
        $targetDate = now()->startOfDay()->addDays($days)->toDateString();

        $this->info("Sending reminders for clinics with due date = {$targetDate} (T-{$days} days)...");

        if (!config('concure.notifications.email_enabled', true)) {
            $this->warn('Email notifications are disabled by config.concure.notifications.email_enabled');
            return Command::SUCCESS;
        }

        $clinics = Clinic::active()
            ->activated()
            ->whereNotNull('next_billing_at')
            ->whereDate('next_billing_at', $targetDate)
            ->get();

        $count = 0;
        foreach ($clinics as $clinic) {
            $amount = $this->computeCycleAmount($clinic);
            $cycleLabel = $this->cycleLabel($clinic->billing_cycle ?? 'monthly');
            $dueDate = optional($clinic->next_billing_at)->format('M d, Y');

            $subject = "Subscription renewal due in {$days} days - {$clinic->name}";
            $message = "Dear {$clinic->name},\n\n".
                "This is a friendly reminder that your subscription will be due on {$dueDate}.\n".
                "Billing cycle: {$cycleLabel}\n".
                ($amount !== null ? ("Amount due: $".number_format($amount,2)."\n") : "").
                "\nPlease renew your subscription to avoid service interruption.\n".
                "If you have any questions, reply to this email or contact support.\n\n".
                "Thank you,\nConCure";

            $recipients = User::byClinic($clinic->id)
                ->byRole('admin')
                ->active()
                ->activated()
                ->pluck('email')
                ->filter()
                ->values();

            if ($recipients->isEmpty() && $clinic->email) {
                $recipients = collect([$clinic->email]);
            }

            foreach ($recipients as $to) {
                $emailService->sendEmail($to, $subject, $message);
                $this->line("Sent reminder to {$to} for clinic {$clinic->name}");
                $count++;
            }
        }

        $this->info("Done. Reminders sent: {$count}");
        return Command::SUCCESS;
    }

    private function computeCycleAmount(Clinic $clinic): ?float
    {
        $plan = $clinic->plan;
        $cycle = $clinic->billing_cycle ?? 'monthly';
        $m = $clinic->custom_monthly_price ?? ($plan->monthly_price ?? null);
        $y = $clinic->custom_yearly_price ?? ($plan->yearly_price ?? null);
        $amount = null;
        switch ($cycle) {
            case 'yearly':
                $amount = $y ?? ($m !== null ? $m * 12 : null);
                break;
            case 'quarterly':
                $amount = $y !== null ? $y / 4 : ($m !== null ? $m * 3 : null);
                break;
            case 'semiannual':
                $amount = $y !== null ? $y / 2 : ($m !== null ? $m * 6 : null);
                break;
            default: // monthly
                $amount = $m;
        }
        return $amount !== null ? (float) $amount : null;
    }

    private function cycleLabel(string $cycle): string
    {
        return match ($cycle) {
            'yearly' => 'Yearly (1x/year)',
            'quarterly' => 'Quarterly (4x/year)',
            'semiannual' => 'Semiannual (2x/year)',
            default => 'Monthly (every month)',
        };
    }
}

