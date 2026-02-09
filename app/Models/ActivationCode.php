<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActivationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'clinic_id',
        'role',
        'is_used',
        'used_by',
        'used_at',
        'expires_at',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Activation code types
     */
    const TYPES = [
        'clinic' => 'Clinic Activation',
        'user' => 'User Activation',
    ];

    /**
     * Get the clinic that owns the activation code.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user who used this activation code.
     */
    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Get the user who created this activation code.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if activation code is valid.
     */
    public function isValid(): bool
    {
        return !$this->is_used && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Mark activation code as used.
     */
    public function markAsUsed(User $user): void
    {
        $this->update([
            'is_used' => true,
            'used_by' => $user->id,
            'used_at' => now(),
        ]);
    }

    /**
     * Generate a unique activation code.
     */
    public static function generateUniqueCode(string $type): string
    {
        do {
            $prefix = strtoupper($type);
            $code = $prefix . '-' . strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Create a clinic activation code.
     */
    public static function createClinicCode(User $creator, ?string $notes = null): self
    {
        return self::create([
            'code' => self::generateUniqueCode('CLINIC'),
            'type' => 'clinic',
            'expires_at' => now()->addMonths(3),
            'created_by' => $creator->id,
            'notes' => $notes,
        ]);
    }

    /**
     * Create a user activation code.
     */
    public static function createUserCode(
        User $creator, 
        Clinic $clinic, 
        string $role, 
        ?string $notes = null
    ): self {
        return self::create([
            'code' => self::generateUniqueCode('USER'),
            'type' => 'user',
            'clinic_id' => $clinic->id,
            'role' => $role,
            'expires_at' => now()->addMonths(1),
            'created_by' => $creator->id,
            'notes' => $notes,
        ]);
    }

    /**
     * Scope to filter unused codes.
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope to filter valid codes.
     */
    public function scopeValid($query)
    {
        return $query->unused()
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to filter expired codes.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }
}
