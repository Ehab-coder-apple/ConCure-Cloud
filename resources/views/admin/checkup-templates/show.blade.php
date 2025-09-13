@extends('layouts.app')

@section('page-title', $template->name . ' - ' . __('Checkup Template Details'))

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        {{ $template->name }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Checkup Template Details') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.checkup-templates.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Templates') }}
                    </a>
                    <a href="{{ route('admin.checkup-templates.edit', $template) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit Template') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Template Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Name') }}:</strong></td>
                                    <td>{{ $template->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Description') }}:</strong></td>
                                    <td>{{ $template->description ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Medical Condition') }}:</strong></td>
                                    <td>{{ $template->medical_condition ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Specialty') }}:</strong></td>
                                    <td>{{ $template->specialty ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>{{ __('Checkup Type') }}:</strong></td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ ucfirst(str_replace('_', ' ', $template->checkup_type)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Status') }}:</strong></td>
                                    <td>
                                        <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $template->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                        @if($template->is_default)
                                            <span class="badge bg-primary ms-1">{{ __('Default') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Created By') }}:</strong></td>
                                    <td>{{ $template->creator->first_name }} {{ $template->creator->last_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ __('Created Date') }}:</strong></td>
                                    <td>{{ $template->created_at->format('M d, Y g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        {{ __('Usage Statistics') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $usageStats['total_assignments'] }}</h3>
                                <p class="text-muted mb-0">{{ __('Total Assignments') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success">{{ $usageStats['active_assignments'] }}</h3>
                                <p class="text-muted mb-0">{{ __('Active Assignments') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">{{ $usageStats['total_checkups'] }}</h3>
                                <p class="text-muted mb-0">{{ __('Total Checkups') }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning">{{ $usageStats['usage_rate'] }}%</h3>
                                <p class="text-muted mb-0">{{ __('Usage Rate') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Structure -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap me-2"></i>
                        {{ __('Form Structure') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($template->form_sections)
                        @foreach($template->form_sections as $sectionKey => $section)
                        <div class="mb-4">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-folder me-2"></i>
                                {{ $section['title'] ?? $sectionKey }}
                            </h6>
                            
                            @if(isset($section['fields']) && is_array($section['fields']))
                                <div class="row">
                                    @foreach($section['fields'] as $fieldKey => $field)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-light">
                                            <div class="card-body p-3">
                                                <h6 class="card-title mb-2">
                                                    {{ $field['label'] ?? $fieldKey }}
                                                    @if(isset($field['required']) && $field['required'])
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </h6>
                                                <p class="card-text">
                                                    <span class="badge bg-secondary">{{ ucfirst($field['type'] ?? 'text') }}</span>
                                                    @if(isset($field['options']) && is_array($field['options']))
                                                        <br><small class="text-muted">{{ count($field['options']) }} options</small>
                                                    @endif
                                                    @if(isset($field['min']) || isset($field['max']))
                                                        <br><small class="text-muted">
                                                            Range: {{ $field['min'] ?? 'N/A' }} - {{ $field['max'] ?? 'N/A' }}
                                                        </small>
                                                    @endif
                                                </p>
                                                @if(isset($field['help_text']))
                                                    <small class="text-muted">{{ $field['help_text'] }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">{{ __('No fields defined in this section') }}</p>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                            <h5 class="text-muted">{{ __('No Form Structure Defined') }}</h5>
                            <p class="text-muted">{{ __('This template does not have any form sections or fields configured.') }}</p>
                            <a href="{{ route('admin.checkup-templates.edit', $template) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>
                                {{ __('Configure Form Structure') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Assignments -->
    @if($template->patientAssignments->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        {{ __('Patient Assignments') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Patient') }}</th>
                                    <th>{{ __('Medical Condition') }}</th>
                                    <th>{{ __('Assigned Date') }}</th>
                                    <th>{{ __('Assigned By') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($template->patientAssignments->take(10) as $assignment)
                                <tr>
                                    <td>
                                        <a href="{{ route('patients.show', $assignment->patient) }}" class="text-decoration-none">
                                            {{ $assignment->patient->full_name }}
                                        </a>
                                        <br><small class="text-muted">ID: {{ $assignment->patient->patient_id }}</small>
                                    </td>
                                    <td>{{ $assignment->medical_condition ?: '-' }}</td>
                                    <td>{{ $assignment->assigned_at->format('M d, Y') }}</td>
                                    <td>{{ $assignment->assignedBy->first_name }} {{ $assignment->assignedBy->last_name }}</td>
                                    <td>
                                        <span class="badge {{ $assignment->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $assignment->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($template->patientAssignments->count() > 10)
                        <div class="text-center mt-3">
                            <small class="text-muted">{{ __('Showing 10 of') }} {{ $template->patientAssignments->count() }} {{ __('assignments') }}</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        {{ __('Template Actions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="btn-group me-2" role="group">
                        <a href="{{ route('admin.checkup-templates.edit', $template) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Template') }}
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="cloneTemplate()">
                            <i class="fas fa-copy me-1"></i>
                            {{ __('Clone Template') }}
                        </button>
                    </div>
                    
                    <div class="btn-group me-2" role="group">
                        <form action="{{ route('admin.checkup-templates.toggle-status', $template) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-{{ $template->is_active ? 'warning' : 'success' }}">
                                <i class="fas fa-{{ $template->is_active ? 'pause' : 'play' }} me-1"></i>
                                {{ $template->is_active ? __('Deactivate') : __('Activate') }}
                            </button>
                        </form>
                    </div>

                    @if($template->patientAssignments->count() === 0 && $template->checkups->count() === 0)
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                            <i class="fas fa-trash me-1"></i>
                            {{ __('Delete Template') }}
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clone Modal -->
<div class="modal fade" id="cloneModal" tabindex="-1" aria-labelledby="cloneModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cloneModalLabel">
                    <i class="fas fa-copy me-2"></i>
                    {{ __('Clone Template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.checkup-templates.clone', $template) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="clone_name" class="form-label">{{ __('New Template Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="clone_name" name="name" value="{{ $template->name }} (Copy)" required>
                    </div>
                    <div class="alert alert-info">
                        <small>{{ __('This will create a copy of the template that you can customize.') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-copy me-1"></i>
                        {{ __('Clone Template') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cloneTemplate() {
    const modal = new bootstrap.Modal(document.getElementById('cloneModal'));
    modal.show();
}

function confirmDelete() {
    if (confirm('{{ __("Are you sure you want to delete this template? This action cannot be undone.") }}')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.checkup-templates.destroy", $template) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
