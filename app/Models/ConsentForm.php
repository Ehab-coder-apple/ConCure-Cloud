<?php

namespace App\Models;

use App\Services\StorageQuotaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'patient_id',
        'session_id',
        'treatment_id',
        'patient_file_id',
        'title',
        'body',
        'signature_data',
        'signed_at',
        'signer_name',
        'pdf_file_name',
        'pdf_path',
        'pdf_file_size',
        'created_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
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

        static::creating(function (self $consent): void {
            $tenantId = auth()->check() ? auth()->user()->clinic?->tenant_id : null;

            if ($tenantId && empty($consent->tenant_id)) {
                $consent->tenant_id = $tenantId;
            }

            if (!$consent->signed_at) {
                $consent->signed_at = now();
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

    public function patientFile(): BelongsTo
    {
        return $this->belongsTo(PatientFile::class, 'patient_file_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPdfUrlAttribute(): string
    {
        return $this->patientFile?->file_url ?? StorageQuotaService::getSecureUrl($this->pdf_path);
    }
}