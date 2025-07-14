<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\MasterAuthController;
use Symfony\Component\HttpFoundation\Response;

class MasterAuth
{
    /**
     * Handle an incoming request for master authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!MasterAuthController::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->route('master.login')
                ->with('error', __('Please login to access the Master Dashboard.'));
        }

        // Verify the user still exists and has proper permissions
        $user = MasterAuthController::user();
        
        if (!$user) {
            // Clear invalid session
            session()->forget([
                'master_user_id',
                'master_user_name',
                'master_user_email',
                'master_login_time',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expired'], 401);
            }

            return redirect()->route('master.login')
                ->with('error', __('Your session has expired. Please login again.'));
        }

        // Add master user to request attributes for easy access
        $request->attributes->set('master_user', $user);

        return $next($request);
    }
}
