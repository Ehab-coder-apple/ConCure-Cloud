<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'surgical_case_id',
        'operation_date',
        'theatre',
        'asa_class',
        'anesthesia_type',
        'preop_assessment',      // structured JSON or rich text (to be refined)
        'operative_note',        // main intra-op note
        'postop_assessment',     // immediate post-op status / plan
        'complications',
        'estimated_blood_loss_ml',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'operation_date' => 'datetime',
        'preop_assessment' => 'array',
        'postop_assessment' => 'array',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function surgicalCase(): BelongsTo
    {
        return $this->belongsTo(SurgicalCase::class, 'surgical_case_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
