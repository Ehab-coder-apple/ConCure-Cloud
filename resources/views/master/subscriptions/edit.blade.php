@extends('master.layouts.app')

@section('title', 'Edit Subscription - ' . $clinic->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Subscription
                    </h1>
                    <p class="text-muted mb-0">{{ $clinic->name }} - Subscription Settings</p>
                </div>
                <div>
                    <a href="{{ route('master.subscriptions.show', $clinic) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subscription Settings</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master.subscriptions.update', $clinic) }}">
                        @csrf
                        @method('PUT')

                        <!-- Plan Selection -->
                        <div class="mb-4">
                            <label for="plan_id" class="form-label">
                                <i class="fas fa-layer-group me-2"></i>
                                Subscription Plan
                            </label>
                            <select id="plan_id" name="plan_id" class="form-select">
                                <option value="">No plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ (string)old('plan_id', $clinic->plan_id) === (string)$plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} — ${{ number_format($plan->monthly_price,2) }}/month @if($plan->yearly_price) or ${{ number_format($plan->yearly_price,2) }}/year @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Changing plan may also update the clinic's max users to match the plan.</div>
                        </div>

                        <!-- Billing Cycle -->
                        <div class="mb-4">
                            <label for="billing_cycle" class="form-label">
                                <i class="fas fa-sync-alt me-2"></i>
                                Billing Cycle
                            </label>
                            <select id="billing_cycle" name="billing_cycle" class="form-select">
                                @php($cycle = old('billing_cycle', $clinic->billing_cycle ?? 'monthly'))
                                <option value="monthly" {{ $cycle === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ $cycle === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>

                            <!-- Custom Prices (optional) -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign me-2"></i>
                                    Custom Prices (optional)
                                </label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="custom_monthly_price" class="form-label small text-muted">Monthly Price</label>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control @error('custom_monthly_price') is-invalid @enderror"
                                               id="custom_monthly_price" name="custom_monthly_price"
                                               value="{{ old('custom_monthly_price', $clinic->custom_monthly_price) }}"
                                               placeholder="Leave blank to use plan's monthly price">
                                        @error('custom_monthly_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="custom_yearly_price" class="form-label small text-muted">Yearly Price</label>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control @error('custom_yearly_price') is-invalid @enderror"
                                               id="custom_yearly_price" name="custom_yearly_price"
                                               value="{{ old('custom_yearly_price', $clinic->custom_yearly_price) }}"
                                               placeholder="Leave blank to use plan's yearly price">
                                        @error('custom_yearly_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-text">If set, these values override the plan prices for this clinic.</div>
                            </div>


                        <!-- User Limit -->
                        <div class="mb-4">
                            <label for="max_users" class="form-label">
                                <i class="fas fa-users me-2"></i>
                                Maximum Users
                            </label>
                            <input type="number"
                                   class="form-control @error('max_users') is-invalid @enderror"
                                   id="max_users"
                                   name="max_users"
                                   value="{{ old('max_users', $clinic->max_users) }}"
                                   min="1"
                                   max="1000"
                                   required>
                            <div class="form-text">
                                Current users: {{ $clinic->users->count() }} / {{ $clinic->max_users }}
                            </div>
                            @error('max_users')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Usage Display -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->users->count() }}</div>
                                        <div class="small text-muted">Active Users</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->patients->count() }}</div>
                                        <div class="small text-muted">Patients</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->prescriptions->count() }}</div>
                                        <div class="small text-muted">Prescriptions</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <div class="h5 mb-0">{{ $clinic->appointments->count() }}</div>
                                        <div class="small text-muted">Appointments</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Update Subscription
                                </button>
                                <a href="{{ route('master.subscriptions.show', $clinic) }}" class="btn btn-outline-secondary ms-2">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Plan Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Plan Features</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Configurable user limits
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Unlimited patients
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Prescription management
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Appointment scheduling
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Basic reporting
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Email support
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Coming Soon -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Coming Soon</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Multiple subscription plans
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Plan upgrades/downgrades
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Usage-based billing
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Advanced analytics
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            Custom feature limits
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
