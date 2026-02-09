<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'type',
        'title',
        'created_by',
        'last_message_at',
        'is_archived',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function clinic(): BelongsTo { return $this->belongsTo(Clinic::class); }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function participants(): HasMany { return $this->hasMany(ConversationParticipant::class); }

    public function messages(): HasMany { return $this->hasMany(Message::class); }

    // Scopes
    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
}

