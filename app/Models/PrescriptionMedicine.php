<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'quantity',
    ];

    /**
     * Common frequencies
     */
    const FREQUENCIES = [
        'once_daily' => 'Once daily',
        'twice_daily' => 'Twice daily',
        'three_times_daily' => 'Three times daily',
        'four_times_daily' => 'Four times daily',
        'every_4_hours' => 'Every 4 hours',
        'every_6_hours' => 'Every 6 hours',
        'every_8_hours' => 'Every 8 hours',
        'every_12_hours' => 'Every 12 hours',
        'as_needed' => 'As needed',
        'before_meals' => 'Before meals',
        'after_meals' => 'After meals',
        'at_bedtime' => 'At bedtime',
    ];

    /**
     * Common durations
     */
    const DURATIONS = [
        '3_days' => '3 days',
        '5_days' => '5 days',
        '7_days' => '7 days',
        '10_days' => '10 days',
        '14_days' => '14 days',
        '21_days' => '21 days',
        '30_days' => '30 days',
        '60_days' => '60 days',
        '90_days' => '90 days',
        'ongoing' => 'Ongoing',
        'as_needed' => 'As needed',
    ];

    /**
     * Get the prescription that owns this medicine.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the medicine (if selected from database).
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Get the frequency display name.
     */
    public function getFrequencyDisplayAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    /**
     * Get the duration display name.
     */
    public function getDurationDisplayAttribute(): string
    {
        return self::DURATIONS[$this->duration] ?? $this->duration;
    }

    /**
     * Get the medicine name (from database or custom).
     */
    public function getMedicineNameDisplayAttribute(): string
    {
        return $this->medicine ? $this->medicine->full_name : $this->medicine_name;
    }

    /**
     * Get formatted dosage instructions.
     */
    public function getFormattedInstructionsAttribute(): string
    {
        $instructions = [];
        
        if ($this->dosage) {
            $instructions[] = "Dosage: {$this->dosage}";
        }
        
        if ($this->frequency) {
            $instructions[] = "Frequency: {$this->frequency_display}";
        }
        
        if ($this->duration) {
            $instructions[] = "Duration: {$this->duration_display}";
        }
        
        if ($this->quantity) {
            $instructions[] = "Quantity: {$this->quantity}";
        }
        
        if ($this->instructions) {
            $instructions[] = "Instructions: {$this->instructions}";
        }
        
        return implode(' | ', $instructions);
    }

    /**
     * Scope to filter by prescription.
     */
    public function scopeByPrescription($query, int $prescriptionId)
    {
        return $query->where('prescription_id', $prescriptionId);
    }

    /**
     * Scope to filter by medicine.
     */
    public function scopeByMedicine($query, int $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }
}
