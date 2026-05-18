<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-1"><i class="fas fa-layer-group text-primary me-2"></i>{{ __('Modular Patient Profile') }}</h5>
            <p class="text-muted small mb-0">{{ __('General information, shared medical overview, visit-based HPI, and specialty modules are managed separately.') }}</p>
        </div>

        @if($availableProfileModules->isNotEmpty())
            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addPatientModuleModal">
                <i class="fas fa-plus me-1"></i>{{ __('Add Module') }}
            </button>
        @endif
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#patient-profile-general" type="button" role="tab">{{ __('General') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#patient-profile-medical-overview" type="button" role="tab">{{ __('Medical Overview') }}</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#patient-profile-hpi" type="button" role="tab">{{ __('HPI') }}</button>
            </li>
            @foreach($activeProfileModules as $module)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#patient-profile-module-{{ $module['key'] }}" type="button" role="tab">
                        <i class="{{ $module['icon'] }} me-1"></i>{{ $module['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="patient-profile-general" role="tabpanel">
                <div class="row g-3">
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Patient ID') }}</small><strong>{{ $patient->patient_id }}</strong></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Age') }}</small><strong>{{ $patient->age_formatted }}</strong></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Gender') }}</small><strong>{{ ucfirst($patient->gender ?? __('Unknown')) }}</strong></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Phone') }}</small><strong>{{ $patient->phone ?: __('Not provided') }}</strong></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Blood Type') }}</small><strong>{{ $patient->blood_type ?: __('Unknown') }}</strong></div></div>
                    <div class="col-md-4"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Clinic') }}</small><strong>{{ $patient->clinic->name ?? __('Not assigned') }}</strong></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Address') }}</small><strong>{{ $patient->address ?: __('Not provided') }}</strong></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><small class="text-muted d-block">{{ __('Emergency Contact') }}</small><strong>{{ $patient->emergency_contact_name ?: __('Not provided') }}</strong><div class="text-muted small">{{ $patient->emergency_contact_phone ?: __('No emergency contact number') }}</div></div></div>
                </div>
            </div>

            <div class="tab-pane fade" id="patient-profile-medical-overview" role="tabpanel">
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-share-alt me-2 text-primary"></i>{{ __('This shared overview is used across all specialty modules. HPI is managed separately per visit.') }}
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h6 class="text-danger mb-2">{{ __('Allergies') }}</h6><div>{{ $medicalOverview->allergies ?: __('No allergies recorded.') }}</div></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h6 class="text-warning mb-2">{{ __('Chronic Diseases') }}</h6><div>{{ $medicalOverview->chronic_diseases ?: __('No chronic diseases recorded.') }}</div></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h6 class="text-info mb-2">{{ __('Surgeries') }}</h6><div>{{ $medicalOverview->surgeries ?: __('No surgeries recorded.') }}</div></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h6 class="text-primary mb-2">{{ __('Medical History Notes') }}</h6><div>{{ $medicalOverview->medical_history ?: __('No shared medical notes recorded.') }}</div></div></div>
                    <div class="col-12"><div class="border rounded p-3 h-100"><h6 class="text-success mb-2">{{ __('Clinical Flags') }}</h6>
                        @if(!empty($medicalOverview->activeFlagLabels()))
                            @foreach($medicalOverview->activeFlagLabels() as $flagLabel)
                                <span class="badge bg-warning text-dark me-1 mb-1">{{ $flagLabel }}</span>
                            @endforeach
                        @else
                            <div>{{ __('No clinical flags recorded.') }}</div>
                        @endif
                    </div></div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">{{ __('Medication Summary') }}</h6>
                                <span class="badge bg-light text-dark">{{ __('Global') }}</span>
                            </div>

                            @if($medicalOverview->current_medications_summary)
                                <div class="alert alert-light border mb-3">{{ $medicalOverview->current_medications_summary }}</div>
                            @endif

                            <div class="mb-3">
                                <strong class="d-block mb-2">{{ __('Current Medications') }}</strong>
                                @forelse($currentMedications as $medication)
                                    <div class="border rounded px-3 py-2 mb-2">
                                        <div class="fw-semibold">{{ $medication->medication_name }}</div>
                                        <div class="small text-muted">{{ collect([$medication->dosage, $medication->frequency, $medication->route])->filter()->join(' • ') ?: __('No dosing details') }}</div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">{{ __('No current medications recorded.') }}</p>
                                @endforelse
                            </div>

                            <div>
                                <strong class="d-block mb-2">{{ __('Past Medications') }}</strong>
                                @forelse($pastMedications as $medication)
                                    <div class="border rounded px-3 py-2 mb-2">
                                        <div class="fw-semibold">{{ $medication->medication_name }}</div>
                                        <div class="small text-muted">{{ $medication->ended_on ? __('Ended :date', ['date' => $medication->ended_on->format('M d, Y')]) : __('Past medication record') }}</div>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">{{ __('No past medications recorded.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="mb-3">{{ __('Update Medical Overview') }}</h6>
                            <form method="POST" action="{{ route('patients.medical-overview.update', ['patient' => $patient->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-2"><label class="form-label small">{{ __('Allergies') }}</label><textarea class="form-control form-control-sm" name="allergies" rows="2">{{ old('allergies', $medicalOverview->allergies) }}</textarea></div>
                                <div class="mb-2"><label class="form-label small">{{ __('Chronic Diseases') }}</label><textarea class="form-control form-control-sm" name="chronic_diseases" rows="2">{{ old('chronic_diseases', $medicalOverview->chronic_diseases) }}</textarea></div>
                                <div class="mb-2"><label class="form-label small">{{ __('Surgeries') }}</label><textarea class="form-control form-control-sm" name="surgeries" rows="2">{{ old('surgeries', $medicalOverview->surgeries) }}</textarea></div>
                                <div class="mb-2"><label class="form-label small">{{ __('Medical History Notes') }}</label><textarea class="form-control form-control-sm" name="medical_history" rows="2">{{ old('medical_history', $medicalOverview->medical_history) }}</textarea></div>
                                <div class="mb-3"><label class="form-label small">{{ __('Current Medications Summary') }}</label><textarea class="form-control form-control-sm" name="current_medications_summary" rows="2">{{ old('current_medications_summary', $medicalOverview->current_medications_summary) }}</textarea></div>
                                <div class="mb-3">
                                    <label class="form-label small d-block">{{ __('Flags') }}</label>
                                    @foreach(\App\Models\PatientMedicalOverview::FLAG_LABELS as $flagKey => $flagLabel)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="flag-{{ $flagKey }}" name="flags[{{ $flagKey }}]" value="1" {{ old("flags.$flagKey", data_get($medicalOverview->flags ?? [], $flagKey)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="flag-{{ $flagKey }}">{{ __($flagLabel) }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">{{ __('Save Overview') }}</button>
                            </form>
                        </div>

                        <div class="border rounded p-3">
                            <h6 class="mb-3">{{ __('Add Medication Record') }}</h6>
                            <form method="POST" action="{{ route('patients.medications.store', ['patient' => $patient->id]) }}">
                                @csrf
                                <div class="mb-2"><input class="form-control form-control-sm" name="medication_name" value="{{ old('medication_name') }}" placeholder="{{ __('Medication name') }}" required></div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input class="form-control form-control-sm" name="dosage" value="{{ old('dosage') }}" placeholder="{{ __('Dosage') }}"></div>
                                    <div class="col-6"><input class="form-control form-control-sm" name="frequency" value="{{ old('frequency') }}" placeholder="{{ __('Frequency') }}"></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input class="form-control form-control-sm" name="route" value="{{ old('route') }}" placeholder="{{ __('Route') }}"></div>
                                    <div class="col-6">
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="current">{{ __('Current') }}</option>
                                            <option value="past">{{ __('Past') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2"><input class="form-control form-control-sm" name="indication" value="{{ old('indication') }}" placeholder="{{ __('Indication') }}"></div>
                                <button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Add Medication') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="patient-profile-hpi" role="tabpanel">
                <div class="alert alert-light border mb-3">
                    <i class="fas fa-file-medical-alt me-2 text-info"></i>{{ __('HPI belongs to visits. Each visit can have its own chief complaint and HPI narrative.') }}
                </div>

                @if($legacyProfileHpi && $recentVisits->isEmpty())
                    <div class="alert alert-warning border">
                        <strong>{{ __('Legacy profile HPI') }}:</strong> {{ $legacyProfileHpi }}
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-3">{{ __('Recent Visits') }}</h6>
                            @forelse($recentVisits as $visit)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $visit->hpi->chief_complaint ?? $visit->reason_for_visit ?? __('Visit') }}</div>
                                            <div class="small text-muted">{{ optional($visit->visit_date)->format('M d, Y h:i A') }} • {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</div>
                                        </div>
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('patients.visits.show', ['patient' => $patient->id, 'visit' => $visit->id]) }}">{{ __('Open') }}</a>
                                    </div>
                                    @if($visit->hpi?->hpi_summary)
                                        <div class="mt-2 text-muted">{{ \Illuminate\Support\Str::limit($visit->hpi->hpi_summary, 180) }}</div>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('No visit-based HPI records have been added yet.') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="border rounded p-3">
                            <h6 class="mb-3">{{ __('Add Visit HPI') }}</h6>
                            <form method="POST" action="{{ route('patients.visits.store', ['patient' => $patient->id]) }}">
                                @csrf
                                <div class="mb-2"><label class="form-label small">{{ __('Visit Date') }}</label><input type="datetime-local" class="form-control form-control-sm" name="visit_date" value="{{ old('visit_date', now()->format('Y-m-d\\TH:i')) }}" required></div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input class="form-control form-control-sm" name="visit_type" value="{{ old('visit_type', 'consultation') }}" placeholder="{{ __('Visit type') }}"></div>
                                    <div class="col-6"><input class="form-control form-control-sm" name="status" value="{{ old('status', 'completed') }}" placeholder="{{ __('Status') }}"></div>
                                </div>
                                <div class="mb-2"><input class="form-control form-control-sm" name="reason_for_visit" value="{{ old('reason_for_visit') }}" placeholder="{{ __('Reason for visit') }}"></div>
                                <div class="mb-2"><input class="form-control form-control-sm" name="chief_complaint" value="{{ old('chief_complaint') }}" placeholder="{{ __('Chief complaint') }}"></div>
                                <div class="mb-2"><textarea class="form-control form-control-sm" name="hpi_summary" rows="3" placeholder="{{ __('HPI summary') }}">{{ old('hpi_summary') }}</textarea></div>
                                <div class="mb-2"><textarea class="form-control form-control-sm" name="associated_symptoms" rows="2" placeholder="{{ __('Associated symptoms') }}">{{ old('associated_symptoms') }}</textarea></div>
                                <button class="btn btn-outline-primary btn-sm" type="submit">{{ __('Save Visit') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($activeProfileModules as $module)
                <div class="tab-pane fade" id="patient-profile-module-{{ $module['key'] }}" role="tabpanel">
                    <div class="border rounded p-3">
                        @php
                            $modulePrimaryAction = \App\Services\PatientProfileModuleRegistry::primaryAction($patient, $module['key']);
                        @endphp
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="mb-1"><i class="{{ $module['icon'] }} me-2"></i>{{ $module['label'] }}</h6>
                                <p class="text-muted mb-0">{{ $module['description'] }}</p>
                            </div>
                            @if($modulePrimaryAction)
                                <a class="btn btn-outline-primary btn-sm" href="{{ $modulePrimaryAction['url'] }}">{{ $modulePrimaryAction['label'] }}</a>
                            @endif
                        </div>

                        @if($module['key'] === 'dental')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Oral Hygiene Status') }}</small>
                                        <strong>{{ $dentalProfile->oral_hygiene_label ?: __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Smoking Habits') }}</small>
                                        <strong>{{ $dentalProfile->smoking_status_label ?: __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Last Dental Visit') }}</small>
                                        <strong>{{ $dentalLastVisitLabel ?? data_get($patient, 'dental_last_visit_label', __('Not recorded')) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Bruxism') }}</small>
                                        <strong>{{ $dentalProfile->bruxism ? __('Yes') : __('No') }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Notes') }}</small>
                                        <div>{{ $dentalProfile->notes ?: __('No dental-specific notes recorded.') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($module['key'] === 'ent')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Ear Examination') }}</small>
                                        <div>{{ $entProfile->hearing_issues ?: __('No ear examination recorded.') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Nose Examination') }}</small>
                                        <div>{{ $entProfile->nasal_issues ?: __('No nose examination recorded.') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Throat Examination') }}</small>
                                        <div>{{ $entProfile->throat_issues ?: __('No throat examination recorded.') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('ENT Notes') }}</small>
                                        <div>{{ $entProfile->notes ?: __('No ENT-specific notes recorded.') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($module['key'] === 'pediatric')
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Birth Weight') }}</small>
                                        <strong>{{ $pediatricProfile->birth_weight ? $pediatricProfile->birth_weight . ' g' : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Gestational Age') }}</small>
                                        <strong>{{ $pediatricProfile->gestational_age ? $pediatricProfile->gestational_age . ' ' . __('weeks') : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Growth Status') }}</small>
                                        <strong>{{ __($pediatricProfile->growth_status_label) }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Vaccination Status') }}</small>
                                        <strong>{{ $pediatricProfile->vaccination_status_label ? __($pediatricProfile->vaccination_status_label) : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($module['key'] === 'nutrition')
                            @php
                                $nutritionProfile = $nutritionProfile ?? new \App\Models\PatientNutrition([
                                    'height' => $latestNutritionMeasurement?->height_cm ?? $patient->height,
                                    'weight' => $latestNutritionMeasurement?->weight_kg ?? $patient->weight,
                                    'bmi' => $latestNutritionMeasurement?->bmi ?? $patient->bmi,
                                ]);
                                $nutritionHeight = $nutritionProfile->height ?? $latestNutritionMeasurement?->height_cm ?? $patient->height;
                                $nutritionWeight = $nutritionProfile->weight ?? $latestNutritionMeasurement?->weight_kg ?? $patient->weight;
                                $nutritionBmi = $nutritionProfile->bmi ?? $latestNutritionMeasurement?->bmi ?? $patient->bmi;
                                $nutritionDietType = $nutritionProfile->diet_type;
                                if (blank($nutritionDietType) && $latestNutritionPlan?->goal) {
                                    $nutritionDietType = \Illuminate\Support\Str::headline(str_replace('_', ' ', $latestNutritionPlan->goal));
                                }
                                $nutritionGoals = $nutritionProfile->goals;
                                if (blank($nutritionGoals) && ($activeNutritionGoal ?? null)) {
                                    $nutritionGoals = collect([
                                        $activeNutritionGoal->target_weight ? __('Target weight: :value kg', ['value' => $activeNutritionGoal->target_weight]) : null,
                                        $activeNutritionGoal->target_bmi ? __('Target BMI: :value', ['value' => $activeNutritionGoal->target_bmi]) : null,
                                        $activeNutritionGoal->notes,
                                    ])->filter()->implode(' • ');
                                }
                            @endphp
                            <div class="alert alert-light border mb-3">
                                <i class="fas fa-share-alt me-2 text-primary"></i>{{ __('Do not store medical diseases in Nutrition. Keep shared conditions in Medical Overview.') }}
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Height') }}</small>
                                        <strong>{{ $nutritionHeight ? $nutritionHeight . ' cm' : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Weight') }}</small>
                                        <strong>{{ $nutritionWeight ? $nutritionWeight . ' kg' : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('BMI') }}</small>
                                        <strong>{{ $nutritionBmi ? number_format((float) $nutritionBmi, 1) : __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Diet Type') }}</small>
                                        <strong>{{ $nutritionDietType ?: __('Not recorded') }}</strong>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ __('Goals') }}</small>
                                        <div>{{ $nutritionGoals ?: __('No nutrition goals recorded yet.') }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            @foreach(\App\Services\PatientProfileModuleRegistry::summaryStats($patient, $module['key']) as $stat)
                                <div class="col-md-4">
                                    <div class="bg-light border rounded p-3 h-100">
                                        <small class="text-muted d-block">{{ $stat['label'] }}</small>
                                        <strong>{{ $stat['value'] }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @php
                            $moduleLinks = \App\Services\PatientProfileModuleRegistry::moduleLinks($patient, $module['key']);
                        @endphp
                        @if(!empty($moduleLinks))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($moduleLinks as $link)
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('No dedicated tools are wired yet for this module. The summary page is ready for future implementation.') }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if($availableProfileModules->isNotEmpty())
    <div class="modal fade" id="addPatientModuleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add Module') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="POST" action="{{ route('patients.modules.store', ['patient' => $patient->id]) }}">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label">{{ __('Available Modules') }}</label>
                        <select class="form-select" name="module_name" required>
                            @foreach($availableProfileModules as $module)
                                <option value="{{ $module['key'] }}">{{ $module['label'] }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">{{ __('Modules are shown as profile tabs only when activated. Pediatric is automatically hidden for patients aged 16 and above.') }}</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Activate Module') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif