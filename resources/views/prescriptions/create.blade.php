@extends('layouts.app')

@section('title', __('Create Prescription'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                        {{ __('New Prescription') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Create a simple prescription for your patient') }}</p>
                </div>
                <div>
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>
                </div>
            </div>

            <!-- Simple Prescription Form -->
            <div class="card">
                <div class="card-body">
                    <!-- Error Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger border-0 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                <div>
                                    <strong>{{ __('Error') }}</strong>
                                    <p class="mb-0">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-0 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-exclamation-triangle text-danger me-2 mt-1"></i>
                                <div>
                                    <strong>{{ __('Please fix the following errors:') }}</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Helpful Info -->
                    <div class="alert alert-info border-0 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <div>
                                <strong>{{ __('Quick Prescription') }}</strong>
                                <p class="mb-0 small">{{ __('Fill in the patient details and add medicines. Use the quick-add buttons for common medications.') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('prescriptions.store') }}" method="POST" onsubmit="return handleFormSubmit(event);">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">
                                    <i class="fas fa-user-injured text-primary me-1"></i>
                                    {{ __('Patient') }} <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select a patient...') }}</option>
                                    @if(isset($patients) && $patients->count() > 0)
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>{{ __('No patients available - please add patients first') }}</option>
                                    @endif
                                </select>
                                @if(isset($patients) && $patients->count() == 0)
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ __('No patients found for your clinic.') }}
                                        <a href="{{ route('patients.create') }}" class="text-decoration-none">{{ __('Add a patient first') }}</a>
                                    </div>
                                @endif
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prescribed_date" class="form-label">
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    {{ __('Date') }}
                                </label>
                                <input type="date" class="form-control @error('prescribed_date') is-invalid @enderror"
                                       id="prescribed_date" name="prescribed_date"
                                       value="{{ old('prescribed_date', date('Y-m-d')) }}" required>
                                @error('prescribed_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="mb-4">
                            <label for="diagnosis" class="form-label">
                                <i class="fas fa-stethoscope text-primary me-1"></i>
                                {{ __('Diagnosis') }} <small class="text-muted">({{ __('Optional') }})</small>
                            </label>
                            <input type="text" class="form-control @error('diagnosis') is-invalid @enderror"
                                   id="diagnosis" name="diagnosis" value="{{ old('diagnosis') }}"
                                   placeholder="{{ __('e.g., Common cold, Hypertension, Diabetes...') }}">
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Simple Medications Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">
                                    <i class="fas fa-pills text-primary me-1"></i>
                                    {{ __('Medications') }}
                                </label>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addMedication()">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Add Medicine') }}
                                </button>
                            </div>

                            <!-- Quick Add Common Medicines -->
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Quick add common medicines:') }}</small>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="addQuickMedicine('Paracetamol', '500mg', '1 tablet', 'Twice daily', '5 days')">
                                        {{ __('Paracetamol 500mg') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="addQuickMedicine('Ibuprofen', '400mg', '1 tablet', 'Twice daily', '3 days')">
                                        {{ __('Ibuprofen 400mg') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="addQuickMedicine('Amoxicillin', '500mg', '1 capsule', '3 times daily', '7 days')">
                                        {{ __('Amoxicillin 500mg') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 mb-2" onclick="addQuickMedicine('Cough Syrup', '5ml', '1 spoon', 'Twice daily', '5 days')">
                                        {{ __('Cough Syrup') }}
                                    </button>
                                </div>
                            </div>

                            <div id="medicationsContainer">
                                <!-- Default empty state -->
                                <div class="text-center py-3 text-muted border rounded" id="emptyState">
                                    <i class="fas fa-pills fa-2x mb-2"></i>
                                    <p class="mb-0">{{ __('Click "Add Medicine" or use quick add buttons above') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note text-primary me-1"></i>
                                {{ __('Notes') }} <small class="text-muted">({{ __('Optional') }})</small>
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="{{ __('Additional notes or instructions for the patient...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="button" class="btn btn-info" onclick="debugForm()">
                                <i class="fas fa-bug me-1"></i>Debug
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-file-prescription me-1" id="submitIcon"></i>
                                <span id="submitText">{{ __('Create Prescription') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Simple Medication Template (Hidden) -->
<div id="medicationTemplate" style="display: none;">
    <div class="medication-item border rounded p-3 mb-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-primary">
                <i class="fas fa-pills me-1"></i>
                {{ __('Medicine') }} <span class="medication-number">1</span>
            </h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeMedication(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">{{ __('Medicine Name') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="medications[0][name]"
                       placeholder="{{ __('e.g., Paracetamol, Amoxicillin...') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Strength') }}</label>
                <input type="text" class="form-control" name="medications[0][strength]"
                       placeholder="{{ __('e.g., 500mg') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Dosage') }}</label>
                <input type="text" class="form-control" name="medications[0][dosage]"
                       placeholder="{{ __('e.g., 1 tablet') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Frequency') }}</label>
                <select class="form-select" name="medications[0][frequency]">
                    <option value="">{{ __('How often?') }}</option>
                    <option value="Once daily">{{ __('Once daily') }}</option>
                    <option value="Twice daily">{{ __('Twice daily') }}</option>
                    <option value="3 times daily">{{ __('3 times daily') }}</option>
                    <option value="4 times daily">{{ __('4 times daily') }}</option>
                    <option value="As needed">{{ __('As needed') }}</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('Duration') }}</label>
                <input type="text" class="form-control" name="medications[0][duration]"
                       placeholder="{{ __('e.g., 7 days') }}">
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('Instructions') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                <input type="text" class="form-control" name="medications[0][instructions]"
                       placeholder="{{ __('e.g., Take with food, After meals...') }}">
            </div>
        </div>
    </div>
</div>

<script>
let medicationCount = 0;

function addMedication(name = '', strength = '', dosage = '', frequency = '', duration = '', instructions = '') {
    const container = document.getElementById('medicationsContainer');
    const template = document.getElementById('medicationTemplate');
    const emptyState = document.getElementById('emptyState');

    // Hide empty state on first medication
    if (medicationCount === 0 && emptyState) {
        emptyState.style.display = 'none';
    }

    // Clone the template
    const newMedication = template.cloneNode(true);
    newMedication.style.display = 'block';
    newMedication.id = '';

    // Update the medication number
    medicationCount++;
    newMedication.querySelector('.medication-number').textContent = medicationCount;

    // Update input names with current index
    const inputs = newMedication.querySelectorAll('input, select');
    inputs.forEach(input => {
        const inputName = input.getAttribute('name');
        if (inputName) {
            input.setAttribute('name', inputName.replace('[0]', `[${medicationCount - 1}]`));
        }
    });

    // Pre-fill values if provided
    if (name) newMedication.querySelector('input[name*="[name]"]').value = name;
    if (strength) newMedication.querySelector('input[name*="[strength]"]').value = strength;
    if (dosage) newMedication.querySelector('input[name*="[dosage]"]').value = dosage;
    if (frequency) newMedication.querySelector('select[name*="[frequency]"]').value = frequency;
    if (duration) newMedication.querySelector('input[name*="[duration]"]').value = duration;
    if (instructions) newMedication.querySelector('input[name*="[instructions]"]').value = instructions;

    container.appendChild(newMedication);

    // Focus on the medicine name input if empty
    if (!name) {
        newMedication.querySelector('input[name*="[name]"]').focus();
    }
}

function addQuickMedicine(name, strength, dosage, frequency, duration) {
    addMedication(name, strength, dosage, frequency, duration, 'Take as directed');
}

function debugForm() {
    const form = document.querySelector('form');
    const formData = new FormData(form);

    console.log('=== FORM DEBUG ===');
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);

    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ':', value);
    }

    // Check medications specifically
    const medicationInputs = document.querySelectorAll('input[name*="medications"]');
    console.log('Medication inputs found:', medicationInputs.length);

    medicationInputs.forEach((input, index) => {
        console.log(`Medication input ${index}:`, input.name, '=', input.value);
    });

    alert('Check browser console for form debug information');
}

function handleFormSubmit(event) {
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('Form submit event triggered');

    const form = event.target;
    const formData = new FormData(form);

    console.log('Form action:', form.action);
    console.log('Form method:', form.method);

    // Log all form data
    console.log('Form data being submitted:');
    for (let [key, value] of formData.entries()) {
        console.log(`  ${key}: ${value}`);
    }

    // Check required fields
    const patientId = formData.get('patient_id');
    const prescribedDate = formData.get('prescribed_date');

    console.log('Validation check:');
    console.log('  Patient ID:', patientId ? 'Present' : 'MISSING');
    console.log('  Prescribed Date:', prescribedDate ? 'Present' : 'MISSING');

    if (!patientId) {
        console.error('ERROR: Patient ID is missing!');
        alert('Please select a patient');
        return false;
    }

    if (!prescribedDate) {
        console.error('ERROR: Prescribed date is missing!');
        alert('Please select a date');
        return false;
    }

    console.log('✅ Form validation passed, submitting...');

    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');

    if (submitBtn && submitIcon && submitText) {
        submitBtn.disabled = true;
        submitIcon.className = 'fas fa-spinner fa-spin me-1';
        submitText.textContent = 'Creating Prescription...';
    }

    // Add a small delay to see the console logs
    setTimeout(() => {
        console.log('Form should be submitting now...');
    }, 100);

    return true; // Allow form submission
}

function removeMedication(button) {
    const medicationItem = button.closest('.medication-item');
    medicationItem.remove();

    medicationCount--;

    // Show empty state if no medications left
    const container = document.getElementById('medicationsContainer');
    if (medicationCount === 0) {
        container.innerHTML = `
            <div class="text-center py-3 text-muted border rounded" id="emptyState">
                <i class="fas fa-pills fa-2x mb-2"></i>
                <p class="mb-0">{{ __('Click "Add Medicine" or use quick add buttons above') }}</p>
            </div>
        `;
    } else {
        // Renumber remaining medications
        const remainingMedications = container.querySelectorAll('.medication-item');
        remainingMedications.forEach((item, index) => {
            item.querySelector('.medication-number').textContent = index + 1;

            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
            });
        });
    }
}

// Auto-add first medication when form loads
document.addEventListener('DOMContentLoaded', function() {
    // Uncomment the line below if you want to start with one medication field
    // addMedication();
});
</script>
@endsection
