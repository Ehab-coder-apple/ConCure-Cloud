@extends('layouts.app')

@section('title', __('Medical History') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-history me-2 text-primary"></i>{{ __('Medical History') }}</h3>
            <p class="text-muted mb-0">{{ $patient->full_name }} • {{ $patient->patient_id }}</p>
        </div>
        <div>
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">{{ __('Back to Patient') }}</a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Total Checkups') }}</small><strong>{{ $patient->checkups->count() }}</strong></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Prescriptions') }}</small><strong>{{ $patient->prescriptions->count() + $patient->simplePrescriptions->count() }}</strong></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Lab Requests') }}</small><strong>{{ $patient->labRequests->count() }}</strong></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Surgical Cases') }}</small><strong>{{ $patient->surgicalCases->count() }}</strong></div></div></div>
    </div>

    <!-- Surgical Cases & Visits -->
    @if($patient->surgicalCases->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-scalpel me-2 text-danger"></i>{{ __('Surgical Cases & Visits') }}</h5></div>
            <div class="card-body">
                @foreach($patient->surgicalCases as $case)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h6 class="mb-0">{{ __('Case') }} #{{ $case->id }}: {{ $case->diagnosis ?: 'N/A' }}</h6>
                                <small class="text-muted">{{ optional($case->scheduled_at)->format('M d, Y') }} • <span class="badge bg-info">{{ $case->status }}</span></small>
                            </div>
                        </div>
                        <p class="mb-2"><strong>{{ __('Surgeon') }}:</strong> {{ optional($case->primarySurgeon)->full_name ?? 'N/A' }}</p>
                        @if($case->visits->isNotEmpty())
                            <div class="small bg-light p-2 rounded">
                                <strong>{{ __('Follow-up Visits') }}:</strong>
                                @foreach($case->visits as $visit)
                                    <div class="mt-2">
                                        <strong>Visit #{{ $visit->visit_number }}</strong> - {{ optional($visit->visit_date)->format('M d, Y') }}
                                        @if($visit->wound_status)
                                            <span class="badge bg-{{ $visit->wound_status === 'healing_well' ? 'success' : ($visit->wound_status === 'infected' ? 'danger' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $visit->wound_status)) }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Checkups -->
    @if($patient->checkups->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-stethoscope me-2 text-success"></i>{{ __('Checkups') }}</h5></div>
            <div class="card-body">
                @foreach($patient->checkups as $checkup)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ optional($checkup->checkup_date)->format('M d, Y') }}</h6>
                                <small class="text-muted">{{ $checkup->recorder->full_name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                        @if($checkup->symptoms)<p class="mb-1"><strong>{{ __('Symptoms') }}:</strong> {{ Str::limit($checkup->symptoms, 100) }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Prescriptions -->
    @if($patient->prescriptions->isNotEmpty() || $patient->simplePrescriptions->isNotEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2 text-warning"></i>{{ __('Prescriptions') }}</h5></div>
            <div class="card-body">
                @forelse($patient->prescriptions as $prescription)
                    <div class="border rounded p-3 mb-2">
                        <h6 class="mb-0">{{ optional($prescription->prescribed_date)->format('M d, Y') }}</h6>
                        <small class="text-muted">{{ $prescription->doctor->full_name ?? 'Unknown' }}</small>
                    </div>
                @empty @endforelse
                @forelse($patient->simplePrescriptions as $prescription)
                    <div class="border rounded p-3 mb-2">
                        <h6 class="mb-0">{{ optional($prescription->prescribed_date)->format('M d, Y') }}</h6>
                        <small class="text-muted">{{ $prescription->doctor->full_name ?? 'Unknown' }}</small>
                    </div>
                @empty @endforelse
            </div>
        </div>
    @endif
</div>
@endsection
