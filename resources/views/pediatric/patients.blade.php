@extends('layouts.app')

@section('title', __('Pediatric Growth Charts'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-baby me-2 text-success"></i>
                        {{ __('Pediatric Growth Charts') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Select a patient to view or record growth measurements') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="row mb-3">
        <div class="col-md-6 col-lg-4">
            <form method="GET" action="{{ route('pediatric.patients') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search by name, ID, or phone...') }}" value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ route('pediatric.patients') }}" class="btn btn-outline-danger">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Patients List -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        {{ __('Pediatric Patients') }} ({{ $patients->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Age') }}</th>
                                        <th>{{ __('Gender') }}</th>
                                        <th>{{ __('Measurements') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                        <tr>
                                            <td><strong>{{ $patient->patient_id }}</strong></td>
                                            <td>
                                                <a href="{{ route('patients.show', $patient) }}" class="text-decoration-none">
                                                    {{ $patient->full_name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $patient->age }} {{ __('yrs') }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ strtolower($patient->gender) === 'female' ? 'danger' : 'primary' }}">
                                                    <i class="fas fa-{{ strtolower($patient->gender) === 'female' ? 'venus' : 'mars' }} me-1"></i>
                                                    {{ ucfirst($patient->gender ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($patient->growth_measurements_count > 0)
                                                    <span class="badge bg-success">{{ $patient->growth_measurements_count }} {{ __('records') }}</span>
                                                @else
                                                    <span class="text-muted">{{ __('None') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('pediatric.growth-chart', $patient) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    {{ __('Growth Chart') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $patients->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-baby fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Pediatric Patients Found') }}</h5>
                            <p class="text-muted">{{ __('No patients aged 20 or under were found matching your criteria.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

