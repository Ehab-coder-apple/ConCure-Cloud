@extends('layouts.app')

@section('page-title', __('Edit Checkup') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        {{ __('Edit Checkup') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Patient:') }} {{ $patient->full_name }} ({{ $patient->patient_id }})</p>
                    <small class="text-muted">{{ __('Checkup Date:') }} {{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y g:i A') }}</small>
                </div>
                <div>
                    <a href="{{ route('checkups.show', [$patient, $checkup]) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Checkup') }}
                    </a>
                    <a href="{{ route('checkups.index', $patient) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Checkups') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        {{ __('Edit Checkup Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('checkups.update', [$patient, $checkup]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Checkup Date -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="checkup_date" class="form-label">{{ __('Checkup Date & Time') }}</label>
                                <input type="datetime-local" class="form-control @error('checkup_date') is-invalid @enderror"
                                       id="checkup_date" name="checkup_date"
                                       value="{{ old('checkup_date', \Carbon\Carbon::parse($checkup->checkup_date)->format('Y-m-d\TH:i')) }}">
                                @error('checkup_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Vital Signs -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-heartbeat me-1"></i>
                                    {{ __('Vital Signs') }}
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                           id="weight" name="weight" step="0.1" min="1" max="500"
                                           value="{{ old('weight', $checkup->weight) }}" placeholder="70.5">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                    <input type="number" class="form-control @error('height') is-invalid @enderror"
                                           id="height" name="height" step="0.1" min="50" max="300"
                                           value="{{ old('height', $checkup->height) }}" placeholder="175">
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="blood_pressure" class="form-label">{{ __('Blood Pressure') }}</label>
                                    <input type="text" class="form-control @error('blood_pressure') is-invalid @enderror"
                                           id="blood_pressure" name="blood_pressure"
                                           value="{{ old('blood_pressure', $checkup->blood_pressure) }}" placeholder="120/80" pattern="\d{2,3}/\d{2,3}">
                                    <div class="form-text">{{ __('Format: 120/80') }}</div>
                                    @error('blood_pressure')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="heart_rate" class="form-label">{{ __('Heart Rate (bpm)') }}</label>
                                    <input type="number" class="form-control @error('heart_rate') is-invalid @enderror" 
                                           id="heart_rate" name="heart_rate" min="30" max="200" 
                                           value="{{ old('heart_rate', $checkup->heart_rate) }}" placeholder="72">
                                    @error('heart_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Additional Measurements -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-thermometer-half me-1"></i>
                                    {{ __('Additional Measurements') }}
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="temperature" class="form-label">{{ __('Temperature (°C)') }}</label>
                                    <input type="number" class="form-control @error('temperature') is-invalid @enderror" 
                                           id="temperature" name="temperature" step="0.1" min="30" max="45" 
                                           value="{{ old('temperature', $checkup->temperature) }}" placeholder="36.5">
                                    @error('temperature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="respiratory_rate" class="form-label">{{ __('Respiratory Rate (per min)') }}</label>
                                    <input type="number" class="form-control @error('respiratory_rate') is-invalid @enderror" 
                                           id="respiratory_rate" name="respiratory_rate" min="5" max="50" 
                                           value="{{ old('respiratory_rate', $checkup->respiratory_rate) }}" placeholder="16">
                                    @error('respiratory_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="blood_sugar" class="form-label">{{ __('Blood Sugar (mg/dL)') }}</label>
                                    <input type="number" class="form-control @error('blood_sugar') is-invalid @enderror" 
                                           id="blood_sugar" name="blood_sugar" step="0.1" min="20" max="600" 
                                           value="{{ old('blood_sugar', $checkup->blood_sugar) }}" placeholder="100">
                                    @error('blood_sugar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Custom Vital Signs -->
                        @php
                            $patientCustomSigns = $patient->assigned_custom_vital_signs;
                        @endphp

                        @if($patientCustomSigns->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    {{ __('Additional Vital Signs') }}
                                </h6>

                                <div class="row">
                                    @foreach($patientCustomSigns as $assignment)
                                        @php $sign = $assignment->customVitalSign; @endphp
                                        <div class="col-md-6 mb-3">
                                            <label for="custom_{{ $sign->id }}" class="form-label">
                                                {{ $sign->display_name }}
                                                @if($sign->normal_range)
                                                    <small class="text-muted">(Normal: {{ $sign->normal_range }})</small>
                                                @endif
                                                @if($assignment->medical_condition)
                                                    <br><small class="text-info">{{ $assignment->medical_condition }}</small>
                                                @endif
                                            </label>

                                            @if($sign->type === 'select')
                                                <select class="form-select @error('custom_vital_signs.'.$sign->id) is-invalid @enderror"
                                                        id="custom_{{ $sign->id }}" name="custom_vital_signs[{{ $sign->id }}]">
                                                    <option value="">{{ __('Select...') }}</option>
                                                    @foreach($sign->options as $value => $label)
                                                        <option value="{{ $value }}" {{ (old('custom_vital_signs.'.$sign->id) ?? ($checkup->custom_vital_signs[$sign->id] ?? '')) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="number" class="form-control @error('custom_vital_signs.'.$sign->id) is-invalid @enderror"
                                                       id="custom_{{ $sign->id }}" name="custom_vital_signs[{{ $sign->id }}]"
                                                       @if($sign->min_value) min="{{ $sign->min_value }}" @endif
                                                       @if($sign->max_value) max="{{ $sign->max_value }}" @endif
                                                       step="0.1" value="{{ old('custom_vital_signs.'.$sign->id) ?? ($checkup->custom_vital_signs[$sign->id] ?? '') }}"
                                                       placeholder="{{ $sign->unit ? 'Enter value in ' . $sign->unit : 'Enter value' }}">
                                            @endif

                                            @error('custom_vital_signs.'.$sign->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Clinical Notes -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-notes-medical me-1"></i>
                                    {{ __('Clinical Notes') }}
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="symptoms" class="form-label">{{ __('Symptoms') }}</label>
                                    <textarea class="form-control @error('symptoms') is-invalid @enderror" 
                                              id="symptoms" name="symptoms" rows="3" 
                                              placeholder="{{ __('Describe any symptoms the patient is experiencing...') }}">{{ old('symptoms', $checkup->symptoms) }}</textarea>
                                    @error('symptoms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="{{ __('Additional observations and notes...') }}">{{ old('notes', $checkup->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="recommendations" class="form-label">{{ __('Recommendations') }}</label>
                                    <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                              id="recommendations" name="recommendations" rows="3" 
                                              placeholder="{{ __('Treatment recommendations and follow-up instructions...') }}">{{ old('recommendations', $checkup->recommendations) }}</textarea>
                                    @error('recommendations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('checkups.index', $patient) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Update Checkup') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-calculate BMI when weight and height are entered
document.addEventListener('DOMContentLoaded', function() {
    const weightInput = document.getElementById('weight');
    const heightInput = document.getElementById('height');
    
    function calculateBMI() {
        const weight = parseFloat(weightInput.value);
        const height = parseFloat(heightInput.value);
        
        if (weight && height) {
            const heightInMeters = height / 100;
            const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);
            
            // You can display BMI somewhere if needed
            console.log('BMI:', bmi);
        }
    }
    
    weightInput.addEventListener('input', calculateBMI);
    heightInput.addEventListener('input', calculateBMI);
});
</script>
@endsection
