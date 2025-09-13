@extends('layouts.app')

@section('page-title', __('Radiology Requests'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-x-ray text-primary"></i>
                        {{ __('Radiology Requests') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage patient radiology and imaging requests') }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.radiology.tests.manage') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-cogs me-1"></i>
                        {{ __('Manage Tests') }}
                    </a>
                    <a href="{{ route('recommendations.radiology.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('New Request') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Total Requests') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['total']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-x-ray fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Pending') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['pending']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Completed') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['completed']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Urgent') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['urgent']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter"></i>
                        {{ __('Filter Requests') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recommendations.radiology.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    @foreach(\App\Models\RadiologyRequest::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="priority" class="form-label">{{ __('Priority') }}</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">{{ __('All Priorities') }}</option>
                                    @foreach(\App\Models\RadiologyRequest::PRIORITIES as $key => $label)
                                    <option value="{{ $key }}" {{ request('priority') === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }}</label>
                                <select class="form-select" id="patient_id" name="patient_id">
                                    <option value="">{{ __('All Patients') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ __('Request number, diagnosis, or patient name...') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('recommendations.radiology.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Radiology Requests List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i>
                        {{ __('Radiology Requests') }}
                    </h6>
                    <div class="text-muted small">
                        {{ __('Total: :count requests', ['count' => $radiologyRequests->total()]) }}
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($radiologyRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Tests') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($radiologyRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $request->request_number }}</div>
                                            @if($request->suspected_diagnosis)
                                            <small class="text-muted">{{ Str::limit($request->suspected_diagnosis, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $request->patient->first_name }} {{ $request->patient->last_name }}</div>
                                            <small class="text-muted">{{ $request->patient->patient_id }}</small>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @foreach($request->tests->take(2) as $test)
                                                <div>• {{ $test->test_name_display }}</div>
                                                @endforeach
                                                @if($request->tests->count() > 2)
                                                <div class="text-muted">+{{ $request->tests->count() - 2 }} more</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="{{ $request->priority_badge_class }}">{{ $request->priority_display }}</span>
                                        </td>
                                        <td>
                                            <span class="{{ $request->status_badge_class }}">{{ $request->status_display }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $request->requested_date->format('M d, Y') }}</div>
                                            @if($request->due_date)
                                            <small class="text-muted">Due: {{ $request->due_date->format('M d') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('recommendations.radiology.show', $request) }}" class="btn btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($request->status === 'pending')
                                                <a href="{{ route('recommendations.radiology.edit', $request) }}" class="btn btn-outline-warning" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                                <a href="{{ route('recommendations.radiology.pdf', $request) }}" class="btn btn-outline-danger" title="{{ __('PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @if($request->status === 'pending')
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteRequest({{ $request->id }}, '{{ $request->request_number }}')" 
                                                        title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-x-ray fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No radiology requests found') }}</h5>
                            <p class="text-muted">{{ __('Create your first radiology request to get started.') }}</p>
                            <a href="{{ route('recommendations.radiology.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Radiology Request') }}
                            </a>
                        </div>
                    @endif
                </div>
                @if($radiologyRequests->hasPages())
                <div class="card-footer">
                    {{ $radiologyRequests->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteRequest(requestId, requestNumber) {
    // Show confirmation dialog
    if (confirm(`{{ __('Are you sure you want to delete the radiology request') }} "${requestNumber}"?\n\n{{ __('This action cannot be undone and will permanently remove all associated tests and data.') }}`)) {
        
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/radiology/${requestId}`;
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override for DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@endsection
