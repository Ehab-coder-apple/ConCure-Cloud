@extends('layouts.master-welcome')

@section('title', 'Register as Program Owner - ConCure Master Control')

@section('content')
<div class="master-container">
    <div class="container">
        <div class="form-container mx-auto">
            <div class="form-header">
                <div class="master-badge">
                    <i class="fas fa-crown me-1"></i>
                    Program Owner Registration
                </div>
                <h1 class="form-title">
                    <i class="fas fa-user-shield text-primary me-2"></i>
                    Register as Program Owner
                </h1>
                <p class="form-subtitle">Create your master control account to manage the ConCure healthcare platform</p>
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

            <form method="POST" action="{{ route('master.welcome.store') }}">
                @csrf

                <!-- Company Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-building me-2"></i>
                        Company Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" value="{{ old('company_name') }}" 
                                   placeholder="Enter your company name" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_website" class="form-label">Company Website</label>
                            <input type="url" class="form-control @error('company_website') is-invalid @enderror" 
                                   id="company_website" name="company_website" value="{{ old('company_website') }}" 
                                   placeholder="https://www.example.com">
                            @error('company_website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_phone" class="form-label">Company Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('company_phone') is-invalid @enderror" 
                                   id="company_phone" name="company_phone" value="{{ old('company_phone') }}" 
                                   placeholder="+1 (555) 123-4567" required>
                            @error('company_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                      id="company_address" name="company_address" rows="3" 
                                      placeholder="Enter your company address" required>{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Program Owner Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-crown me-2"></i>
                        Program Owner Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="owner_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('owner_first_name') is-invalid @enderror" 
                                   id="owner_first_name" name="owner_first_name" value="{{ old('owner_first_name') }}" 
                                   placeholder="John" required>
                            @error('owner_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('owner_last_name') is-invalid @enderror" 
                                   id="owner_last_name" name="owner_last_name" value="{{ old('owner_last_name') }}" 
                                   placeholder="Doe" required>
                            @error('owner_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('owner_email') is-invalid @enderror"
                                   id="owner_email" name="owner_email" value="{{ old('owner_email') }}"
                                   placeholder="owner@company.com" required>
                            @error('owner_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username') }}"
                                   placeholder="Choose a unique username" required>
                            <div class="form-text">Used for login. Only letters, numbers, dashes, and underscores allowed.</div>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('owner_phone') is-invalid @enderror" 
                                   id="owner_phone" name="owner_phone" value="{{ old('owner_phone') }}" 
                                   placeholder="+1 (555) 123-4567">
                            @error('owner_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Minimum 8 characters" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Confirm your password" required>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input @error('terms') is-invalid @enderror" 
                               type="checkbox" id="terms" name="terms" value="1" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-primary">Master Control Terms of Service</a> and 
                            <a href="#" class="text-primary">Platform Privacy Policy</a> <span class="text-danger">*</span>
                        </label>
                        @error('terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Access Information -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-crown me-1"></i>
                        Program Owner Privileges
                    </h6>
                    <ul class="mb-0">
                        <li>Full platform administrative access</li>
                        <li>Clinic management and monitoring</li>
                        <li>User administration and permissions</li>
                        <li>Financial reports and analytics</li>
                        <li>System configuration and settings</li>
                        <li>Team member invitation and management</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-master btn-lg">
                        <i class="fas fa-user-shield me-2"></i>
                        Create Program Owner Account
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Already have a master control account? 
                        <a href="{{ route('master.welcome.login') }}" class="text-primary text-decoration-none fw-bold">Sign In</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="{{ route('master.welcome.index') }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Master Control Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('owner_email');
    const usernameInput = document.getElementById('username');

    emailInput.addEventListener('blur', function() {
        if (this.value && !usernameInput.value) {
            // Auto-suggest username based on email
            const emailPart = this.value.split('@')[0];
            const cleanUsername = emailPart.replace(/[^a-zA-Z0-9_-]/g, '').toLowerCase();
            usernameInput.value = cleanUsername;
        }
    });
});
</script>
@endpush

@push('scripts')
<script>
    // Form validation and UX improvements
    document.addEventListener('DOMContentLoaded', function() {
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        // Real-time password confirmation
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        // Phone number formatting
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
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
</script>
@endpush
