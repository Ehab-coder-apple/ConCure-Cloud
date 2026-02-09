@extends('layouts.app')

@section('page-title', __('Custom Vital Signs Management'))

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-stethoscope text-primary"></i>
                        {{ __('Custom Vital Signs') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Create and manage additional checkup points for your clinic') }}
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomSignModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Custom Vital Sign') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Guide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('How to Use Custom Vital Signs') }}
                </h6>
                <p class="mb-2">{{ __('Custom vital signs allow you to track additional measurements specific to your medical specialty:') }}</p>
                <ul class="mb-2">
                    <li><strong>{{ __('Number Type') }}:</strong> {{ __('For measurements like oxygen saturation, peak flow, etc.') }}</li>
                    <li><strong>{{ __('Select Type') }}:</strong> {{ __('For categorical assessments like pain level, mobility status, etc.') }}</li>
                    <li><strong>{{ __('Text Type') }}:</strong> {{ __('For free-form observations and notes') }}</li>
                </ul>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadTemplate('oxygen')">
                            <i class="fas fa-lungs me-1"></i>
                            {{ __('Oxygen Saturation Template') }}
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="loadTemplate('pain')">
                            <i class="fas fa-hand-paper me-1"></i>
                            {{ __('Pain Scale Template') }}
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="loadTemplate('mobility')">
                            <i class="fas fa-walking me-1"></i>
                            {{ __('Mobility Assessment Template') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-heartbeat fa-2x text-primary mb-2"></i>
                    <h4 class="text-primary">{{ $customSigns->where('is_active', true)->count() }}</h4>
                    <small class="text-muted">{{ __('Active Signs') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-calculator fa-2x text-success mb-2"></i>
                    <h4 class="text-success">{{ $customSigns->where('type', 'number')->count() }}</h4>
                    <small class="text-muted">{{ __('Number Types') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-list fa-2x text-warning mb-2"></i>
                    <h4 class="text-warning">{{ $customSigns->where('type', 'select')->count() }}</h4>
                    <small class="text-muted">{{ __('Select Types') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-font fa-2x text-info mb-2"></i>
                    <h4 class="text-info">{{ $customSigns->where('type', 'text')->count() }}</h4>
                    <small class="text-muted">{{ __('Text Types') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Vital Signs List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Your Custom Vital Signs') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($customSigns->count() > 0)
                        <!-- Bulk Actions -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    <label class="form-check-label" for="selectAll">
                                        {{ __('Select All') }}
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm" onclick="bulkAction('activate')" id="bulkActivateBtn" disabled>
                                        <i class="fas fa-check me-1"></i>
                                        {{ __('Activate Selected') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bulkAction('deactivate')" id="bulkDeactivateBtn" disabled>
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Deactivate Selected') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()">
                                        </th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Normal Range') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Order') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customSigns as $sign)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input sign-checkbox" value="{{ $sign->id }}" onchange="updateBulkButtons()">
                                        </td>
                                        <td>
                                            <strong>{{ $sign->name }}</strong>
                                            @if($sign->type === 'select' && $sign->options)
                                                <br><small class="text-muted">
                                                    {{ count($sign->options) }} {{ __('options') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $sign->type === 'number' ? 'success' : ($sign->type === 'select' ? 'warning' : 'info') }}">
                                                {{ ucfirst($sign->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $sign->unit ?: '-' }}</td>
                                        <td>{{ $sign->normal_range ?: '-' }}</td>
                                        <td>
                                            <form action="{{ route('admin.custom-vital-signs.toggle-status', $sign) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-{{ $sign->is_active ? 'success' : 'secondary' }}">
                                                    <i class="fas fa-{{ $sign->is_active ? 'check' : 'times' }}"></i>
                                                    {{ $sign->is_active ? __('Active') : __('Inactive') }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>{{ $sign->sort_order }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.custom-vital-signs.edit', $sign) }}" 
                                                   class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete({{ $sign->id }})" title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-stethoscope fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Custom Vital Signs Yet') }}</h5>
                            <p class="text-muted">{{ __('Create your first custom vital sign to start tracking additional measurements.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomSignModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Your First Custom Vital Sign') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Custom Vital Sign Modal -->
<div class="modal fade" id="createCustomSignModal" tabindex="-1" aria-labelledby="createCustomSignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createCustomSignModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Create Custom Vital Sign') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.custom-vital-signs.store') }}" method="POST" id="createCustomSignForm">
                @csrf
                <div class="modal-body">
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="name" class="form-label">{{ __('Vital Sign Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="{{ __('e.g., Oxygen Saturation, Pain Level') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="unit" class="form-label">{{ __('Unit') }}</label>
                            <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                   id="unit" name="unit" value="{{ old('unit') }}" 
                                   placeholder="{{ __('e.g., %, mg/dL, /10') }}">
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Type Selection -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="type" class="form-label">{{ __('Input Type') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required onchange="toggleTypeOptions()">
                                <option value="">{{ __('Select input type...') }}</option>
                                <option value="number" {{ old('type') === 'number' ? 'selected' : '' }}>
                                    {{ __('Number') }} - {{ __('For numeric measurements') }}
                                </option>
                                <option value="select" {{ old('type') === 'select' ? 'selected' : '' }}>
                                    {{ __('Select') }} - {{ __('For predefined options') }}
                                </option>
                                <option value="text" {{ old('type') === 'text' ? 'selected' : '' }}>
                                    {{ __('Text') }} - {{ __('For free-form text') }}
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Number Type Options -->
                    <div id="numberOptions" class="row mb-3" style="display: none;">
                        <div class="col-md-6">
                            <label for="min_value" class="form-label">{{ __('Minimum Value') }}</label>
                            <input type="number" class="form-control" id="min_value" name="min_value" 
                                   value="{{ old('min_value') }}" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label for="max_value" class="form-label">{{ __('Maximum Value') }}</label>
                            <input type="number" class="form-control" id="max_value" name="max_value" 
                                   value="{{ old('max_value') }}" step="0.01">
                        </div>
                    </div>

                    <!-- Select Type Options -->
                    <div id="selectOptions" class="mb-3" style="display: none;">
                        <label class="form-label">{{ __('Options') }} <span class="text-danger">*</span></label>
                        <div id="optionsContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="option_keys[]" placeholder="{{ __('Value (e.g., 0)') }}">
                                <input type="text" class="form-control" name="option_values[]" placeholder="{{ __('Label (e.g., No Pain)') }}">
                                <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Add Option') }}
                        </button>
                    </div>

                    <!-- Normal Range -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="normal_range" class="form-label">{{ __('Normal Range') }}</label>
                            <input type="text" class="form-control @error('normal_range') is-invalid @enderror" 
                                   id="normal_range" name="normal_range" value="{{ old('normal_range') }}" 
                                   placeholder="{{ __('e.g., 95-100%, 0-2/10') }}">
                            @error('normal_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="sort_order" class="form-label">{{ __('Display Order') }}</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', $customSigns->count() + 1) }}" 
                                   min="1">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create Vital Sign') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    {{ __('Confirm Deletion') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this custom vital sign?') }}</p>
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ __('This action cannot be undone and will remove all associated data.') }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('Cancel') }}
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleTypeOptions() {
    const type = document.getElementById('type').value;
    const numberOptions = document.getElementById('numberOptions');
    const selectOptions = document.getElementById('selectOptions');
    
    numberOptions.style.display = type === 'number' ? 'flex' : 'none';
    selectOptions.style.display = type === 'select' ? 'block' : 'none';
}

function addOption() {
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="option_keys[]" placeholder="{{ __('Value') }}">
        <input type="text" class="form-control" name="option_values[]" placeholder="{{ __('Label') }}">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeOption(button) {
    button.closest('.input-group').remove();
}

function confirmDelete(signId) {
    const form = document.getElementById('deleteForm');
    form.action = `/admin/custom-vital-signs/${signId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');
    const checkboxes = document.querySelectorAll('.sign-checkbox');

    // Sync the header checkbox with the main one
    selectAll.checked = selectAllHeader.checked;
    selectAllHeader.checked = selectAll.checked;

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updateBulkButtons();
}

function updateBulkButtons() {
    const checkboxes = document.querySelectorAll('.sign-checkbox:checked');
    const bulkActivateBtn = document.getElementById('bulkActivateBtn');
    const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');

    const hasSelected = checkboxes.length > 0;
    bulkActivateBtn.disabled = !hasSelected;
    bulkDeactivateBtn.disabled = !hasSelected;

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.sign-checkbox');
    const selectAll = document.getElementById('selectAll');
    const selectAllHeader = document.getElementById('selectAllHeader');

    if (checkboxes.length === allCheckboxes.length) {
        selectAll.checked = true;
        selectAllHeader.checked = true;
        selectAll.indeterminate = false;
        selectAllHeader.indeterminate = false;
    } else if (checkboxes.length > 0) {
        selectAll.checked = false;
        selectAllHeader.checked = false;
        selectAll.indeterminate = true;
        selectAllHeader.indeterminate = true;
    } else {
        selectAll.checked = false;
        selectAllHeader.checked = false;
        selectAll.indeterminate = false;
        selectAllHeader.indeterminate = false;
    }
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.sign-checkbox:checked');
    const signIds = Array.from(checkboxes).map(cb => cb.value);

    if (signIds.length === 0) {
        alert('{{ __("Please select at least one vital sign.") }}');
        return;
    }

    const actionText = action === 'activate' ? '{{ __("activate") }}' : '{{ __("deactivate") }}';
    const confirmMessage = `{{ __("Are you sure you want to") }} ${actionText} ${signIds.length} {{ __("vital sign(s)?") }}`;

    if (confirm(confirmMessage)) {
        // Create a form to submit the bulk action
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.custom-vital-signs.index") }}';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = action;
        form.appendChild(actionInput);

        // Add sign IDs
        signIds.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'sign_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        document.body.appendChild(form);
        form.submit();
    }
}

function loadTemplate(templateType) {
    const modal = new bootstrap.Modal(document.getElementById('createCustomSignModal'));
    modal.show();

    // Clear existing form
    document.getElementById('createCustomSignForm').reset();
    document.getElementById('optionsContainer').innerHTML = `
        <div class="input-group mb-2">
            <input type="text" class="form-control" name="option_keys[]" placeholder="{{ __('Value') }}">
            <input type="text" class="form-control" name="option_values[]" placeholder="{{ __('Label') }}">
            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    const templates = {
        oxygen: {
            name: 'Oxygen Saturation',
            unit: '%',
            type: 'number',
            min_value: 70,
            max_value: 100,
            normal_range: '95-100%'
        },
        pain: {
            name: 'Pain Level',
            unit: '/10',
            type: 'select',
            normal_range: '0-2/10',
            options: [
                ['0', 'No Pain (0)'],
                ['1', 'Mild (1)'],
                ['2', 'Mild (2)'],
                ['3', 'Moderate (3)'],
                ['4', 'Moderate (4)'],
                ['5', 'Moderate (5)'],
                ['6', 'Severe (6)'],
                ['7', 'Severe (7)'],
                ['8', 'Very Severe (8)'],
                ['9', 'Very Severe (9)'],
                ['10', 'Worst Possible (10)']
            ]
        },
        mobility: {
            name: 'Mobility Status',
            unit: '',
            type: 'select',
            normal_range: 'Independent',
            options: [
                ['independent', 'Independent'],
                ['assisted', 'Assisted'],
                ['wheelchair', 'Wheelchair'],
                ['bedbound', 'Bedbound']
            ]
        }
    };

    const template = templates[templateType];
    if (template) {
        document.getElementById('name').value = template.name;
        document.getElementById('unit').value = template.unit;
        document.getElementById('type').value = template.type;
        document.getElementById('normal_range').value = template.normal_range;

        if (template.min_value) document.getElementById('min_value').value = template.min_value;
        if (template.max_value) document.getElementById('max_value').value = template.max_value;

        toggleTypeOptions();

        if (template.options) {
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';

            template.options.forEach((option, index) => {
                const div = document.createElement('div');
                div.className = 'input-group mb-2';
                div.innerHTML = `
                    <input type="text" class="form-control" name="option_keys[]" value="${option[0]}" placeholder="{{ __('Value') }}">
                    <input type="text" class="form-control" name="option_values[]" value="${option[1]}" placeholder="{{ __('Label') }}">
                    <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }
    }
}

// Initialize form based on old input
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeOptions();

    @if(old('type') === 'select' && old('option_keys'))
        @foreach(old('option_keys', []) as $index => $key)
            @if($index > 0)
                addOption();
            @endif
        @endforeach

        const keys = @json(old('option_keys', []));
        const values = @json(old('option_values', []));
        const keyInputs = document.querySelectorAll('input[name="option_keys[]"]');
        const valueInputs = document.querySelectorAll('input[name="option_values[]"]');

        keys.forEach((key, index) => {
            if (keyInputs[index]) keyInputs[index].value = key;
        });

        values.forEach((value, index) => {
            if (valueInputs[index]) valueInputs[index].value = value;
        });
    @endif
});
</script>
@endsection
