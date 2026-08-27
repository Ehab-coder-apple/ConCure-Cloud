<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\LabRequest;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

/**
 * Verifies the "New Lab Request" form shows a checkbox checklist of tests
 * grouped by category (Blood, Urine & Stool, Hormones, Imaging, Genetic &
 * Specialty, Biopsy & Pap Smear), and that clinics can add their own
 * tests/categories which persist for future requests.
 */
class LabRequestTestChecklistTest extends TestCase
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

        foreach (['lab_request_tests', 'lab_requests', 'lab_tests', 'external_labs', 'settings', 'patients', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('external_labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('name');
            $table->string('lab_type')->default('medical');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('normal_range_min', 10, 2)->nullable();
            $table->decimal('normal_range_max', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_frequent')->default(false);
            $table->unsignedBigInteger('clinic_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->text('clinical_notes')->nullable();
            $table->date('requested_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->string('lab_name')->nullable();
            $table->string('lab_phone')->nullable();
            $table->string('lab_whatsapp')->nullable();
            $table->string('lab_email')->nullable();
            $table->string('communication_method')->nullable();
            $table->text('communication_notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('result_file_path')->nullable();
            $table->timestamp('result_received_at')->nullable();
            $table->unsignedBigInteger('result_received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('lab_request_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_request_id');
            $table->unsignedBigInteger('lab_test_id')->nullable();
            $table->string('test_name');
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        $this->clinic = Clinic::create([
            'name' => 'Lab Checklist Test Clinic',
            'tenant_id' => 'TEN-LABCHECK',
            'enabled_modules' => ['lab'],
        ]);

        $this->doctor = User::create([
            'username' => 'lab_checklist_doctor',
            'email' => 'lab-checklist-doctor@example.test',
            'password' => 'secret',
            'first_name' => 'Lab',
            'last_name' => 'Doctor',
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => 'P-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);
    }

    public function test_lab_requests_page_shows_builtin_checklist_grouped_by_category(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('recommendations.lab-requests'));

        $response->assertOk();
        $response->assertSee('Blood Tests', false);
        $response->assertSee('Urine &amp; Stool Tests', false);
        $response->assertSee('Hormones Tests', false);
        $response->assertSee('Imaging Tests', false);
        $response->assertSee('Genetic &amp; Specialty', false);
        $response->assertSee('Biopsy &amp; Pap Smear', false);

        $response->assertSee('Complete Blood Count (CBC)', false);
        $response->assertSee('Thyroid Panel (TSH, Free T3, Free T4)', false);
        $response->assertSee('Pap Smear (Cervical Screening)', false);

        // Checkboxes, not free-text-only rows.
        $response->assertSee('lr-test-checkbox', false);
        $response->assertSee('data-category-key="blood"', false);
        $response->assertSee('lr-add-category-btn', false);
    }

    public function test_quick_add_test_to_existing_category_persists_for_the_clinic(): void
    {
        $response = $this->actingAs($this->doctor)->postJson(
            route('recommendations.lab-requests.quick-add-test'),
            ['name' => 'D-Dimer', 'category_key' => 'blood']
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('test.name', 'D-Dimer');
        $response->assertJsonPath('test.category_key', 'blood');

        $this->assertDatabaseHas('lab_tests', [
            'name' => 'D-Dimer',
            'category' => 'Blood Tests',
            'clinic_id' => $this->clinic->id,
        ]);

        // Reappears in the checklist on next page load.
        $page = $this->actingAs($this->doctor)->get(route('recommendations.lab-requests'));
        $page->assertSee('D-Dimer', false);
    }

    public function test_quick_add_new_category_creates_its_own_group(): void
    {
        $response = $this->actingAs($this->doctor)->postJson(
            route('recommendations.lab-requests.quick-add-test'),
            ['name' => 'Troponin I', 'new_category_name' => 'Cardiac Markers']
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('test.category_label', 'Cardiac Markers');
        $response->assertJsonPath('test.category_key', 'cardiac_markers');

        $this->assertDatabaseHas('lab_tests', [
            'name' => 'Troponin I',
            'category' => 'Cardiac Markers',
            'clinic_id' => $this->clinic->id,
        ]);

        $page = $this->actingAs($this->doctor)->get(route('recommendations.lab-requests'));
        $page->assertSee('Cardiac Markers', false);
        $page->assertSee('Troponin I', false);
    }

    public function test_quick_add_test_is_tenant_isolated(): void
    {
        $otherClinic = Clinic::create([
            'name' => 'Other Clinic',
            'tenant_id' => 'TEN-OTHER',
            'enabled_modules' => ['lab'],
        ]);

        LabTest::create([
            'name' => 'Rare Custom Test',
            'category' => 'Custom',
            'clinic_id' => $otherClinic->id,
            'is_active' => true,
        ]);

        $page = $this->actingAs($this->doctor)->get(route('recommendations.lab-requests'));
        $page->assertDontSee('Rare Custom Test', false);
    }

    public function test_store_lab_request_with_checklist_selections_persists_tests(): void
    {
        $customTest = LabTest::create([
            'name' => 'Vitamin D',
            'category' => 'Blood Tests',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->doctor)->post(route('recommendations.lab-requests.store'), [
            'patient_id' => $this->patient->id,
            'priority' => 'normal',
            'communication_method' => 'whatsapp',
            'tests' => [
                ['test_name' => 'Complete Blood Count (CBC)'],
                ['test_name' => 'Vitamin D', 'lab_test_id' => $customTest->id],
            ],
        ]);

        $labRequest = LabRequest::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect();
        $this->assertDatabaseHas('lab_request_tests', [
            'lab_request_id' => $labRequest->id,
            'test_name' => 'Complete Blood Count (CBC)',
            'lab_test_id' => null,
        ]);
        $this->assertDatabaseHas('lab_request_tests', [
            'lab_request_id' => $labRequest->id,
            'test_name' => 'Vitamin D',
            'lab_test_id' => $customTest->id,
        ]);
    }
}
