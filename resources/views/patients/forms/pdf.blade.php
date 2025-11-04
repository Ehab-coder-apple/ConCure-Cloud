<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Patient Form') }} - {{ $patient->full_name }}</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; color: #111; }
        h1, h2, h3, h4 { margin: 0 0 8px 0; }
        .text-muted { color: #555; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .section { margin-top: 16px; }
        .hr { border-top: 1px solid #ddd; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; }
        .table td { padding: 6px 8px; vertical-align: top; }
        .table--border td { border: 1px solid #ddd; }
        pre { white-space: pre-wrap; word-wrap: break-word; background: #f8f8f8; padding: 10px; border: 1px solid #eee; }
    </style>
</head>
<body>
    <h2 class="mb-2">{{ __('Patient Form') }}</h2>
    <div class="mb-3 text-muted">{{ $assignment->template?->name ?? __('Form') }}</div>

    <div class="section">
        <h4 class="mb-1">{{ __('Patient') }}</h4>
        <table class="table">
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
        <h4 class="mb-1">{{ __('Assignment Info') }}</h4>
        <table class="table">
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

    @php($content = data_get($assignment->form_data, 'content'))
    <div class="section">
        <h4 class="mb-1">{{ __('Form Data') }}</h4>
        @if(!empty($content))
            <pre>{{ $content }}</pre>
        @else
            <div class="text-muted">{{ __('No content provided.') }}</div>
        @endif
    </div>
</body>
</html>

