@extends('layouts.app')

@section('title', __('Pediatric Growth Chart') . ' - ' . $patient->full_name)

@push('styles')
<style>
@media print {
    /* Hide everything non-essential */
    .sidebar, .left-side-menu, .navbar, .topbar, .footer, .overlay,
    .btn, button, a.btn, .form-select, .form-check,
    .col-lg-4, .alert-dismissible, .no-print,
    .page-header-row, .patient-info-row, .lbw-banner-row,
    .card-header, .chart-legend-screen {
        display: none !important;
    }
    /* Full width for chart area */
    .col-lg-8 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    .container-fluid { padding: 0 !important; margin: 0 !important; }
    body, .content-page, .content, .wrapper { margin: 0 !important; padding: 0 !important; margin-left: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-body { padding: 0 !important; }
    .row { margin: 0 !important; }
    .mb-4 { margin-bottom: 0.5rem !important; }
    /* Show print header */
    .print-header { display: block !important; }
    /* Make chart fill the page */
    #growthChart { width: 100% !important; height: auto !important; max-height: 75vh !important; }
    .chart-wrapper { height: auto !important; }
    /* Print legend */
    .chart-legend-print { display: block !important; }
    /* Preserve chart colors */
    * { print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
    @page { size: landscape; margin: 1cm; }
}
.print-header { display: none; }
.chart-legend-print { display: none; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Print-only header -->
    <div class="print-header mb-3">
        <h3>{{ __('Pediatric Growth Chart') }} — {{ $patient->full_name }} ({{ $patient->patient_id }})</h3>
        <p>
            {{ __('Gender') }}: {{ $gender === 'boys' ? __('Male') : __('Female') }} |
            {{ __('DOB') }}: {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }} |
            {{ __('Age') }}: @if($ageMonths !== null){{ round($ageMonths, 1) }} {{ __('months') }}@else N/A @endif
            @if(($isPreterm ?? false) || ($isLBW ?? false))
                | {{ __('Birth Weight') }}: {{ $patient->birth_weight ?? 'N/A' }}g
                | {{ __('Gestational Age') }}: {{ $patient->gestational_age_weeks ?? 'N/A' }} {{ __('weeks') }}
            @endif
        </p>
        <hr>
    </div>
    <!-- Header -->
    <div class="row mb-4 page-header-row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-baby me-2 text-success"></i>
                        {{ __('Pediatric Growth Chart') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong>
                        ({{ $patient->patient_id }})
                        <span class="badge bg-{{ $gender === 'boys' ? 'primary' : 'danger' }} ms-2">
                            <i class="fas fa-{{ $gender === 'boys' ? 'mars' : 'venus' }} me-1"></i>
                            {{ $gender === 'boys' ? __('Boy') : __('Girl') }}
                        </span>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button onclick="printGrowthChart()" class="btn btn-outline-primary">
                        <i class="fas fa-print me-1"></i>{{ __('Print') }}
                    </button>
                    <button onclick="exportGrowthChartPDF()" class="btn btn-outline-success">
                        <i class="fas fa-file-pdf me-1"></i>{{ __('Export PDF') }}
                    </button>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back to Patient') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Info -->
    <div class="row mb-4 patient-info-row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Date of Birth') }}</small>
                            <strong>{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Age') }}</small>
                            <strong>
                                @if($ageMonths !== null)
                                    @if($ageMonths < 24)
                                        {{ round($ageMonths, 1) }} {{ __('months') }}
                                    @else
                                        {{ floor($ageMonths / 12) }} {{ __('years') }}, {{ round($ageMonths % 12) }} {{ __('months') }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Birth Weight') }}</small>
                            <strong>{{ $patient->birth_weight ? $patient->birth_weight . ' g' : 'N/A' }}</strong>
                            @if($patient->is_low_birth_weight)
                                <span class="badge bg-warning text-dark ms-1">LBW</span>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Gestational Age') }}</small>
                            <strong>{{ $patient->gestational_age_weeks ? $patient->gestational_age_weeks . ' wks' : 'N/A' }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Measurements') }}</small>
                            <strong>{{ $measurements->count() }}</strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted d-block">{{ __('Latest') }}</small>
                            <strong>{{ $measurements->last()?->measurement_date?->format('M d, Y') ?? 'None' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Birth Weight / Preterm Info Banner --}}
    @if($isPreterm || $isLBW)
    <div class="row mb-4 lbw-banner-row">
        <div class="col-md-12">
            <div class="card border-warning shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 text-warning">
                                    @if($isLBW && $isPreterm)
                                        {{ __('Low Birth Weight & Preterm Infant') }}
                                    @elseif($isLBW)
                                        {{ __('Low Birth Weight Infant') }}
                                    @else
                                        {{ __('Preterm Infant') }}
                                    @endif
                                </h6>
                                <div class="d-flex gap-4 text-sm">
                                    @if($patient->birth_weight)
                                        <span><strong>{{ __('Birth Weight') }}:</strong> {{ $patient->birth_weight }} g</span>
                                    @endif
                                    @if($patient->gestational_age_weeks)
                                        <span><strong>{{ __('Gestational Age') }}:</strong> {{ $patient->gestational_age_weeks }} {{ __('weeks') }}</span>
                                    @endif
                                    @if($correctionMonths)
                                        <span><strong>{{ __('Age Correction') }}:</strong> {{ round($correctionMonths, 1) }} {{ __('months') }}</span>
                                    @endif
                                    @if($correctedAgeMonths !== null && $ageMonths !== null)
                                        <span><strong>{{ __('Corrected Age') }}:</strong>
                                            @if($correctedAgeMonths < 24)
                                                {{ round($correctedAgeMonths, 1) }} {{ __('months') }}
                                            @else
                                                {{ floor($correctedAgeMonths / 12) }} {{ __('yrs') }}, {{ round($correctedAgeMonths - floor($correctedAgeMonths / 12) * 12) }} {{ __('mo') }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="useCorrectedAge"
                                   {{ ($correctedAgeMonths !== null && $ageMonths <= 24) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="useCorrectedAge">
                                {{ __('Use Corrected Age') }}
                            </label>
                        </div>
                    </div>
                    <small class="text-muted mt-1 d-block">
                        {{ __('Corrected age adjusts for prematurity and is recommended for growth assessment until 24 months of chronological age.') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Chart Area -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>{{ __('Growth Chart') }}</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <select id="chartType" class="form-select form-select-sm" style="width: auto;">
                                <option value="weight_for_age">{{ __('Weight-for-Age') }}</option>
                                <option value="height_for_age">{{ __('Height-for-Age') }}</option>
                                <option value="head_circumference_for_age">{{ __('Head Circumference') }}</option>
                                <option value="bmi_for_age">{{ __('BMI-for-Age') }}</option>
                            </select>
                            <select id="ageRange" class="form-select form-select-sm" style="width: auto;">
                                <option value="0-24m">{{ __('0-24 months') }}</option>
                                <option value="2-5y">{{ __('2-5 years') }}</option>
                                <option value="5-20y">{{ __('5-20 years') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-wrapper" style="position: relative; height: 450px;">
                        <canvas id="growthChart"></canvas>
                    </div>
                    <div class="mt-3 text-center chart-legend-screen">
                        <small class="text-muted">
                            <span class="me-3"><span style="color:#e74c3c;">━━</span> P3 / P97</span>
                            <span class="me-3"><span style="color:#e67e22;">╌╌</span> P15 / P85</span>
                            <span class="me-3"><span style="color:#27ae60;">━━</span> P50 (Median)</span>
                            <span><span style="color:#2980b9;">●━●</span> {{ __('Patient') }}</span>
                        </small>
                    </div>
                    <div class="mt-2 text-center chart-legend-print">
                        <small>
                            <span class="me-3"><span style="color:#e74c3c;">━━</span> P3 / P97</span>
                            <span class="me-3"><span style="color:#e67e22;">╌╌</span> P15 / P85</span>
                            <span class="me-3"><span style="color:#27ae60;">━━</span> P50 (Median)</span>
                            <span><span style="color:#2980b9;">●━●</span> {{ __('Patient') }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Add Measurement + History -->
        <div class="col-lg-4 mb-4">
            <!-- Add Measurement Form -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>{{ __('Record Measurement') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('pediatric.growth-chart.store', $patient) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="measurement_date" class="form-control @error('measurement_date') is-invalid @enderror"
                                   value="{{ old('measurement_date', date('Y-m-d')) }}" required>
                            @error('measurement_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Weight (kg)') }}</label>
                            <input type="number" name="weight_kg" step="0.001" min="0" max="300"
                                   class="form-control @error('weight_kg') is-invalid @enderror"
                                   value="{{ old('weight_kg') }}" placeholder="e.g. 3.250">
                            @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Length/Height (cm)') }}</label>
                            <input type="number" name="length_height_cm" step="0.01" min="0" max="250"
                                   class="form-control @error('length_height_cm') is-invalid @enderror"
                                   value="{{ old('length_height_cm') }}" placeholder="e.g. 50.00">
                            @error('length_height_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Head Circumference (cm)') }}</label>
                            <input type="number" name="head_circumference_cm" step="0.01" min="0" max="80"
                                   class="form-control @error('head_circumference_cm') is-invalid @enderror"
                                   value="{{ old('head_circumference_cm') }}" placeholder="e.g. 34.50">
                            @error('head_circumference_cm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Optional notes...') }}">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-1"></i>{{ __('Save Measurement') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Measurement History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-history me-2 text-info"></i>{{ __('Measurement History') }}</h6>
                </div>
                <div class="card-body p-0">
                    @if($measurements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Wt') }}</th>
                                        <th>{{ __('Ht') }}</th>
                                        <th>{{ __('HC') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($measurements->reverse() as $m)
                                    <tr>
                                        <td><small>{{ $m->measurement_date->format('M d, Y') }}</small></td>
                                        <td><small>{{ $m->weight_kg ? number_format($m->weight_kg, 1) . ' kg' : '-' }}</small></td>
                                        <td><small>{{ $m->length_height_cm ? number_format($m->length_height_cm, 1) . ' cm' : '-' }}</small></td>
                                        <td><small>{{ $m->head_circumference_cm ? number_format($m->head_circumference_cm, 1) . ' cm' : '-' }}</small></td>
                                        <td>
                                            <form action="{{ route('pediatric.growth-chart.destroy', [$patient, $m]) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('Delete this measurement?') }}');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-link btn-sm text-danger p-0">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-ruler-combined fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">{{ __('No measurements recorded yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Reference Sources -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-book me-2 text-secondary"></i>{{ __('References & Data Sources') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-success mb-2"><i class="fas fa-globe me-1"></i> {{ __('WHO Child Growth Standards') }}</h6>
                            <ul class="list-unstyled small text-muted mb-3">
                                <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> {{ __('Weight-for-Age (0–5 years)') }}</li>
                                <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> {{ __('Length/Height-for-Age (0–5 years)') }}</li>
                                <li class="mb-1"><i class="fas fa-check-circle text-success me-1"></i> {{ __('Head Circumference-for-Age (0–36 months)') }}</li>
                            </ul>
                            <p class="small text-muted mb-1">
                                {{ __('WHO Multicentre Growth Reference Study Group. WHO Child Growth Standards: Length/height-for-age, weight-for-age, weight-for-length, weight-for-height and body mass index-for-age: Methods and development.') }}
                            </p>
                            <p class="small text-muted">
                                <i class="fas fa-external-link-alt me-1"></i>
                                <a href="https://www.who.int/tools/child-growth-standards" target="_blank" class="text-decoration-none">
                                    {{ __('WHO Child Growth Standards') }}
                                </a>
                                <span class="text-muted ms-1">(Geneva: World Health Organization, 2006)</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-primary mb-2"><i class="fas fa-chart-bar me-1"></i> {{ __('CDC Growth Charts') }}</h6>
                            <ul class="list-unstyled small text-muted mb-3">
                                <li class="mb-1"><i class="fas fa-check-circle text-primary me-1"></i> {{ __('BMI-for-Age (2–20 years)') }}</li>
                                <li class="mb-1"><i class="fas fa-check-circle text-primary me-1"></i> {{ __('Weight-for-Age (5–20 years)') }}</li>
                                <li class="mb-1"><i class="fas fa-check-circle text-primary me-1"></i> {{ __('Height-for-Age (5–20 years)') }}</li>
                            </ul>
                            <p class="small text-muted mb-1">
                                {{ __('Kuczmarski RJ, Ogden CL, Guo SS, et al. 2000 CDC Growth Charts for the United States: Methods and development. National Center for Health Statistics.') }}
                            </p>
                            <p class="small text-muted">
                                <i class="fas fa-external-link-alt me-1"></i>
                                <a href="https://www.cdc.gov/growthcharts/" target="_blank" class="text-decoration-none">
                                    {{ __('CDC Growth Charts') }}
                                </a>
                                <span class="text-muted ms-1">(Vital Health Stat 11(246), 2002)</span>
                            </p>
                        </div>
                    </div>
                    <hr class="my-2">
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('Percentile curves shown: P3, P15, P50 (Median), P85, P97. Corrected age is applied for preterm infants (< 37 weeks gestational age) up to 24 months chronological age per WHO/AAP recommendations.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientId = {{ $patient->id }};
    const gender = '{{ $gender }}';
    const chartConfig = @json($chartConfig);
    let chart = null;

    const ctx = document.getElementById('growthChart').getContext('2d');
    const chartTypeSelect = document.getElementById('chartType');
    const ageRangeSelect = document.getElementById('ageRange');
    const correctedAgeToggle = document.getElementById('useCorrectedAge');

    // Corrected age support
    const correctionMonths = {{ $correctionMonths ?? 'null' }};
    const chronologicalAgeMonths = {{ $ageMonths ? round($ageMonths, 2) : 'null' }};

    // Patient measurement data
    const measurements = @json($measurements);

    function getYAxisLabel(chartType) {
        const labels = {
            'weight_for_age': '{{ __("Weight (kg)") }}',
            'height_for_age': '{{ __("Height (cm)") }}',
            'head_circumference_for_age': '{{ __("Head Circumference (cm)") }}',
            'bmi_for_age': '{{ __("BMI (kg/m²)") }}',
        };
        return labels[chartType] || '';
    }

    function getAvailableRanges(chartType) {
        const data = chartConfig[chartType];
        if (!data || !data[gender]) return [];
        return Object.keys(data[gender]);
    }

    function updateAgeRangeOptions() {
        const chartType = chartTypeSelect.value;
        const ranges = getAvailableRanges(chartType);
        const currentRange = ageRangeSelect.value;

        ageRangeSelect.innerHTML = '';
        const rangeLabels = {
            '0-24m': '{{ __("0-24 months") }}',
            '0-36m': '{{ __("0-36 months") }}',
            '2-5y': '{{ __("2-5 years") }}',
            '5-20y': '{{ __("5-20 years") }}',
            '2-20y': '{{ __("2-20 years") }}',
        };
        ranges.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r;
            opt.textContent = rangeLabels[r] || r;
            if (r === currentRange) opt.selected = true;
            ageRangeSelect.appendChild(opt);
        });

        // If current range not available, select first
        if (!ranges.includes(currentRange) && ranges.length > 0) {
            ageRangeSelect.value = ranges[0];
        }
    }

    function buildChart() {
        const chartType = chartTypeSelect.value;
        const ageRange = ageRangeSelect.value;
        const refData = chartConfig[chartType]?.[gender]?.[ageRange] || [];
        const percentiles = chartConfig.percentiles || [3, 15, 50, 85, 97];
        const colors = chartConfig.percentile_colors || {};

        const datasets = [];

        // Percentile curves
        percentiles.forEach((p, i) => {
            datasets.push({
                label: 'P' + p,
                data: refData.map(row => ({ x: row[0], y: row[i + 1] })),
                borderColor: colors[p] || '#999',
                borderWidth: p === 50 ? 2.5 : 1.5,
                borderDash: p === 50 ? [] : [5, 3],
                fill: false,
                pointRadius: 0,
                tension: 0.4,
                order: 2,
            });
        });

        // Patient data points
        const fieldMap = {
            'weight_for_age': 'weight_kg',
            'height_for_age': 'length_height_cm',
            'head_circumference_for_age': 'head_circumference_cm',
            'bmi_for_age': 'bmi',
        };
        const field = fieldMap[chartType];
        const useCorrected = correctedAgeToggle && correctedAgeToggle.checked && correctionMonths !== null;

        const patientData = measurements
            .filter(m => m.age_months !== null && m[field] !== null && m[field] !== undefined)
            .map(m => {
                let ageX = parseFloat(m.age_months);
                // Apply corrected age: subtract correction, but only for measurements taken before 24 months chronological
                if (useCorrected && ageX <= 24) {
                    ageX = Math.max(0, ageX - correctionMonths);
                }
                return { x: ageX, y: parseFloat(m[field]) };
            });

        const patientLabel = useCorrected ? '{{ __("Patient (Corrected Age)") }}' : '{{ __("Patient") }}';

        datasets.push({
            label: patientLabel,
            data: patientData,
            borderColor: useCorrected ? '#8e44ad' : '#2980b9',
            backgroundColor: useCorrected ? '#8e44ad' : '#2980b9',
            borderWidth: 2.5,
            pointRadius: 5,
            pointHoverRadius: 7,
            fill: false,
            tension: 0.2,
            order: 1,
        });

        // Determine axis range from reference data
        const allAges = refData.map(r => r[0]);
        const minAge = Math.min(...allAges, 0);
        const maxAge = Math.max(...allAges, 24);

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: { datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'nearest', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => items[0] ? `Age: ${items[0].raw.x} months` : '',
                            label: (item) => `${item.dataset.label}: ${item.raw.y}`,
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        title: { display: true, text: '{{ __("Age (months)") }}' },
                        min: minAge,
                        max: maxAge,
                        ticks: { stepSize: ageRange === '5-20y' || ageRange === '2-20y' ? 12 : (ageRange === '2-5y' ? 6 : 3) },
                    },
                    y: {
                        title: { display: true, text: getYAxisLabel(chartType) },
                        beginAtZero: chartType === 'weight_for_age',
                    }
                }
            }
        });
    }

    chartTypeSelect.addEventListener('change', function() {
        updateAgeRangeOptions();
        buildChart();
    });
    ageRangeSelect.addEventListener('change', buildChart);

    // Corrected age toggle
    if (correctedAgeToggle) {
        correctedAgeToggle.addEventListener('change', buildChart);
    }

    // Auto-select appropriate age range based on patient age
    @if($ageMonths !== null)
        const patientAgeMonths = {{ round($ageMonths, 1) }};
        if (patientAgeMonths <= 24) {
            ageRangeSelect.value = '0-24m';
        } else if (patientAgeMonths <= 60) {
            ageRangeSelect.value = '2-5y';
        } else {
            ageRangeSelect.value = '5-20y';
        }
    @endif

    updateAgeRangeOptions();
    buildChart();
});

// Print function
// Shared function to build the printable/PDF page
function buildGrowthChartPage(autoPrint) {
    const canvas = document.getElementById('growthChart');
    if (!canvas) return;

    const imgData = canvas.toDataURL('image/png', 1.0);
    const patientName = @json($patient->full_name);
    const patientId = @json($patient->patient_id);
    const gender = '{{ $gender === "boys" ? __("Male") : __("Female") }}';
    const dob = '{{ $patient->date_of_birth ? $patient->date_of_birth->format("M d, Y") : "N/A" }}';
    const chartType = document.getElementById('chartType');
    const chartLabel = chartType.options[chartType.selectedIndex].text;
    const ageRange = document.getElementById('ageRange');
    const rangeLabel = ageRange.options[ageRange.selectedIndex].text;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
    <title>Growth Chart - ${patientName}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 12px; }
        .header h1 { color: #28a745; margin: 0 0 4px 0; font-size: 22px; }
        .header p { margin: 2px 0; font-size: 13px; color: #666; }
        .info-grid { display: flex; gap: 30px; justify-content: center; margin: 12px 0; flex-wrap: wrap; }
        .info-item { text-align: center; }
        .info-item .label { font-size: 10px; color: #999; text-transform: uppercase; }
        .info-item .value { font-size: 13px; font-weight: bold; }
        .chart-container { text-align: center; margin: 10px 0; }
        .chart-container img { max-width: 100%; height: auto; border: 1px solid #eee; border-radius: 8px; }
        .chart-title { text-align: center; font-size: 15px; font-weight: bold; color: #2980b9; margin-bottom: 8px; }
        .legend { text-align: center; margin-top: 8px; font-size: 12px; color: #666; }
        .legend span { margin: 0 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #aaa; border-top: 1px solid #eee; padding-top: 8px; }
        @media print { body { margin: 0; } @page { size: landscape; margin: 1cm; } * { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>&#x1F476; Pediatric Growth Chart</h1>
        <p><strong>${patientName}</strong> (${patientId})</p>
    </div>
    <div class="info-grid">
        <div class="info-item"><div class="label">Gender</div><div class="value">${gender}</div></div>
        <div class="info-item"><div class="label">Date of Birth</div><div class="value">${dob}</div></div>
        <div class="info-item"><div class="label">Chart Type</div><div class="value">${chartLabel}</div></div>
        <div class="info-item"><div class="label">Age Range</div><div class="value">${rangeLabel}</div></div>
        @if(($isPreterm ?? false) || ($isLBW ?? false))
        <div class="info-item"><div class="label">Birth Weight</div><div class="value">{{ $patient->birth_weight ?? 'N/A' }}g</div></div>
        <div class="info-item"><div class="label">Gestational Age</div><div class="value">{{ $patient->gestational_age_weeks ?? 'N/A' }} weeks</div></div>
        @endif
    </div>
    <div class="chart-container">
        <div class="chart-title">${chartLabel} — ${rangeLabel}</div>
        <img src="${imgData}" />
    </div>
    <div class="legend">
        <span style="color:#e74c3c;">━━</span> P3 / P97 &nbsp;
        <span style="color:#e67e22;">╌╌</span> P15 / P85 &nbsp;
        <span style="color:#27ae60;">━━</span> P50 (Median) &nbsp;
        <span style="color:#2980b9;">●━●</span> Patient
    </div>
    <div class="footer">
        Generated on ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()} — Concure Cloud
    </div>
</body>
</html>`);
    printWindow.document.close();

    if (autoPrint) {
        printWindow.onload = function() {
            setTimeout(function() { printWindow.print(); }, 500);
        };
    }
}

// Print — opens the same formatted page and auto-triggers print dialog
function printGrowthChart() {
    buildGrowthChartPage(true);
}

// Export PDF — opens the formatted page (user can Save as PDF from print dialog)
function exportGrowthChartPDF() {
    buildGrowthChartPage(true);
}
</script>
@endpush
