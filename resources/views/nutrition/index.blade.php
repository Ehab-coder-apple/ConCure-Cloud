@extends('layouts.app')

@section('page-title', __('Nutrition Plans'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-apple-alt text-success"></i>
                        {{ __('Nutrition Plans') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage patient nutrition and diet plans') }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('nutrition.templates') }}" class="btn btn-outline-info">
                        <i class="fas fa-clipboard-list me-1"></i>
                        {{ __('Templates') }}
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Plan') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('nutrition.create.flexible') }}">
                                <i class="fas fa-list-alt me-2 text-success"></i>
                                {{ __('Flexible Meal Options') }}
                                <small class="d-block text-muted">{{ __('Patients choose from meal options') }}</small>
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('nutrition.create.enhanced') }}">
                                <i class="fas fa-utensils me-2"></i>
                                {{ __('Detailed Plan with Foods') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('nutrition.create') }}">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ __('Basic Plan') }}
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Total Plans') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['total']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Active Plans') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['active']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-play-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('Completed') }}</h6>
                            <h2 class="mb-0">{{ number_format($stats['completed']) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">{{ __('This Month') }}</h6>
                            <h2 class="mb-0">{{ number_format($nutritionPlans->where('created_at', '>=', now()->startOfMonth())->count()) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter"></i>
                        {{ __('Filter Plans') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('nutrition.index') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="goal" class="form-label">{{ __('Goal') }}</label>
                                <select class="form-select" id="goal" name="goal">
                                    <option value="">{{ __('All Goals') }}</option>
                                    <option value="weight_loss" {{ request('goal') === 'weight_loss' ? 'selected' : '' }}>{{ __('Weight Loss') }}</option>
                                    <option value="weight_gain" {{ request('goal') === 'weight_gain' ? 'selected' : '' }}>{{ __('Weight Gain') }}</option>
                                    <option value="muscle_gain" {{ request('goal') === 'muscle_gain' ? 'selected' : '' }}>{{ __('Muscle Gain') }}</option>
                                    <option value="diabetic" {{ request('goal') === 'diabetic' ? 'selected' : '' }}>{{ __('Diabetic') }}</option>
                                    <option value="maintenance" {{ request('goal') === 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
                                    <option value="health_improvement" {{ request('goal') === 'health_improvement' ? 'selected' : '' }}>{{ __('Health Improvement') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }}</label>
                                <select class="form-select" id="patient_id" name="patient_id">
                                    <option value="">{{ __('All Patients') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="{{ __('Plan title, number, or patient name...') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Nutrition Plans List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i>
                        {{ __('Nutrition Plans') }}
                    </h6>
                    <div class="text-muted small">
                        {{ __('Total: :count plans', ['count' => $nutritionPlans->total()]) }}
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($nutritionPlans->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Plan') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Goal') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Calories') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nutritionPlans as $plan)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $plan->title }}</div>
                                            <small class="text-muted">{{ $plan->plan_number }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $plan->patient->first_name }} {{ $plan->patient->last_name }}</div>
                                            <small class="text-muted">{{ $plan->patient->patient_id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $plan->goal_display }}</span>
                                        </td>
                                        <td>
                                            @if($plan->duration_days)
                                                {{ $plan->duration_days }} {{ __('days') }}
                                            @else
                                                <span class="text-muted">{{ __('Not specified') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($plan->target_calories)
                                                {{ number_format($plan->target_calories) }} {{ __('kcal') }}
                                            @else
                                                <span class="text-muted">{{ __('Not specified') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $plan->status_badge_class }}">{{ $plan->status_display }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $plan->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $plan->created_at->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('nutrition.show', $plan) }}" class="btn btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($plan->canBeModified())
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('nutrition.edit.enhanced', $plan) }}">
                                                            <i class="fas fa-utensils me-2"></i>
                                                            {{ __('Enhanced Editor') }}
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('nutrition.edit', $plan) }}">
                                                            <i class="fas fa-file-alt me-2"></i>
                                                            {{ __('Basic Editor') }}
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                @endif
                                                <a href="{{ route('nutrition.pdf', $plan) }}" class="btn btn-outline-danger" title="{{ __('PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @if($plan->canBeModified())
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="deletePlan({{ $plan->id }}, '{{ $plan->title }}')"
                                                        title="{{ __('Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-apple-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No nutrition plans found') }}</h5>
                            <p class="text-muted">{{ __('Create your first nutrition plan to get started.') }}</p>
                            <a href="{{ route('nutrition.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Nutrition Plan') }}
                            </a>
                        </div>
                    @endif
                </div>
                @if($nutritionPlans->hasPages())
                <div class="card-footer">
                    {{ $nutritionPlans->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deletePlan(planId, planTitle) {
    // Show confirmation dialog
    if (confirm(`{{ __('Are you sure you want to delete the nutrition plan') }} "${planTitle}"?\n\n{{ __('This action cannot be undone and will permanently remove all associated meals and data.') }}`)) {

        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/nutrition/${planId}`;
        form.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add method override for DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);

        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

@endsection
