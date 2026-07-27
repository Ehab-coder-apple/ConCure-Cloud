<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'recorded_by',
        'medication_name',
        'dosage',
        'frequency',
        'route',
        'indication',
        'status',
        'started_on',
        'ended_on',
        'notes',
    ];

    protected $casts = [
        'started_on' => 'date',
        'ended_on' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }
}