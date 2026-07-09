<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurgicalCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'case_number',
        'status',        // planned, in_progress, completed, cancelled
        'primary_surgeon_id',
        'assistant_surgeon_id',
        'anesthetist_id',
        'diagnosis',
        'planned_procedure',
        'scheduled_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function primarySurgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_surgeon_id');
    }

    public function anesthetist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anesthetist_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(SurgicalOperation::class, 'surgical_case_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(SurgicalVisit::class, 'surgical_case_id');
    }
}
