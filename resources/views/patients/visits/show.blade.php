@extends('layouts.app')

@section('title', __('Visit') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">{{ __('Visit Record') }}</h3>
            <p class="text-muted mb-0">{{ $patient->full_name }} • {{ optional($visit->visit_date)->format('M d, Y h:i A') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.visits.receipt', ['patient' => $patient->id, 'visit' => $visit->id]) }}"
               target="_blank"
               class="btn btn-outline-primary">
                <i class="fas fa-print me-1"></i> {{ __('Print Receipt') }}
            </a>
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Visit Details') }}</h5>
                    <div class="mb-2"><small class="text-muted d-block">{{ __('Visit Type') }}</small><strong>{{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</strong></div>
                    <div class="mb-2"><small class="text-muted d-block">{{ __('Status') }}</small><strong>{{ ucfirst($visit->status) }}</strong></div>
                    <div class="mb-2"><small class="text-muted d-block">{{ __('Reason for Visit') }}</small><strong>{{ $visit->reason_for_visit ?: __('Not recorded') }}</strong></div>
                    <div><small class="text-muted d-block">{{ __('Recorded By') }}</small><strong>{{ $visit->creator->full_name ?? __('Unknown') }}</strong></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Visit HPI') }}</h5>
                    <div class="mb-3"><small class="text-muted d-block">{{ __('Chief Complaint') }}</small><strong>{{ $visit->hpi->chief_complaint ?? __('Not recorded') }}</strong></div>
                    <div class="mb-3"><small class="text-muted d-block">{{ __('HPI Summary') }}</small><div>{{ $visit->hpi->hpi_summary ?: __('No HPI summary recorded.') }}</div></div>
                    <div class="mb-3"><small class="text-muted d-block">{{ __('Associated Symptoms') }}</small><div>{{ $visit->hpi->associated_symptoms ?: __('No associated symptoms recorded.') }}</div></div>
                    <div><small class="text-muted d-block">{{ __('Visit Notes') }}</small><div>{{ $visit->notes ?: $visit->hpi->clinical_notes ?: __('No additional notes recorded.') }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection