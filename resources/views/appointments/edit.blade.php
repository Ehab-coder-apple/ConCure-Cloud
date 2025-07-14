@extends('layouts.app')

@section('title', __('Edit Appointment'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-edit text-primary me-2"></i>
                        {{ __('Edit Appointment') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Update appointment details') }} - {{ $appointment->appointment_number }}</p>
                </div>
                <div>
                    <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Appointment') }}
                    </a>
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
                            <form action="{{ route('appointments.update', $appointment->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Patient Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                            <option value="">{{ __('Select patient...') }}</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                                </option>
                                            @endforeach
                                        </select>
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
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
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
                                               value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_datetime)->format('Y-m-d')) }}" 
                                               required>
                                        @error('appointment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Appointment Time -->
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_time" class="form-label">{{ __('Appointment Time') }} <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                               id="appointment_time" name="appointment_time" 
                                               value="{{ old('appointment_time', \Carbon\Carbon::parse($appointment->appointment_datetime)->format('H:i')) }}" required>
                                        @error('appointment_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Appointment Type -->
                                    <div class="col-md-6 mb-3">
                                        <label for="appointment_type" class="form-label">{{ __('Appointment Type') }}</label>
                                        <select class="form-select @error('appointment_type') is-invalid @enderror" id="appointment_type" name="appointment_type">
                                            <option value="">{{ __('Select type...') }}</option>
                                            <option value="consultation" {{ old('appointment_type', $appointment->type) == 'consultation' ? 'selected' : '' }}>{{ __('Consultation') }}</option>
                                            <option value="follow_up" {{ old('appointment_type', $appointment->type) == 'follow_up' ? 'selected' : '' }}>{{ __('Follow Up') }}</option>
                                            <option value="checkup" {{ old('appointment_type', $appointment->type) == 'checkup' ? 'selected' : '' }}>{{ __('Checkup') }}</option>
                                            <option value="procedure" {{ old('appointment_type', $appointment->type) == 'procedure' ? 'selected' : '' }}>{{ __('Procedure') }}</option>
                                            <option value="other" {{ old('appointment_type', $appointment->type) == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                        </select>
                                        @error('appointment_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Duration -->
                                    <div class="col-md-6 mb-3">
                                        <label for="duration" class="form-label">{{ __('Duration (minutes)') }}</label>
                                        <select class="form-select @error('duration') is-invalid @enderror" id="duration" name="duration">
                                            <option value="15" {{ old('duration', $appointment->duration_minutes) == '15' ? 'selected' : '' }}>15 {{ __('minutes') }}</option>
                                            <option value="30" {{ old('duration', $appointment->duration_minutes) == '30' ? 'selected' : '' }}>30 {{ __('minutes') }}</option>
                                            <option value="45" {{ old('duration', $appointment->duration_minutes) == '45' ? 'selected' : '' }}>45 {{ __('minutes') }}</option>
                                            <option value="60" {{ old('duration', $appointment->duration_minutes) == '60' ? 'selected' : '' }}>1 {{ __('hour') }}</option>
                                            <option value="90" {{ old('duration', $appointment->duration_minutes) == '90' ? 'selected' : '' }}>1.5 {{ __('hours') }}</option>
                                            <option value="120" {{ old('duration', $appointment->duration_minutes) == '120' ? 'selected' : '' }}>2 {{ __('hours') }}</option>
                                        </select>
                                        @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                                            <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Notes -->
                                    <div class="col-12 mb-3">
                                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="3" 
                                                  placeholder="{{ __('Additional notes or reason for appointment...') }}">{{ old('notes', $appointment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('appointments.show', $appointment->id) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            {{ __('Update Appointment') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Current Appointment Info Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Current Appointment Info') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Appointment Number') }}</small>
                                <div class="fw-bold">{{ $appointment->appointment_number }}</div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Current Status') }}</small>
                                <div>
                                    <span class="badge bg-{{ 
                                        $appointment->status == 'completed' ? 'success' : 
                                        ($appointment->status == 'cancelled' ? 'danger' : 
                                        ($appointment->status == 'confirmed' ? 'primary' : 'secondary')) 
                                    }}">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">{{ __('Current Date & Time') }}</small>
                                <div>{{ \Carbon\Carbon::parse($appointment->appointment_datetime)->format('M d, Y g:i A') }}</div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">{{ __('Current Duration') }}</small>
                                <div>{{ $appointment->duration_minutes }} {{ __('minutes') }}</div>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted">{{ __('Last Updated') }}</small>
                                <div>{{ \Carbon\Carbon::parse($appointment->updated_at)->format('M d, Y g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
