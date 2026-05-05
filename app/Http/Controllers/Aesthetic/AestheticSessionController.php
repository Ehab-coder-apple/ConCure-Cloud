<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticSession;
use App\Models\AestheticInventory;
use App\Models\AestheticTreatment;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\SessionImage;
use App\Models\SessionInventoryUsage;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AestheticSessionController extends Controller
{
    /**
     * Display sessions for a patient package.
     */
    public function index(Request $request)
    {
        $query = AestheticSession::with([
            'patientPackage.patient',
            'patientPackage.package.treatments',
            'patient',
            'treatment',
            'images',
        ]);

        if ($request->filled('session_type')) {
            if ($request->session_type === 'package') {
                $query->whereNotNull('patient_package_id');
            } elseif ($request->session_type === 'direct') {
                $query->whereNull('patient_package_id');
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Search package sessions by patient name or package name
                $q->whereHas('patientPackage.patient', function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('patientPackage.package', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                // Search direct sessions by patient name or treatment name
                ->orWhereHas('patient', function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('treatment', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $sessions = $query->latest('session_date')->paginate(15);

        $patientPackages = $this->getTenantPatientPackages();

        $stats = [
            'total' => AestheticSession::count(),
            'scheduled' => AestheticSession::where('status', 'scheduled')->count(),
            'completed' => AestheticSession::where('status', 'completed')->count(),
        ];

        return view('aesthetic.sessions.index', compact('sessions', 'patientPackages', 'stats'));
    }

    /**
     * Show the form for creating a new session.
     */
    public function create(Request $request)
    {
        $patientPackages = $this->getTenantPatientPackages();
        $inventoryItems = $this->getTenantInventory();
        $patients = $this->getTenantPatients();
        $treatments = $this->getTenantTreatments();

        $selectedPackageId = $request->get('patient_package_id');
        $selectedPackage = $selectedPackageId ? PatientPackage::find($selectedPackageId) : null;
        $nextSessionNumber = $selectedPackage ? ($selectedPackage->sessions()->count() + 1) : 1;

        return view('aesthetic.sessions.create', compact(
            'patientPackages', 'inventoryItems', 'patients', 'treatments',
            'selectedPackageId', 'nextSessionNumber'
        ));
    }

    /**
     * Store a newly created session.
     */
    public function store(Request $request)
    {
        $mode = $request->input('session_mode', 'package');

        if ($mode === 'package') {
            $validated = $request->validate([
                'patient_package_id' => 'required|integer|exists:patient_packages,id',
                'session_number' => 'required|integer|min:1',
                'session_date' => 'required|date',
                'status' => 'required|in:scheduled,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientPackageTenant($validated['patient_package_id']);
        } else {
            $validated = $request->validate([
                'patient_id' => 'required|integer|exists:patients,id',
                'treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
                'session_number' => 'required|integer|min:1',
                'session_date' => 'required|date',
                'status' => 'required|in:scheduled,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientTenant($validated['patient_id']);
            if (!empty($validated['treatment_id'])) {
                $this->validateTreatmentTenant($validated['treatment_id']);
            }
        }

        $validated['tenant_id'] = Auth::user()->clinic?->tenant_id;

        $session = DB::transaction(function () use ($validated, $request) {
            $session = AestheticSession::create($validated);

            // Always record inventory items from the form (stock deducted immediately)
            if ($validated['status'] !== 'cancelled') {
                $this->processInventoryUsage($session, $request);
            }

            return $session;
        });

        return redirect()->route('aesthetic.sessions.show', $session)
            ->with('success', __('Session created successfully.'));
    }

    /**
     * Show patient aesthetic timeline (patient profile module).
     */
    public function patientShow(Patient $patient)
    {
        $user = Auth::user();
        $clinicId = $user->clinic_id;

        if ($patient->clinic_id !== $clinicId) {
            abort(403, __('This patient is not in your clinic.'));
        }

        $sessions = AestheticSession::where(function ($q) use ($patient) {
                $q->whereHas('patientPackage', function ($sq) use ($patient) {
                    $sq->where('patient_id', $patient->id);
                })->orWhere('patient_id', $patient->id);
            })
            ->with(['patientPackage.package.treatments', 'patient', 'treatment', 'images'])
            ->orderByDesc('session_date')
            ->paginate(10);

        $patientPackages = PatientPackage::where('patient_id', $patient->id)
            ->with('package.treatments')
            ->latest()
            ->get();

        $stats = [
            'total_sessions' => $sessions->total(),
            'completed' => AestheticSession::where(function ($q) use ($patient) {
                    $q->whereHas('patientPackage', function ($sq) use ($patient) {
                        $sq->where('patient_id', $patient->id);
                    })->orWhere('patient_id', $patient->id);
                })->where('status', 'completed')->count(),
            'scheduled' => AestheticSession::where(function ($q) use ($patient) {
                    $q->whereHas('patientPackage', function ($sq) use ($patient) {
                        $sq->where('patient_id', $patient->id);
                    })->orWhere('patient_id', $patient->id);
                })->where('status', 'scheduled')->count(),
            'packages' => $patientPackages->count(),
        ];

        return view('aesthetic.patients.show', compact('patient', 'sessions', 'patientPackages', 'stats'));
    }

    /**
     * Display the specified session with images and inventory usage.
     */
    public function show(AestheticSession $aestheticSession)
    {
        $this->authorizeTenant($aestheticSession);

        $aestheticSession->load([
            'patientPackage.patient',
            'patientPackage.package.treatments',
            'patient',
            'treatment',
            'images',
            'inventoryUsages.product',
        ]);

        return view('aesthetic.sessions.show', compact('aestheticSession'));
    }

    /**
     * Show the form for editing the specified session.
     */
    public function edit(AestheticSession $aestheticSession)
    {
        $this->authorizeTenant($aestheticSession);

        $patientPackages = $this->getTenantPatientPackages();
        $inventoryItems = $this->getTenantInventory();
        $patients = $this->getTenantPatients();
        $treatments = $this->getTenantTreatments();

        return view('aesthetic.sessions.edit', compact('aestheticSession', 'patientPackages', 'inventoryItems', 'patients', 'treatments'));
    }

    /**
     * Update the specified session.
     */
    public function update(Request $request, AestheticSession $aestheticSession)
    {
        $this->authorizeTenant($aestheticSession);

        $mode = $request->input('session_mode', $aestheticSession->isPackageSession ? 'package' : 'direct');

        if ($mode === 'package') {
            $validated = $request->validate([
                'patient_package_id' => 'required|integer|exists:patient_packages,id',
                'session_number' => 'required|integer|min:1',
                'session_date' => 'required|date',
                'status' => 'required|in:scheduled,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientPackageTenant($validated['patient_package_id']);
            $validated['patient_id'] = null;
            $validated['treatment_id'] = null;
        } else {
            $validated = $request->validate([
                'patient_id' => 'required|integer|exists:patients,id',
                'treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
                'session_number' => 'required|integer|min:1',
                'session_date' => 'required|date',
                'status' => 'required|in:scheduled,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientTenant($validated['patient_id']);
            if (!empty($validated['treatment_id'])) {
                $this->validateTreatmentTenant($validated['treatment_id']);
            }
            $validated['patient_package_id'] = null;
        }

        DB::transaction(function () use ($aestheticSession, $validated, $request) {
            // Restore stock for any existing inventory usages and delete them
            foreach ($aestheticSession->inventoryUsages as $usage) {
                $usage->product->addStock($usage->quantity_used);
            }
            $aestheticSession->inventoryUsages()->delete();

            // Re-record inventory from the form and deduct stock (unless cancelled)
            if ($validated['status'] !== 'cancelled') {
                $this->processInventoryUsage($aestheticSession, $request);
            }

            $aestheticSession->update($validated);
        });

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __('Session updated successfully.'));
    }

    /**
     * Remove the specified session (soft delete).
     */
    public function destroy(AestheticSession $aestheticSession)
    {
        $this->authorizeTenant($aestheticSession);

        $aestheticSession->delete();

        return redirect()->route('aesthetic.sessions.index')
            ->with('success', __('Session deleted successfully.'));
    }

    /**
     * Upload images to a session.
     */
    public function uploadImages(Request $request, AestheticSession $aestheticSession)
    {
        $this->authorizeTenant($aestheticSession);

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|file|max:' . config('app.concure.max_file_size', 10240) . '|mimes:jpg,jpeg,png,webp',
            'type' => 'required|in:before,after',
        ]);

        $clinicId = Auth::user()->clinic_id;
        $tenantDir = StorageQuotaService::getTenantStoragePath($clinicId, 'images');

        $uploaded = 0;
        foreach ($request->file('images') as $image) {
            $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs($tenantDir, $filename, StorageQuotaService::SPACES_DISK);

            $fileSize = $image->getSize();

            SessionImage::create([
                'session_id' => $aestheticSession->id,
                'type' => $request->type,
                'image_path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'file_size' => $fileSize,
            ]);

            app(StorageQuotaService::class)->incrementUsage($clinicId, $fileSize);
            $uploaded++;
        }

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __(':count image(s) uploaded successfully.', ['count' => $uploaded]));
    }

    /**
     * Delete a specific image from a session.
     */
    public function deleteImage(AestheticSession $aestheticSession, SessionImage $sessionImage)
    {
        $this->authorizeTenant($aestheticSession);

        if ($sessionImage->session_id !== $aestheticSession->id) {
            abort(403, __('Image does not belong to this session.'));
        }

        $sessionImage->delete();

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __('Image deleted successfully.'));
    }

    /**
     * Ensure the session belongs to the current tenant.
     */
    private function authorizeTenant(AestheticSession $session): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $session->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to access this session.'));
        }
    }

    /**
     * Get patient packages for the current tenant.
     */
    private function getTenantPatientPackages()
    {
        $clinicId = Auth::user()->clinic_id;

        return PatientPackage::with(['patient', 'package'])
            ->when($clinicId, function ($q) use ($clinicId) {
                $q->whereHas('patient', function ($pq) use ($clinicId) {
                    $pq->where('clinic_id', $clinicId);
                });
            })
            ->latest()
            ->get();
    }

    /**
     * Get inventory items for the current tenant.
     */
    private function getTenantInventory()
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        return AestheticInventory::byTenant($tenantId)
            ->where('quantity', '>', 0)
            ->orderBy('product_name')
            ->get();
    }

    /**
     * Process inventory usage for a completed session.
     */
    private function processInventoryUsage(AestheticSession $session, Request $request): void
    {
        if (!$request->has('inventory_items')) {
            return;
        }

        foreach ($request->input('inventory_items', []) as $item) {
            if (empty($item['product_id']) || empty($item['quantity_used'])) {
                continue;
            }

            $productId = (int) $item['product_id'];
            $quantityUsed = (int) $item['quantity_used'];

            if ($quantityUsed <= 0) {
                continue;
            }

            $product = AestheticInventory::findOrFail($productId);
            $this->authorizeTenantInventory($product);

            if (!$product->deductStock($quantityUsed)) {
                throw new \RuntimeException(
                    __('Insufficient stock for :product. Available: :available, Requested: :requested', [
                        'product' => $product->product_name,
                        'available' => $product->quantity,
                        'requested' => $quantityUsed,
                    ])
                );
            }

            SessionInventoryUsage::create([
                'session_id' => $session->id,
                'product_id' => $product->id,
                'quantity_used' => $quantityUsed,
            ]);
        }
    }

    /**
     * Validate that the patient package belongs to the current tenant.
     */
    private function validatePatientPackageTenant(int $patientPackageId): void
    {
        $clinicId = Auth::user()->clinic_id;

        $exists = PatientPackage::where('id', $patientPackageId)
            ->when($clinicId, function ($q) use ($clinicId) {
                $q->whereHas('patient', function ($pq) use ($clinicId) {
                    $pq->where('clinic_id', $clinicId);
                });
            })
            ->exists();

        if (!$exists) {
            abort(403, __('The selected patient package is not available for your clinic.'));
        }
    }

    /**
     * Validate inventory item belongs to current tenant.
     */
    private function authorizeTenantInventory(AestheticInventory $item): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $item->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to use this inventory item.'));
        }
    }

    /**
     * Get patients for the current tenant/clinic.
     */
    private function getTenantPatients()
    {
        $clinicId = Auth::user()->clinic_id;

        return Patient::where('clinic_id', $clinicId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Get treatments for the current tenant.
     */
    private function getTenantTreatments()
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        return AestheticTreatment::byTenant($tenantId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Validate that the patient belongs to the current clinic.
     */
    private function validatePatientTenant(int $patientId): void
    {
        $clinicId = Auth::user()->clinic_id;

        $exists = Patient::where('id', $patientId)
            ->where('clinic_id', $clinicId)
            ->exists();

        if (!$exists) {
            abort(403, __('The selected patient is not available for your clinic.'));
        }
    }

    /**
     * Validate that the treatment belongs to the current tenant.
     */
    private function validateTreatmentTenant(int $treatmentId): void
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        $exists = AestheticTreatment::byTenant($tenantId)
            ->where('id', $treatmentId)
            ->exists();

        if (!$exists) {
            abort(403, __('The selected treatment is not available for your clinic.'));
        }
    }
}
