@extends('layouts.master-welcome')

@section('title', 'Invite Team Members - ConCure Master Control')

@section('content')
<div class="master-container">
    <div class="container">
        <div class="form-container mx-auto">
            <div class="form-header">
                <div class="master-badge">
                    <i class="fas fa-users me-1"></i>
                    Team Management
                </div>
                <h1 class="form-title">
                    <i class="fas fa-user-plus text-primary me-2"></i>
                    Invite Team Members
                </h1>
                <p class="form-subtitle">Add platform administrators and support agents to your team</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('master.welcome.send-invitation') }}">
                @csrf

                <!-- Team Member Information -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>
                        Team Member Information
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name') }}" 
                                   placeholder="John" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name') }}" 
                                   placeholder="Doe" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="team.member@company.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Role Selection -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user-tag me-2"></i>
                        Role Assignment
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('role') is-invalid @enderror" 
                                       type="radio" name="role" id="platform_admin" value="platform_admin" 
                                       {{ old('role') === 'platform_admin' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="platform_admin">
                                    <strong>Platform Administrator</strong>
                                    <br><small class="text-muted">Administrative access with operational control</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input @error('role') is-invalid @enderror" 
                                       type="radio" name="role" id="support_agent" value="support_agent" 
                                       {{ old('role') === 'support_agent' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="support_agent">
                                    <strong>Support Agent</strong>
                                    <br><small class="text-muted">Customer support and basic monitoring access</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('role')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Permissions -->
                <div class="mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-key me-2"></i>
                        Permissions
                    </h5>
                    
                    <div class="row" id="permissions-container">
                        <!-- Platform Admin Permissions -->
                        <div class="permissions-group" id="platform_admin_permissions" style="display: none;">
                            <div class="col-12 mb-3">
                                <h6 class="fw-bold">Platform Administrator Permissions</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="clinics_view" id="perm_clinics_view" checked>
                                            <label class="form-check-label" for="perm_clinics_view">
                                                View Clinics
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="clinics_edit" id="perm_clinics_edit" checked>
                                            <label class="form-check-label" for="perm_clinics_edit">
                                                Edit Clinics
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="analytics_view" id="perm_analytics_view" checked>
                                            <label class="form-check-label" for="perm_analytics_view">
                                                View Analytics
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="audit_logs_view" id="perm_audit_logs_view" checked>
                                            <label class="form-check-label" for="perm_audit_logs_view">
                                                View Audit Logs
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="system_health_view" id="perm_system_health_view" checked>
                                            <label class="form-check-label" for="perm_system_health_view">
                                                System Health
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="platform_users_view" id="perm_platform_users_view" checked>
                                            <label class="form-check-label" for="perm_platform_users_view">
                                                View Platform Users
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Support Agent Permissions -->
                        <div class="permissions-group" id="support_agent_permissions" style="display: none;">
                            <div class="col-12 mb-3">
                                <h6 class="fw-bold">Support Agent Permissions</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="clinics_view" id="perm_support_clinics_view" checked>
                                            <label class="form-check-label" for="perm_support_clinics_view">
                                                View Clinics (Read-only)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="analytics_view" id="perm_support_analytics_view" checked>
                                            <label class="form-check-label" for="perm_support_analytics_view">
                                                Basic Analytics
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="support_tickets_view" id="perm_support_tickets_view" checked>
                                            <label class="form-check-label" for="perm_support_tickets_view">
                                                Support Tickets
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                   value="user_assistance" id="perm_user_assistance" checked>
                                            <label class="form-check-label" for="perm_user_assistance">
                                                User Assistance
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @error('permissions')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Information Alert -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle me-1"></i>
                        Invitation Process
                    </h6>
                    <ul class="mb-0">
                        <li>Team member will receive an email with temporary login credentials</li>
                        <li>They must change their password on first login</li>
                        <li>You can modify permissions later from the team management panel</li>
                        <li>Only Program Owners can invite new team members</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-master btn-lg">
                        <i class="fas fa-user-plus me-2"></i>
                        Send Invitation
                    </button>
                </div>

                <!-- Back Link -->
                <div class="text-center mt-4">
                    <p class="text-muted">
                        <a href="{{ route('master.dashboard') }}" class="text-primary text-decoration-none fw-bold">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Master Dashboard
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="{{ route('master.welcome.index') }}" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Master Control Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleRadios = document.querySelectorAll('input[name="role"]');
        const permissionsGroups = document.querySelectorAll('.permissions-group');

        function updatePermissions() {
            const selectedRole = document.querySelector('input[name="role"]:checked');
            
            // Hide all permission groups
            permissionsGroups.forEach(group => {
                group.style.display = 'none';
                // Uncheck all checkboxes in hidden groups
                group.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
            });

            if (selectedRole) {
                const permissionsGroup = document.getElementById(selectedRole.value + '_permissions');
                if (permissionsGroup) {
                    permissionsGroup.style.display = 'block';
                    // Check all checkboxes in the visible group
                    permissionsGroup.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = true;
                    });
                }
            }
        }

        // Add event listeners to role radio buttons
        roleRadios.forEach(radio => {
            radio.addEventListener('change', updatePermissions);
        });

        // Initialize permissions display
        updatePermissions();
    });
</script>
@endpush
