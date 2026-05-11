@extends('master.layouts.app')

@section('title', 'Master Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Master Dashboard
                    </h1>
                    <p class="text-muted mb-0">ConCure SaaS Management Center</p>
                </div>
                <div>
                    <span class="badge bg-success fs-6">
                        <i class="fas fa-circle me-1"></i>
                        System Online
                    </span>
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
                                Total Clinics
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_clinics'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hospital fa-2x text-gray-300"></i>
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
                                Active Clinics
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['active_clinics'] }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_users'] }}
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
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
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Growth Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="growthChart" width="100" height="40"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Clinic Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="clinicStatusChart" width="100" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Renewals -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow border-left-warning">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-check me-2"></i>
                        Contracts Renewing Next Month
                        @if($upcomingRenewals->count() > 0)
                            <span class="badge bg-warning text-dark ms-2">{{ $upcomingRenewals->count() }}</span>
                        @endif
                    </h6>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::now()->addMonthNoOverflow()->format('F Y') }}
                    </small>
                </div>
                <div class="card-body">
                    @if($upcomingRenewals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Clinic</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Renewal Date</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingRenewals as $clinic)
                                        <tr>
                                            <td class="fw-bold">{{ $clinic->name }}</td>
                                            <td class="text-muted small">{{ $clinic->email ?? '-' }}</td>
                                            <td class="text-muted small">{{ $clinic->city ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-calendar-day me-1"></i>
                                                    {{ $clinic->contract_renewal_at->format('M d, Y') }}
                                                </span>
                                                <span class="text-muted small ms-1">
                                                    ({{ $clinic->contract_renewal_at->diffForHumans() }})
                                                </span>
                                            </td>
                                            <td>
                                                @if($clinic->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('master.clinics.manage-contract', $clinic) }}" class="btn btn-sm btn-warning me-1" title="Manage Contract">
                                                    <i class="fas fa-file-contract"></i> Renew
                                                </a>
                                                <a href="{{ route('master.clinics.edit', $clinic) }}" class="btn btn-sm btn-outline-primary" title="Edit Clinic">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            No clinic contracts are scheduled to renew in {{ \Carbon\Carbon::now()->addMonthNoOverflow()->format('F Y') }}.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Clinics</h6>
                </div>
                <div class="card-body">
                    @if($recentClinics->count() > 0)
                        @foreach($recentClinics as $clinic)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <div class="icon-circle bg-primary">
                                        <i class="fas fa-hospital text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-gray-500">{{ $clinic->created_at->format('M d, Y') }}</div>
                                    <div class="font-weight-bold">{{ $clinic->name }}</div>
                                    <div class="text-muted small">{{ $clinic->email }}</div>
                                </div>
                                <div>
                                    @if($clinic->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No clinics registered yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        @foreach($recentUsers as $user)
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <div class="icon-circle bg-info">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-gray-500">{{ $user->created_at->format('M d, Y') }}</div>
                                    <div class="font-weight-bold">{{ $user->full_name }}</div>
                                    <div class="text-muted small">
                                        {{ $user->role }} - {{ $user->clinic->name ?? 'No Clinic' }}
                                    </div>
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
                    @else
                        <p class="text-muted">No users registered yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Growth Chart
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: @json($growthData['months']),
        datasets: [{
            label: 'Clinics',
            data: @json($growthData['clinics']),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Users',
            data: @json($growthData['users']),
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Clinic Status Chart
const statusCtx = document.getElementById('clinicStatusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Inactive'],
        datasets: [{
            data: [{{ $stats['active_clinics'] }}, {{ $stats['pending_clinics'] }}],
            backgroundColor: ['#28a745', '#ffc107']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>
@endpush
