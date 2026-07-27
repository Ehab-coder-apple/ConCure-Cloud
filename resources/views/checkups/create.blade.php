@extends('layouts.app')

@section('page-title', __('Add Checkup') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-heartbeat text-danger"></i>
                        {{ __('Add New Checkup') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Patient:') }} {{ $patient->full_name }} ({{ $patient->patient_id }})</p>
                </div>
                <div>
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
                        <i class="fas fa-plus me-2"></i>
                        {{ __('Checkup Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $selectedTemplateId = old('template_id', optional($template)->id);
                    @endphp

                    @include('checkups.partials.template-selector', [
                        'selectedTemplateId' => $selectedTemplateId,
                    ])

                    <form action="{{ route('checkups.store', $patient) }}" method="POST">
                        @csrf

                        <!-- Checkup Date -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="checkup_date" class="form-label">{{ __('Checkup Date & Time') }}</label>
                                <input type="datetime-local" class="form-control @error('checkup_date') is-invalid @enderror"
                                       id="checkup_date" name="checkup_date"
                                       value="{{ old('checkup_date', now()->format('Y-m-d\TH:i')) }}">
                                @error('checkup_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-heartbeat me-1"></i>
                                    {{ __('Vital Signs') }}
                                </h6>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                           id="weight" name="weight" step="0.1" min="1" max="500"
                                           value="{{ old('weight') }}" placeholder="70.5">
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                    <input type="number" class="form-control @error('height') is-invalid @enderror"
                                           id="height" name="height" step="0.1" min="50" max="300"
                                           value="{{ old('height') }}" placeholder="175">
                                    @error('height')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="blood_pressure" class="form-label">{{ __('Blood Pressure') }}</label>
                                    <input type="text" class="form-control @error('blood_pressure') is-invalid @enderror"
                                           id="blood_pressure" name="blood_pressure"
                                           value="{{ old('blood_pressure') }}" placeholder="120/80" pattern="\d{2,3}/\d{2,3}">
                                    <div class="form-text">{{ __('Format: 120/80') }}</div>
                                    @error('blood_pressure')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="heart_rate" class="form-label">{{ __('Heart Rate (bpm)') }}</label>
                                    <input type="number" class="form-control @error('heart_rate') is-invalid @enderror"
                                           id="heart_rate" name="heart_rate" min="30" max="200"
                                           value="{{ old('heart_rate') }}" placeholder="72">
                                    @error('heart_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-thermometer-half me-1"></i>
                                    {{ __('Additional Measurements') }}
                                </h6>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="temperature" class="form-label">{{ __('Temperature (°C)') }}</label>
                                    <input type="number" class="form-control @error('temperature') is-invalid @enderror"
                                           id="temperature" name="temperature" step="0.1" min="30" max="45"
                                           value="{{ old('temperature') }}" placeholder="36.5">
                                    @error('temperature')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="respiratory_rate" class="form-label">{{ __('Respiratory Rate (per min)') }}</label>
                                    <input type="number" class="form-control @error('respiratory_rate') is-invalid @enderror"
                                           id="respiratory_rate" name="respiratory_rate" min="5" max="50"
                                           value="{{ old('respiratory_rate') }}" placeholder="16">
                                    @error('respiratory_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="mb-0">
                                    <label for="blood_sugar" class="form-label">{{ __('Blood Sugar (mg/dL)') }}</label>
                                    <input type="number" class="form-control @error('blood_sugar') is-invalid @enderror"
                                           id="blood_sugar" name="blood_sugar" step="0.1" min="20" max="600"
                                           value="{{ old('blood_sugar') }}" placeholder="100">
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

                        @php
                            $reservedClinicalKeys = \App\Models\PatientCheckup::reservedClinicalCustomFieldKeys();
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
                                                        <option value="{{ $value }}" {{ old('custom_vital_signs.'.$sign->id) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input type="number" class="form-control @error('custom_vital_signs.'.$sign->id) is-invalid @enderror"
                                                       id="custom_{{ $sign->id }}" name="custom_vital_signs[{{ $sign->id }}]"
                                                       @if($sign->min_value) min="{{ $sign->min_value }}" @endif
                                                       @if($sign->max_value) max="{{ $sign->max_value }}" @endif
                                                       step="0.1" value="{{ old('custom_vital_signs.'.$sign->id) }}"
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

                            @if(isset($template) && $template)
                            <input type="hidden" name="template_id" value="{{ $template->id }}">
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-file-medical me-1"></i>
                                        {{ __('Template') }}: {{ $template->name }}
                                    </h6>

                                    @foreach($template->form_sections as $sectionKey => $section)
                                        @php
                                            $fields = collect($section['fields'] ?? [])->reject(fn ($field, $fieldKey) => in_array($fieldKey, $reservedClinicalKeys, true));
                                        @endphp
                                        @if($fields->isNotEmpty())
                                        <div class="mb-3">
                                            <h6 class="fw-semibold">{{ $section['title'] ?? Str::headline($sectionKey) }}</h6>
                                            <div class="row">
                                                @foreach($fields as $fieldKey => $field)
                                                    @php $type = $field['type'] ?? 'text'; @endphp
                                                    <div class="col-12 mb-3">
                                                        <label class="form-label">{{ $field['label'] ?? Str::headline($fieldKey) }}
                                                            @if(!empty($field['required'])) <span class="text-danger">*</span> @endif
                                                        </label>

                                                        @switch($type)
                                                            @case('textarea')
                                                                <textarea class="form-control" name="custom_fields[{{ $fieldKey }}]" rows="3" placeholder="{{ $field['placeholder'] ?? '' }}">{{ old('custom_fields.'.$fieldKey) }}</textarea>
                                                                @break

                                                            @case('number')
                                                                <input type="number" class="form-control" name="custom_fields[{{ $fieldKey }}]"
                                                                    @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                                                    @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                                                                    step="{{ $field['step'] ?? '0.1' }}" value="{{ old('custom_fields.'.$fieldKey) }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                                                                @break

                                                            @case('select')
                                                                <select class="form-select" name="custom_fields[{{ $fieldKey }}]">
                                                                    <option value="">{{ __('Select...') }}</option>
                                                                    @php $opts = $field['options'] ?? []; @endphp
                                                                    @foreach($opts as $optValue => $optLabel)
                                                                        @php $value = is_int($optValue) ? $optLabel : $optValue; $label = is_int($optValue) ? $optLabel : $optLabel; @endphp
                                                                        <option value="{{ $value }}" {{ old('custom_fields.'.$fieldKey) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @break

                                                            @case('checkbox')
                                                                <input type="hidden" name="custom_fields[{{ $fieldKey }}]" value="0">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" value="1" id="cf_{{ $fieldKey }}" name="custom_fields[{{ $fieldKey }}]" {{ old('custom_fields.'.$fieldKey) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="cf_{{ $fieldKey }}">{{ $field['label'] ?? Str::headline($fieldKey) }}</label>
                                                                </div>
                                                                @break

                                                            @case('date')
                                                                <input type="date" class="form-control" name="custom_fields[{{ $fieldKey }}]" value="{{ old('custom_fields.'.$fieldKey) }}">
                                                                @break

                                                            @case('time')
                                                                <input type="time" class="form-control" name="custom_fields[{{ $fieldKey }}]" value="{{ old('custom_fields.'.$fieldKey) }}">
                                                                @break

                                                            @default
                                                                <input type="text" class="form-control" name="custom_fields[{{ $fieldKey }}]" value="{{ old('custom_fields.'.$fieldKey) }}" placeholder="{{ $field['placeholder'] ?? '' }}">
                                                        @endswitch
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        @include('checkups.partials.fixed-clinical-fields')


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
                                              placeholder="{{ __('Describe any symptoms the patient is experiencing...') }}">{{ old('symptoms') }}</textarea>
                                    @error('symptoms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="notes" name="notes" rows="3"
                                              placeholder="{{ __('Additional observations and notes...') }}">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="recommendations" class="form-label">{{ __('Recommendations') }}</label>
                                    <textarea class="form-control @error('recommendations') is-invalid @enderror"
                                              id="recommendations" name="recommendations" rows="3"
                                              placeholder="{{ __('Treatment recommendations and follow-up instructions...') }}">{{ old('recommendations') }}</textarea>
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
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Save Checkup') }}
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
