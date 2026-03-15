<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dental Treatment - {{ $dentalTreatment->treatment_number }}</title>
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

    {{-- Title --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 3 }}pt; color: #000; font-weight: bold;">
        DENTAL TREATMENT PLAN — {{ $dentalTreatment->treatment_number }}
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Patient Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Patient:</strong> {{ $dentalTreatment->patient->first_name }} {{ $dentalTreatment->patient->last_name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ now()->format('d/m/Y') }}
        @if($dentalTreatment->patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ \Carbon\Carbon::parse($dentalTreatment->patient->date_of_birth)->age }}y
        @endif
    </div>
    @php $currentY += $lineSpacing; @endphp

    {{-- Doctor --}}
    @if($dentalTreatment->assignedDoctor)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Doctor:</strong> Dr. {{ $dentalTreatment->assignedDoctor->first_name }} {{ $dentalTreatment->assignedDoctor->last_name }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    @php $currentY += $lineSpacing * 0.5; @endphp

    {{-- Procedure --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Procedure:</strong> {{ $dentalTreatment->procedure_name }}
    </div>
    @php $currentY += $lineSpacing; @endphp

    @if($dentalTreatment->tooth_number)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <strong>Tooth:</strong> {{ $dentalTreatment->tooth_number }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    @if($dentalTreatment->status)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <strong>Status:</strong> {{ ucfirst($dentalTreatment->status) }}
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    {{-- Description --}}
    @if($dentalTreatment->description)
        @php $currentY += $lineSpacing * 0.5; @endphp
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <strong>Description:</strong>
        </div>
        @php $currentY += $lineSpacing; @endphp
        @foreach(explode("\n", $dentalTreatment->description) as $line)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
                {{ $line }}
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endforeach
    @endif

    {{-- Cost --}}
    @if($dentalTreatment->estimated_cost)
        @php $currentY += $lineSpacing * 0.5; @endphp
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            <strong>Estimated Cost:</strong> {{ number_format($dentalTreatment->estimated_cost, 2) }}
        </div>
    @endif

    {{-- Notes --}}
    @if($dentalTreatment->notes)
        @php
            $contentYBottom = $tplSettings['content_y_bottom'] ?? 60;
            $contentXRight = $tplSettings['content_x_right'] ?? 40;
        @endphp
        <div style="position: fixed; bottom: {{ $contentYBottom }}pt; right: {{ $contentXRight }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Notes:</strong> {{ $dentalTreatment->notes }}
        </div>
    @endif
</body>
</html>

