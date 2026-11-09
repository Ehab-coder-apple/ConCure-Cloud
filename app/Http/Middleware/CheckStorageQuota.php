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
        // Detection gate: run for ANY request carrying file(s), regardless
        // of field name. Upload field names vary wildly across controllers
        // (file, image, video, images[], attachment, medicines[0][file],
        // etc.) - whitelisting specific names let most uploads bypass this
        // check entirely. allFiles() picks up every file on the request,
        // including arbitrarily nested array/dot fields.
        $allFiles = $request->allFiles();
        if (empty($allFiles)) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user || !$user->clinic_id) {
            return $next($request);
        }

        $clinicId = $user->clinic_id;

        // Calculate total size of all uploaded files, flattening any level
        // of nesting (Laravel's allFiles() mirrors the input shape, so a
        // field like medicines[0][attachment] arrives as a nested array).
        $totalSize = $this->sumFileSizes($allFiles);

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

    /**
     * Recursively sum the size of every UploadedFile found in a
     * (possibly nested) array as returned by Request::allFiles().
     *
     * @param  array<int|string, mixed>  $files
     */
    protected function sumFileSizes(array $files): int
    {
        $total = 0;

        foreach ($files as $file) {
            if (is_array($file)) {
                $total += $this->sumFileSizes($file);
                continue;
            }

            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                $total += $file->getSize();
            }
        }

        return $total;
    }
}

