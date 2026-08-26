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

class QuickVisitPrescriptionTest extends TestCase
{
    private Clinic $clinic;
    private User $doctor;
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
            $table->unsignedBigInteger('clinic_id');
            $table->string('prescription_number')->unique();
            $table->text('diagnosis')->nullable();
            $table->string('visit_type', 30)->nullable();
            $table->text('notes')->nullable();
            $table->date('prescribed_date');
            $table->string('status')->default('active');
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

        $this->clinic = Clinic::create([
            'name' => 'Quick Visit Test Clinic',
            'tenant_id' => 'TEN-QUICKVISIT',
            'enabled_modules' => ['prescriptions', 'quick_visit'],
        ]);

        $this->doctor = User::create([
            'username' => 'quickvisit_doctor',
            'email' => 'quickvisit-doctor@example.test',
            'password' => 'secret',
            'first_name' => 'Quick',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => 'P-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'gender' => 'female',
            'phone' => '07701234567',
        ]);
    }

    public function test_quick_visit_page_renders_when_module_enabled(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit'));

        $response->assertOk();
        $response->assertViewIs('simple-prescriptions.quick-visit');
        $response->assertSee('Jane Doe', false);
        foreach (SimplePrescription::VISIT_TYPES as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_quick_visit_page_is_blocked_when_module_disabled(): void
    {
        $this->clinic->update(['enabled_modules' => ['prescriptions']]);

        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit'));

        $response->assertStatus(403);
    }

    public function test_store_persists_visit_type_and_medicine_type_and_quantity(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Acute pharyngitis',
            'visit_type' => 'follow_up',
            'notes' => 'Patient improving',
            'medicines' => [
                [
                    'name' => 'new:Amoxicillin',
                    'type' => 'capsule',
                    'dosage' => '500mg',
                    'frequency' => 'BID',
                    'duration' => '7 days',
                    'quantity' => 14,
                ],
            ],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $this->assertSame('follow_up', $prescription->visit_type);

        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Amoxicillin',
            'type' => 'capsule',
            'quantity' => 14,
        ]);
    }

    public function test_store_with_print_after_redirects_to_print_route(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Common cold',
            'print_after' => '1',
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.print', $prescription->id));
    }

    public function test_store_without_visit_type_or_medicine_extras_still_works_like_before(): void
    {
        // Regression check: the existing (non-quick-visit) create form never
        // sends visit_type/type/quantity - store() must still work exactly
        // as before for that payload shape.
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Regular flow diagnosis',
            'medicines' => [
                [
                    'name' => 'new:Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'TID',
                    'duration' => '3 days',
                ],
            ],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $this->assertNull($prescription->visit_type);

        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Paracetamol',
            'type' => null,
            'quantity' => null,
        ]);
    }
}
