<?php

namespace App\Http\Controllers;

use App\Services\ClinicBackupService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get the active tab from request parameter
        $activeTab = $request->get('tab', 'profile');

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
                'dashboard_default_period' => 'month',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'sms_notifications' => false,
                'whatsapp_number' => null,
                'clinic_logo' => null,
            ], $settings);

            // Add logo URL if logo exists (use robust helper that can stream the file)
            if (isset($clinicSettings['clinic_logo']) && $clinicSettings['clinic_logo']) {
                $clinicSettings['clinic_logo_url'] = self::getClinicLogo($user->clinic_id);
            }
        }

        // Get clinic information
        $clinicInfo = [];
        if ($user->clinic_id) {
            $clinic = \App\Models\Clinic::find($user->clinic_id);
            if ($clinic) {
                $clinicInfo = [
                    'name' => $clinic->name,
                    'email' => $clinic->email,
                    'phone' => $clinic->phone,
                    'address' => $clinic->address,
                ];

                // Get website from settings
                $website = DB::table('settings')
                    ->where('clinic_id', $user->clinic_id)
                    ->where('key', 'clinic_website')
                    ->value('value');

                $clinicInfo['website'] = $website;
            }
        }

        // Backup info
        $lastBackup = null; $recentBackups = collect();
        try {
            if ($user->clinic_id) {
                $service = app(\App\Services\ClinicBackupService::class);
                $lastBackup = $service->getLastBackupForClinic((int)$user->clinic_id);
                $recentBackups = $service->getRecentBackups((int)$user->clinic_id, 5);
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Manual backup document types (per clinic)
        $manualDocTypes = [];
        $manualIncludeDb = false; // default OFF for manual
        try {
            if ($user->clinic_id && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $raw = DB::table('settings')->where('clinic_id', $user->clinic_id)->where('key','manual_backup_doc_types')->value('value');
                if ($raw) {
                    $arr = json_decode((string)$raw, true);
                    if (is_array($arr)) { $manualDocTypes = array_values(array_filter(array_map('strtolower', $arr))); }
                    else { $manualDocTypes = array_values(array_filter(array_map(fn($e)=> strtolower(trim($e)), explode(',', (string)$raw)))); }
                }
                $val = DB::table('settings')->where('clinic_id', $user->clinic_id)->where('key','manual_backup_include_db')->value('value');
                $manualIncludeDb = in_array(strtolower((string)$val), ['1','true','yes','on'], true);
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Per-clinic auto backup toggles (super admin only)
        $clinicsAutoBackup = collect();
        try {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                if (\Illuminate\Support\Facades\Schema::hasTable('clinics') && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    $clinics = DB::table('clinics')->select('id','name')->orderBy('id')->get();
                    $map = DB::table('settings')
                        ->where('key','auto_backup_enabled')
                        ->whereNotNull('clinic_id')
                        ->pluck('value','clinic_id');
                    $clinicsAutoBackup = $clinics->map(function($c) use ($map){
                        $val = $map[$c->id] ?? null;
                        $enabled = in_array(strtolower((string)$val), ['1','true','yes','on'], true);
                        return (object) ['id'=>$c->id,'name'=>$c->name,'enabled'=>$enabled];
                    });
                }
            }
        } catch (\Throwable $e) { /* ignore */ }

        // Get global settings (admin only)
        $globalSettings = [];
        if ($user->role === 'admin' || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            $sessionLifetime = DB::table('settings')
                ->whereNull('clinic_id')
                ->where('key', 'session_lifetime')
                ->value('value');

            $globalSettings['session_lifetime'] = $sessionLifetime ?? 480; // Default 8 hours
        }

        return view('settings.index', compact('clinicSettings', 'clinicInfo', 'activeTab', 'lastBackup', 'recentBackups', 'clinicsAutoBackup', 'manualDocTypes', 'manualIncludeDb', 'globalSettings'));
    }

    public function setClinicAutoBackup(Request $request)
    {
        $user = Auth::user();
        if (!method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => __('Only super admin can change this setting.')], 403);
        }

        $clinicId = (int) $request->input('clinic_id');
        $enabled = filter_var($request->input('enabled'), FILTER_VALIDATE_BOOLEAN);
        if ($clinicId <= 0) {
            return response()->json(['success' => false, 'message' => __('Invalid clinic.')], 422);
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            return response()->json(['success' => false, 'message' => __('Settings table not found.')], 500);
        }

        DB::table('settings')->updateOrInsert(
            ['clinic_id' => $clinicId, 'key' => 'auto_backup_enabled'],
            ['value' => $enabled ? '1' : '0', 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
        );

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }

    public function setManualBackupDocTypes(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && ($user->role ?? '') !== 'admin')) {
            return response()->json(['success' => false, 'message' => __('Unauthorized.')], 403);
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            return response()->json(['success' => false, 'message' => __('Settings table not found.')], 500);
        }
        $clinicId = (int) ($user->clinic_id ?? 0);
        if ($user->isSuperAdmin()) {
            $clinicId = (int) $request->input('clinic_id', $clinicId);
        }
        if ($clinicId <= 0) {
            return response()->json(['success' => false, 'message' => __('No clinic context.')], 422);
        }
        $types = $request->input('types', []);
        if (!is_array($types)) { $types = []; }
        $allowedSet = ['pdf','doc','docx','xls','xlsx','xlsm','csv','ppt','pptx'];
        $clean = array_values(array_unique(array_intersect(array_map(fn($e)=> strtolower(trim((string)$e)), $types), $allowedSet)));
        DB::table('settings')->updateOrInsert(
            ['clinic_id' => $clinicId, 'key' => 'manual_backup_doc_types'],
            ['value' => json_encode($clean), 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
        );
        return response()->json(['success' => true, 'types' => $clean]);
    }

    public function setManualBackupIncludeDb(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isSuperAdmin() && ($user->role ?? '') !== 'admin')) {
            return response()->json(['success' => false, 'message' => __('Unauthorized.')], 403);
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            return response()->json(['success' => false, 'message' => __('Settings table not found.')], 500);
        }
        $clinicId = (int) ($user->clinic_id ?? 0);
        if ($user->isSuperAdmin()) {
            $clinicId = (int) $request->input('clinic_id', $clinicId);
        }
        if ($clinicId <= 0) {
            return response()->json(['success' => false, 'message' => __('No clinic context.')], 422);
        }
        $include = filter_var($request->input('include', false), FILTER_VALIDATE_BOOLEAN);
        DB::table('settings')->updateOrInsert(
            ['clinic_id' => $clinicId, 'key' => 'manual_backup_include_db'],
            ['value' => $include ? '1' : '0', 'updated_at' => now(), 'created_at' => DB::raw('COALESCE(created_at, NOW())')]
        );
        return response()->json(['success' => true, 'include' => $include]);
    }


    public function update(Request $request)
    {
        \Log::info('UPDATE METHOD CALLED', ['timestamp' => now()]);
        \Log::info('Request data', [
            'all_data' => $request->all(),
            'files' => $request->allFiles(),
            'has_clinic_logo' => $request->hasFile('clinic_logo'),
            'clinic_logo_valid' => $request->hasFile('clinic_logo') ? $request->file('clinic_logo')->isValid() : 'no file'
        ]);

        // Check if clinic_logo exists in request
        if ($request->has('clinic_logo')) {
            $file = $request->file('clinic_logo');
            \Log::info('File details', [
                'file_exists' => !is_null($file),
                'file_class' => get_class($file),
                'file_valid' => $file ? $file->isValid() : 'null',
                'file_error' => $file ? $file->getError() : 'null',
                'file_size' => $file ? $file->getSize() : 'null',
                'file_name' => $file ? $file->getClientOriginalName() : 'null'
            ]);
        }

        try {
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
            'dashboard_default_period',
            'notifications_enabled',
            'email_notifications',
            'sms_notifications',
            'whatsapp_number',
            'clinic_logo'
        ];

        // Simple validation without file validation
        $validatedData = $request->validate([
            'default_language' => 'nullable|in:en,ar,ku',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
            'currency' => 'nullable|in:USD,EUR,GBP,IQD,JOD,EGP',
            'dashboard_default_period' => 'nullable|in:day,month,year',
            'notifications_enabled' => 'boolean',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'whatsapp_number' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ]);

        // Prevent any attempt to modify application name or other restricted settings
        if ($request->has('app_name') || $request->has('application_name') || $request->has('platform_name')) {
            return response()->json([
                'success' => false,
                'message' => __('Application name cannot be modified by clinic users.')
            ], 403);
        }

        // Handle logo upload with better error handling
        if ($request->has('clinic_logo')) {
            $file = $request->file('clinic_logo');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded.',
                    'debug' => ['error' => 'file_not_found']
                ], 400);
            }

            if (!$file->isValid()) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini (current limit: ' . ini_get('upload_max_filesize') . ').',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                    UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
                ];

                $errorCode = $file->getError();
                $errorMessage = $errorMessages[$errorCode] ?? 'Unknown upload error.';

                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $errorMessage,
                    'debug' => [
                        'error_code' => $errorCode,
                        'file_size' => $file->getSize(),
                        'file_name' => $file->getClientOriginalName(),
                        'max_upload_size' => ini_get('upload_max_filesize')
                    ]
                ], 400);
            }
        }

        // Simple logo upload handling
        if ($request->hasFile('clinic_logo') && $request->file('clinic_logo')->isValid()) {
            \Log::info('Logo upload started', [
                'clinic_id' => $user->clinic_id,
                'file_name' => $request->file('clinic_logo')->getClientOriginalName(),
                'file_size' => $request->file('clinic_logo')->getSize()
            ]);

            $logoFile = $request->file('clinic_logo');

            // Delete old logo if exists
            $oldLogo = DB::table('settings')
                ->where('clinic_id', $user->clinic_id)
                ->where('key', 'clinic_logo')
                ->value('value');

            \Log::info('Old logo check', ['old_logo' => $oldLogo]);

            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
                \Log::info('Deleted old logo', ['path' => $oldLogo]);
            }

            // Store new logo
            $logoPath = $logoFile->store('clinic-logos', 'public');
            $validatedData['clinic_logo'] = $logoPath;

            \Log::info('New logo stored', [
                'path' => $logoPath,
                'full_path' => storage_path('app/public/' . $logoPath),
                'exists' => Storage::disk('public')->exists($logoPath),
                'url' => Storage::url($logoPath)
            ]);
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

        // Add debug info for logo uploads
        $debugInfo = [];
        if (isset($validatedData['clinic_logo'])) {
            $debugInfo['logo_path'] = $validatedData['clinic_logo'];
            $debugInfo['logo_url'] = Storage::url($validatedData['clinic_logo']);
            $debugInfo['file_exists'] = Storage::disk('public')->exists($validatedData['clinic_logo']);
        }

        return response()->json([
            'success' => true,
            'message' => __('Settings updated successfully.'),
            'debug' => $debugInfo
        ]);

        } catch (\Exception $e) {
            \Log::error('Settings update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'clinic_id' => isset($user) ? $user->clinic_id : null,
                'user_id' => isset($user) ? $user->id : null
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while updating settings: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update clinic basic information
     */
    public function updateClinicInfo(Request $request)
    {
        $user = Auth::user();

        // Only allow clinic admins to update clinic info
        if (!$user->clinic_id || !in_array($user->role, ['admin', 'doctor'])) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to update clinic information.')
            ], 403);
        }

        $request->validate([
            'clinic_name' => 'required|string|max:255',
            'clinic_email' => 'required|email|max:255',
            'clinic_phone' => 'nullable|string|max:20',
            'clinic_address' => 'nullable|string|max:500',
            'clinic_website' => 'nullable|url|max:255',
        ]);

        try {
            $clinic = \App\Models\Clinic::findOrFail($user->clinic_id);

            $clinic->update([
                'name' => $request->clinic_name,
                'email' => $request->clinic_email,
                'phone' => $request->clinic_phone,
                'address' => $request->clinic_address,
            ]);

            // Store website in settings if provided
            if ($request->clinic_website) {
                DB::table('settings')->updateOrInsert(
                    [
                        'clinic_id' => $user->clinic_id,
                        'key' => 'clinic_website'
                    ],
                    [
                        'value' => $request->clinic_website,
                        'type' => 'string',
                        'updated_at' => now()
                    ]
                );
            }

            // Log the clinic info update
            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'clinic_info_updated',
                'model_type' => 'Clinic',
                'model_id' => $clinic->id,
                'description' => 'Updated clinic basic information',
                'new_values' => json_encode($request->only(['clinic_name', 'clinic_email', 'clinic_phone', 'clinic_address', 'clinic_website'])),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Clinic information updated successfully.')
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update clinic info', [
                'clinic_id' => $user->clinic_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update clinic information. Please try again.')
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'title_prefix' => 'nullable|string|max:50',
            'scientific_degree' => 'nullable|string|max:100',
        ]);

        try {
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'title_prefix' => $request->title_prefix,
                'scientific_degree' => $request->scientific_degree,
            ]);

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'profile_updated',
                'model_type' => 'User',
                'model_id' => $user->id,
                'description' => 'Updated personal profile information',
                'changes' => json_encode([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'title_prefix' => $request->title_prefix,
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Profile updated successfully.')
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to update profile. Please try again.')
            ], 500);
        }
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

        if (!$logoPath) {
            return null;
        }

        // If a full URL is stored
        if (preg_match('#^https?://#i', $logoPath)) {
            return $logoPath;
        }

        // Normalize path (handle legacy values like "storage/clinic-logos/..." or "public/clinic-logos/...")
        $relative = ltrim(str_replace(['storage/','public/'], '', $logoPath), '/');

        $exists = Storage::disk('public')->exists($relative)
            || file_exists(storage_path('app/public/' . $relative))
            || (function_exists('public_path') && file_exists(public_path($relative)));

        if ($exists) {
            // Use streaming route to avoid dependency on /storage symlink
            return route('clinic.logo', ['clinic' => $clinicId]);
        }

        return null;
    }


    /**
     * Stream/serve clinic logo without requiring public/storage symlink
     */
    public function serveClinicLogo($clinic)
    {
        try {
            $logoPath = DB::table('settings')
                ->where('clinic_id', $clinic)
                ->where('key', 'clinic_logo')
                ->value('value');

            if (!$logoPath) {
                abort(404);
            }

            // If a full URL is stored, redirect to it
            if (preg_match('#^https?://#i', $logoPath)) {
                return redirect()->away($logoPath);
            }

            $relative = ltrim(str_replace(['storage/','public/'], '', $logoPath), '/');

            // Prefer serving via the public disk
            if (Storage::disk('public')->exists($relative)) {
                $mime = Storage::disk('public')->mimeType($relative) ?: 'image/*';
                return response(Storage::disk('public')->get($relative), 200, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=604800'
                ]);
            }

            // Fallback: direct file response from storage path
            $abs = storage_path('app/public/' . $relative);
            if (file_exists($abs)) {
                $mime = File::mimeType($abs) ?: 'image/*';
                return response()->file($abs, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=604800'
                ]);
            }

            // Fallback: if placed directly in public/
            if (function_exists('public_path')) {
                $publicCandidate = public_path($relative);
                if (file_exists($publicCandidate)) {
                    $mime = File::mimeType($publicCandidate) ?: 'image/*';
                    return response()->file($publicCandidate, [
                        'Content-Type' => $mime,
                        'Cache-Control' => 'public, max-age=604800'
                    ]);
                }
            }

            abort(404);
        } catch (\Throwable $e) {
            \Log::error('serveClinicLogo error', [
                'clinic' => $clinic,
                'message' => $e->getMessage(),
            ]);
            abort(404);
        }
    }

    /**
     * Manual on-demand backup for the current clinic (admin/super admin only)
     */
    public function backup(ClinicBackupService $backupService)
    {
        $user = Auth::user();

        // Only allow admins and super admins
        if (!method_exists($user, 'isSuperAdmin') || (!$user->isSuperAdmin() && $user->role !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to create backups.')
            ], 403);
        }

        $clinicId = (int) ($user->clinic_id ?? 0);
        if ($clinicId <= 0 && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('No clinic context for backup.')
            ], 400);
        }

        try {
            $targetClinicId = $clinicId > 0 ? $clinicId : (int) request('clinic_id', 0);
            if ($user->isSuperAdmin() && $targetClinicId > 0) {
                $clinicId = $targetClinicId; // allow super admin to target specific clinic
            }

            $result = $backupService->generateBackupForClinic($clinicId, 'manual', $user->id, 30);
            $fileRel = 'clinic-' . $clinicId . '/' . basename($result['path']);

            return response()->json([
                'success' => true,
                'message' => __('Backup created successfully.'),
                'download_url' => route('settings.download-backup', ['file' => $fileRel]),
                'last_backup_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create backup: :error', ['error' => $e->getMessage()])
            ], 500);
        }
    }
    /**
     * Manual backup that returns the ZIP directly in the same request
     */
    public function backupDirect(ClinicBackupService $backupService)
    {
        $user = Auth::user();
        if (!method_exists($user, 'isSuperAdmin') || (!$user->isSuperAdmin() && ($user->role ?? '') !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to create backups.')
            ], 403);
        }

        $clinicId = (int) ($user->clinic_id ?? 0);
        if ($clinicId <= 0 && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('No clinic context for backup.')
            ], 400);
        }

        try {
            $targetClinicId = $clinicId > 0 ? $clinicId : (int) request('clinic_id', 0);
            if ($user->isSuperAdmin() && $targetClinicId > 0) {
                $clinicId = $targetClinicId;
            }

            $result = $backupService->generateBackupForClinic($clinicId, 'manual', $user->id, 30);
            $path = $result['path'];

            // FS flush retry (handles slow NFS/filesystems): wait up to ~2s for the file to appear
            for ($i = 0; $i < 10; $i++) {
                clearstatcache(true, $path);
                if (is_file($path)) { break; }
                usleep(200000); // 200ms
            }

            $filename = basename($path);
            return response()->download($path, $filename, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => "attachment; filename=\"$filename\""
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to create backup: :error', ['error' => $e->getMessage()])
            ], 500);
        }
    }


    /**
     * Clear application cache
     */
    public function clearCache()
    {
        $user = Auth::user();

        // Only allow admins to clear cache
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to clear cache.')
            ], 403);
        }

        try {
            // Clear Laravel caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            \Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => __('Cache cleared successfully.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to clear cache: :error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Update system (placeholder for future implementation)
     */
    public function updateSystem()
    {
        $user = Auth::user();

        // Only allow admins to update system
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized to update system.')
            ], 403);
        }

        // For now, just return a message that this feature is coming soon
        return response()->json([
            'success' => false,
            'message' => __('System update feature is coming soon.')
        ]);
    }

    /**
     * Update session lifetime setting
     */
    public function updateSessionLifetime(Request $request)
    {
        $user = Auth::user();

        // Only allow admins to update session lifetime
        if ($user->role !== 'admin' && !(method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized. Only administrators can update session settings.')
            ], 403);
        }

        $validated = $request->validate([
            'session_lifetime' => 'required|integer|min:5|max:43200', // Min 5 minutes, Max 30 days
        ]);

        try {
            DB::table('settings')->updateOrInsert(
                [
                    'clinic_id' => null,
                    'key' => 'session_lifetime'
                ],
                [
                    'value' => $validated['session_lifetime'],
                    'type' => 'integer',
                    'updated_at' => now()
                ]
            );

            // Update the session config in runtime
            config(['session.lifetime' => $validated['session_lifetime']]);

            return response()->json([
                'success' => true,
                'message' => __('Session lifetime updated successfully. New sessions will use :minutes minutes.', ['minutes' => $validated['session_lifetime']])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update session lifetime: :error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Create database backup file
     */
    private function createDatabaseBackup()
    {
        $backupDir = storage_path('app/backups');

        // Create backup directory if it doesn't exist
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFileName = "concure_backup_{$timestamp}.sqlite";
        $backupPath = $backupDir . '/' . $backupFileName;

        // Get database path
        $databasePath = database_path('concure.sqlite');

        if (!file_exists($databasePath)) {
            throw new \Exception('Database file not found');
        }

        // Copy database file to backup location
        if (!copy($databasePath, $backupPath)) {
            throw new \Exception('Failed to create backup file');
        }

        return $backupPath;
    }

    /**
     * Download backup file
     */
    public function downloadBackup($file)
    {
        $user = Auth::user();

        // Only allow admins and super admins to download backups
        if (!method_exists($user, 'isSuperAdmin') || (!$user->isSuperAdmin() && $user->role !== 'admin')) {
            abort(403, 'Unauthorized to download backups.');
        }

        $backupPath = storage_path('app/backups/' . $file);

        // Security check: ensure file exists and is in backup directory
        if (!file_exists($backupPath) || !str_starts_with(realpath($backupPath), realpath(storage_path('app/backups')))) {
            abort(404, 'Backup file not found.');
        }

        // Tenant isolation: Admins can only access their clinic's folder
        if (!$user->isSuperAdmin()) {
            $expected = 'clinic-' . (int) $user->clinic_id . DIRECTORY_SEPARATOR;
            $relReal = trim(str_replace(realpath(storage_path('app/backups')) . DIRECTORY_SEPARATOR, '', realpath($backupPath)), DIRECTORY_SEPARATOR);
            if (strpos($relReal, $expected) !== 0) {
                abort(403, 'Unauthorized to access this backup.');
            }
        }

        return response()->download($backupPath);
    }

    /**
     * Show user guide in fullscreen mode
     */
    public function userGuide()
    {
        return view('settings.user-guide');
    }

    /**
     * Export user guide in different languages and formats
     */
    public function exportUserGuide(Request $request)
    {
        $language = $request->get('language', 'en');
        $format = $request->get('format', 'pdf');

        // Validate language
        $supportedLanguages = ['en', 'ar', 'ku-bahdeni', 'ku-sorani'];
        if (!in_array($language, $supportedLanguages)) {
            $language = 'en';
        }

        // For now, return success - the PDF generation is handled client-side
        // In the future, this could be enhanced with server-side PDF generation
        return response()->json([
            'success' => true,
            'message' => __('User guide export initiated'),
            'language' => $language,
            'format' => $format
        ]);
    }
}
