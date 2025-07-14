@extends('layouts.app')

@section('title', __('Lab Requests'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ __('Lab Requests') }}</h1>
                    <p class="text-muted mb-0">{{ __('Manage laboratory test requests') }}</p>
                </div>
                @can('create-prescriptions')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabRequestModal">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Lab Request') }}
                </button>
                @endcan
            </div>



            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('recommendations.lab-requests') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="{{ __('Request number, patient name...') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority" class="form-label">{{ __('Priority') }}</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">{{ __('All Priorities') }}</option>
                                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                                <option value="stat" {{ request('priority') == 'stat' ? 'selected' : '' }}>{{ __('STAT') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="lab_name_filter" class="form-label">{{ __('Laboratory') }}</label>
                            <select class="form-select" id="lab_name_filter" name="lab_name" onchange="handleLabFilterChange()">
                                <option value="">{{ __('All Labs') }}</option>
                                @if(isset($usedLabNames) && $usedLabNames->count() > 0)
                                    @foreach($usedLabNames as $labName)
                                        <option value="{{ $labName }}" {{ request('lab_name') == $labName ? 'selected' : '' }}>
                                            {{ Str::limit($labName, 25) }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="custom" {{ request('lab_name') && !$usedLabNames->contains(request('lab_name')) ? 'selected' : '' }}>
                                    {{ __('Custom search...') }}
                                </option>
                            </select>
                            <!-- Hidden input for custom lab name search -->
                            <input type="text" class="form-control mt-2" id="custom_lab_name" name="custom_lab_name"
                                   value="{{ request('lab_name') && !$usedLabNames->contains(request('lab_name')) ? request('lab_name') : '' }}"
                                   placeholder="{{ __('Enter lab name...') }}"
                                   style="display: {{ request('lab_name') && !$usedLabNames->contains(request('lab_name')) ? 'block' : 'none' }};">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lab Requests List -->
            <div class="card">
                <div class="card-body">
                    @if($labRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Laboratory') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($labRequests as $labRequest)
                                    <tr>
                                        <td>
                                            <strong>{{ $labRequest->request_number }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $labRequest->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $labRequest->patient->full_name }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $labRequest->patient->patient_id }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $labRequest->doctor->full_name }}</td>
                                        <td>
                                            @if($labRequest->lab_name)
                                                <div>
                                                    <i class="fas fa-flask me-1 text-primary"></i>
                                                    <strong>{{ Str::limit($labRequest->lab_name, 20) }}</strong>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('Not specified') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($labRequest->priority == 'urgent')
                                                <span class="badge bg-warning">{{ __('Urgent') }}</span>
                                            @elseif($labRequest->priority == 'stat')
                                                <span class="badge bg-danger">{{ __('STAT') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($labRequest->status == 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif($labRequest->status == 'completed')
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ __('Cancelled') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($labRequest->due_date)
                                                {{ $labRequest->due_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">{{ __('Not set') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary"
                                                        title="{{ __('View Details') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewLabRequestModal"
                                                        onclick="viewLabRequest({{ $labRequest->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary"
                                                        title="{{ __('Print') }}"
                                                        onclick="printLabRequest({{ $labRequest->id }})">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle"
                                                            data-bs-toggle="dropdown" title="{{ __('More Actions') }}">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="editLabRequest({{ $labRequest->id }})">
                                                            <i class="fas fa-edit me-1"></i> {{ __('Edit') }}
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="duplicateLabRequest({{ $labRequest->id }})">
                                                            <i class="fas fa-copy me-1"></i> {{ __('Duplicate') }}
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-success" href="#" onclick="markCompleted({{ $labRequest->id }})">
                                                            <i class="fas fa-check me-1"></i> {{ __('Mark Completed') }}
                                                        </a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="cancelLabRequest({{ $labRequest->id }})">
                                                            <i class="fas fa-times me-1"></i> {{ __('Cancel') }}
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $labRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No lab requests found') }}</h5>
                            <p class="text-muted mb-4">{{ __('Start by creating your first lab request.') }}</p>
                            @can('create-prescriptions')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabRequestModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Lab Request') }}
                            </button>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('create-prescriptions')
<!-- New Lab Request Modal -->
<div class="modal fade" id="newLabRequestModal" tabindex="-1" aria-labelledby="newLabRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('recommendations.lab-requests.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newLabRequestModalLabel">{{ __('New Lab Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Patient Selection -->
                    <div class="mb-3">
                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="patient_id" name="patient_id" required>
                            <option value="">{{ __('Select Patient') }}</option>
                            @if(isset($patients) && $patients->count() > 0)
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->first_name }} {{ $patient->last_name }} (ID: {{ $patient->patient_id }})
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>{{ __('No patients found') }}</option>
                            @endif
                        </select>

                    </div>

                    <!-- Clinical Notes -->
                    <div class="mb-3">
                        <label for="clinical_notes" class="form-label">{{ __('Clinical Notes') }}</label>
                        <textarea class="form-control" id="clinical_notes" name="clinical_notes" rows="3" 
                                  placeholder="{{ __('Clinical indication for tests...') }}"></textarea>
                    </div>

                    <!-- Priority and Due Date -->
                    <div class="row">
                        <div class="col-md-6">
                            <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="normal">{{ __('Normal') }}</option>
                                <option value="urgent">{{ __('Urgent') }}</option>
                                <option value="stat">{{ __('STAT') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        </div>
                    </div>

                    <!-- External Laboratory Selection -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="external_lab_id" class="form-label">{{ __('Preferred Laboratory') }}</label>
                            <select class="form-select" id="external_lab_id" name="external_lab_id" onchange="handleLabSelection()">
                                <option value="">{{ __('Select from preferred labs') }}</option>
                                @if(isset($externalLabs) && $externalLabs->count() > 0)
                                    @foreach($externalLabs as $lab)
                                        <option value="{{ $lab->id }}"
                                                data-name="{{ $lab->name }}"
                                                data-phone="{{ $lab->phone }}"
                                                data-address="{{ $lab->address }}">
                                            {{ $lab->display_name }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="custom">{{ __('Other laboratory (enter manually)') }}</option>
                            </select>
                            <small class="text-muted">{{ __('Select from your preferred labs or choose "Other"') }}</small>
                        </div>
                        <div class="col-md-6">
                            <label for="lab_name" class="form-label">{{ __('Laboratory Name') }}</label>
                            <input type="text" class="form-control" id="lab_name" name="lab_name"
                                   placeholder="{{ __('Auto-filled or enter manually') }}" readonly>
                            <small class="text-muted">{{ __('Will be auto-filled from preferred lab selection') }}</small>
                        </div>
                    </div>

                    <!-- Lab Contact Info -->
                    <div class="mb-3 mt-3">
                        <label for="lab_contact_info" class="form-label">{{ __('Lab Contact Information') }}</label>
                        <input type="text" class="form-control" id="lab_contact_info" name="lab_contact_info"
                               placeholder="{{ __('Phone, address (auto-filled from preferred lab)') }}" readonly>
                    </div>

                    <!-- Tests -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Tests Required') }} <span class="text-danger">*</span></label>
                        <div id="tests-container">
                            <div class="test-item border rounded p-3 mb-2">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="tests[0][test_name]" 
                                               placeholder="{{ __('Test name') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-test" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <input type="text" class="form-control" name="tests[0][instructions]" 
                                           placeholder="{{ __('Special instructions (optional)') }}">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-test">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Add Another Test') }}
                        </button>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="{{ __('Any additional notes...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create Lab Request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<!-- View Lab Request Modal -->
<div class="modal fade" id="viewLabRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Lab Request Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="labRequestDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                    <p class="mt-2">{{ __('Loading lab request details...') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" onclick="printCurrentLabRequest()">
                    <i class="fas fa-print me-1"></i>
                    {{ __('Print') }}
                </button>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let testIndex = 1;
    
    // Add test functionality
    document.getElementById('add-test').addEventListener('click', function() {
        const container = document.getElementById('tests-container');
        const newTest = document.createElement('div');
        newTest.className = 'test-item border rounded p-3 mb-2';
        newTest.innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="tests[${testIndex}][test_name]" 
                           placeholder="{{ __('Test name') }}" required>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-test">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <input type="text" class="form-control" name="tests[${testIndex}][instructions]" 
                       placeholder="{{ __('Special instructions (optional)') }}">
            </div>
        `;
        container.appendChild(newTest);
        testIndex++;
        updateRemoveButtons();
    });
    
    // Remove test functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-test')) {
            e.target.closest('.test-item').remove();
            updateRemoveButtons();
        }
    });
    
    function updateRemoveButtons() {
        const testItems = document.querySelectorAll('.test-item');
        testItems.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-test');
            if (testItems.length > 1) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    // Pre-select patient if patient_id is in URL
    const urlParams = new URLSearchParams(window.location.search);
    const patientId = urlParams.get('patient_id');
    if (patientId) {
        const patientSelect = document.getElementById('patient_id');
        if (patientSelect) {
            patientSelect.value = patientId;
        }
    }
});

// Handle external lab selection
function handleLabSelection() {
    const labSelect = document.getElementById('external_lab_id');
    const labNameInput = document.getElementById('lab_name');
    const labContactInput = document.getElementById('lab_contact_info');

    if (!labSelect || !labNameInput || !labContactInput) return;

    const selectedOption = labSelect.options[labSelect.selectedIndex];

    if (labSelect.value === 'custom') {
        // Enable manual entry
        labNameInput.readOnly = false;
        labContactInput.readOnly = false;
        labNameInput.value = '';
        labContactInput.value = '';
        labNameInput.placeholder = '{{ __("Enter laboratory name") }}';
        labContactInput.placeholder = '{{ __("Enter contact information") }}';
        labNameInput.focus();
    } else if (labSelect.value && selectedOption) {
        // Auto-fill from selected lab
        labNameInput.readOnly = true;
        labContactInput.readOnly = true;
        labNameInput.value = selectedOption.dataset.name || '';

        // Build contact info string
        let contactInfo = [];
        if (selectedOption.dataset.phone) {
            contactInfo.push('📞 ' + selectedOption.dataset.phone);
        }
        if (selectedOption.dataset.address) {
            contactInfo.push('📍 ' + selectedOption.dataset.address);
        }
        labContactInput.value = contactInfo.join(' | ');

        labNameInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
        labContactInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
    } else {
        // Clear fields
        labNameInput.readOnly = true;
        labContactInput.readOnly = true;
        labNameInput.value = '';
        labContactInput.value = '';
        labNameInput.placeholder = '{{ __("Select a preferred lab first") }}';
        labContactInput.placeholder = '{{ __("Will be auto-filled") }}';
    }
}

// Handle lab filter dropdown change
function handleLabFilterChange() {
    const labFilterSelect = document.getElementById('lab_name_filter');
    const customLabInput = document.getElementById('custom_lab_name');

    if (!labFilterSelect || !customLabInput) return;

    if (labFilterSelect.value === 'custom') {
        // Show custom input field
        customLabInput.style.display = 'block';
        customLabInput.focus();
        // Clear the select value so custom input is used
        labFilterSelect.name = '';
        customLabInput.name = 'lab_name';
    } else {
        // Hide custom input field
        customLabInput.style.display = 'none';
        customLabInput.value = '';
        // Use select value
        labFilterSelect.name = 'lab_name';
        customLabInput.name = '';
    }
}

// Lab Request Action Functions
let currentLabRequestId = null;

function viewLabRequest(id) {
    currentLabRequestId = id;
    const detailsContainer = document.getElementById('labRequestDetails');

    // Show loading state
    detailsContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2">{{ __('Loading lab request details...') }}</p>
        </div>
    `;

    // Fetch lab request details
    fetch(`/recommendations/lab-requests/${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayLabRequestDetails(data.labRequest);
        } else {
            detailsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ __('Error loading lab request details.') }}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        detailsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-1"></i>
                {{ __('Error loading lab request details.') }}
            </div>
        `;
    });
}

function displayLabRequestDetails(labRequest) {
    const detailsContainer = document.getElementById('labRequestDetails');

    let testsHtml = '';
    if (labRequest.tests && labRequest.tests.length > 0) {
        testsHtml = labRequest.tests.map(test => `
            <li class="list-group-item">
                <strong>${test.test_name}</strong>
                ${test.instructions ? `<br><small class="text-muted">${test.instructions}</small>` : ''}
            </li>
        `).join('');
    }

    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>{{ __('Patient Information') }}</h6>
                <p><strong>{{ __('Name') }}:</strong> ${labRequest.patient.first_name} ${labRequest.patient.last_name}</p>
                <p><strong>{{ __('Patient ID') }}:</strong> ${labRequest.patient.patient_id}</p>
                <p><strong>{{ __('Doctor') }}:</strong> ${labRequest.doctor.first_name} ${labRequest.doctor.last_name}</p>
            </div>
            <div class="col-md-6">
                <h6>{{ __('Request Information') }}</h6>
                <p><strong>{{ __('Request Number') }}:</strong> ${labRequest.request_number}</p>
                <p><strong>{{ __('Priority') }}:</strong>
                    <span class="badge ${labRequest.priority === 'urgent' ? 'bg-warning' : labRequest.priority === 'stat' ? 'bg-danger' : 'bg-secondary'}">
                        ${labRequest.priority.toUpperCase()}
                    </span>
                </p>
                <p><strong>{{ __('Status') }}:</strong>
                    <span class="badge ${labRequest.status === 'pending' ? 'bg-warning' : labRequest.status === 'completed' ? 'bg-success' : 'bg-danger'}">
                        ${labRequest.status.toUpperCase()}
                    </span>
                </p>
                ${labRequest.due_date ? `<p><strong>{{ __('Due Date') }}:</strong> ${labRequest.due_date}</p>` : ''}
            </div>
        </div>

        ${labRequest.clinical_notes ? `
            <div class="mt-3">
                <h6>{{ __('Clinical Notes') }}</h6>
                <p class="bg-light p-3 rounded">${labRequest.clinical_notes}</p>
            </div>
        ` : ''}

        <div class="mt-3">
            <h6>{{ __('Tests Required') }}</h6>
            <ul class="list-group">
                ${testsHtml || '<li class="list-group-item text-muted">{{ __("No tests specified") }}</li>'}
            </ul>
        </div>

        ${labRequest.notes ? `
            <div class="mt-3">
                <h6>{{ __('Additional Notes') }}</h6>
                <p class="bg-light p-3 rounded">${labRequest.notes}</p>
            </div>
        ` : ''}

        ${labRequest.lab_name ? `
            <div class="mt-3">
                <h6>{{ __('Laboratory') }}</h6>
                <p>${labRequest.lab_name}</p>
            </div>
        ` : ''}
    `;
}

function printLabRequest(id) {
    window.open(`/recommendations/lab-requests/${id}/print`, '_blank');
}

function printCurrentLabRequest() {
    if (currentLabRequestId) {
        printLabRequest(currentLabRequestId);
    }
}

function editLabRequest(id) {
    // For now, show a message - you can implement edit functionality later
    alert(`Edit lab request ${id} - Feature coming soon!`);
}

function duplicateLabRequest(id) {
    if (confirm('{{ __("Create a copy of this lab request?") }}')) {
        // Implement duplication logic
        alert(`Duplicate lab request ${id} - Feature coming soon!`);
    }
}

function markCompleted(id) {
    if (confirm('{{ __("Mark this lab request as completed?") }}')) {
        updateLabRequestStatus(id, 'completed');
    }
}

function cancelLabRequest(id) {
    if (confirm('{{ __("Cancel this lab request?") }}')) {
        updateLabRequestStatus(id, 'cancelled');
    }
}

function updateLabRequestStatus(id, status) {
    fetch(`/recommendations/lab-requests/${id}/status`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to show updated status
        } else {
            alert('{{ __("Error updating lab request status.") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("Error updating lab request status.") }}');
    });
}
</script>
@endpush
