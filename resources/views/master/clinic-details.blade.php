@extends('layouts.master')

@section('title', __('Clinic Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-hospital text-primary me-2"></i>
                        {{ $clinic->name }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('master.clinics') }}">{{ __('Clinics') }}</a></li>
                            <li class="breadcrumb-item active">{{ $clinic->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group me-2">
                        @if($clinic->is_trial ?? false)
                            <button type="button" class="btn btn-outline-warning"
                                    onclick="extendTrial({{ $clinic->id }})"
                                    title="{{ __('Extend Trial') }}">
                                <i class="fas fa-clock me-1"></i>
                                {{ __('Extend Trial') }}
                            </button>
                            <button type="button" class="btn btn-outline-success"
                                    onclick="convertTrial({{ $clinic->id }})"
                                    title="{{ __('Convert to Paid') }}">
                                <i class="fas fa-crown me-1"></i>
                                {{ __('Convert to Paid') }}
                            </button>
                        @else
                            <button type="button" class="btn btn-outline-primary"
                                    onclick="extendSubscription({{ $clinic->id }})"
                                    title="{{ __('Extend Subscription') }}">
                                <i class="fas fa-calendar-plus me-1"></i>
                                {{ __('Extend Subscription') }}
                            </button>
                        @endif

                        <button type="button" class="btn btn-outline-{{ ($clinic->is_active ?? true) ? 'warning' : 'success' }}"
                                onclick="toggleClinicStatus({{ $clinic->id }}, {{ ($clinic->is_active ?? true) ? 'false' : 'true' }})"
                                title="{{ ($clinic->is_active ?? true) ? __('Deactivate') : __('Activate') }}">
                            <i class="fas fa-{{ ($clinic->is_active ?? true) ? 'pause' : 'play' }} me-1"></i>
                            {{ ($clinic->is_active ?? true) ? __('Deactivate') : __('Activate') }}
                        </button>
                    </div>

                    <a href="{{ route('master.clinics') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Clinics') }}
                    </a>
                </div>
            </div>

            <!-- Clinic Status Alert -->
            @if(!$clinic->is_active)
            <div class="alert alert-warning mb-4">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ __('This clinic is currently inactive.') }}
            </div>
            @endif

            @if(($clinic->is_trial ?? false) && $clinic->trial_expires_at)
                @php
                    $trialExpiry = \Carbon\Carbon::parse($clinic->trial_expires_at);
                    $isTrialExpired = $trialExpiry->isPast();
                    $trialDaysLeft = $isTrialExpired ? 0 : $trialExpiry->diffInDays();
                @endphp
                @if($isTrialExpired)
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-clock me-2"></i>
                    {{ __('Trial expired') }} {{ $trialExpiry->diffForHumans() }}
                </div>
                @elseif($trialDaysLeft <= 2)
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-clock me-2"></i>
                    {{ __('Trial expires in') }} {{ $trialDaysLeft }} {{ __('days') }}
                </div>
                @endif
            @endif

            <!-- Clinic Information -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Clinic Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">{{ __('Name') }}:</td>
                                            <td>{{ $clinic->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Email') }}:</td>
                                            <td>{{ $clinic->email ?? __('Not provided') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Phone') }}:</td>
                                            <td>{{ $clinic->phone ?? __('Not provided') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Address') }}:</td>
                                            <td>{{ $clinic->address ?? __('Not provided') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Status') }}:</td>
                                            <td>
                                                <span class="badge bg-{{ $clinic->is_active ? 'success' : 'danger' }}">
                                                    {{ $clinic->is_active ? __('Active') : __('Inactive') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">{{ __('Max Users') }}:</td>
                                            <td>{{ $clinic->max_users ?? 10 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Activation Code') }}:</td>
                                            <td>
                                                @php
                                                    $activationCode = DB::table('activation_codes')
                                                        ->where('clinic_id', $clinic->id)
                                                        ->where('type', 'clinic')
                                                        ->first();
                                                @endphp
                                                <code>{{ $activationCode->code ?? __('Not set') }}</code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Activated At') }}:</td>
                                            <td>{{ $clinic->activated_at ? \Carbon\Carbon::parse($clinic->activated_at)->format('M d, Y H:i') : __('Not activated') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Created At') }}:</td>
                                            <td>{{ $clinic->created_at ? \Carbon\Carbon::parse($clinic->created_at)->format('M d, Y H:i') : __('Unknown') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Subscription') }}:</td>
                                            <td>
                                                @if($clinic->is_trial ?? false)
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ __('Trial') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-crown me-1"></i>
                                                        {{ __('Paid') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-shield me-2"></i>
                                {{ __('Administrator') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($clinic->admin_first_name)
                                <div class="text-center mb-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user-md fa-lg"></i>
                                    </div>
                                </div>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">{{ __('Name') }}:</td>
                                        <td>{{ $clinic->admin_first_name }} {{ $clinic->admin_last_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Email') }}:</td>
                                        <td>{{ $clinic->admin_email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Phone') }}:</td>
                                        <td>{{ $clinic->admin_phone ?? __('Not provided') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">{{ __('Last Login') }}:</td>
                                        <td>{{ $clinic->admin_last_login ? \Carbon\Carbon::parse($clinic->admin_last_login)->diffForHumans() : __('Never') }}</td>
                                    </tr>
                                </table>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3"></i>
                                    <p>{{ __('No administrator assigned') }}</p>
                                    <small>{{ __('Clinic not yet activated') }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <div class="display-6 text-primary mb-2">{{ $stats['total_users'] }}</div>
                            <h6 class="text-muted">{{ __('Total Users') }}</h6>
                            <small class="text-success">{{ $stats['active_users'] }} {{ __('active') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="display-6 text-success mb-2">{{ $stats['total_patients'] }}</div>
                            <h6 class="text-muted">{{ __('Total Patients') }}</h6>
                            <small class="text-success">{{ $stats['active_patients'] }} {{ __('active') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <div class="display-6 text-info mb-2">{{ $stats['total_prescriptions'] }}</div>
                            <h6 class="text-muted">{{ __('Total Prescriptions') }}</h6>
                            <small class="text-info">{{ $stats['recent_prescriptions'] }} {{ __('this month') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <div class="display-6 text-warning mb-2">{{ $stats['total_appointments'] }}</div>
                            <h6 class="text-muted">{{ __('Total Appointments') }}</h6>
                            <small class="text-warning">{{ $stats['upcoming_appointments'] }} {{ __('upcoming') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                {{ __('Users by Role') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if(count($usersByRole) > 0)
                                @foreach($usersByRole as $role => $count)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-capitalize">{{ __($role) }}</span>
                                    <span class="badge bg-primary">{{ $count }}</span>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-users-slash fa-2x mb-2"></i>
                                    <p>{{ __('No users found') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                {{ __('Monthly Activity') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($monthlyActivity->count() > 0)
                                <canvas id="activityChart" height="200"></canvas>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <p>{{ __('No activity data available') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                {{ __('Recent Activity') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($recentActivity->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('User') }}</th>
                                                <th>{{ __('Action') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Date') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentActivity as $activity)
                                            <tr>
                                                <td>
                                                    @if($activity->first_name)
                                                        {{ $activity->first_name }} {{ $activity->last_name }}
                                                    @else
                                                        <span class="text-muted">{{ __('System') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $activity->action }}</span>
                                                </td>
                                                <td>{{ $activity->description ?? __('No description') }}</td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($activity->performed_at)->format('M d, Y H:i') }}
                                                    </small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>{{ __('No recent activity') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trial Management Modals -->
@include('master.partials.trial-modals')

@if($monthlyActivity->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    const activityData = @json($monthlyActivity);

    const labels = activityData.map(item => item.month);
    const data = activityData.map(item => item.activity_count);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __("Activity Count") }}',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
});
</script>
@endpush
@endif

@push('scripts')
<script>
// Trial Management Functions
function extendTrial(clinicId) {
    document.getElementById('extend_trial_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('extendTrialModal')).show();
}

function convertTrial(clinicId) {
    document.getElementById('convert_trial_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('convertTrialModal')).show();
}

function extendSubscription(clinicId) {
    document.getElementById('extend_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('extendSubscriptionModal')).show();
}

function toggleClinicStatus(clinicId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this clinic?`)) {
        fetch(`/master/clinics/${clinicId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
        });
    }
}

// Trial Management Form Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Extend Trial Form
    const extendTrialForm = document.getElementById('extendTrialForm');
    if (extendTrialForm) {
        extendTrialForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const clinicId = document.getElementById('extend_trial_clinic_id').value;
            const days = document.getElementById('extend_trial_days').value;

            fetch(`/master/clinics/${clinicId}/extend-trial`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ days: parseInt(days) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('extendTrialModal')).hide();
                    location.reload();
                } else {
                    alert(data.error || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            });
        });
    }

    // Convert Trial Form
    const convertTrialForm = document.getElementById('convertTrialForm');
    if (convertTrialForm) {
        convertTrialForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const clinicId = document.getElementById('convert_trial_clinic_id').value;
            const months = document.getElementById('convert_trial_months').value;
            const planType = document.getElementById('convert_trial_plan_type').value;

            fetch(`/master/clinics/${clinicId}/convert-trial`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    months: parseInt(months),
                    plan_type: planType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    bootstrap.Modal.getInstance(document.getElementById('convertTrialModal')).hide();
                    location.reload();
                } else {
                    alert(data.error || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            });
        });
    }

    // Extend Subscription Form
    const extendSubscriptionForm = document.getElementById('extendSubscriptionForm');
    if (extendSubscriptionForm) {
        extendSubscriptionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const clinicId = document.getElementById('extend_clinic_id').value;
            const months = document.getElementById('extend_months').value;

            fetch(`/master/clinics/${clinicId}/extend-subscription`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ months: parseInt(months) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('{{ __("Subscription extended successfully!") }}');
                    bootstrap.Modal.getInstance(document.getElementById('extendSubscriptionModal')).hide();
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("An error occurred. Please try again.") }}');
            });
        });
    }
});
</script>
@endpush
@endsection
