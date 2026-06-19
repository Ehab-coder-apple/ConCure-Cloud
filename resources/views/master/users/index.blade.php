@extends('master.layouts.app')

@section('title', 'Super Admin Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i>
                        Super Admin Management
                    </h1>
                    <p class="text-muted mb-0">Manage scoped Super Admin accounts and their allocated clinics</p>
                </div>
                <a href="{{ route('master.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Create Super Admin
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.users.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text"
                               class="form-control"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by name, email, or username">
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('master.users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                Super Admins ({{ $users->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Allocated Clinics</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary me-3">
                                                <i class="fas fa-user-shield text-white"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $user->full_name }}</div>
                                                <div class="text-muted small">{{ $user->email }}</div>
                                                <div class="text-muted small">@{{ $user->username }}</div>
                                                @if($user->createdBy)
                                                    <div class="text-muted small">Created by {{ $user->createdBy->full_name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ \App\Models\User::ROLES[$user->role] ?? ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->superAdminClinics->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($user->superAdminClinics->take(3) as $clinic)
                                                    <span class="badge bg-secondary small">
                                                        {{ $clinic->name }}
                                                    </span>
                                                @endforeach
                                                @if($user->superAdminClinics->count() > 3)
                                                    <span class="badge bg-light text-dark small">
                                                        +{{ $user->superAdminClinics->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mt-1">{{ $user->superAdminClinics->count() }} clinics assigned</div>
                                        @else
                                            <span class="text-muted small">No clinics assigned</span>
                                        @endif
                                        <div class="text-muted small mt-1">
                                            Created quota: {{ $user->createdManagedClinicsCount() }}/{{ $user->getManagedClinicCreationLimit() }}
                                            @if($user->getManagedClinicCreationLimit() > 0)
                                                · {{ $user->remainingManagedClinicCreationSlots() }} remaining
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $user->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $user->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('master.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>

                                            <a href="{{ route('master.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>

                                            @if($user->is_active)
                                                <form method="POST" action="{{ route('master.users.deactivate', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                        <i class="fas fa-pause me-1"></i> Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('master.users.activate', $user) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-play me-1"></i> Activate
                                                    </button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('master.users.destroy', $user) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                    <i class="fas fa-trash me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                    </div>
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Super Admins found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status']))
                            No Super Admins match your current filters.
                        @else
                            No Super Admins have been created yet. Create your first scoped Super Admin to get started.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status']))
                        <a href="{{ route('master.users.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Create First Super Admin
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
