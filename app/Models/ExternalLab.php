<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'whatsapp',
        'email',
        'website',
        'notes',
        'is_active',
        'sort_order',
        'clinic_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the clinic that owns this external lab.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who created this external lab.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to filter active labs.
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
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the display name with contact info.
     */
    public function getDisplayNameAttribute(): string
    {
        $display = $this->name;
        if ($this->phone) {
            $display .= ' - ' . $this->phone;
        }
        return $display;
    }
}
