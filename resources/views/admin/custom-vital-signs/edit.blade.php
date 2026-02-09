@extends('layouts.app')

@section('page-title', __('Edit Custom Vital Sign'))

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        {{ __('Edit Custom Vital Sign') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Modify the settings for') }}: <strong>{{ $customVitalSign->name }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.custom-vital-signs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog me-2"></i>
                        {{ __('Vital Sign Settings') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.custom-vital-signs.update', $customVitalSign) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">{{ __('Vital Sign Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $customVitalSign->name) }}" 
                                       placeholder="{{ __('e.g., Oxygen Saturation, Pain Level') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="unit" class="form-label">{{ __('Unit') }}</label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                       id="unit" name="unit" value="{{ old('unit', $customVitalSign->unit) }}" 
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
                                    <option value="number" {{ old('type', $customVitalSign->type) === 'number' ? 'selected' : '' }}>
                                        {{ __('Number') }} - {{ __('For numeric measurements') }}
                                    </option>
                                    <option value="select" {{ old('type', $customVitalSign->type) === 'select' ? 'selected' : '' }}>
                                        {{ __('Select') }} - {{ __('For predefined options') }}
                                    </option>
                                    <option value="text" {{ old('type', $customVitalSign->type) === 'text' ? 'selected' : '' }}>
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
                                       value="{{ old('min_value', $customVitalSign->min_value) }}" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label for="max_value" class="form-label">{{ __('Maximum Value') }}</label>
                                <input type="number" class="form-control" id="max_value" name="max_value" 
                                       value="{{ old('max_value', $customVitalSign->max_value) }}" step="0.01">
                            </div>
                        </div>

                        <!-- Select Type Options -->
                        <div id="selectOptions" class="mb-3" style="display: none;">
                            <label class="form-label">{{ __('Options') }} <span class="text-danger">*</span></label>
                            <div id="optionsContainer">
                                @if($customVitalSign->type === 'select' && $customVitalSign->options)
                                    @foreach($customVitalSign->options as $key => $value)
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="option_keys[]" value="{{ $key }}" placeholder="{{ __('Value') }}">
                                        <input type="text" class="form-control" name="option_values[]" value="{{ $value }}" placeholder="{{ __('Label') }}">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="option_keys[]" placeholder="{{ __('Value') }}">
                                        <input type="text" class="form-control" name="option_values[]" placeholder="{{ __('Label') }}">
                                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOption()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Option') }}
                            </button>
                        </div>

                        <!-- Normal Range and Settings -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="normal_range" class="form-label">{{ __('Normal Range') }}</label>
                                <input type="text" class="form-control @error('normal_range') is-invalid @enderror" 
                                       id="normal_range" name="normal_range" value="{{ old('normal_range', $customVitalSign->normal_range) }}" 
                                       placeholder="{{ __('e.g., 95-100%, 0-2/10') }}">
                                @error('normal_range')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="sort_order" class="form-label">{{ __('Display Order') }}</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $customVitalSign->sort_order) }}" 
                                       min="1">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Status') }}</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $customVitalSign->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ __('Active') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.custom-vital-signs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Vital Sign') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        {{ __('Preview') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="previewContainer">
                        <p class="text-muted">{{ __('Preview will appear here based on your settings') }}</p>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('Usage Statistics') }}
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $usageCount = \App\Models\PatientCheckup::whereNotNull('custom_vital_signs')
                                                               ->where('custom_vital_signs', 'like', '%"'.$customVitalSign->id.'"%')
                                                               ->count();
                    @endphp
                    <div class="text-center">
                        <h4 class="text-primary">{{ $usageCount }}</h4>
                        <small class="text-muted">{{ __('Times Used in Checkups') }}</small>
                    </div>
                    @if($usageCount > 0)
                        <hr>
                        <small class="text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            {{ __('This vital sign is being used in patient checkups. Changes may affect existing data.') }}
                        </small>
                    @endif
                </div>
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
    
    updatePreview();
}

function addOption() {
    const container = document.getElementById('optionsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="option_keys[]" placeholder="{{ __('Value') }}" onchange="updatePreview()">
        <input type="text" class="form-control" name="option_values[]" placeholder="{{ __('Label') }}" onchange="updatePreview()">
        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeOption(button) {
    button.closest('.input-group').remove();
    updatePreview();
}

function updatePreview() {
    const name = document.getElementById('name').value;
    const unit = document.getElementById('unit').value;
    const type = document.getElementById('type').value;
    const normalRange = document.getElementById('normal_range').value;
    const previewContainer = document.getElementById('previewContainer');
    
    if (!name || !type) {
        previewContainer.innerHTML = '<p class="text-muted">{{ __("Preview will appear here based on your settings") }}</p>';
        return;
    }
    
    let preview = `<div class="mb-3">
        <label class="form-label"><strong>${name}</strong>`;
    
    if (unit) {
        preview += ` (${unit})`;
    }
    
    if (normalRange) {
        preview += `<br><small class="text-muted">Normal: ${normalRange}</small>`;
    }
    
    preview += '</label>';
    
    if (type === 'number') {
        const minValue = document.getElementById('min_value').value;
        const maxValue = document.getElementById('max_value').value;
        preview += `<input type="number" class="form-control" placeholder="Enter value"`;
        if (minValue) preview += ` min="${minValue}"`;
        if (maxValue) preview += ` max="${maxValue}"`;
        preview += '>';
    } else if (type === 'select') {
        preview += '<select class="form-select"><option value="">Select...</option>';
        const keys = document.querySelectorAll('input[name="option_keys[]"]');
        const values = document.querySelectorAll('input[name="option_values[]"]');
        
        for (let i = 0; i < keys.length; i++) {
            if (keys[i].value && values[i].value) {
                preview += `<option value="${keys[i].value}">${values[i].value}</option>`;
            }
        }
        preview += '</select>';
    } else if (type === 'text') {
        preview += '<input type="text" class="form-control" placeholder="Enter text">';
    }
    
    preview += '</div>';
    
    previewContainer.innerHTML = preview;
}

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeOptions();
    updatePreview();
    
    // Add event listeners for real-time preview
    document.getElementById('name').addEventListener('input', updatePreview);
    document.getElementById('unit').addEventListener('input', updatePreview);
    document.getElementById('normal_range').addEventListener('input', updatePreview);
    document.getElementById('min_value').addEventListener('input', updatePreview);
    document.getElementById('max_value').addEventListener('input', updatePreview);
});
</script>
@endsection
