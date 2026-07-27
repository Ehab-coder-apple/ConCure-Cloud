<?php

namespace App\Models;

use App\Models\Concerns\AppliesAccessibleClinicScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    use HasFactory, AppliesAccessibleClinicScope;

    protected $fillable = [
        'clinic_id',
        'amount',
        'currency',
        'paid_at',
        'method',
        'city',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}

