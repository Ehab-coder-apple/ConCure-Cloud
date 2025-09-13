@extends('master.layouts.app')

@section('title', 'Edit Subscription - ' . $clinic->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Subscription
                    </h1>
                    <p class="text-muted mb-0">{{ $clinic->name }} - Subscription Settings</p>
                </div>
                <div>
                    <a href="{{ route('master.subscriptions.show', $clinic) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subscription Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master.subscriptions.update', $clinic) }}">
                        @csrf
                        @method('PUT')

                        <!-- Current Plan Info -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Current Plan: Basic Plan
                            </h6>
                            <p class="mb-0">
                                This clinic is currently on the Basic Plan ($29/month) with basic features.
                                Advanced plan management will be available in future updates.
                            </p>
                        </div>

                        <!-- User Limit -->
                        <div class="mb-4">
                            <label for="max_users" class="form-label">
                                <i class="fas fa-users me-2"></i>
                                Maximum Users
                            </label>
                            <input type="number" 
                                   class="form-control @error('max_users') is-invalid @enderror" 
                                   id="max_users" 
                                   name="max_users" 
                                   value="{{ old('max_users', $clinic->max_users) }}" 
                                   min="1" 
                                   max="1000" 
                                   required>
                            <div class="form-text">
                                Current users: {{ $clinic->users->count() }} / {{ $clinic->max_users }}
                            </div>
                            @error('max_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Usage Display -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->users->count() }}</div>
                                        <div class="small text-muted">Active Users</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->patients->count() }}</div>
                                        <div class="small text-muted">Patients</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->prescriptions->count() }}</div>
                                        <div class="small text-muted">Prescriptions</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->appointments->count() }}</div>
                                        <div class="small text-muted">Appointments</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Update Subscription
                                </button>
                                <a href="{{ route('master.subscriptions.show', $clinic) }}" class="btn btn-outline-secondary ms-2">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Plan Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Plan Features</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Configurable user limits
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Unlimited patients
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Prescription management
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Appointment scheduling
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Basic reporting
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Email support
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Coming Soon -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Coming Soon</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Multiple subscription plans
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Plan upgrades/downgrades
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Usage-based billing
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Advanced analytics
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Custom feature limits
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
