<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'default_language',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function vaccinationSchedules(): HasMany
    {
        return $this->hasMany(VaccinationSchedule::class);
    }

    public function clinics(): HasMany
    {
        return $this->hasMany(Clinic::class);
    }

    /**
     * Get the country flag emoji from ISO code (e.g. IQ → 🇮🇶).
     */
    public function getFlagEmojiAttribute(): string
    {
        $code = strtoupper(substr($this->iso_code, 0, 2));
        if (strlen($code) !== 2) {
            return '🏳';
        }
        // Convert each letter to regional indicator symbol
        $flag = mb_chr(0x1F1E6 + ord($code[0]) - ord('A'))
              . mb_chr(0x1F1E6 + ord($code[1]) - ord('A'));
        return $flag;
    }

    /**
     * Get the default active schedule for this country.
     */
    public function getDefaultScheduleAttribute(): ?VaccinationSchedule
    {
        return $this->vaccinationSchedules()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}

