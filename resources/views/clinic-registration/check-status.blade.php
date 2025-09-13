<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Registration Status - ConCure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .status-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
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
        .btn-check {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-check:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-container">
            <div class="status-header">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h2 class="mb-0">Check Registration Status</h2>
                <p class="mb-0 mt-2">Enter your details to check your clinic registration status</p>
            </div>
            
            <div class="p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('clinic-registration.status') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>
                            Clinic Email Address
                        </label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required
                               placeholder="Enter the email used for registration">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="activation_code" class="form-label">
                            <i class="fas fa-key me-2"></i>
                            Activation Code
                        </label>
                        <input type="text" 
                               class="form-control @error('activation_code') is-invalid @enderror" 
                               id="activation_code" 
                               name="activation_code" 
                               value="{{ old('activation_code') }}" 
                               required
                               placeholder="Enter your activation code"
                               style="text-transform: uppercase;">
                        @error('activation_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            You can find your activation code in the confirmation email
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-check">
                            <i class="fas fa-search me-2"></i>
                            Check Status
                        </button>
                    </div>
                </form>

                <!-- Information Box -->
                <div class="info-box">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-question-circle me-2"></i>
                        Need Help?
                    </h6>
                    <div class="small text-muted">
                        <p class="mb-2">
                            <strong>Can't find your activation code?</strong><br>
                            Check your email inbox and spam folder for the confirmation email sent after registration.
                        </p>
                        <p class="mb-2">
                            <strong>Registration taking too long?</strong><br>
                            Our team typically reviews applications within 24-48 hours during business days.
                        </p>
                        <p class="mb-0">
                            <strong>Still need assistance?</strong><br>
                            Contact our support team at support@concure.com or call +1 (555) 123-4567
                        </p>
                    </div>
                </div>

                <!-- Additional Links -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        Don't have an account yet? 
                        <a href="{{ route('clinic-registration.form') }}" class="text-decoration-none">
                            Register your clinic
                        </a>
                    </p>
                    <p class="text-muted">
                        <a href="{{ url('/') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>
                            Back to Home
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-uppercase activation code
        document.getElementById('activation_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
