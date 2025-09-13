@extends('layouts.app')

@section('page-title', __('Dashboard'))

@section('content')
<div class="container">
    {{-- Trial notification removed - subscription system no longer needed --}}

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    @php
                        $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo(auth()->user()->clinic_id);
                    @endphp
                    @if($clinicLogo)
                        <img src="{{ $clinicLogo }}" alt="Clinic Logo" class="clinic-logo me-3" style="max-height: 80px; max-width: 80px; object-fit: cover;">
                    @endif
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-tachometer-alt text-primary"></i>
                            Dashboard
                        </h1>
                        <p class="text-muted mb-0">Welcome back, {{ auth()->user()->full_name }}!</p>
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        @if(isset($totalPatients))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Patients</h6>
                            <h2 class="mb-0">{{ number_format($totalPatients) }}</h2>
                            @if(isset($newPatientsThisMonth) && $newPatientsThisMonth > 0)
                            <small>+{{ $newPatientsThisMonth }} this month</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($activePrescriptions))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Active Prescriptions</h6>
                            <h2 class="mb-0">{{ number_format($activePrescriptions) }}</h2>
                            @if(isset($prescriptionsThisMonth) && $prescriptionsThisMonth > 0)
                            <small>{{ $prescriptionsThisMonth }} this month</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-prescription-bottle-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($pendingLabRequests))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Pending Lab Requests</h6>
                            <h2 class="mb-0">{{ number_format($pendingLabRequests) }}</h2>
                            @if(isset($urgentLabRequests) && $urgentLabRequests > 0)
                            <small>{{ $urgentLabRequests }} urgent</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-flask fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($totalAppointments))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Today's Appointments</h6>
                            <h2 class="mb-0">{{ number_format($todayAppointments ?? 0) }}</h2>
                            @if(isset($upcomingAppointments) && $upcomingAppointments > 0)
                            <small>{{ $upcomingAppointments }} upcoming</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($totalNutritionPlans))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Nutrition Plans</h6>
                            <h2 class="mb-0">{{ number_format($activeNutritionPlans ?? 0) }}</h2>
                            @if(isset($thisMonthNutritionPlans) && $thisMonthNutritionPlans > 0)
                            <small>{{ $thisMonthNutritionPlans }} this month</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-apple-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(isset($totalRevenue))
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Monthly Revenue</h6>
                            <h2 class="mb-0">${{ number_format($totalRevenue, 2) }}</h2>
                            @if(isset($pendingInvoices) && $pendingInvoices > 0)
                            <small>{{ $pendingInvoices }} pending invoices</small>
                            @endif
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(Auth::user()->hasPermission('patients_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('patients.create') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-plus d-block mb-1"></i>
                                <small>Add Patient</small>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('prescriptions_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('simple-prescriptions.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-prescription d-block mb-1"></i>
                                <small>New Prescription</small>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('appointments_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('appointments.create') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-calendar-plus d-block mb-1"></i>
                                <small>New Appointment</small>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('nutrition_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('nutrition.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-apple-alt d-block mb-1"></i>
                                <small>Nutrition Plan</small>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('medicines_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('medicines.create') }}" class="btn btn-outline-danger w-100">
                                <i class="fas fa-pills d-block mb-1"></i>
                                <small>New Medicine</small>
                            </a>
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('users_create'))
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-user-plus d-block mb-1"></i>
                                <small>New User</small>
                            </a>
                        </div>
                        @endif

                        @can('create-prescriptions')
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-vial d-block mb-1"></i>
                                <small>Lab Request</small>
                            </a>
                        </div>
                        @endcan

                        @can('create-prescriptions')
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('recommendations.radiology.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-x-ray d-block mb-1"></i>
                                <small>Radiology Request</small>
                            </a>
                        </div>
                        @endcan

                        @can('manage-finance')
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('finance.invoices') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-file-invoice d-block mb-1"></i>
                                <small>New Invoice</small>
                            </a>
                        </div>
                        @endcan

                        @can('manage-users')
                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('users.create') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-user-cog d-block mb-1"></i>
                                <small>Add User</small>
                            </a>
                        </div>
                        @endcan

                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-outline-dark w-100">
                                <i class="fas fa-cog d-block mb-1"></i>
                                <small>Settings</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h6>
                    @can('view-audit-logs')
                    <a href="{{ route('settings.audit-logs') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                    @endcan
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($recentActivity) && count($recentActivity) > 0)
                        @foreach($recentActivity as $activity)
                        <div class="border-bottom p-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-light rounded-circle p-2" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $activity['description'] }}</h6>
                                    <p class="mb-1 text-muted small">
                                        by {{ $activity['user_name'] ?? 'System' }}
                                    </p>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($activity['performed_at'])->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-history fa-2x mb-2"></i>
                        <p class="mb-0">No recent activity</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Appointments Overview -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-week text-primary"></i>
                        Appointments Overview
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('appointments.create') }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            New
                        </a>
                        <a href="{{ route('appointments.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>
                            All
                        </a>
                        <a href="{{ route('appointments.index') }}?view=calendar" class="btn btn-info">
                            <i class="fas fa-calendar me-1"></i>
                            Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($appointmentsByDate) && count($appointmentsByDate) > 0)
                        <div class="row g-0">
                            @foreach($appointmentsByDate as $dateGroup)
                            <div class="col-12 border-bottom">
                                <div class="p-3">
                                    <!-- Date Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 {{ $dateGroup['is_today'] ? 'text-primary fw-bold' : 'text-dark' }}">
                                            @if($dateGroup['is_today'])
                                                <i class="fas fa-calendar-day me-1 text-primary"></i>
                                                Today - {{ $dateGroup['date_label'] }}
                                            @else
                                                <i class="fas fa-calendar me-1 text-muted"></i>
                                                {{ $dateGroup['date_label'] }}
                                            @endif
                                        </h6>
                                        <span class="badge {{ $dateGroup['count'] > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ $dateGroup['count'] }} {{ Str::plural('appointment', $dateGroup['count']) }}
                                        </span>
                                    </div>

                                    <!-- Appointments List -->
                                    @if(count($dateGroup['appointments']) > 0)
                                        <div class="row">
                                            @foreach($dateGroup['appointments'] as $appointment)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center p-2 bg-light rounded">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="text-center">
                                                            <div class="fw-bold text-primary" style="font-size: 0.8rem;">
                                                                {{ \Carbon\Carbon::parse($appointment['appointment_datetime'])->format('g:i A') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-bold text-truncate" style="font-size: 0.85rem;">
                                                            {{ $appointment['patient']['first_name'] ?? '' }} {{ $appointment['patient']['last_name'] ?? '' }}
                                                        </div>
                                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;">
                                                            Dr. {{ $appointment['doctor']['first_name'] ?? '' }} {{ $appointment['doctor']['last_name'] ?? '' }}
                                                        </div>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <span class="badge bg-{{ $appointment['status'] === 'scheduled' ? 'success' : ($appointment['status'] === 'completed' ? 'primary' : 'warning') }}" style="font-size: 0.65rem;">
                                                            {{ ucfirst($appointment['status']) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-muted py-2">
                                            <small>No appointments scheduled</small>
                                            @if($dateGroup['is_today'])
                                            <div class="mt-1">
                                                <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-plus me-1"></i>
                                                    Schedule
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <p class="mb-2">No appointments scheduled</p>
                        <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Schedule First Appointment
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Charts Row -->
    @if(isset($monthlyStats) && count($monthlyStats) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i>
                        Monthly Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
@if(isset($monthlyStats) && count($monthlyStats) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyStats = @json($monthlyStats);
    
    const labels = Object.keys(monthlyStats);
    const patientsData = Object.values(monthlyStats).map(stat => stat.patients);
    const prescriptionsData = Object.values(monthlyStats).map(stat => stat.prescriptions);
    const revenueData = Object.values(monthlyStats).map(stat => stat.revenue);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'New Patients',
                    data: patientsData,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Prescriptions',
                    data: prescriptionsData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                },
                {
                    label: 'Revenue ($)',
                    data: revenueData,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
});
</script>
@endif
@endpush
@endsection

@push('styles')
<style>
.clinic-logo {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: white;
    padding: 2px;
    object-fit: cover;
    object-position: center;
}

.clinic-logo:hover {
    border-color: #007bff;
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

@media print {
    .clinic-logo {
        border: 1px solid #ddd !important;
        padding: 1px !important;
        max-height: 70px !important;
        max-width: 70px !important;
        object-fit: cover !important;
    }
}
</style>
@endpush
