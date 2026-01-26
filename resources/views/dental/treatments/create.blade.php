@extends('layouts.app')

@section('title', __('Create Treatment Plan'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        {{ __('Create Treatment Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Plan a new dental treatment for a patient') }}</p>
                </div>
                <div>
                    <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Treatments') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>
                        {{ __('Treatment Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/dental/treatments') }}" id="treatment-form">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ (old('patient_id', $patient?->id) == $p->id) ? 'selected' : '' }}>
                                        {{ $p->full_name }} ({{ $p->patient_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dental Chart Selection (Optional) -->
                        <div class="mb-3">
                            <label for="dental_chart_id" class="form-label">{{ __('Dental Chart') }}</label>
                            <div class="input-group">
                                <select name="dental_chart_id" id="dental_chart_id" class="form-select">
                                    <option value="">{{ __('No Chart Selected') }}</option>
                                    @if($dentalChart)
                                        <option value="{{ $dentalChart->id }}" selected>
                                            {{ __('Chart') }} #{{ $dentalChart->id }} - {{ $dentalChart->chart_type }} ({{ $dentalChart->created_at->format('M d, Y') }})
                                        </option>
                                    @endif
                                </select>
                                <button type="button" class="btn btn-outline-primary" id="create-chart-btn" onclick="showCreateChartModal()">
                                    <i class="fas fa-plus me-1"></i>
                                    {{ __('Create New Chart') }}
                                </button>
                            </div>
                            <small class="text-muted">{{ __('Link this treatment to a specific dental chart or create a new one') }}</small>
                        </div>

                        <!-- Procedure Selection -->
                        <div class="mb-3">
                            <label for="procedure_id" class="form-label">{{ __('Procedure') }} <span class="text-danger">*</span></label>
                            <select name="procedure_id" id="procedure_id" class="form-select @error('procedure_name') is-invalid @enderror" required>
                                <option value="">{{ __('Select Procedure') }}</option>
                                @foreach($procedures->groupBy('category') as $category => $categoryProcedures)
                                    <optgroup label="{{ \App\Models\DentalProcedure::CATEGORIES[$category] ?? $category }}">
                                        @foreach($categoryProcedures as $procedure)
                                            <option value="{{ $procedure->id }}" 
                                                    data-name="{{ $procedure->name }}"
                                                    data-code="{{ $procedure->code }}"
                                                    data-cost="{{ $procedure->default_cost }}"
                                                    data-duration="{{ $procedure->estimated_duration_minutes }}"
                                                    data-description="{{ $procedure->description }}">
                                                {{ $procedure->name }} @if($procedure->code)({{ $procedure->code }})@endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('procedure_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden fields for procedure details -->
                        <input type="hidden" name="procedure_name" id="procedure_name">
                        <input type="hidden" name="procedure_code" id="procedure_code">

                        <!-- Tooth Number(s) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ __('Primary Tooth Number') }}</label>
                                <input type="text" name="tooth_number" id="tooth_number" class="form-control" 
                                       placeholder="{{ __('e.g., 16, 21, 36') }}" value="{{ old('tooth_number') }}">
                                <small class="text-muted">{{ __('FDI tooth numbering system') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_numbers" class="form-label">{{ __('Additional Teeth') }}</label>
                                <input type="text" name="tooth_numbers" id="tooth_numbers" class="form-control" 
                                       placeholder="{{ __('e.g., 16,17,18') }}" value="{{ old('tooth_numbers') }}">
                                <small class="text-muted">{{ __('Comma-separated for multiple teeth') }}</small>
                            </div>
                        </div>

                        <!-- Surfaces Affected -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Surfaces Affected') }}</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="O" id="surface_o">
                                    <label class="form-check-label" for="surface_o">{{ __('Occlusal (O)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="M" id="surface_m">
                                    <label class="form-check-label" for="surface_m">{{ __('Mesial (M)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="D" id="surface_d">
                                    <label class="form-check-label" for="surface_d">{{ __('Distal (D)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="B" id="surface_b">
                                    <label class="form-check-label" for="surface_b">{{ __('Buccal (B)') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="surfaces_affected[]" value="L" id="surface_l">
                                    <label class="form-check-label" for="surface_l">{{ __('Lingual (L)') }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Diagnosis -->
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                            <textarea name="diagnosis" id="diagnosis" class="form-control" rows="2"
                                      placeholder="{{ __('Clinical diagnosis') }}">{{ old('diagnosis') }}</textarea>
                        </div>

                        <!-- ICD-10 Code -->
                        <div class="mb-3">
                            <label for="icd10_code" class="form-label">{{ __('ICD-10 Code') }}</label>
                            <input type="text" name="icd10_code" id="icd10_code" class="form-control"
                                   placeholder="{{ __('e.g., K02.9') }}" value="{{ old('icd10_code') }}">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Treatment Description') }}</label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                      placeholder="{{ __('Detailed treatment plan description') }}">{{ old('description') }}</textarea>
                        </div>

                        <!-- Cost & Duration -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="estimated_cost" class="form-label">{{ __('Estimated Cost') }} <span class="text-danger">*</span></label>
                                <input type="number" name="estimated_cost" id="estimated_cost" class="form-control"
                                       step="0.01" min="0" placeholder="0.00" value="{{ old('estimated_cost') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="currency" class="form-label">{{ __('Currency') }}</label>
                                <select name="currency" id="currency" class="form-select">
                                    <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="IQD" {{ old('currency') == 'IQD' ? 'selected' : '' }}>IQD</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estimated_duration_minutes" class="form-label">{{ __('Duration (minutes)') }}</label>
                                <input type="number" name="estimated_duration_minutes" id="estimated_duration_minutes"
                                       class="form-control" min="0" placeholder="60" value="{{ old('estimated_duration_minutes') }}">
                            </div>
                        </div>

                        <!-- Status, Priority, Severity -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="planned" {{ old('status', 'planned') == 'planned' ? 'selected' : '' }}>{{ __('Planned') }}</option>
                                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">{{ __('Priority') }} <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                                    <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="severity" class="form-label">{{ __('Severity') }}</label>
                                <select name="severity" id="severity" class="form-select">
                                    <option value="">{{ __('Not Specified') }}</option>
                                    <option value="mild" {{ old('severity') == 'mild' ? 'selected' : '' }}>{{ __('Mild') }}</option>
                                    <option value="moderate" {{ old('severity') == 'moderate' ? 'selected' : '' }}>{{ __('Moderate') }}</option>
                                    <option value="severe" {{ old('severity') == 'severe' ? 'selected' : '' }}>{{ __('Severe') }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Assigned Doctor -->
                        <div class="mb-3">
                            <label for="assigned_doctor_id" class="form-label">{{ __('Assigned Doctor') }}</label>
                            <select name="assigned_doctor_id" id="assigned_doctor_id" class="form-select">
                                <option value="">{{ __('Not Assigned') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('assigned_doctor_id', auth()->id()) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scheduled Date -->
                        <div class="mb-3">
                            <label for="scheduled_date" class="form-label">{{ __('Scheduled Date') }}</label>
                            <input type="datetime-local" name="scheduled_date" id="scheduled_date" class="form-control"
                                   value="{{ old('scheduled_date') }}">
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Additional Notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                      placeholder="{{ __('Any additional notes or instructions') }}">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Treatment Plan') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar with Help -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Help & Guidelines') }}
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">{{ __('FDI Tooth Numbering') }}</h6>
                    <p class="small text-muted">
                        {{ __('Use the FDI two-digit system:') }}
                    </p>
                    <ul class="small text-muted">
                        <li>{{ __('Adult teeth: 11-18, 21-28, 31-38, 41-48') }}</li>
                        <li>{{ __('Primary teeth: 51-55, 61-65, 71-75, 81-85') }}</li>
                    </ul>

                    <hr>

                    <h6 class="text-primary">{{ __('Tooth Surfaces') }}</h6>
                    <ul class="small text-muted">
                        <li><strong>O</strong> - {{ __('Occlusal (chewing surface)') }}</li>
                        <li><strong>M</strong> - {{ __('Mesial (toward midline)') }}</li>
                        <li><strong>D</strong> - {{ __('Distal (away from midline)') }}</li>
                        <li><strong>B</strong> - {{ __('Buccal/Facial (cheek side)') }}</li>
                        <li><strong>L</strong> - {{ __('Lingual/Palatal (tongue side)') }}</li>
                    </ul>

                    <hr>

                    <h6 class="text-primary">{{ __('Priority Levels') }}</h6>
                    <ul class="small text-muted">
                        <li><strong>{{ __('Urgent') }}</strong> - {{ __('Immediate attention required') }}</li>
                        <li><strong>{{ __('High') }}</strong> - {{ __('Schedule within 1-2 weeks') }}</li>
                        <li><strong>{{ __('Medium') }}</strong> - {{ __('Schedule within 1 month') }}</li>
                        <li><strong>{{ __('Low') }}</strong> - {{ __('Can be scheduled flexibly') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-fill procedure details when selected
document.getElementById('procedure_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];

    if (selectedOption.value) {
        document.getElementById('procedure_name').value = selectedOption.dataset.name || '';
        document.getElementById('procedure_code').value = selectedOption.dataset.code || '';
        document.getElementById('estimated_cost').value = selectedOption.dataset.cost || '';
        document.getElementById('estimated_duration_minutes').value = selectedOption.dataset.duration || '';
        document.getElementById('description').value = selectedOption.dataset.description || '';
    } else {
        document.getElementById('procedure_name').value = '';
        document.getElementById('procedure_code').value = '';
    }
});

// Load dental charts when patient is selected
document.getElementById('patient_id').addEventListener('change', function() {
    const patientId = this.value;

    if (patientId) {
        // Reload page with patient_id parameter to load their dental charts
        window.location.href = '{{ url("/dental/treatments/create") }}?patient_id=' + patientId;
    }
});

// Show create chart modal
function showCreateChartModal() {
    const patientId = document.getElementById('patient_id').value;

    if (!patientId) {
        alert('{{ __("Please select a patient first") }}');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('createChartModal'));
    modal.show();
}

// Create dental chart via AJAX
function createDentalChart() {
    const patientId = document.getElementById('patient_id').value;
    const chartType = document.getElementById('new_chart_type').value;
    const generalNotes = document.getElementById('new_chart_notes').value;

    if (!patientId) {
        alert('{{ __("Please select a patient first") }}');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('create-chart-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Creating...") }}';

    // Send AJAX request
    fetch(`/dental/patients/${patientId}/charts`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            chart_type: chartType,
            general_notes: generalNotes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new chart to dropdown
            const select = document.getElementById('dental_chart_id');
            const option = document.createElement('option');
            option.value = data.chart.id;
            option.text = `Chart #${data.chart.id} - ${data.chart.chart_type} (${data.chart.created_at})`;
            option.selected = true;
            select.appendChild(option);

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('createChartModal')).hide();

            // Reset form
            document.getElementById('new_chart_type').value = 'adult';
            document.getElementById('new_chart_notes').value = '';

            // Show success message
            alert('{{ __("Dental chart created successfully!") }}');
        } else {
            alert('{{ __("Error creating dental chart: ") }}' + (data.message || '{{ __("Unknown error") }}'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("Error creating dental chart. Please try again.") }}');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}
</script>
@endpush

<!-- Create Dental Chart Modal -->
<div class="modal fade" id="createChartModal" tabindex="-1" aria-labelledby="createChartModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createChartModalLabel">
                    <i class="fas fa-tooth me-2"></i>
                    {{ __('Create New Dental Chart') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="new_chart_type" class="form-label">{{ __('Chart Type') }} <span class="text-danger">*</span></label>
                    <select id="new_chart_type" class="form-select" required>
                        <option value="adult" selected>{{ __('Adult (Permanent Dentition)') }}</option>
                        <option value="pediatric">{{ __('Pediatric (Primary Dentition)') }}</option>
                    </select>
                    <small class="text-muted">{{ __('Select adult for permanent teeth or pediatric for primary teeth') }}</small>
                </div>
                <div class="mb-3">
                    <label for="new_chart_notes" class="form-label">{{ __('General Notes') }}</label>
                    <textarea id="new_chart_notes" class="form-control" rows="3" placeholder="{{ __('Optional notes about this dental chart') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="create-chart-submit-btn" onclick="createDentalChart()">
                    <i class="fas fa-save me-1"></i>
                    {{ __('Create Chart') }}
                </button>
            </div>
        </div>
    </div>
</div>

