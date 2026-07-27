@extends('layouts.app')

@section('title', __('Create Dental Lab Request'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Create Dental Lab Request') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Send dental work to external laboratory') }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.lab-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('dental.lab-requests.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Patient & Treatment Selection (Phase 35) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="patient_type" id="patient_type_registered" value="registered" {{ old('patient_type', old('external_patient_name') ? 'external' : 'registered') == 'registered' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="patient_type_registered">{{ __('Registered Patient') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="patient_type" id="patient_type_external" value="external" {{ old('patient_type') == 'external' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="patient_type_external">{{ __('External Patient') }}</label>
                                    </div>
                                </div>
                                <div id="registered_patient_wrapper">
                                    <div class="position-relative">
                                        <input type="text" class="form-control @error('patient_id') is-invalid @enderror"
                                               id="patient_search" name="patient_search"
                                               placeholder="{{ __('Search by name, ID, phone, email...') }}"
                                               autocomplete="off">
                                        <input type="hidden" id="patient_id" name="patient_id">
                                        <div id="patient_results" class="position-absolute w-100 bg-white border rounded mt-1"
                                             style="display: none; max-height: 300px; overflow-y: auto; z-index: 1000; top: 100%;">
                                        </div>
                                    </div>
                                    @error('patient_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">{{ __('Type to search for a patient') }}</small>
                                </div>
                                <div id="external_patient_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('external_patient_name') is-invalid @enderror" id="external_patient_name" name="external_patient_name" value="{{ old('external_patient_name') }}" placeholder="{{ __('Enter external patient name') }}">
                                    @error('external_patient_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="dental_treatment_id" class="form-label">{{ __('Dental Treatment') }}</label>
                                <select class="form-select @error('dental_treatment_id') is-invalid @enderror" id="dental_treatment_id" name="dental_treatment_id">
                                    <option value="">{{ __('Select Treatment (Optional)') }}</option>
                                    @foreach($treatments as $treatment)
                                        <option value="{{ $treatment->id }}" {{ old('dental_treatment_id', request('dental_treatment_id')) == $treatment->id ? 'selected' : '' }}>
                                            {{ $treatment->treatment_number }} - {{ $treatment->procedure_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('dental_treatment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Doctor & Lab Selection -->
                        <div class="row mb-3">
		                        <div class="col-md-3">
                                <label class="form-label">{{ __('Requesting Doctor') }} <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="doctor_type" id="doctor_type_clinic" value="clinic" {{ old('doctor_type', old('external_doctor_name') ? 'external' : 'clinic') == 'clinic' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="doctor_type_clinic">{{ __('Clinic Doctor') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="doctor_type" id="doctor_type_external" value="external" {{ old('doctor_type') == 'external' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="doctor_type_external">{{ __('External Doctor') }}</label>
                                    </div>
                                </div>
                                <div id="clinic_doctor_wrapper">
                                    <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
                                        <option value="">{{ __('Select Doctor') }}</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id', in_array(auth()->user()->role, ['doctor', 'dental_dept']) ? auth()->id() : '') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="external_doctor_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('external_doctor_name') is-invalid @enderror" id="external_doctor_name" name="external_doctor_name" value="{{ old('external_doctor_name') }}" placeholder="{{ __('Enter external doctor name') }}">
                                    @error('external_doctor_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
		                        <div class="col-md-3">
	                            <label for="assigned_technician_id" class="form-label">{{ __('Assigned Technician') }}</label>
	                            <select class="form-select @error('assigned_technician_id') is-invalid @enderror" id="assigned_technician_id" name="assigned_technician_id">
	                                <option value="">{{ __('Select Technician (Optional)') }}</option>
	                                @foreach($technicians as $technician)
	                                    <option value="{{ $technician->id }}" {{ old('assigned_technician_id') == $technician->id ? 'selected' : '' }}>
	                                        {{ $technician->full_name }}
	                                    </option>
	                                @endforeach
	                            </select>
	                            @error('assigned_technician_id')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                            <small class="text-muted">{{ __('Optional') }}</small>
	                        </div>
		                        <div class="col-md-3">
		                            <label for="assigned_designer_id" class="form-label">{{ __('Assigned CAD/CAM Designer') }}</label>
		                            <select class="form-select @error('assigned_designer_id') is-invalid @enderror" id="assigned_designer_id" name="assigned_designer_id">
		                                <option value="">{{ __('Select Designer (Optional)') }}</option>
		                                @foreach($designers as $designer)
		                                    <option value="{{ $designer->id }}" {{ old('assigned_designer_id') == $designer->id ? 'selected' : '' }}>
		                                        {{ $designer->full_name }}
		                                    </option>
		                                @endforeach
		                            </select>
		                            @error('assigned_designer_id')
		                                <div class="invalid-feedback">{{ $message }}</div>
		                            @enderror
		                            <small class="text-muted">{{ __('Optional') }}</small>
		                        </div>
		                        <div class="col-md-3">
                                <label for="external_lab_id" class="form-label">{{ __('Dental Lab') }}</label>
                                <select class="form-select @error('external_lab_id') is-invalid @enderror" id="external_lab_id" name="external_lab_id">
                                    <option value="">{{ __('Select Lab (Optional)') }}</option>
                                    @foreach($dentalLabs as $lab)
                                        <option value="{{ $lab->id }}" {{ old('external_lab_id') == $lab->id ? 'selected' : '' }}>
                                            {{ $lab->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('external_lab_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Can be assigned later') }}</small>
                            </div>
                        </div>

                        <!-- Work Details -->
	                        <div class="row mb-3">
	                            <div class="col-md-4">
                                <label class="form-label">{{ __('Work Type') }} <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="work_type_mode" id="work_type_mode_list" value="list" {{ old('work_type_mode', old('custom_work_type') ? 'custom' : 'list') == 'list' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_type_mode_list">{{ __('From List') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="work_type_mode" id="work_type_mode_custom" value="custom" {{ old('work_type_mode', old('custom_work_type') ? 'custom' : 'list') == 'custom' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_type_mode_custom">{{ __('Custom') }}</label>
                                    </div>
                                </div>
                                <div id="work_type_list_wrapper">
                                    <select class="form-select @error('work_type') is-invalid @enderror" id="work_type" name="work_type">
                                        <option value="">{{ __('Select Work Type') }}</option>
                                        @foreach(\App\Models\DentalLabRequest::WORK_TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('work_type') == $key ? 'selected' : '' }}>
                                                {{ __($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="work_type_custom_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('custom_work_type') is-invalid @enderror" id="custom_work_type" name="custom_work_type" value="{{ old('custom_work_type') }}" placeholder="{{ __('Enter custom work type') }}">
                                    @error('custom_work_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
	                            <div class="col-md-4">
                                <label for="tooth_number" class="form-label">{{ __('Tooth Number(s)') }}</label>
                                <input type="text" class="form-control @error('tooth_number') is-invalid @enderror" 
                                       id="tooth_number" name="tooth_number" value="{{ old('tooth_number') }}"
                                       placeholder="{{ __('e.g., 11, 12-14, 21,22') }}">
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Enter tooth numbers separated by commas') }}</small>
                            </div>
	                            <div class="col-md-4">
	                                <label for="quantity" class="form-label">{{ __('Total Quantity') }}</label>
	                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
	                                       id="quantity" name="quantity" value="{{ old('quantity') }}"
	                                       min="1" step="1" placeholder="{{ __('e.g., 2') }}">
	                                @error('quantity')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                                <small class="text-muted">{{ __('Optional') }}</small>
	                            </div>
                        </div>

                        <div class="row mb-3">
	                            <div class="col-md-6">
	                                <label for="shade" class="form-label">{{ __('Color') }}</label>
                                <input type="text" class="form-control @error('shade') is-invalid @enderror" 
                                       id="shade" name="shade" value="{{ old('shade') }}"
                                       placeholder="{{ __('e.g., A2, B1') }}">
                                @error('shade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Material') }}</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="material_mode" id="material_mode_list" value="list" {{ old('material_mode', old('custom_material') ? 'custom' : 'list') == 'list' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="material_mode_list">{{ __('From List') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="material_mode" id="material_mode_custom" value="custom" {{ old('material_mode', old('custom_material') ? 'custom' : 'list') == 'custom' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="material_mode_custom">{{ __('Custom') }}</label>
                                    </div>
                                </div>
                                <div id="material_list_wrapper">
                                    <select class="form-select @error('material') is-invalid @enderror" id="material" name="material">
                                        <option value="">{{ __('Select Material') }}</option>
                                        @foreach(\App\Models\DentalLabRequest::MATERIALS as $key => $label)
                                            <option value="{{ $key }}" {{ old('material') == $key ? 'selected' : '' }}>
                                                {{ __($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('material')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="material_custom_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('custom_material') is-invalid @enderror" id="custom_material" name="custom_material" value="{{ old('custom_material') }}" placeholder="{{ __('Enter custom material') }}">
                                    @error('custom_material')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Specifications & Instructions -->
                        <div class="mb-3">
                            <label for="specifications" class="form-label">{{ __('Specifications') }}</label>
                            <textarea class="form-control @error('specifications') is-invalid @enderror"
                                      id="specifications" name="specifications" rows="3"
                                      placeholder="{{ __('Detailed specifications for the lab work...') }}">{{ old('specifications') }}</textarea>
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">{{ __('Special Instructions') }}</label>
                            <textarea class="form-control @error('special_instructions') is-invalid @enderror"
                                      id="special_instructions" name="special_instructions" rows="2"
                                      placeholder="{{ __('Any special instructions or notes...') }}">{{ old('special_instructions') }}</textarea>
                            @error('special_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dates & Priority -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="requested_date" class="form-label">{{ __('Requested Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('requested_date') is-invalid @enderror"
                                       id="requested_date" name="requested_date"
                                       value="{{ old('requested_date', date('Y-m-d')) }}" required>
                                @error('requested_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date" value="{{ old('due_date') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    @foreach(\App\Models\DentalLabRequest::PRIORITIES as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', 'normal') == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="prescription_file" class="form-label">{{ __('Prescription File') }}</label>
                                <input type="file" class="form-control @error('prescription_file') is-invalid @enderror"
                                       id="prescription_file" name="prescription_file" accept=".pdf,.jpg,.jpeg,.png">
                                @error('prescription_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('PDF, JPG, PNG (Max 5MB)') }}</small>
                            </div>
                            <div class="col-md-6">
                                <label for="impression_file" class="form-label">{{ __('Impression File') }}</label>
                                <input type="file" class="form-control @error('impression_file') is-invalid @enderror"
                                       id="impression_file" name="impression_file" accept=".stl,.obj,.pdf,.jpg,.jpeg,.png">
                                @error('impression_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('STL, OBJ, PDF, JPG, PNG (Max 10MB)') }}</small>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="2"
                                      placeholder="{{ __('Any additional notes...') }}">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Lab Request') }}
                            </button>
                            <a href="{{ route('dental.lab-requests.index') }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ __('Required Fields:') }}</strong></p>
                    <ul class="small mb-3">
                        <li>{{ __('Patient (registered or external)') }}</li>
                        <li>{{ __('Requesting Doctor (clinic or external)') }}</li>
                        <li>{{ __('Work Type') }}</li>
                        <li>{{ __('Requested Date') }}</li>
                        <li>{{ __('Priority') }}</li>
                    </ul>

                    <p class="mb-2"><strong>{{ __('Optional Fields:') }}</strong></p>
                    <ul class="small mb-3">
                        <li>{{ __('Dental Treatment (can link to existing treatment)') }}</li>
                        <li>{{ __('Dental Lab (can be assigned later)') }}</li>
                        <li>{{ __('Due Date (recommended)') }}</li>
                    </ul>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-lightbulb me-1"></i>
                        <small>{{ __('A unique request number will be automatically generated when you create the request.') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const patientSearch = document.getElementById('patient_search');
    const patientIdInput = document.getElementById('patient_id');
    const patientResults = document.getElementById('patient_results');
    let searchTimeout;

    // Handle patient search input
    patientSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim();

        // Clear patient_id when user modifies search
        patientIdInput.value = '';

        // Show results container only if there's input
        if (searchTerm.length < 1) {
            patientResults.style.display = 'none';
            return;
        }

        // Debounce the search
        searchTimeout = setTimeout(() => {
            performPatientSearch(searchTerm);
        }, 300);
    });

    // Perform AJAX search
    function performPatientSearch(searchTerm) {
        fetch(`{{ route('patients.api') }}?search=${encodeURIComponent(searchTerm)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            displayPatientResults(data.data || []);
        })
        .catch(error => {
            console.error('Search error:', error);
            patientResults.innerHTML = '<div class="p-2 text-danger">{{ __("Error searching patients") }}</div>';
            patientResults.style.display = 'block';
        });
    }

    // Display search results
    function displayPatientResults(patients) {
        if (patients.length === 0) {
            patientResults.innerHTML = '<div class="p-2 text-muted">{{ __("No patients found") }}</div>';
            patientResults.style.display = 'block';
            return;
        }

        let html = '';
        patients.forEach(patient => {
            const fullName = `${patient.first_name} ${patient.last_name}`;
            const patientId = patient.patient_id || '';
            html += `
                <div class="p-2 border-bottom cursor-pointer patient-result"
                     data-id="${patient.id}"
                     data-name="${fullName}"
                     style="cursor: pointer; padding: 8px 12px;">
                    <strong>${fullName}</strong>
                    ${patientId ? `<br><small class="text-muted">ID: ${patientId}</small>` : ''}
                </div>
            `;
        });

        patientResults.innerHTML = html;
        patientResults.style.display = 'block';

        // Add click handlers to results
        document.querySelectorAll('.patient-result').forEach(item => {
            item.addEventListener('click', function() {
                const patientId = this.getAttribute('data-id');
                const patientName = this.getAttribute('data-name');

                patientIdInput.value = patientId;
                patientSearch.value = patientName;
                patientResults.style.display = 'none';
            });
        });
    }

    // Hide results when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('#patient_search') && !event.target.closest('#patient_results')) {
            patientResults.style.display = 'none';
        }
    });

    // Show results on focus if there's a search term
    patientSearch.addEventListener('focus', function() {
        if (this.value.trim().length > 0 && patientResults.innerHTML) {
            patientResults.style.display = 'block';
        }
    });

    // Patient type toggle (registered vs external)
    const registeredPatientWrapper = document.getElementById('registered_patient_wrapper');
    const externalPatientWrapper = document.getElementById('external_patient_wrapper');
    const patientTypeRadios = document.querySelectorAll('input[name="patient_type"]');
    const externalPatientInput = document.getElementById('external_patient_name');

    function togglePatientType() {
        const selected = document.querySelector('input[name="patient_type"]:checked').value;
        if (selected === 'registered') {
            registeredPatientWrapper.style.display = '';
            externalPatientWrapper.style.display = 'none';
            externalPatientInput.value = '';
        } else {
            registeredPatientWrapper.style.display = 'none';
            externalPatientWrapper.style.display = '';
            patientIdInput.value = '';
            patientSearch.value = '';
        }
    }

    patientTypeRadios.forEach(radio => radio.addEventListener('change', togglePatientType));
    togglePatientType(); // Initialize on page load

    // Doctor type toggle (clinic vs external)
    const clinicDoctorWrapper = document.getElementById('clinic_doctor_wrapper');
    const externalDoctorWrapper = document.getElementById('external_doctor_wrapper');
    const doctorTypeRadios = document.querySelectorAll('input[name="doctor_type"]');
    const doctorSelect = document.getElementById('doctor_id');
    const externalDoctorInput = document.getElementById('external_doctor_name');

    function toggleDoctorType() {
        const selected = document.querySelector('input[name="doctor_type"]:checked').value;
        if (selected === 'clinic') {
            clinicDoctorWrapper.style.display = '';
            externalDoctorWrapper.style.display = 'none';
            externalDoctorInput.value = '';
        } else {
            clinicDoctorWrapper.style.display = 'none';
            externalDoctorWrapper.style.display = '';
            doctorSelect.value = '';
        }
    }

    doctorTypeRadios.forEach(radio => radio.addEventListener('change', toggleDoctorType));
    toggleDoctorType(); // Initialize on page load

    // Work type mode toggle (list vs custom)
    const workTypeListWrapper = document.getElementById('work_type_list_wrapper');
    const workTypeCustomWrapper = document.getElementById('work_type_custom_wrapper');
    const workTypeModeRadios = document.querySelectorAll('input[name="work_type_mode"]');
    const workTypeSelect = document.getElementById('work_type');
    const customWorkTypeInput = document.getElementById('custom_work_type');

    function toggleWorkTypeMode() {
        const selected = document.querySelector('input[name="work_type_mode"]:checked').value;
        if (selected === 'list') {
            workTypeListWrapper.style.display = '';
            workTypeCustomWrapper.style.display = 'none';
            customWorkTypeInput.value = '';
        } else {
            workTypeListWrapper.style.display = 'none';
            workTypeCustomWrapper.style.display = '';
            workTypeSelect.value = '';
        }
    }

    workTypeModeRadios.forEach(radio => radio.addEventListener('change', toggleWorkTypeMode));
    toggleWorkTypeMode();

    // Material mode toggle (list vs custom)
    const materialListWrapper = document.getElementById('material_list_wrapper');
    const materialCustomWrapper = document.getElementById('material_custom_wrapper');
    const materialModeRadios = document.querySelectorAll('input[name="material_mode"]');
    const materialSelect = document.getElementById('material');
    const customMaterialInput = document.getElementById('custom_material');

    function toggleMaterialMode() {
        const selected = document.querySelector('input[name="material_mode"]:checked').value;
        if (selected === 'list') {
            materialListWrapper.style.display = '';
            materialCustomWrapper.style.display = 'none';
            customMaterialInput.value = '';
        } else {
            materialListWrapper.style.display = 'none';
            materialCustomWrapper.style.display = '';
            materialSelect.value = '';
        }
    }

    materialModeRadios.forEach(radio => radio.addEventListener('change', toggleMaterialMode));
    toggleMaterialMode();
});
</script>
@endpush

@endsection

