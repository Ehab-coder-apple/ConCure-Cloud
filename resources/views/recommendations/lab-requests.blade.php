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
                                                <a href="{{ route('recommendations.lab-requests.show', $labRequest->id) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('recommendations.lab-requests.print', $labRequest->id) }}"
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="{{ __('Print') }}" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-info dropdown-toggle dropdown-toggle-split"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false"
                                                            title="{{ __('More Actions') }}">
                                                        <span class="visually-hidden">{{ __('Toggle Dropdown') }}</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <h6 class="dropdown-header">{{ __('Actions') }}</h6>
                                                        </li>
                                                        @if($labRequest->status === 'pending' && auth()->user()->hasPermission('prescriptions_create'))
                                                        <li><a class="dropdown-item" href="{{ route('recommendations.lab-requests.edit', $labRequest->id) }}">
                                                            <i class="fas fa-edit me-2 text-primary"></i> {{ __('Edit') }}
                                                        </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="duplicateLabRequest({{ $labRequest->id }}); return false;">
                                                            <i class="fas fa-copy me-2 text-info"></i> {{ __('Duplicate') }}
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <h6 class="dropdown-header">{{ __('Status') }}</h6>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('recommendations.lab-requests.update-status', $labRequest->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __("Mark this lab request as completed?") }}')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                                                    <i class="fas fa-check me-2 text-success"></i> {{ __('Mark Completed') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('recommendations.lab-requests.update-status', $labRequest->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __("Cancel this lab request?") }}')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="cancelled">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                                                    <i class="fas fa-times me-2 text-danger"></i> {{ __('Cancel') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @if(
                                                            ($labRequest->status === 'pending' && !$labRequest->isSent()) ||
                                                            ($labRequest->status === 'cancelled')
                                                        )
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('recommendations.lab-requests.destroy', $labRequest->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger" style="border: none; background: none; width: 100%; text-align: left;">
                                                                    <i class="fas fa-trash me-2"></i> {{ __('Delete') }}
                                                                </button>
                                                            </form>
                                                        </li>
                                                        @endif
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
                                                data-whatsapp="{{ $lab->whatsapp }}"
                                                data-email="{{ $lab->email }}"
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

                    <!-- Lab Contact Details -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label for="lab_phone" class="form-label">{{ __('Lab Phone') }}</label>
                            <input type="text" class="form-control" id="lab_phone" name="lab_phone"
                                   placeholder="{{ __('Lab phone number') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="lab_whatsapp" class="form-label">{{ __('Lab WhatsApp') }}</label>
                            <input type="text" class="form-control" id="lab_whatsapp" name="lab_whatsapp"
                                   placeholder="{{ __('WhatsApp number (with country code)') }}"
                                   @if(isset($clinicWhatsApp) && $clinicWhatsApp) data-clinic-whatsapp="{{ $clinicWhatsApp }}" @endif>
                            <small class="text-muted">
                                {{ __('e.g., +9647595432033') }}
                                @if(isset($clinicWhatsApp) && $clinicWhatsApp)
                                    <br><strong>{{ __('Clinic default: ') }}{{ $clinicWhatsApp }}</strong>
                                @endif
                            </small>
                        </div>
                        <div class="col-md-4">
                            <label for="lab_email" class="form-label">{{ __('Lab Email') }}</label>
                            <input type="email" class="form-control" id="lab_email" name="lab_email"
                                   placeholder="{{ __('lab@example.com') }}">
                        </div>
                    </div>

                    <!-- Communication Preference -->
                    <div class="mb-3 mt-3">
                        <label class="form-label">{{ __('Preferred Communication Method') }}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="communication_method" id="comm_whatsapp" value="whatsapp" checked>
                                    <label class="form-check-label" for="comm_whatsapp">
                                        <i class="fab fa-whatsapp text-success me-1"></i>
                                        {{ __('WhatsApp (Recommended)') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="communication_method" id="comm_email" value="email">
                                    <label class="form-check-label" for="comm_email">
                                        <i class="fas fa-envelope text-primary me-1"></i>
                                        {{ __('Email') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">{{ __('Choose the primary method for sending this lab request') }}</small>
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
// Aggressive hash removal
function removeHash() {
    if (window.location.hash) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search;
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

// Remove hash immediately
removeHash();

// Remove hash on any URL change
window.addEventListener('hashchange', removeHash);

// Remove hash after any click
document.addEventListener('click', function() {
    setTimeout(removeHash, 10);
});

// Remove hash periodically (aggressive approach)
setInterval(removeHash, 100);

// Add event listeners for view buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.view-lab-request').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const labId = this.getAttribute('data-lab-id');
            viewLabRequest(labId, e);
            removeHash();
            return false;
        });
    });
});
// Debug: Check if user is authenticated
console.log('Current user:', @json(auth()->user()));
console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);

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
    const labPhoneInput = document.getElementById('lab_phone');
    const labWhatsAppInput = document.getElementById('lab_whatsapp');
    const labEmailInput = document.getElementById('lab_email');

    if (!labSelect || !labNameInput) return;

    const selectedOption = labSelect.options[labSelect.selectedIndex];

    if (labSelect.value === 'custom') {
        // Enable manual entry for all fields
        labNameInput.readOnly = false;
        if (labPhoneInput) labPhoneInput.readOnly = false;
        if (labWhatsAppInput) labWhatsAppInput.readOnly = false;
        if (labEmailInput) labEmailInput.readOnly = false;

        // Clear all fields
        labNameInput.value = '';
        if (labPhoneInput) labPhoneInput.value = '';
        if (labWhatsAppInput) {
            // Use clinic's WhatsApp number as default if available
            const clinicWhatsApp = labWhatsAppInput.dataset.clinicWhatsapp;
            labWhatsAppInput.value = clinicWhatsApp || '';
        }
        if (labEmailInput) labEmailInput.value = '';

        // Update placeholders
        labNameInput.placeholder = '{{ __("Enter laboratory name") }}';
        if (labPhoneInput) labPhoneInput.placeholder = '{{ __("Enter phone number") }}';
        if (labWhatsAppInput) {
            const clinicWhatsApp = labWhatsAppInput.dataset.clinicWhatsapp;
            labWhatsAppInput.placeholder = clinicWhatsApp ?
                '{{ __("Using clinic default WhatsApp") }}' :
                '{{ __("Enter WhatsApp number") }}';
        }
        if (labEmailInput) labEmailInput.placeholder = '{{ __("Enter email address") }}';

        labNameInput.focus();
    } else if (labSelect.value && selectedOption) {
        // Auto-fill from selected lab
        labNameInput.readOnly = true;
        if (labPhoneInput) labPhoneInput.readOnly = true;
        if (labWhatsAppInput) labWhatsAppInput.readOnly = true;
        if (labEmailInput) labEmailInput.readOnly = true;

        // Fill the fields from data attributes
        labNameInput.value = selectedOption.dataset.name || '';
        if (labPhoneInput) labPhoneInput.value = selectedOption.dataset.phone || '';
        if (labWhatsAppInput) labWhatsAppInput.value = selectedOption.dataset.whatsapp || selectedOption.dataset.phone || '';
        if (labEmailInput) labEmailInput.value = selectedOption.dataset.email || '';

        // Update placeholders
        labNameInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
        if (labPhoneInput) labPhoneInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
        if (labWhatsAppInput) labWhatsAppInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
        if (labEmailInput) labEmailInput.placeholder = '{{ __("Auto-filled from preferred lab") }}';
    } else {
        // Clear and disable fields
        labNameInput.readOnly = true;
        if (labPhoneInput) labPhoneInput.readOnly = true;
        if (labWhatsAppInput) labWhatsAppInput.readOnly = true;
        if (labEmailInput) labEmailInput.readOnly = true;

        // Clear all fields
        labNameInput.value = '';
        if (labPhoneInput) labPhoneInput.value = '';
        if (labWhatsAppInput) labWhatsAppInput.value = '';
        if (labEmailInput) labEmailInput.value = '';

        // Update placeholders
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

function viewLabRequest(id, event) {
    // Prevent default anchor behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Skip AJAX completely - just show static content with communication buttons
    currentLabRequestId = id;
    const detailsContainer = document.getElementById('labRequestDetails');

    // Show static content immediately
    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>{{ __('Patient Information') }}</h6>
                <p><strong>{{ __('Name') }}:</strong> Ahmed Ali</p>
                <p><strong>{{ __('Phone') }}:</strong> +9647595432033</p>
                <p><strong>{{ __('Request ID') }}:</strong> ${id}</p>
            </div>
            <div class="col-md-6">
                <h6>{{ __('Request Information') }}</h6>
                <p><strong>{{ __('Request Number') }}:</strong> COMM-TEST-${id}</p>
                <p><strong>{{ __('Status') }}:</strong>
                    <span class="badge bg-warning">PENDING</span>
                </p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <h6>{{ __('Tests Requested') }}</h6>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>Communication Test</strong>
                        <br><small class="text-muted">Test the WhatsApp and Email features</small>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Communication & Results Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-comments me-2"></i>
                            {{ __('Communication & Results') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-success w-100" onclick="sendViaWhatsApp(${id})">
                                    <i class="fab fa-whatsapp me-1"></i>
                                    {{ __('Send via WhatsApp') }}
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-primary w-100" onclick="sendViaEmail(${id})">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ __('Send via Email') }}
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-info w-100" onclick="uploadResult(${id})">
                                    <i class="fas fa-upload me-1"></i>
                                    {{ __('Upload Result') }}
                                </button>
                            </div>
                        </div>

                        <!-- Debug Information -->
                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted">
                                <strong>Debug Info:</strong><br>
                                Lab Request ID: ${id}<br>
                                Communication Status: Ready for testing<br>
                                WhatsApp: Available<br>
                                Email: Available<br>
                                File Upload: Available
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('viewLabRequestModal'));
    modal.show();
}

function displayLabRequestDetails(labRequest) {
    const detailsContainer = document.getElementById('labRequestDetails');

    // Debug logging
    console.log('Lab Request Data:', labRequest);

    let testsHtml = '';
    if (labRequest.tests && labRequest.tests.length > 0) {
        if (Array.isArray(labRequest.tests) && typeof labRequest.tests[0] === 'string') {
            // Handle simple array of test names
            testsHtml = labRequest.tests.map(testName => `
                <li class="list-group-item">
                    <strong>${testName}</strong>
                </li>
            `).join('');
        } else {
            // Handle array of test objects
            testsHtml = labRequest.tests.map(test => `
                <li class="list-group-item">
                    <strong>${test.test_name || test}</strong>
                    ${test.instructions ? `<br><small class="text-muted">${test.instructions}</small>` : ''}
                </li>
            `).join('');
        }
    }

    detailsContainer.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>{{ __('Patient Information') }}</h6>
                <p><strong>{{ __('Name') }}:</strong> ${labRequest.patient.full_name || (labRequest.patient.first_name + ' ' + labRequest.patient.last_name)}</p>
                <p><strong>{{ __('Phone') }}:</strong> ${labRequest.patient.phone || 'Not provided'}</p>
                ${labRequest.doctor ? `<p><strong>{{ __('Doctor') }}:</strong> ${labRequest.doctor.name || (labRequest.doctor.first_name + ' ' + labRequest.doctor.last_name)}</p>` : ''}
            </div>
            <div class="col-md-6">
                <h6>{{ __('Request Information') }}</h6>
                <p><strong>{{ __('Request Number') }}:</strong> ${labRequest.request_number}</p>
                ${labRequest.priority ? `<p><strong>{{ __('Priority') }}:</strong>
                    <span class="badge ${labRequest.priority === 'urgent' ? 'bg-warning' : labRequest.priority === 'stat' ? 'bg-danger' : 'bg-secondary'}">
                        ${labRequest.priority.toUpperCase()}
                    </span>
                </p>` : ''}
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

        <!-- Communication Section -->
        <div class="mt-4" style="border: 2px solid #007bff; padding: 15px; border-radius: 5px;">
            <h6 style="color: #007bff;">🔧 Communication & Results (Debug Mode)</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="sendViaWhatsApp(${labRequest.id})" style="font-weight: bold;">
                            <i class="fab fa-whatsapp me-1"></i>
                            📱 Send via WhatsApp
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="sendViaEmail(${labRequest.id})" style="font-weight: bold;">
                            <i class="fas fa-envelope me-1"></i>
                            📧 Send via Email
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-info btn-sm" onclick="uploadResult(${labRequest.id})" style="font-weight: bold;">
                            <i class="fas fa-upload me-1"></i>
                            📁 Upload Result
                        </button>
                        ${labRequest.result_file_path ? \`
                            <a href="/storage/\${labRequest.result_file_path}" target="_blank" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-file-alt me-1"></i>
                                👁️ View Result
                            </a>
                        \` : '<small class="text-muted">No result file uploaded yet</small>'}
                    </div>
                </div>
            </div>

            <!-- Debug Info -->
            <div class="mt-3 p-2 bg-light border rounded">
                <small><strong>Debug Info:</strong></small><br>
                <small>Communication Method: \${labRequest.communication_method || 'null'}</small><br>
                <small>Communication Method Display: \${labRequest.communication_method_display || 'null'}</small><br>
                <small>Sent At: \${labRequest.sent_at || 'null'}</small><br>
                <small>Result File: \${labRequest.result_file_path || 'null'}</small>
            </div>

            ${labRequest.sent_at ? \`
                <div class="mt-3">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i>
                        ✅ Sent via \${labRequest.communication_method_display} on \${new Date(labRequest.sent_at).toLocaleDateString()}
                        \${labRequest.communication_notes ? \`<br><small>\${labRequest.communication_notes}</small>\` : ''}
                    </div>
                </div>
            \` : ''}

            ${labRequest.result_received_at ? \`
                <div class="mt-3">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i>
                        📋 Result received on \${new Date(labRequest.result_received_at).toLocaleDateString()}
                    </div>
                </div>
            \` : ''}
        </div>
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



// Communication Functions
function sendViaWhatsApp(labRequestId) {
    // Get clinic's default WhatsApp number if available
    const clinicWhatsApp = '{{ $clinicWhatsApp ?? "" }}';
    const defaultPhone = clinicWhatsApp || '';

    const phone = prompt('Enter WhatsApp phone number:', defaultPhone);
    if (!phone) return;

    const message = prompt('Enter custom message (optional):', '');

    const formData = new FormData();
    formData.append('phone_number', phone);
    if (message) formData.append('message', message);

    fetch(`/recommendations/lab-requests/${labRequestId}/send-whatsapp`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.whatsapp_url) {
                // Open WhatsApp web URL
                window.open(data.whatsapp_url, '_blank');
                if (data.pdf_url) {
                    // Also open PDF in new tab
                    setTimeout(() => window.open(data.pdf_url, '_blank'), 1000);
                }
            }
            alert('Lab request sent successfully!');
            // Refresh the lab request details
            viewLabRequest(labRequestId);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending lab request');
    });
}

function sendViaEmail(labRequestId) {
    const email = prompt('Enter email address:');
    if (!email) return;

    const subject = prompt('Enter email subject (optional):', '');
    const message = prompt('Enter custom message (optional):', '');

    const formData = new FormData();
    formData.append('email', email);
    if (subject) formData.append('subject', subject);
    if (message) formData.append('message', message);

    fetch(`/recommendations/lab-requests/${labRequestId}/send-email`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Lab request sent via email successfully!');
            // Refresh the lab request details
            viewLabRequest(labRequestId);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending lab request');
    });
}

function uploadResult(labRequestId) {
    // Create a file input element
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.pdf,.jpg,.jpeg,.png,.doc,.docx';
    fileInput.style.display = 'none';

    fileInput.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const description = prompt('Enter description for the result file (optional):', '');

        const formData = new FormData();
        formData.append('result_file', file);
        if (description) formData.append('description', description);

        // Show loading
        const loadingAlert = document.createElement('div');
        loadingAlert.className = 'alert alert-info';
        loadingAlert.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading result file...';
        document.body.appendChild(loadingAlert);

        fetch(`/recommendations/lab-requests/${labRequestId}/upload-result`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.body.removeChild(loadingAlert);

            if (data.success) {
                alert('Result file uploaded successfully!');
                // Refresh the lab request details
                viewLabRequest(labRequestId);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            document.body.removeChild(loadingAlert);
            console.error('Error:', error);
            alert('Error uploading result file');
        });
    };

    // Trigger file selection
    document.body.appendChild(fileInput);
    fileInput.click();
    document.body.removeChild(fileInput);
}

function duplicateLabRequest(id) {
    if (confirm('{{ __("Create a copy of this lab request?") }}')) {
        // Implement duplication logic
        alert(`Duplicate lab request ${id} - Feature coming soon!`);
    }
}



function deleteLabRequest(id) {
    if (confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')) {
        // Create a form to submit the DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('recommendations/lab-requests') }}/${id}`;
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}


</script>
@endpush
