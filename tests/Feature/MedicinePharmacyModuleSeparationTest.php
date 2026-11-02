<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\SimplePrescription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

/**
 * Covers the Medicine (free, core prescribing) vs Pharmacy/Inventory
 * (paid add-on: stock, dispensing, sale invoices) module split:
 *
 *  1. A doctor can still write and save a plain free-text prescription
 *     when the 'medicines' (Pharmacy/Inventory) module is disabled, and
 *     doing so must NOT create any row in the Medicine inventory catalog.
 *  2. Dispensing a prescription (convertToSale) must be strictly blocked
 *     with a 403 when the clinic's Pharmacy/Inventory module is disabled,
 *     regardless of the acting user's role.
 */
class MedicinePharmacyModuleSeparationTest extends TestCase
{
    private Clinic $clinic;
    private User $doctor;
    private User $pharmacist;
    private Patient $patient;

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

        ViewFacade::share([
            'appName' => config('app.name'),
            'companyName' => config('concure.company_name', 'ConCure'),
            'primaryColor' => config('concure.primary_color', '#008080'),
            'supportedLanguages' => config('concure.supported_languages', [
                'en' => 'English',
                'ar' => 'العربية',
            ]),
        ]);

        Schema::dropIfExists('simple_prescription_medicines');
        Schema::dropIfExists('simple_prescriptions');
        Schema::dropIfExists('medicine_forms');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
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
            $table->timestamp('activated_at')->nullable();
            $table->string('language', 10)->default('en');
            $table->json('permissions')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('dosage')->nullable();
            $table->string('form')->nullable();
            $table->boolean('is_frequent')->default(false);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->string('label');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('simple_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->string('prescription_number')->unique();
            $table->text('diagnosis')->nullable();
            $table->string('visit_type', 30)->nullable();
            $table->text('notes')->nullable();
            $table->date('prescribed_date');
            $table->string('status')->default('active');
            $table->boolean('sent_to_doctor')->default(false);
            $table->timestamp('sent_to_doctor_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->boolean('is_dispensed')->default(false);
            $table->timestamp('dispensed_at')->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            $table->string('dispense_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('simple_prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->string('medicine_name');
            $table->string('type', 50)->nullable();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // Legacy prescriptions tables (App\Http\Controllers\PrescriptionController),
        // still live-routed under /prescriptions and used for old data.
        Schema::dropIfExists('prescription_medicines');
        Schema::dropIfExists('prescriptions');

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id');
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->date('prescribed_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medicine_id')->nullable();
            $table->string('medicine_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('duration');
            $table->text('instructions')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });

        // Pharmacy/Inventory (the paid add-on) is NOT in enabled_modules -
        // only the free "Medicine" (prescriptions) module is enabled.
        $this->clinic = Clinic::create([
            'name' => 'Medicine/Pharmacy Split Test Clinic',
            'tenant_id' => 'TEN-MEDPHARM',
            'enabled_modules' => ['prescriptions', 'quick_visit'],
        ]);

        $this->doctor = User::create([
            'username' => 'medpharm_doctor',
            'email' => 'medpharm-doctor@example.test',
            'password' => bcrypt('secret'),
            'first_name' => 'Med',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->pharmacist = User::create([
            'username' => 'medpharm_pharmacist',
            'email' => 'medpharm-pharmacist@example.test',
            'password' => bcrypt('secret'),
            'first_name' => 'Med',
            'last_name' => 'Pharmacist',
            'role' => 'pharmacist',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => 'P-100',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'date_of_birth' => now()->subYears(40)->toDateString(),
            'gender' => 'male',
            'phone' => '07709876543',
        ]);
    }

    public function test_doctor_can_save_free_text_prescription_when_pharmacy_inventory_module_disabled(): void
    {
        $this->assertFalse($this->doctor->canAccessModule('medicines'));
        $this->assertSame(0, Medicine::count());

        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Upper respiratory tract infection',
            'medicines' => [
                [
                    'name' => 'new:Azithromycin',
                    'dosage' => '250mg',
                    'frequency' => 'Once daily',
                    'duration' => '5 days',
                    'instructions' => 'Take with food',
                ],
            ],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        // Prescription was written and saved successfully.
        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Azithromycin',
            'dosage' => '250mg',
            'frequency' => 'Once daily',
            'duration' => '5 days',
        ]);

        // But no inventory/catalog row was created, because the
        // Pharmacy/Inventory module is disabled for this clinic.
        $this->assertSame(0, Medicine::count());
        $this->assertDatabaseMissing('medicines', ['name' => 'Azithromycin']);
    }

    public function test_legacy_prescription_controller_saves_free_text_without_creating_inventory_when_module_disabled(): void
    {
        // The legacy (pre-SimplePrescription) controller is still bound to
        // live /prescriptions routes - it must honor the same Medicine vs
        // Pharmacy/Inventory split: typed medication names save as plain
        // text, and no catalog row is auto-created, when the Pharmacy/
        // Inventory add-on is disabled.
        $this->assertFalse($this->doctor->canAccessModule('medicines'));
        $this->assertSame(0, Medicine::count());

        $response = $this->actingAs($this->doctor)->post(route('prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Tonsillitis',
            'medications' => [
                [
                    'name' => 'Cefuroxime',
                    'strength' => '500mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Twice daily',
                    'duration' => '7 days',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('prescription_medicines', [
            'medicine_name' => 'Cefuroxime',
            'medicine_id' => null,
        ]);

        $this->assertSame(0, Medicine::count());
        $this->assertDatabaseMissing('medicines', ['name' => 'Cefuroxime']);
    }

    public function test_legacy_prescription_controller_links_inventory_when_module_enabled(): void
    {
        // Regression guard: with Pharmacy/Inventory enabled, the legacy
        // controller's find-or-create inventory behavior still works.
        $this->clinic->update(['enabled_modules' => ['prescriptions', 'quick_visit', 'medicines']]);
        $this->assertTrue($this->doctor->fresh()->canAccessModule('medicines'));

        $response = $this->actingAs($this->doctor)->post(route('prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Tonsillitis',
            'medications' => [
                [
                    'name' => 'Cefuroxime',
                    'strength' => '500mg',
                    'dosage' => '1 tablet',
                    'frequency' => 'Twice daily',
                    'duration' => '7 days',
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, Medicine::count());
        $this->assertDatabaseHas('medicines', ['name' => 'Cefuroxime']);
        $this->assertDatabaseHas('prescription_medicines', [
            'medicine_name' => 'Cefuroxime',
        ]);
    }

    public function test_dispense_route_returns_403_when_pharmacy_inventory_module_disabled(): void
    {
        $prescription = SimplePrescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'prescription_number' => 'RX-DISPENSE-TEST-1',
            'diagnosis' => 'Sinusitis',
            'prescribed_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertFalse($this->pharmacist->canAccessModule('medicines'));

        $response = $this->actingAs($this->pharmacist)->post(
            route('simple-prescriptions.dispense', $prescription->id),
            ['payment_method' => 'cash']
        );

        $response->assertStatus(403);

        $prescription->refresh();
        $this->assertFalse((bool) $prescription->is_dispensed);
    }

    public function test_dispense_route_succeeds_when_pharmacy_inventory_module_enabled(): void
    {
        // Sanity check / regression guard: the 403 above is specifically due
        // to the module being disabled, not some unrelated breakage. Once
        // Pharmacy/Inventory is enabled, the pharmacist should get past the
        // module gate (and then hit the normal "no medicines on this
        // prescription to dispense" business flow instead of a 403).
        $this->clinic->update(['enabled_modules' => ['prescriptions', 'quick_visit', 'medicines']]);

        $prescription = SimplePrescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'prescription_number' => 'RX-DISPENSE-TEST-2',
            'diagnosis' => 'Sinusitis',
            'prescribed_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        $this->assertTrue($this->pharmacist->fresh()->canAccessModule('medicines'));

        $response = $this->actingAs($this->pharmacist)->post(
            route('simple-prescriptions.dispense', $prescription->id),
            ['payment_method' => 'cash']
        );

        // No prescriptions to dispense on this (empty) prescription, so it
        // redirects back with a business-rule error - critically, NOT the
        // 403 module-gate abort from the previous test.
        $response->assertStatus(302);
        if ($response->getSession()->has('error')) {
            $this->assertStringNotContainsString(
                'Pharmacy/Inventory module is not enabled',
                $response->getSession()->get('error')
            );
        }
    }
}
