<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Patient Form') }} - {{ $patient->full_name }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 11.5px; line-height: 1.45; color: #222; }
        h1, h2, h3, h4 { margin: 0 0 8px 0; }
        .section { margin-top: 14px; }
        .section-title { font-size: 13px; color: #111; margin-bottom: 6px; font-weight: 700; }
        .hr { border-top: 1px solid #e5e7eb; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; }
        .meta-table { width: 100%; border-collapse: collapse; background: #f9fbfd; border: 1px solid #e5e7eb; }
        .meta-table td { padding: 6px 10px; border-bottom: 1px solid #eef2f7; vertical-align: top; width: 50%; }
        .meta-table tr:last-child td { border-bottom: 0; }
        pre { white-space: pre-wrap; word-wrap: break-word; background: #fafafa; padding: 12px; border: 1px solid #eee; font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size: 11px; line-height: 1.5; }
        .muted { color: #666; }
        /* Rich content (from CKEditor): ensure tables render in PDF */
        .form-rich-content table { width: 100%; border-collapse: collapse; }
        .form-rich-content table, .form-rich-content th, .form-rich-content td { border: 1px solid #6b7280; }
        .form-rich-content th, .form-rich-content td { padding: 6px 8px; vertical-align: top; }
        .form-rich-content thead th { background: #f3f4f6; }
    </style>

</head>
<body>
    @include('components.pdf-clinic-header', [
        'clinicId' => $patient->clinic_id ?? null,
        'documentTitle' => __('Patient Form') . ' — ' . ($assignment->template?->name ?? __('Form')),
    ])

    <div class="section">
        <div class="section-title">{{ __('Patient') }}</div>
        <table class="meta-table">
            <tr>
                <td><strong>{{ __('Name') }}:</strong> {{ $patient->full_name }}</td>
                <td><strong>{{ __('Patient ID') }}:</strong> {{ $patient->patient_id ?? $patient->id }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Phone') }}:</strong> {{ $patient->phone ?? '-' }}</td>
                <td><strong>{{ __('Date') }}:</strong> {{ $assignment->completed_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">{{ __('Assignment Info') }}</div>
        <table class="meta-table">
            <tr>
                <td><strong>{{ __('Assigned At') }}:</strong> {{ $assignment->assigned_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td><strong>{{ __('Assigned By') }}:</strong> {{ $assignment->assignedBy?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Completed At') }}:</strong> {{ $assignment->completed_at?->format('Y-m-d H:i') ?? '-' }}</td>
                <td><strong>{{ __('Filled By') }}:</strong> {{ $assignment->filledBy?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($assignment->notes))
    <div class="section">
        <div class="section-title">{{ __('Notes') }}</div>
        <div class="muted">{{ $assignment->notes }}</div>
    </div>
    @endif


    @php($content = data_get($assignment->form_data, 'content'))
    <div class="section">
        <div class="section-title">{{ __('Form Data') }}</div>
        @if(!empty($content))
            <div class="form-rich-content">{!! $content !!}</div>
        @else
            <div class="text-muted">{{ __('No content provided.') }}</div>
        @endif
    </div>
</body>
</html>

