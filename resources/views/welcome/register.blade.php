@extends('layouts.welcome')

@section('title', 'Register Your Clinic - ConCure')

@section('content')
<div class="welcome-container">
    <div class="container">
        <div class="form-container mx-auto">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="fas fa-clinic-medical text-primary me-2"></i>
                    Register Your Clinic
                </h1>
                <p class="form-subtitle">Start your 30-day free trial and transform your clinic management</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('welcome.store') }}">
                @csrf

                <!-- Clinic Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-hospital me-2"></i>
                        Clinic Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="clinic_name" class="form-label">Clinic Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('clinic_name') is-invalid @enderror" 
                                   id="clinic_name" name="clinic_name" value="{{ old('clinic_name') }}" 
                                   placeholder="Enter your clinic name" required>
                            @error('clinic_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="clinic_address" class="form-label">Clinic Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('clinic_address') is-invalid @enderror" 
                                      id="clinic_address" name="clinic_address" rows="3" 
                                      placeholder="Enter your clinic address" required>{{ old('clinic_address') }}</textarea>
                            @error('clinic_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="clinic_phone" class="form-label">Clinic Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('clinic_phone') is-invalid @enderror" 
                                   id="clinic_phone" name="clinic_phone" value="{{ old('clinic_phone') }}" 
                                   placeholder="+1 (555) 123-4567" required>
                            @error('clinic_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="clinic_email" class="form-label">Clinic Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('clinic_email') is-invalid @enderror" 
                                   id="clinic_email" name="clinic_email" value="{{ old('clinic_email') }}" 
                                   placeholder="clinic@example.com" required>
                            @error('clinic_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Administrator Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user-shield me-2"></i>
                        Administrator Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('admin_first_name') is-invalid @enderror" 
                                   id="admin_first_name" name="admin_first_name" value="{{ old('admin_first_name') }}" 
                                   placeholder="John" required>
                            @error('admin_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="admin_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('admin_last_name') is-invalid @enderror" 
                                   id="admin_last_name" name="admin_last_name" value="{{ old('admin_last_name') }}" 
                                   placeholder="Doe" required>
                            @error('admin_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="admin_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('admin_email') is-invalid @enderror" 
                                   id="admin_email" name="admin_email" value="{{ old('admin_email') }}" 
                                   placeholder="admin@example.com" required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="admin_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('admin_phone') is-invalid @enderror" 
                                   id="admin_phone" name="admin_phone" value="{{ old('admin_phone') }}" 
                                   placeholder="+1 (555) 123-4567">
                            @error('admin_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" placeholder="Minimum 8 characters" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation"
                                       placeholder="Confirm your password" required>
                                <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input @error('terms') is-invalid @enderror" 
                               type="checkbox" id="terms" name="terms" value="1" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-primary">Terms of Service</a> and 
                            <a href="#" class="text-primary">Privacy Policy</a> <span class="text-danger">*</span>
                        </label>
                        @error('terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Registration Information -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-1"></i>
                        ConCure Registration
                    </h6>
                    <ul class="mb-0">
                        <li>Full access to all features</li>
                        <li>Complete clinic management solution</li>
                        <li>Professional support included</li>
                        <li>Secure and reliable platform</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-rocket me-2"></i>
                        Register Clinic
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Already have an account? 
                        <a href="{{ route('welcome.login') }}" class="text-primary text-decoration-none fw-bold">Sign In</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="{{ route('welcome.index') }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Form validation and UX improvements
    document.addEventListener('DOMContentLoaded', function() {
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        // Show/Hide password toggles
        function setupToggle(inputId, btnId) {
            const input = document.getElementById(inputId);
            const btn = document.getElementById(btnId);
            if (!input || !btn) return;
            btn.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }
        setupToggle('password', 'togglePassword');
        setupToggle('password_confirmation', 'toggleConfirmPassword');

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = getPasswordStrength(password);
                // You can add password strength indicator here
            });
        }

        if (confirmPasswordInput) {
            // Real-time password confirmation
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput ? passwordInput.value : '';
                const confirmPassword = this.value;

                if (confirmPassword && password !== confirmPassword) {
                    this.setCustomValidity('Passwords do not match');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }

        // Phone number formatting
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Basic phone formatting can be added here
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                } else if (value.length >= 3) {
                    value = value.replace(/(\d{3})(\d{3})/, '($1) $2');
                }
                this.value = value;
            });
        });
    });

    function getPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        return strength;
    }
</script>
@endpush
