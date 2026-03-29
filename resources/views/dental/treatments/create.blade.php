@extends('layouts.app')

@section('title', __('Create Treatment Plan'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        {{ __('Create Treatment Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Plan a new dental treatment for a patient') }}</p>
                </div>
                <div>
                    <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Treatments') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        {{ __('Treatment Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/dental/treatments') }}" id="treatment-form">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ (old('patient_id', $patient?->id) == $p->id) ? 'selected' : '' }}>
                                        {{ $p->full_name }} ({{ $p->patient_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dental Chart Selection (Optional) -->
                        <div class="mb-3">
                            <label for="dental_chart_id" class="form-label">{{ __('Dental Chart') }}</label>
                            <div class="input-group">
                                <select name="dental_chart_id" id="dental_chart_id" class="form-select">
                                    <option value="">{{ __('No Chart Selected') }}</option>
                                    @if($dentalChart)
                                        <option value="{{ $dentalChart->id }}" selected>
                                            {{ __('Chart') }} #{{ $dentalChart->id }} - {{ $dentalChart->chart_type }} ({{ $dentalChart->created_at->format('M d, Y') }})
                                        </option>
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="create-chart-btn" onclick="showCreateChartModal()">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Create New Chart') }}
                                </button>
                            </div>
                            <small class="text-muted">{{ __('Link this treatment to a specific dental chart or create a new one') }}</small>
                        </div>

                        <!-- Procedure Selection -->
                        <div class="mb-3">
                            <label for="procedure_id" class="form-label">{{ __('Procedure') }} <span class="text-danger">*</span></label>
                            <select name="procedure_id" id="procedure_id" class="form-select @error('procedure_name') is-invalid @enderror" required>
                                <option value="">{{ __('Select Procedure') }}</option>
                                @foreach($procedures->groupBy('category') as $category => $categoryProcedures)
                                    <optgroup label="{{ \App\Models\DentalProcedure::CATEGORIES[$category] ?? $category }}">
                                        @foreach($categoryProcedures as $procedure)
                                            <option value="{{ $procedure->id }}" 
                                                    data-name="{{ $procedure->name }}"
                                                    data-code="{{ $procedure->code }}"
                                                    data-cost="{{ $procedure->default_cost }}"
                                                    data-duration="{{ $procedure->estimated_duration_minutes }}"
                                                    data-description="{{ $procedure->description }}">
                                                {{ $procedure->name }} @if($procedure->code)({{ $procedure->code }})@endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('procedure_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden fields for procedure details -->
                        <input type="hidden" name="procedure_name" id="procedure_name">
                        <input type="hidden" name="procedure_code" id="procedure_code">

                        <!-- Tooth Number(s) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ __('Primary Tooth Number') }}</label>
                                <input type="text" name="tooth_number" id="tooth_number" class="form-control" 
                                       placeholder="{{ __('e.g., 16, 21, 36') }}" value="{{ old('tooth_number') }}">
                                <small class="text-muted">{{ __('FDI tooth numbering system') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_numbers" class="form-label">{{ __('Additional Teeth') }}</label>
                                <input type="text" name="tooth_numbers" id="tooth_numbers" class="form-control" 
                                       placeholder="{{ __('e.g., 16,17,18') }}" value="{{ old('tooth_numbers') }}">
                                <small class="text-muted">{{ __('Comma-separated for multiple teeth') }}</small>
                            </div>
                        </div>

                        <!-- Canal Worksheet (Endodontic) -->
                        <div class="mb-3" id="canalWorksheetSection" style="display:none;">
                            <div class="card border-primary">
                                <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-teeth me-2"></i>{{ __('Canal Worksheet (Endodontic)') }}
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addCustomCanalBtn">
                                        <i class="fas fa-plus me-1"></i>{{ __('Add Canal') }}
                                    </button>
                                </div>
                                <div class="card-body p-0" id="canalWorksheetContainer">
                                    <p class="text-muted p-3 mb-0" id="canalPlaceholder">
                                        <i class="fas fa-info-circle me-1"></i>{{ __('Enter a tooth number above to load standard canal definitions.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Surfaces Affected -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Surfaces Affected') }}</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="O" id="surface_o">
                                    <label class="form-check-label" for="surface_o">{{ __('Occlusal (O)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="M" id="surface_m">
                                    <label class="form-check-label" for="surface_m">{{ __('Mesial (M)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="D" id="surface_d">
                                    <label class="form-check-label" for="surface_d">{{ __('Distal (D)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="B" id="surface_b">
                                    <label class="form-check-label" for="surface_b">{{ __('Buccal (B)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="L" id="surface_l">
                                    <label class="form-check-label" for="surface_l">{{ __('Lingual (L)') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                            <textarea name="diagnosis" id="diagnosis" class="form-control" rows="2"
                                      placeholder="{{ __('Clinical diagnosis') }}">{{ old('diagnosis') }}</textarea>
                        </div>

                        <!-- ICD-10 Code -->
                        <div class="mb-3">
                            <label for="icd10_code" class="form-label">{{ __('ICD-10 Code') }}</label>
                            <input type="text" name="icd10_code" id="icd10_code" class="form-control"
                                   placeholder="{{ __('e.g., K02.9') }}" value="{{ old('icd10_code') }}">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Treatment Description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                      placeholder="{{ __('Detailed treatment plan description') }}">{{ old('description') }}</textarea>
                        </div>

                        <!-- Cost & Duration -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="estimated_cost" class="form-label">{{ __('Estimated Cost') }} <span class="text-danger">*</span></label>
                                <input type="number" name="estimated_cost" id="estimated_cost" class="form-control"
                                       step="0.01" min="0" placeholder="0.00" value="{{ old('estimated_cost') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" id="currency" class="form-select">
                                    <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="IQD" {{ old('currency') == 'IQD' ? 'selected' : '' }}>IQD</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estimated_duration_minutes" class="form-label">{{ __('Duration (minutes)') }}</label>
                                <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes"
                                       class="form-control" min="0" placeholder="60" value="{{ old('estimated_duration_minutes') }}">
                            </div>
                        </div>

                        <!-- Status, Priority, Severity -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="planned" {{ old('status', 'planned') == 'planned' ? 'selected' : '' }}>{{ __('Planned') }}</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="severity" class="form-label">{{ __('Severity') }}</label>
                                <select name="severity" id="severity" class="form-select">
                                    <option value="">{{ __('Not Specified') }}</option>
                                    <option value="mild" {{ old('severity') == 'mild' ? 'selected' : '' }}>{{ __('Mild') }}</option>
                                    <option value="moderate" {{ old('severity') == 'moderate' ? 'selected' : '' }}>{{ __('Moderate') }}</option>
                                    <option value="severe" {{ old('severity') == 'severe' ? 'selected' : '' }}>{{ __('Severe') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Assigned Doctor -->
                        <div class="mb-3">
                            <label for="assigned_doctor_id" class="form-label">{{ __('Assigned Doctor') }}</label>
                            <select name="assigned_doctor_id" id="assigned_doctor_id" class="form-select">
                                <option value="">{{ __('Not Assigned') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('assigned_doctor_id', auth()->user()->role == 'doctor' ? auth()->id() : '') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scheduled Date -->
                        <div class="mb-3">
                            <label for="scheduled_date" class="form-label">{{ __('Scheduled Date') }}</label>
                            <input type="datetime-local" name="scheduled_date" id="scheduled_date" class="form-control"
                                   value="{{ old('scheduled_date') }}">
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                      placeholder="{{ __('Any additional notes or instructions') }}">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Treatment Plan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Help -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Help & Guidelines') }}
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">{{ __('FDI Tooth Numbering') }}</h6>
                    <p class="small text-muted">
                        {{ __('Use the FDI two-digit system:') }}
                    </p>
                    <ul class="small text-muted">
                        <li>{{ __('Adult teeth: 11-18, 21-28, 31-38, 41-48') }}</li>
                        <li>{{ __('Primary teeth: 51-55, 61-65, 71-75, 81-85') }}</li>
                    </ul>

                    <hr>

                    <h6 class="text-primary">{{ __('Tooth Surfaces') }}</h6>
                    <ul class="small text-muted">
                        <li><strong>O</strong> - {{ __('Occlusal (chewing surface)') }}</li>
                        <li><strong>M</strong> - {{ __('Mesial (toward midline)') }}</li>
                        <li><strong>D</strong> - {{ __('Distal (away from midline)') }}</li>
                        <li><strong>B</strong> - {{ __('Buccal/Facial (cheek side)') }}</li>
                        <li><strong>L</strong> - {{ __('Lingual/Palatal (tongue side)') }}</li>
                    </ul>

                    <hr>

                    <h6 class="text-primary">{{ __('Priority Levels') }}</h6>
                    <ul class="small text-muted">
                        <li><strong>{{ __('Urgent') }}</strong> - {{ __('Immediate attention required') }}</li>
                        <li><strong>{{ __('High') }}</strong> - {{ __('Schedule within 1-2 weeks') }}</li>
                        <li><strong>{{ __('Medium') }}</strong> - {{ __('Schedule within 1 month') }}</li>
                        <li><strong>{{ __('Low') }}</strong> - {{ __('Can be scheduled flexibly') }}</li>
                    </ul>

                    <hr>

                    <h6 class="text-primary">{{ __('Canal Name Abbreviations') }}</h6>
                    <p class="small text-muted mb-1">{{ __('Common root canal abbreviations:') }}</p>
                    <table class="table table-sm table-bordered small mb-2">
                        <thead class="table-light">
                            <tr><th>{{ __('Abbr.') }}</th><th>{{ __('Full Name') }}</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>MB</strong></td><td>{{ __('Mesiobuccal') }}</td></tr>
                            <tr><td><strong>MB1</strong></td><td>{{ __('Mesiobuccal (primary)') }}</td></tr>
                            <tr><td><strong>MB2</strong></td><td>{{ __('Mesiobuccal (secondary/accessory)') }}</td></tr>
                            <tr><td><strong>ML</strong></td><td>{{ __('Mesiolingual') }}</td></tr>
                            <tr><td><strong>DB</strong></td><td>{{ __('Distobuccal') }}</td></tr>
                            <tr><td><strong>DL</strong></td><td>{{ __('Distolingual') }}</td></tr>
                            <tr><td><strong>P</strong></td><td>{{ __('Palatal') }}</td></tr>
                            <tr><td><strong>L</strong></td><td>{{ __('Lingual') }}</td></tr>
                            <tr><td><strong>B</strong></td><td>{{ __('Buccal') }}</td></tr>
                            <tr><td><strong>M</strong></td><td>{{ __('Mesial') }}</td></tr>
                            <tr><td><strong>D</strong></td><td>{{ __('Distal') }}</td></tr>
                            <tr><td><strong>C</strong></td><td>{{ __('Central (single canal)') }}</td></tr>
                        </tbody>
                    </table>
                    <p class="small text-muted mb-1">{{ __('Typical canal count by tooth type:') }}</p>
                    <ul class="small text-muted mb-0">
                        <li><strong>{{ __('Incisors') }}</strong> - {{ __('1 canal (C)') }}</li>
                        <li><strong>{{ __('Canines') }}</strong> - {{ __('1 canal (C)') }}</li>
                        <li><strong>{{ __('Upper Premolars') }}</strong> - {{ __('1–2 canals (B, P)') }}</li>
                        <li><strong>{{ __('Lower Premolars') }}</strong> - {{ __('1 canal (C)') }}</li>
                        <li><strong>{{ __('Upper Molars') }}</strong> - {{ __('3–4 canals (MB1, MB2, DB, P)') }}</li>
                        <li><strong>{{ __('Lower Molars') }}</strong> - {{ __('3–4 canals (MB, ML, DB, DL)') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-fill procedure details when selected
document.getElementById('procedure_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];

    if (selectedOption.value) {
        document.getElementById('procedure_name').value = selectedOption.dataset.name || '';
        document.getElementById('procedure_code').value = selectedOption.dataset.code || '';
        document.getElementById('estimated_cost').value = selectedOption.dataset.cost || '';
        document.getElementById('estimated_duration_minutes').value = selectedOption.dataset.duration || '';
        document.getElementById('description').value = selectedOption.dataset.description || '';
    } else {
        document.getElementById('procedure_name').value = '';
        document.getElementById('procedure_code').value = '';
    }
});

// Load dental charts when patient is selected
document.getElementById('patient_id').addEventListener('change', function() {
    const patientId = this.value;

    if (patientId) {
        // Reload page with patient_id parameter to load their dental charts
        window.location.href = '{{ url("/dental/treatments/create") }}?patient_id=' + patientId;
    }
});

// ── Canal Worksheet logic ──────────────────────────────────────────
// Default canal options (fallback if AJAX fails or seeder not run)
let canalOptions = {
    statuses: { not_started: 'Not Started', located: 'Located', instrumented: 'Instrumented', obturated: 'Obturated', completed: 'Completed' },
    maf_sizes: ['08','10','15','20','25','30','35','40','45','50','55','60','70','80'],
    tapers: ['.02','.04','.06','.08'],
    irrigation_protocols: ['NaOCl 2.5%','NaOCl 5.25%','NaOCl 5.25% + EDTA 17%','NaOCl 5.25% + CHX 2%','CHX 2%','EDTA 17%','Saline'],
    obturation_techniques: ['Lateral condensation','Warm vertical condensation','Single cone','Continuous wave','Thermoplasticized injection'],
    sealers: ['AH Plus','BioRoot RCS','TotalFill BC Sealer','Pulp Canal Sealer','Sealapex','EndoSequence BC Sealer']
};

function collectToothNumbers() {
    const teeth = [];
    const primary = document.getElementById('tooth_number').value.trim();
    if (primary) teeth.push(primary);
    const additional = document.getElementById('tooth_numbers').value.trim();
    if (additional) {
        additional.split(',').map(t => t.trim()).filter(Boolean).forEach(t => { if (!teeth.includes(t)) teeth.push(t); });
    }
    return teeth;
}

function onToothNumberChange() {
    const teeth = collectToothNumbers();
    const section = document.getElementById('canalWorksheetSection');
    if (teeth.length === 0) { section.style.display = 'none'; return; }
    section.style.display = 'block';
    loadCanalWorksheet(teeth);
}

document.getElementById('tooth_number').addEventListener('change', onToothNumberChange);
document.getElementById('tooth_number').addEventListener('blur', onToothNumberChange);
document.getElementById('tooth_numbers').addEventListener('change', onToothNumberChange);
document.getElementById('tooth_numbers').addEventListener('blur', onToothNumberChange);

function loadCanalWorksheet(teeth) {
    const container = document.getElementById('canalWorksheetContainer');
    container.innerHTML = '<p class="p-3 mb-0 text-muted"><i class="fas fa-spinner fa-spin me-1"></i>{{ __("Loading canals...") }}</p>';

    const promises = teeth.map(t => fetch(`/dental/canals/standard/${t}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    }).then(r => {
        if (!r.ok || !r.headers.get('content-type')?.includes('json')) {
            return { success: true, tooth_number: t, canals: [], options: null };
        }
        return r.json();
    }).catch(() => ({ success: true, tooth_number: t, canals: [], options: null })));

    Promise.all(promises).then(results => {
        let html = '';
        results.forEach(data => {
            if (data.options && !canalOptions) canalOptions = data.options;
            const tooth = data.tooth_number || data.tooth;
            const canals = data.canals || [];

            // Always build the tooth block — even when no standard canals exist
            html += buildToothBlock(tooth, canals);
        });

        // If no results at all, still create blocks for each tooth
        if (!html) {
            teeth.forEach(tooth => { html += buildToothBlock(tooth, []); });
        }

        container.innerHTML = html;
        bindAddCanalButtons(container);
    }).catch(() => {
        // Fallback: build empty worksheet for manual entry
        let html = '';
        teeth.forEach(tooth => { html += buildToothBlock(tooth, []); });
        container.innerHTML = html;
        bindAddCanalButtons(container);
    });
}

function buildToothBlock(tooth, canals) {
    let html = `<div class="border-bottom p-3 canal-tooth-block" data-tooth="${tooth}">`;
    html += `<h6 class="text-primary mb-2"><i class="fas fa-tooth me-1"></i>{{ __("Tooth") }} #${tooth}</h6>`;
    html += `<div class="table-responsive"><table class="table table-sm mb-0"><thead><tr>
        <th>{{ __("Canal") }}</th><th>{{ __("WL (mm)") }}</th><th>{{ __("MAF") }}</th>
        <th>{{ __("Cone") }}</th><th>{{ __("Taper") }}</th><th>{{ __("Status") }}</th><th>{{ __("Notes") }}</th>
    </tr></thead><tbody>`;

    if (canals.length > 0) {
        canals.forEach(c => { html += buildCreateCanalRow(tooth, c.canal_name, false); });
    } else {
        html += buildCreateCanalRow(tooth, '', true);
    }

    html += `</tbody></table></div>`;
    html += `<button type="button" class="btn btn-sm btn-outline-secondary mt-1 add-create-canal" data-tooth="${tooth}"><i class="fas fa-plus me-1"></i>{{ __("Add Canal") }}</button>`;
    html += `</div>`;
    return html;
}

function bindAddCanalButtons(container) {
    container.querySelectorAll('.add-create-canal').forEach(btn => {
        btn.addEventListener('click', function() {
            const tooth = this.dataset.tooth;
            const tbody = this.closest('.canal-tooth-block').querySelector('tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = buildCreateCanalRowInner(tooth, '', true);
            tbody.appendChild(tr);
        });
    });
}

function buildCreateCanalRow(tooth, canalName, editable) {
    return `<tr>${buildCreateCanalRowInner(tooth, canalName, editable)}</tr>`;
}

function buildCreateCanalRowInner(tooth, canalName, editable) {
    const idx = document.querySelectorAll(`input[name^="canals["]`).length / 7 + Math.random();
    const i = Math.floor(idx);
    const opts = canalOptions || {};
    return `
        <td>${editable ? `<input type="text" class="form-control form-control-sm" name="canals[${i}][canal_name]" value="${canalName}" placeholder="{{ __('Canal name') }}">` : `<strong>${canalName}</strong><input type="hidden" name="canals[${i}][canal_name]" value="${canalName}">`}
            <input type="hidden" name="canals[${i}][tooth_number]" value="${tooth}">
        </td>
        <td><input type="number" class="form-control form-control-sm" name="canals[${i}][working_length]" step="0.5" min="0" max="50" placeholder="mm"></td>
        <td>${buildCreateSelect(`canals[${i}][master_apical_file]`, opts.maf_sizes || [], '', '{{ __("MAF") }}')}</td>
        <td>${buildCreateSelect(`canals[${i}][master_cone_size]`, opts.maf_sizes || [], '', '{{ __("Cone") }}')}</td>
        <td>${buildCreateSelect(`canals[${i}][taper]`, opts.tapers || [], '', '{{ __("Taper") }}')}</td>
        <td>${buildCreateSelect(`canals[${i}][status]`, opts.statuses || {}, 'not_started', '{{ __("Status") }}')}</td>
        <td><input type="text" class="form-control form-control-sm" name="canals[${i}][notes]" placeholder="{{ __('Notes') }}"></td>
    `;
}

function buildCreateSelect(name, options, selected, placeholder) {
    let html = `<select name="${name}" class="form-select form-select-sm">`;
    html += `<option value="">${placeholder}</option>`;
    if (Array.isArray(options)) {
        options.forEach(o => { html += `<option value="${o}" ${o === selected ? 'selected' : ''}>${o}</option>`; });
    } else {
        for (const [k, v] of Object.entries(options)) {
            html += `<option value="${k}" ${k === selected ? 'selected' : ''}>${v}</option>`;
        }
    }
    html += `</select>`;
    return html;
}

document.getElementById('addCustomCanalBtn').addEventListener('click', function() {
    const teeth = collectToothNumbers();
    if (teeth.length === 0) { alert('{{ __("Please enter a tooth number first.") }}'); return; }
    const tooth = teeth[0];
    let block = document.querySelector(`.canal-tooth-block[data-tooth="${tooth}"]`);
    const container = document.getElementById('canalWorksheetContainer');

    // If no block exists yet, create one
    if (!block) {
        container.innerHTML = buildToothBlock(tooth, []);
        bindAddCanalButtons(container);
        block = container.querySelector(`.canal-tooth-block[data-tooth="${tooth}"]`);
    }

    if (block) {
        const tbody = block.querySelector('tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = buildCreateCanalRowInner(tooth, '', true);
        tbody.appendChild(tr);
    }
});

// Show create chart modal
function showCreateChartModal() {
    const patientId = document.getElementById('patient_id').value;

    if (!patientId) {
        alert('{{ __("Please select a patient first") }}');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('createChartModal'));
    modal.show();
}

// Create dental chart via AJAX
function createDentalChart() {
    const patientId = document.getElementById('patient_id').value;
    const chartType = document.getElementById('new_chart_type').value;
    const generalNotes = document.getElementById('new_chart_notes').value;

    if (!patientId) {
        alert('{{ __("Please select a patient first") }}');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('create-chart-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Creating...") }}';

    // Send AJAX request
    fetch(`/dental/patients/${patientId}/charts`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            chart_type: chartType,
            general_notes: generalNotes
        })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Add new chart to dropdown
            const select = document.getElementById('dental_chart_id');
            const option = document.createElement('option');
            option.value = data.chart.id;
            option.text = `Chart #${data.chart.id} - ${data.chart.chart_type} (${data.chart.created_at})`;
            option.selected = true;
            select.appendChild(option);

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('createChartModal')).hide();

            // Reset form
            document.getElementById('new_chart_type').value = 'adult';
            document.getElementById('new_chart_notes').value = '';

            // Show success message
            alert('{{ __("Dental chart created successfully!") }}');
        } else {
            alert('{{ __("Error creating dental chart: ") }}' + (data.message || '{{ __("Unknown error") }}'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("Error creating dental chart: ") }}' + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}
</script>
@endpush

<!-- Create Dental Chart Modal -->
<div class="modal fade" id="createChartModal" tabindex="-1" aria-labelledby="createChartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createChartModalLabel">
                    <i class="fas fa-tooth me-2"></i>
                    {{ __('Create New Dental Chart') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="new_chart_type" class="form-label">{{ __('Chart Type') }} <span class="text-danger">*</span></label>
                    <select id="new_chart_type" class="form-select" required>
                        <option value="adult" selected>{{ __('Adult (Permanent Dentition)') }}</option>
                        <option value="pediatric">{{ __('Pediatric (Primary Dentition)') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Select adult for permanent teeth or pediatric for primary teeth') }}</small>
                </div>
                <div class="mb-3">
                    <label for="new_chart_notes" class="form-label">{{ __('General Notes') }}</label>
                    <textarea id="new_chart_notes" class="form-control" rows="3" placeholder="{{ __('Optional notes about this dental chart') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="create-chart-submit-btn" onclick="createDentalChart()">
                    <i class="fas fa-save me-1"></i>
                    {{ __('Create Chart') }}
                </button>
            </div>
        </div>
    </div>
</div>

