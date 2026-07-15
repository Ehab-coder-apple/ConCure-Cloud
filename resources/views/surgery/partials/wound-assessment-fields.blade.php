{{-- Reusable Wound Assessment Fields (shared by operation post-op and follow-up visit forms) --}}
<hr>
<h5>Wound Assessment</h5>

{{-- Wound Information --}}
<h6 class="mt-3">Wound Information</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Date wound developed</label>
        <input type="date" name="wound_assessment[information][date_developed]" class="form-control" value="{{ old('wound_assessment.information.date_developed') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Duration of wound</label>
        <input type="text" name="wound_assessment[information][duration]" class="form-control" value="{{ old('wound_assessment.information.duration') }}" placeholder="e.g. 3 weeks">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Etiology (Cause)</label>
        <input type="text" name="wound_assessment[information][cause]" class="form-control" value="{{ old('wound_assessment.information.cause') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label d-block">Wound type</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][diabetic_foot_ulcer]" value="1" {{ old('wound_assessment.information.diabetic_foot_ulcer') ? 'checked' : '' }}>
            <label class="form-check-label">Diabetic foot ulcer</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][venous_leg_ulcer]" value="1" {{ old('wound_assessment.information.venous_leg_ulcer') ? 'checked' : '' }}>
            <label class="form-check-label">Venous leg ulcer</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][arterial_ulcer]" value="1" {{ old('wound_assessment.information.arterial_ulcer') ? 'checked' : '' }}>
            <label class="form-check-label">Arterial ulcer</label>
        </div>
        <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][surgical_wound]" value="1" {{ old('wound_assessment.information.surgical_wound') ? 'checked' : '' }}>
            <label class="form-check-label">Surgical wound</label>
        </div>
        <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][traumatic_wound]" value="1" {{ old('wound_assessment.information.traumatic_wound') ? 'checked' : '' }}>
            <label class="form-check-label">Traumatic wound</label>
        </div>
        <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="checkbox" name="wound_assessment[information][burn]" value="1" {{ old('wound_assessment.information.burn') ? 'checked' : '' }}>
            <label class="form-check-label">Burn</label>
        </div>
        <input type="text" name="wound_assessment[information][burn_detail]" class="form-control form-control-sm d-inline-block mt-1" style="width: 220px;" placeholder="Burn detail" value="{{ old('wound_assessment.information.burn_detail') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Pressure injury stage (if applicable)</label>
        @php $pressureStageOld = old('wound_assessment.information.pressure_injury_stage'); @endphp
        <select name="wound_assessment[information][pressure_injury_stage]" class="form-select">
            <option value="">-- Select --</option>
            <option value="1" {{ $pressureStageOld === '1' ? 'selected' : '' }}>Stage 1 - Non-blanchable redness, skin intact</option>
            <option value="2" {{ $pressureStageOld === '2' ? 'selected' : '' }}>Stage 2 - Partial-thickness skin loss or blister</option>
            <option value="3" {{ $pressureStageOld === '3' ? 'selected' : '' }}>Stage 3 - Full-thickness skin loss, fat visible</option>
            <option value="4" {{ $pressureStageOld === '4' ? 'selected' : '' }}>Stage 4 - Full-thickness tissue loss, muscle/tendon/bone exposed</option>
            <option value="unstageable" {{ $pressureStageOld === 'unstageable' ? 'selected' : '' }}>Unstageable - Base covered by slough/eschar</option>
            <option value="dtpi" {{ $pressureStageOld === 'dtpi' ? 'selected' : '' }}>DTPI - Deep tissue damage with purple/maroon discoloration</option>
        </select>
        <div class="mt-2">
            <label class="form-label">Anatomical location</label>
            <input type="text" name="wound_assessment[information][anatomical_location]" class="form-control" value="{{ old('wound_assessment.information.anatomical_location') }}">
        </div>
        <div class="mt-2">
            <label class="form-label">Number of wounds</label>
            <input type="number" name="wound_assessment[information][number_of_wounds]" class="form-control" min="0" value="{{ old('wound_assessment.information.number_of_wounds') }}">
        </div>
    </div>
