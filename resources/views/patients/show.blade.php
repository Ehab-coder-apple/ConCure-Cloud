@extends('layouts.app')

@section('title', __('Patient Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        {{ __('Patient Details') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">{{ __('Patients') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Patient Details') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group me-1" role="group">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-medical me-1"></i>
                            {{ __('Report') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('patient.report', $patient) }}" target="_blank">
                                    <i class="fas fa-eye me-2"></i>
                                    {{ __('View Report') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('patient.report', $patient) }}?format=pdf" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    {{ __('Download PDF') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showReportModal()">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    {{ __('Custom Date Range') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('patients.edit', $patient->id ?? 1) }}" class="btn btn-outline-primary btn-sm me-1">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                    <button type="button" class="btn btn-info btn-sm me-1" onclick="newAppointment()">
                        <i class="fas fa-calendar-plus me-1"></i>
                        {{ __('Appointment') }}
                    </button>
                    <a href="{{ route('patients.vital-signs.index', $patient) }}" class="btn btn-info btn-sm me-1">
                        <i class="fas fa-stethoscope me-1"></i>
                        {{ __('Vital Signs') }}
                    </a>
                    <a href="{{ route('patients.checkup-templates.index', $patient) }}" class="btn btn-warning btn-sm me-1">
                        <i class="fas fa-clipboard-list me-1"></i>
                        {{ __('Templates') }}
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" onclick="newPrescription()">
                        <i class="fas fa-prescription-bottle-alt me-1"></i>
                        {{ __('Prescription') }}
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Patient Information -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="avatar bg-primary text-white rounded-circle mx-auto mb-2" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    {{ strtoupper(substr($patient->first_name ?? 'P', 0, 1) . substr($patient->last_name ?? 'A', 0, 1)) }}
                                </div>
                                <h5 class="mb-1">{{ ($patient->first_name ?? 'Demo') . ' ' . ($patient->last_name ?? 'Patient') }}</h5>
                                <span class="badge bg-primary">{{ $patient->patient_id ?? 'P000001' }}</span>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Age') }}</small>
                                    <div class="fw-bold">{{ $patient->age ?? ($patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '25') }} {{ __('years') }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Gender') }}</small>
                                    <div class="fw-bold">{{ ucfirst($patient->gender ?? 'Male') }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Phone') }}</small>
                                    <div class="fw-bold">{{ $patient->phone ?? '+1-555-0123' }}</div>
                                </div>
                                @if($patient->whatsapp_phone)
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fab fa-whatsapp text-success me-1"></i>
                                        {{ __('WhatsApp') }}
                                    </small>
                                    <div class="fw-bold">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $patient->whatsapp_phone) }}"
                                           target="_blank" class="text-success text-decoration-none">
                                            {{ $patient->whatsapp_phone }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </div>
                                </div>
                                @endif
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Email') }}</small>
                                    <div class="fw-bold">{{ $patient->email ?? 'demo@patient.com' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Address') }}</small>
                                    <div class="fw-bold">{{ $patient->address ?? '123 Main Street, City, State' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-heartbeat me-2"></i>
                                {{ __('Latest Vital Signs') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Height') }}</small>
                                    <div class="fw-bold">{{ $patient->height ?? '170' }} cm</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Weight') }}</small>
                                    <div class="fw-bold">{{ $patient->weight ?? '70' }} kg</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('BMI') }}</small>
                                    <div class="fw-bold">{{ $patient->bmi ?? '24.2' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Blood Type') }}</small>
                                    <div class="fw-bold">{{ $patient->blood_type ?? 'O+' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Records -->
                <div class="col-lg-8">
                    <!-- Medical History -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file-medical me-2"></i>
                                {{ __('Medical History') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>{{ $patient->medical_history ?? __('No medical history recorded yet.') }}</p>
                        </div>
                    </div>

                    <!-- Recent Visits -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ __('Recent Visits') }}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Visit') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">{{ __('No visits recorded yet.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ __('Recent Appointments') }}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="newAppointment()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Appointment') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @if($patient->appointments && $patient->appointments->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($patient->appointments as $appointment)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-info me-2">{{ $appointment->appointment_number }}</span>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_datetime)->format('M d, Y g:i A') }}</small>
                                                </div>
                                                <h6 class="mb-1">{{ $appointment->type ? ucfirst(str_replace('_', ' ', $appointment->type)) : __('Consultation') }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    {{ __('Doctor:') }} {{ $appointment->doctor->first_name ?? 'Unknown' }} {{ $appointment->doctor->last_name ?? '' }}
                                                </p>
                                                @if($appointment->reason)
                                                <p class="mb-0 small text-muted">{{ Str::limit($appointment->reason, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{
                                                    $appointment->status == 'completed' ? 'success' :
                                                    ($appointment->status == 'confirmed' ? 'primary' :
                                                    ($appointment->status == 'cancelled' ? 'danger' : 'secondary'))
                                                }}">
                                                    {{ ucfirst(str_replace('_', ' ', $appointment->status ?? 'scheduled')) }}
                                                </span>
                                                <div class="mt-1">
                                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($patient->appointments->count() >= 5)
                                <div class="text-center mt-3">
                                    <a href="{{ route('appointments.index') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Appointments') }}
                                    </a>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No appointments scheduled yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Prescriptions -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription-bottle-alt me-2"></i>
                                {{ __('Recent Prescriptions') }}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="newPrescription()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Prescription') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @php
                                $allPrescriptions = collect();
                                if($patient->prescriptions) {
                                    $allPrescriptions = $allPrescriptions->merge($patient->prescriptions);
                                }
                                if($patient->simplePrescriptions) {
                                    $allPrescriptions = $allPrescriptions->merge($patient->simplePrescriptions);
                                }
                                $allPrescriptions = $allPrescriptions->sortByDesc('created_at')->take(5);

                                // Debug info
                                $prescriptionCount = $patient->prescriptions ? $patient->prescriptions->count() : 0;
                                $simplePrescriptionCount = $patient->simplePrescriptions ? $patient->simplePrescriptions->count() : 0;
                                $appointmentCount = $patient->appointments ? $patient->appointments->count() : 0;
                            @endphp

                            <!-- Debug Info (remove in production) -->
                            <div class="alert alert-info small mb-3">
                                <strong>Debug:</strong>
                                Prescriptions: {{ $prescriptionCount }},
                                Simple Prescriptions: {{ $simplePrescriptionCount }},
                                Appointments: {{ $appointmentCount }},
                                Total Combined: {{ $allPrescriptions->count() }}
                            </div>

                            @if($allPrescriptions->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($allPrescriptions as $prescription)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-primary me-2">{{ $prescription->prescription_number ?? 'N/A' }}</span>
                                                    <small class="text-muted">{{ $prescription->prescribed_date ? \Carbon\Carbon::parse($prescription->prescribed_date)->format('M d, Y') : ($prescription->created_at ? $prescription->created_at->format('M d, Y') : 'N/A') }}</small>
                                                </div>
                                                <h6 class="mb-1">{{ $prescription->diagnosis ?? __('General Prescription') }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    {{ __('Doctor:') }} {{ $prescription->doctor->first_name ?? 'Unknown' }} {{ $prescription->doctor->last_name ?? '' }}
                                                </p>
                                                @if($prescription->notes)
                                                <p class="mb-0 small text-muted">{{ Str::limit($prescription->notes, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $prescription->status == 'active' ? 'success' : ($prescription->status == 'completed' ? 'info' : 'secondary') }}">
                                                    {{ ucfirst($prescription->status ?? 'active') }}
                                                </span>
                                                <div class="mt-1">
                                                    @if(get_class($prescription) === 'App\Models\SimplePrescription')
                                                        <a href="{{ route('simple-prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('simple-prescriptions.index') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Prescriptions') }}
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-prescription-bottle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No prescriptions recorded yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Checkups -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-heartbeat me-2"></i>
                                {{ __('Recent Checkups') }}
                            </h6>
                            <a href="{{ route('checkups.index', $patient) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list me-1"></i>
                            {{ __('View All Checkups') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="newCheckup()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Checkup') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @if($patient->checkups && $patient->checkups->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($patient->checkups as $checkup)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y g:i A') }}</small>
                                                    <span class="badge bg-light text-dark ms-2">{{ __('Checkup') }}</span>
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    @if($checkup->weight)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Weight') }}</small>
                                                        <span class="fw-bold">{{ $checkup->weight }} kg</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->blood_pressure)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('BP') }}</small>
                                                        <span class="fw-bold">{{ $checkup->blood_pressure }}</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->heart_rate)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Heart Rate') }}</small>
                                                        <span class="fw-bold">{{ $checkup->heart_rate }} bpm</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->temperature)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Temperature') }}</small>
                                                        <span class="fw-bold">{{ $checkup->temperature }}°C</span>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if($checkup->symptoms)
                                                <p class="mb-1 small"><strong>{{ __('Symptoms:') }}</strong> {{ Str::limit($checkup->symptoms, 100) }}</p>
                                                @endif
                                                @if($checkup->notes)
                                                <p class="mb-0 small text-muted">{{ Str::limit($checkup->notes, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">{{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($patient->checkups->count() >= 10)
                                <div class="text-center mt-3">
                                    <a href="{{ route('checkups.index', $patient) }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Checkups') }}
                                    </a>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-heartbeat fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No checkups recorded yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Lab Results -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-flask me-2"></i>
                                {{ __('Lab Results') }}
                            </h6>
                            <a href="{{ route('recommendations.lab-requests') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Lab Request') }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-vial fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">{{ __('No lab results recorded yet.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Checkup Modal -->
<div class="modal fade" id="checkupModal" tabindex="-1" aria-labelledby="checkupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkupModalLabel">
                    <i class="fas fa-heartbeat me-2"></i>
                    {{ __('Add New Checkup') }} - {{ $patient->full_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('checkups.store', $patient) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Vital Signs -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-heartbeat me-1"></i>
                                {{ __('Vital Signs') }}
                            </h6>

                            <div class="mb-3">
                                <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                <input type="number" class="form-control" id="weight" name="weight"
                                       step="0.1" min="1" max="500" placeholder="70.5">
                            </div>

                            <div class="mb-3">
                                <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                <input type="number" class="form-control" id="height" name="height"
                                       step="0.1" min="50" max="300" placeholder="175">
                            </div>

                            <div class="mb-3">
                                <label for="blood_pressure" class="form-label">{{ __('Blood Pressure') }}</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure"
                                       placeholder="120/80" pattern="\d{2,3}/\d{2,3}">
                                <div class="form-text">{{ __('Format: 120/80') }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="heart_rate" class="form-label">{{ __('Heart Rate (bpm)') }}</label>
                                <input type="number" class="form-control" id="heart_rate" name="heart_rate"
                                       min="30" max="200" placeholder="72">
                            </div>
                        </div>

                        <!-- Additional Measurements -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-thermometer-half me-1"></i>
                                {{ __('Additional Measurements') }}
                            </h6>

                            <div class="mb-3">
                                <label for="temperature" class="form-label">{{ __('Temperature (°C)') }}</label>
                                <input type="number" class="form-control" id="temperature" name="temperature"
                                       step="0.1" min="30" max="45" placeholder="36.5">
                            </div>

                            <div class="mb-3">
                                <label for="respiratory_rate" class="form-label">{{ __('Respiratory Rate (per min)') }}</label>
                                <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate"
                                       min="5" max="50" placeholder="16">
                            </div>

                            <div class="mb-3">
                                <label for="blood_sugar" class="form-label">{{ __('Blood Sugar (mg/dL)') }}</label>
                                <input type="number" class="form-control" id="blood_sugar" name="blood_sugar"
                                       step="0.1" min="20" max="600" placeholder="100">
                            </div>
                        </div>
                    </div>

                    <!-- Template Selection -->
                    @php
                        $patientTemplates = $patient->assigned_checkup_templates;
                    @endphp

                    @if($patientTemplates->count() > 0)
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="checkup_template" class="form-label">{{ __('Checkup Template') }}</label>
                            <select class="form-select" id="checkup_template" name="template_id" onchange="loadTemplateFields()">
                                <option value="">{{ __('Standard Checkup (No Template)') }}</option>
                                @foreach($patientTemplates as $assignment)
                                    <option value="{{ $assignment->template->id }}"
                                            data-template="{{ json_encode($assignment->template->form_sections) }}">
                                        {{ $assignment->template->name }}
                                        @if($assignment->medical_condition) - {{ $assignment->medical_condition }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('Select a specialized checkup template or use standard checkup') }}</small>
                        </div>
                    </div>
                    @endif

                    <!-- Custom Vital Signs -->
                    @php
                        $patientCustomSigns = $patient->assigned_custom_vital_signs;
                    @endphp

                    @if($patientCustomSigns->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-stethoscope me-1"></i>
                                {{ __('Additional Vital Signs') }}
                            </h6>

                            <div class="row">
                                @foreach($patientCustomSigns as $assignment)
                                    @php $sign = $assignment->customVitalSign; @endphp
                                    <div class="col-md-6 mb-3">
                                        <label for="custom_{{ $sign->id }}" class="form-label">
                                            {{ $sign->display_name }}
                                            @if($sign->normal_range)
                                                <small class="text-muted">(Normal: {{ $sign->normal_range }})</small>
                                            @endif
                                            @if($assignment->medical_condition)
                                                <br><small class="text-info">{{ $assignment->medical_condition }}</small>
                                            @endif
                                        </label>

                                        @if($sign->type === 'select')
                                            <select class="form-select" id="custom_{{ $sign->id }}" name="custom_vital_signs[{{ $sign->id }}]">
                                                <option value="">{{ __('Select...') }}</option>
                                                @foreach($sign->options as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="number" class="form-control" id="custom_{{ $sign->id }}"
                                                   name="custom_vital_signs[{{ $sign->id }}]"
                                                   @if($sign->min_value) min="{{ $sign->min_value }}" @endif
                                                   @if($sign->max_value) max="{{ $sign->max_value }}" @endif
                                                   step="0.1" placeholder="{{ $sign->unit ? 'Enter value in ' . $sign->unit : 'Enter value' }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('No Custom Vital Signs Assigned') }}
                                </h6>
                                <p class="mb-2">{{ __('This patient has no custom vital signs assigned yet.') }}</p>
                                <a href="{{ route('patients.vital-signs.index', $patient) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    {{ __('Assign Vital Signs') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Custom Template Fields -->
                    <div id="customTemplateFields" style="display: none;">
                        <!-- Template fields will be loaded here dynamically -->
                    </div>

                    <!-- Clinical Notes -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-notes-medical me-1"></i>
                                {{ __('Clinical Notes') }}
                            </h6>

                            <div class="mb-3">
                                <label for="symptoms" class="form-label">{{ __('Symptoms') }}</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="3"
                                          placeholder="{{ __('Describe any symptoms the patient is experiencing...') }}"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                          placeholder="{{ __('Additional observations and notes...') }}"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="recommendations" class="form-label">{{ __('Recommendations') }}</label>
                                <textarea class="form-control" id="recommendations" name="recommendations" rows="3"
                                          placeholder="{{ __('Treatment recommendations and follow-up instructions...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Save Checkup') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Report Date Range Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-file-medical me-2"></i>
                    {{ __('Generate Custom Report') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm" method="GET" action="{{ route('patient.report', $patient) }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="report_date_from" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" id="report_date_from" name="date_from"
                                   value="{{ now()->subMonths(6)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="report_date_to" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" id="report_date_to" name="date_to"
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="report_format" class="form-label">{{ __('Format') }}</label>
                            <select class="form-select" id="report_format" name="format">
                                <option value="html">{{ __('View in Browser') }}</option>
                                <option value="pdf">{{ __('Download PDF') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-medical me-1"></i>
                        {{ __('Generate Report') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function newPrescription() {
    window.location.href = `/simple-prescriptions/create?patient_id={{ $patient->id ?? 1 }}`;
}

function loadTemplateFields() {
    const templateSelect = document.getElementById('checkup_template');
    const customFieldsContainer = document.getElementById('customTemplateFields');

    if (templateSelect.value) {
        const selectedOption = templateSelect.selectedOptions[0];
        const templateData = JSON.parse(selectedOption.dataset.template || '{}');

        let fieldsHtml = '<div class="row mt-4"><div class="col-12"><h5><i class="fas fa-clipboard-list me-2"></i>Template Fields</h5></div></div>';

        Object.keys(templateData).forEach(sectionKey => {
            const section = templateData[sectionKey];
            fieldsHtml += `<div class="row mt-3"><div class="col-12"><h6 class="text-primary">${section.title || sectionKey}</h6></div></div>`;
            fieldsHtml += '<div class="row">';

            Object.keys(section.fields || {}).forEach(fieldKey => {
                const field = section.fields[fieldKey];
                fieldsHtml += `<div class="col-md-6 mb-3">`;
                fieldsHtml += `<label for="custom_field_${fieldKey}" class="form-label">${field.label}`;
                if (field.required) fieldsHtml += ' <span class="text-danger">*</span>';
                fieldsHtml += '</label>';

                switch (field.type) {
                    case 'select':
                        fieldsHtml += `<select class="form-select" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        fieldsHtml += '<option value="">Select...</option>';
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(option => {
                                fieldsHtml += `<option value="${option}">${option}</option>`;
                            });
                        }
                        fieldsHtml += '</select>';
                        break;
                    case 'textarea':
                        fieldsHtml += `<textarea class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]" rows="3"${field.required ? ' required' : ''}></textarea>`;
                        break;
                    case 'checkbox':
                        fieldsHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]" value="1"><label class="form-check-label" for="custom_field_${fieldKey}">${field.label}</label></div>`;
                        break;
                    case 'date':
                        fieldsHtml += `<input type="date" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        break;
                    case 'time':
                        fieldsHtml += `<input type="time" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        break;
                    case 'number':
                        fieldsHtml += `<input type="number" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"`;
                        if (field.min) fieldsHtml += ` min="${field.min}"`;
                        if (field.max) fieldsHtml += ` max="${field.max}"`;
                        if (field.step) fieldsHtml += ` step="${field.step}"`;
                        fieldsHtml += `${field.required ? ' required' : ''}>`;
                        break;
                    default:
                        fieldsHtml += `<input type="text" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                }

                fieldsHtml += '</div>';
            });

            fieldsHtml += '</div>';
        });

        customFieldsContainer.innerHTML = fieldsHtml;
        customFieldsContainer.style.display = 'block';
    } else {
        customFieldsContainer.style.display = 'none';
        customFieldsContainer.innerHTML = '';
    }
}

function newAppointment() {
    window.location.href = `/appointments/create?patient_id={{ $patient->id ?? 1 }}`;
}

function newCheckup() {
    const modal = new bootstrap.Modal(document.getElementById('checkupModal'));
    modal.show();
}

function showReportModal() {
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    modal.show();
}

// Handle report form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    const url = this.action + '?' + params.toString();

    // Open in new tab
    window.open(url, '_blank');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    modal.hide();
});
</script>
@endsection
