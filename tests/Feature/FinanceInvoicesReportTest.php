<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\AestheticInvoice;
use App\Models\Clinic;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MedicineSaleInvoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

/**
 * Covers the Finance > Reports > Invoices Report: filtering by period,
 * category (source module), item type, and user, plus the Excel export
 * and PDF print endpoints.
 */
class FinanceInvoicesReportTest extends TestCase
{
    private Clinic $clinic;
    private User $admin;
    private User $doctor;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ActivationMiddleware::class,
            AuditMiddleware::class,
            EnsureContractIsAccepted::class,
            CheckModuleAccess::class,
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

        foreach (['invoice_items', 'invoices', 'dental_treatments', 'aesthetic_invoices', 'medicine_sale_invoices', 'patients', 'users', 'settings', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_demo')->default(false);
            $table->boolean('can_export')->default(false);
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('source_module')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_rate', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('item_type')->nullable();
            $table->timestamps();
        });

        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('patient_package_id')->nullable();
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
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medicine_sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('completed');
            $table->decimal('original_subtotal', 12, 2)->nullable();
            $table->decimal('original_discount', 12, 2)->nullable();
            $table->decimal('original_tax', 12, 2)->nullable();
            $table->decimal('original_total', 12, 2)->nullable();
            $table->decimal('original_paid_amount', 12, 2)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->string('cancel_reason_code')->nullable();
            $table->text('cancel_notes')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->dateTime('sold_at');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $this->clinic = Clinic::create([
            'tenant_id' => 'T1',
            'name' => 'Test Clinic',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->admin = User::create([
            'username' => 'admin1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Ada',
            'last_name' => 'Min',
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->doctor = User::create([
            'username' => 'doctor1',
            'email' => 'doctor1@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Doc',
            'last_name' => 'Tor',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->patient = Patient::create([
            'patient_id' => 'P-1',
            'clinic_id' => $this->clinic->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_active' => true,
        ]);
    }

    private function makeInvoice(array $overrides, array $items = []): Invoice
    {
        // Invoice::calculateTotals() (run on the 'creating' model event)
        // derives total_amount/balance from subtotal/tax/discount/paid_amount,
        // overwriting any 'total_amount' passed directly. So when a test
        // overrides 'total_amount' without also overriding 'subtotal', mirror
        // it into subtotal/paid_amount here to get the total the test expects.
        if (isset($overrides['total_amount']) && !isset($overrides['subtotal'])) {
            $overrides['subtotal'] = $overrides['total_amount'];
            $overrides['paid_amount'] = $overrides['paid_amount'] ?? $overrides['total_amount'];
        }

        $invoice = Invoice::create(array_merge([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'created_by' => $this->admin->id,
            'invoice_number' => 'INV-' . uniqid(),
            'invoice_date' => now()->toDateString(),
            'subtotal' => 100,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 100,
            'paid_amount' => 100,
            'balance' => 0,
            'status' => 'paid',
        ], $overrides));

        foreach ($items as $item) {
            InvoiceItem::create(array_merge(['invoice_id' => $invoice->id], $item));
        }

        return $invoice;
    }

    private function makeAestheticInvoice(array $overrides = []): AestheticInvoice
    {
        return AestheticInvoice::create(array_merge([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'created_by' => $this->admin->id,
            'invoice_number' => 'AES-' . uniqid(),
            'invoice_date' => now()->toDateString(),
            'subtotal' => 80,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 80,
            'paid_amount' => 80,
            'balance' => 0,
            'status' => 'paid',
        ], $overrides));
    }

    private function makeMedicineSaleInvoice(array $overrides = []): MedicineSaleInvoice
    {
        return MedicineSaleInvoice::create(array_merge([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->admin->id,
            'patient_id' => $this->patient->id,
            'invoice_number' => 'MED-' . uniqid(),
            'payment_method' => 'cash',
            'subtotal' => 40,
            'discount' => 0,
            'tax' => 0,
            'total' => 40,
            'paid_amount' => 40,
            'status' => 'completed',
            'sold_at' => now(),
        ], $overrides));
    }

    public function test_invoices_report_filters_by_period(): void
    {
        $this->makeInvoice(['invoice_date' => now()->toDateString(), 'total_amount' => 100]);
        $this->makeInvoice(['invoice_date' => now()->subMonths(2)->toDateString(), 'total_amount' => 200]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'date_from' => now()->startOfMonth()->format('Y-m-d'),
            'date_to' => now()->endOfMonth()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 100.0);
    }

    public function test_invoices_report_filters_by_category(): void
    {
        $this->makeInvoice(['source_module' => 'quick_visit', 'total_amount' => 50]);
        $this->makeInvoice(['source_module' => null, 'total_amount' => 70]); // falls back to "finance"

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'category' => 'quick_visit',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 50.0);
    }

    public function test_invoices_report_filters_dental_category_via_relation_not_source_module(): void
    {
        $dentalInvoice = $this->makeInvoice(['total_amount' => 300]);
        \DB::table('dental_treatments')->insert(['invoice_id' => $dentalInvoice->id]);
        $this->makeInvoice(['total_amount' => 40]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'category' => 'dental',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 300.0);
    }

    public function test_invoices_report_filters_by_item_type(): void
    {
        $this->makeInvoice(['total_amount' => 60], [
            ['description' => 'Checkup', 'item_type' => 'consultation', 'quantity' => 1, 'unit_price' => 60, 'total_price' => 60],
        ]);
        $this->makeInvoice(['total_amount' => 90], [
            ['description' => 'X-Ray', 'item_type' => 'lab_test', 'quantity' => 1, 'unit_price' => 90, 'total_price' => 90],
        ]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'item_type' => 'consultation',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 60.0);
    }

    public function test_invoices_report_filters_by_user(): void
    {
        $this->makeInvoice(['created_by' => $this->admin->id, 'total_amount' => 10]);
        $this->makeInvoice(['created_by' => $this->doctor->id, 'total_amount' => 20]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'user_id' => $this->doctor->id,
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 20.0);
    }

    public function test_invoices_report_combines_multiple_filters(): void
    {
        $this->makeInvoice(['source_module' => 'quick_visit', 'created_by' => $this->doctor->id, 'total_amount' => 15], [
            ['description' => 'Visit', 'item_type' => 'consultation', 'quantity' => 1, 'unit_price' => 15, 'total_price' => 15],
        ]);
        // Same category/user but different item type -> excluded.
        $this->makeInvoice(['source_module' => 'quick_visit', 'created_by' => $this->doctor->id, 'total_amount' => 25], [
            ['description' => 'Meds', 'item_type' => 'medication', 'quantity' => 1, 'unit_price' => 25, 'total_price' => 25],
        ]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'category' => 'quick_visit',
            'user_id' => $this->doctor->id,
            'item_type' => 'consultation',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 15.0);
    }

    public function test_invoices_report_excel_export_downloads_a_spreadsheet(): void
    {
        $this->makeInvoice(['total_amount' => 100]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_invoices_report_pdf_print_renders_successfully(): void
    {
        $this->makeInvoice(['total_amount' => 100]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices.print'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_demo_clinic_without_export_permission_is_blocked_from_exporting(): void
    {
        $this->clinic->update(['is_demo' => true, 'can_export' => false]);
        $this->makeInvoice(['total_amount' => 100]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices.export'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_doctor_without_finance_reports_permission_cannot_view_report(): void
    {
        $this->doctor->update(['permissions' => []]);
        $this->makeInvoice(['total_amount' => 100]);

        $response = $this->actingAs($this->doctor)->get(route('finance.reports.invoices'));

        $response->assertForbidden();
    }

    /**
     * Regression test: a clinic whose only billing activity is Pharmacy /
     * Inventory sales (stored in medicine_sale_invoices, not the generic
     * invoices table) must still see those invoices in the report with
     * 'All Categories', instead of an empty report.
     */
    public function test_invoices_report_includes_pharmacy_medicine_sale_invoices(): void
    {
        $this->makeMedicineSaleInvoice(['total' => 40, 'paid_amount' => 40]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices'));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 40.0);
    }

    /**
     * Regression test: a clinic running Aesthetic sessions must see those
     * invoices too, since they're stored in aesthetic_invoices rather than
     * the generic invoices table.
     */
    public function test_invoices_report_includes_aesthetic_invoices(): void
    {
        $this->makeAestheticInvoice(['total_amount' => 80, 'paid_amount' => 80]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices'));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 80.0);
    }

    public function test_invoices_report_combines_all_three_invoice_sources_with_all_categories(): void
    {
        $this->makeInvoice(['total_amount' => 10]);
        $this->makeAestheticInvoice(['total_amount' => 80, 'paid_amount' => 80]);
        $this->makeMedicineSaleInvoice(['total' => 40, 'paid_amount' => 40]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices'));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 3 && (float) $summary['total_amount'] === 130.0);
    }

    public function test_invoices_report_category_filter_isolates_medicine_sale_invoices(): void
    {
        $this->makeInvoice(['total_amount' => 10]);
        $this->makeAestheticInvoice(['total_amount' => 80, 'paid_amount' => 80]);
        $this->makeMedicineSaleInvoice(['total' => 40, 'paid_amount' => 40]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'category' => 'medicine_sale',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 40.0);
    }

    public function test_invoices_report_item_type_filter_excludes_pharmacy_and_aesthetic_invoices(): void
    {
        // Pharmacy and Aesthetic invoices don't carry item types, so an
        // item_type filter should exclude them even if other filters match.
        $this->makeInvoice(['total_amount' => 10], [
            ['description' => 'Checkup', 'item_type' => 'consultation', 'quantity' => 1, 'unit_price' => 10, 'total_price' => 10],
        ]);
        $this->makeAestheticInvoice(['total_amount' => 80, 'paid_amount' => 80]);
        $this->makeMedicineSaleInvoice(['total' => 40, 'paid_amount' => 40]);

        $response = $this->actingAs($this->admin)->get(route('finance.reports.invoices', [
            'item_type' => 'consultation',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary', fn ($summary) => $summary['count'] === 1 && (float) $summary['total_amount'] === 10.0);
    }
}
