<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class SettingsController extends Controller
{
    /**
     * Display the master settings page.
     */
    public function index()
    {
        $user = Auth::user();

        // Get master timezone setting
        $masterTimezone = DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'master_timezone')
            ->value('value') ?? 'UTC';

        // Get all available timezones
        $timezones = $this->getTimezones();

        // Get clinics for SQL import dropdown
        $clinics = Clinic::orderBy('name')->get(['id', 'name']);

        return view('master.settings.index', compact('masterTimezone', 'timezones', 'clinics'));
    }

    /**
     * Update master timezone setting.
     */
    public function updateTimezone(Request $request)
    {
        $user = Auth::user();

        // Only allow super admins to update master timezone
        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized. Only super administrators can update master timezone.')
            ], 403);
        }

        $validated = $request->validate([
            'timezone' => 'required|string|timezone',
        ]);

        try {
            DB::table('settings')->updateOrInsert(
                [
                    'clinic_id' => null,
                    'key' => 'master_timezone'
                ],
                [
                    'value' => $validated['timezone'],
                    'type' => 'string',
                    'description' => 'Master admin timezone',
                    'updated_at' => now()
                ]
            );

            // Update the timezone config in runtime
            config(['app.timezone' => $validated['timezone']]);
            date_default_timezone_set($validated['timezone']);

            return response()->json([
                'success' => true,
                'message' => __('Timezone updated successfully to :timezone', ['timezone' => $validated['timezone']])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update timezone: :error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Import SQL data into a specific clinic.
     */
    public function importSql(Request $request)
    {
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only super administrators can import SQL data.'
            ], 403);
        }

        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'sql_file'  => 'required|file|max:51200', // max 50MB
        ]);

        $clinic = Clinic::findOrFail($request->clinic_id);
        $file = $request->file('sql_file');

        // Validate file extension (also accept macOS duplicate names like "file.sql (1)")
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $isSql = in_array($extension, ['sql']) || preg_match('/\.sql\s*\(\d+\)$/i', $originalName);
        if (!$isSql) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type. Only .sql files are allowed.'
            ], 422);
        }

        try {
            // Allow up to 10 minutes for large SQL imports
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            // Disable query log to save memory during bulk import
            DB::disableQueryLog();

            $sql = file_get_contents($file->getRealPath());

            if (empty(trim($sql))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The SQL file is empty.'
                ], 422);
            }

            // Security: block dangerous statements
            $blocked = ['DROP\s+DATABASE', 'DROP\s+TABLE', 'TRUNCATE\s+TABLE', 'ALTER\s+TABLE', 'CREATE\s+DATABASE', 'GRANT\s+', 'REVOKE\s+'];
            foreach ($blocked as $pattern) {
                if (preg_match('/' . $pattern . '/i', $sql)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'SQL file contains blocked statements (DROP, TRUNCATE, ALTER, GRANT, etc.). Please provide only INSERT/UPDATE statements.'
                    ], 422);
                }
            }

            // Save SQL file for background processing
            $jobId = uniqid('imp_', true);
            $tempPath = storage_path("app/sql_import_{$jobId}.sql");
            file_put_contents($tempPath, $sql);
            unset($sql); // Free memory

            Log::info('SQL Import queued', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'job_id' => $jobId,
                'admin' => $user->email,
            ]);

            // Create initial status file
            $statusFile = storage_path("app/sql_import_{$jobId}.json");
            file_put_contents($statusFile, json_encode([
                'status' => 'queued',
                'message' => 'Import is starting...',
                'updated_at' => now()->toIso8601String(),
            ]));

            // Launch artisan command in background (no nginx timeout)
            $artisan = base_path('artisan');
            $cmd = sprintf(
                'nohup php %s sql:import %s %s %s > %s 2>&1 &',
                escapeshellarg($artisan),
                escapeshellarg($tempPath),
                escapeshellarg($clinic->id),
                escapeshellarg($jobId),
                escapeshellarg(storage_path("logs/sql_import_{$jobId}.log"))
            );
            exec($cmd);

            return response()->json([
                'success' => true,
                'background' => true,
                'job_id' => $jobId,
                'message' => "Import started in background for clinic \"{$clinic->name}\". Monitoring progress...",
            ]);

        } catch (\Exception $e) {
            Log::error('SQL Import error', [
                'clinic_id' => $clinic->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check status of a background SQL import job.
     */
    public function importSqlStatus(Request $request)
    {
        $jobId = $request->query('job_id');
        if (!$jobId || !preg_match('/^imp_[a-f0-9_.]+$/i', $jobId)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid job ID.'], 400);
        }

        $statusFile = storage_path("app/sql_import_{$jobId}.json");

        if (!file_exists($statusFile)) {
            return response()->json(['status' => 'unknown', 'message' => 'Job not found.'], 404);
        }

        $data = json_decode(file_get_contents($statusFile), true);

        // Clean up status file if job is done
        if (in_array($data['status'] ?? '', ['completed', 'failed'])) {
            // Keep for 5 minutes then auto-clean (don't delete immediately so frontend can read it)
        }

        return response()->json($data);
    }

    /**
     * Get list of common timezones grouped by region.
     */
    private function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'America/Anchorage' => 'Alaska',
            'Pacific/Honolulu' => 'Hawaii',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris, Berlin, Rome',
            'Europe/Athens' => 'Athens, Istanbul',
            'Europe/Moscow' => 'Moscow',
            'Asia/Dubai' => 'Dubai',
            'Asia/Baghdad' => 'Baghdad',
            'Asia/Tehran' => 'Tehran',
            'Asia/Karachi' => 'Karachi',
            'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
            'Asia/Dhaka' => 'Dhaka',
            'Asia/Bangkok' => 'Bangkok, Hanoi, Jakarta',
            'Asia/Singapore' => 'Singapore',
            'Asia/Hong_Kong' => 'Hong Kong',
            'Asia/Shanghai' => 'Beijing, Shanghai',
            'Asia/Tokyo' => 'Tokyo, Osaka',
            'Asia/Seoul' => 'Seoul',
            'Australia/Sydney' => 'Sydney, Melbourne',
            'Australia/Brisbane' => 'Brisbane',
            'Australia/Adelaide' => 'Adelaide',
            'Australia/Perth' => 'Perth',
            'Pacific/Auckland' => 'Auckland, Wellington',
            'Pacific/Fiji' => 'Fiji',
            'Africa/Cairo' => 'Cairo',
            'Africa/Johannesburg' => 'Johannesburg',
            'Africa/Lagos' => 'Lagos',
            'Africa/Nairobi' => 'Nairobi',
            'America/Sao_Paulo' => 'Brasilia, São Paulo',
            'America/Argentina/Buenos_Aires' => 'Buenos Aires',
            'America/Mexico_City' => 'Mexico City',
            'America/Toronto' => 'Toronto',
        ];
    }
}

