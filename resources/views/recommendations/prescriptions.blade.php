@extends('layouts.app')

@section('title', __('Prescription Recommendations'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                        {{ __('Prescription Management') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage and create prescriptions using your clinic\'s medicine inventory') }}</p>
                </div>
                <div>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-warehouse me-1"></i>
                        {{ __('Manage Medicines') }}
                    </a>
                    <a href="{{ route('medicines.create') }}" class="btn btn-outline-success me-2">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Medicine') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPrescriptionModal">
                        <i class="fas fa-file-prescription me-1"></i>
                        {{ __('New Prescription') }}
                    </button>
                </div>
            </div>

            <!-- Patient Selection -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-injured me-2"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recommendations.prescriptions') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="patient_id" class="form-label">{{ __('Select Patient') }}</label>
                            <select class="form-select" id="patient_id" name="patient_id" onchange="this.form.submit()">
                                <option value="">{{ __('Choose a patient...') }}</option>
                                @if(isset($patients))
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id ?? 'P' . str_pad($patient->id, 6, '0', STR_PAD_LEFT) }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="1">Demo Patient (P000001)</option>
                                    <option value="2">John Smith (P000002)</option>
                                    <option value="3">Sarah Ahmed (P000003)</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="symptoms" class="form-label">{{ __('Primary Symptoms') }}</label>
                            <input type="text" class="form-control" id="symptoms" name="symptoms" 
                                   value="{{ request('symptoms') }}" placeholder="{{ __('e.g., fever, headache, cough...') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="condition" class="form-label">{{ __('Diagnosed Condition') }}</label>
                            <input type="text" class="form-control" id="condition" name="condition" 
                                   value="{{ request('condition') }}" placeholder="{{ __('e.g., hypertension, diabetes...') }}">
                        </div>
                    </form>
                </div>
            </div>

            @if(request('patient_id') || request('symptoms') || request('condition'))
            <!-- Selected Patient Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Patient Summary') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="avatar bg-primary text-white rounded-circle mx-auto mb-2" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                    DP
                                </div>
                                <h6 class="mb-1">Demo Patient</h6>
                                <small class="text-muted">P000001</small>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <small class="text-muted">{{ __('Age') }}</small>
                                    <div class="fw-bold">35 {{ __('years') }}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">{{ __('Gender') }}</small>
                                    <div class="fw-bold">{{ __('Male') }}</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">{{ __('Weight') }}</small>
                                    <div class="fw-bold">75 kg</div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">{{ __('Allergies') }}</small>
                                    <div class="fw-bold text-danger">{{ __('Penicillin') }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Current Medications') }}</small>
                                    <div class="fw-bold">{{ __('Metformin 500mg (2x daily), Lisinopril 10mg (1x daily)') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medicine Inventory -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-pills me-2 text-primary"></i>
                        {{ __('Your Medicine Inventory') }}
                    </h6>
                    <span class="badge bg-primary">{{ __('Clinic Managed') }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @for($i = 1; $i <= 3; $i++)
                        <div class="col-lg-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ __('Medicine') }} {{ $i }}</h6>
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-2">{{ __('In Stock') }}</span>
                                            <i class="fas fa-check text-success"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($i == 1)
                                        <h6 class="text-primary">{{ __('Amoxicillin 500mg') }}</h6>
                                        <p class="small text-muted mb-2">{{ __('Antibiotic for bacterial infections') }}</p>
                                        <div class="mb-2">
                                            <strong>{{ __('Dosage') }}:</strong> 1 {{ __('tablet') }} 3x {{ __('daily') }}<br>
                                            <strong>{{ __('Duration') }}:</strong> 7 {{ __('days') }}<br>
                                            <strong>{{ __('Instructions') }}:</strong> {{ __('Take with food') }}
                                        </div>
                                        <div class="alert alert-warning alert-sm py-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            <small>{{ __('Check for penicillin allergy') }}</small>
                                        </div>
                                    @elseif($i == 2)
                                        <h6 class="text-primary">{{ __('Ibuprofen 400mg') }}</h6>
                                        <p class="small text-muted mb-2">{{ __('Anti-inflammatory pain reliever') }}</p>
                                        <div class="mb-2">
                                            <strong>{{ __('Dosage') }}:</strong> 1 {{ __('tablet') }} 3x {{ __('daily') }}<br>
                                            <strong>{{ __('Duration') }}:</strong> 5 {{ __('days') }}<br>
                                            <strong>{{ __('Instructions') }}:</strong> {{ __('Take after meals') }}
                                        </div>
                                        <div class="alert alert-info alert-sm py-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <small>{{ __('Monitor for stomach irritation') }}</small>
                                        </div>
                                    @else
                                        <h6 class="text-primary">{{ __('Paracetamol 500mg') }}</h6>
                                        <p class="small text-muted mb-2">{{ __('Pain reliever and fever reducer') }}</p>
                                        <div class="mb-2">
                                            <strong>{{ __('Dosage') }}:</strong> 1-2 {{ __('tablets') }} 4x {{ __('daily') }}<br>
                                            <strong>{{ __('Duration') }}:</strong> {{ __('As needed') }}<br>
                                            <strong>{{ __('Instructions') }}:</strong> {{ __('Maximum 8 tablets per day') }}
                                        </div>
                                        <div class="alert alert-success alert-sm py-1">
                                            <i class="fas fa-check-circle me-1"></i>
                                            <small>{{ __('Safe with current medications') }}</small>
                                        </div>
                                    @endif
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addToPrescription({{ $i }})">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add to Prescription') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Current Prescription Draft -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-prescription me-2"></i>
                        {{ __('Prescription Draft') }}
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="fas fa-save me-1"></i>
                            {{ __('Save Draft') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>
                            {{ __('Generate Prescription') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="prescriptionDraft">
                        <div class="text-center py-4">
                            <i class="fas fa-prescription-bottle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">{{ __('No medications added yet. Use your medicine inventory above to build your prescription.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Getting Started -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-pills fa-3x text-primary mb-3"></i>
                    <h5 class="mb-3">{{ __('Manual Prescription Management') }}</h5>
                    <p class="text-muted mb-4">{{ __('Create prescriptions using your clinic\'s medicine inventory. Add medicines, set dosages, and manage patient prescriptions manually.') }}</p>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-warehouse fa-2x text-primary mb-2"></i>
                                            <h6>{{ __('Medicine Inventory') }}</h6>
                                            <small class="text-muted">{{ __('Manage your clinic medicines') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-user-md fa-2x text-success mb-2"></i>
                                            <h6>{{ __('Doctor Control') }}</h6>
                                            <small class="text-muted">{{ __('Full medical professional control') }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-prescription fa-2x text-info mb-2"></i>
                                            <h6>{{ __('Custom Prescriptions') }}</h6>
                                            <small class="text-muted">{{ __('Tailored to patient needs') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Search Medicines Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="searchModalLabel">
                    <i class="fas fa-search me-2"></i>
                    {{ __('Search Medicines') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="{{ __('Search by medicine name, active ingredient, or condition...') }}">
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ __('Amoxicillin 500mg') }}</h6>
                                        <p class="mb-1 text-muted">{{ __('Antibiotic - Penicillin group') }}</p>
                                        <small>{{ __('Used for bacterial infections') }}</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm">{{ __('Add') }}</button>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ __('Ibuprofen 400mg') }}</h6>
                                        <p class="mb-1 text-muted">{{ __('NSAID - Anti-inflammatory') }}</p>
                                        <small>{{ __('Pain relief and inflammation') }}</small>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm">{{ __('Add') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addToPrescription(recommendationId) {
    // This would add the selected medication to the prescription draft
    alert('{{ __("Medication added to prescription draft!") }}');
}
</script>
@endsection
