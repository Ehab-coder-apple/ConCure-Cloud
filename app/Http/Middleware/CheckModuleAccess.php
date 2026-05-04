<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    /**
     * Route-prefix → module key mapping.
     */
    protected array $routeModuleMap = [
        'patients'              => 'patients',
        'image-bank'            => 'image_bank',
        'simple-prescriptions'  => 'prescriptions',
        'recommendations/lab'   => 'lab',
        'recommendations/radiology' => 'radiology',
        'dental'                => 'dental',
        'ent'                   => 'ent',
        'aesthetic'             => 'aesthetic',
        'nutrition'             => 'nutrition',
        'foods'                 => 'food_database',
        'food-groups'           => 'food_database',
        'forms'                 => 'forms',
        'appointments'          => 'appointments',
        'medicines'             => 'medicines',
        'finance'               => 'finance',
        'assistant'             => 'ai_assistant',
        'messages'              => 'messages',
        'pediatric'             => 'pediatric',
        'vaccination'           => 'vaccination',
        'whatsapp'              => 'whatsapp',
    ];

    /**
     * Handle an incoming request.
     *
     * If a specific module is passed as parameter, check that module.
     * Otherwise, auto-detect from the request path.
     */
    public function handle(Request $request, Closure $next, ?string $module = null): Response
    {
        $user = $request->user();

        // Not logged in – let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Resolve module from parameter or from URL path
        $moduleKey = $module ?? $this->resolveModuleFromPath($request->path());

        // If we can't determine the module, allow (don't block unknown routes)
        if (!$moduleKey) {
            return $next($request);
        }

        if (!$user->canAccessModule($moduleKey)) {
            abort(403, 'This module is not enabled for your clinic.');
        }

        return $next($request);
    }

    /**
     * Try to match the request path to a module key.
     */
    protected function resolveModuleFromPath(string $path): ?string
    {
        foreach ($this->routeModuleMap as $prefix => $module) {
            if (str_starts_with($path, $prefix)) {
                return $module;
            }
        }

        return null;
    }
}

