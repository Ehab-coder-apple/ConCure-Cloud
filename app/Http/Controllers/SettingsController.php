<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get clinic-specific settings
        $clinicSettings = [];
        if ($user->clinic_id) {
            $settings = DB::table('settings')
                ->where('clinic_id', $user->clinic_id)
                ->pluck('value', 'key')
                ->toArray();

            $clinicSettings = array_merge([
                'default_language' => 'en',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'currency' => 'USD',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'sms_notifications' => false,
                'clinic_logo' => null,
            ], $settings);

            // Add logo URL if logo exists
            if (isset($clinicSettings['clinic_logo']) && $clinicSettings['clinic_logo']) {
                $clinicSettings['clinic_logo_url'] = Storage::url($clinicSettings['clinic_logo']);
            }
        }

        return view('settings.index', compact('clinicSettings'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Only allow clinic admins to update settings
        if (!$user->clinic_id || !in_array($user->role, ['admin', 'doctor'])) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to update settings.')
            ], 403);
        }

        $allowedSettings = [
            'default_language',
            'timezone',
            'date_format',
            'time_format',
            'currency',
            'notifications_enabled',
            'email_notifications',
            'sms_notifications',
            'clinic_logo'
        ];

        $validatedData = $request->validate([
            'default_language' => 'nullable|in:en,ar,ku',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
            'notifications_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'clinic_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Prevent any attempt to modify application name or other restricted settings
        if ($request->has('app_name') || $request->has('application_name') || $request->has('platform_name')) {
            return response()->json([
                'success' => false,
                'message' => __('Application name cannot be modified by clinic users.')
            ], 403);
        }

        // Handle logo upload
        if ($request->hasFile('clinic_logo')) {
            $logoFile = $request->file('clinic_logo');

            // Delete old logo if exists
            $oldLogo = DB::table('settings')
                ->where('clinic_id', $user->clinic_id)
                ->where('key', 'clinic_logo')
                ->value('value');

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $logoPath = $logoFile->store('clinic-logos', 'public');
            $validatedData['clinic_logo'] = $logoPath;
        }

        // Update only allowed clinic settings
        foreach ($validatedData as $key => $value) {
            if (in_array($key, $allowedSettings)) {
                DB::table('settings')->updateOrInsert(
                    [
                        'clinic_id' => $user->clinic_id,
                        'key' => $key
                    ],
                    [
                        'value' => $value,
                        'type' => is_bool($value) ? 'boolean' : 'string',
                        'updated_at' => now()
                    ]
                );
            }
        }

        // Log the settings update
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'user_role' => $user->role,
            'clinic_id' => $user->clinic_id,
            'action' => 'settings_updated',
            'model_type' => 'Settings',
            'model_id' => null,
            'description' => 'Updated clinic settings',
            'new_values' => json_encode($validatedData),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Settings updated successfully.')
        ]);
    }

    public function deleteLogo(Request $request)
    {
        $user = Auth::user();

        // Only allow clinic admins to delete logo
        if (!$user->clinic_id || !in_array($user->role, ['admin', 'doctor'])) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to delete logo.')
            ], 403);
        }

        // Get current logo path
        $logoPath = DB::table('settings')
            ->where('clinic_id', $user->clinic_id)
            ->where('key', 'clinic_logo')
            ->value('value');

        if ($logoPath) {
            // Delete file from storage
            if (Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            // Remove from database
            DB::table('settings')
                ->where('clinic_id', $user->clinic_id)
                ->where('key', 'clinic_logo')
                ->delete();

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'logo_deleted',
                'model_type' => 'Settings',
                'model_id' => null,
                'description' => 'Deleted clinic logo',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('Logo deleted successfully.')
        ]);
    }

    public function auditLogs(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'admin') {
            abort(403, 'Only administrators can view audit logs.');
        }

        $query = DB::table('audit_logs')
            ->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_role')) {
            $query->where('user_role', $request->user_role);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        $auditLogs = $query->orderBy('performed_at', 'desc')
            ->paginate(50)
            ->appends($request->query());

        return view('settings.audit-logs', compact('auditLogs'));
    }

    /**
     * Get clinic logo URL for a specific clinic
     */
    public static function getClinicLogo($clinicId)
    {
        $logoPath = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'clinic_logo')
            ->value('value');

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }

        return null;
    }
}
