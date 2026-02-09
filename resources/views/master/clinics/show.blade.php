@extends('master.layouts.app')

@section('title', 'Clinic Details - ' . $clinic->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-hospital me-2"></i>
                        {{ $clinic->name }}
                    </h1>
                    <p class="text-muted mb-0">Clinic ID: {{ $clinic->id }}</p>
                </div>
                <div>
                    <a href="{{ route('master.clinics.edit', $clinic) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>
                        Edit Clinic
                    </a>
                    <a href="{{ route('master.clinics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Clinics
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
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6">
                                        <i class="fas fa-pause-circle me-1"></i>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <div>
                                <div class="small text-muted">Status</div>
                                <div>Created {{ $clinic->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            @if($clinic->is_active)
                                <form method="POST" action="{{ route('master.clinics.deactivate', $clinic) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-warning btn-sm"
                                            onclick="return confirm('Are you sure you want to deactivate this clinic?')">
                                        <i class="fas fa-pause me-1"></i>
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('master.clinics.activate', $clinic) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
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

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users
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
                                Active Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_users'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Total Patients
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
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
    </div>

    <!-- Clinic Information and Users -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Clinic Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="font-weight-bold">Name:</td>
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
                        <tr>
                            <td class="font-weight-bold">Address:</td>
                            <td>{{ $clinic->address ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Max Users:</td>
                            <td>{{ $clinic->max_users }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Created:</td>
                            <td>{{ $clinic->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($clinic->activated_at)
                        <tr>
                            <td class="font-weight-bold">Activated:</td>
                            <td>{{ $clinic->activated_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Users ({{ $clinic->users->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($clinic->users->count() > 0)
                        @foreach($clinic->users->take(10) as $user)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <div class="icon-circle bg-{{ $user->role === 'admin' ? 'primary' : 'info' }}">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold">{{ $user->full_name }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                    <div class="text-muted small">{{ ucfirst($user->role) }}</div>
                                </div>
                                <div>
                                    @if($user->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        
                        @if($clinic->users->count() > 10)
                            <div class="text-center">
                                <small class="text-muted">
                                    And {{ $clinic->users->count() - 10 }} more users...
                                </small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">No users found for this clinic.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
