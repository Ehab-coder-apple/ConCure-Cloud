<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AestheticInventoryController extends Controller
{
    /**
     * Display a listing of the inventory.
     */
    public function index(Request $request)
    {
        $query = AestheticInventory::query();

        if ($request->filled('search')) {
            $query->where('product_name', 'like', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', 0);
            }
        }

        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expired') {
                $query->expired();
            } elseif ($request->expiry_status === 'near') {
                $query->nearExpiry();
            }
        }

        $items = $query->latest()->paginate(15);

        $stats = [
            'total' => AestheticInventory::count(),
            'low_stock' => AestheticInventory::lowStock()->count(),
            'out_of_stock' => AestheticInventory::where('quantity', 0)->count(),
            'expired' => AestheticInventory::expired()->count(),
        ];

        $existingTypes = $this->getExistingTypes();

        return view('aesthetic.inventory.index', compact('items', 'stats', 'existingTypes'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create()
    {
        $existingTypes = $this->getExistingTypes();
        return view('aesthetic.inventory.create', compact('existingTypes'));
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date|after_or_equal:today',
        ]);

        $validated['tenant_id'] = Auth::user()->clinic?->tenant_id;

        AestheticInventory::create($validated);

        return redirect()->route('aesthetic.inventory.index')
            ->with('success', __('Inventory item created successfully.'));
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(AestheticInventory $aestheticInventory)
    {
        $this->authorizeTenant($aestheticInventory);

        $existingTypes = $this->getExistingTypes();
        return view('aesthetic.inventory.edit', compact('aestheticInventory', 'existingTypes'));
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, AestheticInventory $aestheticInventory)
    {
        $this->authorizeTenant($aestheticInventory);

        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);

        $aestheticInventory->update($validated);

        return redirect()->route('aesthetic.inventory.index')
            ->with('success', __('Inventory item updated successfully.'));
    }

    /**
     * Remove the specified item (soft delete).
     */
    public function destroy(AestheticInventory $aestheticInventory)
    {
        $this->authorizeTenant($aestheticInventory);

        $aestheticInventory->delete();

        return redirect()->route('aesthetic.inventory.index')
            ->with('success', __('Inventory item deleted successfully.'));
    }

    /**
     * Quick adjust stock quantity.
     */
    public function adjustStock(Request $request, AestheticInventory $aestheticInventory)
    {
        $this->authorizeTenant($aestheticInventory);

        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $newQuantity = max(0, $aestheticInventory->quantity + $validated['adjustment']);
        $aestheticInventory->update(['quantity' => $newQuantity]);

        return redirect()->route('aesthetic.inventory.index')
            ->with('success', __('Stock adjusted. New quantity: :quantity', ['quantity' => $newQuantity]));
    }

    /**
     * Ensure the item belongs to the current tenant.
     */
    private function authorizeTenant(AestheticInventory $item): void
    {
        $user = Auth::user();
        $userTenantId = $user->clinic?->tenant_id;

        if ($userTenantId && $item->tenant_id !== $userTenantId) {
            abort(403, __('You are not authorized to access this inventory item.'));
        }
    }

    /**
     * Get existing types from tenant inventory merged with built-ins.
     */
    private function getExistingTypes(): array
    {
        $builtIn = AestheticInventory::TYPES;

        $custom = AestheticInventory::select('type')
            ->distinct()
            ->pluck('type')
            ->reject(fn ($t) => array_key_exists($t, $builtIn))
            ->mapWithKeys(fn ($t) => [$t => ucwords(str_replace(['_', '-'], ' ', $t))])
            ->all();

        return array_merge($builtIn, $custom);
    }
}