</div>

{{-- Wound Assessment (TIME Framework) --}}
<h6 class="mt-4">Wound Assessment (TIME Framework)</h6>
<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label d-block">Tissue</label>
        @php $tissueOld = old('wound_assessment.time_framework.tissue', []); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][tissue][granulation]" value="1" {{ data_get($tissueOld, 'granulation') ? 'checked' : '' }}>
            <label class="form-check-label">Granulation</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][tissue][slough]" value="1" {{ data_get($tissueOld, 'slough') ? 'checked' : '' }}>
            <label class="form-check-label">Slough</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][tissue][necrotic]" value="1" {{ data_get($tissueOld, 'necrotic') ? 'checked' : '' }}>
            <label class="form-check-label">Necrotic tissue</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][tissue][epithelial]" value="1" {{ data_get($tissueOld, 'epithelial') ? 'checked' : '' }}>
            <label class="form-check-label">Epithelial tissue</label>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label d-block">Infection / Inflammation</label>
        @php $infectionOld = old('wound_assessment.time_framework.infection', []); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][infection][none]" value="1" {{ data_get($infectionOld, 'none') ? 'checked' : '' }}>
            <label class="form-check-label">None</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][infection][local]" value="1" {{ data_get($infectionOld, 'local') ? 'checked' : '' }}>
            <label class="form-check-label">Local infection</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][infection][spreading]" value="1" {{ data_get($infectionOld, 'spreading') ? 'checked' : '' }}>
            <label class="form-check-label">Spreading infection</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][infection][osteomyelitis]" value="1" {{ data_get($infectionOld, 'osteomyelitis') ? 'checked' : '' }}>
            <label class="form-check-label">Osteomyelitis</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][infection][biofilm_suspected]" value="1" {{ data_get($infectionOld, 'biofilm_suspected') ? 'checked' : '' }}>
            <label class="form-check-label">Biofilm suspected</label>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label d-block">Infection signs</label>
        @php $signsOld = old('wound_assessment.time_framework.signs', []); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][redness]" value="1" {{ data_get($signsOld, 'redness') ? 'checked' : '' }}>
            <label class="form-check-label">Redness</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][warmth]" value="1" {{ data_get($signsOld, 'warmth') ? 'checked' : '' }}>
            <label class="form-check-label">Warmth</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][swelling]" value="1" {{ data_get($signsOld, 'swelling') ? 'checked' : '' }}>
            <label class="form-check-label">Swelling</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][pain]" value="1" {{ data_get($signsOld, 'pain') ? 'checked' : '' }}>
            <label class="form-check-label">Pain</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][odor]" value="1" {{ data_get($signsOld, 'odor') ? 'checked' : '' }}>
            <label class="form-check-label">Odor</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][signs][purulent_discharge]" value="1" {{ data_get($signsOld, 'purulent_discharge') ? 'checked' : '' }}>
            <label class="form-check-label">Discharge</label>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">Moisture</label>
        <select name="wound_assessment[time_framework][moisture][level]" class="form-select mb-2">
            @php $moistureOld = old('wound_assessment.time_framework.moisture.level'); @endphp
            <option value="">-- Select --</option>
            <option value="dry" {{ $moistureOld === 'dry' ? 'selected' : '' }}>Dry</option>
            <option value="low" {{ $moistureOld === 'low' ? 'selected' : '' }}>Low exudate</option>
            <option value="moderate" {{ $moistureOld === 'moderate' ? 'selected' : '' }}>Moderate exudate</option>
            <option value="heavy" {{ $moistureOld === 'heavy' ? 'selected' : '' }}>Heavy exudate</option>
        </select>
        <label class="form-label">Exudate type</label>
        @php $exudateOld = old('wound_assessment.time_framework.exudate.type'); @endphp
        <select name="wound_assessment[time_framework][exudate][type]" class="form-select">
            <option value="">-- Select --</option>
            <option value="serous" {{ $exudateOld === 'serous' ? 'selected' : '' }}>Serous</option>
            <option value="sanguineous" {{ $exudateOld === 'sanguineous' ? 'selected' : '' }}>Sanguineous</option>
            <option value="serosanguineous" {{ $exudateOld === 'serosanguineous' ? 'selected' : '' }}>Serosanguineous</option>
            <option value="purulent" {{ $exudateOld === 'purulent' ? 'selected' : '' }}>Purulent</option>
        </select>
    </div>
