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
                <div class="btn-group">
                    <a href="{{ route('patients.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import me-2"></i>
                        {{ __('Import Patients') }}
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
                                   value="{{ request('search') }}" placeholder="{{ __('Name, ID, Phone...') }}">
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
                                            <span class="badge bg-{{ $patient->gender == 'male' ? 'info' : 'pink' }}">
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    {{ __('Add New Patient') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('patients.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">{{ __('Date of Birth') }}</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                        </div>
                        <div class="col-md-6">
                            <label for="gender" class="form-label">{{ __('Gender') }}</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">{{ __('Select Gender') }}</option>
                                <option value="male">{{ __('Male') }}</option>
                                <option value="female">{{ __('Female') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
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
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="medical_history" class="form-label">{{ __('Medical History') }}</label>
                            <textarea class="form-control" id="medical_history" name="medical_history" rows="3" placeholder="{{ __('Any relevant medical history, allergies, or conditions...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        {{ __('Add Patient') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function newPrescription(patientId) {
    window.location.href = `/simple-prescriptions/create?patient_id=${patientId}`;
}

function newAppointment(patientId) {
    window.location.href = `/appointments/create?patient_id=${patientId}`;
}
</script>
@endsection
