<?php

namespace App\Http\Controllers;

use App\Models\SimplePrescription;
use App\Models\SimplePrescriptionMedicine;
use App\Models\Patient;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SimplePrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = SimplePrescription::with(['patient', 'doctor'])
            ->forClinic(Auth::user()->clinic_id);

        // Filter by patient name
        if ($request->filled('patient_name')) {
            $patientName = $request->patient_name;
            $query->whereHas('patient', function ($q) use ($patientName) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$patientName}%")
                  ->orWhere('first_name', 'LIKE', "%{$patientName}%")
                  ->orWhere('last_name', 'LIKE', "%{$patientName}%")
                  ->orWhere('patient_id', 'LIKE', "%{$patientName}%");
            });
        }

        // Filter by patient ID (for direct patient filtering)
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('prescribed_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('prescribed_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get patients for dropdown filter
        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('simple-prescriptions.index', compact('prescriptions', 'patients'));
    }

    public function create(Request $request)
    {
        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $medicines = Medicine::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedPatientId = $request->get('patient_id');

        return view('simple-prescriptions.create', compact('patients', 'medicines', 'selectedPatientId'));
    }

    public function store(Request $request)
    {
        // Simple validation
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'prescribed_date' => 'required|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'nullable|string|max:255',
            'medicines.*.dosage' => 'nullable|string|max:100',
            'medicines.*.frequency' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instructions' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create prescription
            $prescription = SimplePrescription::create([
                'patient_id' => $request->patient_id,
                'doctor_id' => Auth::id(),
                'clinic_id' => Auth::user()->clinic_id,
                'prescription_number' => SimplePrescription::generatePrescriptionNumber(),
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'prescribed_date' => $request->prescribed_date,
                'status' => 'active'
            ]);

            // Add medicines if provided
            if ($request->medicines) {
                foreach ($request->medicines as $medicine) {
                    if (!empty($medicine['name'])) {
                        // Check if it's a new medicine (starts with 'new:')
                        $medicineName = $medicine['name'];
                        if (strpos($medicineName, 'new:') === 0) {
                            // Create new medicine
                            $newMedicineName = substr($medicineName, 4); // Remove 'new:' prefix
                            $newMedicine = Medicine::create([
                                'name' => $newMedicineName,
                                'generic_name' => $newMedicineName,
                                'dosage' => $medicine['strength'] ?? null,
                                'form' => 'other',
                                'is_frequent' => false,
                                'clinic_id' => Auth::user()->clinic_id,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $medicineName = $newMedicineName;
                        }

                        SimplePrescriptionMedicine::create([
                            'prescription_id' => $prescription->id,
                            'medicine_name' => $medicineName,
                            'dosage' => $medicine['dosage'] ?? null,
                            'frequency' => $medicine['frequency'] ?? null,
                            'duration' => $medicine['duration'] ?? null,
                            'instructions' => $medicine['instructions'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('simple-prescriptions.show', $prescription->id)
                ->with('success', 'Prescription created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating prescription: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines'])
            ->forClinic(Auth::user()->clinic_id)
            ->findOrFail($id);

        return view('simple-prescriptions.show', compact('prescription'));
    }

    public function edit($id)
    {
        $prescription = SimplePrescription::with('medicines')
            ->forClinic(Auth::user()->clinic_id)
            ->findOrFail($id);

        $patients = Patient::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $medicines = Medicine::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('simple-prescriptions.edit', compact('prescription', 'patients', 'medicines'));
    }

    public function update(Request $request, $id)
    {
        $prescription = SimplePrescription::forClinic(Auth::user()->clinic_id)->findOrFail($id);

        // Simple validation
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'prescribed_date' => 'required|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'medicines' => 'nullable|array',
            'medicines.*.name' => 'nullable|string|max:255',
            'medicines.*.dosage' => 'nullable|string|max:100',
            'medicines.*.frequency' => 'nullable|string|max:100',
            'medicines.*.duration' => 'nullable|string|max:100',
            'medicines.*.instructions' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update prescription
            $prescription->update([
                'patient_id' => $request->patient_id,
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'prescribed_date' => $request->prescribed_date,
            ]);

            // Delete existing medicines and add new ones
            $prescription->medicines()->delete();

            if ($request->medicines) {
                foreach ($request->medicines as $medicine) {
                    if (!empty($medicine['name'])) {
                        // Check if it's a new medicine (starts with 'new:')
                        $medicineName = $medicine['name'];
                        if (strpos($medicineName, 'new:') === 0) {
                            // Create new medicine
                            $newMedicineName = substr($medicineName, 4); // Remove 'new:' prefix
                            $newMedicine = Medicine::create([
                                'name' => $newMedicineName,
                                'generic_name' => $newMedicineName,
                                'dosage' => $medicine['strength'] ?? null,
                                'form' => 'other',
                                'is_frequent' => false,
                                'clinic_id' => Auth::user()->clinic_id,
                                'created_by' => Auth::id(),
                                'is_active' => true,
                            ]);
                            $medicineName = $newMedicineName;
                        }

                        SimplePrescriptionMedicine::create([
                            'prescription_id' => $prescription->id,
                            'medicine_name' => $medicineName,
                            'dosage' => $medicine['dosage'] ?? null,
                            'frequency' => $medicine['frequency'] ?? null,
                            'duration' => $medicine['duration'] ?? null,
                            'instructions' => $medicine['instructions'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('simple-prescriptions.show', $prescription->id)
                ->with('success', 'Prescription updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating prescription: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $prescription = SimplePrescription::forClinic(Auth::user()->clinic_id)->findOrFail($id);

        $prescription->delete();

        return redirect()->route('simple-prescriptions.index')
            ->with('success', 'Prescription deleted successfully!');
    }

    public function pdf($id)
    {
        $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
            ->forClinic(Auth::user()->clinic_id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('simple-prescriptions.pdf', compact('prescription'));

        $filename = 'prescription-' . $prescription->prescription_number . '.pdf';

        return $pdf->download($filename);
    }

    public function print($id)
    {
        $prescription = SimplePrescription::with(['patient', 'doctor', 'medicines', 'clinic'])
            ->forClinic(Auth::user()->clinic_id)
            ->findOrFail($id);

        return view('simple-prescriptions.print', compact('prescription'));
    }


}
