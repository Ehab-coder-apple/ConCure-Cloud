<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\AestheticAftercareTemplate;
use App\Models\AestheticSession;
use App\Models\Clinic;
use App\Models\NotificationLog;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class AestheticConsentAftercareWorkflowTest extends TestCase
{
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

        Storage::fake('public');
        Config::set('filesystems.disks.spaces-private', [
            'driver' => 's3',
            'key' => null,
            'secret' => null,
            'endpoint' => null,
            'bucket' => null,
        ]);

        View::share([
            'appName' => config('app.name'),
            'companyName' => config('concure.company_name', 'ConCure'),
            'primaryColor' => config('concure.primary_color', '#008080'),
            'supportedLanguages' => config('concure.supported_languages', [
                'en' => 'English',
                'ar' => 'العربية',
            ]),
        ]);

        foreach ([
            'aesthetic_aftercare_issues',
            'aesthetic_aftercare_templates',
            'consent_forms',
            'notification_logs',
            'patient_files',
            'patient_modules',
            'patient_medical_overviews',
            'patient_aesthetic',
            'session_inventory_usage',
            'session_images',
            'aesthetic_package_treatment',
            'aesthetic_session_treatment',
            'aesthetic_sessions',
            'aesthetic_inventory',
            'aesthetic_packages',
            'aesthetic_treatments',
            'patient_packages',
            'patients',
            'users',
            'clinics',
        ] as $table) {
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
            $table->string('whatsapp_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('job')->nullable();
            $table->string('education')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->string('blood_type')->nullable();
            $table->integer('birth_weight')->nullable();
            $table->integer('gestational_age_weeks')->nullable();
            $table->text('notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('patient_medical_overviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
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

        Schema::create('patient_aesthetic', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('skin_type')->nullable();
            $table->json('skin_concerns')->nullable();
            $table->text('allergies')->nullable();
            $table->text('previous_treatments')->nullable();
            $table->text('current_skincare_routine')->nullable();
            $table->text('desired_outcomes')->nullable();
            $table->string('sun_exposure')->nullable();
            $table->boolean('is_pregnant_or_breastfeeding')->default(false);
            $table->boolean('photosensitivity')->default(false);
            $table->text('medical_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedInteger('sessions_used')->default(0);
            $table->unsignedInteger('sessions_remaining')->default(0);
            $table->date('purchase_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedInteger('total_sessions')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_package_treatment', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('treatment_id');
        });

        Schema::create('aesthetic_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('default_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('product_name');
            $table->string('type')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('patient_package_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->unsignedInteger('session_number')->default(1);
            $table->date('session_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_session_treatment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('treatment_id');
            $table->timestamps();
        });

        Schema::create('session_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('tenant_id')->nullable();
            $table->string('type')->nullable();
            $table->string('image_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('session_inventory_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_used')->default(1);
            $table->timestamps();
        });

        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('category')->default('medical_report');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('consent_forms', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedBigInteger('patient_file_id')->nullable();
            $table->string('title');
            $table->longText('body');
            $table->longText('signature_data');
            $table->timestamp('signed_at');
            $table->string('signer_name')->nullable();
            $table->string('pdf_file_name')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('pdf_file_size')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('type');
            $table->string('channel')->nullable();
            $table->string('recipient')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->string('external_id')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aesthetic_aftercare_templates', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
            $table->string('category', 100);
            $table->string('title');
            $table->longText('instructions');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_aftercare_issues', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedBigInteger('aftercare_template_id')->nullable();
            $table->unsignedBigInteger('patient_file_id')->nullable();
            $table->string('template_name');
            $table->string('template_category', 100)->nullable();
            $table->string('title');
            $table->longText('instructions_snapshot');
            $table->text('notes')->nullable();
            $table->timestamp('issued_at');
            $table->string('pdf_file_name')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedBigInteger('pdf_file_size')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_consent_is_required_before_session_can_be_completed_and_pdf_is_archived(): void
    {
        [$user, $patient, $session] = $this->makeDirectSessionContext();

        $response = $this->actingAs($user)->put(route('aesthetic.sessions.update', $session), [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
            'treatment_id' => null,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'status' => 'completed',
            'notes' => 'Attempt to close before consent',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('scheduled', $session->fresh()->status);

        $consentResponse = $this->actingAs($user)->post(route('aesthetic.sessions.consent.store', $session), [
            'title' => 'Laser Consent',
            'body' => 'I understand the treatment risks and agree to proceed.',
            'signer_name' => 'Sara Patient',
            'signature_data' => $this->fakeSignatureDataUrl(),
        ]);

        $consentResponse->assertRedirect(route('aesthetic.sessions.show', $session));

        $patientFile = PatientFile::first();
        $this->assertNotNull($patientFile);
        Storage::disk('public')->assertExists($patientFile->file_path);
        $this->assertDatabaseHas('consent_forms', [
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'patient_file_id' => $patientFile->id,
        ]);

        $completionResponse = $this->actingAs($user)->put(route('aesthetic.sessions.update', $session), [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
            'treatment_id' => null,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'status' => 'completed',
            'notes' => 'Completed after consent',
        ]);

        $completionResponse->assertRedirect(route('aesthetic.sessions.show', $session));
        $this->assertSame('completed', $session->fresh()->status);
    }

    public function test_aftercare_issue_creates_pdf_and_patient_file_entry(): void
    {
        [$user, $patient, $session] = $this->makeDirectSessionContext();

        $template = AestheticAftercareTemplate::create([
            'tenant_id' => 'TEN-1',
            'name' => 'Laser Cooling Instructions',
            'category' => 'laser_treatments',
            'title' => 'Laser Aftercare Instructions',
            'instructions' => 'Avoid heat, sun exposure, and active ingredients for 48 hours.',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('aesthetic.sessions.aftercare.store', $session), [
            'aftercare_template_id' => $template->id,
            'notes' => 'Use cool compress if needed.',
        ]);

        $response->assertRedirect(route('aesthetic.sessions.show', $session));

        $patientFile = PatientFile::first();
        $this->assertNotNull($patientFile);
        Storage::disk('public')->assertExists($patientFile->file_path);
        $this->assertDatabaseHas('aesthetic_aftercare_issues', [
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'aftercare_template_id' => $template->id,
            'patient_file_id' => $patientFile->id,
        ]);
    }

    public function test_session_aftercare_tab_shows_whatsapp_button_for_issued_aftercare(): void
    {
        [$user, $patient, $session] = $this->makeDirectSessionContext();

        $template = AestheticAftercareTemplate::create([
            'tenant_id' => 'TEN-1',
            'name' => 'Laser Cooling Instructions',
            'category' => 'laser_treatments',
            'title' => 'Laser Aftercare Instructions',
            'instructions' => 'Avoid heat, sun exposure, and active ingredients for 48 hours.',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $patient->update(['whatsapp_phone' => '07501112233']);

        $this->actingAs($user)->post(route('aesthetic.sessions.aftercare.store', $session), [
            'aftercare_template_id' => $template->id,
            'notes' => 'Use cool compress if needed.',
        ])->assertRedirect(route('aesthetic.sessions.show', $session));

        $issue = \App\Models\AestheticAftercareIssue::first();

        $response = $this->actingAs($user)->get(route('aesthetic.sessions.show', $session));

        $response->assertOk();
        $response->assertSee('WhatsApp');
        $response->assertSee(route('aesthetic.sessions.aftercare.send-whatsapp', [$session, $issue]), false);
    }

    public function test_aftercare_issue_can_send_whatsapp_reminder_and_log_it(): void
    {
        [$user, $patient, $session] = $this->makeDirectSessionContext();

        $template = AestheticAftercareTemplate::create([
            'tenant_id' => 'TEN-1',
            'name' => 'Laser Cooling Instructions',
            'category' => 'laser_treatments',
            'title' => 'Laser Aftercare Instructions',
            'instructions' => 'Avoid heat, sun exposure, and active ingredients for 48 hours.',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $patient->update(['whatsapp_phone' => '07501112233']);

        $this->actingAs($user)->post(route('aesthetic.sessions.aftercare.store', $session), [
            'aftercare_template_id' => $template->id,
            'notes' => 'Use cool compress if needed.',
        ])->assertRedirect(route('aesthetic.sessions.show', $session));

        $issue = \App\Models\AestheticAftercareIssue::first();

        $mock = \Mockery::mock(WhatsAppService::class);
        $mock->shouldReceive('setClinicContext')->once()->with($user->clinic_id)->andReturnSelf();
        $mock->shouldReceive('sendDocument')->once()->withArgs(function ($phone, $filePath, $fileName, $message) use ($patient) {
            return $phone === '07501112233'
                && is_string($filePath)
                && file_exists($filePath)
                && str_ends_with($fileName, '.pdf')
                && str_contains($message, $patient->full_name)
                && str_contains($message, 'Laser Aftercare Instructions');
        })->andReturn([
            'success' => true,
            'status' => 'sent',
            'message_id' => 'aftercare-wa-123',
        ]);
        $this->app->instance(WhatsAppService::class, $mock);

        $response = $this->actingAs($user)->postJson(route('aesthetic.sessions.aftercare.send-whatsapp', [$session, $issue]));

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Aftercare reminder sent successfully.',
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'clinic_id' => $user->clinic_id,
            'patient_id' => $patient->id,
            'type' => NotificationLog::TYPE_FOLLOW_UP,
            'channel' => 'whatsapp',
            'status' => NotificationLog::STATUS_SENT,
            'notifiable_type' => \App\Models\AestheticAftercareIssue::class,
            'notifiable_id' => $issue->id,
        ]);
    }

    public function test_session_can_redirect_directly_to_invoice_creation_after_save(): void
    {
        [$user, $patient] = $this->makeDirectSessionContext(true);

        $response = $this->actingAs($user)->post(route('aesthetic.sessions.store'), [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
            'treatment_id' => \App\Models\AestheticTreatment::first()->id,
            'session_number' => 2,
            'session_date' => now()->toDateString(),
            'status' => 'scheduled',
            'notes' => 'Create invoice immediately',
            'next_action' => 'create_invoice',
        ]);

        $session = AestheticSession::latest('id')->first();

        $response->assertRedirect(route('aesthetic.invoices.create', ['session_id' => $session->id]));
        $this->assertSame('Create invoice immediately', $session->notes);
    }

    public function test_direct_session_can_store_assigned_person_and_show_it(): void
    {
        [$user, $patient] = $this->makeDirectSessionContext();

        $assignee = User::create([
            'username' => 'aesthetic_session_runner',
            'email' => 'aesthetic-session-runner@example.test',
            'password' => 'secret',
            'first_name' => 'Session',
            'last_name' => 'Runner',
            'role' => 'doctor',
            'clinic_id' => $user->clinic_id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('aesthetic.sessions.store'), [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
            'session_number' => 2,
            'session_date' => now()->toDateString(),
            'assigned_user_id' => $assignee->id,
            'status' => 'scheduled',
            'notes' => 'Assigned session',
        ]);

        $session = AestheticSession::latest('id')->first();

        $response->assertRedirect(route('aesthetic.sessions.show', $session));
        $this->assertSame($assignee->id, $session->assigned_user_id);

        $showPage = $this->actingAs($user)->get(route('aesthetic.sessions.show', $session));
        $showPage->assertOk();
        $showPage->assertSee('Assigned Person');
        $showPage->assertSee($assignee->full_name);
    }

    public function test_package_session_can_update_assigned_person(): void
    {
        [$user, $patient, $patientPackage, $session] = $this->makePackageSessionContext();

        $assignee = User::create([
            'username' => 'aesthetic_package_runner',
            'email' => 'aesthetic-package-runner@example.test',
            'password' => 'secret',
            'first_name' => 'Package',
            'last_name' => 'Runner',
            'role' => 'doctor',
            'clinic_id' => $user->clinic_id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $response = $this->actingAs($user)->put(route('aesthetic.sessions.update', $session), [
            'session_mode' => 'package',
            'patient_package_id' => $patientPackage->id,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'assigned_user_id' => $assignee->id,
            'status' => 'scheduled',
            'notes' => 'Assigned package session',
        ]);

        $response->assertRedirect(route('aesthetic.sessions.show', $session));
        $session->refresh();

        $this->assertSame($assignee->id, $session->assigned_user_id);

        $indexPage = $this->actingAs($user)->get(route('aesthetic.sessions.index'));
        $indexPage->assertOk();
        $indexPage->assertSee('Assigned');
        $indexPage->assertSee($assignee->full_name);
    }

    public function test_session_create_page_shows_create_new_patient_shortcut(): void
    {
        [$user] = $this->makeDirectSessionContext();

        $response = $this->actingAs($user)->get(route('aesthetic.sessions.create'));

        $response->assertOk();
        $response->assertSee('Create New Patient');
        $response->assertSee(route('patients.create', ['return_to' => route('aesthetic.sessions.create', ['session_mode' => 'package'], false)]), false);
        $response->assertSee(route('patients.create', ['return_to' => route('aesthetic.sessions.create', ['session_mode' => 'direct'], false)]), false);
    }

    public function test_patient_creation_can_return_to_aesthetic_session_form_with_selected_patient(): void
    {
        [$user] = $this->makeDirectSessionContext();

        $returnTo = route('aesthetic.sessions.create', ['session_mode' => 'direct'], false);

        $response = $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Return',
            'last_name' => 'Patient',
            'phone' => '5551112222',
            'return_to' => $returnTo,
            'selected_modules' => [],
        ]);

        $patient = Patient::where('first_name', 'Return')->where('last_name', 'Patient')->first();

        $response->assertRedirect(route('aesthetic.sessions.create', [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
        ]));

        $createPage = $this->actingAs($user)->get(route('aesthetic.sessions.create', [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
        ]));

        $createPage->assertOk();
        $createPage->assertSee('value="direct" checked', false);
        $createPage->assertSee('value="' . $patient->id . '"', false);
        $createPage->assertSee('selected', false);
    }

    public function test_patient_creation_can_return_to_package_session_form(): void
    {
        [$user] = $this->makeDirectSessionContext();

        $returnTo = route('aesthetic.sessions.create', ['session_mode' => 'package'], false);

        $response = $this->actingAs($user)->post(route('patients.store'), [
            'first_name' => 'Package',
            'last_name' => 'Patient',
            'phone' => '5553334444',
            'return_to' => $returnTo,
            'selected_modules' => [],
        ]);

        $patient = Patient::where('first_name', 'Package')->where('last_name', 'Patient')->first();

        $response->assertRedirect(route('aesthetic.sessions.create', [
            'session_mode' => 'package',
            'patient_id' => $patient->id,
        ]));

        $createPage = $this->actingAs($user)->get(route('aesthetic.sessions.create', [
            'session_mode' => 'package',
            'patient_id' => $patient->id,
        ]));

        $createPage->assertOk();
        $createPage->assertSee('value="package" checked', false);
    }

    public function test_package_session_show_displays_suggested_next_due_picker(): void
    {
        [$user, $patient, $patientPackage, $session] = $this->makePackageSessionContext();

        \App\Models\ConsentForm::create([
            'tenant_id' => 'TEN-1',
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'title' => 'Package Consent',
            'body' => 'Consent body',
            'signature_data' => $this->fakeSignatureDataUrl(),
            'signer_name' => $patient->full_name,
            'created_by' => $user->id,
            'signed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('aesthetic.sessions.show', $session));

        $response->assertOk();
        $response->assertSee('Next Due Reminder');
        $response->assertSee(now()->addDays(28)->format('Y-m-d'), false);
        $response->assertSee('Mark Completed & Save Reminder');
    }

    public function test_completing_package_session_can_save_and_surface_next_due_reminder(): void
    {
        [$user, $patient, $patientPackage, $session] = $this->makePackageSessionContext();

        \App\Models\ConsentForm::create([
            'tenant_id' => 'TEN-1',
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'title' => 'Package Consent',
            'body' => 'Consent body',
            'signature_data' => $this->fakeSignatureDataUrl(),
            'signer_name' => $patient->full_name,
            'created_by' => $user->id,
            'signed_at' => now(),
        ]);

        $nextDueDate = now()->addWeeks(5)->toDateString();

        $response = $this->actingAs($user)->put(route('aesthetic.sessions.update', $session), [
            'session_mode' => 'package',
            'patient_package_id' => $patientPackage->id,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'status' => 'completed',
            'notes' => 'Completed package session',
            'next_due_date' => $nextDueDate,
        ]);

        $response->assertRedirect(route('aesthetic.sessions.show', $session));
        $session->refresh();

        $this->assertSame('completed', $session->status);
        $this->assertSame($nextDueDate, $session->next_due_date?->toDateString());

        $formattedNextDueDate = $session->next_due_date?->format('M d, Y');

        $sessionsIndex = $this->actingAs($user)->get(route('aesthetic.sessions.index'));
        $sessionsIndex->assertOk();
        $sessionsIndex->assertSee('Package Follow-up Reminders');
        $sessionsIndex->assertSee($patient->full_name);
        $sessionsIndex->assertSee($formattedNextDueDate);

        $patientPage = $this->actingAs($user)->get(route('patients.aesthetic.show', $patient));
        $patientPage->assertOk();
        $patientPage->assertSee('Next Due Follow-ups');
        $patientPage->assertSee($formattedNextDueDate);
        $patientPage->assertSee('Glow Package');
    }

    public function test_sessions_index_shows_whatsapp_action_for_follow_up_reminders(): void
    {
        [$user, $patient, $patientPackage, $session] = $this->makePackageSessionContext();

        $patient->update(['whatsapp_phone' => '07501112233']);
        $session->update([
            'status' => 'completed',
            'next_due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('aesthetic.sessions.index'));

        $response->assertOk();
        $response->assertSee('WhatsApp');
        $response->assertSee(route('aesthetic.sessions.send-whatsapp-reminder', $session), false);
    }

    public function test_follow_up_reminder_can_send_whatsapp_and_log_the_attempt(): void
    {
        [$user, $patient, $patientPackage, $session] = $this->makePackageSessionContext();

        $patient->update(['whatsapp_phone' => '07501112233']);
        $session->update([
            'status' => 'completed',
            'next_due_date' => now()->addDays(5)->toDateString(),
        ]);

        $mock = \Mockery::mock(WhatsAppService::class);
        $mock->shouldReceive('setClinicContext')->once()->with($user->clinic_id)->andReturnSelf();
        $mock->shouldReceive('sendMessage')->once()->withArgs(function ($phone, $message) use ($patient) {
            return $phone === '07501112233'
                && str_contains($message, $patient->full_name)
                && str_contains($message, 'Glow Package');
        })->andReturn([
            'success' => true,
            'status' => 'sent',
            'message_id' => 'wa-123',
        ]);
        $this->app->instance(WhatsAppService::class, $mock);

        $response = $this->actingAs($user)
            ->postJson(route('aesthetic.sessions.send-whatsapp-reminder', $session));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Reminder sent successfully.',
            ]);

        $this->assertDatabaseHas('notification_logs', [
            'clinic_id' => $user->clinic_id,
            'patient_id' => $patient->id,
            'type' => NotificationLog::TYPE_FOLLOW_UP,
            'channel' => 'whatsapp',
            'status' => NotificationLog::STATUS_SENT,
            'notifiable_type' => AestheticSession::class,
            'notifiable_id' => $session->id,
        ]);
    }

    public function test_direct_session_requires_treatment_for_create_invoice_shortcut(): void
    {
        [$user, $patient] = $this->makeDirectSessionContext();

        $response = $this->actingAs($user)->from(route('aesthetic.sessions.create'))->post(route('aesthetic.sessions.store'), [
            'session_mode' => 'direct',
            'patient_id' => $patient->id,
            'session_number' => 2,
            'session_date' => now()->toDateString(),
            'status' => 'scheduled',
            'notes' => 'Missing treatment',
            'next_action' => 'create_invoice',
        ]);

        $response->assertRedirect(route('aesthetic.sessions.create'));
        $response->assertSessionHasErrors('treatment_ids');
    }

    public function test_full_local_flow_shows_consent_and_aftercare_documents_on_patient_page(): void
    {
        $this->withoutExceptionHandling();

        [$user, $patient, $session] = $this->makeDirectSessionContext(true);

        $templateResponse = $this->actingAs($user)->post(route('aesthetic.aftercare-templates.store'), [
            'name' => 'Laser Cooling Instructions',
            'title' => 'Laser Aftercare Instructions',
            'category' => 'laser_treatments',
            'instructions' => 'Avoid heat, sun exposure, and active ingredients for 48 hours.',
            'is_active' => 1,
        ]);

        $templateResponse->assertRedirect(route('aesthetic.aftercare-templates.index'));
        $template = AestheticAftercareTemplate::first();
        $this->assertNotNull($template);

        $sessionPage = $this->actingAs($user)->get(route('aesthetic.sessions.show', $session));
        $sessionPage->assertOk();
        $sessionPage->assertSee('Consent & Aftercare');
        $sessionPage->assertSee('Consent Pending');
        $sessionPage->assertSee('Issue Aftercare PDF');
        $sessionPage->assertSee(route('aesthetic.invoices.create', ['session_id' => $session->id]), false);
        $sessionPage->assertSee('Create Invoice');

        $consentResponse = $this->actingAs($user)->post(route('aesthetic.sessions.consent.store', $session), [
            'title' => 'Laser Consent',
            'body' => 'I understand the treatment risks and agree to proceed.',
            'signer_name' => 'Sara Patient',
            'treatment_id' => $session->treatment_id,
            'signature_data' => $this->fakeSignatureDataUrl(),
        ]);
        $consentResponse->assertRedirect(route('aesthetic.sessions.show', $session));

        $aftercareResponse = $this->actingAs($user)->post(route('aesthetic.sessions.aftercare.store', $session), [
            'aftercare_template_id' => $template->id,
            'treatment_id' => $session->treatment_id,
            'notes' => 'Use cool compress if needed.',
        ]);
        $aftercareResponse->assertRedirect(route('aesthetic.sessions.show', $session));

        $this->assertSame(2, PatientFile::count());
        $this->assertDatabaseHas('consent_forms', [
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'title' => 'Laser Consent',
        ]);
        $this->assertDatabaseHas('aesthetic_aftercare_issues', [
            'patient_id' => $patient->id,
            'session_id' => $session->id,
            'template_name' => 'Laser Cooling Instructions',
        ]);

        foreach (PatientFile::pluck('file_path') as $filePath) {
            Storage::disk('public')->assertExists($filePath);
        }

        $patientPage = $this->actingAs($user)->get(route('patients.aesthetic.show', $patient));
        $patientPage->assertOk();
        $patientPage->assertSee('Consent & Aftercare PDFs');
        $patientPage->assertSee('Laser Consent');
        $patientPage->assertSee('Laser Cooling Instructions');
    }

    private function makeDirectSessionContext(bool $withTreatment = false): array
    {
        $clinic = Clinic::create([
            'name' => 'Aesthetic Clinic',
            'tenant_id' => 'TEN-1',
            'enabled_modules' => ['aesthetic', 'patients'],
        ]);

        $user = User::create([
            'username' => 'aesthetic_admin',
            'email' => 'aesthetic-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Aesthetic',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Sara',
            'last_name' => 'Patient',
        ]);

        $treatment = null;
        if ($withTreatment) {
            $treatment = \App\Models\AestheticTreatment::create([
                'tenant_id' => $clinic->tenant_id,
                'name' => 'Laser Facial',
                'category' => 'laser_treatments',
                'default_price' => 150,
                'is_active' => true,
            ]);
        }

        $session = AestheticSession::create([
            'tenant_id' => $clinic->tenant_id,
            'patient_id' => $patient->id,
            'treatment_id' => $treatment?->id,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'status' => 'scheduled',
            'notes' => 'Initial visit',
        ]);

        return [$user, $patient, $session];
    }

    private function makePackageSessionContext(): array
    {
        $clinic = Clinic::create([
            'name' => 'Aesthetic Clinic',
            'tenant_id' => 'TEN-1',
            'enabled_modules' => ['aesthetic', 'patients'],
        ]);

        $user = User::create([
            'username' => 'aesthetic_package_admin',
            'email' => 'aesthetic-package-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Package',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Package',
            'last_name' => 'Patient',
        ]);

        $package = \App\Models\AestheticPackage::create([
            'tenant_id' => $clinic->tenant_id,
            'name' => 'Glow Package',
            'total_sessions' => 4,
            'price' => 400,
            'discount' => 0,
        ]);

        $patientPackage = \App\Models\PatientPackage::create([
            'tenant_id' => $clinic->tenant_id,
            'patient_id' => $patient->id,
            'package_id' => $package->id,
            'sessions_used' => 1,
            'sessions_remaining' => 3,
            'purchase_date' => now()->toDateString(),
        ]);

        $session = AestheticSession::create([
            'tenant_id' => $clinic->tenant_id,
            'patient_package_id' => $patientPackage->id,
            'session_number' => 1,
            'session_date' => now()->toDateString(),
            'status' => 'scheduled',
            'notes' => 'Package follow-up session',
        ]);

        return [$user, $patient, $patientPackage, $session];
    }

    private function fakeSignatureDataUrl(): string
    {
        return 'data:image/png;base64,' . base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+iY1cAAAAASUVORK5CYII='));
    }
}