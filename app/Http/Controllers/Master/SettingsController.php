<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

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

            Log::info('SQL Import started', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'admin' => $user->email,
            ]);

            // Save uploaded file to a temp path for mysql CLI
            $tempPath = storage_path('app/temp_import_' . time() . '.sql');
            file_put_contents($tempPath, $sql);

            try {
                $result = $this->importViaMysqlCli($tempPath);
            } finally {
                @unlink($tempPath); // Always clean up
            }

            if ($result['success']) {
                Log::info('SQL Import completed successfully', [
                    'clinic_id' => $clinic->id,
                    'file_name' => $file->getClientOriginalName(),
                    'method' => $result['method'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "SQL import completed successfully for clinic \"{$clinic->name}\" (via {$result['method']}).",
                ]);
            } else {
                Log::error('SQL Import failed', [
                    'clinic_id' => $clinic->id,
                    'error' => $result['error'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Import failed.',
                    'error' => $result['error'],
                ], 422);
            }

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
     * Import SQL file using mysql CLI (fastest method).
     */
    private function importViaMysqlCli(string $filePath): array
    {
        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Try mysql CLI first (10x-100x faster than PDO)
        $mysqlBin = $this->findMysqlBinary();

        if ($mysqlBin) {
            $command = [
                $mysqlBin,
                '-h', $dbHost,
                '-P', (string) $dbPort,
                '-u', $dbUser,
                '--default-character-set=utf8mb4',
                $dbName,
            ];

            $process = new Process($command, null, ['MYSQL_PWD' => $dbPass]);
            $process->setTimeout(600); // 10 minutes
            $process->setInput(file_get_contents($filePath));
            $process->run();

            if ($process->isSuccessful()) {
                return ['success' => true, 'method' => 'mysql-cli'];
            }

            Log::warning('mysql CLI import failed, falling back to PDO', [
                'error' => $process->getErrorOutput(),
            ]);
        }

        // Fallback: PDO with optimizations
        $sql = file_get_contents($filePath);
        $pdo = DB::connection()->getPdo();

        // Disable checks for faster import
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $pdo->exec('SET UNIQUE_CHECKS=0');
        $pdo->exec('SET AUTOCOMMIT=0');

        try {
            $pdo->exec($sql);
            $pdo->exec('COMMIT');
        } catch (\Exception $e) {
            $pdo->exec('ROLLBACK');
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $pdo->exec('SET UNIQUE_CHECKS=1');
            $pdo->exec('SET AUTOCOMMIT=1');
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        $pdo->exec('SET UNIQUE_CHECKS=1');
        $pdo->exec('SET AUTOCOMMIT=1');

        return ['success' => true, 'method' => 'pdo-optimized'];
    }

    /**
     * Find the mysql binary path.
     */
    private function findMysqlBinary(): ?string
    {
        $paths = ['/usr/bin/mysql', '/usr/local/bin/mysql', '/usr/local/mysql/bin/mysql'];
        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Try which command
        $which = trim(shell_exec('which mysql 2>/dev/null') ?? '');
        if ($which && file_exists($which)) {
            return $which;
        }

        return null;
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

