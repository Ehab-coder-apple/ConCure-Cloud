@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
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

    <!-- Patient & Test Information -->
    <div class="card mb-3">
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

    <!-- Audiogram Chart -->
    @if($audiometryTest->right_ear_data || $audiometryTest->left_ear_data)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Audiogram Chart') }}</h5>
        </div>
        <div class="card-body">
            <canvas id="audiogramChart" width="800" height="400"></canvas>
        </div>
    </div>
    @endif

    <!-- Speech Audiometry Results -->
    @if($audiometryTest->right_srt || $audiometryTest->left_srt || $audiometryTest->right_wrs || $audiometryTest->left_wrs)
    <div class="card mb-3">
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
    const ctx = document.getElementById('audiogramChart');
    if (!ctx) return;

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

    // Create audiogram chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: frequencies.map(f => f + ' Hz'),
            datasets: [
                {
                    label: 'Right Ear',
                    data: rightValues,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    pointStyle: 'circle',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    borderWidth: 2,
                    tension: 0.1,
                    spanGaps: true
                },
                {
                    label: 'Left Ear',
                    data: leftValues,
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    pointStyle: 'crossRot',
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    borderWidth: 2,
                    tension: 0.1,
                    spanGaps: true
                }
            ]
        },
        options: {
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
                            size: 14,
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
                            size: 14,
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
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Pure Tone Audiogram',
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y + ' dB HL';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
