<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\User;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request)
    {
        $query = Clinic::with(['users']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $clinics = $query->latest()->paginate(15);

        // Calculate subscription stats
        $stats = [
            'total_subscriptions' => Clinic::count(),
            'active_subscriptions' => Clinic::where('is_active', true)->count(),
            'inactive_subscriptions' => Clinic::where('is_active', false)->count(),
            'total_revenue' => 0, // Placeholder for future billing integration
        ];

        return view('master.subscriptions.index', compact('clinics', 'stats'));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create()
    {
        return view('master.subscriptions.create');
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        // This would handle subscription plan creation
        // For now, we'll redirect back with a message
        return redirect()->route('master.subscriptions.index')
            ->with('info', 'Subscription management is coming soon!');
    }

    /**
     * Display the specified subscription.
     */
    public function show(Clinic $subscription)
    {
        $clinic = $subscription;
        $clinic->load(['users', 'patients', 'prescriptions', 'appointments']);
        
        $stats = [
            'total_users' => $clinic->users()->count(),
            'active_users' => $clinic->users()->where('is_active', true)->count(),
            'total_patients' => $clinic->patients()->count(),
            'total_prescriptions' => $clinic->prescriptions()->count(),
            'total_appointments' => $clinic->appointments()->count(),
            'monthly_patients' => $clinic->patients()->whereMonth('created_at', now()->month)->count(),
        ];

        // Subscription details (placeholder)
        $subscriptionDetails = [
            'plan' => 'Basic Plan',
            'price' => '$29/month',
            'features' => [
                'Up to ' . $clinic->max_users . ' users',
                'Unlimited patients',
                'Prescription management',
                'Appointment scheduling',
                'Basic reporting',
                'Email support'
            ],
            'billing_cycle' => 'Monthly',
            'next_billing' => now()->addMonth(),
            'status' => $clinic->is_active ? 'Active' : 'Inactive'
        ];

        return view('master.subscriptions.show', compact('clinic', 'stats', 'subscriptionDetails'));
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(Clinic $subscription)
    {
        $clinic = $subscription;
        return view('master.subscriptions.edit', compact('clinic'));
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, Clinic $subscription)
    {
        $clinic = $subscription;
        
        $request->validate([
            'max_users' => 'required|integer|min:1|max:1000',
        ]);

        $clinic->update([
            'max_users' => $request->max_users,
        ]);

        return redirect()->route('master.subscriptions.show', $clinic)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(Clinic $subscription)
    {
        $clinic = $subscription;
        
        // Check if clinic has any data
        $hasData = $clinic->patients()->exists() || 
                   $clinic->prescriptions()->exists() || 
                   $clinic->appointments()->exists();

        if ($hasData) {
            return back()->withErrors(['error' => 'Cannot delete subscription with existing data. Deactivate instead.']);
        }

        // Delete all users first
        $clinic->users()->delete();
        
        // Delete the clinic/subscription
        $clinic->delete();

        return redirect()->route('master.subscriptions.index')
            ->with('success', 'Subscription cancelled and deleted successfully.');
    }

    /**
     * Get subscription statistics for charts.
     */
    public function getSubscriptionStats()
    {
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'new_subscriptions' => Clinic::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'active_subscriptions' => Clinic::where('is_active', true)
                    ->whereYear('created_at', '<=', $date->year)
                    ->whereMonth('created_at', '<=', $date->month)
                    ->count(),
            ];
        }

        return response()->json($monthlyStats);
    }

    /**
     * Get revenue statistics (placeholder).
     */
    public function getRevenueStats()
    {
        // Placeholder for future billing integration
        $revenueStats = [
            'monthly_revenue' => 0,
            'annual_revenue' => 0,
            'average_revenue_per_user' => 0,
            'churn_rate' => 0,
        ];

        return response()->json($revenueStats);
    }
}