</div>


<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label d-block">Edge</label>
        @php $edgeOld = old('wound_assessment.time_framework.edge', []); @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][edge][attached]" value="1" {{ data_get($edgeOld, 'attached') ? 'checked' : '' }}>
            <label class="form-check-label">Attached</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][edge][undermining]" value="1" {{ data_get($edgeOld, 'undermining') ? 'checked' : '' }}>
            <label class="form-check-label">Undermining</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][edge][rolled_edge]" value="1" {{ data_get($edgeOld, 'rolled_edge') ? 'checked' : '' }}>
            <label class="form-check-label">Rolled edge (Epibole)</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][edge][macerated]" value="1" {{ data_get($edgeOld, 'macerated') ? 'checked' : '' }}>
            <label class="form-check-label">Macerated</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="wound_assessment[time_framework][edge][hyperkeratosis]" value="1" {{ data_get($edgeOld, 'hyperkeratosis') ? 'checked' : '' }}>
            <label class="form-check-label">Hyperkeratosis</label>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label d-block">Wound Measurements</label>
        <div class="row">
            <div class="col-md-4 mb-2">
                <input type="number" step="0.1" min="0" name="wound_assessment[measurements][length_cm]" class="form-control" placeholder="Length (cm)" value="{{ old('wound_assessment.measurements.length_cm') }}">
            </div>
            <div class="col-md-4 mb-2">
                <input type="number" step="0.1" min="0" name="wound_assessment[measurements][width_cm]" class="form-control" placeholder="Width (cm)" value="{{ old('wound_assessment.measurements.width_cm') }}">
            </div>
            <div class="col-md-4 mb-2">
                <input type="number" step="0.1" min="0" name="wound_assessment[measurements][depth_cm]" class="form-control" placeholder="Depth (cm)" value="{{ old('wound_assessment.measurements.depth_cm') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-2">
                <input type="text" name="wound_assessment[measurements][undermining_clock_position]" class="form-control" placeholder="Undermining (clock)" value="{{ old('wound_assessment.measurements.undermining_clock_position') }}">
            </div>
            <div class="col-md-4 mb-2">
                <input type="text" name="wound_assessment[measurements][tunneling]" class="form-control" placeholder="Tunneling" value="{{ old('wound_assessment.measurements.tunneling') }}">
            </div>
            <div class="col-md-4 mb-2">
                <input type="text" name="wound_assessment[measurements][probe_to_bone_test]" class="form-control" placeholder="Probe-to-bone test" value="{{ old('wound_assessment.measurements.probe_to_bone_test') }}">
            </div>
        </div>
    </div>
</div>

