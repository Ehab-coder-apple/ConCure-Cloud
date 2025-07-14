<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_number',
        'patient_id',
        'doctor_id',
        'title',
        'description',
        'goal',
        'goal_description',
        'duration_days',
        'target_calories',
        'target_protein',
        'target_carbs',
        'target_fat',
        'initial_weight',
        'target_weight',
        'current_weight',
        'initial_height',
        'initial_bmi',
        'current_bmi',
        'target_bmi',
        'weight_goal_kg',
        'weekly_weight_goal',
        'instructions',
        'restrictions',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_calories' => 'decimal:2',
        'target_protein' => 'decimal:2',
        'target_carbs' => 'decimal:2',
        'target_fat' => 'decimal:2',
        'initial_weight' => 'decimal:2',
        'target_weight' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'initial_height' => 'decimal:2',
        'initial_bmi' => 'decimal:2',
        'current_bmi' => 'decimal:2',
        'target_bmi' => 'decimal:2',
        'weight_goal_kg' => 'decimal:2',
        'weekly_weight_goal' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Diet plan goals
     */
    const GOALS = [
        'weight_loss' => 'Weight Loss',
        'weight_gain' => 'Weight Gain',
        'maintenance' => 'Weight Maintenance',
        'muscle_gain' => 'Muscle Gain',
        'health_improvement' => 'Health Improvement',
        'other' => 'Other',
    ];

    /**
     * Diet plan statuses
     */
    const STATUSES = [
        'active' => 'Active',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dietPlan) {
            if (!$dietPlan->plan_number) {
                $dietPlan->plan_number = self::generatePlanNumber();
            }
            
            if (!$dietPlan->start_date) {
                $dietPlan->start_date = now()->toDateString();
            }
            
            // Calculate end date if duration is provided
            if ($dietPlan->duration_days && !$dietPlan->end_date) {
                $dietPlan->end_date = now()->addDays($dietPlan->duration_days)->toDateString();
            }
        });
    }

    /**
     * Get the patient that owns the diet plan.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor who created this diet plan.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the meals for this diet plan.
     */
    public function meals(): HasMany
    {
        return $this->hasMany(DietPlanMeal::class);
    }

    /**
     * Get the weight records for this diet plan.
     */
    public function weightRecords(): HasMany
    {
        return $this->hasMany(DietPlanWeightRecord::class);
    }

    /**
     * Get the goal display name.
     */
    public function getGoalDisplayAttribute(): string
    {
        return self::GOALS[$this->goal] ?? $this->goal;
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'badge bg-success',
            'completed' => 'badge bg-primary',
            'cancelled' => 'badge bg-danger',
            default => 'badge bg-secondary',
        };
    }

    /**
     * Get the duration in days.
     */
    public function getDurationInDaysAttribute(): int
    {
        if ($this->duration_days) {
            return $this->duration_days;
        }
        
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date);
        }
        
        return 0;
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->start_date || !$this->end_date || $this->status !== 'active') {
            return 0;
        }
        
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(now());
        
        if ($totalDays <= 0) {
            return 100;
        }
        
        return min(100, max(0, ($elapsedDays / $totalDays) * 100));
    }

    /**
     * Generate a unique plan number.
     */
    public static function generatePlanNumber(): string
    {
        do {
            $number = 'DIET-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (self::where('plan_number', $number)->exists());

        return $number;
    }

    /**
     * Add meal to diet plan.
     */
    public function addMeal(array $mealData): DietPlanMeal
    {
        return $this->meals()->create($mealData);
    }

    /**
     * Check if diet plan can be modified.
     */
    public function canBeModified(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark diet plan as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark diet plan as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Check if diet plan is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && 
               $this->end_date->isPast() && 
               $this->status === 'active';
    }

    /**
     * Get meals grouped by day.
     */
    public function getMealsByDay(): array
    {
        $mealsByDay = [];

        foreach ($this->meals as $meal) {
            $day = $meal->day_number;
            if (!isset($mealsByDay[$day])) {
                $mealsByDay[$day] = [];
            }
            $mealsByDay[$day][] = $meal;
        }

        ksort($mealsByDay);
        return $mealsByDay;
    }

    /**
     * Get the latest weight record.
     */
    public function getLatestWeightRecordAttribute()
    {
        return $this->weightRecords()->latest('record_date')->first();
    }

    /**
     * Get total weight change from start.
     */
    public function getTotalWeightChangeAttribute(): ?float
    {
        if (!$this->initial_weight || !$this->current_weight) {
            return null;
        }

        return $this->current_weight - $this->initial_weight;
    }

    /**
     * Get weight change percentage.
     */
    public function getWeightChangePercentageAttribute(): ?float
    {
        if (!$this->initial_weight || !$this->total_weight_change) {
            return null;
        }

        return ($this->total_weight_change / $this->initial_weight) * 100;
    }

    /**
     * Get progress towards weight goal.
     */
    public function getWeightProgressPercentageAttribute(): ?float
    {
        if (!$this->initial_weight || !$this->target_weight || !$this->current_weight) {
            return null;
        }

        $totalGoal = abs($this->target_weight - $this->initial_weight);
        $currentProgress = abs($this->current_weight - $this->initial_weight);

        if ($totalGoal == 0) {
            return 100;
        }

        return min(100, ($currentProgress / $totalGoal) * 100);
    }

    /**
     * Get BMI change from initial.
     */
    public function getBmiChangeAttribute(): ?float
    {
        if (!$this->initial_bmi || !$this->current_bmi) {
            return null;
        }

        return $this->current_bmi - $this->initial_bmi;
    }

    /**
     * Check if weight goal is achieved.
     */
    public function isWeightGoalAchieved(): bool
    {
        if (!$this->target_weight || !$this->current_weight) {
            return false;
        }

        $tolerance = 0.5; // 0.5kg tolerance

        return abs($this->current_weight - $this->target_weight) <= $tolerance;
    }

    /**
     * Get expected weight loss/gain per week.
     */
    public function getExpectedWeeklyProgressAttribute(): ?float
    {
        if (!$this->weekly_weight_goal) {
            return $this->weekly_weight_goal;
        }

        // Default safe weight loss/gain rates
        switch ($this->goal) {
            case 'weight_loss':
                return -0.5; // 0.5kg loss per week
            case 'weight_gain':
                return 0.3; // 0.3kg gain per week
            case 'maintenance':
                return 0; // No change
            default:
                return null;
        }
    }

    /**
     * Add weight record to this diet plan.
     */
    public function addWeightRecord(array $recordData): DietPlanWeightRecord
    {
        $recordData['diet_plan_id'] = $this->id;
        $recordData['patient_id'] = $this->patient_id;

        return DietPlanWeightRecord::create($recordData);
    }

    /**
     * Initialize weight tracking from patient data.
     */
    public function initializeWeightTracking(): void
    {
        $patient = $this->patient;

        if ($patient) {
            $updateData = [];

            // Set initial weight and height from patient
            if ($patient->weight && !$this->initial_weight) {
                $updateData['initial_weight'] = $patient->weight;
                $updateData['current_weight'] = $patient->weight;
            }

            if ($patient->height && !$this->initial_height) {
                $updateData['initial_height'] = $patient->height;
            }

            if ($patient->bmi && !$this->initial_bmi) {
                $updateData['initial_bmi'] = $patient->bmi;
                $updateData['current_bmi'] = $patient->bmi;
            }

            // Calculate target BMI if not set
            if ($this->initial_height && $this->target_weight && !$this->target_bmi) {
                $updateData['target_bmi'] = Patient::calculateBMI($this->target_weight, $this->initial_height);
            }

            if (!empty($updateData)) {
                $this->update($updateData);
            }
        }
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by doctor.
     */
    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by goal.
     */
    public function scopeByGoal($query, string $goal)
    {
        return $query->where('goal', $goal);
    }

    /**
     * Scope to filter active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter expired plans.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', now());
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter recent plans.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('start_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by start date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('start_date', 'desc');
    }
}
