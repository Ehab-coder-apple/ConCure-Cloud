<?php

namespace Tests\Feature;

use App\Http\Controllers\Aesthetic\AestheticTreatmentController;
use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Tests\TestCase;

class AestheticTreatmentTenantResolutionTest extends TestCase
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

        Schema::dropIfExists('aesthetic_treatments');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('aesthetic_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('category');
            $table->decimal('default_price', 10, 2);
            $table->boolean('session_required')->default(false);
            $table->integer('sessions_count')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_create_form_auto_assigns_missing_clinic_tenant_id(): void
    {
        $clinic = Clinic::create([
            'name' => 'Aesthetic Clinic',
            'enabled_modules' => ['aesthetic'],
            'is_active' => true,
            'activated_at' => now(),
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

        $this->actingAs($user);

        $response = app(AestheticTreatmentController::class)->create();

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('TEN-' . $clinic->id, $clinic->fresh()->tenant_id);
    }

    public function test_store_auto_assigns_missing_clinic_tenant_id_before_creating_treatment(): void
    {
        $clinic = Clinic::create([
            'name' => 'Tenantless Aesthetic Clinic',
            'enabled_modules' => ['aesthetic'],
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $user = User::create([
            'username' => 'tenantless_aesthetic_admin',
            'email' => 'tenantless-aesthetic-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Tenantless',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('aesthetic.treatments.store'), [
                'name' => 'Hydra Facial',
                'category' => 'skincare',
                'default_price' => '120',
                'session_required' => '1',
                'sessions_count' => '3',
                'description' => 'Hydration-focused treatment',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('aesthetic.treatments.index'));

        $clinic->refresh();

        $this->assertSame('TEN-' . $clinic->id, $clinic->tenant_id);
        $this->assertDatabaseHas('aesthetic_treatments', [
            'name' => 'Hydra Facial',
            'tenant_id' => $clinic->tenant_id,
            'category' => 'skincare',
        ]);
    }
}