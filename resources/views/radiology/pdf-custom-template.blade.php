<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Radiology Request - {{ $radiologyRequest->request_number }}</title>
    <style>
        @page { margin: 0; padding: 0; }
        body { margin: 0; padding: 0; font-family: 'dejavusans', sans-serif; }
    </style>
</head>
<body>
    @if($templateImagePath)
        <div style="position: fixed; top: 0; left: 0; z-index: -1;">
            <img src="{{ $templateImagePath }}" style="width: 210mm; height: 297mm;" />
        </div>
    @endif

    @php
        $contentX = $tplSettings['content_x'];
        $contentY = $tplSettings['content_y'];
        $fontSize = $tplSettings['font_size'];
        $lineSpacing = $tplSettings['line_spacing'];
        $currentY = $contentY;
    @endphp

    {{-- Request Number --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 2 }}pt; color: #000; font-weight: bold;">
        RADIOLOGY REQUEST — {{ $radiologyRequest->request_number }}
        @if($radiologyRequest->priority === 'urgent' || $radiologyRequest->priority === 'stat')
            <span style="color: #e74c3c; font-size: {{ $fontSize }}pt;">[{{ strtoupper($radiologyRequest->priority) }}]</span>
        @endif
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Patient Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Patient:</strong> {{ $radiologyRequest->patient->first_name }} {{ $radiologyRequest->patient->last_name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ $radiologyRequest->requested_date->format('d/m/Y') }}
        @if($radiologyRequest->patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ \Carbon\Carbon::parse($radiologyRequest->patient->date_of_birth)->age }}y
        @endif
    </div>
    @php $currentY += $lineSpacing; @endphp

    {{-- Doctor --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
        <strong>Doctor:</strong> Dr. {{ $radiologyRequest->doctor->first_name }} {{ $radiologyRequest->doctor->last_name }}
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Diagnosis --}}
    @if($radiologyRequest->suspected_diagnosis)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <strong>Diagnosis:</strong> {{ $radiologyRequest->suspected_diagnosis }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    {{-- Clinical Notes --}}
    @if($radiologyRequest->clinical_notes)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Clinical Notes:</strong> {{ $radiologyRequest->clinical_notes }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    @php $currentY += $lineSpacing * 0.5; @endphp

    {{-- Tests --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 1 }}pt; color: #000; font-weight: bold;">
        Tests Required ({{ $radiologyRequest->tests->count() }}):
    </div>
    @php $currentY += $lineSpacing; @endphp

    @foreach($radiologyRequest->tests as $index => $test)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <b>{{ $index + 1 }}. {{ $test->test_name_display ?? $test->test_name }}</b>
            @if($test->urgent) <span style="color: #e74c3c;">[URGENT]</span> @endif
            @if($test->with_contrast) <span style="color: #f39c12;">[CONTRAST]</span> @endif
        </div>
        @php $currentY += $lineSpacing; @endphp

        @if($test->clinical_indication || $test->instructions)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX + 15 }}pt; font-size: {{ max(7, $fontSize - 2) }}pt; color: #333;">
                @if($test->clinical_indication)Indication: {{ $test->clinical_indication }} @endif
                @if($test->instructions)| Instructions: {{ $test->instructions }} @endif
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endif
    @endforeach

    {{-- Notes --}}
    @if($radiologyRequest->notes)
        @php
            $contentYBottom = $tplSettings['content_y_bottom'] ?? 60;
            $contentXRight = $tplSettings['content_x_right'] ?? 40;
        @endphp
        <div style="position: fixed; bottom: {{ $contentYBottom }}pt; right: {{ $contentXRight }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Notes:</strong> {{ $radiologyRequest->notes }}
        </div>
    @endif
</body>
</html>

