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

        foreach (['settings', 'medicine_transactions', 'medicines', 'dental_treatments', 'medicine_sale_invoices', 'aesthetic_invoices', 'invoices', 'expenses', 'receipts', 'patients', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
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

    private function makeFinanceUserAndPatient(): array
    {
        $clinic = Clinic::create([
            'name' => 'Finance Clinic',
            'email' => 'finance@example.test',
            'tenant_id' => 1,
        ]);

        $user = User::create([
            'username' => 'finance_admin',
            'email' => 'finance_admin@example.test',
            'password' => 'secret',
            'first_name' => 'Finance',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'permissions' => [],
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
}