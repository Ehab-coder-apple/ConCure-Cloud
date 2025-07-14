@extends('layouts.guest')

@section('title', __('Register Your Clinic'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">
                    <i class="fas fa-hospital me-3"></i>
                    {{ __('Join ConCure') }}
                </h1>
                <p class="lead text-muted">{{ __('Professional Clinic Management System - Request Access Today') }}</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Registration Form -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        {{ __('Clinic Registration Request') }}
                    </h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('clinic.register') }}">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Clinic Information -->
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-hospital me-2"></i>
                                    {{ __('Clinic Information') }}
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="clinic_name" class="form-label">{{ __('Clinic Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('clinic_name') is-invalid @enderror" 
                                       id="clinic_name" name="clinic_name" value="{{ old('clinic_name') }}" required>
                                @error('clinic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="clinic_phone" class="form-label">{{ __('Phone Number') }}</label>
                                <input type="tel" class="form-control @error('clinic_phone') is-invalid @enderror" 
                                       id="clinic_phone" name="clinic_phone" value="{{ old('clinic_phone') }}">
                                @error('clinic_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="clinic_address" class="form-label">{{ __('Address') }}</label>
                                <textarea class="form-control @error('clinic_address') is-invalid @enderror" 
                                          id="clinic_address" name="clinic_address" rows="2" 
                                          placeholder="{{ __('Enter your clinic address...') }}">{{ old('clinic_address') }}</textarea>
                                @error('clinic_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="clinic_website" class="form-label">{{ __('Website') }}</label>
                                <input type="url" class="form-control @error('clinic_website') is-invalid @enderror" 
                                       id="clinic_website" name="clinic_website" value="{{ old('clinic_website') }}" 
                                       placeholder="https://your-clinic.com">
                                @error('clinic_website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Administrator Information -->
                            <div class="col-12 mt-4">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-user-shield me-2"></i>
                                    {{ __('Administrator Information') }}
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="admin_first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('admin_first_name') is-invalid @enderror" 
                                       id="admin_first_name" name="admin_first_name" value="{{ old('admin_first_name') }}" required>
                                @error('admin_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="admin_last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('admin_last_name') is-invalid @enderror" 
                                       id="admin_last_name" name="admin_last_name" value="{{ old('admin_last_name') }}" required>
                                @error('admin_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="admin_email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                                       id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                                <div class="form-text">{{ __('This will be your login email address') }}</div>
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- System Requirements -->
                            <div class="col-12 mt-4">
                                <h5 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-cog me-2"></i>
                                    {{ __('System Requirements') }}
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="expected_users" class="form-label">{{ __('Expected Number of Users') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('expected_users') is-invalid @enderror" id="expected_users" name="expected_users" required>
                                    <option value="">{{ __('Select range') }}</option>
                                    <option value="5" {{ old('expected_users') == '5' ? 'selected' : '' }}>1-5 {{ __('users') }}</option>
                                    <option value="10" {{ old('expected_users') == '10' ? 'selected' : '' }}>6-10 {{ __('users') }}</option>
                                    <option value="25" {{ old('expected_users') == '25' ? 'selected' : '' }}>11-25 {{ __('users') }}</option>
                                    <option value="50" {{ old('expected_users') == '50' ? 'selected' : '' }}>26-50 {{ __('users') }}</option>
                                    <option value="100" {{ old('expected_users') == '100' ? 'selected' : '' }}>51-100 {{ __('users') }}</option>
                                    <option value="200" {{ old('expected_users') == '200' ? 'selected' : '' }}>100+ {{ __('users') }}</option>
                                </select>
                                @error('expected_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12">
                                <label for="message" class="form-label">{{ __('Additional Information') }}</label>
                                <textarea class="form-control @error('message') is-invalid @enderror" 
                                          id="message" name="message" rows="4" 
                                          placeholder="{{ __('Tell us about your clinic, special requirements, or any questions you have...') }}">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>
                                {{ __('Submit Registration Request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Cards -->
            <div class="row mt-5">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-cloud fa-3x text-primary mb-3"></i>
                            <h5>{{ __('Cloud-Based') }}</h5>
                            <p class="text-muted">{{ __('Access your clinic data from anywhere, anytime with our secure cloud platform.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                            <h5>{{ __('Secure & Compliant') }}</h5>
                            <p class="text-muted">{{ __('HIPAA compliant with enterprise-grade security to protect patient data.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-headset fa-3x text-info mb-3"></i>
                            <h5>{{ __('24/7 Support') }}</h5>
                            <p class="text-muted">{{ __('Dedicated support team to help you get the most out of ConCure.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Already have an activation code? -->
            <div class="card mt-4 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted">{{ __('Already have an activation code?') }}</h6>
                    <a href="{{ route('clinic.activate.form') }}" class="btn btn-outline-primary">
                        <i class="fas fa-key me-1"></i>
                        {{ __('Activate Your Clinic') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
