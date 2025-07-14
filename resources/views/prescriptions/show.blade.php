@extends('layouts.app')

@section('title', __('Prescription Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                        {{ __('Prescription Details') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('prescriptions.index') }}">{{ __('Prescriptions') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Details') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-success me-2">
                        <i class="fas fa-file-pdf me-1"></i>
                        {{ __('Download PDF') }}
                    </button>
                    <button type="button" class="btn btn-outline-primary me-2">
                        <i class="fas fa-print me-1"></i>
                        {{ __('Print') }}
                    </button>
                    <a href="{{ route('prescriptions.edit', $prescription->id ?? 1) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Prescription Header -->
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        {{ __('Prescription') }} #{{ $prescription->prescription_number ?? 'RX-' . date('Y') . '-00001' }}
                                    </h5>
                                    <small class="text-muted">{{ __('Created on') }} {{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('F d, Y') : now()->format('F d, Y') }}</small>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <span class="badge bg-{{ $prescription->status == 'active' ? 'success' : ($prescription->status == 'completed' ? 'primary' : 'secondary') }} fs-6">
                                        {{ ucfirst($prescription->status ?? 'Active') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Patient Information -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user-injured me-2"></i>
                                        {{ __('Patient Information') }}
                                    </h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-primary text-white rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                            {{ strtoupper(substr($prescription->patient->first_name ?? 'D', 0, 1) . substr($prescription->patient->last_name ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ ($prescription->patient->first_name ?? 'Demo') . ' ' . ($prescription->patient->last_name ?? 'Patient') }}</h6>
                                            <small class="text-muted">{{ __('Patient ID') }}: {{ $prescription->patient->patient_id ?? 'P000001' }}</small>
                                        </div>
                                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <strong>{{ __('Age') }}:</strong> {{ $prescription->patient->age ?? '35' }}
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ __('Gender') }}:</strong> {{ ucfirst($prescription->patient->gender ?? 'Male') }}
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ __('Weight') }}:</strong> {{ $prescription->patient->weight ?? '75' }}kg
                                        </div>
                                        <div class="col-6">
                                            <strong>{{ __('Blood Type') }}:</strong> {{ $prescription->patient->blood_type ?? 'O+' }}
                                        </div>
                                        <div class="col-12">
                                            <strong>{{ __('Phone') }}:</strong> {{ $prescription->patient->phone ?? '+1-555-0123' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Doctor Information -->
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user-md me-2"></i>
                                        {{ __('Prescribing Doctor') }}
                                    </h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar bg-success text-white rounded-circle me-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                            {{ strtoupper(substr($prescription->doctor->first_name ?? 'D', 0, 1) . substr($prescription->doctor->last_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ ($prescription->doctor->first_name ?? 'Dr. John') . ' ' . ($prescription->doctor->last_name ?? 'Smith') }}</h6>
                                            <small class="text-muted">{{ $prescription->doctor->specialization ?? 'General Practitioner' }}</small>
                                        </div>
                                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-12">
                                            <strong>{{ __('License') }}:</strong> {{ $prescription->doctor->medical_license ?? 'MD123456' }}
                                        </div>
                                        <div class="col-12">
                                            <strong>{{ __('Phone') }}:</strong> {{ $prescription->doctor->phone ?? '+1-555-0456' }}
                                        </div>
                                        <div class="col-12">
                                            <strong>{{ __('Email') }}:</strong> {{ $prescription->doctor->email ?? 'doctor@concure.com' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diagnosis and Symptoms -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                {{ __('Diagnosis & Symptoms') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>{{ __('Primary Diagnosis') }}:</strong>
                                <p class="mb-2">{{ $prescription->diagnosis ?? 'Hypertension and Type 2 Diabetes' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>{{ __('Symptoms') }}:</strong>
                                <p class="mb-0">{{ $prescription->symptoms ?? 'Elevated blood pressure, frequent urination, increased thirst' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                {{ __('Instructions & Notes') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>{{ __('General Instructions') }}:</strong>
                                <p class="mb-2">{{ $prescription->instructions ?? 'Take medications as prescribed. Monitor blood pressure daily. Follow up in 2 weeks.' }}</p>
                            </div>
                            @if($prescription->notes ?? false)
                            <div class="mb-0">
                                <strong>{{ __('Doctor Notes') }}:</strong>
                                <p class="mb-0">{{ $prescription->notes }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Medications -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-pills me-2"></i>
                                {{ __('Prescribed Medications') }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Medication') }}</th>
                                            <th>{{ __('Strength') }}</th>
                                            <th>{{ __('Dosage') }}</th>
                                            <th>{{ __('Frequency') }}</th>
                                            <th>{{ __('Duration') }}</th>
                                            <th>{{ __('Instructions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($prescription->medicines) && $prescription->medicines->count() > 0)
                                            @foreach($prescription->medicines as $medicine)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $medicine->name }}</div>
                                                    <small class="text-muted">{{ $medicine->generic_name ?? '' }}</small>
                                                </td>
                                                <td>{{ $medicine->pivot->strength ?? $medicine->strength }}</td>
                                                <td>{{ $medicine->pivot->dosage ?? '1 tablet' }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $medicine->pivot->frequency ?? 'twice_daily')) }}</td>
                                                <td>{{ $medicine->pivot->duration ?? '7 days' }}</td>
                                                <td>{{ $medicine->pivot->instructions ?? 'Take with food' }}</td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <!-- Demo medications -->
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">Lisinopril</div>
                                                    <small class="text-muted">ACE Inhibitor</small>
                                                </td>
                                                <td>10mg</td>
                                                <td>1 tablet</td>
                                                <td>Once daily</td>
                                                <td>30 days</td>
                                                <td>Take in the morning</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">Metformin</div>
                                                    <small class="text-muted">Antidiabetic</small>
                                                </td>
                                                <td>500mg</td>
                                                <td>1 tablet</td>
                                                <td>Twice daily</td>
                                                <td>30 days</td>
                                                <td>Take with meals</td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">Aspirin</div>
                                                    <small class="text-muted">Antiplatelet</small>
                                                </td>
                                                <td>81mg</td>
                                                <td>1 tablet</td>
                                                <td>Once daily</td>
                                                <td>30 days</td>
                                                <td>Take with food</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .breadcrumb, .card-header .row .col-md-6:last-child {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
}
</style>
@endsection
