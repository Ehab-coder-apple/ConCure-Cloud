@extends('layouts.app')

@section('title', __('Aesthetic - ') . $patient->full_name)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-spa me-2 text-primary"></i>{{ __('Aesthetic Module') }}</h3>
            <p class="text-muted mb-0">{{ __('Aesthetic sessions, packages, and before/after images for') }} {{ $patient->full_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('patients.show', ['patient' => $patient->id]) }}" class="btn btn-outline-secondary">{{ __('Back to Patient Profile') }}</a>
            <a href="{{ route('patients.edit', ['patient' => $patient->id]) }}#modules-pane" class="btn btn-outline-info">
                <i class="fas fa-edit me-1"></i>{{ __('Edit Intake') }}
            </a>
            <a href="{{ route('aesthetic.sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>{{ __('New Direct Session') }}
            </a>
        </div>
    </div>

    <!-- Aesthetic Intake / Consultation Card -->
    @if($patient->aestheticProfile)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-info"></i>{{ __('Aesthetic Consultation Intake') }}</h5>
            <span class="badge bg-info">{{ __('Recorded') }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small mb-1">{{ __('Skin Type') }}</div>
                    <div class="fw-semibold">
                        @if($patient->aestheticProfile->skin_type)
                            <span class="badge bg-secondary">{{ $patient->aestheticProfile->skin_type_label }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small mb-1">{{ __('Sun Exposure') }}</div>
                    <div class="fw-semibold">
                        @if($patient->aestheticProfile->sun_exposure)
                            {{ $patient->aestheticProfile->sun_exposure_label }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small mb-1">{{ __('Pregnancy / Breastfeeding') }}</div>
                    <div class="fw-semibold">
                        @if($patient->aestheticProfile->is_pregnant_or_breastfeeding)
                            <span class="badge bg-danger">{{ __('Yes') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('No') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small mb-1">{{ __('Photosensitivity') }}</div>
                    <div class="fw-semibold">
                        @if($patient->aestheticProfile->photosensitivity)
                            <span class="badge bg-warning">{{ __('Yes') }}</span>
                        @else
                            <span class="badge bg-success">{{ __('No') }}</span>
                        @endif
                    </div>
                </div>
                @if(count($patient->aestheticProfile->skin_concerns_labels) > 0)
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Skin Concerns') }}</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($patient->aestheticProfile->skin_concerns_labels as $concernLabel)
                            <span class="badge bg-primary">{{ $concernLabel }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($patient->aestheticProfile->allergies)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Allergies') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->allergies }}</p>
                </div>
                @endif
                @if($patient->aestheticProfile->previous_treatments)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Previous Treatments') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->previous_treatments }}</p>
                </div>
                @endif
                @if($patient->aestheticProfile->current_skincare_routine)
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Current Skincare Routine') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->current_skincare_routine }}</p>
                </div>
                @endif
                @if($patient->aestheticProfile->desired_outcomes)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Desired Outcomes') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->desired_outcomes }}</p>
                </div>
                @endif
                @if($patient->aestheticProfile->medical_conditions)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">{{ __('Relevant Medical Conditions') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->medical_conditions }}</p>
                </div>
                @endif
                @if($patient->aestheticProfile->notes)
                <div class="col-12">
                    <div class="text-muted small mb-1">{{ __('Additional Notes') }}</div>
                    <p class="mb-0 small">{{ $patient->aestheticProfile->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Sessions Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>{{ __('Treatment Sessions') }}</h5>
                    <span class="badge bg-primary">{{ $sessions->total() }} {{ __('total') }}</span>
                </div>
                <div class="card-body">
                    @forelse($sessions as $session)
                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <div class="bg-{{ $session->status_color }} bg-opacity-10 rounded p-2 text-center" style="width: 60px;">
                                    <div class="text-{{ $session->status_color }} fw-bold">{{ $session->session_date->format('d') }}</div>
                                    <small class="text-muted">{{ $session->session_date->format('M') }}</small>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ __('Session :number', ['number' => $session->session_number]) }}
                                            @if($session->isDirectSession)
                                                <span class="badge bg-warning ms-1">{{ __('Direct') }}</span>
                                            @else
                                                <span class="badge bg-info ms-1">{{ __('Package') }}</span>
                                            @endif
                                        </h6>
                                        <p class="mb-1 text-muted">
                                            @if($session->isPackageSession)
                                                {{ $session->patientPackage?->package?->name ?? __('Package') }}
                                            @else
                                                {{ $session->treatment?->name ?? __('Direct Treatment') }}
                                            @endif
                                        </p>
                                        <span class="badge bg-{{ $session->status_color }}">{{ $session->status_display }}</span>
                                        @if($session->consentForms->count() > 0)
                                            <span class="badge bg-success ms-1"><i class="fas fa-file-signature me-1"></i>{{ __('Consent') }}</span>
                                        @endif
                                        @if($session->aftercareIssues->count() > 0)
                                            <span class="badge bg-info ms-1"><i class="fas fa-file-medical me-1"></i>{{ $session->aftercareIssues->count() }} {{ __('aftercare') }}</span>
                                        @endif
                                        @if($session->next_due_date)
                                            <span class="badge bg-warning text-dark ms-1"><i class="fas fa-bell me-1"></i>{{ __('Next Due :date', ['date' => $session->next_due_date->format('M d')]) }}</span>
                                        @endif
                                        @if($session->has_comparison)
                                            <span class="badge bg-success ms-1"><i class="fas fa-images me-1"></i>{{ __('Before/After') }}</span>
                                        @elseif($session->images->count() > 0)
                                            <span class="badge bg-primary ms-1">{{ $session->images->count() }} {{ __('images') }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('aesthetic.sessions.show', $session) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-xmark fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No aesthetic sessions found for this patient.') }}</p>
                            <a href="{{ route('aesthetic.sessions.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary mt-2">
                                {{ __('Create First Session') }}
                            </a>
                        </div>
                    @endforelse
                </div>
                @if($sessions->hasPages())
                    <div class="card-footer bg-white">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Packages -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-box me-2 text-info"></i>{{ __('Packages') }}</h5>
                    <a href="{{ route('aesthetic.patient-packages.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-plus me-1"></i>{{ __('Assign') }}
                    </a>
                </div>
                <div class="card-body">
                    @forelse($patientPackages as $pp)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold">{{ $pp->package?->name ?? __('Package') }}</div>
                                <small class="text-muted">{{ $pp->sessions_used }} / {{ $pp->total_sessions }} {{ __('sessions used') }}</small>
                                @if(($followUpReminderMap[$pp->id] ?? null)?->next_due_date)
                                    <div class="small text-warning mt-1">
                                        <i class="fas fa-bell me-1"></i>{{ __('Next due') }} {{ $followUpReminderMap[$pp->id]->next_due_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                            <span class="badge bg-{{ $pp->remaining_sessions > 0 ? 'success' : 'danger' }}">
                                {{ $pp->remaining_sessions }} {{ __('left') }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0 small">{{ __('No packages assigned yet.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bell me-2 text-warning"></i>{{ __('Next Due Follow-ups') }}</h5>
                    <span class="badge bg-warning text-dark">{{ $followUpReminders->count() ?? 0 }}</span>
                </div>
                <div class="card-body">
                    @if(($followUpReminders->isEmpty() ?? true))
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0 small">{{ __('No open follow-up reminders for this patient right now.') }}</p>
                        </div>
                    @else
                        @foreach($followUpReminders as $reminder)
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                <div>
                                    <div class="fw-semibold">{{ __('Session :number', ['number' => $reminder->session_number]) }}</div>
                                    <small class="text-muted">
                                        @if($reminder->isPackageSession)
                                            {{ $reminder->patientPackage?->package?->name ?? __('Package') }}
                                        @else
                                            {{ $reminder->effective_treatments->pluck('name')->implode(', ') ?: __('Direct Treatment') }}
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold text-warning">{{ $reminder->next_due_date?->format('M d, Y') }}</div>
                                    <a href="{{ route('aesthetic.sessions.show', $reminder) }}" class="btn btn-sm btn-outline-primary mt-1">{{ __('Open') }}</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-signature me-2 text-primary"></i>{{ __('Consent & Aftercare PDFs') }}</h5>
                    <span class="badge bg-primary">{{ ($consentForms->count() ?? 0) + ($aftercareIssues->count() ?? 0) }}</span>
                </div>
                <div class="card-body">
                    @if(($consentForms->isEmpty() ?? true) && ($aftercareIssues->isEmpty() ?? true))
                        <div class="text-center py-3 text-muted">
                            <p class="mb-0 small">{{ __('No consent or aftercare PDFs have been generated for this patient yet.') }}</p>
                        </div>
                    @else
                        @if(($consentForms->isNotEmpty() ?? false))
                            <div class="mb-3">
                                <h6 class="text-muted text-uppercase small mb-2">{{ __('Consent Forms') }}</h6>
                                @foreach($consentForms as $consent)
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ $consent->title }}</div>
                                            <small class="text-muted">{{ $consent->signed_at?->format('M d, Y h:i A') }}</small>
                                        </div>
                                        @if($consent->pdf_url)
                                            <a href="{{ $consent->pdf_url }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Open PDF') }}</a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(($aftercareIssues->isNotEmpty() ?? false))
                            <div>
                                <h6 class="text-muted text-uppercase small mb-2">{{ __('Aftercare Forms') }}</h6>
                                @foreach($aftercareIssues as $issue)
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ $issue->template_name }}</div>
                                            <small class="text-muted">{{ $issue->issued_at?->format('M d, Y h:i A') }}</small>
                                        </div>
                                        @if($issue->pdf_url)
                                            <a href="{{ $issue->pdf_url }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Open PDF') }}</a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-success"></i>{{ __('Quick Stats') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="mb-0 text-primary">{{ $stats['total_sessions'] }}</h4>
                                <small class="text-muted">{{ __('Sessions') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="mb-0 text-success">{{ $stats['completed'] }}</h4>
                                <small class="text-muted">{{ __('Completed') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="mb-0 text-warning">{{ $stats['scheduled'] }}</h4>
                                <small class="text-muted">{{ __('Scheduled') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="mb-0 text-info">{{ $stats['packages'] }}</h4>
                                <small class="text-muted">{{ __('Packages') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <h4 class="mb-0 text-warning">{{ $stats['follow_up_due'] }}</h4>
                                <small class="text-muted">{{ __('Next Due') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
