@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-3">Surgical Case #{{ $surgicalCase->id }}</h1>

    <p><strong>Patient:</strong> {{ $surgicalCase->patient->first_name }} {{ $surgicalCase->patient->last_name }}</p>
    <p><strong>Primary Surgeon:</strong> {{ optional($surgicalCase->primarySurgeon)->first_name }} {{ optional($surgicalCase->primarySurgeon)->last_name }}</p>
    <p><strong>Diagnosis:</strong> {{ $surgicalCase->diagnosis }}</p>
    <p><strong>Planned Procedure:</strong> {{ $surgicalCase->planned_procedure }}</p>
    <p><strong>Status:</strong> <span class="badge bg-secondary text-uppercase">{{ $surgicalCase->status }}</span></p>

    <div class="mb-3">
        <a href="{{ route('surgery.edit', $surgicalCase) }}" class="btn btn-outline-secondary">Edit Case</a>
        <a href="{{ route('surgery.operations.create', $surgicalCase) }}" class="btn btn-primary">Record Operation</a>
        <a href="{{ route('surgery.visit.create', $surgicalCase) }}" class="btn btn-success">Add Follow-up Visit</a>
        <a href="{{ route('surgery.index') }}" class="btn btn-link">Back to list</a>
    </div>

	    {{-- Healing Progress Monitor: chronological comparison across all visits --}}
	    @if($surgicalCase->visits->count() > 0)
	        <hr>
	        <h3 class="mb-3">Healing Progress Monitor</h3>
	        <div class="table-responsive mb-4">
	            <table class="table table-sm table-bordered align-middle text-center">
	                <thead class="table-light">
	                    <tr>
	                        <th rowspan="2" class="align-middle">Visit</th>
	                        <th rowspan="2" class="align-middle">Wound Status</th>
	                        <th colspan="3">Dimensions (cm)</th>
	                        <th colspan="4">Wound Bed Composition (%)</th>
	                    </tr>
	                    <tr>
	                        <th>Length</th>
	                        <th>Width</th>
	                        <th>Depth</th>
	                        <th>Granulation</th>
	                        <th>Slough</th>
	                        <th>Necrosis</th>
	                        <th>Epithelial</th>
	                    </tr>
	                </thead>
	                <tbody>
	                    @foreach($surgicalCase->visits->sortBy('visit_date') as $progressVisit)
	                        @php
	                            $pWound = $progressVisit->wound_assessment;
	                            $pMeasure = data_get($pWound, 'measurements');
	                            $pBed = data_get($pWound, 'bed_composition');
	                        @endphp
	                        <tr>
	                            <td class="text-start">
	                                <strong>Visit #{{ $progressVisit->visit_number }}</strong><br>
	                                <span class="text-muted">{{ optional($progressVisit->visit_date)->format('M d, Y') }}</span>
	                            </td>
	                            <td>
	                                @if($progressVisit->wound_status)
	                                    <span class="badge bg-{{ $progressVisit->wound_status === 'healing_well' ? 'success' : ($progressVisit->wound_status === 'infected' ? 'danger' : 'warning') }}">
	                                        {{ ucfirst(str_replace('_', ' ', $progressVisit->wound_status)) }}
	                                    </span>
	                                @else
	                                    -
	                                @endif
	                            </td>
	                            <td>{{ data_get($pMeasure, 'length_cm') ?? '-' }}</td>
	                            <td>{{ data_get($pMeasure, 'width_cm') ?? '-' }}</td>
	                            <td>{{ data_get($pMeasure, 'depth_cm') ?? '-' }}</td>
	                            <td>{{ data_get($pBed, 'granulation_pct') !== null ? data_get($pBed, 'granulation_pct') . '%' : '-' }}</td>
	                            <td>{{ data_get($pBed, 'slough_pct') !== null ? data_get($pBed, 'slough_pct') . '%' : '-' }}</td>
	                            <td>{{ data_get($pBed, 'necrosis_pct') !== null ? data_get($pBed, 'necrosis_pct') . '%' : '-' }}</td>
	                            <td>{{ data_get($pBed, 'epithelial_pct') !== null ? data_get($pBed, 'epithelial_pct') . '%' : '-' }}</td>
	                        </tr>
	                    @endforeach
	                </tbody>
	            </table>
	        </div>

	        <h3 class="mb-3">Follow-up Visits <span class="badge bg-secondary">{{ $surgicalCase->visits->count() }}</span></h3>
	        <div class="accordion mb-4" id="visitsAccordion">
	            @foreach($surgicalCase->visits->sortByDesc('visit_date') as $visit)
	                @php $vWound = $visit->wound_assessment; @endphp
	                <div class="accordion-item">
	                    <h2 class="accordion-header">
	                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#visit-{{ $visit->id }}">
	                            Visit #{{ $visit->visit_number }} — {{ optional($visit->visit_date)->format('M d, Y') }}
	                            @if($visit->wound_status)
	                                <span class="badge ms-2 bg-{{ $visit->wound_status === 'healing_well' ? 'success' : ($visit->wound_status === 'infected' ? 'danger' : 'warning') }}">
	                                    {{ ucfirst(str_replace('_', ' ', $visit->wound_status)) }}
	                                </span>
	                            @endif
	                        </button>
	                    </h2>
	                    <div id="visit-{{ $visit->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#visitsAccordion">
	                        <div class="accordion-body">
	                            <div class="mb-2 text-end">
	                                <a href="{{ route('surgery.visit.edit', [$surgicalCase, $visit]) }}" class="btn btn-sm btn-outline-primary">Edit Visit</a>
	                            </div>
	                            @if($visit->clinical_observations)
	                                <p><strong>Clinical Observations:</strong> {{ $visit->clinical_observations }}</p>
	                            @endif
	                            @if($visit->medications_prescribed)
	                                <p><strong>Medications Prescribed:</strong> {{ $visit->medications_prescribed }}</p>
	                            @endif

	                            @if($vWound)
	                                {{-- Wound Information --}}
	                                @if($vInfo = data_get($vWound, 'information'))
	                                    @php
	                                        $vTypes = [];
	                                        if(data_get($vInfo, 'diabetic_foot_ulcer')) $vTypes[] = 'Diabetic foot ulcer';
	                                        if(data_get($vInfo, 'venous_leg_ulcer')) $vTypes[] = 'Venous leg ulcer';
	                                        if(data_get($vInfo, 'arterial_ulcer')) $vTypes[] = 'Arterial ulcer';
	                                        if(data_get($vInfo, 'surgical_wound')) $vTypes[] = 'Surgical wound';
	                                        if(data_get($vInfo, 'traumatic_wound')) $vTypes[] = 'Traumatic wound';
	                                        if(data_get($vInfo, 'burn')) $vTypes[] = 'Burn' . (data_get($vInfo, 'burn_detail') ? ' (' . data_get($vInfo, 'burn_detail') . ')' : '');
	                                        $vPressureStageLabels = [
	                                            '1' => 'Stage 1 - Non-blanchable redness, skin intact',
	                                            '2' => 'Stage 2 - Partial-thickness skin loss or blister',
	                                            '3' => 'Stage 3 - Full-thickness skin loss, fat visible',
	                                            '4' => 'Stage 4 - Full-thickness tissue loss, muscle/tendon/bone exposed',
	                                            'unstageable' => 'Unstageable - Base covered by slough/eschar',
	                                            'dtpi' => 'DTPI - Deep tissue damage with purple/maroon discoloration',
	                                        ];
	                                        $vPressureStageValue = data_get($vInfo, 'pressure_injury_stage');
	                                    @endphp
	                                    <h6 class="mt-2">Wound Information</h6>
	                                    <p><strong>Wound types:</strong> {{ $vTypes ? implode(', ', $vTypes) : '-' }}</p>
	                                    <p><strong>Pressure injury stage:</strong> {{ $vPressureStageValue ? ($vPressureStageLabels[$vPressureStageValue] ?? $vPressureStageValue) : '-' }}</p>
	                                    <p><strong>Anatomical location:</strong> {{ data_get($vInfo, 'anatomical_location') ?: '-' }}
	                                        &nbsp;|&nbsp; <strong>Number of wounds:</strong> {{ data_get($vInfo, 'number_of_wounds') ?? '-' }}
	                                    </p>
	                                @endif

	                                @if($measure = data_get($vWound, 'measurements'))
	                                    <h6 class="mt-2">Wound Measurements</h6>
	                                    <p>
	                                        <strong>Size:</strong> {{ data_get($measure, 'length_cm') }} x {{ data_get($measure, 'width_cm') }} x {{ data_get($measure, 'depth_cm') }} cm
	                                    </p>
	                                @endif

	                                @if($bed = data_get($vWound, 'bed_composition'))
	                                    <p><strong>Wound Bed:</strong>
	                                        Granulation {{ data_get($bed, 'granulation_pct') }}%,
	                                        Slough {{ data_get($bed, 'slough_pct') }}%,
	                                        Necrosis {{ data_get($bed, 'necrosis_pct') }}%,
	                                        Epithelial {{ data_get($bed, 'epithelial_pct') }}%
	                                    </p>
	                                @endif

	                                @if($time = data_get($vWound, 'time_framework'))
	                                    @php
	                                        $tissue = [];
	                                        foreach (['granulation' => 'Granulation', 'slough' => 'Slough', 'necrotic' => 'Necrotic', 'epithelial' => 'Epithelial'] as $key => $label) {
	                                            if (data_get($time, 'tissue.' . $key)) $tissue[] = $label;
	                                        }
	                                        $infection = [];
	                                        $infMap = ['none' => 'None', 'local' => 'Local infection', 'spreading' => 'Spreading infection', 'osteomyelitis' => 'Osteomyelitis', 'biofilm_suspected' => 'Biofilm suspected'];
	                                        foreach ($infMap as $key => $label) {
	                                            if (data_get($time, 'infection.' . $key)) $infection[] = $label;
	                                        }
	                                        $edges = [];
	                                        $edgeMap = ['attached' => 'Attached', 'undermining' => 'Undermining', 'rolled_edge' => 'Rolled edge (Epibole)', 'macerated' => 'Macerated', 'hyperkeratosis' => 'Hyperkeratosis'];
	                                        foreach ($edgeMap as $key => $label) {
	                                            if (data_get($time, 'edge.' . $key)) $edges[] = $label;
	                                        }
	                                    @endphp
	                                    <p><strong>Tissue:</strong> {{ $tissue ? implode(', ', $tissue) : '-' }}
	                                        &nbsp;|&nbsp; <strong>Infection/Inflammation:</strong> {{ $infection ? implode(', ', $infection) : '-' }}
	                                    </p>
	                                    <p><strong>Moisture:</strong> {{ data_get($time, 'moisture.level') ?: '-' }}
	                                        &nbsp;|&nbsp; <strong>Exudate type:</strong> {{ data_get($time, 'exudate.type') ?: '-' }}
	                                    </p>
	                                    <p><strong>Edge:</strong> {{ $edges ? implode(', ', $edges) : '-' }}</p>
	                                @endif

	                                @if($skin = data_get($vWound, 'surrounding_skin'))
	                                    @php
	                                        $skinLabels = ['normal' => 'Normal', 'maceration' => 'Maceration', 'erythema' => 'Erythema', 'callus' => 'Callus', 'edema' => 'Edema', 'induration' => 'Induration', 'dry_skin' => 'Dry skin'];
	                                        $skinOut = [];
	                                        foreach ($skinLabels as $key => $label) {
	                                            if (data_get($skin, $key)) $skinOut[] = $label;
	                                        }
	                                    @endphp
	                                    <p><strong>Surrounding Skin:</strong> {{ $skinOut ? implode(', ', $skinOut) : '-' }}</p>
	                                @endif

	                                @if($pain = data_get($vWound, 'pain'))
	                                    <p><strong>Pain:</strong> Score {{ data_get($pain, 'score') ?? '-' }},
	                                        At rest: {{ data_get($pain, 'at_rest') ?: '-' }},
	                                        During dressing change: {{ data_get($pain, 'during_dressing_change') ?: '-' }}
	                                    </p>
	                                @endif

	                                @if($neuro = data_get($vWound, 'neurological'))
	                                    <p><strong>Neurological:</strong>
	                                        Monofilament: {{ data_get($neuro, 'monofilament_test') ?: '-' }},
	                                        Vibration: {{ data_get($neuro, 'vibration_sensation') ?: '-' }},
	                                        Protective sensation: {{ data_get($neuro, 'protective_sensation') ?: '-' }},
	                                        Neuropathy: {{ data_get($neuro, 'neuropathy_present') ?: '-' }}
	                                    </p>
	                                @endif

	                                @if($class = data_get($vWound, 'classification'))
	                                    @if(data_get($class, 'wifi_stage'))
	                                        <p><strong>WIfI Clinical Stage:</strong> {{ data_get($class, 'wifi_stage') }}</p>
	                                    @endif
	                                @endif

	                                @if($lab = data_get($vWound, 'laboratory'))
	                                    <h6 class="mt-2">Laboratory Results</h6>
	                                    <p>
	                                        HbA1c {{ data_get($lab, 'hba1c') }},
	                                        FBG {{ data_get($lab, 'fasting_blood_glucose') }},
	                                        WBC {{ data_get($lab, 'wbc') }},
	                                        CRP {{ data_get($lab, 'crp') }},
	                                        ESR {{ data_get($lab, 'esr') }}
	                                    </p>
	                                    <p>
	                                        Albumin {{ data_get($lab, 'albumin') }},
	                                        Hb {{ data_get($lab, 'hemoglobin') }},
	                                        Creatinine {{ data_get($lab, 'creatinine') }},
	                                        GFR {{ data_get($lab, 'gfr') }}
	                                    </p>
	                                @endif

	                                @if($micro = data_get($vWound, 'microbiology'))
	                                    <h6 class="mt-2">Microbiology</h6>
	                                    <p><strong>Swab collected:</strong> {{ data_get($micro, 'swab_collected') ?: '-' }}
	                                        &nbsp;|&nbsp; <strong>Tissue culture:</strong> {{ data_get($micro, 'tissue_culture') ?: '-' }}
	                                    </p>
	                                    <p><strong>Organism isolated:</strong> {{ data_get($micro, 'organism_isolated') ?: '-' }}
	                                        &nbsp;|&nbsp; <strong>Antibiotic sensitivity:</strong> {{ data_get($micro, 'antibiotic_sensitivity') ?: '-' }}
	                                    </p>
	                                @endif

	                                @if($treat = data_get($vWound, 'treatment'))
	                                    @php
	                                        $debMap = ['sharp' => 'Sharp', 'surgical' => 'Surgical', 'autolytic' => 'Autolytic', 'mechanical' => 'Mechanical', 'enzymatic' => 'Enzymatic'];
	                                        $deb = [];
	                                        foreach ($debMap as $key => $label) {
	                                            if (data_get($treat, 'debridement.' . $key)) $deb[] = $label;
	                                        }
	                                        $cleanMap = ['normal_saline' => 'Normal saline', 'hocl' => 'HOCl', 'phmb' => 'PHMB'];
	                                        $clean = [];
	                                        foreach ($cleanMap as $key => $label) {
	                                            if (data_get($treat, 'cleansing.' . $key)) $clean[] = $label;
	                                        }
	                                        if ($other = data_get($treat, 'cleansing.other')) $clean[] = $other;
	                                        $dressLabels = ['foam' => 'Foam', 'alginate' => 'Alginate', 'hydrofiber' => 'Hydrofiber', 'hydrocolloid' => 'Hydrocolloid', 'silver' => 'Silver', 'iodine' => 'Iodine', 'silicone' => 'Silicone', 'contact_layer' => 'Contact layer', 'ecm' => 'ECM', 'hydrogel' => 'Hydrogel'];
	                                        $dressOut = [];
	                                        foreach ($dressLabels as $key => $label) {
	                                            if (data_get($treat, 'dressing.' . $key)) $dressOut[] = $label;
	                                        }
	                                        $advLabels = ['npwt' => 'NPWT', 'skin_graft' => 'Skin graft', 'flap_surgery' => 'Flap surgery', 'hbot' => 'Hyperbaric oxygen therapy'];
	                                        $advOut = [];
	                                        foreach ($advLabels as $key => $label) {
	                                            if (data_get($treat, 'advanced.' . $key)) $advOut[] = $label;
	                                        }
	                                        $offLabels = ['tcc' => 'Total Contact Cast', 'removable_walker' => 'Removable walker', 'therapeutic_shoes' => 'Therapeutic shoes', 'wheelchair' => 'Wheelchair', 'crutches' => 'Crutches'];
	                                        $offOut = [];
	                                        foreach ($offLabels as $key => $label) {
	                                            if (data_get($treat, 'offloading.' . $key)) $offOut[] = $label;
	                                        }
	                                    @endphp
	                                    <h6 class="mt-2">Treatment</h6>
	                                    <p><strong>Debridement:</strong> {{ $deb ? implode(', ', $deb) : '-' }}
	                                        &nbsp;|&nbsp; <strong>Cleansing:</strong> {{ $clean ? implode(', ', $clean) : '-' }}
	                                    </p>
	                                    <p><strong>Dressing:</strong> {{ $dressOut ? implode(', ', $dressOut) : '-' }}</p>
	                                    <p><strong>Advanced therapy:</strong> {{ $advOut ? implode(', ', $advOut) : '-' }}
	                                        &nbsp;|&nbsp; <strong>Offloading:</strong> {{ $offOut ? implode(', ', $offOut) : '-' }}
	                                    </p>
	                                @endif

	                                @if($follow = data_get($vWound, 'followup'))
	                                    <h6 class="mt-2">Follow-up</h6>
	                                    <p><strong>Dressing change frequency:</strong> {{ data_get($follow, 'dressing_change_frequency') ?: '-' }}</p>
	                                    <p><strong>Healing progress:</strong> {{ data_get($follow, 'healing_progress') ?: '-' }}
	                                        &nbsp;|&nbsp; <strong>Complications:</strong> {{ data_get($follow, 'complications') ?: '-' }}
	                                    </p>
	                                @endif

	                                @if($outcome = data_get($vWound, 'outcome'))
	                                    @php
	                                        $outLabels = [
	                                            'completely_healed' => 'Completely healed',
	                                            'improved' => 'Improved',
	                                            'no_change' => 'No change',
	                                            'deteriorated' => 'Deteriorated',
	                                            'infection_resolved' => 'Infection resolved',
	                                        ];
	                                        $outOut = [];
	                                        foreach ($outLabels as $key => $label) {
	                                            if (data_get($outcome, $key)) $outOut[] = $label;
	                                        }
	                                    @endphp
	                                    @if($outOut || data_get($outcome, 'summary'))
	                                        <p><strong>Outcome:</strong> {{ data_get($outcome, 'summary') }} {{ $outOut ? '(' . implode(', ', $outOut) . ')' : '' }}</p>
	                                    @endif
	                                @endif
	                            @endif

	                            @if($visit->notes)
	                                <p class="mb-0"><strong>Notes:</strong> {{ $visit->notes }}</p>
	                            @endif
	                        </div>
	                    </div>
	                </div>
	            @endforeach
	        </div>
	    @endif

	    @if($latestOperation)
	        @php
	            $wound = data_get($latestOperation->postop_assessment, 'wound_assessment');
	        @endphp
	        <hr>
	        <ul class="nav nav-tabs" role="tablist">
	            <li class="nav-item" role="presentation">
	                <button class="nav-link active" id="op-tab" data-bs-toggle="tab" data-bs-target="#op-pane" type="button" role="tab" aria-controls="op-pane" aria-selected="true">
	                    Operation
	                </button>
	            </li>
	            <li class="nav-item" role="presentation">
	                <button class="nav-link" id="wound-tab" data-bs-toggle="tab" data-bs-target="#wound-pane" type="button" role="tab" aria-controls="wound-pane" aria-selected="false">
	                    Wound
	                </button>
	            </li>
	        </ul>

	        <div class="tab-content pt-3">
	            <div class="tab-pane fade show active" id="op-pane" role="tabpanel" aria-labelledby="op-tab">
	                <div class="d-flex justify-content-between align-items-center">
	                    <h3>Latest Operation</h3>
	                    <a href="{{ route('surgery.operations.edit', [$surgicalCase, $latestOperation]) }}" class="btn btn-sm btn-outline-primary">Edit Operation</a>
	                </div>
	                <p><strong>Date:</strong> {{ optional($latestOperation->operation_date)->format('Y-m-d H:i') }}</p>
	                <p><strong>Theatre:</strong> {{ $latestOperation->theatre }}</p>
	                <p><strong>ASA Class:</strong> {{ $latestOperation->asa_class }}</p>
	                <p><strong>Anesthesia:</strong> {{ $latestOperation->anesthesia_type }}</p>

	                <h5 class="mt-3">Pre-op Assessment</h5>
	                <p>{{ data_get($latestOperation->preop_assessment, 'vitals_and_risk') }}</p>
	                <p>{{ data_get($latestOperation->preop_assessment, 'notes') }}</p>

	                <h5 class="mt-3">Operative Note</h5>
	                <p>{{ $latestOperation->operative_note }}</p>

	                <h5 class="mt-3">Post-op Assessment</h5>
	                <p>{{ data_get($latestOperation->postop_assessment, 'status') }}</p>
	                <p>{{ data_get($latestOperation->postop_assessment, 'plan') }}</p>

	                <p><strong>Complications:</strong> {{ $latestOperation->complications }}</p>
	                <p><strong>Estimated Blood Loss:</strong> {{ $latestOperation->estimated_blood_loss_ml }} ml</p>
	            </div>

	            <div class="tab-pane fade" id="wound-pane" role="tabpanel" aria-labelledby="wound-tab">
	                @if($wound)
	                    <h3>Wound Assessment</h3>

	                    {{-- Wound Information --}}
	                    @if($info = data_get($wound, 'information'))
	                        <h6>Wound Information</h6>
	                        <p><strong>Date developed:</strong> {{ data_get($info, 'date_developed') }}</p>
	                        <p><strong>Duration:</strong> {{ data_get($info, 'duration') }}</p>
	                        <p><strong>Etiology (Cause):</strong> {{ data_get($info, 'cause') }}</p>
	                        @php
	                            $pressureStageLabels = [
	                                '1' => 'Stage 1 - Non-blanchable redness, skin intact',
	                                '2' => 'Stage 2 - Partial-thickness skin loss or blister',
	                                '3' => 'Stage 3 - Full-thickness skin loss, fat visible',
	                                '4' => 'Stage 4 - Full-thickness tissue loss, muscle/tendon/bone exposed',
	                                'unstageable' => 'Unstageable - Base covered by slough/eschar',
	                                'dtpi' => 'DTPI - Deep tissue damage with purple/maroon discoloration',
	                            ];
	                            $pressureStageValue = data_get($info, 'pressure_injury_stage');
	                        @endphp
	                        <p><strong>Pressure injury stage:</strong> {{ $pressureStageValue ? ($pressureStageLabels[$pressureStageValue] ?? $pressureStageValue) : '-' }}</p>
	                        <p><strong>Anatomical location:</strong> {{ data_get($info, 'anatomical_location') }}</p>
	                        <p><strong>Number of wounds:</strong> {{ data_get($info, 'number_of_wounds') }}</p>
	                        <p><strong>Wound types:</strong>
	                            @php
	                                $types = [];
	                                if(data_get($info, 'diabetic_foot_ulcer')) $types[] = 'Diabetic foot ulcer';
	                                if(data_get($info, 'venous_leg_ulcer')) $types[] = 'Venous leg ulcer';
	                                if(data_get($info, 'arterial_ulcer')) $types[] = 'Arterial ulcer';
	                                if(data_get($info, 'surgical_wound')) $types[] = 'Surgical wound';
	                                if(data_get($info, 'traumatic_wound')) $types[] = 'Traumatic wound';
	                                if(data_get($info, 'burn')) $types[] = 'Burn' . (data_get($info, 'burn_detail') ? ' (' . data_get($info, 'burn_detail') . ')' : '');
	                            @endphp
	                            {{ $types ? implode(', ', $types) : '-' }}
	                        </p>
	                    @endif

	                    {{-- TIME Framework & Measurements (summary) --}}
	                    @if($time = data_get($wound, 'time_framework'))
	                        <h6 class="mt-2">TIME Framework</h6>
	                        <p><strong>Tissue:</strong>
	                            @php
	                                $tissue = [];
	                                foreach (['granulation' => 'Granulation', 'slough' => 'Slough', 'necrotic' => 'Necrotic', 'epithelial' => 'Epithelial'] as $key => $label) {
	                                    if(data_get($time, 'tissue.' . $key)) $tissue[] = $label;
	                                }
	                            @endphp
	                            {{ $tissue ? implode(', ', $tissue) : '-' }}
	                        </p>
	                        <p><strong>Infection/Inflammation:</strong>
	                            @php
	                                $infection = [];
	                                $map = [
	                                    'none' => 'None',
	                                    'local' => 'Local infection',
	                                    'spreading' => 'Spreading infection',
	                                    'osteomyelitis' => 'Osteomyelitis',
	                                    'biofilm_suspected' => 'Biofilm suspected',
	                                ];
	                                foreach ($map as $key => $label) {
	                                    if(data_get($time, 'infection.' . $key)) $infection[] = $label;
	                                }
	                            @endphp
	                            {{ $infection ? implode(', ', $infection) : '-' }}
	                        </p>
	                        <p><strong>Infection signs:</strong>
	                            @php
	                                $signs = [];
	                                $signMap = [
	                                    'redness' => 'Redness',
	                                    'warmth' => 'Warmth',
	                                    'swelling' => 'Swelling',
	                                    'pain' => 'Pain',
	                                    'odor' => 'Odor',
	                                    'purulent_discharge' => 'Discharge',
	                                ];
	                                foreach ($signMap as $key => $label) {
	                                    if(data_get($time, 'signs.' . $key)) $signs[] = $label;
	                                }
	                            @endphp
	                            {{ $signs ? implode(', ', $signs) : '-' }}
	                        </p>
	                        <p><strong>Moisture:</strong> {{ data_get($time, 'moisture.level') ?: '-' }}</p>
	                        <p><strong>Exudate type:</strong> {{ data_get($time, 'exudate.type') ?: '-' }}</p>
	                        <p><strong>Edge:</strong>
	                            @php
	                                $edges = [];
	                                $edgeMap = [
	                                    'attached' => 'Attached',
	                                    'undermining' => 'Undermining',
	                                    'rolled_edge' => 'Rolled edge (Epibole)',
	                                    'macerated' => 'Macerated',
	                                    'hyperkeratosis' => 'Hyperkeratosis',
	                                ];
	                                foreach ($edgeMap as $key => $label) {
	                                    if(data_get($time, 'edge.' . $key)) $edges[] = $label;
	                                }
	                            @endphp
	                            {{ $edges ? implode(', ', $edges) : '-' }}
	                        </p>
	                    @endif

	                    @if($measure = data_get($wound, 'measurements'))
	                        <h6 class="mt-2">Wound Measurements</h6>
	                        <p>
	                            <strong>Size:</strong>
	                            {{ data_get($measure, 'length_cm') }} x
	                            {{ data_get($measure, 'width_cm') }} x
	                            {{ data_get($measure, 'depth_cm') }} cm
	                        </p>
	                        <p><strong>Undermining (clock):</strong> {{ data_get($measure, 'undermining_clock_position') }}</p>
	                        <p><strong>Tunneling:</strong> {{ data_get($measure, 'tunneling') }}</p>
	                        <p><strong>Probe-to-bone test:</strong> {{ data_get($measure, 'probe_to_bone_test') }}</p>
	                    @endif

	                    @if($bed = data_get($wound, 'bed_composition'))
	                        <h6 class="mt-2">Wound Bed Composition (%)</h6>
	                        <p>
	                            Granulation {{ data_get($bed, 'granulation_pct') }}%,
	                            Slough {{ data_get($bed, 'slough_pct') }}%,
	                            Necrosis {{ data_get($bed, 'necrosis_pct') }}%,
	                            Epithelial {{ data_get($bed, 'epithelial_pct') }}%
	                        </p>
	                    @endif

	                    @if($skin = data_get($wound, 'surrounding_skin'))
	                        <h6 class="mt-2">Surrounding Skin</h6>
	                        <p>
	                            @php
	                                $skinLabels = [
	                                    'normal' => 'Normal',
	                                    'maceration' => 'Maceration',
	                                    'erythema' => 'Erythema',
	                                    'callus' => 'Callus',
	                                    'edema' => 'Edema',
	                                    'induration' => 'Induration',
	                                    'dry_skin' => 'Dry skin',
	                                ];
	                                $skinOut = [];
	                                foreach ($skinLabels as $key => $label) {
	                                    if(data_get($skin, $key)) $skinOut[] = $label;
	                                }
	                            @endphp
	                            {{ $skinOut ? implode(', ', $skinOut) : '-' }}
	                        </p>
	                    @endif

	                    @if($pain = data_get($wound, 'pain'))
	                        <h6 class="mt-2">Pain Assessment</h6>
	                        <p><strong>Score:</strong> {{ data_get($pain, 'score') }}</p>
	                        <p><strong>At rest:</strong> {{ data_get($pain, 'at_rest') }}</p>
	                        <p><strong>During dressing change:</strong> {{ data_get($pain, 'during_dressing_change') }}</p>
	                    @endif

	                    @if($vascular = data_get($wound, 'vascular'))
	                        <h6 class="mt-2">Vascular Assessment</h6>
	                        <p><strong>Pedal pulses:</strong> {{ data_get($vascular, 'pedal_pulses') }}</p>
	                        <p><strong>Capillary refill:</strong> {{ data_get($vascular, 'capillary_refill') }}</p>
	                        <p><strong>ABI/TBI:</strong> ABI {{ data_get($vascular, 'abi') }}, TBI {{ data_get($vascular, 'tbi') }}</p>
	                        <p><strong>Doppler findings:</strong> {{ data_get($vascular, 'doppler_findings') }}</p>
	                        <p><strong>Skin temperature:</strong> {{ data_get($vascular, 'skin_temperature') }}</p>
	                    @endif

	                    @if($neuro = data_get($wound, 'neurological'))
	                        <h6 class="mt-2">Neurological Assessment</h6>
	                        <p><strong>Monofilament test:</strong> {{ data_get($neuro, 'monofilament_test') }}</p>
	                        <p><strong>Vibration sensation:</strong> {{ data_get($neuro, 'vibration_sensation') }}</p>
	                        <p><strong>Protective sensation:</strong> {{ data_get($neuro, 'protective_sensation') }}</p>
	                        <p><strong>Neuropathy present:</strong> {{ data_get($neuro, 'neuropathy_present') }}</p>
	                    @endif

	                    @if($class = data_get($wound, 'classification'))
	                        <h6 class="mt-2">Classification</h6>

	                        @if(data_get($class, 'wifi_stage') || data_get($class, 'wifi.wound') !== null)
	                            <div class="mb-2">
	                                <strong>WIfI Scale:</strong>
	                                @if(data_get($class, 'wifi_stage')) Stage {{ data_get($class, 'wifi_stage') }} @endif
	                                @if(data_get($class, 'wifi.wound') !== null || data_get($class, 'wifi.ischemia') !== null || data_get($class, 'wifi.infection') !== null)
	                                    <span class="text-muted">
	                                        (Wound: {{ data_get($class, 'wifi.wound', '-') }},
	                                         Ischemia: {{ data_get($class, 'wifi.ischemia', '-') }},
	                                         Infection: {{ data_get($class, 'wifi.infection', '-') }})
	                                    </span>
	                                @endif
	                            </div>
	                        @endif

	                    @endif

	                    @if($lab = data_get($wound, 'laboratory'))
	                        <h6 class="mt-2">Laboratory Results</h6>
	                        <p>
	                            HbA1c {{ data_get($lab, 'hba1c') }},
	                            FBG {{ data_get($lab, 'fasting_blood_glucose') }},
	                            WBC {{ data_get($lab, 'wbc') }},
	                            CRP {{ data_get($lab, 'crp') }},
	                            ESR {{ data_get($lab, 'esr') }}
	                        </p>
	                        <p>
	                            Albumin {{ data_get($lab, 'albumin') }},
	                            Hb {{ data_get($lab, 'hemoglobin') }},
	                            Creatinine {{ data_get($lab, 'creatinine') }},
	                            GFR {{ data_get($lab, 'gfr') }}
	                        </p>
	                    @endif

	                    @if($micro = data_get($wound, 'microbiology'))
	                        <h6 class="mt-2">Microbiology</h6>
	                        <p><strong>Swab collected:</strong> {{ data_get($micro, 'swab_collected') }}</p>
	                        <p><strong>Tissue culture:</strong> {{ data_get($micro, 'tissue_culture') }}</p>
	                        <p><strong>Organism isolated:</strong> {{ data_get($micro, 'organism_isolated') }}</p>
	                        <p><strong>Antibiotic sensitivity:</strong> {{ data_get($micro, 'antibiotic_sensitivity') }}</p>
	                    @endif

	                    @if($treat = data_get($wound, 'treatment'))
	                        <h6 class="mt-2">Treatment</h6>
	                        <p><strong>Debridement:</strong>
	                            @php
	                                $deb = [];
	                                $debMap = [
	                                    'sharp' => 'Sharp',
	                                    'surgical' => 'Surgical',
	                                    'autolytic' => 'Autolytic',
	                                    'mechanical' => 'Mechanical',
	                                    'enzymatic' => 'Enzymatic',
	                                ];
	                                foreach ($debMap as $key => $label) {
	                                    if(data_get($treat, 'debridement.' . $key)) $deb[] = $label;
	                                }
	                            @endphp
	                            {{ $deb ? implode(', ', $deb) : '-' }}
	                        </p>
	                        <p><strong>Cleansing:</strong>
	                            @php
	                                $clean = [];
	                                $cleanMap = [
	                                    'normal_saline' => 'Normal saline',
	                                    'hocl' => 'HOCl',
	                                    'phmb' => 'PHMB',
	                                ];
	                                foreach ($cleanMap as $key => $label) {
	                                    if(data_get($treat, 'cleansing.' . $key)) $clean[] = $label;
	                                }
	                                if($other = data_get($treat, 'cleansing.other')) $clean[] = $other;
	                            @endphp
	                            {{ $clean ? implode(', ', $clean) : '-' }}
	                        </p>
	                        @if($dress = data_get($treat, 'dressing'))
	                            <p><strong>Dressing:</strong>
	                                @php
	                                    $dressLabels = [
	                                        'foam' => 'Foam',
	                                        'alginate' => 'Alginate',
	                                        'hydrofiber' => 'Hydrofiber',
	                                        'hydrocolloid' => 'Hydrocolloid',
	                                        'silver' => 'Silver',
	                                        'iodine' => 'Iodine',
	                                        'silicone' => 'Silicone',
	                                        'contact_layer' => 'Contact layer',
	                                        'ecm' => 'ECM',
	                                        'hydrogel' => 'Hydrogel',
	                                    ];
	                                    $dressOut = [];
	                                    foreach ($dressLabels as $key => $label) {
	                                        if(data_get($dress, $key)) $dressOut[] = $label;
	                                    }
	                                @endphp
	                                {{ $dressOut ? implode(', ', $dressOut) : '-' }}
	                            </p>
	                        @endif
	                        @if($adv = data_get($treat, 'advanced'))
	                            <p><strong>Advanced therapy:</strong>
	                                @php
	                                    $advLabels = [
	                                        'npwt' => 'NPWT',
	                                        'skin_graft' => 'Skin graft',
	                                        'flap_surgery' => 'Flap surgery',
	                                        'hbot' => 'Hyperbaric oxygen therapy',
	                                    ];
	                                    $advOut = [];
	                                    foreach ($advLabels as $key => $label) {
	                                        if(data_get($adv, $key)) $advOut[] = $label;
	                                    }
	                                @endphp
	                                {{ $advOut ? implode(', ', $advOut) : '-' }}
	                            </p>
	                        @endif
	                        @if($off = data_get($treat, 'offloading'))
	                            <p><strong>Offloading:</strong>
	                                @php
	                                    $offLabels = [
	                                        'tcc' => 'Total Contact Cast',
	                                        'removable_walker' => 'Removable walker',
	                                        'therapeutic_shoes' => 'Therapeutic shoes',
	                                        'wheelchair' => 'Wheelchair',
	                                        'crutches' => 'Crutches',
	                                    ];
	                                    $offOut = [];
	                                    foreach ($offLabels as $key => $label) {
	                                        if(data_get($off, $key)) $offOut[] = $label;
	                                    }
	                                @endphp
	                                {{ $offOut ? implode(', ', $offOut) : '-' }}
	                            </p>
	                        @endif
	                    @endif

	                    @if($follow = data_get($wound, 'followup'))
	                        <h6 class="mt-2">Follow-up</h6>
	                        <p><strong>Dressing change frequency:</strong> {{ data_get($follow, 'dressing_change_frequency') }}</p>
	                        <p><strong>Weekly measurements:</strong> {{ data_get($follow, 'weekly_measurements') }}</p>
	                        <p><strong>Photographs:</strong> {{ data_get($follow, 'photographs') }}</p>
	                        <p><strong>Complications:</strong> {{ data_get($follow, 'complications') }}</p>
	                        <p><strong>Healing progress:</strong> {{ data_get($follow, 'healing_progress') }}</p>
	                    @endif

	                    @if($outcome = data_get($wound, 'outcome'))
	                        <h6 class="mt-2">Outcome</h6>
	                        <p><strong>Summary:</strong> {{ data_get($outcome, 'summary') }}</p>
	                        <p><strong>Details:</strong>
	                            @php
	                                $outLabels = [
	                                    'completely_healed' => 'Completely healed',
	                                    'improved' => 'Improved',
	                                    'no_change' => 'No change',
	                                    'deteriorated' => 'Deteriorated',
	                                    'infection_resolved' => 'Infection resolved',
	                                    'amputation' => 'Amputation',
	                                    'death' => 'Death',
	                                ];
	                                $outOut = [];
	                                foreach ($outLabels as $key => $label) {
	                                    if(data_get($outcome, $key)) $outOut[] = $label;
	                                }
	                            @endphp
	                            {{ $outOut ? implode(', ', $outOut) : '-' }}
	                        </p>
	                    @endif

	                    @if($heal = data_get($wound, 'healing_time'))
	                        <h6 class="mt-2">Healing Time</h6>
	                        <p><strong>Date healed:</strong> {{ data_get($heal, 'date_healed') }}</p>
	                        <p><strong>Total healing days:</strong> {{ data_get($heal, 'total_healing_days') }}</p>
	                    @endif
	                @else
	                    <p class="text-muted">No wound assessment recorded for this operation.</p>
	                @endif
	            </div>
	        </div>
	    @else
	        <p class="mt-3 text-muted">No operation has been recorded for this case yet.</p>
	    @endif
</div>
@endsection
