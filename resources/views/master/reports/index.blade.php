@extends('master.layouts.app')

@section('title', 'Reports | ConCure Master')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center">
        <i class="fas fa-chart-bar fa-2x text-primary me-3"></i>
        <div>
            <h5 class="mb-0">System Reports</h5>
            <small class="text-muted">High-level analytics across all clinics</small>
        </div>
    </div>
    <div>
        <a href="{{ route('master.reports.login-activity') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-sign-in-alt me-1"></i> Login Activity Report
        </a>
    </div>
</div>

@push('styles')
<style>
    .reports-card .card-body { padding: 1rem; }
    .chart-wrap { position: relative; height: 220px; width: 100%; }
    @media (max-width: 991.98px) { .chart-wrap { height: 200px; } }
    @media (max-width: 575.98px) { .chart-wrap { height: 180px; } }
</style>
@endpush

{{-- Filters & Flash --}}
@if(session('success'))
  <div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger py-2 px-3">{{ session('error') }}</div>
@endif
{{-- Filters --}}
<form method="GET" class="card mb-4">
    <div class="card-body row g-3 align-items-end">
        <div class="col-sm-3 col-md-2">
            <label class="form-label">From</label>
            <input type="date" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="col-sm-3 col-md-2">
            <label class="form-label">To</label>
            <input type="date" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div class="col-sm-3 col-md-3">
            <label class="form-label">Clinic</label>
            <select name="clinic_id" class="form-select">
                <option value="">All Clinics</option>
                @foreach($clinics as $clinic)
                    <option value="{{ $clinic->id }}" {{ ($filters['clinic_id'] ?? '') == $clinic->id ? 'selected' : '' }}>
                        {{ $clinic->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3 col-md-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
            <a href="{{ route('master.reports') }}" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
    </div>
</form>

<!-- Quick add payment + import -->
<div class="card reports-card mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Clinic</label>
        <form class="d-flex gap-2" method="POST" action="{{ route('master.reports.payments.store') }}">
          @csrf
          <select name="clinic_id" class="form-select form-select-sm" required>
            @foreach(($clinics ?? []) as $c)
              <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Amount</label>
        <input type="number" name="amount" step="0.01" min="0.01" class="form-control form-control-sm" required>
      </div>
      <div class="col-md-2">
        <label class="form-label small">Paid at</label>
        <input type="date" name="paid_at" value="{{ now()->toDateString() }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small">Method</label>
        <input type="text" name="method" class="form-control form-control-sm" placeholder="e.g. bank">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus me-1"></i>Add Payment</button>
        </form>
      </div>
    </div>

    <form method="POST" action="{{ route('master.reports.payments.import') }}" enctype="multipart/form-data" class="mt-2 d-flex gap-2 align-items-center">
      @csrf
      <label class="form-label small mb-0">Import CSV</label>
      <input type="file" name="csv" accept=".csv" class="form-control form-control-sm" style="max-width:300px">
      <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-file-import me-1"></i>Import</button>
      <span class="text-muted small">Headers: clinic_id, paid_at, amount, method, reference, notes</span>
    </form>
  </div>
</div>

<!-- Financial summary -->
<div class="row g-3 mb-2">
  <div class="col-md-4">
    <div class="card reports-card border-left-success">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-success mb-1">Collected (Subscriptions)</div>
          <div class="h5 mb-0 text-gray-800">{{ $currencySymbol }}{{ number_format($collectedAmount, 2) }}</div>
        </div>
        <div class="icon-circle bg-success text-white"><i class="fas fa-coins"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card reports-card border-left-primary">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-primary mb-1">Expected Monthly Fees</div>
          <div class="h5 mb-0 text-gray-800">{{ $currencySymbol }}{{ number_format($expectedMonthlyFees, 2) }}</div>
          <small class="text-muted">{{ $expectedFormulaNote ?? '' }}</small>
        </div>
        <div class="icon-circle bg-primary text-white"><i class="fas fa-file-invoice-dollar"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card reports-card border-left-info">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-info mb-1">Active Subscribers</div>
          <div class="h5 mb-0 text-gray-800">{{ $activeSubscribers }}</div>
        </div>
        <div class="icon-circle bg-info text-white"><i class="fas fa-user-check"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card reports-card border-left-secondary">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-secondary mb-1">Service Charges (in range)</div>
          <div class="h5 mb-0 text-gray-800">{{ $currencySymbol }}{{ number_format($serviceCharges ?? 0, 2) }}</div>
        </div>
          <div class="mt-2">
            <a href="{{ route('master.reports.service-charges.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-file-export me-1"></i> Export CSV
            </a>
          </div>

        <div class="icon-circle bg-secondary text-white"><i class="fas fa-receipt"></i></div>
      </div>
    </div>
  </div>

</div>

<!-- Patient Analytics -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card reports-card border-left-primary">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-primary mb-1">Total Patients</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($patientStats['total']) }}</div>
        </div>
        <div class="icon-circle bg-primary text-white"><i class="fas fa-users"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-success">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-success mb-1">Active Patients</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($patientStats['active']) }}</div>
        </div>
        <div class="icon-circle bg-success text-white"><i class="fas fa-user-check"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-info">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-info mb-1">New Patients (30d)</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($patientStats['new']) }}</div>
        </div>
        <div class="icon-circle bg-info text-white"><i class="fas fa-user-plus"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-warning">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-warning mb-1">Total Prescriptions</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($prescriptionStats['total']) }}</div>
        </div>
        <div class="icon-circle bg-warning text-white"><i class="fas fa-prescription-bottle-alt"></i></div>
      </div>
    </div>
  </div>