<h6 class="mt-3">Wound Bed Composition (%)</h6>
<div class="row">
    <div class="col-md-3 mb-2">
        <input type="number" step="1" min="0" max="100" name="wound_assessment[bed_composition][granulation_pct]" class="form-control" placeholder="Granulation %" value="{{ old('wound_assessment.bed_composition.granulation_pct') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="number" step="1" min="0" max="100" name="wound_assessment[bed_composition][slough_pct]" class="form-control" placeholder="Slough %" value="{{ old('wound_assessment.bed_composition.slough_pct') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="number" step="1" min="0" max="100" name="wound_assessment[bed_composition][necrosis_pct]" class="form-control" placeholder="Necrosis %" value="{{ old('wound_assessment.bed_composition.necrosis_pct') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="number" step="1" min="0" max="100" name="wound_assessment[bed_composition][epithelial_pct]" class="form-control" placeholder="Epithelial %" value="{{ old('wound_assessment.bed_composition.epithelial_pct') }}">
    </div>
</div>


<h6 class="mt-3">Surrounding Skin</h6>
@php $skinOld = old('wound_assessment.surrounding_skin', []); @endphp
<div class="row">
    <div class="col-md-6 mb-2">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][normal]" value="1" {{ data_get($skinOld, 'normal') ? 'checked' : '' }}>
            <label class="form-check-label">Normal</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][maceration]" value="1" {{ data_get($skinOld, 'maceration') ? 'checked' : '' }}>
            <label class="form-check-label">Maceration</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][erythema]" value="1" {{ data_get($skinOld, 'erythema') ? 'checked' : '' }}>
            <label class="form-check-label">Erythema</label>
        </div>
    </div>
    <div class="col-md-6 mb-2">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][callus]" value="1" {{ data_get($skinOld, 'callus') ? 'checked' : '' }}>
            <label class="form-check-label">Callus</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][edema]" value="1" {{ data_get($skinOld, 'edema') ? 'checked' : '' }}>
            <label class="form-check-label">Edema</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][induration]" value="1" {{ data_get($skinOld, 'induration') ? 'checked' : '' }}>
            <label class="form-check-label">Induration</label>
        </div>
        <div class="form-check form-check-inline mt-1">
            <input class="form-check-input" type="checkbox" name="wound_assessment[surrounding_skin][dry_skin]" value="1" {{ data_get($skinOld, 'dry_skin') ? 'checked' : '' }}>
            <label class="form-check-label">Dry skin</label>
        </div>
    </div>
</div>

<h6 class="mt-3">Pain Assessment</h6>
<div class="row">
    <div class="col-md-3 mb-2">
        <label class="form-label">Pain score (0–10)</label>
        <input type="number" min="0" max="10" name="wound_assessment[pain][score]" class="form-control" value="{{ old('wound_assessment.pain.score') }}">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">At rest</label>
        <input type="text" name="wound_assessment[pain][at_rest]" class="form-control" value="{{ old('wound_assessment.pain.at_rest') }}">
    </div>
    <div class="col-md-5 mb-2">
        <label class="form-label">During dressing change</label>
        <input type="text" name="wound_assessment[pain][during_dressing_change]" class="form-control" value="{{ old('wound_assessment.pain.during_dressing_change') }}">
    </div>
</div>

<h6 class="mt-3">Vascular Assessment</h6>
<div class="row">
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][pedal_pulses]" class="form-control" placeholder="Pedal pulses" value="{{ old('wound_assessment.vascular.pedal_pulses') }}">
    </div>
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][capillary_refill]" class="form-control" placeholder="Capillary refill" value="{{ old('wound_assessment.vascular.capillary_refill') }}">
    </div>
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][abi]" class="form-control" placeholder="ABI" value="{{ old('wound_assessment.vascular.abi') }}">
    </div>
</div>
<div class="row mt-1">
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][tbi]" class="form-control" placeholder="TBI" value="{{ old('wound_assessment.vascular.tbi') }}">
    </div>
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][doppler_findings]" class="form-control" placeholder="Doppler findings" value="{{ old('wound_assessment.vascular.doppler_findings') }}">
    </div>
    <div class="col-md-4 mb-2">
        <input type="text" name="wound_assessment[vascular][skin_temperature]" class="form-control" placeholder="Skin temperature" value="{{ old('wound_assessment.vascular.skin_temperature') }}">
    </div>
</div>

