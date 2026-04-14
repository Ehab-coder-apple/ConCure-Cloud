@extends('layouts.app')

@section('title', __('ENT Module') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-notes-medical me-2 text-primary"></i>{{ __('ENT Module') }}</h3>
            <p class="text-muted mb-0">{{ __('ENT-specific issues, notes, visit context, and supporting audiometry or scan files for this patient.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <a href="{{ route('patient.ent', ['patient' => $patient->id]) }}" class="btn btn-primary">{{ __('Open Direct ENT Route') }}</a>
        </div>
    </div>

    <div class="alert alert-light border mb-4">
        <i class="fas fa-share-alt me-2 text-primary"></i>{{ __('Shared allergies and other general medical data remain in Medical Overview. This module stores ENT-specific findings only.') }}
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('ENT Summary') }}</h5>
                    <form method="POST" action="{{ route('patients.ent.update', ['patient' => $patient->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3"><label class="form-label">{{ __('Hearing Issues') }}</label><textarea class="form-control" name="hearing_issues" rows="3">{{ old('hearing_issues', $entProfile->hearing_issues) }}</textarea></div>
                        <div class="mb-3"><label class="form-label">{{ __('Nasal Obstruction / Nasal Issues') }}</label><textarea class="form-control" name="nasal_issues" rows="3">{{ old('nasal_issues', $entProfile->nasal_issues) }}</textarea></div>
                        <div class="mb-3"><label class="form-label">{{ __('Throat Problems') }}</label><textarea class="form-control" name="throat_issues" rows="3">{{ old('throat_issues', $entProfile->throat_issues) }}</textarea></div>
                        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="dizziness" name="dizziness" value="1" {{ old('dizziness', $entProfile->dizziness) ? 'checked' : '' }}><label class="form-check-label" for="dizziness">{{ __('Dizziness / Vertigo') }}</label></div>
                        <div class="mb-3"><label class="form-label">{{ __('ENT Notes') }}</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $entProfile->notes) }}</textarea></div>
                        <button class="btn btn-primary" type="submit">{{ __('Save ENT Summary') }}</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Upload ENT Files') }}</h5>
                    <form method="POST" action="{{ route('patients.upload', ['patient' => $patient->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('File Type') }}</label>
                            <select class="form-select" name="category" required>
                                <option value="ent_audiometry">{{ __('Audiometry') }}</option>
                                <option value="ent_scan">{{ __('ENT Scan / Imaging') }}</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">{{ __('Description') }}</label><input class="form-control" name="description" value="{{ old('description') }}" placeholder="{{ __('Optional file note') }}"></div>
                        <div class="mb-3"><label class="form-label">{{ __('Choose File') }}</label><input class="form-control" type="file" name="file" required></div>
                        <button class="btn btn-outline-primary" type="submit">{{ __('Upload File') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('ENT Snapshot') }}</h5>
                            <div class="row g-3">
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Issues Logged') }}</small><strong>{{ $entProfile->issue_count }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Dizziness') }}</small><strong>{{ $entProfile->dizziness ? __('Yes') : __('No') }}</strong></div></div>
                                <div class="col-md-4"><div class="bg-light border rounded p-3 h-100"><small class="text-muted d-block">{{ __('ENT Files') }}</small><strong>{{ $entFileCount }}</strong></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('ENT Findings Summary') }}</h5>
                            <div class="border rounded p-3 mb-2"><small class="text-muted d-block">{{ __('Hearing Issues') }}</small><div>{{ $entProfile->hearing_issues ?: __('No hearing issues recorded.') }}</div></div>
                            <div class="border rounded p-3 mb-2"><small class="text-muted d-block">{{ __('Nasal Obstruction') }}</small><div>{{ $entProfile->nasal_issues ?: __('No nasal issues recorded.') }}</div></div>
                            <div class="border rounded p-3"><small class="text-muted d-block">{{ __('Throat Problems') }}</small><div>{{ $entProfile->throat_issues ?: __('No throat problems recorded.') }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('ENT Files') }}</h5>
                            @forelse($entFiles as $file)
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between gap-2 align-items-start">
                                        <div>
                                            <div class="fw-semibold">{{ $file->original_name }}</div>
                                            <div class="small text-muted">{{ $file->category_display }} • {{ $file->file_size_human }} • {{ optional($file->created_at)->format('M d, Y') ?: __('No date') }}</div>
                                        </div>
                                        <a href="{{ $file->file_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">{{ __('Open') }}</a>
                                    </div>
                                    @if($file->description)
                                        <div class="small mt-2 text-muted">{{ $file->description }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No ENT files uploaded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">{{ __('Linked Visits & ENT Notes Context') }}</h5>
                            <p class="text-muted">{{ __('ENT notes stay in this module, while visit records provide encounter-specific context without duplicating shared medical overview data.') }}</p>
                            <div class="mb-3"><span class="badge bg-light text-dark">{{ __('Recent visits loaded: :count', ['count' => $visitContextCount]) }}</span></div>
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
                                <p class="text-muted mb-0">{{ __('No visits linked yet. Visit-based HPI entries will appear here for ENT context.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Audiometry Tests Section --}}
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-volume-high text-primary me-2"></i>{{ __('Audiometry Tests') }}</h6>
                <a href="{{ route('ent.audiometry.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus me-1"></i>{{ __('Add Audiometry Test') }}
                </a>
            </div>
            <div class="card-body">
                @if($audiometryTests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Test Date') }}</th>
                                    <th>{{ __('Test Type') }}</th>
                                    <th>{{ __('Right Ear') }}</th>
                                    <th>{{ __('Left Ear') }}</th>
                                    <th>{{ __('ENT Record') }}</th>
                                    <th>{{ __('Performed By') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($audiometryTests as $test)
                                <tr>
                                    <td>{{ $test->test_date->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $test->test_type_display }}</span>
                                    </td>
                                    <td>
                                        @if($test->right_interpretation)
                                            <span class="badge {{ $test->right_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $test->right_interpretation_display }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($test->left_interpretation)
                                            <span class="badge {{ $test->left_interpretation === 'normal' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $test->left_interpretation_display }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($test->ent_record_id)
                                            <a href="{{ route('ent.show', $test->ent_record_id) }}" class="text-decoration-none">
                                                <i class="fas fa-link me-1"></i>{{ __('Linked') }}
                                            </a>
                                        @else
                                            <span class="text-muted">{{ __('Standalone') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $test->performer?->full_name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('ent.audiometry.show', $test) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-chart-line me-1"></i>{{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">
                        {{ __('No audiometry tests recorded for this patient.') }}
                        <a href="{{ route('ent.audiometry.create', ['patient_id' => $patient->id]) }}">{{ __('Create the first test') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection