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

            $jobId = 'imp_' . bin2hex(random_bytes(12));
            $statusToken = bin2hex(random_bytes(24));
            $statusFile = $this->getImportStatusPath($jobId);
            $statusUrl = route('api.master.settings.import-sql-status', [
                'job_id' => $jobId,
                'token' => $statusToken,
            ]);

            Log::info('SQL Import started', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'job_id' => $jobId,
                'admin' => $user->email,
            ]);

            // Write initial status
            $this->writeImportStatus($statusFile, [
                'job_id' => $jobId,
                'status_token_hash' => hash('sha256', $statusToken),
                'status' => 'running',
                'message' => 'Import is running...',
            ]);

            // Release the PHP session lock before continuing with the long-running import.
            $this->closeSessionLock($request);

            // Send response IMMEDIATELY, then continue processing
            $response = response()->json([
                'success' => true,
                'background' => true,
                'job_id' => $jobId,
                'status_url' => $statusUrl,
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
                $elapsed = $this->executeImport($sql);
                unset($sql);

                $this->writeImportStatus($statusFile, [
                    'status' => 'completed',
                    'message' => "Import completed successfully in {$elapsed}s.",
                    'elapsed' => $elapsed,
                ]);

                Log::info('SQL Import completed', [
                    'clinic_id' => $clinic->id,
                    'job_id' => $jobId,
                    'seconds' => $elapsed,
                ]);
            } catch (\Throwable $importErr) {
                $elapsed = round(microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)), 2);

                $this->writeImportStatus($statusFile, [
                    'status' => 'failed',
                    'message' => "Import failed after {$elapsed}s: " . $importErr->getMessage(),
                    'elapsed' => $elapsed,
                ]);

                Log::error('SQL Import failed', [
                    'clinic_id' => $clinic->id,
                    'job_id' => $jobId,
                    'error' => $importErr->getMessage(),
                    'seconds' => $elapsed,
                ]);
            }

            // Return is ignored since response already sent, but needed to end method
            return $response;

        } catch (\Throwable $e) {
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
        $token = (string) $request->query('token', '');

        if (!$jobId || !preg_match('/^imp_[a-z0-9._-]+$/i', $jobId)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid job ID.'], 400);
        }

        if ($token === '') {
            return response()->json(['status' => 'error', 'message' => 'Missing status token.'], 400);
        }

        $statusFile = $this->getImportStatusPath($jobId);

        if (!file_exists($statusFile)) {
            return response()->json(['status' => 'unknown', 'message' => 'Job not found.'], 404);
        }

        $data = json_decode(file_get_contents($statusFile), true) ?: [];

        $expectedHash = $data['status_token_hash'] ?? null;
        if (!$expectedHash || !hash_equals($expectedHash, hash('sha256', $token))) {
            return response()->json(['status' => 'forbidden', 'message' => 'Invalid status token.'], 403);
        }

        return response()->json(array_filter([
            'job_id' => $data['job_id'] ?? $jobId,
            'status' => $data['status'] ?? 'unknown',
            'message' => $data['message'] ?? 'Job status unavailable.',
            'elapsed' => $data['elapsed'] ?? null,
            'updated_at' => $data['updated_at'] ?? null,
        ], static fn ($value) => $value !== null));
    }

    private function executeImport(string $sql): float
    {
        $startTime = microtime(true);
        $dbConfig = config('database.connections.mysql');
        $mysqli = null;

        try {
            $mysqli = new \mysqli(
                $dbConfig['host'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['database'],
                $dbConfig['port'] ?? 3306
            );

            if ($mysqli->connect_error) {
                throw new \RuntimeException('mysqli connection failed: ' . $mysqli->connect_error);
            }

            $mysqli->set_charset($dbConfig['charset'] ?? 'utf8mb4');
            $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
            $mysqli->query('SET UNIQUE_CHECKS=0');
            $mysqli->query('SET AUTOCOMMIT=0');

            if (!$mysqli->multi_query($sql)) {
                throw new \RuntimeException('MySQL error: ' . $mysqli->error);
            }

            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }

                if (!$mysqli->more_results()) {
                    break;
                }

                if (!$mysqli->next_result()) {
                    throw new \RuntimeException('MySQL error: ' . $mysqli->error);
                }
            } while (true);

            $mysqli->query('COMMIT');

            return round(microtime(true) - $startTime, 2);
        } catch (\Throwable $e) {
            if ($mysqli instanceof \mysqli && !$mysqli->connect_errno) {
                try {
                    $mysqli->query('ROLLBACK');
                } catch (\Throwable $rollbackError) {
                    // Ignore rollback failures; the original import exception is more important.
                }
            }

            throw $e;
        } finally {
            if ($mysqli instanceof \mysqli && !$mysqli->connect_errno) {
                $this->restoreImportSession($mysqli);
                $mysqli->close();
            }
        }
    }

    private function restoreImportSession(\mysqli $mysqli): void
    {
        try {
            $mysqli->query('SET FOREIGN_KEY_CHECKS=1');
            $mysqli->query('SET UNIQUE_CHECKS=1');
            $mysqli->query('SET AUTOCOMMIT=1');
        } catch (\Throwable $e) {
            Log::warning('Failed to restore MySQL session settings after SQL import.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function writeImportStatus(string $statusFile, array $data): void
    {
        $statusDirectory = dirname($statusFile);
        if (!is_dir($statusDirectory)) {
            mkdir($statusDirectory, 0775, true);
        }

        $existing = [];
        if (is_file($statusFile)) {
            $existing = json_decode(file_get_contents($statusFile), true) ?: [];
        }

        file_put_contents($statusFile, json_encode(array_merge($existing, $data, [
            'updated_at' => now()->toIso8601String(),
        ])), LOCK_EX);
    }

    private function getImportStatusPath(string $jobId): string
    {
        return storage_path("app/sql_import_{$jobId}.json");
    }

    private function closeSessionLock(Request $request): void
    {
        if (!$request->hasSession()) {
            return;
        }

        try {
            $request->session()->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to save session before SQL import.', [
                'error' => $e->getMessage(),
            ]);
        }

        if (function_exists('session_write_close') && session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
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

