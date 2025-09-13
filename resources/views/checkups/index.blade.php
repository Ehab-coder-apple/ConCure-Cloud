@extends('layouts.app')

@section('page-title', __('Patient Checkups') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-heartbeat text-danger"></i>
                        {{ __('Checkups') }} - {{ $patient->full_name }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Patient ID:') }} {{ $patient->patient_id }}</p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                    <a href="{{ route('checkups.create', $patient) }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Checkup') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('checkups.index', $patient) }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-4">
                                <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('checkups.index', $patient) }}" class="btn btn-outline-secondary">
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

    <!-- Checkups List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Checkup History') }}
                        <span class="badge bg-secondary ms-2">{{ $checkups->total() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($checkups->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Vital Signs') }}</th>
                                        <th>{{ __('Measurements') }}</th>
                                        <th>{{ __('Symptoms') }}</th>
                                        <th>{{ __('Recorded By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($checkups as $checkup)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @if($checkup->blood_pressure)
                                                    <div><strong>BP:</strong> {{ $checkup->blood_pressure }}</div>
                                                @endif
                                                @if($checkup->heart_rate)
                                                    <div><strong>HR:</strong> {{ $checkup->heart_rate }} bpm</div>
                                                @endif
                                                @if($checkup->temperature)
                                                    <div><strong>Temp:</strong> {{ $checkup->temperature }}°C</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @if($checkup->weight)
                                                    <div><strong>Weight:</strong> {{ $checkup->weight }} kg</div>
                                                @endif
                                                @if($checkup->height)
                                                    <div><strong>Height:</strong> {{ $checkup->height }} cm</div>
                                                @endif
                                                @if($checkup->bmi)
                                                    <div><strong>BMI:</strong> {{ $checkup->bmi }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($checkup->symptoms)
                                                <span class="small">{{ Str::limit($checkup->symptoms, 50) }}</span>
                                            @else
                                                <span class="text-muted small">{{ __('None recorded') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                {{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('checkups.show', [$patient, $checkup]) }}" 
                                                   class="btn btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('checkups.edit', [$patient, $checkup]) }}" 
                                                   class="btn btn-outline-warning" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="deleteCheckup({{ $checkup->id }})" title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($checkups->hasPages())
                        <div class="card-footer">
                            {{ $checkups->links() }}
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-heartbeat fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No checkups found') }}</h5>
                            <p class="text-muted mb-4">{{ __('No checkups have been recorded for this patient yet.') }}</p>
                            <a href="{{ route('checkups.create', $patient) }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Checkup') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('Confirm Delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ __('Are you sure you want to delete this checkup? This action cannot be undone.') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function deleteCheckup(checkupId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `{{ route('checkups.index', $patient) }}/${checkupId}`;
    modal.show();
}
</script>
@endsection
