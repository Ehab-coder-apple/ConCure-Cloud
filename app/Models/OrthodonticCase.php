<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrthodonticCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_number',
        'patient_id',
        'clinic_id',
        'doctor_id',
        'treatment_type',
        'diagnosis',
        'malocclusion_class',
        'treatment_objectives',
        'start_date',
        'estimated_duration_months',
        'estimated_completion_date',
        'actual_completion_date',
        'current_phase',
        'status',
        'total_cost',
        'currency',
        'paid_amount',
        'balance',
        'payment_plan',
        'appliances',
        'notes',
        'created_by',
        'updated_by',
        // Clinical Assessment Fields
        'skeletal_class',
        'overjet',
        'overbite',
        'midline',
        'crowding',
        'crossbite',
        'open_bite',
        // Visual Tooth Chart
        'tooth_states',
    ];

    protected $casts = [
        'start_date' => 'date',
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'total_cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'appliances' => 'array',
        'overjet' => 'decimal:2',
        'overbite' => 'decimal:2',
        'open_bite' => 'decimal:2',
        'tooth_states' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const TREATMENT_TYPES = [
        'metal_braces' => 'Metal Braces',
        'ceramic_braces' => 'Ceramic Braces',
        'clear_aligners' => 'Clear Aligners (e.g., Invisalign)',
        'lingual_braces' => 'Lingual Braces',
        'self_ligating' => 'Self-Ligating Braces',
        'other' => 'Other',
    ];

    const MALOCCLUSION_CLASSES = [
        'class_1' => 'Class I',
        'class_2_div_1' => 'Class II Division 1',
        'class_2_div_2' => 'Class II Division 2',
        'class_3' => 'Class III',
    ];

    const PHASES = [
        'initial' => 'Initial Setup',
        'alignment' => 'Alignment & Leveling',
        'space_closure' => 'Space Closure',
        'finishing' => 'Finishing & Detailing',
        'retention' => 'Retention',
    ];

    const TREATMENT_PHASES = [
        'bonding' => 'Bonding / Bracket Placement',
        'alignment' => 'Alignment & Leveling',
        'space_closure' => 'Space Closure',
        'finishing' => 'Finishing & Detailing',
        'retention' => 'Retention',
    ];

    const STATUSES = [
        'active' => 'Active',
        'paused' => 'Paused',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    const PAYMENT_PLANS = [
        'full' => 'Full Payment',
        'monthly' => 'Monthly Installments',
        'custom' => 'Custom Schedule',
    ];

    const SKELETAL_CLASSES = [
        'class_1' => 'Class I (Normal)',
        'class_2' => 'Class II (Retrognathic)',
        'class_3' => 'Class III (Prognathic)',
    ];

    const CROWDING_LEVELS = [
        'none' => 'None',
        'mild' => 'Mild (1-3mm)',
        'moderate' => 'Moderate (4-6mm)',
        'severe' => 'Severe (>6mm)',
    ];

    const MIDLINE_OPTIONS = [
        'centered' => 'Centered',
        'shifted_right' => 'Shifted Right',
        'shifted_left' => 'Shifted Left',
    ];

    const CROSSBITE_OPTIONS = [
        'none' => 'None',
        'anterior' => 'Anterior',
        'posterior_unilateral' => 'Posterior Unilateral',
        'posterior_bilateral' => 'Posterior Bilateral',
    ];

    const TOOTH_STATUSES = [
        'bracket_placed' => 'Bracket Placed',
        'missing_bracket' => 'Missing Bracket',
        'band' => 'Band',
        'elastic_attachment' => 'Elastic Attachment',
        'extraction_space' => 'Extraction Space',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($case) {
            if (!$case->case_number) {
                $case->case_number = self::generateCaseNumber();
            }
            
            // Calculate estimated completion date
            if ($case->start_date && $case->estimated_duration_months) {
                $case->estimated_completion_date = $case->start_date
                    ->copy()
                    ->addMonths($case->estimated_duration_months);
            }
            
            // Calculate balance
            $case->balance = $case->total_cost - $case->paid_amount;
        });

        static::updating(function ($case) {
            // Recalculate balance if cost or paid amount changed
            if ($case->isDirty(['total_cost', 'paid_amount'])) {
                $case->balance = $case->total_cost - $case->paid_amount;
            }
        });
    }

    /**
     * Generate unique case number.
     */
    public static function generateCaseNumber(): string
    {
        $date = now()->format('Ymd');
        $lastCase = self::whereDate('created_at', today())->latest('id')->first();
        $sequence = $lastCase ? ((int) substr($lastCase->case_number, -4)) + 1 : 1;
        
        return 'ORT-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Relationships

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(OrthodonticVisit::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(OrthodonticPhoto::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrthodonticPayment::class);
    }

    // Accessors

    public function getTreatmentTypeDisplayAttribute(): string
    {
        return self::TREATMENT_TYPES[$this->treatment_type] ?? $this->treatment_type;
    }

    public function getStatusDisplayAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getCurrentPhaseDisplayAttribute(): ?string
    {
        return $this->current_phase ? (self::PHASES[$this->current_phase] ?? $this->current_phase) : null;
    }

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->start_date || !$this->estimated_duration_months) {
            return 0;
        }

        $monthsElapsed = $this->start_date->diffInMonths(now());
        $percentage = ($monthsElapsed / $this->estimated_duration_months) * 100;

        return min(100, max(0, (int) $percentage));
    }

    public function getPaymentProgressPercentageAttribute(): int
    {
        if ($this->total_cost <= 0) {
            return 0;
        }

        return min(100, (int) (($this->paid_amount / $this->total_cost) * 100));
    }

    public function getDaysUntilCompletionAttribute(): ?int
    {
        if (!$this->estimated_completion_date) {
            return null;
        }

        return now()->diffInDays($this->estimated_completion_date, false);
    }
}
