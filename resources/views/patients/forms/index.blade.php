@extends('layouts.app')

@section('page-title', __('Patient Forms') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-alt text-secondary"></i>
                    {{ __('Patient Forms') }}
                </h1>
                <p class="text-muted mb-0">
                    {{ __('Manage assigned forms for') }}: <strong>{{ $patient->full_name }}</strong>
                </p>
            </div>
            <div>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back to Patient') }}
                </a>
                @if(Auth::user()->canAssignForms())
                <a href="{{ route('patients.forms.create', $patient) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> {{ __('Assign Form') }}
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <form method="GET" action="{{ route('patients.forms.index', $patient) }}" class="row g-2">
                <div class="col-auto">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="assigned" {{ request('status')=='assigned' ? 'selected' : '' }}>{{ __('Assigned') }}</option>
                        <option value="in_progress" {{ request('status')=='in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Assigned Forms Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Assigned Forms') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($forms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                  <tr>
                                    <th>{{ __('Form Template') }}</th>
                                    <th>{{ __('Assigned') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Completed') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($forms as $f)
                                  <tr>
                                    <td>
                                        <strong>{{ $f->template?->name ?? __('(Deleted Template)') }}</strong>
                                        @if($f->notes)
                                            <br><small class="text-muted">{{ Str::limit($f->notes, 80) }}</small>
                                        @endif
                                            @if($f->hasAttachment())
                                                <span class="ms-2 text-muted" title="{{ __('Has attachment') }}"><i class="fas fa-paperclip"></i></span>
                                            @endif
                                            @if($f->hasSnapshotPdf())
                                                <span class="ms-1 text-success" title="{{ __('Stored PDF available') }}"><i class="fas fa-file-pdf"></i></span>
                                            @endif
                                        </td>

                                    <td>
                                        <small class="text-muted">{{ $f->assigned_at?->format('Y-m-d H:i') ?? '-' }}</small>
                                        @if($f->assignedBy)
                                            <br><small class="text-muted">{{ __('by') }} {{ $f->assignedBy->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                          $badge = match($f->status){
                                            'completed' => 'success',
                                            'in_progress' => 'info',
                                            default => 'secondary'
                                          };
                                        @endphp
                                        <span class="badge bg-{{ $badge }}">{{ __(Str::title(str_replace('_',' ', $f->status))) }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $f->completed_at?->format('Y-m-d H:i') ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if(Auth::user()->canFillForms() && $f->status !== 'completed')
                                            <a href="{{ route('patients.forms.fill', [$patient, $f]) }}" class="btn btn-outline-primary" title="{{ __('Fill/Continue') }}">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('patients.forms.show', [$patient, $f]) }}" class="btn btn-outline-info" title="{{ __('View') }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($f->status === 'completed')
                                                @if($f->hasSnapshotPdf())
                                                <a href="{{ route('patients.forms.pdf-snapshot', [$patient, $f]) }}" target="_blank" class="btn btn-outline-success" title="{{ __('Open Stored PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @else
                                                <a href="{{ route('patients.forms.pdf', [$patient, $f]) }}?open=1" target="_blank" class="btn btn-outline-success" title="{{ __('Open PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                @endif
                                            @endif
                                            @if($f->hasAttachment())
                                            <a href="{{ route('patients.forms.attachment', [$patient, $f]) }}" class="btn btn-outline-primary" title="{{ __('Download Attachment') }}">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                            @endif
                                            @if(Auth::user()->canAssignForms())
                                            <form method="POST" action="{{ route('patients.forms.destroy', [$patient, $f]) }}" onsubmit="return confirm('{{ __('Remove this form from the patient?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-outline-danger" type="submit" title="{{ __('Remove') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $forms->withQueryString()->links() }}</div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No forms assigned') }}</h5>
                            <p class="text-muted">{{ __('This patient has no forms yet.') }}</p>
                            @if(Auth::user()->canAssignForms())
                            <a href="{{ route('patients.forms.create', $patient) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> {{ __('Assign First Form') }}
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

