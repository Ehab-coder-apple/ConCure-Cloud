<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\MasterInvoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminClinicScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['master_invoices', 'patients', 'clinic_super_admin', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tenant_id')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('role')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('clinic_super_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('clinic_id');
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->nullable();
            $table->foreignId('clinic_id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        Schema::create('master_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->foreignId('clinic_id');
            $table->string('currency')->default('USD');
            $table->date('invoice_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function test_scoped_super_admin_only_sees_assigned_clinic_records(): void
    {
        [$clinicA, $clinicB] = $this->makeClinics();
        $user = $this->makeUser('master_admin');
        $user->superAdminClinics()->attach($clinicA->id);

        Patient::create(['patient_id' => 'P-1', 'clinic_id' => $clinicA->id, 'first_name' => 'Allowed']);
        Patient::create(['patient_id' => 'P-2', 'clinic_id' => $clinicB->id, 'first_name' => 'Blocked']);
        MasterInvoice::create(['clinic_id' => $clinicA->id, 'subtotal' => 100, 'status' => 'draft']);
        MasterInvoice::create(['clinic_id' => $clinicB->id, 'subtotal' => 200, 'status' => 'draft']);

        $this->actingAs($user);

        $this->assertSame([$clinicA->id], $user->accessibleClinicIds());
        $this->assertTrue($user->canAccessClinic($clinicA->id));
        $this->assertFalse($user->canAccessClinic($clinicB->id));
        $this->assertSame(['Allowed'], Patient::orderBy('id')->pluck('first_name')->all());
        $this->assertSame([$clinicA->id], MasterInvoice::orderBy('id')->pluck('clinic_id')->all());
    }

    public function test_global_master_admin_retains_access_to_all_clinics(): void
    {
        [$clinicA, $clinicB] = $this->makeClinics();
        $user = $this->makeUser('super_admin');

        Patient::create(['patient_id' => 'P-1', 'clinic_id' => $clinicA->id, 'first_name' => 'First']);
        Patient::create(['patient_id' => 'P-2', 'clinic_id' => $clinicB->id, 'first_name' => 'Second']);

        $this->actingAs($user);

        $this->assertTrue($user->canAccessClinic($clinicA->id));
        $this->assertTrue($user->canAccessClinic($clinicB->id));
        $this->assertCount(2, Patient::all());
    }

    private function makeClinics(): array
    {
        return [
            Clinic::create(['name' => 'Clinic A', 'tenant_id' => 'TEN-A']),
            Clinic::create(['name' => 'Clinic B', 'tenant_id' => 'TEN-B']),
        ];
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'username' => $role . '_user',
            'email' => $role . '@example.test',
            'password' => 'secret',
            'first_name' => 'Scoped',
            'last_name' => 'Admin',
            'role' => $role,
            'is_active' => true,
            'activated_at' => now(),
        ]);
    }
}
