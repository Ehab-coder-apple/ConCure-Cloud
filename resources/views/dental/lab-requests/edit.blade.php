@extends('layouts.app')

@section('title', __('Edit Dental Lab Request'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Edit Lab Request') }} #{{ $labRequest->request_number }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Update dental lab request details') }}</p>
                </div>
                <div>
                    <a href="{{ route('dental.lab-requests.show', $labRequest) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Details') }}
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
                    <form action="{{ route('dental.lab-requests.update', $labRequest) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Patient & Treatment Selection (Phase 35) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="patient_type" id="patient_type_registered" value="registered" {{ old('patient_type', $labRequest->external_patient_name ? 'external' : 'registered') == 'registered' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="patient_type_registered">{{ __('Registered Patient') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="patient_type" id="patient_type_external" value="external" {{ old('patient_type', $labRequest->external_patient_name ? 'external' : 'registered') == 'external' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="patient_type_external">{{ __('External Patient') }}</label>
                                    </div>
                                </div>
                                <div id="registered_patient_wrapper">
                                    <div class="position-relative">
                                        <input type="text" class="form-control @error('patient_id') is-invalid @enderror"
                                               id="patient_search" name="patient_search"
                                               placeholder="{{ __('Search by name, ID, phone, email...') }}"
                                               value="{{ old('patient_search', $labRequest->patient->full_name ?? '') }}"
                                               autocomplete="off">
                                        <input type="hidden" id="patient_id" name="patient_id"
                                               value="{{ old('patient_id', $labRequest->patient_id) }}">
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
                                    <input type="text" class="form-control @error('external_patient_name') is-invalid @enderror" id="external_patient_name" name="external_patient_name" value="{{ old('external_patient_name', $labRequest->external_patient_name) }}" placeholder="{{ __('Enter external patient name') }}">
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
                                        <option value="{{ $treatment->id }}" {{ old('dental_treatment_id', $labRequest->dental_treatment_id) == $treatment->id ? 'selected' : '' }}>
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
                                        <input class="form-check-input" type="radio" name="doctor_type" id="doctor_type_clinic" value="clinic" {{ old('doctor_type', $labRequest->external_doctor_name ? 'external' : 'clinic') == 'clinic' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="doctor_type_clinic">{{ __('Clinic Doctor') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="doctor_type" id="doctor_type_external" value="external" {{ old('doctor_type', $labRequest->external_doctor_name ? 'external' : 'clinic') == 'external' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="doctor_type_external">{{ __('External Doctor') }}</label>
                                    </div>
                                </div>
                                <div id="clinic_doctor_wrapper">
                                    <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
                                        <option value="">{{ __('Select Doctor') }}</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id', $labRequest->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="external_doctor_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('external_doctor_name') is-invalid @enderror" id="external_doctor_name" name="external_doctor_name" value="{{ old('external_doctor_name', $labRequest->external_doctor_name) }}" placeholder="{{ __('Enter external doctor name') }}">
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
	                                    <option value="{{ $technician->id }}" {{ old('assigned_technician_id', $labRequest->assigned_technician_id) == $technician->id ? 'selected' : '' }}>
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
		                                    <option value="{{ $designer->id }}" {{ old('assigned_designer_id', $labRequest->assigned_designer_id) == $designer->id ? 'selected' : '' }}>
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
                                        <option value="{{ $lab->id }}" {{ old('external_lab_id', $labRequest->external_lab_id) == $lab->id ? 'selected' : '' }}>
                                            {{ $lab->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('external_lab_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

	                        <!-- Work Details -->
	                        <div class="row mb-3">
	                            <div class="col-md-4">
                                <label class="form-label">{{ __('Work Type') }} <span class="text-danger">*</span></label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="work_type_mode" id="work_type_mode_list" value="list" {{ old('work_type_mode', $labRequest->custom_work_type ? 'custom' : 'list') == 'list' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_type_mode_list">{{ __('From List') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="work_type_mode" id="work_type_mode_custom" value="custom" {{ old('work_type_mode', $labRequest->custom_work_type ? 'custom' : 'list') == 'custom' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_type_mode_custom">{{ __('Custom') }}</label>
                                    </div>
                                </div>
                                <div id="work_type_list_wrapper">
                                    <select class="form-select @error('work_type') is-invalid @enderror" id="work_type" name="work_type">
                                        <option value="">{{ __('Select Work Type') }}</option>
                                        @foreach(\App\Models\DentalLabRequest::WORK_TYPES as $key => $label)
                                            <option value="{{ $key }}" {{ old('work_type', $labRequest->work_type) == $key ? 'selected' : '' }}>
                                                {{ __($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="work_type_custom_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('custom_work_type') is-invalid @enderror" id="custom_work_type" name="custom_work_type" value="{{ old('custom_work_type', $labRequest->custom_work_type) }}" placeholder="{{ __('Enter custom work type') }}">
                                    @error('custom_work_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
	                            <div class="col-md-4">
                                <label for="tooth_number" class="form-label">{{ __('Tooth Number(s)') }}</label>
                                <input type="text" class="form-control @error('tooth_number') is-invalid @enderror" 
                                       id="tooth_number" name="tooth_number" value="{{ old('tooth_number', $labRequest->tooth_number) }}"
                                       placeholder="{{ __('e.g., 11, 12-14, 21,22') }}">
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
	                            <div class="col-md-4">
	                                <label for="quantity" class="form-label">{{ __('Total Quantity') }}</label>
	                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
	                                       id="quantity" name="quantity" value="{{ old('quantity', $labRequest->quantity) }}"
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
                                       id="shade" name="shade" value="{{ old('shade', $labRequest->shade) }}"
                                       placeholder="{{ __('e.g., A2, B1') }}">
                                @error('shade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Material') }}</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="material_mode" id="material_mode_list" value="list" {{ old('material_mode', $labRequest->custom_material ? 'custom' : 'list') == 'list' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="material_mode_list">{{ __('From List') }}</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="material_mode" id="material_mode_custom" value="custom" {{ old('material_mode', $labRequest->custom_material ? 'custom' : 'list') == 'custom' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="material_mode_custom">{{ __('Custom') }}</label>
                                    </div>
                                </div>
                                <div id="material_list_wrapper">
                                    <select class="form-select @error('material') is-invalid @enderror" id="material" name="material">
                                        <option value="">{{ __('Select Material') }}</option>
                                        @foreach(\App\Models\DentalLabRequest::MATERIALS as $key => $label)
                                            <option value="{{ $key }}" {{ old('material', $labRequest->material) == $key ? 'selected' : '' }}>
                                                {{ __($label) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('material')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div id="material_custom_wrapper" style="display: none;">
                                    <input type="text" class="form-control @error('custom_material') is-invalid @enderror" id="custom_material" name="custom_material" value="{{ old('custom_material', $labRequest->custom_material) }}" placeholder="{{ __('Enter custom material') }}">
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
                                      placeholder="{{ __('Detailed specifications for the lab work...') }}">{{ old('specifications', $labRequest->specifications) }}</textarea>
                            @error('specifications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="special_instructions" class="form-label">{{ __('Special Instructions') }}</label>
                            <textarea class="form-control @error('special_instructions') is-invalid @enderror"
                                      id="special_instructions" name="special_instructions" rows="2"
                                      placeholder="{{ __('Any special instructions or notes...') }}">{{ old('special_instructions', $labRequest->special_instructions) }}</textarea>
                            @error('special_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status & Priority -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach(\App\Models\DentalLabRequest::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $labRequest->status) == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                    @foreach(\App\Models\DentalLabRequest::PRIORITIES as $key => $label)
                                        <option value="{{ $key }}" {{ old('priority', $labRequest->priority) == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="received_by_id" class="form-label">{{ __('Received By') }}</label>
                                <select class="form-select @error('received_by_id') is-invalid @enderror" id="received_by_id" name="received_by_id">
                                    <option value="">{{ __('Not received yet') }}</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('received_by_id', $labRequest->received_by_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('received_by_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="requested_date" class="form-label">{{ __('Requested Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('requested_date') is-invalid @enderror"
                                       id="requested_date" name="requested_date"
                                       value="{{ old('requested_date', $labRequest->requested_date->format('Y-m-d')) }}" required>
                                @error('requested_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date"
                                       value="{{ old('due_date', $labRequest->due_date?->format('Y-m-d')) }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="received_date" class="form-label">{{ __('Received Date') }}</label>
                                <input type="date" class="form-control @error('received_date') is-invalid @enderror"
                                       id="received_date" name="received_date"
                                       value="{{ old('received_date', $labRequest->received_date?->format('Y-m-d')) }}">
                                @error('received_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Communication -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="communication_method" class="form-label">{{ __('Communication Method') }}</label>
                                <select class="form-select @error('communication_method') is-invalid @enderror" id="communication_method" name="communication_method">
                                    <option value="">{{ __('Select Method') }}</option>
                                    @foreach(\App\Models\DentalLabRequest::COMMUNICATION_METHODS as $key => $label)
                                        <option value="{{ $key }}" {{ old('communication_method', $labRequest->communication_method) == $key ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('communication_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="sent_at" class="form-label">{{ __('Sent At') }}</label>
                                <input type="datetime-local" class="form-control @error('sent_at') is-invalid @enderror"
                                       id="sent_at" name="sent_at"
                                       value="{{ old('sent_at', $labRequest->sent_at?->format('Y-m-d\TH:i')) }}">
                                @error('sent_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="communication_notes" class="form-label">{{ __('Communication Notes') }}</label>
                            <textarea class="form-control @error('communication_notes') is-invalid @enderror"
                                      id="communication_notes" name="communication_notes" rows="2"
                                      placeholder="{{ __('Notes about communication with the lab...') }}">{{ old('communication_notes', $labRequest->communication_notes) }}</textarea>
                            @error('communication_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Cost Information -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="estimated_cost" class="form-label">{{ __('Estimated Cost') }}</label>
                                <input type="number" step="0.01" class="form-control @error('estimated_cost') is-invalid @enderror"
                                       id="estimated_cost" name="estimated_cost" value="{{ old('estimated_cost', $labRequest->estimated_cost) }}">
                                @error('estimated_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="actual_cost" class="form-label">{{ __('Actual Cost') }}</label>
                                <input type="number" step="0.01" class="form-control @error('actual_cost') is-invalid @enderror"
                                       id="actual_cost" name="actual_cost" value="{{ old('actual_cost', $labRequest->actual_cost) }}">
                                @error('actual_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                <input type="text" class="form-control @error('currency') is-invalid @enderror"
                                       id="currency" name="currency" value="{{ old('currency', $labRequest->currency ?? 'IQD') }}" maxlength="3">
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="prescription_file" class="form-label">{{ __('Prescription File') }}</label>
                                @if($labRequest->prescription_file_path)
                                    <div class="mb-1">
                                        <small class="text-muted">
                                            {{ __('Current:') }}
                                            <a href="{{ Storage::url($labRequest->prescription_file_path) }}" target="_blank">{{ __('View') }}</a>
                                        </small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('prescription_file') is-invalid @enderror"
                                       id="prescription_file" name="prescription_file" accept=".pdf,.jpg,.jpeg,.png">
                                @error('prescription_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('PDF, JPG, PNG (Max 5MB)') }}</small>
                            </div>
                            <div class="col-md-4">
                                <label for="impression_file" class="form-label">{{ __('Impression File') }}</label>
                                @if($labRequest->impression_file_path)
                                    <div class="mb-1">
                                        <small class="text-muted">
                                            {{ __('Current:') }}
                                            <a href="{{ Storage::url($labRequest->impression_file_path) }}" target="_blank">{{ __('View') }}</a>
                                        </small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('impression_file') is-invalid @enderror"
                                       id="impression_file" name="impression_file" accept=".stl,.obj,.pdf,.jpg,.jpeg,.png">
                                @error('impression_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('STL, OBJ, PDF, JPG, PNG (Max 10MB)') }}</small>
                            </div>
                            <div class="col-md-4">
                                <label for="result_file" class="form-label">{{ __('Result File') }}</label>
                                @if($labRequest->result_file_path)
                                    <div class="mb-1">
                                        <small class="text-muted">
                                            {{ __('Current:') }}
                                            <a href="{{ Storage::url($labRequest->result_file_path) }}" target="_blank">{{ __('View') }}</a>
                                        </small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('result_file') is-invalid @enderror"
                                       id="result_file" name="result_file" accept=".pdf,.jpg,.jpeg,.png">
                                @error('result_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('PDF, JPG, PNG (Max 5MB)') }}</small>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="2"
                                      placeholder="{{ __('Any additional notes...') }}">{{ old('notes', $labRequest->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="quality_notes" class="form-label">{{ __('Quality Notes') }}</label>
                            <textarea class="form-control @error('quality_notes') is-invalid @enderror"
                                      id="quality_notes" name="quality_notes" rows="2"
                                      placeholder="{{ __('Notes about quality of received work...') }}">{{ old('quality_notes', $labRequest->quality_notes) }}</textarea>
                            @error('quality_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Lab Request') }}
                            </button>
                            <a href="{{ route('dental.lab-requests.show', $labRequest) }}" class="btn btn-secondary">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Request Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ __('Request Number') }}:</strong><br>
                        {{ $labRequest->request_number }}
                    </p>
                    <p class="mb-2">
                        <strong>{{ __('Created') }}:</strong><br>
                        {{ $labRequest->created_at->format('M d, Y H:i') }}
                    </p>
                    <p class="mb-0">
                        <strong>{{ __('Last Updated') }}:</strong><br>
                        {{ $labRequest->updated_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        {{ __('Tips') }}
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>{{ __('Update status as work progresses') }}</li>
                        <li>{{ __('Set received date when work is completed') }}</li>
                        <li>{{ __('Upload result file when available') }}</li>
                        <li>{{ __('Add quality notes for future reference') }}</li>
                        <li>{{ __('Track actual cost for accurate billing') }}</li>
                    </ul>
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

