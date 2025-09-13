@extends('layouts.app')

@section('page-title', __('Create Radiology Request'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle text-success"></i>
                        {{ __('Create Radiology Request') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Request radiology and imaging tests for your patient') }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.radiology.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Requests') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('recommendations.radiology.store') }}" method="POST" id="radiology-form">
        @csrf
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Basic Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $selectedPatient?->id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    @foreach(\App\Models\RadiologyRequest::PRIORITIES as $key => $label)
                                    <option value="{{ $key }}" {{ old('priority', 'normal') === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="suspected_diagnosis" class="form-label">{{ __('Suspected Diagnosis') }}</label>
                            <input type="text" class="form-control @error('suspected_diagnosis') is-invalid @enderror" 
                                   id="suspected_diagnosis" name="suspected_diagnosis" value="{{ old('suspected_diagnosis') }}"
                                   placeholder="{{ __('e.g., Pneumonia, Fracture, etc.') }}">
                            @error('suspected_diagnosis')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clinical_notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                <textarea class="form-control @error('clinical_notes') is-invalid @enderror" 
                                          id="clinical_notes" name="clinical_notes" rows="3"
                                          placeholder="{{ __('Clinical findings and relevant information...') }}">{{ old('clinical_notes') }}</textarea>
                                @error('clinical_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="clinical_history" class="form-label">{{ __('Clinical History') }}</label>
                                <textarea class="form-control @error('clinical_history') is-invalid @enderror" 
                                          id="clinical_history" name="clinical_history" rows="3"
                                          placeholder="{{ __('Relevant medical history...') }}">{{ old('clinical_history') }}</textarea>
                                @error('clinical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                   id="due_date" name="due_date" value="{{ old('due_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Leave empty if no specific due date') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tests Required -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-x-ray"></i>
                            {{ __('Tests Required') }}
                        </h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addTest()">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Add Test') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="tests-container">
                            <div class="test-item border rounded p-3 mb-3" data-index="0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ __('Test') }} #1</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-test" onclick="removeTest(0)" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control test-name" name="tests[0][test_name]"
                                                   placeholder="{{ __('Enter test name or select from database') }}" required>
                                            <button type="button" class="btn btn-outline-secondary" onclick="showTestSelector(0)">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="tests[0][radiology_test_id]" class="radiology-test-id">
                                        <div class="form-text">{{ __('You can type a custom test name or click the search button to select from our database') }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ __('Options') }}</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tests[0][with_contrast]" value="1">
                                            <label class="form-check-label">{{ __('With Contrast') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="tests[0][urgent]" value="1">
                                            <label class="form-check-label">{{ __('Urgent') }}</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ __('Clinical Indication') }}</label>
                                        <input type="text" class="form-control" name="tests[0][clinical_indication]" 
                                               placeholder="{{ __('Why this test is needed') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ __('Special Instructions') }}</label>
                                        <input type="text" class="form-control" name="tests[0][instructions]" 
                                               placeholder="{{ __('Special instructions for this test') }}">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Special Requirements') }}</label>
                                    <textarea class="form-control" name="tests[0][special_requirements]" rows="2"
                                              placeholder="{{ __('Any special requirements or preparations') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Radiology Center Information -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-hospital"></i>
                            {{ __('Radiology Center') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="radiology_center_name" class="form-label">{{ __('Center Name') }}</label>
                            <input type="text" class="form-control @error('radiology_center_name') is-invalid @enderror" 
                                   id="radiology_center_name" name="radiology_center_name" value="{{ old('radiology_center_name') }}"
                                   placeholder="{{ __('Radiology center name') }}">
                            @error('radiology_center_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="radiology_center_phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="tel" class="form-control @error('radiology_center_phone') is-invalid @enderror" 
                                   id="radiology_center_phone" name="radiology_center_phone" value="{{ old('radiology_center_phone') }}"
                                   placeholder="{{ __('Phone number') }}">
                            @error('radiology_center_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="radiology_center_whatsapp" class="form-label">{{ __('WhatsApp') }}</label>
                            <input type="tel" class="form-control @error('radiology_center_whatsapp') is-invalid @enderror" 
                                   id="radiology_center_whatsapp" name="radiology_center_whatsapp" value="{{ old('radiology_center_whatsapp') }}"
                                   placeholder="{{ __('WhatsApp number') }}">
                            @error('radiology_center_whatsapp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="radiology_center_email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control @error('radiology_center_email') is-invalid @enderror" 
                                   id="radiology_center_email" name="radiology_center_email" value="{{ old('radiology_center_email') }}"
                                   placeholder="{{ __('Email address') }}">
                            @error('radiology_center_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="radiology_center_address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control @error('radiology_center_address') is-invalid @enderror" 
                                      id="radiology_center_address" name="radiology_center_address" rows="3"
                                      placeholder="{{ __('Center address') }}">{{ old('radiology_center_address') }}</textarea>
                            @error('radiology_center_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note"></i>
                            {{ __('Additional Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="{{ __('Any additional notes or instructions...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Request') }}
                            </button>
                            <a href="{{ route('recommendations.radiology.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Test Selection Modal -->
<div class="modal fade" id="testSelectorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Select Radiology Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="testSearch" placeholder="{{ __('Search tests...') }}">
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <h6>{{ __('Categories') }}</h6>
                        <div class="list-group" id="categoryList">
                            <button type="button" class="list-group-item list-group-item-action active" data-category="">
                                {{ __('All Tests') }} <span class="badge bg-primary">26</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="x_ray">
                                {{ __('X-Ray') }} <span class="badge bg-secondary">5</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="ct_scan">
                                {{ __('CT Scan') }} <span class="badge bg-secondary">5</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="mri">
                                {{ __('MRI') }} <span class="badge bg-secondary">5</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="ultrasound">
                                {{ __('Ultrasound') }} <span class="badge bg-secondary">4</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="nuclear_medicine">
                                {{ __('Nuclear Medicine') }} <span class="badge bg-secondary">3</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="angiography">
                                {{ __('Angiography') }} <span class="badge bg-secondary">2</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="mammography">
                                {{ __('Mammography') }} <span class="badge bg-secondary">1</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" data-category="other">
                                {{ __('Other') }} <span class="badge bg-secondary">1</span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6>{{ __('Tests') }}</h6>
                        <div id="testsList" style="max-height: 400px; overflow-y: auto;">
                            <!-- Tests will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-info" onclick="openCreateTestModal()">
                    <i class="fas fa-plus"></i> {{ __('Create New Test') }}
                </button>
                <button type="button" class="btn btn-primary" onclick="selectCustomTest()">{{ __('Use Custom Name') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Create Test Modal -->
<div class="modal fade" id="quickCreateTestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Create New Test') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickCreateTestForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="category" placeholder="{{ __('e.g., x_ray, ct_scan, mri, ultrasound') }}" required>
                        <div class="form-text">{{ __('Use existing category or create new one') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Create & Use') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let testIndex = 1;

// Add new test
function addTest() {
    const container = document.getElementById('tests-container');
    const testItem = document.createElement('div');
    testItem.className = 'test-item border rounded p-3 mb-3';
    testItem.setAttribute('data-index', testIndex);

    testItem.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 class="mb-0">{{ __('Test') }} #${testIndex + 1}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-test" onclick="removeTest(${testIndex})">
                <i class="fas fa-trash"></i>
            </button>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">{{ __('Test Name') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control test-name" name="tests[${testIndex}][test_name]"
                           placeholder="{{ __('Enter test name or select from database') }}" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="showTestSelector(${testIndex})">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <input type="hidden" name="tests[${testIndex}][radiology_test_id]" class="radiology-test-id">
                <div class="form-text">{{ __('You can type a custom test name or click the search button to select from our database') }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">{{ __('Options') }}</label>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tests[${testIndex}][with_contrast]" value="1">
                    <label class="form-check-label">{{ __('With Contrast') }}</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tests[${testIndex}][urgent]" value="1">
                    <label class="form-check-label">{{ __('Urgent') }}</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('Clinical Indication') }}</label>
                <input type="text" class="form-control" name="tests[${testIndex}][clinical_indication]"
                       placeholder="{{ __('Why this test is needed') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ __('Special Instructions') }}</label>
                <input type="text" class="form-control" name="tests[${testIndex}][instructions]"
                       placeholder="{{ __('Special instructions for this test') }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Special Requirements') }}</label>
            <textarea class="form-control" name="tests[${testIndex}][special_requirements]" rows="2"
                      placeholder="{{ __('Any special requirements or preparations') }}"></textarea>
        </div>
    `;

    container.appendChild(testItem);
    testIndex++;

    // Show remove buttons if more than one test
    updateRemoveButtons();
}

// Remove test
function removeTest(index) {
    const testItem = document.querySelector(`[data-index="${index}"]`);
    if (testItem) {
        testItem.remove();
        updateRemoveButtons();
        updateTestNumbers();
    }
}

// Update remove button visibility
function updateRemoveButtons() {
    const testItems = document.querySelectorAll('.test-item');
    const removeButtons = document.querySelectorAll('.remove-test');

    removeButtons.forEach(button => {
        button.style.display = testItems.length > 1 ? 'inline-block' : 'none';
    });
}

// Update test numbers
function updateTestNumbers() {
    const testItems = document.querySelectorAll('.test-item');
    testItems.forEach((item, index) => {
        const header = item.querySelector('h6');
        header.textContent = `{{ __('Test') }} #${index + 1}`;
    });
}

let currentTestIndex = 0;
let allTests = [];

// Show test selector modal
function showTestSelector(testIndex) {
    currentTestIndex = testIndex;
    loadTests();
    const modal = new bootstrap.Modal(document.getElementById('testSelectorModal'));
    modal.show();
}

// Load tests from server
function loadTests(category = '', search = '') {
    const url = new URL('{{ route("recommendations.radiology.tests.search") }}');
    if (category) url.searchParams.append('category', category);
    if (search) url.searchParams.append('search', search);

    console.log('Loading tests with category:', category, 'search:', search, 'URL:', url.toString());

    fetch(url)
        .then(response => response.json())
        .then(tests => {
            console.log('Loaded tests:', tests.length, 'tests');
            allTests = tests;
            displayTests(tests);
        })
        .catch(error => {
            console.error('Error loading tests:', error);
            document.getElementById('testsList').innerHTML = '<div class="alert alert-danger">Error loading tests</div>';
        });
}

// Display tests in the modal
function displayTests(tests) {
    const testsList = document.getElementById('testsList');

    if (tests.length === 0) {
        testsList.innerHTML = '<div class="alert alert-info">{{ __("No tests found") }}</div>';
        return;
    }

    let html = '';
    tests.forEach(test => {
        html += `
            <div class="card mb-2 test-card" style="cursor: pointer;" onclick="selectTest(${test.id}, '${test.name}')">
                <div class="card-body p-3">
                    <h6 class="card-title mb-1">${test.name}</h6>
                    <small class="text-muted">${test.description || ''}</small>
                    ${test.preparation_instructions ? `<br><small class="text-info"><i class="fas fa-info-circle"></i> ${test.preparation_instructions}</small>` : ''}
                </div>
            </div>
        `;
    });

    testsList.innerHTML = html;
}

// Select a test from the database
function selectTest(testId, testName) {
    const testNameInput = document.querySelector(`input[name="tests[${currentTestIndex}][test_name]"]`);
    const testIdInput = document.querySelector(`input[name="tests[${currentTestIndex}][radiology_test_id]"]`);

    testNameInput.value = testName;
    testIdInput.value = testId;

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('testSelectorModal'));
    modal.hide();
}

// Select custom test (just close modal, keep current input)
function selectCustomTest() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('testSelectorModal'));
    modal.hide();
}

// Open create test modal
function openCreateTestModal() {
    const testSelectorModal = bootstrap.Modal.getInstance(document.getElementById('testSelectorModal'));
    testSelectorModal.hide();

    const createModal = new bootstrap.Modal(document.getElementById('quickCreateTestModal'));
    createModal.show();
}

// Handle quick create test form
document.getElementById('quickCreateTestForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Creating...") }}';
    submitBtn.disabled = true;

    fetch('{{ route("recommendations.radiology.tests.create-custom") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Use the newly created test
            const testNameInput = document.querySelector(`input[name="tests[${currentTestIndex}][test_name]"]`);
            const testIdInput = document.querySelector(`input[name="tests[${currentTestIndex}][radiology_test_id]"]`);

            testNameInput.value = data.test.name;
            testIdInput.value = data.test.id;

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('quickCreateTestModal'));
            modal.hide();

            // Reset form
            this.reset();

            // Show success message with toast notification
            showSuccessToast('{{ __("Test created successfully and added to your request!") }}');
        } else {
            alert(data.message || '{{ __("Failed to create test") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred while creating the test") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Helper function for success notifications
function showSuccessToast(message) {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    // Add to toast container or create one
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();

    // Remove element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();

    // Category filter
    document.querySelectorAll('#categoryList .list-group-item').forEach(item => {
        item.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('#categoryList .list-group-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            // Load tests for category
            const category = this.getAttribute('data-category');
            const search = document.getElementById('testSearch').value;
            loadTests(category, search);
        });
    });

    // Search functionality
    let searchTimeout;
    document.getElementById('testSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const category = document.querySelector('#categoryList .list-group-item.active').getAttribute('data-category');
            loadTests(category, this.value);
        }, 300);
    });
});
</script>
@endpush

@endsection
