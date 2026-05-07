<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credential_used',
        'session_id',
        'ip_address',
        'device_fingerprint',
        'user_agent',
        'browser',
        'os',
        'terminated_at',
        'termination_reason',
        'terminated_by_session_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    /**
     * Get the user that owns this session
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this session is still active (not terminated)
     */
    public function isActive(): bool
    {
        return is_null($this->terminated_at);
    }

    /**
     * Terminate this session
     */
    public function terminate(string $reason, ?string $terminatedBySessionId = null): void
    {
        $this->update([
            'terminated_at' => now(),
            'termination_reason' => $reason,
            'terminated_by_session_id' => $terminatedBySessionId,
        ]);
    }

    /**
     * Scope to get only active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereNull('terminated_at');
    }

    /**
     * Scope to get sessions for a specific user credential
     */
    public function scopeForCredential($query, int $userId, string $credential)
    {
        return $query->where('user_id', $userId)
            ->where('credential_used', $credential);
    }
}
