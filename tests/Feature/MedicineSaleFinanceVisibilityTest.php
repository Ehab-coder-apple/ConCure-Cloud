<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\MedicineSaleInvoice;
use App\Models\OrthodonticPayment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class MedicineSaleFinanceVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->withoutMiddleware([
            AuditMiddleware::class,
            ActivationMiddleware::class,
            EnsureContractIsAccepted::class,
            CheckModuleAccess::class,
            SetLocale::class,
            SetClinicTimezone::class,
        ]);

        View::share('primaryColor', null);
        View::share('companyName', 'Test Clinic');

        foreach (['settings', 'audit_logs', 'patient_files', 'orthodontic_payments', 'orthodontic_cases', 'medicine_transactions', 'medicines', 'dental_treatments', 'medicine_sale_invoices', 'aesthetic_invoices', 'invoices', 'expenses', 'receipts', 'patients', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('role')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('permissions')->nullable();
            $table->string('language')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('patient_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('session_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medicine_sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('sold_at');
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('dosage')->nullable();
            $table->string('form')->nullable();
            $table->text('description')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            $table->boolean('is_frequent')->default(false);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('medicine_sale_invoice_id')->nullable();
            $table->string('type')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('reference_number')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('stock_before', 12, 2)->default(0);
            $table->decimal('stock_after', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('transaction_date')->nullable();
            $table->timestamps();
        });

        Schema::create('orthodontic_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('doctor_id')->nullable();
            $table->string('treatment_type')->nullable();
            $table->date('start_date')->nullable();
            $table->integer('estimated_duration_months')->nullable();
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->string('current_phase')->nullable();
            $table->string('status')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('currency')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('payment_plan')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orthodontic_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orthodontic_case_id')->nullable();
            $table->foreignId('patient_id')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_type')->nullable();
            $table->integer('installment_number')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('expense_date')->nullable();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('receipt_date')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_role')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->string('action')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamps();
        });
    }

    public function test_finance_invoices_page_lists_medicine_sale_invoices(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient();

        $sale = MedicineSaleInvoice::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'MS-TEST-1001',
            'payment_method' => 'cash',
            'subtotal' => 75,
            'discount' => 0,
            'tax' => 0,
            'total' => 75,
            'paid_amount' => 75,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('finance.invoices'))
            ->assertOk()
            ->assertSee('Medicine Sales')
            ->assertSee($sale->invoice_number)
            ->assertSee('Medicine');
    }

    public function test_finance_dashboard_shows_recent_medicine_sales(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient();

        $sale = MedicineSaleInvoice::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'MS-TEST-2002',
            'payment_method' => 'cash',
            'subtotal' => 125,
            'discount' => 5,
            'tax' => 0,
            'total' => 120,
            'paid_amount' => 120,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertSee('Recent Medicine Sales')
            ->assertSee($sale->invoice_number);
    }

    public function test_finance_dashboard_summary_cards_include_medicine_sales(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient();

        MedicineSaleInvoice::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'MS-TEST-3003',
            'payment_method' => 'cash',
            'subtotal' => 4000,
            'discount' => 0,
            'tax' => 0,
            'total' => 4000,
            'paid_amount' => 4000,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertViewHas('monthlyRevenue', fn ($value) => (float) $value === 4000.0)
            ->assertViewHas('monthlyProfit', fn ($value) => (float) $value === 4000.0)
            ->assertViewHas('cashFlow', fn ($value) => (float) $value === 4000.0);
    }

    public function test_finance_department_revenue_includes_orthodontic_payments(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient();

        $this->createOrthodonticPayment($user, $patient, 2500);

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertOk()
            ->assertViewHas('deptRevenue', function ($deptRevenue) {
                return isset($deptRevenue['orthodontics'])
                    && (float) $deptRevenue['orthodontics']['revenue'] === 2500.0
                    && (float) $deptRevenue['orthodontics']['paid'] === 2500.0;
            })
            ->assertViewHas('monthlyRevenue', fn ($value) => (float) $value === 2500.0)
            ->assertViewHas('cashFlow', fn ($value) => (float) $value === 2500.0);
    }

    public function test_main_dashboard_total_revenue_includes_medicine_sales(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient('accountant', ['finance_view'], ['dashboard', 'finance']);

        MedicineSaleInvoice::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'invoice_number' => 'MS-TEST-4004',
            'payment_method' => 'cash',
            'subtotal' => 4000,
            'discount' => 0,
            'tax' => 0,
            'total' => 4000,
            'paid_amount' => 4000,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalRevenue', fn ($value) => (float) $value === 4000.0)
            ->assertViewHas('monthlyStats', function ($stats) {
                return collect($stats)->contains(fn ($row) => (float) ($row['revenue'] ?? 0) === 4000.0);
            });
    }

    public function test_main_dashboard_total_revenue_includes_orthodontic_payments(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient('accountant', ['finance_view'], ['dashboard', 'finance']);

        $this->createOrthodonticPayment($user, $patient, 2500);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('totalRevenue', fn ($value) => (float) $value === 2500.0)
            ->assertViewHas('monthlyStats', function ($stats) {
                return collect($stats)->contains(fn ($row) => (float) ($row['revenue'] ?? 0) === 2500.0);
            });
    }

    public function test_legacy_single_item_sell_flow_creates_finance_visible_invoice(): void
    {
        [$user, $patient] = $this->makeFinanceUserAndPatient();

        $medicine = \App\Models\Medicine::create([
            'name' => 'Test Antibiotic',
            'form' => 'tablet',
            'stock_quantity' => 10,
            'purchase_price' => 5,
            'selling_price' => 12,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('medicines.sell.process', $medicine), [
                'patient_id' => $patient->id,
                'quantity' => 2,
                'unit_price' => 12,
                'payment_method' => 'cash',
                'notes' => 'Legacy sell flow',
            ]);

        $invoice = MedicineSaleInvoice::query()->latest('id')->first();

        $this->assertNotNull($invoice);
        $response->assertRedirect(route('medicines.sales.show', $invoice));
        $this->assertSame('24.00', number_format((float) $invoice->total, 2, '.', ''));

        $this->actingAs($user)
            ->get(route('finance.invoices'))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    private function makeFinanceUserAndPatient(string $role = 'admin', array $permissions = [], ?array $enabledModules = null): array
    {
        $clinic = Clinic::create([
            'name' => 'Finance Clinic',
            'email' => 'finance@example.test',
            'tenant_id' => 1,
            'enabled_modules' => $enabledModules,
        ]);

        $user = User::create([
            'username' => 'finance_' . $role . '_' . uniqid(),
            'email' => 'finance_' . $role . '_' . uniqid() . '@example.test',
            'password' => 'secret',
            'first_name' => 'Finance',
            'last_name' => 'Admin',
            'role' => $role,
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'permissions' => $permissions,
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'patient_id' => 'P-1001',
            'is_active' => true,
        ]);

        return [$user, $patient];
    }

    private function createOrthodonticPayment(User $user, Patient $patient, float $amount): OrthodonticPayment
    {
        $caseId = \Illuminate\Support\Facades\DB::table('orthodontic_cases')->insertGetId([
            'case_number' => 'ORT-TEST-' . uniqid(),
            'patient_id' => $patient->id,
            'clinic_id' => $user->clinic_id,
            'doctor_id' => $user->id,
            'treatment_type' => 'metal_braces',
            'start_date' => now()->toDateString(),
            'estimated_duration_months' => 12,
            'estimated_completion_date' => now()->addYear()->toDateString(),
            'current_phase' => 'initial',
            'status' => 'active',
            'total_cost' => $amount,
            'currency' => 'USD',
            'paid_amount' => 0,
            'balance' => $amount,
            'payment_plan' => 'monthly',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return OrthodonticPayment::create([
            'orthodontic_case_id' => $caseId,
            'patient_id' => $patient->id,
            'clinic_id' => $user->clinic_id,
            'payment_date' => now()->toDateString(),
            'amount' => $amount,
            'currency' => 'USD',
            'payment_method' => 'cash',
            'payment_type' => 'installment',
            'receipt_number' => 'ORT-RCPT-' . uniqid(),
            'received_by' => $user->id,
        ]);
    }
}