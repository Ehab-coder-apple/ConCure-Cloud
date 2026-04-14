<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EntModuleAccessControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([AuditMiddleware::class, ActivationMiddleware::class, SetLocale::class, SetClinicTimezone::class]);

        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->json('settings')->nullable();
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
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->timestamps();
        });
    }

    public function test_disabled_clinic_cannot_open_ent_routes_even_if_patients_module_is_enabled(): void
    {
        $clinic = Clinic::create([
            'name' => 'Patients Only Clinic',
            'is_active' => true,
            'activated_at' => now(),
            'enabled_modules' => ['patients'],
        ]);

        $suffix = uniqid();

        $user = User::create([
            'username' => 'ent_guard_' . $suffix,
            'email' => 'ent_guard_' . $suffix . '@example.test',
            'password' => 'secret',
            'first_name' => 'ENT',
            'last_name' => 'Guard',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-ENT-1',
            'first_name' => 'Mina',
            'last_name' => 'Test',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $this->get(route('patients.ent.show', ['patient' => $patient->id]))->assertForbidden();
        $this->get(route('patient.ent', ['patient' => $patient->id]))->assertForbidden();
        $this->get(route('ent.index'))->assertForbidden();
    }

    public function test_staff_without_ent_permission_cannot_open_ent_routes_when_clinic_has_ent_enabled(): void
    {
        $clinic = Clinic::create([
            'name' => 'ENT Enabled Clinic',
            'is_active' => true,
            'activated_at' => now(),
            'enabled_modules' => ['patients', 'ent'],
        ]);

        $suffix = uniqid();

        $user = User::create([
            'username' => 'ent_staff_' . $suffix,
            'email' => 'ent_staff_' . $suffix . '@example.test',
            'password' => 'secret',
            'first_name' => 'No',
            'last_name' => 'EntAccess',
            'role' => 'assistant',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
            'permissions' => ['patients_view'],
        ]);

        $patient = Patient::create([
            'patient_id' => 'P-ENT-2',
            'first_name' => 'Sara',
            'last_name' => 'Test',
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);

        $this->get(route('patients.ent.show', ['patient' => $patient->id]))->assertForbidden();
        $this->get(route('patient.ent', ['patient' => $patient->id]))->assertForbidden();
        $this->get(route('ent.index'))->assertForbidden();
    }
}