@php
    $cid = $clinicId ?? auth()->user()->clinic_id ?? null;
    $info = \App\Helpers\ClinicHelper::getClinicInfo($cid);

    $clinicName = $clinicName ?? ($info['name'] ?? 'ConCure Clinic');
    $documentTitle = $documentTitle ?? '';

    // Prefer an absolute filesystem path for PDF engines (more reliable than HTTP)
    $logoSrc = $info['logo_pdf_path'] ?? null;
    if ($logoSrc && !preg_match('#^(?:https?://|data:|file://)#i', $logoSrc)) {
        // If it's a local absolute path, normalize for DomPDF using file:// and verify existence
        if (file_exists($logoSrc)) {
            $logoSrc = 'file://' . $logoSrc;
        } else {
            $logoSrc = null; // force fallback below
        }
    }
    // Fallback to stored HTTPS logo if no local path resolved
    if (!$logoSrc && !empty($info['logo'])) {
        if (preg_match('#^https?://#i', $info['logo'])) {
            $logoSrc = $info['logo'];
        } elseif (!empty($cid)) {
            // Try public route that streams the stored logo
            try { $logoSrc = route('clinic.logo', ['clinic' => $cid]); } catch (\Throwable $e) { /* ignore */ }
        }
    }

    $lines = [];
    if (!empty($info['address'])) { $lines[] = $info['address']; }
    if (!empty($info['phone']))   { $lines[] = __('Phone') . ': ' . $info['phone']; }
    if (!empty($info['email']))   { $lines[] = __('Email') . ': ' . $info['email']; }
    $clinicInfoText = $clinicInfo ?? implode(' • ', array_filter($lines));
@endphp

<div class="pdf-clinic-header">
    <table style="width: 100%; margin-bottom: 18px;">
        <tr>
            @if($logoSrc)
                <td style="width: 84px; vertical-align: top; text-align: center;">
                    <img src="{{ $logoSrc }}" alt="Clinic Logo"
                         style="max-height: 80px; max-width: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #e9ecef; padding: 1px;">
                </td>
            @endif
            <td style="vertical-align: top; text-align: {{ $logoSrc ? 'left' : 'center' }}; {{ $logoSrc ? 'padding-left: 14px;' : '' }}">
                <h1 style="color: #20B2AA; font-size: 22px; margin: 0 0 6px 0; font-weight: 700;">{{ $clinicName }}</h1>
                @if(!empty($clinicInfoText))
                    <p style="font-size: 11px; color: #666; margin: 0 0 6px 0; line-height: 1.35;">{{ $clinicInfoText }}</p>
                @endif
                @if($documentTitle)
                    <p style="font-size: 13px; color: #333; margin: 8px 0 0 0; font-weight: 600;">{{ $documentTitle }}</p>
                @endif
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #20B2AA; margin-bottom: 16px;"></div>
</div>
