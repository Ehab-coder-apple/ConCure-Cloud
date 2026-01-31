@extends('layouts.app')

@section('title', __('Dental Charts') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Dental Charts') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ url("/dental/patients/{$patient->id}/charts/create") }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Dental Chart') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Age') }}</small>
                            <p class="mb-0"><strong>{{ $patient->age ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Phone') }}</small>
                            <p class="mb-0"><strong>{{ $patient->phone ?? 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Total Charts') }}</small>
                            <p class="mb-0"><strong>{{ $charts->total() }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ __('Latest Chart') }}</small>
                            <p class="mb-0"><strong>{{ $charts->first()?->created_at?->format('M d, Y') ?? 'None' }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dental Charts List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Dental Chart History') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($charts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Chart Type') }}</th>
                                        <th>{{ __('Teeth Recorded') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($charts as $chart)
                                        <tr>
                                            <td>
                                                <strong>{{ $chart->created_at->format('M d, Y') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $chart->created_at->format('h:i A') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $chart->chart_type === 'adult' ? 'primary' : 'info' }}">
                                                    {{ ucfirst($chart->chart_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $chart->toothRecords->count() }}</strong> {{ __('teeth') }}
                                            </td>
                                            <td>{{ $chart->creator->name ?? 'N/A' }}</td>
                                            <td>
                                                <small>{{ Str::limit($chart->general_notes, 50) }}</small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url("/dental/patients/{$patient->id}/charts/{$chart->id}") }}" 
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('View Chart') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                                                    <a href="{{ url("/dental/patients/{$patient->id}/charts/{$chart->id}/edit") }}" 
                                                       class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}">
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
                            <h5 class="text-muted">{{ __('No Dental Charts Yet') }}</h5>
                            <p class="text-muted">{{ __('Create the first dental chart for this patient') }}</p>
                            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                                <a href="{{ url("/dental/patients/{$patient->id}/charts/create") }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Create First Chart') }}
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

