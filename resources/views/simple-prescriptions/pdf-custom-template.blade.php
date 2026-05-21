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
    @php
        $pageWidthPt = $rxSettings['page_width_pt'] ?? 595.28;
        $pageHeightPt = $rxSettings['page_height_pt'] ?? 841.89;
    @endphp

    {{-- Background template image using fixed positioning for mPDF --}}
    @if($templateImagePath)
        <div style="position: fixed; top: 0; left: 0; width: {{ $pageWidthPt }}pt; height: {{ $pageHeightPt }}pt; overflow: hidden;">
            <img src="{{ $templateImagePath }}" style="width: {{ $pageWidthPt }}pt; height: {{ $pageHeightPt }}pt; max-width: none; max-height: none;" />
        </div>
    @endif

    @php
        $medX = $rxSettings['medicine_x'] ?? 40;
        $medY = $rxSettings['medicine_y'] ?? 200;
        $fontSize = $rxSettings['font_size'] ?? 11;
        $lineSpacing = $rxSettings['line_spacing'] ?? 22;
        // Header section uses its own independent settings
        $headerY = $rxSettings['header_y'] ?? 20;
        $headerFontSize = $rxSettings['header_font_size'] ?? 11;
        $headerLineSpacing = $rxSettings['header_line_spacing'] ?? 18;
        $patientY = max(5, $headerY);
        $diagnosisY = max(5, $patientY + $headerLineSpacing);
        $contentRightMargin = 20; // pt from right edge
    @endphp

    {{-- Patient info --}}
    <div style="position: fixed; top: {{ $patientY }}pt; left: {{ $medX }}pt; right: {{ $contentRightMargin }}pt; font-size: {{ $headerFontSize }}pt; color: #000;">
        @if($prescription->patient)
            <span style="white-space: nowrap;"><strong>Patient:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</span>
        @endif
        <span style="margin: 0 6pt;">|</span>
        <span style="white-space: nowrap;"><strong>Date:</strong> {{ $prescription->prescribed_date ? $prescription->prescribed_date->format('d/m/Y') : date('d/m/Y') }}</span>
        @if($prescription->patient && $prescription->patient->date_of_birth)
            <span style="margin: 0 6pt;">|</span>
            <span style="white-space: nowrap;"><strong>Age:</strong> {{ $prescription->patient->age_formatted }}</span>
        @endif
        @if($prescription->patient && $prescription->patient->latest_weight_kg)
            <span style="margin: 0 6pt;">|</span>
            <span style="white-space: nowrap;"><strong>Weight:</strong> {{ $prescription->patient->latest_weight_kg }} kg</span>
        @endif
        @if($prescription->patient && $prescription->patient->latest_height)
            <span style="margin: 0 6pt;">|</span>
            <span style="white-space: nowrap;"><strong>Height:</strong> {{ $prescription->patient->latest_height }} cm</span>
        @endif
    </div>

    {{-- Diagnosis --}}
    @if($prescription->diagnosis)
        <div style="position: fixed; top: {{ $diagnosisY }}pt; left: {{ $medX }}pt; right: {{ $contentRightMargin }}pt; font-size: {{ max(8, $headerFontSize - 1) }}pt; color: #333;">
            <strong>Dx:</strong> {{ $prescription->diagnosis }}
        </div>
    @endif

    {{-- Medicines overlay using fixed positioning with pt units for mPDF --}}
    @php $currentY = $medY; @endphp
    @foreach($medicines as $index => $medicine)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $medX }}pt; right: {{ $contentRightMargin }}pt; font-size: {{ $fontSize }}pt; line-height: {{ $lineSpacing }}pt; color: #000;">
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

