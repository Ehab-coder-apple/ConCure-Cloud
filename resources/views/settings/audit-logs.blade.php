@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-history text-primary"></i>
                        {{ __('Audit Logs') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('System activity and user actions log') }}</p>
                </div>
                <div>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Settings') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-filter"></i>
                        {{ __('Filter Logs') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('settings.audit-logs') }}">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="action" class="form-label">{{ __('Action') }}</label>
                                <select class="form-select" id="action" name="action">
                                    <option value="">{{ __('All Actions') }}</option>
                                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>{{ __('Login') }}</option>
                                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>{{ __('Logout') }}</option>
                                    <option value="patient_created" {{ request('action') === 'patient_created' ? 'selected' : '' }}>{{ __('Patient Created') }}</option>
                                    <option value="prescription_created" {{ request('action') === 'prescription_created' ? 'selected' : '' }}>{{ __('Prescription Created') }}</option>
                                    <option value="appointment_created" {{ request('action') === 'appointment_created' ? 'selected' : '' }}>{{ __('Appointment Created') }}</option>
                                    <option value="settings_updated" {{ request('action') === 'settings_updated' ? 'selected' : '' }}>{{ __('Settings Updated') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="user_role" class="form-label">{{ __('User Role') }}</label>
                                <select class="form-select" id="user_role" name="user_role">
                                    <option value="">{{ __('All Roles') }}</option>
                                    <option value="admin" {{ request('user_role') === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                                    <option value="doctor" {{ request('user_role') === 'doctor' ? 'selected' : '' }}>{{ __('Doctor') }}</option>
                                    <option value="nurse" {{ request('user_role') === 'nurse' ? 'selected' : '' }}>{{ __('Nurse') }}</option>
                                    <option value="receptionist" {{ request('user_role') === 'receptionist' ? 'selected' : '' }}>{{ __('Receptionist') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_from" class="form-label">{{ __('Date From') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="date_to" class="form-label">{{ __('Date To') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                                <a href="{{ route('settings.audit-logs') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-list"></i>
                        {{ __('Activity Log') }}
                    </h6>
                    <div class="text-muted small">
                        {{ __('Total: :count entries', ['count' => $auditLogs->total()]) }}
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($auditLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date & Time') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Action') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('IP Address') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($auditLogs as $log)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($log->performed_at)->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->performed_at)->format('g:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $log->user_name ?? 'System' }}</div>
                                            @if($log->user_id)
                                            <small class="text-muted">ID: {{ $log->user_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->user_role === 'admin' ? 'danger' : ($log->user_role === 'doctor' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst($log->user_role ?? 'system') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ str_replace('_', ' ', ucfirst($log->action)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $log->description }}</div>
                                            @if($log->old_values || $log->new_values)
                                            <div class="mt-1">
                                                @if($log->old_values)
                                                <small class="text-muted d-block">
                                                    <strong>{{ __('Before:') }}</strong> {{ $log->old_values }}
                                                </small>
                                                @endif
                                                @if($log->new_values)
                                                <small class="text-muted d-block">
                                                    <strong>{{ __('After:') }}</strong> {{ $log->new_values }}
                                                </small>
                                                @endif
                                            </div>
                                            @endif
                                            @if($log->model_type && $log->model_id)
                                            <small class="text-muted d-block">
                                                {{ $log->model_type }} ID: {{ $log->model_id }}
                                            </small>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted font-monospace">{{ $log->ip_address ?? 'N/A' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No audit logs found') }}</h5>
                            <p class="text-muted">{{ __('No activity has been logged yet or your filters returned no results.') }}</p>
                        </div>
                    @endif
                </div>
                @if($auditLogs->hasPages())
                <div class="card-footer">
                    {{ $auditLogs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
