@extends('layouts.app')

@section('title', __('Prescription Details'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-prescription me-2"></i>
                            {{ __('Prescription Details') }}
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="{{ route('simple-prescriptions.print', $prescription->id) }}"
                               class="btn btn-success btn-sm" target="_blank" title="{{ __('Print') }}">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="{{ route('simple-prescriptions.pdf', $prescription->id) }}"
                               class="btn btn-danger btn-sm" title="{{ __('Download PDF') }}">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <a href="{{ route('simple-prescriptions.edit', $prescription->id) }}"
                               class="btn btn-light btn-sm" title="{{ __('Edit Prescription') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('simple-prescriptions.index') }}"
                               class="btn btn-outline-light btn-sm" title="{{ __('Back to Prescriptions') }}">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Prescription Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 class="text-primary">{{ $prescription->prescription_number }}</h4>
                            <p class="text-muted mb-0">{{ __('Created on') }} {{ $prescription->created_at->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-{{ $prescription->status === 'active' ? 'success' : ($prescription->status === 'completed' ? 'primary' : 'secondary') }} fs-6">
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-injured text-primary me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Name') }}:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}<br>
                                    <strong>{{ __('Patient ID') }}:</strong> {{ $prescription->patient->patient_id }}<br>
                                    <strong>{{ __('Gender') }}:</strong> {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ __('Phone') }}:</strong> {{ $prescription->patient->phone ?? 'Not provided' }}<br>
                                    <strong>{{ __('Email') }}:</strong> {{ $prescription->patient->email ?? 'Not provided' }}<br>
                                    <strong>{{ __('Date of Birth') }}:</strong> {{ $prescription->patient->date_of_birth ? \Carbon\Carbon::parse($prescription->patient->date_of_birth)->format('M d, Y') : 'Not provided' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-md text-primary me-2"></i>
                                {{ __('Doctor Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Doctor') }}:</strong> Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}<br>
                                    <strong>{{ __('Phone') }}:</strong> {{ $prescription->doctor->phone ?? 'Not provided' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ __('Email') }}:</strong> {{ $prescription->doctor->email ?? 'Not provided' }}<br>
                                    <strong>{{ __('Prescribed Date') }}:</strong> {{ $prescription->prescribed_date->format('F d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnosis -->
                    @if($prescription->diagnosis)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-stethoscope text-primary me-2"></i>
                                    {{ __('Diagnosis') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $prescription->diagnosis }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Medicines -->
                    @if($prescription->medicines->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-pills text-primary me-2"></i>
                                    {{ __('Prescribed Medicines') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($prescription->medicines as $index => $medicine)
                                    <div class="medicine-item mb-4 p-3 border rounded {{ $loop->last ? 'mb-0' : '' }}" style="background-color: #f8f9fa;">
                                        <div class="medicine-header mb-3">
                                            <h6 class="text-primary mb-0">
                                                <i class="fas fa-capsules me-2"></i>
                                                {{ $index + 1 }}. {{ $medicine->medicine_name }}
                                            </h6>
                                        </div>
                                        <div class="row" style="display: flex; gap: 15px;">
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Dosage') }}</small>
                                                    <strong class="text-dark">{{ $medicine->dosage ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Frequency') }}</small>
                                                    <strong class="text-dark">{{ $medicine->frequency ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Duration') }}</small>
                                                    <strong class="text-dark">{{ $medicine->duration ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @if($medicine->instructions)
                                            <div class="mt-3 pt-3 border-top">
                                                <small class="text-muted d-block mb-1">{{ __('Instructions') }}</small>
                                                <div class="text-dark">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    {{ $medicine->instructions }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($prescription->notes)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note text-primary me-2"></i>
                                    {{ __('Notes') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $prescription->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('simple-prescriptions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            {{ __('Back to Prescriptions') }}
                        </a>
                        <div class="d-flex flex-wrap gap-1">
                            <div class="btn-group">
                                <a href="{{ route('simple-prescriptions.print', $prescription->id) }}"
                                   class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-print me-1"></i>
                                    {{ __('Print') }}
                                </a>
                                <a href="{{ route('simple-prescriptions.pdf', $prescription->id) }}"
                                   class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    {{ __('PDF') }}
                                </a>
                            </div>
                            <a href="{{ route('simple-prescriptions.edit', $prescription->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                {{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
/* Ensure buttons stay horizontal and compact */
.card-header .btn-group {
    white-space: nowrap;
}

.card-header .btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    min-width: 36px;
    border-radius: 0;
}

.card-header .btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.card-header .btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.card-header .btn i {
    font-size: 0.875rem;
}

/* Responsive adjustments - keep horizontal on mobile */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header .btn-group {
        display: flex;
        justify-content: center;
    }

    .card-header .btn-group .btn {
        padding: 0.375rem 0.75rem;
        min-width: 40px;
    }
}

/* Extra small screens - make buttons slightly larger for touch */
@media (max-width: 576px) {
    .card-header .btn-group .btn {
        padding: 0.5rem 0.75rem;
        font-size: 1rem;
        min-width: 44px;
    }

    .card-header .btn i {
        font-size: 1rem;
    }
}
</style>
@endsection