</div>

<!-- Lab & Appointment Analytics -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card reports-card border-left-secondary">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-secondary mb-1">Lab Requests</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($labStats['total']) }}</div>
        </div>
        <div class="icon-circle bg-secondary text-white"><i class="fas fa-flask"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-danger">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-danger mb-1">Pending Labs</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($labStats['pending']) }}</div>
        </div>
        <div class="icon-circle bg-danger text-white"><i class="fas fa-clock"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-primary">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-primary mb-1">Total Appointments</div>
          <div class="h5 mb-0 text-gray-800">{{ number_format($appointmentStats['total']) }}</div>
        </div>
        <div class="icon-circle bg-primary text-white"><i class="fas fa-calendar-alt"></i></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card reports-card border-left-success">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-xs text-uppercase text-success mb-1">Revenue Generated</div>
          <div class="h5 mb-0 text-gray-800">{{ $currencySymbol }}{{ number_format($financialStats['total_revenue'], 2) }}</div>
        </div>
        <div class="icon-circle bg-success text-white"><i class="fas fa-dollar-sign"></i></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100 border-left-primary reports-card">
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
                <div class="chart-wrap mt-3">
                    <canvas id="clinicsChart"></canvas>
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
        <div class="card h-100 border-left-success reports-card">
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
                <div class="chart-wrap mt-3">
                    <canvas id="usersChart"></canvas>
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

<!-- Detailed Analytics Charts -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100 border-left-info reports-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-info mb-1">Patient Demographics</div>
                        <div class="h5 mb-0 text-gray-800">Age Groups</div>
                    </div>
                    <div class="icon-circle bg-info text-white">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="chart-wrap mt-3">
                    <canvas id="ageGroupsChart"></canvas>
                </div>
                <div class="text-muted mt-3">
                    @forelse($patientStats['age_groups'] as $group => $count)
                        <span class="me-3">{{ $group }}: <strong>{{ $count }}</strong></span>
                    @empty
                        <em>No age data available.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 border-left-warning reports-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-warning mb-1">Top Prescribed Medicines</div>
                        <div class="h5 mb-0 text-gray-800">Most Frequent</div>
                    </div>
                    <div class="icon-circle bg-warning text-white">
                        <i class="fas fa-pills"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @forelse($prescriptionStats['top_medicines']->take(5) as $medicine)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-sm">{{ $medicine->name }}</span>
                            <span class="badge bg-warning">{{ $medicine->count }}</span>
                        </div>
                    @empty
                        <em class="text-muted">No prescription data available.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100 border-left-secondary reports-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-secondary mb-1">Most Requested Lab Tests</div>
                        <div class="h5 mb-0 text-gray-800">Top 5</div>
                    </div>
                    <div class="icon-circle bg-secondary text-white">
                        <i class="fas fa-microscope"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @forelse($labStats['top_tests']->take(5) as $test)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-sm">{{ $test->name }}</span>
                            <span class="badge bg-secondary">{{ $test->count }}</span>
                        </div>
                    @empty
                        <em class="text-muted">No lab test data available.</em>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 border-left-primary reports-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs text-uppercase text-primary mb-1">Appointment Types</div>
                        <div class="h5 mb-0 text-gray-800">Distribution</div>
                    </div>
                    <div class="icon-circle bg-primary text-white">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="chart-wrap mt-3">
                    <canvas id="appointmentTypesChart"></canvas>
                </div>
                <div class="text-muted mt-3">
                    @forelse($appointmentStats['types'] as $type => $count)
                        <span class="me-3">{{ ucfirst(str_replace('_', ' ', $type)) }}: <strong>{{ $count }}</strong></span>
                    @empty
                        <em>No appointment data available.</em>
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
                    borderWidth: 0,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                }
            }
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
                    backgroundColor: '#36b9cc',
                    borderWidth: 0,
                    barThickness: 22,
                    maxBarThickness: 26,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Age Groups Chart
    const ageGroupsCtx = document.getElementById('ageGroupsChart');
    if (ageGroupsCtx) {
        const ageData = @json(array_values($patientStats['age_groups']));
        const ageLabels = @json(array_keys($patientStats['age_groups']));

        new Chart(ageGroupsCtx, {
            type: 'doughnut',
            data: {
                labels: ageLabels,
                datasets: [{
                    data: ageData,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    borderWidth: 0,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
                }
            }
        });
    }

    // Appointment Types Chart
    const appointmentTypesCtx = document.getElementById('appointmentTypesChart');
    if (appointmentTypesCtx) {
        const appointmentData = @json($appointmentStats['type_values'] ?? []);
        const appointmentLabels = @json($appointmentStats['type_labels'] ?? []);

        new Chart(appointmentTypesCtx, {
            type: 'bar',
            data: {
                labels: appointmentLabels,
                datasets: [{
                    label: 'Appointments',
                    data: appointmentData,
                    backgroundColor: '#4e73df',
                    borderWidth: 0,
                    barThickness: 20,
                    maxBarThickness: 25,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
})();
</script>
@endpush
@endsection

