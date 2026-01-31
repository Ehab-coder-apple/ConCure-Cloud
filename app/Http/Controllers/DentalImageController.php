<?php

namespace App\Http\Controllers;

use App\Models\DentalImage;
use App\Models\DentalChart;
use App\Models\DentalTreatment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DentalImageController extends Controller
{
    /**
     * Display a listing of dental images.
     */
    public function index(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to patient dental images.');
            }
        }

        $query = DentalImage::where('patient_id', $patient->id)
                           ->with(['uploader', 'dentalChart', 'dentalTreatment']);

        // Filter by image type
        if ($request->filled('image_type')) {
            $query->byImageType($request->image_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $images = $query->paginate(20);

        return view('dental.images.index', compact('patient', 'images'));
    }

    /**
     * Show the form for uploading a new dental image.
     */
    public function upload(Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to upload dental images.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can upload dental images.');
        }

        // Get patient's dental charts
        $dentalCharts = DentalChart::where('patient_id', $patient->id)
                                  ->latest()
                                  ->get();

        // Get patient's dental treatments
        $dentalTreatments = DentalTreatment::where('patient_id', $patient->id)
                                          ->latest()
                                          ->get();

        return view('dental.images.upload', compact('patient', 'dentalCharts', 'dentalTreatments'));
    }

    /**
     * Store a newly uploaded dental image.
     */
    public function store(Request $request, Patient $patient)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to upload dental images.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            abort(403, 'Only doctors, dental assistants, and dentists can upload dental images.');
        }

        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,dcm,dicom|max:20480', // 20MB max
            'image_type' => 'required|in:panoramic,periapical,bitewing,occlusal,cephalometric,intraoral_photo,extraoral_photo,cbct,other',
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'dental_treatment_id' => 'nullable|exists:dental_treatments,id',
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_date' => 'nullable|date',
        ]);

        try {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dental_images/' . $patient->id, $filename, 'public');

            $dentalImage = DentalImage::create([
                'patient_id' => $patient->id,
                'clinic_id' => $user->clinic_id,
                'dental_chart_id' => $request->dental_chart_id,
                'dental_treatment_id' => $request->dental_treatment_id,
                'tooth_number' => $request->tooth_number,
                'tooth_numbers' => $request->tooth_numbers,
                'image_type' => $request->image_type,
                'file_path' => $path,
                'filename' => $filename,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'title' => $request->title,
                'description' => $request->description,
                'image_date' => $request->image_date ?? now(),
                'uploaded_by' => $user->id,
            ]);

            return redirect()->route('dental.images.show', ['patient' => $patient, 'dentalImage' => $dentalImage])
                           ->with('success', 'Dental image uploaded successfully.');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Failed to upload dental image. Please try again.');
        }
    }

    /**
     * Display the specified dental image.
     */
    public function show(Patient $patient, DentalImage $dentalImage)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to dental image.');
            }
        }

        $dentalImage->load(['uploader', 'dentalChart', 'dentalTreatment']);

        return view('dental.images.show', compact('patient', 'dentalImage'));
    }

    /**
     * Remove the specified dental image.
     */
    public function destroy(Patient $patient, DentalImage $dentalImage)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                abort(403, 'Unauthorized access to delete dental image.');
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            abort(403, 'Only doctors, dental assistants, and administrators can delete dental images.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($dentalImage->file_path)) {
            Storage::disk('public')->delete($dentalImage->file_path);
        }

        $dentalImage->delete();

        return redirect()->route('dental.images.index', $patient)
                       ->with('success', 'Dental image deleted successfully.');
    }

    /**
     * Link image to a specific tooth (AJAX endpoint).
     */
    public function linkToTooth(Request $request, Patient $patient, DentalImage $dentalImage)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'tooth_number' => 'nullable|string',
            'tooth_numbers' => 'nullable|array',
        ]);

        $dentalImage->update([
            'tooth_number' => $request->tooth_number,
            'tooth_numbers' => $request->tooth_numbers,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image linked to tooth successfully.',
        ]);
    }

    /**
     * Update image metadata (AJAX endpoint).
     */
    public function updateMetadata(Request $request, Patient $patient, DentalImage $dentalImage)
    {
        $user = Auth::user();

        // Check access
        if (!$user->isSuperAdmin()) {
            if ($user->clinic_id && $patient->clinic_id !== $user->clinic_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_date' => 'nullable|date',
        ]);

        $dentalImage->update($request->only(['title', 'description', 'image_date']));

        return response()->json([
            'success' => true,
            'message' => 'Image metadata updated successfully.',
        ]);
    }
}


