<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Diet Plan - {{ $dietPlan->plan_number }}</title>
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
        {{ $dietPlan->title ?? 'Nutrition Plan' }} — {{ $dietPlan->plan_number }}
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Patient Info --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
        <strong>Patient:</strong> {{ $dietPlan->patient->first_name }} {{ $dietPlan->patient->last_name }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {{ $dietPlan->created_at->format('d/m/Y') }}
        @if($dietPlan->patient->date_of_birth)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ \Carbon\Carbon::parse($dietPlan->patient->date_of_birth)->age }}y
        @endif
    </div>
    @php $currentY += $lineSpacing; @endphp

    {{-- Doctor --}}
    <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
        <strong>Doctor:</strong> Dr. {{ $dietPlan->doctor->first_name }} {{ $dietPlan->doctor->last_name }}
    </div>
    @php $currentY += $lineSpacing * 1.5; @endphp

    {{-- Goal & Duration --}}
    @if($dietPlan->goal || $dietPlan->duration_days)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize }}pt; color: #000;">
            @if($dietPlan->goal)<strong>Goal:</strong> {{ ucfirst($dietPlan->goal) }} @endif
            @if($dietPlan->duration_days) &nbsp;|&nbsp; <strong>Duration:</strong> {{ $dietPlan->duration_days }} days @endif
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    {{-- Targets --}}
    @if($dietPlan->target_calories)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Calories:</strong> {{ $dietPlan->target_calories }} kcal
            @if($dietPlan->target_protein) | <strong>Protein:</strong> {{ $dietPlan->target_protein }}g @endif
            @if($dietPlan->target_carbs) | <strong>Carbs:</strong> {{ $dietPlan->target_carbs }}g @endif
            @if($dietPlan->target_fat) | <strong>Fat:</strong> {{ $dietPlan->target_fat }}g @endif
        </div>
        @php $currentY += $lineSpacing; @endphp
    @endif

    @php $currentY += $lineSpacing * 0.5; @endphp

    {{-- Meals --}}
    @foreach($dietPlan->meals as $meal)
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ $fontSize + 1 }}pt; color: #000; font-weight: bold;">
            {{ $meal->meal_type ? ucfirst($meal->meal_type) : 'Meal' }}
            @if($meal->time) ({{ $meal->time }}) @endif
        </div>
        @php $currentY += $lineSpacing; @endphp

        @foreach($meal->foods as $mealFood)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX + 15 }}pt; font-size: {{ $fontSize }}pt; color: #000;">
                • {{ $mealFood->food->name ?? $mealFood->food_name ?? 'Food item' }}
                @if($mealFood->quantity) — {{ $mealFood->quantity }} {{ $mealFood->unit ?? '' }} @endif
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endforeach

        @if($meal->notes)
            <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX + 15 }}pt; font-size: {{ max(7, $fontSize - 2) }}pt; color: #333;">
                <em>{{ $meal->notes }}</em>
            </div>
            @php $currentY += $lineSpacing; @endphp
        @endif

        @php $currentY += $lineSpacing * 0.3; @endphp
    @endforeach

    {{-- Instructions --}}
    @if($dietPlan->instructions)
        @php $currentY += $lineSpacing * 0.5; @endphp
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Instructions:</strong> {{ $dietPlan->instructions }}
        </div>
    @endif

    {{-- Restrictions --}}
    @if($dietPlan->restrictions)
        @php $currentY += $lineSpacing; @endphp
        <div style="position: fixed; top: {{ $currentY }}pt; left: {{ $contentX }}pt; font-size: {{ max(8, $fontSize - 1) }}pt; color: #333;">
            <strong>Restrictions:</strong> {{ $dietPlan->restrictions }}
        </div>
    @endif
</body>
</html>

