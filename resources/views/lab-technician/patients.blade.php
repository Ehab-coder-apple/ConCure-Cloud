@extends('layouts.app')

@section('title', __('Upload Patient Lab Results'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i>
                        {{ __('Upload Patient Lab Results') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Search for patients with lab requests and upload results') }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.lab-technician.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Dashboard') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('recommendations.lab-technician.patients') }}">
                        <div class="input-group">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="{{ __('Search by patient name, ID, phone, or email...') }}"
                                   value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Search') }}
                            </button>
                            @if(request('search'))
                                <a href="{{ route('recommendations.lab-technician.patients') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Patients') }}
                        @if(request('search'))
                            <small class="text-muted">({{ __('Search results for') }}: "{{ request('search') }}")</small>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if(!request('search'))
                        <!-- Show prompt when no search has been performed -->
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-4x text-primary mb-4"></i>
                            <h4 class="text-dark mb-3">{{ __('Search for a Patient') }}</h4>
                            <p class="text-muted mb-4">
                                {{ __('Enter a patient name, ID, phone number, or email in the search box above to find patients.') }}
                            </p>
                            <div class="alert alert-info d-inline-block" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Search to upload results for system lab requests or manual/verbal requests.') }}
                            </div>
                        </div>
                    @elseif($patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Gender') }}</th>
                                        <th>{{ __('Age') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                    <tr>
                                        <td>
                                            <strong>{{ $patient->patient_id }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $patient->full_name }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $patient->phone ?? __('N/A') }}</td>
                                        <td>{{ $patient->email ?? __('N/A') }}</td>
                                        <td>
                                            @if($patient->gender === 'male')
                                                <i class="fas fa-mars text-primary"></i> {{ __('Male') }}
                                            @elseif($patient->gender === 'female')
                                                <i class="fas fa-venus text-danger"></i> {{ __('Female') }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($patient->date_of_birth)
                                                {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} {{ __('years') }}
                                            @else
                                                {{ __('N/A') }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('recommendations.lab-technician.patients.files', $patient->id) }}"
                                               class="btn btn-sm btn-primary"
                                               title="{{ __('Upload Lab Result') }}">
                                                <i class="fas fa-upload me-1"></i>
                                                {{ __('Upload Result') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $patients->appends(['search' => request('search')])->links() }}
                        </div>
                    @else
                        <!-- No results found after search -->
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No patients found') }}</h5>
                            <p class="text-muted mb-3">
                                {{ __('No patients match your search criteria.') }}
                            </p>
                            <p class="text-muted small">
                                {{ __('Try adjusting your search terms or check the patient information.') }}
                            </p>
                            <a href="{{ route('recommendations.lab-technician.patients') }}" class="btn btn-outline-primary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear Search') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

