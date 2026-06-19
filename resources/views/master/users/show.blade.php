@extends('master.layouts.app')

@section('title', 'Super Admin Details - ' . $user->full_name)

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
                    <a href="{{ route('master.users.edit', $user) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Edit
                    </a>
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
                                    {{ \App\Models\User::ROLES[$user->role] ?? ucfirst($user->role) }}
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
                                    {{ \App\Models\User::ROLES[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                        </tr>
                        @if($user->title_prefix)
                        <tr>
                            <td class="font-weight-bold">Title/Prefix:</td>
                            <td>{{ $user->title_prefix }}</td>
                        </tr>
                        @endif
                        @if($user->scientific_degree)
                        <tr>
                            <td class="font-weight-bold">Scientific Degree:</td>
                            <td>{{ $user->scientific_degree }}</td>
                        </tr>
                        @endif
                        @if($user->educational_institution)
                        <tr>
                            <td class="font-weight-bold">Educational Institution:</td>
                            <td>{{ $user->educational_institution }}</td>
                        </tr>
                        @endif
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
                    <h6 class="m-0 font-weight-bold text-primary">Allocated Clinics</h6>
                </div>
                <div class="card-body">
                    @if($user->superAdminClinics->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($user->superAdminClinics as $clinic)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $clinic->name }}</div>
                                            <div class="small text-muted">Clinic ID: {{ $clinic->id }}@if($clinic->city) · {{ $clinic->city }}@endif</div>
                                        </div>
                                        <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-hospital me-1"></i>
                                            View
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Clinics Assigned</h6>
                            <p class="text-muted small">This Super Admin does not currently have any clinic allocations.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Statistics -->
    @if($user->superAdminClinics->count() > 0)
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
                                <div class="h4 mb-0 font-weight-bold text-primary">{{ $stats['allocated_clinics'] }}</div>
                                <div class="text-muted small">Allocated Clinics</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-success">{{ $stats['clinic_users'] }}</div>
                                <div class="text-muted small">Users in Scope</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0 font-weight-bold text-info">{{ $stats['clinic_patients'] }}</div>
                                <div class="text-muted small">Patients in Scope</div>
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
