<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dental Chart - {{ $patient->full_name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #008080; padding-bottom: 8px; }
        .header h1 { color: #008080; margin: 0 0 5px 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #666; font-size: 9px; }
        .section { margin-bottom: 10px; }
        .section-title { background-color: #008080; color: white; padding: 4px 8px; font-size: 11px; font-weight: bold; margin-bottom: 5px; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; padding: 3px 8px; font-weight: bold; width: 130px; font-size: 9px; background: #f8f9fa; }
        .info-value { display: table-cell; padding: 3px 8px; font-size: 9px; }
        .jaw-title { font-weight: bold; font-size: 11px; margin: 8px 0 4px; color: #008080; }
        .tooth-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .tooth-table td { text-align: center; padding: 4px 2px; font-size: 8px; vertical-align: top; }
        .tooth-box { width: 28px; height: 28px; border: 1px solid #ccc; margin: 0 auto 2px; line-height: 28px; font-weight: bold; font-size: 9px; }
        .tooth-num { font-size: 7px; color: #666; }
        .legend-table { width: 100%; border-collapse: collapse; }
        .legend-table td { padding: 2px 6px; font-size: 8px; }
        .legend-color-box { width: 12px; height: 12px; display: inline-block; border: 1px solid #ccc; }
        .records-table { width: 100%; border-collapse: collapse; font-size: 8px; }
        .records-table th { background: #008080; color: white; padding: 3px 6px; text-align: left; font-size: 8px; }
        .records-table td { padding: 3px 6px; border-bottom: 1px solid #eee; }
        .records-table tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 1px 5px; font-size: 7px; font-weight: bold; border: 1px solid #ccc; }
        .quadrant-header { font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 Dental Chart</h1>
        <p><strong>Patient:</strong> {{ $patient->full_name }}
            @if($patient->patient_id) &nbsp;|&nbsp; <strong>ID:</strong> {{ $patient->patient_id }} @endif
            &nbsp;|&nbsp; <strong>Type:</strong> {{ ucfirst($dentalChart->chart_type) }}
            &nbsp;|&nbsp; <strong>Date:</strong> {{ $dentalChart->created_at->format('F d, Y') }}
        </p>
    </div>

    @php
        $toothNumbers = $dentalChart->tooth_numbers;
        $toothRecords = $dentalChart->toothRecords->keyBy('tooth_number');

        $normalizeConditions = function ($record) {
            if (!$record) return [];
            $conditions = is_array($record->conditions) ? $record->conditions : [];
            if (empty($conditions) && !empty($record->primary_condition)) $conditions = [$record->primary_condition];
            $conditions = array_values(array_unique(array_filter($conditions, fn($c) => $c !== null && $c !== '')));
            if (count($conditions) > 1) $conditions = array_values(array_filter($conditions, fn($c) => $c !== 'healthy'));
            if (empty($conditions) && !empty($record->primary_condition)) $conditions = [$record->primary_condition];
            return $conditions;
        };
    @endphp

    <!-- Upper Jaw -->
    <div class="section">
        <div class="section-title">Upper Jaw (Maxillary)</div>
        <table class="tooth-table">
            <tr>
                <td colspan="8" class="quadrant-header">Right</td>
                <td style="width: 4px;"></td>
                <td colspan="8" class="quadrant-header">Left</td>
            </tr>
            <tr>
                @foreach(array_reverse($toothNumbers['upper_right']) as $num)
                    @php $record = $toothRecords->get($num); $color = $record ? $record->primary_condition_color : '#FFFFFF'; @endphp
                    <td>
                        <div class="tooth-box" style="background-color: {{ $color }};">{{ $num }}</div>
                        <div class="tooth-num">
                            @if($record){{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}@else Healthy @endif
                        </div>
                    </td>
                @endforeach
                <td style="width: 4px; border-left: 2px solid #008080;"></td>
                @foreach($toothNumbers['upper_left'] as $num)
                    @php $record = $toothRecords->get($num); $color = $record ? $record->primary_condition_color : '#FFFFFF'; @endphp
                    <td>
                        <div class="tooth-box" style="background-color: {{ $color }};">{{ $num }}</div>
                        <div class="tooth-num">
                            @if($record){{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}@else Healthy @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Lower Jaw -->
    <div class="section">
        <div class="section-title">Lower Jaw (Mandibular)</div>
        <table class="tooth-table">
            <tr>
                <td colspan="8" class="quadrant-header">Right</td>
                <td style="width: 4px;"></td>
                <td colspan="8" class="quadrant-header">Left</td>
            </tr>
            <tr>
                @foreach(array_reverse($toothNumbers['lower_right']) as $num)
                    @php $record = $toothRecords->get($num); $color = $record ? $record->primary_condition_color : '#FFFFFF'; @endphp
                    <td>
                        <div class="tooth-box" style="background-color: {{ $color }};">{{ $num }}</div>
                        <div class="tooth-num">
                            @if($record){{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}@else Healthy @endif
                        </div>
                    </td>
                @endforeach
                <td style="width: 4px; border-left: 2px solid #008080;"></td>
                @foreach($toothNumbers['lower_left'] as $num)
                    @php $record = $toothRecords->get($num); $color = $record ? $record->primary_condition_color : '#FFFFFF'; @endphp
                    <td>
                        <div class="tooth-box" style="background-color: {{ $color }};">{{ $num }}</div>
                        <div class="tooth-num">
                            @if($record){{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? '' }}@else Healthy @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Legend -->
    <div class="section">
        <div class="section-title">Condition Legend</div>
        <table class="legend-table">
            <tr>
                @php $i = 0; @endphp
                @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                    @if($i > 0 && $i % 5 == 0)</tr><tr>@endif
                    <td><span class="legend-color-box" style="background-color: {{ $condition['color'] }};"></span></td>
                    <td>{{ $condition['name'] }}</td>
                    @php $i++; @endphp
                @endforeach
            </tr>
        </table>
    </div>

    <!-- Tooth Records Detail -->
    @if($dentalChart->toothRecords->count() > 0)
    <div class="section">
        <div class="section-title">Tooth Records Detail</div>
        <table class="records-table">
            <thead>
                <tr>
                    <th>Tooth #</th>
                    <th>Primary Condition</th>
                    <th>All Conditions</th>
                    <th>Surfaces</th>
                    <th>Severity</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dentalChart->toothRecords->sortBy('tooth_number') as $record)
                <tr>
                    <td><strong>{{ $record->tooth_number }}</strong></td>
                    <td>
                        <span class="badge" style="background-color: {{ $record->primary_condition_color }};">
                            {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? $record->primary_condition }}
                        </span>
                    </td>
                    <td>
                        @foreach($normalizeConditions($record) as $cond)
                            {{ \App\Models\DentalToothRecord::CONDITIONS[$cond]['name'] ?? $cond }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>{{ $record->surfaces_affected ? implode(', ', array_map('strtoupper', $record->surfaces_affected)) : '-' }}</td>
                    <td>{{ $record->severity ? ucfirst($record->severity) : '-' }}</td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($dentalChart->general_notes)
    <div class="section">
        <div class="section-title">General Notes</div>
        <p style="padding: 5px; background: #f8f9fa; border-left: 2px solid #008080; font-size: 9px;">{{ $dentalChart->general_notes }}</p>
    </div>
    @endif

    <div style="text-align: center; margin-top: 15px; color: #999; font-size: 8px; border-top: 1px solid #eee; padding-top: 5px;">
        Generated on {{ now()->format('F d, Y \a\t h:i A') }} &bull; Dental Chart #{{ $dentalChart->id }}
    </div>
</body>
</html>

