<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (auth()->check()) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Log the request for audit purposes.
     */
    private function logRequest(Request $request, Response $response): void
    {
        $user = auth()->user();
        
        // Skip logging for certain routes to avoid noise
        $skipRoutes = [
            'api/user',
            'dashboard',
            'settings',
        ];

        $path = $request->path();
        foreach ($skipRoutes as $skipRoute) {
            if (str_contains($path, $skipRoute) && $request->isMethod('GET')) {
                return;
            }
        }

        // Only log significant actions
        $significantMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (!in_array($request->method(), $significantMethods)) {
            return;
        }

        // Determine action description
        $action = $this->determineAction($request);
        
        if ($action) {
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->full_name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => $action,
                'description' => $this->generateDescription($request, $action),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);
        }
    }

    /**
     * Determine the action based on the request.
     */
    private function determineAction(Request $request): ?string
    {
        $method = $request->method();
        $path = $request->path();

        // Map routes to actions
        $actionMap = [
            'POST' => [
                'patients' => 'create_patient',
                'users' => 'create_user',
                'prescriptions' => 'create_prescription',
                'lab-requests' => 'create_lab_request',
                'diet-plans' => 'create_diet_plan',
                'invoices' => 'create_invoice',
                'expenses' => 'create_expense',
                'login' => 'login',
                'logout' => 'logout',
            ],
            'PUT' => [
                'patients' => 'update_patient',
                'users' => 'update_user',
                'prescriptions' => 'update_prescription',
                'invoices' => 'update_invoice',
            ],
            'PATCH' => [
                'patients' => 'update_patient',
                'users' => 'update_user',
            ],
            'DELETE' => [
                'patients' => 'delete_patient',
                'users' => 'delete_user',
                'prescriptions' => 'delete_prescription',
                'invoices' => 'delete_invoice',
            ],
        ];

        if (isset($actionMap[$method])) {
            foreach ($actionMap[$method] as $routePattern => $action) {
                if (str_contains($path, $routePattern)) {
                    return $action;
                }
            }
        }

        return null;
    }

    /**
     * Generate a human-readable description of the action.
     */
    private function generateDescription(Request $request, string $action): string
    {
        $descriptions = [
            'create_patient' => 'Created a new patient',
            'update_patient' => 'Updated patient information',
            'delete_patient' => 'Deleted a patient',
            'create_user' => 'Created a new user',
            'update_user' => 'Updated user information',
            'delete_user' => 'Deleted a user',
            'create_prescription' => 'Created a new prescription',
            'update_prescription' => 'Updated prescription',
            'delete_prescription' => 'Deleted prescription',
            'create_lab_request' => 'Created a new lab request',
            'create_diet_plan' => 'Created a new diet plan',
            'create_invoice' => 'Created a new invoice',
            'update_invoice' => 'Updated invoice',
            'delete_invoice' => 'Deleted invoice',
            'create_expense' => 'Created a new expense',
            'login' => 'User logged in',
            'logout' => 'User logged out',
        ];

        $baseDescription = $descriptions[$action] ?? "Performed action: {$action}";
        
        // Add additional context if available
        if ($request->route() && $request->route()->parameter('id')) {
            $baseDescription .= " (ID: {$request->route()->parameter('id')})";
        }

        return $baseDescription;
    }
}
