<?php

namespace Tests\Feature;

use App\Imports\PatientsImport;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientCheckup;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PatientsImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('patient_checkups');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('activation_code')->unique();
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
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('job')->nullable();
            $table->string('education')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('bmi', 6, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->boolean('is_pregnant')->default(false);
            $table->text('chronic_illnesses')->nullable();
            $table->text('surgeries_history')->nullable();
            $table->text('diet_history')->nullable();
            $table->text('notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('created_by');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('patient_checkups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('recorded_by');
            $table->timestamp('checkup_date');
            $table->timestamps();
        });
    }

    public function test_import_creates_a_historical_checkup_from_previous_visit_date(): void
    {
        $clinic = Clinic::create([
            'name' => 'Timeline Clinic',
            'activation_code' => 'timeline-clinic-code',
        ]);

        $user = User::create([
            'username' => 'importer',
            'email' => 'importer@example.com',
            'password' => 'password',
            'first_name' => 'Import',
            'last_name' => 'User',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $import = new PatientsImport();
        $import->collection(collect([
            collect([
                'first_name' => 'Layla',
                'last_name' => 'Nasser',
                'date_of_birth' => '1992-05-10',
                'previous_visit_date' => '2026-02-15',
                'gender' => 'female',
                'phone' => '+9647501112222',
            ]),
        ]));

        $patient = Patient::where('first_name', 'Layla')->where('last_name', 'Nasser')->first();

        $this->assertNotNull($patient);
        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(0, $import->getSkippedCount());
        $this->assertDatabaseHas('patient_checkups', [
            'patient_id' => $patient->id,
            'recorded_by' => $user->id,
        ]);
        $this->assertTrue(
            PatientCheckup::where('patient_id', $patient->id)
                ->whereDate('checkup_date', '2026-02-15')
                ->exists()
        );
        $this->assertSame('2026-02-15', optional($patient->fresh()->last_visit_date)->format('Y-m-d'));
    }

    public function test_import_template_headers_include_previous_visit_date(): void
    {
        $headers = PatientsImport::getExpectedHeaders();

        $this->assertArrayHasKey('previous_visit_date', $headers);
        $this->assertSame('Previous Visit Date (YYYY-MM-DD, optional)', $headers['previous_visit_date']);
    }

    public function test_import_accepts_common_excel_style_day_month_year_dates(): void
    {
        $clinic = Clinic::create([
            'name' => 'Excel Dates Clinic',
            'activation_code' => 'excel-dates-clinic-code',
        ]);

        $user = User::create([
            'username' => 'excel_importer',
            'email' => 'excel-importer@example.com',
            'password' => 'password',
            'first_name' => 'Excel',
            'last_name' => 'Importer',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $import = new PatientsImport();
        $import->collection(collect([
            collect([
                'first_name' => 'Dana',
                'last_name' => 'Kareem',
                'date_of_birth' => '10/05/1992',
                'previous_visit_date' => '26/02/2013',
                'gender' => 'female',
            ]),
        ]));

        $patient = Patient::where('first_name', 'Dana')->where('last_name', 'Kareem')->first();

        $this->assertNotNull($patient);
        $this->assertSame('1992-05-10', optional($patient->date_of_birth)->format('Y-m-d'));
        $this->assertTrue(
            PatientCheckup::where('patient_id', $patient->id)
                ->whereDate('checkup_date', '2013-02-26')
                ->exists()
        );
        $this->assertSame([], $import->getErrors());
    }

    public function test_import_skips_legacy_template_instruction_row(): void
    {
        $clinic = Clinic::create([
            'name' => 'Legacy Template Clinic',
            'activation_code' => 'legacy-template-clinic-code',
        ]);

        $user = User::create([
            'username' => 'legacy_importer',
            'email' => 'legacy-importer@example.com',
            'password' => 'password',
            'first_name' => 'Legacy',
            'last_name' => 'Importer',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $import = new PatientsImport();
        $import->collection(collect([
            collect(PatientsImport::getExpectedHeaders()),
            collect([
                'first_name' => 'Sara',
                'last_name' => 'Yousef',
                'date_of_birth' => '1991-01-10',
                'gender' => 'female',
                'phone' => '+9647505551111',
            ]),
        ]));

        $patient = Patient::where('first_name', 'Sara')->where('last_name', 'Yousef')->first();

        $this->assertNotNull($patient);
        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(0, $import->getSkippedCount());
        $this->assertSame([], $import->getErrors());
    }

    public function test_import_skips_older_instruction_row_with_slightly_different_labels(): void
    {
        $clinic = Clinic::create([
            'name' => 'Older Template Clinic',
            'activation_code' => 'older-template-clinic-code',
        ]);

        $user = User::create([
            'username' => 'older_template_importer',
            'email' => 'older-template-importer@example.com',
            'password' => 'password',
            'first_name' => 'Older',
            'last_name' => 'Importer',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $import = new PatientsImport();
        $import->collection(collect([
            collect([
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'date_of_birth' => 'Date of Birth (YYYY-MM-DD)',
                'previous_visit_date' => 'Previous Visit Date (YYYY-MM-DD, optional)',
                'gender' => 'Gender (male/female/other)',
                'phone' => 'Phone Number',
                'whatsapp_phone' => 'WhatsApp Phone',
                'email' => 'Email Address',
            ]),
            collect([
                'first_name' => 'Noor',
                'last_name' => 'Ali',
                'date_of_birth' => '10/05/1992',
                'previous_visit_date' => '26/02/2013',
                'gender' => 'female',
                'email' => 'noor.ali@example.com',
            ]),
        ]));

        $patient = Patient::where('first_name', 'Noor')->where('last_name', 'Ali')->first();

        $this->assertNotNull($patient);
        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(0, $import->getSkippedCount());
        $this->assertSame([], $import->getErrors());
        $this->assertSame([], $import->getWarnings());
        $this->assertTrue(
            PatientCheckup::where('patient_id', $patient->id)
                ->whereDate('checkup_date', '2013-02-26')
                ->exists()
        );
    }

    public function test_import_keeps_missing_dob_and_gender_empty_instead_of_faking_values(): void
    {
        $clinic = Clinic::create([
            'name' => 'Nullable Patient Fields Clinic',
            'activation_code' => 'nullable-patient-fields-clinic-code',
        ]);

        $user = User::create([
            'username' => 'nullable_importer',
            'email' => 'nullable-importer@example.com',
            'password' => 'password',
            'first_name' => 'Nullable',
            'last_name' => 'Importer',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $import = new PatientsImport();
        $import->collection(collect([
            collect([
                'first_name' => 'Zainab',
                'last_name' => 'Mahmood',
                'date_of_birth' => '',
                'gender' => '',
                'phone' => '+9647501113333',
            ]),
        ]));

        $patient = Patient::where('first_name', 'Zainab')->where('last_name', 'Mahmood')->first();

        $this->assertNotNull($patient);
        $this->assertNull($patient->getRawOriginal('date_of_birth'));
        $this->assertNull($patient->getRawOriginal('gender'));
        $this->assertNull($patient->age_formatted);
        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame(0, $import->getSkippedCount());
    }
}