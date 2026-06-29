<?php

namespace App\Models;

use App\Models\Concerns\AppliesAccessibleClinicScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use App\Services\PatientProfileModuleRegistry;

class Patient extends Model
{
    use HasFactory, AppliesAccessibleClinicScope;

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
        'birth_weight',
        'gestational_age_weeks',
        'bmi',
        'blood_type',
        'allergies',
        'history_of_present_illness',
        'is_pregnant',
        'chronic_illnesses',
        'surgeries_history',
        'diet_history',
        'medical_history',
        'notes',
        'emergency_contact_name',
        'emergency_contact_phone',
        'clinic_id',
        'vaccination_schedule_id',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'bmi' => 'decimal:2',
        'birth_weight' => 'decimal:2',
        'gestational_age_weeks' => 'integer',
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
     * Get the images for the patient.
     */
    public function images(): HasMany
    {
        return $this->hasMany(\App\Models\PatientImage::class);
    }

    /**
     * Get the videos for the patient.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(\App\Models\PatientVideo::class);
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

    public function medicalOverview(): HasOne
    {
        return $this->hasOne(PatientMedicalOverview::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(PatientModule::class);
    }

    public function activeModules(): HasMany
    {
        return $this->modules()->where('is_active', true);
    }

    public function medications(): HasMany
    {
        return $this->hasMany(PatientMedication::class)->latest('started_on');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(PatientVisit::class, 'patient_id')->latest('visit_date');
    }

    /**
     * Get the lab requests for the patient.
     */
    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    /**
     * Get the radiology requests for the patient.
     */
    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(RadiologyRequest::class);
    }

    /**
     * Get the dental charts for the patient.
     */
    public function dentalCharts(): HasMany
    {
        return $this->hasMany(DentalChart::class);
    }

    /**
     * Get the dental treatments for the patient.
     */
    public function dentalTreatments(): HasMany
    {
        return $this->hasMany(DentalTreatment::class);
    }

    /**
     * Get the canal treatments for the patient.
     */
    public function canalTreatments(): HasMany
    {
        return $this->hasMany(CanalTreatment::class);
    }

    /**
     * Get the dental images for the patient.
     */
    public function dentalImages(): HasMany
    {
        return $this->hasMany(DentalImage::class);
    }

    /**
     * Get the orthodontic cases for the patient.
     */
    public function orthodonticCases(): HasMany
    {
        return $this->hasMany(\App\Models\OrthodonticCase::class);
    }

    public function dentalProfile(): HasOne
    {
        return $this->hasOne(PatientDental::class);
    }

    public function entProfile(): HasOne
    {
        return $this->hasOne(PatientEnt::class);
    }

    public function aestheticProfile(): HasOne
    {
        return $this->hasOne(PatientAesthetic::class);
    }

    /**
     * Get ENT records for this patient.
     */
    public function entRecords(): HasMany
    {
        return $this->hasMany(\App\Models\EntRecord::class);
    }

    /**
     * Get audiometry tests for this patient.
     */
    public function audiometryTests(): HasMany
    {
        return $this->hasMany(\App\Models\AudiometryTest::class);
    }

    public function pediatricProfile(): HasOne
    {
        return $this->hasOne(PatientPediatric::class);
    }

    public function nutritionProfile(): HasOne
    {
        return $this->hasOne(PatientNutrition::class);
    }

    /**
     * Get the latest dental chart for the patient.
     */
    public function getLatestDentalChartAttribute(): ?DentalChart
    {
        return $this->dentalCharts()->latest()->first();
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
     * Get the aesthetic patient packages for the patient.
     */
    public function aestheticPatientPackages(): HasMany
    {
        return $this->hasMany(PatientPackage::class);
    }

    /**
     * Get the aesthetic sessions for the patient (both package and direct).
     */
    public function aestheticSessions(): HasMany
    {
        return $this->hasMany(AestheticSession::class, 'patient_id');
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
        // NOTE: Some legacy/imported records may contain invalid dates like "0000-00-00".
        // Accessing $this->date_of_birth would trigger Eloquent's date casting and can throw,
        // causing 500s in views that display age. Use the raw value and parse defensively.
        // Prefer the *current* raw attribute value (without casting). Fallback to original.
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');

        if (empty($rawDob) || $rawDob === '0000-00-00' || $rawDob === '0000-00-00 00:00:00') {
            return 0;
        }

        try {
            return \Illuminate\Support\Carbon::parse($rawDob)->age;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get a DOB value safe for HTML date inputs.
     */
    public function getDateOfBirthForFormAttribute(): ?string
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');

        if (empty($rawDob) || $rawDob === '0000-00-00' || $rawDob === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($rawDob)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get DOB formatted for display, safe against null and legacy '0000-00-00' values.
     */
    public function getDobFormattedAttribute(): ?string
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');

        if (empty($rawDob) || $rawDob === '0000-00-00' || $rawDob === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($rawDob)->format('M d, Y');
        } catch (\Throwable $e) {
            return null;
        }
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
     * Get the latest visit date shown on patient listings.
     */
    public function getLastVisitDateAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        if ($this->relationLoaded('checkups')) {
            $latestLoadedCheckup = $this->getRelation('checkups')
                ->filter(fn ($checkup) => !empty($checkup->checkup_date))
                ->sortByDesc(fn ($checkup) => $checkup->checkup_date instanceof \DateTimeInterface
                    ? $checkup->checkup_date->getTimestamp()
                    : strtotime((string) $checkup->checkup_date))
                ->first();

            if ($latestLoadedCheckup?->checkup_date) {
                return $latestLoadedCheckup->checkup_date;
            }
        }

        $latestCheckupDate = $this->checkups()->latest('checkup_date')->value('checkup_date');

        return $latestCheckupDate ? \Illuminate\Support\Carbon::parse($latestCheckupDate) : null;
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
    public static function generatePatientId(?int $clinicId = null): string
    {
        $clinic = $clinicId ? Clinic::find($clinicId) : null;
        // Use multibyte-safe substring to avoid producing invalid UTF-8 prefixes
        // (e.g., clinics with Arabic names). Invalid UTF-8 here can crash inserts/logging.
        $prefix = $clinic && $clinic->name
            ? Str::upper(Str::substr((string) $clinic->name, 0, 3))
            : 'PAT';

        // If the name starts with whitespace/symbols and yields an empty prefix, fall back.
        $prefix = trim((string) $prefix);
        if ($prefix === '') {
            $prefix = $clinic ? ('CL' . $clinic->id) : 'PAT';
        }

        // Use a database transaction to ensure atomicity
        return \DB::transaction(function () use ($prefix, $clinicId) {
            // Get the highest existing number for this prefix to avoid duplicates
            // Use sharedLock to allow reads but prevent concurrent writes during this transaction
            $lastPatient = self::where('patient_id', 'LIKE', $prefix . '-%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(patient_id, "-", -1) AS UNSIGNED) DESC')
                ->sharedLock()
                ->first();

            $lastNumber = 0;
            if ($lastPatient && $lastPatient->patient_id) {
                // Extract the number after the last dash
                $parts = explode('-', $lastPatient->patient_id);
                $lastPart = end($parts);
                // Remove any non-numeric characters
                $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastPart);
            }

            // Start from the next number after the last one
            $startNumber = max(10000, $lastNumber + 1);

            $maxAttempts = 100;
            $attempt = 0;

            do {
                $attempt++;

                // Generate sequential numbers starting from last + 1
                $number = str_pad((string) ($startNumber + $attempt - 1), 5, '0', STR_PAD_LEFT);
                $patientId = $prefix . '-' . $number;

                // Double-check uniqueness within this transaction
                $exists = self::where('patient_id', $patientId)->exists();

                if (!$exists) {
                    return $patientId;
                }

                if ($attempt >= $maxAttempts) {
                    // Emergency fallback: use microtime for guaranteed uniqueness
                    $uniqueSuffix = str_pad((string) (int) (microtime(true) * 10000) % 100000, 5, '0', STR_PAD_LEFT);
                    $patientId = $prefix . '-' . $uniqueSuffix;

                    \Log::warning('Patient ID generation reached max attempts', [
                        'clinic_id' => $clinicId,
                        'prefix' => $prefix,
                        'final_id' => $patientId,
                        'attempts' => $attempt,
                        'last_number' => $lastNumber,
                    ]);

                    // Final safety check
                    if (self::where('patient_id', $patientId)->exists()) {
                        // Use a completely unique ID with timestamp
                        $patientId = $prefix . '-' . time() . '-' . mt_rand(100, 999);
                    }

                    return $patientId;
                }
            } while (true);
        });
    }

    /**
     * Calculate BMI.
     */
    public static function calculateBMI(float $weight, float $height): ?float
    {
        // Guard against zero/negative height to avoid division by zero
        if ($height <= 0 || $weight <= 0) {
            return null;
        }

        // Height should be in cm, convert to meters
        $heightInMeters = $height / 100;
        $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);

        // Cap at 9999.99 to fit decimal(6,2) column; values above ~100 are medically unrealistic
        // but we store them rather than silently discarding
        return min($bmi, 9999.99);
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

        $targetCalories = $tdee + $dailyCalorieAdjustment; // signed: negative(loss), positive(gain)
        $recommendedWeeklyGoal = $weeklyWeightGoal;

        // Safety limits
        $minCalories = $this->gender === 'male' ? 1500 : 1200;
        if ($targetCalories < $minCalories) {
            $targetCalories = $minCalories;
            // Recalculate safe weekly loss (negative sign)
            $safeWeekly = (($tdee - $minCalories) * 7) / $caloriesPerKg;
            $recommendedWeeklyGoal = -round($safeWeekly, 2);
        }

        // Cap excessive surplus
        $maxSurplus = 500; // 500 kcal/day surplus
        if (($targetCalories - $tdee) > $maxSurplus) {
            $targetCalories = $tdee + $maxSurplus;
            $recommendedWeeklyGoal = round((($maxSurplus * 7) / $caloriesPerKg), 2);
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
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
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

    /**
     * Get the patient's assigned forms.
     */
    public function patientForms()
    {
        return $this->hasMany(\App\Models\PatientForm::class);
    }

    /**
     * Get the patient's growth measurements.
     */
    public function growthMeasurements()
    {
        return $this->hasMany(\App\Models\GrowthMeasurement::class)->orderBy('measurement_date');
    }

    /**
     * Get the vaccination schedule assigned to this patient.
     */
    public function vaccinationSchedule(): BelongsTo
    {
        return $this->belongsTo(VaccinationSchedule::class, 'vaccination_schedule_id');
    }

    /**
     * Get the patient's vaccination records.
     */
    public function vaccinations(): HasMany
    {
        return $this->hasMany(PatientVaccination::class)->orderBy('scheduled_date');
    }

    /**
     * Get the patient's nutrition progress measurements.
     */
    public function nutritionProgressMeasurements(): HasMany
    {
        return $this->hasMany(NutritionProgressMeasurement::class)->orderBy('measurement_date');
    }

    /**
     * Get the patient's nutrition goals.
     */
    public function nutritionGoals(): HasMany
    {
        return $this->hasMany(NutritionGoal::class);
    }

    /**
     * Get the patient's active nutrition goal.
     */
    public function activeNutritionGoal()
    {
        return $this->hasOne(NutritionGoal::class)->where('is_active', true)->latest();
    }

    public function getAllergiesAttribute($value): ?string
    {
        return $this->overviewValue('allergies', $value);
    }

    public function getChronicIllnessesAttribute($value): ?string
    {
        return $this->overviewValue('chronic_diseases', $value);
    }

    public function getSurgeriesHistoryAttribute($value): ?string
    {
        return $this->overviewValue('surgeries', $value);
    }

    public function getMedicalHistoryAttribute($value): ?string
    {
        return $this->overviewValue('medical_history', $value);
    }

    public function getIsPregnantAttribute($value): bool
    {
        $overview = $this->getMedicalOverviewRelation();

        if ($overview?->hasFlag('pregnant')) {
            return true;
        }

        return (bool) $value;
    }

    public function getMedicalFlagsAttribute(): array
    {
        $overview = $this->getMedicalOverviewRelation();
        $flags = $overview?->flags ?? [];

        if (($this->attributes['is_pregnant'] ?? false) && !isset($flags['pregnant'])) {
            $flags['pregnant'] = true;
        }

        return collect($flags)->filter()->all();
    }

    public function getActiveProfileModulesAttribute()
    {
        $activeModuleNames = ($this->relationLoaded('activeModules') ? $this->activeModules : $this->activeModules()->get())
            ->pluck('module_name')
            ->all();
        $activeModuleNames = array_values(array_unique(array_merge(
            PatientProfileModuleRegistry::defaultActiveModulesForPatient($this),
            $activeModuleNames
        )));

        return collect(PatientProfileModuleRegistry::eligibleModulesForPatient($this))
            ->filter(fn (array $module) => in_array($module['key'], $activeModuleNames, true))
            ->values();
    }

    public function getAvailableProfileModulesAttribute()
    {
        $activeModuleNames = ($this->relationLoaded('activeModules') ? $this->activeModules : $this->activeModules()->get())
            ->pluck('module_name')
            ->all();
        $activeModuleNames = array_values(array_unique(array_merge(
            PatientProfileModuleRegistry::defaultActiveModulesForPatient($this),
            $activeModuleNames
        )));

        return collect(PatientProfileModuleRegistry::eligibleModulesForPatient($this))
            ->reject(fn (array $module) => in_array($module['key'], $activeModuleNames, true))
            ->values();
    }

    /**
     * Check if the patient is low birth weight (< 2500g).
     */
    public function getIsLowBirthWeightAttribute(): bool
    {
        return $this->birth_weight !== null && $this->birth_weight < 2500;
    }

    private function getPediatricProfileRelation(): ?PatientPediatric
    {
        if (!class_exists(\App\Models\PatientPediatric::class)) {
            return null;
        }

        if ($this->relationLoaded('pediatricProfile')) {
            return $this->getRelation('pediatricProfile');
        }

        return $this->pediatricProfile()->first();
    }

    public function getBirthWeightAttribute($value)
    {
        return data_get($this->getPediatricProfileRelation(), 'birth_weight') ?? $value;
    }

    public function getGestationalAgeWeeksAttribute($value)
    {
        return data_get($this->getPediatricProfileRelation(), 'gestational_age') ?? $value;
    }

    private function getMedicalOverviewRelation(): ?PatientMedicalOverview
    {
        if (!class_exists(\App\Models\PatientMedicalOverview::class)) {
            return null;
        }

        if ($this->relationLoaded('medicalOverview')) {
            return $this->getRelation('medicalOverview');
        }

        return $this->medicalOverview()->first();
    }

    private function overviewValue(string $overviewColumn, $fallback): ?string
    {
        return data_get($this->getMedicalOverviewRelation(), $overviewColumn) ?? $fallback;
    }

    /**
     * Check if this is a pediatric patient (age <= 20 years).
     */
    public function getIsPediatricAttribute(): bool
    {
        return $this->age <= 20;
    }

    /**
     * Get corrected age in months for preterm infants.
     * Corrected age = Chronological age - (40 - gestational age at birth) weeks
     * Only applied until 2 years (24 months) chronological age.
     */
    public function getCorrectedAgeMonthsAttribute(): ?float
    {
        if (!$this->date_of_birth || !$this->gestational_age_weeks) {
            return null;
        }

        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');
        if (empty($rawDob) || $rawDob === '0000-00-00') {
            return null;
        }

        try {
            $dob = \Carbon\Carbon::parse($rawDob);
        } catch (\Exception $e) {
            return null;
        }

        $chronologicalMonths = $dob->floatDiffInMonths(now());

        // Only correct until 24 months
        if ($chronologicalMonths > 24) {
            return $chronologicalMonths;
        }

        $weeksPreterm = 40 - $this->gestational_age_weeks;
        $correctionMonths = $weeksPreterm * 7 / 30.44; // avg days/month
        return max(0, $chronologicalMonths - $correctionMonths);
    }

    /**
     * Get the appropriate age range key for growth chart selection.
     * Returns: '0-24m', '2-5y', '5-20y'
     */
    public function getGrowthAgeRangeAttribute(): string
    {
        $ageMonths = $this->is_low_birth_weight ? ($this->corrected_age_months ?? ($this->age * 12)) : ($this->age * 12);

        if ($ageMonths <= 24) {
            return '0-24m';
        } elseif ($ageMonths <= 60) {
            return '2-5y';
        }
        return '5-20y';
    }

    /**
     * Get the patient's pediatric prescriptions.
     */
    public function pediatricPrescriptions(): HasMany
    {
        return $this->hasMany(PediatricPrescription::class)->orderByDesc('created_at');
    }

    /**
     * Get the latest weight from checkups, growth measurements, or patient profile.
     * Priority: checkups > growth_measurements > patient.weight
     */
    public function getLatestWeightKgAttribute(): ?float
    {
        // First, check latest checkup
        $latestCheckup = $this->checkups()->whereNotNull('weight')->latest('checkup_date')->first();
        if ($latestCheckup && $latestCheckup->weight) {
            return (float) $latestCheckup->weight;
        }

        // Then, check growth measurements (pediatric)
        $latestGrowth = $this->growthMeasurements()->latest('measurement_date')->first();
        if ($latestGrowth && $latestGrowth->weight_kg) {
            return (float) $latestGrowth->weight_kg;
        }

        // Fallback to patient profile weight
        return $this->weight ? (float) $this->weight : null;
    }

    /**
     * Get the latest height from checkups, growth measurements, or patient profile.
     * Priority: checkups > growth_measurements > patient.height
     */
    public function getLatestHeightAttribute(): ?float
    {
        // First, check latest checkup
        $latestCheckup = $this->checkups()->whereNotNull('height')->latest('checkup_date')->first();
        if ($latestCheckup && $latestCheckup->height) {
            return (float) $latestCheckup->height;
        }

        // Then, check growth measurements (pediatric)
        $latestGrowth = $this->growthMeasurements()->latest('measurement_date')->first();
        if ($latestGrowth && $latestGrowth->height_cm) {
            return (float) $latestGrowth->height_cm;
        }

        // Fallback to patient profile height
        return $this->height ? (float) $this->height : null;
    }

    /**
     * Get patient age in months.
     */
    public function getAgeMonthsAttribute(): int
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');
        if (empty($rawDob) || $rawDob === '0000-00-00') {
            return 0;
        }
        try {
            return (int) \Illuminate\Support\Carbon::parse($rawDob)->diffInMonths(now());
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get patient age in days.
     */
    public function getAgeDaysAttribute(): int
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');
        if (empty($rawDob) || $rawDob === '0000-00-00') {
            return 0;
        }
        try {
            return (int) \Illuminate\Support\Carbon::parse($rawDob)->diffInDays(now());
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get patient age formatted based on pediatric thresholds.
     *
     * Formatting rules:
     * - Neonatal (< 30 days): Display in Days only (e.g., "24 Days")
     * - Infant (1 month to < 12 months): Display in Months and Days (e.g., "8 Months, 12 Days")
     * - Child (1 year+): Display in Years and Months (e.g., "3 Years, 5 Months")
     */
    public function getAgeFormattedAttribute(): ?string
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');

        if (empty($rawDob) || $rawDob === '0000-00-00' || $rawDob === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $dob = \Illuminate\Support\Carbon::parse($rawDob);
            $now = now();

            $totalDays = $dob->diffInDays($now);

            // Neonatal (Less than 30 days): Display in Days only
            if ($totalDays < 30) {
                return $totalDays . ' ' . __($totalDays == 1 ? 'Day' : 'Days');
            }

            $totalMonths = $dob->diffInMonths($now);

            // Infant (1 month to less than 12 months): Display in Months and Days
            if ($totalMonths < 12) {
                // Calculate remaining days after full months
                $monthsAgo = $dob->copy()->addMonths($totalMonths);
                $remainingDays = $monthsAgo->diffInDays($now);

                if ($remainingDays > 0) {
                    return $totalMonths . ' ' . __($totalMonths == 1 ? 'Month' : 'Months') . ', ' .
                           $remainingDays . ' ' . __($remainingDays == 1 ? 'Day' : 'Days');
                } else {
                    return $totalMonths . ' ' . __($totalMonths == 1 ? 'Month' : 'Months');
                }
            }

            // Child (1 year and older): Display in Years and Months
            $years = $dob->age;
            $months = $totalMonths % 12;

            if ($months > 0) {
                return $years . ' ' . __($years == 1 ? 'Year' : 'Years') . ', ' .
                       $months . ' ' . __($months == 1 ? 'Month' : 'Months');
            } else {
                return $years . ' ' . __($years == 1 ? 'Year' : 'Years');
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get pediatric-specific age display with capitalization.
     * Alias for age_formatted for consistency.
     */
    public function getPediatricAgeAttribute(): ?string
    {
        return $this->age_formatted;
    }

    /**
     * Get detailed age breakdown for tooltips/detailed display.
     * Returns array with years, months, days breakdown.
     */
    public function getAgeBreakdownAttribute(): ?array
    {
        $rawDob = $this->getAttributes()['date_of_birth'] ?? $this->getRawOriginal('date_of_birth');

        if (empty($rawDob) || $rawDob === '0000-00-00' || $rawDob === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            $dob = \Illuminate\Support\Carbon::parse($rawDob);
            $now = now();

            return [
                'total_days' => $dob->diffInDays($now),
                'total_months' => $dob->diffInMonths($now),
                'years' => $dob->age,
                'months' => $dob->diffInMonths($now) % 12,
                'days_in_current_month' => $dob->copy()->addMonths($dob->diffInMonths($now))->diffInDays($now),
                'formatted' => $this->age_formatted,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
