@extends('layouts.app')

@section('title', __('Edit Prescription'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                        {{ __('Edit Prescription') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('prescriptions.index') }}">{{ __('Prescriptions') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('prescriptions.show', $prescription->id ?? 1) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Details') }}
                    </a>
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                </div>
            </div>

            <form action="{{ route('prescriptions.update', $prescription->id ?? 1) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Patient Information -->
                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-user-injured me-2"></i>
                                    {{ __('Patient Information') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">{{ __('Select Patient') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                        <option value="">{{ __('Choose a patient...') }}</option>
                                        @if(isset($patients))
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $prescription->patient_id ?? '1') == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="1" {{ old('patient_id', $prescription->patient_id ?? '1') == '1' ? 'selected' : '' }}>Demo Patient (P000001)</option>
                                            <option value="2" {{ old('patient_id', $prescription->patient_id ?? '1') == '2' ? 'selected' : '' }}>John Smith (P000002)</option>
                                            <option value="3" {{ old('patient_id', $prescription->patient_id ?? '1') == '3' ? 'selected' : '' }}>Sarah Ahmed (P000003)</option>
                                        @endif
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="text-center mb-3">
                                    <div class="avatar bg-primary text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                        DP
                                    </div>
                                    <h6 class="mb-1">Demo Patient</h6>
                                    <small class="text-muted">P000001</small>
                                </div>
                                
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <strong>{{ __('Age') }}:</strong> 35
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('Gender') }}:</strong> {{ __('Male') }}
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('Weight') }}:</strong> 75kg
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ __('Blood Type') }}:</strong> O+
                                    </div>
                                    <div class="col-12">
                                        <strong>{{ __('Allergies') }}:</strong>
                                        <span class="text-danger">{{ __('Penicillin') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prescription Details -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-prescription me-2"></i>
                                    {{ __('Prescription Details') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="diagnosis" class="form-label">{{ __('Primary Diagnosis') }}</label>
                                        <input type="text" class="form-control @error('diagnosis') is-invalid @enderror" 
                                               id="diagnosis" name="diagnosis" 
                                               value="{{ old('diagnosis', $prescription->diagnosis ?? 'Hypertension and Type 2 Diabetes') }}" 
                                               placeholder="{{ __('e.g., Hypertension, Diabetes...') }}">
                                        @error('diagnosis')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="prescription_date" class="form-label">{{ __('Prescription Date') }}</label>
                                        <input type="date" class="form-control @error('prescription_date') is-invalid @enderror" 
                                               id="prescription_date" name="prescription_date" 
                                               value="{{ old('prescription_date', $prescription->prescription_date ?? date('Y-m-d')) }}">
                                        @error('prescription_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">{{ __('Status') }}</label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                            <option value="active" {{ old('status', $prescription->status ?? 'active') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                            <option value="completed" {{ old('status', $prescription->status ?? 'active') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                            <option value="cancelled" {{ old('status', $prescription->status ?? 'active') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="symptoms" class="form-label">{{ __('Symptoms') }}</label>
                                        <textarea class="form-control @error('symptoms') is-invalid @enderror" 
                                                  id="symptoms" name="symptoms" rows="2" 
                                                  placeholder="{{ __('Patient reported symptoms...') }}">{{ old('symptoms', $prescription->symptoms ?? 'Elevated blood pressure, frequent urination, increased thirst') }}</textarea>
                                        @error('symptoms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions and Notes -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note me-2"></i>
                                    {{ __('Instructions & Notes') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="instructions" class="form-label">{{ __('General Instructions') }}</label>
                                        <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                                  id="instructions" name="instructions" rows="3" 
                                                  placeholder="{{ __('General instructions for the patient...') }}">{{ old('instructions', $prescription->instructions ?? 'Take medications as prescribed. Monitor blood pressure daily. Follow up in 2 weeks.') }}</textarea>
                                        @error('instructions')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12">
                                        <label for="notes" class="form-label">{{ __('Doctor Notes') }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="2" 
                                                  placeholder="{{ __('Private notes for medical records...') }}">{{ old('notes', $prescription->notes ?? '') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Medications -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-pills me-2"></i>
                                    {{ __('Current Medications') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Medication') }}</th>
                                                <th>{{ __('Strength') }}</th>
                                                <th>{{ __('Dosage') }}</th>
                                                <th>{{ __('Frequency') }}</th>
                                                <th>{{ __('Duration') }}</th>
                                                <th>{{ __('Instructions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Lisinopril</strong></td>
                                                <td>10mg</td>
                                                <td>1 tablet</td>
                                                <td>Once daily</td>
                                                <td>30 days</td>
                                                <td>Take in the morning</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Metformin</strong></td>
                                                <td>500mg</td>
                                                <td>1 tablet</td>
                                                <td>Twice daily</td>
                                                <td>30 days</td>
                                                <td>Take with meals</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Aspirin</strong></td>
                                                <td>81mg</td>
                                                <td>1 tablet</td>
                                                <td>Once daily</td>
                                                <td>30 days</td>
                                                <td>Take with food</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('To modify medications, please create a new prescription or use the medicine management feature.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('prescriptions.show', $prescription->id ?? 1) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Update Prescription') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
