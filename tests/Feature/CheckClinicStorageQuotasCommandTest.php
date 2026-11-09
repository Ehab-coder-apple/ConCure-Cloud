<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\User;
use App\Services\StorageQuotaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Phase 2 coverage: the daily concure:check-clinic-storage-quotas command
 * correctly classifies clinics into warning (>=threshold%, <100%) vs
 * critical (>=100%, hard-blocked), emails all super_admin users a single
 * digest, and posts to Slack when a webhook URL is configured.
 */
class CheckClinicStorageQuotasCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('storage_limit')->default(StorageQuotaService::DEFAULT_LIMIT);
            $table->unsignedBigInteger('storage_used')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('role');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Cache::flush();

        Mail::fake();
        Http::fake();
    }

    protected function tearDown(): void
    {
        foreach (['users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    /**
     * StorageQuotaService::getStorageUsed() always computes/caches usage
     * from the patient_files/patient_images/patient_videos/dental_images
     * tables (not the clinics.storage_used column, which is just a synced
     * display copy) - pre-seed that cache entry directly since this test
     * doesn't create those file tables.
     */
    private function seedUsage(Clinic $clinic, int $bytes): void
    {
        Cache::put("clinic_{$clinic->id}_storage_used", $bytes, 600);
    }

    private function makeSuperAdmin(string $email = 'super@example.com'): User
    {
        return User::create([
            'username' => 'super_' . uniqid(),
            'email' => $email,
            'password' => bcrypt('password'),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_command_reports_no_findings_when_all_clinics_are_under_threshold(): void
    {
        $this->makeSuperAdmin();
        $clinic = Clinic::create(['name' => 'Healthy Clinic', 'storage_limit' => 1_000_000, 'storage_used' => 100]);
        $this->seedUsage($clinic, 100);

        $this->artisan('concure:check-clinic-storage-quotas')
            ->expectsOutputToContain('No clinics at or above the warning threshold')
            ->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_command_emails_super_admins_for_warning_clinic(): void
    {
        $admin = $this->makeSuperAdmin('warn-admin@example.com');
        // 950/1000 = 95% - over the default 90% warning threshold, under 100%.
        $clinic = Clinic::create(['name' => 'Almost Full Clinic', 'storage_limit' => 1000, 'storage_used' => 950]);
        $this->seedUsage($clinic, 950);

        $this->artisan('concure:check-clinic-storage-quotas')->assertSuccessful();

        Mail::assertSent(\App\Mail\GenericEmail::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_command_flags_clinic_at_or_over_100_percent_as_critical(): void
    {
        $this->makeSuperAdmin();
        $clinic = Clinic::create(['name' => 'Over Limit Clinic', 'storage_limit' => 1000, 'storage_used' => 1200]);
        $this->seedUsage($clinic, 1200);

        $this->artisan('concure:check-clinic-storage-quotas')
            ->expectsOutputToContain('1 critical')
            ->assertSuccessful();
    }

    public function test_command_respects_custom_warning_threshold_option(): void
    {
        $this->makeSuperAdmin();
        // 800/1000 = 80% - would be ignored at the default 90% threshold,
        // but should be flagged when threshold is lowered to 75%.
        $clinic = Clinic::create(['name' => 'Mid Usage Clinic', 'storage_limit' => 1000, 'storage_used' => 800]);
        $this->seedUsage($clinic, 800);

        $this->artisan('concure:check-clinic-storage-quotas', ['--warning-threshold' => 75])
            ->expectsOutputToContain('1 warning')
            ->assertSuccessful();
    }

    public function test_command_posts_to_slack_when_webhook_configured(): void
    {
        config(['services.slack.storage_alerts_webhook_url' => 'https://hooks.slack.test/abc']);
        $this->makeSuperAdmin();
        $clinic = Clinic::create(['name' => 'Over Limit Clinic', 'storage_limit' => 1000, 'storage_used' => 1200]);
        $this->seedUsage($clinic, 1200);

        $this->artisan('concure:check-clinic-storage-quotas')->assertSuccessful();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://hooks.slack.test/abc'
                && str_contains($request['text'], 'Over Limit Clinic');
        });
    }

    public function test_command_skips_slack_when_webhook_not_configured(): void
    {
        config(['services.slack.storage_alerts_webhook_url' => null]);
        $this->makeSuperAdmin();
        $clinic = Clinic::create(['name' => 'Over Limit Clinic', 'storage_limit' => 1000, 'storage_used' => 1200]);
        $this->seedUsage($clinic, 1200);

        $this->artisan('concure:check-clinic-storage-quotas')->assertSuccessful();

        Http::assertNothingSent();
    }
}
