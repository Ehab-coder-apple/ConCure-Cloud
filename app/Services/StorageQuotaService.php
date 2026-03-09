<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StorageQuotaService
{
    /**
     * Default storage limit: 5 GB in bytes.
     */
    public const DEFAULT_LIMIT = 5368709120;

    /**
     * Cache TTL in seconds (10 minutes).
     */
    protected const CACHE_TTL = 600;

    /**
     * Calculate actual storage used by a clinic from patient_files table.
     * Uses SUM(file_size) with a join through patients for tenant isolation.
     */
    public function calculateStorageUsed(int $clinicId): int
    {
        return (int) DB::table('patient_files')
            ->join('patients', 'patient_files.patient_id', '=', 'patients.id')
            ->where('patients.clinic_id', $clinicId)
            ->sum('patient_files.file_size');
    }

    /**
     * Get cached storage usage for a clinic. Falls back to DB calculation.
     */
    public function getStorageUsed(int $clinicId): int
    {
        return Cache::remember(
            "clinic_{$clinicId}_storage_used",
            self::CACHE_TTL,
            fn () => $this->calculateStorageUsed($clinicId)
        );
    }

    /**
     * Get storage limit for a clinic.
     */
    public function getStorageLimit(int $clinicId): int
    {
        if (!Schema::hasColumn('clinics', 'storage_limit')) {
            return self::DEFAULT_LIMIT;
        }

        $clinic = Clinic::find($clinicId);
        return $clinic ? (int) $clinic->storage_limit : self::DEFAULT_LIMIT;
    }

    /**
     * Get remaining storage for a clinic.
     */
    public function getStorageRemaining(int $clinicId): int
    {
        $limit = $this->getStorageLimit($clinicId);
        $used = $this->getStorageUsed($clinicId);
        return max(0, $limit - $used);
    }

    /**
     * Get full storage info for a clinic.
     */
    public function getStorageInfo(int $clinicId): array
    {
        $limit = $this->getStorageLimit($clinicId);
        $used = $this->getStorageUsed($clinicId);
        $remaining = max(0, $limit - $used);
        $percentage = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;

        return [
            'clinic_id'      => $clinicId,
            'storage_limit'  => $limit,
            'storage_used'   => $used,
            'storage_remaining' => $remaining,
            'percentage_used' => $percentage,
            'limit_gb'       => round($limit / (1024 * 1024 * 1024), 2),
            'used_gb'        => round($used / (1024 * 1024 * 1024), 2),
            'remaining_gb'   => round($remaining / (1024 * 1024 * 1024), 2),
            'warning'        => $percentage >= 80 && $percentage < 95,
            'critical'       => $percentage >= 95,
        ];
    }

    /**
     * Check if a clinic can upload a file of the given size.
     */
    public function canUpload(int $clinicId, int $fileSize): bool
    {
        $limit = $this->getStorageLimit($clinicId);
        $used = $this->getStorageUsed($clinicId);
        return ($used + $fileSize) <= $limit;
    }

    /**
     * Recalculate and sync storage_used column for a clinic.
     */
    public function syncStorageUsed(int $clinicId): int
    {
        $used = $this->calculateStorageUsed($clinicId);

        if (Schema::hasColumn('clinics', 'storage_used')) {
            Clinic::where('id', $clinicId)->update(['storage_used' => $used]);
        }

        // Bust cache
        Cache::forget("clinic_{$clinicId}_storage_used");

        return $used;
    }

    /**
     * Increment storage_used after a file upload.
     */
    public function incrementUsage(int $clinicId, int $fileSize): void
    {
        if (Schema::hasColumn('clinics', 'storage_used')) {
            DB::table('clinics')
                ->where('id', $clinicId)
                ->increment('storage_used', $fileSize);
        }
        Cache::forget("clinic_{$clinicId}_storage_used");
    }

    /**
     * Decrement storage_used after a file deletion.
     */
    public function decrementUsage(int $clinicId, int $fileSize): void
    {
        if (Schema::hasColumn('clinics', 'storage_used')) {
            DB::table('clinics')
                ->where('id', $clinicId)
                ->decrement('storage_used', $fileSize);

            // Ensure it doesn't go negative
            DB::table('clinics')
                ->where('id', $clinicId)
                ->where('storage_used', '<', 0)
                ->update(['storage_used' => 0]);
        }
        Cache::forget("clinic_{$clinicId}_storage_used");
    }

    // ─── DigitalOcean Spaces helpers ────────────────────────────────

    /**
     * The disk name used for heavy medical file storage.
     */
    public const SPACES_DISK = 'spaces-private';

    /**
     * Generate the tenant folder path for a given clinic and file type.
     *
     * @param int    $clinicId
     * @param string $type  One of: documents, lab, xrays, radiology, images, finance, dental-lab
     * @return string  e.g. "tenant_12/xrays"
     */
    public static function getTenantStoragePath(int $clinicId, string $type): string
    {
        $allowed = ['documents', 'lab', 'xrays', 'radiology', 'images', 'finance', 'dental-lab', 'videos'];
        if (!in_array($type, $allowed)) {
            $type = 'documents';
        }
        return "tenant_{$clinicId}/{$type}";
    }

    /**
     * Generate a secure (temporary) URL for a stored file.
     *
     * New files (path starts with "tenant_") → signed temporary URL from Spaces.
     * Legacy files → local /storage/ relative URL.
     *
     * @param string|null $path
     * @param int         $minutes  Link expiry in minutes (default 10)
     * @return string
     */
    public static function getSecureUrl(?string $path, int $minutes = 10): string
    {
        if (!$path) {
            return '#';
        }

        // New files stored on DigitalOcean Spaces
        if (str_starts_with($path, 'tenant_')) {
            try {
                return Storage::disk(self::SPACES_DISK)
                    ->temporaryUrl($path, now()->addMinutes($minutes));
            } catch (\Exception $e) {
                // Fallback: unsigned URL (works if bucket is public, or for debugging)
                return Storage::disk(self::SPACES_DISK)->url($path);
            }
        }

        // Legacy files on local public disk
        return '/storage/' . ltrim($path, '/');
    }

    /**
     * Check if a file exists on the correct disk (Spaces or local).
     */
    public static function fileExistsOnDisk(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        if (str_starts_with($path, 'tenant_')) {
            return Storage::disk(self::SPACES_DISK)->exists($path);
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Generate a presigned PUT URL for direct browser-to-Spaces upload.
     *
     * @param string $path        Full object key, e.g. "tenant_5/videos/abc.mp4"
     * @param string $contentType MIME type of the file
     * @param int    $minutes     URL expiry in minutes
     * @return string             Presigned PUT URL
     */
    public static function getPresignedUploadUrl(string $path, string $contentType = 'application/octet-stream', int $minutes = 30): string
    {
        $config = config('filesystems.disks.' . self::SPACES_DISK);

        $client = new \Aws\S3\S3Client([
            'version'     => 'latest',
            'region'      => $config['region'],
            'endpoint'    => $config['endpoint'],
            'credentials' => [
                'key'    => $config['key'],
                'secret' => $config['secret'],
            ],
        ]);

        $command = $client->getCommand('PutObject', [
            'Bucket'      => $config['bucket'],
            'Key'         => $path,
            'ContentType' => $contentType,
            'ACL'         => 'private',
        ]);

        $request = $client->createPresignedRequest($command, now()->addMinutes($minutes));

        return (string) $request->getUri();
    }

    /**
     * Delete a file from the correct disk (Spaces or local).
     */
    public static function deleteFromDisk(?string $path): bool
    {
        if (!$path) {
            return true;
        }

        if (str_starts_with($path, 'tenant_')) {
            return Storage::disk(self::SPACES_DISK)->delete($path);
        }

        // Try public, then private (legacy)
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->delete($path);
        }

        return true;
    }
}