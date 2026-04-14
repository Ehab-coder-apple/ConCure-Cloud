@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-volume-high me-2"></i>{{ __('Audiometry Tests') }}</h2>
                    <p class="text-muted">{{ __('All audiometry tests for your clinic') }}</p>
                </div>
                <a href="{{ route('ent.audiometry.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('New Audiometry Test') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('ent.audiometry.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="patient_id" class="form-label">{{ __('Patient') }}</label>
                    <select name="patient_id" id="patient_id" class="form-select">
                        <option value="">{{ __('All Patients') }}</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} ({{ $patient->patient_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="search" class="form-label">{{ __('Search') }}</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="{{ __('Search patient name, test type, notes...') }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>{{ __('Filter') }}
                    </button>
                    <a href="{{ route('ent.audiometry.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>{{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card">
        <div class="card-body">
            @if($audiometryTests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ __('Test Date') }}</th>
                                <th>{{ __('Patient') }}</th>
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
                                    <a href="{{ route('patients.show', $test->patient) }}">
                                        {{ $test->patient->full_name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $test->patient->patient_id }}</small>
                                </td>
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
                                        <span class="badge bg-secondary">{{ __('Standalone') }}</span>
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

                <div class="mt-3">
                    {{ $audiometryTests->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-volume-high fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No audiometry tests found') }}</h5>
                    <p class="text-muted">
                        @if(request()->has('search') || request()->has('patient_id'))
                            {{ __('Try adjusting your filters') }}
                        @else
                            {{ __('Start by creating your first audiometry test') }}
                        @endif
                    </p>
                    <a href="{{ route('ent.audiometry.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-1"></i>{{ __('Create First Test') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
