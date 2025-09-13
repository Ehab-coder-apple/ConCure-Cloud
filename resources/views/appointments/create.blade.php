@extends('layouts.app')

@section('title', __('Schedule Appointment'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-plus text-primary me-2"></i>
                        {{ __('Schedule Appointment') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Schedule a new appointment for a patient') }}</p>
                </div>
                <div>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Appointments') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ __('Appointment Details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('appointments.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Patient Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                                <option value="">{{ __('Select patient...') }}</option>
                                                @foreach($patients as $patient)
                                                    <option value="{{ $patient->id }}" {{ (old('patient_id') == $patient->id || request('patient_id') == $patient->id) ? 'selected' : '' }}>
                                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-success" onclick="openQuickAddPatient()" title="{{ __('Add New Patient') }}">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        </div>
                                        @error('patient_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Doctor Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="doctor_id" class="form-label">{{ __('Doctor') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required>
                                            <option value="">{{ __('Select doctor...') }}</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Appointment Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_date" class="form-label">{{ __('Appointment Date') }} <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                               id="appointment_date" name="appointment_date" 
                                               value="{{ old('appointment_date', date('Y-m-d')) }}" 
                                               min="{{ date('Y-m-d') }}" required>
                                        @error('appointment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Appointment Time -->
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_time" class="form-label">{{ __('Appointment Time') }} <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                               id="appointment_time" name="appointment_time" 
                                               value="{{ old('appointment_time', '09:00') }}" required>
                                        @error('appointment_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Appointment Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_type" class="form-label">{{ __('Appointment Type') }}</label>
                                        <select class="form-select @error('appointment_type') is-invalid @enderror" id="appointment_type" name="appointment_type">
                                            <option value="">{{ __('Select type...') }}</option>
                                            <option value="consultation" {{ old('appointment_type') == 'consultation' ? 'selected' : '' }}>{{ __('Consultation') }}</option>
                                            <option value="follow_up" {{ old('appointment_type') == 'follow_up' ? 'selected' : '' }}>{{ __('Follow Up') }}</option>
                                            <option value="checkup" {{ old('appointment_type') == 'checkup' ? 'selected' : '' }}>{{ __('Checkup') }}</option>
                                            <option value="procedure" {{ old('appointment_type') == 'procedure' ? 'selected' : '' }}>{{ __('Procedure') }}</option>
                                            <option value="other" {{ old('appointment_type') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                        </select>
                                        @error('appointment_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Duration -->
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label">{{ __('Duration (minutes)') }}</label>
                                        <select class="form-select @error('duration') is-invalid @enderror" id="duration" name="duration">
                                            <option value="15" {{ old('duration') == '15' ? 'selected' : '' }}>15 {{ __('minutes') }}</option>
                                            <option value="30" {{ old('duration', '30') == '30' ? 'selected' : '' }}>30 {{ __('minutes') }}</option>
                                            <option value="45" {{ old('duration') == '45' ? 'selected' : '' }}>45 {{ __('minutes') }}</option>
                                            <option value="60" {{ old('duration') == '60' ? 'selected' : '' }}>1 {{ __('hour') }}</option>
                                            <option value="90" {{ old('duration') == '90' ? 'selected' : '' }}>1.5 {{ __('hours') }}</option>
                                            <option value="120" {{ old('duration') == '120' ? 'selected' : '' }}>2 {{ __('hours') }}</option>
                                        </select>
                                        @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-12 mb-3">
                                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="3" 
                                                  placeholder="{{ __('Additional notes or reason for appointment...') }}">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            {{ __('Schedule Appointment') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Quick Info Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Appointment Guidelines') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-primary">{{ __('Scheduling Tips') }}</h6>
                                <ul class="list-unstyled small">
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Select appropriate appointment duration') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Add relevant notes for context') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Choose the correct appointment type') }}</li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <h6 class="text-primary">{{ __('Appointment Types') }}</h6>
                                <ul class="list-unstyled small">
                                    <li><strong>{{ __('Consultation') }}:</strong> {{ __('Initial patient visit') }}</li>
                                    <li><strong>{{ __('Follow Up') }}:</strong> {{ __('Return visit for ongoing care') }}</li>
                                    <li><strong>{{ __('Checkup') }}:</strong> {{ __('Routine health examination') }}</li>
                                    <li><strong>{{ __('Procedure') }}:</strong> {{ __('Medical procedure or treatment') }}</li>
                                </ul>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <small>{{ __('Appointments can be modified or cancelled after scheduling if needed.') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Schedule Preview -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-day me-2"></i>
                                {{ __('Today\'s Schedule') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">{{ __('Quick view of today\'s appointments') }}</p>
                            <div class="text-center">
                                <div class="text-primary h4 mb-0" id="todayCount">-</div>
                                <small class="text-muted">{{ __('appointments today') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Patient Modal -->
<div class="modal fade" id="quickAddPatientModal" tabindex="-1" aria-labelledby="quickAddPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickAddPatientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Quick Add New Patient') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddPatientForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="quick_last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_phone" class="form-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="quick_phone" name="phone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="quick_email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                            <input type="date" class="form-control" id="quick_date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_gender" class="form-label">{{ __('Gender') }}</label>
                            <select class="form-select" id="quick_gender" name="gender">
                                <option value="">{{ __('Select gender...') }}</option>
                                <option value="male">{{ __('Male') }}</option>
                                <option value="female">{{ __('Female') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="quick_address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control" id="quick_address" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div id="quickAddPatientError" class="alert alert-danger d-none"></div>
                    <div id="quickAddPatientSuccess" class="alert alert-success d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success" id="quickAddPatientBtn">
                        <i class="fas fa-user-plus me-1"></i>
                        {{ __('Add Patient') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-populate patient info when selected
document.getElementById('patient_id').addEventListener('change', function() {
    // You can add AJAX call here to get patient details if needed
});

// Auto-populate doctor info when selected
document.getElementById('doctor_id').addEventListener('change', function() {
    // You can add AJAX call here to check doctor availability if needed
});

// Load today's appointment count
document.addEventListener('DOMContentLoaded', function() {
    // You can add AJAX call here to get today's appointment count
    document.getElementById('todayCount').textContent = '0';
});

// Quick Add Patient functionality
function openQuickAddPatient() {
    const modal = new bootstrap.Modal(document.getElementById('quickAddPatientModal'));
    modal.show();
}

// Handle quick add patient form submission
document.getElementById('quickAddPatientForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('quickAddPatientBtn');
    const errorDiv = document.getElementById('quickAddPatientError');
    const successDiv = document.getElementById('quickAddPatientSuccess');

    // Reset alerts
    errorDiv.classList.add('d-none');
    successDiv.classList.add('d-none');

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Adding...") }}';

    // Prepare form data
    const formData = new FormData(this);

    // Send AJAX request
    fetch('{{ route("patients.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new patient to dropdown
            const patientSelect = document.getElementById('patient_id');
            const newOption = document.createElement('option');
            newOption.value = data.patient.id;
            newOption.textContent = `${data.patient.first_name} ${data.patient.last_name} (${data.patient.patient_id})`;
            newOption.selected = true;
            patientSelect.appendChild(newOption);

            // Show success message
            successDiv.textContent = '{{ __("Patient added successfully!") }}';
            successDiv.classList.remove('d-none');

            // Close modal after 1 second
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('quickAddPatientModal'));
                modal.hide();

                // Reset form
                document.getElementById('quickAddPatientForm').reset();
                successDiv.classList.add('d-none');
            }, 1000);
        } else {
            // Show error message
            errorDiv.textContent = data.message || '{{ __("Error adding patient. Please try again.") }}';
            errorDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorDiv.textContent = '{{ __("Error adding patient. Please try again.") }}';
        errorDiv.classList.remove('d-none');
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus me-1"></i>{{ __("Add Patient") }}';
    });
});
</script>
@endsection
