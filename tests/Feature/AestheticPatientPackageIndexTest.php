<?php

namespace Tests\Feature;

use App\Http\Controllers\Aesthetic\AestheticPatientPackageController;
use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\AestheticPackage;
use App\Models\AestheticTreatment;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Tests\TestCase;

class AestheticPatientPackageIndexTest extends TestCase
{
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

        Schema::dropIfExists('patient_packages');
        Schema::dropIfExists('aesthetic_package_treatment');
        Schema::dropIfExists('aesthetic_packages');
        Schema::dropIfExists('aesthetic_treatments');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
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
            $table->string('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('aesthetic_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('category');
            $table->decimal('default_price', 10, 2);
            $table->boolean('session_required')->default(false);
            $table->integer('sessions_count')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->unsignedBigInteger('treatment_id');
            $table->unsignedInteger('total_sessions');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('aesthetic_package_treatment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('treatment_id');
            $table->timestamps();
        });

        Schema::create('patient_packages', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('package_id');
            $table->unsignedInteger('sessions_used')->default(0);
            $table->unsignedInteger('sessions_remaining');
            $table->date('purchase_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_index_keeps_soft_deleted_packages_available_for_listing(): void
    {
        $clinic = Clinic::create([
            'name' => 'Dr Omer El Bazi Clinic',
            'tenant_id' => 'TEN-OMER-1',
        ]);

        $user = User::create([
            'username' => 'omer_admin',
            'email' => 'omer-admin@example.test',
            'password' => 'secret',
            'first_name' => 'Omer',
            'last_name' => 'Admin',
            'role' => 'admin',
            'clinic_id' => $clinic->id,
        ]);

        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => 'Sara',
            'last_name' => 'Patient',
            'phone' => '123456789',
        ]);

        $treatment = AestheticTreatment::create([
            'tenant_id' => $clinic->tenant_id,
            'name' => 'Hydra Facial',
            'category' => 'skincare',
            'default_price' => 120,
            'session_required' => true,
            'sessions_count' => 3,
            'is_active' => true,
        ]);

        $package = AestheticPackage::create([
            'tenant_id' => $clinic->tenant_id,
            'name' => 'Glow Package',
            'treatment_id' => $treatment->id,
            'total_sessions' => 3,
            'price' => 300,
            'discount' => 0,
        ]);

        PatientPackage::create([
            'tenant_id' => $clinic->tenant_id,
            'patient_id' => $patient->id,
            'package_id' => $package->id,
            'sessions_used' => 1,
            'sessions_remaining' => 2,
            'purchase_date' => now()->toDateString(),
        ]);

        $package->delete();

        $this->actingAs($user);

        $response = app(AestheticPatientPackageController::class)->index(new Request());

        $this->assertInstanceOf(View::class, $response);

        $packages = $response->getData()['packages'];
        $listedPackage = $packages->getCollection()->first();

        $this->assertInstanceOf(PatientPackage::class, $listedPackage);
        $this->assertNotNull($listedPackage->package);
        $this->assertTrue($listedPackage->package->trashed());
        $this->assertSame('Glow Package', $listedPackage->package->name);
    }
}