<h6 class="mt-3">Neurological Assessment</h6>
<div class="row">
    <div class="col-md-3 mb-2">
        <input type="text" name="wound_assessment[neurological][monofilament_test]" class="form-control" placeholder="Monofilament test" value="{{ old('wound_assessment.neurological.monofilament_test') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="text" name="wound_assessment[neurological][vibration_sensation]" class="form-control" placeholder="Vibration sensation" value="{{ old('wound_assessment.neurological.vibration_sensation') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="text" name="wound_assessment[neurological][protective_sensation]" class="form-control" placeholder="Protective sensation" value="{{ old('wound_assessment.neurological.protective_sensation') }}">
    </div>
    <div class="col-md-3 mb-2">
        <input type="text" name="wound_assessment[neurological][neuropathy_present]" class="form-control" placeholder="Neuropathy present" value="{{ old('wound_assessment.neurological.neuropathy_present') }}">
    </div>
</div>


<h6 class="mt-3">Classification & WIfI Scale</h6>
<div class="card mb-3 border-secondary">
    <div class="card-header bg-light"><strong>WIfI Scale (Wound, Ischemia, foot Infection)</strong></div>
    <div class="card-body pb-1">
        <div class="row">
            <div class="col-md-3 mb-2">
                <label class="form-label small text-muted">Wound (0-3)</label>
                <select name="wound_assessment[classification][wifi][wound]" class="form-select form-select-sm">
                    <option value="">Select...</option>
                    <option value="0" {{ old('wound_assessment.classification.wifi.wound') === '0' ? 'selected' : '' }}>0 - No ulcer, no gangrene</option>
                    <option value="1" {{ old('wound_assessment.classification.wifi.wound') === '1' ? 'selected' : '' }}>1 - Small, shallow ulcer / minor gangrene</option>
                    <option value="2" {{ old('wound_assessment.classification.wifi.wound') === '2' ? 'selected' : '' }}>2 - Deep ulcer / gangrene of digits</option>
                    <option value="3" {{ old('wound_assessment.classification.wifi.wound') === '3' ? 'selected' : '' }}>3 - Extensive ulcer / extensive gangrene</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small text-muted">Ischemia (0-3)</label>
                <select name="wound_assessment[classification][wifi][ischemia]" class="form-select form-select-sm">
                    <option value="">Select...</option>
                    <option value="0" {{ old('wound_assessment.classification.wifi.ischemia') === '0' ? 'selected' : '' }}>0 - ABI ≥0.80 / TP ≥60</option>
                    <option value="1" {{ old('wound_assessment.classification.wifi.ischemia') === '1' ? 'selected' : '' }}>1 - ABI 0.6-0.79 / TP 40-59</option>
                    <option value="2" {{ old('wound_assessment.classification.wifi.ischemia') === '2' ? 'selected' : '' }}>2 - ABI 0.4-0.59 / TP 30-39</option>
                    <option value="3" {{ old('wound_assessment.classification.wifi.ischemia') === '3' ? 'selected' : '' }}>3 - ABI ≤0.39 / TP <30</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small text-muted">foot Infection (0-3)</label>
                <select name="wound_assessment[classification][wifi][infection]" class="form-select form-select-sm">
                    <option value="">Select...</option>
                    <option value="0" {{ old('wound_assessment.classification.wifi.infection') === '0' ? 'selected' : '' }}>0 - Uninfected</option>
                    <option value="1" {{ old('wound_assessment.classification.wifi.infection') === '1' ? 'selected' : '' }}>1 - Mild (skin/subcut. only)</option>
                    <option value="2" {{ old('wound_assessment.classification.wifi.infection') === '2' ? 'selected' : '' }}>2 - Moderate (erythema >2cm/deeper)</option>
                    <option value="3" {{ old('wound_assessment.classification.wifi.infection') === '3' ? 'selected' : '' }}>3 - Severe (with SIRS)</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label small text-muted fw-bold">Clinical Stage</label>
                <select name="wound_assessment[classification][wifi_stage]" class="form-select form-select-sm border-primary">
                    <option value="">Select Stage...</option>
                    <option value="1" {{ old('wound_assessment.classification.wifi_stage') === '1' ? 'selected' : '' }}>Stage 1 (Very Low Risk)</option>
                    <option value="2" {{ old('wound_assessment.classification.wifi_stage') === '2' ? 'selected' : '' }}>Stage 2 (Low Risk)</option>
                    <option value="3" {{ old('wound_assessment.classification.wifi_stage') === '3' ? 'selected' : '' }}>Stage 3 (Moderate Risk)</option>
                    <option value="4" {{ old('wound_assessment.classification.wifi_stage') === '4' ? 'selected' : '' }}>Stage 4 (High Risk)</option>
                </select>
            </div>
        </div>
    </div>
</div>

<h6 class="mt-3">Laboratory Results</h6>
<div class="row">
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][hba1c]" class="form-control" placeholder="HbA1c" value="{{ old('wound_assessment.laboratory.hba1c') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][fasting_blood_glucose]" class="form-control" placeholder="Fasting blood glucose" value="{{ old('wound_assessment.laboratory.fasting_blood_glucose') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][wbc]" class="form-control" placeholder="WBC" value="{{ old('wound_assessment.laboratory.wbc') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][crp]" class="form-control" placeholder="CRP" value="{{ old('wound_assessment.laboratory.crp') }}"></div>
</div>
<div class="row mt-1">
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][esr]" class="form-control" placeholder="ESR" value="{{ old('wound_assessment.laboratory.esr') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][albumin]" class="form-control" placeholder="Albumin" value="{{ old('wound_assessment.laboratory.albumin') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][hemoglobin]" class="form-control" placeholder="Hemoglobin" value="{{ old('wound_assessment.laboratory.hemoglobin') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][creatinine]" class="form-control" placeholder="Creatinine" value="{{ old('wound_assessment.laboratory.creatinine') }}"></div>
</div>
<div class="row mt-1">
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[laboratory][gfr]" class="form-control" placeholder="GFR" value="{{ old('wound_assessment.laboratory.gfr') }}"></div>
</div>

<h6 class="mt-3">Microbiology</h6>
<div class="row">
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[microbiology][swab_collected]" class="form-control" placeholder="Swab collected" value="{{ old('wound_assessment.microbiology.swab_collected') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[microbiology][tissue_culture]" class="form-control" placeholder="Tissue culture" value="{{ old('wound_assessment.microbiology.tissue_culture') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[microbiology][organism_isolated]" class="form-control" placeholder="Organism isolated" value="{{ old('wound_assessment.microbiology.organism_isolated') }}"></div>
    <div class="col-md-3 mb-2"><input type="text" name="wound_assessment[microbiology][antibiotic_sensitivity]" class="form-control" placeholder="Antibiotic sensitivity" value="{{ old('wound_assessment.microbiology.antibiotic_sensitivity') }}"></div>
</div>


<h6 class="mt-3">Treatment</h6>
@php $treatOld = old('wound_assessment.treatment', []); @endphp
<div class="row">
    <div class="col-md-3 mb-2">
        <strong>Debridement</strong>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][debridement][sharp]" value="1" {{ data_get($treatOld, 'debridement.sharp') ? 'checked' : '' }}><label class="form-check-label">Sharp</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][debridement][surgical]" value="1" {{ data_get($treatOld, 'debridement.surgical') ? 'checked' : '' }}><label class="form-check-label">Surgical</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][debridement][autolytic]" value="1" {{ data_get($treatOld, 'debridement.autolytic') ? 'checked' : '' }}><label class="form-check-label">Autolytic</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][debridement][mechanical]" value="1" {{ data_get($treatOld, 'debridement.mechanical') ? 'checked' : '' }}><label class="form-check-label">Mechanical</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][debridement][enzymatic]" value="1" {{ data_get($treatOld, 'debridement.enzymatic') ? 'checked' : '' }}><label class="form-check-label">Enzymatic</label></div>
    </div>
    <div class="col-md-3 mb-2">
        <strong>Cleansing Solution</strong>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][cleansing][normal_saline]" value="1" {{ data_get($treatOld, 'cleansing.normal_saline') ? 'checked' : '' }}><label class="form-check-label">Normal saline</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][cleansing][hocl]" value="1" {{ data_get($treatOld, 'cleansing.hocl') ? 'checked' : '' }}><label class="form-check-label">Hypochlorous acid (HOCl)</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][cleansing][phmb]" value="1" {{ data_get($treatOld, 'cleansing.phmb') ? 'checked' : '' }}><label class="form-check-label">PHMB</label></div>
        <input type="text" name="wound_assessment[treatment][cleansing][other]" class="form-control mt-1" placeholder="Other" value="{{ data_get($treatOld, 'cleansing.other') }}">
    </div>
    <div class="col-md-3 mb-2">
        <strong>Dressing</strong>
        @php $dressOld = data_get($treatOld, 'dressing', []); @endphp
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][foam]" value="1" {{ data_get($dressOld, 'foam') ? 'checked' : '' }}><label class="form-check-label">Foam</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][alginate]" value="1" {{ data_get($dressOld, 'alginate') ? 'checked' : '' }}><label class="form-check-label">Alginate</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][hydrofiber]" value="1" {{ data_get($dressOld, 'hydrofiber') ? 'checked' : '' }}><label class="form-check-label">Hydrofiber</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][hydrocolloid]" value="1" {{ data_get($dressOld, 'hydrocolloid') ? 'checked' : '' }}><label class="form-check-label">Hydrocolloid</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][silver]" value="1" {{ data_get($dressOld, 'silver') ? 'checked' : '' }}><label class="form-check-label">Silver dressing</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][iodine]" value="1" {{ data_get($dressOld, 'iodine') ? 'checked' : '' }}><label class="form-check-label">Iodine dressing</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][silicone]" value="1" {{ data_get($dressOld, 'silicone') ? 'checked' : '' }}><label class="form-check-label">Silicone dressing</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][contact_layer]" value="1" {{ data_get($dressOld, 'contact_layer') ? 'checked' : '' }}><label class="form-check-label">Contact layer</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][ecm]" value="1" {{ data_get($dressOld, 'ecm') ? 'checked' : '' }}><label class="form-check-label">ECM</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][dressing][hydrogel]" value="1" {{ data_get($dressOld, 'hydrogel') ? 'checked' : '' }}><label class="form-check-label">Hydrogel</label></div>
    </div>
    <div class="col-md-3 mb-2">
        <strong>Advanced Therapy & Offloading</strong>
        @php $advOld = data_get($treatOld, 'advanced', []); $offOld = data_get($treatOld, 'offloading', []); @endphp
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][advanced][npwt]" value="1" {{ data_get($advOld, 'npwt') ? 'checked' : '' }}><label class="form-check-label">NPWT</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][advanced][skin_graft]" value="1" {{ data_get($advOld, 'skin_graft') ? 'checked' : '' }}><label class="form-check-label">Skin graft</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][advanced][flap_surgery]" value="1" {{ data_get($advOld, 'flap_surgery') ? 'checked' : '' }}><label class="form-check-label">Flap surgery</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][advanced][hbot]" value="1" {{ data_get($advOld, 'hbot') ? 'checked' : '' }}><label class="form-check-label">Hyperbaric oxygen therapy</label></div>
        <hr class="my-2">
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][offloading][tcc]" value="1" {{ data_get($offOld, 'tcc') ? 'checked' : '' }}><label class="form-check-label">Total Contact Cast</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][offloading][removable_walker]" value="1" {{ data_get($offOld, 'removable_walker') ? 'checked' : '' }}><label class="form-check-label">Removable Walker</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][offloading][therapeutic_shoes]" value="1" {{ data_get($offOld, 'therapeutic_shoes') ? 'checked' : '' }}><label class="form-check-label">Therapeutic Shoes</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][offloading][wheelchair]" value="1" {{ data_get($offOld, 'wheelchair') ? 'checked' : '' }}><label class="form-check-label">Wheelchair</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[treatment][offloading][crutches]" value="1" {{ data_get($offOld, 'crutches') ? 'checked' : '' }}><label class="form-check-label">Crutches</label></div>
    </div>
