@extends('layouts.app')

@section('title', __('Edit Lab Request'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ __('Edit Lab Request') }}</h1>
                    <p class="text-muted mb-0">{{ __('Request #:number', ['number' => $labRequest->request_number]) }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.lab-requests.show', $labRequest) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Details') }}
                    </a>
                    <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <form action="{{ route('recommendations.lab-requests.update', $labRequest) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Basic Information') }}</h5>
                            </div>
                            <div class="card-body">
                                <!-- Patient Selection -->
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                        <option value="">{{ __('Select Patient') }}</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ $labRequest->patient_id == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name }} - {{ $patient->patient_id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Clinical Notes -->
                                <div class="mb-3">
                                    <label for="clinical_notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                    <textarea class="form-control @error('clinical_notes') is-invalid @enderror" 
                                              id="clinical_notes" name="clinical_notes" rows="3" 
                                              placeholder="{{ __('Enter clinical notes, symptoms, or relevant medical history') }}">{{ old('clinical_notes', $labRequest->clinical_notes) }}</textarea>
                                    @error('clinical_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Due Date and Priority -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                               id="due_date" name="due_date" 
                                               value="{{ old('due_date', $labRequest->due_date?->format('Y-m-d')) }}"
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                            <option value="normal" {{ old('priority', $labRequest->priority) == 'normal' ? 'selected' : '' }}>{{ __('Normal') }}</option>
                                            <option value="urgent" {{ old('priority', $labRequest->priority) == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                                            <option value="stat" {{ old('priority', $labRequest->priority) == 'stat' ? 'selected' : '' }}>{{ __('STAT') }}</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Laboratory Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Laboratory Information') }}</h5>
                            </div>
                            <div class="card-body">
                                <!-- External Laboratory Selection -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="external_lab_id" class="form-label">{{ __('Preferred Laboratory') }}</label>
                                        <select class="form-select" id="external_lab_id" name="external_lab_id" onchange="handleLabSelection()">
                                            <option value="">{{ __('Select from preferred labs') }}</option>
                                            @if($externalLabs->count() > 0)
                                                @foreach($externalLabs as $lab)
                                                    <option value="{{ $lab->id }}"
                                                            data-name="{{ $lab->name }}"
                                                            data-phone="{{ $lab->phone }}"
                                                            data-whatsapp="{{ $lab->whatsapp }}"
                                                            data-email="{{ $lab->email }}"
                                                            data-address="{{ $lab->address }}"
                                                            {{ $labRequest->lab_name == $lab->name ? 'selected' : '' }}>
                                                        {{ $lab->display_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                            <option value="custom" {{ !$externalLabs->where('name', $labRequest->lab_name)->count() ? 'selected' : '' }}>{{ __('Other laboratory (enter manually)') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="communication_method" class="form-label">{{ __('Communication Method') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('communication_method') is-invalid @enderror" id="communication_method" name="communication_method" required>
                                            <option value="whatsapp" {{ old('communication_method', $labRequest->communication_method) == 'whatsapp' ? 'selected' : '' }}>{{ __('WhatsApp') }}</option>
                                            <option value="email" {{ old('communication_method', $labRequest->communication_method) == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                                        </select>
                                        @error('communication_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Lab Details -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="lab_name" class="form-label">{{ __('Laboratory Name') }}</label>
                                        <input type="text" class="form-control @error('lab_name') is-invalid @enderror" 
                                               id="lab_name" name="lab_name" 
                                               value="{{ old('lab_name', $labRequest->lab_name) }}"
                                               placeholder="{{ __('Enter laboratory name') }}">
                                        @error('lab_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lab_phone" class="form-label">{{ __('Phone Number') }}</label>
                                        <input type="text" class="form-control @error('lab_phone') is-invalid @enderror" 
                                               id="lab_phone" name="lab_phone" 
                                               value="{{ old('lab_phone', $labRequest->lab_phone) }}"
                                               placeholder="{{ __('Enter phone number') }}">
                                        @error('lab_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="lab_whatsapp" class="form-label">{{ __('WhatsApp Number') }}</label>
                                        <input type="text" class="form-control @error('lab_whatsapp') is-invalid @enderror" 
                                               id="lab_whatsapp" name="lab_whatsapp" 
                                               value="{{ old('lab_whatsapp', $labRequest->lab_whatsapp) }}"
                                               placeholder="{{ __('Enter WhatsApp number') }}">
                                        @error('lab_whatsapp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lab_email" class="form-label">{{ __('Email Address') }}</label>
                                        <input type="email" class="form-control @error('lab_email') is-invalid @enderror" 
                                               id="lab_email" name="lab_email" 
                                               value="{{ old('lab_email', $labRequest->lab_email) }}"
                                               placeholder="{{ __('Enter email address') }}">
                                        @error('lab_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tests -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Lab Tests') }}</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTest()">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Add Test') }}
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="tests-container">
                                    @foreach($labRequest->tests as $index => $test)
                                        <div class="test-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">{{ __('Test :number', ['number' => $index + 1]) }}</h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTest(this)">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="tests[{{ $index }}][test_name]" 
                                                           value="{{ $test->test_name }}" required 
                                                           placeholder="{{ __('Enter test name') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Instructions') }}</label>
                                                    <input type="text" class="form-control" name="tests[{{ $index }}][instructions]" 
                                                           value="{{ $test->instructions }}"
                                                           placeholder="{{ __('Special instructions (optional)') }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($labRequest->tests->count() == 0)
                                    <div class="text-muted text-center py-3">
                                        {{ __('No tests added yet. Click "Add Test" to get started.') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">{{ __('Additional Notes') }}</h5>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="{{ __('Any additional notes or special instructions') }}">{{ old('notes', $labRequest->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('recommendations.lab-requests.show', $labRequest) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Update Lab Request') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Quick Lab Tests -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">{{ __('Quick Add Tests') }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="quick_test_search" class="form-label">{{ __('Search Tests') }}</label>
                                    <input type="text" class="form-control" id="quick_test_search" 
                                           placeholder="{{ __('Type to search tests...') }}" 
                                           onkeyup="filterQuickTests()">
                                </div>
                                
                                <div id="quick-tests-list" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($labTests as $test)
                                        <div class="quick-test-item border-bottom py-2" data-test-name="{{ strtolower($test->name) }}">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $test->name }}</strong>
                                                    @if($test->category)
                                                        <br><small class="text-muted">{{ $test->category }}</small>
                                                    @endif
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="addQuickTest('{{ $test->name }}', '{{ $test->id }}')">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let testIndex = {{ $labRequest->tests->count() }};

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

        // Update placeholders
        labNameInput.placeholder = '{{ __("Enter laboratory name") }}';
        if (labPhoneInput) labPhoneInput.placeholder = '{{ __("Enter phone number") }}';
        if (labWhatsAppInput) labWhatsAppInput.placeholder = '{{ __("Enter WhatsApp number") }}';
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

        labNameInput.value = '';
        if (labPhoneInput) labPhoneInput.value = '';
        if (labWhatsAppInput) labWhatsAppInput.value = '';
        if (labEmailInput) labEmailInput.value = '';

        labNameInput.placeholder = '{{ __("Select a laboratory first") }}';
        if (labPhoneInput) labPhoneInput.placeholder = '{{ __("Select a laboratory first") }}';
        if (labWhatsAppInput) labWhatsAppInput.placeholder = '{{ __("Select a laboratory first") }}';
        if (labEmailInput) labEmailInput.placeholder = '{{ __("Select a laboratory first") }}';
    }
}

// Add new test
function addTest() {
    const container = document.getElementById('tests-container');
    const testHtml = `
        <div class="test-item border rounded p-3 mb-3" data-index="${testIndex}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">{{ __('Test') }} ${testIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTest(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tests[${testIndex}][test_name]" required
                           placeholder="{{ __('Enter test name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Instructions') }}</label>
                    <input type="text" class="form-control" name="tests[${testIndex}][instructions]"
                           placeholder="{{ __('Special instructions (optional)') }}">
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', testHtml);
    testIndex++;

    // Focus on the new test name input
    const newTestInput = container.lastElementChild.querySelector('input[name*="[test_name]"]');
    if (newTestInput) {
        newTestInput.focus();
    }
}

// Remove test
function removeTest(button) {
    const testItem = button.closest('.test-item');
    if (testItem) {
        testItem.remove();
        updateTestNumbers();
    }
}

// Update test numbers after removal
function updateTestNumbers() {
    const testItems = document.querySelectorAll('.test-item');
    testItems.forEach((item, index) => {
        const header = item.querySelector('h6');
        if (header) {
            header.textContent = `{{ __('Test') }} ${index + 1}`;
        }
    });
}

// Add quick test
function addQuickTest(testName, testId) {
    const container = document.getElementById('tests-container');
    const testHtml = `
        <div class="test-item border rounded p-3 mb-3" data-index="${testIndex}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="mb-0">{{ __('Test') }} ${testIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTest(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="tests[${testIndex}][test_name]"
                           value="${testName}" required placeholder="{{ __('Enter test name') }}">
                    <input type="hidden" name="tests[${testIndex}][lab_test_id]" value="${testId}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Instructions') }}</label>
                    <input type="text" class="form-control" name="tests[${testIndex}][instructions]"
                           placeholder="{{ __('Special instructions (optional)') }}">
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', testHtml);
    testIndex++;
}

// Filter quick tests
function filterQuickTests() {
    const searchTerm = document.getElementById('quick_test_search').value.toLowerCase();
    const testItems = document.querySelectorAll('.quick-test-item');

    testItems.forEach(item => {
        const testName = item.dataset.testName;
        if (testName.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Set initial lab selection state
    handleLabSelection();
});
</script>
@endpush
