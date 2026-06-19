<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticAftercareIssue;
use App\Models\AestheticAftercareTemplate;
use App\Models\AestheticSession;
use App\Models\AestheticInventory;
use App\Models\AestheticTreatment;
use App\Models\ConsentForm;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\SessionImage;
use App\Models\SessionInventoryUsage;
use App\Models\User;
use App\Services\StorageQuotaService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

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
            'assignedUser',
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
        $followUpReminders = $this->getOutstandingPackageReminderQuery()
            ->latest('next_due_date')
            ->take(6)
            ->get();

        $stats = [
            'total' => AestheticSession::count(),
            'scheduled' => AestheticSession::where('status', 'scheduled')->count(),
            'completed' => AestheticSession::where('status', 'completed')->count(),
            'follow_up_due' => $this->getOutstandingPackageReminderQuery()->count(),
        ];

        return view('aesthetic.sessions.index', compact('sessions', 'patientPackages', 'stats', 'followUpReminders'));
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
        $assignableUsers = $this->getAssignableUsers();

        $selectedPackageId = $request->get('patient_package_id');
        $selectedPatientId = $request->get('patient_id');
        $defaultSessionMode = $request->get('session_mode', 'package');
        $selectedPackage = $selectedPackageId ? PatientPackage::find($selectedPackageId) : null;
        $nextSessionNumber = $selectedPackage ? ($selectedPackage->sessions()->count() + 1) : 1;

        return view('aesthetic.sessions.create', compact(
            'patientPackages', 'inventoryItems', 'patients', 'treatments', 'assignableUsers',
            'selectedPackageId', 'selectedPatientId', 'defaultSessionMode', 'nextSessionNumber'
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
                'assigned_user_id' => 'nullable|integer|exists:users,id',
                'status' => 'required|in:scheduled,started,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientPackageTenant($validated['patient_package_id']);
        } else {
            $validated = $request->validate([
                'patient_id' => 'required|integer|exists:patients,id',
                'treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
                'session_number' => 'required|integer|min:1',
                'session_date' => 'required|date',
                'assigned_user_id' => 'nullable|integer|exists:users,id',
                'status' => 'required|in:scheduled,started,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientTenant($validated['patient_id']);
            if (!empty($validated['treatment_id'])) {
                $this->validateTreatmentTenant($validated['treatment_id']);
            }

            if ($request->input('next_action') === 'create_invoice' && empty($validated['treatment_id'])) {
                return back()->withInput()->withErrors([
                    'treatment_id' => __('Please select a treatment so the invoice item and price can be filled automatically.'),
                ]);
            }
        }

        if (!empty($validated['assigned_user_id'])) {
            $this->validateAssignableUser($validated['assigned_user_id']);
        }

        if (in_array($validated['status'], ['started', 'completed'], true)) {
            return back()->withInput()->withErrors([
                'status' => __('Consent must be captured before a session can be marked as started or completed.'),
            ]);
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

        if ($request->input('next_action') === 'create_invoice') {
            return redirect()->route('aesthetic.invoices.create', ['session_id' => $session->id])
                ->with('success', __('Session created successfully. Complete the invoice details below.'));
        }

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
            ->with(['patientPackage.package.treatments', 'patient', 'treatment', 'images', 'consentForms', 'aftercareIssues'])
            ->orderByDesc('session_date')
            ->paginate(10);

        $patientPackages = PatientPackage::where('patient_id', $patient->id)
            ->with('package.treatments')
            ->latest()
            ->get();

        $followUpReminders = $this->getOutstandingPackageReminderQuery()
            ->whereHas('patientPackage', fn ($query) => $query->where('patient_id', $patient->id))
            ->latest('next_due_date')
            ->get();

        $followUpReminderMap = $followUpReminders->keyBy('patient_package_id');

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
            'follow_up_due' => $followUpReminders->count(),
        ];

        $consentForms = ConsentForm::with(['session', 'treatment', 'patientFile'])
            ->where('patient_id', $patient->id)
            ->latest('signed_at')
            ->get();

        $aftercareIssues = AestheticAftercareIssue::with(['session', 'template', 'patientFile'])
            ->where('patient_id', $patient->id)
            ->latest('issued_at')
            ->get();

        return view('aesthetic.patients.show', compact('patient', 'sessions', 'patientPackages', 'stats', 'consentForms', 'aftercareIssues', 'followUpReminders', 'followUpReminderMap'));
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
            'patientPackage.package.treatment',
            'patient',
            'treatment',
            'assignedUser',
            'images',
            'inventoryUsages.product',
            'consentForms.patientFile',
            'consentForms.treatment',
            'consentForms.creator',
            'aftercareIssues.patientFile',
            'aftercareIssues.template',
            'aftercareIssues.issuer',
        ]);

        $aftercareTemplates = AestheticAftercareTemplate::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $sessionTreatments = $this->getSessionTreatments($aestheticSession);
        $suggestedNextDueDate = $this->getSuggestedNextDueDate($aestheticSession);

        return view('aesthetic.sessions.show', compact('aestheticSession', 'aftercareTemplates', 'sessionTreatments', 'suggestedNextDueDate'));
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
        $assignableUsers = $this->getAssignableUsers();

        return view('aesthetic.sessions.edit', compact('aestheticSession', 'patientPackages', 'inventoryItems', 'patients', 'treatments', 'assignableUsers'));
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
                'assigned_user_id' => 'nullable|integer|exists:users,id',
                'next_due_date' => 'nullable|date|after_or_equal:session_date',
                'status' => 'required|in:scheduled,started,completed,cancelled,no_show',
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
                'assigned_user_id' => 'nullable|integer|exists:users,id',
                'next_due_date' => 'nullable|date|after_or_equal:session_date',
                'status' => 'required|in:scheduled,started,completed,cancelled,no_show',
                'notes' => 'nullable|string|max:2000',
            ]);
            $this->validatePatientTenant($validated['patient_id']);
            if (!empty($validated['treatment_id'])) {
                $this->validateTreatmentTenant($validated['treatment_id']);
            }
            $validated['patient_package_id'] = null;
        }

        if (!empty($validated['assigned_user_id'])) {
            $this->validateAssignableUser($validated['assigned_user_id']);
        }

        $validated['next_due_date'] = $this->resolveNextDueDate($request, $aestheticSession, $mode, $validated['status']);

        if ($error = $this->statusConsentError($validated['status'], $aestheticSession)) {
            return back()->withInput()->withErrors(['status' => $error]);
        }

        DB::transaction(function () use ($aestheticSession, $validated, $request) {
            // If inventory_items are submitted (full edit form), restore old stock,
            // delete old usages, and re-record from the form.
            if ($request->has('inventory_items')) {
                foreach ($aestheticSession->inventoryUsages as $usage) {
                    $usage->product->addStock($usage->quantity_used);
                }
                $aestheticSession->inventoryUsages()->delete();

                if ($validated['status'] !== 'cancelled') {
                    $this->processInventoryUsage($aestheticSession, $request);
                }
            } elseif ($validated['status'] === 'cancelled') {
                // Quick cancel: restore stock and clear usages even without inventory_items
                foreach ($aestheticSession->inventoryUsages as $usage) {
                    $usage->product->addStock($usage->quantity_used);
                }
                $aestheticSession->inventoryUsages()->delete();
            }

            $aestheticSession->update($validated);
        });

        return redirect()->route('aesthetic.sessions.show', $aestheticSession)
            ->with('success', __('Session updated successfully.'));
    }

    /**
     * Send a WhatsApp follow-up reminder for a package session.
     */
    public function sendWhatsAppReminder(AestheticSession $aestheticSession, WhatsAppService $whatsAppService): JsonResponse
    {
        $this->authorizeTenant($aestheticSession);

        $aestheticSession->loadMissing([
            'patientPackage.patient',
            'patientPackage.package.treatments',
            'patient',
            'treatment',
        ]);

        if (!$aestheticSession->next_due_date) {
            return response()->json([
                'success' => false,
                'message' => __('Add a next due date before sending a reminder.'),
            ], 422);
        }

        $patient = $aestheticSession->resolvedPatient;
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => __('This reminder is not linked to a patient.'),
            ], 422);
        }

        $phoneNumber = $patient->whatsapp_phone ?: $patient->phone;
        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => __('The patient does not have a WhatsApp or phone number.'),
            ], 422);
        }

        $message = $this->buildFollowUpReminderMessage($aestheticSession, $patient);

        $whatsAppService->setClinicContext(Auth::user()->clinic_id);
        $result = $whatsAppService->sendMessage($phoneNumber, $message);

        $status = ($result['success'] ?? false)
            ? NotificationLog::STATUS_SENT
            : (isset($result['whatsapp_url']) ? NotificationLog::STATUS_PENDING : NotificationLog::STATUS_FAILED);

        $this->logFollowUpReminderAttempt($aestheticSession, $patient, $phoneNumber, $message, $status, $result);

        if (($result['success'] ?? false) || isset($result['whatsapp_url'])) {
            return response()->json([
                'success' => true,
                'message' => ($result['success'] ?? false)
                    ? __('Reminder sent successfully.')
                    : __('Opening WhatsApp to finish sending the reminder.'),
                'whatsapp_url' => $result['whatsapp_url'] ?? null,
                'auto_open' => isset($result['whatsapp_url']),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? __('Failed to send reminder.'),
        ], 422);
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
     * Get active clinic users who can be assigned to run sessions.
     */
    private function getAssignableUsers()
    {
        return User::where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
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

    /**
     * Validate that the assigned user belongs to the current clinic.
     */
    private function validateAssignableUser(int $userId): void
    {
        $exists = User::where('id', $userId)
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('is_active', true)
            ->exists();

        if (!$exists) {
            abort(403, __('The selected assigned person is not available for your clinic.'));
        }
    }

    private function statusConsentError(string $status, ?AestheticSession $session = null): ?string
    {
        if (!in_array($status, ['started', 'completed'], true)) {
            return null;
        }

        if (!$session || !$session->consentForms()->exists()) {
            return __('Consent must be captured before a session can be marked as started or completed.');
        }

        return null;
    }

    private function getSessionTreatments(AestheticSession $session)
    {
        if ($session->isDirectSession && $session->treatment) {
            return collect([$session->treatment]);
        }

        $package = $session->patientPackage?->package;
        if (!$package) {
            return collect();
        }

        $treatments = $package->treatments ?? collect();
        if ($treatments->isNotEmpty()) {
            return $treatments->values();
        }

        return $package->treatment ? collect([$package->treatment]) : collect();
    }

    private function getSuggestedNextDueDate(AestheticSession $session): ?Carbon
    {
        if (!$session->isPackageSession || !$session->has_pending_follow_up_slot) {
            return null;
        }

        return $session->next_due_date ?: $session->suggested_next_due_date;
    }

    private function resolveNextDueDate(Request $request, AestheticSession $session, string $mode, string $status): ?string
    {
        if ($mode !== 'package' || $status !== 'completed') {
            return null;
        }

        if (!$session->has_pending_follow_up_slot) {
            return null;
        }

        if ($request->has('next_due_date')) {
            return $request->input('next_due_date') ?: null;
        }

        return optional($session->next_due_date)->format('Y-m-d');
    }

    private function getOutstandingPackageReminderQuery()
    {
        return AestheticSession::with(['patientPackage.patient', 'patientPackage.package'])
            ->whereNotNull('patient_package_id')
            ->where('status', 'completed')
            ->whereNotNull('next_due_date')
            ->whereHas('patientPackage', fn ($query) => $query->where('sessions_remaining', '>', 0))
            ->whereRaw('NOT EXISTS (
                SELECT 1 FROM aesthetic_sessions future_sessions
                WHERE future_sessions.patient_package_id = aesthetic_sessions.patient_package_id
                  AND future_sessions.session_number > aesthetic_sessions.session_number
                  AND future_sessions.deleted_at IS NULL
            )');
    }

    private function buildFollowUpReminderMessage(AestheticSession $session, Patient $patient): string
    {
        $clinic = Auth::user()?->clinic;
        $packageName = $session->patientPackage?->package?->name ?? __('Treatment Package');
        $nextSessionNumber = $session->session_number + 1;
        $dueDate = optional($session->next_due_date)->format('Y-m-d') ?? '';
        $customTemplate = null;

        if (Schema::hasTable('notification_settings') && Auth::user()?->clinic_id) {
            $customTemplate = NotificationSetting::withoutGlobalScopes()
                ->where('clinic_id', Auth::user()->clinic_id)
                ->value('follow_up_reminder_template');
        }

        $template = $customTemplate ?: "Hello {patient_name},\nThis is a reminder that your next {package_name} session (Session {next_session_number}) is due on {due_date}.\nPlease contact {clinic_name} to confirm your visit.\n— {clinic_name}";

        return strtr($template, [
            '{patient_name}' => $patient->full_name ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')),
            '{patient_first_name}' => $patient->first_name ?? '',
            '{patient_last_name}' => $patient->last_name ?? '',
            '{patient_id}' => $patient->patient_id ?? '',
            '{patient_phone}' => $patient->whatsapp_phone ?: ($patient->phone ?? ''),
            '{clinic_name}' => $clinic?->name ?? config('app.name'),
            '{clinic_phone}' => $clinic?->phone ?? '',
            '{appointment_date}' => $dueDate,
            '{appointment_time}' => '',
            '{doctor_name}' => trim((Auth::user()?->first_name ?? '') . ' ' . (Auth::user()?->last_name ?? '')),
            '{due_date}' => $dueDate,
            '{package_name}' => $packageName,
            '{session_number}' => (string) $session->session_number,
            '{next_session_number}' => (string) $nextSessionNumber,
            '{treatment_name}' => $session->treatment?->name ?? ($session->patientPackage?->package?->treatment?->name ?? __('Treatment Session')),
        ]);
    }

    private function logFollowUpReminderAttempt(
        AestheticSession $session,
        Patient $patient,
        string $phoneNumber,
        string $message,
        string $status,
        array $result
    ): void {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        NotificationLog::withoutGlobalScopes()->create([
            'clinic_id' => Auth::user()->clinic_id,
            'patient_id' => $patient->id,
            'type' => NotificationLog::TYPE_FOLLOW_UP,
            'channel' => 'whatsapp',
            'recipient' => $phoneNumber,
            'message' => $message,
            'status' => $status,
            'error_message' => $result['error'] ?? null,
            'external_id' => $result['message_id'] ?? $result['message_sid'] ?? null,
            'notifiable_type' => AestheticSession::class,
            'notifiable_id' => $session->id,
            'metadata' => [
                'next_due_date' => optional($session->next_due_date)->format('Y-m-d'),
                'package_name' => $session->patientPackage?->package?->name,
                'whatsapp_url' => $result['whatsapp_url'] ?? null,
            ],
            'sent_at' => $status === NotificationLog::STATUS_SENT ? now() : null,
        ]);
    }
}
