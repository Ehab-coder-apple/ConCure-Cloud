<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckExportPermission
{
    /**
     * Handle an incoming request.
     *
     * Enforces two data extraction rules:
     * 1. Demo clinics cannot export any data unless can_export = true (set by master admin).
     * 2. System-uploaded data can only be exported by master admin (super_admin or master_admin).
     *
     * Master-level users (super_admin, master_admin) always bypass these restrictions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not logged in – let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Master-level users always have export access
        if ($user->isSuperAdmin() || $user->isMasterAdmin()) {
            return $next($request);
        }

        // Rule 2: Demo clinics cannot export without explicit permission
        $clinic = $user->clinic;
        if ($clinic && $clinic->is_demo && !$clinic->canExportData()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Demo clinics are not permitted to export data. Please contact the master admin for permission.'),
                ], 403);
            }

            return back()->with('error', __('Demo clinics are not permitted to export data. Please contact the master admin for permission.'));
        }

        return $next($request);
    }
}

