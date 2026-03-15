<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Request #{{ $labRequest->request_number }}</title>
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

    {{-- Title & Request Number --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 2 }}pt; color: #000; font-weight: bold;">
        LAB REQUEST — {{ $labRequest->request_number }}
        @if($labRequest->priority === 'urgent')
            <span style="color: #e74c3c;">[URGENT]</span>
        @endif
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Patient Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Patient:</strong> {{ $labRequest->patient->first_name }} {{ $labRequest->patient->last_name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ $labRequest->created_at->format('d/m/Y') }}
        @if($labRequest->patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ \Carbon\Carbon::parse($labRequest->patient->date_of_birth)->age }}y
        @endif
    </div>
    @php $currentY += $lineSpacing; @endphp

    {{-- Doctor --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
        <strong>Doctor:</strong> Dr. {{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Clinical Notes --}}
    @if($labRequest->clinical_notes)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Clinical Notes:</strong> {{ $labRequest->clinical_notes }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    @php $currentY += $lineSpacing * 0.5; @endphp

    {{-- Tests --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 1 }}pt; color: #000; font-weight: bold;">
        Tests Requested ({{ $labRequest->tests->count() }}):
    </div>
    @php $currentY += $lineSpacing; @endphp

    @foreach($labRequest->tests as $index => $test)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <b>{{ $index + 1 }}. {{ $test->test_name }}</b>
        </div>
        @php $currentY += $lineSpacing; @endphp

        @if($test->instructions)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX + 15 }}pt; font-size: {{ max(7, $fontSize - 2) }}pt; color: #333;">
                <em>{{ $test->instructions }}</em>
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endif
    @endforeach

    {{-- Notes & Lab Info at bottom --}}
    @if($labRequest->notes || $labRequest->lab_name)
        @php
            $contentYBottom = $tplSettings['content_y_bottom'] ?? 60;
            $contentXRight = $tplSettings['content_x_right'] ?? 40;
        @endphp
        @if($labRequest->notes)
            <div style="position: fixed; bottom: {{ $contentYBottom + ($labRequest->lab_name ? $lineSpacing : 0) }}pt; right: {{ $contentXRight }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
                <strong>Notes:</strong> {{ $labRequest->notes }}
            </div>
        @endif
        @if($labRequest->lab_name)
            <div style="position: fixed; bottom: {{ $contentYBottom }}pt; right: {{ $contentXRight }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
                <strong>Lab:</strong> {{ $labRequest->lab_name }}
                @if($labRequest->lab_phone) | {{ $labRequest->lab_phone }} @endif
            </div>
        @endif
    @endif
</body>
</html>

