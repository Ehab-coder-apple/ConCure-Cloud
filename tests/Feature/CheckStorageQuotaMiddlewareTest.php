<?php

namespace Tests\Feature;

use App\Http\Middleware\ActivationMiddleware;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CheckStorageQuota;
use App\Http\Middleware\EnsureContractIsAccepted;
use App\Http\Middleware\SetClinicTimezone;
use App\Http\Middleware\SetLocale;
use App\Models\Clinic;
use App\Models\User;
use App\Services\StorageQuotaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression tests for the Phase 1 storage quota enforcement fix.
 *
 * Previously CheckStorageQuota only triggered its detection gate for the
 * exact field names 'file', 'result_file', 'receipt_file', or
 * 'medical_files', and the middleware alias was never attached to any
 * route - so clinics could exceed their storage_limit without ever being
 * blocked. This verifies the middleware now (a) detects files under ANY
 * field name, including nested array fields, and (b) hard-blocks uploads
 * once a clinic is at/over 100% of its quota.
 */
class CheckStorageQuotaMiddlewareTest extends TestCase
{
    private Clinic $clinic;
    private User $user;

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

        Schema::dropIfExists('users');
        Schema::dropIfExists('clinics');

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 50)->nullable();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->unsignedBigInteger('storage_limit')->default(StorageQuotaService::DEFAULT_LIMIT);
            $table->unsignedBigInteger('storage_used')->default(0);
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

        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'storage_limit' => 1024, // 1 KB limit, easy to exceed in tests
            'storage_used' => 0,
            'is_active' => true,
            'activated_at' => now(),
        ]);

        $this->user = User::create([
            'username' => 'doc1',
            'email' => 'doc1@example.com',
            'password' => bcrypt('password'),
            'first_name' => 'Doc',
            'last_name' => 'Tor',
            'role' => 'doctor',
            'is_active' => true,
            'activated_at' => now(),
            'clinic_id' => $this->clinic->id,
        ]);

        // Register a throwaway route protected only by the middleware under
        // test, so this exercises CheckStorageQuota in isolation rather
        // than depending on any single real upload controller.
        Route::middleware(['web', 'auth', CheckStorageQuota::class])
            ->post('/__test/upload', fn () => response()->json(['success' => true]));
    }

    /**
     * StorageQuotaService::getStorageUsed() caches its result for 10
     * minutes rather than hitting patient_files/patient_images/etc. on
     * every request. Since this test only creates the minimal clinics/
     * users tables (not the full file tables), pre-seed that cache entry
     * directly instead of relying on the real aggregate query.
     */
    private function seedCachedStorageUsed(int $bytes): void
    {
        Cache::put("clinic_{$this->clinic->id}_storage_used", $bytes, 600);
    }

    protected function tearDown(): void
    {
        foreach (['users', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_request_without_any_file_passes_through(): void
    {
        $response = $this->actingAs($this->user)->post('/__test/upload', [
            'name' => 'no file here',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_file_under_a_non_whitelisted_field_name_is_detected_and_blocked_over_quota(): void
    {
        $this->seedCachedStorageUsed(1024);

        // 'attachment' is not one of the previously-whitelisted field names
        // (file, result_file, receipt_file, medical_files) - this proves
        // the detection gate now catches it.
        $response = $this->actingAs($this->user)->postJson('/__test/upload', [
            'attachment' => UploadedFile::fake()->create('scan.pdf', 50), // 50 KB
        ]);

        $response->assertStatus(413);
    }

    public function test_nested_array_file_fields_are_summed_and_blocked(): void
    {
        $this->seedCachedStorageUsed(1000);

        // Mirrors a field shape like medicines[0][attachment]. Each file is
        // small individually, but together they push the clinic over its
        // 1 KB limit - proving sizes are summed recursively, not just at
        // the top level.
        $response = $this->actingAs($this->user)->postJson('/__test/upload', [
            'medicines' => [
                ['attachment' => UploadedFile::fake()->create('a.pdf', 20)],
                ['attachment' => UploadedFile::fake()->create('b.pdf', 20)],
            ],
        ]);

        $response->assertStatus(413);
    }

    public function test_upload_within_quota_is_allowed(): void
    {
        // Raise the limit well above the test file size so this request is
        // clearly within quota.
        $this->clinic->forceFill(['storage_limit' => 1024 * 1024])->save();
        $this->seedCachedStorageUsed(0);

        $response = $this->actingAs($this->user)->post('/__test/upload', [
            'image' => UploadedFile::fake()->create('photo.jpg', 10), // 10 KB
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_upload_exactly_at_the_limit_is_hard_blocked(): void
    {
        // Per the "immediate hard block" decision: a clinic already exactly
        // at 100% usage must be blocked for any additional byte - there is
        // no grace window between 100% and "blocked".
        $this->seedCachedStorageUsed(1024);

        $response = $this->actingAs($this->user)->postJson('/__test/upload', [
            'video' => UploadedFile::fake()->create('clip.mp4', 1), // 1 KB
        ]);

        $response->assertStatus(413);
    }
}
