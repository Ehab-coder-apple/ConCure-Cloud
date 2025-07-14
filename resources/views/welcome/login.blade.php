@extends('layouts.welcome')

@section('title', 'Sign In - ConCure')

@section('content')
<div class="welcome-container">
    <div class="container">
        <div class="form-container mx-auto" style="max-width: 500px;">
            <div class="form-header">
                <h1 class="form-title">
                    <i class="fas fa-sign-in-alt text-primary me-2"></i>
                    Welcome Back
                </h1>
                <p class="form-subtitle">Sign in to access your clinic management dashboard</p>
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

            <form method="POST" action="{{ route('welcome.authenticate') }}">
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
                    <button type="submit" class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Sign In
                    </button>
                </div>

                <!-- Demo Login Options -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-1"></i>
                        Demo Access
                    </h6>
                    <p class="mb-2">Try ConCure with demo accounts:</p>
                    <div class="d-grid gap-2">
                        <a href="/dev/login-admin" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-shield me-1"></i>
                            Demo as Admin
                        </a>
                        <a href="/dev/login-doctor" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-user-md me-1"></i>
                            Demo as Doctor
                        </a>
                    </div>
                </div>

                <!-- Register Link -->
                <div class="text-center">
                    <p class="text-muted">
                        Don't have an account? 
                        <a href="{{ route('welcome.register') }}" class="text-primary text-decoration-none fw-bold">Start Free Trial</a>
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
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        
        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }, 5000);
    });
</script>
@endpush
