<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\SimplePrescription;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Tests\TestCase;

class QuickVisitPrescriptionTest extends TestCase
{
    private Clinic $clinic;
    private User $doctor;
    private User $secondDoctor;
    private User $assistant;
    private Patient $patient;

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

        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('simple_prescription_medicines');
        Schema::dropIfExists('simple_prescriptions');
        Schema::dropIfExists('medicine_forms');
        Schema::dropIfExists('medicines');
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
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('key');
            $table->string('label');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('simple_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->string('prescription_number')->unique();
            $table->text('diagnosis')->nullable();
            $table->string('visit_type', 30)->nullable();
            $table->text('notes')->nullable();
            $table->date('prescribed_date');
            $table->string('status')->default('active');
            $table->boolean('sent_to_doctor')->default(false);
            $table->timestamp('sent_to_doctor_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->boolean('is_dispensed')->default(false);
            $table->timestamp('dispensed_at')->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            $table->string('dispense_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('clinic_id');
            $table->string('source_module')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
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
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('item_type')->nullable();
            $table->timestamps();
        });

        Schema::create('simple_prescription_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->string('medicine_name');
            $table->string('type', 50)->nullable();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('transfers');
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('type')->default('direct');
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('message_type')->default('text');
            $table->text('body')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('transfer_type');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->string('status')->default('pending');
            $table->string('priority')->default('normal');
            $table->unsignedBigInteger('acted_by')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $this->clinic = Clinic::create([
            'name' => 'Quick Visit Test Clinic',
            'tenant_id' => 'TEN-QUICKVISIT',
            'enabled_modules' => ['prescriptions', 'quick_visit', 'messages'],
        ]);

        $this->doctor = User::create([
            'username' => 'quickvisit_doctor',
            'email' => 'quickvisit-doctor@example.test',
            'password' => 'secret',
            'first_name' => 'Quick',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => 'P-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => now()->subYears(30)->toDateString(),
            'gender' => 'female',
            'phone' => '07701234567',
        ]);

        $this->secondDoctor = User::create([
            'username' => 'quickvisit_doctor2',
            'email' => 'quickvisit-doctor2@example.test',
            'password' => bcrypt('secret'),
            'first_name' => 'Second',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->assistant = User::create([
            'username' => 'quickvisit_assistant',
            'email' => 'quickvisit-assistant@example.test',
            'password' => bcrypt('secret'),
            'first_name' => 'Quick',
            'last_name' => 'Assistant',
            'role' => 'assistant',
            'clinic_id' => $this->clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }

    public function test_quick_visit_page_renders_when_module_enabled(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit'));

        $response->assertOk();
        $response->assertViewIs('simple-prescriptions.quick-visit');
        $response->assertSee('Jane Doe', false);
        foreach (SimplePrescription::VISIT_TYPES as $label) {
            $response->assertSee($label, false);
        }

        // Regression guard: the page's inline <script> (which calls jQuery's
        // $(document).ready(...) and relies on window.bootstrap for the
        // "Add New Patient" modal) must be emitted via @push('scripts') so it
        // renders AFTER the jQuery/Bootstrap <script src> tags in the layout
        // footer. If it ever moves back into the body of the page, jQuery/
        // bootstrap won't be defined yet and the New Patient / Save & Print
        // buttons silently stop working.
        $html = $response->getContent();
        $jqueryPos = strpos($html, 'jquery-3.7.1.min.js');
        $bootstrapPos = strpos($html, 'bootstrap.bundle.min.js');
        $inlineScriptPos = strpos($html, 'qvMedicineRowCount');

        $this->assertNotFalse($jqueryPos, 'jQuery script tag not found in page.');
        $this->assertNotFalse($bootstrapPos, 'Bootstrap bundle script tag not found in page.');
        $this->assertNotFalse($inlineScriptPos, 'Quick Visit inline script not found in page.');
        $this->assertGreaterThan($jqueryPos, $inlineScriptPos, 'Quick Visit script must render after jQuery.');
        $this->assertGreaterThan($bootstrapPos, $inlineScriptPos, 'Quick Visit script must render after Bootstrap.');
    }

    public function test_diagnosis_and_history_fields_have_voice_typing_enabled(): void
    {
        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit'));

        $response->assertOk();
        $html = $response->getContent();

        // The Diagnosis/History textareas must sit inside a voice-enabled
        // scope so HCPs can dictate instead of typing.
        $this->assertStringContainsString('data-auto-voice-scope="quick-visit-notes"', $html);
        $this->assertStringContainsString('id="diagnosis"', $html);
        $this->assertStringContainsString('id="notes"', $html);

        $notesScopeStart = strpos($html, 'data-auto-voice-scope="quick-visit-notes"');
        $diagnosisFieldPos = strpos($html, 'id="diagnosis"');
        $notesFieldPos = strpos($html, 'id="notes"');
        $this->assertGreaterThan($notesScopeStart, $diagnosisFieldPos, 'Diagnosis field must be inside the voice-typing scope.');
        $this->assertGreaterThan($notesScopeStart, $notesFieldPos, 'History/Notes field must be inside the voice-typing scope.');

        // The voice-input JS engine that scans for data-auto-voice-scope must
        // actually be loaded on this page.
        $this->assertStringContainsString('const VoiceInput', $html);
    }

    public function test_quick_visit_page_is_blocked_when_module_disabled(): void
    {
        $this->clinic->update(['enabled_modules' => ['prescriptions']]);

        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit'));

        $response->assertStatus(403);
    }

    public function test_store_persists_visit_type_and_medicine_type_and_quantity(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Acute pharyngitis',
            'visit_type' => 'follow_up',
            'notes' => 'Patient improving',
            'medicines' => [
                [
                    'name' => 'new:Amoxicillin',
                    'type' => 'capsule',
                    'dosage' => '500mg',
                    'frequency' => 'BID',
                    'duration' => '7 days',
                    'quantity' => 14,
                ],
            ],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $this->assertSame('follow_up', $prescription->visit_type);

        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Amoxicillin',
            'type' => 'capsule',
            'quantity' => 14,
        ]);
    }

    public function test_store_with_print_after_redirects_to_print_route(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Common cold',
            'print_after' => '1',
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.print', $prescription->id));
    }

    public function test_store_with_print_after_and_custom_template_redirects_to_custom_pdf_route(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Common cold',
            'print_after' => '1',
            'print_template' => 'custom_pdf',
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.pdf', [$prescription->id, 'template' => 'custom']));
    }

    public function test_store_without_visit_type_or_medicine_extras_still_works_like_before(): void
    {
        // Regression check: the existing (non-quick-visit) create form never
        // sends visit_type/type/quantity - store() must still work exactly
        // as before for that payload shape.
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Regular flow diagnosis',
            'medicines' => [
                [
                    'name' => 'new:Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'TID',
                    'duration' => '3 days',
                ],
            ],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $this->assertNull($prescription->visit_type);

        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Paracetamol',
            'type' => null,
            'quantity' => null,
        ]);
    }

    public function test_quick_visit_page_lists_doctors_for_send_to_doctor_dropdown(): void
    {
        $response = $this->actingAs($this->assistant)->get(route('simple-prescriptions.quick-visit'));

        $response->assertOk();
        $response->assertSee('id="assigned_doctor_id"', false);
        $response->assertSee('Quick Doctor', false);
        $response->assertSee('Second Doctor', false);
    }

    public function test_assistant_can_send_visit_to_doctor(): void
    {
        $response = $this->actingAs($this->assistant)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Fever, needs doctor review',
            'send_to_doctor' => '1',
            'assigned_doctor_id' => $this->secondDoctor->id,
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        $response->assertRedirect(route('simple-prescriptions.quick-visit'));
        $response->assertSessionHas('success');

        $this->assertSame($this->secondDoctor->id, $prescription->doctor_id);
        $this->assertSame($this->assistant->id, $prescription->created_by);
        $this->assertTrue($prescription->sent_to_doctor);
        $this->assertNotNull($prescription->sent_to_doctor_at);
        $this->assertTrue($prescription->isPendingDoctorReview());

        // The assigned doctor can mark it reviewed, clearing it from the queue.
        $reviewResponse = $this->actingAs($this->secondDoctor)
            ->post(route('simple-prescriptions.mark-reviewed', $prescription->id));
        $reviewResponse->assertRedirect();

        $this->assertFalse($prescription->fresh()->isPendingDoctorReview());
    }

    public function test_send_to_doctor_requires_a_doctor_to_be_selected(): void
    {
        $response = $this->actingAs($this->assistant)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'send_to_doctor' => '1',
            'medicines' => [],
        ]);

