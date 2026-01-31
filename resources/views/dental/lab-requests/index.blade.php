@extends('layouts.app')

@section('title', __('Dental Lab Requests'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Dental Lab Requests') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage dental laboratory work requests') }}</p>
                </div>
                <div>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ route('dental.lab-requests.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Lab Request') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('dental.lab-requests.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="{{ __('Request #, Patient, Lab...') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach(\App\Models\DentalLabRequest::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority" class="form-label">{{ __('Priority') }}</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">{{ __('All Priorities') }}</option>
                                @foreach(\App\Models\DentalLabRequest::PRIORITIES as $key => $label)
                                    <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="work_type" class="form-label">{{ __('Work Type') }}</label>
                            <select class="form-select" id="work_type" name="work_type">
                                <option value="">{{ __('All Types') }}</option>
                                @foreach(\App\Models\DentalLabRequest::WORK_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ request('work_type') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="external_lab_id" class="form-label">{{ __('Dental Lab') }}</label>
                            <select class="form-select" id="external_lab_id" name="external_lab_id">
                                <option value="">{{ __('All Labs') }}</option>
                                @foreach($dentalLabs as $lab)
                                    <option value="{{ $lab->id }}" {{ request('external_lab_id') == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>
                                {{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('dental.lab-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo me-1"></i>
                                {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Lab Requests') }} ({{ $requests->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Work Type') }}</th>
                                        <th>{{ __('Dental Lab') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Requested') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr>
                                            <td>
                                                <strong>{{ $request->request_number }}</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('patients.show', $request->patient) }}" class="text-decoration-none">
                                                    {{ $request->patient->full_name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $request->patient->patient_id }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $request->work_type_display }}</strong>
                                                @if($request->tooth_number)
                                                    <br><small class="text-muted">Tooth #{{ $request->tooth_number }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->externalLab)
                                                    {{ $request->externalLab->name }}
                                                @else
                                                    <span class="text-muted">{{ __('Not assigned') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="{{ $request->status_badge_class }}">
                                                    {{ $request->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="{{ $request->priority_badge_class }}">
                                                    {{ $request->priority_display }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $request->requested_date->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $request->doctor->full_name }}</small>
                                            </td>
                                            <td>
                                                @if($request->due_date)
                                                    {{ $request->due_date->format('M d, Y') }}
                                                    @if($request->due_date->isPast() && $request->status !== 'completed')
                                                        <br><span class="badge bg-danger">{{ __('Overdue') }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('dental.lab-requests.show', $request) }}"
                                                   class="btn btn-sm btn-info" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'lab_dept']))
                                                    <a href="{{ route('dental.lab-requests.edit', $request) }}"
                                                       class="btn btn-sm btn-primary" title="{{ __('Edit') }}">
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
                            {{ $requests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('No dental lab requests found.') }}</p>
                            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                                <a href="{{ route('dental.lab-requests.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Create First Lab Request') }}
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

