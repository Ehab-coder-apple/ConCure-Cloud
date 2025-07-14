@extends('layouts.master')

@section('title', __('Trial Analytics'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-info me-2"></i>
                        {{ __('Trial Analytics') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Trial Analytics') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('master.clinics') }}" class="btn btn-primary">
                        <i class="fas fa-hospital me-1"></i>
                        {{ __('Manage Clinics') }}
                    </a>
                </div>
            </div>

            <!-- Trial Statistics Overview -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <div class="display-4 text-info mb-2">{{ $trialStats['total_trials'] }}</div>
                            <h6 class="text-muted">{{ __('Total Trials') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="display-4 text-success mb-2">{{ $trialStats['active_trials'] }}</div>
                            <h6 class="text-muted">{{ __('Active Trials') }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <div class="display-4 text-warning mb-2">{{ $trialStats['expiring_soon'] }}</div>
                            <h6 class="text-muted">{{ __('Expiring Soon') }}</h6>
                            <small class="text-muted">({{ __('Next 7 days') }})</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <div class="display-4 text-danger mb-2">{{ $trialStats['expired_trials'] }}</div>
                            <h6 class="text-muted">{{ __('Expired Trials') }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expiring Trials Alert -->
            @if($expiringTrials->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Trials Expiring Soon') }}
                                <span class="badge bg-dark ms-2">{{ $expiringTrials->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Clinic') }}</th>
                                            <th>{{ __('Administrator') }}</th>
                                            <th>{{ __('Trial Expires') }}</th>
                                            <th>{{ __('Days Left') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expiringTrials as $clinic)
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($clinic->trial_expires_at);
                                            $daysLeft = $expiryDate->diffInDays();
                                        @endphp
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $clinic->name }}</div>
                                                    <small class="text-muted">{{ $clinic->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($clinic->admin_first_name)
                                                    <div>
                                                        <div>{{ $clinic->admin_first_name }} {{ $clinic->admin_last_name }}</div>
                                                        <small class="text-muted">{{ $clinic->admin_email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">{{ __('Not activated') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $daysLeft <= 1 ? 'danger' : 'warning' }}">
                                                    {{ $expiryDate->format('M d, Y') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-{{ $daysLeft <= 1 ? 'danger' : 'warning' }} fw-bold">
                                                    {{ $daysLeft }} {{ __('days') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-warning" 
                                                            onclick="extendTrial({{ $clinic->id }})"
                                                            title="{{ __('Extend Trial') }}">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="convertTrial({{ $clinic->id }})"
                                                            title="{{ __('Convert to Paid') }}">
                                                        <i class="fas fa-crown"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Trial Conversion Chart -->
            @if($trialConversions->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('Trial Conversion Trends') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="conversionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-tools me-2"></i>
                                {{ __('Trial Management Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <a href="{{ route('master.clinics') }}?subscription=trial" class="btn btn-outline-info w-100">
                                        <i class="fas fa-clock d-block mb-2 fa-2x"></i>
                                        {{ __('View All Trials') }}
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('master.clinics') }}?subscription=expired" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-exclamation-triangle d-block mb-2 fa-2x"></i>
                                        {{ __('Expired Trials') }}
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('master.analytics') }}" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-chart-line d-block mb-2 fa-2x"></i>
                                        {{ __('Full Analytics') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($trialConversions->count() > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('conversionChart').getContext('2d');
    const conversions = @json($trialConversions);
    
    const labels = conversions.map(item => item.month);
    const data = conversions.map(item => item.conversions);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __("Trial Conversions") }}',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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
});

// Trial management functions (reuse from clinics page)
function extendTrial(clinicId) {
    const days = prompt('{{ __("Extend trial by how many days?") }}', '7');
    if (days && !isNaN(days) && days > 0) {
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
                location.reload();
            } else {
                alert(data.error || 'An error occurred');
            }
        });
    }
}

function convertTrial(clinicId) {
    if (confirm('{{ __("Convert this trial to a paid subscription?") }}')) {
        const months = prompt('{{ __("Subscription duration in months:") }}', '12');
        if (months && !isNaN(months) && months > 0) {
            fetch(`/master/clinics/${clinicId}/convert-trial`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    months: parseInt(months),
                    plan_type: 'professional'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.error || 'An error occurred');
                }
            });
        }
    }
}
</script>
@endpush
@endif
@endsection
