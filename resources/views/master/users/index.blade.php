@extends('master.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-users me-2"></i>
                        User Management
                    </h1>
                    <p class="text-muted mb-0">Manage all users across all clinics</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.users.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by name, email, or username">
                    </div>
                    <div class="col-md-2">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="clinic_id" class="form-label">Clinic</label>
                        <select class="form-select" id="clinic_id" name="clinic_id">
                            <option value="">All Clinics</option>
                            @foreach($clinics as $clinic)
                                <option value="{{ $clinic->id }}" {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                    {{ $clinic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                System Users ({{ $users->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Clinic</th>
                                <th>Role</th>
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
                                            <div class="icon-circle bg-{{ $user->role === 'admin' ? 'primary' : 'info' }} me-3">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $user->full_name }}</div>
                                                <div class="text-muted small">{{ $user->email }}</div>
                                                <div class="text-muted small">@{{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($user->clinic)
                                            <div class="font-weight-bold">{{ $user->clinic->name }}</div>
                                            <div class="text-muted small">ID: {{ $user->clinic->id }}</div>
                                        @else
                                            <span class="text-muted">No Clinic</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->role === 'admin' ? 'primary' : ($user->role === 'doctor' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
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
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('master.users.show', $user) }}">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($user->is_active)
                                                    <li>
                                                        <form method="POST" action="{{ route('master.users.deactivate', $user) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-warning"
                                                                    onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                                <i class="fas fa-pause me-2"></i>Deactivate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form method="POST" action="{{ route('master.users.activate', $user) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item text-success">
                                                                <i class="fas fa-play me-2"></i>Activate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('master.users.destroy', $user) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                            <i class="fas fa-trash me-2"></i>Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
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
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No users found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'role', 'clinic_id', 'status']))
                            No users match your current filters.
                        @else
                            No users have been created yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
