@extends('master.layouts.app')

@section('title', 'Edit Clinic')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Clinic
                    </h1>
                    <p class="text-muted mb-0">Update clinic details and limits</p>
                </div>
                <div>
                    <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Back to Clinic
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                    <form method="POST" action="{{ route('master.clinics.update', $clinic) }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Clinic Name *</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $clinic->name) }}"
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
                                       value="{{ old('email', $clinic->email) }}"
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
                                       value="{{ old('phone', $clinic->phone) }}"
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
                                       value="{{ old('max_users', $clinic->max_users) }}"
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
                            <div class="col-md-12 mb-3">
                                <label class="form-label d-block">Clinic Type</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="clinic_type" id="clinic_type_tenant" value="tenant" {{ old('clinic_type', $clinic->is_demo ? 'demo' : 'tenant') === 'tenant' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="clinic_type_tenant">Tenant Clinic</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="clinic_type" id="clinic_type_demo" value="demo" {{ old('clinic_type', $clinic->is_demo ? 'demo' : 'tenant') === 'demo' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="clinic_type_demo">Demo Clinic (Sales)</label>
                                </div>
                                @error('clinic_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Changing clinic type affects reporting. Demo clinics are excluded from financial reports.</div>
                            </div>
                        </div>


                        <div class="mb-4">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address"
                                      name="address"
                                      rows="3"
                                      placeholder="Enter clinic address">{{ old('address', $clinic->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Billing & Fees -->
                        <div class="card mb-4">
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
                                            <input type="number" step="0.01" min="0" class="form-control @error('billing_user_price') is-invalid @enderror" id="billing_user_price" name="billing_user_price" value="{{ old('billing_user_price', $clinic->billing_user_price) }}" placeholder="e.g. 5.00">
                                        </div>
                                        @error('billing_user_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Set the monthly fee charged per user for this clinic (optional).</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="billing_user_count" class="form-label">Billable Users</label>
                                        <input type="number" min="1" step="1" class="form-control @error('billing_user_count') is-invalid @enderror" id="billing_user_count" name="billing_user_count" value="{{ old('billing_user_count', $clinic->billing_user_count) }}" placeholder="Leave empty to use Maximum Users">
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
                                            <input type="number" step="0.01" min="0" class="form-control @error('service_charge_amount') is-invalid @enderror" id="service_charge_amount" name="service_charge_amount" value="{{ old('service_charge_amount', $clinic->service_charge_amount) }}" placeholder="e.g. 100.00">
                                        </div>
                                        @error('service_charge_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Charged once when the contract is signed.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="service_charge_date" class="form-label">Service Charge Date</label>
                                        <input type="date" class="form-control @error('service_charge_date') is-invalid @enderror" id="service_charge_date" name="service_charge_date" value="{{ old('service_charge_date', optional($clinic->service_charge_date)->toDateString()) }}">
                                        @error('service_charge_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">If set, service charge will be included in reports for the selected date range.</div>
                                    </div>
                                </div>
                                <div class="text-muted small">Note: Demo clinics are excluded from Master financial reports.</div>
                            </div>
                        </div>

                        <!-- Admin User Information -->
                        @if($adminUser)
                            <hr class="my-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user-shield me-2"></i>
                                Admin User Information
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admin_first_name" class="form-label">Admin First Name *</label>
                                    <input type="text"
                                           class="form-control @error('admin_first_name') is-invalid @enderror"
                                           id="admin_first_name"
                                           name="admin_first_name"
                                           value="{{ old('admin_first_name', $adminUser->first_name) }}"
                                           required
                                           placeholder="Enter admin first name">
                                    @error('admin_first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="admin_last_name" class="form-label">Admin Last Name *</label>
                                    <input type="text"
                                           class="form-control @error('admin_last_name') is-invalid @enderror"
                                           id="admin_last_name"
                                           name="admin_last_name"
                                           value="{{ old('admin_last_name', $adminUser->last_name) }}"
                                           required
                                           placeholder="Enter admin last name">
                                    @error('admin_last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admin_username" class="form-label">Admin Username *</label>
                                    <input type="text"
                                           class="form-control @error('admin_username') is-invalid @enderror"
                                           id="admin_username"
                                           name="admin_username"
                                           value="{{ old('admin_username', $adminUser->username) }}"
                                           required
                                           placeholder="Enter admin username">
                                    @error('admin_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Username must contain only letters, numbers, dashes, and underscores</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email"
                                           class="form-control"
                                           id="admin_email"
                                           value="{{ $adminUser->email }}"
                                           readonly
                                           disabled>
                                    <div class="form-text">Email cannot be changed from this form</div>
                                </div>
                            </div>
                        @endif

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                            <a href="{{ route('master.clinics.show', $clinic) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Tools -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-shield me-2"></i>
                        Admin Tools
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-muted small">
                        Reset the clinic admin's password. Admin user information (name, username) can be edited in the main form.
                    </div>
                    <form method="POST" action="{{ route('master.clinics.reset-admin-password', $clinic) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password"
                                   class="form-control @error('new_password') is-invalid @enderror"
                                   id="new_password" name="new_password" required minlength="8">
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required minlength="8">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-2"></i>
                                Reset Admin Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

