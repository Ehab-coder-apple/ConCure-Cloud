<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\SubscriptionPlan;

class PlanController extends Controller
{
    public function index()
    {
        // Guard against missing table on servers that haven't run new migrations yet
        if (!Schema::hasTable('subscription_plans')) {
            $plans = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 20, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]);
            return view('master.plans.index', [
                'plans' => $plans,
                'missingTable' => true,
            ]);
        }

        $plans = SubscriptionPlan::orderBy('monthly_price')->paginate(20);
        return view('master.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('master.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'max_users' => 'nullable|integer|min:1|max:100000',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // If features comes as comma-separated string, normalize
        if (is_string($request->features)) {
            $data['features'] = array_values(array_filter(array_map('trim', explode(',', $request->features))));
        }

        SubscriptionPlan::create($data);
        return redirect()->route('master.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('master.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'max_users' => 'nullable|integer|min:1|max:100000',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);
        if (is_string($request->features)) {
            $data['features'] = array_values(array_filter(array_map('trim', explode(',', $request->features))));
        }
        $plan->update($data);
        return redirect()->route('master.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        // Prevent deleting a plan with clinics assigned
        if ($plan->clinics()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete a plan that has clinics assigned.']);
        }
        $plan->delete();
        return redirect()->route('master.plans.index')->with('success', 'Plan deleted.');
    }
}

