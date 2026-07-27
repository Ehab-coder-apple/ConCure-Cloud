@extends('layouts.app')

@section('title', __('Pediatric Module') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-baby me-2 text-primary"></i>{{ __('Pediatric Module') }}</h3>
            <p class="text-muted mb-0">{{ __('Child-specific pediatric summary, classifications, growth context, and vaccination follow-up.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <a href="{{ route('pediatric.growth-chart', ['patient' => $patient->id]) }}" class="btn btn-primary">{{ __('Open Growth Chart') }}</a>
        </div>
    </div>

    <div class="alert alert-light border mb-4">
        <i class="fas fa-child me-2 text-primary"></i>{{ __('This module is only shown for patients younger than 16. Shared medical data stays in Medical Overview and is not duplicated here.') }}
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Pediatric Summary') }}</h5>
                    <form method="POST" action="{{ route('patients.pediatric.update', ['patient' => $patient->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3"><label class="form-label">{{ __('Birth Weight (g)') }}</label><input type="number" step="0.01" class="form-control" name="birth_weight" value="{{ old('birth_weight', $pediatricProfile->birth_weight) }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('Gestational Age (weeks)') }}</label><input type="number" class="form-control" name="gestational_age" value="{{ old('gestational_age', $pediatricProfile->gestational_age) }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('Feeding Type') }}</label><select class="form-select" name="feeding_type"><option value="">{{ __('Select feeding type') }}</option>@foreach(\App\Models\PatientPediatric::FEEDING_TYPES as $value => $label)<option value="{{ $value }}" {{ old('feeding_type', $pediatricProfile->feeding_type) === $value ? 'selected' : '' }}>{{ __($label) }}</option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">{{ __('Vaccination Status') }}</label><select class="form-select" name="vaccination_status"><option value="">{{ __('Select vaccination status') }}</option>@foreach(\App\Models\PatientPediatric::VACCINATION_STATUSES as $value => $label)<option value="{{ $value }}" {{ old('vaccination_status', $pediatricProfile->vaccination_status) === $value ? 'selected' : '' }}>{{ __($label) }}</option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">{{ __('Notes') }}</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $pediatricProfile->notes) }}</textarea></div>
                        <button class="btn btn-primary" type="submit">{{ __('Save Pediatric Summary') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Quick Links') }}</h5>
                    <a href="{{ route('pediatric.growth-chart', ['patient' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-2">{{ __('Growth Chart') }}</a>
                    <a href="{{ route('vaccination.show', ['patient' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-2">{{ __('Vaccination Timeline') }}</a>
                    <a href="{{ route('pediatric.medication.history') }}" class="btn btn-outline-primary w-100 mb-0">{{ __('Pediatric Medication History') }}</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">{{ __('Pediatric Snapshot') }}</h5>
                                <span class="badge bg-light text-dark">{{ __('Future-ready for growth tracking charts') }}</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Birth Weight') }}</small><strong>{{ $pediatricProfile->birth_weight ? $pediatricProfile->birth_weight . ' g' : __('Not recorded') }}</strong></div></div>
                                <div class="col-md-3"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Gestational Age') }}</small><strong>{{ $pediatricProfile->gestational_age ? $pediatricProfile->gestational_age . ' ' . __('weeks') : __('Not recorded') }}</strong></div></div>
                                <div class="col-md-3"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Growth Status') }}</small><strong>{{ __($pediatricProfile->growth_status_label) }}</strong></div></div>
                                <div class="col-md-3"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Vaccination Status') }}</small><strong>{{ $pediatricProfile->vaccination_status_label ? __($pediatricProfile->vaccination_status_label) : __('Not recorded') }}</strong></div></div>
                            </div>
                            @if(!empty($pediatricProfile->classification_labels))
                                <div class="mt-3">@foreach($pediatricProfile->classification_labels as $label)<span class="badge bg-warning text-dark me-1">{{ __($label) }}</span>@endforeach</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Growth Tracking') }}</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Measurements') }}</small><strong>{{ $patient->growth_measurements_count }}</strong></div></div>
                                <div class="col-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Latest') }}</small><strong>{{ optional($latestGrowthMeasurement?->measurement_date)->format('M d, Y') ?: __('None') }}</strong></div></div>
                            </div>
                            @forelse($recentGrowthMeasurements as $measurement)
                                <div class="border rounded p-3 mb-2">
                                    <div class="fw-semibold">{{ optional($measurement->measurement_date)->format('M d, Y') ?: __('No date') }}</div>
                                    <div class="small text-muted">{{ collect([$measurement->weight_kg ? number_format((float) $measurement->weight_kg, 1) . ' kg' : null, $measurement->length_height_cm ? number_format((float) $measurement->length_height_cm, 1) . ' cm' : null, $measurement->head_circumference_cm ? number_format((float) $measurement->head_circumference_cm, 1) . ' cm HC' : null])->filter()->join(' • ') ?: __('No measurement values') }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No growth measurements recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Vaccination Follow-up') }}</h5>
                            <div class="row g-3 mb-3">
                                <div class="col-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Vaccines') }}</small><strong>{{ $patient->vaccinations_count }}</strong></div></div>
                                <div class="col-6"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Schedule') }}</small><strong>{{ $patient->vaccinationSchedule->name ?? __('Not assigned') }}</strong></div></div>
                            </div>
                            @forelse($recentVaccinations as $vaccination)
                                <div class="border rounded p-3 mb-2">
                                    <div class="fw-semibold">{{ $vaccination->vaccine->global_name ?? __('Vaccine') }}</div>
                                    <div class="small text-muted">{{ __('Dose') }} {{ $vaccination->dose_number }} • {{ ucfirst(str_replace('_', ' ', $vaccination->status)) }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No vaccination records recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection