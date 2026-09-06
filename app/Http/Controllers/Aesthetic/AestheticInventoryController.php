<?php

namespace App\Http\Controllers\Aesthetic;

use App\Http\Controllers\Controller;
use App\Models\AestheticInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'purchased_quantity' => 'required|integer|min:0',
            'bonus_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $validated['quantity'] = $validated['purchased_quantity'] + $validated['bonus_quantity'];
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
            'purchased_quantity' => 'required|integer|min:0',
            'bonus_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $validated['quantity'] = $validated['purchased_quantity'] + $validated['bonus_quantity'];

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
     * Sold vs. remaining inventory report for a given period.
     */
    public function report(Request $request)
    {
        $period = $request->get('period', 'month');

        if ($period === 'week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($period === 'custom') {
            $startDate = $request->filled('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : now()->startOfMonth();
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : now()->endOfMonth();
        } else {
            $period = 'month';
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        $items = AestheticInventory::with(['sessionUsages' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('session', function ($sessionQuery) use ($startDate, $endDate) {
                $sessionQuery->whereBetween('session_date', [$startDate->toDateString(), $endDate->toDateString()]);
            });
        }])->orderBy('product_name')->get();

        $rows = $items->map(function ($item) {
            $soldQuantity = (int) $item->sessionUsages->sum('quantity_used');
            $totalSoldValue = (float) $item->sessionUsages->sum(function ($usage) {
                return $usage->quantity_used * (float) $usage->unit_price;
            });
            $remainingQuantity = (int) $item->quantity;
            $currentStockValue = $remainingQuantity * (float) $item->purchase_price;

            return (object) [
                'product' => $item,
                'sold_quantity' => $soldQuantity,
                'total_sold_value' => $totalSoldValue,
                'remaining_quantity' => $remainingQuantity,
                'current_stock_value' => $currentStockValue,
            ];
        });

        $totals = [
            'sold_quantity' => $rows->sum('sold_quantity'),
            'total_sold_value' => $rows->sum('total_sold_value'),
            'remaining_quantity' => $rows->sum('remaining_quantity'),
            'current_stock_value' => $rows->sum('current_stock_value'),
        ];

        $currency = $this->getCurrency();

        return view('aesthetic.inventory.report', compact('rows', 'totals', 'period', 'startDate', 'endDate', 'currency'));
    }

    /**
     * Resolve the clinic's configured currency code.
     */
    private function getCurrency(): string
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

    /**
     * Quick adjust stock quantity.
     */
    public function adjustStock(Request $request, AestheticInventory $aestheticInventory)
    {
        $this->authorizeTenant($aestheticInventory);

        $validated = $request->validate([
            'adjustment' => 'required|integer',
            'stock_type' => 'required|in:purchased,bonus',
            'reason' => 'nullable|string|max:255',
        ]);

        $column = $validated['stock_type'] === 'bonus' ? 'bonus_quantity' : 'purchased_quantity';
        $newColumnQuantity = max(0, $aestheticInventory->{$column} + $validated['adjustment']);

        // Clamp the adjustment so the total quantity never goes negative.
        $appliedAdjustment = $newColumnQuantity - $aestheticInventory->{$column};
        $newTotalQuantity = $aestheticInventory->quantity + $appliedAdjustment;

        $aestheticInventory->update([
            $column => $newColumnQuantity,
            'quantity' => $newTotalQuantity,
        ]);

        return redirect()->route('aesthetic.inventory.index')
            ->with('success', __('Stock adjusted. New quantity: :quantity', ['quantity' => $newTotalQuantity]));
    }

    /**
     * Ensure the item belongs to the current tenant.
     */
    private function authorizeTenant(AestheticInventory $item): void
    {
        $user = Auth::user();

        // Superadmins bypass tenant check
        if ($user->isSuperAdmin()) {
            return;
        }

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
