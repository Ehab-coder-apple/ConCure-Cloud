<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'dejavusans', sans-serif;
        }
        .page-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .background-template {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .medicine-overlay {
            position: absolute;
            top: {{ $rxSettings['medicine_y'] }}px;
            left: {{ $rxSettings['medicine_x'] }}px;
            z-index: 10;
            font-size: {{ $rxSettings['font_size'] }}px;
            line-height: {{ $rxSettings['line_spacing'] }}px;
            color: #000;
        }
        .medicine-line {
            margin-bottom: {{ max(0, $rxSettings['line_spacing'] - $rxSettings['font_size'] - 2) }}px;
        }
        .medicine-number {
            display: inline-block;
            font-weight: bold;
            min-width: 18px;
        }
        .medicine-name {
            font-weight: bold;
        }
        .medicine-details {
            font-size: {{ max(7, $rxSettings['font_size'] - 2) }}px;
            color: #333;
            padding-left: 20px;
        }
        .patient-info-overlay {
            position: absolute;
            top: {{ max(10, $rxSettings['medicine_y'] - 60) }}px;
            left: {{ $rxSettings['medicine_x'] }}px;
            z-index: 10;
            font-size: {{ $rxSettings['font_size'] }}px;
            color: #000;
        }
        .diagnosis-overlay {
            position: absolute;
            top: {{ max(10, $rxSettings['medicine_y'] - 30) }}px;
            left: {{ $rxSettings['medicine_x'] }}px;
            z-index: 10;
            font-size: {{ max(8, $rxSettings['font_size'] - 1) }}px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        {{-- Background template image --}}
        @if($templateImagePath)
            <img src="{{ $templateImagePath }}" class="background-template" alt="">
        @endif

        {{-- Patient info --}}
        <div class="patient-info-overlay">
            <strong>Patient:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Date:</strong> {{ $prescription->prescribed_date->format('d/m/Y') }}
            @if($prescription->patient->date_of_birth)
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Age:</strong> {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }}y
            @endif
        </div>

        {{-- Diagnosis --}}
        @if($prescription->diagnosis)
            <div class="diagnosis-overlay">
                <strong>Dx:</strong> {{ $prescription->diagnosis }}
            </div>
        @endif

        {{-- Medicines overlay --}}
        <div class="medicine-overlay">
            @foreach($medicines as $index => $medicine)
                <div class="medicine-line">
                    <span class="medicine-number">{{ $index + 1 }}.</span>
                    <span class="medicine-name">{{ $medicine->medicine_name }}</span>
                    @if($medicine->dosage || $medicine->frequency || $medicine->duration)
                        <br>
                        <span class="medicine-details">
                            @if($medicine->dosage){{ $medicine->dosage }}@endif
                            @if($medicine->frequency) — {{ $medicine->frequency }}@endif
                            @if($medicine->duration) — {{ $medicine->duration }}@endif
                        </span>
                    @endif
                    @if($medicine->instructions)
                        <br>
                        <span class="medicine-details"><em>{{ $medicine->instructions }}</em></span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Notes at bottom if present --}}
        @if($prescription->notes)
            <div style="position: absolute; bottom: 60px; left: {{ $rxSettings['medicine_x'] }}px; font-size: {{ max(7, $rxSettings['font_size'] - 2) }}px; color: #333; z-index: 10;">
                <strong>Notes:</strong> {{ $prescription->notes }}
            </div>
        @endif
    </div>
</body>
</html>

