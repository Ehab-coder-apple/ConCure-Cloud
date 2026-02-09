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

                        <!-- Patient & Treatment Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->full_name }} ({{ $patient->patient_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                            <div class="col-md-6">
                                <label for="doctor_id" class="form-label">{{ __('Requesting Doctor') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required>
                                    <option value="">{{ __('Select Doctor') }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('doctor_id', auth()->user()->role === 'doctor' ? auth()->id() : '') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="work_type" class="form-label">{{ __('Work Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('work_type') is-invalid @enderror" id="work_type" name="work_type" required>
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
                            <div class="col-md-6">
                                <label for="tooth_number" class="form-label">{{ __('Tooth Number(s)') }}</label>
                                <input type="text" class="form-control @error('tooth_number') is-invalid @enderror" 
                                       id="tooth_number" name="tooth_number" value="{{ old('tooth_number') }}"
                                       placeholder="{{ __('e.g., 11, 12-14, 21,22') }}">
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('Enter tooth numbers separated by commas') }}</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shade" class="form-label">{{ __('Shade') }}</label>
                                <input type="text" class="form-control @error('shade') is-invalid @enderror" 
                                       id="shade" name="shade" value="{{ old('shade') }}"
                                       placeholder="{{ __('e.g., A2, B1') }}">
                                @error('shade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="material" class="form-label">{{ __('Material') }}</label>
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
                        <li>{{ __('Patient') }}</li>
                        <li>{{ __('Requesting Doctor') }}</li>
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
@endsection

