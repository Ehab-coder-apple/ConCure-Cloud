@extends('layouts.app')

@section('title', __('Dental Laboratories'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-info"></i>
                        {{ __('Dental Laboratories') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage preferred dental laboratories for dental lab requests') }}</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDentalLabModal">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add Dental Lab') }}
                </button>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('dental.external-labs.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="{{ __('Lab name, phone, email...') }}">
                        </div>
                        <div class="col-md-3">
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
                            <a href="{{ route('dental.external-labs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Dental Labs List -->
            <div class="card">
                <div class="card-body">
                    @if($dentalLabs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Laboratory Name') }}</th>
                                        <th>{{ __('Contact Information') }}</th>
                                        <th>{{ __('Turnaround') }}</th>
                                        <th>{{ __('Digital Impressions') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dentalLabs as $lab)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $lab->name }}</strong>
                                                @if($lab->website)
                                                    <br>
                                                    <a href="{{ $lab->website }}" target="_blank" class="text-primary small">
                                                        <i class="fas fa-external-link-alt me-1"></i>{{ __('Website') }}
                                                    </a>
                                                @endif
                                                @if($lab->equipment_capabilities)
                                                    <br><small class="text-muted"><i class="fas fa-cogs me-1"></i>{{ Str::limit($lab->equipment_capabilities, 40) }}</small>
                                                @endif
                                            </div>
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
                                            @if($lab->turnaround_days)
                                                <span class="badge bg-warning text-dark">{{ $lab->turnaround_days }} {{ __('days') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($lab->accepts_digital_impressions)
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ __('Yes') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('No') }}</span>
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
                                            {{ $lab->creator->full_name }}
                                            <br>
                                            <small class="text-muted">{{ $lab->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary"
                                                        title="{{ __('Edit') }}"
                                                        onclick="editDentalLab({{ $lab->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-{{ $lab->is_active ? 'warning' : 'success' }}"
                                                        title="{{ $lab->is_active ? __('Deactivate') : __('Activate') }}"
                                                        onclick="toggleDentalLabStatus({{ $lab->id }})">
                                                    <i class="fas fa-{{ $lab->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger"
                                                        title="{{ __('Delete') }}"
                                                        onclick="deleteDentalLab({{ $lab->id }})">
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
                            {{ $dentalLabs->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tooth fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No dental laboratories found') }}</h5>
                            <p class="text-muted mb-4">{{ __('Start by adding your first preferred dental laboratory.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDentalLabModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Dental Lab') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Dental Lab Modal -->
<div class="modal fade" id="newDentalLabModal" tabindex="-1" aria-labelledby="newDentalLabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('dental.external-labs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newDentalLabModalLabel">
                        <i class="fas fa-tooth me-2 text-info"></i>
                        {{ __('Add Dental Laboratory') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="name" class="form-label">{{ __('Laboratory Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="turnaround_days" class="form-label">{{ __('Turnaround (Days)') }}</label>
                            <input type="number" class="form-control" id="turnaround_days" name="turnaround_days" min="1">
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
                        <label for="equipment_capabilities" class="form-label">{{ __('Equipment & Capabilities') }}</label>
                        <textarea class="form-control" id="equipment_capabilities" name="equipment_capabilities"
                                  rows="2" placeholder="{{ __('e.g., CAD/CAM, 3D Printing, Milling Machine...') }}"></textarea>
                    </div>

                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accepts_digital_impressions"
                                   name="accepts_digital_impressions" value="1">
                            <label class="form-check-label" for="accepts_digital_impressions">
                                {{ __('Accepts Digital Impressions') }}
                            </label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"
                                  placeholder="{{ __('Any additional notes about this dental laboratory...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Add Dental Lab') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Dental Lab Modal -->
<div class="modal fade" id="editDentalLabModal" tabindex="-1" aria-labelledby="editDentalLabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editDentalLabForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editDentalLabModalLabel">
                        <i class="fas fa-tooth me-2 text-info"></i>
                        {{ __('Edit Dental Laboratory') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="edit_name" class="form-label">{{ __('Laboratory Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_sort_order" class="form-label">{{ __('Sort Order') }}</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                        </div>
                        <div class="col-md-3">
                            <label for="edit_turnaround_days" class="form-label">{{ __('Turnaround (Days)') }}</label>
                            <input type="number" class="form-control" id="edit_turnaround_days" name="turnaround_days" min="1">
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
                        <label for="edit_equipment_capabilities" class="form-label">{{ __('Equipment & Capabilities') }}</label>
                        <textarea class="form-control" id="edit_equipment_capabilities" name="equipment_capabilities"
                                  rows="2" placeholder="{{ __('e.g., CAD/CAM, 3D Printing, Milling Machine...') }}"></textarea>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_accepts_digital_impressions"
                                       name="accepts_digital_impressions" value="1">
                                <label class="form-check-label" for="edit_accepts_digital_impressions">
                                    {{ __('Accepts Digital Impressions') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="edit_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update Dental Lab') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editDentalLab(labId) {
    fetch(`/dental/external-labs/${labId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const lab = data.lab;
            document.getElementById('edit_name').value = lab.name || '';
            document.getElementById('edit_phone').value = lab.phone || '';
            document.getElementById('edit_whatsapp').value = lab.whatsapp || '';
            document.getElementById('edit_email').value = lab.email || '';
            document.getElementById('edit_website').value = lab.website || '';
            document.getElementById('edit_address').value = lab.address || '';
            document.getElementById('edit_notes').value = lab.notes || '';
            document.getElementById('edit_sort_order').value = lab.sort_order || 0;
            document.getElementById('edit_is_active').checked = lab.is_active;
            document.getElementById('edit_turnaround_days').value = lab.turnaround_days || '';
            document.getElementById('edit_accepts_digital_impressions').checked = lab.accepts_digital_impressions || false;
            document.getElementById('edit_equipment_capabilities').value = lab.equipment_capabilities || '';

            document.getElementById('editDentalLabForm').action = `/dental/external-labs/${labId}`;
            new bootstrap.Modal(document.getElementById('editDentalLabModal')).show();
        } else {
            alert('{{ __("Error loading dental laboratory data.") }}');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('{{ __("Error loading dental laboratory data: ") }}' + error.message);
    });
}

function toggleDentalLabStatus(labId) {
    if (confirm('{{ __("Change the status of this dental laboratory?") }}')) {
        fetch(`/dental/external-labs/${labId}/toggle-status`, {
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
                alert('{{ __("Error updating dental laboratory status.") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error updating dental laboratory status.") }}');
        });
    }
}

function deleteDentalLab(labId) {
    if (confirm('{{ __("Are you sure you want to delete this dental laboratory? This action cannot be undone.") }}')) {
        fetch(`/dental/external-labs/${labId}`, {
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
                alert('{{ __("Error deleting dental laboratory.") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error deleting dental laboratory.") }}');
        });
    }
}
</script>
@endpush
