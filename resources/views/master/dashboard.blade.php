@extends('layouts.master')

@section('title', __('Master Dashboard'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-crown text-warning me-2"></i>
                        {{ __('ConCure Master Dashboard') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('SaaS Platform Management & Control Center') }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Generate Activation Code') }}
                    </button>
                    <button type="button" class="btn btn-outline-info ms-2" data-bs-toggle="modal" data-bs-target="#activationGuideModal">
                        <i class="fas fa-question-circle me-1"></i>
                        {{ __('Activation Guide') }}
                    </button>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3">
                                        <i class="fas fa-hospital fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Clinics') }}</h6>
                                    <h3 class="mb-0">{{ $stats['total_clinics'] }}</h3>
                                    <small class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ $stats['active_clinics'] }} {{ __('Active') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info text-white rounded-circle p-3">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Users') }}</h6>
                                    <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                                    <small class="text-info">
                                        <i class="fas fa-user-md me-1"></i>
                                        {{ __('Across all clinics') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle p-3">
                                        <i class="fas fa-user-injured fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Patients') }}</h6>
                                    <h3 class="mb-0">{{ $stats['total_patients'] }}</h3>
                                    <small class="text-success">
                                        <i class="fas fa-heartbeat me-1"></i>
                                        {{ __('Platform wide') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning text-white rounded-circle p-3">
                                        <i class="fas fa-key fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Pending Activations') }}</h6>
                                    <h3 class="mb-0">{{ $stats['pending_activations'] }}</h3>
                                    <small class="text-warning">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ __('Awaiting activation') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trial Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                {{ __('Trial Management Overview') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-3">
                                        <div class="display-6 text-info mb-2">{{ $stats['trial_clinics'] }}</div>
                                        <h6 class="text-muted">{{ __('Active Trials') }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3">
                                        <div class="display-6 text-warning mb-2">{{ $stats['expiring_trials'] }}</div>
                                        <h6 class="text-muted">{{ __('Expiring Soon') }}</h6>
                                        <small class="text-warning">({{ __('Next 7 days') }})</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3">
                                        <div class="display-6 text-danger mb-2">{{ $stats['expired_trials'] }}</div>
                                        <h6 class="text-muted">{{ __('Expired Trials') }}</h6>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3">
                                        <a href="{{ route('master.analytics.trials') }}" class="btn btn-info">
                                            <i class="fas fa-chart-line me-1"></i>
                                            {{ __('Trial Analytics') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                {{ __('Quick Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-2 col-md-4 col-6">
                                    <a href="{{ route('master.clinics') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-hospital d-block mb-2 fa-2x"></i>
                                        {{ __('Manage Clinics') }}
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <a href="{{ route('master.activation-codes') }}" class="btn btn-outline-info w-100">
                                        <i class="fas fa-key d-block mb-2 fa-2x"></i>
                                        {{ __('Activation Codes') }}
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <a href="{{ route('master.analytics') }}" class="btn btn-outline-success w-100">
                                        <i class="fas fa-chart-bar d-block mb-2 fa-2x"></i>
                                        {{ __('Analytics') }}
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <a href="{{ route('master.program-features') }}" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-star d-block mb-2 fa-2x"></i>
                                        {{ __('Program Features') }}
                                    </a>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                                        <i class="fas fa-plus d-block mb-2 fa-2x"></i>
                                        {{ __('New Clinic') }}
                                    </button>
                                </div>
                                <div class="col-lg-2 col-md-4 col-6">
                                    <a href="{{ route('master.analytics.trials') }}" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-clock d-block mb-2 fa-2x"></i>
                                        {{ __('Trial Analytics') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Clinics -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('Recent Clinics') }}
                            </h6>
                            <a href="{{ route('master.clinics') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                        </div>
                        <div class="card-body p-0">
                            @if($recentClinics->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentClinics as $clinic)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $clinic->name }}</h6>
                                                <small class="text-muted">{{ $clinic->email }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $clinic->is_active ? 'success' : 'secondary' }}">
                                                    {{ $clinic->is_active ? __('Active') : __('Inactive') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($clinic->created_at)->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-hospital fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No clinics registered yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Activation Codes -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-key me-2"></i>
                                {{ __('Recent Activation Codes') }}
                            </h6>
                            <a href="{{ route('master.activation-codes') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                        </div>
                        <div class="card-body p-0">
                            @if($recentActivations->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($recentActivations->take(5) as $activation)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1 font-monospace">{{ $activation->code }}</h6>
                                                <small class="text-muted">
                                                    {{ __('Created by') }}: {{ $activation->creator_first_name }} {{ $activation->creator_last_name }}
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $activation->is_used ? 'success' : (\Carbon\Carbon::parse($activation->expires_at)->isPast() ? 'danger' : 'warning') }}">
                                                    @if($activation->is_used)
                                                        {{ __('Used') }}
                                                    @elseif(\Carbon\Carbon::parse($activation->expires_at)->isPast())
                                                        {{ __('Expired') }}
                                                    @else
                                                        {{ __('Active') }}
                                                    @endif
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($activation->created_at)->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-key fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No activation codes generated yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Activation Code Modal -->
<div class="modal fade" id="generateCodeModal" tabindex="-1" aria-labelledby="generateCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateCodeModalLabel">
                    <i class="fas fa-key me-2"></i>
                    {{ __('Generate Clinic Activation Code') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="generateCodeForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('Clinic Information') }}
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="clinic_name" class="form-label">{{ __('Clinic Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="clinic_name" name="clinic_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">{{ __('Admin Email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="admin_first_name" class="form-label">{{ __('Admin First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="admin_first_name" name="admin_first_name" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="admin_last_name" class="form-label">{{ __('Admin Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="admin_last_name" name="admin_last_name" required>
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('Subscription Settings') }}
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="max_users" class="form-label">{{ __('Maximum Users') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_users" name="max_users" min="1" max="1000" value="10" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="subscription_months" class="form-label">{{ __('Subscription (Months)') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="subscription_months" name="subscription_months" required>
                                <option value="1">1 {{ __('Month') }}</option>
                                <option value="3">3 {{ __('Months') }}</option>
                                <option value="6">6 {{ __('Months') }}</option>
                                <option value="12" selected>12 {{ __('Months') }}</option>
                                <option value="24">24 {{ __('Months') }}</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ __('Internal notes about this clinic...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-1"></i>
                        {{ __('Generate Code') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ __('Activation Code Generated') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="fas fa-key fa-3x text-success mb-3"></i>
                    <h4>{{ __('Activation Code') }}</h4>
                    <div class="bg-light p-3 rounded">
                        <h2 class="font-monospace text-primary mb-0" id="generatedCode"></h2>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('Share this code with the clinic administrator. It expires in 30 days.') }}
                </div>
                <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard()">
                    <i class="fas fa-copy me-1"></i>
                    {{ __('Copy Code') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Activation Guide Modal -->
<div class="modal fade" id="activationGuideModal" tabindex="-1" aria-labelledby="activationGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="activationGuideModalLabel">
                    <i class="fas fa-question-circle me-2"></i>
                    {{ __('Clinic Activation Guide') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>{{ __('Important:') }}</strong> {{ __('You are a Program Owner with full platform access. The activation codes you generate are for clinic administrators to set up their individual clinics.') }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-crown me-2"></i>
                                    {{ __('Your Role: Program Owner') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-success">✅ {{ __('What you can do:') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('Access Master Dashboard') }}</li>
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('Generate activation codes for clinics') }}</li>
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('Manage all clinics in the platform') }}</li>
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('View analytics and reports') }}</li>
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('Manage platform users') }}</li>
                                        <li><i class="fas fa-check text-success me-2"></i>{{ __('Update software and settings') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-success">
                                    <i class="fas fa-thumbs-up me-2"></i>
                                    {{ __('You are already fully activated and can use all platform features!') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-hospital me-2"></i>
                                    {{ __('Activation Codes: For Clinic Admins') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-info">ℹ️ {{ __('What activation codes do:') }}</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-arrow-right text-info me-2"></i>{{ __('Create new clinics in your platform') }}</li>
                                        <li><i class="fas fa-arrow-right text-info me-2"></i>{{ __('Set up clinic administrator accounts') }}</li>
                                        <li><i class="fas fa-arrow-right text-info me-2"></i>{{ __('Define clinic subscription terms') }}</li>
                                        <li><i class="fas fa-arrow-right text-info me-2"></i>{{ __('Establish user limits and permissions') }}</li>
                                    </ul>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ __('These codes are NOT for you - they are for clinic administrators to register their clinics.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-list-ol me-2"></i>
                                    {{ __('Complete Workflow: How to Onboard a New Clinic') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">1</span>
                                            </div>
                                            <h6 class="mt-2">{{ __('Generate Code') }}</h6>
                                            <p class="small text-muted">{{ __('Click "Generate Activation Code" and fill in clinic details') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">2</span>
                                            </div>
                                            <h6 class="mt-2">{{ __('Share Code') }}</h6>
                                            <p class="small text-muted">{{ __('Send the activation code to the clinic administrator') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center mb-3">
                                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <span class="fw-bold">3</span>
                                            </div>
                                            <h6 class="mt-2">{{ __('Clinic Activates') }}</h6>
                                            <p class="small text-muted">{{ __('Clinic admin uses code at activation page') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3">
                                    <h6><i class="fas fa-link me-2"></i>{{ __('Clinic Activation URL:') }}</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control font-monospace" value="{{ url('/activate-clinic') }}" readonly id="activationUrl">
                                        <button class="btn btn-outline-primary" type="button" onclick="copyActivationUrl()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">{{ __('Share this URL with clinic administrators along with their activation code') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-info">
                                    <i class="fas fa-user-md me-2"></i>
                                    {{ __('For Clinic Administrators') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small">{{ __('When a clinic administrator receives an activation code, they should:') }}</p>
                                <ol class="small">
                                    <li>{{ __('Visit the activation URL') }}</li>
                                    <li>{{ __('Enter the 15-character activation code') }}</li>
                                    <li>{{ __('Create their admin username and password') }}</li>
                                    <li>{{ __('Complete clinic setup') }}</li>
                                    <li>{{ __('Start using ConCure for their clinic') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-success">
                                    <i class="fas fa-chart-line me-2"></i>
                                    {{ __('Monitor Progress') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="small">{{ __('Track activation progress through:') }}</p>
                                <ul class="small">
                                    <li><strong>{{ __('Dashboard:') }}</strong> {{ __('View pending activations') }}</li>
                                    <li><strong>{{ __('Clinics:') }}</strong> {{ __('See all registered clinics') }}</li>
                                    <li><strong>{{ __('Activation Codes:') }}</strong> {{ __('Monitor code usage') }}</li>
                                    <li><strong>{{ __('Analytics:') }}</strong> {{ __('Track platform growth') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>
                    {{ __('Got it!') }}
                </button>
                <a href="{{ url('/activate-clinic') }}" target="_blank" class="btn btn-outline-info">
                    <i class="fas fa-external-link-alt me-1"></i>
                    {{ __('View Activation Page') }}
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('generateCodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Generating...") }}';
    submitBtn.disabled = true;
    
    fetch('{{ route("master.generate-code") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('generatedCode').textContent = data.activation_code;
            bootstrap.Modal.getInstance(document.getElementById('generateCodeModal')).hide();
            new bootstrap.Modal(document.getElementById('successModal')).show();
            this.reset();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

function copyToClipboard() {
    const code = document.getElementById('generatedCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        alert('{{ __("Activation code copied to clipboard!") }}');
    });
}

function copyActivationUrl() {
    const url = document.getElementById('activationUrl').value;
    navigator.clipboard.writeText(url).then(() => {
        alert('{{ __("Activation URL copied to clipboard!") }}');
    });
}
</script>
@endsection
