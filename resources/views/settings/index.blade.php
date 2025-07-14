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
                            <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="pill">
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
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- General Settings -->
                        <div class="tab-pane fade show active" id="general">
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
                                    <form>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="clinic_name" class="form-label">{{ __('Clinic Name') }}</label>
                                                <input type="text" class="form-control" id="clinic_name" value="Demo Clinic">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_phone" class="form-label">{{ __('Phone Number') }}</label>
                                                <input type="tel" class="form-control" id="clinic_phone" value="+1-555-0123">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_email" class="form-label">{{ __('Email Address') }}</label>
                                                <input type="email" class="form-control" id="clinic_email" value="info@democlinic.com">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="clinic_website" class="form-label">{{ __('Website') }}</label>
                                                <input type="url" class="form-control" id="clinic_website" value="https://democlinic.com">
                                            </div>
                                            <div class="col-12">
                                                <label for="clinic_address" class="form-label">{{ __('Address') }}</label>
                                                <textarea class="form-control" id="clinic_address" rows="3">123 Medical Center Drive
City, State 12345
Country</textarea>
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
                                                <button type="button" class="btn btn-outline-warning">
                                                    <i class="fas fa-broom me-1"></i>
                                                    {{ __('Clear Cache') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-info">
                                                    <i class="fas fa-download me-1"></i>
                                                    {{ __('Backup Database') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-success">
                                                    <i class="fas fa-sync me-1"></i>
                                                    {{ __('Update System') }}
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
document.getElementById('clinicSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;

    fetch('{{ route("settings.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Check if logo was uploaded (reload page to show new logo)
            const logoFile = document.getElementById('clinic_logo').files[0];
            if (logoFile) {
                // Reload page to show new logo
                location.reload();
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
        console.error('Error:', error);

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            {{ __("An error occurred. Please try again.") }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        this.insertBefore(alertDiv, this.firstChild);
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
