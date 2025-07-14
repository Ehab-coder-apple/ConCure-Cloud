@php
    $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo(auth()->user()->clinic_id ?? $clinicId ?? null);
    $clinicName = auth()->user()->clinic->name ?? $clinicName ?? 'ConCure Clinic';
    $clinicInfo = $clinicInfo ?? '';
@endphp

<div class="clinic-header" style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #20B2AA;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
        @if($clinicLogo)
            <img src="{{ $clinicLogo }}" alt="Clinic Logo" style="max-height: 85px; max-width: 85px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ecef; padding: 2px;">
        @endif
        <div style="text-align: {{ $clinicLogo ? 'left' : 'center' }};">
            <h1 style="color: #20B2AA; font-size: 24px; margin: 0; font-weight: bold;">{{ $clinicName }}</h1>
            @if($clinicInfo)
                <p style="font-size: 12px; color: #666; margin: 5px 0 0 0;">{{ $clinicInfo }}</p>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        .clinic-header img {
            max-height: 60px !important;
            max-width: 60px !important;
        }
        .clinic-header h1 {
            font-size: 20px !important;
        }
        .clinic-header p {
            font-size: 10px !important;
        }
    }
</style>
