@extends('layouts.guest')

@section('title', __('Welcome to ConCure SaaS'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="display-3 text-white mb-3">
                    <i class="fas fa-hospital me-3"></i>
                    {{ __('ConCure SaaS') }}
                </h1>
                <p class="lead text-white-50">{{ __('Professional Multi-Tenant Clinic Management Platform') }}</p>
            </div>

            <!-- Access Cards -->
            <div class="row g-4 mb-5">
                <!-- Master Dashboard -->
                <div class="col-md-6">
                    <div class="card h-100 border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <i class="fas fa-crown fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ __('Master Dashboard') }}</h4>
                            <small>{{ __('Platform Administration') }}</small>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="text-danger mb-3">{{ __('SaaS Platform Control') }}</h5>
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Manage all clinics') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Generate activation codes') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Platform analytics') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Subscription management') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('System monitoring') }}
                                </li>
                            </ul>
                            <div class="d-grid">
                                <a href="{{ route('master.login') }}" class="btn btn-danger btn-lg">
                                    <i class="fas fa-crown me-2"></i>
                                    {{ __('Master Login') }}
                                </a>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">{{ __('Program owners only') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinic Dashboard -->
                <div class="col-md-6">
                    <div class="card h-100 border-primary">
                        <div class="card-header bg-primary text-white text-center">
                            <i class="fas fa-user-md fa-2x mb-2"></i>
                            <h4 class="mb-0">{{ __('Clinic Dashboard') }}</h4>
                            <small>{{ __('Clinic Management') }}</small>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="text-primary mb-3">{{ __('Clinic Operations') }}</h5>
                            <ul class="list-unstyled text-start mb-4">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Patient management') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Prescription system') }}
                                </li>

                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Appointment scheduling') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Staff management') }}
                                </li>
                            </ul>
                            <div class="d-grid">
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-hospital me-2"></i>
                                    {{ __('Clinic Login') }}
                                </a>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">{{ __('Clinic staff & administrators') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="row g-4 mb-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header text-center">
                            <h5 class="mb-0">
                                <i class="fas fa-star text-warning me-2"></i>
                                {{ __('Platform Features') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-cloud fa-3x text-info mb-3"></i>
                                    <h6>{{ __('Cloud-Based') }}</h6>
                                    <p class="text-muted small">{{ __('Access from anywhere with secure cloud infrastructure') }}</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                                    <h6>{{ __('Secure & Compliant') }}</h6>
                                    <p class="text-muted small">{{ __('HIPAA compliant with enterprise-grade security') }}</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                    <h6>{{ __('Multi-Tenant') }}</h6>
                                    <p class="text-muted small">{{ __('Complete data isolation between clinics') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-key fa-2x text-warning mb-2"></i>
                            <h6>{{ __('Have an Activation Code?') }}</h6>
                            <a href="{{ route('clinic.activate.form') }}" class="btn btn-outline-warning btn-sm">
                                {{ __('Activate Clinic') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                            <h6>{{ __('New Clinic?') }}</h6>
                            <a href="{{ route('clinic.register.form') }}" class="btn btn-outline-info btn-sm">
                                {{ __('Request Access') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-headset fa-2x text-success mb-2"></i>
                            <h6>{{ __('Need Help?') }}</h6>
                            <a href="mailto:support@concure.com" class="btn btn-outline-success btn-sm">
                                {{ __('Contact Support') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="text-success mb-1">
                                        <i class="fas fa-check-circle me-2"></i>
                                        {{ __('System Status: All Services Operational') }}
                                    </h6>
                                    <small class="text-muted">{{ __('Last updated') }}: {{ now()->format('M d, Y H:i') }} UTC</small>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-center gap-3">
                                        <div class="text-center">
                                            <div class="badge bg-success rounded-pill">99.9%</div>
                                            <small class="d-block text-muted">{{ __('Uptime') }}</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="badge bg-info rounded-pill">&lt;50ms</div>
                                            <small class="d-block text-muted">{{ __('Response') }}</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="badge bg-primary rounded-pill">24/7</div>
                                            <small class="d-block text-muted">{{ __('Support') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
