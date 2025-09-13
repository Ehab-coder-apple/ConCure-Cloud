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
     * Calculate BMR (Basal Metabolic Rate) using Mifflin-St Jeor Equation.
     */
    public function calculateBMR(): ?float
    {
        if (!$this->weight || !$this->height || !$this->date_of_birth || !$this->gender) {
            return null;
        }

        $age = $this->age;
        $weight = $this->weight; // in kg
        $height = $this->height; // in cm

        // Mifflin-St Jeor Equation
        if ($this->gender === 'male') {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
        } else {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
        }

        return round($bmr, 0);
    }

    /**
     * Calculate TDEE (Total Daily Energy Expenditure) based on activity level.
     */
    public function calculateTDEE(string $activityLevel = 'sedentary'): ?float
    {
        $bmr = $this->calculateBMR();
        if (!$bmr) {
            return null;
        }

        // Activity multipliers
        $multipliers = [
            'sedentary' => 1.2,      // Little/no exercise
            'light' => 1.375,        // Light exercise 1-3 days/week
            'moderate' => 1.55,      // Moderate exercise 3-5 days/week
            'active' => 1.725,       // Hard exercise 6-7 days/week
            'very_active' => 1.9     // Very hard exercise, physical job
        ];

        $multiplier = $multipliers[$activityLevel] ?? 1.2;
        return round($bmr * $multiplier, 0);
    }

    /**
     * Calculate target calories for weight loss/gain goal.
     */
    public function calculateTargetCalories(string $goal, float $weeklyWeightGoal = 0.5, string $activityLevel = 'sedentary'): ?array
    {
        $tdee = $this->calculateTDEE($activityLevel);
        if (!$tdee) {
            return null;
        }

        // 1 kg of fat = approximately 7700 calories
        $caloriesPerKg = 7700;
        $dailyCalorieAdjustment = ($weeklyWeightGoal * $caloriesPerKg) / 7;

        $targetCalories = $tdee;
        $recommendedWeeklyGoal = $weeklyWeightGoal;

        switch ($goal) {
            case 'weight_loss':
                // For weight loss, create calorie deficit
                $targetCalories = $tdee - abs($dailyCalorieAdjustment);

                // Safety limits: minimum 1200 calories for women, 1500 for men
                $minCalories = $this->gender === 'male' ? 1500 : 1200;
                if ($targetCalories < $minCalories) {
                    $targetCalories = $minCalories;
                    // Recalculate safe weekly weight loss
                    $recommendedWeeklyGoal = (($tdee - $minCalories) * 7) / $caloriesPerKg;
                }
                break;

            case 'weight_gain':
                // For weight gain, create calorie surplus
                $targetCalories = $tdee + abs($dailyCalorieAdjustment);

                // Safety limit: maximum reasonable surplus
                $maxSurplus = 500; // 500 calories surplus per day
                if (($targetCalories - $tdee) > $maxSurplus) {
                    $targetCalories = $tdee + $maxSurplus;
                    $recommendedWeeklyGoal = ($maxSurplus * 7) / $caloriesPerKg;
                }
                break;

            case 'maintenance':
                // For maintenance, use TDEE
                $targetCalories = $tdee;
                $recommendedWeeklyGoal = 0;
                break;
        }

        return [
            'target_calories' => round($targetCalories, 0),
            'bmr' => $this->calculateBMR(),
            'tdee' => $tdee,
            'recommended_weekly_goal' => round($recommendedWeeklyGoal, 2),
            'daily_calorie_adjustment' => round($dailyCalorieAdjustment, 0),
            'activity_level' => $activityLevel
        ];
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
    public function scopeByClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            // If no clinic ID provided, return empty result set for security
            return $query->whereRaw('1 = 0');
        }

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

    /**
     * Get the patient's vital signs assignments.
     */
    public function vitalSignsAssignments()
    {
        return $this->hasMany(\App\Models\PatientVitalSignsAssignment::class);
    }

    /**
     * Get the patient's active vital signs assignments.
     */
    public function activeVitalSignsAssignments()
    {
        return $this->hasMany(\App\Models\PatientVitalSignsAssignment::class)->where('is_active', true);
    }

    /**
     * Get the patient's assigned custom vital signs.
     */
    public function getAssignedCustomVitalSignsAttribute()
    {
        return \App\Models\PatientVitalSignsAssignment::getPatientActiveVitalSigns($this);
    }

    /**
     * Check if patient has any assigned custom vital signs.
     */
    public function hasAssignedVitalSigns(): bool
    {
        return $this->activeVitalSignsAssignments()->exists();
    }

    /**
     * Get patient's medical conditions from vital signs assignments.
     */
    public function getMedicalConditionsAttribute(): array
    {
        return $this->activeVitalSignsAssignments()
                   ->whereNotNull('medical_condition')
                   ->distinct('medical_condition')
                   ->pluck('medical_condition')
                   ->toArray();
    }

    /**
     * Get the patient's checkup template assignments.
     */
    public function checkupTemplateAssignments()
    {
        return $this->hasMany(\App\Models\PatientCheckupTemplateAssignment::class);
    }

    /**
     * Get the patient's active checkup template assignments.
     */
    public function activeCheckupTemplateAssignments()
    {
        return $this->hasMany(\App\Models\PatientCheckupTemplateAssignment::class)->where('is_active', true);
    }

    /**
     * Get the patient's assigned checkup templates.
     */
    public function getAssignedCheckupTemplatesAttribute()
    {
        return \App\Models\PatientCheckupTemplateAssignment::getPatientActiveTemplates($this);
    }

    /**
     * Check if patient has any assigned checkup templates.
     */
    public function hasAssignedCheckupTemplates(): bool
    {
        return $this->activeCheckupTemplateAssignments()->exists();
    }

    /**
     * Get recommended checkup templates for this patient.
     */
    public function getRecommendedCheckupTemplatesAttribute()
    {
        return \App\Models\PatientCheckupTemplateAssignment::getRecommendedTemplates($this);
    }
}
