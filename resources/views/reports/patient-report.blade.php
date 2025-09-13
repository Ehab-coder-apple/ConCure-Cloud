@extends('layouts.app')

@section('page-title', __('Patient Report') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <!-- Report Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-medical text-primary"></i>
                        {{ __('Patient Medical Report') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Generated on') }}: {{ now()->format('M d, Y g:i A') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                    <a href="{{ route('patient.report', $patient) }}?format=pdf&date_from={{ $dateFrom }}&date_to={{ $dateTo }}" 
                       class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i>
                        {{ __('Download PDF') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-info me-2"></i>
                        {{ __('Patient Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ __('Patient ID') }}:</strong><br>
                            {{ $patient->patient_id }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Full Name') }}:</strong><br>
                            {{ $patient->full_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Date of Birth') }}:</strong><br>
                            {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}
                            @if($patient->date_of_birth)
                                ({{ $patient->age }} {{ __('years old') }})
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Gender') }}:</strong><br>
                            {{ ucfirst($patient->gender ?? 'N/A') }}
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <strong>{{ __('Phone') }}:</strong><br>
                            {{ $patient->phone ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Email') }}:</strong><br>
                            {{ $patient->email ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Address') }}:</strong><br>
                            {{ $patient->address ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-success me-2"></i>
                        {{ __('Report Summary') }}
                        <small class="text-muted">({{ Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('M d, Y') }})</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-primary">{{ $reportData['summary']['total_checkups'] }}</h3>
                                <p class="text-muted mb-0">{{ __('Checkups') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border-end">
                                <h3 class="text-warning">{{ $reportData['summary']['total_prescriptions'] }}</h3>
                                <p class="text-muted mb-0">{{ __('Prescriptions') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h3 class="text-info">{{ $reportData['summary']['total_appointments'] }}</h3>
                            <p class="text-muted mb-0">{{ __('Appointments') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Vital Signs -->
    @if($reportData['latest_checkup'])
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat text-danger me-2"></i>
                        {{ __('Latest Vital Signs') }}
                        <small class="text-muted">({{ $reportData['latest_checkup']->checkup_date->format('M d, Y') }})</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($reportData['latest_checkup']->weight)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-weight fa-2x text-primary mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->weight }} kg</h6>
                                <small class="text-muted">{{ __('Weight') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($reportData['latest_checkup']->height)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-ruler-vertical fa-2x text-info mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->height }} cm</h6>
                                <small class="text-muted">{{ __('Height') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($reportData['latest_checkup']->blood_pressure)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-tint fa-2x text-danger mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->blood_pressure }}</h6>
                                <small class="text-muted">{{ __('Blood Pressure') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($reportData['latest_checkup']->heart_rate)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-heartbeat fa-2x text-warning mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->heart_rate }} bpm</h6>
                                <small class="text-muted">{{ __('Heart Rate') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($reportData['latest_checkup']->temperature)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-thermometer-half fa-2x text-success mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->temperature }}°C</h6>
                                <small class="text-muted">{{ __('Temperature') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($reportData['latest_checkup']->blood_sugar)
                        <div class="col-md-2">
                            <div class="text-center">
                                <i class="fas fa-vial fa-2x text-secondary mb-2"></i>
                                <h6>{{ $reportData['latest_checkup']->blood_sugar }} mg/dL</h6>
                                <small class="text-muted">{{ __('Blood Sugar') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Custom Vital Signs -->
                    @if($reportData['latest_checkup']->hasCustomVitalSigns())
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-stethoscope me-2"></i>
                                {{ __('Additional Vital Signs') }}
                            </h6>
                            <div class="row">
                                @foreach($reportData['latest_checkup']->custom_vital_signs_with_config as $customSign)
                                <div class="col-md-3 mb-2">
                                    <div class="text-center">
                                        <i class="fas fa-heartbeat fa-2x {{ $customSign['status_class'] }} mb-2"></i>
                                        <h6 class="{{ $customSign['status_class'] }}">{{ $customSign['formatted_value'] }}</h6>
                                        <small class="text-muted">{{ $customSign['config']->name }}</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <!-- Restore the original row structure -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- BMI History -->
    @if(count($reportData['bmi_history']) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-info me-2"></i>
                        {{ __('BMI History') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Weight (kg)') }}</th>
                                    <th>{{ __('Height (cm)') }}</th>
                                    <th>{{ __('BMI') }}</th>
                                    <th>{{ __('Category') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['bmi_history'] as $bmi)
                                <tr>
                                    <td>{{ Carbon\Carbon::parse($bmi['date'])->format('M d, Y') }}</td>
                                    <td>{{ $bmi['weight'] }}</td>
                                    <td>{{ $bmi['height'] }}</td>
                                    <td>{{ $bmi['bmi'] }}</td>
                                    <td>
                                        @if($bmi['bmi'] < 18.5)
                                            <span class="badge bg-info">{{ __('Underweight') }}</span>
                                        @elseif($bmi['bmi'] < 25)
                                            <span class="badge bg-success">{{ __('Normal') }}</span>
                                        @elseif($bmi['bmi'] < 30)
                                            <span class="badge bg-warning">{{ __('Overweight') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Obese') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Checkups -->
    @if($reportData['checkups']->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-stethoscope text-primary me-2"></i>
                        {{ __('Recent Checkups') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Vital Signs') }}</th>
                                    <th>{{ __('Custom Signs') }}</th>
                                    <th>{{ __('Symptoms') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Recorded By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['checkups']->take(10) as $checkup)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y') }}</td>
                                    <td>
                                        <small>
                                            @if($checkup->blood_pressure)
                                                <div><strong>BP:</strong> {{ $checkup->blood_pressure }}</div>
                                            @endif
                                            @if($checkup->heart_rate)
                                                <div><strong>HR:</strong> {{ $checkup->heart_rate }} bpm</div>
                                            @endif
                                            @if($checkup->weight)
                                                <div><strong>Weight:</strong> {{ $checkup->weight }} kg</div>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            @if($checkup->hasCustomVitalSigns())
                                                @foreach($checkup->custom_vital_signs_with_config as $customSign)
                                                    <div class="{{ $customSign['status_class'] }}">
                                                        <strong>{{ $customSign['config']->name }}:</strong> {{ $customSign['formatted_value'] }}
                                                    </div>
                                                @endforeach
                                            @else
                                                None
                                            @endif
                                        </small>
                                    </td>
                                    <td>{{ Str::limit($checkup->symptoms ?? 'None', 50) }}</td>
                                    <td>{{ Str::limit($checkup->notes ?? 'None', 50) }}</td>
                                    <td>{{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Prescriptions -->
    @if($reportData['prescriptions']->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-pills text-warning me-2"></i>
                        {{ __('Recent Prescriptions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Medicines') }}</th>
                                    <th>{{ __('Instructions') }}</th>
                                    <th>{{ __('Prescribed By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportData['prescriptions']->take(10) as $prescription)
                                <tr>
                                    <td>{{ $prescription->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @foreach($prescription->medicines as $medicine)
                                            <div class="small">{{ $medicine->name }}</div>
                                        @endforeach
                                    </td>
                                    <td>{{ Str::limit($prescription->instructions ?? 'No instructions', 50) }}</td>
                                    <td>{{ $prescription->prescriber->first_name ?? 'Unknown' }} {{ $prescription->prescriber->last_name ?? '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
