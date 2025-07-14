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

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
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

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            return public_path('storage/' . str_replace('storage/', '', $logoPath));
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
