@extends('layouts.app')

@section('title', __('Nutrition Module') . ' - ' . $patient->full_name)

@php
    $goalSummary = $nutritionProfile->goals;
    if (blank($goalSummary) && $activeGoal) {
        $goalSummary = collect([
            $activeGoal->target_weight ? __('Target weight: :value kg', ['value' => $activeGoal->target_weight]) : null,
            $activeGoal->target_bmi ? __('Target BMI: :value', ['value' => $activeGoal->target_bmi]) : null,
            $activeGoal->notes,
        ])->filter()->implode(' • ');
    }
@endphp

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-apple-whole me-2 text-primary"></i>{{ __('Nutrition Module') }}</h3>
            <p class="text-muted mb-0">{{ __('Capture the patient nutrition summary, review progress over time, and connect the nutrition workflow with follow-up visits.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <a href="{{ route('patient.nutrition', ['patient' => $patient->id]) }}" class="btn btn-primary">{{ __('Open Direct Nutrition Route') }}</a>
        </div>
    </div>

    <div class="alert alert-light border mb-4">
        <i class="fas fa-share-alt me-2 text-primary"></i>{{ __('Use this module for diet type, goals, and nutrition notes only. Shared diseases and chronic conditions stay in Medical Overview.') }}
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Nutrition Summary') }}</h5>
                    <form method="POST" action="{{ route('patients.nutrition.update', ['patient' => $patient->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3"><label class="form-label">{{ __('Height (cm)') }}</label><input class="form-control" name="height" type="number" step="0.01" min="30" max="300" value="{{ old('height', $nutritionProfile->height) }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('Weight (kg)') }}</label><input class="form-control" name="weight" type="number" step="0.01" min="1" max="500" value="{{ old('weight', $nutritionProfile->weight) }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('BMI') }}</label><input class="form-control" value="{{ $nutritionProfile->bmi ? number_format((float) $nutritionProfile->bmi, 1) : __('Auto-calculated after save') }}" disabled></div>
                        <div class="mb-3"><label class="form-label">{{ __('Diet Type') }}</label><input class="form-control" name="diet_type" value="{{ old('diet_type', $nutritionProfile->diet_type) }}" placeholder="{{ __('e.g. Balanced, Weight loss, Diabetic, Vegan') }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('Goals') }}</label><textarea class="form-control" name="goals" rows="3" placeholder="{{ __('Weight goals, adherence goals, behavior goals...') }}">{{ old('goals', $nutritionProfile->goals) }}</textarea></div>
                        <div class="mb-3"><label class="form-label">{{ __('Notes') }}</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $nutritionProfile->notes) }}</textarea></div>
                        <button class="btn btn-primary" type="submit">{{ __('Save Nutrition Summary') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Nutrition Tools') }}</h5>
                    <a href="{{ route('nutrition.progress.dashboard', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary w-100 mb-2">{{ __('Open Progress Dashboard') }}</a>
                    <a href="{{ route('nutrition.index', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary w-100">{{ __('Open Nutrition Plans') }}</a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Nutrition Snapshot') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Diet Plans') }}</small><strong>{{ $patient->diet_plans_count }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Progress Records') }}</small><strong>{{ $patient->nutrition_progress_measurements_count }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Last Visit') }}</small><strong>{{ $lastVisitLabel }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Height') }}</small><strong>{{ $nutritionProfile->height ? $nutritionProfile->height . ' cm' : __('Not recorded') }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Weight') }}</small><strong>{{ $nutritionProfile->weight ? $nutritionProfile->weight . ' kg' : __('Not recorded') }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('BMI') }}</small><strong>{{ $nutritionProfile->bmi ? number_format((float) $nutritionProfile->bmi, 1) : __('Not recorded') }}</strong></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Diet & Goals') }}</h5>
                            <div class="border rounded p-3 mb-2"><small class="text-muted d-block">{{ __('Diet Type') }}</small><div>{{ $nutritionProfile->diet_type ?: __('No diet type recorded.') }}</div></div>
                            <div class="border rounded p-3 mb-2"><small class="text-muted d-block">{{ __('Goals') }}</small><div>{{ $goalSummary ?: __('No goals recorded yet.') }}</div></div>
                            <div class="border rounded p-3"><small class="text-muted d-block">{{ __('Notes') }}</small><div>{{ $nutritionProfile->notes ?: __('No nutrition notes recorded yet.') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Progress Over Time') }}</h5>
                            @forelse($recentMeasurements as $measurement)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div class="fw-semibold">{{ optional($measurement->measurement_date)->format('M d, Y') ?: __('No date') }}</div>
                                        <div class="small text-muted">{{ __('BMI') }}: {{ $measurement->bmi ? number_format((float) $measurement->bmi, 1) : __('N/A') }}</div>
                                    </div>
                                    <div class="small text-muted mt-1">{{ __('Weight') }}: {{ $measurement->weight_kg ?: __('N/A') }} kg • {{ __('Height') }}: {{ $measurement->height_cm ?: __('N/A') }} cm</div>
                                    @if($measurement->notes)
                                        <div class="small mt-2 text-muted">{{ $measurement->notes }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No nutrition progress entries recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Linked Visits & Nutrition Follow-up') }}</h5>
                            <p class="text-muted">{{ __('Nutrition stays focused on diet and goals, while visits keep the encounter-based follow-up timeline.') }}</p>
                            @forelse($recentVisits as $visit)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $visit->hpi->chief_complaint ?? $visit->reason_for_visit ?? __('Visit') }}</div>
                                            <div class="small text-muted">{{ optional($visit->visit_date)->format('M d, Y h:i A') }} • {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</div>
                                        </div>
                                        <a href="{{ route('patients.visits.show', ['patient' => $patient->id, 'visit' => $visit->id]) }}" class="btn btn-sm btn-outline-secondary">{{ __('Open Visit') }}</a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No visit-based follow-up has been added yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection