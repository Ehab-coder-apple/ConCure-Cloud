<!DOCTYPE html>
<html lang="{{ $language }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Vaccination Card') }} - {{ $patient->full_name }}</title>
    <style>
        body {
            font-family: {{ $isRtl ? "'XB Riyaz', 'Tahoma', 'Arial'" : "'Helvetica', 'Arial'" }}, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .header h1 { font-size: 20px; color: #2563eb; margin: 0; }
        .header p { color: #666; margin: 4px 0 0; }
        .patient-info { margin-bottom: 15px; }
        .patient-info table { width: 100%; border-collapse: collapse; }
        .patient-info td { padding: 4px 8px; font-size: 11px; }
        .patient-info .label { font-weight: bold; color: #555; width: 120px; }
        .stats-row { margin-bottom: 15px; text-align: center; }
        .stat-box { display: inline-block; padding: 8px 16px; margin: 0 6px; border-radius: 6px; text-align: center; }
        .stat-box .num { font-size: 18px; font-weight: bold; display: block; }
        .stat-box .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        .bg-blue { background: #dbeafe; color: #1e40af; }
        .bg-green { background: #d1fae5; color: #065f46; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-yellow { background: #fef3c7; color: #92400e; }
        table.vaccines { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.vaccines th, table.vaccines td { border: 1px solid #d1d5db; padding: 5px 8px; text-align: {{ $isRtl ? 'right' : 'left' }}; font-size: 10px; }
        table.vaccines th { background: #f3f4f6; font-weight: 600; font-size: 9px; text-transform: uppercase; }
        .status-on_time { color: #065f46; font-weight: bold; }
        .status-delayed { color: #92400e; font-weight: bold; }
        .status-missed { color: #991b1b; font-weight: bold; }
        .status-upcoming { color: #075985; }
        .status-skipped { color: #6b7280; }
        .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        @if($patient->clinic)
            <h1>{{ $patient->clinic->name }}</h1>
        @endif
        <p style="font-size: 16px; font-weight: bold; color: #1e40af;">{{ __('Vaccination Card') }}</p>
    </div>

    <div class="patient-info">
        <table>
            <tr>
                <td class="label">{{ __('Patient Name') }}:</td>
                <td><strong>{{ $patient->full_name }}</strong></td>
                <td class="label">{{ __('Patient ID') }}:</td>
                <td>{{ $patient->patient_id }}</td>
            </tr>
            <tr>
                <td class="label">{{ __('Date of Birth') }}:</td>
                <td>{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : '—' }}</td>
                <td class="label">{{ __('Schedule') }}:</td>
                <td>{{ $patient->vaccinationSchedule->name ?? '—' }} ({{ $patient->vaccinationSchedule?->country?->name ?? '' }})</td>
            </tr>
        </table>
    </div>

    <div class="stats-row">
        <div class="stat-box bg-blue"><span class="num">{{ $stats['total'] }}</span><span class="lbl">{{ __('Total') }}</span></div>
        <div class="stat-box bg-green"><span class="num">{{ $stats['given'] }}</span><span class="lbl">{{ __('Given') }}</span></div>
        <div class="stat-box bg-red"><span class="num">{{ $stats['missed'] }}</span><span class="lbl">{{ __('Missed') }}</span></div>
        <div class="stat-box bg-yellow"><span class="num">{{ $stats['upcoming'] }}</span><span class="lbl">{{ __('Upcoming') }}</span></div>
    </div>

    <table class="vaccines">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('Vaccine') }}</th>
                <th>{{ __('Dose') }}</th>
                <th>{{ __('Scheduled Date') }}</th>
                <th>{{ __('Given Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Batch #') }}</th>
                <th>{{ __('Notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patient->vaccinations as $i => $vacc)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong>{{ $vacc->vaccine->getLocalizedName($language) }}</strong></td>
                <td>{{ $vacc->dose_number }}</td>
                <td>{{ $vacc->scheduled_date->format('Y-m-d') }}</td>
                <td>{{ $vacc->given_date ? $vacc->given_date->format('Y-m-d') : '—' }}</td>
                <td class="status-{{ $vacc->status }}">{{ __(ucfirst(str_replace('_', ' ', $vacc->status))) }}</td>
                <td>{{ $vacc->batch_number ?? '' }}</td>
                <td>{{ Str::limit($vacc->notes, 30) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('Generated on') }}: {{ now()->format('M d, Y H:i') }}
        &mdash; {{ __('Completion') }}: {{ $stats['completion_percentage'] }}%
        @if($patient->clinic)
            &mdash; {{ $patient->clinic->name }}
        @endif
    </div>
</body>
</html>

