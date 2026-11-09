<?php

namespace App\Contracts;

/**
 * Implemented by any Eloquent model whose rows are summed by
 * StorageQuotaService::calculateStorageUsed() (patient_files,
 * patient_images, patient_videos, dental_images, ...).
 *
 * StorageUsageObserver uses this contract to keep Clinic::storage_used in
 * sync in real time via incrementUsage()/decrementUsage(), instead of
 * waiting for a manual "Recalculate" pass. Any future file-bearing model
 * that should count against a clinic's quota just needs to implement this
 * interface and be registered with StorageUsageObserver in
 * AppServiceProvider::boot() - no controller changes required.
 */
interface TracksStorageUsage
{
    /**
     * The clinic this row's bytes should be charged against, or null if it
     * cannot be resolved (e.g. an orphaned record whose parent was already
     * deleted) - in which case the observer skips the quota update.
     */
    public function getStorageClinicId(): ?int;

    /**
     * The size, in bytes, this row contributes to clinic storage usage.
     */
    public function getStorageBytes(): int;
}
