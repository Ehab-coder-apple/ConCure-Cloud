@extends('layouts.app')

@section('title', __('All Dental Charts'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('All Dental Charts') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('View and manage dental charts for all patients') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('dental.charts.all') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Patient') }}</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="{{ __('Patient name or ID') }}" 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="chart_type" class="form-label">{{ __('Chart Type') }}</label>
                            <select name="chart_type" id="chart_type" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="adult" {{ request('chart_type') == 'adult' ? 'selected' : '' }}>{{ __('Adult') }}</option>
                                <option value="pediatric" {{ request('chart_type') == 'pediatric' ? 'selected' : '' }}>{{ __('Pediatric') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">{{ __('Sort By') }}</label>
                            <select name="sort_by" id="sort_by" class="form-select">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>{{ __('Date Created') }}</option>
                                <option value="updated_at" {{ request('sort_by') == 'updated_at' ? 'selected' : '' }}>{{ __('Last Updated') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Filter') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Dental Charts') }} ({{ $charts->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($charts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Chart ID') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Chart Type') }}</th>
                                        <th>{{ __('Teeth Records') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Created Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($charts as $chart)
                                        <tr>
                                            <td><strong>#{{ $chart->id }}</strong></td>
                                            <td>
                                                <a href="{{ route('patients.show', $chart->patient) }}" class="text-decoration-none">
                                                    {{ $chart->patient->full_name }}
                                                </a>
                                            </td>
                                            <td>{{ $chart->patient->patient_id }}</td>
                                            <td>
                                                <span class="badge bg-{{ $chart->chart_type === 'adult' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($chart->chart_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $chart->toothRecords->count() }}</strong> {{ __('teeth') }}
                                            </td>
                                            <td>{{ $chart->creator->full_name ?? 'N/A' }}</td>
                                            <td>{{ $chart->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ url("/dental/patients/{$chart->patient_id}/charts/{$chart->id}") }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="{{ __('View Chart') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                                                    <a href="{{ url("/dental/patients/{$chart->patient_id}/charts/{$chart->id}/edit") }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $charts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-tooth fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No dental charts found') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

