@extends('layouts.app')

@section('title', __('Lab Request Details'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Lab Request') }} #{{ $labRequest->request_number }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Created') }} {{ $labRequest->created_at->format('M d, Y H:i') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('dental.lab-requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'lab_dept', 'dental_dept']))
                        <a href="{{ route('dental.lab-requests.edit', $labRequest) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Patient & Treatment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient & Treatment Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Patient') }}:</strong><br>
                                <a href="{{ route('patients.show', $labRequest->patient) }}" class="text-decoration-none">
                                    {{ $labRequest->patient->full_name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $labRequest->patient->patient_id }}</small>
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Age') }}:</strong> {{ $labRequest->patient->age }} {{ __('years') }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Phone') }}:</strong> {{ $labRequest->patient->phone ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Requesting Doctor') }}:</strong><br>
                                {{ $labRequest->doctor->full_name }}
                            </p>
                            @if($labRequest->dentalTreatment)
                                <p class="mb-2">
                                    <strong>{{ __('Related Treatment') }}:</strong><br>
                                    <a href="{{ route('dental.treatments.show', $labRequest->dentalTreatment) }}" class="text-decoration-none">
                                        {{ $labRequest->dentalTreatment->treatment_number }} - {{ $labRequest->dentalTreatment->procedure_name }}
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tooth me-2"></i>
                        {{ __('Work Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Work Type') }}:</strong><br>
                                <span class="fs-5">{{ $labRequest->work_type_display }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Tooth Number(s)') }}:</strong><br>
                                <span class="fs-5">{{ $labRequest->tooth_number ?? '-' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Shade') }}:</strong> {{ $labRequest->shade ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Material') }}:</strong> {{ $labRequest->material_display ?? '-' }}
                            </p>
                        </div>
                    </div>

                    @if($labRequest->specifications)
                        <div class="mb-3">
                            <strong>{{ __('Specifications') }}:</strong>
                            <p class="mb-0 mt-1">{{ $labRequest->specifications }}</p>
                        </div>
                    @endif

                    @if($labRequest->special_instructions)
                        <div class="mb-0">
                            <strong>{{ __('Special Instructions') }}:</strong>
                            <p class="mb-0 mt-1">{{ $labRequest->special_instructions }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lab Information -->
            @if($labRequest->externalLab)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-flask me-2"></i>
                            {{ __('Laboratory Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>{{ __('Lab Name') }}:</strong><br>
                                    {{ $labRequest->externalLab->name }}
                                </p>
                                @if($labRequest->externalLab->phone)
                                    <p class="mb-2">
                                        <strong>{{ __('Phone') }}:</strong> {{ $labRequest->externalLab->phone }}
                                    </p>
                                @endif
                                @if($labRequest->externalLab->whatsapp)
                                    <p class="mb-2">
                                        <strong>{{ __('WhatsApp') }}:</strong>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $labRequest->externalLab->whatsapp) }}" target="_blank">
                                            {{ $labRequest->externalLab->whatsapp }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($labRequest->externalLab->email)
                                    <p class="mb-2">
                                        <strong>{{ __('Email') }}:</strong>
                                        <a href="mailto:{{ $labRequest->externalLab->email }}">{{ $labRequest->externalLab->email }}</a>
                                    </p>
                                @endif
                                @if($labRequest->externalLab->address)
                                    <p class="mb-2">
                                        <strong>{{ __('Address') }}:</strong><br>
                                        {{ $labRequest->externalLab->address }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Files -->
            @if($labRequest->prescription_file_path || $labRequest->impression_file_path || $labRequest->result_file_path)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-file me-2"></i>
                            {{ __('Attached Files') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($labRequest->prescription_file_path)
                                <div class="col-md-4 mb-3">
                                    <strong>{{ __('Prescription') }}:</strong><br>
                                    <a href="{{ Storage::url($labRequest->prescription_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-download me-1"></i>
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            @endif
                            @if($labRequest->impression_file_path)
                                <div class="col-md-4 mb-3">
                                    <strong>{{ __('Impression') }}:</strong><br>
                                    <a href="{{ Storage::url($labRequest->impression_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-download me-1"></i>
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            @endif
                            @if($labRequest->result_file_path)
                                <div class="col-md-4 mb-3">
                                    <strong>{{ __('Result') }}:</strong><br>
                                    <a href="{{ Storage::url($labRequest->result_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-download me-1"></i>
                                        {{ __('Download') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Communication & Notes -->
            @if($labRequest->communication_notes || $labRequest->notes || $labRequest->quality_notes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-comments me-2"></i>
                            {{ __('Communication & Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($labRequest->communication_method && $labRequest->sent_at)
                            <div class="mb-3">
                                <strong>{{ __('Communication') }}:</strong>
                                <p class="mb-0 mt-1">
                                    {{ __('Sent via') }} <span class="badge bg-info">{{ ucfirst($labRequest->communication_method) }}</span>
                                    {{ __('on') }} {{ $labRequest->sent_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                        @endif

                        @if($labRequest->communication_notes)
                            <div class="mb-3">
                                <strong>{{ __('Communication Notes') }}:</strong>
                                <p class="mb-0 mt-1">{{ $labRequest->communication_notes }}</p>
                            </div>
                        @endif

                        @if($labRequest->notes)
                            <div class="mb-3">
                                <strong>{{ __('Additional Notes') }}:</strong>
                                <p class="mb-0 mt-1">{{ $labRequest->notes }}</p>
                            </div>
                        @endif

                        @if($labRequest->quality_notes)
                            <div class="mb-0">
                                <strong>{{ __('Quality Notes') }}:</strong>
                                <p class="mb-0 mt-1">{{ $labRequest->quality_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
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
                        <span class="{{ $labRequest->status_badge_class }} fs-6">
                            {{ $labRequest->status_display }}
                        </span>
                    </p>
                    <p class="mb-3">
                        <strong>{{ __('Priority') }}:</strong><br>
                        <span class="{{ $labRequest->priority_badge_class }} fs-6">
                            {{ $labRequest->priority_display }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        {{ __('Timeline') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>{{ __('Requested Date') }}:</strong><br>
                        {{ $labRequest->requested_date->format('M d, Y') }}
                    </p>
                    @if($labRequest->due_date)
                        <p class="mb-2">
                            <strong>{{ __('Due Date') }}:</strong><br>
                            {{ $labRequest->due_date->format('M d, Y') }}
                            @if($labRequest->due_date->isPast() && $labRequest->status !== 'completed')
                                <br><span class="badge bg-danger">{{ __('Overdue') }}</span>
                            @endif
                        </p>
                    @endif
                    @if($labRequest->received_date)
                        <p class="mb-0">
                            <strong>{{ __('Received Date') }}:</strong><br>
                            {{ $labRequest->received_date->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Cost Information -->
            @if($labRequest->estimated_cost || $labRequest->actual_cost)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>
                            {{ __('Cost Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($labRequest->estimated_cost)
                            <p class="mb-2">
                                <strong>{{ __('Estimated Cost') }}:</strong><br>
                                {{ $labRequest->currency }} {{ number_format($labRequest->estimated_cost, 2) }}
                            </p>
                        @endif
                        @if($labRequest->actual_cost)
                            <p class="mb-0">
                                <strong>{{ __('Actual Cost') }}:</strong><br>
                                {{ $labRequest->currency }} {{ number_format($labRequest->actual_cost, 2) }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'lab_dept']))
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('dental.lab-requests.edit', $labRequest) }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Request') }}
                        </a>

                        @if(in_array(auth()->user()->role, ['admin', 'program_owner']))
                            <form method="POST" action="{{ route('dental.lab-requests.destroy', $labRequest) }}"
                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this lab request?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash me-1"></i>
                                    {{ __('Delete Request') }}
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

