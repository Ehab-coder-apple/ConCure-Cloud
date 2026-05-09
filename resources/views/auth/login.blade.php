@extends('layouts.guest')

@section('content')
<div class="login-container">
    <div class="login-background">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>

    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-card-header">
                        <div class="brand-logo">
                            <div class="logo-icon">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h2 class="brand-name">{{ config('app.name') }}</h2>
                            <p class="brand-subtitle">Professional Clinic Management</p>
                        </div>
                    </div>

                    <div class="login-card-body">
                        <div class="login-header-text">
                            <h3 class="login-title">Welcome Back</h3>
                            <p class="login-subtitle">Sign in to your clinic dashboard</p>
                        </div>

                        @if(session('auto_logout_reason'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-clock me-2"></i>
                                <strong>Session Expired</strong>
                                @if(session('auto_logout_reason') === 'inactivity')
                                    <p class="mb-0">You were automatically logged out due to inactivity. This is a security measure to protect sensitive medical data.</p>
                                @elseif(session('auto_logout_reason') === 'session_expired')
                                    <p class="mb-0">Your session has expired. Please log in again to continue.</p>
                                @else
                                    <p class="mb-0">You have been logged out. Please log in again to continue.</p>
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('session_terminated'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 4px solid #0d6efd; background-color: #e7f3ff;" id="sessionTerminatedAlert">
                                <i class="fas fa-shield-alt me-2" style="color: #0d6efd; font-size: 1.2em;"></i>
                                <strong style="color: #0d6efd;" id="alertTitle">Security Alert</strong>
                                <p class="mb-0" style="margin-top: 8px; color: #333;" id="alertMessage">
                                    For your security, you have been logged out because your account was accessed from another device.
                                </p>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <script>
                                // Check if user's locale is Arabic
                                document.addEventListener('DOMContentLoaded', function() {
                                    var alertTitle = document.getElementById('alertTitle');
                                    var alertMessage = document.getElementById('alertMessage');
                                    var alert = document.getElementById('sessionTerminatedAlert');
                                    var isArabic = false;

                                    // Check 1: HTML lang attribute
                                    if (document.documentElement.lang === 'ar') {
                                        isArabic = true;
                                    }

                                    // Check 2: Check if HTML dir is rtl
                                    if (document.documentElement.dir === 'rtl') {
                                        isArabic = true;
                                    }

                                    // Check 3: PHP locale value
                                    var locale = '{{ app()->getLocale() }}';
                                    if (locale === 'ar') {
                                        isArabic = true;
                                    }

                                    if (isArabic) {
                                        alertTitle.textContent = 'تنبيه أمني';
                                        alertMessage.textContent = 'حرصاً على حماية بيانات العيادة والمرضى، تم إنهاء هذه الجلسة لأن الحساب تم فتحه على جهاز آخر.';
                                        if (alert) {
                                            alert.dir = 'rtl';
                                            alert.style.textAlign = 'right';
                                        }
                                    }

                                    if (alert) {
                                        // Scroll alert into view
                                        setTimeout(function() {
                                            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }, 100);
                                    }
                                });
                            </script>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" class="login-form needs-validation" novalidate id="loginForm">
                            @csrf

                            <!-- Username -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i> Username or Email
                                </label>
                                <div class="input-wrapper">
                                    <input id="username" type="text"
                                           class="form-control @error('username') is-invalid @enderror"
                                           name="username" value="{{ old('username') }}" required autofocus
                                           placeholder="Enter your username or email">
                                    <div class="input-focus-line"></div>
                                </div>
                                @error('username')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <div class="input-wrapper">
                                    <div class="password-input-group">
                                        <input id="password" type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               name="password" required autocomplete="current-password"
                                               placeholder="Enter your password">
                                        <button type="button" class="password-toggle" id="togglePasswordAuth" aria-label="Show password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="input-focus-line"></div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="form-group remember-group">
                                <div class="custom-checkbox">
                                    <input class="checkbox-input" type="checkbox" name="remember" id="remember"
                                           {{ old('remember') ? 'checked' : '' }}>
                                    <label class="checkbox-label" for="remember">
                                        <span class="checkbox-custom"></span>
                                        <span class="checkbox-text">Remember me for 30 days</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group">
                                <button type="submit" class="btn-login" id="loginButton">
                                    <span class="btn-text">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Sign In to Dashboard
                                    </span>
                                    <div class="btn-loading">
                                        <div class="spinner"></div>
                                        <span>Signing in...</span>
                                    </div>
                                </button>
                            </div>
                        </form>

                        <!-- Additional Options -->
                        <div class="login-footer">
                            <div class="divider">
                                <span>or</span>
                            </div>

                            <div class="access-links">
                                <a href="{{ route('master.login') }}" class="access-link master-access">
                                    <i class="fas fa-crown"></i>
                                    <span>Master Admin Access</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>

                            <!-- Demo Access -->
                            @if(config('app.env') === 'local')
                            <div class="demo-section">
                                <div class="demo-header">
                                    <i class="fas fa-flask"></i>
                                    <span>Demo Access</span>
                                </div>
                                <div class="demo-buttons">
                                    <a href="/dev/login-admin" class="demo-btn admin">
                                        <i class="fas fa-user-shield"></i>
                                        <span>Admin Demo</span>
                                    </a>
                                    <a href="/dev/login-doctor" class="demo-btn doctor">
                                        <i class="fas fa-user-md"></i>
                                        <span>Doctor Demo</span>
                                    </a>
                                </div>
                                <div class="demo-credentials">
                                    <small>Admin: admin/admin123 • Doctor: doctor/doctor123</small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="login-page-footer">
                    <div class="footer-content">
                        <p class="powered-by">
                            <i class="fas fa-heart"></i>
                            Powered by <strong>{{ $companyName ?? 'ConCure' }}</strong>
                        </p>
                        <div class="footer-links">
                            <a href="#" class="footer-link">Privacy Policy</a>
                            <a href="#" class="footer-link">Terms of Service</a>
                            <a href="#" class="footer-link">Support</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
