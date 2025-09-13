@extends('master.layouts.app')

@section('title', 'Subscription Details - ' . $clinic->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        {{ $clinic->name }} - Subscription
                    </h1>
                    <p class="text-muted mb-0">Subscription ID: {{ $clinic->id }}</p>
                </div>
                <div>
                    <a href="{{ route('master.subscriptions.edit', $clinic) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Edit Plan
                    </a>
                    <a href="{{ route('master.subscriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Subscriptions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Status and Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                @if($clinic->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i>
                                        {{ $subscriptionDetails['status'] }}
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6">
                                        <i class="fas fa-pause-circle me-1"></i>
                                        {{ $subscriptionDetails['status'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="me-4">
                                <span class="badge bg-primary fs-6">{{ $subscriptionDetails['plan'] }}</span>
                            </div>
                            <div>
                                <div class="small text-muted">Next Billing</div>
                                <div>{{ $subscriptionDetails['next_billing']->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-info btn-sm" onclick="alert('Billing management coming soon!')">
                                <i class="fas fa-receipt me-1"></i>
                                View Billing
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_users'] }} / {{ $clinic->max_users }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Patients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_patients'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Prescriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_prescriptions'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-prescription-bottle-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_appointments'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Details and Clinic Information -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subscription Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="font-weight-bold">Plan:</td>
                            <td>{{ $subscriptionDetails['plan'] }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Price:</td>
                            <td>{{ $subscriptionDetails['price'] }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Billing Cycle:</td>
                            <td>{{ $subscriptionDetails['billing_cycle'] }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                @if($clinic->is_active)
                                    <span class="badge bg-success">{{ $subscriptionDetails['status'] }}</span>
                                @else
                                    <span class="badge bg-warning">{{ $subscriptionDetails['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Next Billing:</td>
                            <td>{{ $subscriptionDetails['next_billing']->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Started:</td>
                            <td>{{ $clinic->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Plan Features</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach($subscriptionDetails['features'] as $feature)
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="mt-4">
                        <button class="btn btn-outline-primary btn-sm" onclick="alert('Plan upgrades coming soon!')">
                            <i class="fas fa-arrow-up me-1"></i>
                            Upgrade Plan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clinic Information -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Clinic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Clinic Name:</td>
                                    <td>{{ $clinic->name }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Email:</td>
                                    <td>{{ $clinic->email ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Phone:</td>
                                    <td>{{ $clinic->phone ?? 'Not provided' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold">Address:</td>
                                    <td>{{ $clinic->address ?? 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Registration Date:</td>
                                    <td>{{ $clinic->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Activation Code:</td>
                                    <td class="font-family-monospace">{{ $clinic->activation_code }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-hospital me-1"></i>
                            View Full Clinic Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coming Soon Notice -->
    <div class="alert alert-info">
        <h6 class="alert-heading">
            <i class="fas fa-info-circle me-2"></i>
            Advanced Billing Features Coming Soon
        </h6>
        <p class="mb-0">
            Full billing integration, payment history, invoices, and automated billing management 
            will be available in future updates.
        </p>
    </div>
</div>
@endsection
