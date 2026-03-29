<?php

namespace App\Http\Controllers;

use App\Models\CanalTreatment;
use App\Models\DentalTreatment;
use App\Models\ToothCanal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CanalTreatmentController extends Controller
{
    /**
     * Get canal worksheet data for a dental treatment (AJAX).
     */
    public function getWorksheet(DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dentalTreatment->load('canalTreatments');

        // Get the tooth number(s) for this treatment
        $toothNumbers = [];
        if ($dentalTreatment->tooth_number) {
            $toothNumbers[] = $dentalTreatment->tooth_number;
        }
        if ($dentalTreatment->tooth_numbers && is_array($dentalTreatment->tooth_numbers)) {
            $toothNumbers = array_merge($toothNumbers, $dentalTreatment->tooth_numbers);
        }
        $toothNumbers = array_unique($toothNumbers);

        // Get standard canal definitions for each tooth
        $standardCanals = [];
        foreach ($toothNumbers as $toothNum) {
            $canals = ToothCanal::getForTooth($toothNum);
            if ($canals->isEmpty()) {
                // Fallback: generate canals based on tooth type
                $toothType = ToothCanal::getToothType($toothNum);
                $arch = ToothCanal::getArch($toothNum);
                $canals = ToothCanal::getForToothType($toothType, $arch);
            }
            $standardCanals[$toothNum] = $canals;
        }

        // Get existing canal treatment records
        $existingCanals = $dentalTreatment->canalTreatments
            ->groupBy('tooth_number')
            ->map(fn($group) => $group->keyBy('canal_name'));

        return response()->json([
            'success' => true,
            'tooth_numbers' => $toothNumbers,
            'standard_canals' => $standardCanals,
            'existing_canals' => $existingCanals,
            'options' => [
                'statuses' => CanalTreatment::STATUSES,
                'maf_sizes' => CanalTreatment::MAF_SIZES,
                'tapers' => CanalTreatment::TAPERS,
                'irrigation_protocols' => CanalTreatment::IRRIGATION_PROTOCOLS,
                'obturation_techniques' => CanalTreatment::OBTURATION_TECHNIQUES,
                'sealers' => CanalTreatment::SEALERS,
            ],
        ]);
    }

    /**
     * Store/update canal treatments for a dental treatment (bulk save).
     */
    public function store(Request $request, DentalTreatment $dentalTreatment)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$user->isSuperAdmin() && $user->clinic_id && $dentalTreatment->clinic_id !== $user->clinic_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'canals' => 'required|array|min:1',
            'canals.*.tooth_number' => 'required|string|max:10',
            'canals.*.canal_name' => 'required|string|max:50',
            'canals.*.working_length' => 'nullable|numeric|min:0|max:50',
            'canals.*.master_apical_file' => 'nullable|string|max:20',
            'canals.*.master_cone_size' => 'nullable|string|max:20',
            'canals.*.taper' => 'nullable|string|max:10',
            'canals.*.irrigation_protocol' => 'nullable|string|max:100',
            'canals.*.obturation_technique' => 'nullable|string|max:100',
            'canals.*.sealer_type' => 'nullable|string|max:100',
            'canals.*.status' => 'nullable|in:not_started,located,instrumented,obturated,completed',
            'canals.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $savedCanals = [];
            foreach ($request->canals as $canalData) {
                $canal = CanalTreatment::updateOrCreate(
                    [
                        'dental_treatment_id' => $dentalTreatment->id,
                        'tooth_number' => $canalData['tooth_number'],
                        'canal_name' => $canalData['canal_name'],
                    ],
                    [
                        'patient_id' => $dentalTreatment->patient_id,
                        'clinic_id' => $dentalTreatment->clinic_id,
                        'working_length' => $canalData['working_length'] ?? null,
                        'master_apical_file' => $canalData['master_apical_file'] ?? null,
                        'master_cone_size' => $canalData['master_cone_size'] ?? null,
                        'taper' => $canalData['taper'] ?? null,
                        'irrigation_protocol' => $canalData['irrigation_protocol'] ?? null,
                        'obturation_technique' => $canalData['obturation_technique'] ?? null,
                        'sealer_type' => $canalData['sealer_type'] ?? null,
                        'status' => $canalData['status'] ?? 'not_started',
                        'notes' => $canalData['notes'] ?? null,
                        'created_by' => $canal->created_by ?? $user->id,
                        'updated_by' => $user->id,
                    ]
                );
                $savedCanals[] = $canal;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Canal treatments saved successfully.',
                'canals' => $savedCanals,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save canal treatments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific canal treatment record.
     */
    public function destroy(CanalTreatment $canalTreatment)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $user->clinic_id && $canalTreatment->clinic_id !== $user->clinic_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!in_array($user->role, ['doctor', 'admin', 'program_owner', 'dental_dept'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $canalTreatment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Canal treatment record deleted.',
        ]);
    }

    /**
     * Get canal treatment history for a patient (for clinical history view).
     */
    public function patientHistory(int $patientId)
    {
        $user = Auth::user();

        $canalTreatments = CanalTreatment::where('patient_id', $patientId)
            ->when($user->clinic_id, fn($q) => $q->where('clinic_id', $user->clinic_id))
            ->with(['dentalTreatment', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('dental_treatment_id');

        return response()->json([
            'success' => true,
            'canal_treatments' => $canalTreatments,
        ]);
    }
}

