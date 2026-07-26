<?php

namespace Tests\Feature;

use App\Http\Controllers\Aesthetic\AestheticInvoiceController;
use App\Http\Controllers\FinanceController;
use App\Models\AestheticInvoice;
use App\Models\Invoice;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
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

        ViewFacade::share([
            'appName' => config('app.name'),
            'companyName' => config('concure.company_name', 'ConCure'),
            'primaryColor' => config('concure.primary_color', '#008080'),
            'supportedLanguages' => config('concure.supported_languages', [
                'en' => 'English',
                'ar' => 'العربية',
            ]),
        ]);

        Schema::dropIfExists('aesthetic_invoice_items');
        Schema::dropIfExists('aesthetic_invoices');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('dental_treatments');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('session_inventory_usage');
        Schema::dropIfExists('aesthetic_inventory');
        Schema::dropIfExists('aesthetic_session_treatment');
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('source_module')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('item_type')->nullable();
            $table->timestamps();
        });

        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('assigned_doctor_id')->nullable();
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('procedure_name')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('category')->nullable();
            $table->date('receipt_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('category')->nullable();
            $table->date('expense_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('status')->default('pending');
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

        Schema::create('aesthetic_session_treatment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('treatment_id');
            $table->timestamps();
        });

        Schema::create('aesthetic_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('product_name');
            $table->string('type')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('session_inventory_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_used')->default(1);
            $table->timestamps();
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

    public function test_generate_invoice_number_is_global_across_tenants(): void
    {
        $firstClinic = Clinic::create([
            'name' => 'First Invoice Clinic',
            'tenant_id' => 'TEN-1',
            'enabled_modules' => ['aesthetic'],
        ]);

        $secondClinic = Clinic::create([
            'name' => 'Second Invoice Clinic',
            'tenant_id' => 'TEN-2',
            'enabled_modules' => ['aesthetic'],
        ]);

        $user = User::create([
            'username' => 'second_clinic_invoice_admin',
            'email' => 'second-clinic-invoice-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Second',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $secondClinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $firstClinic->id,
            'first_name' => 'Existing',
            'last_name' => 'Invoice',
        ]);

        $today = now()->toDateString();
        $todayPrefix = now()->format('Ymd');

        DB::table('aesthetic_invoices')->insert([
            'tenant_id' => $firstClinic->tenant_id,
            'clinic_id' => $firstClinic->id,
            'invoice_number' => "AEST-{$todayPrefix}-0001",
            'patient_id' => $patient->id,
            'invoice_date' => $today,
            'subtotal' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance' => 0,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $this->assertSame("AEST-{$todayPrefix}-0002", AestheticInvoice::generateInvoiceNumber());
    }

    public function test_create_form_prefills_invoice_for_direct_session(): void
    {
        $clinic = Clinic::create([
            'name' => 'Direct Session Clinic',
            'tenant_id' => 'TEN-100',
            'enabled_modules' => ['aesthetic'],
        ]);

        $user = User::create([
            'username' => 'direct_invoice_admin',
            'email' => 'direct-invoice-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Direct',
            'last_name' => 'Invoice',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Lina',
            'last_name' => 'Patient',
        ]);

        $treatment = \App\Models\AestheticTreatment::create([
            'tenant_id' => $clinic->tenant_id,
            'name' => 'Direct Peel',
            'category' => 'skincare',
            'default_price' => 180,
            'is_active' => true,
        ]);

        $session = \App\Models\AestheticSession::create([
            'tenant_id' => $clinic->tenant_id,
            'patient_id' => $patient->id,
            'treatment_id' => $treatment->id,
            'session_number' => 3,
            'session_date' => now()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);
        $response = app(AestheticInvoiceController::class)->create(new Request(['session_id' => $session->id]));

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();

        $this->assertSame($patient->id, $data['preselectedPatient']->id);
        $this->assertSame($session->id, $data['preselectedSession']->id);
        $this->assertSame('Direct Peel - Session #3', $data['lineItems'][0]['description']);
        $this->assertSame($treatment->id, $data['lineItems'][0]['treatment_id']);
        $this->assertEquals(180, $data['lineItems'][0]['unit_price']);
    }

    public function test_finance_invoices_page_lists_aesthetic_invoices_with_links(): void
    {
        $clinic = Clinic::create([
            'name' => 'Finance Linked Clinic',
            'tenant_id' => 'TEN-500',
            'enabled_modules' => ['aesthetic', 'finance'],
        ]);

        $user = User::create([
            'username' => 'finance_link_admin',
            'email' => 'finance-link-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Finance',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'patient_id' => 'P-9001',
            'first_name' => 'Nora',
            'last_name' => 'Finance',
            'is_active' => true,
        ]);

        $invoice = AestheticInvoice::create([
            'tenant_id' => $clinic->tenant_id,
            'clinic_id' => $clinic->id,
            'invoice_number' => 'AEST-TEST-0001',
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 150,
            'tax_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 150,
            'paid_amount' => 50,
            'balance' => 100,
            'status' => 'partial',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('finance.invoices'));

        $response->assertOk();
        $response->assertSee('Aesthetic Department Invoices');
        $response->assertSee('AEST-TEST-0001');
        $response->assertSee('Nora Finance');
        $response->assertSee(route('aesthetic.invoices.show', $invoice), false);
        $response->assertSee(route('aesthetic.invoices.edit', $invoice), false);
    }

    public function test_finance_revenue_report_includes_aesthetic_invoices_and_paid_amounts(): void
    {
        $clinic = Clinic::create([
            'name' => 'Revenue Clinic',
            'tenant_id' => 'TEN-700',
            'enabled_modules' => ['aesthetic', 'finance'],
        ]);

        $user = User::create([
            'username' => 'revenue_admin',
            'email' => 'revenue-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Revenue',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Profit',
            'last_name' => 'Patient',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-0001',
            'patient_id' => $patient->id,
            'clinic_id' => $clinic->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100,
            'tax_rate' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
            'paid_amount' => 40,
            'balance' => 60,
            'status' => 'partial_paid',
            'created_by' => $user->id,
        ]);

        DB::table('invoice_items')->insert([
            'invoice_id' => $invoice->id,
            'description' => 'Clinic service',
            'quantity' => 1,
            'unit_price' => 100,
            'item_type' => 'consultation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AestheticInvoice::create([
            'tenant_id' => $clinic->tenant_id,
            'clinic_id' => $clinic->id,
            'invoice_number' => 'AEST-REV-0001',
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 80,
            'tax_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 80,
            'paid_amount' => 20,
            'balance' => 60,
            'status' => 'partial',
            'created_by' => $user->id,
        ]);

        DB::table('receipts')->insert([
            'clinic_id' => $clinic->id,
            'description' => 'Other income',
            'amount' => 30,
            'category' => 'other',
            'receipt_date' => now()->toDateString(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expenses')->insert([
            'clinic_id' => $clinic->id,
            'description' => 'Supplies',
            'amount' => 50,
            'category' => 'supplies',
            'expense_date' => now()->toDateString(),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user);

        $controller = app(FinanceController::class);
        $method = new \ReflectionMethod($controller, 'getReportData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, $user);

        $this->assertSame(270.0, (float) $data['currentMonth']['revenue']);
        $this->assertSame(220.0, (float) $data['currentMonth']['profit']);

        $profitLossMethod = new \ReflectionMethod($controller, 'getProfitLossData');
        $profitLossMethod->setAccessible(true);
        $profitLossData = $profitLossMethod->invoke($controller, $user, now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(270.0, (float) $profitLossData['revenue']['total']);
        $this->assertSame(220.0, (float) $profitLossData['grossProfit']);
        $this->assertEquals(80, $profitLossData['revenue']['byType']['aesthetic_invoices']);
        $this->assertEquals(40, $profitLossData['revenue']['byType']['invoice_payments']);
        $this->assertEquals(20, $profitLossData['revenue']['byType']['aesthetic_payments']);
    }

    public function test_user_performance_report_groups_financial_activity_by_responsible_user(): void
    {
        $clinic = Clinic::create([
            'name' => 'Per User Clinic',
            'tenant_id' => 'TEN-701',
            'enabled_modules' => ['aesthetic', 'finance', 'dental'],
        ]);

        $admin = User::create([
            'username' => 'per_user_admin',
            'email' => 'per-user-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Finance',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $doctor = User::create([
            'username' => 'per_user_doctor',
            'email' => 'per-user-doctor@example.test',
            'password' => 'secret',
            'first_name' => 'Dental',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Report',
            'last_name' => 'Patient',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-USER-0001',
            'patient_id' => $patient->id,
            'clinic_id' => $clinic->id,
            'source_module' => 'finance',
            'invoice_date' => now()->toDateString(),
            'subtotal' => 120,
            'tax_rate' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 120,
            'paid_amount' => 50,
            'balance' => 70,
            'status' => 'partial_paid',
            'created_by' => $admin->id,
        ]);

        DB::table('dental_treatments')->insert([
            'patient_id' => $patient->id,
            'clinic_id' => $clinic->id,
            'invoice_id' => $invoice->id,
            'assigned_doctor_id' => $doctor->id,
            'created_by' => $admin->id,
            'procedure_name' => 'Root Canal',
            'estimated_cost' => 120,
            'paid_amount' => 50,
            'payment_status' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        AestheticInvoice::create([
            'tenant_id' => $clinic->tenant_id,
            'clinic_id' => $clinic->id,
            'invoice_number' => 'AEST-USER-0001',
            'patient_id' => $patient->id,
            'invoice_date' => now()->toDateString(),
            'subtotal' => 80,
            'tax_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 80,
            'paid_amount' => 20,
            'balance' => 60,
            'status' => 'partial',
            'created_by' => $admin->id,
        ]);

        DB::table('receipts')->insert([
            'clinic_id' => $clinic->id,
            'description' => 'Other income',
            'amount' => 30,
            'category' => 'other',
            'receipt_date' => now()->toDateString(),
            'created_by' => $admin->id,
            'status' => 'approved',
            'approved_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('expenses')->insert([
            'clinic_id' => $clinic->id,
            'description' => 'Doctor supplies',
            'amount' => 25,
            'category' => 'supplies',
            'expense_date' => now()->toDateString(),
            'created_by' => $doctor->id,
            'approved_by' => $admin->id,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson(route('finance.reports.user-performance', [
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();

        $rows = collect($response->json('rows'))->keyBy('user_name');

        $this->assertEqualsCanonicalizing(['aesthetic', 'dental', 'finance'], $response->json('available_modules'));
        $this->assertEquals(2, $response->json('summary.people_count'));
        $this->assertEquals(300.0, (float) $response->json('summary.total_revenue'));
        $this->assertEquals(25.0, (float) $response->json('summary.expenses'));
        $this->assertEquals(275.0, (float) $response->json('summary.net_total'));

        $doctorRow = $rows->get('Dental Doctor');
        $adminRow = $rows->get('Finance Admin');

        $this->assertNotNull($doctorRow);
        $this->assertNotNull($adminRow);

        $this->assertEquals(120.0, (float) $doctorRow['billed_revenue']);
        $this->assertEquals(50.0, (float) $doctorRow['collected_payments']);
        $this->assertEquals(25.0, (float) $doctorRow['expenses']);
        $this->assertEquals(145.0, (float) $doctorRow['net_total']);

        $this->assertEquals(80.0, (float) $adminRow['billed_revenue']);
        $this->assertEquals(20.0, (float) $adminRow['collected_payments']);
        $this->assertEquals(30.0, (float) $adminRow['other_receipts']);
        $this->assertEquals(130.0, (float) $adminRow['net_total']);
    }
}