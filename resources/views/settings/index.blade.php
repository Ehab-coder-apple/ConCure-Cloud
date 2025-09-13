@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cog text-primary me-2"></i>
                    {{ __('Settings') }}
                </h1>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    <!-- Settings Navigation -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">{{ __('Settings Categories') }}</h6>
                        </div>
                        <div class="list-group list-group-flush">
                            <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="pill">
                                <i class="fas fa-user me-2"></i>
                                {{ __('My Profile') }}
                            </a>
                            <a href="#general" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('General Settings') }}
                            </a>
                            <a href="#clinic" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('Clinic Information') }}
                            </a>
                            <a href="#users" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                                <i class="fas fa-users me-2"></i>
                                {{ __('User Management') }}
                            </a>
                            <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                                <i class="fas fa-server me-2"></i>
                                {{ __('System Settings') }}
                            </a>
                            <a href="#user-guide" class="list-group-item list-group-item-action" data-bs-toggle="pill">
                                <i class="fas fa-book me-2"></i>
                                {{ __('User Guide') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- My Profile -->
                        <div class="tab-pane fade show active" id="profile">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-user me-2"></i>
                                        {{ __('My Profile') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="profileForm">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                                           value="{{ auth()->user()->first_name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                                           value="{{ auth()->user()->last_name }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">{{ __('Email') }}</label>
                                                    <input type="email" class="form-control" id="email" name="email"
                                                           value="{{ auth()->user()->email }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">{{ __('Phone') }}</label>
                                                    <input type="text" class="form-control" id="phone" name="phone"
                                                           value="{{ auth()->user()->phone }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="role" class="form-label">{{ __('Role') }}</label>
                                                    <input type="text" class="form-control" id="role"
                                                           value="{{ auth()->user()->role_display }}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="title_prefix" class="form-label">{{ __('Title/Prefix') }}</label>
                                                    <select class="form-select" id="title_prefix" name="title_prefix">
                                                        <option value="">{{ __('Select Title/Prefix') }}</option>
                                                        @foreach(auth()->user()->getAvailableTitlePrefixes() as $prefix)
                                                            <option value="{{ $prefix }}"
                                                                {{ auth()->user()->title_prefix === $prefix ? 'selected' : '' }}>
                                                                {{ $prefix }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <div class="form-text">
                                                        {{ __('This will be used in documents and reports (e.g., Dr. John Smith, Nutritionist Jane Doe)') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                {{ __('Update Profile') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- General Settings -->
                        <div class="tab-pane fade" id="general">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cog me-2"></i>
                                        {{ __('General Settings') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Application Information (Read-Only) -->
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <h6 class="text-primary">{{ __('Application Information') }}</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Application Name') }}</label>
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                {{ config('app.name', 'ConCure Clinic Management') }}
                                            </div>
                                            <small class="text-muted">{{ __('Application name is managed by the platform administrator') }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Platform Version') }}</label>
                                            <div class="form-control-plaintext bg-light p-2 rounded">
                                                {{ config('concure.version', '1.0.0') }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Clinic Settings (Editable) -->
                                    <form id="clinicSettingsForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h6 class="text-primary">{{ __('Clinic Preferences') }}</h6>
                                            </div>

                                            <!-- Clinic Logo Section -->
                                            <div class="col-12">
                                                <h6 class="text-primary mt-3">{{ __('Clinic Logo') }}</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label for="clinic_logo" class="form-label">{{ __('Upload Logo') }}</label>
                                                        <input type="file" class="form-control" id="clinic_logo" name="clinic_logo" accept="image/*">
                                                        <div class="form-text">{{ __('Supported formats: JPEG, PNG, JPG, GIF, SVG. Max size: 2MB') }}</div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        @if(isset($clinicSettings['clinic_logo']) && $clinicSettings['clinic_logo'])
                                                            <div class="current-logo">
                                                                <label class="form-label">{{ __('Current Logo') }}</label>
                                                                <div class="d-flex align-items-center gap-3">
                                                                    <img src="{{ asset('storage/' . $clinicSettings['clinic_logo']) }}"
                                                                         alt="{{ __('Clinic Logo') }}"
                                                                         class="img-thumbnail"
                                                                         style="max-width: 100px; max-height: 100px;">
                                                                    <button type="button" class="btn btn-outline-danger btn-sm" id="deleteLogo">
                                                                        <i class="fas fa-trash me-1"></i>
                                                                        {{ __('Delete') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="no-logo text-muted">
                                                                <label class="form-label">{{ __('Current Logo') }}</label>
                                                                <p class="mb-0">{{ __('No logo uploaded') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="default_language" class="form-label">{{ __('Default Language') }}</label>
                                                <select class="form-select" id="default_language" name="default_language">
                                                    <option value="en" {{ ($clinicSettings['default_language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                                    <option value="ar" {{ ($clinicSettings['default_language'] ?? 'en') == 'ar' ? 'selected' : '' }}>العربية</option>
                                                    <option value="ku" {{ ($clinicSettings['default_language'] ?? 'en') == 'ku' ? 'selected' : '' }}>کوردی</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="UTC" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                                    <option value="America/New_York" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                                    <option value="America/Chicago" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                                    <option value="America/Denver" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                                    <option value="America/Los_Angeles" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="USD" {{ ($clinicSettings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                                    <option value="EUR" {{ ($clinicSettings['currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                                    <option value="GBP" {{ ($clinicSettings['currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                                    <option value="IQD" {{ ($clinicSettings['currency'] ?? 'USD') == 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                                                </select>
                                            </div>

                                            <!-- Communication Settings -->
                                            <div class="col-12 mt-4">
                                                <h6 class="text-primary">{{ __('Communication Settings') }}</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="whatsapp_number" class="form-label">
                                                    <i class="fab fa-whatsapp text-success me-1"></i>
                                                    {{ __('WhatsApp Number') }}
                                                </label>
                                                <input type="tel"
                                                       class="form-control"
                                                       id="whatsapp_number"
                                                       name="whatsapp_number"
                                                       value="{{ $clinicSettings['whatsapp_number'] ?? '' }}"
                                                       placeholder="9647501234567">
                                                <div class="form-text">
                                                    {{ __('Default WhatsApp number for sending lab requests and reports. Include country code (e.g., 9647501234567 for Iraq)') }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Notification Preferences') }}</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ ($clinicSettings['email_notifications'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="email_notifications">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ __('Email Notifications') }}
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications" value="1" {{ ($clinicSettings['sms_notifications'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="sms_notifications">
                                                        <i class="fas fa-sms me-1"></i>
                                                        {{ __('SMS Notifications') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                {{ __('Save Changes') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Clinic Information -->
                        <div class="tab-pane fade" id="clinic">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-hospital me-2"></i>
                                        {{ __('Clinic Information') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="clinicInfoForm">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="clinic_name" class="form-label">{{ __('Clinic Name') }}</label>
                                                <input type="text" class="form-control" id="clinic_name" name="clinic_name"
                                                       value="{{ $clinicInfo['name'] ?? '' }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_phone" class="form-label">{{ __('Phone Number') }}</label>
                                                <input type="tel" class="form-control" id="clinic_phone" name="clinic_phone"
                                                       value="{{ $clinicInfo['phone'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_email" class="form-label">{{ __('Email Address') }}</label>
                                                <input type="email" class="form-control" id="clinic_email" name="clinic_email"
                                                       value="{{ $clinicInfo['email'] ?? '' }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_website" class="form-label">{{ __('Website') }}</label>
                                                <input type="url" class="form-control" id="clinic_website" name="clinic_website"
                                                       value="{{ $clinicInfo['website'] ?? '' }}">
                                            </div>
                                            <div class="col-12">
                                                <label for="clinic_address" class="form-label">{{ __('Address') }}</label>
                                                <textarea class="form-control" id="clinic_address" name="clinic_address" rows="3">{{ $clinicInfo['address'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                {{ __('Save Changes') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- User Management -->
                        <div class="tab-pane fade" id="users">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        {{ __('User Management') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ __('System Users') }}</h6>
                                        <button type="button" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add User') }}
                                        </button>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Name') }}</th>
                                                    <th>{{ __('Email') }}</th>
                                                    <th>{{ __('Role') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                    <th>{{ __('Actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Program Owner managed in master system -->
                                                <tr>
                                                    <td>System Administrator</td>
                                                    <td>admin@demo.clinic</td>
                                                    <td><span class="badge bg-warning">Admin</span></td>
                                                    <td><span class="badge bg-success">Active</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Dr. Demo</td>
                                                    <td>doctor@demo.clinic</td>
                                                    <td><span class="badge bg-info">Doctor</span></td>
                                                    <td><span class="badge bg-success">Active</span></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane fade" id="system">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-server me-2"></i>
                                        {{ __('System Settings') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <h6 class="text-primary">{{ __('System Information') }}</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('ConCure Version') }}:</strong> 1.0.0
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Laravel Version') }}:</strong> {{ app()->version() }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('PHP Version') }}:</strong> {{ PHP_VERSION }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ __('Database') }}:</strong> SQLite
                                        </div>
                                        
                                        <div class="col-12 mt-4">
                                            <h6 class="text-primary">{{ __('Maintenance') }}</h6>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-2">
                                                @if(auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                                    <i class="fas fa-broom me-1"></i>
                                                    {{ __('Clear Cache') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-info" onclick="backupDatabase()">
                                                    <i class="fas fa-download me-1"></i>
                                                    {{ __('Backup Database') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-success" onclick="updateSystem()">
                                                    <i class="fas fa-sync me-1"></i>
                                                    {{ __('Update System') }}
                                                </button>
                                                @else
                                                <div class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    {{ __('System maintenance functions are available to administrators only.') }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Guide -->
                        <div class="tab-pane fade" id="user-guide">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-book me-2"></i>
                                        {{ __('ConCure User Guide') }}
                                    </h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-4">
                                        <i class="fas fa-book fa-4x text-primary mb-3"></i>
                                        <h5 class="text-primary">{{ __('Comprehensive User Guide') }}</h5>
                                        <p class="text-muted">
                                            {{ __('Access the complete ConCure user guide with step-by-step instructions, available in multiple languages with PDF export functionality.') }}
                                        </p>
                                    </div>

                                    <div class="row justify-content-center">
                                        <div class="col-md-8">
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-flag-usa me-3 text-primary"></i>
                                                        <span>English Guide</span>
                                                    </div>
                                                    <span class="badge bg-success">Available</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-flag me-3 text-success"></i>
                                                        <span>Arabic Guide (العربية)</span>
                                                    </div>
                                                    <span class="badge bg-success">Available</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-flag me-3 text-warning"></i>
                                                        <span>Kurdish Bahdeni (کوردی بادینی)</span>
                                                    </div>
                                                    <span class="badge bg-success">Available</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-flag me-3 text-info"></i>
                                                        <span>Kurdish Sorani (کوردی سۆرانی)</span>
                                                    </div>
                                                    <span class="badge bg-success">Available</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <a href="{{ route('settings.user-guide') }}" class="btn btn-primary btn-lg" target="_blank">
                                            <i class="fas fa-external-link-alt me-2"></i>
                                            {{ __('Open User Guide') }}
                                        </a>
                                        <p class="text-muted mt-2 small">
                                            {{ __('Opens in a new window with fullscreen view and PDF export options') }}
                                        </p>
                                    </div>

                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-secondary">{{ __('Features') }}</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled text-start">
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Step-by-step instructions') }}</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Multi-language support') }}</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('PDF export functionality') }}</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled text-start">
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Fullscreen reading mode') }}</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Printable format') }}</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Always up-to-date') }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.current-logo img {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.current-logo img:hover {
    border-color: #007bff;
    transform: scale(1.05);
}

.no-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
}

#clinic_logo {
    transition: border-color 0.3s ease;
}

#clinic_logo:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>
@endpush

@push('scripts')
<script>
// System maintenance functions
function backupDatabase() {
    if (!confirm('{{ __("Create a database backup? This may take a few moments.") }}')) {
        return;
    }

    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Creating Backup...") }}';
    button.disabled = true;

    fetch('{{ route("settings.backup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalText;
        button.disabled = false;

        if (data.success) {
            alert(data.message);
            if (data.download_url) {
                // Automatically start download
                const link = document.createElement('a');
                link.href = data.download_url;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        } else {
            alert(data.message || '{{ __("Failed to create backup") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('{{ __("An error occurred while creating backup") }}');
    });
}

function clearCache() {
    if (!confirm('{{ __("Clear all application caches? This will temporarily slow down the system.") }}')) {
        return;
    }

    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Clearing Cache...") }}';
    button.disabled = true;

    fetch('{{ route("settings.clear-cache") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalText;
        button.disabled = false;

        if (data.success) {
            alert(data.message);
        } else {
            alert(data.message || '{{ __("Failed to clear cache") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert('{{ __("An error occurred while clearing cache") }}');
    });
}

function updateSystem() {
    alert('{{ __("System update feature is coming soon.") }}');
}

console.log('=== JAVASCRIPT LOADED ===');
console.log('Form element found:', document.getElementById('clinicSettingsForm'));

// Add click listener to submit button as backup
const submitButton = document.querySelector('#clinicSettingsForm button[type="submit"]');
console.log('Submit button found:', submitButton);

if (submitButton) {
    submitButton.addEventListener('click', function(e) {
        console.log('=== SUBMIT BUTTON CLICKED ===');
        e.preventDefault(); // Prevent default form submission

        // Manually trigger our form handling
        handleFormSubmission();
    });
}

function handleFormSubmission() {
    console.log('=== MANUAL FORM SUBMISSION STARTED ===');

    const form = document.getElementById('clinicSettingsForm');
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Debug form data
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    console.log('Route URL:', '{{ route("settings.update") }}');

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;

    console.log('About to send fetch request...');

    fetch('{{ route("settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('✅ ' + data.message);
            // Reload page to show updated settings
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('❌ ' + (data.message || '{{ __("An error occurred while updating settings.") }}'));
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('❌ {{ __("An error occurred while updating settings.") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Keep the original form submit listener as backup
document.getElementById('clinicSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    console.log('=== FORM SUBMISSION STARTED ===');

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Debug form data
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    console.log('Route URL:', '{{ route("settings.update") }}');
    console.log('Form element:', this);
    console.log('Submit button:', submitBtn);

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;

    console.log('About to send fetch request...');

    fetch('{{ route("settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Check if logo was uploaded (reload page to show new logo)
            const logoFile = document.getElementById('clinic_logo').files[0];
            if (logoFile) {
                // Add a small delay to ensure database transaction is committed
                console.log('Logo uploaded, debug path:', data.debug_logo_path);
                setTimeout(() => {
                    location.reload();
                }, 1000); // 1 second delay
            } else {
                // Show success message for other settings
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                // Insert alert at the top of the form
                this.insertBefore(alertDiv, this.firstChild);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            }
        } else {
            // Show error message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            this.insertBefore(alertDiv, this.firstChild);
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            {{ __("An error occurred. Please try again.") }}<br>
            <small>Debug: ${error.message}</small>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        this.insertBefore(alertDiv, this.firstChild);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle profile form submission
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Clear previous alerts
    form.querySelectorAll('.alert').forEach(alert => alert.remove());

    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Updating...") }}';
    submitBtn.disabled = true;

    const formData = new FormData(form);

    fetch('{{ route("settings.update-profile") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);

        if (data.success) {
            // Optionally reload page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            {{ __("An error occurred. Please try again.") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle clinic info form submission
document.getElementById('clinicInfoForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // Clear previous alerts
    form.querySelectorAll('.alert').forEach(alert => alert.remove());

    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;

    const formData = new FormData(form);

    fetch('{{ route("settings.update-clinic-info") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);

        if (data.success) {
            // Optionally reload page to show updated data
            setTimeout(() => {
                location.reload();
            }, 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            {{ __("An error occurred. Please try again.") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        form.insertBefore(alertDiv, form.firstChild);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle logo deletion
document.addEventListener('DOMContentLoaded', function() {
    const deleteLogoBtn = document.getElementById('deleteLogo');
    if (deleteLogoBtn) {
        deleteLogoBtn.addEventListener('click', function() {
            if (confirm('{{ __("Are you sure you want to delete the clinic logo?") }}')) {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Deleting...") }}';
                this.disabled = true;

                fetch('{{ route("settings.delete-logo") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to show updated logo section
                        location.reload();
                    } else {
                        alert(data.message || '{{ __("Error deleting logo") }}');
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __("An error occurred while deleting the logo") }}');
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        });
    }
});


</script>
@endpush

@endsection
