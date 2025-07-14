@extends('layouts.app')

@section('title', __('Edit Patient'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-edit text-primary me-2"></i>
                        {{ __('Edit Patient') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">{{ __('Patients') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Edit Patient') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('patients.show', $patient->id ?? 1) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Patient') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-edit me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('patients.update', $patient->id ?? 1) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row g-3">
                                    <!-- Basic Information -->
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-user me-2"></i>
                                            {{ __('Basic Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" name="first_name" 
                                               value="{{ old('first_name', $patient->first_name ?? 'Demo') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" name="last_name" 
                                               value="{{ old('last_name', $patient->last_name ?? 'Patient') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                               id="date_of_birth" name="date_of_birth" 
                                               value="{{ old('date_of_birth', $patient->date_of_birth ?? '1990-01-01') }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">{{ __('Gender') }}</label>
                                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                            <option value="">{{ __('Select Gender') }}</option>
                                            <option value="male" {{ old('gender', $patient->gender ?? 'male') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                            <option value="female" {{ old('gender', $patient->gender ?? '') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Contact Information -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-phone me-2"></i>
                                            {{ __('Contact Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone"
                                               value="{{ old('phone', $patient->phone ?? '+1-555-0123') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="whatsapp_phone" class="form-label">
                                            <i class="fab fa-whatsapp text-success me-1"></i>
                                            {{ __('WhatsApp Number') }}
                                        </label>
                                        <input type="tel" class="form-control @error('whatsapp_phone') is-invalid @enderror"
                                               id="whatsapp_phone" name="whatsapp_phone"
                                               value="{{ old('whatsapp_phone', $patient->whatsapp_phone ?? '') }}"
                                               placeholder="{{ __('WhatsApp number for communication') }}">
                                        @error('whatsapp_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">{{ __('Email') }}</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email"
                                               value="{{ old('email', $patient->email ?? 'demo@patient.com') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="address" class="form-label">{{ __('Address') }}</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                                  id="address" name="address" rows="2">{{ old('address', $patient->address ?? '123 Main Street, City, State') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Medical Information -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-heartbeat me-2"></i>
                                            {{ __('Medical Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                        <input type="number" class="form-control @error('height') is-invalid @enderror" 
                                               id="height" name="height" step="0.1" 
                                               value="{{ old('height', $patient->height ?? '170') }}">
                                        @error('height')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                        <input type="number" class="form-control @error('weight') is-invalid @enderror" 
                                               id="weight" name="weight" step="0.1" 
                                               value="{{ old('weight', $patient->weight ?? '70') }}">
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="blood_type" class="form-label">{{ __('Blood Type') }}</label>
                                        <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                            <option value="">{{ __('Select Blood Type') }}</option>
                                            <option value="A+" {{ old('blood_type', $patient->blood_type ?? 'O+') == 'A+' ? 'selected' : '' }}>A+</option>
                                            <option value="A-" {{ old('blood_type', $patient->blood_type ?? '') == 'A-' ? 'selected' : '' }}>A-</option>
                                            <option value="B+" {{ old('blood_type', $patient->blood_type ?? '') == 'B+' ? 'selected' : '' }}>B+</option>
                                            <option value="B-" {{ old('blood_type', $patient->blood_type ?? '') == 'B-' ? 'selected' : '' }}>B-</option>
                                            <option value="AB+" {{ old('blood_type', $patient->blood_type ?? '') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                            <option value="AB-" {{ old('blood_type', $patient->blood_type ?? '') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                            <option value="O+" {{ old('blood_type', $patient->blood_type ?? 'O+') == 'O+' ? 'selected' : '' }}>O+</option>
                                            <option value="O-" {{ old('blood_type', $patient->blood_type ?? '') == 'O-' ? 'selected' : '' }}>O-</option>
                                        </select>
                                        @error('blood_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="is_active" class="form-label">{{ __('Status') }}</label>
                                        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                            <option value="1" {{ old('is_active', $patient->is_active ?? '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                            <option value="0" {{ old('is_active', $patient->is_active ?? '1') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="medical_history" class="form-label">{{ __('Medical History') }}</label>
                                        <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                                  id="medical_history" name="medical_history" rows="4" 
                                                  placeholder="{{ __('Any relevant medical history, allergies, or conditions...') }}">{{ old('medical_history', $patient->medical_history ?? '') }}</textarea>
                                        @error('medical_history')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('patients.show', $patient->id ?? 1) }}" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i>
                                                {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                {{ __('Update Patient') }}
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
    </div>
</div>
@endsection
