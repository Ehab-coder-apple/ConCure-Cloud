@extends('layouts.app')

@section('title', __('Edit Simple Prescription'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('Edit Prescription') }} - {{ $prescription->prescription_number }}
                    </h5>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ __('Please fix the following errors:') }}</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('simple-prescriptions.update', $prescription->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Patient and Date -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">
                                    <i class="fas fa-user-injured text-primary me-1"></i>
                                    {{ __('Patient') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" 
                                        id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select a patient...') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" 
                                                {{ (old('patient_id', $prescription->patient_id) == $patient->id) ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prescribed_date" class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    {{ __('Date') }} <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('prescribed_date') is-invalid @enderror"
                                       id="prescribed_date" name="prescribed_date"
                                       value="{{ old('prescribed_date', $prescription->prescribed_date->format('Y-m-d')) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="mb-4">
                            <label for="diagnosis" class="form-label">
                                <i class="fas fa-stethoscope text-primary me-1"></i>
                                {{ __('Diagnosis') }}
                            </label>
                            <textarea class="form-control @error('diagnosis') is-invalid @enderror" 
                                      id="diagnosis" name="diagnosis" rows="3"
                                      placeholder="{{ __('Enter diagnosis...') }}">{{ old('diagnosis', $prescription->diagnosis) }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-primary me-1"></i>
                                {{ __('Notes') }}
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="2"
                                      placeholder="{{ __('Additional notes...') }}">{{ old('notes', $prescription->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Medicines -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-pills text-primary me-1"></i>
                                {{ __('Medicines') }}
                            </label>
                            
                            <div id="medicines-container">
                                <!-- Existing medicines will be loaded here -->
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMedicine()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Medicine') }}
                            </button>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('simple-prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Prescription') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Medicine Template (Hidden) -->
<div id="medicine-template" style="display: none;">
    <div class="card mb-3 medicine-item">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ __('Medicine') }} <span class="medicine-number">1</span></h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMedicine(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">{{ __('Medicine Name') }}</label>
                    <div class="medicine-select-container">
                        <select class="form-select medicine-select" name="medicines[0][name]" onchange="handleMedicineSelect(this)">
                            <option value="">{{ __('Select medicine...') }}</option>
                            @foreach($medicines as $medicine)
                                <option value="{{ $medicine->name }}"
                                        data-dosage="{{ $medicine->dosage }}"
                                        data-form="{{ $medicine->form }}">
                                    {{ $medicine->name }}
                                    @if($medicine->dosage) - {{ $medicine->dosage }} @endif
                                    @if($medicine->form) ({{ ucfirst($medicine->form) }}) @endif
                                </option>
                            @endforeach
                            <option value="custom" class="text-primary">{{ __('+ Add New Medicine') }}</option>
                        </select>
                        <input type="text" class="form-control mt-2 custom-medicine-input"
                               placeholder="{{ __('Enter new medicine name...') }}"
                               style="display: none;"
                               onblur="handleCustomMedicine(this)">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Dosage') }}</label>
                        <input type="text" class="form-control" name="medicines[0][dosage]" placeholder="{{ __('e.g., 1 tablet') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Frequency') }}</label>
                        <input type="text" class="form-control" name="medicines[0][frequency]" placeholder="{{ __('e.g., Twice daily') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Duration') }}</label>
                        <input type="text" class="form-control" name="medicines[0][duration]" placeholder="{{ __('e.g., 7 days') }}">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Instructions') }}</label>
                    <textarea class="form-control" name="medicines[0][instructions]" rows="2" placeholder="{{ __('Special instructions...') }}"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let medicineCount = 0;

function addMedicine(name = '', dosage = '', frequency = '', duration = '', instructions = '') {
    const container = document.getElementById('medicines-container');
    const template = document.getElementById('medicine-template');
    const newMedicine = template.cloneNode(true);
    
    newMedicine.style.display = 'block';
    newMedicine.id = '';
    
    // Update medicine number
    medicineCount++;
    newMedicine.querySelector('.medicine-number').textContent = medicineCount;
    
    // Update input names and values
    const inputs = newMedicine.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        const inputName = input.getAttribute('name');
        if (inputName) {
            input.setAttribute('name', inputName.replace('[0]', `[${medicineCount - 1}]`));
        }

        // Set values for existing medicines
        if (inputName && inputName.includes('[name]')) {
            if (input.tagName === 'SELECT') {
                // Try to find the medicine in the dropdown
                const option = Array.from(input.options).find(opt => opt.value === name);
                if (option) {
                    input.value = name;
                } else {
                    // Create a custom option for existing medicine not in inventory
                    const customOption = document.createElement('option');
                    customOption.value = name;
                    customOption.textContent = name + ' (Custom)';
                    customOption.selected = true;
                    input.appendChild(customOption);
                }
            } else {
                input.value = name;
            }
        }
        if (inputName && inputName.includes('[dosage]')) input.value = dosage;
        if (inputName && inputName.includes('[frequency]')) input.value = frequency;
        if (inputName && inputName.includes('[duration]')) input.value = duration;
        if (inputName && inputName.includes('[instructions]')) input.value = instructions;
    });
    
    container.appendChild(newMedicine);
}

function removeMedicine(button) {
    button.closest('.medicine-item').remove();
}

// Medicine selection functions
function handleMedicineSelect(selectElement) {
    const customInput = selectElement.parentElement.querySelector('.custom-medicine-input');
    const medicineItem = selectElement.closest('.medicine-item');

    if (selectElement.value === 'custom') {
        // Show custom input
        selectElement.style.display = 'none';
        customInput.style.display = 'block';
        customInput.focus();
    } else if (selectElement.value) {
        // Auto-fill dosage if available
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const dosage = selectedOption.getAttribute('data-dosage');
        const form = selectedOption.getAttribute('data-form');

        if (dosage && medicineItem) {
            const dosageInput = medicineItem.querySelector('input[name*="[dosage]"]');
            if (dosageInput && !dosageInput.value) {
                dosageInput.value = '1 ' + (form || 'tablet');
            }
        }
    }
}

function handleCustomMedicine(inputElement) {
    const selectElement = inputElement.parentElement.querySelector('.medicine-select');
    const customValue = inputElement.value.trim();

    if (customValue) {
        // Set the select value to the new medicine with 'new:' prefix
        selectElement.value = 'new:' + customValue;
        selectElement.style.display = 'block';
        inputElement.style.display = 'none';

        // Create a temporary option to show the custom medicine
        const tempOption = document.createElement('option');
        tempOption.value = 'new:' + customValue;
        tempOption.textContent = customValue + ' (New)';
        tempOption.selected = true;
        selectElement.appendChild(tempOption);
    } else {
        // Cancel custom input
        selectElement.style.display = 'block';
        inputElement.style.display = 'none';
        selectElement.value = '';
    }
}

// Load existing medicines
document.addEventListener('DOMContentLoaded', function() {
    @if($prescription->medicines->count() > 0)
        @foreach($prescription->medicines as $medicine)
            addMedicine(
                '{{ addslashes($medicine->medicine_name) }}',
                '{{ addslashes($medicine->dosage ?? '') }}',
                '{{ addslashes($medicine->frequency ?? '') }}',
                '{{ addslashes($medicine->duration ?? '') }}',
                '{{ addslashes($medicine->instructions ?? '') }}'
            );
        @endforeach
    @else
        // Add one empty medicine if no existing medicines
        addMedicine();
    @endif
});
</script>
@endsection
