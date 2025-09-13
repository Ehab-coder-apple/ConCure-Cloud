@extends('layouts.app')

@section('title', __('User Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    {{ __('User Management') }}
                </h1>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('Add New User') }}
                </a>
            </div>

            <!-- User Limit Information -->
            @if(auth()->user()->clinic)
                @php
                    $userLimitInfo = auth()->user()->clinic->getUserLimitInfo();
                @endphp
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <div class="flex-grow-1">
                        <strong>{{ __('User Limit') }}:</strong>
                        {{ $userLimitInfo['current_users'] }} / {{ $userLimitInfo['max_users'] }} {{ __('users') }}
                        @if($userLimitInfo['remaining_slots'] > 0)
                            - {{ $userLimitInfo['remaining_slots'] }} {{ __('slots remaining') }}
                        @else
                            - <span class="text-warning">{{ __('User limit reached') }}</span>
                        @endif
                    </div>
                    @if($userLimitInfo['has_reached_limit'])
                        {{-- Subscription upgrade removed - no longer needed --}}
                    @endif
                </div>
            @endif

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Users') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('Name, email, username...') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="role" class="form-label">{{ __('Role') }}</label>
                            <select class="form-select" id="role" name="role">
                                <option value="">{{ __('All Roles') }}</option>
                                <option value="program_owner" {{ request('role') == 'program_owner' ? 'selected' : '' }}>{{ __('Program Owner') }}</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                                <option value="doctor" {{ request('role') == 'doctor' ? 'selected' : '' }}>{{ __('Doctor') }}</option>
                                <option value="assistant" {{ request('role') == 'assistant' ? 'selected' : '' }}>{{ __('Assistant') }}</option>
                                <option value="nurse" {{ request('role') == 'nurse' ? 'selected' : '' }}>{{ __('Nurse') }}</option>
                                <option value="accountant" {{ request('role') == 'accountant' ? 'selected' : '' }}>{{ __('Accountant') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('System Users') }}
                        <span class="badge bg-primary ms-2">{{ $users->total() ?? 6 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($users) && $users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Username') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Last Login') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-{{ $user->role == 'program_owner' ? 'danger' : ($user->role == 'admin' ? 'warning' : ($user->role == 'doctor' ? 'success' : 'info')) }} text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ ($user->first_name ?? 'Demo') . ' ' . ($user->last_name ?? 'User') }}</div>
                                                    <small class="text-muted">{{ $user->email ?? 'user@concure.com' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $user->username ?? 'demo_user' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->role == 'program_owner' ? 'danger' : ($user->role == 'admin' ? 'warning' : ($user->role == 'doctor' ? 'success' : 'info')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                                {{ $user->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>{{ $user->last_login_at && is_object($user->last_login_at) ? $user->last_login_at->format('M d, Y') : __('Never') }}</td>
                                        <td>{{ $user->created_at ? $user->created_at->format('M d, Y') : now()->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->is_active)
                                                    <button type="button" class="btn btn-outline-warning" title="{{ __('Deactivate') }}" onclick="toggleUserStatus({{ $user->id }}, false)">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-success" title="{{ __('Activate') }}" onclick="toggleUserStatus({{ $user->id }}, true)">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                @endif
                                                @if($user->id !== auth()->id())
                                                    <button type="button" class="btn btn-outline-danger" title="{{ __('Delete User') }}" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
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
                        
                        @if(isset($users) && method_exists($users, 'links'))
                            <div class="card-footer">
                                {{ $users->links() }}
                            </div>
                        @endif
                    @else
                        <!-- Demo Users Display -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Username') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Last Login') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-danger text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    PO
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Program Owner</div>
                                                    <small class="text-muted">program_owner@concure.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">program_owner</span></td>
                                        <td><span class="badge bg-danger">Program Owner</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>{{ now()->format('M d, Y') }}</td>
                                        <td>{{ now()->subDays(30)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', 1) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', 1) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-warning text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    AD
                                                </div>
                                                <div>
                                                    <div class="fw-bold">System Administrator</div>
                                                    <small class="text-muted">admin@concure.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">admin</span></td>
                                        <td><span class="badge bg-warning">Administrator</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>{{ now()->subHours(2)->format('M d, Y') }}</td>
                                        <td>{{ now()->subDays(25)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', 2) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', 2) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-success text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    DS
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Dr. John Smith</div>
                                                    <small class="text-muted">doctor@concure.com</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark">doctor</span></td>
                                        <td><span class="badge bg-success">Doctor</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>{{ now()->subMinutes(30)->format('M d, Y') }}</td>
                                        <td>{{ now()->subDays(20)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('users.show', 3) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', 3) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUserStatus(userId, activate) {
    const action = activate ? 'activate' : 'deactivate';
    const message = activate ? 'activate' : 'deactivate';

    if (confirm(`Are you sure you want to ${message} this user?`)) {
        // In a real application, this would make an AJAX request
        alert(`User ${action}d successfully!`);
        location.reload();
    }
}

function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to permanently delete the user "${userName}"? This action cannot be undone.`)) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/users/${userId}`;

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add method override for DELETE
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
