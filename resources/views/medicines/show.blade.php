@extends('layouts.app')

@section('title', $medicine->name . ' - ' . __('Medicine Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-pills text-primary me-2"></i>
                        {{ $medicine->name }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Medicine Details') }}</p>
                </div>
                <div>
                    <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit Medicine') }}
                    </a>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Inventory') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Medicine Information -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Medicine Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Medicine Name') }}</small>
                                    <h5 class="mb-0">{{ $medicine->name }}</h5>
                                </div>
                                
                                @if($medicine->generic_name)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Generic Name') }}</small>
                                    <div class="fw-bold">{{ $medicine->generic_name }}</div>
                                </div>
                                @endif

                                @if($medicine->brand_name)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Brand Name') }}</small>
                                    <div class="fw-bold">{{ $medicine->brand_name }}</div>
                                </div>
                                @endif

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Form') }}</small>
                                    <div class="fw-bold">{{ $medicine->form_display }}</div>
                                </div>

                                @if($medicine->dosage)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Dosage/Strength') }}</small>
                                    <div class="fw-bold">{{ $medicine->dosage }}</div>
                                </div>
                                @endif

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Status') }}</small>
                                    <div>
                                        <span class="badge bg-{{ $medicine->is_active ? 'success' : 'secondary' }} fs-6">
                                            {{ $medicine->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                        @if($medicine->is_frequent)
                                            <span class="badge bg-info fs-6 ms-1">{{ __('Frequent') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($medicine->description)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">{{ __('Description') }}</small>
                                    <p class="mb-0">{{ $medicine->description }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Medical Information -->
                    @if($medicine->side_effects || $medicine->contraindications)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Medical Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($medicine->side_effects)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Side Effects') }}</small>
                                    <p class="mb-0">{{ $medicine->side_effects }}</p>
                                </div>
                                @endif

                                @if($medicine->contraindications)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Contraindications') }}</small>
                                    <p class="mb-0">{{ $medicine->contraindications }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Usage Statistics -->
                    @if(isset($usageStats) && $usageStats['total_prescriptions'] > 0)
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('Usage Statistics') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <h4 class="text-primary mb-0">{{ $usageStats['total_prescriptions'] }}</h4>
                                        <small class="text-muted">{{ __('Total Prescriptions') }}</small>
                                    </div>
                                </div>
                            </div>

                            @if($usageStats['recent_prescriptions']->count() > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block mb-2">{{ __('Recent Prescriptions') }}</small>
                                <div class="list-group list-group-flush">
                                    @foreach($usageStats['recent_prescriptions'] as $prescriptionMedicine)
                                        <div class="list-group-item px-0 py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $prescriptionMedicine->prescription->patient->first_name }} 
                                                        {{ $prescriptionMedicine->prescription->patient->last_name }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $prescriptionMedicine->prescription->prescribed_date->format('M d, Y') }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted">
                                                        {{ $prescriptionMedicine->dosage ?? 'N/A' }} - 
                                                        {{ $prescriptionMedicine->frequency ?? 'N/A' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Medicine Actions Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                {{ __('Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>
                                    {{ __('Edit Medicine') }}
                                </a>
                                
                                <button type="button" class="btn btn-outline-{{ $medicine->is_frequent ? 'warning' : 'info' }}" 
                                        onclick="toggleFrequent({{ $medicine->id }})">
                                    <i class="fas fa-star me-2"></i>
                                    {{ $medicine->is_frequent ? __('Remove from Frequent') : __('Mark as Frequent') }}
                                </button>
                                
                                <button type="button" class="btn btn-outline-{{ $medicine->is_active ? 'secondary' : 'success' }}" 
                                        onclick="toggleStatus({{ $medicine->id }})">
                                    <i class="fas fa-{{ $medicine->is_active ? 'pause' : 'play' }} me-2"></i>
                                    {{ $medicine->is_active ? __('Deactivate') : __('Activate') }}
                                </button>
                                
                                <button type="button" class="btn btn-outline-danger" onclick="deleteMedicine({{ $medicine->id }})">
                                    <i class="fas fa-trash me-2"></i>
                                    {{ __('Delete Medicine') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Medicine Summary -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                {{ __('Summary') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Full Name') }}</small>
                                <div class="fw-bold">{{ $medicine->full_name }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Created By') }}</small>
                                <div>{{ $medicine->creator->first_name ?? 'System' }} {{ $medicine->creator->last_name ?? '' }}</div>
                            </div>
                            
                            <div class="mb-0">
                                <small class="text-muted">{{ __('Created Date') }}</small>
                                <div>{{ $medicine->created_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this medicine? This action cannot be undone.') }}</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('This medicine will be removed from all future prescriptions.') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete Medicine') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFrequent(medicineId) {
    fetch(`/medicines/${medicineId}/toggle-frequent`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function toggleStatus(medicineId) {
    fetch(`/medicines/${medicineId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteMedicine(medicineId) {
    document.getElementById('deleteForm').action = `/medicines/${medicineId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
