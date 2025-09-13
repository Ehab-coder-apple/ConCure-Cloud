@extends('layouts.app')

@section('page-title', __('Radiology Request') . ' - ' . $radiologyRequest->request_number)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-x-ray text-primary"></i>
                        {{ __('Radiology Request') }} - {{ $radiologyRequest->request_number }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Created on') }} {{ $radiologyRequest->created_at->format('M d, Y \a\t g:i A') }}
                        {{ __('by') }} {{ $radiologyRequest->doctor->full_name }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('recommendations.radiology.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Requests') }}
                    </a>
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
                        <i class="fas fa-user"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Name') }}:</strong> {{ $radiologyRequest->patient->full_name }}<br>
                            <strong>{{ __('Patient ID') }}:</strong> {{ $radiologyRequest->patient->patient_id }}<br>
                            <strong>{{ __('Age') }}:</strong> {{ $radiologyRequest->patient->age }} {{ __('years') }}<br>
                            <strong>{{ __('Gender') }}:</strong> {{ ucfirst($radiologyRequest->patient->gender) }}
                        </div>
                        <div class="col-md-6">
                            @if($radiologyRequest->patient->phone)
                            <strong>{{ __('Phone') }}:</strong> {{ $radiologyRequest->patient->phone }}<br>
                            @endif
                            @if($radiologyRequest->patient->email)
                            <strong>{{ __('Email') }}:</strong> {{ $radiologyRequest->patient->email }}<br>
                            @endif
                            @if($radiologyRequest->patient->allergies)
                            <strong>{{ __('Allergies') }}:</strong> <span class="text-danger">{{ $radiologyRequest->patient->allergies }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-stethoscope"></i>
                        {{ __('Clinical Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($radiologyRequest->suspected_diagnosis)
                    <div class="mb-3">
                        <strong>{{ __('Suspected Diagnosis') }}:</strong>
                        <span class="badge bg-info ms-2">{{ $radiologyRequest->suspected_diagnosis }}</span>
                    </div>
                    @endif

                    @if($radiologyRequest->clinical_notes)
                    <div class="mb-3">
                        <strong>{{ __('Clinical Notes') }}:</strong>
                        <p class="mt-1 mb-0">{{ $radiologyRequest->clinical_notes }}</p>
                    </div>
                    @endif

                    @if($radiologyRequest->clinical_history)
                    <div class="mb-3">
                        <strong>{{ __('Clinical History') }}:</strong>
                        <p class="mt-1 mb-0">{{ $radiologyRequest->clinical_history }}</p>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Priority') }}:</strong>
                            <span class="{{ $radiologyRequest->priority_badge_class }}">{{ $radiologyRequest->priority_display }}</span>
                        </div>
                        <div class="col-md-6">
                            @if($radiologyRequest->due_date)
                            <strong>{{ __('Due Date') }}:</strong> {{ $radiologyRequest->due_date->format('M d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tests Required -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-x-ray"></i>
                        {{ __('Tests Required') }}
                        <span class="badge bg-primary ms-2">{{ $radiologyRequest->tests->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($radiologyRequest->tests as $test)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">{{ $test->test_name_display }}</h6>
                            <div>
                                @if($test->urgent)
                                <span class="badge bg-danger">{{ __('Urgent') }}</span>
                                @endif
                                @if($test->with_contrast)
                                <span class="badge bg-warning">{{ __('With Contrast') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($test->radiologyTest)
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>{{ __('Category') }}:</strong> {{ $test->test_category }}<br>
                                    <strong>{{ __('Body Part') }}:</strong> {{ $test->body_part }}
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <strong>{{ __('Duration') }}:</strong> {{ $test->estimated_duration }}<br>
                                    <strong>{{ __('Preparation') }}:</strong> {{ $test->preparation_instructions ?: 'None' }}
                                </small>
                            </div>
                        </div>
                        @endif

                        @if($test->clinical_indication)
                        <div class="mb-2">
                            <strong>{{ __('Clinical Indication') }}:</strong> {{ $test->clinical_indication }}
                        </div>
                        @endif

                        @if($test->instructions)
                        <div class="mb-2">
                            <strong>{{ __('Instructions') }}:</strong> {{ $test->instructions }}
                        </div>
                        @endif

                        @if($test->special_requirements)
                        <div class="mb-2">
                            <strong>{{ __('Special Requirements') }}:</strong> {{ $test->special_requirements }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Radiology Center Information -->
            @if($radiologyRequest->radiology_center_name || $radiologyRequest->radiology_center_phone || $radiologyRequest->radiology_center_email)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-hospital"></i>
                        {{ __('Radiology Center') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($radiologyRequest->radiology_center_name)
                            <strong>{{ __('Name') }}:</strong> {{ $radiologyRequest->radiology_center_name }}<br>
                            @endif
                            @if($radiologyRequest->radiology_center_phone)
                            <strong>{{ __('Phone') }}:</strong> {{ $radiologyRequest->radiology_center_phone }}<br>
                            @endif
                            @if($radiologyRequest->radiology_center_whatsapp)
                            <strong>{{ __('WhatsApp') }}:</strong> {{ $radiologyRequest->radiology_center_whatsapp }}<br>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($radiologyRequest->radiology_center_email)
                            <strong>{{ __('Email') }}:</strong> {{ $radiologyRequest->radiology_center_email }}<br>
                            @endif
                            @if($radiologyRequest->radiology_center_address)
                            <strong>{{ __('Address') }}:</strong><br>
                            <address class="mb-0">{{ $radiologyRequest->radiology_center_address }}</address>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Results -->
            @if($radiologyRequest->hasResults())
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-medical"></i>
                        {{ __('Results') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($radiologyRequest->result_file_path)
                    <div class="mb-3">
                        <strong>{{ __('Result File') }}:</strong>
                        <a href="{{ Storage::url($radiologyRequest->result_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-download me-1"></i>
                            {{ __('Download') }}
                        </a>
                    </div>
                    @endif

                    @if($radiologyRequest->radiologist_report)
                    <div class="mb-3">
                        <strong>{{ __('Radiologist Report') }}:</strong>
                        <div class="mt-1 p-2 bg-light rounded">{{ $radiologyRequest->radiologist_report }}</div>
                    </div>
                    @endif

                    @if($radiologyRequest->findings)
                    <div class="mb-3">
                        <strong>{{ __('Findings') }}:</strong>
                        <div class="mt-1 p-2 bg-light rounded">{{ $radiologyRequest->findings }}</div>
                    </div>
                    @endif

                    @if($radiologyRequest->impression)
                    <div class="mb-3">
                        <strong>{{ __('Impression') }}:</strong>
                        <div class="mt-1 p-2 bg-light rounded">{{ $radiologyRequest->impression }}</div>
                    </div>
                    @endif

                    @if($radiologyRequest->result_received_at)
                    <div class="text-muted small">
                        {{ __('Results received on') }} {{ $radiologyRequest->result_received_at->format('M d, Y \a\t g:i A') }}
                        @if($radiologyRequest->resultReceiver)
                        {{ __('by') }} {{ $radiologyRequest->resultReceiver->full_name }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Additional Notes -->
            @if($radiologyRequest->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note"></i>
                        {{ __('Additional Notes') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $radiologyRequest->notes }}</p>
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
                        <i class="fas fa-info-circle"></i>
                        {{ __('Request Status') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <span class="{{ $radiologyRequest->status_badge_class }} fs-6">{{ $radiologyRequest->status_display }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>{{ __('Request Number') }}:</strong><br>
                        <code>{{ $radiologyRequest->request_number }}</code>
                    </div>

                    <div class="mb-3">
                        <strong>{{ __('Requested Date') }}:</strong><br>
                        {{ $radiologyRequest->requested_date->format('M d, Y') }}
                    </div>

                    @if($radiologyRequest->sent_at)
                    <div class="mb-3">
                        <strong>{{ __('Sent At') }}:</strong><br>
                        {{ $radiologyRequest->sent_at->format('M d, Y g:i A') }}
                        @if($radiologyRequest->communication_method)
                        <br><small class="text-muted">{{ __('via') }} {{ $radiologyRequest->communication_method_display }}</small>
                        @endif
                    </div>
                    @endif

                    @if($radiologyRequest->communication_notes)
                    <div class="mb-3">
                        <strong>{{ __('Communication Notes') }}:</strong><br>
                        <small>{{ $radiologyRequest->communication_notes }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i>
                        {{ __('Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('recommendations.radiology.pdf', $radiologyRequest) }}" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>
                            {{ __('Download PDF') }}
                        </a>

                        @if($radiologyRequest->status === 'pending')
                        <a href="{{ route('recommendations.radiology.edit', $radiologyRequest) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Request') }}
                        </a>
                        @endif

                        <a href="{{ route('recommendations.radiology.create') }}?patient_id={{ $radiologyRequest->patient_id }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Request for Patient') }}
                        </a>

                        <hr>

                        <!-- Status Update -->
                        @if(auth()->user()->canEditRadiologyRequests())
                        <form action="{{ route('recommendations.radiology.update-status', $radiologyRequest) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label class="form-label small">{{ __('Update Status') }}</label>
                                <select class="form-select form-select-sm" name="status" required>
                                    @foreach(\App\Models\RadiologyRequest::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ $radiologyRequest->status === $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="{{ __('Status update notes...') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Status') }}
                            </button>
                        </form>
                        @endif

                        <!-- Upload Results -->
                        @if(auth()->user()->canEditRadiologyRequests() && !$radiologyRequest->hasResults())
                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#uploadResultModal">
                            <i class="fas fa-upload me-1"></i>
                            {{ __('Upload Results') }}
                        </button>
                        @endif

                        @if($radiologyRequest->status === 'pending')
                        <hr>
                        <form action="{{ route('recommendations.radiology.destroy', $radiologyRequest) }}" method="POST"
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this radiology request?') }}')">
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
            </div>
        </div>
    </div>
</div>

<!-- Upload Result Modal -->
<div class="modal fade" id="uploadResultModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Upload Radiology Results') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('recommendations.radiology.upload-result', $radiologyRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="result_file" class="form-label">{{ __('Result File') }} <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="result_file" name="result_file"
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        <div class="form-text">{{ __('Accepted formats: PDF, JPG, PNG, DOC, DOCX. Max size: 10MB') }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="radiologist_report" class="form-label">{{ __('Radiologist Report') }}</label>
                        <textarea class="form-control" id="radiologist_report" name="radiologist_report" rows="4"
                                  placeholder="{{ __('Enter the radiologist\'s report...') }}"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="findings" class="form-label">{{ __('Findings') }}</label>
                        <textarea class="form-control" id="findings" name="findings" rows="3"
                                  placeholder="{{ __('Key findings from the imaging...') }}"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="impression" class="form-label">{{ __('Impression') }}</label>
                        <textarea class="form-control" id="impression" name="impression" rows="3"
                                  placeholder="{{ __('Clinical impression and recommendations...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>
                        {{ __('Upload Results') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
