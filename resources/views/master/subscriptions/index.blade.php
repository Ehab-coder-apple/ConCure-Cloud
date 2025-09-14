@extends('master.layouts.app')

@section('title', 'Subscription Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Subscription Management
                    </h1>
                    <p class="text-muted mb-0">Manage clinic subscriptions and billing</p>
                </div>
                <div>
                    <a class="btn btn-outline-info me-2" href="{{ route('master.plans.create') }}">
                        <i class="fas fa-plus me-2"></i>
                        Create Plan
                    </a>
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
                                Total Subscriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_subscriptions'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-300"></i>
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
                                Active Subscriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_subscriptions'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Inactive Subscriptions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['inactive_subscriptions'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
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
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($stats['total_revenue']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.subscriptions.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by clinic name or email">
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
                            <a href="{{ route('master.subscriptions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                Active Subscriptions ({{ $clinics->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($clinics->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Clinic</th>
                                <th>Plan</th>
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
                                                <div class="text-muted small">{{ $clinic->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @php($plan = $clinic->plan)
                                            @if($plan)
                                                <div class="font-weight-bold">{{ $plan->name }}</div>
                                                <div class="text-muted small">
                                                    @php($cycle = $clinic->billing_cycle ?? 'monthly')
                                                    @php($m = $clinic->custom_monthly_price ?? $plan->monthly_price)
                                                    @php($y = $clinic->custom_yearly_price ?? $plan->yearly_price)
                                                    @php($price = null)
                                                    @switch($cycle)
                                                        @case('yearly')
                                                            @php($price = $y ?? ($m !== null ? $m * 12 : null))
                                                            ${{ $price !== null ? number_format($price, 2) : '0.00' }}/year
                                                            @break
                                                        @case('quarterly')
                                                            @php($price = $y !== null ? $y/4 : ($m !== null ? $m*3 : null))
                                                            ${{ $price !== null ? number_format($price, 2) : '0.00' }}/quarter
                                                            @break
                                                        @case('semiannual')
                                                            @php($price = $y !== null ? $y/2 : ($m !== null ? $m*6 : null))
                                                            ${{ $price !== null ? number_format($price, 2) : '0.00' }}/6 months
                                                            @break
                                                        @default
                                                            @php($price = $m)
                                                            ${{ $price !== null ? number_format($price, 2) : '0.00' }}/month
                                                    @endswitch
                                                </div>
                                            @else
                                                <div class="text-muted">No plan</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="font-weight-bold">{{ $clinic->users->count() }}</div>
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
                                                    <a class="dropdown-item" href="{{ route('master.subscriptions.show', $clinic) }}">
                                                        <i class="fas fa-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('master.subscriptions.edit', $clinic) }}">
                                                        <i class="fas fa-edit me-2"></i>Edit Plan
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-info" href="#" onclick="alert('Billing management coming soon!')">
                                                        <i class="fas fa-receipt me-2"></i>View Billing
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('master.subscriptions.destroy', $clinic) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"
                                                                onclick="return confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')">
                                                            <i class="fas fa-times me-2"></i>Cancel Subscription
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
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No subscriptions found</h5>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'status']))
                            No subscriptions match your current filters.
                        @else
                            No active subscriptions yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Note -->
    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle me-2"></i>
        Subscription plans and assignments are active. Automated billing will be added later.
    </div>
</div>
@endsection
