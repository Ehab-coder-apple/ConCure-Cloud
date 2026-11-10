<?php

namespace Tests\Feature;

use App\Http\Controllers\Master\DashboardController;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Phase 3 follow-up: the Master Admin's own dashboard (not just the
 * per-clinic show page) surfaces a storage alert summary so over-quota
 * clinics are visible without having to click into each one.
 *
 * DashboardController::getClinicStorageAlerts() is private; exercised via
 * reflection against a minimal clinics-only schema (same lightweight
 * pattern as StorageQuotaPhase2Test) to avoid depending on the full users
 * migration chain, which has an unrelated pre-existing break.
 */
class MasterDashboardStorageAlertsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('storage_limit')->default(0);
            $table->unsignedBigInteger('storage_used')->default(0);
            $table->timestamps();
        });

        // A super_admin's hasGlobalClinicAccess()/isSuperAdmin() are pure
        // role checks with no DB lookups, so an unsaved instance works fine
        // with actingAs() here - no need for the full users table schema.
        $this->actingAs(new User(['role' => 'super_admin']));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('clinics');

        parent::tearDown();
    }

    private function callGetClinicStorageAlerts(): array
    {
        $controller = app(DashboardController::class);
        $method = new \ReflectionMethod(DashboardController::class, 'getClinicStorageAlerts');
        $method->setAccessible(true);

        return $method->invoke($controller);
    }

    public function test_clinics_under_90_percent_are_not_flagged(): void
    {
        Clinic::create(['name' => 'Healthy Clinic', 'storage_limit' => 1000, 'storage_used' => 500]);

        $alerts = $this->callGetClinicStorageAlerts();

        $this->assertCount(0, $alerts['critical']);
        $this->assertCount(0, $alerts['warning']);
    }

    public function test_clinic_at_90_to_99_percent_is_bucketed_as_warning(): void
    {
        Clinic::create(['name' => 'Almost Full Clinic', 'storage_limit' => 1000, 'storage_used' => 920]);

        $alerts = $this->callGetClinicStorageAlerts();

        $this->assertCount(0, $alerts['critical']);
        $this->assertCount(1, $alerts['warning']);
        $this->assertSame('Almost Full Clinic', $alerts['warning'][0]['clinic']->name);
        $this->assertTrue($alerts['warning'][0]['info']['warning']);
        $this->assertFalse($alerts['warning'][0]['info']['critical']);
    }

    public function test_clinic_at_or_over_100_percent_is_bucketed_as_critical(): void
    {
        Clinic::create(['name' => 'Mina Clinic', 'storage_limit' => 1000, 'storage_used' => 6789]);

        $alerts = $this->callGetClinicStorageAlerts();

        $this->assertCount(1, $alerts['critical']);
        $this->assertCount(0, $alerts['warning']);
        $this->assertSame('Mina Clinic', $alerts['critical'][0]['clinic']->name);
        $this->assertSame(678.9, $alerts['critical'][0]['info']['percentage_used']);
        $this->assertTrue($alerts['critical'][0]['info']['critical']);
    }

    public function test_clinics_with_zero_storage_limit_are_excluded_to_avoid_division_by_zero(): void
    {
        Clinic::create(['name' => 'Unconfigured Clinic', 'storage_limit' => 0, 'storage_used' => 0]);

        $alerts = $this->callGetClinicStorageAlerts();

        $this->assertCount(0, $alerts['critical']);
        $this->assertCount(0, $alerts['warning']);
    }

    public function test_multiple_flagged_clinics_are_each_bucketed_correctly(): void
    {
        Clinic::create(['name' => 'Healthy', 'storage_limit' => 1000, 'storage_used' => 100]);
        Clinic::create(['name' => 'Warning Clinic', 'storage_limit' => 1000, 'storage_used' => 950]);
        Clinic::create(['name' => 'Critical Clinic A', 'storage_limit' => 1000, 'storage_used' => 1200]);
        Clinic::create(['name' => 'Critical Clinic B', 'storage_limit' => 1000, 'storage_used' => 6789]);

        $alerts = $this->callGetClinicStorageAlerts();

        $this->assertCount(2, $alerts['critical']);
        $this->assertCount(1, $alerts['warning']);
    }
}
