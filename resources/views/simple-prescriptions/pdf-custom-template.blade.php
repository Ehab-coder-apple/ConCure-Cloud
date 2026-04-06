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
    </style>
</head>
<body>
    {{-- Background template image using fixed positioning for mPDF --}}
    @if($templateImagePath)
        <div style="position: fixed; top: 0; left: 0; z-index: -1;">
            <img src="{{ $templateImagePath }}" style="width: 210mm; height: 297mm;" />
        </div>
    @endif

    @php
        $medX = $rxSettings['medicine_x'] ?? 40;
        $medY = $rxSettings['medicine_y'] ?? 200;
        $fontSize = $rxSettings['font_size'] ?? 11;
        $lineSpacing = $rxSettings['line_spacing'] ?? 22;
        $patientY = max(5, $medY - 55);
        $diagnosisY = max(5, $medY - 28);
    @endphp

    {{-- Patient info --}}
    <div style="position: fixed; top: {{ $patientY }}pt; left: {{ $medX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        @if($prescription->patient)
            <strong>Patient:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ $prescription->prescribed_date ? $prescription->prescribed_date->format('d/m/Y') : date('d/m/Y') }}
        @if($prescription->patient && $prescription->patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ $prescription->patient->age_formatted }}
        @endif
    </div>

    {{-- Diagnosis --}}
    @if($prescription->diagnosis)
        <div style="position: fixed; top: {{ $diagnosisY }}pt; left: {{ $medX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Dx:</strong> {{ $prescription->diagnosis }}
        </div>
    @endif

    {{-- Medicines overlay using fixed positioning with pt units for mPDF --}}
    @php $currentY = $medY; @endphp
    @foreach($medicines as $index => $medicine)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $medX }}pt; font-size: {{ $fontSize }}pt; line-height: {{ $lineSpacing }}pt; color: #000;">
            <b>{{ $index + 1 }}. {{ $medicine->medicine_name }}</b>
            @if($medicine->dosage || $medicine->frequency || $medicine->duration)
                <br>
                <span style="font-size: {{ max(7, $fontSize - 2) }}pt; color: #333; padding-left: 15pt;">
                    @if($medicine->dosage){{ $medicine->dosage }}@endif
                    @if($medicine->frequency) — {{ $medicine->frequency }}@endif
                    @if($medicine->duration) — {{ $medicine->duration }}@endif
                </span>
            @endif
            @if($medicine->instructions)
                <br>
                <span style="font-size: {{ max(7, $fontSize - 2) }}pt; color: #333; padding-left: 15pt;">
                    <em>{{ $medicine->instructions }}</em>
                </span>
            @endif
        </div>
        @php
            $lines = 1;
            if ($medicine->dosage || $medicine->frequency || $medicine->duration) $lines++;
            if ($medicine->instructions) $lines++;
            $currentY += $lineSpacing * $lines;
        @endphp
    @endforeach

    {{-- Notes at bottom if present --}}
    @if($prescription->notes)
        @php
            $notesYBottom = $rxSettings['notes_y_bottom'] ?? 60;
            $notesXRight = $rxSettings['notes_x_right'] ?? 40;
        @endphp
        <div style="position: fixed; bottom: {{ $notesYBottom }}pt; right: {{ $notesXRight }}pt; font-size: {{ max(7, $fontSize - 2) }}pt; color: #333;">
            <strong>Notes:</strong> {{ $prescription->notes }}
        </div>
    @endif
</body>
</html>

