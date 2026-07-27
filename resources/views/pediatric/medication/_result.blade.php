{{-- Static result partial for server-rendered calculation --}}
@php
    $safety = $calc['safety'];
    $colors = ['safe' => '#10b981', 'warning' => '#f59e0b', 'danger' => '#ef4444'];
    $alertClass = ['safe' => 'success', 'warning' => 'warning', 'danger' => 'danger'];
@endphp

<div class="row g-3 mb-3">
    <div class="col-md-4 text-center">
        <div class="border rounded p-3">
            <div class="text-muted small">{{ __('Recommended Dose') }}</div>
            <div class="h3 mb-0 fw-bold" style="color:{{ $colors[$safety['status']] }}">{{ $calc['recommended_dose_mg'] }} mg</div>
            @if($calc['dose_ml'])
            <div class="small text-muted">({{ $calc['dose_ml'] }} ml)</div>
            @endif
        </div>
    </div>
    <div class="col-md-4 text-center">
        <div class="border rounded p-3">
            <div class="text-muted small">{{ __('Safe Range') }}</div>
            <div class="h5 mb-0">{{ $calc['dose_min_mg'] }} – {{ $calc['dose_max_mg'] }} mg</div>
        </div>
    </div>
    <div class="col-md-4 text-center">
        <div class="border rounded p-3">
            <div class="text-muted small">{{ __('Frequency') }}</div>
            <div class="h5 mb-0">{{ $calc['frequency_per_day'] }}x / day</div>
            @if($calc['frequency_hours'])
            <div class="small text-muted">Every {{ $calc['frequency_hours'] }}h</div>
            @endif
        </div>
    </div>
</div>

<div class="alert alert-{{ $alertClass[$safety['status']] }} mb-0">
    <i class="fas fa-{{ $safety['status'] === 'safe' ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
    {{ $safety['message'] }}
</div>

