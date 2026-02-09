@extends('layouts.app')

@section('title', __('Radiology Technician Dashboard'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-x-ray me-2"></i>
                        {{ __('Radiology Technician Dashboard') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage radiology requests and upload results') }}</p>
                </div>
                <div>
                    <a href="{{ route('recommendations.radiology-technician.patients') }}" class="btn btn-primary">
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-x-ray fa-2x text-gray-300"></i>
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
                                {{ __('Scheduled') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['scheduled'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('In Progress') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
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
    </div>

    <!-- Pending Requests -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('Pending Radiology Requests') }}
                    </h6>
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
                                    <tr>
                                        <td>
                                            <strong>{{ $request->request_number }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $request->patient->full_name }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $request->patient->patient_id }}</small>
                                        </td>
                                        <td>{{ $request->doctor->full_name }}</td>
                                        <td>
                                            <small>{{ $request->tests->count() }} {{ __('test(s)') }}</small>
                                        </td>
                                        <td>
                                            @if($request->priority === 'stat')
                                                <span class="badge bg-danger">{{ __('STAT') }}</span>
                                            @elseif($request->priority === 'urgent')
                                                <span class="badge bg-warning">{{ __('Urgent') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Normal') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->due_date)
                                                {{ $request->due_date->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-warning">{{ __('Pending') }}</span>
                                            @elseif($request->status === 'scheduled')
                                                <span class="badge bg-info">{{ __('Scheduled') }}</span>
                                            @elseif($request->status === 'in_progress')
                                                <span class="badge bg-primary">{{ __('In Progress') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('recommendations.radiology.show', $request->id) }}"
                                               class="btn btn-sm btn-info"
                                               title="{{ __('View Details') }}">
                                                <i class="fas fa-eye"></i>
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
                            <h5 class="text-muted">{{ __('No pending radiology requests') }}</h5>
                            <p class="text-muted">{{ __('All requests have been processed') }}</p>
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
                        <a href="{{ route('recommendations.radiology-technician.patients') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-upload text-primary me-2"></i>
                            {{ __('Upload Patient Radiology Results') }}
                            <small class="text-muted d-block">{{ __('Upload results for manual/verbal requests') }}</small>
                        </a>
                        <a href="{{ route('recommendations.radiology.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-info me-2"></i>
                            {{ __('View All Radiology Requests') }}
                            <small class="text-muted d-block">{{ __('Browse all radiology requests') }}</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-check-double me-2"></i>
                        {{ __('Recently Completed') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($completedRequests->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($completedRequests->take(5) as $request)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $request->patient->full_name }}</strong>
                                            <small class="text-muted d-block">{{ $request->request_number }}</small>
                                        </div>
                                        <small class="text-muted">{{ $request->result_received_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">{{ __('No completed requests in the last 30 days') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


