@extends('layouts.app')

@section('page-title', __('Patient Vital Signs') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-stethoscope text-primary"></i>
                        {{ __('Custom Vital Signs') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Manage additional vital signs for') }}: <strong>{{ $patient->full_name }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignVitalSignModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Assign Vital Sign') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ __('Patient ID') }}:</strong><br>
                            {{ $patient->patient_id }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Age') }}:</strong><br>
                            {{ $patient->age }} {{ __('years old') }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Medical Conditions') }}:</strong><br>
                            @if(count($patient->medical_conditions) > 0)
                                {{ implode(', ', $patient->medical_conditions) }}
                            @else
                                <span class="text-muted">{{ __('None assigned') }}</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <strong>{{ __('Active Vital Signs') }}:</strong><br>
                            <span class="badge bg-primary">{{ $assignments->where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Templates -->
    @if($medicalTemplates->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        {{ __('Quick Assignment Templates') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __('Assign multiple vital signs based on medical conditions:') }}</p>
                    <div class="row">
                        @foreach($medicalTemplates->take(6) as $template)
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-info btn-sm w-100" 
                                    onclick="assignFromTemplate({{ $template->id }}, '{{ $template->condition_name }}')">
                                <i class="fas fa-plus me-1"></i>
                                {{ $template->condition_name }}
                                <small class="d-block">{{ $template->vital_signs_count }} {{ __('vital signs') }}</small>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Assigned Vital Signs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Assigned Vital Signs') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Vital Sign') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Medical Condition') }}</th>
                                        <th>{{ __('Assigned Date') }}</th>
                                        <th>{{ __('Assigned By') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                    <tr class="{{ !$assignment->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>{{ $assignment->customVitalSign->name }}</strong>
                                            @if($assignment->customVitalSign->unit)
                                                <small class="text-muted">({{ $assignment->customVitalSign->unit }})</small>
                                            @endif
                                            @if($assignment->customVitalSign->normal_range)
                                                <br><small class="text-muted">Normal: {{ $assignment->customVitalSign->normal_range }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $assignment->customVitalSign->type === 'number' ? 'success' : ($assignment->customVitalSign->type === 'select' ? 'warning' : 'info') }}">
                                                {{ ucfirst($assignment->customVitalSign->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $assignment->medical_condition ?: '-' }}
                                            @if($assignment->reason)
                                                <br><small class="text-muted">{{ Str::limit($assignment->reason, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment->formatted_assigned_date }}
                                            @if($assignment->isRecentAssignment())
                                                <br><small class="text-success">{{ __('Recent') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment->assignedBy->first_name }} {{ $assignment->assignedBy->last_name }}
                                        </td>
                                        <td>
                                            <span class="badge {{ $assignment->status_badge_class }}">
                                                {{ $assignment->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="editAssignment({{ $assignment->id }})" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('patients.vital-signs.toggle', [$patient, $assignment]) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $assignment->is_active ? 'warning' : 'success' }}" 
                                                            title="{{ $assignment->is_active ? __('Deactivate') : __('Activate') }}">
                                                        <i class="fas fa-{{ $assignment->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmRemove({{ $assignment->id }}, '{{ $assignment->customVitalSign->name }}')" 
                                                        title="{{ __('Remove') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-stethoscope fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Vital Signs Assigned') }}</h5>
                            <p class="text-muted">{{ __('This patient has no custom vital signs assigned yet.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignVitalSignModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Assign First Vital Sign') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Vital Sign Modal -->
<div class="modal fade" id="assignVitalSignModal" tabindex="-1" aria-labelledby="assignVitalSignModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignVitalSignModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Assign Vital Sign') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.vital-signs.assign', $patient) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="custom_vital_sign_id" class="form-label">{{ __('Vital Sign') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('custom_vital_sign_id') is-invalid @enderror" 
                                id="custom_vital_sign_id" name="custom_vital_sign_id" required>
                            <option value="">{{ __('Select vital sign...') }}</option>
                            @foreach($availableVitalSigns as $vitalSign)
                                <option value="{{ $vitalSign->id }}" {{ old('custom_vital_sign_id') == $vitalSign->id ? 'selected' : '' }}>
                                    {{ $vitalSign->name }}
                                    @if($vitalSign->unit) ({{ $vitalSign->unit }}) @endif
                                    @if($vitalSign->normal_range) - Normal: {{ $vitalSign->normal_range }} @endif
                                </option>
                            @endforeach
                        </select>
                        @error('custom_vital_sign_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="medical_condition" class="form-label">{{ __('Medical Condition') }}</label>
                        <input type="text" class="form-control @error('medical_condition') is-invalid @enderror" 
                               id="medical_condition" name="medical_condition" value="{{ old('medical_condition') }}" 
                               placeholder="{{ __('e.g., Diabetes, Hypertension') }}">
                        @error('medical_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">{{ __('Reason for Assignment') }}</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" 
                                  id="reason" name="reason" rows="3" 
                                  placeholder="{{ __('Why is this vital sign needed for this patient?') }}">{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Assign Vital Sign') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template Assignment Modal -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">
                    <i class="fas fa-clipboard-list me-2"></i>
                    {{ __('Assign from Template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.vital-signs.assign-template', $patient) }}" method="POST" id="templateForm">
                @csrf
                <input type="hidden" id="template_id" name="template_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Template') }}</label>
                        <div id="templateInfo" class="alert alert-info">
                            <!-- Template info will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="template_reason" class="form-label">{{ __('Reason for Assignment') }}</label>
                        <textarea class="form-control" id="template_reason" name="reason" rows="3" 
                                  placeholder="{{ __('Why are these vital signs needed for this patient?') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Assign Template') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function assignFromTemplate(templateId, templateName) {
    document.getElementById('template_id').value = templateId;
    document.getElementById('templateInfo').innerHTML = `
        <strong>${templateName}</strong><br>
        <small>This will assign all vital signs from this template to the patient.</small>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('templateModal'));
    modal.show();
}

function editAssignment(assignmentId) {
    // For now, just show an alert. You can implement a full edit modal later
    alert('Edit functionality will be implemented in the next update.');
}

function confirmRemove(assignmentId, vitalSignName) {
    if (confirm(`Are you sure you want to remove "${vitalSignName}" from this patient?`)) {
        // Create and submit a form to remove the assignment
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('patients.vital-signs.index', $patient) }}/${assignmentId}`;
        
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
