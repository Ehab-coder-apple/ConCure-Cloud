<?php

namespace App\Http\Controllers;

use App\Models\SurgicalCase;
use App\Models\SurgicalVisit;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurgicalVisitController extends Controller
{
    /**
     * Show form to record a surgical visit for a case.
     */
    public function create(Request $request, SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $surgicalCase->load(['patient']);

        // Calculate the next visit number based on existing visits
        $visitCount = $surgicalCase->visits()->count();
        $visitNumber = $visitCount + 1;
        $patient = $surgicalCase->patient;

        return view('surgery.visits.create', compact('surgicalCase', 'patient', 'visitNumber'));
    }

    /**
     * Store a new surgical visit (post-op follow-up).
     */
    public function store(Request $request, SurgicalCase $surgicalCase)
    {
        $user = Auth::user();

        if ($user->clinic_id && $surgicalCase->clinic_id !== $user->clinic_id) {
            abort(403);
        }

        $validated = $request->validate([
            'visit_date' => 'required|date',
            'clinical_observations' => 'nullable|string',
            'wound_status' => 'nullable|string',
            'wound_assessment' => 'nullable|array',
            'medications_prescribed' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $user, $surgicalCase) {
            // Calculate the next visit number based on existing visits
            $visitCount = $surgicalCase->visits()->count();
            $visitNumber = $visitCount + 1;

            SurgicalVisit::create([
                'clinic_id' => $surgicalCase->clinic_id,
                'patient_id' => $surgicalCase->patient_id,
                'surgical_case_id' => $surgicalCase->id,
                'visit_date' => $validated['visit_date'],
                'visit_number' => $visitNumber,
                'clinical_observations' => $validated['clinical_observations'] ?? null,
                'wound_status' => $validated['wound_status'] ?? null,
                'wound_assessment' => $validated['wound_assessment'] ?? null,
                'medications_prescribed' => $validated['medications_prescribed'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $user->id,
            ]);
        });

        return redirect()->route('surgery.show', $surgicalCase)
            ->with('success', 'Surgical visit recorded successfully.');
    }
}
