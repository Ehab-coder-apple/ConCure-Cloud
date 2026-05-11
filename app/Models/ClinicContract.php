<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'contract_type',
        'contract_title',
        'contract_content',
        'monthly_price',
        'yearly_price',
        'contract_duration_months',
        'start_date',
        'end_date',
        'requires_renewal',
        'status',
        'accepted_at',
        'accepted_by_user_id',
        'acceptance_ip',
        'signature_name',
        'created_by',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'contract_duration_months' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'requires_renewal' => 'boolean',
        'accepted_at' => 'datetime',
    ];

    /**
     * The clinic this contract belongs to.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * The user who accepted the contract.
     */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    /**
     * The admin who created the contract.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the contract is pending acceptance.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the contract is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the contract has expired.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Accept the contract.
     */
    public function accept(User $user, string $signatureName, string $ip): bool
    {
        $this->status = 'accepted';
        $this->accepted_at = now();
        $this->accepted_by_user_id = $user->id;
        $this->signature_name = $signatureName;
        $this->acceptance_ip = $ip;

        return $this->save();
    }
}
