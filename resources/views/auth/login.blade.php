@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Logo/Header -->
                    <div class="text-center mb-4">
                        <i class="fas fa-clinic-medical fa-3x text-primary mb-3"></i>
                        <h2 class="h4 text-primary fw-bold">{{ config('app.name') }}</h2>
                        <p class="text-muted">Clinic Management System</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <input id="username" type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   name="username" value="{{ old('username') }}" 
                                   required autocomplete="username" autofocus>
                            @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary" id="togglePasswordAuth" aria-label="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @push('scripts')
                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const input = document.getElementById('password');
                            const btn = document.getElementById('togglePasswordAuth');
                            if (input && btn) {
                                btn.addEventListener('click', function () {
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
                        });
                        </script>
                        @endpush

                        <!-- Remember Me -->
                        <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" 
                                   {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>

                    <!-- Registration disabled: accounts are created by master admin -->

                    <!-- Demo Access -->
                    @if(config('app.env') === 'local')
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i> Demo Access</h6>
                        <div class="d-grid gap-2 mb-2">
                            <a href="/dev/login-admin" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user-shield me-1"></i>
                                Demo as Admin
                            </a>
                            <a href="/dev/login-doctor" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-user-md me-1"></i>
                                Demo as Doctor
                            </a>
                        </div>
                        <small class="text-muted d-block">
                            <strong>Admin:</strong> admin / admin123 &nbsp; • &nbsp;
                            <strong>Doctor:</strong> doctor / doctor123
                        </small>
                    </div>
                    @endif
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
@endsection
