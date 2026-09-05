<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\AestheticInventory;
use App\Models\AestheticSession;
use App\Models\AestheticTreatment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\SessionInventoryUsage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

class AestheticInventoryReportTest extends TestCase
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

        Schema::dropIfExists('session_inventory_usage');
        Schema::dropIfExists('aesthetic_inventory');
        Schema::dropIfExists('aesthetic_sessions');
        Schema::dropIfExists('patient_packages');
        Schema::dropIfExists('aesthetic_treatments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('settings');

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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('aesthetic_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
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
            $table->unsignedInteger('session_number')->default(1);
            $table->date('session_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('status')->default('completed');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_inventory', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('product_name');
            $table->string('type')->default('consumable');
            $table->integer('quantity')->default(0);
            $table->integer('purchased_quantity')->default(0);
            $table->integer('bonus_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('session_inventory_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity_used');
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
            'name' => 'Inventory Report Test Clinic',
            'tenant_id' => 'TEN-INV-REPORT-TEST',
            'enabled_modules' => ['aesthetic'],
        ]);

        $this->user = User::create([
            'username' => 'inv_report_admin',
            'email' => 'inv-report-admin@example.test',
            'password' => bcrypt('secret'),
            'first_name' => 'Inv',
            'last_name' => 'Report',
            'role' => 'admin',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    private function makeUsage(AestheticInventory $product, int $quantity, string $sessionDate): void
    {
        $patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'first_name' => 'Test',
            'last_name' => 'Patient',
        ]);

        $treatment = AestheticTreatment::create([
            'tenant_id' => $this->clinic->tenant_id,
            'name' => 'Laser Session',
        ]);

        $session = AestheticSession::create([
            'tenant_id' => $this->clinic->tenant_id,
            'patient_id' => $patient->id,
            'treatment_id' => $treatment->id,
            'session_number' => 1,
            'session_date' => $sessionDate,
            'status' => 'completed',
        ]);

        SessionInventoryUsage::create([
            'session_id' => $session->id,
            'tenant_id' => $this->clinic->tenant_id,
            'product_id' => $product->id,
            'quantity_used' => $quantity,
        ]);
    }

    public function test_report_defaults_to_current_month_and_shows_all_products(): void
    {
        $productA = AestheticInventory::create([
            'tenant_id' => $this->clinic->tenant_id,
            'product_name' => 'Botox Vial',
            'type' => 'medication',
            'quantity' => 10,
            'purchased_quantity' => 10,
            'bonus_quantity' => 0,
            'purchase_price' => 50,
            'selling_price' => 100,
        ]);

        $productB = AestheticInventory::create([
            'tenant_id' => $this->clinic->tenant_id,
            'product_name' => 'Filler Syringe',
            'type' => 'consumable',
            'quantity' => 5,
            'purchased_quantity' => 5,
            'bonus_quantity' => 0,
            'purchase_price' => 30,
            'selling_price' => 60,
        ]);

        // Sale within current month.
        $this->makeUsage($productA, 3, now()->startOfMonth()->addDays(2)->toDateString());
        // Sale outside current month (last month) - should not count.
        $this->makeUsage($productA, 2, now()->subMonthsNoOverflow(2)->toDateString());

        $response = $this->actingAs($this->user)->get(route('aesthetic.inventory.report'));

        $response->assertOk();
        $response->assertViewIs('aesthetic.inventory.report');
        $response->assertSee('Botox Vial');
        $response->assertSee('Filler Syringe');

        $rows = $response->viewData('rows');
        $botoxRow = $rows->firstWhere('product.id', $productA->id);
        $fillerRow = $rows->firstWhere('product.id', $productB->id);

        $this->assertSame(3, $botoxRow->sold_quantity);
        $this->assertEquals(300.0, $botoxRow->total_sold_value); // 3 * 100
        $this->assertSame(10, $botoxRow->remaining_quantity);
        $this->assertEquals(500.0, $botoxRow->current_stock_value); // 10 * 50

        $this->assertSame(0, $fillerRow->sold_quantity);
        $this->assertSame(5, $fillerRow->remaining_quantity);
        $this->assertEquals(150.0, $fillerRow->current_stock_value); // 5 * 30
    }

    public function test_weekly_period_only_counts_usage_within_current_week(): void
    {
        $product = AestheticInventory::create([
            'tenant_id' => $this->clinic->tenant_id,
            'product_name' => 'Chemical Peel Kit',
            'type' => 'consumable',
            'quantity' => 20,
            'purchased_quantity' => 20,
            'bonus_quantity' => 0,
            'purchase_price' => 10,
            'selling_price' => 25,
        ]);

        $this->makeUsage($product, 4, now()->startOfWeek()->toDateString());
        $this->makeUsage($product, 6, now()->subWeeks(2)->toDateString());

        $response = $this->actingAs($this->user)
            ->get(route('aesthetic.inventory.report', ['period' => 'week']));

        $response->assertOk();
        $rows = $response->viewData('rows');
        $row = $rows->firstWhere('product.id', $product->id);

        $this->assertSame(4, $row->sold_quantity);
        $this->assertEquals(100.0, $row->total_sold_value); // 4 * 25
    }

    public function test_custom_period_filters_by_given_date_range(): void
    {
        $product = AestheticInventory::create([
            'tenant_id' => $this->clinic->tenant_id,
            'product_name' => 'Microneedling Cartridge',
            'type' => 'consumable',
            'quantity' => 15,
            'purchased_quantity' => 15,
            'bonus_quantity' => 0,
            'purchase_price' => 5,
            'selling_price' => 15,
        ]);

        $this->makeUsage($product, 2, '2026-01-05');
        $this->makeUsage($product, 3, '2026-01-15');
        $this->makeUsage($product, 7, '2026-02-01'); // outside custom range

        $response = $this->actingAs($this->user)->get(route('aesthetic.inventory.report', [
            'period' => 'custom',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]));

        $response->assertOk();
        $rows = $response->viewData('rows');
        $row = $rows->firstWhere('product.id', $product->id);

        $this->assertSame(5, $row->sold_quantity); // 2 + 3
        $this->assertEquals(75.0, $row->total_sold_value); // 5 * 15
    }
}
