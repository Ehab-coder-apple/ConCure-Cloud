@extends('layouts.app')

@section('title', __('Edit User'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user-edit text-primary me-2"></i>
                        {{ __('Edit User') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Edit User') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('users.show', $user->id ?? 1) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Details') }}
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Users') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-circle me-2"></i>
                                {{ __('User Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('users.update', $user->id ?? 1) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row g-3">
                                    <!-- Basic Information -->
                                    <div class="col-12">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-id-card me-2"></i>
                                            {{ __('Basic Information') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" name="first_name" 
                                               value="{{ old('first_name', $user->first_name ?? 'Demo') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" name="last_name" 
                                               value="{{ old('last_name', $user->last_name ?? 'User') }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">{{ __('Username') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                               id="username" name="username" 
                                               value="{{ old('username', $user->username ?? 'demo_user') }}" required>
                                        <div class="form-text">{{ __('Must be unique and contain only letters, numbers, and underscores') }}</div>
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" 
                                               value="{{ old('email', $user->email ?? 'demo@concure.com') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" 
                                               value="{{ old('phone', $user->phone ?? '+1-555-0123') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="language" class="form-label">{{ __('Preferred Language') }}</label>
                                        <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                                            <option value="en" {{ old('language', $user->language ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                            <option value="ar" {{ old('language', $user->language ?? '') == 'ar' ? 'selected' : '' }}>العربية</option>
                                            <option value="ku" {{ old('language', $user->language ?? '') == 'ku' ? 'selected' : '' }}>کوردی</option>
                                        </select>
                                        @error('language')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Role and Permissions -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-user-shield me-2"></i>
                                            {{ __('Role and Permissions') }}
                                        </h6>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="role" class="form-label">{{ __('User Role') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="updateRoleDescription()">
                                            <option value="">{{ __('Select Role') }}</option>
                                            <option value="admin" {{ old('role', $user->role ?? 'admin') == 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                                            <option value="doctor" {{ old('role', $user->role ?? '') == 'doctor' ? 'selected' : '' }}>{{ __('Doctor') }}</option>
                                            <option value="nutritionist" {{ old('role', $user->role ?? '') == 'nutritionist' ? 'selected' : '' }}>{{ __('Nutritionist') }}</option>
                                            <option value="assistant" {{ old('role', $user->role ?? '') == 'assistant' ? 'selected' : '' }}>{{ __('Medical Assistant') }}</option>
                                            <option value="nurse" {{ old('role', $user->role ?? '') == 'nurse' ? 'selected' : '' }}>{{ __('Nurse') }}</option>
                                            <option value="accountant" {{ old('role', $user->role ?? '') == 'accountant' ? 'selected' : '' }}>{{ __('Accountant') }}</option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="is_active" class="form-label">{{ __('Account Status') }}</label>
                                        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                            <option value="1" {{ old('is_active', $user->is_active ?? '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                            <option value="0" {{ old('is_active', $user->is_active ?? '1') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                        </select>
                                        @error('is_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <div id="roleDescription" class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <span id="roleDescriptionText">
                                                @switch($user->role ?? 'admin')
                                                    @case('admin')
                                                        {{ __('Administrative access to manage users, settings, and clinic operations.') }}
                                                        @break
                                                    @case('doctor')
                                                        {{ __('Access to patient records, prescriptions, lab requests, and medical features.') }}
                                                        @break
                                                    @case('nutritionist')
                                                        {{ __('Specialized access to nutrition plans, diet management, food database, and patient dietary care.') }}
                                                        @break
                                                    @case('assistant')
                                                        {{ __('Patient management, appointment scheduling, and basic medical record access.') }}
                                                        @break
                                                    @case('nurse')
                                                        {{ __('Patient care features, vital signs recording, and medication administration.') }}
                                                        @break
                                                    @case('accountant')
                                                        {{ __('Financial management, invoicing, expense tracking, and reporting features.') }}
                                                        @break
                                                    @default
                                                        {{ __('Standard user access to assigned features.') }}
                                                @endswitch
                                            </span>
                                        </div>
                                    </div>

                                    <!-- System Permissions -->
                                    <div class="col-12 mt-4" id="systemPermissions">
                                        <h5 class="text-primary border-bottom pb-2 mb-4">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            {{ __('System Permissions') }}
                                        </h5>

                                        @php
                                            $allPermissions = \App\Models\User::getAllPermissions();
                                            $permissionSections = \App\Models\User::getPermissionSections();
                                            $userPermissions = $user->permissions ?? [];
                                        @endphp

                                        <div class="alert alert-info mb-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>{{ __('Flexible Permission System:') }}</strong>
                                            {{ __('You can grant any combination of permissions to any user, regardless of their role. This allows you to customize access for each individual user based on their specific responsibilities.') }}
                                        </div>

                                        <!-- Permission Sections -->
                                        <div class="row">
                                            @foreach($allPermissions as $sectionKey => $sectionPermissions)
                                                @php
                                                    $section = $permissionSections[$sectionKey] ?? ['name' => ucfirst($sectionKey), 'icon' => 'fas fa-cog', 'color' => 'secondary'];
                                                @endphp

                                                <div class="col-lg-6 col-md-12 mb-4">
                                                    <div class="card border-{{ $section['color'] }}">
                                                        <div class="card-header bg-{{ $section['color'] }} text-white">
                                                            <h6 class="mb-0">
                                                                <i class="{{ $section['icon'] }} me-2"></i>
                                                                {{ __($section['name']) }}
                                                            </h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                @foreach($sectionPermissions as $permission => $label)
                                                                <div class="col-12 mb-2">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                               name="permissions[]" value="{{ $permission }}"
                                                                               id="permission_{{ $permission }}"
                                                                               {{ in_array($permission, $userPermissions) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="permission_{{ $permission }}">
                                                                            <strong>{{ __($label) }}</strong>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                @endforeach
                                                            </div>

                                                            <!-- Section Actions -->
                                                            <div class="mt-3 pt-3 border-top">
                                                                <button type="button" class="btn btn-sm btn-outline-{{ $section['color'] }} me-2"
                                                                        onclick="selectAllInSection('{{ $sectionKey }}')">
                                                                    <i class="fas fa-check-square me-1"></i>
                                                                    {{ __('Select All') }}
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                        onclick="deselectAllInSection('{{ $sectionKey }}')">
                                                                    <i class="fas fa-square me-1"></i>
                                                                    {{ __('Deselect All') }}
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <!-- Global Permission Actions -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="mb-3 text-center">{{ __('Quick Actions') }}</h6>

                                                        <!-- Role-Based Suggestions -->
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <h6 class="text-muted mb-2">{{ __('Role-Based Suggestions:') }}</h6>
                                                                <div class="btn-group-sm d-flex flex-wrap gap-2">
                                                                    <button type="button" class="btn btn-outline-primary" onclick="applyRolePermissions('admin')">
                                                                        <i class="fas fa-user-shield me-1"></i>
                                                                        {{ __('Admin Permissions') }}
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-info" onclick="applyRolePermissions('doctor')">
                                                                        <i class="fas fa-user-md me-1"></i>
                                                                        {{ __('Doctor Permissions') }}
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-success" onclick="applyRolePermissions('assistant')">
                                                                        <i class="fas fa-user-tie me-1"></i>
                                                                        {{ __('Assistant Permissions') }}
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-warning" onclick="applyRolePermissions('nurse')">
                                                                        <i class="fas fa-user-nurse me-1"></i>
                                                                        {{ __('Nurse Permissions') }}
                                                                    </button>
                                                                    <button type="button" class="btn btn-outline-secondary" onclick="applyRolePermissions('accountant')">
                                                                        <i class="fas fa-calculator me-1"></i>
                                                                        {{ __('Accountant Permissions') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <!-- General Actions -->
                                                        <div class="text-center">
                                                            <button type="button" class="btn btn-success me-2" onclick="selectAllPermissions()">
                                                                <i class="fas fa-check-double me-1"></i>
                                                                {{ __('Grant All Permissions') }}
                                                            </button>
                                                            <button type="button" class="btn btn-warning me-2" onclick="selectBasicPermissions()">
                                                                <i class="fas fa-user me-1"></i>
                                                                {{ __('Basic User Permissions') }}
                                                            </button>
                                                            <button type="button" class="btn btn-danger" onclick="deselectAllPermissions()">
                                                                <i class="fas fa-times me-1"></i>
                                                                {{ __('Remove All Permissions') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Professional Information (for medical staff) -->
                                    <div class="col-12 mt-4" id="professionalInfo" style="display: {{ in_array($user->role ?? 'doctor', ['doctor', 'assistant', 'nurse']) ? 'block' : 'none' }};">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-stethoscope me-2"></i>
                                            {{ __('Professional Information') }}
                                        </h6>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="medical_license" class="form-label">{{ __('Medical License Number') }}</label>
                                                <input type="text" class="form-control @error('medical_license') is-invalid @enderror" 
                                                       id="medical_license" name="medical_license" 
                                                       value="{{ old('medical_license', $user->medical_license ?? 'MD123456') }}">
                                                @error('medical_license')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="specialization" class="form-label">{{ __('Specialization') }}</label>
                                                <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                                       id="specialization" name="specialization" 
                                                       value="{{ old('specialization', $user->specialization ?? 'General Practice') }}" 
                                                       placeholder="{{ __('e.g., General Practice, Cardiology...') }}">
                                                @error('specialization')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Password Change -->
                                    <div class="col-12 mt-4">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">
                                            <i class="fas fa-lock me-2"></i>
                                            {{ __('Change Password') }}
                                        </h6>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            {{ __('Leave password fields empty to keep current password.') }}
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">{{ __('New Password') }}</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password">
                                        <div class="form-text">{{ __('Minimum 8 characters with letters and numbers') }}</div>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                               id="password_confirmation" name="password_confirmation">
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('users.show', $user->id ?? 1) }}" class="btn btn-secondary">
                                                <i class="fas fa-times me-1"></i>
                                                {{ __('Cancel') }}
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                {{ __('Update User') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Permission section mappings
const sectionPermissions = {
    @php
        $allPermissions = \App\Models\User::getAllPermissions();
    @endphp
    @foreach($allPermissions as $sectionKey => $sectionPermissions)
    '{{ $sectionKey }}': [
        @foreach($sectionPermissions as $permission => $label)
        '{{ $permission }}',
        @endforeach
    ],
    @endforeach
};

// Role-based permission suggestions
const rolePermissions = {
    @php
        $roles = ['admin', 'doctor', 'assistant', 'nurse', 'accountant', 'patient'];
    @endphp
    @foreach($roles as $role)
    '{{ $role }}': [
        @foreach(\App\Models\User::getSuggestedPermissions($role) as $permission)
        '{{ $permission }}',
        @endforeach
    ],
    @endforeach
};

function updateRoleDescription() {
    const role = document.getElementById('role').value;
    const descriptionDiv = document.getElementById('roleDescription');
    const descriptionText = document.getElementById('roleDescriptionText');
    const professionalInfo = document.getElementById('professionalInfo');
    const permissionsSection = document.getElementById('systemPermissions');

    const descriptions = {
        'admin': '{{ __("Administrative access to manage users, settings, and clinic operations.") }}',
        'doctor': '{{ __("Access to patient records, prescriptions, lab requests, and medical features.") }}',
        'nutritionist': '{{ __("Specialized access to nutrition plans, diet management, food database, and patient dietary care.") }}',
        'assistant': '{{ __("Patient management, appointment scheduling, and basic medical record access.") }}',
        'nurse': '{{ __("Patient care features, vital signs recording, and medication administration.") }}',
        'accountant': '{{ __("Financial management, invoicing, expense tracking, and reporting features.") }}'
    };

    if (role && descriptions[role]) {
        descriptionText.textContent = descriptions[role];
        descriptionDiv.style.display = 'block';
    } else {
        descriptionDiv.style.display = 'none';
    }

    // Show professional info for medical roles
    if (role === 'doctor' || role === 'assistant' || role === 'nurse') {
        professionalInfo.style.display = 'block';
    } else {
        professionalInfo.style.display = 'none';
    }

    // Permissions are always editable for maximum flexibility
    if (permissionsSection) {
        permissionsSection.style.opacity = '1';
        permissionsSection.style.pointerEvents = 'auto';
    }
}

// Permission management functions
function selectAllInSection(sectionKey) {
    const permissions = sectionPermissions[sectionKey] || [];
    permissions.forEach(permission => {
        const checkbox = document.getElementById('permission_' + permission);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function deselectAllInSection(sectionKey) {
    const permissions = sectionPermissions[sectionKey] || [];
    permissions.forEach(permission => {
        const checkbox = document.getElementById('permission_' + permission);
        if (checkbox) {
            checkbox.checked = false;
        }
    });
}

function selectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllPermissions() {
    const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}

function selectBasicPermissions() {
    // First deselect all
    deselectAllPermissions();

    // Then select basic permissions
    const basicPermissions = [
        'dashboard_view',
        'patients_view',
        'appointments_view',
        'prescriptions_view',
        'medicines_view'
    ];

    basicPermissions.forEach(permission => {
        const checkbox = document.getElementById('permission_' + permission);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
}

function applyRolePermissions(role) {
    // First deselect all permissions
    deselectAllPermissions();

    // Get suggested permissions for the role
    const permissions = rolePermissions[role] || [];

    // Apply the permissions
    permissions.forEach(permission => {
        const checkbox = document.getElementById('permission_' + permission);
        if (checkbox) {
            checkbox.checked = true;
        }
    });

    // Show a notification
    const roleNames = {
        'admin': 'Administrator',
        'doctor': 'Doctor',
        'assistant': 'Assistant',
        'nurse': 'Nurse',
        'accountant': 'Accountant',
        'patient': 'Patient'
    };

    // You could add a toast notification here if desired
    console.log(`Applied ${roleNames[role]} permissions (${permissions.length} permissions)`);
}

// Initialize role description on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRoleDescription();
});
</script>
@endsection
