<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimplePrescriptionMedicine extends Model
{
    use HasFactory;

    protected $table = 'simple_prescription_medicines';

    protected $fillable = [
        'prescription_id',
        'medicine_name',
        'dosage',
        'frequency',
        'duration',
        'instructions'
    ];

    // Relationships
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(SimplePrescription::class, 'prescription_id');
    }
}
