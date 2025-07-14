@extends('layouts.app')

@section('title', __('User Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        {{ __('User Details') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">{{ __('Users') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('User Details') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('users.edit', $user->id ?? 1) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit User') }}
                    </a>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Users') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- User Profile -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-circle me-2"></i>
                                {{ __('User Profile') }}
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="avatar bg-{{ $user->role == 'program_owner' ? 'danger' : ($user->role == 'admin' ? 'warning' : ($user->role == 'doctor' ? 'success' : 'info')) }} text-white rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                {{ strtoupper(substr($user->first_name ?? 'D', 0, 1) . substr($user->last_name ?? 'U', 0, 1)) }}
                            </div>
                            <h5 class="mb-1">{{ ($user->first_name ?? 'Demo') . ' ' . ($user->last_name ?? 'User') }}</h5>
                            <p class="text-muted mb-2">{{ $user->email ?? 'demo@concure.com' }}</p>
                            <span class="badge bg-{{ $user->role == 'program_owner' ? 'danger' : ($user->role == 'admin' ? 'warning' : ($user->role == 'doctor' ? 'success' : 'info')) }} fs-6">
                                {{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}
                            </span>
                            <div class="mt-3">
                                <span class="badge bg-{{ $user->is_active ?? true ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ?? true ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-address-card me-2"></i>
                                {{ __('Contact Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Username') }}</small>
                                    <div class="fw-bold">{{ $user->username ?? 'demo_user' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Email') }}</small>
                                    <div class="fw-bold">{{ $user->email ?? 'demo@concure.com' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Phone') }}</small>
                                    <div class="fw-bold">{{ $user->phone ?? '+1-555-0123' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Language') }}</small>
                                    <div class="fw-bold">
                                        @switch($user->language ?? 'en')
                                            @case('ar')
                                                العربية
                                                @break
                                            @case('ku')
                                                کوردی
                                                @break
                                            @default
                                                English
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(in_array($user->role ?? 'doctor', ['doctor', 'assistant', 'nurse']))
                    <!-- Professional Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                {{ __('Professional Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Medical License') }}</small>
                                    <div class="fw-bold">{{ $user->medical_license ?? 'MD123456' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Specialization') }}</small>
                                    <div class="fw-bold">{{ $user->specialization ?? 'General Practice' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Years of Experience') }}</small>
                                    <div class="fw-bold">{{ $user->experience_years ?? '10' }} {{ __('years') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- User Activity and Details -->
                <div class="col-lg-8">
                    <!-- Account Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Account Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('User ID') }}</small>
                                    <div class="fw-bold">#{{ $user->id ?? '1' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('Account Created') }}</small>
                                    <div class="fw-bold">{{ $user->created_at ? $user->created_at->format('F d, Y') : now()->subDays(30)->format('F d, Y') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('Last Login') }}</small>
                                    <div class="fw-bold">{{ $user->last_login_at ? $user->last_login_at->format('F d, Y g:i A') : now()->format('F d, Y g:i A') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('Email Verified') }}</small>
                                    <div class="fw-bold">
                                        @if($user->email_verified_at ?? true)
                                            <span class="badge bg-success">{{ __('Verified') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('Account Status') }}</small>
                                    <div class="fw-bold">
                                        <span class="badge bg-{{ $user->is_active ?? true ? 'success' : 'secondary' }}">
                                            {{ $user->is_active ?? true ? __('Active') : __('Inactive') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">{{ __('Created By') }}</small>
                                    <div class="fw-bold">{{ $user->creator->full_name ?? 'System Administrator' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Permissions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-shield me-2"></i>
                                {{ __('Role & Permissions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-primary">{{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}</h6>
                                <p class="text-muted mb-3">
                                    @switch($user->role ?? 'doctor')
                                        @case('program_owner')
                                            {{ __('Full system access including user management, settings, and all clinic operations.') }}
                                            @break
                                        @case('admin')
                                            {{ __('Administrative access to manage users, settings, and clinic operations.') }}
                                            @break
                                        @case('doctor')
                                            {{ __('Access to patient records, prescriptions, lab requests, and medical features.') }}
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
                                </p>
                            </div>
                            
                            <div class="row g-2">
                                @php
                                    $permissions = [
                                        'patients' => __('Patient Management'),
                                        'prescriptions' => __('Prescriptions'),
                                        'lab_requests' => __('Lab Requests'),
                                        'appointments' => __('Appointments'),
                                        'finance' => __('Financial Management'),
                                        'reports' => __('Reports'),
                                        'settings' => __('Settings'),
                                        'users' => __('User Management')
                                    ];
                                    
                                    $rolePermissions = [
                                        'program_owner' => array_keys($permissions),
                                        'admin' => ['patients', 'prescriptions', 'lab_requests', 'appointments', 'finance', 'reports', 'settings', 'users'],
                                        'doctor' => ['patients', 'prescriptions', 'lab_requests', 'appointments', 'reports'],
                                        'assistant' => ['patients', 'appointments'],
                                        'nurse' => ['patients', 'appointments'],
                                        'accountant' => ['finance', 'reports']
                                    ];
                                    
                                    $userPermissions = $rolePermissions[$user->role ?? 'doctor'] ?? [];
                                @endphp
                                
                                @foreach($permissions as $key => $permission)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            @if(in_array($key, $userPermissions))
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                            @else
                                                <i class="fas fa-times-circle text-muted me-2"></i>
                                            @endif
                                            <span class="{{ in_array($key, $userPermissions) ? 'text-dark' : 'text-muted' }}">{{ $permission }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-history me-2"></i>
                                {{ __('Recent Activity') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ __('Logged in') }}</h6>
                                        <p class="text-muted mb-0">{{ now()->format('F d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ __('Updated patient record') }}</h6>
                                        <p class="text-muted mb-0">{{ now()->subHours(2)->format('F d, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">{{ __('Created prescription') }}</h6>
                                        <p class="text-muted mb-0">{{ now()->subHours(4)->format('F d, Y g:i A') }}</p>
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

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}
</style>
@endsection
