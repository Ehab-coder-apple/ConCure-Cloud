@extends('layouts.app')

@section('title', __('Patient Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    {{ __('Patient Management') }}
                </h1>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i>
                            {{ __('Actions') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('patients.export') }}">
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    {{ __('Export to Excel') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); confirmClearAll();">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    {{ __('Clear All Patients') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('patients.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import me-2"></i>
                        {{ __('Import') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                        <i class="fas fa-plus me-2"></i>
                        {{ __('Add New Patient') }}
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('patients.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Patients') }}</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   value="{{ request('search') }}"
                                   placeholder="{{ __('Search by name, ID, phone, email (min 1 character)...') }}"
                                   minlength="1">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="gender" class="form-label">{{ __('Gender') }}</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">{{ __('All Genders') }}</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>
                                    {{ __('Search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Patients List') }}
                        <span class="badge bg-primary ms-2">{{ $patients->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($patients) && $patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Patient ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Age') }}</th>
                                        <th>{{ __('Gender') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Last Visit') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $patient->patient_id ?? 'P' . str_pad($patient->id, 6, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($patient->first_name ?? 'P', 0, 1) . substr($patient->last_name ?? 'A', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') }}</div>
                                                    <small class="text-muted">{{ $patient->email ?? '' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $patient->age ?? ($patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '-') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $patient->gender == 'male' ? 'info' : 'pink' }} text-dark">
                                                {{ ucfirst($patient->gender ?? 'Unknown') }}
                                            </span>
                                        </td>
                                        <td>{{ $patient->phone ?? '-' }}</td>
                                        <td>{{ $patient->last_visit_date ? \Carbon\Carbon::parse($patient->last_visit_date)->format('M d, Y') : __('Never') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $patient->is_active ? 'success' : 'secondary' }}">
                                                {{ $patient->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-outline-primary" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('patient.report', $patient->id) }}" class="btn btn-outline-success" title="{{ __('Generate Report') }}" target="_blank">
                                                    <i class="fas fa-file-medical"></i>
                                                </a>
                                                <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info" title="{{ __('New Appointment') }}" onclick="newAppointment({{ $patient->id }})">
                                                    <i class="fas fa-calendar-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" title="{{ __('New Prescription') }}" onclick="newPrescription({{ $patient->id }})">
                                                    <i class="fas fa-prescription-bottle-alt"></i>
                                                </button>
                                                @if($patient->whatsapp_phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $patient->whatsapp_phone) }}"
                                                   target="_blank" class="btn btn-outline-success" title="{{ __('WhatsApp') }}">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(isset($patients) && method_exists($patients, 'links'))
                            <div class="card-footer">
                                {{ $patients->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Patients Found') }}</h5>
                            <p class="text-muted">{{ __('Start by adding your first patient to the system.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Add First Patient') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Add New Patient') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2"></i>
                                {{ __('Basic Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>

                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                        </div>

                        <div class="col-md-6">
                            <label for="gender" class="form-label">{{ __('Gender') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">{{ __('Select Gender') }}</option>
                                <option value="male">{{ __('Male') }}</option>
                                <option value="female">{{ __('Female') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-phone me-2"></i>
                                {{ __('Contact Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="{{ __('Phone number') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_phone" class="form-label">
                                <i class="fab fa-whatsapp text-success me-1"></i>
                                {{ __('WhatsApp Number') }}
                            </label>
                            <input type="tel" class="form-control" id="whatsapp_phone" name="whatsapp_phone"
                                   placeholder="{{ __('WhatsApp number for communication') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">{{ __('Email Address') }}</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="{{ __('Email address') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="{{ __('Home address') }}"></textarea>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Personal Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="job" class="form-label">{{ __('Occupation') }}</label>
                            <input type="text" class="form-control" id="job" name="job" placeholder="{{ __('Job/Occupation') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="education" class="form-label">{{ __('Education Level') }}</label>
                            <input type="text" class="form-control" id="education" name="education" placeholder="{{ __('Education level') }}">
                        </div>

                        <!-- Physical Information -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-weight me-2"></i>
                                {{ __('Physical Information') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                            <input type="number" class="form-control" id="height" name="height" min="50" max="300" step="0.1" placeholder="{{ __('Height in centimeters') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                            <input type="number" class="form-control" id="weight" name="weight" min="1" max="500" step="0.1" placeholder="{{ __('Weight in kilograms') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="blood_type" class="form-label">{{ __('Blood Type') }}</label>
                            <select class="form-select" id="blood_type" name="blood_type">
                                <option value="">{{ __('Select Blood Type') }}</option>
                                <option value="NA">{{ __('NA - Not available') }}</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <!-- Pediatric Information -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-baby me-2"></i>
                                {{ __('Pediatric Information') }}
                            </h6>
                            <small class="text-muted d-block mb-3">{{ __('Fill these fields for infants/children to enable automatic growth chart type detection (LBW / Preterm).') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_birth_weight" class="form-label">{{ __('Birth Weight (grams)') }}</label>
                            <input type="number" class="form-control" id="modal_birth_weight" name="birth_weight" min="200" max="7000" step="1" placeholder="{{ __('e.g. 2500') }}">
                            <small class="text-muted">{{ __('Low Birth Weight: < 2500g') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_gestational_age_weeks" class="form-label">{{ __('Gestational Age (weeks)') }}</label>
                            <input type="number" class="form-control" id="modal_gestational_age_weeks" name="gestational_age_weeks" min="20" max="45" step="1" placeholder="{{ __('e.g. 40') }}">
                            <small class="text-muted">{{ __('Preterm: < 37 weeks | Full term: 37-42 weeks') }}</small>
                        </div>

                        <div class="col-12" id="modal-pediatric-status-indicator" style="display:none;">
                            <div class="alert alert-info py-2 mb-0" id="modal-pediatric-status-message"></div>
                        </div>

                        <!-- Medical Information -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-heartbeat me-2"></i>
                                {{ __('Medical Information') }}
                            </h6>
                        </div>

                        <div class="col-12">
                            <label for="history_of_present_illness" class="form-label">{{ __('History of Present Illness') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="history_of_present_illness" name="history_of_present_illness" rows="3" placeholder="{{ __('Describe the history of the present illness...') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="allergies" class="form-label">{{ __('Allergies') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="allergies" name="allergies" rows="2" placeholder="{{ __('Known allergies and reactions') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="chronic_illnesses" class="form-label">{{ __('Chronic Illnesses') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="chronic_illnesses" name="chronic_illnesses" rows="2" placeholder="{{ __('Chronic conditions and ongoing health issues') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="surgeries_history" class="form-label">{{ __('Surgery History') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="surgeries_history" name="surgeries_history" rows="2" placeholder="{{ __('Previous surgeries and procedures') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="diet_history" class="form-label">{{ __('Diet History') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="diet_history" name="diet_history" rows="2" placeholder="{{ __('Previous diets and nutritional information') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="medical_files" class="form-label">
                                <i class="fas fa-file-medical me-1"></i>
                                {{ __('Medical History Files') }}
                            </label>
                            <input type="file" class="form-control" id="medical_files" name="medical_files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">{{ __('Upload medical reports, lab results, or other relevant documents (PDF, Images, Word documents)') }}</small>
                        </div>

                        <!-- Special Conditions -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Special Conditions') }}
                            </h6>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_pregnant" name="is_pregnant" value="1">
                                <label class="form-check-label" for="is_pregnant">
                                    {{ __('Currently Pregnant') }}
                                </label>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-phone-alt me-2"></i>
                                {{ __('Emergency Contact') }}
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="emergency_contact_name" class="form-label">{{ __('Emergency Contact Name') }}</label>
                            <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name" placeholder="{{ __('Full name of emergency contact') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="emergency_contact_phone" class="form-label">{{ __('Emergency Contact Phone') }}</label>
                            <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" placeholder="{{ __('Emergency contact phone number') }}">
                        </div>

                        <!-- Additional Notes -->
                        <div class="col-12 mt-4">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-sticky-note me-2"></i>
                                {{ __('Additional Notes') }}
                            </h6>
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <div class="voice-input-wrapper">
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ __('Additional notes about the patient') }}"></textarea>
                                <button type="button" class="btn-voice" title="{{ __('Voice input') }}"><i class="fas fa-microphone"></i></button>
                                <div class="voice-status"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Create Patient') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Pediatric status indicator for modal
document.addEventListener('DOMContentLoaded', function() {
    const bwInput = document.getElementById('modal_birth_weight');
    const gaInput = document.getElementById('modal_gestational_age_weeks');
    const indicator = document.getElementById('modal-pediatric-status-indicator');
    const message = document.getElementById('modal-pediatric-status-message');

    function updateModalPediatricStatus() {
        const bw = parseInt(bwInput.value);
        const ga = parseInt(gaInput.value);
        if (!bw && !ga) { indicator.style.display = 'none'; return; }

        let labels = [];
        let alertClass = 'alert-success';

        if (bw && bw < 2500) { labels.push('{{ __("Low Birth Weight") }} (<2500g)'); alertClass = 'alert-warning'; }
        if (ga && ga < 37) { labels.push('{{ __("Preterm") }} (<37 weeks)'); alertClass = 'alert-warning'; }

        if (labels.length === 0) {
            labels.push('{{ __("Normal birth weight & full term") }}');
        }

        message.className = 'alert py-2 mb-0 ' + alertClass;
        message.innerHTML = '<i class="fas fa-info-circle me-1"></i> <strong>{{ __("Detected") }}:</strong> ' + labels.join(' & ') +
            ' — {{ __("Growth chart will adjust automatically.") }}';
        indicator.style.display = '';
    }

    bwInput.addEventListener('input', updateModalPediatricStatus);
    gaInput.addEventListener('input', updateModalPediatricStatus);
});

function newPrescription(patientId) {
    window.location.href = `/simple-prescriptions/create?patient_id=${patientId}`;
}

function newAppointment(patientId) {
    window.location.href = `/appointments/create?patient_id=${patientId}`;
}

function confirmClearAll() {
    if (confirm('{{ __("Are you sure you want to delete ALL patients? This action cannot be undone!") }}')) {
        if (confirm('{{ __("This will permanently delete all patient records and their associated data. Are you absolutely sure?") }}')) {
            fetch('{{ route("patients.clear-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }
}
</script>
@include('partials.voice-input')
@endsection
