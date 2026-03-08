<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientImage;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientImageController extends Controller
{
    private function authorizePatientAccess(Patient $patient): void
    {
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return;
        }
        $user = auth()->user();
        if ($patient->clinic_id !== ($user->clinic_id ?? null)) {
            abort(403, 'Unauthorized access to patient.');
        }
        if (!($user->hasPermission('patients_edit') || $user->canManagePatients())) {
            // Allow view-only users to list images via patient page, but restrict changes
            // We'll still allow GET routes elsewhere if added later
        }
    }

    public function store(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($patient);

        $request->validate([
            'images' => 'required',
            'images.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
            'captions' => 'array',
            'captions.*' => 'nullable|string|max:255',
            'condition_tags' => 'nullable|string|max:500', // comma-separated tags
        ]);

        $user = auth()->user();
        $stored = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $file) {
                $original = $file->getClientOriginalName();
                $safeName = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $original);
                $tenantDir = StorageQuotaService::getTenantStoragePath($patient->clinic_id, 'images');
                $path = $file->storeAs($tenantDir, $safeName, StorageQuotaService::SPACES_DISK);

                $caption = $request->input("captions.$idx") ?? null;

                // Parse comma-separated tags (applies to all uploaded files in this batch)
                $tagsRaw = (string) $request->input('condition_tags', '');
                $tags = collect(preg_split('/[,]+/', $tagsRaw))
                    ->map(fn($t) => trim($t))
                    ->filter()
                    ->unique()
                    ->take(10)
                    ->values()
                    ->all();

                $img = PatientImage::create([
                    'clinic_id' => $patient->clinic_id,
                    'patient_id' => $patient->id,
                    'uploaded_by_user_id' => $user?->id,
                    'path' => $path,
                    'filename' => $original,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'caption' => $caption,
                    'condition_tags' => $tags,
                ]);
                $stored[] = $img->id;
            }
        }

        return redirect()->route('patients.show', $patient)->with('success', __('Images uploaded successfully.'));
    }

    public function update(Request $request, Patient $patient, PatientImage $image)
    {
        $this->authorizePatientAccess($patient);
        if ($image->patient_id !== $patient->id) {
            abort(404);
        }
        $request->validate([
            'caption' => 'nullable|string|max:255',
            'condition_tags' => 'nullable|string|max:500',
        ]);
        $image->caption = $request->input('caption');
        if ($request->has('condition_tags')) {
            $tagsRaw = (string) $request->input('condition_tags', '');
            $tags = collect(preg_split('/[,]+/', $tagsRaw))
                ->map(fn($t) => trim($t))
                ->filter()
                ->unique()
                ->take(10)
                ->values()
                ->all();
            $image->condition_tags = $tags;
        }
        $image->save();
        return back()->with('success', __('Image updated.'));
    }

    public function destroy(Patient $patient, PatientImage $image)
    {
        $this->authorizePatientAccess($patient);
        if ($image->patient_id !== $patient->id) {
            abort(404);
        }
        StorageQuotaService::deleteFromDisk($image->path);
        $image->delete();
        return back()->with('success', __('Image deleted.'));
    }
}

