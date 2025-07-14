<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request)
    {
        $query = DB::table('prescriptions')
            ->leftJoin('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->leftJoin('users as doctors', 'prescriptions.doctor_id', '=', 'doctors.id')
            ->select(
                'prescriptions.*',
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.patient_id',
                'doctors.first_name as doctor_first_name',
                'doctors.last_name as doctor_last_name'
            )
            ->where('prescriptions.clinic_id', Auth::user()->clinic_id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patients.first_name', 'like', "%{$search}%")
                  ->orWhere('patients.last_name', 'like', "%{$search}%")
                  ->orWhere('prescriptions.prescription_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('prescriptions.status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('prescriptions.created_at', '>=', $request->date_from);
        }

        $prescriptions = $query->orderBy('prescriptions.created_at', 'desc')->paginate(15);

        return view('prescriptions.index', compact('prescriptions'));
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create(Request $request)
    {
        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        $selectedPatientId = $request->get('patient_id');

        return view('prescriptions.create', compact('patients', 'selectedPatientId'));
    }

    /**
     * Store a newly created prescription.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => [
                'required',
                'exists:patients,id,clinic_id,' . Auth::user()->clinic_id . ',is_active,1'
            ],
            'diagnosis' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'prescribed_date' => 'required|date',
            'medications' => 'nullable|array',
            'medications.*.name' => 'nullable|string|max:255',
            'medications.*.strength' => 'nullable|string|max:100',
            'medications.*.dosage' => 'nullable|string|max:100',
            'medications.*.frequency' => 'nullable|string|max:100',
            'medications.*.duration' => 'nullable|string|max:100',
            'medications.*.instructions' => 'nullable|string|max:500',
        ], [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'The selected patient is invalid or does not belong to your clinic.',
            'prescribed_date.required' => 'The prescribed date field is required.',
            'prescribed_date.date' => 'Please enter a valid date.',
        ]);

        try {
            DB::beginTransaction();

            // Create prescription
            $prescriptionId = DB::table('prescriptions')->insertGetId([
                'patient_id' => $request->patient_id,
                'doctor_id' => Auth::id(),
                'clinic_id' => Auth::user()->clinic_id,
                'prescription_number' => 'RX-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'prescribed_date' => $request->prescribed_date,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add medications if provided
            if ($request->filled('medications') && is_array($request->medications)) {
                foreach ($request->medications as $medication) {
                    if (!empty($medication['name']) && trim($medication['name']) !== '') {
                        // Find or create medicine
                        $medicineId = DB::table('medicines')
                            ->where('name', $medication['name'])
                            ->where('clinic_id', Auth::user()->clinic_id)
                            ->value('id');

                        if (!$medicineId) {
                            $medicineId = DB::table('medicines')->insertGetId([
                                'name' => $medication['name'],
                                'generic_name' => $medication['name'], // Use name as generic name
                                'dosage' => $medication['strength'] ?? null,
                                'form' => 'other', // Default form
                                'is_frequent' => false,
                                'clinic_id' => Auth::user()->clinic_id,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        // Add to prescription
                        DB::table('prescription_medicines')->insert([
                            'prescription_id' => $prescriptionId,
                            'medicine_id' => $medicineId,
                            'medicine_name' => $medication['name'],
                            'dosage' => $medication['dosage'] ?? '1 tablet',
                            'frequency' => $medication['frequency'] ?? 'As directed',
                            'duration' => $medication['duration'] ?? '3 days',
                            'instructions' => $medication['instructions'] ?? null,
                            'quantity' => $medication['quantity'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('prescriptions.show', $prescriptionId)
                ->with('success', __('Prescription created successfully.'));

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            \Log::error('Prescription creation failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            // Redirect back to create form with error
            return redirect()->route('prescriptions.create')
                ->withInput()
                ->with('error', __('Error creating prescription: ') . $e->getMessage());
        }
    }

    /**
     * Display the specified prescription.
     */
    public function show($id)
    {
        $prescription = DB::table('prescriptions')
            ->leftJoin('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->leftJoin('users as doctors', 'prescriptions.doctor_id', '=', 'doctors.id')
            ->select(
                'prescriptions.*',
                'patients.first_name as patient_first_name',
                'patients.last_name as patient_last_name',
                'patients.patient_id',
                'patients.date_of_birth',
                'patients.gender',
                'patients.weight',
                'patients.height',
                'patients.allergies',
                'patients.phone as patient_phone',
                'patients.email as patient_email',
                'doctors.first_name as doctor_first_name',
                'doctors.last_name as doctor_last_name',
                'doctors.phone as doctor_phone',
                'doctors.email as doctor_email'
            )
            ->where('prescriptions.id', $id)
            ->where('prescriptions.clinic_id', Auth::user()->clinic_id)
            ->first();

        if (!$prescription) {
            abort(404, 'Prescription not found');
        }

        // Get prescription medicines
        $medicines = DB::table('prescription_medicines')
            ->leftJoin('medicines', 'prescription_medicines.medicine_id', '=', 'medicines.id')
            ->select(
                'prescription_medicines.medicine_name as name',
                'medicines.dosage as strength',
                'prescription_medicines.dosage',
                'prescription_medicines.frequency',
                'prescription_medicines.duration',
                'prescription_medicines.instructions'
            )
            ->where('prescription_medicines.prescription_id', $id)
            ->get();

        $prescription->medicines = $medicines;

        return view('prescriptions.show', compact('prescription'));
    }

    /**
     * Show the form for editing the specified prescription.
     */
    public function edit($id)
    {
        $prescription = DB::table('prescriptions')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->first();

        if (!$prescription) {
            abort(404, 'Prescription not found');
        }

        $patients = DB::table('patients')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_id')
            ->get();

        return view('prescriptions.edit', compact('prescription', 'patients'));
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'diagnosis' => 'nullable|string|max:500',
            'symptoms' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'prescription_date' => 'required|date',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $updated = DB::table('prescriptions')
            ->where('id', $id)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->update([
                'patient_id' => $request->patient_id,
                'diagnosis' => $request->diagnosis,
                'symptoms' => $request->symptoms,
                'instructions' => $request->instructions,
                'notes' => $request->notes,
                'prescription_date' => $request->prescription_date,
                'status' => $request->status,
                'updated_at' => now(),
            ]);

        if ($updated) {
            return redirect()->route('prescriptions.show', $id)
                ->with('success', __('Prescription updated successfully.'));
        }

        return back()->with('error', __('Prescription not found or access denied.'));
    }

    /**
     * Remove the specified prescription.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete prescription medicines first
            DB::table('prescription_medicines')
                ->where('prescription_id', $id)
                ->delete();

            // Delete prescription
            $deleted = DB::table('prescriptions')
                ->where('id', $id)
                ->where('clinic_id', Auth::user()->clinic_id)
                ->delete();

            DB::commit();

            if ($deleted) {
                return redirect()->route('prescriptions.index')
                    ->with('success', __('Prescription deleted successfully.'));
            }

            return back()->with('error', __('Prescription not found or access denied.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('Error deleting prescription: ') . $e->getMessage());
        }
    }

    /**
     * Generate PDF for the prescription.
     */
    public function generatePDF($id)
    {
        // This would generate a PDF of the prescription
        // For now, redirect to show page
        return redirect()->route('prescriptions.show', $id)
            ->with('info', __('PDF generation feature will be implemented soon.'));
    }
}
