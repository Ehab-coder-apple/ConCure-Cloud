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

        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['sql'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type. Only .sql files are allowed.'
            ], 422);
        }

        try {
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

            Log::info('SQL Import started', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'admin' => $user->email,
            ]);

            // Execute SQL within a transaction
            DB::beginTransaction();

            // Split SQL by semicolons (handle multi-statement files)
            $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));
            $executed = 0;
            $errors = [];

            foreach ($statements as $index => $statement) {
                if (empty($statement)) continue;
                try {
                    DB::unprepared($statement);
                    $executed++;
                } catch (\Exception $e) {
                    $errors[] = 'Statement #' . ($index + 1) . ': ' . $e->getMessage();
                    // Stop on first error to avoid partial imports
                    DB::rollBack();
                    Log::error('SQL Import failed at statement', [
                        'clinic_id' => $clinic->id,
                        'statement_index' => $index + 1,
                        'error' => $e->getMessage(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Import failed at statement #' . ($index + 1) . '. All changes rolled back.',
                        'error' => $e->getMessage(),
                        'executed_before_error' => $executed,
                    ], 422);
                }
            }

            DB::commit();

            Log::info('SQL Import completed successfully', [
                'clinic_id' => $clinic->id,
                'statements_executed' => $executed,
            ]);

            return response()->json([
                'success' => true,
                'message' => "SQL import completed successfully for clinic \"{$clinic->name}\". {$executed} statement(s) executed.",
                'statements_executed' => $executed,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SQL Import error', [
                'clinic_id' => $clinic->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
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

