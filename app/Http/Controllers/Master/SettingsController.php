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

            $jobId = uniqid('imp_', true);
            $statusFile = storage_path("app/sql_import_{$jobId}.json");

            Log::info('SQL Import started', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'job_id' => $jobId,
                'admin' => $user->email,
            ]);

            // Write initial status
            file_put_contents($statusFile, json_encode([
                'status' => 'running',
                'message' => 'Import is running...',
                'updated_at' => now()->toIso8601String(),
            ]));

            // Send response IMMEDIATELY, then continue processing
            $response = response()->json([
                'success' => true,
                'background' => true,
                'job_id' => $jobId,
                'message' => "Import started for clinic \"{$clinic->name}\". Processing...",
            ]);

            // Flush the response to the client
            $response->send();

            // Tell PHP-FPM to close the connection to nginx/client
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            // --- Everything below runs AFTER the client gets the response ---
            ignore_user_abort(true);
            set_time_limit(600);

            try {
                $startTime = microtime(true);

                // Use mysqli::multi_query() — MUCH faster than PDO::exec() for multi-statement SQL
                $dbConfig = config('database.connections.mysql');
                $mysqli = new \mysqli(
                    $dbConfig['host'],
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $dbConfig['database'],
                    $dbConfig['port'] ?? 3306
                );

                if ($mysqli->connect_error) {
                    throw new \Exception('mysqli connection failed: ' . $mysqli->connect_error);
                }

                $mysqli->set_charset($dbConfig['charset'] ?? 'utf8mb4');

                // Speed optimizations
                $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
                $mysqli->query('SET UNIQUE_CHECKS=0');
                $mysqli->query('SET AUTOCOMMIT=0');

                // Execute entire SQL in one shot via native multi-statement handler
                if ($mysqli->multi_query($sql)) {
                    // Consume all results (required by multi_query protocol)
                    do {
                        if ($result = $mysqli->store_result()) {
                            $result->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                }

                // Check for errors after consuming all results
                if ($mysqli->errno) {
                    $error = $mysqli->error;
                    $mysqli->query('ROLLBACK');
                    $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
                    $mysqli->query('SET UNIQUE_CHECKS=1');
                    $mysqli->close();
                    throw new \Exception("MySQL error: {$error}");
                }

                $mysqli->query('COMMIT');
                $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
                $mysqli->query('SET UNIQUE_CHECKS=1');
                $mysqli->query('SET AUTOCOMMIT=1');
                $mysqli->close();

                unset($sql);
                $elapsed = round(microtime(true) - $startTime, 2);

                file_put_contents($statusFile, json_encode([
                    'status' => 'completed',
                    'message' => "Import completed successfully in {$elapsed}s.",
                    'elapsed' => $elapsed,
                    'updated_at' => now()->toIso8601String(),
                ]));

                Log::info('SQL Import completed', [
                    'clinic_id' => $clinic->id,
                    'job_id' => $jobId,
                    'seconds' => $elapsed,
                ]);
            } catch (\Exception $importErr) {
                $elapsed = round(microtime(true) - ($startTime ?? microtime(true)), 2);

                file_put_contents($statusFile, json_encode([
                    'status' => 'failed',
                    'message' => "Import failed after {$elapsed}s: " . $importErr->getMessage(),
                    'elapsed' => $elapsed,
                    'updated_at' => now()->toIso8601String(),
                ]));

                Log::error('SQL Import failed', [
                    'clinic_id' => $clinic->id,
                    'job_id' => $jobId,
                    'error' => $importErr->getMessage(),
                    'seconds' => $elapsed,
                ]);
            }

            // Return is ignored since response already sent, but needed to end method
            return $response;

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

