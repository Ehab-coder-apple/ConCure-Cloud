@extends('master.layouts.app')

@section('title', 'Create New Clinic')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Create New Clinic
                    </h1>
                    <p class="text-muted mb-0">Add a new clinic to the ConCure system</p>
                </div>
                <div>
                    <a href="{{ route('master.clinics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Clinics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('master.clinics.store') }}">
        @csrf

        <div class="row">
            <!-- Clinic Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-hospital me-2"></i>
                            Clinic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Clinic Name *</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="Enter clinic name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Clinic Email *</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="clinic@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="max_users" class="form-label">Maximum Users *</label>
                                <input type="number"
                                       class="form-control @error('max_users') is-invalid @enderror"
                                       id="max_users"
                                       name="max_users"
                                       value="{{ old('max_users', 10) }}"
                                       min="1"
                                       max="1000"
                                       required>
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum number of users allowed for this clinic</div>
                            </div>
                        </div>

	                        <div class="row">
	                            <div class="col-md-6 mb-3">
	                                <label for="speciality" class="form-label">Speciality</label>
	                                <select class="form-select @error('speciality') is-invalid @enderror" id="speciality" name="speciality">
	                                    <option value="">Select speciality</option>
	                                    @foreach(($specialities ?? []) as $sp)
	                                        <option value="{{ $sp }}" {{ old('speciality') === $sp ? 'selected' : '' }}>{{ $sp }}</option>
	                                    @endforeach
	                                </select>
	                                @error('speciality')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Clinic Type</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="clinic_type" id="clinic_type_tenant" value="tenant" {{ old('clinic_type', 'tenant') === 'tenant' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="clinic_type_tenant">Tenant Clinic</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="clinic_type" id="clinic_type_demo" value="demo" {{ old('clinic_type') === 'demo' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="clinic_type_demo">Demo Clinic (Sales)</label>
                                </div>
                                @error('clinic_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Demo clinics are for sales/testing only and are excluded from financial reports.</div>
                            </div>
                        </div>


	                        <div class="row">
	                            <div class="col-md-4 mb-3">
	                                <label for="city" class="form-label">City</label>
	                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="City">
	                                @error('city')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                            <div class="col-md-4 mb-3">
	                                <label for="area" class="form-label">Area</label>
	                                <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area') }}" placeholder="Area">
	                                @error('area')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                            <div class="col-md-4 mb-3">
	                                <label for="street" class="form-label">Street</label>
	                                <input type="text" class="form-control @error('street') is-invalid @enderror" id="street" name="street" value="{{ old('street') }}" placeholder="Street">
	                                @error('street')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                        </div>
                    </div>
                </div>
                <!-- Billing & Fees -->
                <div class="card mb-4" id="billingCard">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Billing & Fees
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="billing_user_price" class="form-label">Price per User (monthly)</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('concure.currency_symbol', '$') }}</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('billing_user_price') is-invalid @enderror" id="billing_user_price" name="billing_user_price" value="{{ old('billing_user_price') }}" placeholder="e.g. 5.00">
                                </div>
                                @error('billing_user_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Set the monthly fee charged per user for this clinic (optional).</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="billing_user_count" class="form-label">Billable Users</label>
                                <input type="number" min="1" step="1" class="form-control @error('billing_user_count') is-invalid @enderror" id="billing_user_count" name="billing_user_count" value="{{ old('billing_user_count') }}" placeholder="Leave empty to use Maximum Users">
                                @error('billing_user_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">If empty, the Maximum Users value will be used.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="service_charge_amount" class="form-label">One-time Service Charge</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('concure.currency_symbol', '$') }}</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('service_charge_amount') is-invalid @enderror" id="service_charge_amount" name="service_charge_amount" value="{{ old('service_charge_amount') }}" placeholder="e.g. 100.00">
                                </div>
                                @error('service_charge_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Charged once when the contract is signed.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="service_charge_date" class="form-label">Service Charge Date</label>
                                <input type="date" class="form-control @error('service_charge_date') is-invalid @enderror" id="service_charge_date" name="service_charge_date" value="{{ old('service_charge_date') }}">
                                @error('service_charge_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">If set, service charge will be included in reports for the selected date range.</div>
                            </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="service_charge_note" class="form-label">Service Charge Note</label>
                                <input type="text" class="form-control @error('service_charge_note') is-invalid @enderror" id="service_charge_note" name="service_charge_note" value="{{ old('service_charge_note') }}" placeholder="Optional note (e.g. setup scope, payment reference)">
                                @error('service_charge_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        </div>
                        <div class="text-muted small">Note: Demo clinics are excluded from Master financial reports.</div>
                    </div>
                </div>


                <!-- Admin User Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-shield me-2"></i>
                            Admin User Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This will create the main administrator account for the clinic.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_first_name" class="form-label">First Name *</label>
                                <input type="text"
                                       class="form-control @error('admin_first_name') is-invalid @enderror"
                                       id="admin_first_name"
                                       name="admin_first_name"
                                       value="{{ old('admin_first_name') }}"
                                       required
                                       placeholder="Enter first name">
                                @error('admin_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="admin_last_name" class="form-label">Last Name *</label>
                                <input type="text"
                                       class="form-control @error('admin_last_name') is-invalid @enderror"
                                       id="admin_last_name"
                                       name="admin_last_name"
                                       value="{{ old('admin_last_name') }}"
                                       required
                                       placeholder="Enter last name">
                                @error('admin_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="admin_email" class="form-label">Email Address *</label>
                                <input type="email"
                                       class="form-control @error('admin_email') is-invalid @enderror"
                                       id="admin_email"
                                       name="admin_email"
                                       value="{{ old('admin_email') }}"
                                       required
                                       placeholder="admin@example.com">
                                @error('admin_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="admin_password" class="form-label">Password *</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('admin_password') is-invalid @enderror"
                                           id="admin_password"
                                           name="admin_password"
                                           required
                                           minlength="8"
                                           placeholder="Enter secure password">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleAdminPassword" aria-label="Show password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('admin_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters required</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary & Actions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-check-circle me-2"></i>
                            Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>What will be created:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-hospital text-primary me-2"></i>New clinic account</li>
                                <li><i class="fas fa-user-shield text-success me-2"></i>Admin user account</li>
                                <li><i class="fas fa-key text-info me-2"></i>Login credentials</li>
                                <li><i class="fas fa-cog text-warning me-2"></i>Default settings</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6>Default Features:</h6>
                            <ul class="list-unstyled small text-muted">
                                <li>• Patient Management</li>
                                <li>• Prescription System</li>
                                <li>• Appointment Scheduling</li>
                                <li>• Lab Requests</li>
                                <li>• Financial Management</li>
                                <li>• User Guide (4 languages)</li>
                            </ul>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Create Clinic
                            </button>
                            <a href="{{ route('master.clinics.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle admin password visibility
        const input = document.getElementById('admin_password');
        const btn = document.getElementById('toggleAdminPassword');
        if (input && btn) {
            btn.addEventListener('click', function() {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
                btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }

        // Hide Billing & Fees when Demo clinic selected
        const billingCard = document.getElementById('billingCard');
        function updateBillingVisibility() {
            if (!billingCard) return;
            const selected = document.querySelector('input[name="clinic_type"]:checked');
            const isDemo = selected && selected.value === 'demo';
            billingCard.classList.toggle('d-none', !!isDemo);
            billingCard.querySelectorAll('input, select, textarea, button')
                .forEach(el => { el.disabled = !!isDemo; });
        }
        document.querySelectorAll('input[name="clinic_type"]').forEach(r => {
            r.addEventListener('change', updateBillingVisibility);
        });
        updateBillingVisibility();
    });
</script>
@endpush

        </div>
    </form>
</div>
@endsection
