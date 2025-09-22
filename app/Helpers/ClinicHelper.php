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

        // Normalize legacy values like "storage/clinic-logos/..."
        $relative = ltrim(str_replace('storage/', '', $logoPath), '/');

        if (Storage::disk('public')->exists($relative)) {
            return Storage::url($relative);
        }

        // Fallback: check if file is directly in public/
        if (function_exists('public_path') && file_exists(public_path($logoPath))) {
            return asset($logoPath);
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

        $relative = ltrim(str_replace('storage/', '', $logoPath), '/');

        if (Storage::disk('public')->exists($relative)) {
            return public_path('storage/' . $relative);
        }

        // Fallback: if stored directly under public/
        if (function_exists('public_path')) {
            $publicCandidate = public_path($logoPath);
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
}
