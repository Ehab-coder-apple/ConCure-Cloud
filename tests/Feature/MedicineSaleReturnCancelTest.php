<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Medicine;
use App\Models\MedicineSaleInvoice;
use App\Models\MedicineTransaction;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

/**
 * Covers Sales Return & Invoice Management for the Pharmacy/Inventory
 * multi-item sale invoice (MedicineSaleInvoice + MedicineTransaction):
 *
 *  1. Partial return: validates remaining-quantity cap, restocks the
 *     medicine, credits the invoice (subtotal/total/paid_amount reduced),
 *     and records InvoiceReturn + ReturnItem audit rows.
 *  2. Edit invoice: quantity delta adjusts stock up/down and recalculates
 *     totals.
 *  3. Cancel invoice: restores all outstanding stock, zeroes the invoice's
 *     financial fields (so revenue ledgers exclude it), requires a reason.
 *  4. Authorization: only admins/pharmacists/super-admins may return,
 *     edit, or cancel a sale invoice.
 */
class MedicineSaleReturnCancelTest extends TestCase
{
    private Clinic $clinic;
    private User $pharmacist;
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

        foreach (['return_items', 'invoice_returns', 'medicine_transactions', 'medicine_sale_invoices', 'medicines', 'patients', 'users', 'clinics'] as $table) {
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('dosage')->nullable();
            $table->string('form')->nullable();
            $table->boolean('is_frequent')->default(false);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('invoice_number')->unique();
            $table->string('payment_method', 50)->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('completed');
            $table->decimal('original_subtotal', 12, 2)->nullable();
            $table->decimal('original_discount', 12, 2)->nullable();
            $table->decimal('original_tax', 12, 2)->nullable();
            $table->decimal('original_total', 12, 2)->nullable();
            $table->decimal('original_paid_amount', 12, 2)->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->string('cancel_reason_code', 50)->nullable();
            $table->text('cancel_notes')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->unsignedBigInteger('edited_by')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('sold_at');
            $table->timestamps();
        });

        Schema::create('medicine_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('medicine_sale_invoice_id')->nullable();
            $table->string('type');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->decimal('stock_before', 12, 2)->nullable();
            $table->decimal('stock_after', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
        });

        Schema::create('invoice_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('returned_by_user_id');
            $table->string('reason_code', 50);
            $table->text('notes')->nullable();
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_return_id');
            $table->unsignedBigInteger('medicine_transaction_id')->nullable();
            $table->unsignedBigInteger('medicine_id');
            $table->decimal('quantity_returned', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 12, 2);
            $table->string('reason_code', 50)->nullable();
            $table->timestamps();
        });

        $this->clinic = Clinic::create([
            'tenant_id' => 'T1',
            'name' => 'Test Clinic',
            'enabled_modules' => ['medicines', 'finance'],
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->pharmacist = User::create([
            'username' => 'pharmacist1',
            'email' => 'pharmacist1@example.com',
            'password' => Hash::make('password'),
            'first_name' => 'Pat',
            'last_name' => 'Pharm',
            'role' => 'pharmacist',
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

    /**
     * Creates a 2-line sale invoice (10 units @ $5, 4 units @ $10) and
     * decrements stock exactly like MedicineSaleController::store().
     */
    private function createSaleInvoice(): MedicineSaleInvoice
    {
        $medA = Medicine::create([
            'clinic_id' => $this->clinic->id, 'created_by' => $this->pharmacist->id,
            'name' => 'Paracetamol', 'form' => 'tablet', 'stock_quantity' => 40, 'is_active' => true,
        ]);
        $medB = Medicine::create([
            'clinic_id' => $this->clinic->id, 'created_by' => $this->pharmacist->id,
            'name' => 'Ibuprofen', 'form' => 'tablet', 'stock_quantity' => 20, 'is_active' => true,
        ]);

        $invoice = MedicineSaleInvoice::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $this->pharmacist->id,
            'patient_id' => $this->patient->id,
            'invoice_number' => MedicineSaleInvoice::generateInvoiceNumber($this->clinic->id),
            'payment_method' => 'cash',
            'subtotal' => 90,
            'discount' => 0,
            'tax' => 0,
            'total' => 90,
            'paid_amount' => 90,
            'sold_at' => now(),
        ]);

        MedicineTransaction::create([
            'medicine_id' => $medA->id, 'clinic_id' => $this->clinic->id, 'user_id' => $this->pharmacist->id,
            'medicine_sale_invoice_id' => $invoice->id, 'type' => 'sale', 'quantity' => 10, 'unit_price' => 5,
            'total_amount' => 50, 'stock_before' => 40, 'stock_after' => 30, 'transaction_date' => now(),
        ]);
        MedicineTransaction::create([
            'medicine_id' => $medB->id, 'clinic_id' => $this->clinic->id, 'user_id' => $this->pharmacist->id,
            'medicine_sale_invoice_id' => $invoice->id, 'type' => 'sale', 'quantity' => 4, 'unit_price' => 10,
            'total_amount' => 40, 'stock_before' => 20, 'stock_after' => 16, 'transaction_date' => now(),
        ]);

        $medA->decrement('stock_quantity', 10);
        $medB->decrement('stock_quantity', 4);

        return $invoice->fresh();
    }

    public function test_partial_return_restocks_medicine_and_credits_invoice(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();

        $response = $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            [
                'reason_code' => 'expired_medication',
                'notes' => 'Expired batch',
                'items' => [
                    ['transaction_id' => $lineA->id, 'quantity' => 3],
                ],
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $medA = Medicine::where('name', 'Paracetamol')->first();
        $this->assertEquals(33, (float) $medA->stock_quantity); // 30 + 3 returned

        $invoice->refresh();
        $this->assertEquals(75, (float) $invoice->subtotal); // 90 - 15
        $this->assertEquals(75, (float) $invoice->total);
        $this->assertEquals(75, (float) $invoice->paid_amount);
        $this->assertEquals('partially_returned', $invoice->status);
        $this->assertEquals(90, (float) $invoice->original_total);

        $this->assertDatabaseHas('invoice_returns', [
            'invoice_id' => $invoice->id,
            'reason_code' => 'expired_medication',
            'refund_amount' => 15,
        ]);
        $this->assertDatabaseHas('return_items', [
            'medicine_transaction_id' => $lineA->id,
            'quantity_returned' => 3,
            'line_total' => 15,
        ]);
    }

    public function test_return_quantity_cannot_exceed_remaining_sold_quantity(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->first();

        $response = $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            [
                'reason_code' => 'defective_product',
                'items' => [
                    ['transaction_id' => $lineA->id, 'quantity' => 999],
                ],
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);

        $invoice->refresh();
        $this->assertEquals('completed', $invoice->status);
        $this->assertEquals(90, (float) $invoice->total); // unchanged
        $this->assertDatabaseCount('invoice_returns', 0);
    }

    public function test_second_return_cannot_exceed_whats_left_after_first_return(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();

        // First return: 6 of 10.
        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 6]]]
        )->assertOk();

        // Second return attempts 5 more (only 4 remain) -> must fail.
        $response = $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 5]]]
        );

        $response->assertStatus(422);
    }

    public function test_edit_invoice_adjusts_stock_by_quantity_delta(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();
        $lineB = $invoice->items()->where('medicine_id', Medicine::where('name', 'Ibuprofen')->first()->id)->first();

        // Increase line A from 10 to 12 (deducts 2 more stock).
        // Decrease line B from 4 to 1 (reverts 3 stock).
        $response = $this->actingAs($this->pharmacist)->putJson(
            route('medicines.sales.update', $invoice),
            [
                'items' => [
                    ['transaction_id' => $lineA->id, 'quantity' => 12, 'unit_price' => 5],
                    ['transaction_id' => $lineB->id, 'quantity' => 1, 'unit_price' => 10],
                ],
                'discount' => 0,
                'tax' => 0,
                'paid_amount' => 70,
            ]
        );

        $response->assertOk();

        $medA = Medicine::where('name', 'Paracetamol')->first();
        $medB = Medicine::where('name', 'Ibuprofen')->first();
        $this->assertEquals(28, (float) $medA->stock_quantity); // 30 - 2
        $this->assertEquals(19, (float) $medB->stock_quantity); // 16 + 3

        $invoice->refresh();
        $this->assertEquals(70, (float) $invoice->subtotal); // 12*5 + 1*10
        $this->assertEquals(70, (float) $invoice->total);
        $this->assertEquals('edited', $invoice->status);
        $this->assertEquals(90, (float) $invoice->original_total);
    }

    public function test_edit_invoice_rejects_increase_beyond_available_stock(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();

        $response = $this->actingAs($this->pharmacist)->putJson(
            route('medicines.sales.update', $invoice),
            [
                'items' => [
                    ['transaction_id' => $lineA->id, 'quantity' => 1000, 'unit_price' => 5],
                ],
            ]
        );

        $response->assertStatus(422);

        $medA = Medicine::where('name', 'Paracetamol')->first();
        $this->assertEquals(30, (float) $medA->stock_quantity); // unchanged
    }

    public function test_setting_edit_quantity_to_zero_removes_the_line_and_fully_reverts_stock(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();
        $lineB = $invoice->items()->where('medicine_id', Medicine::where('name', 'Ibuprofen')->first()->id)->first();

        $this->actingAs($this->pharmacist)->putJson(
            route('medicines.sales.update', $invoice),
            [
                'items' => [
                    ['transaction_id' => $lineA->id, 'quantity' => 0, 'unit_price' => 5],
                    ['transaction_id' => $lineB->id, 'quantity' => 4, 'unit_price' => 10],
                ],
            ]
        )->assertOk();

        $medA = Medicine::where('name', 'Paracetamol')->first();
        $this->assertEquals(40, (float) $medA->stock_quantity); // fully reverted
        $this->assertDatabaseMissing('medicine_transactions', ['id' => $lineA->id]);

        $invoice->refresh();
        $this->assertEquals(40, (float) $invoice->subtotal); // only line B remains
    }

    public function test_cancel_invoice_restores_remaining_stock_and_zeroes_financials(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();

        // Partially return 3 of line A first, then cancel the rest.
        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 3]]]
        )->assertOk();

        $response = $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.cancel', $invoice),
            ['reason_code' => 'patient_cancelled_refused', 'notes' => 'Refused service']
        );

        $response->assertOk();

        $medA = Medicine::where('name', 'Paracetamol')->first(); // 30 (post-sale) + 3 (returned) + 7 (remaining cancelled) = 40
        $medB = Medicine::where('name', 'Ibuprofen')->first();   // fully restored: 16 + 4 = 20
        $this->assertEquals(40, (float) $medA->stock_quantity);
        $this->assertEquals(20, (float) $medB->stock_quantity);

        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->total);
        $this->assertEquals(0, (float) $invoice->paid_amount);
        $this->assertEquals('cancelled', $invoice->status);
        $this->assertEquals('patient_cancelled_refused', $invoice->cancel_reason_code);
    }

    public function test_cancel_invoice_requires_a_reason(): void
    {
        $invoice = $this->createSaleInvoice();

        $response = $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.cancel', $invoice),
            []
        );

        $response->assertStatus(422);

        $invoice->refresh();
        $this->assertEquals('completed', $invoice->status);
    }

    public function test_cancelled_invoice_cannot_be_cancelled_or_returned_against_again(): void
    {
        $invoice = $this->createSaleInvoice();

        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.cancel', $invoice),
            ['reason_code' => 'other']
        )->assertOk();

        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.cancel', $invoice),
            ['reason_code' => 'other']
        )->assertStatus(422);

        $lineA = $invoice->items()->first();
        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 1]]]
        )->assertStatus(422);
    }

    public function test_doctor_cannot_cancel_or_return_or_edit_a_sale_invoice(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->first();

        $this->actingAs($this->doctor)->postJson(
            route('medicines.sales.cancel', $invoice),
            ['reason_code' => 'other']
        )->assertForbidden();

        $this->actingAs($this->doctor)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 1]]]
        )->assertForbidden();

        $this->actingAs($this->doctor)->putJson(
            route('medicines.sales.update', $invoice),
            ['items' => [['transaction_id' => $lineA->id, 'quantity' => 9, 'unit_price' => 5]]]
        )->assertForbidden();
    }

    public function test_items_json_endpoint_returns_remaining_returnable_quantities(): void
    {
        $invoice = $this->createSaleInvoice();
        $lineA = $invoice->items()->where('medicine_id', Medicine::where('name', 'Paracetamol')->first()->id)->first();

        $this->actingAs($this->pharmacist)->postJson(
            route('medicines.sales.returns.store', $invoice),
            ['reason_code' => 'defective_product', 'items' => [['transaction_id' => $lineA->id, 'quantity' => 4]]]
        )->assertOk();

        $response = $this->actingAs($this->pharmacist)->getJson(route('medicines.sales.items', $invoice));

        $response->assertOk();
        $response->assertJsonFragment(['transaction_id' => $lineA->id, 'remaining_returnable' => 6.0]);
    }
}
