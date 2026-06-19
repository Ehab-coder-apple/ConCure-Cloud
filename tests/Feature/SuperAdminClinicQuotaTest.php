<?php

namespace Tests\Feature;

use App\Http\Controllers\Master\ClinicController;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminClinicQuotaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        foreach (['clinic_super_admin', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->integer('max_users')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
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
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->json('permissions')->nullable();
            $table->string('language')->nullable();
            $table->timestamps();
        });

        Schema::create('clinic_super_admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('clinic_id');
            $table->timestamps();
        });
    }

    public function test_scoped_super_admin_quota_is_consumed_by_new_clinic_creation(): void
    {
        $allocatedClinic = Clinic::create(['name' => 'Allocated', 'email' => 'allocated@test.local']);
        $user = $this->makeScopedSuperAdmin(1);
        $user->superAdminClinics()->attach($allocatedClinic->id);

        $response = $this->actingAs($user)->post(route('master.clinics.store'), [
            'name' => 'Created Clinic',
            'email' => 'created@test.local',
            'max_users' => 15,
            'admin_first_name' => 'Clinic',
            'admin_last_name' => 'Admin',
            'admin_email' => 'clinic-admin@test.local',
            'admin_password' => 'secret123',
        ]);

        $response->assertRedirect(route('master.clinics.index'));

        $createdClinic = Clinic::where('email', 'created@test.local')->firstOrFail();
        $user->refresh();

        $this->assertTrue($user->canAccessClinic($allocatedClinic->id));
        $this->assertTrue($user->canAccessClinic($createdClinic->id));
        $this->assertSame($user->id, data_get($createdClinic->settings, User::CLINIC_SETTINGS_SCOPED_OWNER_ID));
        $this->assertTrue($user->ownsManagedClinic($createdClinic));
        $this->assertFalse($user->ownsManagedClinic($allocatedClinic));
        $this->assertSame(1, $user->createdManagedClinicsCount());
        $this->assertSame(0, $user->remainingManagedClinicCreationSlots());
        $this->assertFalse($user->canCreateManagedClinic());
    }

    public function test_scoped_super_admin_helper_reports_remaining_slots_from_metadata(): void
    {
        $user = $this->makeScopedSuperAdmin(2);
        $ownedClinic = Clinic::create([
            'name' => 'Owned',
            'email' => 'owned@test.local',
            'settings' => [User::CLINIC_SETTINGS_SCOPED_OWNER_ID => $user->id],
        ]);

        $this->assertSame(2, $user->getManagedClinicCreationLimit());
        $this->assertSame([$ownedClinic->id], $user->createdManagedClinicIds());
        $this->assertSame(1, $user->remainingManagedClinicCreationSlots());
        $this->assertTrue($user->canCreateManagedClinic());
    }

    private function makeScopedSuperAdmin(int $limit): User
    {
        return User::create([
            'username' => 'scoped_' . uniqid(),
            'email' => uniqid('scoped_', true) . '@example.test',
            'password' => 'secret',
            'first_name' => 'Scoped',
            'last_name' => 'Admin',
            'role' => 'master_admin',
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
            'metadata' => [User::METADATA_MANAGED_CLINIC_LIMIT => $limit],
        ]);
    }
}
