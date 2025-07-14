<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinic;

class SubscriptionController extends Controller
{
    /**
     * Show trial expired page.
     */
    public function expired()
    {
        $user = auth()->user();
        $clinic = $user->clinic;

        if (!$clinic || !$clinic->is_trial || !$clinic->isTrialExpired()) {
            return redirect()->route('dashboard');
        }

        return view('subscription.expired', compact('clinic'));
    }

    /**
     * Show subscription plans.
     */
    public function plans()
    {
        $user = auth()->user();
        $clinic = $user->clinic;

        $plans = [
            [
                'name' => 'Basic Plan',
                'price' => '$29',
                'period' => 'month',
                'max_users' => 5,
                'features' => [
                    'Up to 5 users',
                    'Patient management',
                    'Prescription system',
                    'Basic reporting',
                    'Email support'
                ]
            ],
            [
                'name' => 'Professional Plan',
                'price' => '$59',
                'period' => 'month',
                'popular' => true,
                'max_users' => 15,
                'features' => [
                    'Up to 15 users',
                    'Advanced patient management',
                    'Prescription & nutrition planning',
                    'Lab request management',
                    'Advanced reporting',
                    'Priority support',
                    'Multi-language support'
                ]
            ],
            [
                'name' => 'Enterprise Plan',
                'price' => '$99',
                'period' => 'month',
                'max_users' => 30,
                'features' => [
                    'Up to 30 users',
                    'All features included',
                    'Custom integrations',
                    'Advanced analytics',
                    'Dedicated support',
                    'Custom training'
                ]
            ]
        ];

        return view('subscription.plans', compact('clinic', 'plans'));
    }

    /**
     * Show upgrade form.
     */
    public function upgrade(Request $request)
    {
        $user = auth()->user();
        $clinic = $user->clinic;
        $plan = $request->get('plan', 'professional');

        return view('subscription.upgrade', compact('clinic', 'plan'));
    }

    /**
     * Process subscription upgrade.
     */
    public function processUpgrade(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:basic,professional,enterprise',
            'payment_method' => 'required|in:credit_card,bank_transfer',
        ]);

        $user = auth()->user();
        $clinic = $user->clinic;

        // Set user limits based on plan
        $userLimits = [
            'basic' => 5,
            'professional' => 15,
            'enterprise' => 30,
        ];

        $selectedPlan = $request->input('plan');
        $maxUsers = $userLimits[$selectedPlan];

        // In a real application, you would process payment here
        // For now, we'll simulate a successful upgrade

        $months = 12; // Default to annual subscription
        $clinic->convertTrialToSubscription($months, $selectedPlan, $maxUsers);

        return redirect()->route('dashboard')
                       ->with('success', "Congratulations! Your {$selectedPlan} subscription has been activated. You can now have up to {$maxUsers} users!");
    }

    /**
     * Show trial status.
     */
    public function status()
    {
        $user = auth()->user();
        $clinic = $user->clinic;

        return view('subscription.status', compact('clinic'));
    }
}
