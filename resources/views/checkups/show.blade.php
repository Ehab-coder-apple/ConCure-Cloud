@extends('layouts.app')

@section('page-title', __('Checkup Details') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-heartbeat text-danger"></i>
                        {{ __('Checkup Details') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient:') }} {{ $patient->full_name }} ({{ $patient->patient_id }}) | 
                        {{ __('Date:') }} {{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y g:i A') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('checkups.index', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Checkups') }}
                    </a>
                    <a href="{{ route('checkups.edit', [$patient, $checkup]) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Vital Signs -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-heartbeat text-danger me-2"></i>
                        {{ __('Vital Signs') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($checkup->weight)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-weight text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Weight') }}</small>
                                    <strong>{{ $checkup->weight }} kg</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->height)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-vertical text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Height') }}</small>
                                    <strong>{{ $checkup->height }} cm</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->bmi)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calculator text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('BMI') }}</small>
                                    <strong>{{ $checkup->bmi }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->blood_pressure)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-tint text-danger me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Blood Pressure') }}</small>
                                    <strong>{{ $checkup->blood_pressure }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->heart_rate)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-heartbeat text-danger me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Heart Rate') }}</small>
                                    <strong>{{ $checkup->heart_rate }} bpm</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->temperature)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-thermometer-half text-warning me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Temperature') }}</small>
                                    <strong>{{ $checkup->temperature }}°C</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Measurements -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line text-info me-2"></i>
                        {{ __('Additional Measurements') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($checkup->respiratory_rate)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lungs text-info me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Respiratory Rate') }}</small>
                                    <strong>{{ $checkup->respiratory_rate }} /min</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($checkup->blood_sugar)
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-vial text-success me-2"></i>
                                <div>
                                    <small class="text-muted d-block">{{ __('Blood Sugar') }}</small>
                                    <strong>{{ $checkup->blood_sugar }} mg/dL</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if(!$checkup->respiratory_rate && !$checkup->blood_sugar)
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('No additional measurements recorded') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Clinical Notes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-notes-medical text-success me-2"></i>
                        {{ __('Clinical Notes') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($checkup->symptoms)
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">{{ __('Symptoms') }}</h6>
                            <p class="mb-0">{{ $checkup->symptoms }}</p>
                        </div>
                        @endif
                        
                        @if($checkup->notes)
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">{{ __('Clinical Notes') }}</h6>
                            <p class="mb-0">{{ $checkup->notes }}</p>
                        </div>
                        @endif
                        
                        @if($checkup->recommendations)
                        <div class="col-md-4 mb-3">
                            <h6 class="text-primary">{{ __('Recommendations') }}</h6>
                            <p class="mb-0">{{ $checkup->recommendations }}</p>
                        </div>
                        @endif
                    </div>
                    
                    @if(!$checkup->symptoms && !$checkup->notes && !$checkup->recommendations)
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('No clinical notes recorded') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Checkup Metadata -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle text-secondary me-2"></i>
                        {{ __('Checkup Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Recorded By') }}</small>
                            <strong>{{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Checkup Date') }}</small>
                            <strong>{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y g:i A') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">{{ __('Last Updated') }}</small>
                            <strong>{{ \Carbon\Carbon::parse($checkup->updated_at)->format('M d, Y g:i A') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
