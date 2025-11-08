<?php

namespace App\Http\Controllers;

use App\Models\PatientImage;
use App\Models\PatientVitalSignsAssignment;
use App\Models\Patient;
use Illuminate\Http\Request;

class ImageBankController extends Controller
{
    /**
     * Medical Image Bank - aggregate patient images with filters.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Permissions: allow anyone who can manage/view patients; in dev, bypass
        if (!(config('app.debug') || env('DISABLE_PERMISSIONS', true))) {
            if (!$user || (!$user->canManagePatients() && !$user->hasPermission('patients_view'))) {
                abort(403);
            }
        }

        $query = PatientImage::with(['patient'])
            ->when($user && $user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
            ->whereNotNull('mime')
            ->where('mime', 'like', 'image/%'); // images only by default

        // Filter: patient (ID/code/name)
        $patientParam = trim((string) $request->input('patient'));
        if ($patientParam !== '') {
            if (is_numeric($patientParam)) {
                $query->where('patient_id', (int) $patientParam);
            } else {
                // Try match by patient code or name
                $query->where(function ($q) use ($patientParam) {
                    $q->whereHas('patient', function ($pq) use ($patientParam) {
                        $pq->where('patient_id', 'like', "%{$patientParam}%")
                           ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$patientParam}%"])
                           ->orWhere('first_name', 'like', "%{$patientParam}%")
                           ->orWhere('last_name', 'like', "%{$patientParam}%");
                    });
                });
            }
        }

        // Filter: patient-level medical condition (from active assignments)
        $condition = trim((string) $request->input('condition'));
        if ($condition !== '') {
            $patientIds = PatientVitalSignsAssignment::query()
                ->where('is_active', true)
                ->whereNotNull('medical_condition')
                ->where('medical_condition', $condition)
                ->whereHas('patient', function ($q) use ($user) {
                    if ($user && $user->clinic_id) {
                        $q->where('clinic_id', $user->clinic_id);
                    }
                })
                ->pluck('patient_id')
                ->unique()
                ->values()
                ->all();

            if (!empty($patientIds)) {
                $query->whereIn('patient_id', $patientIds);
            } else {
                $query->whereRaw('1=0');
            }
        }

        // Filter: image-level condition tag
        $tag = trim((string) $request->input('tag'));
        if ($tag !== '') {
            // Prefer JSON contains; fallback to LIKE for older setups
            try {
                $query->whereJsonContains('condition_tags', $tag);
            } catch (\Throwable $e) {
                $query->where('condition_tags', 'like', '%"' . addcslashes($tag, '"%_') . '"%');
            }
        }

        // Filter: caption/filename contains
        $q = trim((string) $request->input('q'));
        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('caption', 'like', "%{$q}%")
                   ->orWhere('filename', 'like', "%{$q}%");
            });
        }

        // Build query
        $images = $query->latest()->paginate(30)->withQueryString();

        // Build filter options
        // Patients that have images (limit to keep dropdown manageable)
        $patientIdsWithImages = (clone $query)->distinct('patient_id')->pluck('patient_id');
        $patients = Patient::whereIn('id', $patientIdsWithImages)
            ->when($user && $user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
            ->orderBy('last_name')
            ->limit(200)
            ->get(['id','patient_id','first_name','last_name']);

        // Distinct patient-level conditions (active)
        $conditions = PatientVitalSignsAssignment::query()
            ->where('is_active', true)
            ->whereNotNull('medical_condition')
            ->whereHas('patient', function ($q) use ($user) {
                if ($user && $user->clinic_id) {
                    $q->where('clinic_id', $user->clinic_id);
                }
            })
            ->distinct()
            ->orderBy('medical_condition')
            ->pluck('medical_condition');

        // Distinct image-level tags (flatten and unique)
        $tags = collect(
            PatientImage::query()
                ->when($user && $user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
                ->whereNotNull('condition_tags')
                ->pluck('condition_tags')
                ->all()
        )->flatMap(function ($arr) {
            if (is_string($arr)) {
                // Some DBs may return JSON string
                $decoded = json_decode($arr, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($arr) ? $arr : [];
        })->filter()->unique()->sort()->values();

        return view('images.bank', [
            'images' => $images,
            'patients' => $patients,
            'conditions' => $conditions,
            'tags' => $tags,
            'filters' => [
                'patient' => $patientParam,
                'condition' => $condition,
                'tag' => $tag,
                'q' => $q,
            ],
        ]);
    }
}

