@extends('layouts.app')

@section('title', __('Quick Visit'))

@push('styles')
<style>
#medicinesTable td { vertical-align: top; }
#medicinesTable .select2-container { width: 100% !important; }
#patientInfoBar .badge { font-weight: 500; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-bolt me-2"></i>{{ __('Quick Visit') }}
            </h5>
            <div class="small">
                {{ __('User') }}: {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                &nbsp;|&nbsp; {{ now()->format('d/m/Y') }} &nbsp;|&nbsp; <span id="liveClock">{{ now()->format('h:i A') }}</span>
            </div>
        </div>

        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>{{ __('Please fix the following errors:') }}</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('simple-prescriptions.store') }}" method="POST" id="quickVisitForm">
                @csrf
                <input type="hidden" name="prescribed_date" value="{{ date('Y-m-d') }}">
                <input type="hidden" name="print_after" id="print_after" value="0">

                <!-- Patient + Visit Type -->
                <div class="row g-3 align-items-end mb-2">
                    <div class="col-md-4">
                        <label for="patient_id" class="form-label">
                            {{ __('Patient') }} <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <select class="form-select @error('patient_id') is-invalid @enderror"
                                    id="patient_id" name="patient_id" required style="flex: 1;">
                                <option value="">{{ __('Select a patient...') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                            data-gender="{{ $patient->gender }}"
                                            data-age="{{ $patient->date_of_birth ? $patient->age : '' }}"
                                            data-phone="{{ $patient->phone }}"
                                            {{ (old('patient_id', $selectedPatientId ?? '') == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" id="newPatientBtn" title="{{ __('Add New Patient') }}">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                        @error('patient_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-5">
                        <div id="patientInfoBar" class="small text-muted">{{ __('Select a patient to see details') }}</div>
                    </div>

                    <div class="col-md-3">
                        <label for="visit_type" class="form-label">{{ __('Visit Type') }}</label>
                        <select class="form-select" id="visit_type" name="visit_type">
                            <option value="">-- {{ __('Select') }} --</option>
                            @foreach($visitTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('visit_type') === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Diagnosis + Notes -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2"
                                  placeholder="{{ __('Enter diagnosis...') }}">{{ old('diagnosis') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="notes" class="form-label">{{ __('History / Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"
                                  placeholder="{{ __('History or additional notes...') }}">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Medicines Grid -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">{{ __('Medicines') }}</label>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addMedicineRow()">
                        <i class="fas fa-plus me-1"></i>{{ __('Add Row') }}
                    </button>
                </div>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered" id="medicinesTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">{{ __('Drug Name') }}</th>
                                <th style="min-width: 130px;">{{ __('Type') }}</th>
                                <th style="min-width: 110px;">{{ __('Dose') }}</th>
                                <th style="min-width: 110px;">{{ __('Duration') }}</th>
                                <th style="min-width: 80px;">{{ __('Qty') }}</th>
                                <th style="min-width: 140px;">{{ __('Frequency / Time') }}</th>
                                <th style="width: 36px;"></th>
                            </tr>
                        </thead>
                        <tbody id="medicinesTableBody"></tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('simple-prescriptions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>{{ __('Save') }}
                        </button>
                        <button type="button" class="btn btn-success" id="saveAndPrintBtn">
                            <i class="fas fa-print me-1"></i>{{ __('Save & Print') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden source of medicine <option>s, cloned into each new row -->
<select id="medicineOptionsSource" style="display:none;">
    @foreach($medicines as $medicine)
        <option value="{{ $medicine->name }}" data-dosage="{{ $medicine->dosage }}" data-form="{{ $medicine->form }}">
            {{ $medicine->name }}
            @if($medicine->dosage) - {{ $medicine->dosage }} @endif
            @if($medicine->form) ({{ ucfirst($medicine->form) }}) @endif
        </option>
    @endforeach
</select>

<!-- New Patient Modal -->
<div class="modal fade" id="newPatientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add New Patient') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="newPatientErrors" class="alert alert-danger d-none"></div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="np_first_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="np_last_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Phone') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="np_phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Gender') }}</label>
                        <select class="form-select" id="np_gender">
                            <option value="">--</option>
                            <option value="male">{{ __('Male') }}</option>
                            <option value="female">{{ __('Female') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Date of Birth') }}</label>
                        <input type="date" class="form-control" id="np_dob" max="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="np_save">
                    <i class="fas fa-save me-1"></i>{{ __('Save Patient') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let qvMedicineRowCount = 0;
const medicineForms = @json($medicineForms);

function qvBuildMedicineSelectHtml() {
    return document.getElementById('medicineOptionsSource').innerHTML;
}

function qvBuildTypeOptionsHtml(selected) {
    let html = '<option value="">--</option>';
    for (const [key, label] of Object.entries(medicineForms)) {
        html += `<option value="${key}" ${key === selected ? 'selected' : ''}>${label}</option>`;
    }
    return html;
}

function addMedicineRow() {
    const index = qvMedicineRowCount++;
    const tbody = document.getElementById('medicinesTableBody');
    const tr = document.createElement('tr');
    tr.className = 'medicine-row';
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm medicine-select" name="medicines[${index}][name]">
                <option value="">{{ __('Select or type...') }}</option>
                ${qvBuildMedicineSelectHtml()}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" name="medicines[${index}][type]">
                ${qvBuildTypeOptionsHtml('')}
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm" name="medicines[${index}][dosage]" placeholder="{{ __('e.g., 500mg') }}"></td>
        <td><input type="text" class="form-control form-control-sm" name="medicines[${index}][duration]" placeholder="{{ __('e.g., 7 days') }}"></td>
        <td><input type="number" min="0" class="form-control form-control-sm" name="medicines[${index}][quantity]"></td>
        <td><input type="text" class="form-control form-control-sm" name="medicines[${index}][frequency]" placeholder="{{ __('e.g., BID') }}"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMedicineRow(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);

    initMedicineSelect2(tr.querySelector('.medicine-select'));
}

function removeMedicineRow(button) {
    const row = button.closest('tr');
    $(row).find('.medicine-select').select2('destroy');
    row.remove();
}

function initMedicineSelect2(selectEl) {
    const $select = $(selectEl);

    $select.select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("Type to search medicine...") }}',
        allowClear: true,
        width: '100%',
        minimumInputLength: 1,
        tags: true,
        dropdownAutoWidth: true,
        createTag: function (params) {
            const term = $.trim(params.term);
            if (term === '') {
                return undefined;
            }

            const alreadyExists = Array.from(selectEl.options).some(function (opt) {
                return opt.value && opt.value.toLowerCase() === term.toLowerCase();
            });
            if (alreadyExists) {
                return undefined;
            }

            return {
                id: 'new:' + term,
                text: term + ' ({{ __('Add as new medicine') }})',
                newTag: true
            };
        },
        language: {
            noResults: function () { return '{{ __("No medicines found") }}'; },
            searching: function () { return '{{ __("Searching...") }}'; },
            inputTooShort: function () { return '{{ __("Type 1 or more characters to search...") }}'; }
        }
    });

    $select.on('select2:select', function () {
        const row = selectEl.closest('tr');
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const form = selectedOption ? selectedOption.getAttribute('data-form') : null;
        const typeSelect = row ? row.querySelector('select[name*="[type]"]') : null;
        if (form && typeSelect && !typeSelect.value) {
            typeSelect.value = form;
        }
    });
}

// Patient info bar
function qvUpdatePatientInfo() {
    const select = document.getElementById('patient_id');
    const bar = document.getElementById('patientInfoBar');
    const option = select.options[select.selectedIndex];

    if (!option || !option.value) {
        bar.innerHTML = '{{ __("Select a patient to see details") }}';
        return;
    }

    const gender = option.getAttribute('data-gender');
    const age = option.getAttribute('data-age');
    const phone = option.getAttribute('data-phone');

    let parts = [];
    if (gender) parts.push(`<span class="badge bg-secondary me-1">${gender.charAt(0).toUpperCase() + gender.slice(1)}</span>`);
    if (age) parts.push(`<span class="badge bg-info text-dark me-1">{{ __('Age') }}: ${age}</span>`);
    if (phone) parts.push(`<span class="badge bg-light text-dark border">{{ __('Mobile') }}: ${phone}</span>`);

    bar.innerHTML = parts.length ? parts.join(' ') : '{{ __("No additional details on file") }}';
}

document.addEventListener('DOMContentLoaded', function () {
    addMedicineRow();
    qvUpdatePatientInfo();

    // Live clock (display only)
    setInterval(function () {
        const el = document.getElementById('liveClock');
        if (el) {
            el.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }, 30000);
});

$(document).ready(function () {
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("Select a patient...") }}',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () { return '{{ __("No patients found") }}'; },
            searching: function () { return '{{ __("Searching...") }}'; }
        }
    });

    $('#patient_id').on('change', qvUpdatePatientInfo);

    // Save & Print
    document.getElementById('saveAndPrintBtn').addEventListener('click', function () {
        document.getElementById('print_after').value = '1';
        document.getElementById('quickVisitForm').submit();
    });

    // Quick "Add New Patient" modal
    const newPatientModalEl = document.getElementById('newPatientModal');
    const newPatientModal = new bootstrap.Modal(newPatientModalEl);

    document.getElementById('newPatientBtn').addEventListener('click', function () {
        document.getElementById('newPatientErrors').classList.add('d-none');
        document.getElementById('np_first_name').value = '';
        document.getElementById('np_last_name').value = '';
        document.getElementById('np_phone').value = '';
        document.getElementById('np_gender').value = '';
        document.getElementById('np_dob').value = '';
        newPatientModal.show();
    });

    document.getElementById('np_save').addEventListener('click', function () {
        const errorsBox = document.getElementById('newPatientErrors');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const payload = {
            first_name: document.getElementById('np_first_name').value.trim(),
            last_name: document.getElementById('np_last_name').value.trim(),
            phone: document.getElementById('np_phone').value.trim(),
            gender: document.getElementById('np_gender').value,
            date_of_birth: document.getElementById('np_dob').value,
        };

        fetch('{{ route('patients.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw data;
            }
            return data;
        })
        .then((data) => {
            const patient = data.patient;
            const select = document.getElementById('patient_id');

            const option = document.createElement('option');
            option.value = patient.id;
            option.text = `${patient.full_name} (${patient.patient_id ?? ''})`;
            option.setAttribute('data-gender', payload.gender || '');
            option.setAttribute('data-phone', payload.phone || '');
            select.appendChild(option);

            $(select).val(patient.id).trigger('change');

            newPatientModal.hide();
        })
        .catch((error) => {
            const message = error?.message || '{{ __('Failed to create patient.') }}';
            const errorList = error?.errors ? Object.values(error.errors).flat().join(' ') : '';
            errorsBox.textContent = errorList || message;
            errorsBox.classList.remove('d-none');
        });
    });
});
</script>
@endsection
