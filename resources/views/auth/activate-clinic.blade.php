@extends('layouts.guest')

@section('title', __('Activate Clinic'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-hospital me-2"></i>
                        {{ __('Activate Your Clinic') }}
                    </h4>
                    <p class="mb-0 mt-2">{{ __('Welcome to ConCure - Professional Clinic Management') }}</p>
                </div>
                <div class="card-body p-4">
                    @if (session('csrf_error'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>{{ __('Session Expired') }}</strong><br>
                            {{ __('Your session has expired for security reasons. Please fill out the form again.') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clinic.activate') }}" id="activationForm">
                        @csrf
                        
                        <!-- Step 1: Activation Code -->
                        <div class="step" id="step1">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-key me-2"></i>
                                {{ __('Step 1: Enter Activation Code') }}
                            </h5>
                            
                            <div class="mb-3">
                                <label for="activation_code" class="form-label">{{ __('Activation Code') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg text-center font-monospace @error('activation_code') is-invalid @enderror"
                                       id="activation_code" name="activation_code" value="{{ old('activation_code') }}"
                                       placeholder="CLINIC-XXXXXXXX" maxlength="15" required>
                                <div class="form-text">{{ __('Enter the 15-character activation code provided by ConCure') }}</div>
                                @error('activation_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div id="codeValidation" class="d-none">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>{{ __('Valid Activation Code!') }}</strong>
                                    <div id="clinicInfo" class="mt-2"></div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="button" class="btn btn-primary btn-lg" id="validateCodeBtn">
                                    <i class="fas fa-search me-2"></i>
                                    {{ __('Validate Code') }}
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Admin Account Setup -->
                        <div class="step d-none" id="step2">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-user-shield me-2"></i>
                                {{ __('Step 2: Create Admin Account') }}
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="admin_username" class="form-label">{{ __('Username') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('admin_username') is-invalid @enderror" 
                                           id="admin_username" name="admin_username" value="{{ old('admin_username') }}" required>
                                    <div class="form-text">{{ __('Choose a unique username for login') }}</div>
                                    @error('admin_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Email Address') }}</label>
                                    <input type="email" class="form-control" id="admin_email_display" readonly>
                                    <div class="form-text">{{ __('Pre-filled from activation code') }}</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="admin_password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('admin_password') is-invalid @enderror" 
                                           id="admin_password" name="admin_password" required>
                                    <div class="form-text">{{ __('Minimum 8 characters') }}</div>
                                    @error('admin_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="admin_password_confirmation" class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" required>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="backToStep1">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    {{ __('Back') }}
                                </button>
                                <button type="button" class="btn btn-primary" id="proceedToStep3">
                                    {{ __('Continue') }}
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Clinic Details -->
                        <div class="step d-none" id="step3">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('Step 3: Clinic Details') }}
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">{{ __('Clinic Name') }}</label>
                                    <input type="text" class="form-control" id="clinic_name_display" readonly>
                                    <div class="form-text">{{ __('Pre-configured from activation code') }}</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="clinic_phone" class="form-label">{{ __('Phone Number') }}</label>
                                    <input type="tel" class="form-control @error('clinic_phone') is-invalid @enderror" 
                                           id="clinic_phone" name="clinic_phone" value="{{ old('clinic_phone') }}">
                                    @error('clinic_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Email') }}</label>
                                    <input type="email" class="form-control" id="clinic_email_display" readonly>
                                </div>
                                
                                <div class="col-12">
                                    <label for="clinic_address" class="form-label">{{ __('Address') }}</label>
                                    <textarea class="form-control @error('clinic_address') is-invalid @enderror" 
                                              id="clinic_address" name="clinic_address" rows="3" 
                                              placeholder="{{ __('Enter your clinic address...') }}">{{ old('clinic_address') }}</textarea>
                                    @error('clinic_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-between mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="backToStep2">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    {{ __('Back') }}
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check me-2"></i>
                                    {{ __('Activate Clinic') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6 class="text-muted">{{ __('Need Help?') }}</h6>
                    <p class="text-muted mb-2">{{ __('Contact our support team if you need assistance with activation.') }}</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="mailto:support@concure.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>
                            {{ __('Email Support') }}
                        </a>
                        <a href="tel:+1-555-CONCURE" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>
                            {{ __('Call Support') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let clinicData = null;

// Auto-refresh CSRF token every 30 minutes to prevent 419 errors
function refreshCSRFToken() {
    fetch('{{ route("csrf-token") }}')
        .then(response => response.json())
        .then(data => {
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
            const tokenInputs = document.querySelectorAll('input[name="_token"]');
            tokenInputs.forEach(input => input.value = data.token);
        })
        .catch(error => console.log('CSRF token refresh failed:', error));
}

// Refresh token every 30 minutes
setInterval(refreshCSRFToken, 30 * 60 * 1000);

// Refresh token before form submission to prevent 419 errors
document.getElementById('activationForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Refresh CSRF token before submission
    fetch('{{ route("csrf-token") }}')
        .then(response => response.json())
        .then(data => {
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
            const tokenInputs = document.querySelectorAll('input[name="_token"]');
            tokenInputs.forEach(input => input.value = data.token);

            // Submit form after token refresh
            this.submit();
        })
        .catch(error => {
            console.log('CSRF token refresh failed:', error);
            // Submit anyway, might still work
            this.submit();
        });
});

document.getElementById('validateCodeBtn').addEventListener('click', function() {
    const code = document.getElementById('activation_code').value.trim();

    if (!code || code.length !== 15) {
        alert('{{ __("Please enter a valid 15-character activation code.") }}');
        return;
    }

    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Validating...") }}';
    btn.disabled = true;

    // Refresh CSRF token before validation request
    fetch('{{ route("csrf-token") }}')
        .then(response => response.json())
        .then(data => {
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);

            // Now make the validation request with fresh token
            return fetch('{{ route("clinic.validate-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': data.token
                },
                body: JSON.stringify({ code: code })
            });
        })
        .catch(error => {
            console.log('CSRF token refresh failed, trying with existing token:', error);
            // Fallback to existing token
            return fetch('{{ route("clinic.validate-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: code })
            });
        })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            clinicData = data.clinic_info;
            showCodeValidation(data);
            setTimeout(() => showStep(2), 1000);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

function showCodeValidation(data) {
    const validationDiv = document.getElementById('codeValidation');
    const clinicInfoDiv = document.getElementById('clinicInfo');
    
    clinicInfoDiv.innerHTML = `
        <strong>{{ __('Clinic') }}:</strong> ${data.clinic_info.name}<br>
        <strong>{{ __('Admin') }}:</strong> ${data.clinic_info.admin_first_name} ${data.clinic_info.admin_last_name}<br>
        <strong>{{ __('Max Users') }}:</strong> ${data.clinic_info.max_users}<br>
        <strong>{{ __('Expires') }}:</strong> ${new Date(data.clinic_info.expires_at).toLocaleDateString()}
    `;
    
    validationDiv.classList.remove('d-none');
}

function showStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step').forEach(step => step.classList.add('d-none'));
    
    // Show target step
    document.getElementById(`step${stepNumber}`).classList.remove('d-none');
    
    // Populate data if moving to step 2 or 3
    if (stepNumber === 2 && clinicData) {
        document.getElementById('admin_email_display').value = clinicData.admin_email;
        document.getElementById('admin_username').value = clinicData.admin_first_name.toLowerCase() + '_' + clinicData.admin_last_name.toLowerCase();
    }
    
    if (stepNumber === 3 && clinicData) {
        document.getElementById('clinic_name_display').value = clinicData.name;
        document.getElementById('clinic_email_display').value = clinicData.email;
    }
}

// Navigation buttons
document.getElementById('backToStep1').addEventListener('click', () => showStep(1));
document.getElementById('backToStep2').addEventListener('click', () => showStep(2));
document.getElementById('proceedToStep3').addEventListener('click', function() {
    // Validate step 2 fields
    const username = document.getElementById('admin_username').value;
    const password = document.getElementById('admin_password').value;
    const confirmPassword = document.getElementById('admin_password_confirmation').value;
    
    if (!username || !password || !confirmPassword) {
        alert('{{ __("Please fill in all required fields.") }}');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('{{ __("Passwords do not match.") }}');
        return;
    }
    
    if (password.length < 8) {
        alert('{{ __("Password must be at least 8 characters long.") }}');
        return;
    }
    
    showStep(3);
});

// Format activation code input
document.getElementById('activation_code').addEventListener('input', function() {
    let value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
    if (value.length > 15) value = value.substring(0, 15);
    this.value = value;
});

// Session expiration warning (warn 1 hour before 8-hour session expires)
setTimeout(function() {
    if (confirm('{{ __("Your session will expire soon. Would you like to refresh the page to continue?") }}')) {
        window.location.reload();
    }
}, 7 * 60 * 60 * 1000); // 7 hours

// Show helpful message if user gets 419 error
window.addEventListener('beforeunload', function() {
    // Store form data in localStorage to help user recover
    const formData = {
        activation_code: document.getElementById('activation_code').value,
        admin_username: document.getElementById('admin_username').value,
        clinic_phone: document.getElementById('clinic_phone').value,
        clinic_address: document.getElementById('clinic_address').value
    };
    localStorage.setItem('clinic_activation_backup', JSON.stringify(formData));
});

// Restore form data if available (helps with 419 recovery)
document.addEventListener('DOMContentLoaded', function() {
    const backup = localStorage.getItem('clinic_activation_backup');
    if (backup) {
        try {
            const data = JSON.parse(backup);
            if (data.activation_code) document.getElementById('activation_code').value = data.activation_code;
            if (data.admin_username) document.getElementById('admin_username').value = data.admin_username;
            if (data.clinic_phone) document.getElementById('clinic_phone').value = data.clinic_phone;
            if (data.clinic_address) document.getElementById('clinic_address').value = data.clinic_address;
        } catch (e) {
            console.log('Could not restore form data:', e);
        }
    }
});
</script>
@endsection
