@extends('layouts.app')

@section('title', __('Dental Module') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-tooth me-2 text-primary"></i>{{ __('Dental Module') }}</h3>
            <p class="text-muted mb-0">{{ __('Dental-specific habits, notes, charts, procedures, and visit links for this patient.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <a href="{{ route('dental.charts.index', ['patient' => $patient->id]) }}" class="btn btn-primary">{{ __('Open Dental Charts') }}</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Dental Summary') }}</h5>
                    <form method="POST" action="{{ route('patients.dental.update', ['patient' => $patient->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">{{ __('Oral Hygiene Status') }}</label>
                            <select class="form-select" name="oral_hygiene">
                                <option value="">{{ __('Select status') }}</option>
                                @foreach(\App\Models\PatientDental::ORAL_HYGIENE_STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ old('oral_hygiene', $dentalProfile->oral_hygiene) === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Smoking Habits') }}</label>
                            <select class="form-select" name="smoking_status">
                                <option value="">{{ __('Select status') }}</option>
                                @foreach(\App\Models\PatientDental::SMOKING_STATUSES as $value => $label)
                                    <option value="{{ $value }}" {{ old('smoking_status', $dentalProfile->smoking_status) === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="bruxism" name="bruxism" {{ old('bruxism', $dentalProfile->bruxism) ? 'checked' : '' }}>
                            <label class="form-check-label" for="bruxism">{{ __('Bruxism') }}</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" name="notes" rows="5" placeholder="{{ __('Dental-specific notes only. Shared allergies and chronic conditions stay in Medical Overview.') }}">{{ old('notes', $dentalProfile->notes) }}</textarea>
                        </div>

                        <button class="btn btn-primary" type="submit">{{ __('Save Dental Summary') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Quick Links') }}</h5>
                    <a href="{{ route('dental.charts.index', ['patient' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-2">{{ __('Dental Charts') }}</a>
                    <a href="{{ route('dental.treatments.index', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-2">{{ __('Procedure History') }}</a>
                    <a href="{{ route('dental.history', ['patient' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-0">{{ __('Dental History Timeline') }}</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">{{ __('Dental Snapshot') }}</h5>
                                <span class="badge bg-light text-dark">{{ __('Future-ready for tooth chart and procedures') }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Charts') }}</small><strong>{{ $patient->dental_charts_count }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Procedures') }}</small><strong>{{ $patient->dental_treatments_count }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Images') }}</small><strong>{{ $patient->dental_images_count }}</strong></div></div>
                                <div class="col-md-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Last Dental Visit') }}</small><strong>{{ $dentalLastVisitLabel }}</strong></div></div>
                                <div class="col-md-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Latest Chart Type') }}</small><strong>{{ $latestDentalChart?->chart_type_display ?: __('No chart yet') }}</strong></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('Recent Charts') }}</h5>
                                <a href="{{ route('dental.charts.create', ['patient' => $patient->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('New Chart') }}</a>
                            </div>
                            @forelse($recentDentalCharts as $chart)
                                <div class="border rounded p-3 mb-2">
                                    <div class="fw-semibold">{{ $chart->chart_type_display }}</div>
                                    <div class="small text-muted">{{ optional($chart->created_at)->format('M d, Y') ?: __('No date') }} • {{ $chart->creator->full_name ?? __('Unknown') }}</div>
                                    @if($chart->general_notes)
                                        <div class="small mt-2">{{ \Illuminate\Support\Str::limit($chart->general_notes, 120) }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No dental charts recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ __('Procedure History') }}</h5>
                                <a href="{{ route('dental.treatments.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('New Procedure') }}</a>
                            </div>
                            @forelse($recentDentalTreatments as $treatment)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div class="fw-semibold">{{ $treatment->procedure_name }}</div>
                                        <span class="badge bg-light text-dark">{{ $treatment->status_display }}</span>
                                    </div>
                                    <div class="small text-muted">{{ optional($treatment->created_at)->format('M d, Y') ?: __('No date') }} • {{ $treatment->diagnosis ?: __('No diagnosis') }}</div>
                                    @if($treatment->notes)
                                        <div class="small mt-2">{{ \Illuminate\Support\Str::limit($treatment->notes, 120) }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No dental procedures recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Linked Visits & Dental Notes Context') }}</h5>
                            <p class="text-muted">{{ __('Dental notes remain dental-specific here, while visits provide clinical context and HPI linkage without duplicating shared medical data.') }}</p>
                            @forelse($recentVisits as $visit)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $visit->hpi->chief_complaint ?? $visit->reason_for_visit ?? __('Visit') }}</div>
                                            <div class="small text-muted">{{ optional($visit->visit_date)->format('M d, Y h:i A') }} • {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</div>
                                        </div>
                                        <a href="{{ route('patients.visits.show', ['patient' => $patient->id, 'visit' => $visit->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('Open Visit') }}</a>
                                    </div>
                                    @if($visit->notes || $visit->hpi?->hpi_summary)
                                        <div class="small mt-2 text-muted">{{ \Illuminate\Support\Str::limit($visit->notes ?: $visit->hpi?->hpi_summary, 150) }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No visits linked yet. Visit-based HPI entries will appear here for dental context.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection