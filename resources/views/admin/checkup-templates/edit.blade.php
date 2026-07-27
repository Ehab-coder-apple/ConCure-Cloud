@extends('layouts.app')

@section('page-title', __('Edit Checkup Template') . ' - ' . $template->name)

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        {{ __('Edit Checkup Template') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Modify the custom checkup form') }}: <strong>{{ $template->name }}</strong></p>
                </div>
                <div>
                    <a href="{{ route('admin.checkup-templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Templates') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Templates -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-magic me-2"></i>
                        {{ __('Quick Start Templates') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ __('Start with a pre-built template and customize it:') }}</p>
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="loadTemplate('pre_surgery')">
                                <i class="fas fa-scalpel me-1"></i>
                                {{ __('Pre-Surgery Assessment') }}
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="loadTemplate('diabetes')">
                                <i class="fas fa-tint me-1"></i>
                                {{ __('Diabetes Follow-up') }}
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="loadTemplate('cardiac')">
                                <i class="fas fa-heartbeat me-1"></i>
                                {{ __('Cardiac Assessment') }}
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="loadTemplate('mental_health')">
                                <i class="fas fa-brain me-1"></i>
                                {{ __('Mental Health') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Form -->
    <form action="{{ route('admin.checkup-templates.update', ['template' => $template->id]) }}" method="POST" id="templateForm" onsubmit="return ensureFormConfigOnSubmit()">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Basic Information') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $template->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="checkup_type" class="form-label">{{ __('Checkup Type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('checkup_type') is-invalid @enderror"
                                        id="checkup_type" name="checkup_type" required>
                                    <option value="">{{ __('Select type...') }}</option>
                                    @foreach($checkupTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('checkup_type', $template->checkup_type) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('checkup_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="medical_condition" class="form-label">{{ __('Medical Condition') }}</label>
                                <input type="text" class="form-control @error('medical_condition') is-invalid @enderror"
                                       id="medical_condition" name="medical_condition" value="{{ old('medical_condition', $template->medical_condition) }}"
                                       placeholder="{{ __('e.g., Diabetes, Hypertension, Pre-Surgery') }}">
                                @error('medical_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="specialty" class="form-label">{{ __('Medical Specialty') }}</label>
                                <input type="text" class="form-control @error('specialty') is-invalid @enderror"
                                       id="specialty" name="specialty" value="{{ old('specialty', $template->specialty) }}"
                                       placeholder="{{ __('e.g., Cardiology, Endocrinology, Surgery') }}">
                                @error('specialty')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="{{ __('Describe the purpose and use of this template') }}">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', $template->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                {{ __('Set as default template for this condition') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Builder -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tools me-2"></i>
                            {{ __('Form Builder') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">{{ __('Form Builder Instructions') }}</h6>
                            <p class="mb-2">{{ __('Create sections and fields for your custom checkup form:') }}</p>
                            <ul class="mb-0">
                                <li>{{ __('Add sections to organize related fields') }}</li>
                                <li>{{ __('Add fields within each section') }}</li>
                                <li>{{ __('Configure field types, validation, and options') }}</li>
                                <li>{{ __('Use the preview to see how the form will look') }}</li>
                            </ul>
                            <div class="mt-3 pt-3 border-top">
                                <strong>{{ __('Built-in visit history fields:') }}</strong>
                                {{ __('Chief Complaint, Diagnosis, and Clinical Examination are included automatically in every template.') }}
                            </div>
                        </div>

                        <div id="formBuilder">
                            <!-- Form sections will be added here dynamically -->
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" onclick="addSection()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Section') }}
                            </button>
                        </div>

                        <!-- Hidden input to store form configuration -->
                        @php
                            $oldFormConfig = old('form_config');
                            $formConfigJson = '';
                            if (is_string($oldFormConfig)) {
                                $decoded = json_decode($oldFormConfig, true);
                                if (is_array($decoded) && !empty($decoded['sections'])) {
                                    $formConfigJson = $oldFormConfig; // keep client's JSON if it has sections
                                }
                            } elseif (is_array($oldFormConfig)) {
                                if (!empty($oldFormConfig['sections'])) {
                                    $formConfigJson = json_encode($oldFormConfig, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                                }
                            }
                            if ($formConfigJson === '') {
                                // Debug: Let's see what we have
                                \Log::info('Template Edit - Raw form_config:', ['form_config' => $template->form_config]);
                                \Log::info('Template Edit - Form sections:', ['sections' => $template->form_sections]);
                                \Log::info('Template Edit - Sections count:', ['count' => count($template->form_sections)]);

                                // Fall back to normalized sections accessor
                                $formConfigJson = json_encode(['sections' => $template->form_sections], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                            }
                        @endphp
                        <!-- Debug Output (remove in production) -->
                        @if(config('app.debug'))
                        <div class="alert alert-warning">
                            <strong>DEBUG INFO:</strong><br>
                            Template ID: {{ $template->id }}<br>
                            Sections Count: {{ count($template->form_sections) }}<br>
                            Fields Count: {{ $template->fields_count }}<br>
                            <details>
                                <summary>Raw form_config</summary>
                                <pre style="font-size:10px; max-height:200px; overflow:auto;">{{ json_encode($template->form_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                            <details>
                                <summary>Normalized form_sections</summary>
                                <pre style="font-size:10px; max-height:200px; overflow:auto;">{{ json_encode($template->form_sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        </div>
                        @endif
                        <textarea name="form_config" id="form_config" style="display:none;">{{ $formConfigJson }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-info" onclick="previewTemplate()">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ __('Preview Template') }}
                                </button>
                            </div>
                            <div>
                                <a href="{{ route('admin.checkup-templates.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    {{ __('Update Template') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye me-2"></i>
                    {{ __('Template Preview') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let sectionCounter = 0;
let fieldCounter = 0;
let isLoadingConfig = false; // Flag to prevent updateFormConfig during initialization

// Field types available
const fieldTypes = @json($fieldTypes);

// Default templates
const defaultTemplates = {
    'pre_surgery': {
        name: 'Pre-Surgery Assessment',
        medical_condition: 'Pre-Surgery',
        specialty: 'Surgery',
        checkup_type: 'pre_op',
        description: 'Comprehensive pre-operative evaluation and clearance',
        sections: {
            'surgical_assessment': {
                title: 'Surgical Assessment',
                fields: {
                    'surgical_procedure': { type: 'text', label: 'Planned Procedure', required: true },
                    'surgical_site': { type: 'text', label: 'Surgical Site', required: true },
                    'anesthesia_type': { type: 'select', label: 'Anesthesia Type', options: ['General', 'Regional', 'Local', 'Sedation'] },
                    'surgical_risk': { type: 'select', label: 'Surgical Risk Assessment', options: ['Low', 'Moderate', 'High'] }
                }
            },
            'pre_op_clearance': {
                title: 'Pre-Operative Clearance',
                fields: {
                    'cardiac_clearance': { type: 'checkbox', label: 'Cardiac Clearance Required' },
                    'pulmonary_clearance': { type: 'checkbox', label: 'Pulmonary Clearance Required' },
                    'lab_work_ordered': { type: 'checkbox', label: 'Lab Work Ordered' }
                }
            }
        }
    },
    'diabetes': {
        name: 'Diabetes Follow-up',
        medical_condition: 'Diabetes',
        specialty: 'Endocrinology',
        checkup_type: 'follow_up',
        description: 'Comprehensive diabetes management and monitoring',
        sections: {
            'diabetes_management': {
                title: 'Diabetes Management',
                fields: {
                    'hba1c_level': { type: 'number', label: 'HbA1c Level (%)', min: 4, max: 15, step: 0.1 },
                    'medication_compliance': { type: 'select', label: 'Medication Compliance', options: ['Excellent', 'Good', 'Fair', 'Poor'] },
                    'diet_compliance': { type: 'select', label: 'Diet Compliance', options: ['Excellent', 'Good', 'Fair', 'Poor'] },
                    'exercise_frequency': { type: 'select', label: 'Exercise Frequency', options: ['Daily', '4-6 times/week', '2-3 times/week', '1 time/week', 'Rarely'] }
                }
            }
        }
    },
    'cardiac': {
        name: 'Cardiac Assessment',
        medical_condition: 'Cardiac',
        specialty: 'Cardiology',
        checkup_type: 'follow_up',
        description: 'Comprehensive cardiac evaluation and monitoring',
        sections: {
            'cardiac_assessment': {
                title: 'Cardiac Assessment',
                fields: {
                    'chest_pain': { type: 'select', label: 'Chest Pain', options: ['None', 'Mild', 'Moderate', 'Severe'] },
                    'shortness_of_breath': { type: 'select', label: 'Shortness of Breath', options: ['None', 'On exertion', 'At rest', 'Severe'] },
                    'palpitations': { type: 'checkbox', label: 'Palpitations' },
                    'ankle_swelling': { type: 'checkbox', label: 'Ankle Swelling' }
                }
            }
        }
    },
    'mental_health': {
        name: 'Mental Health Assessment',
        medical_condition: 'Mental Health',
        specialty: 'Psychiatry',
        checkup_type: 'follow_up',
        description: 'Comprehensive mental health evaluation and screening',
        sections: {
            'mood_assessment': {
                title: 'Mood Assessment',
                fields: {
                    'mood_rating': { type: 'select', label: 'Overall Mood (1-10)', options: ['1 - Very Poor', '2 - Poor', '3 - Below Average', '4 - Fair', '5 - Average', '6 - Above Average', '7 - Good', '8 - Very Good', '9 - Excellent', '10 - Outstanding'] },
                    'anxiety_level': { type: 'select', label: 'Anxiety Level', options: ['None', 'Mild', 'Moderate', 'Severe'] },
                    'sleep_quality': { type: 'select', label: 'Sleep Quality', options: ['Excellent', 'Good', 'Fair', 'Poor', 'Very Poor'] }
                }
            }
        }
    }
};

const defaultClinicalSection = @json(\App\Models\CustomCheckupTemplate::defaultClinicalSummarySection());
const defaultClinicalFieldKeys = Object.keys(defaultClinicalSection.fields || {});

function cloneBuilderData(data) {
    return JSON.parse(JSON.stringify(data));
}

function normalizeSections(sections = {}) {
    const clinicalSummary = cloneBuilderData(defaultClinicalSection);
    const normalizedSections = {};

    Object.entries(sections || {}).forEach(([sectionKey, section]) => {
        const normalizedSection = cloneBuilderData(section || {});
        normalizedSection.fields = normalizedSection.fields || {};

        Object.entries(normalizedSection.fields).forEach(([fieldKey, field]) => {
            if (!defaultClinicalFieldKeys.includes(fieldKey)) {
                return;
            }

            clinicalSummary.fields[fieldKey] = {
                ...clinicalSummary.fields[fieldKey],
                ...field,
            };

            delete normalizedSection.fields[fieldKey];
        });

        if (sectionKey === 'clinical_summary' || normalizedSection.title === defaultClinicalSection.title) {
            Object.entries(normalizedSection.fields).forEach(([fieldKey, field]) => {
                clinicalSummary.fields[fieldKey] = field;
            });
            return;
        }

        normalizedSections[sectionKey] = normalizedSection;
    });

    return {
        clinical_summary: clinicalSummary,
        ...normalizedSections,
    };
}

function loadTemplate(templateKey) {
    const template = defaultTemplates[templateKey];
    if (!template) return;

    document.getElementById('name').value = template.name;
    document.getElementById('medical_condition').value = template.medical_condition;
    document.getElementById('specialty').value = template.specialty;
    document.getElementById('checkup_type').value = template.checkup_type;
    document.getElementById('description').value = template.description;

    loadExistingConfig({ sections: template.sections || {} });
    updateFormConfig();
}

function addSection(title = '', fields = {}, options = {}) {
    sectionCounter++;
    const sectionId = 'section_' + sectionCounter;
    const isSystemSection = Boolean(options.isSystemSection);
    const sectionKey = options.sectionKey || title.toLowerCase().replace(/\s+/g, '_');

    const sectionHtml = `
        <div class="card mb-3" id="${sectionId}" data-section-key="${sectionKey}" data-system-section="${isSystemSection ? '1' : '0'}">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-folder me-2"></i>
                        <input type="text" class="form-control d-inline-block" style="width: auto;"
                               placeholder="Section Title" value="${title}" onchange="updateFormConfig()" ${isSystemSection ? 'readonly' : ''}>
                        ${isSystemSection ? '<span class="badge bg-primary-subtle text-primary-emphasis border ms-2">Built-in</span>' : ''}
                    </h6>
                    ${isSystemSection ? '' : `<button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSection('${sectionId}')"><i class="fas fa-trash"></i></button>`}
                </div>
            </div>
            <div class="card-body">
                <div class="section-fields" id="${sectionId}_fields"></div>
                ${isSystemSection ? '<p class="text-muted small mb-0">These fields support visit history and stay with every template.</p>' : `<button type="button" class="btn btn-outline-secondary btn-sm" onclick="addField('${sectionId}')"><i class="fas fa-plus me-1"></i>Add Field</button>`}
            </div>
        </div>
    `;

    document.getElementById('formBuilder').insertAdjacentHTML('beforeend', sectionHtml);

    Object.keys(fields).forEach(fieldKey => {
        addField(sectionId, fields[fieldKey], {
            fieldKey,
            isSystemField: isSystemSection && defaultClinicalFieldKeys.includes(fieldKey),
        });
    });

    updateFormConfig();
}

function removeSection(sectionId) {
    document.getElementById(sectionId).remove();
    updateFormConfig();
}

function addField(sectionId, fieldData = {}, options = {}) {
    fieldCounter++;
    const fieldId = 'field_' + fieldCounter;
    const isSystemField = Boolean(options.isSystemField);
    const fieldKey = options.fieldKey || '';

    const fieldHtml = `
        <div class="border rounded p-3 mb-3" id="${fieldId}" data-field-key="${fieldKey}" data-system-field="${isSystemField ? '1' : '0'}">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Field Label</label>
                    <input type="text" class="form-control" placeholder="Field Label"
                           value="${fieldData.label || ''}" onchange="updateFormConfig()" ${isSystemField ? 'readonly' : ''}>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Field Type</label>
                    <select class="form-select" onchange="updateFieldType(this); updateFormConfig()" ${isSystemField ? 'disabled' : ''}>
                        ${Object.keys(fieldTypes).map(key => `<option value="${key}" ${fieldData.type === key ? 'selected' : ''}>${fieldTypes[key]}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Required</label>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" ${fieldData.required ? 'checked' : ''} onchange="updateFormConfig()" ${isSystemField ? 'disabled' : ''}>
                        <label class="form-check-label">Required Field</label>
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label">Actions</label>
                    ${isSystemField ? '<span class="badge bg-primary-subtle text-primary-emphasis border">Locked</span>' : `<button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeField('${fieldId}')"><i class="fas fa-trash"></i></button>`}
                </div>
            </div>
            <div class="field-options" style="display: none;">
                <label class="form-label">Options (one per line)</label>
                <textarea class="form-control" rows="3" placeholder="Option 1&#10;Option 2&#10;Option 3" onchange="updateFormConfig()" ${isSystemField ? 'readonly' : ''}>${fieldData.options ? fieldData.options.join('\\n') : ''}</textarea>
            </div>
        </div>
    `;

    document.getElementById(sectionId + '_fields').insertAdjacentHTML('beforeend', fieldHtml);

    if (fieldData.type === 'select' || fieldData.type === 'radio') {
        const fieldElement = document.getElementById(fieldId);
        fieldElement.querySelector('.field-options').style.display = 'block';
    }

    updateFormConfig();
}

function removeField(fieldId) {
    document.getElementById(fieldId).remove();
    updateFormConfig();
}

function updateFieldType(selectElement) {
    const fieldContainer = selectElement.closest('.border');
    const optionsDiv = fieldContainer.querySelector('.field-options');
    const fieldType = selectElement.value;

    if (fieldType === 'select' || fieldType === 'radio') {
        optionsDiv.style.display = 'block';
    } else {
        optionsDiv.style.display = 'none';
    }
}

function updateFormConfig() {
    const sections = {};

    document.querySelectorAll('#formBuilder .card').forEach(sectionCard => {
        const sectionTitle = sectionCard.querySelector('input[placeholder="Section Title"]').value;
        if (!sectionTitle) return;

        const sectionKey = sectionCard.dataset.sectionKey || sectionTitle.toLowerCase().replace(/\s+/g, '_');
        sections[sectionKey] = {
            title: sectionTitle,
            fields: {}
        };

        if (sectionCard.dataset.systemSection === '1') {
            sections[sectionKey].is_system = true;
        }

        sectionCard.querySelectorAll('.section-fields .border').forEach(fieldDiv => {
            const labelInput = fieldDiv.querySelector('input[placeholder="Field Label"]');
            const typeSelect = fieldDiv.querySelector('select');
            const requiredCheckbox = fieldDiv.querySelector('input[type="checkbox"]');

            // Guard against null elements
            if (!labelInput || !typeSelect || !requiredCheckbox) {
                console.warn('Skipping field div with missing elements:', fieldDiv);
                return;
            }

            const label = labelInput.value;
            if (!label) return;

            const fieldKey = fieldDiv.dataset.fieldKey || label.toLowerCase().replace(/\s+/g, '_');
            const type = typeSelect.value;
            const required = requiredCheckbox.checked;
            const field = {
                type: type,
                label: label,
                required: required
            };

            if (type === 'select' || type === 'radio') {
                const optionsTextarea = fieldDiv.querySelector('textarea');
                if (optionsTextarea && optionsTextarea.value) {
                    field.options = optionsTextarea.value.split('\n').filter(opt => opt.trim());
                }
            }

            sections[sectionKey].fields[fieldKey] = field;
        });
    });

    document.getElementById('form_config').value = JSON.stringify({ sections: sections });
}

function previewTemplate() {
    updateFormConfig();
    const formConfig = JSON.parse(document.getElementById('form_config').value || '{}');

    let previewHtml = '<div class="alert alert-info">This is how your template will look in checkup forms:</div>';

    Object.keys(formConfig.sections || {}).forEach(sectionKey => {
        const section = formConfig.sections[sectionKey];
        previewHtml += `<h6 class="text-primary border-bottom pb-2">${section.title}</h6>`;
        previewHtml += '<div class="row">';

        Object.keys(section.fields || {}).forEach(fieldKey => {
            const field = section.fields[fieldKey];
            previewHtml += `<div class="col-12 mb-3">`;
            previewHtml += `<label class="form-label">${field.label}${field.required ? ' <span class="text-danger">*</span>' : ''}</label>`;

            switch (field.type) {
                case 'select':
                    previewHtml += '<select class="form-select" disabled><option>Select...</option>';
                    if (field.options) {
                        field.options.forEach(option => {
                            previewHtml += `<option>${option}</option>`;
                        });
                    }
                    previewHtml += '</select>';
                    break;
                case 'textarea':
                    previewHtml += '<textarea class="form-control" rows="3" disabled></textarea>';
                    break;
                case 'checkbox':
                    previewHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" disabled><label class="form-check-label">${field.label}</label></div>`;
                    break;
                default:
                    previewHtml += `<input type="${field.type}" class="form-control" disabled>`;
            }

            previewHtml += '</div>';
        });

        previewHtml += '</div>';
    });

    document.getElementById('previewContent').innerHTML = previewHtml;
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}

function initFormBuilder() {
    let loaded = false;
    const formConfigElement = document.getElementById('form_config');

    if (!formConfigElement) {
        console.error('form_config element not found!');
        return;
    }

    const existingConfig = formConfigElement.value;

    console.log('Initializing Form Builder...');
    console.log('Existing config (raw):', existingConfig);

    if (existingConfig && existingConfig.trim() !== '') {
        try {
            const parsedConfig = JSON.parse(existingConfig);
            console.log('Parsed config:', parsedConfig);

            if (parsedConfig && parsedConfig.sections) {
                console.log('Sections count:', Object.keys(parsedConfig.sections).length);
                loadExistingConfig(parsedConfig);
                loaded = true;
                console.log('Successfully loaded existing configuration');
            } else {
                console.warn('Parsed config has no sections property');
            }
        } catch (e) {
            console.error('Error loading existing config:', e);
            console.error('Config value that failed to parse:', existingConfig);
            console.error('Parse error details:', e.message);
        }
    }

    if (!loaded) {
        console.warn('No existing config found, loading default clinical summary section');
        loadExistingConfig({ sections: {} });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFormBuilder);
} else {
    initFormBuilder();
}

function loadExistingConfig(config) {
    document.getElementById('formBuilder').innerHTML = '';
    sectionCounter = 0;
    fieldCounter = 0;

    const normalizedSections = normalizeSections(config.sections || {});

    Object.keys(normalizedSections).forEach(sectionKey => {
        const section = normalizedSections[sectionKey];
        addSection(section.title, section.fields || {}, {
            sectionKey,
            isSystemSection: sectionKey === 'clinical_summary',
        });
    });
}

function ensureFormConfigOnSubmit() {
    updateFormConfig();

    try {
        const raw = document.getElementById('form_config').value || '{}';
        const cfg = JSON.parse(raw);
        if (!cfg.sections || Object.keys(cfg.sections).length === 0) {
            alert('Please add at least one section and give it a title before updating.');
            return false;
        }
    } catch (e) {
        alert('Form configuration is invalid JSON. Please adjust your sections and try again.');
        return false;
    }

    return true;
}

</script>
@endsection

