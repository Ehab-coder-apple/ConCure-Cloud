<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticAftercareTemplate;
use App\Models\AestheticTreatment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AestheticAftercareTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = AestheticAftercareTemplate::query();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        $templates = $query->latest()->paginate(15);

        $stats = [
            'total' => AestheticAftercareTemplate::count(),
            'active' => AestheticAftercareTemplate::where('is_active', true)->count(),
            'inactive' => AestheticAftercareTemplate::where('is_active', false)->count(),
        ];

        $categories = $this->getExistingCategories();

        return view('aesthetic.aftercare-templates.index', compact('templates', 'stats', 'categories'));
    }

    public function create()
    {
        $this->ensureTenantExists();

        $categories = $this->getExistingCategories();

        return view('aesthetic.aftercare-templates.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->ensureTenantExists();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'instructions' => 'required|string|max:20000',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = Auth::id();

        AestheticAftercareTemplate::create($validated);

        return redirect()->route('aesthetic.aftercare-templates.index')
            ->with('success', __('Aftercare template created successfully.'));
    }

    public function edit(AestheticAftercareTemplate $aestheticAftercareTemplate)
    {
        $this->authorizeTenant($aestheticAftercareTemplate);

        $categories = $this->getExistingCategories();

        return view('aesthetic.aftercare-templates.edit', [
            'aestheticAftercareTemplate' => $aestheticAftercareTemplate,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, AestheticAftercareTemplate $aestheticAftercareTemplate)
    {
        $this->authorizeTenant($aestheticAftercareTemplate);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'instructions' => 'required|string|max:20000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $aestheticAftercareTemplate->update($validated);

        return redirect()->route('aesthetic.aftercare-templates.index')
            ->with('success', __('Aftercare template updated successfully.'));
    }

    public function destroy(AestheticAftercareTemplate $aestheticAftercareTemplate)
    {
        $this->authorizeTenant($aestheticAftercareTemplate);
        $aestheticAftercareTemplate->delete();

        return redirect()->route('aesthetic.aftercare-templates.index')
            ->with('success', __('Aftercare template deleted successfully.'));
    }

    private function ensureTenantExists(): ?string
    {
        $user = Auth::user();
        $clinic = $user?->clinic;

        if (!$user || !$user->clinic_id || !$clinic) {
            abort(403, __('Unable to resolve clinic tenant.'));
        }

        return $clinic->ensureTenantId();
    }

    private function authorizeTenant(AestheticAftercareTemplate $template): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $tenantId = $user->clinic?->tenant_id;
        if ($tenantId && $template->tenant_id !== $tenantId) {
            abort(403, __('You are not authorized to access this aftercare template.'));
        }
    }

    private function getExistingCategories(): array
    {
        $builtIn = AestheticTreatment::CATEGORIES;

        $custom = AestheticAftercareTemplate::query()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->reject(fn ($category) => array_key_exists($category, $builtIn))
            ->mapWithKeys(fn ($category) => [$category => ucwords(str_replace(['_', '-'], ' ', $category))])
            ->all();

        return array_merge($builtIn, $custom);
    }
}