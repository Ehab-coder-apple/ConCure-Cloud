<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVideo;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientVideoController extends Controller
{
    private function authorizePatientAccess(Patient $patient): void
    {
        $user = auth()->user();
        if ($patient->clinic_id !== ($user->clinic_id ?? null)) {
            abort(403, 'Unauthorized access to patient.');
        }
    }

    /**
     * Generate a presigned PUT URL for direct browser → Spaces upload.
     */
    public function presignedUrl(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'filename'     => 'required|string|max:255',
            'content_type' => 'required|string|max:100',
            'size'         => 'required|integer|min:1',
        ]);

        $allowedMimes = [
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
            'video/x-ms-wmv', 'video/webm', 'video/x-matroska',
        ];
        if (!in_array($request->input('content_type'), $allowedMimes)) {
            return response()->json(['error' => __('Invalid video type.')], 422);
        }

        // Build a safe object key
        $original = $request->input('filename');
        $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $original);
        $tenantDir = StorageQuotaService::getTenantStoragePath($patient->clinic_id, 'videos');
        $path = $tenantDir . '/' . $safeName;

        $url = StorageQuotaService::getPresignedUploadUrl(
            $path,
            $request->input('content_type'),
            30 // 30 min expiry
        );

        return response()->json([
            'upload_url' => $url,
            'path'       => $path,
        ]);
    }

    /**
     * Confirm upload: save the DB record after the browser finished uploading directly to Spaces.
     */
    public function store(Request $request, Patient $patient): JsonResponse
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'path'           => 'required|string|max:500',
            'filename'       => 'required|string|max:255',
            'content_type'   => 'required|string|max:100',
            'size'           => 'required|integer|min:1',
            'title'          => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'condition_tags' => 'nullable|string|max:500',
        ]);

        // Verify the path belongs to this tenant
        $expectedPrefix = StorageQuotaService::getTenantStoragePath($patient->clinic_id, 'videos');
        if (!str_starts_with($request->input('path'), $expectedPrefix)) {
            return response()->json(['error' => __('Invalid path.')], 422);
        }

        // Parse tags
        $tagsRaw = (string) $request->input('condition_tags', '');
        $tags = collect(preg_split('/[,]+/', $tagsRaw))
            ->map(fn($t) => trim($t))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();

        $video = PatientVideo::create([
            'clinic_id'           => $patient->clinic_id,
            'patient_id'          => $patient->id,
            'uploaded_by_user_id' => auth()->id(),
            'path'                => $request->input('path'),
            'filename'            => $request->input('filename'),
            'mime'                => $request->input('content_type'),
            'size'                => $request->input('size'),
            'title'               => $request->input('title'),
            'description'         => $request->input('description'),
            'condition_tags'      => $tags,
        ]);

        return response()->json([
            'success' => true,
            'video'   => $video,
            'message' => __('Video uploaded successfully.'),
        ]);
    }

    public function update(Request $request, Patient $patient, PatientVideo $video)
    {
        $this->authorizePatientAccess($patient);
        if ($video->patient_id !== $patient->id) {
            abort(404);
        }
        $request->validate([
            'title'          => 'nullable|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'condition_tags' => 'nullable|string|max:500',
        ]);
        $video->title = $request->input('title');
        $video->description = $request->input('description');
        if ($request->has('condition_tags')) {
            $tagsRaw = (string) $request->input('condition_tags', '');
            $tags = collect(preg_split('/[,]+/', $tagsRaw))
                ->map(fn($t) => trim($t))
                ->filter()
                ->unique()
                ->take(10)
                ->values()
                ->all();
            $video->condition_tags = $tags;
        }
        $video->save();
        return back()->with('success', __('Video updated.'));
    }

    public function destroy(Patient $patient, PatientVideo $video)
    {
        $this->authorizePatientAccess($patient);
        if ($video->patient_id !== $patient->id) {
            abort(404);
        }
        StorageQuotaService::deleteFromDisk($video->path);
        $video->delete();
        return back()->with('success', __('Video deleted.'));
    }
}

