<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StorageQuotaService;
use Symfony\Component\HttpFoundation\Response;

class CheckStorageQuota
{
    protected StorageQuotaService $storageService;

    public function __construct(StorageQuotaService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Handle an incoming request.
     * Checks if the authenticated user's clinic has sufficient storage quota
     * before allowing file uploads.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on requests that contain files
        if (!$request->hasFile('file') && !$request->hasFile('result_file') && !$request->hasFile('receipt_file') && !$request->hasFile('medical_files')) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user || !$user->clinic_id) {
            return $next($request);
        }

        $clinicId = $user->clinic_id;

        // Calculate total size of all uploaded files
        $totalSize = 0;
        foreach ($request->allFiles() as $key => $files) {
            // Handle both single files and arrays of files
            $fileArray = is_array($files) ? $files : [$files];
            foreach ($fileArray as $file) {
                $totalSize += $file->getSize();
            }
        }

        if ($totalSize === 0) {
            return $next($request);
        }

        if (!$this->storageService->canUpload($clinicId, $totalSize)) {
            $info = $this->storageService->getStorageInfo($clinicId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Storage quota exceeded. Your clinic has used :used GB of :limit GB.', [
                        'used' => $info['used_gb'],
                        'limit' => $info['limit_gb'],
                    ]),
                    'storage_info' => $info,
                ], 413);
            }

            return back()->withErrors([
                'file' => __('Storage quota exceeded. Your clinic has used :used GB of :limit GB. Please contact your administrator to increase the storage limit.', [
                    'used' => $info['used_gb'],
                    'limit' => $info['limit_gb'],
                ]),
            ]);
        }

        return $next($request);
    }
}

