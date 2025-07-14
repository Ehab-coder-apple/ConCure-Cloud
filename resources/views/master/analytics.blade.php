@extends('layouts.master')

@section('title', __('Platform Analytics'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        {{ __('Platform Analytics') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Analytics') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="exportAnalytics()">
                        <i class="fas fa-download me-1"></i>
                        {{ __('Export Report') }}
                    </button>
                </div>
            </div>

            <!-- Time Period Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('master.analytics') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="period" class="form-label">{{ __('Time Period') }}</label>
                            <select class="form-select" id="period" name="period">
                                <option value="7" {{ request('period') == '7' ? 'selected' : '' }}>{{ __('Last 7 Days') }}</option>
                                <option value="30" {{ request('period') == '30' ? 'selected' : '' }}>{{ __('Last 30 Days') }}</option>
                                <option value="90" {{ request('period') == '90' ? 'selected' : '' }}>{{ __('Last 3 Months') }}</option>
                                <option value="365" {{ request('period') == '365' ? 'selected' : '' }}>{{ __('Last Year') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                    {{ __('Apply Filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary text-white rounded-circle p-3">
                                        <i class="fas fa-hospital fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Clinics') }}</h6>
                                    <h3 class="mb-0">{{ $analytics['total_clinics'] ?? 0 }}</h3>
                                    <small class="text-success">
                                        <i class="fas fa-arrow-up me-1"></i>
                                        {{ $analytics['clinic_growth'] ?? 0 }}% {{ __('growth') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-info text-white rounded-circle p-3">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Users') }}</h6>
                                    <h3 class="mb-0">{{ $analytics['total_users'] ?? 0 }}</h3>
                                    <small class="text-info">
                                        <i class="fas fa-user-md me-1"></i>
                                        {{ $analytics['active_users'] ?? 0 }} {{ __('active') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-success text-white rounded-circle p-3">
                                        <i class="fas fa-user-injured fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Total Patients') }}</h6>
                                    <h3 class="mb-0">{{ $analytics['total_patients'] ?? 0 }}</h3>
                                    <small class="text-success">
                                        <i class="fas fa-heartbeat me-1"></i>
                                        {{ $analytics['new_patients'] ?? 0 }} {{ __('new') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning text-white rounded-circle p-3">
                                        <i class="fas fa-prescription-bottle-alt fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">{{ __('Prescriptions') }}</h6>
                                    <h3 class="mb-0">{{ $analytics['total_prescriptions'] ?? 0 }}</h3>
                                    <small class="text-warning">
                                        <i class="fas fa-pills me-1"></i>
                                        {{ __('This period') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Monthly Growth Chart -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                {{ __('Monthly Growth Trends') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="growthChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Clinics -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-trophy me-2"></i>
                                {{ __('Top Active Clinics') }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($analytics['top_clinics']) && count($analytics['top_clinics']) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($analytics['top_clinics'] as $index => $clinic)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">
                                                    <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                    {{ $clinic->name }}
                                                </div>
                                                <small class="text-muted">{{ $clinic->user_count }} {{ __('users') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success">{{ $clinic->active_users ?? 0 }}</span>
                                                <br>
                                                <small class="text-muted">{{ __('active') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No clinic data available') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Subscription Status -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ __('Subscription Status') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="subscriptionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>
                                {{ __('Recent Platform Activity') }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($analytics['recent_activity']) && count($analytics['recent_activity']) > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($analytics['recent_activity'] as $activity)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fw-bold">{{ $activity->action }}</div>
                                                <small class="text-muted">{{ $activity->user_name }} - {{ $activity->description }}</small>
                                            </div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($activity->performed_at)->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No recent activity') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Growth Chart
const growthCtx = document.getElementById('growthChart').getContext('2d');
const growthChart = new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($analytics['monthly_labels'] ?? []) !!},
        datasets: [{
            label: '{{ __("New Clinics") }}',
            data: {!! json_encode($analytics['monthly_registrations'] ?? []) !!},
            borderColor: 'rgb(220, 53, 69)',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Subscription Chart
const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
const subscriptionChart = new Chart(subscriptionCtx, {
    type: 'doughnut',
    data: {
        labels: ['{{ __("Active") }}', '{{ __("Expired") }}', '{{ __("Expiring Soon") }}'],
        datasets: [{
            data: [
                {{ $analytics['subscription_status']->active_subscriptions ?? 0 }},
                {{ $analytics['subscription_status']->expired_subscriptions ?? 0 }},
                {{ $analytics['subscription_status']->expiring_soon ?? 0 }}
            ],
            backgroundColor: [
                'rgb(25, 135, 84)',
                'rgb(220, 53, 69)',
                'rgb(255, 193, 7)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

function exportAnalytics() {
    window.location.href = '{{ route("master.analytics.export") }}';
}
</script>
@endpush
@endsection
