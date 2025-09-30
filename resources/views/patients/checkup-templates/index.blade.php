@extends('layouts.app')

@section('page-title', __('Patient Checkup Templates') . ' - ' . $patient->full_name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-clipboard-list text-primary"></i>
                        {{ __('Custom Checkup Templates') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Manage specialized checkup forms for') }}: <strong>{{ $patient->full_name }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patient') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignTemplateModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Assign Template') }}
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
                            <strong>{{ __('Active Templates') }}:</strong><br>
                            <span class="badge bg-primary">{{ $assignments->where('is_active', true)->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Templates -->
    @if($recommendedTemplates->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        {{ __('Recommended Templates') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __('Based on patient\'s medical conditions, these templates are recommended:') }}</p>
                    <div class="row">
                        @foreach($recommendedTemplates->take(6) as $template)
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-success btn-sm w-100"
                                    onclick="assignRecommendedTemplate({{ $template->id }}, '{{ $template->name }}')">
                                <i class="fas fa-plus me-1"></i>
                                {{ $template->name }}
                                <small class="d-block">{{ $template->medical_condition }}</small>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Assigned Templates -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Assigned Checkup Templates') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($assignments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Template') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Medical Condition') }}</th>
                                        <th>{{ __('Fields') }}</th>
                                        <th>{{ __('Assigned Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Usage') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $assignment)
                                    <tr class="{{ !$assignment->is_active ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>{{ $assignment->template->name }}</strong>
                                            @if($assignment->template->description)
                                                <br><small class="text-muted">{{ Str::limit($assignment->template->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ ucfirst(str_replace('_', ' ', $assignment->template->checkup_type)) }}
                                            </span>
                                            @if($assignment->template->specialty)
                                                <br><small class="text-muted">{{ $assignment->template->specialty }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $assignment->medical_condition ?: '-' }}
                                            @if($assignment->reason)
                                                <br><small class="text-muted">{{ Str::limit($assignment->reason, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $assignment->template->fields_count }} {{ __('fields') }}
                                            </span>
                                            <br><small class="text-muted">{{ $assignment->template->sections_count }} {{ __('sections') }}</small>
                                        </td>
                                        <td>
                                            {{ $assignment->formatted_assigned_date }}
                                            @if($assignment->isRecentAssignment())
                                                <br><small class="text-success">{{ __('Recent') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $assignment->status_badge_class }}">
                                                {{ $assignment->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $stats = $assignment->usage_stats; @endphp
                                            <small class="text-muted">
                                                {{ $stats['total_checkups'] }} {{ __('checkups') }}
                                                @if($stats['last_used'])
                                                    <br>{{ __('Last:') }} {{ $stats['last_used'] }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info"
                                                        onclick="previewTemplate({{ $assignment->template->id }})" title="{{ __('Preview') }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary"
                                                        onclick="editAssignment({{ $assignment->id }}, '{{ $assignment->medical_condition }}', '{{ $assignment->reason }}')"
                                                        title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('patients.checkup-templates.toggle', [$patient, $assignment]) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $assignment->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $assignment->is_active ? __('Deactivate') : __('Activate') }}">
                                                        <i class="fas fa-{{ $assignment->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="confirmRemove({{ $assignment->id }}, '{{ $assignment->template->name }}')"
                                                        title="{{ __('Delete') }}">
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
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Checkup Templates Assigned') }}</h5>
                            <p class="text-muted">{{ __('This patient has no custom checkup templates assigned yet.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignTemplateModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Assign First Template') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Template Modal -->
<div class="modal fade" id="assignTemplateModal" tabindex="-1" aria-labelledby="assignTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTemplateModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Assign Checkup Template') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.checkup-templates.assign', $patient) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="template_id" class="form-label">{{ __('Checkup Template') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('template_id') is-invalid @enderror"
                                id="template_id" name="template_id" required onchange="updateTemplatePreview()">
                            <option value="">{{ __('Select template...') }}</option>
                            @foreach($availableTemplates as $template)
                                <option value="{{ $template->id }}"
                                        data-description="{{ $template->description }}"
                                        data-condition="{{ $template->medical_condition }}"
                                        data-specialty="{{ $template->specialty }}"
                                        data-fields="{{ $template->fields_count }}"
                                        data-sections="{{ $template->sections_count }}"
                                        {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                    @if($template->medical_condition) - {{ $template->medical_condition }} @endif
                                </option>
                            @endforeach
                        </select>
                        <div id="templateEmptyHint" class="form-text mt-1" style="display:none;">
                            {{ __('No templates found.') }}
                            <a href="{{ route('admin.checkup-templates.create') }}" target="_blank">
                                {{ __('Create a new checkup template') }}
                            </a>
                        </div>

                        @error('template_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="templatePreview" class="mb-3" style="display: none;">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">{{ __('Template Preview') }}</h6>
                            <div id="previewContent"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="medical_condition" class="form-label">{{ __('Medical Condition') }}</label>
                        <input type="text" class="form-control @error('medical_condition') is-invalid @enderror"
                               id="medical_condition" name="medical_condition" value="{{ old('medical_condition') }}"
                               placeholder="{{ __('e.g., Type 2 Diabetes, Hypertension') }}">
                        @error('medical_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">{{ __('Reason for Assignment') }}</label>
                        <textarea class="form-control @error('reason') is-invalid @enderror"
                                  id="reason" name="reason" rows="3"
                                  placeholder="{{ __('Why is this checkup template needed for this patient?') }}">{{ old('reason') }}</textarea>
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
                        {{ __('Assign Template') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1" aria-labelledby="editAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAssignmentModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('Edit Template Assignment') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editAssignmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_medical_condition" class="form-label">{{ __('Medical Condition') }}</label>
                        <input type="text" class="form-control" id="edit_medical_condition" name="medical_condition"
                               placeholder="{{ __('e.g., Type 2 Diabetes, Hypertension') }}">
                    </div>

                    <div class="mb-3">
                        <label for="edit_reason" class="form-label">{{ __('Reason for Assignment') }}</label>
                        <textarea class="form-control" id="edit_reason" name="reason" rows="3"
                                  placeholder="{{ __('Why is this checkup template needed for this patient?') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update Assignment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function assignRecommendedTemplate(templateId, templateName) {
    document.getElementById('template_id').value = templateId;
    updateTemplatePreview();

    const modal = new bootstrap.Modal(document.getElementById('assignTemplateModal'));
    modal.show();
}

function editAssignment(assignmentId, medicalCondition, reason) {
    // Set form action URL
    const form = document.getElementById('editAssignmentForm');
    form.action = `/patients/{{ $patient->id }}/checkup-templates/${assignmentId}`;

    // Populate form fields
    document.getElementById('edit_medical_condition').value = medicalCondition || '';
    document.getElementById('edit_reason').value = reason || '';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editAssignmentModal'));
    modal.show();
}

function updateTemplatePreview() {
    const select = document.getElementById('template_id');
    const preview = document.getElementById('templatePreview');
    const content = document.getElementById('previewContent');
    const conditionInput = document.getElementById('medical_condition');

    if (select.value) {
        const option = select.selectedOptions[0];
        const description = option.dataset.description;
        const condition = option.dataset.condition;
        const specialty = option.dataset.specialty;
        const fields = option.dataset.fields;
        const sections = option.dataset.sections;

        let metaHtml = '';
        if (specialty) {
            metaHtml += `<small class="text-muted">Specialty: ${specialty}</small>`;
        }
        // Only show counts if they are present (server-rendered options have them; AJAX-loaded may not)
        if (fields !== undefined && sections !== undefined && fields !== '' && sections !== '') {
            metaHtml += `${specialty ? '<br>' : ''}<small class=\"text-muted\">${fields} fields in ${sections} sections</small>`;
        }

        content.innerHTML = `
            <strong>${option.text}</strong><br>
            ${description ? description + '<br>' : ''}
            ${metaHtml}
        `;

        if (condition && !conditionInput.value) {
            conditionInput.value = condition;

        }

        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function previewTemplate(templateId) {
    // Fetch template preview data
    fetch(`/patients/{{ $patient->id }}/checkup-templates/${templateId}/preview`)
        .then(response => response.json())
        .then(data => {
            showTemplatePreview(data);
        })
        .catch(error => {
            console.error('Error fetching template preview:', error);
            alert('Error loading template preview. Please try again.');
        });
}

function showTemplatePreview(templateData) {
    const template = templateData.template;
    const sections = templateData.form_sections || {};

    let previewHtml = `
        <div class="mb-3">
            <h5 class="text-primary">${template.name}</h5>
            <p class="text-muted">${template.description || 'No description available'}</p>
            <div class="row mb-3">
                <div class="col-md-6">
                    <small><strong>Medical Condition:</strong> ${template.medical_condition || 'N/A'}</small>
                </div>
                <div class="col-md-6">
                    <small><strong>Specialty:</strong> ${template.specialty || 'N/A'}</small>
                </div>
            </div>
        </div>
        <hr>
        <div class="alert alert-info">
            <small><i class="fas fa-info-circle me-1"></i>This is how the template will appear in checkup forms:</small>
        </div>
    `;

    if (Object.keys(sections).length > 0) {
        Object.keys(sections).forEach(sectionKey => {
            const section = sections[sectionKey];
            previewHtml += `
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2">
                        <i class="fas fa-folder me-2"></i>${section.title || sectionKey}
                    </h6>
                    <div class="row">
            `;

            if (section.fields && Object.keys(section.fields).length > 0) {
                Object.keys(section.fields).forEach(fieldKey => {
                    const field = section.fields[fieldKey];
                    previewHtml += `

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                ${field.label || fieldKey}
                                ${field.required ? '<span class="text-danger">*</span>' : ''}
                            </label>
                    `;

                    switch (field.type) {
                        case 'select':
                            previewHtml += '<select class="form-select" disabled><option>Select...</option>';
                            if (field.options && Array.isArray(field.options)) {
                                field.options.forEach(option => {
                                    previewHtml += `<option>${option}</option>`;
                                });
                            }
                            previewHtml += '</select>';
                            break;
                        case 'textarea':
                            previewHtml += '<textarea class="form-control" rows="3" disabled placeholder="Enter text..."></textarea>';
                            break;
                        case 'checkbox':
                            previewHtml += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" disabled>
                                    <label class="form-check-label">${field.label || fieldKey}</label>
                                </div>
                            `;
                            break;
                        case 'date':
                            previewHtml += '<input type="date" class="form-control" disabled>';
                            break;
                        case 'time':
                            previewHtml += '<input type="time" class="form-control" disabled>';
                            break;
                        case 'number':
                            previewHtml += `<input type="number" class="form-control" disabled placeholder="Enter number..."${field.min ? ` min="${field.min}"` : ''}${field.max ? ` max="${field.max}"` : ''}>`;
                            break;
                        default:
                            previewHtml += '<input type="text" class="form-control" disabled placeholder="Enter text...">';
                    }

                    previewHtml += '</div>';
                });
            } else {
                previewHtml += '<div class="col-12"><p class="text-muted">No fields defined in this section</p></div>';
            }

            previewHtml += '</div></div>';
        });
    } else {
        previewHtml += '<div class="text-center py-4"><p class="text-muted">No form structure defined for this template</p></div>';
    }

    // Show in modal
    document.getElementById('previewModalLabel').textContent = `Template Preview: ${template.name}`;
    document.getElementById('previewModalBody').innerHTML = previewHtml;

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

function confirmRemove(assignmentId, templateName) {
    if (confirm(`Are you sure you want to remove "${templateName}" from this patient?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('patients.checkup-templates.index', $patient) }}/${assignmentId}`;

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
<script>
// Load templates dynamically when the Assign modal opens (ensures up-to-date list)
(function(){
  const modalEl = document.getElementById('assignTemplateModal');
  if (!modalEl) return;

  let templatesLoaded = false;

  modalEl.addEventListener('shown.bs.modal', function(){
    const select = document.getElementById('template_id');
    if (!select) return;

    // Only load once per page load, unless explicitly refreshed
    if (templatesLoaded) return;

    // Show loading option
    select.innerHTML = `<option value="">{{ __('Loading templates...') }}</option>`;

    fetch(`/patients/{{ $patient->id }}/checkup-templates/available`)
      .then(r => {
        if (!r.ok) {
          console.error('HTTP error:', r.status, r.statusText);
          throw new Error('Failed to load templates: ' + r.statusText);
        }
        return r.json();
      })
      .then(list => {
        console.log('Templates loaded:', list);

        // Rebuild options list
        const frag = document.createDocumentFragment();
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = `{{ __('Select template...') }}`;
        frag.appendChild(placeholder);

        const emptyHint = document.getElementById('templateEmptyHint');
        if (Array.isArray(list) && list.length > 0) {
          list.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name + (t.medical_condition ? ` - ${t.medical_condition}` : '');
            opt.dataset.description = t.description || '';
            opt.dataset.condition = t.medical_condition || '';
            opt.dataset.specialty = t.specialty || '';
            if (typeof t.fields_count !== 'undefined') opt.dataset.fields = t.fields_count;
            if (typeof t.sections_count !== 'undefined') opt.dataset.sections = t.sections_count;
            frag.appendChild(opt);
          });
          if (emptyHint) emptyHint.style.display = 'none';
          templatesLoaded = true;
        } else {
          // No templates available
          const noTemplatesOpt = document.createElement('option');
          noTemplatesOpt.value = '';
          noTemplatesOpt.textContent = `{{ __('No templates found.') }} `;
          noTemplatesOpt.disabled = true;
          frag.appendChild(noTemplatesOpt);

          if (emptyHint) emptyHint.style.display = '';
        }

        // Replace select contents
        select.innerHTML = '';
        select.appendChild(frag);
      })
      .catch(err => {
        console.error('Error loading available templates:', err);
        // Graceful fallback - keep server-rendered options if they exist
        const serverOptions = select.querySelectorAll('option[value!=""]');
        if (serverOptions.length === 0) {
          select.innerHTML = `<option value="">{{ __('No templates found.') }} <a href="{{ route('admin.checkup-templates.create') }}">{{ __('Create a new checkup template') }}</a></option>`;
        }
        const emptyHint = document.getElementById('templateEmptyHint');
        if (emptyHint) emptyHint.style.display = '';
      });
  });
})();
</script>

<!-- Template Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
