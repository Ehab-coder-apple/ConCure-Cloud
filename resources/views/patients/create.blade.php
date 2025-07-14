@extends('layouts.app')

@section('title', __('Add New Patient'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        {{ __('Add New Patient') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">{{ __('Patients') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Add New Patient') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Patients') }}
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-plus me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('patients.store') }}" method="POST">
                                @csrf
                                
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
                                               value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" name="last_name" 
                                               value="{{ old('last_name') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }} <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                               id="date_of_birth" name="date_of_birth" 
                                               value="{{ old('date_of_birth') }}" required>
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">{{ __('Gender') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                            <option value="">{{ __('Select Gender') }}</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
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
                                               value="{{ old('phone') }}"
                                               placeholder="{{ __('Phone number') }}">
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
                                               value="{{ old('whatsapp_phone') }}"
                                               placeholder="{{ __('WhatsApp number for communication') }}">
                                        @error('whatsapp_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email"
                                               value="{{ old('email') }}"
                                               placeholder="{{ __('Email address') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="address" class="form-label">{{ __('Address') }}</label>
                                        <textarea class="form-control @error('address') is-invalid @enderror"
                                                  id="address" name="address" rows="2"
                                                  placeholder="{{ __('Home address') }}">{{ old('address') }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Personal Information -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            {{ __('Personal Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="job" class="form-label">{{ __('Occupation') }}</label>
                                        <input type="text" class="form-control @error('job') is-invalid @enderror"
                                               id="job" name="job"
                                               value="{{ old('job') }}"
                                               placeholder="{{ __('Job/Occupation') }}">
                                        @error('job')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="education" class="form-label">{{ __('Education Level') }}</label>
                                        <input type="text" class="form-control @error('education') is-invalid @enderror"
                                               id="education" name="education"
                                               value="{{ old('education') }}"
                                               placeholder="{{ __('Education level') }}">
                                        @error('education')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Physical Information -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-weight me-2"></i>
                                            {{ __('Physical Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                        <input type="number" class="form-control @error('height') is-invalid @enderror"
                                               id="height" name="height" min="50" max="300" step="0.1"
                                               value="{{ old('height') }}"
                                               placeholder="{{ __('Height in centimeters') }}">
                                        @error('height')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                        <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                               id="weight" name="weight" min="1" max="500" step="0.1"
                                               value="{{ old('weight') }}"
                                               placeholder="{{ __('Weight in kilograms') }}">
                                        @error('weight')
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
                                    
                                    <div class="col-12">
                                        <label for="allergies" class="form-label">{{ __('Allergies') }}</label>
                                        <textarea class="form-control @error('allergies') is-invalid @enderror"
                                                  id="allergies" name="allergies" rows="2"
                                                  placeholder="{{ __('Known allergies and reactions') }}">{{ old('allergies') }}</textarea>
                                        @error('allergies')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="chronic_illnesses" class="form-label">{{ __('Chronic Illnesses') }}</label>
                                        <textarea class="form-control @error('chronic_illnesses') is-invalid @enderror"
                                                  id="chronic_illnesses" name="chronic_illnesses" rows="2"
                                                  placeholder="{{ __('Chronic conditions and ongoing health issues') }}">{{ old('chronic_illnesses') }}</textarea>
                                        @error('chronic_illnesses')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="surgeries_history" class="form-label">{{ __('Surgery History') }}</label>
                                        <textarea class="form-control @error('surgeries_history') is-invalid @enderror"
                                                  id="surgeries_history" name="surgeries_history" rows="2"
                                                  placeholder="{{ __('Previous surgeries and procedures') }}">{{ old('surgeries_history') }}</textarea>
                                        @error('surgeries_history')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="diet_history" class="form-label">{{ __('Diet History') }}</label>
                                        <textarea class="form-control @error('diet_history') is-invalid @enderror"
                                                  id="diet_history" name="diet_history" rows="2"
                                                  placeholder="{{ __('Previous diets and nutritional information') }}">{{ old('diet_history') }}</textarea>
                                        @error('diet_history')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Special Conditions -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ __('Special Conditions') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input @error('is_pregnant') is-invalid @enderror" 
                                                   type="checkbox" id="is_pregnant" name="is_pregnant" value="1"
                                                   {{ old('is_pregnant') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_pregnant">
                                                {{ __('Currently Pregnant') }}
                                            </label>
                                            @error('is_pregnant')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Emergency Contact -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-phone-alt me-2"></i>
                                            {{ __('Emergency Contact') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="emergency_contact_name" class="form-label">{{ __('Emergency Contact Name') }}</label>
                                        <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                               id="emergency_contact_name" name="emergency_contact_name"
                                               value="{{ old('emergency_contact_name') }}"
                                               placeholder="{{ __('Full name of emergency contact') }}">
                                        @error('emergency_contact_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="emergency_contact_phone" class="form-label">{{ __('Emergency Contact Phone') }}</label>
                                        <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                               id="emergency_contact_phone" name="emergency_contact_phone"
                                               value="{{ old('emergency_contact_phone') }}"
                                               placeholder="{{ __('Emergency contact phone number') }}">
                                        @error('emergency_contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Additional Notes -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-sticky-note me-2"></i>
                                            {{ __('Additional Notes') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                                  id="notes" name="notes" rows="3"
                                                  placeholder="{{ __('Additional notes about the patient') }}">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Create Patient') }}
                                    </button>
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
