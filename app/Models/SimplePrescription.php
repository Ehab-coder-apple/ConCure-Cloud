<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimplePrescription extends Model
{
    use HasFactory;

    protected $table = 'simple_prescriptions';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'clinic_id',
        'prescription_number',
        'diagnosis',
        'notes',
        'prescribed_date',
        'status'
    ];

    protected $casts = [
        'prescribed_date' => 'date',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medicines(): HasMany
    {
        return $this->hasMany(SimplePrescriptionMedicine::class, 'prescription_id');
    }

    // Generate prescription number
    public static function generatePrescriptionNumber(): string
    {
        return 'RX-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    // Scope for clinic
    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
