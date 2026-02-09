@extends('layouts.app')

@section('title', __('Settings'))

@section('content')
<div style="margin-left: 250px; padding: 20px; width: calc(100% - 250px); box-sizing: border-box;">
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
                            <a href="#profile" class="list-group-item list-group-item-action {{ $activeTab === 'profile' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-user me-2"></i>
                                {{ __('My Profile') }}
                            </a>
                            <a href="#general" class="list-group-item list-group-item-action {{ $activeTab === 'general' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('General Settings') }}
                            </a>
                            <a href="#clinic" class="list-group-item list-group-item-action {{ $activeTab === 'clinic' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('Clinic Information') }}
                            </a>
                            <a href="#users" class="list-group-item list-group-item-action {{ $activeTab === 'users' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-users me-2"></i>
                                {{ __('User Management') }}
                            </a>
                            <a href="#system" class="list-group-item list-group-item-action {{ $activeTab === 'system' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-server me-2"></i>
                                {{ __('System Settings') }}
                            </a>
                            <a href="#user-guide" class="list-group-item list-group-item-action {{ $activeTab === 'user-guide' ? 'active' : '' }}" data-bs-toggle="pill">
                                <i class="fas fa-book me-2"></i>
                                {{ __('User Guide') }}
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                    <!-- Quick Actions for Admins -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 text-primary">
                                <i class="fas fa-bolt me-2"></i>
                                {{ __('Quick Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('users.create') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-user-plus me-1"></i>
                                    {{ __('Create User') }}
                                </a>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-users me-1"></i>
                                    {{ __('Manage Users') }}
                                </a>
                                @can('view-audit-logs')
                                <a href="{{ route('settings.audit-logs') }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-history me-1"></i>
                                    {{ __('Audit Logs') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- My Profile -->
                        <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}" id="profile">
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

                                        <!-- Doctor Name Font Size Control -->
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ __('Doctor Name Preview') }}</label>
                                                    <div class="form-control font-size-preview" 
                                                         id="doctor_name_preview"
                                                         style="font-size: {{ auth()->user()->doctor_name_font_size ?? 12 }}px; font-weight: bold; background-color: #f8f9fa;">
                                                        Dr. {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                                                    </div>
                                                    <div class="form-text">
                                                        {{ __('Preview of how your name will appear on prescription PDFs') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="doctor_name_font_size" class="form-label">{{ __('Font Size') }}</label>
                                                    <div class="input-group">
                                                        <input type="range"
                                                               class="form-range font-size-slider"
                                                               id="doctor_name_font_size"
                                                               name="doctor_name_font_size"
                                                               min="6"
                                                               max="20"
                                                               value="{{ auth()->user()->doctor_name_font_size ?? 12 }}"
                                                               data-target="doctor_name_preview">
                                                        <span class="input-group-text font-size-display" id="doctor_name_size_display">{{ auth()->user()->doctor_name_font_size ?? 12 }}px</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="scientific_degree" class="form-label">{{ __('Scientific Degree / Educational Level') }}</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           id="scientific_degree"
                                                           name="scientific_degree"
                                                           list="degree_options"
                                                           value="{{ auth()->user()->scientific_degree }}"
                                                           placeholder="{{ __('Select or type your degree') }}">
                                                    <datalist id="degree_options">
                                                        @foreach(\App\Models\User::getAvailableScientificDegrees() as $degree)
                                                            <option value="{{ $degree }}">
                                                        @endforeach
                                                    </datalist>
                                                    <div class="form-text">
                                                        {{ __('Select from the list or type your own educational qualification (e.g., Consultant, Master, PhD, etc.)') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="educational_institution" class="form-label">{{ __('Educational Institution') }}</label>
                                                    <input type="text"
                                                           class="form-control"
                                                           id="educational_institution"
                                                           name="educational_institution"
                                                           value="{{ auth()->user()->educational_institution }}"
                                                           placeholder="{{ __('e.g., University of Baghdad, Harvard Medical School, etc.') }}">
                                                    <div class="form-text">
                                                        {{ __('The institution where you obtained your degree (optional)') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(in_array(auth()->user()->role, ['doctor', 'admin', 'super_admin']))
                                        <!-- Professional Credentials Section -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-certificate me-2"></i>
                                                    {{ __('Professional Credentials') }}
                                                </h6>
                                                <p class="text-muted small">{{ __('This information will be displayed on prescription PDFs and official documents') }}</p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label for="specialization" class="form-label">{{ __('Specialization') }}</label>
                                                    <textarea class="form-control font-size-preview"
                                                              id="specialization"
                                                              name="specialization"
                                                              rows="2"
                                                              style="font-size: {{ auth()->user()->specialization_font_size ?? 10 }}px;"
                                                              placeholder="{{ __('e.g., Specialist in Oral and Maxillofacial Surgery - Dental Implants and Cosmetic Dentistry') }}">{{ auth()->user()->specialization }}</textarea>
                                                    <div class="form-text">
                                                        {{ __('Your medical specialization and areas of expertise') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="specialization_font_size" class="form-label">{{ __('Font Size') }}</label>
                                                    <div class="input-group">
                                                        <input type="range"
                                                               class="form-range font-size-slider"
                                                               id="specialization_font_size"
                                                               name="specialization_font_size"
                                                               min="6"
                                                               max="20"
                                                               value="{{ auth()->user()->specialization_font_size ?? 10 }}"
                                                               data-target="specialization">
                                                        <span class="input-group-text font-size-display" id="specialization_size_display">{{ auth()->user()->specialization_font_size ?? 10 }}px</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label for="medical_degrees" class="form-label">{{ __('Medical Degrees & Certifications') }}</label>
                                                    <textarea class="form-control font-size-preview"
                                                              id="medical_degrees"
                                                              name="medical_degrees"
                                                              rows="3"
                                                              style="font-size: {{ auth()->user()->medical_degrees_font_size ?? 9 }}px;"
                                                              placeholder="{{ __('e.g., B.D.S - Bachelor of Dental Surgery&#10;D.I.D - Diploma in Implant Dentistry&#10;Fellowship in Cosmetic Dentistry') }}">{{ auth()->user()->medical_degrees }}</textarea>
                                                    <div class="form-text">
                                                        {{ __('List your degrees, diplomas, and certifications (one per line)') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="medical_degrees_font_size" class="form-label">{{ __('Font Size') }}</label>
                                                    <div class="input-group">
                                                        <input type="range"
                                                               class="form-range font-size-slider"
                                                               id="medical_degrees_font_size"
                                                               name="medical_degrees_font_size"
                                                               min="6"
                                                               max="20"
                                                               value="{{ auth()->user()->medical_degrees_font_size ?? 9 }}"
                                                               data-target="medical_degrees">
                                                        <span class="input-group-text font-size-display" id="medical_degrees_size_display">{{ auth()->user()->medical_degrees_font_size ?? 9 }}px</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="mb-3">
                                                    <label for="professional_credentials" class="form-label">{{ __('Professional Credentials & Memberships') }}</label>
                                                    <textarea class="form-control font-size-preview"
                                                              id="professional_credentials"
                                                              name="professional_credentials"
                                                              rows="3"
                                                              style="font-size: {{ auth()->user()->professional_credentials_font_size ?? 9 }}px;"
                                                              placeholder="{{ __('e.g., Member of Iraqi Dental Association&#10;Certified Implantologist&#10;Board Certified in Oral Surgery') }}">{{ auth()->user()->professional_credentials }}</textarea>
                                                    <div class="form-text">
                                                        {{ __('Professional memberships, board certifications, and other credentials') }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="professional_credentials_font_size" class="form-label">{{ __('Font Size') }}</label>
                                                    <div class="input-group">
                                                        <input type="range"
                                                               class="form-range font-size-slider"
                                                               id="professional_credentials_font_size"
                                                               name="professional_credentials_font_size"
                                                               min="6"
                                                               max="20"
                                                               value="{{ auth()->user()->professional_credentials_font_size ?? 9 }}"
                                                               data-target="professional_credentials">
                                                        <span class="input-group-text font-size-display" id="professional_credentials_size_display">{{ auth()->user()->professional_credentials_font_size ?? 9 }}px</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

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
                        <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="general">
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
                                                                    <img src="{{ route('clinic.logo', ['clinic' => auth()->user()->clinic_id]) }}"
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
                                                <label for="dashboard_default_period" class="form-label">{{ __('Dashboard Default Period') }}</label>
                                                <select class="form-select" id="dashboard_default_period" name="dashboard_default_period">
                                                    <option value="day" {{ ($clinicSettings['dashboard_default_period'] ?? 'month') == 'day' ? 'selected' : '' }}>{{ __('Day') }}</option>
                                                    <option value="month" {{ ($clinicSettings['dashboard_default_period'] ?? 'month') == 'month' ? 'selected' : '' }}>{{ __('Month') }}</option>
                                                    <option value="year" {{ ($clinicSettings['dashboard_default_period'] ?? 'month') == 'year' ? 'selected' : '' }}>{{ __('Year') }}</option>
                                                </select>
                                                <div class="form-text">{{ __('Determines the default time period for dashboard statistics for all users in this clinic.') }}</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="timezone" class="form-label">{{ __('Timezone') }}</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="UTC" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>

                                                    <optgroup label="Middle East">
                                                        <option value="Asia/Baghdad" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Baghdad' ? 'selected' : '' }}>Iraq (Baghdad) - GMT+3</option>
                                                        <option value="Asia/Riyadh" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Riyadh' ? 'selected' : '' }}>Saudi Arabia (Riyadh) - GMT+3</option>
                                                        <option value="Asia/Kuwait" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Kuwait' ? 'selected' : '' }}>Kuwait - GMT+3</option>
                                                        <option value="Asia/Dubai" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Dubai' ? 'selected' : '' }}>UAE (Dubai) - GMT+4</option>
                                                        <option value="Asia/Qatar" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Qatar' ? 'selected' : '' }}>Qatar - GMT+3</option>
                                                        <option value="Asia/Bahrain" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Bahrain' ? 'selected' : '' }}>Bahrain - GMT+3</option>
                                                        <option value="Asia/Amman" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Amman' ? 'selected' : '' }}>Jordan (Amman) - GMT+3</option>
                                                        <option value="Asia/Beirut" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Beirut' ? 'selected' : '' }}>Lebanon (Beirut) - GMT+2</option>
                                                        <option value="Asia/Damascus" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Damascus' ? 'selected' : '' }}>Syria (Damascus) - GMT+3</option>
                                                        <option value="Asia/Jerusalem" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Jerusalem' ? 'selected' : '' }}>Palestine/Israel - GMT+2</option>
                                                        <option value="Asia/Tehran" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Tehran' ? 'selected' : '' }}>Iran (Tehran) - GMT+3:30</option>
                                                    </optgroup>

                                                    <optgroup label="Africa">
                                                        <option value="Africa/Cairo" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Africa/Cairo' ? 'selected' : '' }}>Egypt (Cairo) - GMT+2</option>
                                                        <option value="Africa/Casablanca" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Africa/Casablanca' ? 'selected' : '' }}>Morocco (Casablanca) - GMT+1</option>
                                                        <option value="Africa/Algiers" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Africa/Algiers' ? 'selected' : '' }}>Algeria (Algiers) - GMT+1</option>
                                                        <option value="Africa/Tunis" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Africa/Tunis' ? 'selected' : '' }}>Tunisia - GMT+1</option>
                                                        <option value="Africa/Tripoli" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Africa/Tripoli' ? 'selected' : '' }}>Libya (Tripoli) - GMT+2</option>
                                                    </optgroup>

                                                    <optgroup label="Europe">
                                                        <option value="Europe/London" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Europe/London' ? 'selected' : '' }}>UK (London) - GMT+0</option>
                                                        <option value="Europe/Paris" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Europe/Paris' ? 'selected' : '' }}>France (Paris) - GMT+1</option>
                                                        <option value="Europe/Berlin" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Europe/Berlin' ? 'selected' : '' }}>Germany (Berlin) - GMT+1</option>
                                                        <option value="Europe/Rome" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Europe/Rome' ? 'selected' : '' }}>Italy (Rome) - GMT+1</option>
                                                        <option value="Europe/Istanbul" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Europe/Istanbul' ? 'selected' : '' }}>Turkey (Istanbul) - GMT+3</option>
                                                    </optgroup>

                                                    <optgroup label="Americas">
                                                        <option value="America/New_York" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>US Eastern Time - GMT-5</option>
                                                        <option value="America/Chicago" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>US Central Time - GMT-6</option>
                                                        <option value="America/Denver" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Denver' ? 'selected' : '' }}>US Mountain Time - GMT-7</option>
                                                        <option value="America/Los_Angeles" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'America/Los_Angeles' ? 'selected' : '' }}>US Pacific Time - GMT-8</option>
                                                    </optgroup>

                                                    <optgroup label="Asia">
                                                        <option value="Asia/Karachi" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Karachi' ? 'selected' : '' }}>Pakistan (Karachi) - GMT+5</option>
                                                        <option value="Asia/Kolkata" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Kolkata' ? 'selected' : '' }}>India (Kolkata) - GMT+5:30</option>
                                                        <option value="Asia/Singapore" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Singapore' ? 'selected' : '' }}>Singapore - GMT+8</option>
                                                        <option value="Asia/Tokyo" {{ ($clinicSettings['timezone'] ?? 'UTC') == 'Asia/Tokyo' ? 'selected' : '' }}>Japan (Tokyo) - GMT+9</option>
                                                    </optgroup>
                                                </select>
                                                <div class="form-text">{{ __('Timezone affects how dates and times are displayed throughout the system.') }}</div>
                                            </div>

                                            <!-- Finance Settings -->
                                            <div class="col-12 mt-4">
                                                <h6 class="text-primary">{{ __('Finance Settings') }}</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="USD" {{ ($clinicSettings['currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                                    <option value="EUR" {{ ($clinicSettings['currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                                    <option value="GBP" {{ ($clinicSettings['currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                                    <option value="IQD" {{ ($clinicSettings['currency'] ?? 'USD') == 'IQD' ? 'selected' : '' }}>IQD (د.ع)</option>
                                                    <option value="JOD" {{ ($clinicSettings['currency'] ?? 'USD') == 'JOD' ? 'selected' : '' }}>JOD (د.أ)</option>
                                                    <option value="EGP" {{ ($clinicSettings['currency'] ?? 'USD') == 'EGP' ? 'selected' : '' }}>EGP (ج.م)</option>
                                                </select>
                                                <div class="form-text">{{ __('Currency used for invoices, expenses, and financial reports.') }}</div>
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
                        <div class="tab-pane fade {{ $activeTab === 'clinic' ? 'show active' : '' }}" id="clinic">
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
                        <div class="tab-pane fade {{ $activeTab === 'users' ? 'show active' : '' }}" id="users">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-users me-2"></i>
                                        {{ __('User Management') }}
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>{{ __('User Management Access') }}</strong><br>
                                                    {{ __('As a system administrator, you have full access to user management features including creating users with custom permissions.') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="card border-primary">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                                                        <h5 class="card-title">{{ __('Create New User') }}</h5>
                                                        <p class="card-text text-muted">
                                                            {{ __('Add new users to your system with custom roles and permissions') }}
                                                        </p>
                                                        <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus me-1"></i>
                                                            {{ __('Create User') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card border-success">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-users-cog fa-3x text-success mb-3"></i>
                                                        <h5 class="card-title">{{ __('Manage Users') }}</h5>
                                                        <p class="card-text text-muted">
                                                            {{ __('View, edit, and manage all system users and their permissions') }}
                                                        </p>
                                                        <a href="{{ route('users.index') }}" class="btn btn-success">
                                                            <i class="fas fa-users me-1"></i>
                                                            {{ __('Manage Users') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">{{ __('Quick User Overview') }}</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @php
                                                            // Show only users from the current clinic and hide super admins in tenant view
                                                            $users = \App\Models\User::where('clinic_id', auth()->user()->clinic_id)
                                                                ->where('role', '!=', 'super_admin')
                                                                ->with('doctors')
                                                                ->orderBy('created_at', 'desc')
                                                                ->take(5)
                                                                ->get();
                                                        @endphp

                                                        @if($users->count() > 0)
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
                                                                        @foreach($users as $user)
                                                                        <tr>
                                                                            <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                                                            <td>{{ $user->email }}</td>
                                                                            <td>
                                                                                @php
                                                                                    $roleColors = [
                                                                                        'super_admin' => 'danger',
                                                                                        'admin' => 'warning',
                                                                                        'doctor' => 'info',
                                                                                        'nutritionist' => 'success',
                                                                                        'pharmacist' => 'primary',
                                                                                        'assistant' => 'secondary',
                                                                                        'nurse' => 'light',
                                                                                        'accountant' => 'dark'
                                                                                    ];
                                                                                    $color = $roleColors[$user->role] ?? 'secondary';
                                                                                @endphp
                                                                                <span class="badge bg-{{ $color }}">{{ __(ucfirst(str_replace('_', ' ', $user->role))) }}</span>
                                                                                @if($user->role === 'assistant' && $user->doctors && $user->doctors->count() > 0)
                                                                                    <br>
                                                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                                                        <i class="fas fa-user-md me-1"></i>
                                                                                        {{ __('Assigned to:') }}
                                                                                        @foreach($user->doctors as $doctor)
                                                                                            <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $doctor->name }}</span>{{ !$loop->last ? ' ' : '' }}
                                                                                        @endforeach
                                                                                    </small>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if($user->is_active)
                                                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                                                @else
                                                                                    <span class="badge bg-danger">{{ __('Inactive') }}</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-eye"></i>
                                                                                </a>
                                                                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                                                                                    <i class="fas fa-edit"></i>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="text-center mt-3">
                                                                <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                                                                    <i class="fas fa-list me-1"></i>
                                                                    {{ __('View All Users') }}
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="text-center py-4">
                                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                                <h5 class="text-muted">{{ __('No Users Found') }}</h5>
                                                                <p class="text-muted">{{ __('Start by creating your first user.') }}</p>
                                                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                                    <i class="fas fa-plus me-1"></i>
                                                                    {{ __('Create First User') }}
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">{{ __('Access Restricted') }}</h5>
                                            <p class="text-muted">{{ __('User management is available to administrators only.') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- System Settings -->
                        <div class="tab-pane fade {{ $activeTab === 'system' ? 'show active' : '' }}" id="system">
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

                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                                        <div class="col-12 mt-4">
                                            <h6 class="text-primary">{{ __('Session Settings') }}</h6>
                                        </div>
                                        <div class="col-12">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <form id="sessionLifetimeForm">
                                                        @csrf
                                                        <div class="row align-items-end">
                                                            <div class="col-md-6">
                                                                <label for="session_lifetime" class="form-label">
                                                                    <i class="fas fa-clock me-1"></i>
                                                                    {{ __('Session Lifetime (minutes)') }}
                                                                </label>
                                                                <input type="number"
                                                                       class="form-control"
                                                                       id="session_lifetime"
                                                                       name="session_lifetime"
                                                                       value="{{ $globalSettings['session_lifetime'] ?? 480 }}"
                                                                       min="5"
                                                                       max="43200"
                                                                       required>
                                                                <small class="text-muted">
                                                                    {{ __('How long users can stay logged in without activity. Min: 5 minutes, Max: 30 days (43200 minutes)') }}
                                                                </small>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="text-muted small">
                                                                    <strong>{{ __('Current:') }}</strong>
                                                                    <span id="currentSessionDisplay">
                                                                        {{ floor(($globalSettings['session_lifetime'] ?? 480) / 60) }} {{ __('hours') }}
                                                                        {{ ($globalSettings['session_lifetime'] ?? 480) % 60 }} {{ __('minutes') }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button type="submit" class="btn btn-primary w-100">
                                                                    <i class="fas fa-save me-1"></i>
                                                                    {{ __('Update Session Lifetime') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(auth()->user()->clinic_id && in_array(auth()->user()->role, ['admin', 'doctor']))
                                        <div class="col-12 mt-4">
                                            <h6 class="text-primary">{{ __('Patient Management') }}</h6>
                                        </div>
                                        <div class="col-12">
                                            <div class="card bg-light border-0">
                                                <div class="card-body">
                                                    <form id="patientInactivityForm">
                                                        @csrf
                                                        <div class="row align-items-end">
                                                            <div class="col-md-6">
                                                                <label for="patient_inactivity_days" class="form-label">
                                                                    <i class="fas fa-user-clock me-1"></i>
                                                                    {{ __('Patient Auto-Deactivation Period (days)') }}
                                                                </label>
                                                                <input type="number"
                                                                       class="form-control"
                                                                       id="patient_inactivity_days"
                                                                       name="patient_inactivity_days"
                                                                       value="{{ $patientInactivityDays ?? '' }}"
                                                                       min="30"
                                                                       max="3650"
                                                                       placeholder="{{ __('Leave empty to disable') }}">
                                                                <small class="text-muted">
                                                                    {{ __('Automatically deactivate patients after this many days of inactivity. Activity includes: appointments, visits, prescriptions, lab tests, radiology requests, or any patient record updates. Minimum: 30 days. Leave empty to disable auto-deactivation.') }}
                                                                </small>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="text-muted small">
                                                                    <strong>{{ __('Current:') }}</strong>
                                                                    <span id="currentInactivityDisplay">
                                                                        @if($patientInactivityDays)
                                                                            {{ $patientInactivityDays }} {{ __('days') }}
                                                                            ({{ round($patientInactivityDays / 30, 1) }} {{ __('months') }})
                                                                        @else
                                                                            {{ __('Disabled') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button type="submit" class="btn btn-primary w-100">
                                                                    <i class="fas fa-save me-1"></i>
                                                                    {{ __('Update Setting') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(auth()->user()->clinic_id && in_array(auth()->user()->role, ['admin', 'doctor']))
                                        <div class="col-12 mt-4">
                                                <small class="text-muted">{{ __('Doctors') }}</small>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-12 mt-4">
                                            <h6 class="text-primary">{{ __('Maintenance') }}</h6>
                                        </div>
                                        <div class="col-12">
                                            @if(isset($lastBackup) || (isset($recentBackups) && $recentBackups->count()))
                                                <div class="mb-3 p-2 bg-light rounded">
                                                    <div class="small text-muted">
                                                        <i class="fas fa-database me-1"></i>
                                                        {{ __('Last backup:') }}
                                                        <strong>
                                                        @if(isset($lastBackup) && $lastBackup)
                                                            {{ \Carbon\Carbon::parse($lastBackup->completed_at ?? $lastBackup->created_at)->format('Y-m-d H:i') }}
                                                        @else
                                                            {{ __('None') }}
                                                        @endif
                                                        </strong>
                                                    </div>
                                                    @if(isset($recentBackups) && $recentBackups->count())
                                                        <div class="mt-1">
                                                            <div class="small text-muted mb-1">{{ __('Recent backups:') }}</div>
                                                            <ul class="list-unstyled mb-0">
                                                                @foreach($recentBackups as $b)
                                                                    <?php $rel = trim(str_replace(storage_path('app/backups/'), '', (string)$b->path), '/'); ?>
                                                                    <li class="small">
                                                                        <a href="{{ route('settings.download-backup', ['file' => $rel]) }}" class="text-decoration-none">
                                                                            <i class="fas fa-file-archive me-1"></i>
                                                                            {{ basename((string)$b->path) }}
                                                                        </a>
                                                                        <span class="text-muted ms-1">{{ $b->size_bytes ? number_format($b->size_bytes/1024, 0) . ' KB' : '' }}</span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                                            <div class="mb-3 p-2 bg-light rounded">
                                                <div class="small text-muted mb-1">
                                                    <i class="fas fa-file me-1"></i>
                                                    {{ __('Manual backup: options') }}
                                                </div>
                                                <?php $docTypes = isset($manualDocTypes) && is_array($manualDocTypes) ? $manualDocTypes : []; ?>
                                                <div class="d-flex flex-column gap-2">
                                                    <div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch" id="include_db_json" {{ !empty($manualIncludeDb) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="include_db_json">{{ __('Include database data (JSON)') }}</label>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="saveBackupIncludeDb(event)">
                                                            <i class="fas fa-save me-1"></i>{{ __('Save option') }}
                                                        </button>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-3 align-items-center mt-2">
                                                        @php $opts = [
                                                            'pdf' => 'PDF',
                                                            'doc' => 'Word (.doc)',
                                                            'docx' => 'Word (.docx)',
                                                            'xls' => 'Excel (.xls)',
                                                            'xlsx' => 'Excel (.xlsx)',
                                                            'xlsm' => 'Excel (.xlsm)',
                                                            'csv' => 'CSV',
                                                            'ppt' => 'PowerPoint (.ppt)',
                                                            'pptx' => 'PowerPoint (.pptx)'
                                                        ]; @endphp
                                                        @foreach($opts as $ext => $label)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" id="doc_{{ $ext }}" value="{{ $ext }}" {{ in_array($ext, $docTypes) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="doc_{{ $ext }}">{{ $label }}</label>
                                                        </div>
                                                        @endforeach
                                                        <button type="button" class="btn btn-sm btn-primary" onclick="saveBackupDocTypes(event)">
                                                            <i class="fas fa-save me-1"></i>{{ __('Save types') }}
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="small text-muted mt-1">{{ __('Automatic backups always include all files.') }}</div>
                                            </div>
                                            @endif

                                            <div class="d-flex gap-2">
                                                @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                                                <button type="button" class="btn btn-outline-info" onclick="backupDatabase(event)">
                                                    <i class="fas fa-database me-1"></i>
                                                    {{ __('Backup Data') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                                    <i class="fas fa-broom me-1"></i>
                                                    {{ __('Clear Cache') }}
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
                        <div class="tab-pane fade {{ $activeTab === 'user-guide' ? 'show active' : '' }}" id="user-guide">
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
function backupDatabase(event) {
    if (!confirm('{{ __("Create a database backup? This may take a few moments.") }}')) {
        return;
    }

    const button = event.currentTarget || event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Creating Backup...") }}';
    button.disabled = true;

    fetch('{{ route("settings.backup-direct") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(async (response) => {
        if (!response.ok) {
            let message = '{{ __("Failed to create backup") }}';
            try {
                const ct = response.headers.get('Content-Type') || '';
                if (ct.includes('application/json')) {
                    const data = await response.json();
                    if (data && data.message) message = data.message;
                } else {
                    const text = await response.text();
                    if (text && text.length < 400) message = text;
                }
            } catch (_) { /* ignore */ }
            if (response.status === 429) message = '{{ __("Please wait a little before trying again. A backup was just created (rate limit)") }}';
            if (response.status === 403) message = '{{ __("You are not authorized to create backups.") }}';
            if (response.status === 419) message = '{{ __("Your session expired. Please refresh the page and try again.") }}';
            throw new Error(message);
        }
        const dispo = response.headers.get('Content-Disposition') || '';
        let filename = (dispo.match(/filename\*=UTF-8''([^;\n]+)/) || [])[1] || (dispo.match(/filename="?([^";\n]+)/) || [])[1] || 'clinic-backup.zip';
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = decodeURIComponent(filename);
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();

        button.innerHTML = originalText;
        button.disabled = false;
        alert('{{ __("Backup created successfully.") }}');
    })
    .catch((error) => {
        console.error('Backup error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        alert(error.message || '{{ __("An error occurred while creating backup") }}');
    });
}

    function saveBackupDocTypes(event) {
        const exts = [];
        document.querySelectorAll('[id^="doc_"]').forEach(cb => { if (cb.checked) exts.push(cb.value); });
        const btn = event.currentTarget || event.target;
        const prev = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Saving...") }}';
        fetch('{{ route("settings.backup-types") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')

            },
            body: JSON.stringify({ types: exts })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = prev;
            if (data && data.success) { alert('{{ __("Saved document types for manual backups.") }}'); }
            else { alert((data && data.message) || '{{ __("Failed to save") }}'); }
        })
        .catch(() => { btn.disabled = false; btn.innerHTML = prev; alert('{{ __("Network error") }}'); });
    }

    function saveBackupIncludeDb(event) {
        const include = document.getElementById('include_db_json')?.checked ? true : false;
        const btn = event.currentTarget || event.target;
        const prev = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Saving...") }}';
        fetch('{{ route("settings.backup-include-db") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ include })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = prev;
            if (data && data.success) { alert('{{ __("Saved manual backup option.") }}'); }
            else { alert((data && data.message) || '{{ __("Failed to save") }}'); }
        })
        .catch(() => { btn.disabled = false; btn.innerHTML = prev; alert('{{ __("Network error") }}'); });
    }



function clearCache() {
    if (!confirm('{{ __("Clear all application caches? This will temporarily slow down the system.") }}')) {
        return;
    }

    const button = event.currentTarget || event.target;
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

// Handle URL anchors for tabs
document.addEventListener('DOMContentLoaded', function() {
    // Check if there's a hash in the URL
    const hash = window.location.hash;
    if (hash) {
        // Find the tab link that matches the hash
        const tabLink = document.querySelector(`a[href="${hash}"][data-bs-toggle="pill"]`);
        if (tabLink) {
            // Remove active class from all tabs
            document.querySelectorAll('.list-group-item-action').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active', 'show');
            });

            // Activate the target tab
            tabLink.classList.add('active');
            const targetPane = document.querySelector(hash);
            if (targetPane) {
                targetPane.classList.add('active', 'show');
            }
        }
    }
});

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



// Per-clinic auto-backup toggle (super admin)
function toggleClinicAutoBackup(clinicId, el) {
    const enabled = el.checked;
    const inputId = 'auto_backup_' + clinicId;
    const switchEl = document.getElementById(inputId);
    if (switchEl) switchEl.disabled = true;

    fetch('{{ route('settings.system.auto-backup-clinic') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ clinic_id: clinicId, enabled })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || '{{ __('Failed to update automatic backup setting.') }}');
            el.checked = !enabled; // revert
        }
    })
    .catch(() => {
        alert('{{ __('Network error while updating setting.') }}');
        el.checked = !enabled; // revert
    })
    .finally(() => {
        if (switchEl) switchEl.disabled = false;
    });
}

// Handle session lifetime form submission
document.addEventListener('DOMContentLoaded', function() {
    const sessionForm = document.getElementById('sessionLifetimeForm');
    const sessionInput = document.getElementById('session_lifetime');
    const sessionDisplay = document.getElementById('currentSessionDisplay');

    if (sessionForm) {
        // Update display when input changes
        sessionInput.addEventListener('input', function() {
            const minutes = parseInt(this.value) || 0;
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            sessionDisplay.textContent = `${hours} {{ __('hours') }} ${mins} {{ __('minutes') }}`;
        });

        sessionForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Updating...") }}';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('{{ route("settings.update-session-lifetime") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show mt-3`;
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                sessionForm.parentElement.appendChild(alertDiv);

                if (data.success) {
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alertDiv.innerHTML = `
                    {{ __("An error occurred. Please try again.") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                sessionForm.parentElement.appendChild(alertDiv);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Handle patient inactivity period form submission
    const patientInactivityForm = document.getElementById('patientInactivityForm');
    const inactivityInput = document.getElementById('patient_inactivity_days');
    const inactivityDisplay = document.getElementById('currentInactivityDisplay');

    if (patientInactivityForm) {
        // Update display when input changes
        if (inactivityInput) {
            inactivityInput.addEventListener('input', function() {
                const days = parseInt(this.value) || 0;
                if (days > 0) {
                    const months = (days / 30).toFixed(1);
                    inactivityDisplay.textContent = days + ' {{ __('days') }} (' + months + ' {{ __('months') }})';
                } else {
                    inactivityDisplay.textContent = '{{ __('Disabled') }}';
                }
            });
        }

        patientInactivityForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Updating...") }}';
            submitBtn.disabled = true;

            const formData = new FormData(this);

            fetch('{{ route("settings.update-patient-inactivity-period") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show mt-3`;
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                patientInactivityForm.parentElement.appendChild(alertDiv);

                if (data.success) {
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alertDiv.innerHTML = `
                    {{ __("An error occurred. Please try again.") }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;

                patientInactivityForm.parentElement.appendChild(alertDiv);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }


    // Font size slider live preview
    document.querySelectorAll('.font-size-slider').forEach(slider => {
        slider.addEventListener('input', function() {
            const targetId = this.dataset.target;
            const fontSize = this.value;
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.style.fontSize = fontSize + 'px';
            }
        });
    });
});
</script>
</div>
</div>
@endsection
