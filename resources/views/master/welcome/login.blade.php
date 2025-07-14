@extends('layouts.master-welcome')

@section('title', 'Master Control Login - ConCure')

@section('content')
<div class="master-container">
    <div class="container">
        <div class="form-container mx-auto" style="max-width: 500px;">
            <div class="form-header">
                <div class="master-badge">
                    <i class="fas fa-shield-alt me-1"></i>
                    Master Control Access
                </div>
                <h1 class="form-title">
                    <i class="fas fa-sign-in-alt text-primary me-2"></i>
                    Welcome Back
                </h1>
                <p class="form-subtitle">Sign in to access the ConCure master control panel</p>
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

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('master.welcome.authenticate') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="Enter your email address" required autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" 
                                style="border: none; background: none; padding: 0 15px;" onclick="togglePassword()">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="#" class="text-primary text-decoration-none">Forgot Password?</a>
                    </div>
                </div>

                <div class="d-grid gap-2 mb-4">
                    <button type="submit" class="btn btn-primary-master btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Access Control Panel
                    </button>
                </div>

                <!-- Access Levels Info -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-1"></i>
                        Access Levels
                    </h6>
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">
                                <strong>Program Owner:</strong> Full platform control<br>
                                <strong>Platform Admin:</strong> Administrative access<br>
                                <strong>Support Agent:</strong> Customer support access
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-muted">
                        Need to register as Program Owner? 
                        <a href="{{ route('master.welcome.register') }}" class="text-primary text-decoration-none fw-bold">Create Account</a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Quick Access -->
        <div class="text-center mt-4">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                        <div class="card-body text-white">
                            <h6 class="card-title">
                                <i class="fas fa-users me-1"></i>
                                Need Access?
                            </h6>
                            <p class="card-text small mb-3">
                                Contact your Program Owner to get invited as a team member with appropriate access level.
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-crown me-1"></i>
                                    Program Owner
                                </span>
                                <span class="badge bg-primary">
                                    <i class="fas fa-user-shield me-1"></i>
                                    Platform Admin
                                </span>
                                <span class="badge bg-success">
                                    <i class="fas fa-headset me-1"></i>
                                    Support Agent
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Auto-focus on email field
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) {
            emailInput.focus();
        }
    });

    // Form submission loading state
    document.querySelector('form').addEventListener('submit', function() {
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Authenticating...';
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 5000);
    });
</script>
@endpush
