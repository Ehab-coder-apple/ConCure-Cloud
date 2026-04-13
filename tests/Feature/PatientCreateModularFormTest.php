<?php

namespace Tests\Feature;

use App\Http\Controllers\PatientController;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientDental;
use App\Models\PatientEnt;
use App\Models\PatientMedicalOverview;
use App\Models\PatientNutrition;
use App\Models\PatientPediatric;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PatientCreateModularFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        View::share('primaryColor', null);
        View::share('companyName', 'Test Clinic');

        foreach ([
            'patient_nutrition',
            'patient_pediatric',
            'patient_ent',
            'patient_dental',
            'patient_modules',
            'patient_medical_overviews',
            'patients',
            'settings',
            'users',
            'clinics',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->json('settings')->nullable();
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
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('job')->nullable();
            $table->string('education')->nullable();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->string('blood_type')->nullable();
            $table->integer('birth_weight')->nullable();
            $table->integer('gestational_age_weeks')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->text('notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('patient_medical_overviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique();
            $table->text('allergies')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->text('surgeries')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('current_medications_summary')->nullable();
            $table->json('flags')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('module_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('patient_dental', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique();
            $table->string('oral_hygiene')->nullable();
            $table->string('smoking_status')->nullable();
            $table->boolean('bruxism')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_ent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_pediatric', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique();
            $table->decimal('birth_weight', 8, 2)->nullable();
            $table->unsignedInteger('gestational_age')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_nutrition', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->unique();
            $table->decimal('height', 6, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->timestamps();
        });
    }

    public function test_index_page_renders_modular_add_patient_modal(): void
    {
        $user = $this->createActivatedAdmin();
        $this->actingAs($user);

        $view = app(PatientController::class)->index(Request::create(route('patients.index'), 'GET'));
        $moduleKeys = collect($view->getData()['moduleDefinitions'] ?? [])->pluck('key')->all();
        $medicalFlags = array_keys($view->getData()['medicalFlags'] ?? []);

        $this->assertSame('patients.index', $view->name());
        $this->assertContains('nutrition', $moduleKeys);
        $this->assertContains('dental', $moduleKeys);
        $this->assertContains('pregnant', $medicalFlags);
        $this->assertArrayHasKey('patients', $view->getData());
    }

    public function test_patient_create_and_index_views_include_voice_typing_support(): void
    {
        $user = $this->createActivatedAdmin();
        $this->actingAs($user);

        $createView = app(PatientController::class)->create();
        $createBlade = file_get_contents(resource_path('views/patients/create.blade.php'));
        $this->assertSame('patients.create', $createView->name());
        $this->assertStringContainsString('data-auto-voice-scope="patient-create-form"', $createBlade);
        $this->assertStringContainsString("@include('partials.voice-input')", $createBlade);

        $indexView = app(PatientController::class)->index(Request::create(route('patients.index'), 'GET'));
        $indexBlade = file_get_contents(resource_path('views/patients/index.blade.php'));
        $this->assertSame('patients.index', $indexView->name());
        $this->assertStringContainsString('data-auto-voice-scope="patient-create-modal"', $indexBlade);
        $this->assertStringContainsString("@include('partials.voice-input')", $indexBlade);
    }

    public function test_create_page_renders_modular_tabs_and_module_picker(): void
    {
        $user = $this->createActivatedAdmin();

        $this->actingAs($user);
        $view = app(PatientController::class)->create();
        $moduleKeys = collect($view->getData()['moduleDefinitions'] ?? [])->pluck('key')->all();
        $medicalFlags = array_keys($view->getData()['medicalFlags'] ?? []);

        $this->assertSame('patients.create', $view->name());
        $this->assertContains('nutrition', $moduleKeys);
        $this->assertContains('dental', $moduleKeys);
        $this->assertContains('ent', $moduleKeys);
        $this->assertContains('pediatric', $moduleKeys);
        $this->assertContains('pregnant', $medicalFlags);
        $this->assertContains('diabetic', $medicalFlags);
        $this->assertContains('hypertensive', $medicalFlags);
    }

    public function test_patient_forms_only_show_modules_enabled_for_the_users_clinic(): void
    {
        $user = $this->createActivatedAdmin(['dental']);

        $this->actingAs($user);

        $createView = app(PatientController::class)->create();
        $indexView = app(PatientController::class)->index(Request::create(route('patients.index'), 'GET'));

        $this->assertSame(['dental'], collect($createView->getData()['moduleDefinitions'] ?? [])->pluck('key')->all());
        $this->assertSame(['dental'], collect($indexView->getData()['moduleDefinitions'] ?? [])->pluck('key')->all());
    }

    public function test_store_saves_selected_modules_and_minimal_profile_data(): void
    {
        $user = $this->createActivatedAdmin();

        $response = $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Lina',
            'last_name' => 'Kareem',
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
            'gender' => 'female',
            'phone' => '555-1000',
            'email' => 'lina@example.test',
            'blood_type' => 'A+',
            'address' => 'North Street',
            'emergency_contact_name' => 'Huda Kareem',
            'emergency_contact_phone' => '555-2000',
            'allergies' => 'Peanuts',
            'chronic_illnesses' => 'Asthma',
            'current_medications_summary' => 'Inhaler as needed',
            'surgeries_history' => 'Appendectomy',
            'medical_history' => 'Shared intake note',
            '_supports_extended_medical_flags' => '1',
            'medical_flags' => [
                'diabetic' => '1',
                'hypertensive' => '1',
            ],
            'selected_modules' => ['dental', 'pediatric', 'nutrition', 'ent'],
            'dental_oral_hygiene' => 'good',
            'dental_smoking_status' => 'never',
            'pediatric_birth_weight' => '2800',
            'pediatric_gestational_age_weeks' => '38',
            'nutrition_height' => '156',
            'nutrition_weight' => '49',
            'ent_notes' => 'Seasonal rhinitis symptoms.',
        ]);

        $patient = Patient::query()->where('email', 'lina@example.test')->first();

        $this->assertNotNull($patient);
        $response->assertRedirect(route('patients.show', $patient));
        $this->assertSame(156.0, (float) $patient->height);
        $this->assertSame(49.0, (float) $patient->weight);
        $this->assertSame(2800, (int) $patient->birth_weight);
        $this->assertSame(38, (int) $patient->gestational_age_weeks);

        $overview = PatientMedicalOverview::query()->where('patient_id', $patient->id)->first();
        $this->assertNotNull($overview);
        $this->assertSame('Peanuts', $overview->allergies);
        $this->assertTrue($overview->flags['diabetic']);
        $this->assertTrue($overview->flags['hypertensive']);

        $moduleNames = DB::table('patient_modules')
            ->where('patient_id', $patient->id)
            ->orderBy('module_name')
            ->pluck('module_name')
            ->all();

        $this->assertSame(['dental', 'ent', 'nutrition', 'pediatric'], $moduleNames);

        $dental = PatientDental::query()->where('patient_id', $patient->id)->first();
        $pediatric = PatientPediatric::query()->where('patient_id', $patient->id)->first();
        $nutrition = PatientNutrition::query()->where('patient_id', $patient->id)->first();
        $ent = PatientEnt::query()->where('patient_id', $patient->id)->first();

        $this->assertSame('good', $dental?->oral_hygiene);
        $this->assertSame('never', $dental?->smoking_status);
        $this->assertSame(2800.0, (float) $pediatric?->birth_weight);
        $this->assertSame(38, (int) $pediatric?->gestational_age);
        $this->assertSame(156.0, (float) $nutrition?->height);
        $this->assertSame(49.0, (float) $nutrition?->weight);
        $this->assertSame('Seasonal rhinitis symptoms.', $ent?->notes);
    }

    public function test_store_ignores_modules_not_enabled_for_the_patient_clinic(): void
    {
        $user = $this->createActivatedAdmin(['dental']);

        $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Mina',
            'last_name' => 'Saad',
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
            'gender' => 'male',
            'phone' => '555-7000',
            'email' => 'mina@example.test',
            'selected_modules' => ['dental', 'nutrition', 'ent'],
            'dental_oral_hygiene' => 'good',
            'nutrition_height' => '145',
            'nutrition_weight' => '40',
            'ent_notes' => 'Should be ignored',
        ]);

        $patient = Patient::query()->where('email', 'mina@example.test')->firstOrFail();

        $this->assertSame(
            ['dental'],
            DB::table('patient_modules')->where('patient_id', $patient->id)->orderBy('module_name')->pluck('module_name')->all()
        );
        $this->assertNotNull(PatientDental::query()->where('patient_id', $patient->id)->first());
        $this->assertNull(PatientNutrition::query()->where('patient_id', $patient->id)->first());
        $this->assertNull(PatientEnt::query()->where('patient_id', $patient->id)->first());
    }

    public function test_edit_view_preloads_selected_module_keys_for_existing_patient(): void
    {
        $user = $this->createActivatedAdmin();
        $patient = $this->createPatientFor($user, [
            'date_of_birth' => Carbon::now()->subYears(10)->toDateString(),
            'height' => 140,
            'weight' => 35,
        ]);

        PatientDental::query()->create([
            'patient_id' => $patient->id,
            'oral_hygiene' => 'fair',
        ]);
        PatientNutrition::query()->create([
            'patient_id' => $patient->id,
            'height' => 140,
            'weight' => 35,
        ]);

        $this->actingAs($user);
        $view = app(PatientController::class)->edit($patient);

        $this->assertSame('patients.edit', $view->name());
        $this->assertSame(['dental', 'nutrition'], $view->getData()['selectedModuleKeys']);
    }

    public function test_edit_view_renders_for_legacy_patient_with_invalid_dob_and_empty_gender(): void
    {
        $user = $this->createActivatedAdmin();

        DB::table('patients')->insert([
            'patient_id' => 'LEGACY-' . uniqid(),
            'first_name' => 'Legacy',
            'last_name' => 'Import',
            'date_of_birth' => '0000-00-00',
            'gender' => '',
            'phone' => '555-9090',
            'email' => 'legacy_' . uniqid() . '@example.test',
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $patient = Patient::query()->where('first_name', 'Legacy')->where('last_name', 'Import')->firstOrFail();

        $this->actingAs($user);
        $this->withViewErrors([]);

        $view = app(PatientController::class)->edit($patient);
        $html = $view->render();

        $this->assertSame('patients.edit', $view->name());
        $this->assertStringContainsString('name="date_of_birth" value=""', $html);
        $this->assertStringContainsString('name="gender"', $html);
    }

    public function test_update_syncs_selected_modules_and_deactivates_removed_ones(): void
    {
        $user = $this->createActivatedAdmin();
        $user->update(['role' => 'super_admin']);
        $patient = $this->createPatientFor($user, [
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
            'height' => 150,
            'weight' => 44,
        ]);

        DB::table('patient_modules')->insert([
            ['patient_id' => $patient->id, 'module_name' => 'dental', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['patient_id' => $patient->id, 'module_name' => 'nutrition', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        PatientMedicalOverview::query()->create([
            'patient_id' => $patient->id,
            'flags' => ['pregnant' => false, 'diabetic' => true, 'hypertensive' => false],
        ]);
        PatientDental::query()->create([
            'patient_id' => $patient->id,
            'oral_hygiene' => 'fair',
            'smoking_status' => 'former',
        ]);
        PatientNutrition::query()->create([
            'patient_id' => $patient->id,
            'height' => 150,
            'weight' => 44,
        ]);

        $request = Request::create(route('patients.update', $patient), 'PUT', [
            'first_name' => 'Updated',
            'last_name' => 'Patient',
            'date_of_birth' => Carbon::now()->subYears(12)->toDateString(),
            'gender' => 'female',
            'phone' => '555-9000',
            'email' => 'updated@example.test',
            'blood_type' => 'B+',
            'selected_modules' => ['dental', 'ent'],
            '_supports_extended_medical_flags' => '1',
            'medical_flags' => [
                'hypertensive' => '1',
            ],
            'dental_oral_hygiene' => 'good',
            'dental_smoking_status' => 'never',
            'ent_notes' => 'ENT follow-up note.',
            'is_active' => '1',
        ]);

        $this->actingAs($user);
        $request->setUserResolver(fn () => $user);
        $response = app(PatientController::class)->update($request, $patient);

        $this->assertSame(route('patients.show', $patient), $response->getTargetUrl());

        $patient->refresh();
        $overview = PatientMedicalOverview::query()->where('patient_id', $patient->id)->first();

        $this->assertSame('Updated', $patient->first_name);
        $this->assertSame(150.0, (float) $patient->height);
        $this->assertSame(44.0, (float) $patient->weight);
        $this->assertFalse((bool) $patient->is_pregnant);
        $this->assertFalse((bool) data_get($overview->flags, 'diabetic', false));
        $this->assertTrue((bool) data_get($overview->flags, 'hypertensive', false));

        $moduleStates = DB::table('patient_modules')
            ->where('patient_id', $patient->id)
            ->orderBy('module_name')
            ->get(['module_name', 'is_active'])
            ->mapWithKeys(fn ($row) => [$row->module_name => (bool) $row->is_active])
            ->all();

        $this->assertSame([
            'dental' => true,
            'ent' => true,
            'nutrition' => false,
        ], $moduleStates);

        $this->assertSame('good', PatientDental::query()->where('patient_id', $patient->id)->value('oral_hygiene'));
        $this->assertSame('never', PatientDental::query()->where('patient_id', $patient->id)->value('smoking_status'));
        $this->assertSame('ENT follow-up note.', PatientEnt::query()->where('patient_id', $patient->id)->value('notes'));
        $this->assertSame(150.0, (float) PatientNutrition::query()->where('patient_id', $patient->id)->value('height'));
    }

    private function createPatientFor(User $user, array $overrides = []): Patient
    {
        return Patient::query()->create(array_merge([
            'first_name' => 'Sample',
            'last_name' => 'Patient',
            'date_of_birth' => Carbon::now()->subYears(20)->toDateString(),
            'gender' => 'female',
            'phone' => '555-0000',
            'email' => 'patient_' . uniqid() . '@example.test',
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
        ], $overrides));
    }

    private function createActivatedAdmin(?array $enabledModules = null): User
    {
        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'is_active' => true,
            'activated_at' => now(),
            'enabled_modules' => $enabledModules,
        ]);

        return User::create([
            'username' => 'admin_' . uniqid(),
            'email' => 'admin_' . uniqid() . '@example.test',
            'password' => 'secret',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }
}