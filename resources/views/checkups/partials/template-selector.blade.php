@php
    $selectedTemplateId = $selectedTemplateId ?? null;
    $patientTemplates = $patientTemplates ?? collect();
@endphp

<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-light border mb-0">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-clipboard-list text-primary me-1"></i>
                        {{ __('Checkup Form Type') }}
                    </h6>
                    <p class="text-muted mb-0 small">
                        {{ __('Choose the default form or one of the patient\'s assigned checkup templates before saving the visit.') }}
                    </p>
                </div>
                <a href="{{ route('patients.checkup-templates.index', $patient) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-cog me-1"></i>
                    {{ __('Manage Templates') }}
                </a>
            </div>

            <form method="GET" action="{{ route('checkups.create', $patient) }}" class="row g-3 align-items-end">
                <div class="col-lg-8">
                    <label for="template_id" class="form-label">{{ __('Checkup Template') }}</label>
                    <select class="form-select" id="template_id" name="template_id">
                        <option value="">{{ __('Default checkup form') }}</option>
                        @foreach($patientTemplates as $assignment)
                            <option value="{{ $assignment->template_id }}" {{ (string) $selectedTemplateId === (string) $assignment->template_id ? 'selected' : '' }}>
                                {{ $assignment->template->name }}@if($assignment->medical_condition) — {{ $assignment->medical_condition }} @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        {{ __('The default form shows the standard checkup fields. Templates add specialized sections for this patient.') }}
                    </div>
                </div>
                <div class="col-lg-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i>
                        {{ __('Load Form') }}
                    </button>
                    @if($selectedTemplateId)
                        <a href="{{ route('checkups.create', $patient) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-medical me-1"></i>
                            {{ __('Use Default') }}
                        </a>
                    @endif
                </div>
            </form>

            @if($patientTemplates->isEmpty())
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('No custom checkup templates are assigned to this patient yet. The default checkup form is ready to use.') }}
                </div>
            @elseif(isset($template) && $template)
                <div class="alert alert-success mt-3 mb-0">
                    <div class="fw-semibold">{{ __('Selected template:') }} {{ $template->name }}</div>
                    <div class="small text-muted">
                        {{ $template->description ?: __('This template adds custom sections to the standard checkup form.') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>