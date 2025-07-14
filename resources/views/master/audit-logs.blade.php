@extends('layouts.master')

@section('title', __('Audit Logs'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-history text-info me-2"></i>
                        {{ __('Audit Logs') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Audit Logs') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('master.audit-logs.export') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i>
                        {{ __('Export Logs') }}
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('master.audit-logs') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="action" class="form-label">{{ __('Action') }}</label>
                            <input type="text" class="form-control" id="action" name="action" 
                                   value="{{ request('action') }}" placeholder="{{ __('Search actions...') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                    {{ __('Filter') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('System Activity Logs') }}
                        <span class="badge bg-primary ms-2">{{ $auditLogs->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($auditLogs) && $auditLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Timestamp') }}</th>
                                        <th>{{ __('User') }}</th>
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
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->performed_at)->format('H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $log->user_name ?? 'System' }}</div>
                                                <small class="text-muted">{{ $log->user_role ?? 'system' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $log->action }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div>{{ $log->description ?? $log->action }}</div>
                                                @if($log->model_type && $log->model_id)
                                                    <small class="text-muted">{{ $log->model_type }} #{{ $log->model_id }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <small class="font-monospace">{{ $log->ip_address ?? 'N/A' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(method_exists($auditLogs, 'links'))
                            <div class="card-footer">
                                {{ $auditLogs->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Audit Logs Found') }}</h5>
                            <p class="text-muted">{{ __('No activity logs match your current filters.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
