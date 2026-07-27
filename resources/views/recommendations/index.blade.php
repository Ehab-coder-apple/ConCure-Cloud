@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-prescription text-primary"></i>
                    Recommendations Dashboard
                </h1>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Prescriptions</h5>
                            <h2 class="mb-0">{{ $recentPrescriptions->where('status', 'active')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pills fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Pending Lab Requests</h5>
                            <h2 class="mb-0">{{ $recentLabRequests->where('status', 'pending')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-flask fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title">Active Diet Plans</h5>
                            <h2 class="mb-0">{{ $recentDietPlans->where('status', 'active')->count() }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-apple-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    @can('create-prescriptions')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('recommendations.prescriptions') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-prescription-bottle-alt"></i>
                                <br>Create Prescription
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-warning btn-lg w-100">
                                <i class="fas fa-vial"></i>
                                <br>Request Lab Tests
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('recommendations.diet-plans') }}" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-utensils"></i>
                                <br>Create Diet Plan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Prescriptions -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-pills text-primary"></i>
                        Recent Prescriptions
                    </h6>
                    <a href="{{ route('recommendations.prescriptions') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentPrescriptions as $prescription)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $prescription->patient->full_name }}</h6>
                                <small class="text-muted">{{ $prescription->prescription_number }}</small>
                                <br>
                                <span class="{{ $prescription->status_badge_class }}">
                                    {{ $prescription->status_display }}
                                </span>
                            </div>
                            <small class="text-muted">
                                {{ $prescription->prescribed_date->format('M d') }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-prescription fa-2x mb-2"></i>
                        <p class="mb-0">No recent prescriptions</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Lab Requests -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-flask text-warning"></i>
                        Recent Lab Requests
                    </h6>
                    <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-sm btn-outline-warning">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentLabRequests as $labRequest)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $labRequest->patient->full_name }}</h6>
                                <small class="text-muted">{{ $labRequest->request_number }}</small>
                                <br>
                                <span class="{{ $labRequest->status_badge_class }}">
                                    {{ $labRequest->status_display }}
                                </span>
                                @if($labRequest->priority !== 'normal')
                                <span class="{{ $labRequest->priority_badge_class }}">
                                    {{ $labRequest->priority_display }}
                                </span>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $labRequest->requested_date->format('M d') }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-flask fa-2x mb-2"></i>
                        <p class="mb-0">No recent lab requests</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Diet Plans -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-apple-alt text-success"></i>
                        Recent Diet Plans
                    </h6>
                    <a href="{{ route('recommendations.diet-plans') }}" class="btn btn-sm btn-outline-success">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentDietPlans as $dietPlan)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $dietPlan->patient->full_name }}</h6>
                                <small class="text-muted">{{ $dietPlan->title }}</small>
                                <br>
                                <span class="{{ $dietPlan->status_badge_class }}">
                                    {{ $dietPlan->status_display }}
                                </span>
                                <small class="text-muted d-block">
                                    Goal: {{ $dietPlan->goal_display }}
                                </small>
                            </div>
                            <small class="text-muted">
                                {{ $dietPlan->start_date->format('M d') }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-apple-alt fa-2x mb-2"></i>
                        <p class="mb-0">No recent diet plans</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
