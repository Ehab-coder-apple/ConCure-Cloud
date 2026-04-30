<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Clinic-scoped custom medicine form (e.g. "Gel", "Lozenge", "Mouthwash").
 *
 * Merged with Medicine::FORMS at render-time so each clinic sees the
 * canonical built-in forms plus its own additions.
 */
class MedicineForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'key',
        'label',
        'created_by',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByClinic($query, ?int $clinicId)
    {
        if ($clinicId === null) {
            return $query->whereRaw('1 = 0');
        }
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Build a stable key from a free-text label. Falls back to a short hash
     * if the slug would otherwise be empty (e.g. all non-ASCII input).
     */
    public static function makeKey(string $label): string
    {
        $slug = Str::slug($label, '_');
        if ($slug === '') {
            $slug = 'custom_' . substr(md5($label), 0, 8);
        }
        return Str::limit($slug, 60, '');
    }
}
