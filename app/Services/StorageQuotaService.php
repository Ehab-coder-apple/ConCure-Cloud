<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

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


}