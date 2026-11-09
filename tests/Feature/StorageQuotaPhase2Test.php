<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\DentalImage;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientImage;
use App\Models\PatientVideo;
use App\Services\StorageQuotaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Phase 2 coverage:
 *  1. Data sync - StorageUsageObserver increments/decrements
 *     Clinic::storage_used in real time as PatientFile, PatientImage,
 *     PatientVideo, and DentalImage rows are created/removed, without a
 *     manual "Recalculate".
 *  2. Concurrency safety - StorageQuotaService::canUpload() serializes via
 *     Cache::lock() so a held lock blocks a concurrent check.
 */
class StorageQuotaPhase2Test extends TestCase
{
    private Clinic $clinic;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['dental_images', 'patient_videos', 'patient_images', 'patient_files', 'patients', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('storage_limit')->default(StorageQuotaService::DEFAULT_LIMIT);
            $table->unsignedBigInteger('storage_used')->default(0);
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('patient_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamps();
        });

        Schema::create('patient_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('path');
            $table->string('filename')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('patient_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('path');
            $table->string('filename')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('dental_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->string('image_type')->nullable();
            $table->string('file_path');
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->clinic = Clinic::create(['name' => 'Test Clinic', 'storage_limit' => StorageQuotaService::DEFAULT_LIMIT, 'storage_used' => 0]);
        $this->patient = Patient::create(['clinic_id' => $this->clinic->id, 'first_name' => 'John', 'last_name' => 'Doe']);

        Cache::flush();
    }

    protected function tearDown(): void
    {
        foreach (['dental_images', 'patient_videos', 'patient_images', 'patient_files', 'patients', 'clinics'] as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    private function storageUsed(): int
    {
        return (int) $this->clinic->fresh()->storage_used;
    }

    public function test_creating_a_patient_image_increments_clinic_storage_used(): void
    {
        $this->assertSame(0, $this->storageUsed());

        PatientImage::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'path' => 'tenant_1/images/a.jpg',
            'filename' => 'a.jpg',
            'mime' => 'image/jpeg',
            'size' => 2048,
        ]);

        $this->assertSame(2048, $this->storageUsed());
    }

    public function test_deleting_a_patient_image_decrements_clinic_storage_used(): void
    {
        $image = PatientImage::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'path' => 'tenant_1/images/a.jpg',
            'filename' => 'a.jpg',
            'mime' => 'image/jpeg',
            'size' => 2048,
        ]);

        $this->assertSame(2048, $this->storageUsed());

        $image->delete();

        $this->assertSame(0, $this->storageUsed());
    }

    public function test_patient_video_is_tracked_via_direct_clinic_id(): void
    {
        $video = PatientVideo::create([
            'clinic_id' => $this->clinic->id,
            'patient_id' => $this->patient->id,
            'path' => 'tenant_1/videos/v.mp4',
            'filename' => 'v.mp4',
            'mime' => 'video/mp4',
            'size' => 5000,
        ]);

        $this->assertSame(5000, $this->storageUsed());

        $video->delete();

        $this->assertSame(0, $this->storageUsed());
    }

    public function test_patient_file_resolves_clinic_id_through_its_patient_relation(): void
    {
        $file = PatientFile::create([
            'patient_id' => $this->patient->id,
            'original_name' => 'report.pdf',
            'file_name' => 'report.pdf',
            'file_path' => 'tenant_1/documents/report.pdf',
            'file_type' => 'document',
            'file_size' => 3000,
            'mime_type' => 'application/pdf',
            'category' => 'medical_report',
        ]);

        $this->assertSame(3000, $this->storageUsed());

        $file->delete();

        $this->assertSame(0, $this->storageUsed());
    }

    public function test_soft_deleting_a_dental_image_does_not_decrement_storage(): void
    {
        $image = DentalImage::create([
            'patient_id' => $this->patient->id,
            'clinic_id' => $this->clinic->id,
            'image_type' => 'panoramic',
            'file_path' => 'tenant_1/xrays/x.jpg',
            'filename' => 'x.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 4096,
        ]);

        $this->assertSame(4096, $this->storageUsed());

        // Soft delete: StorageQuotaService::calculateStorageUsed() queries
        // dental_images via DB::table(...) (no soft-delete scope applied),
        // so the row's bytes are still "used" until it's force-deleted.
        // Decrementing here would desync storage_used from that source of
        // truth.
        $image->delete();

        $this->assertSame(4096, $this->storageUsed());
    }

    public function test_force_deleting_a_dental_image_decrements_storage(): void
    {
        $image = DentalImage::create([
            'patient_id' => $this->patient->id,
            'clinic_id' => $this->clinic->id,
            'image_type' => 'panoramic',
            'file_path' => 'tenant_1/xrays/x.jpg',
            'filename' => 'x.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 4096,
        ]);

        $image->delete();
        $this->assertSame(4096, $this->storageUsed());

        $image->forceDelete();

        $this->assertSame(0, $this->storageUsed());
    }

    public function test_can_upload_blocks_when_another_request_holds_the_clinic_storage_lock(): void
    {
        $service = app(StorageQuotaService::class);

        // Simulate a concurrent request already performing its canUpload()
        // check for this clinic by holding the same lock key.
        $competingLock = Cache::lock("clinic_{$this->clinic->id}_storage_check", 10);
        $this->assertTrue($competingLock->get());

        try {
            // canUpload() should fail closed (return false) rather than
            // proceed without the lock, since it can't safely read a
            // consistent "used" value while another check is in flight.
            $this->assertFalse($service->canUpload($this->clinic->id, 100));
        } finally {
            $competingLock->release();
        }
    }

    public function test_can_upload_succeeds_once_the_lock_is_free(): void
    {
        $service = app(StorageQuotaService::class);

        $this->assertTrue($service->canUpload($this->clinic->id, 100));
    }

    public function test_get_storage_info_flags_match_the_90_and_100_percent_ui_thresholds(): void
    {
        $service = app(StorageQuotaService::class);
        $this->clinic->update(['storage_limit' => 1000]);

        Cache::put("clinic_{$this->clinic->id}_storage_used", 890, 600);
        $info = $service->getStorageInfo($this->clinic->id);
        $this->assertFalse($info['warning']);
        $this->assertFalse($info['critical']);

        Cache::put("clinic_{$this->clinic->id}_storage_used", 900, 600);
        $info = $service->getStorageInfo($this->clinic->id);
        $this->assertTrue($info['warning']);
        $this->assertFalse($info['critical']);

        Cache::put("clinic_{$this->clinic->id}_storage_used", 1000, 600);
        $info = $service->getStorageInfo($this->clinic->id);
        $this->assertFalse($info['warning']);
        $this->assertTrue($info['critical']);
    }
}
