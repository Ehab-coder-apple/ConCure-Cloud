@extends('layouts.app')

@section('title', __('Nutrition Progress Dashboard'))

@section('content')
<div class="container-fluid py-4">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-area text-success me-2"></i>{{ __('Nutrition Progress Dashboard') }}</h2>
            <p class="text-muted mb-0">{{ __('Track body composition and progress toward nutrition goals') }}</p>
        </div>
    </div>

    {{-- Patient Selector --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <form method="GET" action="{{ route('nutrition.progress.dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold"><i class="fas fa-user me-1"></i>{{ __('Select Patient') }}</label>
                    <select name="patient_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- {{ __('Choose a patient') }} --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ ($selectedPatient && $selectedPatient->id == $p->id) ? 'selected' : '' }}>
                                {{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($selectedPatient)
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMeasurementModal">
                            <i class="fas fa-plus me-1"></i>{{ __('Add Measurement') }}
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#setGoalModal">
                            <i class="fas fa-bullseye me-1"></i>{{ __('Set Goals') }}
                        </button>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if($selectedPatient)
    {{-- Patient Info Bar --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white py-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h5 class="mb-0">{{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }}</h5>
                    <small class="opacity-75">{{ $selectedPatient->patient_id }} · {{ ucfirst($selectedPatient->gender ?? 'N/A') }} · {{ $selectedPatient->age }}y</small>
                </div>
                <div class="col-md-9">
                    <div class="row text-center">
                        @php
                            $latest = $measurements->last();
                            $prev = $measurements->count() > 1 ? $measurements[$measurements->count() - 2] : null;
                        @endphp
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('Weight') }}</small>
                            <strong>{{ $latest?->weight_kg ?? $selectedPatient->weight ?? '-' }} kg</strong>
                            @if($prev && $latest && $latest->weight_kg && $prev->weight_kg)
                                @php $delta = $latest->weight_kg - $prev->weight_kg; @endphp
                                <small class="{{ $delta < 0 ? 'text-warning' : ($delta > 0 ? 'text-info' : '') }}">
                                    {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 1) }}
                                </small>
                            @endif
                        </div>
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('BMI') }}</small>
                            <strong>{{ $latest?->bmi ?? $selectedPatient->bmi ?? '-' }}</strong>
                            @if($latest && $latest->bmi)
                                <small class="badge" style="background:{{ $latest->bmi_color }}; font-size:0.65rem">{{ $latest->bmi_category }}</small>
                            @endif
                        </div>
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('Fat %') }}</small>
                            <strong>{{ $latest?->fat_percentage ?? '-' }}%</strong>
                        </div>
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('Muscle %') }}</small>
                            <strong>{{ $latest?->muscle_percentage ?? '-' }}%</strong>
                        </div>
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('WHR') }}</small>
                            <strong>{{ $latest?->waist_to_hip_ratio ?? '-' }}</strong>
                        </div>
                        <div class="col">
                            <small class="opacity-75 d-block">{{ __('Visits') }}</small>
                            <strong>{{ $measurements->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Metric Toggle Buttons --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <small class="text-muted fw-semibold me-2">{{ __('Metrics:') }}</small>
                <button class="btn btn-sm metric-toggle active" data-metric="weight" style="--c:#3b82f6"><i class="fas fa-weight me-1"></i>{{ __('Weight') }}</button>
                <button class="btn btn-sm metric-toggle active" data-metric="bmi" style="--c:#8b5cf6"><i class="fas fa-calculator me-1"></i>{{ __('BMI') }}</button>
                <button class="btn btn-sm metric-toggle" data-metric="fat_percentage" style="--c:#ef4444"><i class="fas fa-fire me-1"></i>{{ __('Fat %') }}</button>
                <button class="btn btn-sm metric-toggle" data-metric="muscle_percentage" style="--c:#10b981"><i class="fas fa-dumbbell me-1"></i>{{ __('Muscle %') }}</button>
                <button class="btn btn-sm metric-toggle" data-metric="waist_to_hip_ratio" style="--c:#f59e0b"><i class="fas fa-ruler me-1"></i>{{ __('WHR') }}</button>
                <button class="btn btn-sm metric-toggle" data-metric="visceral_fat" style="--c:#ec4899"><i class="fas fa-heartbeat me-1"></i>{{ __('Visceral Fat') }}</button>
                <button class="btn btn-sm metric-toggle" data-metric="body_water_percentage" style="--c:#06b6d4"><i class="fas fa-tint me-1"></i>{{ __('Body Water') }}</button>
                @if($goal && $goal->target_weight)
                <button class="btn btn-sm metric-toggle" data-metric="weight_to_goal" style="--c:#a855f7"><i class="fas fa-bullseye me-1"></i>{{ __('Weight→Goal %') }}</button>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Chart --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body">
            <div style="position: relative; height: 420px;">
                <canvas id="progressChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Goal Progress Cards --}}
    @if($goal)
    <div class="row g-3 mb-4">
        @php
            $goalMetrics = [
                ['label' => 'Weight', 'current' => $latest?->weight_kg, 'target' => $goal->target_weight, 'unit' => 'kg', 'icon' => 'fa-weight', 'color' => '#3b82f6'],
                ['label' => 'Fat %', 'current' => $latest?->fat_percentage, 'target' => $goal->target_fat_percentage, 'unit' => '%', 'icon' => 'fa-fire', 'color' => '#ef4444'],
                ['label' => 'Muscle %', 'current' => $latest?->muscle_percentage, 'target' => $goal->target_muscle_percentage, 'unit' => '%', 'icon' => 'fa-dumbbell', 'color' => '#10b981'],
                ['label' => 'BMI', 'current' => $latest?->bmi, 'target' => $goal->target_bmi, 'unit' => '', 'icon' => 'fa-calculator', 'color' => '#8b5cf6'],
                ['label' => 'Visceral Fat', 'current' => $latest?->visceral_fat, 'target' => $goal->target_visceral_fat, 'unit' => '', 'icon' => 'fa-heartbeat', 'color' => '#ec4899'],
                ['label' => 'Body Water', 'current' => $latest?->body_water_percentage, 'target' => $goal->target_body_water_percentage, 'unit' => '%', 'icon' => 'fa-tint', 'color' => '#06b6d4'],
            ];
        @endphp
        @foreach($goalMetrics as $gm)
            @if($gm['target'])
            @php
                $pct = ($gm['current'] && $gm['target']) ? round(min(abs($gm['current'] / $gm['target']) * 100, 150), 0) : 0;
                $diff = ($gm['current'] && $gm['target']) ? $gm['current'] - $gm['target'] : null;
            @endphp
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm text-center" style="border-radius:12px;">
                    <div class="card-body py-3 px-2">
                        <i class="fas {{ $gm['icon'] }} mb-2" style="font-size:1.3rem; color:{{ $gm['color'] }}"></i>
                        <div class="fw-semibold small">{{ $gm['label'] }}</div>
                        <div class="fs-5 fw-bold">{{ $gm['current'] ?? '-' }}{{ $gm['unit'] }}</div>
                        <div class="text-muted" style="font-size:0.72rem">{{ __('Goal') }}: {{ $gm['target'] }}{{ $gm['unit'] }}</div>
                        <div class="progress mt-2" style="height:6px;">
                            <div class="progress-bar" style="width:{{ min($pct, 100) }}%; background:{{ $gm['color'] }}"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Measurement History Table --}}
    @if($measurements->count() > 0)
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>{{ __('Measurement History') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:0.85rem">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Weight') }}</th>
                            <th>{{ __('BMI') }}</th>
                            <th>{{ __('Fat %') }}</th>
                            <th>{{ __('Muscle %') }}</th>
                            <th>{{ __('Waist') }}</th>
                            <th>{{ __('Hip') }}</th>
                            <th>{{ __('WHR') }}</th>
                            <th>{{ __('VF') }}</th>
                            <th>{{ __('Water %') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($measurements->reverse() as $m)
                        <tr>
                            <td>{{ $m->measurement_date->format('d M Y') }}</td>
                            <td>{{ $m->weight_kg ?? '-' }}</td>
                            <td><span class="badge" style="background:{{ $m->bmi_color }}">{{ $m->bmi ?? '-' }}</span></td>
                            <td>{{ $m->fat_percentage ?? '-' }}</td>
                            <td>{{ $m->muscle_percentage ?? '-' }}</td>
                            <td>{{ $m->waist_cm ?? '-' }}</td>
                            <td>{{ $m->hip_cm ?? '-' }}</td>
                            <td>{{ $m->waist_to_hip_ratio ?? '-' }}</td>
                            <td>{{ $m->visceral_fat ?? '-' }}</td>
                            <td>{{ $m->body_water_percentage ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('nutrition.progress.measurement.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger p-1" style="font-size:0.7rem"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @endif {{-- end selectedPatient --}}
</div>

@if($selectedPatient ?? false)
{{-- Add Measurement Modal --}}
<div class="modal fade" id="addMeasurementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('nutrition.progress.measurement.store') }}">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
            <div class="modal-content" style="border-radius:14px">
                <div class="modal-header border-0 bg-success bg-opacity-10">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success me-2"></i>{{ __('New Measurement') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Date') }} *</label>
                            <input type="date" name="measurement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Weight (kg)') }}</label>
                            <input type="number" step="0.01" name="weight_kg" class="form-control" placeholder="e.g. 75.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Height (cm)') }}</label>
                            <input type="number" step="0.01" name="height_cm" class="form-control" value="{{ $selectedPatient->height }}" placeholder="e.g. 170">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Fat %') }}</label>
                            <input type="number" step="0.1" name="fat_percentage" class="form-control" placeholder="e.g. 22.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Muscle %') }}</label>
                            <input type="number" step="0.1" name="muscle_percentage" class="form-control" placeholder="e.g. 35.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Waist (cm)') }}</label>
                            <input type="number" step="0.1" name="waist_cm" class="form-control" placeholder="e.g. 80">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Hip (cm)') }}</label>
                            <input type="number" step="0.1" name="hip_cm" class="form-control" placeholder="e.g. 95">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Visceral Fat') }}</label>
                            <input type="number" step="0.1" name="visceral_fat" class="form-control" placeholder="e.g. 8">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Body Water %') }}</label>
                            <input type="number" step="0.1" name="body_water_percentage" class="form-control" placeholder="e.g. 55">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Optional notes...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>{{ __('Save Measurement') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Set Goal Modal --}}
<div class="modal fade" id="setGoalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('nutrition.progress.goal.store') }}">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
            <div class="modal-content" style="border-radius:14px">
                <div class="modal-header border-0 bg-primary bg-opacity-10">
                    <h5 class="modal-title"><i class="fas fa-bullseye text-primary me-2"></i>{{ __('Set Nutrition Goals') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target Weight (kg)') }}</label>
                            <input type="number" step="0.1" name="target_weight" class="form-control" value="{{ $goal?->target_weight }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target Fat %') }}</label>
                            <input type="number" step="0.1" name="target_fat_percentage" class="form-control" value="{{ $goal?->target_fat_percentage }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target Muscle %') }}</label>
                            <input type="number" step="0.1" name="target_muscle_percentage" class="form-control" value="{{ $goal?->target_muscle_percentage }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target BMI') }}</label>
                            <input type="number" step="0.1" name="target_bmi" class="form-control" value="{{ $goal?->target_bmi }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target Visceral Fat') }}</label>
                            <input type="number" step="0.1" name="target_visceral_fat" class="form-control" value="{{ $goal?->target_visceral_fat }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Target Body Water %') }}</label>
                            <input type="number" step="0.1" name="target_body_water_percentage" class="form-control" value="{{ $goal?->target_body_water_percentage }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ $goal?->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Save Goals') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .metric-toggle {
        border: 2px solid var(--c, #6b7280);
        color: var(--c, #6b7280);
        background: transparent;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 0.78rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .metric-toggle.active {
        background: var(--c, #6b7280);
        color: #fff;
    }
    .metric-toggle:hover {
        opacity: 0.85;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if(($selectedPatient ?? false) && ($measurements ?? collect())->count() > 0)
@php
    $chartPayload = [
        'labels' => $measurements->pluck('measurement_date')->map(fn($d) => $d->format('d M'))->values(),
        'datasets' => [
            'weight' => $measurements->pluck('weight_kg')->values(),
            'bmi' => $measurements->pluck('bmi')->values(),
            'fat_percentage' => $measurements->pluck('fat_percentage')->values(),
            'muscle_percentage' => $measurements->pluck('muscle_percentage')->values(),
            'waist_to_hip_ratio' => $measurements->pluck('waist_to_hip_ratio')->values(),
            'visceral_fat' => $measurements->pluck('visceral_fat')->values(),
            'body_water_percentage' => $measurements->pluck('body_water_percentage')->values(),
        ],
    ];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartPayload);

    const metricConfig = {
        weight:              { label: '{{ __("Weight (kg)") }}',    color: '#3b82f6', yAxisID: 'y' },
        bmi:                 { label: '{{ __("BMI") }}',            color: '#8b5cf6', yAxisID: 'y1' },
        fat_percentage:      { label: '{{ __("Fat %") }}',          color: '#ef4444', yAxisID: 'y1' },
        muscle_percentage:   { label: '{{ __("Muscle %") }}',       color: '#10b981', yAxisID: 'y1' },
        waist_to_hip_ratio:  { label: '{{ __("WHR") }}',            color: '#f59e0b', yAxisID: 'y2' },
        visceral_fat:        { label: '{{ __("Visceral Fat") }}',   color: '#ec4899', yAxisID: 'y1' },
        body_water_percentage:{ label: '{{ __("Body Water %") }}',  color: '#06b6d4', yAxisID: 'y1' },
    };

    const activeMetrics = new Set(['weight', 'bmi']);

    function buildDatasets() {
        const datasets = [];
        activeMetrics.forEach(key => {
            const cfg = metricConfig[key];
            if (!cfg || !chartData.datasets[key]) return;
            datasets.push({
                label: cfg.label,
                data: chartData.datasets[key],
                borderColor: cfg.color,
                backgroundColor: cfg.color + '20',
                borderWidth: 2.5,
                pointRadius: 4,
                pointHoverRadius: 7,
                tension: 0.3,
                fill: false,
                yAxisID: cfg.yAxisID,
            });
        });
        return datasets;
    }

    const ctx = document.getElementById('progressChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: { labels: chartData.labels, datasets: buildDatasets() },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, padding: 15 } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 },
                    callbacks: {
                        afterBody: function(items) {
                            const idx = items[0]?.dataIndex;
                            if (idx > 0) {
                                return items.map(i => {
                                    const prev = i.dataset.data[idx - 1];
                                    const curr = i.dataset.data[idx];
                                    if (prev != null && curr != null) {
                                        const d = (curr - prev).toFixed(2);
                                        return `Δ ${i.dataset.label}: ${d > 0 ? '+' : ''}${d}`;
                                    }
                                    return '';
                                }).filter(Boolean);
                            }
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y:  { type: 'linear', position: 'left', title: { display: true, text: '{{ __("Weight (kg)") }}' }, display: activeMetrics.has('weight') },
                y1: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: '{{ __("% / Index") }}' }, display: true },
                y2: { type: 'linear', position: 'right', grid: { drawOnChartArea: false }, display: false, title: { display: true, text: 'Ratio' } },
            },
        },
    });

    // Metric toggles
    document.querySelectorAll('.metric-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const metric = this.dataset.metric;
            this.classList.toggle('active');
            if (activeMetrics.has(metric)) activeMetrics.delete(metric);
            else activeMetrics.add(metric);

            chart.data.datasets = buildDatasets();
            chart.options.scales.y.display = activeMetrics.has('weight');
            chart.options.scales.y2.display = activeMetrics.has('waist_to_hip_ratio');
            chart.update();
        });
    });
});
</script>
@endif
@endpush
