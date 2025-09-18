<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'conversation_id',
        'message_id',
        'patient_id',
        'transfer_type',
        'source_type',
        'source_id',
        'status',
        'acted_by',
        'acted_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acted_at' => 'datetime',
    ];

    // Relationships
    public function clinic(): BelongsTo { return $this->belongsTo(Clinic::class); }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
    public function message(): BelongsTo { return $this->belongsTo(Message::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'acted_by'); }

    public function source(): MorphTo { return $this->morphTo(__FUNCTION__, 'source_type', 'source_id'); }

    // Scopes
    public function scopeForClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}

