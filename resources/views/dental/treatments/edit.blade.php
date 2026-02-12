@extends('layouts.app')

@section('title', __('Edit Treatment Plan'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        {{ __('Edit Treatment Plan') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Treatment #') }}: <strong>{{ $dentalTreatment->treatment_number }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/treatments/{$dentalTreatment->id}") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Treatment') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ url("/dental/treatments/{$dentalTreatment->id}") }}" method="POST" id="treatment-form">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient & Procedure Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-user-injured me-2"></i>
                            {{ __('Patient & Procedure Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Patient (Read-only) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Patient') }}</label>
                                <input type="text" class="form-control" value="{{ $dentalTreatment->patient->full_name }} ({{ $dentalTreatment->patient->patient_id }})" disabled>
                            </div>

                            <!-- Treatment Number (Read-only) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Treatment Number') }}</label>
                                <input type="text" class="form-control" value="{{ $dentalTreatment->treatment_number }}" disabled>
                            </div>

                            <!-- Procedure Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Procedure Name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="procedure_name" class="form-control" value="{{ old('procedure_name', $dentalTreatment->procedure_name) }}" required>
                                @error('procedure_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Procedure Code -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Procedure Code') }}</label>
                                <input type="text" name="procedure_code" class="form-control" value="{{ old('procedure_code', $dentalTreatment->procedure_code) }}" placeholder="e.g., D0120">
                                @error('procedure_code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tooth Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Tooth Number (FDI)') }}</label>
                                <input type="text" name="tooth_number" class="form-control" value="{{ old('tooth_number', $dentalTreatment->tooth_number) }}" placeholder="e.g., 11, 21, 36">
                                <small class="text-muted">{{ __('FDI notation: 11-18, 21-28, 31-38, 41-48') }}</small>
                                @error('tooth_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Surfaces Affected -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Surfaces Affected') }}</label>
                                <div class="d-flex gap-3 flex-wrap">
                                    @php
                                        $surfaces = old('surfaces_affected', $dentalTreatment->surfaces_affected ?? []);
                                    @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="O" id="surface_o" {{ in_array('O', $surfaces) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="surface_o">{{ __('Occlusal') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="M" id="surface_m" {{ in_array('M', $surfaces) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="surface_m">{{ __('Mesial') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="D" id="surface_d" {{ in_array('D', $surfaces) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="surface_d">{{ __('Distal') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="B" id="surface_b" {{ in_array('B', $surfaces) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="surface_b">{{ __('Buccal') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="L" id="surface_l" {{ in_array('L', $surfaces) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="surface_l">{{ __('Lingual') }}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Diagnosis -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label">{{ __('Diagnosis') }}</label>
                                <input type="text" name="diagnosis" class="form-control" value="{{ old('diagnosis', $dentalTreatment->diagnosis) }}" placeholder="{{ __('Enter diagnosis') }}">
                                @error('diagnosis')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- ICD-10 Code -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('ICD-10 Code') }}</label>
                                <input type="text" name="icd10_code" class="form-control" value="{{ old('icd10_code', $dentalTreatment->icd10_code) }}" placeholder="e.g., K02.9">
                                @error('icd10_code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="{{ __('Detailed description of the treatment...') }}">{{ old('description', $dentalTreatment->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cost & Payment -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>
                            {{ __('Cost & Payment Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Estimated Cost -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Estimated Cost') }}</label>
                                <input type="number" name="estimated_cost" class="form-control" value="{{ old('estimated_cost', $dentalTreatment->estimated_cost) }}" step="0.01" min="0">
                                @error('estimated_cost')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actual Cost -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Actual Cost') }}</label>
                                <input type="number" name="actual_cost" class="form-control" value="{{ old('actual_cost', $dentalTreatment->actual_cost) }}" step="0.01" min="0">
                                @error('actual_cost')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" class="form-select">
                                    <option value="USD" {{ old('currency', $dentalTreatment->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="EUR" {{ old('currency', $dentalTreatment->currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="GBP" {{ old('currency', $dentalTreatment->currency) === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                    <option value="IQD" {{ old('currency', $dentalTreatment->currency) === 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                                    <option value="EGP" {{ old('currency', $dentalTreatment->currency) === 'EGP' ? 'selected' : '' }}>EGP (£E)</option>
                                </select>
                                @error('currency')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Status -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Payment Status') }} <span class="text-danger">*</span></label>
                                <select name="payment_status" class="form-select" required>
                                    <option value="unpaid" {{ old('payment_status', $dentalTreatment->payment_status) === 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                                    <option value="partial" {{ old('payment_status', $dentalTreatment->payment_status) === 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                                    <option value="paid" {{ old('payment_status', $dentalTreatment->payment_status) === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                </select>
                                @error('payment_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Paid Amount -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Paid Amount') }}</label>
                                <input type="number" name="paid_amount" class="form-control" value="{{ old('paid_amount', $dentalTreatment->amount_paid) }}" step="0.01" min="0">
                                @error('paid_amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estimated Duration -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ __('Estimated Duration (minutes)') }}</label>
                                <input type="number" name="estimated_duration_minutes" class="form-control" value="{{ old('estimated_duration_minutes', $dentalTreatment->estimated_duration_minutes) }}" min="1">
                                @error('estimated_duration_minutes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            {{ __('Additional Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Notes -->
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Treatment Notes') }}</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('Additional notes about the treatment...') }}">{{ old('notes', $dentalTreatment->notes) }}</textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Post-Treatment Notes -->
                            <div class="col-12">
                                <label class="form-label">{{ __('Post-Treatment Notes') }}</label>
                                <textarea name="post_treatment_notes" class="form-control" rows="3" placeholder="{{ __('Notes after treatment completion...') }}">{{ old('post_treatment_notes', $dentalTreatment->post_treatment_notes) }}</textarea>
                                @error('post_treatment_notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status & Priority -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Status & Priority') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="planned" {{ old('status', $dentalTreatment->status) === 'planned' ? 'selected' : '' }}>{{ __('Planned') }}</option>
                                <option value="in_progress" {{ old('status', $dentalTreatment->status) === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="completed" {{ old('status', $dentalTreatment->status) === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ old('status', $dentalTreatment->status) === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low" {{ old('priority', $dentalTreatment->priority) === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                                <option value="medium" {{ old('priority', $dentalTreatment->priority) === 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                <option value="high" {{ old('priority', $dentalTreatment->priority) === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                <option value="urgent" {{ old('priority', $dentalTreatment->priority) === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                            </select>
                            @error('priority')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Severity -->
                        <div>
                            <label class="form-label">{{ __('Severity') }}</label>
                            <select name="severity" class="form-select">
                                <option value="">{{ __('Not Specified') }}</option>
                                <option value="mild" {{ old('severity', $dentalTreatment->severity) === 'mild' ? 'selected' : '' }}>{{ __('Mild') }}</option>
                                <option value="moderate" {{ old('severity', $dentalTreatment->severity) === 'moderate' ? 'selected' : '' }}>{{ __('Moderate') }}</option>
                                <option value="severe" {{ old('severity', $dentalTreatment->severity) === 'severe' ? 'selected' : '' }}>{{ __('Severe') }}</option>
                            </select>
                            @error('severity')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('Scheduling') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Scheduled Date -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Scheduled Date & Time') }}</label>
                            <input type="datetime-local" name="scheduled_date" class="form-control"
                                   value="{{ old('scheduled_date', $dentalTreatment->scheduled_date ? $dentalTreatment->scheduled_date->format('Y-m-d\TH:i') : '') }}">
                            @error('scheduled_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Assigned Doctor -->
                        <div>
                            <label class="form-label">{{ __('Assigned Doctor') }}</label>
                            <select name="assigned_doctor_id" class="form-select">
                                <option value="">{{ __('Not Assigned') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('assigned_doctor_id', $dentalTreatment->assigned_doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_doctor_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-1"></i>
                            {{ __('Update Treatment Plan') }}
                        </button>
                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}") }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

