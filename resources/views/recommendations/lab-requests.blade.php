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
                @if(auth()->user()->canCreateLabRequests())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabRequestModal">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Lab Request') }}
                </button>
                @endif
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
                                <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>{{ __('Uploaded (Results Ready)') }}</option>
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
                            <select class="form-select" id="lab_name_filter" name="lab_name">
                                <option value="">{{ __('All Labs') }}</option>
                                @if(isset($usedLabNames) && $usedLabNames->count() > 0)
                                    @foreach($usedLabNames as $labName)
                                        <option value="{{ $labName }}" {{ request('lab_name') == $labName ? 'selected' : '' }}>
                                            {{ Str::limit($labName, 25) }}
                                        </option>
                                    @endforeach
                                @endif
                                <option value="custom" {{ request('lab_name') && (!isset($usedLabNames) || !$usedLabNames->contains(request('lab_name'))) ? 'selected' : '' }}>
                                    {{ __('Custom search...') }}
                                </option>
                            </select>
                            <!-- Hidden input for custom lab name search -->
                            <input type="text" class="form-control mt-2" id="custom_lab_name" name="custom_lab_name"
                                   value="{{ request('lab_name') && (!isset($usedLabNames) || !$usedLabNames->contains(request('lab_name'))) ? request('lab_name') : '' }}"
                                   placeholder="{{ __('Enter lab name...') }}"
                                   style="display: {{ request('lab_name') && (!isset($usedLabNames) || !$usedLabNames->contains(request('lab_name'))) ? 'block' : 'none' }};">
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
                                                <strong>{{ $labRequest->patient?->full_name ?? __('Unknown') }}</strong>
                                                <br>
                                                <small class="text-muted">ID: {{ $labRequest->patient?->patient_id ?? '—' }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $labRequest->doctor?->full_name ?? __('Not specified') }}</td>
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
                                                    <a href="{{ route('recommendations.lab-requests.pdf', $labRequest->id) }}"
                                                       class="btn btn-sm btn-outline-success"
                                                       title="{{ __('Download PDF') }}" target="_blank">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('recommendations.lab-requests.pdf', $labRequest->id) }}">
                                                                <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('recommendations.lab-requests.pdf', [$labRequest->id, 'template' => 'custom']) }}">
                                                                <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <a href="{{ route('messages.index') }}"
                                                   class="btn btn-sm btn-outline-secondary js-share-internal"
                                                   title="{{ __('Share Internally') }}"
                                                   data-transfer-type="lab_request"
                                                   data-patient-id="{{ $labRequest->patient_id }}"
                                                   data-source-type="lab_request"
                                                   data-source-id="{{ $labRequest->id }}"
                                                   data-patient-name="{{ $labRequest->patient?->full_name ?? '' }}"
                                                   data-request-number="{{ $labRequest->request_number ?? '' }}">
                                                    <i class="fas fa-share-nodes"></i>
                                                </a>
                                                    <div class="d-inline-flex align-items-center gap-1">
                                                    @if($labRequest->status === 'pending' && auth()->user()->canEditLabRequests())
                                                        <a href="{{ route('recommendations.lab-requests.edit', $labRequest->id) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="{{ __('Edit') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-info"
                                                       onclick="duplicateLabRequest({{ $labRequest->id }}); return false;"
                                                       title="{{ __('Duplicate') }}">
                                                        <i class="fas fa-copy"></i>
                                                    </a>

                                                    <form action="{{ route('recommendations.lab-requests.update-status', $labRequest->id) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('{{ __("Mark this lab request as completed?") }}')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="{{ __('Mark Completed') }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('recommendations.lab-requests.update-status', $labRequest->id) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('{{ __("Cancel this lab request?") }}')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ __('Cancel') }}">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>

                                                    @if(($labRequest->status === 'pending' && !$labRequest->isSent()) || ($labRequest->status === 'cancelled'))
                                                        <form action="{{ route('recommendations.lab-requests.destroy', $labRequest->id) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
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
                            @if(auth()->user()->canCreateLabRequests())
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLabRequestModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Lab Request') }}
                            </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canCreateLabRequests())
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
                    <!-- Patient, Priority and Due Date -->
                    <div class="row">
                        <div class="col-md-5 mb-3">
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
                        <div class="col-md-3 mb-3">
                            <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="normal">{{ __('Normal') }}</option>
                                <option value="urgent">{{ __('Urgent') }}</option>
                                <option value="stat">{{ __('STAT') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                            <input type="date" class="form-control" id="due_date" name="due_date"
                                   min="{{ date('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- External Laboratory Selection -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="external_lab_id" class="form-label">{{ __('Preferred Laboratory') }}</label>
                            <select class="form-select" id="external_lab_id" name="external_lab_id">
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

                    <!-- Tests Checklist -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">{{ __('Tests Required') }} <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="lr-add-category-btn">
                                <i class="fas fa-folder-plus me-1"></i>{{ __('Add Category') }}
                            </button>
                        </div>

                        <div class="row" id="lr-tests-grid">
                            @foreach($labTestCatalog ?? [] as $categoryKey => $group)
                                <div class="col-md-4 lr-category-card mb-3" data-category-key="{{ $categoryKey }}">
                                    <strong class="text-primary text-uppercase small d-block mb-2 pb-1 border-bottom border-primary">{{ $group['label'] }}</strong>
                                    <div class="lr-tests-list">
                                        @foreach($group['tests'] as $test)
                                            <div class="form-check">
                                                <input class="form-check-input lr-test-checkbox" type="checkbox"
                                                       id="lr_test_{{ $categoryKey }}_{{ $loop->index }}"
                                                       data-test-name="{{ $test['name'] }}"
                                                       data-lab-test-id="{{ $test['id'] }}">
                                                <label class="form-check-label small" for="lr_test_{{ $categoryKey }}_{{ $loop->index }}">
                                                    {{ $test['name'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm p-0 mt-1 lr-add-test-btn"
                                            data-category-key="{{ $categoryKey }}" data-category-label="{{ $group['label'] }}">
                                        <i class="fas fa-plus me-1"></i>{{ __('Add test') }}
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Other / one-off tests not worth adding to the checklist -->
                        <div class="mt-1">
                            <label class="form-label small text-muted mb-1">{{ __('Other / Additional Tests (not listed above)') }}</label>
                            <div id="tests-container"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-test">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Row') }}
                            </button>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Clinical Indications / ICD-10 Codes / Notes') }}</label>
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
@endif

<!-- Quick Add Lab Test / Category Modal -->
<div class="modal fade" id="lrQuickAddTestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lrQuickAddTestModalLabel">{{ __('Add Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="lrQuickAddErrors" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lrQuickAddName" placeholder="{{ __('e.g., D-Dimer') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                    <select class="form-select" id="lrQuickAddCategorySelect">
                        @foreach(\App\Models\LabTest::CATEGORIES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                        <option value="__new__">{{ __('+ Add new category...') }}</option>
                    </select>
                    <input type="text" class="form-control mt-2 d-none" id="lrQuickAddNewCategoryName"
                           placeholder="{{ __('New category name (e.g., Cardiology)') }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="lrQuickAddSaveBtn">
                    <i class="fas fa-save me-1"></i>{{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Lab Request Modal -->




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


// Init Lab Request form scripts (robust to timing and dynamic content)
(function initLabRequestScripts() {
    function setup() {
        // Delegated: Add test
        document.addEventListener('click', function(e) {
            const addBtn = e.target.closest('#add-test');
            if (!addBtn) return;
            const container = document.getElementById('tests-container');
            if (!container) return;

            // Compute next index as max existing + 1 to avoid duplicates after deletions
            const nextIndex = (function() {
                let maxIdx = -1;
                container.querySelectorAll('input[name^="tests["][name$="[test_name]"]').forEach(input => {
                    const m = input.name.match(/^tests\[(\d+)\]\[test_name\]$/);
                    if (m) {
                        const n = parseInt(m[1], 10);
                        if (!Number.isNaN(n)) maxIdx = Math.max(maxIdx, n);
                    }
                });
                return maxIdx + 1;
            })();

            // Build the new test item without template literals for maximum compatibility
            const item = document.createElement('div');
            item.className = 'test-item border rounded p-3 mb-2';

            const row = document.createElement('div');
            row.className = 'row';

            const col8 = document.createElement('div');
            col8.className = 'col-md-8';
            const inputName = document.createElement('input');
            inputName.type = 'text';
            inputName.className = 'form-control';
            inputName.name = 'tests[' + nextIndex + '][test_name]';
            inputName.placeholder = "{{ __('Test name') }}";
            inputName.required = true;
            col8.appendChild(inputName);

            const col4 = document.createElement('div');
            col4.className = 'col-md-4';
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm remove-test';
            removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
            col4.appendChild(removeBtn);

            row.appendChild(col8);
            row.appendChild(col4);

            const instrWrap = document.createElement('div');
            instrWrap.className = 'mt-2';
            const inputInstr = document.createElement('input');
            inputInstr.type = 'text';
            inputInstr.className = 'form-control';
            inputInstr.name = 'tests[' + nextIndex + '][instructions]';
            inputInstr.placeholder = "{{ __('Special instructions (optional)') }}";
            instrWrap.appendChild(inputInstr);

            item.appendChild(row);
            item.appendChild(instrWrap);

            container.appendChild(item);
            updateRemoveButtons();
        });

        // Delegated: Remove test
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-test')) {
                const item = e.target.closest('.test-item');
                if (item) item.remove();
                updateRemoveButtons();
            }
        });

        function updateRemoveButtons() {
            const testItems = document.querySelectorAll('#tests-container .test-item');
            testItems.forEach(item => {
                const removeBtn = item.querySelector('.remove-test');
                if (!removeBtn) return;
                removeBtn.style.display = (testItems.length > 1) ? 'inline-block' : 'none';
            });
        }

        // Initialize remove button visibility on load
        updateRemoveButtons();

        // Wire up Preferred Laboratory change handler
        const labSelectEl = document.getElementById('external_lab_id');
        if (labSelectEl) {
            labSelectEl.addEventListener('change', handleLabSelection);
            // Run once to set initial state
            try { handleLabSelection(); } catch (e) {}
        }

        // Wire up Lab filter change handler (was inline onchange)
        const labFilterEl = document.getElementById('lab_name_filter');
        if (labFilterEl && typeof handleLabFilterChange === 'function') {
            labFilterEl.addEventListener('change', handleLabFilterChange);
            try { handleLabFilterChange(); } catch (e) {}
        }

        // Pre-select patient if patient_id is in URL
        const urlParams = new URLSearchParams(window.location.search);
        const patientId = urlParams.get('patient_id');
        if (patientId) {
            const patientSelect = document.getElementById('patient_id');
            if (patientSelect) patientSelect.value = patientId;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();

// Tests Required checklist: quick-add-test/category + build hidden inputs on submit
(function initLabTestChecklist() {
    let lrQuickAddContext = { categoryKey: null, categoryLabel: null, isNewCategory: false };

    function openQuickAddModal(categoryKey, categoryLabel) {
        lrQuickAddContext = { categoryKey, categoryLabel, isNewCategory: !categoryKey };

        const nameInput = document.getElementById('lrQuickAddName');
        const select = document.getElementById('lrQuickAddCategorySelect');
        const newCategoryInput = document.getElementById('lrQuickAddNewCategoryName');
        const errorsBox = document.getElementById('lrQuickAddErrors');

        nameInput.value = '';
        errorsBox.classList.add('d-none');

        if (categoryKey) {
            select.value = categoryKey;
            newCategoryInput.classList.add('d-none');
            newCategoryInput.value = '';
        } else {
            select.value = '__new__';
            newCategoryInput.classList.remove('d-none');
            newCategoryInput.value = '';
        }

        const modalEl = document.getElementById('lrQuickAddTestModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        setTimeout(() => nameInput.focus(), 300);
    }

    function findOrCreateCategoryCard(categoryKey, categoryLabel) {
        const grid = document.getElementById('lr-tests-grid');
        let card = grid.querySelector('.lr-category-card[data-category-key="' + categoryKey + '"]');
        if (card) return card;

        card = document.createElement('div');
        card.className = 'col-md-4 lr-category-card mb-3';
        card.dataset.categoryKey = categoryKey;
        card.innerHTML =
            '<strong class="text-primary text-uppercase small d-block mb-2 pb-1 border-bottom border-primary"></strong>' +
            '<div class="lr-tests-list"></div>' +
            '<button type="button" class="btn btn-link btn-sm p-0 mt-1 lr-add-test-btn">' +
                '<i class="fas fa-plus me-1"></i>{{ __("Add test") }}' +
            '</button>';
        card.querySelector('strong').textContent = categoryLabel;
        card.querySelector('.lr-add-test-btn').dataset.categoryKey = categoryKey;
        card.querySelector('.lr-add-test-btn').dataset.categoryLabel = categoryLabel;
        grid.appendChild(card);
        return card;
    }

    function appendCheckbox(card, test) {
        const list = card.querySelector('.lr-tests-list');
        const idx = list.querySelectorAll('.form-check').length;
        const id = 'lr_test_' + card.dataset.categoryKey + '_new_' + idx + '_' + test.id;

        const wrap = document.createElement('div');
        wrap.className = 'form-check';
        wrap.innerHTML =
            '<input class="form-check-input lr-test-checkbox" type="checkbox" id="' + id + '" checked>' +
            '<label class="form-check-label small" for="' + id + '"></label>';
        const checkbox = wrap.querySelector('input');
        checkbox.dataset.testName = test.name;
        checkbox.dataset.labTestId = test.id;
        wrap.querySelector('label').textContent = test.name;
        list.appendChild(wrap);
    }

    function setup() {
        const select = document.getElementById('lrQuickAddCategorySelect');
        const newCategoryInput = document.getElementById('lrQuickAddNewCategoryName');

        if (select) {
            select.addEventListener('change', function () {
                if (select.value === '__new__') {
                    newCategoryInput.classList.remove('d-none');
                } else {
                    newCategoryInput.classList.add('d-none');
                    newCategoryInput.value = '';
                }
            });
        }

        document.addEventListener('click', function (e) {
            const addTestBtn = e.target.closest('.lr-add-test-btn');
            if (addTestBtn) {
                openQuickAddModal(addTestBtn.dataset.categoryKey, addTestBtn.dataset.categoryLabel);
                return;
            }

            const addCategoryBtn = e.target.closest('#lr-add-category-btn');
            if (addCategoryBtn) {
                openQuickAddModal(null, null);
            }
        });

        const saveBtn = document.getElementById('lrQuickAddSaveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                const nameInput = document.getElementById('lrQuickAddName');
                const errorsBox = document.getElementById('lrQuickAddErrors');
                const selectedCategory = select.value;

                const name = nameInput.value.trim();
                if (!name) {
                    errorsBox.textContent = '{{ __("Test name is required.") }}';
                    errorsBox.classList.remove('d-none');
                    return;
                }

                const payload = { name: name };
                if (selectedCategory === '__new__') {
                    const newName = newCategoryInput.value.trim();
                    if (!newName) {
                        errorsBox.textContent = '{{ __("Category name is required.") }}';
                        errorsBox.classList.remove('d-none');
                        return;
                    }
                    payload.new_category_name = newName;
                } else {
                    payload.category_key = selectedCategory;
                }

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch('{{ route("recommendations.lab-requests.quick-add-test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok || !data.success) throw data;
                    return data;
                })
                .then((data) => {
                    const card = findOrCreateCategoryCard(data.test.category_key, data.test.category_label);
                    appendCheckbox(card, data.test);
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('lrQuickAddTestModal')).hide();
                })
                .catch((error) => {
                    const message = error?.message || '{{ __("Failed to add test.") }}';
                    const errorList = error?.errors ? Object.values(error.errors).flat().join(' ') : '';
                    errorsBox.textContent = errorList || message;
                    errorsBox.classList.remove('d-none');
                });
            });
        }

        // Build hidden tests[] inputs from checked checkboxes right before submit
        const form = document.querySelector('#newLabRequestModal form');
        if (form) {
            form.addEventListener('submit', function (e) {
                form.querySelectorAll('.lr-hidden-test-input').forEach((el) => el.remove());

                const checked = Array.from(document.querySelectorAll('.lr-test-checkbox:checked'));
                const freeRows = Array.from(document.querySelectorAll('#tests-container input[name$="[test_name]"]'))
                    .filter((input) => input.value.trim() !== '');

                if (checked.length === 0 && freeRows.length === 0) {
                    e.preventDefault();
                    alert('{{ __("Please select at least one test from the checklist or add one below.") }}');
                    return;
                }

                let nextIndex = 0;
                form.querySelectorAll('input[name^="tests["]').forEach((input) => {
                    const m = input.name.match(/^tests\[(\d+)\]/);
                    if (m) nextIndex = Math.max(nextIndex, parseInt(m[1], 10) + 1);
                });

                checked.forEach((cb) => {
                    const idx = nextIndex++;
                    addHidden(form, 'tests[' + idx + '][test_name]', cb.dataset.testName);
                    if (cb.dataset.labTestId) {
                        addHidden(form, 'tests[' + idx + '][lab_test_id]', cb.dataset.labTestId);
                    }
                });
            });
        }

        function addHidden(form, name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.className = 'lr-hidden-test-input';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();

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
        if (labPhoneInput) labPhoneInput.placeholder = '{{ __("Will be auto-filled") }}';
        if (labWhatsAppInput) labWhatsAppInput.placeholder = '{{ __("Will be auto-filled") }}';
        if (labEmailInput) labEmailInput.placeholder = '{{ __("Will be auto-filled") }}';
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





function duplicateLabRequest(id) {
    if (confirm('{{ __("Create a copy of this lab request?") }}')) {
        // Implement duplication logic
        alert('Duplicate lab request ' + id + ' - Feature coming soon!');
    }
}



function deleteLabRequest(id) {
    if (confirm('{{ __("Are you sure you want to delete this lab request? This action cannot be undone.") }}')) {
        // Create a form to submit the DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url('recommendations/lab-requests') }}' + '/' + id;
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

// Delegated handler for Share Internally buttons (prevents oval overlay)
(function(){
  document.addEventListener('click', function(ev){
    const link = ev.target.closest('.js-share-internal');
    if(!link) return;
    if (link.dataset.enhanced) return;
    try {
      const payload = {
        transfer_type: link.dataset.transferType || 'lab_request',
        patient_id: Number(link.dataset.patientId || 0),
        source_type: link.dataset.sourceType || 'lab_request',
        source_id: Number(link.dataset.sourceId || 0),
        metadata: {
          patient_name: link.dataset.patientName || '',
          request_number: link.dataset.requestNumber || ''
        }
      };
      const v = JSON.stringify(payload);
      localStorage.setItem('prefill_transfer', v);
      sessionStorage.setItem('prefill_transfer', v);
      link.href = link.href + '?prefill_transfer=' + encodeURIComponent(btoa(v));
      link.dataset.enhanced = '1';
    } catch(e) {}
  }, { passive: true });
})();



</script>
@endpush
