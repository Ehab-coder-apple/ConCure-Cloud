<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $issue->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header, .section { margin-bottom: 20px; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin-bottom: 4px;">{{ $issue->title }}</h2>
        <div class="muted">{{ $clinic?->name }} • {{ __('Issued at') }} {{ $issue->issued_at?->format('M d, Y h:i A') }}</div>
    </div>

    <div class="section">
        <strong>{{ __('Patient') }}:</strong> {{ trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) }}<br>
        <strong>{{ __('Patient ID') }}:</strong> {{ $patient->patient_id ?: $patient->id }}<br>
        @if($issue->session)
            <strong>{{ __('Session') }}:</strong> #{{ $issue->session->session_number }}<br>
        @endif
        <strong>{{ __('Template') }}:</strong> {{ $issue->template_name }}
        @if($issue->template_category)
            ({{ $issue->template_category_display }})
        @endif
    </div>

    <div class="section" style="line-height: 1.6; white-space: pre-line;">{{ $issue->instructions_snapshot }}</div>

    @if($issue->notes)
        <div class="section">
            <strong>{{ __('Practitioner Notes') }}</strong>
            <div style="white-space: pre-line; margin-top: 6px;">{{ $issue->notes }}</div>
        </div>
    @endif
</body>
</html>