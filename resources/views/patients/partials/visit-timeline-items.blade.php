@forelse($visitTimeline as $checkup)
    @php
        $drawerId = 'visitTimelineDrawer' . $checkup->id;
        $summaryCollapseId = 'visitTimelineSummary' . $checkup->id;
        $isExpandedByDefault = (bool) $checkup->is_most_recent_visit;
        $isHistoryItem = !($visitTimeline->currentPage() === 1 && $loop->first);
        $prescriptions = collect($checkup->timeline_prescriptions ?? []);
        $attachments = collect($checkup->timeline_attachments ?? []);
    @endphp
    <div class="visit-timeline-record {{ $isHistoryItem ? 'd-none' : '' }}" data-visit-history-item="{{ $isHistoryItem ? 'true' : 'false' }}">
        <div class="visit-timeline-entry">
            <div class="visit-timeline-marker">
                <span class="visit-timeline-dot {{ $checkup->is_most_recent_visit ? 'visit-timeline-dot-latest' : ($checkup->is_first_visit ? 'visit-timeline-dot-first' : '') }}"></span>
            </div>
            <div class="flex-grow-1">
                <div class="card shadow-sm visit-timeline-card {{ $checkup->is_first_visit ? 'is-first-visit' : '' }} {{ $checkup->is_most_recent_visit ? 'is-most-recent' : '' }}">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                            <div>
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <span class="badge bg-primary-subtle text-primary border">{{ optional($checkup->checkup_date)->format('M d, Y') }}</span>
                                    @if($checkup->is_most_recent_visit)
                                        <span class="badge bg-success">{{ __('Most Recent Visit') }}</span>
                                    @endif
                                    @if($checkup->is_first_visit)
                                        <span class="badge text-bg-dark">{{ __('First Visit') }}</span>
                                    @endif
                                </div>
                                @if($checkup->recorder)
                                    <small class="text-muted"><i class="fas fa-user-md me-1"></i>{{ $checkup->recorder->full_name }}</small>
                                @endif
                            </div>
                            <div class="d-flex gap-2 align-items-start flex-wrap justify-content-md-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary visit-summary-toggle {{ $isExpandedByDefault ? '' : 'collapsed' }}"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $summaryCollapseId }}"
                                    aria-expanded="{{ $isExpandedByDefault ? 'true' : 'false' }}"
                                    aria-controls="{{ $summaryCollapseId }}"
                                >
                                    <i class="fas fa-chevron-down me-1 small"></i>
                                    <span class="label-collapsed">{{ __('Expand Summary') }}</span>
                                    <span class="label-expanded">{{ __('Collapse Summary') }}</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#{{ $drawerId }}" aria-controls="{{ $drawerId }}">
                                    <i class="fas fa-eye me-1"></i>{{ __('View Details') }}
                                </button>
                                <a href="{{ route('checkups.show', [$patient, $checkup]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-up-right-from-square me-1"></i>{{ __('Open') }}
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="visit-summary-label mb-1"><i class="fas fa-stethoscope me-1"></i>{{ __('Chief Complaint') }}</div>
                            <div class="visit-card-preview">{{ $checkup->chief_complaint ? Str::limit($checkup->chief_complaint, 110) : __('No chief complaint recorded.') }}</div>
                        </div>

                        <div class="collapse {{ $isExpandedByDefault ? 'show' : '' }}" id="{{ $summaryCollapseId }}">
                            <div class="pt-3 border-top">
                                <div class="mb-3">
                                    <div class="visit-summary-label mb-1"><i class="fas fa-stethoscope me-1"></i>{{ __('Chief Complaint') }}</div>
                                    <div>{{ $checkup->chief_complaint ?: __('No chief complaint recorded.') }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="visit-summary-label mb-1"><i class="fas fa-file-medical me-1"></i>{{ __('Diagnosis') }}</div>
                                    <div>{{ $checkup->diagnosis ?: __('No diagnosis recorded.') }}</div>
                                </div>

                                <div>
                                    <div class="visit-summary-label mb-1"><i class="fas fa-pills me-1"></i>{{ __('Prescription Summary') }}</div>
                                    <div>{{ $checkup->timeline_prescription_summary ?: __('No prescriptions linked to this visit.') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $drawerId }}" aria-labelledby="{{ $drawerId }}Label" style="width:min(560px, 96vw)">
            <div class="offcanvas-header border-bottom">
                <div>
                    <h5 class="offcanvas-title" id="{{ $drawerId }}Label">{{ __('Visit Details') }}</h5>
                    <small class="text-muted">{{ optional($checkup->checkup_date)->format('l, M d, Y g:i A') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="offcanvas-body">
                <div class="mb-4">
                    <h6><i class="fas fa-notes-medical me-2 text-primary"></i>{{ __('Clinical Notes') }}</h6>
                    <p class="mb-0 text-muted">{{ $checkup->notes ?: __('No clinical notes recorded.') }}</p>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-heartbeat me-2 text-danger"></i>{{ __('Vital Signs') }}</h6>
                    <div class="visit-vitals-grid">
                        @foreach([
                            __('Blood Pressure') => $checkup->blood_pressure,
                            __('Heart Rate') => $checkup->heart_rate ? $checkup->heart_rate . ' bpm' : null,
                            __('Temperature') => $checkup->temperature ? $checkup->temperature . '°C' : null,
                            __('Respiratory Rate') => $checkup->respiratory_rate ? $checkup->respiratory_rate . ' rpm' : null,
                            __('Weight') => $checkup->weight ? $checkup->weight . ' kg' : null,
                            __('Height') => $checkup->height ? $checkup->height . ' cm' : null,
                            __('Blood Sugar') => $checkup->blood_sugar,
                        ] as $label => $value)
                            @if($value)
                                <div class="visit-vital-card">
                                    <div class="visit-summary-label">{{ $label }}</div>
                                    <div class="fw-semibold">{{ $value }}</div>
                                </div>
                            @endif
                        @endforeach
                        @foreach($checkup->custom_vital_signs_with_config as $customSign)
                            <div class="visit-vital-card">
                                <div class="visit-summary-label">{{ $customSign['config']->name }}</div>
                                <div class="fw-semibold">{{ $customSign['formatted_value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if(empty($checkup->custom_vital_signs_with_config) && !$checkup->blood_pressure && !$checkup->heart_rate && !$checkup->temperature && !$checkup->respiratory_rate && !$checkup->weight && !$checkup->height && !$checkup->blood_sugar)
                        <p class="text-muted mb-0">{{ __('No vital signs recorded.') }}</p>
                    @endif
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-user-check me-2 text-info"></i>{{ __('Physical Examination') }}</h6>
                    <p class="mb-0 text-muted">{{ $checkup->examination ?: __('No physical examination details recorded.') }}</p>
                </div>

                <div class="mb-4">
                    <h6><i class="fas fa-pills me-2 text-success"></i>{{ __('Full Prescription Details') }}</h6>
                    @forelse($prescriptions as $prescription)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div class="fw-semibold">{{ $prescription['number'] ?: __('Prescription') }}</div>
                                <small class="text-muted">{{ $prescription['doctor_name'] ?: __('Doctor not recorded') }}</small>
                            </div>
                            @if($prescription['diagnosis'])<p class="mb-2"><strong>{{ __('Diagnosis:') }}</strong> {{ $prescription['diagnosis'] }}</p>@endif
                            @if($prescription['notes'])<p class="mb-2"><strong>{{ __('Notes:') }}</strong> {{ $prescription['notes'] }}</p>@endif
                            @if(!empty($prescription['medicines']))
                                <ul class="mb-0 ps-3">
                                    @foreach($prescription['medicines'] as $medicine)
                                        <li class="mb-1">
                                            <strong>{{ $medicine['name'] }}</strong>
                                            @if($medicine['dosage']) · {{ $medicine['dosage'] }} @endif
                                            @if($medicine['frequency']) · {{ $medicine['frequency'] }} @endif
                                            @if($medicine['duration']) · {{ $medicine['duration'] }} @endif
                                            @if($medicine['instructions'])<div class="small text-muted">{{ $medicine['instructions'] }}</div>@endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ __('No prescriptions linked to this visit.') }}</p>
                    @endforelse
                </div>

                <div>
                    <h6><i class="fas fa-paperclip me-2 text-warning"></i>{{ __('Attachments') }}</h6>
                    @forelse($attachments as $attachment)
                        <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener" class="d-flex align-items-start gap-3 text-decoration-none border rounded p-3 mb-2">
                            <i class="{{ $attachment['icon'] }} mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-body">{{ $attachment['label'] }}</div>
                                <div class="small text-muted">{{ $attachment['type'] }} · {{ $attachment['meta'] }}</div>
                                @if($attachment['description'])<div class="small text-muted">{{ $attachment['description'] }}</div>@endif
                                @if($attachment['uploaded_at'] || $attachment['uploader'])
                                    <div class="small text-muted">
                                        {{ $attachment['uploaded_at'] ?? '' }}@if(!empty($attachment['uploaded_at']) && !empty($attachment['uploader'])) · @endif{{ $attachment['uploader'] ? __('by :name', ['name' => $attachment['uploader']]) : '' }}
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="visit-timeline-empty text-center py-3 text-muted">
                            <i class="fas fa-paperclip d-block mb-2"></i>
                            {{ __('No attachments were uploaded for this visit.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="visit-timeline-empty text-center py-5 px-3 text-muted">
        <i class="fas fa-stethoscope fa-2x mb-3"></i>
        <p class="mb-3">{{ request('search') || request('visit_search') ? __('No visits match your search.') : __('No checkups recorded yet.') }}</p>
        <a href="{{ route('checkups.create', $patient) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i>{{ __('Record First Checkup') }}
        </a>
    </div>
@endforelse