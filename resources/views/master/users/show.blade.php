@extends('master.layouts.app')

@section('title', 'User Details - ' . $user->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ $user->full_name }}
                    </h1>
                    <p class="text-muted mb-0">User ID: {{ $user->id }} | @{{ $user->username }}</p>
                </div>
                <div>
                    <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Users
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
                                @if($user->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times-circle me-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <div class="me-4">
                                <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'doctor' ? 'success' : 'secondary') }} fs-6">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            <div>
                                <div class="small text-muted">Member since</div>
                                <div>{{ $user->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            @if($user->is_active)
                                <form method="POST" action="{{ route('master.users.deactivate', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm"
                                            onclick="return confirm('Are you sure you want to deactivate this user?')">
                                        <i class="fas fa-pause me-1"></i>
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('master.users.activate', $user) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-play me-1"></i>
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="font-weight-bold">Full Name:</td>
                            <td>{{ $user->full_name }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Username:</td>
                            <td>@{{ $user->username }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Role:</td>
                            <td>
                                <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'doctor' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Status:</td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Created:</td>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($user->activated_at)
                        <tr>
                            <td class="font-weight-bold">Activated:</td>
                            <td>{{ $user->activated_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($user->last_login_at)
                        <tr>
                            <td class="font-weight-bold">Last Login:</td>
                            <td>{{ $user->last_login_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Clinic Information</h6>
                </div>
                <div class="card-body">
                    @if($user->clinic)
                        <table class="table table-borderless">
                            <tr>
                                <td class="font-weight-bold">Clinic Name:</td>
                                <td>{{ $user->clinic->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Clinic ID:</td>
                                <td>{{ $user->clinic->id }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Clinic Email:</td>
                                <td>{{ $user->clinic->email ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Clinic Phone:</td>
                                <td>{{ $user->clinic->phone ?? 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Clinic Status:</td>
                                <td>
                                    @if($user->clinic->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Max Users:</td>
                                <td>{{ $user->clinic->max_users }}</td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <a href="{{ route('master.clinics.show', $user->clinic) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-hospital me-1"></i>
                                View Clinic Details
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Clinic Assigned</h6>
                            <p class="text-muted small">This user is not associated with any clinic.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Statistics -->
    @if($user->clinic)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-primary">{{ $stats['patients_created'] }}</div>
                                <div class="text-muted small">Patients in Clinic</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-success">{{ $stats['prescriptions_created'] }}</div>
                                <div class="text-muted small">Prescriptions in Clinic</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-info">{{ $stats['appointments_created'] }}</div>
                                <div class="text-muted small">Appointments in Clinic</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-warning">{{ $stats['account_age'] }}</div>
                                <div class="text-muted small">Account Age</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Created By Information -->
    @if($user->createdBy)
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Creation</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Created by <strong>{{ $user->createdBy->full_name }}</strong> 
                        ({{ $user->createdBy->email }}) 
                        on {{ $user->created_at->format('M d, Y \a\t H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
