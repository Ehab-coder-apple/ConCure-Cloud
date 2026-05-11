<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureContractIsAccepted
{
    /**
     * Handle an incoming request.
     *
     * Block clinic access if there's a pending contract that needs to be accepted.
     * Only applies to non-super admin users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip for guests, super admins, or if accessing contract-related routes
        if (!$user ||
            $user->isSuperAdmin() ||
            $request->routeIs('contract.*') ||
            $request->routeIs('logout') ||
            $request->routeIs('master.*')) {
            return $next($request);
        }

        // Check if user's clinic has a pending contract
        $clinic = $user->clinic;

        if ($clinic) {
            $pendingContract = $clinic->activeContract()
                ->where('status', 'pending')
                ->first();

            if ($pendingContract) {
                // Redirect to contract acceptance page
                return redirect()->route('contract.show');
            }
        }

        return $next($request);
    }
}
