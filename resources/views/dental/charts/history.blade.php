@extends('layouts.app')

@section('title', __('Dental History') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-clock-rotate-left me-2 text-primary"></i>{{ __('Dental History') }}</h3>
            <p class="text-muted mb-0">{{ $patient->full_name }} • {{ $patient->patient_id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.dental.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Dental Module') }}</a>
            <a href="{{ url("/dental/patients/{$patient->id}/charts") }}" class="btn btn-outline-primary">{{ __('Dental Charts') }}</a>
            <a href="{{ url("/dental/patients/{$patient->id}/charts/create") }}" class="btn btn-primary">{{ __('New Dental Chart') }}</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Total Charts') }}</small><strong>{{ $charts->count() }}</strong></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Latest Chart') }}</small><strong>{{ optional($charts->first()?->created_at)->format('M d, Y') ?: __('Not recorded') }}</strong></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><small class="text-muted d-block">{{ __('Total Procedures Linked') }}</small><strong>{{ $charts->sum(fn ($chart) => $chart->treatments->count()) }}</strong></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @forelse($charts as $chart)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $chart->chart_type_display }}</div>
                            <div class="small text-muted">{{ optional($chart->created_at)->format('M d, Y h:i A') ?: __('No date') }} • {{ $chart->creator->full_name ?? $chart->creator->name ?? __('Unknown') }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ url("/dental/patients/{$patient->id}/charts/{$chart->id}") }}" class="btn btn-sm btn-outline-primary">{{ __('View Chart') }}</a>
                            <a href="{{ url("/dental/patients/{$patient->id}/charts/{$chart->id}/edit") }}" class="btn btn-sm btn-outline-secondary">{{ __('Edit') }}</a>
                        </div>
                    </div>

                    <div class="row g-3 mb-2">
                        <div class="col-md-4"><div class="bg-light border rounded p-2"><small class="text-muted d-block">{{ __('Teeth Recorded') }}</small><strong>{{ $chart->toothRecords->count() }}</strong></div></div>
                        <div class="col-md-4"><div class="bg-light border rounded p-2"><small class="text-muted d-block">{{ __('Procedures') }}</small><strong>{{ $chart->treatments->count() }}</strong></div></div>
                        <div class="col-md-4"><div class="bg-light border rounded p-2"><small class="text-muted d-block">{{ __('Chart ID') }}</small><strong>#{{ $chart->id }}</strong></div></div>
                    </div>

                    @if($chart->general_notes)
                        <div class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($chart->general_notes, 220) }}</div>
                    @endif

                    @if($chart->treatments->isNotEmpty())
                        <div class="small">
                            <strong>{{ __('Linked Procedures') }}:</strong>
                            {{ $chart->treatments->pluck('procedure_name')->filter()->join(', ') }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-tooth fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No dental history recorded yet.') }}</h5>
                    <p class="text-muted">{{ __('Create the first dental chart to start the patient dental timeline.') }}</p>
                    <a href="{{ url("/dental/patients/{$patient->id}/charts/create") }}" class="btn btn-primary">{{ __('Create First Dental Chart') }}</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection