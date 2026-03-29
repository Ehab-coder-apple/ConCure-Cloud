<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToothCanal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tooth_number',
        'canal_name',
        'canal_code',
        'tooth_type',
        'arch',
        'display_order',
        'is_common',
    ];

    protected $casts = [
        'is_common' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Tooth types
     */
    const TOOTH_TYPES = [
        'incisor' => 'Incisor',
        'canine' => 'Canine',
        'premolar' => 'Premolar',
        'molar' => 'Molar',
    ];

    /**
     * Arch types
     */
    const ARCHES = [
        'upper' => 'Upper (Maxillary)',
        'lower' => 'Lower (Mandibular)',
    ];

    /**
     * Get standard canals for a specific tooth number.
     */
    public static function getForTooth(string $toothNumber): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('tooth_number', $toothNumber)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get standard canals for a tooth type and arch.
     */
    public static function getForToothType(string $toothType, string $arch): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('tooth_type', $toothType)
            ->where('arch', $arch)
            ->orderBy('tooth_number')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get common canals only (exclude rare variants like MB2).
     */
    public static function getCommonForTooth(string $toothNumber): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('tooth_number', $toothNumber)
            ->where('is_common', true)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Determine tooth type from FDI tooth number.
     */
    public static function getToothType(string $toothNumber): string
    {
        $num = (int) substr($toothNumber, -1);

        if ($num >= 1 && $num <= 2) return 'incisor';
        if ($num === 3) return 'canine';
        if ($num >= 4 && $num <= 5) return 'premolar';
        if ($num >= 6 && $num <= 8) return 'molar';

        return 'unknown';
    }

    /**
     * Determine arch from FDI tooth number.
     */
    public static function getArch(string $toothNumber): string
    {
        $quadrant = (int) substr($toothNumber, 0, 1);
        return ($quadrant <= 2) ? 'upper' : 'lower';
    }
}

