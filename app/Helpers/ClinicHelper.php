<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClinicHelper
{
    /**
     * Get clinic logo URL for a specific clinic
     */
    public static function getClinicLogo($clinicId)
    {
        if (!$clinicId) {
            return null;
        }

        $logoPath = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'clinic_logo')
            ->value('value');

        if (!$logoPath) {
            return null;
        }

        // If a full URL was stored
        if (preg_match('#^https?://#i', $logoPath)) {
            return $logoPath;
        }

        // Normalize legacy values like "storage/clinic-logos/..." or "public/clinic-logos/..."
        $relative = ltrim(str_replace(['storage/','public/'], '', $logoPath), '/');

        $exists = Storage::disk('public')->exists($relative)
            || file_exists(storage_path('app/public/' . $relative))
            || (function_exists('public_path') && file_exists(public_path($relative)));

        if ($exists) {
            return route('clinic.logo', ['clinic' => $clinicId]);
        }

        return null;
    }

    /**
     * Get clinic logo path for PDF generation (absolute path)
     */
    public static function getClinicLogoPdfPath($clinicId)
    {
        if (!$clinicId) {
            return null;
        }

        $logoPath = DB::table('settings')
            ->where('clinic_id', $clinicId)
            ->where('key', 'clinic_logo')
            ->value('value');

        if (!$logoPath) {
            return null;
        }

        $relative = ltrim(str_replace(['storage/','public/'], '', $logoPath), '/');

        if (Storage::disk('public')->exists($relative)) {
            // Use absolute storage path to avoid relying on the public/storage symlink (better for PDF engines)
            return storage_path('app/public/' . $relative);
        }

        // Fallback: if stored directly under public/
        if (function_exists('public_path')) {
            $publicCandidate = public_path($relative);
            if (file_exists($publicCandidate)) {
                return $publicCandidate;
            }
        }

        return null;
    }

    /**
     * Get clinic information for headers
     */
    public static function getClinicInfo($clinicId)
    {
        if (!$clinicId) {
            return [
                'name' => 'ConCure Clinic',
                'logo' => null,
                'logo_pdf_path' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
            ];
        }

        $clinic = DB::table('clinics')->where('id', $clinicId)->first();

        return [
            'name' => $clinic->name ?? 'ConCure Clinic',
            'logo' => self::getClinicLogo($clinicId),
            'logo_pdf_path' => self::getClinicLogoPdfPath($clinicId),
            'address' => $clinic->address ?? null,
            'phone' => $clinic->phone ?? null,
            'email' => $clinic->email ?? null,
        ];
    }

    /**
     * Resolve the best logo source for PDFs.
     * Prefers embedding as a base64 data URI (most reliable for DomPDF),
     * and falls back to a public URL if no local file is available.
     */
    public static function getClinicLogoPdfSrc($clinicId)
    {
        // Try local absolute path first
        $path = self::getClinicLogoPdfPath($clinicId);
        if ($path && file_exists($path) && is_readable($path)) {
            // Detect mime type
            $mime = null;
            if (function_exists('mime_content_type')) {
                $mime = @mime_content_type($path) ?: null;
            }
            if (!$mime) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $map = [
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    'bmp' => 'image/bmp',
                ];
                $mime = $map[$ext] ?? 'image/png';
            }

            try {
                $data = @file_get_contents($path);
                if ($data !== false && strlen($data) > 0) {
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            } catch (\Throwable $e) {
                // Ignore and fall back to URL
            }
        }

        // Fallback to stored HTTPS/logo route or external URL
        return self::getClinicLogo($clinicId);
    }

}