        $response->assertSessionHasErrors('assigned_doctor_id');
        $this->assertDatabaseMissing('simple_prescriptions', ['patient_id' => $this->patient->id]);
    }

    public function test_direct_cost_creates_a_linked_invoice(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Routine checkup',
            'cost' => '25000',
            'payment_status' => 'paid',
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();
        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));

        $this->assertNotNull($prescription->invoice_id);

        $invoice = $prescription->invoice;
        $this->assertNotNull($invoice);
        $this->assertEquals(25000, (float) $invoice->total_amount);
        $this->assertEquals(25000, (float) $invoice->paid_amount);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('quick_visit', $invoice->source_module);
        $this->assertSame($this->patient->id, $invoice->patient_id);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'Quick Visit - Consultation & Prescription Fee',
        ]);
    }

    public function test_no_invoice_created_when_cost_is_omitted(): void
    {
        $response = $this->actingAs($this->doctor)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'No charge visit',
            'medicines' => [],
        ]);

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();
        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));

        $this->assertNull($prescription->invoice_id);
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_patient_history_endpoint_returns_previous_visits(): void
    {
        $olderVisit = SimplePrescription::create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'created_by' => $this->doctor->id,
            'clinic_id' => $this->clinic->id,
            'prescription_number' => SimplePrescription::generatePrescriptionNumber(),
            'diagnosis' => 'Low back pain',
            'visit_type' => 'follow_up',
            'notes' => 'Improving with treatment',
            'prescribed_date' => now()->subDays(10)->toDateString(),
            'status' => 'active',
        ]);
        $olderVisit->medicines()->create([
            'medicine_name' => 'Ibuprofen',
            'dosage' => '400mg',
        ]);

        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.patient-history', $this->patient->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $visits = $response->json('visits');
        $this->assertCount(1, $visits);
        $this->assertSame('Low back pain', $visits[0]['diagnosis']);
        $this->assertSame('Improving with treatment', $visits[0]['notes']);
        $this->assertStringContainsString('Ibuprofen', $visits[0]['treatment']);
        $this->assertSame('Follow-up', $visits[0]['visit_type']);
    }

    private function sendVisitToDoctor(): SimplePrescription
    {
        $this->actingAs($this->assistant)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'diagnosis' => 'Sore throat',
            'notes' => 'Started 2 days ago',
            'send_to_doctor' => '1',
            'assigned_doctor_id' => $this->doctor->id,
            'medicines' => [
                [
                    'name' => 'new:Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => 'TID',
                    'duration' => '3 days',
                ],
            ],
        ]);

        return SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();
    }

    public function test_reviewing_a_sent_visit_opens_the_editable_quick_visit_form_not_a_pdf(): void
    {
        $prescription = $this->sendVisitToDoctor();

        $response = $this->actingAs($this->doctor)
            ->get(route('simple-prescriptions.quick-visit.review', $prescription->id));

        $response->assertOk();
        // Must render the same editable Quick Visit template, not a
        // static report/PDF view.
        $response->assertViewIs('simple-prescriptions.quick-visit');
        $response->assertViewHas('prescription', function ($viewPrescription) use ($prescription) {
            return $viewPrescription->id === $prescription->id;
        });

        $html = $response->getContent();
        // Pre-filled editable fields, not read-only report markup.
        $this->assertStringContainsString('Sore throat', $html);
        $this->assertStringContainsString('Started 2 days ago', $html);
        $this->assertStringContainsString('Paracetamol', $html);
        $this->assertStringContainsString(route('simple-prescriptions.quick-visit.update', $prescription->id), $html);
        $this->assertStringContainsString('Complete Review', $html);
        // The patient is locked in (via a hidden field) rather than
        // re-selectable, and the Send-to-Doctor controls are gone.
        $this->assertStringContainsString('name="patient_id" value="' . $prescription->patient_id . '"', $html);
        $this->assertStringNotContainsString('id="sendToDoctorBtn"', $html);
    }

    public function test_only_the_assigned_doctor_or_admin_can_open_the_review_form(): void
    {
        $prescription = $this->sendVisitToDoctor();

        $response = $this->actingAs($this->secondDoctor)
            ->get(route('simple-prescriptions.quick-visit.review', $prescription->id));

        $response->assertStatus(403);
    }

    public function test_doctor_can_edit_and_complete_a_sent_visit_from_the_quick_visit_form(): void
    {
        $prescription = $this->sendVisitToDoctor();

        $response = $this->actingAs($this->doctor)
            ->put(route('simple-prescriptions.quick-visit.update', $prescription->id), [
                'diagnosis' => 'Acute tonsillitis (confirmed)',
                'visit_type' => 'follow_up',
                'notes' => 'Started 2 days ago; throat culture taken',
                'medicines' => [
                    [
                        'name' => 'new:Amoxicillin',
                        'type' => 'capsule',
                        'dosage' => '500mg',
                        'frequency' => 'BID',
                        'duration' => '7 days',
                        'quantity' => 14,
                    ],
                ],
            ]);

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));
        $response->assertSessionHas('success');

        $prescription->refresh();
        $this->assertSame('Acute tonsillitis (confirmed)', $prescription->diagnosis);
        $this->assertSame('follow_up', $prescription->visit_type);
        $this->assertNotNull($prescription->reviewed_at);
        $this->assertFalse($prescription->isPendingDoctorReview());

        // The old medicine (Paracetamol) was replaced by the doctor's edit.
        $this->assertDatabaseMissing('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Paracetamol',
        ]);
        $this->assertDatabaseHas('simple_prescription_medicines', [
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Amoxicillin',
            'type' => 'capsule',
            'quantity' => 14,
        ]);
    }

    public function test_completing_review_with_cost_creates_an_invoice_when_none_exists_yet(): void
    {
        $prescription = $this->sendVisitToDoctor();

        $response = $this->actingAs($this->doctor)
            ->put(route('simple-prescriptions.quick-visit.update', $prescription->id), [
                'diagnosis' => 'Acute tonsillitis',
                'cost' => '15000',
                'payment_status' => 'paid',
                'medicines' => [],
            ]);

        $response->assertRedirect(route('simple-prescriptions.show', $prescription->id));

        $prescription->refresh();
        $this->assertNotNull($prescription->invoice_id);
        $this->assertEquals(15000, (float) $prescription->invoice->total_amount);
        $this->assertSame('paid', $prescription->invoice->status);
    }

    public function test_only_the_assigned_doctor_or_admin_can_complete_the_review(): void
    {
        $prescription = $this->sendVisitToDoctor();

        $response = $this->actingAs($this->secondDoctor)
            ->put(route('simple-prescriptions.quick-visit.update', $prescription->id), [
                'diagnosis' => 'Hijacked edit attempt',
                'medicines' => [],
            ]);

        $response->assertStatus(403);
        $this->assertNotSame('Hijacked edit attempt', $prescription->fresh()->diagnosis);
    }

    public function test_sending_a_visit_to_doctor_delivers_it_as_a_message_not_just_a_prescription_flag(): void
    {
        $response = $this->actingAs($this->assistant)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'send_to_doctor' => '1',
            'assigned_doctor_id' => $this->doctor->id,
            // Assistant only entered the patient - nothing else yet.
            'medicines' => [],
        ]);

        $response->assertRedirect(route('simple-prescriptions.quick-visit'));

        $prescription = SimplePrescription::where('patient_id', $this->patient->id)->firstOrFail();

        // A real message (not just the sent_to_doctor flag) must exist so it
        // shows up as an alert in the doctor's Messages inbox.
        $this->assertDatabaseHas('messages', [
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'message_type' => 'transfer',
        ]);

        $message = \App\Models\Message::where('patient_id', $this->patient->id)->firstOrFail();
        $this->assertSame('Jane Doe', $message->metadata['patient_name']);
        $this->assertTrue($message->metadata['quick_visit']);

        // The doctor is a recipient and it's unread, driving the Messages
        // unread badge/alert.
        $this->assertDatabaseHas('message_recipients', [
            'message_id' => $message->id,
            'user_id' => $this->doctor->id,
            'read_at' => null,
        ]);

        // The transfer links straight back to this prescription so the
        // Messages page's "Preview" action can open the Quick Visit review
        // form for it.
        $this->assertDatabaseHas('transfers', [
            'clinic_id' => $this->clinic->id,
            'transfer_type' => 'prescription',
            'source_type' => SimplePrescription::class,
            'source_id' => $prescription->id,
            'status' => 'pending',
        ]);
    }

    public function test_no_message_is_sent_when_messages_module_is_disabled(): void
    {
        $this->clinic->update(['enabled_modules' => ['prescriptions', 'quick_visit']]);

        $this->actingAs($this->assistant)->post(route('simple-prescriptions.store'), [
            'patient_id' => $this->patient->id,
            'prescribed_date' => now()->toDateString(),
            'send_to_doctor' => '1',
            'assigned_doctor_id' => $this->doctor->id,
            'medicines' => [],
        ])->assertRedirect(route('simple-prescriptions.quick-visit'));

        // No message infrastructure required - the prescription's own
        // sent_to_doctor flag and the Prescriptions list "Review" button
        // remain the fallback path.
        $this->assertDatabaseCount('messages', 0);
        $this->assertDatabaseHas('simple_prescriptions', [
            'patient_id' => $this->patient->id,
            'sent_to_doctor' => true,
        ]);
    }
}
