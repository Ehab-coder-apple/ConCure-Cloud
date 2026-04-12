@extends('layouts.app')

@push('styles')
<style>
@media print {
    /* Hide all unnecessary elements */
    .sidebar,
    .navbar,
    nav.breadcrumb,
    .btn,
    button,
    .no-print,
    #sidebar,
    .topbar,
    .app-header,
    .app-sidebar {
        display: none !important;
        visibility: hidden !important;
    }

    /* Force full page layout */
    html, body {
        width: 100% !important;
        height: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        overflow: visible !important;
    }

    body {
        padding: 8mm 10mm !important;
    }

    /* Reset container widths */
    .container-fluid {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Print-only header - compact for portrait */
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 10px;
        border-bottom: 2px solid #000;
        padding-bottom: 8px;
    }

    .print-header h1 {
        font-size: 24px !important;
        font-weight: bold !important;
        margin: 0 0 5px 0 !important;
        color: #000 !important;
    }

    .print-header p {
        font-size: 11px !important;
        margin: 0 !important;
        color: #333 !important;
        line-height: 1.3 !important;
    }

    .d-none {
        display: block !important;
    }

    /* Screen-only title */
    .screen-title {
        display: none !important;
    }

    /* Card styling - compact for portrait */
    .card {
        border: 1px solid #333 !important;
        margin-bottom: 8px !important;
        page-break-inside: avoid;
        box-shadow: none !important;
        background: white !important;
    }

    .card-header {
        padding: 8px 12px !important;
        border-bottom: 1px solid #333 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    .card-header h5 {
        font-size: 14px !important;
        font-weight: bold !important;
        margin: 0 !important;
    }

    .card-header.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .card-header.bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .card-body {
        padding: 10px !important;
        background: white !important;
    }

    /* Audiogram row - Stack vertically for portrait */
    .row {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
    }

    .col-md-6 {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        float: none !important;
        padding: 0 !important;
        margin-bottom: 10px !important;
    }

    /* Optimize chart size for portrait */
    canvas {
        width: 100% !important;
        max-width: 100% !important;
        height: 280px !important;
        display: block !important;
    }

    /* Text content sizing - compact */
    p {
        font-size: 11px !important;
        line-height: 1.3 !important;
        color: #000 !important;
        margin: 3px 0 !important;
    }

    strong {
        font-weight: bold !important;
        color: #000 !important;
    }

    h5 {
        font-size: 14px !important;
        color: #000 !important;
        margin: 0 0 5px 0 !important;
    }

    h6 {
        font-size: 12px !important;
        font-weight: bold !important;
        color: #000 !important;
        margin: 5px 0 3px 0 !important;
    }

    /* Badge styling - compact */
    .badge {
        border: 1px solid #000 !important;
        padding: 3px 8px !important;
        font-size: 10px !important;
        font-weight: bold !important;
        display: inline-block !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .badge.bg-success {
        background-color: #198754 !important;
        color: white !important;
    }

    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    /* Interpretation badges under charts */
    .text-center {
        text-align: center !important;
        margin-top: 5px !important;
    }

    /* Page settings - PORTRAIT mode */
    @page {
        size: A4 portrait;
        margin: 10mm 10mm;
    }

    /* Prevent orphaned content */
    h5, h6 {
        page-break-after: avoid !important;
    }

    .card {
        page-break-inside: avoid !important;
    }

    /* Force exact color printing */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Hide links */
    a[href]:after {
        content: none !important;
    }

    /* Hide the Test Information card in print - info is in header */
    .test-info-card {
        display: none !important;
    }

    /* Force page break before page 2 content */
    .page-2-content {
        page-break-before: always !important;
    }

    /* Ensure audiogram section stays on page 1 */
    .audiogram-section {
        page-break-after: auto !important;
        page-break-inside: avoid !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Print-only header -->
    <div class="print-header d-none">
        <h1>{{ __('AUDIOGRAM REPORT') }}</h1>
        <p>
            <strong>{{ Auth::user()->clinic->name ?? 'ConCure Clinic Management System' }}</strong><br>
            {{ __('Pure Tone Audiometry Test') }} | {{ __('Test Date') }}: {{ $audiometryTest->test_date->format('F d, Y') }}<br>
            {{ __('Patient') }}: {{ $audiometryTest->patient->full_name }} ({{ $audiometryTest->patient->patient_id }})<br>
            {{ __('Test Type') }}: {{ $audiometryTest->test_type_display }} | {{ __('Performed By') }}: {{ $audiometryTest->performer->full_name }}
        </p>
    </div>

    <div class="row mb-4 no-print screen-title">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-chart-line me-2"></i>{{ __('Audiogram Report') }}</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            @if($audiometryTest->entRecord)
                            <li class="breadcrumb-item"><a href="{{ route('ent.index') }}">{{ __('ENT Records') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('ent.show', $audiometryTest->entRecord) }}">{{ __('ENT Record') }}</a></li>
                            @else
                            <li class="breadcrumb-item"><a href="{{ route('patients.show', $audiometryTest->patient) }}">{{ __('Patient') }}</a></li>
                            @endif
                            <li class="breadcrumb-item active">{{ __('Audiogram') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print me-1"></i>{{ __('Print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient & Test Information - Hidden in print, info is in header -->
    <div class="card mb-3 test-info-card">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Test Information') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>{{ __('Patient') }}:</strong><br>
                        <a href="{{ route('patients.show', $audiometryTest->patient) }}">
                            {{ $audiometryTest->patient->full_name }}
                        </a>
                        <br><small class="text-muted">{{ $audiometryTest->patient->patient_id }}</small>
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong>{{ __('Test Date') }}:</strong><br>{{ $audiometryTest->test_date->format('Y-m-d') }}</p>
                    <p><strong>{{ __('Test Type') }}:</strong><br>{{ $audiometryTest->test_type_display }}</p>
                </div>
                <div class="col-md-4">
                    <p><strong>{{ __('Performed By') }}:</strong><br>{{ $audiometryTest->performer->full_name }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Audiogram Charts - Page 1 -->
    <div class="row audiogram-section">
        <!-- Right Ear Audiogram -->
        @if($audiometryTest->right_ear_data)
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-ear-listen me-2"></i>{{ __('Right Ear Audiogram') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="rightEarChart" width="400" height="400"></canvas>
                    @if($audiometryTest->right_interpretation)
                    <div class="mt-3 text-center">
                        <strong>{{ __('Interpretation') }}:</strong>
                        <span class="badge {{ $audiometryTest->right_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }} ms-2">
                            {{ $audiometryTest->right_interpretation_display }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Left Ear Audiogram -->
        @if($audiometryTest->left_ear_data)
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-ear-listen me-2"></i>{{ __('Left Ear Audiogram') }}</h5>
                </div>
                <div class="card-body">
                    <canvas id="leftEarChart" width="400" height="400"></canvas>
                    @if($audiometryTest->left_interpretation)
                    <div class="mt-3 text-center">
                        <strong>{{ __('Interpretation') }}:</strong>
                        <span class="badge {{ $audiometryTest->left_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }} ms-2">
                            {{ $audiometryTest->left_interpretation_display }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Page 2: Speech Audiometry Results -->
    @if($audiometryTest->right_srt || $audiometryTest->left_srt || $audiometryTest->right_wrs || $audiometryTest->left_wrs)
    <div class="card mb-3 page-2-content">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Speech Audiometry Results') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if($audiometryTest->right_srt || $audiometryTest->right_wrs)
                <div class="col-md-6">
                    <h6 class="text-danger">{{ __('Right Ear') }}</h6>
                    @if($audiometryTest->right_srt)
                    <p><strong>SRT:</strong> {{ $audiometryTest->right_srt }} dB</p>
                    @endif
                    @if($audiometryTest->right_wrs)
                    <p><strong>WRS:</strong> {{ $audiometryTest->right_wrs }}%</p>
                    @endif
                </div>
                @endif

                @if($audiometryTest->left_srt || $audiometryTest->left_wrs)
                <div class="col-md-6">
                    <h6 class="text-primary">{{ __('Left Ear') }}</h6>
                    @if($audiometryTest->left_srt)
                    <p><strong>SRT:</strong> {{ $audiometryTest->left_srt }} dB</p>
                    @endif
                    @if($audiometryTest->left_wrs)
                    <p><strong>WRS:</strong> {{ $audiometryTest->left_wrs }}%</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Tympanometry Results -->
    @if($audiometryTest->right_tympanometry || $audiometryTest->left_tympanometry)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Tympanometry Results') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if($audiometryTest->right_tympanometry)
                <div class="col-md-6">
                    <p><strong class="text-danger">{{ __('Right Ear') }}:</strong> {{ $audiometryTest->right_tympanometry }}</p>
                </div>
                @endif
                @if($audiometryTest->left_tympanometry)
                <div class="col-md-6">
                    <p><strong class="text-primary">{{ __('Left Ear') }}:</strong> {{ $audiometryTest->left_tympanometry }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Clinical Interpretation -->
    @if($audiometryTest->right_interpretation || $audiometryTest->left_interpretation)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Clinical Interpretation') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if($audiometryTest->right_interpretation)
                <div class="col-md-6">
                    <h6 class="text-danger">{{ __('Right Ear') }}</h6>
                    <p>
                        <span class="badge {{ $audiometryTest->right_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                            {{ $audiometryTest->right_interpretation_display }}
                        </span>
                    </p>
                </div>
                @endif

                @if($audiometryTest->left_interpretation)
                <div class="col-md-6">
                    <h6 class="text-primary">{{ __('Left Ear') }}</h6>
                    <p>
                        <span class="badge {{ $audiometryTest->left_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                            {{ $audiometryTest->left_interpretation_display }}
                        </span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Notes & Recommendations -->
    @if($audiometryTest->notes || $audiometryTest->recommendations)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Notes & Recommendations') }}</h5>
        </div>
        <div class="card-body">
            @if($audiometryTest->notes)
            <p><strong>{{ __('Test Notes') }}:</strong><br>{{ $audiometryTest->notes }}</p>
            @endif

            @if($audiometryTest->recommendations)
            <p><strong>{{ __('Recommendations') }}:</strong><br>{{ $audiometryTest->recommendations }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="mb-3">
        @if($audiometryTest->entRecord)
        <a href="{{ route('ent.show', $audiometryTest->entRecord) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Back to ENT Record') }}
        </a>
        @else
        <a href="{{ route('patients.show', $audiometryTest->patient) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Back to Patient') }}
        </a>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data
    const frequencies = [250, 500, 1000, 2000, 3000, 4000, 6000, 8000];
    const rightEarData = @json($audiometryTest->right_ear_data ?? []);
    const leftEarData = @json($audiometryTest->left_ear_data ?? []);

    // Convert data to arrays
    const rightValues = frequencies.map(freq => {
        const val = rightEarData[freq];
        return val !== null && val !== undefined ? val : null;
    });

    const leftValues = frequencies.map(freq => {
        const val = leftEarData[freq];
        return val !== null && val !== undefined ? val : null;
    });

    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                reverse: true, // Audiograms show worse hearing (higher dB) at bottom
                min: -10,
                max: 120,
                ticks: {
                    stepSize: 10,
                    callback: function(value) {
                        return value + ' dB';
                    }
                },
                title: {
                    display: true,
                    text: 'Hearing Level (dB HL)',
                    font: {
                        size: 12,
                        weight: 'bold'
                    }
                },
                grid: {
                    color: function(context) {
                        // Highlight normal hearing threshold (0-25 dB)
                        if (context.tick.value === 25) {
                            return 'rgba(0, 128, 0, 0.3)';
                        }
                        return 'rgba(0, 0, 0, 0.1)';
                    },
                    lineWidth: function(context) {
                        if (context.tick.value === 25) {
                            return 2;
                        }
                        return 1;
                    }
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Frequency (Hz)',
                    font: {
                        size: 12,
                        weight: 'bold'
                    }
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.parsed.y !== null) {
                            return context.parsed.y + ' dB HL';
                        }
                        return '';
                    }
                }
            }
        }
    };

    // Create Right Ear Chart
    const rightCtx = document.getElementById('rightEarChart');
    if (rightCtx) {
        new Chart(rightCtx, {
            type: 'line',
            data: {
                labels: frequencies.map(f => f + ' Hz'),
                datasets: [{
                    label: 'Right Ear',
                    data: rightValues,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    pointStyle: 'circle',
                    pointRadius: 7,
                    pointHoverRadius: 9,
                    borderWidth: 3,
                    tension: 0.1,
                    spanGaps: true
                }]
            },
            options: commonOptions
        });
    }

    // Create Left Ear Chart
    const leftCtx = document.getElementById('leftEarChart');
    if (leftCtx) {
        new Chart(leftCtx, {
            type: 'line',
            data: {
                labels: frequencies.map(f => f + ' Hz'),
                datasets: [{
                    label: 'Left Ear',
                    data: leftValues,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    pointStyle: 'crossRot',
                    pointRadius: 7,
                    pointHoverRadius: 9,
                    borderWidth: 3,
                    tension: 0.1,
                    spanGaps: true
                }]
            },
            options: commonOptions
        });
    }
});
</script>
@endpush
@endsection
