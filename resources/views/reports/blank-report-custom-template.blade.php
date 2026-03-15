<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Report - {{ $patient->full_name }}</title>
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

    {{-- Report Title --}}
    @if(isset($report_title) && $report_title)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 4 }}pt; color: #000; font-weight: bold; text-align: center; width: {{ 595 - $contentX * 2 }}pt;">
            {{ $report_title }}
        </div>
        @php $currentY += $lineSpacing * 2; @endphp
    @endif

    {{-- Patient Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Patient:</strong> {{ $patient->full_name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ isset($generated_date) ? $generated_date->format('d/m/Y') : now()->format('d/m/Y') }}
        @if($patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}y
        @endif
        @if($patient->gender)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Gender:</strong> {{ ucfirst($patient->gender) }}
        @endif
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Doctor Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
        <strong>Doctor:</strong> {{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }}
        @if($doctor->specialization)
            &nbsp;—&nbsp;{{ $doctor->specialization }}
        @endif
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Notes Content --}}
    @if(isset($notes) && !empty($notes))
        @php
            $noteLines = explode("\n", $notes);
            $contentYBottom = $tplSettings['content_y_bottom'] ?? 60;
            $contentXRight = $tplSettings['content_x_right'] ?? 40;
        @endphp
        @foreach($noteLines as $line)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; right: {{ $contentXRight }}pt; font-size: {{ $fontSize }}pt; line-height: {{ $lineSpacing }}pt; color: #000;">
                {{ $line }}
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endforeach
    @endif
</body>
</html>

