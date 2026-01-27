@extends('layouts.app')

@section('title', __('External Laboratories'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ __('External Laboratories') }}</h1>
                    <p class="text-muted mb-0">{{ __('Manage preferred external laboratories for lab requests') }}</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabModal">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add Laboratory') }}
                </button>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('external-labs.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('Lab name, phone, email...') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="lab_type" class="form-label">{{ __('Lab Type') }}</label>
                            <select class="form-select" id="lab_type" name="lab_type">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="medical" {{ request('lab_type') == 'medical' ? 'selected' : '' }}>{{ __('Medical Lab') }}</option>
                                <option value="dental" {{ request('lab_type') == 'dental' ? 'selected' : '' }}>{{ __('Dental Lab') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('external-labs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- External Labs List -->
            <div class="card">
                <div class="card-body">
                    @if($externalLabs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Laboratory Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Contact Information') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Sort Order') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($externalLabs as $lab)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $lab->name }}</strong>
                                                @if($lab->website)
                                                    <br>
                                                    <a href="{{ $lab->website }}" target="_blank" class="text-primary small">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        {{ __('Website') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($lab->lab_type === 'dental')
                                                <span class="badge bg-info">
                                                    <i class="fas fa-tooth me-1"></i>
                                                    {{ __('Dental') }}
                                                </span>
                                            @else
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-flask me-1"></i>
                                                    {{ __('Medical') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lab->phone)
                                                <div><i class="fas fa-phone me-1"></i> {{ $lab->phone }}</div>
                                            @endif
                                            @if($lab->email)
                                                <div><i class="fas fa-envelope me-1"></i> {{ $lab->email }}</div>
                                            @endif
                                            @if($lab->address)
                                                <div><i class="fas fa-map-marker-alt me-1"></i> {{ Str::limit($lab->address, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lab->is_active)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $lab->sort_order }}</span>
                                        </td>
                                        <td>
                                            {{ $lab->creator->full_name }}
                                            <br>
                                            <small class="text-muted">{{ $lab->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        title="{{ __('Edit') }}"
                                                        onclick="editLab({{ $lab->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-{{ $lab->is_active ? 'warning' : 'success' }}" 
                                                        title="{{ $lab->is_active ? __('Deactivate') : __('Activate') }}"
                                                        onclick="toggleStatus({{ $lab->id }})">
                                                    <i class="fas fa-{{ $lab->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        title="{{ __('Delete') }}"
                                                        onclick="deleteLab({{ $lab->id }})">
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
                        <div class="d-flex justify-content-center mt-4">
                            {{ $externalLabs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No external laboratories found') }}</h5>
                            <p class="text-muted mb-4">{{ __('Start by adding your first preferred laboratory.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Laboratory') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Lab Modal -->
<div class="modal fade" id="newLabModal" tabindex="-1" aria-labelledby="newLabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('external-labs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newLabModalLabel">{{ __('Add External Laboratory') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">{{ __('Laboratory Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="lab_type" class="form-label">{{ __('Lab Type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="lab_type" name="lab_type" required>
                                <option value="medical" selected>{{ __('Medical Lab') }}</option>
                                <option value="dental">{{ __('Dental Lab') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-4">
                            <label for="whatsapp" class="form-label">{{ __('WhatsApp') }}</label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                   placeholder="{{ __('e.g., +9647595432033') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="website" class="form-label">{{ __('Website') }}</label>
                        <input type="url" class="form-control" id="website" name="website" placeholder="https://">
                    </div>

                    <div class="mt-3">
                        <label for="address" class="form-label">{{ __('Address') }}</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>

                    <div class="mt-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"
                                  placeholder="{{ __('Any additional notes about this laboratory...') }}"></textarea>
                    </div>

                    <!-- Dental-specific fields (shown only for dental labs) -->
                    <div id="dental_fields" style="display: none;">
                        <hr class="my-3">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-tooth me-2"></i>
                            {{ __('Dental Lab Specific Information') }}
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="turnaround_days" class="form-label">{{ __('Average Turnaround (Days)') }}</label>
                                <input type="number" class="form-control" id="turnaround_days" name="turnaround_days" min="1">
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="accepts_digital_impressions"
                                               name="accepts_digital_impressions" value="1">
                                        <label class="form-check-label" for="accepts_digital_impressions">
                                            {{ __('Accepts Digital Impressions') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="equipment_capabilities" class="form-label">{{ __('Equipment & Capabilities') }}</label>
                            <textarea class="form-control" id="equipment_capabilities" name="equipment_capabilities"
                                      rows="2" placeholder="{{ __('e.g., CAD/CAM, 3D Printing, Milling Machine...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Add Laboratory') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Lab Modal -->
<div class="modal fade" id="editLabModal" tabindex="-1" aria-labelledby="editLabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editLabForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editLabModalLabel">{{ __('Edit External Laboratory') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">{{ __('Laboratory Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_lab_type" class="form-label">{{ __('Lab Type') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_lab_type" name="lab_type" required>
                                <option value="medical">{{ __('Medical Lab') }}</option>
                                <option value="dental">{{ __('Dental Lab') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_sort_order" class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="edit_phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_whatsapp" class="form-label">{{ __('WhatsApp') }}</label>
                            <input type="text" class="form-control" id="edit_whatsapp" name="whatsapp"
                                   placeholder="{{ __('e.g., +9647595432033') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="edit_email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="edit_website" class="form-label">{{ __('Website') }}</label>
                        <input type="url" class="form-control" id="edit_website" name="website" placeholder="https://">
                    </div>

                    <div class="mt-3">
                        <label for="edit_address" class="form-label">{{ __('Address') }}</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                    </div>

                    <div class="mt-3">
                        <label for="edit_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                    </div>

                    <!-- Dental-specific fields (shown only for dental labs) -->
                    <div id="edit_dental_fields" style="display: none;">
                        <hr class="my-3">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-tooth me-2"></i>
                            {{ __('Dental Lab Specific Information') }}
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_turnaround_days" class="form-label">{{ __('Average Turnaround (Days)') }}</label>
                                <input type="number" class="form-control" id="edit_turnaround_days" name="turnaround_days" min="1">
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_accepts_digital_impressions"
                                               name="accepts_digital_impressions" value="1">
                                        <label class="form-check-label" for="edit_accepts_digital_impressions">
                                            {{ __('Accepts Digital Impressions') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="edit_equipment_capabilities" class="form-label">{{ __('Equipment & Capabilities') }}</label>
                            <textarea class="form-control" id="edit_equipment_capabilities" name="equipment_capabilities"
                                      rows="2" placeholder="{{ __('e.g., CAD/CAM, 3D Printing, Milling Machine...') }}"></textarea>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">
                                {{ __('Active') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update Laboratory') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Show/hide dental fields in create modal based on lab type selection
document.getElementById('lab_type').addEventListener('change', function() {
    const dentalFields = document.getElementById('dental_fields');
    if (this.value === 'dental') {
        dentalFields.style.display = 'block';
    } else {
        dentalFields.style.display = 'none';
    }
});

// Show/hide dental fields in edit modal based on lab type selection
document.getElementById('edit_lab_type').addEventListener('change', function() {
    const dentalFields = document.getElementById('edit_dental_fields');
    if (this.value === 'dental') {
        dentalFields.style.display = 'block';
    } else {
        dentalFields.style.display = 'none';
    }
});

function editLab(labId) {
    // Fetch lab data and populate edit modal
    fetch(`/external-labs/${labId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Lab data received:', data);
            if (data.success) {
                const lab = data.lab;
                document.getElementById('edit_name').value = lab.name || '';
                document.getElementById('edit_lab_type').value = lab.lab_type || 'medical';
                document.getElementById('edit_phone').value = lab.phone || '';
                document.getElementById('edit_whatsapp').value = lab.whatsapp || '';
                document.getElementById('edit_email').value = lab.email || '';
                document.getElementById('edit_website').value = lab.website || '';
                document.getElementById('edit_address').value = lab.address || '';
                document.getElementById('edit_notes').value = lab.notes || '';
                document.getElementById('edit_sort_order').value = lab.sort_order || 0;
                document.getElementById('edit_is_active').checked = lab.is_active;

                // Populate dental-specific fields
                const dentalFields = document.getElementById('edit_dental_fields');
                if (lab.lab_type === 'dental') {
                    dentalFields.style.display = 'block';
                    document.getElementById('edit_turnaround_days').value = lab.turnaround_days || '';
                    document.getElementById('edit_accepts_digital_impressions').checked = lab.accepts_digital_impressions || false;
                    document.getElementById('edit_equipment_capabilities').value = lab.equipment_capabilities || '';
                } else {
                    dentalFields.style.display = 'none';
                }

                // Update form action
                document.getElementById('editLabForm').action = `/external-labs/${labId}`;

                // Show modal
                new bootstrap.Modal(document.getElementById('editLabModal')).show();
            } else {
                console.error('Server returned error:', data);
                alert('{{ __("Error loading laboratory data.") }}');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('{{ __("Error loading laboratory data: ") }}' + error.message);
        });
}

function toggleStatus(labId) {
    if (confirm('{{ __("Change the status of this laboratory?") }}')) {
        fetch(`/external-labs/${labId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('{{ __("Error updating laboratory status.") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error updating laboratory status.") }}');
        });
    }
}

function deleteLab(labId) {
    if (confirm('{{ __("Are you sure you want to delete this laboratory? This action cannot be undone.") }}')) {
        fetch(`/external-labs/${labId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('{{ __("Error deleting laboratory.") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error deleting laboratory.") }}');
        });
    }
}
</script>
@endpush
