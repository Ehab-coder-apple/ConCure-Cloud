@extends('layouts.app')

@section('title', __('Pediatric Prescription Builder'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-pills me-2 text-primary"></i>
                        {{ __('Pediatric Prescription Builder') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Multi-drug dosing calculator with safety validation') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pediatric.medication.history') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history me-1"></i> {{ __('History') }}
                    </a>
                    <a href="{{ route('pediatric.medication.drug-admin') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-database me-1"></i> {{ __('Drug Database') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Step 1: Patient Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-user-injured me-2"></i>{{ __('Step 1: Select Patient') }}</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">{{ __('Patient') }}</label>
                    <select id="patientSelect" class="form-select">
                        <option value="">-- {{ __('Select Patient') }} --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <div id="patientInfoBar" class="alert alert-info mb-0 d-none" style="font-size: 0.9rem;">
                        <div class="d-flex flex-wrap gap-3">
                            <span><i class="fas fa-weight me-1"></i> <strong>{{ __('Weight') }}:</strong> <span id="ptWeight">--</span> kg</span>
                            <span><i class="fas fa-birthday-cake me-1"></i> <strong>{{ __('Age') }}:</strong> <span id="ptAge">--</span></span>
                            <span><i class="fas fa-venus-mars me-1"></i> <span id="ptGender">--</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Add & Calculate Drugs (Real-time) -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2 text-primary"></i>{{ __('Step 2: Add Drugs & Review') }}</h5>
            <button type="button" id="addDrugRowBtn" class="btn btn-sm btn-outline-primary" disabled>
                <i class="fas fa-plus me-1"></i>{{ __('Add Drug') }}
            </button>
        </div>
        <div class="card-body p-0">
            <div id="noDrugsMsg" class="text-muted text-center py-4">{{ __('Select a patient first, then add drugs to the prescription.') }}</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 d-none" id="drugTable">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAllRx" checked></th>
                            <th>{{ __('Drug') }}</th>
                            <th>{{ __('Form') }}</th>
                            <th>{{ __('Dose') }}</th>
                            <th>{{ __('Frequency') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Safety') }}</th>
                            <th>{{ __('Notes') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="drugTableBody"></tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-none" id="prescribeFooter">
            <form method="POST" action="{{ route('pediatric.medication.prescribe.bulk') }}" id="bulkPrescriptionForm">
                @csrf
                <input type="hidden" name="patient_id" id="bulkPatientId">
                <div id="bulkHiddenFields"></div>
                <button type="submit" class="btn btn-success w-100" id="saveBulkBtn">
                    <i class="fas fa-save me-2"></i>{{ __('Save All Prescriptions') }}
                </button>
            </form>
        </div>
    </div>
</div>

@php
    $drugFormsData = $drugs->keyBy('id')->map(fn($d) => $d->forms);
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const patientSelect = document.getElementById('patientSelect');
    const addDrugRowBtn = document.getElementById('addDrugRowBtn');
    const drugTable = document.getElementById('drugTable');
    const drugTableBody = document.getElementById('drugTableBody');
    const noDrugsMsg = document.getElementById('noDrugsMsg');
    const prescribeFooter = document.getElementById('prescribeFooter');
    const patientInfoBar = document.getElementById('patientInfoBar');

    const drugForms = @json($drugFormsData);
    const drugsData = @json($drugs->map(fn($d) => ['id' => $d->id, 'name' => $d->generic_name . ($d->brand_name ? ' ('.$d->brand_name.')' : ''), 'category' => filled($d->category) ? $d->category : 'Uncategorized']));

    let rowCounter = 0;
    let patientWeight = null;

    const csrfToken = '{{ csrf_token() }}';
    const calcUrl = '{{ route("pediatric.medication.calculate") }}';
    const validateUrl = '{{ route("pediatric.medication.validate") }}';
    const patientInfoUrl = '{{ url("pediatric/medication/patient") }}';

    function freqLabel(freq, hours) {
        if (hours) return freq + 'x/day (Every ' + hours + 'h)';
        return freq + ' times/day';
    }

    function debounce(fn, delay) {
        let timer;
        return function(...args) { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), delay); };
    }

    // Patient change
    patientSelect.addEventListener('change', function() {
        if (!this.value) {
            patientInfoBar.classList.add('d-none');
            addDrugRowBtn.disabled = true;
            patientWeight = null;
            return;
        }
        addDrugRowBtn.disabled = false;
        fetch(`${patientInfoUrl}/${this.value}/info`)
            .then(r => r.json())
            .then(data => {
                patientWeight = data.weight_kg;
                document.getElementById('ptWeight').textContent = data.weight_kg ?? 'N/A';
                document.getElementById('ptAge').textContent = data.age_months + ' months (' + data.age + ' years)';
                document.getElementById('ptGender').textContent = data.gender ? data.gender.charAt(0).toUpperCase() + data.gender.slice(1) : '--';
                patientInfoBar.classList.remove('d-none');
                patientInfoBar.classList.toggle('alert-warning', !data.weight_kg);
                patientInfoBar.classList.toggle('alert-info', !!data.weight_kg);
                // Recalculate all existing rows for new patient
                recalcAllRows();
            });
    });

    function showTable() {
        noDrugsMsg.classList.add('d-none');
        drugTable.classList.remove('d-none');
        prescribeFooter.classList.remove('d-none');
    }

    function hideTableIfEmpty() {
        if (!drugTableBody.querySelectorAll('tr').length) {
            noDrugsMsg.classList.remove('d-none');
            drugTable.classList.add('d-none');
            prescribeFooter.classList.add('d-none');
        }
    }

    // Add drug row
    addDrugRowBtn.addEventListener('click', function() {
        showTable();
        const idx = rowCounter++;

        // Build optgroup-based drug options grouped by category
        let drugOpts = '<option value="">-- Drug --</option>';
        const grouped = {};
        drugsData.forEach(d => {
            if (!grouped[d.category]) grouped[d.category] = [];
            grouped[d.category].push(d);
        });
        const sortedCats = Object.keys(grouped).sort((a, b) => {
            if (a === 'Uncategorized') return 1;
            if (b === 'Uncategorized') return -1;
            return a.localeCompare(b);
        });
        sortedCats.forEach(cat => {
            drugOpts += `<optgroup label="${cat}">`;
            grouped[cat].forEach(d => { drugOpts += `<option value="${d.id}">${d.name}</option>`; });
            drugOpts += '</optgroup>';
        });

        const tr = document.createElement('tr');
        tr.dataset.idx = idx;
        tr.innerHTML = `
            <td><input type="checkbox" class="rx-check" data-idx="${idx}" checked></td>
            <td>
                <select class="form-select form-select-sm drug-select" data-idx="${idx}" style="min-width:140px">
                    ${drugOpts}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm form-select-field" data-idx="${idx}" disabled style="min-width:160px">
                    <option value="">-- Form --</option>
                </select>
            </td>
            <td class="dose-cell" data-idx="${idx}">
                <span class="text-muted small">--</span>
            </td>
            <td class="freq-cell" data-idx="${idx}">
                <span class="text-muted small">--</span>
            </td>
            <td class="dur-cell" data-idx="${idx}">
                <span class="text-muted small">--</span>
            </td>
            <td class="safety-cell" data-idx="${idx}">
                <span class="badge bg-secondary">{{ __('Pending') }}</span>
            </td>
            <td class="notes-cell" data-idx="${idx}">
                <span class="text-muted small">--</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="{{ __('Remove') }}">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        drugTableBody.appendChild(tr);

        const drugSel = tr.querySelector('.drug-select');
        const formSel = tr.querySelector('.form-select-field');

        // Drug change -> populate forms
        drugSel.addEventListener('change', function() {
            formSel.innerHTML = '<option value="">-- Form --</option>';
            if (this.value && drugForms[this.value]) {
                drugForms[this.value].forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.id;
                    opt.textContent = f.form.charAt(0).toUpperCase() + f.form.slice(1) + ' (' + f.concentration + ')';
                    formSel.appendChild(opt);
                });
                formSel.disabled = false;
            } else {
                formSel.disabled = true;
                clearRowResult(idx);
            }
        });

        // Form change -> auto-calculate
        formSel.addEventListener('change', function() {
            if (this.value && drugSel.value) {
                calculateRow(idx, drugSel.value, this.value);
            } else {
                clearRowResult(idx);
            }
        });

        // Remove
        tr.querySelector('.remove-row-btn').addEventListener('click', function() {
            tr.remove();
            hideTableIfEmpty();
        });
    });

    function clearRowResult(idx) {
        const tr = drugTableBody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr) return;
        tr.querySelector('.dose-cell').innerHTML = '<span class="text-muted small">--</span>';
        tr.querySelector('.freq-cell').innerHTML = '<span class="text-muted small">--</span>';
        tr.querySelector('.dur-cell').innerHTML = '<span class="text-muted small">--</span>';
        tr.querySelector('.safety-cell').innerHTML = '<span class="badge bg-secondary">Pending</span>';
        tr.querySelector('.notes-cell').innerHTML = '<span class="text-muted small">--</span>';
        tr.dataset.calcData = '';
    }

    function calculateRow(idx, drugId, formId) {
        const tr = drugTableBody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr) return;
        tr.querySelector('.safety-cell').innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i>';

        fetch(calcUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ patient_id: patientSelect.value, drug_id: drugId, form_id: formId })
        })
        .then(r => r.json())
        .then(data => {
            data.drug_id = drugId;
            data.form_id = formId;
            tr.dataset.calcData = JSON.stringify(data);
            renderRowResult(idx, data);
        })
        .catch(() => {
            tr.querySelector('.safety-cell').innerHTML = '<span class="badge bg-warning">Error</span>';
        });
    }

    function renderRowResult(idx, r) {
        const tr = drugTableBody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr) return;

        const hasError = !!r.error;
        const safety = hasError ? { status: 'warning', message: r.error } : r.safety;
        const colors = { safe: 'success', warning: 'warning', danger: 'danger' };
        const icons = { safe: 'check-circle', warning: 'exclamation-triangle', danger: 'times-circle' };
        const isDanger = safety.status === 'danger';

        // Dose cell with mg/kg
        if (hasError) {
            tr.querySelector('.dose-cell').innerHTML = '<span class="text-muted">--</span>';
        } else {
            const mlStr = r.dose_ml ? `<span class="text-info">(${r.dose_ml} ml)</span>` : '';
            const mgKgStr = r.mg_per_kg ? `<div class="small text-primary fw-bold">= ${r.mg_per_kg} mg/kg</div>` : '';
            const rangeStr = `<div class="small text-muted">${r.dose_min_mg} – ${r.dose_max_mg} mg</div>`;
            tr.querySelector('.dose-cell').innerHTML = `
                <input type="number" step="0.01" class="form-control form-control-sm rx-dose" data-idx="${idx}" value="${r.recommended_dose_mg}" style="width:90px">
                <div class="small mt-1">${mlStr} ${mgKgStr}</div>
                ${rangeStr}
            `;
        }

        // Frequency cell with human-readable label
        if (hasError) {
            tr.querySelector('.freq-cell').innerHTML = '<span class="text-muted">--</span>';
        } else {
            tr.querySelector('.freq-cell').innerHTML = `
                <input type="number" min="1" class="form-control form-control-sm rx-freq" data-idx="${idx}" value="${r.frequency_per_day}" style="width:60px">
                <div class="small text-muted mt-1">${freqLabel(r.frequency_per_day, r.frequency_hours)}</div>
            `;
        }

        // Duration
        if (!hasError) {
            tr.querySelector('.dur-cell').innerHTML = `<input type="number" min="1" class="form-control form-control-sm rx-dur" data-idx="${idx}" value="5" style="width:60px">`;
        }

        // Safety cell with daily dose info
        let dailyInfo = '';
        if (!hasError && r.daily_dose_mg) {
            dailyInfo = `<div class="small mt-1">Daily: ${r.daily_mg_per_kg || '--'} mg/kg/day`;
            if (r.max_daily_mg_per_kg) dailyInfo += ` <span class="text-muted">(Max: ${r.max_daily_mg_per_kg})</span>`;
            else if (r.max_daily_mg) dailyInfo += ` <span class="text-muted">(Max: ${r.max_daily_mg} mg)</span>`;
            dailyInfo += '</div>';
        }
        tr.querySelector('.safety-cell').innerHTML = `
            <span class="badge bg-${colors[safety.status]}">
                <i class="fas fa-${icons[safety.status]} me-1"></i>${safety.status.toUpperCase()}
            </span>
            <div class="small text-muted mt-1" style="max-width:220px">${safety.message}</div>
            ${dailyInfo}
        `;

        // Notes/override cell
        tr.querySelector('.notes-cell').innerHTML = `
            ${isDanger ? `<input type="text" class="form-control form-control-sm rx-override border-danger" data-idx="${idx}" placeholder="⚠️ Override reason (required)" style="min-width:140px">` : ''}
            <input type="text" class="form-control form-control-sm mt-1 rx-notes" data-idx="${idx}" placeholder="Notes" style="min-width:140px">
        `;

        // Attach inline validation listeners for dose/freq changes
        const doseInput = tr.querySelector('.rx-dose');
        const freqInput = tr.querySelector('.rx-freq');
        if (doseInput) doseInput.addEventListener('input', debounce(() => inlineValidate(idx), 400));
        if (freqInput) freqInput.addEventListener('input', debounce(() => inlineValidate(idx), 400));

        tr.classList.toggle('table-warning', hasError);
        tr.querySelector('.rx-check').disabled = hasError;
    }

    // Inline validate when dose or frequency changes
    function inlineValidate(idx) {
        const tr = drugTableBody.querySelector(`tr[data-idx="${idx}"]`);
        if (!tr || !tr.dataset.calcData) return;
        const orig = JSON.parse(tr.dataset.calcData);

        const dose = tr.querySelector('.rx-dose')?.value;
        const freq = tr.querySelector('.rx-freq')?.value;
        if (!dose || !freq) return;

        tr.querySelector('.safety-cell').innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i>';

        fetch(validateUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                patient_id: patientSelect.value,
                drug_id: orig.drug_id,
                form_id: orig.form_id,
                dose_mg: dose,
                frequency_per_day: freq,
            })
        })
        .then(r => r.json())
        .then(data => {
            const safety = data.safety;
            const colors = { safe: 'success', warning: 'warning', danger: 'danger' };
            const icons = { safe: 'check-circle', warning: 'exclamation-triangle', danger: 'times-circle' };
            const isDanger = safety.status === 'danger';

            // Update mg/kg display in dose cell
            const doseMg = parseFloat(dose);
            const mlStr = data.dose_ml ? `<span class="text-info">(${data.dose_ml} ml)</span>` : '';
            const mgKgStr = data.mg_per_kg ? `<div class="small text-primary fw-bold">= ${data.mg_per_kg} mg/kg</div>` : '';
            const doseInput = tr.querySelector('.rx-dose');
            const doseExtra = tr.querySelector('.dose-cell .dose-extra');
            // Update the extra info below the input
            let extraDiv = tr.querySelector('.dose-cell .dose-info');
            if (!extraDiv) {
                extraDiv = document.createElement('div');
                extraDiv.className = 'dose-info';
                doseInput.parentNode.appendChild(extraDiv);
            }
            extraDiv.innerHTML = `<div class="small mt-1">${mlStr} ${mgKgStr}</div><div class="small text-muted">${orig.dose_min_mg} – ${orig.dose_max_mg} mg</div>`;

            // Update frequency label
            const freqVal = parseInt(freq);
            const freqLbl = tr.querySelector('.freq-cell .freq-label');
            if (freqLbl) freqLbl.textContent = freqLabel(freqVal, null);

            // Daily dose info
            let dailyInfo = '';
            if (data.daily_mg_per_kg) {
                dailyInfo = `<div class="small mt-1">Daily: ${data.daily_mg_per_kg} mg/kg/day`;
                if (data.max_daily_mg_per_kg) dailyInfo += ` <span class="text-muted">(Max: ${data.max_daily_mg_per_kg})</span>`;
                else if (data.max_daily_mg) dailyInfo += ` <span class="text-muted">(Max: ${data.max_daily_mg} mg)</span>`;
                dailyInfo += '</div>';
            }

            tr.querySelector('.safety-cell').innerHTML = `
                <span class="badge bg-${colors[safety.status]}">
                    <i class="fas fa-${icons[safety.status]} me-1"></i>${safety.status.toUpperCase()}
                </span>
                <div class="small text-muted mt-1" style="max-width:220px">${safety.message}</div>
                ${dailyInfo}
            `;

            // Show/hide override field
            const notesCell = tr.querySelector('.notes-cell');
            const existingOverride = notesCell.querySelector('.rx-override');
            const existingNotes = notesCell.querySelector('.rx-notes');
            const notesVal = existingNotes?.value || '';

            if (isDanger && !existingOverride) {
                const ov = document.createElement('input');
                ov.type = 'text'; ov.className = 'form-control form-control-sm rx-override border-danger';
                ov.dataset.idx = idx; ov.placeholder = '⚠️ Override reason (required)';
                ov.style.minWidth = '140px';
                notesCell.insertBefore(ov, notesCell.firstChild);
            } else if (!isDanger && existingOverride) {
                existingOverride.remove();
            }
        });
    }

    function recalcAllRows() {
        drugTableBody.querySelectorAll('tr').forEach(tr => {
            const drugSel = tr.querySelector('.drug-select');
            const formSel = tr.querySelector('.form-select-field');
            if (drugSel?.value && formSel?.value) {
                calculateRow(tr.dataset.idx, drugSel.value, formSel.value);
            }
        });
    }

    // Select all checkbox
    document.getElementById('selectAllRx').onchange = function() {
        document.querySelectorAll('.rx-check:not(:disabled)').forEach(cb => cb.checked = this.checked);
    };

    // Build hidden fields on submit
    document.getElementById('bulkPrescriptionForm').addEventListener('submit', function(e) {
        const hiddenContainer = document.getElementById('bulkHiddenFields');
        hiddenContainer.innerHTML = '';
        document.getElementById('bulkPatientId').value = patientSelect.value;
        let count = 0;

        drugTableBody.querySelectorAll('tr').forEach(tr => {
            const cb = tr.querySelector('.rx-check');
            if (!cb || !cb.checked || cb.disabled) return;

            const calcStr = tr.dataset.calcData;
            if (!calcStr) return;
            const r = JSON.parse(calcStr);
            if (r.error) return;

            const idx = tr.dataset.idx;
            const dose = tr.querySelector('.rx-dose')?.value || r.recommended_dose_mg;
            const freq = tr.querySelector('.rx-freq')?.value || r.frequency_per_day;
            const dur = tr.querySelector('.rx-dur')?.value || 5;
            const override = tr.querySelector('.rx-override')?.value || '';
            const notes = tr.querySelector('.rx-notes')?.value || '';

            const prefix = `items[${count}]`;
            hiddenContainer.innerHTML += `
                <input type="hidden" name="${prefix}[drug_id]" value="${r.drug_id}">
                <input type="hidden" name="${prefix}[form_id]" value="${r.form_id}">
                <input type="hidden" name="${prefix}[dose_mg]" value="${dose}">
                <input type="hidden" name="${prefix}[frequency_per_day]" value="${freq}">
                <input type="hidden" name="${prefix}[duration_days]" value="${dur}">
                <input type="hidden" name="${prefix}[override_reason]" value="${override}">
                <input type="hidden" name="${prefix}[notes]" value="${notes}">
            `;
            count++;
        });

        if (!count) {
            e.preventDefault();
            alert('{{ __("Please select at least one drug to prescribe.") }}');
        }
    });
});
</script>
@endpush
@endsection