</div>

<h6 class="mt-3">Follow-up</h6>
<div class="row">
    <div class="col-md-4 mb-2"><input type="text" name="wound_assessment[followup][dressing_change_frequency]" class="form-control" placeholder="Dressing change frequency" value="{{ old('wound_assessment.followup.dressing_change_frequency') }}"></div>
    <div class="col-md-4 mb-2"><input type="text" name="wound_assessment[followup][weekly_measurements]" class="form-control" placeholder="Weekly wound measurements" value="{{ old('wound_assessment.followup.weekly_measurements') }}"></div>
    <div class="col-md-4 mb-2"><input type="text" name="wound_assessment[followup][photographs]" class="form-control" placeholder="Photographs" value="{{ old('wound_assessment.followup.photographs') }}"></div>
</div>
<div class="row mt-1">
    <div class="col-md-6 mb-2"><input type="text" name="wound_assessment[followup][complications]" class="form-control" placeholder="Complications" value="{{ old('wound_assessment.followup.complications') }}"></div>
    <div class="col-md-6 mb-2"><input type="text" name="wound_assessment[followup][healing_progress]" class="form-control" placeholder="Healing progress" value="{{ old('wound_assessment.followup.healing_progress') }}"></div>
</div>

<h6 class="mt-3">Outcome</h6>
@php $outOld = old('wound_assessment.outcome', []); @endphp
<div class="row">
    <div class="col-md-6 mb-2">
        <input type="text" name="wound_assessment[outcome][summary]" class="form-control mb-2" placeholder="Overall outcome" value="{{ data_get($outOld, 'summary') }}">
        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][completely_healed]" value="1" {{ data_get($outOld, 'completely_healed') ? 'checked' : '' }}><label class="form-check-label">Completely healed</label></div>
        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][improved]" value="1" {{ data_get($outOld, 'improved') ? 'checked' : '' }}><label class="form-check-label">Improved</label></div>
        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][no_change]" value="1" {{ data_get($outOld, 'no_change') ? 'checked' : '' }}><label class="form-check-label">No change</label></div>
        <div class="form-check form-check-inline mt-1"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][deteriorated]" value="1" {{ data_get($outOld, 'deteriorated') ? 'checked' : '' }}><label class="form-check-label">Deteriorated</label></div>
        <div class="form-check form-check-inline mt-1"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][infection_resolved]" value="1" {{ data_get($outOld, 'infection_resolved') ? 'checked' : '' }}><label class="form-check-label">Infection resolved</label></div>
    </div>
    <div class="col-md-6 mb-2">
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][amputation]" value="1" {{ data_get($outOld, 'amputation') ? 'checked' : '' }}><label class="form-check-label">Amputation</label></div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="wound_assessment[outcome][death]" value="1" {{ data_get($outOld, 'death') ? 'checked' : '' }}><label class="form-check-label">Death</label></div>
    </div>
</div>

<h6 class="mt-3">Healing Time</h6>
<div class="row">
    <div class="col-md-4 mb-2">
        <label class="form-label">Date healed</label>
        <input type="date" name="wound_assessment[healing_time][date_healed]" class="form-control" value="{{ old('wound_assessment.healing_time.date_healed') }}">
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Total healing days</label>
        <input type="number" min="0" name="wound_assessment[healing_time][total_healing_days]" class="form-control" value="{{ old('wound_assessment.healing_time.total_healing_days') }}">
    </div>
</div>
