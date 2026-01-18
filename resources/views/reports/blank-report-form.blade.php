@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-alt text-primary"></i>
                        {{ __('Create Blank Report') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Fill in the details for the medical report') }}</p>
                </div>
                <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Patient') }}
                </a>
            </div>

            <!-- Patient Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('Patient ID') }}:</strong> {{ $patient->patient_id }}</p>
                            <p class="mb-2"><strong>{{ __('Name') }}:</strong> {{ $patient->full_name }}</p>
                            <p class="mb-2"><strong>{{ __('Gender') }}:</strong> {{ ucfirst($patient->gender ?? 'N/A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>{{ __('Date of Birth') }}:</strong> 
                                @if($patient->date_of_birth)
                                    {{ $patient->date_of_birth->format('F d, Y') }} ({{ $patient->date_of_birth->age }} years)
                                @else
                                    N/A
                                @endif
                            </p>
                            <p class="mb-2"><strong>{{ __('Phone') }}:</strong> {{ $patient->phone ?? 'N/A' }}</p>
                            <p class="mb-2"><strong>{{ __('Address') }}:</strong> {{ $patient->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Form -->
            <form action="{{ route('patient.blank-report.preview', $patient) }}" method="POST" id="blankReportForm">
                @csrf
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-medical me-2"></i>
                            {{ __('Report Details') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Report Title -->
                        <div class="mb-4">
                            <label for="report_title" class="form-label">
                                <i class="fas fa-heading me-1"></i>
                                {{ __('Report Title') }}
                            </label>
                            <input type="text"
                                   class="form-control @error('report_title') is-invalid @enderror"
                                   id="report_title"
                                   name="report_title"
                                   placeholder="{{ __('e.g., Sick Leave Certificate, Medical Fitness Report, etc.') }}"
                                   value="{{ old('report_title', request('report_title', 'Medical Report')) }}">
                            @error('report_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Enter a descriptive title for this report') }}
                            </small>
                        </div>

                        <!-- Notes/Special Information -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="fas fa-notes-medical me-1"></i>
                                {{ __('Notes / Special Information') }} <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="15"
                                      placeholder="{{ __('Enter medical notes, observations, recommendations, sick leave details, or any special information...') }}"
                                      required>{{ old('notes', request('notes')) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('This information will be included in the PDF report') }}
                            </small>
                        </div>

                        <!-- Quick Templates -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-magic me-1"></i>
                                {{ __('Quick Templates') }}
                            </label>
                            <div class="btn-group flex-wrap" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('sick_leave')">
                                    <i class="fas fa-bed me-1"></i>
                                    {{ __('Sick Leave') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('fitness')">
                                    <i class="fas fa-heartbeat me-1"></i>
                                    {{ __('Medical Fitness') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('follow_up')">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    {{ __('Follow-up') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertTemplate('referral')">
                                    <i class="fas fa-share me-1"></i>
                                    {{ __('Referral') }}
                                </button>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                {{ __('Click a template to insert pre-formatted text into the notes field') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-0 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Preview the report before saving to patient files') }}
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i>
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-eye me-1"></i>
                                    {{ __('Preview Report') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Quick template insertion
function insertTemplate(type) {
    const notesField = document.getElementById('notes');
    const titleField = document.getElementById('report_title');
    let template = '';
    let title = '';
    
    switch(type) {
        case 'sick_leave':
            title = 'Sick Leave Certificate';
            template = `SICK LEAVE CERTIFICATE

This is to certify that {{ $patient->full_name }} has been examined and found to be suffering from a medical condition that requires rest and treatment.

Diagnosis: [Enter diagnosis here]

Recommended Period of Rest: [Enter number of days] days
From: [Start date] To: [End date]

The patient is advised to:
- Take complete rest
- Follow prescribed medication
- Avoid strenuous activities
- Follow up as needed

This certificate is issued upon the patient's request for official purposes.`;
            break;

        case 'fitness':
            title = 'Medical Fitness Certificate';
            template = `MEDICAL FITNESS CERTIFICATE

This is to certify that {{ $patient->full_name }} has been examined and found to be:

☐ Medically fit for work/study/travel
☐ Fit with restrictions (specify below)
☐ Temporarily unfit (specify duration)

Physical Examination Findings:
- General condition: [Normal/Specify]
- Blood pressure: [Value]
- Heart rate: [Value]
- Other relevant findings: [Specify]

Remarks: [Any additional comments]

This certificate is valid for: [Specify duration]`;
            break;

        case 'follow_up':
            title = 'Follow-up Recommendation';
            template = `FOLLOW-UP RECOMMENDATION

Patient: {{ $patient->full_name }}
Date of Visit: {{ now()->format('F d, Y') }}

Current Condition:
[Describe current medical condition]

Treatment Provided:
[List treatments, medications, or procedures]

Follow-up Instructions:
- Next appointment: [Date]
- Medications to continue: [List medications]
- Lifestyle modifications: [Specify]
- Warning signs to watch for: [Specify]

Additional Notes:
[Any other relevant information]`;
            break;

        case 'referral':
            title = 'Medical Referral';
            template = `MEDICAL REFERRAL

Patient: {{ $patient->full_name }}
Referred to: [Specialist name/department]

Reason for Referral:
[Describe the medical condition requiring specialist consultation]

Relevant Medical History:
[Brief summary of relevant medical history]

Current Medications:
[List current medications]

Investigations Done:
[List any tests or examinations already performed]

Specific Questions/Concerns:
[What you would like the specialist to address]

Thank you for your consultation.`;
            break;
    }

    if (template) {
        notesField.value = template;
        titleField.value = title;
    }
}
</script>
@endsection

