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
                    <a href="{{ route('patients.edit', $patient->id ?? 1) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit Patient') }}
                    </a>
                    <button type="button" class="btn btn-info me-2" onclick="newAppointment()">
                        <i class="fas fa-calendar-plus me-1"></i>
                        {{ __('New Appointment') }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="newPrescription()">
                        <i class="fas fa-prescription-bottle-alt me-1"></i>
                        {{ __('New Prescription') }}
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
                                    <a href="#" class="btn btn-sm btn-outline-secondary">
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

<script>
function newPrescription() {
    window.location.href = `/simple-prescriptions/create?patient_id={{ $patient->id ?? 1 }}`;
}

function newAppointment() {
    window.location.href = `/appointments/create?patient_id={{ $patient->id ?? 1 }}`;
}

function newCheckup() {
    // For now, we'll show an alert. In a full implementation, this would open a checkup form
    alert('{{ __("Checkup functionality will be implemented in the next update.") }}');
}
</script>
@endsection
