@extends('layouts.app')

@section('title', __('Create Dental Chart') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle text-success me-2"></i>
                        {{ __('Create Dental Chart') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/charts") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Charts') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ url("/dental/patients/{$patient->id}/charts") }}" method="POST" id="dental-chart-form">
        @csrf

        <!-- Chart Type Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Chart Configuration') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Chart Type') }} <span class="text-danger">*</span></label>
                                <select name="chart_type" id="chart_type" class="form-select" required>
                                    <option value="adult" selected>{{ __('Adult (Permanent Dentition)') }}</option>
                                    <option value="pediatric">{{ __('Pediatric (Primary Dentition)') }}</option>
                                </select>
                                <div class="form-text">{{ __('Select adult for permanent teeth or pediatric for primary teeth') }}</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Visit ID') }} <span class="text-muted">({{ __('Optional') }})</span></label>
                                <input type="text" name="visit_id" class="form-control" placeholder="{{ __('Enter visit/appointment ID') }}">
                                <div class="form-text">{{ __('Link this chart to a specific visit or appointment') }}</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">{{ __('General Notes') }}</label>
                                <textarea name="general_notes" class="form-control" rows="3" placeholder="{{ __('Enter general observations, treatment plan summary, or other notes') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tooth Records -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-teeth me-2"></i>
                            {{ __('Tooth Records') }}
                        </h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addToothRecord()">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Add Tooth') }}
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="tooth-records-container">
                            <!-- Tooth records will be added here dynamically -->
                            <div class="text-center py-4 text-muted" id="empty-state">
                                <i class="fas fa-tooth fa-3x mb-3"></i>
                                <p class="mb-0">{{ __('No tooth records added yet. Click "Add Tooth" to start recording dental conditions.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ url("/dental/patients/{$patient->id}/charts") }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create Dental Chart') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Tooth Record Template (Hidden) -->
<div id="tooth-record-template" style="display: none;">
    <div class="tooth-record-item border rounded p-3 mb-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-primary">
                <i class="fas fa-tooth me-1"></i>
                {{ __('Tooth Record') }} <span class="tooth-number-display">1</span>
            </h6>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeToothRecord(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">{{ __('Tooth Number') }} <span class="text-danger">*</span></label>
                <input type="text" class="form-control tooth-number-input" name="tooth_records[INDEX][tooth_number]" 
                       placeholder="e.g., 11, 16, 21" required pattern="[0-9]{2}">
                <div class="form-text">{{ __('FDI notation (11-48 or 51-85)') }}</div>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Primary Condition') }} <span class="text-danger">*</span></label>
                <select class="form-select" name="tooth_records[INDEX][primary_condition]" required>
                    @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                        <option value="{{ $key }}">{{ $condition['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Severity') }}</label>
                <select class="form-select" name="tooth_records[INDEX][severity]">
                    <option value="">{{ __('Not Applicable') }}</option>
                    <option value="mild">{{ __('Mild') }}</option>
                    <option value="moderate">{{ __('Moderate') }}</option>
                    <option value="severe">{{ __('Severe') }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">{{ __('Surfaces Affected') }}</label>
                <select class="form-select" name="tooth_records[INDEX][surfaces_affected][]" multiple size="3">
                    @foreach(\App\Models\DentalToothRecord::SURFACES as $key => $surface)
                        <option value="{{ $key }}">{{ $surface }}</option>
                    @endforeach
                </select>
                <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple') }}</div>
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea class="form-control" name="tooth_records[INDEX][notes]" rows="2" 
                          placeholder="{{ __('Additional notes about this tooth') }}"></textarea>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let toothRecordCount = 0;

function addToothRecord() {
    const container = document.getElementById('tooth-records-container');
    const template = document.getElementById('tooth-record-template');
    const emptyState = document.getElementById('empty-state');

    // Hide empty state
    if (emptyState) {
        emptyState.style.display = 'none';
    }

    // Clone template
    const newRecord = template.cloneNode(true);
    newRecord.style.display = 'block';
    newRecord.id = '';

    // Replace INDEX with actual index
    const html = newRecord.innerHTML.replace(/INDEX/g, toothRecordCount);
    newRecord.innerHTML = html;

    // Update display number
    const displaySpan = newRecord.querySelector('.tooth-number-display');
    if (displaySpan) {
        displaySpan.textContent = toothRecordCount + 1;
    }

    container.appendChild(newRecord);
    toothRecordCount++;

    // Focus on tooth number input
    const toothInput = newRecord.querySelector('.tooth-number-input');
    if (toothInput) {
        toothInput.focus();
    }
}

function removeToothRecord(button) {
    const record = button.closest('.tooth-record-item');
    if (record) {
        record.remove();
    }

    // Show empty state if no records left
    const container = document.getElementById('tooth-records-container');
    const records = container.querySelectorAll('.tooth-record-item');
    const emptyState = document.getElementById('empty-state');

    if (records.length === 0 && emptyState) {
        emptyState.style.display = 'block';
    }
}

// Form validation
document.getElementById('dental-chart-form').addEventListener('submit', function(e) {
    const container = document.getElementById('tooth-records-container');
    const records = container.querySelectorAll('.tooth-record-item');

    if (records.length === 0) {
        e.preventDefault();
        alert('{{ __("Please add at least one tooth record before submitting.") }}');
        return false;
    }

    // Check for duplicate tooth numbers
    const toothNumbers = [];
    let hasDuplicates = false;

    records.forEach(record => {
        const input = record.querySelector('.tooth-number-input');
        if (input && input.value) {
            if (toothNumbers.includes(input.value)) {
                hasDuplicates = true;
            }
            toothNumbers.push(input.value);
        }
    });

    if (hasDuplicates) {
        e.preventDefault();
        alert('{{ __("Duplicate tooth numbers detected. Each tooth can only be recorded once.") }}');
        return false;
    }
});

// Add first tooth record automatically
document.addEventListener('DOMContentLoaded', function() {
    // Optionally add one record by default
    // addToothRecord();
});
</script>
@endpush

