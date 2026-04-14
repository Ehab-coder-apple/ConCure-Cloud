@extends('layouts.app')
@section('title', __('Edit Patient'))
@php
    $selectedModules = collect($selectedModuleKeys ?? [])->merge([
        filled(old('dental_oral_hygiene')) || filled(old('dental_smoking_status')) ? 'dental' : null,
        filled(old('pediatric_birth_weight')) || filled(old('pediatric_gestational_age_weeks')) ? 'pediatric' : null,
        filled(old('nutrition_height')) || filled(old('nutrition_weight')) ? 'nutrition' : null,
        filled(old('ent_notes')) ? 'ent' : null,
    ])->filter()->unique()->values();
@endphp
@section('content')
<div class="container-fluid py-3 py-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1"><i class="fas fa-user-edit text-primary me-2"></i>{{ __('Edit Patient') }}</h1><p class="text-muted mb-0">{{ __('Update patient information and manage specialty modules.') }}</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to Patient') }}</a>
            <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="d-inline" onsubmit="return confirm(@json(__('Are you sure?')));">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash-alt me-1"></i>{{ __('Delete Patient') }}</button></form>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <form action="{{ route('patients.update', $patient->id) }}" method="POST" class="needs-validation" novalidate>
                @csrf @method('PUT')
                <input type="hidden" name="_supports_extended_medical_flags" value="1">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 p-lg-4">
                        <div class="alert alert-light border d-flex gap-3 align-items-start mb-4">
                            <i class="fas fa-layer-group text-primary mt-1"></i>
                            <div>
                                <div class="fw-semibold">{{ __('Modular patient creation') }}</div>
                                <div class="text-muted small mb-0">{{ __('Keep intake fast: General for essentials, Medical Overview for shared conditions, and Modules only when needed.') }}</div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs flex-wrap gap-2 border-0 mb-4" id="patient-edit-tabs" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general-pane" type="button" role="tab">{{ __('General') }}</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#medical-pane" type="button" role="tab">{{ __('Medical Overview') }}</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#modules-pane" type="button" role="tab">{{ __('Modules') }}</button></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="general-pane" role="tabpanel">
                                <div class="card bg-light border-0"><div class="card-body"><div class="row g-3">
                                    <div class="col-md-6"><label for="first_name" class="form-label">{{ __('First Name') }} *</label><input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required></div>
                                    <div class="col-md-6"><label for="last_name" class="form-label">{{ __('Last Name') }} *</label><input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required></div>
                                    <div class="col-md-6"><label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label><input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth_for_form) }}"></div>
                                    <div class="col-md-6"><label for="gender" class="form-label">{{ __('Gender') }}</label><select class="form-select" id="gender" name="gender"><option value="">{{ __('Select') }}</option><option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>{{ __('Male') }}</option><option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>{{ __('Female') }}</option><option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option></select></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Phone') }}</label><input type="tel" class="form-control" name="phone" value="{{ old('phone', $patient->phone) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('WhatsApp') }}</label><input type="tel" class="form-control" name="whatsapp_phone" value="{{ old('whatsapp_phone', $patient->whatsapp_phone) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Email') }}</label><input type="email" class="form-control" name="email" value="{{ old('email', $patient->email) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Blood Type') }}</label><select class="form-select" name="blood_type"><option value="">{{ __('Select') }}</option>@foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-','NA'] as $type)<option value="{{ $type }}" {{ old('blood_type', $patient->blood_type) === $type ? 'selected' : '' }}>{{ $type }}</option>@endforeach</select></div>
                                    <div class="col-12"><label class="form-label">{{ __('Address') }}</label><textarea class="form-control" name="address" rows="2">{{ old('address', $patient->address) }}</textarea></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Occupation') }}</label><input type="text" class="form-control" name="job" value="{{ old('job', $patient->job) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Education') }}</label><input type="text" class="form-control" name="education" value="{{ old('education', $patient->education) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Height (cm)') }}</label><input type="number" class="form-control" name="height" value="{{ old('height', $patient->height) }}" min="50" max="300" step="0.1"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Weight (kg)') }}</label><input type="number" class="form-control" name="weight" value="{{ old('weight', $patient->weight) }}" min="1" max="500" step="0.1"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Emergency Contact Name') }}</label><input type="text" class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}"></div>
                                    <div class="col-md-6"><label class="form-label">{{ __('Emergency Contact Phone') }}</label><input type="tel" class="form-control" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}"></div>
                                </div></div></div>
                            </div>
                            <div class="tab-pane fade" id="medical-pane" role="tabpanel">
                                <div class="accordion" id="medical-overview-accordion">
                                    <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                                        <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-shared-history">{{ __('Shared history') }}</button></h2>
                                        <div id="collapse-shared-history" class="accordion-collapse collapse show" data-bs-parent="#medical-overview-accordion">
                                            <div class="accordion-body row g-3">
                                                <div class="col-12"><label for="allergies" class="form-label">{{ __('Allergies') }}</label><textarea class="form-control @error('allergies') is-invalid @enderror" id="allergies" name="allergies" rows="2">{{ old('allergies', $patient->medicalOverview?->allergies) }}</textarea>@error('allergies')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                                <div class="col-12"><label for="chronic_illnesses" class="form-label">{{ __('Chronic Diseases') }}</label><textarea class="form-control @error('chronic_illnesses') is-invalid @enderror" id="chronic_illnesses" name="chronic_illnesses" rows="2">{{ old('chronic_illnesses', $patient->medicalOverview?->chronic_diseases) }}</textarea>@error('chronic_illnesses')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                                <div class="col-12"><label for="current_medications_summary" class="form-label">{{ __('Current Medications') }}</label><textarea class="form-control @error('current_medications_summary') is-invalid @enderror" id="current_medications_summary" name="current_medications_summary" rows="2">{{ old('current_medications_summary', $patient->medicalOverview?->current_medications_summary) }}</textarea>@error('current_medications_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border rounded-3 mb-3 overflow-hidden">
                                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-procedures">{{ __('Procedures & notes') }}</button></h2>
                                        <div id="collapse-procedures" class="accordion-collapse collapse" data-bs-parent="#medical-overview-accordion">
                                            <div class="accordion-body row g-3">
                                                <div class="col-12"><label for="surgeries_history" class="form-label">{{ __('Surgeries') }}</label><textarea class="form-control @error('surgeries_history') is-invalid @enderror" id="surgeries_history" name="surgeries_history" rows="2">{{ old('surgeries_history', $patient->medicalOverview?->surgeries) }}</textarea>@error('surgeries_history')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                                <div class="col-12"><label for="medical_history" class="form-label">{{ __('Medical History') }}</label><textarea class="form-control @error('medical_history') is-invalid @enderror" id="medical_history" name="medical_history" rows="3">{{ old('medical_history', $patient->medicalOverview?->medical_history) }}</textarea>@error('medical_history')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item border rounded-3 overflow-hidden">
                                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-flags">{{ __('Flags') }}</button></h2>
                                        <div id="collapse-flags" class="accordion-collapse collapse" data-bs-parent="#medical-overview-accordion">
                                            <div class="accordion-body">
                                                <div class="row g-3">
                                                    @foreach(($medicalFlags ?? []) as $flagKey => $flagLabel)
                                                        <div class="col-sm-6 col-lg-4">
                                                            <div class="form-check border rounded p-3 h-100">
                                                                <input class="form-check-input" type="checkbox" id="medical_flag_{{ $flagKey }}" name="medical_flags[{{ $flagKey }}]" value="1" @checked(old('medical_flags.'.$flagKey, data_get($patient->medicalOverview?->flags ?? [], $flagKey)))>
                                                                <label class="form-check-label fw-semibold" for="medical_flag_{{ $flagKey }}">{{ __($flagLabel) }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="modules-pane" role="tabpanel">
                                <div class="card bg-light border-0 mb-3"><div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center"><div><h6 class="mb-1">{{ __('Optional modules') }}</h6><p class="text-muted mb-0 small">{{ __('No HPI here. Only add the specialty modules you need, and only their minimal intake fields.') }}</p></div><div class="d-flex flex-column flex-sm-row gap-2"><button type="button" class="btn btn-outline-primary" data-add-module-toggle><i class="fas fa-plus me-1"></i>{{ __('Add Module') }}</button><div class="d-none" id="module-selector-wrap"><select class="form-select" id="module-selector"><option value="">{{ __('Choose module') }}</option>@foreach($moduleDefinitions as $mod)<option value="{{ $mod['key'] }}">{{ $mod['label'] }}</option>@endforeach</select></div></div></div></div>
                                <div class="alert alert-info d-none" id="pediatric-eligibility-alert"><i class="fas fa-info-circle me-1"></i>{{ __('Pediatric is available only for patients younger than 16 years.') }}</div>
                                <div class="row g-3">
                                    @foreach($moduleDefinitions as $module)
                                        @php($isSelected = $selectedModules->contains($module['key']))
                                        <div class="col-12 col-lg-6 module-card {{ $isSelected ? '' : 'd-none' }}" data-module-card="{{ $module['key'] }}">
                                            <div class="card border shadow-sm h-100">
                                                <div class="card-header bg-white d-flex justify-content-between align-items-start gap-3">
                                                    <div><div class="fw-semibold"><i class="{{ $module['icon'] }} text-primary me-2"></i>{{ $module['label'] }}</div><div class="small text-muted">{{ $module['description'] }}</div></div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-remove-module="{{ $module['key'] }}">{{ __('Remove') }}</button>
                                                </div>
                                                <div class="card-body">
                                                    <input type="hidden" name="selected_modules[]" value="{{ $module['key'] }}" class="selected-module-input" {{ $isSelected ? '' : 'disabled' }}>
                                                    @if($module['key'] === 'dental')
                                                        <div class="row g-3"><div class="col-md-6"><label for="dental_oral_hygiene" class="form-label">{{ __('Oral Hygiene') }}</label><select id="dental_oral_hygiene" name="dental_oral_hygiene" class="form-select module-field" {{ $isSelected ? '' : 'disabled' }}><option value="">{{ __('Select status') }}</option>@foreach(($dentalOralHygieneOptions ?? []) as $value => $label)<option value="{{ $value }}" @selected(old('dental_oral_hygiene', $patient->dentalProfile?->oral_hygiene) === $value)>{{ __($label) }}</option>@endforeach</select></div><div class="col-md-6"><label for="dental_smoking_status" class="form-label">{{ __('Smoking Status') }}</label><select id="dental_smoking_status" name="dental_smoking_status" class="form-select module-field" {{ $isSelected ? '' : 'disabled' }}><option value="">{{ __('Select status') }}</option>@foreach(($dentalSmokingStatusOptions ?? []) as $value => $label)<option value="{{ $value }}" @selected(old('dental_smoking_status', $patient->dentalProfile?->smoking_status) === $value)>{{ __($label) }}</option>@endforeach</select></div></div>
                                                    @elseif($module['key'] === 'pediatric')
                                                        <div class="row g-3"><div class="col-md-6"><label for="pediatric_birth_weight" class="form-label">{{ __('Birth Weight') }}</label><input type="number" id="pediatric_birth_weight" name="pediatric_birth_weight" min="200" max="7000" step="1" class="form-control module-field @error('pediatric_birth_weight') is-invalid @enderror" value="{{ old('pediatric_birth_weight', $patient->pediatricProfile?->birth_weight) }}" {{ $isSelected ? '' : 'disabled' }}>@error('pediatric_birth_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label for="pediatric_gestational_age_weeks" class="form-label">{{ __('Gestational Age') }}</label><input type="number" id="pediatric_gestational_age_weeks" name="pediatric_gestational_age_weeks" min="20" max="45" step="1" class="form-control module-field @error('pediatric_gestational_age_weeks') is-invalid @enderror" value="{{ old('pediatric_gestational_age_weeks', $patient->pediatricProfile?->gestational_age) }}" {{ $isSelected ? '' : 'disabled' }}>@error('pediatric_gestational_age_weeks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12 d-none" id="pediatric-status-indicator"><div id="pediatric-status-message" class="alert py-2 mb-0"></div></div></div>
                                                    @elseif($module['key'] === 'nutrition')
                                                        <div class="row g-3"><div class="col-md-6"><label for="nutrition_height" class="form-label">{{ __('Height (cm)') }}</label><input type="number" id="nutrition_height" name="nutrition_height" min="50" max="300" step="0.1" class="form-control module-field @error('nutrition_height') is-invalid @enderror" value="{{ old('nutrition_height', $patient->nutritionProfile?->height) }}" {{ $isSelected ? '' : 'disabled' }}>@error('nutrition_height')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label for="nutrition_weight" class="form-label">{{ __('Weight (kg)') }}</label><input type="number" id="nutrition_weight" name="nutrition_weight" min="1" max="500" step="0.1" class="form-control module-field @error('nutrition_weight') is-invalid @enderror" value="{{ old('nutrition_weight', $patient->nutritionProfile?->weight) }}" {{ $isSelected ? '' : 'disabled' }}>@error('nutrition_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                                                    @elseif($module['key'] === 'ent')
                                                        <div><label for="ent_notes" class="form-label">{{ __('Notes') }}</label><textarea id="ent_notes" name="ent_notes" rows="4" class="form-control module-field @error('ent_notes') is-invalid @enderror" {{ $isSelected ? '' : 'disabled' }}>{{ old('ent_notes', $patient->entProfile?->notes) }}</textarea>@error('ent_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>{{ __('Update Patient') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const addModuleToggle = document.querySelector('[data-add-module-toggle]');
    const moduleSelectorWrap = document.getElementById('module-selector-wrap');
    const moduleSelector = document.getElementById('module-selector');
    const moduleCards = Array.from(document.querySelectorAll('[data-module-card]'));
    const dobInput = document.getElementById('date_of_birth');
    const pediatricAlert = document.getElementById('pediatric-eligibility-alert');
    const pediatricOption = moduleSelector ? moduleSelector.querySelector('option[value="pediatric"]') : null;
    const bwInput = document.getElementById('pediatric_birth_weight');
    const gaInput = document.getElementById('pediatric_gestational_age_weeks');
    const indicator = document.getElementById('pediatric-status-indicator');
    const message = document.getElementById('pediatric-status-message');

    function calculateAge(value) {
        if (!value) return null;
        const dob = new Date(value + 'T00:00:00');
        if (Number.isNaN(dob.getTime())) return null;
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) age -= 1;
        return age;
    }

    function setModuleState(moduleKey, isActive) {
        const card = document.querySelector('[data-module-card="' + moduleKey + '"]');
        if (!card) return;
        card.classList.toggle('d-none', !isActive);
        card.querySelectorAll('.selected-module-input, .module-field').forEach(function (field) {
            field.disabled = !isActive;
        });
    }

    function refreshModuleSelector() {
        if (!moduleSelector) return;
        moduleCards.forEach(function (card) {
            const option = moduleSelector.querySelector('option[value="' + card.dataset.moduleCard + '"]');
            if (option) option.disabled = !card.classList.contains('d-none');
        });
    }

    function updatePediatricEligibility() {
        const age = calculateAge(dobInput ? dobInput.value : '');
        const eligible = age !== null && age < 16;
        if (pediatricOption) {
            pediatricOption.hidden = !eligible;
            pediatricOption.disabled = !eligible || !document.querySelector('[data-module-card="pediatric"]').classList.contains('d-none');
        }
        if (pediatricAlert) pediatricAlert.classList.toggle('d-none', eligible || age === null);
        if (!eligible) setModuleState('pediatric', false);
        refreshModuleSelector();
    }

    function updatePediatricStatus() {
        const pediatricCard = document.querySelector('[data-module-card="pediatric"]');
        if (!indicator || !message || !bwInput || !gaInput || pediatricCard.classList.contains('d-none')) {
            if (indicator) indicator.classList.add('d-none');
            return;
        }
        const bw = parseInt(bwInput.value, 10);
        const ga = parseInt(gaInput.value, 10);
        if (!bw && !ga) {
            indicator.classList.add('d-none');
            return;
        }
        const labels = [];
        let alertClass = 'alert-success';
        if (bw && bw < 2500) { labels.push('{{ __("Low Birth Weight") }} (<2500g)'); alertClass = 'alert-warning'; }
        if (ga && ga < 37) { labels.push('{{ __("Preterm") }} (<37 {{ __("weeks") }})'); alertClass = 'alert-warning'; }
        if (labels.length === 0) labels.push('{{ __("Normal birth weight & full term") }}');
        message.className = 'alert py-2 mb-0 ' + alertClass;
        message.innerHTML = '<i class="fas fa-info-circle me-1"></i><strong>{{ __("Detected") }}:</strong> ' + labels.join(' & ');
        indicator.classList.remove('d-none');
    }

    if (addModuleToggle) addModuleToggle.addEventListener('click', function () { moduleSelectorWrap.classList.toggle('d-none'); });
    if (moduleSelector) moduleSelector.addEventListener('change', function () { if (!this.value) return; setModuleState(this.value, true); this.value = ''; moduleSelectorWrap.classList.add('d-none'); refreshModuleSelector(); updatePediatricStatus(); });
    document.querySelectorAll('[data-remove-module]').forEach(function (button) { button.addEventListener('click', function () { setModuleState(this.dataset.removeModule, false); refreshModuleSelector(); updatePediatricStatus(); }); });
    if (dobInput) { dobInput.addEventListener('input', updatePediatricEligibility); dobInput.addEventListener('change', updatePediatricEligibility); }
    if (bwInput) bwInput.addEventListener('input', updatePediatricStatus);
    if (gaInput) gaInput.addEventListener('input', updatePediatricStatus);
    refreshModuleSelector(); updatePediatricEligibility(); updatePediatricStatus();

    const firstInvalidField = document.querySelector('.is-invalid');
    if (firstInvalidField && window.bootstrap) {
        const pane = firstInvalidField.closest('.tab-pane');
        const tabTrigger = pane ? document.querySelector('[data-bs-target="#' + pane.id + '"]') : null;
        if (tabTrigger) bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
    }
});
</script>
@endpush
@endsection
