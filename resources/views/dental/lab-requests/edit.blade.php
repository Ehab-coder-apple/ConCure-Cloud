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

                        <!-- Patient & Treatment Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', $labRequest->patient_id) == $patient->id ? 'selected' : '' }}>
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
                            <div class="col-md-6">
                                <label for="doctor_id" class="form-label">{{ __('Requesting Doctor') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id" required>
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="work_type" class="form-label">{{ __('Work Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('work_type') is-invalid @enderror" id="work_type" name="work_type" required>
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
                            <div class="col-md-6">
                                <label for="tooth_number" class="form-label">{{ __('Tooth Number(s)') }}</label>
                                <input type="text" class="form-control @error('tooth_number') is-invalid @enderror" 
                                       id="tooth_number" name="tooth_number" value="{{ old('tooth_number', $labRequest->tooth_number) }}"
                                       placeholder="{{ __('e.g., 11, 12-14, 21,22') }}">
                                @error('tooth_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shade" class="form-label">{{ __('Shade') }}</label>
                                <input type="text" class="form-control @error('shade') is-invalid @enderror" 
                                       id="shade" name="shade" value="{{ old('shade', $labRequest->shade) }}"
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
                                        <option value="{{ $key }}" {{ old('material', $labRequest->material) == $key ? 'selected' : '' }}>
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
    // Initialize Select2 for patient dropdown with search enabled
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("Select Patient") }}',
        allowClear: true,
        width: '100%',
        dropdownParent: $(document.body),
        language: {
            noResults: function() {
                return '{{ __("No patients found") }}';
            },
            searching: function() {
                return '{{ __("Searching...") }}';
            }
        }
    });
});
</script>
@endpush

@endsection

