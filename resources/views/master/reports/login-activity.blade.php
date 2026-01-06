@extends('master.layouts.app')

@section('title', 'Login/Logout Activity Report | ConCure Master')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-sign-in-alt fa-2x text-primary me-3"></i>
        <div>
            <h5 class="mb-0">Login/Logout Activity Report</h5>
            <small class="text-muted">Track user login sessions and activity across all clinics</small>
        </div>
    </div>
    <div>
        <a href="{{ route('master.reports') }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
        <a href="{{ route('master.reports.login-activity.export', request()->query()) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <div class="text-xs text-uppercase text-primary mb-1">Total Sessions</div>
                <div class="h5 mb-0 text-gray-800">{{ number_format($stats['total_sessions']) }}</div>
                <small class="text-muted">In selected period</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <div class="text-xs text-uppercase text-success mb-1">Unique Users</div>
                <div class="h5 mb-0 text-gray-800">{{ number_format($stats['unique_users']) }}</div>
                <small class="text-muted">Different users logged in</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="text-xs text-uppercase text-info mb-1">Avg Session Duration</div>
                <div class="h5 mb-0 text-gray-800">{{ $stats['avg_duration_formatted'] }}</div>
                <small class="text-muted">{{ number_format($stats['completed_sessions']) }} completed sessions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-warning h-100">
            <div class="card-body">
                <div class="text-xs text-uppercase text-warning mb-1">Active Sessions</div>
                <div class="h5 mb-0 text-gray-800">{{ number_format($stats['active_sessions']) }}</div>
                <small class="text-muted">{{ number_format($stats['timed_out_sessions']) }} timed out</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i>Filters
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('master.reports.login-activity') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ $filters['from'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ $filters['to'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Clinic</label>
                    <select name="clinic_id" class="form-select">
                        <option value="">All Clinics</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}" {{ $filters['clinic_id'] == $clinic->id ? 'selected' : '' }}>
                                {{ $clinic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $filters['user_id'] == $user->id ? 'selected' : '' }}>
                                {{ $user->first_name }} {{ $user->last_name }} ({{ ucfirst($user->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($roles as $roleKey => $roleLabel)
                            <option value="{{ $roleKey }}" {{ $filters['role'] == $roleKey ? 'selected' : '' }}>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('master.reports.login-activity') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table me-2"></i>Login Sessions
        <span class="badge bg-primary ms-2">{{ $paginatedSessions->total() }} total</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Clinic</th>
                        <th>Session Start</th>
                        <th>Session End</th>
                        <th>Duration</th>
                        <th>Activities</th>
                        <th>IP Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedSessions as $session)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $session->user_name }}</div>
                                <small class="text-muted">ID: {{ $session->user_id }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $session->user_role === 'admin' ? 'danger' : ($session->user_role === 'doctor' ? 'primary' : 'secondary') }}">
                                    {{ ucfirst($session->user_role ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $session->clinic_name }}</td>
                            <td>
                                <div>{{ $session->login_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $session->login_at->format('g:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $endTime = $session->logout_at ?? $session->estimated_end;
                                @endphp
                                <div>{{ $endTime->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $endTime->format('g:i A') }}</small>
                                @if(!$session->logout_at)
                                    <small class="text-warning d-block">
                                        <i class="fas fa-info-circle"></i> Estimated
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $session->duration_formatted }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $session->login_count }} {{ $session->login_count > 1 ? 'logins' : 'login' }}
                                </span>
                            </td>
                            <td>
                                <code class="text-sm">{{ $session->ip_address ?? 'N/A' }}</code>
                            </td>
                            <td>
                                @if($session->status === 'Active Session')
                                    <span class="badge bg-success">
                                        <i class="fas fa-circle"></i> Active
                                    </span>
                                @elseif($session->status === 'Timed Out')
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Timed Out
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-check"></i> Completed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No login sessions found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($paginatedSessions->hasPages())
            <div class="mt-3">
                {{ $paginatedSessions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Charts Section -->
<div class="row g-3 mt-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Sessions by Role
            </div>
            <div class="card-body">
                @if(count($stats['sessions_by_role']) > 0)
                    <canvas id="roleChart" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4">No data available</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i>Top 10 Clinics by Sessions
            </div>
            <div class="card-body">
                @if(count($stats['sessions_by_clinic']) > 0)
                    <canvas id="clinicChart" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4">No data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }
    .table-sm th, .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Sessions by Role Chart
    @if(count($stats['sessions_by_role']) > 0)
    const roleCtx = document.getElementById('roleChart');
    if (roleCtx) {
        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_map('ucfirst', array_keys($stats['sessions_by_role']))) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['sessions_by_role'])) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    @endif

    // Sessions by Clinic Chart
    @if(count($stats['sessions_by_clinic']) > 0)
    const clinicCtx = document.getElementById('clinicChart');
    if (clinicCtx) {
        new Chart(clinicCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($stats['sessions_by_clinic'])) !!},
                datasets: [{
                    label: 'Sessions',
                    data: {!! json_encode(array_values($stats['sessions_by_clinic'])) !!},
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    @endif
</script>
@endpush
@endsection

