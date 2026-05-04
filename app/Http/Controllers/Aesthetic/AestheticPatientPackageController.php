<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\PatientPackage;
use App\Models\AestheticPackage;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AestheticPatientPackageController extends Controller
{
    /**
     * Display a listing of patient packages.
     */
    public function index(Request $request)
    {
        $query = PatientPackage::with(['patient', 'package.treatments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhereHas('package', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'completed') {
                $query->completed();
            }
        }

        $packages = $query->latest('purchase_date')->paginate(15);

        $patients = $this->getTenantPatients();
        $aestheticPackages = $this->getTenantPackages();

        $stats = [
            'total' => PatientPackage::count(),
            'active' => PatientPackage::active()->count(),
            'completed' => PatientPackage::completed()->count(),
        ];

        return view('aesthetic.patient-packages.index', compact('packages', 'patients', 'aestheticPackages', 'stats'));
    }

    /**
     * Show the form for creating a new patient package.
     */
    public function create(Request $request)
    {
        $patients = $this->getTenantPatients();
        $aestheticPackages = $this->getTenantPackages();

        return view('aesthetic.patient-packages.create', compact('patients', 'aestheticPackages'));
    }

    /**
     * Store a newly created patient package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'package_id' => 'required|integer|exists:aesthetic_packages,id',
            'purchase_date' => 'required|date',
            'sessions_remaining' => 'nullable|integer|min:0',
        ]);

        $this->validatePatientTenant($validated['patient_id']);
        $this->validatePackageTenant($validated['package_id']);

        $package = AestheticPackage::findOrFail($validated['package_id']);

        $validated['tenant_id'] = Auth::user()->clinic?->tenant_id;
        $validated['sessions_used'] = 0;
        $validated['sessions_remaining'] = $validated['sessions_remaining'] ?? $package->total_sessions;

        PatientPackage::create($validated);

        return redirect()->route('aesthetic.patient-packages.index')
            ->with('success', __('Package assigned to patient successfully.'));
    }

    /**
     * Show the form for editing the specified patient package.
     */
    public function edit(PatientPackage $patientPackage)
    {
        $this->authorizeTenant($patientPackage);

        $patients = $this->getTenantPatients();
        $aestheticPackages = $this->getTenantPackages();

        return view('aesthetic.patient-packages.edit', compact('patientPackage', 'patients', 'aestheticPackages'));
    }

    /**
     * Update the specified patient package.
     */
    public function update(Request $request, PatientPackage $patientPackage)
    {
        $this->authorizeTenant($patientPackage);

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'package_id' => 'required|integer|exists:aesthetic_packages,id',
            'purchase_date' => 'required|date',
            'sessions_remaining' => 'required|integer|min:0',
            'sessions_used' => 'required|integer|min:0',
        ]);

        $this->validatePatientTenant($validated['patient_id']);
        $this->validatePackageTenant($validated['package_id']);

        $patientPackage->update($validated);

        return redirect()->route('aesthetic.patient-packages.index')
            ->with('success', __('Patient package updated successfully.'));
    }

    /**
     * Remove the specified patient package (soft delete).
     */
    public function destroy(PatientPackage $patientPackage)
    {
        $this->authorizeTenant($patientPackage);

        $patientPackage->delete();

        return redirect()->route('aesthetic.patient-packages.index')
            ->with('success', __('Patient package removed successfully.'));
    }

    /**
     * Use one session from the patient package.
     */
    public function useSession(PatientPackage $patientPackage)
    {
        $this->authorizeTenant($patientPackage);

        if ($patientPackage->useSession()) {
            return redirect()->back()
                ->with('success', __('Session used successfully. Remaining sessions: :count', ['count' => $patientPackage->sessions_remaining]));
        }

        return redirect()->back()
            ->with('error', __('No remaining sessions in this package.'));
    }

    /**
     * Ensure the patient package belongs to the current tenant.
     */
    private function authorizeTenant(PatientPackage $patientPackage): void
    {
        $user = Auth::user();
        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $patientPackage->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to access this patient package.'));
        }
    }

    /**
     * Get patients available for the current tenant/clinic.
     */
    private function getTenantPatients()
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id;

        return Patient::when($clinicId, function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        })
        ->orderBy('first_name')
        ->get();
    }

    /**
     * Get packages available for the current tenant.
     */
    private function getTenantPackages()
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        return AestheticPackage::byTenant($tenantId)
            ->with('treatments')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate that the patient belongs to the current clinic/tenant.
     */
    private function validatePatientTenant(int $patientId): void
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id;

        $patient = Patient::findOrFail($patientId);

        if ($clinicId && $patient->clinic_id !== $clinicId) {
            abort(403, __('The selected patient is not available for your clinic.'));
        }
    }

    /**
     * Validate that the package belongs to the current tenant.
     */
    private function validatePackageTenant(int $packageId): void
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        $exists = AestheticPackage::byTenant($tenantId)
            ->where('id', $packageId)
            ->exists();

        if (!$exists) {
            abort(403, __('The selected package is not available for your clinic.'));
        }
    }
}
