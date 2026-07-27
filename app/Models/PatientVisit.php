<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PatientVisit extends Model
{
    use HasFactory;

    protected $table = 'visits';

    protected $fillable = [
        'patient_id',
        'clinic_id',
        'recorded_by',
        'visit_date',
        'visit_type',
        'status',
        'reason_for_visit',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function hpi(): HasOne
    {
        return $this->hasOne(VisitHpi::class, 'visit_id');
    }
}