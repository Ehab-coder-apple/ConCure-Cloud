<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AestheticTreatmentController extends Controller
{
    /**
     * Display a listing of aesthetic treatments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Auto-clone built-in treatments for this clinic on first visit
        $tenantId = $user->clinic?->tenant_id;

        if ($tenantId) {
            try {
                $treatmentCount = AestheticTreatment::where('tenant_id', $tenantId)->count();
                if ($treatmentCount === 0) {
                    $cloned = AestheticTreatment::cloneBuiltInForTenant($tenantId);
                    if ($cloned > 0) {
                        session()->flash('success', __('Welcome! :count default treatments have been added to your clinic.', ['count' => $cloned]));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to clone built-in treatments for tenant: ' . $tenantId, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $query = AestheticTreatment::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $treatments = $query->latest()->paginate(15);

        $stats = [
            'total' => AestheticTreatment::count(),
            'active' => AestheticTreatment::where('is_active', true)->count(),
            'inactive' => AestheticTreatment::where('is_active', false)->count(),
        ];

        $existingCategories = $this->getExistingCategories();

        return view('aesthetic.treatments.index', compact('treatments', 'stats', 'existingCategories'));
    }

    /**
     * Show the form for creating a new treatment.
     */
    public function create()
    {
        $user = Auth::user();

        // Ensure user has a valid clinic and tenant
        if (!$user->clinic_id || !$user->clinic?->tenant_id) {
            return redirect()->route('aesthetic.treatments.index')
                ->withErrors(['error' => __('Unable to create treatment. User clinic or tenant not found. Please contact support.')]);
        }

        $existingCategories = $this->getExistingCategories();
        $clinicCurrency = $this->resolveCurrency();
        return view('aesthetic.treatments.create', compact('existingCategories', 'clinicCurrency'));
    }

    /**
     * Store a newly created treatment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'session_required' => 'boolean',
            'sessions_count' => 'required_if:session_required,1|nullable|integer|min:1',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $validated['session_required'] = $request->boolean('session_required', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        if (!$validated['session_required']) {
            $validated['sessions_count'] = null;
        }

        $user = Auth::user();
        $tenantId = $user->clinic?->tenant_id;

        // Ensure user has a valid tenant
        if (!$tenantId) {
            return back()->withErrors(['error' => __('Unable to create treatment. User clinic or tenant not found.')]);
        }

        $validated['tenant_id'] = $tenantId;

        AestheticTreatment::create($validated);

        return redirect()->route('aesthetic.treatments.index')
            ->with('success', __('Aesthetic treatment created successfully.'));
    }

    /**
     * Show the form for editing the specified treatment.
     */
    public function edit(AestheticTreatment $aestheticTreatment)
    {
        $this->authorizeTenant($aestheticTreatment);

        $existingCategories = $this->getExistingCategories();
        $clinicCurrency = $this->resolveCurrency();
        return view('aesthetic.treatments.edit', compact('aestheticTreatment', 'existingCategories', 'clinicCurrency'));
    }

    /**
     * Update the specified treatment.
     */
    public function update(Request $request, AestheticTreatment $aestheticTreatment)
    {
        $this->authorizeTenant($aestheticTreatment);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'session_required' => 'boolean',
            'sessions_count' => 'required_if:session_required,1|nullable|integer|min:1',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
        ]);

        $validated['session_required'] = $request->boolean('session_required', false);
        $validated['is_active'] = $request->boolean('is_active', true);

        if (!$validated['session_required']) {
            $validated['sessions_count'] = null;
        }

        $aestheticTreatment->update($validated);

        return redirect()->route('aesthetic.treatments.index')
            ->with('success', __('Aesthetic treatment updated successfully.'));
    }

    /**
     * Remove the specified treatment (soft delete).
     */
    public function destroy(AestheticTreatment $aestheticTreatment)
    {
        $this->authorizeTenant($aestheticTreatment);

        $aestheticTreatment->delete();

        return redirect()->route('aesthetic.treatments.index')
            ->with('success', __('Aesthetic treatment deleted successfully.'));
    }

    /**
     * Resolve the clinic's configured currency code.
     */
    private function resolveCurrency(): string
    {
        $user = Auth::user();

        // If user has no clinic_id, return default
        if (!$user || !$user->clinic_id) {
            return 'USD';
        }

        $code = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'currency')
            ->value('value');
        return is_string($code) && $code !== '' ? strtoupper($code) : 'USD';
    }

    /**
     * Ensure the treatment belongs to the current tenant.
     */
    private function authorizeTenant(AestheticTreatment $treatment): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $treatment->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to access this treatment.'));
        }
    }

    /**
     * Get existing categories from tenant treatments merged with built-ins.
     */
    private function getExistingCategories(): array
    {
        $builtIn = AestheticTreatment::CATEGORIES;

        $custom = AestheticTreatment::select('category')
            ->distinct()
            ->pluck('category')
            ->reject(fn ($cat) => array_key_exists($cat, $builtIn))
            ->mapWithKeys(fn ($cat) => [$cat => ucwords(str_replace(['_', '-'], ' ', $cat))])
            ->all();

        return array_merge($builtIn, $custom);
    }
}
