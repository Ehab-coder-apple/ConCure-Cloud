@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Logo/Header -->
                    <div class="text-center mb-4">
                        <i class="fas fa-clinic-medical fa-3x text-primary mb-3"></i>
                        <h2 class="h4 text-primary fw-bold">Register for {{ config('app.name') }}</h2>
                        <p class="text-muted">Create your account with an activation code</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" id="registrationForm">
                        @csrf

                        <!-- Activation Code -->
                        <div class="mb-3">
                            <label for="activation_code" class="form-label">
                                <i class="fas fa-key"></i> Activation Code *
                            </label>
                            <input id="activation_code" type="text" 
                                   class="form-control @error('activation_code') is-invalid @enderror" 
                                   name="activation_code" value="{{ old('activation_code') }}" 
                                   required placeholder="Enter your activation code">
                            @error('activation_code')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                Contact your administrator to get an activation code.
                            </div>
                        </div>

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input id="first_name" type="text" 
                                       class="form-control @error('first_name') is-invalid @enderror" 
                                       name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input id="last_name" type="text" 
                                       class="form-control @error('last_name') is-invalid @enderror" 
                                       name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-at"></i> Username *
                            </label>
                            <input id="username" type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   name="username" value="{{ old('username') }}" required>
                            @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address *
                            </label>
                            <input id="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input id="phone" type="tel" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Clinic Information (shown for clinic registration) -->
                        <div id="clinicFields" style="display: none;">
                            <hr>
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-clinic-medical"></i> Clinic Information
                            </h5>
                            
                            <div class="mb-3">
                                <label for="clinic_name" class="form-label">
                                    <i class="fas fa-building"></i> Clinic Name *
                                </label>
                                <input id="clinic_name" type="text" 
                                       class="form-control @error('clinic_name') is-invalid @enderror" 
                                       name="clinic_name" value="{{ old('clinic_name') }}">
                                @error('clinic_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="clinic_email" class="form-label">
                                        <i class="fas fa-envelope"></i> Clinic Email *
                                    </label>
                                    <input id="clinic_email" type="email" 
                                           class="form-control @error('clinic_email') is-invalid @enderror" 
                                           name="clinic_email" value="{{ old('clinic_email') }}">
                                    @error('clinic_email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="clinic_phone" class="form-label">
                                        <i class="fas fa-phone"></i> Clinic Phone
                                    </label>
                                    <input id="clinic_phone" type="tel" 
                                           class="form-control @error('clinic_phone') is-invalid @enderror" 
                                           name="clinic_phone" value="{{ old('clinic_phone') }}">
                                    @error('clinic_phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="clinic_address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Clinic Address
                                </label>
                                <textarea id="clinic_address" 
                                          class="form-control @error('clinic_address') is-invalid @enderror" 
                                          name="clinic_address" rows="3">{{ old('clinic_address') }}</textarea>
                                @error('clinic_address')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input id="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <input id="password_confirmation" type="password" 
                                       class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-4">
                        <p class="text-muted">Already have an account?</p>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company Info -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-building"></i>
                    Powered by {{ $companyName ?? 'Connect Pure' }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const activationCodeInput = document.getElementById('activation_code');
    const clinicFields = document.getElementById('clinicFields');
    
    activationCodeInput.addEventListener('blur', function() {
        const code = this.value.trim();
        if (code.startsWith('CLINIC-')) {
            clinicFields.style.display = 'block';
            // Make clinic fields required
            document.getElementById('clinic_name').required = true;
            document.getElementById('clinic_email').required = true;
        } else {
            clinicFields.style.display = 'none';
            // Remove required attribute from clinic fields
            document.getElementById('clinic_name').required = false;
            document.getElementById('clinic_email').required = false;
        }
    });
});
</script>
@endpush
@endsection
