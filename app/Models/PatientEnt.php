<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientEnt extends Model
{
    use HasFactory;

    protected $table = 'patient_ent';

    protected $fillable = [
        'patient_id',
        'hearing_issues',
        'nasal_issues',
        'throat_issues',
        'dizziness',
        'notes',
    ];

    protected $casts = [
        'dizziness' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getIssueCountAttribute(): int
    {
        return collect([
            $this->hearing_issues,
            $this->nasal_issues,
            $this->throat_issues,
        ])->filter(fn ($value) => filled($value))->count();
    }
}