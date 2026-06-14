<?php

namespace Tests\Feature;

use App\Http\Controllers\Aesthetic\AestheticInvoiceController;
use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Tests\TestCase;

class AestheticInvoiceTenantResolutionTest extends TestCase
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

        Schema::dropIfExists('aesthetic_invoice_items');
        Schema::dropIfExists('aesthetic_invoices');
        Schema::dropIfExists('aesthetic_sessions');
        Schema::dropIfExists('patient_packages');
        Schema::dropIfExists('aesthetic_treatments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('settings');
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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('patient_package_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->unsignedInteger('session_number')->default(1);
            $table->date('session_date')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('patient_package_id')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('treatment_id')->nullable();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    public function test_create_form_auto_assigns_missing_clinic_tenant_id(): void
    {
        $clinic = Clinic::create(['name' => 'Aesthetic Clinic', 'enabled_modules' => ['aesthetic']]);
        $user = User::create([
            'username' => 'invoice_admin',
            'email' => 'invoice-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Invoice',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->actingAs($user);
        $response = app(AestheticInvoiceController::class)->create(new Request());

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame('TEN-' . $clinic->id, $clinic->fresh()->tenant_id);
    }

    public function test_store_auto_assigns_missing_clinic_tenant_id_before_creating_invoice(): void
    {
        $clinic = Clinic::create(['name' => 'Tenantless Invoice Clinic', 'enabled_modules' => ['aesthetic']]);
        $user = User::create([
            'username' => 'tenantless_invoice_admin',
            'email' => 'tenantless-invoice-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Tenantless',
            'last_name' => 'Invoice',
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

        $response = $this->actingAs($user)->post(route('aesthetic.invoices.store'), [
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'items' => [[
                'description' => 'Hydra Facial',
                'quantity' => 1,
                'unit_price' => 150,
                'discount' => 0,
            ]],
        ]);

        $response->assertStatus(302);
        $clinic->refresh();

        $this->assertSame('TEN-' . $clinic->id, $clinic->tenant_id);
        $this->assertDatabaseHas('aesthetic_invoices', [
            'patient_id' => $patient->id,
            'tenant_id' => $clinic->tenant_id,
            'clinic_id' => $clinic->id,
        ]);
    }
}