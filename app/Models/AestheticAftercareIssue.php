<?php

namespace App\Models;

use App\Services\StorageQuotaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AestheticAftercareIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'session_id',
        'treatment_id',
        'aftercare_template_id',
        'patient_file_id',
        'template_name',
        'template_category',
        'title',
        'instructions_snapshot',
        'notes',
        'issued_at',
        'pdf_file_name',
        'pdf_path',
        'pdf_file_size',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'pdf_file_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;

            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            } else {
                $query->whereRaw('1 = 0');
            }
        });

        static::creating(function (self $issue): void {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;

            if ($tenantId && empty($issue->tenant_id)) {
                $issue->tenant_id = $tenantId;
            }

            if (!$issue->issued_at) {
                $issue->issued_at = now();
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AestheticSession::class, 'session_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(AestheticTreatment::class, 'treatment_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AestheticAftercareTemplate::class, 'aftercare_template_id');
    }

    public function patientFile(): BelongsTo
    {
        return $this->belongsTo(PatientFile::class, 'patient_file_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function getPdfUrlAttribute(): string
    {
        return $this->patientFile?->file_url ?? StorageQuotaService::getSecureUrl($this->pdf_path);
    }

    public function getTemplateCategoryDisplayAttribute(): string
    {
        return AestheticTreatment::CATEGORIES[$this->template_category] ?? ucwords(str_replace(['_', '-'], ' ', (string) $this->template_category));
    }
}