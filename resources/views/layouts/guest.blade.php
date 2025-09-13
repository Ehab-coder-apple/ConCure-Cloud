<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ConCure') - {{ config('app.name', 'ConCure') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #20b2aa;
            --primary-dark: #1a9b94;
            --primary-light: #4dc4bd;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b6b;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Enhanced Login Styles */
        .login-container {
            position: relative;
            min-height: 100vh;
        }

        .login-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #20b2aa 100%);
            z-index: -1;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            bottom: 30%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .login-card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            text-align: center;
            position: relative;
        }

        .login-card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .brand-logo {
            position: relative;
            z-index: 1;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: transform 0.3s ease;
        }

        .logo-icon:hover {
            transform: scale(1.1) rotate(5deg);
        }

        .logo-icon i {
            font-size: 2rem;
            color: white;
        }

        .brand-name {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .brand-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            margin: 0;
        }

        .login-card-body {
            padding: 2.5rem;
        }

        .login-header-text {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-light);
            font-size: 0.9rem;
            margin: 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-header {
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }

        /* Enhanced Form Styles */
        .login-form {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .form-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
            background: white;
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .input-focus-line {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            transition: width 0.3s ease;
            border-radius: 1px;
        }

        .form-control:focus + .input-focus-line {
            width: 100%;
        }

        .password-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(32, 178, 170, 0.1);
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding-left: 0.5rem;
        }

        .invalid-feedback i {
            margin-right: 0.25rem;
        }

        /* Custom Checkbox */
        .remember-group {
            margin: 1.5rem 0;
        }

        .custom-checkbox {
            display: flex;
            align-items: center;
        }

        .checkbox-input {
            display: none;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .checkbox-custom {
            width: 20px;
            height: 20px;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            margin-right: 0.75rem;
            position: relative;
            transition: all 0.3s ease;
            background: white;
        }

        .checkbox-input:checked + .checkbox-label .checkbox-custom {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkbox-input:checked + .checkbox-label .checkbox-custom::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .checkbox-text {
            user-select: none;
        }

        /* Enhanced Button Styles */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(32, 178, 170, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-text {
            display: flex;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        .btn-text i {
            margin-right: 0.5rem;
        }

        .btn-loading {
            position: absolute;
            display: flex;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-login.loading .btn-text {
            opacity: 0;
        }

        .btn-login.loading .btn-loading {
            opacity: 1;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Login Footer Styles */
        .login-footer {
            margin-top: 2rem;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .access-links {
            margin-bottom: 1.5rem;
        }

        .access-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .access-link:hover {
            border-color: var(--primary-color);
            background: rgba(32, 178, 170, 0.05);
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .access-link.master-access {
            border-color: #dc3545;
            color: #dc3545;
        }

        .access-link.master-access:hover {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.05);
            color: #dc3545;
        }

        .demo-section {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .demo-header {
            display: flex;
            align-items: center;
            color: #856404;
            font-weight: 600;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .demo-header i {
            margin-right: 0.5rem;
        }

        .demo-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .demo-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border: 1px solid rgba(255, 193, 7, 0.5);
            border-radius: 8px;
            text-decoration: none;
            color: #856404;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .demo-btn:hover {
            background: rgba(255, 193, 7, 0.2);
            color: #856404;
        }

        .demo-btn i {
            margin-right: 0.25rem;
        }

        .demo-credentials {
            text-align: center;
        }

        .demo-credentials small {
            color: #6c757d;
            font-size: 0.75rem;
        }

        .login-page-footer {
            margin-top: 2rem;
            text-align: center;
        }

        .footer-content {
            color: rgba(255, 255, 255, 0.8);
        }

        .powered-by {
            margin: 0 0 1rem 0;
            font-size: 0.9rem;
        }

        .powered-by i {
            color: #ff6b6b;
            margin-right: 0.5rem;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: white;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(32, 178, 170, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
        }

        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(32, 178, 170, 0.25);
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light)) !important;
        }

        .border-primary {
            border-color: var(--primary-color) !important;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .medical-icon {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .step {
            transition: all 0.3s ease;
        }

        .step.d-none {
            opacity: 0;
            transform: translateX(20px);
        }

        .step:not(.d-none) {
            opacity: 1;
            transform: translateX(0);
        }

        /* RTL Support */
        [dir="rtl"] {
            text-align: right;
        }

        [dir="rtl"] .medical-icon {
            margin-right: 0;
            margin-left: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .card {
                margin: 10px 0;
            }
            
            .display-4 {
                font-size: 2rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-hospital medical-icon"></i>
                ConCure
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="{{ route('login') }}">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    {{ __('Login') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white mb-0">
                        &copy; {{ date('Y') }} ConCure. {{ __('All rights reserved.') }}
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="text-white">
                        <a href="mailto:support@concure.com" class="text-white text-decoration-none me-3">
                            <i class="fas fa-envelope me-1"></i>
                            {{ __('Support') }}
                        </a>
                        <a href="tel:+1-555-CONCURE" class="text-white text-decoration-none">
                            <i class="fas fa-phone me-1"></i>
                            {{ __('Call Us') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Form validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + '{{ __("Processing...") }}';
                        submitBtn.disabled = true;
                        
                        // Re-enable after 10 seconds as fallback
                        setTimeout(function() {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 10000);
                    }
                });
            });
        });
    </script>

    <!-- Global CSRF Error Handler -->
    <script>
    // Global error handler for CSRF token issues
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('419')) {
            if (confirm('{{ __("Session expired. Would you like to refresh the page?") }}')) {
                window.location.reload();
            }
        }
    });

    // Handle fetch errors globally
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                if (response.status === 419) {
                    if (confirm('{{ __("Session expired. Would you like to refresh the page?") }}')) {
                        window.location.reload();
                    }
                }
                return response;
            })
            .catch(error => {
                console.error('Fetch error:', error);
                throw error;
            });
    };

    // Enhanced Login Functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('togglePasswordAuth');

        if (passwordInput && passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                const icon = passwordToggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
                passwordToggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }

        // Enhanced form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');

        if (loginForm && loginButton) {
            loginForm.addEventListener('submit', function(e) {
                // Add loading state
                loginButton.classList.add('loading');
                loginButton.disabled = true;

                // Remove loading state after 5 seconds (fallback)
                setTimeout(() => {
                    loginButton.classList.remove('loading');
                    loginButton.disabled = false;
                }, 5000);
            });
        }

        // Input focus animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });

            // Check if input has value on load
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        });

        // Form validation feedback
        const form = document.querySelector('.needs-validation');
        if (form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        }

        // Smooth animations for cards
        const cards = document.querySelectorAll('.login-card, .card');
        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    });
    </script>

    @stack('scripts')
</body>
</html>
