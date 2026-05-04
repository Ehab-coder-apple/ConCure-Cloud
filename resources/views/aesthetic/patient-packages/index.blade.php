@extends('layouts.app')

@section('title', __('Patient Packages'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-check text-primary me-2"></i>
                        {{ __('Patient Packages') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Track package usage and remaining sessions for patients') }}</p>
                </div>
                <a href="{{ route('aesthetic.patient-packages.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Assign Package') }}
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-box-open fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Packages') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-play-circle fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $stats['active'] }}</h4>
                            <small class="text-muted">{{ __('Active (Remaining Sessions)') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-secondary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['completed'] }}</h4>
                            <small class="text-muted">{{ __('Completed') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.patient-packages.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Search by patient name or package...') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Patient') }}</label>
                                <select class="form-select" name="patient_id">
                                    <option value="">{{ __('All Patients') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        {{ __('Filter') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Packages Table -->
            <div class="card">
                <div class="card-body">
                    @if($packages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Package') }}</th>
                                        <th>{{ __('Progress') }}</th>
                                        <th>{{ __('Sessions') }}</th>
                                        <th>{{ __('Remaining') }}</th>
                                        <th>{{ __('Purchase Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packages as $patientPackage)
                                    <tr>
                                        <td>
                                            <strong>{{ $patientPackage->patient->first_name }} {{ $patientPackage->patient->last_name }}</strong>
                                            @if($patientPackage->patient->phone)
                                                <small class="text-muted d-block">{{ $patientPackage->patient->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $patientPackage->package->name }}</strong>
                                            @if($patientPackage->package->treatments->count() > 0)
                                                <small class="d-block text-muted">
                                                    @foreach($patientPackage->package->treatments as $pt)
                                                        {{ $pt->name }}{{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                </small>
                                            @elseif($patientPackage->package->treatment)
                                                <small class="d-block text-muted">{{ $patientPackage->package->treatment->name }}</small>
                                            @endif
                                        </td>
                                        <td style="min-width: 150px;">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $patientPackage->is_active ? 'bg-success' : 'bg-secondary' }}"
                                                     role="progressbar"
                                                     style="width: {{ $patientPackage->usage_percentage }}%"
                                                     aria-valuenow="{{ $patientPackage->usage_percentage }}"
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    {{ $patientPackage->usage_percentage }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $patientPackage->sessions_used }} / {{ $patientPackage->sessions_used + $patientPackage->sessions_remaining }}</span>
                                        </td>
                                        <td>
                                            @if($patientPackage->sessions_remaining > 0)
                                                <span class="badge bg-success">{{ $patientPackage->sessions_remaining }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $patientPackage->purchase_date->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @if($patientPackage->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Completed') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if($patientPackage->is_active)
                                                    <form method="POST" action="{{ route('aesthetic.patient-packages.use-session', $patientPackage) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success"
                                                                title="{{ __('Use Session') }}">
                                                            <i class="fas fa-minus-circle"></i>
                                                            {{ __('Use') }}
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('aesthetic.patient-packages.edit', $patientPackage) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('aesthetic.patient-packages.destroy', $patientPackage) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to remove this package from the patient?') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $packages->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No patient packages found') }}</h5>
                            <p class="text-muted">{{ __('Assign a package to a patient to get started.') }}</p>
                            <a href="{{ route('aesthetic.patient-packages.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Assign First Package') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
