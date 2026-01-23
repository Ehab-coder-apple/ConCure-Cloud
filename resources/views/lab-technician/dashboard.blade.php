@extends('layouts.app')

@section('title', __('Lab Technician Dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('Lab Technician Dashboard') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage lab requests and upload results') }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.lab-technician.patients') }}" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>
                        {{ __('Upload Patient Results') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Pending Requests') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-vial fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('Urgent') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['urgent_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Completed Today') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Overdue') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['overdue_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Lab Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Pending Lab Requests') }}
                    </h6>
                    <a href="{{ route('recommendations.lab-requests') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View All Requests') }}
                    </a>
                </div>
                <div class="card-body">
                    @if($pendingRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Tests') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingRequests as $request)
                                    <tr class="{{ $request->due_date && $request->due_date->isPast() ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $request->request_number }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $request->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $request->patient?->full_name ?? __('Unknown') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $request->patient?->patient_id }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $request->doctor?->full_name ?? __('N/A') }}</td>
                                        <td>
                                            <small>{{ $request->tests->count() }} {{ __('test(s)') }}</small>
                                        </td>
                                        <td>
                                            @if($request->priority === 'urgent')
                                                <span class="badge bg-danger">{{ __('Urgent') }}</span>
                                            @elseif($request->priority === 'high')
                                                <span class="badge bg-warning">{{ __('High') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->due_date)
                                                <div>{{ $request->due_date->format('M d, Y') }}</div>
                                                @if($request->due_date->isPast())
                                                    <small class="text-danger">
                                                        <i class="fas fa-exclamation-circle"></i>
                                                        {{ __('Overdue') }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($request->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('recommendations.lab-requests.show', $request->id) }}"
                                               class="btn btn-sm btn-primary"
                                               title="{{ __('View & Upload Result') }}">
                                                <i class="fas fa-upload"></i>
                                                {{ __('Upload') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="text-muted">{{ __('No pending lab requests') }}</h5>
                            <p class="text-muted">{{ __('All lab requests have been completed!') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>
                        {{ __('Quick Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('recommendations.lab-technician.patients') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-upload text-primary me-2"></i>
                            {{ __('Upload Patient Lab Results') }}
                            <small class="text-muted d-block">{{ __('Upload results for manual/verbal requests') }}</small>
                        </a>
                        <a href="{{ route('recommendations.lab-requests') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-info me-2"></i>
                            {{ __('View All Lab Requests') }}
                            <small class="text-muted d-block">{{ __('See all lab requests in the system') }}</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">{{ __('Lab Technician Responsibilities:') }}</h6>
                    <ul class="mb-0">
                        <li>{{ __('Upload results for formal lab requests') }}</li>
                        <li>{{ __('Upload results for manual/verbal requests') }}</li>
                        <li>{{ __('Ensure timely completion of urgent requests') }}</li>
                        <li>{{ __('Maintain accurate patient records') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .text-xs {
        font-size: 0.7rem;
    }
</style>
@endpush


