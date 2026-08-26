<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\AestheticSession;
use App\Models\AestheticTreatment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

class AestheticFollowUpRemindersPageTest extends TestCase
{
    private Clinic $clinic;
    private User $user;

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

        Schema::dropIfExists('aesthetic_session_treatment');
        Schema::dropIfExists('aesthetic_sessions');
        Schema::dropIfExists('patient_packages');
        Schema::dropIfExists('aesthetic_treatments');
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
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
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

        Schema::create('patient_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedInteger('sessions_remaining')->default(0);
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
            $table->string('external_practitioner_name')->nullable();
            $table->unsignedInteger('session_number')->default(1);
            $table->date('session_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('completed');
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

        Schema::dropIfExists('session_images');
        Schema::create('session_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('tenant_id')->nullable();
            $table->string('type')->nullable();
            $table->string('image_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
        });

        $this->clinic = Clinic::create([
            'name' => 'Follow-up Test Clinic',
            'tenant_id' => 'TEN-FOLLOWUP-TEST',
            'enabled_modules' => ['aesthetic'],
        ]);

        $this->user = User::create([
            'username' => 'followup_admin',
            'email' => 'followup-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Follow',
            'last_name' => 'Up',
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    private function makeDirectSession(string $patientName, ?string $nextDueDate): AestheticSession
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => $patientName,
            'last_name' => 'Patient',
            'phone' => '07701234567',
        ]);

        $treatment = AestheticTreatment::create([
            'tenant_id' => $this->clinic->tenant_id,
            'name' => 'Laser Session',
        ]);

        return AestheticSession::create([
            'tenant_id' => $this->clinic->tenant_id,
            'patient_id' => $patient->id,
            'treatment_id' => $treatment->id,
            'session_number' => 1,
            'session_date' => now()->subDays(5),
            'next_due_date' => $nextDueDate,
            'status' => 'completed',
        ]);
    }

    public function test_follow_up_reminders_page_lists_sessions_with_next_due_date_sorted_ascending(): void
    {
        $this->makeDirectSession('Later Due', now()->addDays(30)->toDateString());
        $this->makeDirectSession('Sooner Due', now()->addDays(2)->toDateString());
        // Completed session without a next_due_date should be excluded.
        $this->makeDirectSession('No Reminder', null);

        $response = $this->actingAs($this->user)
            ->get(route('aesthetic.sessions.follow-up-reminders'));

        $response->assertOk();
        $response->assertViewIs('aesthetic.sessions.follow-up-reminders');
        $response->assertDontSee('No Reminder');

        $content = $response->getContent();
        $soonerPos = strpos($content, 'Sooner Due');
        $laterPos = strpos($content, 'Later Due');

        $this->assertNotFalse($soonerPos);
        $this->assertNotFalse($laterPos);
        $this->assertLessThan($laterPos, $soonerPos, 'Sooner due date should appear before later due date.');
    }

    public function test_follow_up_reminders_page_paginates_at_25_per_page(): void
    {
        foreach (range(1, 30) as $i) {
            $this->makeDirectSession("Patient {$i}", now()->addDays($i)->toDateString());
        }

        $response = $this->actingAs($this->user)
            ->get(route('aesthetic.sessions.follow-up-reminders'));

        $response->assertOk();
        $paginator = $response->viewData('followUpReminders');

        $this->assertSame(30, $paginator->total());
        $this->assertCount(25, $paginator->items());
        $this->assertSame(25, $paginator->perPage());
    }

    public function test_view_all_link_present_on_sessions_index_widget(): void
    {
        $this->makeDirectSession('Widget Patient', now()->addDays(3)->toDateString());

        $response = $this->actingAs($this->user)->get(route('aesthetic.sessions.index'));

        $response->assertOk();
        $response->assertSee(route('aesthetic.sessions.follow-up-reminders'), false);
    }

    public function test_whatsapp_action_button_present_for_patient_with_phone(): void
    {
        $session = $this->makeDirectSession('Whats App', now()->addDays(1)->toDateString());

        $response = $this->actingAs($this->user)
            ->get(route('aesthetic.sessions.follow-up-reminders'));

        $response->assertOk();
        $response->assertSee(route('aesthetic.sessions.send-whatsapp-reminder', $session), false);
        $response->assertSee('js-send-followup-whatsapp', false);
    }
}
