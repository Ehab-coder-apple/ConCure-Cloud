<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminPermissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([AuditMiddleware::class, ActivationMiddleware::class, SetLocale::class, SetClinicTimezone::class]);

        foreach (['clinic_super_admin', 'users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->json('enabled_modules')->nullable();
            $table->json('settings')->nullable();
            $table->integer('max_users')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('title_prefix')->nullable();
            $table->string('scientific_degree')->nullable();
            $table->string('educational_institution')->nullable();
            $table->string('role')->nullable();
            $table->foreignId('clinic_id')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('permissions')->nullable();
            $table->json('metadata')->nullable();
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

    public function test_scoped_super_admin_without_permitted_ent_module_is_blocked(): void
    {
        $clinic = Clinic::create(['name' => 'ENT Clinic', 'email' => 'ent@test.local', 'enabled_modules' => ['patients', 'ent']]);
        $user = $this->makeScopedSuperAdmin(['patients']);
        $user->superAdminClinics()->attach($clinic->id);

        $this->actingAs($user)
            ->get(route('ent.index'))
            ->assertForbidden();
    }

    public function test_scoped_super_admin_without_finance_module_is_blocked_from_finance_routes(): void
    {
        $clinic = Clinic::create(['name' => 'Finance Clinic', 'email' => 'finance@test.local', 'enabled_modules' => ['finance']]);
        $user = $this->makeScopedSuperAdmin(['patients']);
        $user->superAdminClinics()->attach($clinic->id);

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertForbidden();
    }

    public function test_scoped_super_admin_cannot_create_users_beyond_managed_user_limit(): void
    {
        $clinic = Clinic::create(['name' => 'Users Clinic', 'email' => 'users@test.local', 'enabled_modules' => ['patients']]);
        $user = $this->makeScopedSuperAdmin(['patients'], 1);
        $user->superAdminClinics()->attach($clinic->id);

        User::create([
            'username' => 'existing_doctor',
            'email' => 'existing_doctor@test.local',
            'password' => 'secret',
            'first_name' => 'Existing',
            'last_name' => 'Doctor',
            'role' => 'doctor',
            'clinic_id' => $clinic->id,
            'created_by' => $user->id,
            'is_active' => true,
            'activated_at' => now(),
            'language' => 'en',
        ]);

        $response = $this->actingAs($user)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'first_name' => 'Blocked',
                'last_name' => 'User',
                'username' => 'blocked_user',
                'email' => 'blocked_user@test.local',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'role' => 'doctor',
                'clinic_id' => $clinic->id,
                'language' => 'en',
            ]);

        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors('clinic_id');
        $this->assertSame(1, User::where('created_by', $user->id)->where('clinic_id', $clinic->id)->count());
    }

    private function makeScopedSuperAdmin(array $permittedModules, int $userLimit = 1): User
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
            'metadata' => [
                User::METADATA_PERMITTED_MODULES => $permittedModules,
                User::METADATA_MANAGED_USER_LIMIT => $userLimit,
            ],
        ]);
    }
}