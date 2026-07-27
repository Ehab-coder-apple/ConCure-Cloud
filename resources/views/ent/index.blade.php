@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-notes-medical me-2"></i>{{ __('ENT Records') }}</h2>
                <a href="{{ route('ent.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('New ENT Record') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('ent.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">{{ __('Patient') }}</label>
                    <select name="patient_id" class="form-select">
                        <option value="">{{ __('All Patients') }}</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                            {{ $patient->full_name }} ({{ $patient->patient_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search diagnosis, complaint...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>{{ __('Filter') }}
                    </button>
                    <a href="{{ route('ent.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-1"></i>{{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card">
        <div class="card-body">
            @if($entRecords->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Patient') }}</th>
                            <th>{{ __('Chief Complaint') }}</th>
                            <th>{{ __('Diagnosis') }}</th>
                            <th>{{ __('Doctor') }}</th>
                            <th>{{ __('Audiometry') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entRecords as $record)
                        <tr>
                            <td>{{ $record->visit_date->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('patients.show', $record->patient) }}">
                                    {{ $record->patient->full_name }}
                                </a>
                                <br><small class="text-muted">{{ $record->patient->patient_id }}</small>
                            </td>
                            <td>{{ Str::limit($record->chief_complaint, 50) }}</td>
                            <td>{{ Str::limit($record->diagnosis, 50) }}</td>
                            <td>{{ $record->doctor->full_name }}</td>
                            <td>
                                @if($record->audiometryTests->count() > 0)
                                    <span class="badge bg-success">{{ $record->audiometryTests->count() }} test(s)</span>
                                @else
                                    <span class="badge bg-secondary">None</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('ent.show', $record) }}" class="btn btn-sm btn-info" title="{{ __('View') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('ent.edit', $record) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $entRecords->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">{{ __('No ENT records found.') }}</p>
                <a href="{{ route('ent.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>{{ __('Create First Record') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
