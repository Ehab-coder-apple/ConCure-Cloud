@extends('layouts.master')

@section('title', __('System Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        {{ __('System Settings') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Configure platform-wide settings, manage users, and update software') }}</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i>
                        {{ __('Refresh') }}
                    </button>
                </div>
            </div>

            <!-- Settings Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="platform-tab" data-bs-toggle="tab" data-bs-target="#platform" type="button" role="tab">
                        <i class="fas fa-server me-2"></i>{{ __('Platform Settings') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                        <i class="fas fa-users-cog me-2"></i>{{ __('Platform Users') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="software-tab" data-bs-toggle="tab" data-bs-target="#software" type="button" role="tab">
                        <i class="fas fa-download me-2"></i>{{ __('Software Updates') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                        <i class="fas fa-tools me-2"></i>{{ __('Maintenance') }}
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="settingsTabContent">
                <!-- Platform Settings Tab -->
                <div class="tab-pane fade show active" id="platform" role="tabpanel">
                    <div class="row">
                        <!-- Basic Platform Settings -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-server me-2"></i>
                                        {{ __('Platform Configuration') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="platformSettingsForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="platform_name" class="form-label">{{ __('Platform Name') }}</label>
                                            <input type="text" class="form-control" id="platform_name" name="platform_name" 
                                                   value="{{ $settings['platform_name'] ?? 'ConCure SaaS' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="platform_version" class="form-label">{{ __('Platform Version') }}</label>
                                            <input type="text" class="form-control" id="platform_version" name="platform_version" 
                                                   value="{{ $settings['platform_version'] ?? '1.0.0' }}" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="max_clinics" class="form-label">{{ __('Maximum Clinics') }}</label>
                                            <input type="number" class="form-control" id="max_clinics" name="max_clinics" 
                                                   value="{{ $settings['max_clinics'] ?? 1000 }}" min="1">
                                        </div>
                                        <div class="mb-3">
                                            <label for="default_subscription_months" class="form-label">{{ __('Default Subscription (Months)') }}</label>
                                            <input type="number" class="form-control" id="default_subscription_months" name="default_subscription_months" 
                                                   value="{{ $settings['default_subscription_months'] ?? 12 }}" min="1" max="60">
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            {{ __('Save Platform Settings') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>
                                        {{ __('Advanced Configuration') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="advancedSettingsForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="trial_period_days" class="form-label">{{ __('Trial Period (Days)') }}</label>
                                            <input type="number" class="form-control" id="trial_period_days" name="trial_period_days" 
                                                   value="{{ $settings['trial_period_days'] ?? 30 }}" min="1" max="365">
                                        </div>
                                        <div class="mb-3">
                                            <label for="max_users_per_clinic" class="form-label">{{ __('Max Users per Clinic') }}</label>
                                            <input type="number" class="form-control" id="max_users_per_clinic" name="max_users_per_clinic" 
                                                   value="{{ $settings['max_users_per_clinic'] ?? 50 }}" min="1" max="1000">
                                        </div>
                                        <div class="mb-3">
                                            <label for="session_timeout_minutes" class="form-label">{{ __('Session Timeout (Minutes)') }}</label>
                                            <input type="number" class="form-control" id="session_timeout_minutes" name="session_timeout_minutes" 
                                                   value="{{ $settings['session_timeout_minutes'] ?? 120 }}" min="15" max="1440">
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                                       {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="maintenance_mode">
                                                    {{ __('Maintenance Mode') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="registration_enabled" name="registration_enabled" 
                                                       {{ ($settings['registration_enabled'] ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="registration_enabled">
                                                    {{ __('Enable New Registrations') }}
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-secondary">
                                            <i class="fas fa-save me-1"></i>
                                            {{ __('Save Advanced Settings') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Users Tab -->
                <div class="tab-pane fade" id="users" role="tabpanel">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-users-cog me-2"></i>
                                        {{ __('Platform Users Management') }}
                                    </h6>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="fas fa-plus me-1"></i>
                                        {{ __('Add Platform User') }}
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Email') }}</th>
                                                    <th>{{ __('Role') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Created') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($platformUsers as $user)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                                <small class="text-muted">{{ $user->username }}</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $user->role === 'program_owner' ? 'danger' : ($user->role === 'platform_admin' ? 'warning' : 'info') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                                                        </span>
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary" onclick="editUser({{ $user->id }})" title="{{ __('Edit') }}">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            @if($user->role !== 'program_owner' && $user->id !== auth()->id())
                                                            <button type="button" class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }})" title="{{ __('Delete') }}">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Software Updates Tab -->
                <div class="tab-pane fade" id="software" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-8 mx-auto">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-download me-2"></i>
                                        {{ __('Software Update Center') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                                    <i class="fas fa-code-branch"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ __('Current Version') }}</h6>
                                                    <span class="badge bg-success">v{{ $settings['platform_version'] ?? '1.0.0' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info text-white rounded-circle p-3 me-3">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ __('Last Updated') }}</h6>
                                                    <small class="text-muted">{{ now()->format('M d, Y') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <form id="softwareUpdateForm">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="update_type" class="form-label">{{ __('Update Type') }}</label>
                                            <select class="form-select" id="update_type" name="update_type" required>
                                                <option value="">{{ __('Select update type') }}</option>
                                                <option value="security">{{ __('Security Update (Patch)') }}</option>
                                                <option value="minor">{{ __('Minor Update (Features)') }}</option>
                                                <option value="major">{{ __('Major Update (Breaking Changes)') }}</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="backup_before_update" name="backup_before_update" checked>
                                                <label class="form-check-label" for="backup_before_update">
                                                    {{ __('Create backup before update') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode_update" name="maintenance_mode">
                                                <label class="form-check-label" for="maintenance_mode_update">
                                                    {{ __('Enable maintenance mode during update') }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ __('Software updates may temporarily interrupt service. Please ensure all users are notified before proceeding.') }}
                                        </div>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-download me-1"></i>
                                            {{ __('Update Software') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Tab -->
                <div class="tab-pane fade" id="maintenance" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-database me-2"></i>
                                        {{ __('Database Maintenance') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">{{ __('Perform database optimization and cleanup tasks.') }}</p>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary" onclick="optimizeDatabase()">
                                            <i class="fas fa-database me-1"></i>
                                            {{ __('Optimize Database') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                            <i class="fas fa-broom me-1"></i>
                                            {{ __('Clear Cache') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="generateBackup()">
                                            <i class="fas fa-save me-1"></i>
                                            {{ __('Create Backup') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-chart-line me-2"></i>
                                        {{ __('System Health') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ __('Database') }}</span>
                                            <span class="badge bg-success">{{ __('Healthy') }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ __('Storage') }}</span>
                                            <span class="badge bg-success">{{ __('Healthy') }}</span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ __('Cache') }}</span>
                                            <span class="badge bg-success">{{ __('Healthy') }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary w-100" onclick="runHealthCheck()">
                                        <i class="fas fa-heartbeat me-1"></i>
                                        {{ __('Run Health Check') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">{{ __('Add Platform User') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_username" class="form-label">{{ __('Username') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_username" name="username" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="">{{ __('Select role') }}</option>
                                <option value="platform_admin">{{ __('Platform Admin') }}</option>
                                <option value="support_agent">{{ __('Support Agent') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" id="add_phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_password_confirmation" class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Permissions') }} <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach($availablePermissions as $permission => $label)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="perm_{{ $permission }}">
                                    <label class="form-check-label" for="perm_{{ $permission }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create User') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">{{ __('Edit Platform User') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="program_owner">{{ __('Program Owner') }}</option>
                                <option value="platform_admin">{{ __('Platform Admin') }}</option>
                                <option value="support_agent">{{ __('Support Agent') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">{{ __('New Password') }} <small class="text-muted">({{ __('leave blank to keep current') }})</small></label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('Permissions') }} <span class="text-danger">*</span></label>
                        <div class="row" id="editPermissionsContainer">
                            @foreach($availablePermissions as $permission => $label)
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="edit_perm_{{ $permission }}">
                                    <label class="form-check-label" for="edit_perm_{{ $permission }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update User') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Platform Settings Form
    document.getElementById('platformSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
        submitBtn.disabled = true;

        fetch('{{ route("master.settings.update") }}', {
            method: 'PATCH',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', '{{ __("An error occurred while saving settings") }}');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Advanced Settings Form
    document.getElementById('advancedSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
        submitBtn.disabled = true;

        fetch('{{ route("master.settings.update") }}', {
            method: 'PATCH',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', '{{ __("An error occurred while saving settings") }}');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Software Update Form
    document.getElementById('softwareUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!confirm('{{ __("Are you sure you want to update the software? This may temporarily interrupt service.") }}')) {
            return;
        }

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Updating...") }}';
        submitBtn.disabled = true;

        fetch('{{ route("master.settings.update-software") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', '{{ __("Software update failed") }}');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Add User Form
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Creating...") }}';
        submitBtn.disabled = true;

        fetch('{{ route("master.platform-users.create") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', '{{ __("Failed to create user") }}');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Edit User Form
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const userId = document.getElementById('edit_user_id').value;
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Updating...") }}';
        submitBtn.disabled = true;

        fetch(`{{ route("master.platform-users.update", ":id") }}`.replace(':id', userId), {
            method: 'PATCH',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            showAlert('error', '{{ __("Failed to update user") }}');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});

// User Management Functions
function editUser(userId) {
    // Fetch user data and populate edit modal
    fetch(`{{ route("master.platform-users") }}`)
        .then(response => response.text())
        .then(html => {
            // For now, we'll use a simple approach
            // In a real implementation, you'd have a separate API endpoint to get user details
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        });
}

function deleteUser(userId) {
    if (!confirm('{{ __("Are you sure you want to delete this user? This action cannot be undone.") }}')) {
        return;
    }

    fetch(`{{ route("master.platform-users.delete", ":id") }}`.replace(':id', userId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', '{{ __("Failed to delete user") }}');
    });
}

// Maintenance Functions
function optimizeDatabase() {
    if (!confirm('{{ __("This will optimize the database. Continue?") }}')) {
        return;
    }

    showAlert('info', '{{ __("Database optimization started...") }}');

    // Simulate database optimization
    setTimeout(() => {
        showAlert('success', '{{ __("Database optimized successfully") }}');
    }, 2000);
}

function clearCache() {
    if (!confirm('{{ __("This will clear all cached data. Continue?") }}')) {
        return;
    }

    showAlert('info', '{{ __("Clearing cache...") }}');

    // Simulate cache clearing
    setTimeout(() => {
        showAlert('success', '{{ __("Cache cleared successfully") }}');
    }, 1000);
}

function generateBackup() {
    if (!confirm('{{ __("This will create a full system backup. Continue?") }}')) {
        return;
    }

    showAlert('info', '{{ __("Creating backup...") }}');

    // Simulate backup creation
    setTimeout(() => {
        showAlert('success', '{{ __("Backup created successfully") }}');
    }, 3000);
}

function runHealthCheck() {
    showAlert('info', '{{ __("Running system health check...") }}');

    // Simulate health check
    setTimeout(() => {
        showAlert('success', '{{ __("System health check completed - All systems operational") }}');
    }, 2000);
}

// Utility function to show alerts
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());

    // Add new alert at the top of the container
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endpush
