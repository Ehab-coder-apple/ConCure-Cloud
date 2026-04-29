<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class SettingsController extends Controller
{
    private const MAX_SQL_BYTES = 52428800;

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

        // Resolve master branding logo (uploaded via this page) for preview.
        $brandingLogoRelPath = self::getMasterBrandingLogoRelPath();
        $brandingLogoUrl = $brandingLogoRelPath && file_exists(public_path($brandingLogoRelPath))
            ? asset($brandingLogoRelPath) . '?v=' . filemtime(public_path($brandingLogoRelPath))
            : null;

        return view('master.settings.index', compact(
            'masterTimezone', 'timezones', 'clinics', 'brandingLogoUrl'
        ));
    }

    /**
     * Resolve the relative path (under public/) of the configured master branding logo.
     * Falls back to images/concure-logo.png if a file exists there but no DB row is set.
     */
    public static function getMasterBrandingLogoRelPath(): ?string
    {
        $stored = DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'master_branding_logo_path')
            ->value('value');

        if ($stored && file_exists(public_path($stored))) {
            return $stored;
        }

        $legacy = 'images/concure-logo.png';
        if (file_exists(public_path($legacy))) {
            return $legacy;
        }

        return null;
    }

    /**
     * Like getMasterBrandingLogoRelPath() but only returns a path that DomPDF
     * can definitely render (PNG or JPEG, valid getimagesize). Returns null
     * for WebP, broken files, or formats DomPDF cannot parse, so the PDF
     * silently omits the image instead of showing an error string.
     */
    public static function getMasterBrandingLogoForPdfRelPath(): ?string
    {
        $rel = self::getMasterBrandingLogoRelPath();
        if (!$rel) {
            return null;
        }

        $abs = public_path($rel);
        $info = @getimagesize($abs);
        if (!$info || empty($info['mime'])) {
            return null;
        }

        return in_array($info['mime'], ['image/png', 'image/jpeg'], true) ? $rel : null;
    }

    /**
     * Upload (or replace) the master branding logo used on PDFs and master pages.
     */
    public function updateBrandingLogo(Request $request)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return redirect()->route('master.settings')
                ->withErrors(['logo' => __('Unauthorized. Only super administrators can update branding.')]);
        }

        $request->validate([
            'logo' => 'required|file|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        $file = $request->file('logo');

        $dir = public_path('images');
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Remove any previous concure-logo.* so stale extensions don't linger.
        foreach ((array) glob($dir . DIRECTORY_SEPARATOR . 'concure-logo.*') as $oldFile) {
            @unlink($oldFile);
        }

        // Re-encode the upload to a clean RGBA PNG so DomPDF can always parse it.
        // DomPDF chokes on WebP, 16-bit / interlaced / indexed-alpha PNGs, etc.
        $targetPath = $dir . DIRECTORY_SEPARATOR . 'concure-logo.png';
        $sourcePath = $file->getRealPath();
        $normalized = self::normalizeImageToPng($sourcePath, $targetPath);

        if (!$normalized) {
            // Fallback: keep the original bytes with their real extension.
            $ext = strtolower($file->getClientOriginalExtension()) ?: 'png';
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
                $ext = 'png';
            }
            $filename = 'concure-logo.' . $ext;
            $file->move($dir, $filename);
            Log::warning('Master branding logo: GD normalization unavailable, stored as-is.', [
                'extension' => $ext,
            ]);
        } else {
            $filename = 'concure-logo.png';
        }

        $relativePath = 'images/' . $filename;

        DB::table('settings')->updateOrInsert(
            ['clinic_id' => null, 'key' => 'master_branding_logo_path'],
            [
                'value' => $relativePath,
                'type' => 'string',
                'description' => 'Master branding logo path (relative to public/)',
                'updated_at' => now(),
            ]
        );

        return redirect()->route('master.settings')
            ->with('success', __('Branding logo updated successfully.'));
    }

    /**
     * Remove the master branding logo.
     */
    public function deleteBrandingLogo()
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            return redirect()->route('master.settings')
                ->withErrors(['logo' => __('Unauthorized. Only super administrators can update branding.')]);
        }

        $stored = DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'master_branding_logo_path')
            ->value('value');

        if ($stored) {
            $abs = public_path($stored);
            if (file_exists($abs)) {
                @unlink($abs);
            }
        }

        // Clean up any concure-logo.* leftovers regardless.
        foreach ((array) glob(public_path('images') . DIRECTORY_SEPARATOR . 'concure-logo.*') as $oldFile) {
            @unlink($oldFile);
        }

        DB::table('settings')
            ->whereNull('clinic_id')
            ->where('key', 'master_branding_logo_path')
            ->delete();

        return redirect()->route('master.settings')
            ->with('success', __('Branding logo removed.'));
    }

    /**
     * Re-encode an arbitrary uploaded image (PNG/JPG/WebP/GIF) into a clean
     * 8-bit RGBA PNG suitable for DomPDF. Returns true on success.
     */
    private static function normalizeImageToPng(string $sourcePath, string $targetPath): bool
    {
        if (!extension_loaded('gd') || !function_exists('imagepng')) {
            return false;
        }

        $info = @getimagesize($sourcePath);
        if (!$info || empty($info['mime'])) {
            return false;
        }

        $mime = $info['mime'];
        $img = null;

        switch ($mime) {
            case 'image/png':
                $img = @imagecreatefrompng($sourcePath);
                break;
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $img = @imagecreatefromwebp($sourcePath);
                }
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($sourcePath);
                break;
        }

        if (!$img) {
            return false;
        }

        // Flatten onto a transparent canvas to guarantee a vanilla 32-bit PNG.
        $width = imagesx($img);
        $height = imagesy($img);
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
        imagealphablending($canvas, true);
        imagecopy($canvas, $img, 0, 0, 0, 0, $width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $ok = @imagepng($canvas, $targetPath, 6);

        imagedestroy($img);
        imagedestroy($canvas);

        return (bool) $ok;
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

        Log::info('SQL Import request received', [
            'admin' => $user->email,
            'ip' => $request->ip(),
            'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            'clinic_id_input' => $request->input('clinic_id'),
            'has_sql_file' => $request->hasFile('sql_file'),
            'transport' => $request->header('X-Sql-Import-Encoding', $request->hasFile('sql_file') ? 'multipart' : 'unknown'),
            'content_type' => $request->header('Content-Type'),
        ]);

        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        // From this point onward this request no longer needs session writes.
        // Release the PHP session lock before reading/scanning the upload so the
        // browser is not blocked behind concurrent session-backed requests.
        $this->closeSessionLock($request);

        $clinic = Clinic::findOrFail($request->clinic_id);

        try {
            // Allow up to 10 minutes for large SQL imports
            set_time_limit(600);
            ini_set('memory_limit', '512M');

            // Disable query log to save memory during bulk import
            DB::disableQueryLog();

            $payload = $this->extractSqlPayload($request);
            $sql = $this->normalizeImportSql($payload['sql']);
            $originalName = $payload['original_name'];
            $fileSize = $payload['file_size'];
            $transport = $payload['transport'];

            if (empty(trim($sql))) {
                return response()->json([
                    'success' => false,
                    'message' => 'The SQL file is empty or contains only skipped dump wrapper statements.'
                ], 422);
            }

            $blockedStatement = $this->detectBlockedSqlStatement($sql);
            if ($blockedStatement !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL file contains a blocked schema/destructive statement near: ' . $blockedStatement . '. Allowed content is INSERT/UPDATE plus standard dump wrapper statements.'
                ], 422);
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
                'file_name' => $originalName,
                'file_size' => $fileSize,
                'transport' => $transport,
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

        } catch (ValidationException $e) {
            $errors = $e->errors();
            $firstError = $e->getMessage();

            foreach ($errors as $messages) {
                if (!empty($messages[0])) {
                    $firstError = (string) $messages[0];
                    break;
                }
            }

            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors,
            ], 422);
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

    private function extractSqlPayload(Request $request): array
    {
        if ($request->hasFile('sql_file')) {
            $file = $request->file('sql_file');

            if (!$file || !$file->isValid()) {
                throw ValidationException::withMessages([
                    'sql_file' => 'The uploaded SQL file is invalid.',
                ]);
            }

            $originalName = $file->getClientOriginalName() ?: 'upload.sql';
            $this->assertAllowedSqlFilename($originalName);

            $size = (int) ($file->getSize() ?? 0);
            $this->assertSqlSizeWithinLimit($size);

            $sql = file_get_contents($file->getRealPath());
            if ($sql === false) {
                throw new \RuntimeException('Unable to read the uploaded SQL file.');
            }

            return [
                'sql' => $sql,
                'original_name' => $originalName,
                'file_size' => $size,
                'transport' => 'multipart',
            ];
        }

        $encoding = strtolower((string) $request->header('X-Sql-Import-Encoding', ''));
        if ($encoding !== 'gzip') {
            throw ValidationException::withMessages([
                'sql_file' => 'A SQL file is required.',
            ]);
        }

        $compressedPayload = $request->getContent();
        if (!is_string($compressedPayload) || $compressedPayload === '') {
            throw ValidationException::withMessages([
                'sql_file' => 'The SQL payload is empty.',
            ]);
        }

        $sql = $this->decodeGzipSqlPayload($compressedPayload);
        $this->assertSqlSizeWithinLimit(strlen($sql));

        $rawFileName = (string) $request->header('X-Sql-File-Name', 'upload.sql');
        $originalName = urldecode($rawFileName) ?: 'upload.sql';
        $this->assertAllowedSqlFilename($originalName);

        return [
            'sql' => $sql,
            'original_name' => $originalName,
            'file_size' => strlen($sql),
            'transport' => 'gzip-body',
        ];
    }

    private function decodeGzipSqlPayload(string $compressedPayload): string
    {
        if (strlen($compressedPayload) > self::MAX_SQL_BYTES) {
            throw ValidationException::withMessages([
                'sql_file' => 'The compressed SQL payload exceeds the 50MB limit.',
            ]);
        }

        $sql = false;

        if (function_exists('gzdecode')) {
            $sql = @gzdecode($compressedPayload);
        }

        if ($sql === false && function_exists('zlib_decode')) {
            $sql = @zlib_decode($compressedPayload);
        }

        if (!is_string($sql)) {
            throw ValidationException::withMessages([
                'sql_file' => 'The compressed SQL payload could not be decoded.',
            ]);
        }

        return $sql;
    }

    private function normalizeImportSql(string $sql): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $sql);

        $skipPatterns = [
            '/^\s*--[^\n]*(?:\n|$)/m',
            '/^\s*#[^\n]*(?:\n|$)/m',
            '/^\s*\/\*![0-9]{5}\s+SET\b[\s\S]*?\*\/\s*;?\s*$/im',
            '/^\s*SET\s+(?:SQL_MODE|TIME_ZONE|FOREIGN_KEY_CHECKS|UNIQUE_CHECKS|AUTOCOMMIT|SQL_NOTES|NAMES|CHARACTER_SET_CLIENT|CHARACTER_SET_RESULTS|COLLATION_CONNECTION)\b[\s\S]*?;\s*$/im',
            '/^\s*START\s+TRANSACTION\s*;\s*$/im',
            '/^\s*COMMIT\s*;\s*$/im',
            '/^\s*LOCK\s+TABLES\b[\s\S]*?;\s*$/im',
            '/^\s*UNLOCK\s+TABLES\s*;\s*$/im',
            '/^\s*(?:\/\*![0-9]{5}\s+)?ALTER\s+TABLE\b[\s\S]*?\b(?:DISABLE|ENABLE)\s+KEYS\b[\s\S]*?(?:\*\/)?\s*;\s*$/im',
        ];

        foreach ($skipPatterns as $pattern) {
            $normalized = preg_replace($pattern, '', $normalized) ?? $normalized;
        }

        return trim($normalized);
    }

    private function detectBlockedSqlStatement(string $sql): ?string
    {
        $blockedPatterns = [
            '/\bDROP\s+(?:DATABASE|TABLE|VIEW|TRIGGER|FUNCTION|PROCEDURE|EVENT)\b/i',
            '/\bTRUNCATE\s+(?:TABLE\s+)?[`"a-z0-9_]/i',
            '/\bALTER\s+TABLE\b/i',
            '/\bCREATE\s+(?:DATABASE|TABLE|VIEW|TRIGGER|FUNCTION|PROCEDURE|EVENT|USER)\b/i',
            '/\bGRANT\b/i',
            '/\bREVOKE\b/i',
            '/\bRENAME\s+TABLE\b/i',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1] ?? 0;
                return $this->summarizeSqlFragment($sql, $offset);
            }
        }

        return null;
    }

    private function summarizeSqlFragment(string $sql, int $offset): string
    {
        $fragment = substr($sql, max(0, $offset), 140);
        $fragment = preg_replace('/\s+/', ' ', $fragment) ?? $fragment;
        $fragment = trim($fragment);

        if ($fragment === '') {
            return 'unknown statement';
        }

        return strlen($fragment) === 140 ? $fragment . '...' : $fragment;
    }

    private function assertAllowedSqlFilename(string $originalName): void
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $isSql = $extension === 'sql' || preg_match('/\.sql\s*\(\d+\)$/i', $originalName);

        if (!$isSql) {
            throw ValidationException::withMessages([
                'sql_file' => 'Invalid file type. Only .sql files are allowed.',
            ]);
        }
    }

    private function assertSqlSizeWithinLimit(int $bytes): void
    {
        if ($bytes > self::MAX_SQL_BYTES) {
            throw ValidationException::withMessages([
                'sql_file' => 'The SQL file may not be greater than 50MB.',
            ]);
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

