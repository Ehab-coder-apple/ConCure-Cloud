<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitHpi extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'chief_complaint',
        'hpi_summary',
        'associated_symptoms',
        'clinical_notes',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(PatientVisit::class, 'visit_id');
    }
}