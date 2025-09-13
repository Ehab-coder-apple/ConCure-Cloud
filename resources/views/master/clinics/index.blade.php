@extends('master.layouts.app')

@section('title', 'Clinic Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-hospital me-2"></i>
                        Clinic Management
                    </h1>
                    <p class="text-muted mb-0">Manage all registered clinics</p>
                </div>
                <div>
                    <a href="{{ route('master.clinics.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Add New Clinic
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.clinics.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by name, email, or phone">
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
                            <a href="{{ route('master.clinics.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clinics Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                Registered Clinics ({{ $clinics->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($clinics->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Clinic</th>
                                <th>Contact</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clinics as $clinic)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary me-3">
                                                <i class="fas fa-hospital text-white"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $clinic->name }}</div>
                                                <div class="text-muted small">ID: {{ $clinic->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @if($clinic->email)
                                                <div><i class="fas fa-envelope me-1"></i>{{ $clinic->email }}</div>
                                            @endif
                                            @if($clinic->phone)
                                                <div><i class="fas fa-phone me-1"></i>{{ $clinic->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="font-weight-bold">{{ $clinic->users_count ?? $clinic->users->count() }}</div>
                                            <div class="text-muted small">/ {{ $clinic->max_users }} max</div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($clinic->is_active)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-pause-circle me-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $clinic->created_at->format('M d, Y') }}</div>
                                        <div class="text-muted small">{{ $clinic->created_at->diffForHumans() }}</div>
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
                                                    <a class="dropdown-item" href="{{ route('master.clinics.show', $clinic) }}">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('master.clinics.edit', $clinic) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                @if($clinic->is_active)
                                                    <li>
                                                        <form method="POST" action="{{ route('master.clinics.deactivate', $clinic) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-warning"
                                                                    onclick="return confirm('Are you sure you want to deactivate this clinic?')">
                                                                <i class="fas fa-pause me-2"></i>Deactivate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form method="POST" action="{{ route('master.clinics.activate', $clinic) }}" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="dropdown-item text-success">
                                                                <i class="fas fa-play me-2"></i>Activate
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('master.clinics.destroy', $clinic) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Are you sure you want to delete this clinic? This action cannot be undone.')">
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
                        Showing {{ $clinics->firstItem() }} to {{ $clinics->lastItem() }} of {{ $clinics->total() }} results
                    </div>
                    {{ $clinics->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No clinics found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status']))
                            No clinics match your current filters.
                        @else
                            No clinics have been registered yet.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'status']))
                        <a href="{{ route('master.clinics.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Add First Clinic
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
