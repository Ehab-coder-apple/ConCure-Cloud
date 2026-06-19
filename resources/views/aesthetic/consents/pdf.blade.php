<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $consentForm->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header, .section { margin-bottom: 20px; }
        .muted { color: #6b7280; }
        .signature { border-top: 1px solid #9ca3af; margin-top: 12px; padding-top: 8px; }
        .signature img { max-height: 90px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin-bottom: 4px;">{{ $consentForm->title }}</h2>
        <div class="muted">{{ $clinic?->name }} • {{ __('Signed at') }} {{ $consentForm->signed_at?->format('M d, Y h:i A') }}</div>
    </div>

    <div class="section">
        <strong>{{ __('Patient') }}:</strong> {{ trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) }}<br>
        <strong>{{ __('Patient ID') }}:</strong> {{ $patient->patient_id ?: $patient->id }}<br>
        @if($consentForm->session)
            <strong>{{ __('Session') }}:</strong> #{{ $consentForm->session->session_number }}<br>
        @endif
        @if($consentForm->treatment)
            <strong>{{ __('Treatment') }}:</strong> {{ $consentForm->treatment->name }}
        @endif
    </div>

    <div class="section" style="line-height: 1.6; white-space: pre-line;">{{ $consentForm->body }}</div>

    <div class="signature">
        <div><strong>{{ __('Signed electronically by') }}:</strong> {{ $consentForm->signer_name ?: trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) }}</div>
        <div class="muted">{{ __('Timestamp') }}: {{ $consentForm->signed_at?->format('M d, Y h:i A') }}</div>
        <div style="margin-top: 10px;"><img src="{{ $consentForm->signature_data }}" alt="Signature"></div>
    </div>
</body>
</html>