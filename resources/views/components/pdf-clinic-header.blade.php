@php
    $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($clinicId ?? auth()->user()->clinic_id ?? null);
    $clinicName = $clinicName ?? (auth()->user()->clinic->name ?? 'ConCure Clinic');
    $clinicInfo = $clinicInfo ?? '';
    $documentTitle = $documentTitle ?? '';
@endphp

<div class="pdf-clinic-header">
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            @if($clinicLogo)
                <td style="width: 80px; vertical-align: top; text-align: center;">
                    <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                         alt="Clinic Logo"
                         style="max-height: 80px; max-width: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e9ecef; padding: 1px;">
                </td>
            @endif
            <td style="vertical-align: top; text-align: {{ $clinicLogo ? 'left' : 'center' }}; {{ $clinicLogo ? 'padding-left: 15px;' : '' }}">
                <h1 style="color: #20B2AA; font-size: 22px; margin: 0 0 5px 0; font-weight: bold;">{{ $clinicName }}</h1>
                @if($clinicInfo)
                    <p style="font-size: 11px; color: #666; margin: 0 0 5px 0; line-height: 1.3;">{{ $clinicInfo }}</p>
                @endif
                @if($documentTitle)
                    <p style="font-size: 14px; color: #333; margin: 10px 0 0 0; font-weight: bold;">{{ $documentTitle }}</p>
                @endif
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #20B2AA; margin-bottom: 20px;"></div>
</div>
