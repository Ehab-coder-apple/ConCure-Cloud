@extends('layouts.app')

@section('title', __('Patient Management'))

@php
    $selectedModules = collect(old('selected_modules', []))
        ->merge([
            filled(old('dental_oral_hygiene')) || filled(old('dental_smoking_status')) ? 'dental' : null,
            filled(old('pediatric_birth_weight')) || filled(old('pediatric_gestational_age_weeks')) ? 'pediatric' : null,
            filled(old('nutrition_height')) || filled(old('nutrition_weight')) ? 'nutrition' : null,
            filled(old('ent_notes')) ? 'ent' : null,
        ])->filter()->unique()->values();
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    {{ __('Patient Management') }}
                </h1>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            {{ __('Actions') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('patients.export') }}">
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    {{ __('Export to Excel') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); confirmClearAll();">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    {{ __('Clear All Patients') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('patients.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import me-2"></i>
                        {{ __('Import') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('Add New Patient') }}
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('patients.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Patients') }}</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}"
                                   placeholder="{{ __('Search by name, ID, phone, email (min 1 character)...') }}"
                                   minlength="1">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="gender" class="form-label">{{ __('Gender') }}</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">{{ __('All Genders') }}</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Patients List') }}
                        <span class="badge bg-primary ms-2">{{ $patients->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($patients) && $patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Age') }}</th>
                                        <th>{{ __('Gender') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Last Visit') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $patient->patient_id ?? 'P' . str_pad($patient->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($patient->first_name ?? 'P', 0, 1) . substr($patient->last_name ?? 'A', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') }}</div>
                                                    <small class="text-muted">{{ $patient->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $patient->age_formatted ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $patient->gender == 'male' ? 'info' : 'pink' }} text-dark">
                                                {{ ucfirst($patient->gender ?? 'Unknown') }}
                                            </span>
                                        </td>
                                        <td>{{ $patient->phone ?? '-' }}</td>
                                        <td>{{ $patient->last_visit_date ? \Carbon\Carbon::parse($patient->last_visit_date)->format('M d, Y') : __('Never') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $patient->is_active ? 'success' : 'secondary' }}">
                                                {{ $patient->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('checkups.index', $patient->id) }}" class="btn btn-outline-info" title="{{ __('Check Up') }}">
                                                    <i class="fas fa-notes-medical"></i>
                                                </a>
                                                <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info" title="{{ __('New Appointment') }}" onclick="newAppointment({{ $patient->id }})">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" title="{{ __('New Prescription') }}" onclick="newPrescription({{ $patient->id }})">
                                                    <i class="fas fa-prescription-bottle-alt"></i>
                                                </button>
                                                @if($patient->whatsapp_phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $patient->whatsapp_phone) }}"
                                                   target="_blank" class="btn btn-outline-success" title="{{ __('WhatsApp') }}">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(isset($patients) && method_exists($patients, 'links'))
                            <div class="card-footer">
                                {{ $patients->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Patients Found') }}</h5>
                            <p class="text-muted">{{ __('Start by adding your first patient to the system.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Add First Patient') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Add New Patient') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate data-auto-voice-scope="patient-create-modal">
                @csrf
                <input type="hidden" name="_supports_extended_medical_flags" value="1">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="alert alert-light border d-flex gap-3 align-items-start mb-4">
                        <i class="fas fa-layer-group text-primary mt-1"></i>
                        <div>
                            <div class="fw-semibold">{{ __('Modular patient creation') }}</div>
                            <div class="text-muted small mb-0">{{ __('General for essentials, Medical Overview for shared context, and Modules only for the specialties this patient needs.') }}</div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs flex-wrap gap-2 border-0 mb-4" id="patient-modal-tabs" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#modal-general-pane" type="button" role="tab">{{ __('General') }}</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#modal-medical-pane" type="button" role="tab">{{ __('Medical Overview') }}</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#modal-modules-pane" type="button" role="tab">{{ __('Modules') }}</button></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="modal-general-pane" role="tabpanel">
                            <div class="card bg-light border-0"><div class="card-body"><div class="row g-3">
                                @if(($availableClinics ?? collect())->isNotEmpty() && auth()->user()?->isSuperAdmin() && !auth()->user()?->clinic_id)
                                    <div class="col-12">
                                        <label for="modal_clinic_id" class="form-label">{{ __('Clinic') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('clinic_id') is-invalid @enderror" id="modal_clinic_id" name="clinic_id">
                                            <option value="">{{ __('Select clinic') }}</option>
                                            @foreach($availableClinics as $clinic)
                                                <option value="{{ $clinic->id }}" @selected(old('clinic_id') == $clinic->id)>{{ $clinic->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('clinic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endif

                                <div class="col-md-6"><label for="modal_first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label><input type="text" class="form-control @error('first_name') is-invalid @enderror" id="modal_first_name" name="first_name" value="{{ old('first_name') }}" required>@error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label><input type="text" class="form-control @error('last_name') is-invalid @enderror" id="modal_last_name" name="last_name" value="{{ old('last_name') }}" required>@error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_date_of_birth" class="form-label">{{ __('Date of Birth') }}</label><input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="modal_date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">@error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_gender" class="form-label">{{ __('Gender') }}</label><select class="form-select @error('gender') is-invalid @enderror" id="modal_gender" name="gender"><option value="">{{ __('Select Gender') }}</option><option value="male" @selected(old('gender') === 'male')>{{ __('Male') }}</option><option value="female" @selected(old('gender') === 'female')>{{ __('Female') }}</option><option value="other" @selected(old('gender') === 'other')>{{ __('Other') }}</option></select>@error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_phone" class="form-label">{{ __('Phone') }}</label><input type="tel" class="form-control @error('phone') is-invalid @enderror" id="modal_phone" name="phone" value="{{ old('phone') }}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_whatsapp_phone" class="form-label">{{ __('WhatsApp') }}</label><input type="tel" class="form-control @error('whatsapp_phone') is-invalid @enderror" id="modal_whatsapp_phone" name="whatsapp_phone" value="{{ old('whatsapp_phone') }}">@error('whatsapp_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_email" class="form-label">{{ __('Email') }}</label><input type="email" class="form-control @error('email') is-invalid @enderror" id="modal_email" name="email" value="{{ old('email') }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_blood_type" class="form-label">{{ __('Blood Type') }}</label><select class="form-select @error('blood_type') is-invalid @enderror" id="modal_blood_type" name="blood_type"><option value="">{{ __('Select Blood Type') }}</option>@foreach(['NA' => __('NA - Not available'), 'A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'] as $value => $label)<option value="{{ $value }}" @selected(old('blood_type') === $value)>{{ $label }}</option>@endforeach</select>@error('blood_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12"><label for="modal_address" class="form-label">{{ __('Address') }}</label><textarea class="form-control @error('address') is-invalid @enderror" id="modal_address" name="address" rows="3">{{ old('address') }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_job" class="form-label">{{ __('Occupation') }}</label><input type="text" class="form-control @error('job') is-invalid @enderror" id="modal_job" name="job" value="{{ old('job') }}">@error('job')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_education" class="form-label">{{ __('Education Level') }}</label><input type="text" class="form-control @error('education') is-invalid @enderror" id="modal_education" name="education" value="{{ old('education') }}">@error('education')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_emergency_contact_name" class="form-label">{{ __('Emergency Contact Name') }}</label><input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" id="modal_emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">@error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label for="modal_emergency_contact_phone" class="form-label">{{ __('Emergency Contact Phone') }}</label><input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" id="modal_emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">@error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12"><label for="modal_notes" class="form-label">{{ __('Notes') }}</label><textarea class="form-control @error('notes') is-invalid @enderror" id="modal_notes" name="notes" rows="3">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            </div></div></div>
                        </div>

                        <div class="tab-pane fade" id="modal-medical-pane" role="tabpanel">
                            <div class="alert alert-info"><i class="fas fa-circle-info me-1"></i>{{ __('HPI stays per visit. Use this section only for shared cross-module history, flags, and uploaded history files.') }}</div>
                            <div class="accordion" id="modal-medical-overview-accordion">
                                <div class="accordion-item border rounded-3 mb-3 overflow-hidden"><h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#modal-collapse-shared-history">{{ __('Shared history') }}</button></h2><div id="modal-collapse-shared-history" class="accordion-collapse collapse show" data-bs-parent="#modal-medical-overview-accordion"><div class="accordion-body row g-3"><div class="col-12"><label for="modal_allergies" class="form-label">{{ __('Allergies') }}</label><textarea class="form-control @error('allergies') is-invalid @enderror" id="modal_allergies" name="allergies" rows="2">{{ old('allergies') }}</textarea>@error('allergies')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label for="modal_chronic_illnesses" class="form-label">{{ __('Chronic Diseases') }}</label><textarea class="form-control @error('chronic_illnesses') is-invalid @enderror" id="modal_chronic_illnesses" name="chronic_illnesses" rows="2">{{ old('chronic_illnesses') }}</textarea>@error('chronic_illnesses')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label for="modal_current_medications_summary" class="form-label">{{ __('Current Medications') }}</label><textarea class="form-control @error('current_medications_summary') is-invalid @enderror" id="modal_current_medications_summary" name="current_medications_summary" rows="2">{{ old('current_medications_summary') }}</textarea>@error('current_medications_summary')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div></div>
                                <div class="accordion-item border rounded-3 mb-3 overflow-hidden"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modal-collapse-procedures">{{ __('Procedures, files & notes') }}</button></h2><div id="modal-collapse-procedures" class="accordion-collapse collapse" data-bs-parent="#modal-medical-overview-accordion"><div class="accordion-body row g-3"><div class="col-12"><label for="modal_surgeries_history" class="form-label">{{ __('Surgeries') }}</label><textarea class="form-control @error('surgeries_history') is-invalid @enderror" id="modal_surgeries_history" name="surgeries_history" rows="2">{{ old('surgeries_history') }}</textarea>@error('surgeries_history')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label for="modal_medical_history" class="form-label">{{ __('Medical History') }}</label><textarea class="form-control @error('medical_history') is-invalid @enderror" id="modal_medical_history" name="medical_history" rows="3">{{ old('medical_history') }}</textarea>@error('medical_history')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12"><label for="modal_medical_files" class="form-label">{{ __('Medical History Files') }}</label><input type="file" class="form-control @error('medical_files.*') is-invalid @enderror" id="modal_medical_files" name="medical_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"><small class="text-muted">{{ __('Upload reports, scans, or intake-related documents.') }}</small>@error('medical_files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div></div>
                                <div class="accordion-item border rounded-3 overflow-hidden"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#modal-collapse-flags">{{ __('Flags') }}</button></h2><div id="modal-collapse-flags" class="accordion-collapse collapse" data-bs-parent="#modal-medical-overview-accordion"><div class="accordion-body"><div class="row g-3">@foreach(($medicalFlags ?? []) as $flagKey => $flagLabel)<div class="col-sm-6 col-lg-4"><div class="form-check border rounded p-3 h-100"><input class="form-check-input" type="checkbox" id="modal_medical_flag_{{ $flagKey }}" name="medical_flags[{{ $flagKey }}]" value="1" @checked(old('medical_flags.' . $flagKey))><label class="form-check-label fw-semibold" for="modal_medical_flag_{{ $flagKey }}">{{ __($flagLabel) }}</label></div></div>@endforeach</div></div></div></div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="modal-modules-pane" role="tabpanel">
                            <div class="card bg-light border-0 mb-3"><div class="card-body d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center"><div><h6 class="mb-1">{{ __('Optional modules') }}</h6><p class="text-muted mb-0 small">{{ __('Add only the specialty modules you need. HPI is intentionally not part of this intake form.') }}</p></div><div class="d-flex flex-column flex-sm-row gap-2"><button type="button" class="btn btn-outline-primary" data-add-module-toggle><i class="fas fa-plus me-1"></i>{{ __('Add Module') }}</button><div class="d-none" id="modal-module-selector-wrap"><select class="form-select" id="modal-module-selector"><option value="">{{ __('Choose module') }}</option>@foreach($moduleDefinitions as $module)<option value="{{ $module['key'] }}">{{ $module['label'] }}</option>@endforeach</select></div></div></div></div>
                            <div class="alert alert-info d-none" id="modal-pediatric-eligibility-alert"><i class="fas fa-info-circle me-1"></i>{{ __('Pediatric is available only for patients younger than 16 years.') }}</div>
                            <div class="row g-3">
                                @foreach($moduleDefinitions as $module)
                                    @php($isSelected = $selectedModules->contains($module['key']))
                                    <div class="col-12 col-lg-6 module-card {{ $isSelected ? '' : 'd-none' }}" data-module-card="{{ $module['key'] }}"><div class="card border shadow-sm h-100"><div class="card-header bg-white d-flex justify-content-between align-items-start gap-3"><div><div class="fw-semibold"><i class="{{ $module['icon'] }} text-primary me-2"></i>{{ $module['label'] }}</div><div class="small text-muted">{{ $module['description'] }}</div></div><button type="button" class="btn btn-sm btn-outline-danger" data-remove-module="{{ $module['key'] }}">{{ __('Remove') }}</button></div><div class="card-body"><input type="hidden" name="selected_modules[]" value="{{ $module['key'] }}" class="selected-module-input" {{ $isSelected ? '' : 'disabled' }}>
                                        @if($module['key'] === 'dental')
                                            <div class="row g-3"><div class="col-md-6"><label for="modal_dental_oral_hygiene" class="form-label">{{ __('Oral Hygiene') }}</label><select id="modal_dental_oral_hygiene" name="dental_oral_hygiene" class="form-select module-field @error('dental_oral_hygiene') is-invalid @enderror" {{ $isSelected ? '' : 'disabled' }}><option value="">{{ __('Select status') }}</option>@foreach(($dentalOralHygieneOptions ?? []) as $value => $label)<option value="{{ $value }}" @selected(old('dental_oral_hygiene') === $value)>{{ __($label) }}</option>@endforeach</select>@error('dental_oral_hygiene')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label for="modal_dental_smoking_status" class="form-label">{{ __('Smoking Status') }}</label><select id="modal_dental_smoking_status" name="dental_smoking_status" class="form-select module-field @error('dental_smoking_status') is-invalid @enderror" {{ $isSelected ? '' : 'disabled' }}><option value="">{{ __('Select status') }}</option>@foreach(($dentalSmokingStatusOptions ?? []) as $value => $label)<option value="{{ $value }}" @selected(old('dental_smoking_status') === $value)>{{ __($label) }}</option>@endforeach</select>@error('dental_smoking_status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                                        @elseif($module['key'] === 'pediatric')
                                            <div class="row g-3"><div class="col-md-6"><label for="modal_pediatric_birth_weight" class="form-label">{{ __('Birth Weight') }}</label><input type="number" id="modal_pediatric_birth_weight" name="pediatric_birth_weight" min="200" max="7000" step="1" class="form-control module-field @error('pediatric_birth_weight') is-invalid @enderror" value="{{ old('pediatric_birth_weight') }}" {{ $isSelected ? '' : 'disabled' }}>@error('pediatric_birth_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label for="modal_pediatric_gestational_age_weeks" class="form-label">{{ __('Gestational Age') }}</label><input type="number" id="modal_pediatric_gestational_age_weeks" name="pediatric_gestational_age_weeks" min="20" max="45" step="1" class="form-control module-field @error('pediatric_gestational_age_weeks') is-invalid @enderror" value="{{ old('pediatric_gestational_age_weeks') }}" {{ $isSelected ? '' : 'disabled' }}>@error('pediatric_gestational_age_weeks')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-12 d-none" id="modal-pediatric-status-indicator"><div id="modal-pediatric-status-message" class="alert py-2 mb-0"></div></div></div>
                                        @elseif($module['key'] === 'nutrition')
                                            <div class="row g-3"><div class="col-md-6"><label for="modal_nutrition_height" class="form-label">{{ __('Height (cm)') }}</label><input type="number" id="modal_nutrition_height" name="nutrition_height" min="50" max="300" step="0.1" class="form-control module-field @error('nutrition_height') is-invalid @enderror" value="{{ old('nutrition_height') }}" {{ $isSelected ? '' : 'disabled' }}>@error('nutrition_height')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label for="modal_nutrition_weight" class="form-label">{{ __('Weight (kg)') }}</label><input type="number" id="modal_nutrition_weight" name="nutrition_weight" min="1" max="500" step="0.1" class="form-control module-field @error('nutrition_weight') is-invalid @enderror" value="{{ old('nutrition_weight') }}" {{ $isSelected ? '' : 'disabled' }}>@error('nutrition_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                                        @elseif($module['key'] === 'ent')
                                            <div><label for="modal_ent_notes" class="form-label">{{ __('Notes') }}</label><textarea id="modal_ent_notes" name="ent_notes" rows="4" class="form-control module-field @error('ent_notes') is-invalid @enderror" {{ $isSelected ? '' : 'disabled' }}>{{ old('ent_notes') }}</textarea>@error('ent_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                        @endif
                                    </div></div></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create Patient') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addPatientModal');
    if (!modal) return;

    const addModuleToggle = modal.querySelector('[data-add-module-toggle]');
    const moduleSelectorWrap = modal.querySelector('#modal-module-selector-wrap');
    const moduleSelector = modal.querySelector('#modal-module-selector');
    const moduleCards = Array.from(modal.querySelectorAll('[data-module-card]'));
    const dobInput = modal.querySelector('#modal_date_of_birth');
    const pediatricAlert = modal.querySelector('#modal-pediatric-eligibility-alert');
    const pediatricOption = moduleSelector ? moduleSelector.querySelector('option[value="pediatric"]') : null;
    const bwInput = modal.querySelector('#modal_pediatric_birth_weight');
    const gaInput = modal.querySelector('#modal_pediatric_gestational_age_weeks');
    const indicator = modal.querySelector('#modal-pediatric-status-indicator');
    const message = modal.querySelector('#modal-pediatric-status-message');

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

    function getModuleCard(moduleKey) {
        return modal.querySelector('[data-module-card="' + moduleKey + '"]');
    }

    function setModuleState(moduleKey, isActive) {
        const card = getModuleCard(moduleKey);
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
        const pediatricCard = getModuleCard('pediatric');
        const age = calculateAge(dobInput ? dobInput.value : '');
        const eligible = age !== null && age < 16;
        if (pediatricOption && pediatricCard) {
            pediatricOption.hidden = !eligible;
            pediatricOption.disabled = !eligible || !pediatricCard.classList.contains('d-none');
        }
        if (pediatricAlert) pediatricAlert.classList.toggle('d-none', eligible || age === null);
        if (!eligible) setModuleState('pediatric', false);
        refreshModuleSelector();
    }

    function updatePediatricStatus() {
        const pediatricCard = getModuleCard('pediatric');
        if (!indicator || !message || !bwInput || !gaInput || !pediatricCard || pediatricCard.classList.contains('d-none')) {
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

    if (addModuleToggle) addModuleToggle.addEventListener('click', function () {
        moduleSelectorWrap.classList.toggle('d-none');
    });

    if (moduleSelector) {
        moduleSelector.addEventListener('change', function () {
            if (!this.value) return;
            setModuleState(this.value, true);
            this.value = '';
            moduleSelectorWrap.classList.add('d-none');
            refreshModuleSelector();
            updatePediatricStatus();
        });
    }

    modal.querySelectorAll('[data-remove-module]').forEach(function (button) {
        button.addEventListener('click', function () {
            setModuleState(this.dataset.removeModule, false);
            refreshModuleSelector();
            updatePediatricStatus();
        });
    });

    if (dobInput) {
        dobInput.addEventListener('input', updatePediatricEligibility);
        dobInput.addEventListener('change', updatePediatricEligibility);
    }
    if (bwInput) bwInput.addEventListener('input', updatePediatricStatus);
    if (gaInput) gaInput.addEventListener('input', updatePediatricStatus);

    refreshModuleSelector();
    updatePediatricEligibility();
    updatePediatricStatus();

    const firstInvalidField = modal.querySelector('.is-invalid');
    if (firstInvalidField && window.bootstrap) {
        const pane = firstInvalidField.closest('.tab-pane');
        const tabTrigger = pane ? modal.querySelector('[data-bs-target="#' + pane.id + '"]') : null;
        if (tabTrigger) bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
    }

    @if(session()->has('errors') || old('_supports_extended_medical_flags'))
    if (window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
    @endif
});

function newPrescription(patientId) {
    window.location.href = `/simple-prescriptions/create?patient_id=${patientId}`;
}

function newAppointment(patientId) {
    window.location.href = `/appointments/create?patient_id=${patientId}`;
}

function confirmClearAll() {
    if (confirm('{{ __("Are you sure you want to delete ALL patients? This action cannot be undone!") }}')) {
        if (confirm('{{ __("This will permanently delete all patient records and their associated data. Are you absolutely sure?") }}')) {
            fetch('{{ route("patients.clear-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }
}
</script>
@include('partials.voice-input')
@endsection
