<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'whatsapp_phone',
        'email',
        'address',
        'job',
        'education',
        'height',
        'weight',
        'bmi',
        'allergies',
        'is_pregnant',
        'chronic_illnesses',
        'surgeries_history',
        'diet_history',
        'notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'clinic_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'bmi' => 'decimal:2',
        'is_pregnant' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (!$patient->patient_id) {
                $patient->patient_id = self::generatePatientId($patient->clinic_id);
            }
            
            // Calculate BMI if height and weight are provided
            if ($patient->height && $patient->weight) {
                $patient->bmi = self::calculateBMI($patient->weight, $patient->height);
            }
        });

        static::updating(function ($patient) {
            // Recalculate BMI if height or weight changed
            if ($patient->isDirty(['height', 'weight']) && $patient->height && $patient->weight) {
                $patient->bmi = self::calculateBMI($patient->weight, $patient->height);
            }
        });
    }

    /**
     * Get the clinic that owns the patient.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this patient.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the checkups for the patient.
     */
    public function checkups(): HasMany
    {
        return $this->hasMany(PatientCheckup::class);
    }

    /**
     * Get the files for the patient.
     */
    public function files(): HasMany
    {
        return $this->hasMany(PatientFile::class);
    }

    /**
     * Get the prescriptions for the patient.
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get the simple prescriptions for the patient.
     */
    public function simplePrescriptions(): HasMany
    {
        return $this->hasMany(SimplePrescription::class);
    }

    /**
     * Get the lab requests for the patient.
     */
    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    /**
     * Get the diet plans for the patient.
     */
    public function dietPlans(): HasMany
    {
        return $this->hasMany(DietPlan::class);
    }

    /**
     * Get the invoices for the patient.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the appointments for the patient.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the communication logs for the patient.
     */
    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class);
    }

    /**
     * Get patient's full name.
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get patient's age.
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? $this->date_of_birth->age : 0;
    }

    /**
     * Get BMI category.
     */
    public function getBmiCategoryAttribute(): string
    {
        if (!$this->bmi) {
            return 'Unknown';
        }

        if ($this->bmi < 18.5) {
            return 'Underweight';
        } elseif ($this->bmi < 25) {
            return 'Normal weight';
        } elseif ($this->bmi < 30) {
            return 'Overweight';
        } else {
            return 'Obese';
        }
    }

    /**
     * Get the latest checkup.
     */
    public function getLatestCheckupAttribute()
    {
        return $this->checkups()->latest('checkup_date')->first();
    }

    /**
     * Get the latest weight from checkups.
     */
    public function getLatestWeightAttribute(): ?float
    {
        $latestCheckup = $this->checkups()
                             ->whereNotNull('weight')
                             ->latest('checkup_date')
                             ->first();
        
        return $latestCheckup ? $latestCheckup->weight : $this->weight;
    }

    /**
     * Generate a unique patient ID.
     */
    public static function generatePatientId(int $clinicId): string
    {
        $clinic = Clinic::find($clinicId);
        $prefix = $clinic ? strtoupper(substr($clinic->name, 0, 3)) : 'PAT';
        
        do {
            $number = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $patientId = $prefix . '-' . $number;
        } while (self::where('patient_id', $patientId)->exists());

        return $patientId;
    }

    /**
     * Calculate BMI.
     */
    public static function calculateBMI(float $weight, float $height): float
    {
        // Height should be in cm, convert to meters
        $heightInMeters = $height / 100;
        return round($weight / ($heightInMeters * $heightInMeters), 2);
    }

    /**
     * Scope to filter active patients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to search patients.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('patient_id', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by gender.
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to filter by age range.
     */
    public function scopeByAgeRange($query, int $minAge, int $maxAge)
    {
        $minDate = now()->subYears($maxAge)->startOfYear();
        $maxDate = now()->subYears($minAge)->endOfYear();
        
        return $query->whereBetween('date_of_birth', [$minDate, $maxDate]);
    }
}
