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

            <form id="labRequestEditForm" action="{{ route('recommendations.lab-requests.update', $labRequest) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-xl-6 col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Basic Information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Patient, Due Date and Priority -->
                                        <div class="row">
                                    <div class="col-md-6 mb-3">
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
                                    <div class="col-md-3 mb-3">
                                        <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                        <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                               id="due_date" name="due_date"
                                               value="{{ old('due_date', $labRequest->due_date?->format('Y-m-d')) }}"
                                               min="{{ date('Y-m-d') }}">
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
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
                            </div>

                            <!-- Laboratory Information -->
                            <div class="col-xl-6 col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">{{ __('Laboratory Information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                <!-- External Laboratory Selection -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="external_lab_id" class="form-label">{{ __('Preferred Laboratory') }}</label>
                                        <select class="form-select" id="external_lab_id" name="external_lab_id">
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
                            </div>
                        </div>

                        <!-- Tests -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ __('Lab Tests') }} <span class="text-danger">*</span></h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="lr-add-category-btn">
                                    <i class="fas fa-folder-plus me-1"></i>{{ __('Add Category') }}
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row" id="lr-tests-grid">
                                    @foreach($labTestCatalog ?? [] as $categoryKey => $group)
                                        <div class="col-md-4 lr-category-card mb-3" data-category-key="{{ $categoryKey }}">
                                            <strong class="text-primary text-uppercase small d-block mb-2 pb-1 border-bottom border-primary">{{ $group['label'] }}</strong>
                                            <div class="lr-tests-list">
                                                @foreach($group['tests'] as $test)
                                                    @php
                                                        $isChecked = $test['id']
                                                            ? in_array($test['id'], $checkedLabTestIds ?? [], true)
                                                            : in_array(strtolower(trim($test['name'])), $checkedBuiltinNames ?? [], true);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input lr-test-checkbox" type="checkbox"
                                                               id="edit_lr_test_{{ $categoryKey }}_{{ $loop->index }}"
                                                               data-test-name="{{ $test['name'] }}"
                                                               data-lab-test-id="{{ $test['id'] }}"
                                                               {{ $isChecked ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="edit_lr_test_{{ $categoryKey }}_{{ $loop->index }}">
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

                                <!-- Other / one-off tests that don't match the checklist above -->
                                <div class="mt-1">
                                    <label class="form-label small text-muted mb-1">{{ __('Other / Additional Tests (not listed above)') }}</label>
                                    <div id="tests-container">
                                        @foreach($otherTests ?? [] as $index => $test)
                                            <div class="test-item border rounded p-3 mb-2">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <input type="text" class="form-control" name="tests[{{ $index }}][test_name]"
                                                               value="{{ $test->test_name }}"
                                                               placeholder="{{ __('Test name') }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <button type="button" class="btn btn-outline-danger btn-sm remove-test">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <input type="text" class="form-control" name="tests[{{ $index }}][instructions]"
                                                           value="{{ $test->instructions }}"
                                                           placeholder="{{ __('Special instructions (optional)') }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-test">
                                        <i class="fas fa-plus me-1"></i>
                                        {{ __('Add Row') }}
                                    </button>
                                </div>
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
                </div>
            </form>
        </div>
    </div>
</div>

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
@endsection

@push('scripts')
<script>
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

// Tests checklist: quick-add-test/category + "Other" freeform rows + sync on submit
(function initLabTestChecklist() {
    let lrQuickAddContext = { categoryKey: null, categoryLabel: null };

    function openQuickAddModal(categoryKey, categoryLabel) {
        lrQuickAddContext = { categoryKey, categoryLabel };

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
        const id = 'edit_lr_test_' + card.dataset.categoryKey + '_new_' + idx + '_' + test.id;

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

    function updateRemoveButtons() {
        const testItems = document.querySelectorAll('#tests-container .test-item');
        testItems.forEach((item) => {
            const removeBtn = item.querySelector('.remove-test');
            if (removeBtn) removeBtn.style.display = 'inline-block';
        });
    }

    function addOtherTestRow() {
        const container = document.getElementById('tests-container');
        if (!container) return;

        let nextIndex = 0;
        document.querySelectorAll('input[name^="tests["]').forEach((input) => {
            const m = input.name.match(/^tests\[(\d+)\]/);
            if (m) nextIndex = Math.max(nextIndex, parseInt(m[1], 10) + 1);
        });

        const item = document.createElement('div');
        item.className = 'test-item border rounded p-3 mb-2';
        item.innerHTML =
            '<div class="row">' +
                '<div class="col-md-8">' +
                    '<input type="text" class="form-control" name="tests[' + nextIndex + '][test_name]" placeholder="{{ __("Test name") }}" required>' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<button type="button" class="btn btn-outline-danger btn-sm remove-test"><i class="fas fa-trash"></i></button>' +
                '</div>' +
            '</div>' +
            '<div class="mt-2">' +
                '<input type="text" class="form-control" name="tests[' + nextIndex + '][instructions]" placeholder="{{ __("Special instructions (optional)") }}">' +
            '</div>';

        container.appendChild(item);
        updateRemoveButtons();
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
                return;
            }

            const addOtherBtn = e.target.closest('#add-test');
            if (addOtherBtn) {
                addOtherTestRow();
                return;
            }

            if (e.target.closest('.remove-test')) {
                const item = e.target.closest('.test-item');
                if (item) item.remove();
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

        updateRemoveButtons();

        // Build hidden tests[] inputs from checked checkboxes right before submit
        const form = document.getElementById('labRequestEditForm');
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

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    // Wire up change handler and set initial selection state
    const labSelectEl = document.getElementById('external_lab_id');
    if (labSelectEl) {
        labSelectEl.addEventListener('change', handleLabSelection);
    }
    handleLabSelection();
});
</script>
@endpush
