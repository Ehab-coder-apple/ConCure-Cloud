<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticInvoice;
use App\Models\AestheticInvoiceItem;
use App\Models\AestheticSession;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\AestheticTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AestheticInvoiceController extends Controller
{
    /**
     * Display a listing of aesthetic invoices.
     */
    public function index(Request $request)
    {
        $query = AestheticInvoice::with(['patient', 'session', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('invoice_date', [$request->date_from, $request->date_to]);
        }

        $invoices = $query->latest('invoice_date')->paginate(15);

        $patients = $this->getTenantPatients();

        $stats = [
            'total' => AestheticInvoice::count(),
            'draft' => AestheticInvoice::where('status', 'draft')->count(),
            'sent' => AestheticInvoice::where('status', 'sent')->count(),
            'paid' => AestheticInvoice::where('status', 'paid')->count(),
            'partial' => AestheticInvoice::where('status', 'partial')->count(),
            'overdue' => AestheticInvoice::overdue()->count(),
            'cancelled' => AestheticInvoice::where('status', 'cancelled')->count(),
            'total_revenue' => AestheticInvoice::whereIn('status', ['paid', 'partial'])->sum('paid_amount'),
            'outstanding' => AestheticInvoice::whereIn('status', ['draft', 'sent', 'partial', 'overdue'])->sum('balance'),
        ];

        return view('aesthetic.invoices.index', compact('invoices', 'patients', 'stats'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        if (!$this->ensureClinicTenantId()) {
            return redirect()->route('aesthetic.invoices.index')
                ->withErrors(['error' => __('Unable to create invoice. User clinic or tenant not found. Please contact support.')]);
        }

        $patients = $this->getTenantPatients();
        $treatments = $this->getTenantTreatments();
        $sessions = $this->getTenantSessions();
        $clinicCurrency = $this->resolveCurrency();

        $preselectedPatient = null;
        $preselectedSession = null;
        $preselectedPackage = null;
        $lineItems = [];

        if ($request->filled('session_id')) {
            $preselectedSession = AestheticSession::with(['patientPackage.patient', 'patientPackage.package.treatment'])->find($request->session_id);
            if ($preselectedSession) {
                $preselectedPatient = $preselectedSession->patientPackage->patient;
                $preselectedPackage = $preselectedSession->patientPackage;

                if ($preselectedSession->patientPackage->package) {
                    $lineItems[] = [
                        'description' => $preselectedSession->patientPackage->package->name . ' - Session #' . $preselectedSession->session_number,
                        'treatment_id' => $preselectedSession->patientPackage->package->treatment_id,
                        'quantity' => 1,
                        'unit_price' => $preselectedSession->patientPackage->package->final_price,
                        'discount' => 0,
                    ];
                }
            }
        }

        return view('aesthetic.invoices.create', compact(
            'patients', 'treatments', 'sessions',
            'preselectedPatient', 'preselectedSession', 'preselectedPackage', 'lineItems', 'clinicCurrency'
        ));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        if (!$this->ensureClinicTenantId()) {
            return back()->withInput()
                ->withErrors(['error' => __('Unable to create invoice. User clinic or tenant not found. Please contact support.')]);
        }

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'session_id' => 'nullable|integer|exists:aesthetic_sessions,id',
            'patient_package_id' => 'nullable|integer|exists:patient_packages,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $this->validatePatientTenant($validated['patient_id']);
        if (!empty($validated['session_id'])) {
            $this->validateSessionTenant($validated['session_id']);
        }

        $invoice = DB::transaction(function () use ($request, $validated) {
            $invoice = AestheticInvoice::create([
                'clinic_id' => Auth::user()->clinic_id,
                'patient_id' => $validated['patient_id'],
                'session_id' => $validated['session_id'] ?? null,
                'patient_package_id' => $validated['patient_package_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                $invoice->items()->create([
                    'treatment_id' => $item['treatment_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total_price' => max(0, $itemTotal),
                ]);
            }

            $invoice->recalculateSubtotal();

            return $invoice;
        });

        return redirect()->route('aesthetic.invoices.show', $invoice)
            ->with('success', __('Invoice :number created successfully.', ['number' => $invoice->invoice_number]));
    }

    /**
     * Display the specified invoice (receipt view).
     */
    public function show(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        $aestheticInvoice->load(['patient', 'session.patientPackage.package', 'patientPackage.package', 'items.treatment', 'creator']);
        $clinicCurrency = $this->resolveCurrency();

        return view('aesthetic.invoices.show', compact('aestheticInvoice', 'clinicCurrency'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        if (in_array($aestheticInvoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
                ->with('error', __('Paid or cancelled invoices cannot be edited.'));
        }

        $patients = $this->getTenantPatients();
        $treatments = $this->getTenantTreatments();
        $sessions = $this->getTenantSessions();

        $aestheticInvoice->load('items');
        $clinicCurrency = $this->resolveCurrency();

        return view('aesthetic.invoices.edit', compact('aestheticInvoice', 'patients', 'treatments', 'sessions', 'clinicCurrency'));
    }

    /**
     * Update the specified invoice.
     */
    public function update(Request $request, AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        if (!$this->ensureClinicTenantId()) {
            return back()->withInput()
                ->withErrors(['error' => __('Unable to update invoice. User clinic or tenant not found. Please contact support.')]);
        }

        if (in_array($aestheticInvoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
                ->with('error', __('Paid or cancelled invoices cannot be edited.'));
        }

        $validated = $request->validate([
            'patient_id' => 'required|integer|exists:patients,id',
            'session_id' => 'nullable|integer|exists:aesthetic_sessions,id',
            'patient_package_id' => 'nullable|integer|exists:patient_packages,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.treatment_id' => 'nullable|integer|exists:aesthetic_treatments,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        $this->validatePatientTenant($validated['patient_id']);

        DB::transaction(function () use ($aestheticInvoice, $request, $validated) {
            $aestheticInvoice->update([
                'patient_id' => $validated['patient_id'],
                'session_id' => $validated['session_id'] ?? null,
                'patient_package_id' => $validated['patient_package_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'tax_rate' => $validated['tax_rate'] ?? 0,
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Track existing item IDs
            $existingIds = [];
            foreach ($validated['items'] as $item) {
                $itemTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0);
                if (!empty($item['id'])) {
                    $invoiceItem = AestheticInvoiceItem::find($item['id']);
                    if ($invoiceItem && $invoiceItem->invoice_id === $aestheticInvoice->id) {
                        $invoiceItem->update([
                            'treatment_id' => $item['treatment_id'] ?? null,
                            'description' => $item['description'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'discount' => $item['discount'] ?? 0,
                            'total_price' => max(0, $itemTotal),
                        ]);
                        $existingIds[] = $invoiceItem->id;
                    }
                } else {
                    $newItem = $aestheticInvoice->items()->create([
                        'treatment_id' => $item['treatment_id'] ?? null,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'total_price' => max(0, $itemTotal),
                    ]);
                    $existingIds[] = $newItem->id;
                }
            }

            // Remove deleted items
            $aestheticInvoice->items()->whereNotIn('id', $existingIds)->delete();

            $aestheticInvoice->recalculateSubtotal();
        });

        return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
            ->with('success', __('Invoice :number updated successfully.', ['number' => $aestheticInvoice->invoice_number]));
    }

    /**
     * Remove the specified invoice (soft delete).
     */
    public function destroy(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        $aestheticInvoice->delete();

        return redirect()->route('aesthetic.invoices.index')
            ->with('success', __('Invoice :number deleted successfully.', ['number' => $aestheticInvoice->invoice_number]));
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Request $request, AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $aestheticInvoice->balance,
            'payment_method' => 'required|string|in:cash,card,bank_transfer,other',
            'payment_date' => 'nullable|date',
        ]);

        $aestheticInvoice->recordPayment(
            (float) $validated['amount'],
            $validated['payment_method']
        );

        return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
            ->with('success', __('Payment of :amount recorded successfully. Remaining balance: :balance', [
                'amount' => number_format($validated['amount'], 2),
                'balance' => number_format($aestheticInvoice->balance, 2),
            ]));
    }

    /**
     * Send invoice (mark as sent).
     */
    public function send(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        if ($aestheticInvoice->status === 'draft') {
            $aestheticInvoice->update(['status' => 'sent']);
        }

        return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
            ->with('success', __('Invoice :number marked as sent.', ['number' => $aestheticInvoice->invoice_number]));
    }

    /**
     * Cancel the invoice.
     */
    public function cancel(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        if (in_array($aestheticInvoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('aesthetic.invoices.show', $aestheticInvoice)
                ->with('error', __('Paid invoices cannot be cancelled.'));
        }

        $aestheticInvoice->update(['status' => 'cancelled']);

        return redirect()->route('aesthetic.invoices.index')
            ->with('success', __('Invoice :number cancelled.', ['number' => $aestheticInvoice->invoice_number]));
    }

    /**
     * Print receipt view.
     */
    public function receipt(AestheticInvoice $aestheticInvoice)
    {
        $this->authorizeTenant($aestheticInvoice);

        $aestheticInvoice->load(['patient', 'items.treatment', 'creator']);

        return view('aesthetic.invoices.receipt', compact('aestheticInvoice'));
    }

    // ─── Helpers ────────────────────────────────────────────────

    private function resolveCurrency(): string
    {
        $clinicId = Auth::user()?->clinic_id;

        if (!$clinicId) {
            return 'USD';
        }

        $code = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'currency')
            ->value('value');
        return is_string($code) && $code !== '' ? strtoupper($code) : 'USD';
    }

    private function ensureClinicTenantId(): ?string
    {
        $user = Auth::user();
        $clinic = $user?->clinic;

        if (!$user || !$user->clinic_id || !$clinic) {
            return null;
        }

        return $clinic->ensureTenantId();
    }

    private function authorizeTenant(AestheticInvoice $invoice): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $invoice->tenant_id !== $userTenantId) {
            abort(403, __('Unauthorized access to this invoice.'));
        }
    }

    private function getTenantPatients()
    {
        $clinicId = Auth::user()->clinic_id;
        return Patient::when($clinicId, fn($q) => $q->where('clinic_id', $clinicId))
            ->orderBy('first_name')
            ->get();
    }

    private function getTenantTreatments()
    {
        $tenantId = Auth::user()->clinic?->tenant_id;
        return AestheticTreatment::byTenant($tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function getTenantSessions()
    {
        $clinicId = Auth::user()->clinic_id;
        return AestheticSession::with(['patientPackage.patient', 'patientPackage.package'])
            ->whereHas('patientPackage.patient', fn($q) => $q->when($clinicId, fn($sq) => $sq->where('clinic_id', $clinicId)))
            ->where('status', 'completed')
            ->latest('session_date')
            ->get();
    }

    private function validatePatientTenant(int $patientId): void
    {
        $clinicId = Auth::user()->clinic_id;
        $patient = Patient::findOrFail($patientId);
        if ($clinicId && $patient->clinic_id !== $clinicId) {
            abort(403, __('The selected patient is not available for your clinic.'));
        }
    }

    private function validateSessionTenant(int $sessionId): void
    {
        $clinicId = Auth::user()->clinic_id;
        $exists = AestheticSession::where('id', $sessionId)
            ->whereHas('patientPackage.patient', fn($q) => $q->when($clinicId, fn($sq) => $sq->where('clinic_id', $clinicId)))
            ->exists();
        if (!$exists) {
            abort(403, __('The selected session is not available for your clinic.'));
        }
    }
}
