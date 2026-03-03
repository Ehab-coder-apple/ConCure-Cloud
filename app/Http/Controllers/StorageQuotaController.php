<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StorageQuotaController extends Controller
{
    protected StorageQuotaService $storageService;

    public function __construct(StorageQuotaService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Get storage info for the authenticated user's clinic (JSON).
     */
    public function getStorageInfo(Request $request)
    {
        $clinicId = $request->user()->clinic_id;

        if (!$clinicId) {
            return response()->json(['success' => false, 'message' => 'No clinic assigned.'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $this->storageService->getStorageInfo($clinicId),
        ]);
    }

    /**
     * Master admin: Update storage limit for a clinic.
     */
    public function updateStorageLimit(Request $request, Clinic $clinic)
    {
        $request->validate([
            'storage_limit_gb' => 'required|numeric|min:0.1|max:10000',
        ]);

        $limitBytes = (int) ($request->storage_limit_gb * 1024 * 1024 * 1024);

        if (Schema::hasColumn('clinics', 'storage_limit')) {
            $clinic->update(['storage_limit' => $limitBytes]);
        }

        return back()->with('success', "Storage limit updated to {$request->storage_limit_gb} GB for {$clinic->name}.");
    }

    /**
     * Master admin: Sync (recalculate) storage used for a clinic.
     */
    public function syncStorage(Request $request, Clinic $clinic)
    {
        $used = $this->storageService->syncStorageUsed($clinic->id);
        $usedGb = round($used / (1024 * 1024 * 1024), 2);

        return back()->with('success', "Storage recalculated for {$clinic->name}: {$usedGb} GB used.");
    }

    /**
     * Master admin: Get storage info for a specific clinic (JSON).
     */
    public function getClinicStorageInfo(Clinic $clinic)
    {
        return response()->json([
            'success' => true,
            'data' => $this->storageService->getStorageInfo($clinic->id),
        ]);
    }
}

