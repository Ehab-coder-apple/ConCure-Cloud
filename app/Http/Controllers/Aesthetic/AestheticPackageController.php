<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticPackage;
use App\Models\AestheticTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AestheticPackageController extends Controller
{
    /**
     * Display a listing of aesthetic packages.
     */
    public function index(Request $request)
    {
        $query = AestheticPackage::with(['treatment', 'treatments']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhereHas('treatments', function ($tq) use ($request) {
                      $tq->where('name', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->filled('treatment_id')) {
            $query->whereHas('treatments', function ($q) use ($request) {
                $q->where('aesthetic_treatments.id', $request->treatment_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        $packages = $query->latest()->paginate(15);
        $treatments = $this->getTenantTreatments();

        $stats = [
            'total' => AestheticPackage::count(),
            'active' => AestheticPackage::active()->count(),
            'expired' => AestheticPackage::expired()->count(),
        ];

        return view('aesthetic.packages.index', compact('packages', 'treatments', 'stats'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $treatments = $this->getTenantTreatments();
        $clinicCurrency = $this->resolveCurrency();
        return view('aesthetic.packages.create', compact('treatments', 'clinicCurrency'));
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'treatment_ids' => 'required|array|min:1',
            'treatment_ids.*' => 'integer|exists:aesthetic_treatments,id',
            'total_sessions' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        foreach ($validated['treatment_ids'] as $treatmentId) {
            $this->validateTreatmentTenant($treatmentId);
        }

        $validated['tenant_id'] = Auth::user()->clinic?->tenant_id;
        $validated['treatment_id'] = $validated['treatment_ids'][0] ?? null;

        $package = AestheticPackage::create($validated);
        $package->treatments()->sync($validated['treatment_ids']);

        return redirect()->route('aesthetic.packages.index')
            ->with('success', __('Aesthetic package created successfully.'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(AestheticPackage $aestheticPackage)
    {
        $this->authorizeTenant($aestheticPackage);

        $treatments = $this->getTenantTreatments();
        $selectedTreatmentIds = $aestheticPackage->treatments->pluck('id')->toArray();
        $clinicCurrency = $this->resolveCurrency();

        return view('aesthetic.packages.edit', compact('aestheticPackage', 'treatments', 'selectedTreatmentIds', 'clinicCurrency'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, AestheticPackage $aestheticPackage)
    {
        $this->authorizeTenant($aestheticPackage);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'treatment_ids' => 'required|array|min:1',
            'treatment_ids.*' => 'integer|exists:aesthetic_treatments,id',
            'total_sessions' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        foreach ($validated['treatment_ids'] as $treatmentId) {
            $this->validateTreatmentTenant($treatmentId);
        }

        $validated['treatment_id'] = $validated['treatment_ids'][0] ?? null;

        $aestheticPackage->update($validated);
        $aestheticPackage->treatments()->sync($validated['treatment_ids']);

        return redirect()->route('aesthetic.packages.index')
            ->with('success', __('Aesthetic package updated successfully.'));
    }

    /**
     * Remove the specified package (soft delete).
     */
    public function destroy(AestheticPackage $aestheticPackage)
    {
        $this->authorizeTenant($aestheticPackage);

        $aestheticPackage->delete();

        return redirect()->route('aesthetic.packages.index')
            ->with('success', __('Aesthetic package deleted successfully.'));
    }

    /**
     * Resolve the clinic's configured currency code.
     */
    private function resolveCurrency(): string
    {
        $code = DB::table('settings')
            ->where('clinic_id', Auth::user()->clinic_id)
            ->where('key', 'currency')
            ->value('value');
        return is_string($code) && $code !== '' ? strtoupper($code) : 'USD';
    }

    /**
     * Ensure the package belongs to the current tenant.
     */
    private function authorizeTenant(AestheticPackage $package): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $package->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to access this package.'));
        }
    }

    /**
     * Get treatments available for the current tenant.
     */
    private function getTenantTreatments()
    {
        $tenantId = Auth::user()->clinic?->tenant_id;

        return AestheticTreatment::byTenant($tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
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
