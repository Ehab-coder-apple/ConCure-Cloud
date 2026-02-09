@extends('layouts.app')

@section('title', __('Simple Prescriptions'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-prescription me-2"></i>
                            {{ __('Simple Prescriptions') }}
                        </h5>
                        <a href="{{ route('simple-prescriptions.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Prescription') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-filter me-2"></i>
                                {{ __('Filter Prescriptions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('simple-prescriptions.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <label for="patient_name" class="form-label">{{ __('Patient Name') }}</label>
                                    <input type="text" class="form-control" id="patient_name" name="patient_name"
                                           value="{{ request('patient_name') }}"
                                           placeholder="{{ __('Search by patient name or ID') }}">
                                </div>

                                <div class="col-md-3">
                                    <label for="patient_id" class="form-label">{{ __('Select Patient') }}</label>
                                    <select class="form-select" id="patient_id" name="patient_id">
                                        <option value="">{{ __('All Patients') }}</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from"
                                           value="{{ request('date_from') }}">
                                </div>

                                <div class="col-md-2">
                                    <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to"
                                           value="{{ request('date_to') }}">
                                </div>

                                <div class="col-md-2">
                                    <label for="status" class="form-label">{{ __('Status') }}</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">{{ __('All Status') }}</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>
                                        {{ __('Filter') }}
                                    </button>
                                    <a href="{{ route('simple-prescriptions.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Clear') }}
                                    </a>
                                    @if(request()->hasAny(['patient_name', 'patient_id', 'date_from', 'date_to', 'status']))
                                        <span class="badge bg-info ms-2">
                                            {{ $prescriptions->total() }} {{ __('results found') }}
                                        </span>
                                    @endif
                                </div>
                            </form>


                        </div>
                    </div>

                    @if($prescriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Prescription #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Diagnosis') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescriptions as $prescription)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $prescription->prescription_number }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $prescription->patient->patient_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                {{ Str::limit($prescription->diagnosis ?? 'No diagnosis', 50) }}
                                            </td>
                                            <td>
                                                {{ $prescription->prescribed_date->format('M d, Y') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $prescription->status === 'active' ? 'success' : ($prescription->status === 'completed' ? 'primary' : 'secondary') }}">
                                                    {{ ucfirst($prescription->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('simple-prescriptions.show', $prescription->id) }}" 
                                                       class="btn btn-outline-primary" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('simple-prescriptions.edit', $prescription->id) }}" 
                                                       class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('simple-prescriptions.destroy', $prescription->id) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('{{ __('Are you sure you want to delete this prescription?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $prescriptions->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-prescription fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No prescriptions found') }}</h5>
                            <p class="text-muted">{{ __('Create your first prescription to get started.') }}</p>
                            <a href="{{ route('simple-prescriptions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create First Prescription') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Real-time patient name search
document.getElementById('patient_name').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const patientSelect = document.getElementById('patient_id');

    // Clear patient dropdown when typing in search
    if (searchTerm.length > 0) {
        patientSelect.value = '';
    }
});

// Clear search when selecting from dropdown
document.getElementById('patient_id').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('patient_name').value = '';
    }
});

// Auto-submit form on patient selection
document.getElementById('patient_id').addEventListener('change', function() {
    if (this.value) {
        this.form.submit();
    }
});


</script>


@endpush
