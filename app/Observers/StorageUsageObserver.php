<?php

namespace App\Observers;

use App\Contracts\TracksStorageUsage;
use App\Services\StorageQuotaService;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Keeps Clinic::storage_used in sync in real time as file-bearing rows
 * (PatientFile, PatientImage, PatientVideo, DentalImage, ...) are created
 * and permanently removed, instead of relying solely on the manual
 * "Recalculate" action on the Master dashboard.
 *
 * Wired at the model level (one observer, registered per model in
 * AppServiceProvider::boot()) rather than in each upload/delete
 * controller, so every current and future code path that creates or
 * deletes one of these rows is covered automatically - the same lesson
 * from Phase 1's route-level middleware attachment.
 */
class StorageUsageObserver
{
    public function __construct(protected StorageQuotaService $storageService)
    {
    }

    public function created(TracksStorageUsage $model): void
    {
        $clinicId = $model->getStorageClinicId();
        $bytes = $model->getStorageBytes();

        if ($clinicId && $bytes > 0) {
            $this->storageService->incrementUsage($clinicId, $bytes);
        }
    }

    /**
     * Fired on both soft deletes and permanent deletes. For models using
     * SoftDeletes, StorageQuotaService::calculateStorageUsed() queries the
     * table via DB::table(...) (not Eloquent), which does NOT apply the
     * soft-delete scope - so a soft-deleted row's bytes are still counted
     * as "used" until it's force-deleted. Decrementing here for soft
     * deletes would desync storage_used from that source of truth, so we
     * only act here for models that are NOT soft-deletable; soft-deletable
     * models are decremented in forceDeleted() instead.
     */
    public function deleted(TracksStorageUsage $model): void
    {
        if ($this->usesSoftDeletes($model)) {
            return;
        }

        $this->decrement($model);
    }

    public function forceDeleted(TracksStorageUsage $model): void
    {
        $this->decrement($model);
    }

    protected function decrement(TracksStorageUsage $model): void
    {
        $clinicId = $model->getStorageClinicId();
        $bytes = $model->getStorageBytes();

        if ($clinicId && $bytes > 0) {
            $this->storageService->decrementUsage($clinicId, $bytes);
        }
    }

    protected function usesSoftDeletes(TracksStorageUsage $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }
}
