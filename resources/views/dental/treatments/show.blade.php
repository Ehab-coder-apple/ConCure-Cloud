@extends('layouts.app')

@section('title', __('Treatment Plan Details'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        {{ __('Treatment Plan') }} #{{ $dentalTreatment->treatment_number }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Created') }} {{ $dentalTreatment->created_at->format('M d, Y') }}
                        @if($dentalTreatment->creator)
                            {{ __('by') }} {{ $dentalTreatment->creator->name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/edit") }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            {{ __('PDF') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Patient Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Name') }}:</strong>
                                <a href="{{ route('patients.show', $dentalTreatment->patient) }}" class="text-decoration-none">
                                    {{ $dentalTreatment->patient->full_name }}
                                </a>
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Patient ID') }}:</strong> {{ $dentalTreatment->patient->patient_id }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Age') }}:</strong> {{ $dentalTreatment->patient->age }} {{ __('years') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Phone') }}:</strong> {{ $dentalTreatment->patient->phone ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Gender') }}:</strong> {{ ucfirst($dentalTreatment->patient->gender ?? '-') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tooth me-2"></i>
                        {{ __('Treatment Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Procedure') }}:</strong><br>
                                <span class="fs-5">{{ $dentalTreatment->procedure_name }}</span>
                                @if($dentalTreatment->procedure_code)
                                    <span class="badge bg-secondary">{{ $dentalTreatment->procedure_code }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Tooth Number(s)') }}:</strong><br>
                                @if($dentalTreatment->tooth_number)
                                    <span class="badge bg-primary fs-6">#{{ $dentalTreatment->tooth_number }}</span>
                                @endif
                                @if($dentalTreatment->tooth_numbers && count($dentalTreatment->tooth_numbers) > 0)
                                    @foreach($dentalTreatment->tooth_numbers as $tooth)
                                        <span class="badge bg-secondary">#{{ $tooth }}</span>
                                    @endforeach
                                @endif
                                @if(!$dentalTreatment->tooth_number && (!$dentalTreatment->tooth_numbers || count($dentalTreatment->tooth_numbers) == 0))
                                    <span class="text-muted">{{ __('Not specified') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($dentalTreatment->surfaces_affected && count($dentalTreatment->surfaces_affected) > 0)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Surfaces Affected') }}:</strong><br>
                                    @foreach($dentalTreatment->surfaces_affected as $surface)
                                        <span class="badge bg-info">{{ $surface }}</span>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->diagnosis)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Diagnosis') }}:</strong><br>
                                    {{ $dentalTreatment->diagnosis }}
                                    @if($dentalTreatment->icd10_code)
                                        <span class="badge bg-secondary">{{ $dentalTreatment->icd10_code }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->description)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Description') }}:</strong><br>
                                    {{ $dentalTreatment->description }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->notes)
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-0">
                                    <strong>{{ __('Notes') }}:</strong><br>
                                    {{ $dentalTreatment->notes }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cost & Payment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>
                        {{ __('Cost & Payment') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>{{ __('Estimated Cost') }}:</strong><br>
                                <span class="fs-5 text-primary">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->estimated_cost ?? 0, 2) }}</span>
                            </p>
                        </div>
                        @if($dentalTreatment->actual_cost)
                            <div class="col-md-4">
                                <p class="mb-2">
                                    <strong>{{ __('Actual Cost') }}:</strong><br>
                                    <span class="fs-5">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->actual_cost, 2) }}</span>
                                </p>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>{{ __('Payment Status') }}:</strong><br>
                                <span class="badge bg-{{ $dentalTreatment->payment_status === 'paid' ? 'success' : ($dentalTreatment->payment_status === 'partial' ? 'warning' : 'danger') }} fs-6">
                                    {{ ucfirst($dentalTreatment->payment_status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($dentalTreatment->amount_paid > 0 || $dentalTreatment->remaining_balance > 0)
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>{{ __('Amount Paid') }}:</strong>
                                    {{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->amount_paid ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>{{ __('Remaining Balance') }}:</strong>
                                    <span class="text-danger">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->remaining_balance ?? 0, 2) }}</span>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline & Dates -->
            @if($dentalTreatment->scheduled_date || $dentalTreatment->completed_at || $dentalTreatment->estimated_duration_minutes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('Timeline') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($dentalTreatment->scheduled_date)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Scheduled Date') }}:</strong><br>
                                        {{ $dentalTreatment->scheduled_date->format('M d, Y H:i') }}
                                    </p>
                                </div>
                            @endif
                            @if($dentalTreatment->completed_at)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Completed Date') }}:</strong><br>
                                        {{ $dentalTreatment->completed_at->format('M d, Y H:i') }}
                                    </p>
                                </div>
                            @endif
                            @if($dentalTreatment->estimated_duration_minutes)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Estimated Duration') }}:</strong><br>
                                        {{ $dentalTreatment->estimated_duration_minutes }} {{ __('minutes') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dental Lab Requests -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('Dental Lab Requests') }}
                    </h6>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                        <a href="{{ route('dental.lab-requests.create', ['dental_treatment_id' => $dentalTreatment->id, 'patient_id' => $dentalTreatment->patient_id]) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Send to Lab') }}
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($dentalTreatment->dentalLabRequests && $dentalTreatment->dentalLabRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Work Type') }}</th>
                                        <th>{{ __('Lab') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dentalTreatment->dentalLabRequests as $labRequest)
                                        <tr>
                                            <td>
                                                <small>{{ $labRequest->request_number }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $labRequest->work_type_display }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $labRequest->externalLab->name ?? __('Not assigned') }}</small>
                                            </td>
                                            <td>
                                                <span class="{{ $labRequest->status_badge_class }}">
                                                    {{ $labRequest->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    @if($labRequest->due_date)
                                                        {{ $labRequest->due_date->format('M d, Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('dental.lab-requests.show', $labRequest) }}"
                                                   class="btn btn-xs btn-info" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('No lab requests for this treatment yet.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Status Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>{{ __('Status') }}:</strong><br>
                        <span class="badge {{ $dentalTreatment->status_badge_class }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $dentalTreatment->status)) }}
                        </span>
                    </p>
                    <p class="mb-3">
                        <strong>{{ __('Priority') }}:</strong><br>
                        <span class="badge bg-{{ $dentalTreatment->priority === 'urgent' ? 'danger' : ($dentalTreatment->priority === 'high' ? 'warning' : ($dentalTreatment->priority === 'medium' ? 'info' : 'secondary')) }} fs-6">
                            {{ ucfirst($dentalTreatment->priority) }}
                        </span>
                    </p>
                    @if($dentalTreatment->severity)
                        <p class="mb-0">
                            <strong>{{ __('Severity') }}:</strong><br>
                            <span class="badge bg-secondary fs-6">{{ ucfirst($dentalTreatment->severity) }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Assigned Personnel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>
                        {{ __('Assigned Personnel') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($dentalTreatment->assignedDoctor)
                        <p class="mb-2">
                            <strong>{{ __('Assigned Doctor') }}:</strong><br>
                            {{ $dentalTreatment->assignedDoctor->name }}
                        </p>
                    @endif
                    @if($dentalTreatment->performedBy)
                        <p class="mb-0">
                            <strong>{{ __('Performed By') }}:</strong><br>
                            {{ $dentalTreatment->performedBy->name }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Dental Chart Link -->
            @if($dentalTreatment->dentalChart)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            {{ __('Related Dental Chart') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>{{ __('Chart Type') }}:</strong> {{ ucfirst($dentalTreatment->dentalChart->chart_type) }}
                        </p>
                        <p class="mb-3">
                            <strong>{{ __('Created') }}:</strong> {{ $dentalTreatment->dentalChart->created_at->format('M d, Y') }}
                        </p>
                        <a href="{{ url("/dental/patients/{$dentalTreatment->patient_id}/charts/{$dentalTreatment->dental_chart_id}") }}"
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('View Dental Chart') }}
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner']))
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($dentalTreatment->status !== 'completed')
                            <form method="POST" action="{{ url("/dental/treatments/{$dentalTreatment->id}/complete") }}" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('{{ __('Mark this treatment as completed?') }}')">
                                    <i class="fas fa-check me-1"></i>
                                    {{ __('Mark as Completed') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/edit") }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Treatment') }}
                        </a>

                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}" class="btn btn-outline-primary w-100 mb-2" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            {{ __('Download PDF') }}
                        </a>

                        @if(in_array(auth()->user()->role, ['admin', 'program_owner']))
                            <form method="POST" action="{{ url("/dental/treatments/{$dentalTreatment->id}") }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this treatment plan?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash me-1"></i>
                                    {{ __('Delete Treatment') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

