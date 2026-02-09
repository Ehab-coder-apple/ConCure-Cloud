<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Your Clinic - ConCure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .registration-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 800px;
            margin: 0 auto;
        }
        .registration-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .form-section {
            border-left: 4px solid #667eea;
            padding-left: 1rem;
            margin-bottom: 2rem;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .feature-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
            color: #6c757d;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <div class="registration-header">
                <i class="fas fa-hospital fa-3x mb-3"></i>
                <h2 class="mb-0">Register Your Clinic</h2>
                <p class="mb-0 mt-2">Join ConCure and start managing your clinic today</p>
            </div>
            
            <div class="p-4">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                    <div class="step">3</div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('clinic-registration.register') }}" id="registrationForm">
                    @csrf
                    
                    <!-- Clinic Information Section -->
                    <div class="form-section">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-hospital me-2"></i>
                            Clinic Information
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clinic_name" class="form-label">Clinic Name *</label>
                                <input type="text" 
                                       class="form-control @error('clinic_name') is-invalid @enderror" 
                                       id="clinic_name" 
                                       name="clinic_name" 
                                       value="{{ old('clinic_name') }}" 
                                       required
                                       placeholder="Enter your clinic name">
                                @error('clinic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="clinic_email" class="form-label">Clinic Email *</label>
                                <input type="email" 
                                       class="form-control @error('clinic_email') is-invalid @enderror" 
                                       id="clinic_email" 
                                       name="clinic_email" 
                                       value="{{ old('clinic_email') }}" 
                                       required
                                       placeholder="clinic@example.com">
                                @error('clinic_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clinic_phone" class="form-label">Phone Number</label>
                                <input type="text" 
                                       class="form-control @error('clinic_phone') is-invalid @enderror" 
                                       id="clinic_phone" 
                                       name="clinic_phone" 
                                       value="{{ old('clinic_phone') }}"
                                       placeholder="+1 (555) 123-4567">
                                @error('clinic_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="clinic_address" class="form-label">Address</label>
                                <input type="text" 
                                       class="form-control @error('clinic_address') is-invalid @enderror" 
                                       id="clinic_address" 
                                       name="clinic_address" 
                                       value="{{ old('clinic_address') }}"
                                       placeholder="Enter clinic address">
                                @error('clinic_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Admin User Information Section -->
                    <div class="form-section">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-user-shield me-2"></i>
                            Administrator Account
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This will be your main administrator account for managing the clinic.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_first_name" class="form-label">First Name *</label>
                                <input type="text" 
                                       class="form-control @error('admin_first_name') is-invalid @enderror" 
                                       id="admin_first_name" 
                                       name="admin_first_name" 
                                       value="{{ old('admin_first_name') }}" 
                                       required
                                       placeholder="Enter your first name">
                                @error('admin_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="admin_last_name" class="form-label">Last Name *</label>
                                <input type="text" 
                                       class="form-control @error('admin_last_name') is-invalid @enderror" 
                                       id="admin_last_name" 
                                       name="admin_last_name" 
                                       value="{{ old('admin_last_name') }}" 
                                       required
                                       placeholder="Enter your last name">
                                @error('admin_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control @error('admin_email') is-invalid @enderror" 
                                       id="admin_email" 
                                       name="admin_email" 
                                       value="{{ old('admin_email') }}" 
                                       required
                                       placeholder="your@email.com">
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="admin_password" class="form-label">Password *</label>
                                <input type="password" 
                                       class="form-control @error('admin_password') is-invalid @enderror" 
                                       id="admin_password" 
                                       name="admin_password" 
                                       required
                                       minlength="8"
                                       placeholder="Enter secure password">
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="admin_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="admin_password_confirmation" 
                                   name="admin_password_confirmation" 
                                   required
                                   minlength="8"
                                   placeholder="Confirm your password">
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-section">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-file-contract me-2"></i>
                            Terms & Conditions
                        </h5>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('terms_accepted') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="terms_accepted" 
                                       name="terms_accepted" 
                                       value="1"
                                       {{ old('terms_accepted') ? 'checked' : '' }}>
                                <label class="form-check-label" for="terms_accepted">
                                    I agree to the <a href="#" target="_blank">Terms and Conditions</a> *
                                </label>
                                @error('terms_accepted')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('privacy_accepted') is-invalid @enderror" 
                                       type="checkbox" 
                                       id="privacy_accepted" 
                                       name="privacy_accepted" 
                                       value="1"
                                       {{ old('privacy_accepted') ? 'checked' : '' }}>
                                <label class="form-check-label" for="privacy_accepted">
                                    I agree to the <a href="#" target="_blank">Privacy Policy</a> *
                                </label>
                                @error('privacy_accepted')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-register btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            Register Clinic
                        </button>
                    </div>
                </form>

                <!-- Additional Links -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Already have an account? 
                        <a href="{{ url('/') }}" class="text-decoration-none">Login here</a>
                    </p>
                    <p class="text-muted">
                        <a href="{{ route('clinic-registration.check-status') }}" class="text-decoration-none">
                            <i class="fas fa-search me-1"></i>
                            Check Registration Status
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
