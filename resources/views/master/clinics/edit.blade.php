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

                            <div class="col-md-6 mb-3">
                                <label for="storage_limit_gb" class="form-label">
                                    <i class="fas fa-hdd me-1"></i> Storage Limit (GB)
                                </label>
                                @php
                                    $currentLimitGb = round(($clinic->storage_limit ?? \App\Services\StorageQuotaService::DEFAULT_LIMIT) / (1024 * 1024 * 1024), 2);
                                @endphp
                                <input type="number"
                                       class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                       id="storage_limit_gb"
                                       name="storage_limit_gb"
                                       value="{{ old('storage_limit_gb', $currentLimitGb) }}"
                                       step="0.5"
                                       min="0.1"
                                       max="10000">
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maximum storage space in GB for file uploads (default: 5 GB)</div>
                            </div>
                        </div>

	                        <div class="row">
	                            <div class="col-md-6 mb-3">
	                                <label for="speciality" class="form-label">Speciality</label>
	                                <input type="text" list="speciality-list" class="form-control @error('speciality') is-invalid @enderror" id="speciality" name="speciality" value="{{ old('speciality', $clinic->speciality) }}" placeholder="Select or type a speciality">
	                                <datalist id="speciality-list">
	                                    @foreach(($specialities ?? []) as $sp)
	                                        <option value="{{ $sp }}">
	                                    @endforeach
	                                </datalist>
	                                @error('speciality')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                            <div class="col-md-6 mb-3">
	                                <label for="country_id" class="form-label">
	                                    <i class="fas fa-globe me-1"></i> Country
	                                </label>
	                                <select class="form-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
	                                    <option value="">— No country assigned —</option>
	                                    @foreach(($countries ?? []) as $country)
	                                        <option value="{{ $country->id }}" {{ old('country_id', $clinic->country_id) == $country->id ? 'selected' : '' }}>
	                                            {{ $country->flag_emoji }} {{ $country->name }} ({{ $country->iso_code }})
	                                        </option>
	                                    @endforeach
	                                </select>
	                                @error('country_id')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                                <div class="form-text">Used by the Vaccination module to auto-assign the country's default schedule.</div>
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

                        <!-- Export Permission (visible for demo clinics) -->
                        <div class="row" id="exportPermissionRow">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="can_export" id="can_export" value="1"
                                           {{ old('can_export', $clinic->can_export) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="can_export">
                                        <i class="fas fa-file-export me-1"></i> Allow Data Export
                                    </label>
                                </div>
                                <div class="form-text">When enabled, this demo clinic can export data. By default, demo clinics cannot export any data.</div>
                            </div>
                        </div>

	                        <div class="mb-2 text-muted small">
	                            Current Address: {{ $clinic->formatted_address ?? ($clinic->address ?? 'Not provided') }}
	                        </div>
	                        <div class="row">
	                            <div class="col-md-4 mb-4">
	                                <label for="city" class="form-label">City</label>
	                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $clinic->city) }}" placeholder="City">
	                                @error('city')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                            <div class="col-md-4 mb-4">
	                                <label for="area" class="form-label">Area</label>
	                                <input type="text" class="form-control @error('area') is-invalid @enderror" id="area" name="area" value="{{ old('area', $clinic->area) }}" placeholder="Area">
	                                @error('area')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
	                            </div>
	                            <div class="col-md-4 mb-4">
	                                <label for="street" class="form-label">Street</label>
	                                <input type="text" class="form-control @error('street') is-invalid @enderror" id="street" name="street" value="{{ old('street', $clinic->street) }}" placeholder="Street">
	                                @error('street')
	                                    <div class="invalid-feedback">{{ $message }}</div>
	                                @enderror
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
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="service_charge_note" class="form-label">Service Charge Note</label>
                                        <input type="text" class="form-control @error('service_charge_note') is-invalid @enderror" id="service_charge_note" name="service_charge_note" value="{{ old('service_charge_note', $clinic->service_charge_note) }}" placeholder="Optional note (e.g. setup scope, payment reference)">
                                        @error('service_charge_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="text-muted small">Note: Demo clinics are excluded from Master financial reports.</div>
                            </div>
                        </div>

                        <!-- Module Access Control -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-cubes me-2"></i>
                                    Module Access Control
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info small mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select the modules this clinic can access. If none are selected, all modules will be enabled by default.
                                </div>

                                @foreach($moduleGroups as $groupKey => $group)
                                <div class="mb-3">
                                    <h6 class="text-secondary fw-bold border-bottom pb-2 mb-2">
                                        <i class="{{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
                                    </h6>
                                    <div class="row ps-2">
                                        @foreach($group['modules'] as $key => $label)
                                        <div class="col-md-6 col-lg-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input module-checkbox" type="checkbox"
                                                       name="enabled_modules[]"
                                                       value="{{ $key }}"
                                                       id="module_{{ $key }}"
                                                       {{ in_array($key, old('enabled_modules', $clinic->enabled_modules ?? [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="module_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach

                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllModules">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllModules">Deselect All</button>
                                </div>
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
                                    <div class="form-text">Username must contain only letters, numbers, periods, dashes, and underscores</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="admin_email" class="form-label">Admin Email *</label>
                                    <input type="email"
                                           class="form-control @error('admin_email') is-invalid @enderror"
                                           id="admin_email"
                                           name="admin_email"
                                           value="{{ old('admin_email', $adminUser->email) }}"
                                           required
                                           placeholder="Enter admin email">
                                    @error('admin_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Admin user's email address</div>
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
            <div class="card mb-4">
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

            <!-- WhatsApp Configuration -->
            @php
                $waSettings = $clinic->settings['whatsapp'] ?? [];
                $waConfigured = !empty($waSettings['meta_phone_number_id']) && !empty($waSettings['meta_access_token']);
            @endphp
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fab fa-whatsapp me-2"></i>
                        WhatsApp Configuration
                    </h6>
                </div>
                <div class="card-body">
                    @if($waConfigured)
                    <div class="alert alert-success py-2 mb-3">
                        <i class="fas fa-check-circle me-1"></i>
                        <strong>Connected</strong>
                        @if(!empty($waSettings['meta_verified_name']))
                            — {{ $waSettings['meta_verified_name'] }}
                        @endif
                        @if(!empty($waSettings['meta_phone_display']))
                            <br><small>{{ $waSettings['meta_phone_display'] }}</small>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Not configured
                    </div>
                    @endif

                    <form id="whatsappConfigForm">
                        <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
                        <div class="mb-3">
                            <label for="wa_phone_number_id" class="form-label">Phone Number ID</label>
                            <input type="text" class="form-control form-control-sm" id="wa_phone_number_id"
                                   value="{{ $waSettings['meta_phone_number_id'] ?? '' }}"
                                   placeholder="123456789012345" required>
                            <small class="form-text text-muted">From Meta Developer Dashboard</small>
                        </div>
                        <div class="mb-3">
                            <label for="wa_access_token" class="form-label">Access Token</label>
                            <input type="password" class="form-control form-control-sm" id="wa_access_token"
                                   placeholder="{{ $waConfigured ? '••••••••••••••••' : 'EAAxxxxxxx...' }}"
                                   {{ $waConfigured ? '' : 'required' }}>
                            <small class="form-text text-muted">Permanent token from Business Settings</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-sm" id="btnSaveWa">
                                <i class="fas fa-save me-1"></i>
                                {{ $waConfigured ? 'Update' : 'Save & Connect' }}
                            </button>
                        </div>
                    </form>
                    <div id="waConfigStatus" class="mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            r.addEventListener('change', updateExportPermissionVisibility);
        });
        updateBillingVisibility();

        // Show/hide export permission toggle based on clinic type
        const exportPermissionRow = document.getElementById('exportPermissionRow');
        function updateExportPermissionVisibility() {
            if (!exportPermissionRow) return;
            const selected = document.querySelector('input[name="clinic_type"]:checked');
            const isDemo = selected && selected.value === 'demo';
            exportPermissionRow.style.display = isDemo ? '' : 'none';
        }
        updateExportPermissionVisibility();

        // Module select/deselect all
        const selectAll = document.getElementById('selectAllModules');
        const deselectAll = document.getElementById('deselectAllModules');
        if (selectAll) {
            selectAll.addEventListener('click', function() {
                document.querySelectorAll('.module-checkbox').forEach(cb => cb.checked = true);
            });
        }
        if (deselectAll) {
            deselectAll.addEventListener('click', function() {
                document.querySelectorAll('.module-checkbox').forEach(cb => cb.checked = false);
            });
        }

        // WhatsApp Config Form
        document.getElementById('whatsappConfigForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const clinicId = this.querySelector('input[name="clinic_id"]').value;
            const phoneNumberId = document.getElementById('wa_phone_number_id').value;
            const accessToken = document.getElementById('wa_access_token').value;
            const statusDiv = document.getElementById('waConfigStatus');
            const btn = document.getElementById('btnSaveWa');

            if (!phoneNumberId.trim()) { alert('Please enter the Phone Number ID'); return; }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
            statusDiv.style.display = 'block';
            statusDiv.className = 'mt-2 alert alert-info py-1 small';
            statusDiv.textContent = 'Verifying credentials with Meta API...';

            fetch(`/master/clinics/${clinicId}/whatsapp-config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ meta_phone_number_id: phoneNumberId, meta_access_token: accessToken })
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Update';
                if (data.success) {
                    statusDiv.className = 'mt-2 alert alert-success py-1 small';
                    statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    statusDiv.className = 'mt-2 alert alert-danger py-1 small';
                    statusDiv.innerHTML = '<i class="fas fa-times-circle"></i> ' + (data.message || 'Failed');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Save & Connect';
                statusDiv.className = 'mt-2 alert alert-danger py-1 small';
                statusDiv.textContent = 'Error: ' + err.message;
            });
        });
    });
</script>
@endpush
@endsection

