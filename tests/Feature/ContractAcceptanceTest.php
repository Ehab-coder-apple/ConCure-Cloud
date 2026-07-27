<?php

namespace Tests\Feature;

use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\SetClinicTimezone;
use App\Models\Clinic;
use App\Models\ClinicContract;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContractAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([AuditMiddleware::class, SetClinicTimezone::class]);

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('clinic_contracts');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
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

        Schema::create('clinic_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('contract_type')->default('service_agreement');
            $table->string('contract_title')->nullable();
            $table->longText('contract_content');
            $table->decimal('annual_fee', 10, 2)->nullable();
            $table->integer('contract_duration_months')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('requires_renewal')->default(true);
            $table->string('status')->default('draft');
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger('accepted_by_user_id')->nullable();
            $table->string('acceptance_ip', 45)->nullable();
            $table->string('signature_name')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_tenant_can_accept_pending_contract_and_notification_is_stored(): void
    {
        $clinic = Clinic::create([
            'name' => 'Contract Clinic',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $creator = User::create([
            'username' => 'master_contract_creator',
            'email' => 'creator@example.test',
            'password' => 'secret',
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'role' => 'super_admin',
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $tenantUser = User::create([
            'username' => 'tenant_admin',
            'email' => 'tenant@example.test',
            'password' => 'secret',
            'first_name' => 'Tenant',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $contract = ClinicContract::create([
            'clinic_id' => $clinic->id,
            'contract_title' => 'ConCure Contract',
            'contract_content' => str_repeat('Contract terms ', 20),
            'status' => 'pending',
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($tenantUser)
            ->post(route('contract.accept'), [
                'signature_name' => 'Tenant Admin',
                'agree' => '1',
            ]);

        $response->assertRedirect(route('dashboard'));

        $contract->refresh();

        $this->assertSame('accepted', $contract->status);
        $this->assertSame($tenantUser->id, $contract->accepted_by_user_id);
        $this->assertSame('Tenant Admin', $contract->signature_name);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\\Notifications\\ContractAccepted',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $creator->id,
        ]);
    }
}