@extends('layouts.app')

@section('title', __('Orthodontic Cases'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-teeth me-2 text-primary"></i>
                        {{ __('Orthodontic Cases') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage orthodontic treatment cases') }}</p>
                </div>
                <div>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ route('orthodontics.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Orthodontic Case') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Total Cases') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Active Cases') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-smile-beam fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Completed Cases') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('orthodontics.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Treatment Type') }}</label>
                            <select name="treatment_type" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="metal_braces" {{ request('treatment_type') === 'metal_braces' ? 'selected' : '' }}>{{ __('Metal Braces') }}</option>
                                <option value="ceramic_braces" {{ request('treatment_type') === 'ceramic_braces' ? 'selected' : '' }}>{{ __('Ceramic Braces') }}</option>
                                <option value="clear_aligners" {{ request('treatment_type') === 'clear_aligners' ? 'selected' : '' }}>{{ __('Clear Aligners') }}</option>
                                <option value="lingual_braces" {{ request('treatment_type') === 'lingual_braces' ? 'selected' : '' }}>{{ __('Lingual Braces') }}</option>
                                <option value="self_ligating" {{ request('treatment_type') === 'self_ligating' ? 'selected' : '' }}>{{ __('Self-Ligating') }}</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ __('Search') }}</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Patient name, case #') }}" value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('orthodontics.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo me-1"></i>{{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cases List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Orthodontic Cases') }} ({{ $cases->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($cases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Case #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Treatment Type') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('Duration') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Payment') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cases as $case)
                                    <tr>
                                        <td>
                                            <strong>{{ $case->case_number }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('patients.show', $case->patient) }}">
                                                {{ $case->patient->full_name }}
                                            </a>
                                            <br><small class="text-muted">{{ $case->patient->patient_id }}</small>
                                        </td>
                                        <td>{{ \App\Models\OrthodonticCase::TREATMENT_TYPES[$case->treatment_type] ?? $case->treatment_type }}</td>
                                        <td>{{ $case->doctor->full_name }}</td>
                                        <td>{{ $case->start_date->format('Y-m-d') }}</td>
                                        <td>{{ $case->estimated_duration_months }} {{ __('months') }}</td>
                                        <td>
                                            @if($case->status === 'active')
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @elseif($case->status === 'completed')
                                                <span class="badge bg-info">{{ __('Completed') }}</span>
                                            @elseif($case->status === 'paused')
                                                <span class="badge bg-warning">{{ __('Paused') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Cancelled') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $paymentPercentage = $case->total_cost > 0 ? ($case->paid_amount / $case->total_cost * 100) : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ min($paymentPercentage, 100) }}%;"
                                                     aria-valuenow="{{ $paymentPercentage }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    {{ number_format($paymentPercentage, 0) }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($case->paid_amount, 2) }} / {{ number_format($case->total_cost, 2) }} {{ $case->currency }}
                                            </small>
                                        </td>
                                        <td>
                                            <a href="{{ route('orthodontics.show', $case) }}" class="btn btn-sm btn-info" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('orthodontics.edit', $case) }}" class="btn btn-sm btn-warning" title="{{ __('Edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $cases->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-teeth fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No orthodontic cases found.') }}</p>
                            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                                <a href="{{ route('orthodontics.create') }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Create Your First Case') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
