<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaccinationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'version',
        'is_default',
        'effective_from',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScheduleItem::class, 'schedule_id')->orderBy('sort_order')->orderBy('recommended_age_value');
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'vaccination_schedule_id');
    }

    /**
     * Get the total number of vaccine doses in this schedule.
     */
    public function getTotalDosesAttribute(): int
    {
        return $this->items()->count();
    }
}

