<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the barcode field added to medicines for the Create Sell
 * (multi-item invoice) barcode-scanner feature:
 *  - staff can assign/update a barcode via the Medicine create/edit forms
 *  - a barcode must be unique within a clinic, but the same barcode value
 *    may be reused across different clinics
 *  - Medicine::findByBarcode() correctly scopes lookups by clinic
 */
class MedicineBarcodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ActivationMiddleware::class,
            AuditMiddleware::class,
            EnsureContractIsAccepted::class,
            SetClinicTimezone::class,
            SetLocale::class,
        ]);
    }

    private function makeClinicAdmin(string $suffix): array
    {
        $clinic = Clinic::create([
            'name' => "Barcode Test Clinic {$suffix}",
            'enabled_modules' => ['medicines'],
        ]);

        $user = User::create([
            'username' => "barcode_admin_{$suffix}",
            'email' => "barcode-admin-{$suffix}@example.test",
            'password' => bcrypt('secret'),
            'first_name' => 'Barcode',
            'last_name' => "Admin {$suffix}",
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        return [$clinic, $user];
    }

    public function test_admin_can_create_medicine_with_barcode(): void
    {
        [$clinic, $admin] = $this->makeClinicAdmin('A');

        $response = $this->actingAs($admin)->post(route('medicines.store'), [
            'name' => 'Paracetamol',
            'form' => 'tablet',
            'barcode' => 'BC-0001',
        ]);

        $response->assertRedirect(route('medicines.index'));

        $this->assertDatabaseHas('medicines', [
            'clinic_id' => $clinic->id,
            'name' => 'Paracetamol',
            'barcode' => 'BC-0001',
        ]);
    }

    public function test_admin_can_update_medicine_barcode(): void
    {
        [$clinic, $admin] = $this->makeClinicAdmin('B');

        $medicine = Medicine::create([
            'name' => 'Ibuprofen',
            'form' => 'tablet',
            'clinic_id' => $clinic->id,
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $this->assertNull($medicine->barcode);

        $response = $this->actingAs($admin)->put(route('medicines.update', $medicine), [
            'name' => 'Ibuprofen',
            'form' => 'tablet',
            'barcode' => 'BC-0002',
        ]);

        $response->assertRedirect(route('medicines.show', $medicine));

        $this->assertEquals('BC-0002', $medicine->fresh()->barcode);
    }

    public function test_duplicate_barcode_is_rejected_within_same_clinic(): void
    {
        [$clinic, $admin] = $this->makeClinicAdmin('C');

        Medicine::create([
            'name' => 'Amoxicillin',
            'form' => 'capsule',
            'barcode' => 'DUP-0001',
            'clinic_id' => $clinic->id,
            'created_by' => $admin->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('medicines.store'), [
            'name' => 'Cetirizine',
            'form' => 'tablet',
            'barcode' => 'DUP-0001',
        ]);

        $response->assertSessionHasErrors('barcode');

        $this->assertEquals(
            1,
            Medicine::where('clinic_id', $clinic->id)->where('barcode', 'DUP-0001')->count()
        );
    }

    public function test_same_barcode_is_allowed_across_different_clinics(): void
    {
        [$clinicA, $adminA] = $this->makeClinicAdmin('D1');
        [$clinicB, $adminB] = $this->makeClinicAdmin('D2');

        $this->actingAs($adminA)->post(route('medicines.store'), [
            'name' => 'Vitamin C',
            'form' => 'tablet',
            'barcode' => 'SHARED-0001',
        ])->assertRedirect(route('medicines.index'));

        $this->actingAs($adminB)->post(route('medicines.store'), [
            'name' => 'Vitamin C',
            'form' => 'tablet',
            'barcode' => 'SHARED-0001',
        ])->assertRedirect(route('medicines.index'));

        $this->assertEquals(1, Medicine::where('clinic_id', $clinicA->id)->where('barcode', 'SHARED-0001')->count());
        $this->assertEquals(1, Medicine::where('clinic_id', $clinicB->id)->where('barcode', 'SHARED-0001')->count());
    }

    public function test_find_by_barcode_scopes_lookup_to_the_given_clinic(): void
    {
        [$clinicA, $adminA] = $this->makeClinicAdmin('E1');
        [$clinicB, $adminB] = $this->makeClinicAdmin('E2');

        $medicineA = Medicine::create([
            'name' => 'Omeprazole',
            'form' => 'capsule',
            'barcode' => 'SCAN-0001',
            'clinic_id' => $clinicA->id,
            'created_by' => $adminA->id,
            'is_active' => true,
        ]);

        $found = Medicine::findByBarcode($clinicA->id, 'SCAN-0001');
        $this->assertNotNull($found);
        $this->assertTrue($found->is($medicineA));

        $this->assertNull(Medicine::findByBarcode($clinicB->id, 'SCAN-0001'));
        $this->assertNull(Medicine::findByBarcode($clinicA->id, 'NOT-A-REAL-CODE'));
        $this->assertNull(Medicine::findByBarcode(null, 'SCAN-0001'));
    }
}
