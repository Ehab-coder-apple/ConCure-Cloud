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
            $table->date('date_of_birth');
            $table->string('gender');
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
    }

    public function test_import_template_headers_include_previous_visit_date(): void
    {
        $headers = PatientsImport::getExpectedHeaders();

        $this->assertArrayHasKey('previous_visit_date', $headers);
        $this->assertSame('Previous Visit Date (YYYY-MM-DD, optional)', $headers['previous_visit_date']);
    }
}