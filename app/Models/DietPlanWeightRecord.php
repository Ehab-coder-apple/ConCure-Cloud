<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietPlanWeightRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_plan_id',
        'patient_id',
        'weight',
        'height',
        'bmi',
        'weight_change',
        'weight_change_percentage',
        'notes',
        'measurements',
        'record_date',
        'recorded_by',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'weight_change' => 'decimal:2',
        'weight_change_percentage' => 'decimal:2',
        'record_date' => 'date',
        'measurements' => 'json',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($record) {
            // Calculate BMI if height and weight are provided
            if ($record->height && $record->weight) {
                $record->bmi = Patient::calculateBMI($record->weight, $record->height);
            }

            // Calculate weight change from previous record
            $previousRecord = self::where('diet_plan_id', $record->diet_plan_id)
                                 ->where('record_date', '<', $record->record_date)
                                 ->orderBy('record_date', 'desc')
                                 ->first();

            if ($previousRecord) {
                $record->weight_change = $record->weight - $previousRecord->weight;
                if ($previousRecord->weight > 0) {
                    $record->weight_change_percentage = ($record->weight_change / $previousRecord->weight) * 100;
                }
            } else {
                // First record - compare with diet plan initial weight
                $dietPlan = DietPlan::find($record->diet_plan_id);
                if ($dietPlan && $dietPlan->initial_weight) {
                    $record->weight_change = $record->weight - $dietPlan->initial_weight;
                    if ($dietPlan->initial_weight > 0) {
                        $record->weight_change_percentage = ($record->weight_change / $dietPlan->initial_weight) * 100;
                    }
                }
            }

            // Set record date if not provided
            if (!$record->record_date) {
                $record->record_date = now()->toDateString();
            }
        });

        static::created(function ($record) {
            // Update diet plan current weight and BMI
            $dietPlan = $record->dietPlan;
            if ($dietPlan) {
                $dietPlan->update([
                    'current_weight' => $record->weight,
                    'current_bmi' => $record->bmi,
                ]);
            }
        });
    }

    /**
     * Get the diet plan that owns this weight record.
     */
    public function dietPlan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class);
    }

    /**
     * Get the patient that owns this weight record.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded this weight.
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get weight change status (gained/lost/maintained).
     */
    public function getWeightChangeStatusAttribute(): string
    {
        if (!$this->weight_change) {
            return 'No Change';
        }

        if ($this->weight_change > 0) {
            return 'Weight Gain';
        } elseif ($this->weight_change < 0) {
            return 'Weight Loss';
        } else {
            return 'Maintained';
        }
    }

    /**
     * Get weight change badge class.
     */
    public function getWeightChangeBadgeClassAttribute(): string
    {
        if (!$this->weight_change) {
            return 'badge bg-secondary';
        }

        $dietPlan = $this->dietPlan;
        if (!$dietPlan) {
            return 'badge bg-secondary';
        }

        // Determine if weight change is in the right direction based on goal
        $isPositiveChange = false;

        switch ($dietPlan->goal) {
            case 'weight_loss':
                $isPositiveChange = $this->weight_change < 0;
                break;
            case 'weight_gain':
                $isPositiveChange = $this->weight_change > 0;
                break;
            case 'maintenance':
                $isPositiveChange = abs($this->weight_change) <= 0.5; // Within 0.5kg is good
                break;
            default:
                $isPositiveChange = true; // Neutral for other goals
        }

        return $isPositiveChange ? 'badge bg-success' : 'badge bg-warning';
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
     * Get progress towards goal.
     */
    public function getProgressTowardsGoalAttribute(): ?float
    {
        $dietPlan = $this->dietPlan;
        if (!$dietPlan || !$dietPlan->initial_weight || !$dietPlan->target_weight) {
            return null;
        }

        $totalGoal = abs($dietPlan->target_weight - $dietPlan->initial_weight);
        $currentProgress = abs($this->weight - $dietPlan->initial_weight);

        if ($totalGoal == 0) {
            return 100;
        }

        return min(100, ($currentProgress / $totalGoal) * 100);
    }

    /**
     * Scope to filter by diet plan.
     */
    public function scopeByDietPlan($query, int $dietPlanId)
    {
        return $query->where('diet_plan_id', $dietPlanId);
    }

    /**
     * Scope to filter by patient.
     */
    public function scopeByPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('record_date', [$startDate, $endDate]);
    }

    /**
     * Scope to order by record date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('record_date', 'desc');
    }

    /**
     * Scope to order by record date ascending.
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('record_date', 'asc');
    }
}
