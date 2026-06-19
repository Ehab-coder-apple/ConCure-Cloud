@extends('layouts.app')

@php
    $patientName = $aestheticSession->isPackageSession
        ? ($aestheticSession->patientPackage?->patient?->first_name . ' ' . $aestheticSession->patientPackage?->patient?->last_name)
        : ($aestheticSession->patient?->first_name . ' ' . $aestheticSession->patient?->last_name);
    $latestConsent = $aestheticSession->consentForms->sortByDesc(fn ($consent) => $consent->signed_at?->timestamp ?? 0)->first();
    $aftercareIssues = $aestheticSession->aftercareIssues->sortByDesc(fn ($issue) => $issue->issued_at?->timestamp ?? 0);
    $consentObtained = (bool) $latestConsent;
    $nextDueDateValue = old('next_due_date', optional($suggestedNextDueDate)->format('Y-m-d'));
@endphp

@section('title', __('Session :number - :patient', ['number' => $aestheticSession->session_number, 'patient' => $patientName ?? '']))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        {{ __('Session :number', ['number' => $aestheticSession->session_number]) }}
                        @if($aestheticSession->isDirectSession)
                            <span class="badge bg-warning ms-2">{{ __('Direct Treatment') }}</span>
                        @endif
                    </h1>
                    <p class="text-muted mb-0">
                        @if($aestheticSession->isPackageSession)
                            {{ $aestheticSession->patientPackage?->patient?->first_name }} {{ $aestheticSession->patientPackage?->patient?->last_name }}
                            - {{ $aestheticSession->patientPackage?->package?->name ?? __('Package') }}
                        @else
                            {{ $aestheticSession->patient?->first_name }} {{ $aestheticSession->patient?->last_name }}
                            @if($aestheticSession->treatment)- {{ $aestheticSession->treatment->name }}@endif
                        @endif
                    </p>
                    @if($aestheticSession->assigned_user_id)
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-user-md me-1"></i>{{ __('Assigned Person: :name', ['name' => $aestheticSession->assigned_person_display]) }}
                        </p>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('aesthetic.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>

                    <a href="{{ route('aesthetic.invoices.create', ['session_id' => $aestheticSession->id]) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-invoice-dollar me-1"></i>
                        {{ __('Create Invoice') }}
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-check-double me-1"></i>{{ __('Status') }}
                        </button>
                        <ul class="dropdown-menu">
                            @foreach(\App\Models\AestheticSession::STATUSES as $key => $label)
                                @continue(!$consentObtained && in_array($key, ['started', 'completed'], true))
                                @if($key !== $aestheticSession->status)
                                <li>
                                    <form method="POST" action="{{ route('aesthetic.sessions.update', $aestheticSession) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="session_mode" value="{{ $aestheticSession->isPackageSession ? 'package' : 'direct' }}">
                                        <input type="hidden" name="patient_package_id" value="{{ $aestheticSession->patient_package_id ?? '' }}">
                                        <input type="hidden" name="patient_id" value="{{ $aestheticSession->patient_id ?? '' }}">
                                        <input type="hidden" name="treatment_id" value="{{ $aestheticSession->treatment_id ?? '' }}">
                                        <input type="hidden" name="session_number" value="{{ $aestheticSession->session_number }}">
                                        <input type="hidden" name="session_date" value="{{ $aestheticSession->session_date->format('Y-m-d') }}">
                                        <input type="hidden" name="notes" value="{{ $aestheticSession->notes ?? '' }}">
                                        <input type="hidden" name="status" value="{{ $key }}">
                                        <button type="submit" class="dropdown-item">{{ __($label) }}</button>
                                    </form>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <a href="{{ route('aesthetic.sessions.edit', $aestheticSession) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <h6 class="mb-2"><i class="fas fa-triangle-exclamation me-2"></i>{{ __('Please review the following') }}</h6>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Session Info Card -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar fa-2x text-primary mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->session_date->format('M d, Y') }}</h5>
                            <small class="text-muted">{{ __('Session Date') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-info-circle fa-2x text-{{ $aestheticSession->status_color }} mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->status_display }}</h5>
                            <small class="text-muted">{{ __('Status') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-images fa-2x text-info mb-2"></i>
                            <h5 class="mb-1">{{ $aestheticSession->images->count() }}</h5>
                            <small class="text-muted">{{ __('Images') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-spa fa-2x text-success mb-2"></i>
                            @if($aestheticSession->isPackageSession)
                                @if($aestheticSession->patientPackage?->package?->treatments?->count() > 0)
                                    <h5 class="mb-1">
                                        @foreach($aestheticSession->patientPackage->package->treatments as $pt)
                                            {{ $pt->name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </h5>
                                @else
                                    <h5 class="mb-1">{{ $aestheticSession->patientPackage?->package?->treatment?->name ?? '-' }}</h5>
                                @endif
                                <small class="text-muted">{{ __('Package Treatments') }}</small>
                            @else
                                <h5 class="mb-1">{{ $aestheticSession->treatment?->name ?? '-' }}</h5>
                                <small class="text-muted">{{ __('Direct Treatment') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($aestheticSession->assigned_user_id)
                <div class="alert alert-light border d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-user-md text-info"></i>
                    <span><strong>{{ __('Assigned Person:') }}</strong> {{ $aestheticSession->assigned_person_display }}</span>
                </div>
            @endif

            @if($aestheticSession->notes)
            <div class="alert alert-light border mb-4">
                <h6 class="alert-heading"><i class="fas fa-sticky-note me-2"></i>{{ __('Notes') }}</h6>
                <p class="mb-0">{{ $aestheticSession->notes }}</p>
            </div>
            @endif

            @if($aestheticSession->isPackageSession)
                <div class="card mb-4 border-{{ $aestheticSession->has_open_reminder ? 'warning' : 'light' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-bell me-2 text-warning"></i>{{ __('Next Due Reminder') }}
                        </h6>
                        @if($aestheticSession->next_due_date)
                            <span class="badge bg-warning text-dark">{{ $aestheticSession->next_due_date->format('M d, Y') }}</span>
                        @elseif($aestheticSession->has_pending_follow_up_slot)
                            <span class="badge bg-light text-dark border">{{ __('Suggested') }} {{ optional($suggestedNextDueDate)->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!$aestheticSession->has_pending_follow_up_slot)
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-circle-check me-2"></i>{{ __('This package has no remaining sessions to schedule after this appointment.') }}
                            </div>
                        @else
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-8">
                                    <form method="POST" action="{{ route('aesthetic.sessions.update', $aestheticSession) }}" class="row g-3">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="session_mode" value="package">
                                        <input type="hidden" name="patient_package_id" value="{{ $aestheticSession->patient_package_id }}">
                                        <input type="hidden" name="session_number" value="{{ $aestheticSession->session_number }}">
                                        <input type="hidden" name="session_date" value="{{ $aestheticSession->session_date->format('Y-m-d') }}">
                                        <input type="hidden" name="status" value="completed">
                                        <input type="hidden" name="notes" value="{{ $aestheticSession->notes ?? '' }}">
                                        <div class="col-md-7">
                                            <label class="form-label">{{ __('Next Due Date (Optional)') }}</label>
                                            <input type="date" name="next_due_date" class="form-control @error('next_due_date') is-invalid @enderror" value="{{ $nextDueDateValue }}" min="{{ $aestheticSession->session_date->format('Y-m-d') }}">
                                            @error('next_due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">{{ __('Suggested interval: :weeks weeks after this session.', ['weeks' => 4]) }}</small>
                                        </div>
                                        <div class="col-md-5 d-flex gap-2 align-items-end">
                                            <button type="submit" class="btn btn-warning w-100">
                                                <i class="fas fa-calendar-plus me-1"></i>
                                                {{ $aestheticSession->status === 'completed' ? __('Save Reminder') : __('Mark Completed & Save Reminder') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-lg-4">
                                    @if($aestheticSession->has_subsequent_package_session)
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-calendar-check me-2"></i>{{ __('A later package session already exists, so this reminder is informational only.') }}
                                        </div>
                                    @else
                                        <div class="small text-muted">
                                            {{ __('Use this reminder to know when the next package session should be booked after completion.') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-signature me-2 text-primary"></i>{{ __('Consent & Aftercare') }}
                    </h6>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-{{ $consentObtained ? 'success' : 'warning text-dark' }}">{{ $consentObtained ? __('Consent Signed') : __('Consent Pending') }}</span>
                        <span class="badge bg-info">{{ $aftercareIssues->count() }} {{ __('Aftercare PDF(s)') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3" id="session-doc-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="consent-tab" data-bs-toggle="pill" data-bs-target="#consent-pane" type="button" role="tab">{{ __('Consent') }}</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="aftercare-tab" data-bs-toggle="pill" data-bs-target="#aftercare-pane" type="button" role="tab">{{ __('Aftercare') }}</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="consent-pane" role="tabpanel">
                            @if($latestConsent)
                                <div class="border rounded p-3 mb-4 bg-light">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $latestConsent->title }}</div>
                                            <div class="small text-muted">{{ __('Signed by') }} {{ $latestConsent->signer_name ?: $patientName }} • {{ $latestConsent->signed_at?->format('M d, Y h:i A') }}</div>
                                            @if($latestConsent->treatment)
                                                <div class="small text-muted">{{ __('Treatment') }}: {{ $latestConsent->treatment->name }}</div>
                                            @endif
                                        </div>
                                        @if($latestConsent->pdf_url)
                                            <a href="{{ $latestConsent->pdf_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-file-pdf me-1"></i>{{ __('Open Consent PDF') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-warning">
                                    <i class="fas fa-circle-info me-2"></i>{{ __('A consent form must be signed before this session can move to Started or Completed status.') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('aesthetic.sessions.consent.store', $aestheticSession) }}" id="consentFormCapture">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Consent Title') }}</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', __('Aesthetic Procedure Consent')) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Signer Name') }}</label>
                                        <input type="text" class="form-control @error('signer_name') is-invalid @enderror" name="signer_name" value="{{ old('signer_name', trim($patientName)) }}">
                                    </div>
                                    @if(($sessionTreatments ?? collect())->count() > 0)
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Treatment (Optional)') }}</label>
                                            <select class="form-select @error('treatment_id') is-invalid @enderror" name="treatment_id">
                                                <option value="">{{ __('Link to session only') }}</option>
                                                @foreach($sessionTreatments as $treatment)
                                                    <option value="{{ $treatment->id }}" {{ (string) old('treatment_id', $aestheticSession->treatment_id ?? '') === (string) $treatment->id ? 'selected' : '' }}>{{ $treatment->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Consent Body') }}</label>
                                        <textarea class="form-control @error('body') is-invalid @enderror" name="body" rows="6" required>{{ old('body', __('I confirm that the procedure, expected benefits, possible risks, aftercare expectations, and alternative options have been explained to me. I have had the opportunity to ask questions and I agree to proceed with this aesthetic session.')) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Electronic Signature') }}</label>
                                        <div class="border rounded bg-white p-2">
                                            <canvas id="consentSignaturePad" class="w-100" style="height:220px;"></canvas>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                                            <span>{{ __('Please sign inside the box using a mouse, trackpad, or touch input.') }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearConsentSignature">{{ __('Clear Signature') }}</button>
                                        </div>
                                        <input type="hidden" name="signature_data" id="consent_signature_data">
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-file-signature me-1"></i>{{ __('Save Signed Consent') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="aftercare-pane" role="tabpanel">
                            @if(($aftercareTemplates ?? collect())->isEmpty())
                                <div class="alert alert-info border-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span><i class="fas fa-circle-info me-2"></i>{{ __('No aftercare templates are configured yet for this clinic.') }}</span>
                                    <a href="{{ route('aesthetic.aftercare-templates.create') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-plus me-1"></i>{{ __('Create Template') }}
                                    </a>
                                </div>
                            @else
                                <form method="POST" action="{{ route('aesthetic.sessions.aftercare.store', $aestheticSession) }}" class="border rounded p-3 mb-4 bg-light">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Aftercare Template') }}</label>
                                            <select class="form-select @error('aftercare_template_id') is-invalid @enderror" name="aftercare_template_id" required>
                                                <option value="">{{ __('Select Template') }}</option>
                                                @foreach($aftercareTemplates as $template)
                                                    <option value="{{ $template->id }}" {{ (string) old('aftercare_template_id') === (string) $template->id ? 'selected' : '' }}>{{ $template->name }} — {{ $template->category_display }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if(($sessionTreatments ?? collect())->count() > 0)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Treatment (Optional)') }}</label>
                                                <select class="form-select @error('treatment_id') is-invalid @enderror" name="treatment_id">
                                                    <option value="">{{ __('Use session default') }}</option>
                                                    @foreach($sessionTreatments as $treatment)
                                                        <option value="{{ $treatment->id }}">{{ $treatment->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                        <div class="col-12">
                                            <label class="form-label">{{ __('Practitioner Notes (Optional)') }}</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="3" placeholder="{{ __('Add patient-specific aftercare notes...') }}">{{ old('notes') }}</textarea>
                                        </div>
                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-file-medical me-1"></i>{{ __('Issue Aftercare PDF') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif

                            @if($aftercareIssues->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Template') }}</th>
                                                <th>{{ __('Issued') }}</th>
                                                <th>{{ __('Notes') }}</th>
                                                <th class="text-end">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($aftercareIssues as $issue)
                                                @php($issuePhone = $issue->patient?->whatsapp_phone ?: $issue->patient?->phone)
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold">{{ $issue->template_name }}</div>
                                                        <div class="small text-muted">{{ $issue->template_category_display }}</div>
                                                    </td>
                                                    <td>{{ $issue->issued_at?->format('M d, Y h:i A') }}</td>
                                                    <td>{{ $issue->notes ?: '—' }}</td>
                                                    <td class="text-end">
                                                        <div class="d-inline-flex gap-2">
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-success btn-sm js-send-aftercare-whatsapp"
                                                                data-url="{{ route('aesthetic.sessions.aftercare.send-whatsapp', [$aestheticSession, $issue]) }}"
                                                                {{ $issuePhone ? '' : 'disabled' }}
                                                                title="{{ $issuePhone ? __('Send aftercare reminder via WhatsApp') : __('Patient has no WhatsApp number') }}"
                                                            >
                                                                <i class="fab fa-whatsapp me-1"></i>{{ __('WhatsApp') }}
                                                            </button>

                                                            @if($issue->pdf_url)
                                                                <a href="{{ $issue->pdf_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                                    <i class="fas fa-file-pdf me-1"></i>{{ __('Open') }}
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-file-medical fa-2x mb-2"></i>
                                    <p class="mb-0">{{ __('No aftercare forms have been issued for this session yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Before/After Comparison -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-images me-2"></i>
                        {{ __('Before / After Comparison') }}
                    </h6>
                    @if($aestheticSession->has_comparison)
                        <span class="badge bg-success">{{ __('Comparison Ready') }}</span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#demoComparisonModal">
                            <i class="fas fa-eye me-1"></i>{{ __('View Demo') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Before Images -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-camera me-2 text-warning"></i>
                                {{ __('Before') }}
                                <span class="badge bg-warning ms-2">{{ $aestheticSession->beforeImages->count() }}</span>
                            </h6>
                            @if($aestheticSession->beforeImages->count() > 0)
                                <div class="row g-2">
                                    @foreach($aestheticSession->beforeImages as $image)
                                    <div class="col-6 col-lg-4">
                                        <div class="position-relative">
                                            <a href="{{ $image->image_url }}" target="_blank">
                                                <img src="{{ $image->image_url }}" class="img-fluid rounded border" alt="Before"
                                                     style="width: 100%; height: 150px; object-fit: cover;">
                                            </a>
                                            <form method="POST" action="{{ route('aesthetic.sessions.images.destroy', [$aestheticSession, $image]) }}"
                                                  class="position-absolute top-0 end-0 m-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Delete this image?') }}')"
                                                        style="padding: 2px 6px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0 small">{{ __('No before images yet') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- After Images -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-camera me-2 text-success"></i>
                                {{ __('After') }}
                                <span class="badge bg-success ms-2">{{ $aestheticSession->afterImages->count() }}</span>
                            </h6>
                            @if($aestheticSession->afterImages->count() > 0)
                                <div class="row g-2">
                                    @foreach($aestheticSession->afterImages as $image)
                                    <div class="col-6 col-lg-4">
                                        <div class="position-relative">
                                            <a href="{{ $image->image_url }}" target="_blank">
                                                <img src="{{ $image->image_url }}" class="img-fluid rounded border" alt="After"
                                                     style="width: 100%; height: 150px; object-fit: cover;">
                                            </a>
                                            <form method="POST" action="{{ route('aesthetic.sessions.images.destroy', [$aestheticSession, $image]) }}"
                                                  class="position-absolute top-0 end-0 m-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('{{ __('Delete this image?') }}')"
                                                        style="padding: 2px 6px; font-size: 10px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-camera fa-2x mb-2"></i>
                                    <p class="mb-0 small">{{ __('No after images yet') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Comparison Modal -->
            <div class="modal fade" id="demoComparisonModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-images me-2"></i>{{ __('Demo: Before / After Comparison') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="alert alert-info m-3 mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('This is a demo showing how before and after images will appear once uploaded to a session.') }}
                            </div>
                            <div class="p-3">
                                <div class="row g-3">
                                    <!-- Demo Before -->
                                    <div class="col-md-6">
                                        <div class="card border-warning h-100">
                                            <div class="card-header bg-warning text-dark">
                                                <i class="fas fa-calendar-day me-2"></i>{{ __('Before Treatment') }}
                                                <span class="badge bg-dark ms-2">{{ __('Demo') }}</span>
                                            </div>
                                            <div class="card-body text-center p-0">
                                                <div class="demo-image-placeholder bg-secondary bg-opacity-10 d-flex flex-column align-items-center justify-content-center"
                                                     style="height: 280px;">
                                                    <i class="fas fa-user-circle fa-5x text-secondary mb-3"></i>
                                                    <p class="text-muted fw-bold mb-1">{{ __('BEFORE') }}</p>
                                                    <p class="text-muted small">{{ __('Patient photo before treatment session') }}</p>
                                                    <span class="badge bg-warning mt-2">{{ __('Day 0') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Demo After -->
                                    <div class="col-md-6">
                                        <div class="card border-success h-100">
                                            <div class="card-header bg-success text-white">
                                                <i class="fas fa-calendar-check me-2"></i>{{ __('After Treatment') }}
                                                <span class="badge bg-light text-success ms-2">{{ __('Demo') }}</span>
                                            </div>
                                            <div class="card-body text-center p-0">
                                                <div class="demo-image-placeholder bg-success bg-opacity-10 d-flex flex-column align-items-center justify-content-center"
                                                     style="height: 280px;">
                                                    <i class="fas fa-user-check fa-5x text-success mb-3"></i>
                                                    <p class="text-success fw-bold mb-1">{{ __('AFTER') }}</p>
                                                    <p class="text-muted small">{{ __('Patient photo after treatment session') }}</p>
                                                    <span class="badge bg-success mt-2">{{ __('Day 30') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Demo Comparison Slider -->
                                <div class="mt-4">
                                    <h6 class="text-center mb-3">
                                        <i class="fas fa-exchange-alt me-2 text-primary"></i>{{ __('Side-by-Side Comparison View') }}
                                    </h6>
                                    <div class="row g-0">
                                        <div class="col-6 position-relative">
                                            <div class="bg-dark text-white text-center py-5" style="border-radius: 8px 0 0 8px;">
                                                <i class="fas fa-user fa-4x text-secondary mb-2"></i>
                                                <p class="mb-0 fw-bold">{{ __('BEFORE') }}</p>
                                                <small class="text-muted">{{ __('Baseline') }}</small>
                                            </div>
                                            <span class="position-absolute top-0 start-0 m-2 badge bg-warning">{{ __('Before') }}</span>
                                        </div>
                                        <div class="col-6 position-relative">
                                            <div class="bg-primary text-white text-center py-5" style="border-radius: 0 8px 8px 0;">
                                                <i class="fas fa-user-check fa-4x text-white mb-2"></i>
                                                <p class="mb-0 fw-bold">{{ __('AFTER') }}</p>
                                                <small class="text-white-50">{{ __('Post Session') }}</small>
                                            </div>
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-success">{{ __('After') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <a href="#upload-section" class="btn btn-primary" onclick="document.getElementById('upload-section').scrollIntoView({behavior:'smooth'});" data-bs-dismiss="modal">
                                <i class="fas fa-upload me-1"></i>{{ __('Upload Real Images') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Usage -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-boxes me-2 text-primary"></i>
                        {{ __('Inventory Used') }}
                    </h6>
                    @if($aestheticSession->inventoryUsages->count() > 0)
                        <span class="badge bg-success">{{ __('Stock Deducted') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('No Items Recorded') }}</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($aestheticSession->inventoryUsages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Quantity Used') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($aestheticSession->inventoryUsages as $usage)
                                    <tr>
                                        <td>{{ $usage->product->product_name }}</td>
                                        <td><span class="badge bg-secondary">{{ \App\Models\AestheticInventory::TYPES[$usage->product->type] ?? $usage->product->type }}</span></td>
                                        <td><strong>{{ $usage->quantity_used }}</strong></td>
                                        <td>{{ $usage->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-boxes fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No inventory items recorded for this session.') }}</p>
                            <p class="mb-0 small">{{ __('Add inventory items when creating or editing the session.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upload Images -->
            <div class="row" id="upload-section">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-upload me-2 text-warning"></i>
                                {{ __('Upload Before Images') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.images.store', $aestheticSession) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="before">
                                <div class="mb-3">
                                    <input type="file" class="form-control @error('images') is-invalid @enderror"
                                           name="images[]" accept="image/*" multiple required>
                                    <small class="form-text text-muted">{{ __('You can select multiple images') }}</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-upload me-1"></i>
                                    {{ __('Upload Before Images') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-upload me-2 text-success"></i>
                                {{ __('Upload After Images') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('aesthetic.sessions.images.store', $aestheticSession) }}"
                                  enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="type" value="after">
                                <div class="mb-3">
                                    <input type="file" class="form-control @error('images') is-invalid @enderror"
                                           name="images[]" accept="image/*" multiple required>
                                    <small class="form-text text-muted">{{ __('You can select multiple images') }}</small>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-upload me-1"></i>
                                    {{ __('Upload After Images') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const canvas = document.getElementById('consentSignaturePad');
    const hiddenInput = document.getElementById('consent_signature_data');
    const clearBtn = document.getElementById('clearConsentSignature');
    const form = document.getElementById('consentFormCapture');

    if (!canvas || !form) {
        return;
    }

    const context = canvas.getContext('2d');
    let drawing = false;
    let hasSignature = false;

    function resetStyles() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        context.setTransform(1, 0, 0, 1, 0, 0);
        context.scale(ratio, ratio);
        context.lineWidth = 2;
        context.lineJoin = 'round';
        context.lineCap = 'round';
        context.strokeStyle = '#111827';
    }

    function clearCanvas() {
        context.setTransform(1, 0, 0, 1, 0, 0);
        context.clearRect(0, 0, canvas.width, canvas.height);
        resetStyles();
        hasSignature = false;
        hiddenInput.value = '';
    }

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        clearCanvas();
    }

    function getPosition(event) {
        const rect = canvas.getBoundingClientRect();
        const point = event.touches ? event.touches[0] : event;

        return { x: point.clientX - rect.left, y: point.clientY - rect.top };
    }

    function startDrawing(event) {
        drawing = true;
        const position = getPosition(event);
        context.beginPath();
        context.moveTo(position.x, position.y);
        event.preventDefault();
    }

    function draw(event) {
        if (!drawing) {
            return;
        }

        const position = getPosition(event);
        context.lineTo(position.x, position.y);
        context.stroke();
        hasSignature = true;
        event.preventDefault();
    }

    function endDrawing() {
        drawing = false;
    }

    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', endDrawing);
    canvas.addEventListener('mouseleave', endDrawing);
    canvas.addEventListener('touchstart', startDrawing, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    canvas.addEventListener('touchend', endDrawing);
    clearBtn?.addEventListener('click', clearCanvas);

    form.addEventListener('submit', function (event) {
        if (!hasSignature) {
            event.preventDefault();
            alert(@json(__('Please capture the patient signature before saving the consent form.')));
            return;
        }

        hiddenInput.value = canvas.toDataURL('image/png');
    });

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
})();

(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('.js-send-aftercare-whatsapp').forEach((button) => {
        button.addEventListener('click', async () => {
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __('Sending...') }}';

            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || '{{ __('Failed to send the aftercare reminder.') }}');
                }

                if (data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank', 'noopener');
                }

                button.classList.remove('btn-outline-success', 'btn-outline-danger');
                button.classList.add('btn-success');
                button.innerHTML = '<i class="fas fa-check me-1"></i>{{ __('Ready') }}';
            } catch (error) {
                button.classList.remove('btn-outline-success', 'btn-success');
                button.classList.add('btn-outline-danger');
                button.innerHTML = '<i class="fas fa-times me-1"></i>{{ __('Failed') }}';
                button.title = error.message;
            }

            setTimeout(() => {
                button.disabled = false;
                button.classList.remove('btn-success', 'btn-outline-danger');
                button.classList.add('btn-outline-success');
                button.innerHTML = originalHtml;
            }, 2500);
        });
    });
})();
</script>
@endpush
