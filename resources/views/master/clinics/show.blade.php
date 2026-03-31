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

    <!-- Storage Usage Card -->
    @php
        $storageInfo = app(\App\Services\StorageQuotaService::class)->getStorageInfo($clinic->id);
        $barColor = $storageInfo['critical'] ? 'bg-danger' : ($storageInfo['warning'] ? 'bg-warning' : 'bg-success');
        $pct = min($storageInfo['percentage_used'], 100);
    @endphp
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-hdd me-2"></i>Storage Usage
                    </h6>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('master.clinics.sync-storage', $clinic) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Recalculate storage">
                                <i class="fas fa-sync-alt me-1"></i> Recalculate
                            </button>
                        </form>
                        <span class="badge {{ $storageInfo['critical'] ? 'bg-danger' : ($storageInfo['warning'] ? 'bg-warning text-dark' : 'bg-success') }} fs-6 align-self-center">
                            {{ $storageInfo['percentage_used'] }}% used
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($storageInfo['critical'])
                    <div class="alert alert-danger py-2 mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Critical:</strong> Storage is almost full for this clinic!
                    </div>
                    @elseif($storageInfo['warning'])
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <strong>Warning:</strong> Storage usage is above 80%.
                    </div>
                    @endif

                    <div class="progress mb-3" style="height: 24px;">
                        <div class="progress-bar {{ $barColor }} progress-bar-striped {{ $storageInfo['critical'] ? 'progress-bar-animated' : '' }}"
                             role="progressbar"
                             style="width: {{ $pct }}%"
                             aria-valuenow="{{ $pct }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ $storageInfo['used_gb'] }} GB / {{ $storageInfo['limit_gb'] }} GB
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-3">
                            <div class="text-muted small">Used</div>
                            <div class="h5 font-weight-bold">{{ $storageInfo['used_gb'] }} GB</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Remaining</div>
                            <div class="h5 font-weight-bold text-success">{{ $storageInfo['remaining_gb'] }} GB</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Limit</div>
                            <div class="h5 font-weight-bold">{{ $storageInfo['limit_gb'] }} GB</div>
                        </div>
                        <div class="col-3">
                            <div class="text-muted small">Quick Change Limit</div>
                            <form method="POST" action="{{ route('master.clinics.update-storage-limit', $clinic) }}" class="input-group input-group-sm">
                                @csrf
                                <input type="number" name="storage_limit_gb" step="0.5" min="0.1" max="10000"
                                       class="form-control" value="{{ $storageInfo['limit_gb'] }}" style="max-width:80px;">
                                <span class="input-group-text">GB</span>
                                <button class="btn btn-primary btn-sm" type="submit">Set</button>
                            </form>
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
	                            <td class="font-weight-bold">Clinic Type:</td>
	                            <td>
	                                @if($clinic->is_demo)
	                                    <span class="badge bg-secondary">Demo</span>
	                                @else
	                                    <span class="badge bg-primary">Tenant</span>
	                                @endif
	                            </td>
	                        </tr>
	                        <tr>
	                            <td class="font-weight-bold">Speciality:</td>
	                            <td>{{ $clinic->speciality ?? 'Not provided' }}</td>
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
	                            <td>
	                                {{ $clinic->formatted_address ?? ($clinic->address ?? 'Not provided') }}
	                                @if($clinic->city || $clinic->area || $clinic->street)
	                                    <div class="text-muted small mt-1">
	                                        <div><strong>City:</strong> {{ $clinic->city ?? '-' }}</div>
	                                        <div><strong>Area:</strong> {{ $clinic->area ?? '-' }}</div>
	                                        <div><strong>Street:</strong> {{ $clinic->street ?? '-' }}</div>
	                                    </div>
	                                @endif
	                            </td>
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

        <!-- Module Access -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cubes me-2"></i>Enabled Modules
                    </h6>
                </div>
                <div class="card-body">
                    @if($clinic->enabled_modules === null)
                        <span class="text-muted"><i class="fas fa-check-circle text-success me-1"></i> All modules enabled (default)</span>
                    @else
                        @foreach(\App\Models\Clinic::MODULE_GROUPS as $groupKey => $group)
                        <div class="mb-3">
                            <h6 class="text-secondary fw-bold border-bottom pb-2 mb-2">
                                <i class="{{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
                            </h6>
                            <div class="row ps-2">
                                @foreach($group['modules'] as $key => $label)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    @if(in_array($key, $clinic->enabled_modules ?? []))
                                        <span class="text-success"><i class="fas fa-check-circle me-1"></i> {{ $label }}</span>
                                    @else
                                        <span class="text-muted"><i class="fas fa-times-circle me-1"></i> <s>{{ $label }}</s></span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @endif
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
