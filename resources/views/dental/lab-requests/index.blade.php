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
                                <option value="uploaded" {{ request('status') == 'uploaded' ? 'selected' : '' }}>
                                    {{ __('Uploaded (Results Ready)') }}
                                </option>
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
                        <div class="col-md-3">
                            <label for="doctor_name" class="form-label">{{ __('Doctor Name') }}</label>
                            <input type="text" class="form-control" id="doctor_name" name="doctor_name"
                                   value="{{ request('doctor_name') }}"
                                   placeholder="{{ __('Search doctor...') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="material" class="form-label">{{ __('Material') }}</label>
                            <select class="form-select" id="material" name="material">
                                <option value="">{{ __('All Materials') }}</option>
                                @foreach(\App\Models\DentalLabRequest::MATERIALS as $key => $label)
                                    <option value="{{ $key }}" {{ request('material') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="assigned_person" class="form-label">{{ __('Assigned Person') }}</label>
                            <input type="text" class="form-control" id="assigned_person" name="assigned_person"
                                   value="{{ request('assigned_person') }}"
                                   placeholder="{{ __('Search technician/designer...') }}">
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
	                                        <th>{{ __('Assigned') }}</th>
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
                                            <td>{{-- Phase 35: external patient support --}}
                                                @if($request->patient_id && $request->patient)
                                                    <a href="{{ route('patients.show', $request->patient) }}" class="text-decoration-none">
                                                        {{ $request->patient->full_name }}
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">{{ $request->patient->patient_id }}</small>
                                                @elseif($request->external_patient_name)
                                                    {{ $request->external_patient_name }}
                                                    <br>
                                                    <small class="badge bg-secondary">{{ __('External') }}</small>
                                                @else
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @endif
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
	                                                @if($request->assignedTechnician || $request->assignedDesigner)
	                                                    @if($request->assignedTechnician)
	                                                        <small class="text-muted">{{ __('Tech') }}:</small>
	                                                        {{ $request->assignedTechnician->full_name }}
	                                                    @endif
	                                                    @if($request->assignedDesigner)
	                                                        <br>
	                                                        <small class="text-muted">{{ __('Designer') }}:</small>
	                                                        {{ $request->assignedDesigner->full_name }}
	                                                    @endif
	                                                @else
	                                                    <span class="text-muted">-</span>
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
                                                <small class="text-muted">
                                                    @if($request->doctor_id && $request->doctor)
                                                        {{ $request->doctor->full_name }}
                                                    @elseif($request->external_doctor_name)
                                                        {{ $request->external_doctor_name }} <span class="badge bg-secondary">{{ __('Ext') }}</span>
                                                    @endif
                                                </small>
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
		                                                @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'master_admin', 'super_admin', 'lab_dept', 'dental_technician', 'cad_cam_designer']))
                                                    <a href="{{ route('dental.lab-requests.edit', $request) }}"
                                                       class="btn btn-sm btn-primary" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
		                                                @if(in_array(auth()->user()->role, ['lab_dept', 'dental_technician', 'cad_cam_designer', 'admin', 'master_admin', 'super_admin']) && !in_array($request->status, ['completed', 'cancelled']))
	                                                    <form method="POST" action="{{ route('dental.lab-requests.complete', $request) }}" class="d-inline"
	                                                          onsubmit="return confirm('{{ __('Mark this lab request as completed?') }}');">
	                                                        @csrf
	                                                        <button type="submit" class="btn btn-sm btn-success" title="{{ __('Mark Completed') }}">
	                                                            <i class="fas fa-check"></i>
	                                                        </button>
	                                                    </form>
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

