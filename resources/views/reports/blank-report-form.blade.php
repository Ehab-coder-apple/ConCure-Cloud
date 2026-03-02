@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt text-primary"></i>
                        {{ __('Create Blank Report') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Fill in the details for the medical report') }}</p>
                </div>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Patient') }}
                </a>
            </div>

            <!-- Patient Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('Patient ID') }}:</strong> {{ $patient->patient_id }}</p>
                            <p class="mb-2"><strong>{{ __('Name') }}:</strong> {{ $patient->full_name }}</p>
                            <p class="mb-2"><strong>{{ __('Gender') }}:</strong> {{ ucfirst($patient->gender ?? 'N/A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('Date of Birth') }}:</strong> 
                                @if($patient->date_of_birth)
                                    {{ $patient->date_of_birth->format('F d, Y') }} ({{ $patient->date_of_birth->age }} years)
                                @else
                                    N/A
                                @endif
                            </p>
                            <p class="mb-2"><strong>{{ __('Phone') }}:</strong> {{ $patient->phone ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>{{ __('Address') }}:</strong> {{ $patient->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Form -->
            <form action="{{ route('patient.blank-report.preview', $patient) }}" method="POST" id="blankReportForm">
                @csrf
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-medical me-2"></i>
                            {{ __('Report Details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Report Title -->
                        <div class="mb-4">
                            <label for="report_title" class="form-label">
                                <i class="fas fa-heading me-1"></i>
                                {{ __('Report Title') }}
                            </label>
                            <input type="text"
                                   class="form-control @error('report_title') is-invalid @enderror"
                                   id="report_title"
                                   name="report_title"
                                   placeholder="{{ __('e.g., Sick Leave Certificate, Medical Fitness Report, etc.') }}"
                                   value="{{ old('report_title', request('report_title', 'Medical Report')) }}">
                            @error('report_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Enter a descriptive title for this report') }}
                            </small>
                        </div>

                        <!-- Notes/Special Information -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-notes-medical me-1"></i>
                                {{ __('Notes / Special Information') }} <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="15"
                                      placeholder="{{ __('Enter medical notes, observations, recommendations, sick leave details, or any special information...') }}"
                                      required>{{ old('notes', request('notes')) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('This information will be included in the PDF report') }}
                            </small>
                        </div>

                        <!-- Quick Templates -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-magic me-1"></i>
                                {{ __('Quick Templates') }}
                            </label>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                {{-- Built-in templates --}}
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('sick_leave')">
                                    <i class="fas fa-bed me-1"></i>
                                    {{ __('Sick Leave') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('fitness')">
                                    <i class="fas fa-heartbeat me-1"></i>
                                    {{ __('Medical Fitness') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('follow_up')">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    {{ __('Follow-up') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('referral')">
                                    <i class="fas fa-share me-1"></i>
                                    {{ __('Referral') }}
                                </button>

                                {{-- Custom templates from DB --}}
                                @foreach($customTemplates as $tpl)
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="insertCustomTemplate({{ $tpl->id }})">
                                    <i class="{{ $tpl->icon ?? 'fas fa-file-alt' }} me-1"></i>
                                    {{ $tpl->name }}
                                </button>
                                @endforeach

                                {{-- Divider + management buttons --}}
                                <span class="border-start ps-2 ms-1"></span>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="openSaveTemplateModal()">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Save as Template') }}
                                </button>
                                @if($customTemplates->count() > 0)
                                <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#manageTemplatesModal">
                                    <i class="fas fa-cog me-1"></i>
                                    {{ __('Manage') }}
                                </button>
                                @endif
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                {{ __('Click a template to insert pre-formatted text. Use "Save as Template" to create your own.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Preview the report before saving to patient files') }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ __('Preview Report') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Save Template Modal --}}
<div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-labelledby="saveTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="saveTemplateModalLabel">
                    <i class="fas fa-save me-2"></i>{{ __('Save as Template') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="tpl_edit_id" value="">
                <div class="mb-3">
                    <label for="tpl_name" class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tpl_name" placeholder="{{ __('e.g., My Sick Leave') }}" required>
                </div>
                <div class="mb-3">
                    <label for="tpl_title" class="form-label">{{ __('Report Title (auto-filled when used)') }}</label>
                    <input type="text" class="form-control" id="tpl_title" placeholder="{{ __('e.g., Sick Leave Certificate') }}">
                </div>
                <div class="mb-3">
                    <label for="tpl_icon" class="form-label">{{ __('Icon') }}</label>
                    <select class="form-select" id="tpl_icon">
                        @foreach($templateIcons as $iconClass => $iconLabel)
                        <option value="{{ $iconClass }}">{{ $iconLabel }} ({{ $iconClass }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tpl_content" class="form-label">{{ __('Template Content') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="tpl_content" rows="10" required></textarea>
                    <small class="form-text text-muted">{{ __('This will be inserted into the Notes field when you use the template.') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" onclick="saveTemplate()">
                    <i class="fas fa-save me-1"></i>{{ __('Save Template') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Manage Templates Modal --}}
<div class="modal fade" id="manageTemplatesModal" tabindex="-1" aria-labelledby="manageTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="manageTemplatesModalLabel">
                    <i class="fas fa-cog me-2"></i>{{ __('Manage My Templates') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($customTemplates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Icon') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Report Title') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customTemplates as $tpl)
                            <tr id="tpl-row-{{ $tpl->id }}">
                                <td><i class="{{ $tpl->icon ?? 'fas fa-file-alt' }} fa-lg text-primary"></i></td>
                                <td>{{ $tpl->name }}</td>
                                <td>{{ $tpl->title ?? '-' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editTemplate({{ $tpl->id }}, {{ json_encode($tpl->name) }}, {{ json_encode($tpl->title) }}, {{ json_encode($tpl->content) }}, {{ json_encode($tpl->icon) }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate({{ $tpl->id }}, {{ json_encode($tpl->name) }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center py-4">{{ __('No custom templates yet.') }}</p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
// Custom templates data
const customTemplatesData = @json($customTemplates->keyBy('id'));

// Insert built-in template
function insertTemplate(type) {
    const notesField = document.getElementById('notes');
    const titleField = document.getElementById('report_title');
    let template = '';
    let title = '';
    
    switch(type) {
        case 'sick_leave':
            title = 'Sick Leave Certificate';
            template = `SICK LEAVE CERTIFICATE

This is to certify that {{ $patient->full_name }} has been examined and found to be suffering from a medical condition that requires rest and treatment.

Diagnosis: [Enter diagnosis here]

Recommended Period of Rest: [Enter number of days] days
From: [Start date] To: [End date]

The patient is advised to:
- Take complete rest
- Follow prescribed medication
- Avoid strenuous activities
- Follow up as needed

This certificate is issued upon the patient's request for official purposes.`;
            break;

        case 'fitness':
            title = 'Medical Fitness Certificate';
            template = `MEDICAL FITNESS CERTIFICATE

This is to certify that {{ $patient->full_name }} has been examined and found to be:

☐ Medically fit for work/study/travel
☐ Fit with restrictions (specify below)
☐ Temporarily unfit (specify duration)

Physical Examination Findings:
- General condition: [Normal/Specify]
- Blood pressure: [Value]
- Heart rate: [Value]
- Other relevant findings: [Specify]

Remarks: [Any additional comments]

This certificate is valid for: [Specify duration]`;
            break;

        case 'follow_up':
            title = 'Follow-up Recommendation';
            template = `FOLLOW-UP RECOMMENDATION

Patient: {{ $patient->full_name }}
Date of Visit: {{ now()->format('F d, Y') }}

Current Condition:
[Describe current medical condition]

Treatment Provided:
[List treatments, medications, or procedures]

Follow-up Instructions:
- Next appointment: [Date]
- Medications to continue: [List medications]
- Lifestyle modifications: [Specify]
- Warning signs to watch for: [Specify]

Additional Notes:
[Any other relevant information]`;
            break;

        case 'referral':
            title = 'Medical Referral';
            template = `MEDICAL REFERRAL

Patient: {{ $patient->full_name }}
Referred to: [Specialist name/department]

Reason for Referral:
[Describe the medical condition requiring specialist consultation]

Relevant Medical History:
[Brief summary of relevant medical history]

Current Medications:
[List current medications]

Investigations Done:
[List any tests or examinations already performed]

Specific Questions/Concerns:
[What you would like the specialist to address]

Thank you for your consultation.`;
            break;
    }

    if (template) {
        notesField.value = template;
        titleField.value = title;
    }
}

// Insert custom template from DB
function insertCustomTemplate(id) {
    const tpl = customTemplatesData[id];
    if (!tpl) return;
    document.getElementById('notes').value = tpl.content;
    if (tpl.title) document.getElementById('report_title').value = tpl.title;
}

// Open save-template modal (pre-fill from current notes)
function openSaveTemplateModal() {
    document.getElementById('tpl_edit_id').value = '';
    document.getElementById('tpl_name').value = '';
    document.getElementById('tpl_title').value = document.getElementById('report_title').value || '';
    document.getElementById('tpl_content').value = document.getElementById('notes').value || '';
    document.getElementById('tpl_icon').value = 'fas fa-file-alt';
    document.getElementById('saveTemplateModalLabel').innerHTML = '<i class="fas fa-save me-2"></i>{{ __("Save as Template") }}';
    new bootstrap.Modal(document.getElementById('saveTemplateModal')).show();
}

// Edit existing template
function editTemplate(id, name, title, content, icon) {
    bootstrap.Modal.getInstance(document.getElementById('manageTemplatesModal'))?.hide();
    setTimeout(() => {
        document.getElementById('tpl_edit_id').value = id;
        document.getElementById('tpl_name').value = name || '';
        document.getElementById('tpl_title').value = title || '';
        document.getElementById('tpl_content').value = content || '';
        document.getElementById('tpl_icon').value = icon || 'fas fa-file-alt';
        document.getElementById('saveTemplateModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>{{ __("Edit Template") }}';
        new bootstrap.Modal(document.getElementById('saveTemplateModal')).show();
    }, 400);
}

// Save or update template via AJAX
function saveTemplate() {
    const editId = document.getElementById('tpl_edit_id').value;
    const name = document.getElementById('tpl_name').value.trim();
    const title = document.getElementById('tpl_title').value.trim();
    const content = document.getElementById('tpl_content').value.trim();
    const icon = document.getElementById('tpl_icon').value;

    if (!name || !content) {
        alert('{{ __("Name and Content are required.") }}');
        return;
    }

    const url = editId
        ? '{{ url("/report-templates") }}/' + editId
        : '{{ route("report-templates.store") }}';
    const method = editId ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ name, title, content, icon }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('saveTemplateModal'))?.hide();
            location.reload();
        } else {
            alert(data.message || '{{ __("Failed to save template.") }}');
        }
    })
    .catch(() => alert('{{ __("Failed to save template.") }}'));
}

// Delete template
function deleteTemplate(id, name) {
    if (!confirm('{{ __("Are you sure you want to delete template:") }} ' + name + '?')) return;

    fetch('{{ url("/report-templates") }}/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '{{ __("Failed to delete template.") }}');
        }
    })
    .catch(() => alert('{{ __("Failed to delete template.") }}'));
}
</script>
@endsection

