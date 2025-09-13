@extends('master.layouts.app')

@section('title', 'Reports | ConCure Master')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="fas fa-chart-bar fa-2x text-primary me-3"></i>
    <div>
        <h4 class="mb-0">System Reports</h4>
        <small class="text-muted">High-level analytics across all clinics</small>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="card mb-4">
    <div class="card-body row g-3 align-items-end">
        <div class="col-sm-4 col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="col-sm-4 col-md-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
            <a href="{{ route('master.reports') }}" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
    </div>
</form>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100 border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-primary mb-1">Clinics Overview</div>
                        <div class="h5 mb-0 text-gray-800">Active vs Inactive</div>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <canvas id="clinicsChart" height="130"></canvas>
                </div>
                <div class="text-muted mt-3">
                    Total in range: <strong>{{ $clinicsTotal }}</strong>
                    <span class="ms-3">Active: <strong>{{ $clinicsActive }}</strong></span>
                    <span class="ms-3">Inactive: <strong>{{ $clinicsInactive }}</strong></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-success mb-1">Users Growth</div>
                        <div class="h5 mb-0 text-gray-800">By Role</div>
                    </div>
                    <div class="icon-circle bg-success text-white">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <canvas id="usersChart" height="130"></canvas>
                </div>
                <div class="text-muted mt-3">
                    @forelse($usersByRole as $role => $count)
                        <span class="me-3">{{ ucfirst(str_replace('_',' ', $role)) }}: <strong>{{ $count }}</strong></span>
                    @empty
                        <em>No users in selected range.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const clinicsCtx = document.getElementById('clinicsChart');
    if (clinicsCtx) {
        new Chart(clinicsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive'],
                datasets: [{
                    data: [{{ (int) $clinicsActive }}, {{ (int) $clinicsInactive }}],
                    backgroundColor: ['#1cc88a', '#f6c23e'],
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }

    const usersCtx = document.getElementById('usersChart');
    if (usersCtx) {
        const labels = {!! json_encode(array_keys($usersByRole)) !!};
        const data = {!! json_encode(array_values($usersByRole)) !!};
        new Chart(usersCtx, {
            type: 'bar',
            data: {
                labels: labels.map(l => l.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase())),
                datasets: [{
                    label: 'Users',
                    data,
                    backgroundColor: '#36b9cc'
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, precision: 0 } }
            }
        });
    }
})();
</script>
@endpush
@endsection

