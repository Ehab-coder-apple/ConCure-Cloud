@extends('layouts.app')

@section('title', __('Patient Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        {{ __('Patient Details') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">{{ __('Patients') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Patient Details') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <div class="btn-group me-1" role="group">
                        <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-file-medical me-1"></i>
                            {{ __('Report') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('patient.report', $patient) }}" target="_blank">
                                    <i class="fas fa-eye me-2"></i>
                                    {{ __('View Report') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('patient.report', $patient) }}?format=pdf" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    {{ __('Download PDF') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="showReportModal()">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    {{ __('Custom Date Range') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('patient.blank-report', $patient) }}">
                                    <i class="fas fa-file-alt me-2"></i>
                                    {{ __('Blank Report') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('patients.edit', $patient->id ?? 1) }}" class="btn btn-outline-primary btn-sm me-1">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit') }}
                    </a>
                    <button type="button" class="btn btn-info btn-sm me-1" onclick="newAppointment()">
                        <i class="fas fa-calendar-plus me-1"></i>
                        {{ __('Appointment') }}
                    </button>
                    <a href="{{ route('patients.vital-signs.index', $patient) }}" class="btn btn-info btn-sm me-1">
                        <i class="fas fa-stethoscope me-1"></i>
                        {{ __('Vital Signs') }}
                    </a>
                    <a href="{{ route('patients.checkup-templates.index', $patient) }}" class="btn btn-warning btn-sm me-1">
                        <i class="fas fa-clipboard-list me-1"></i>
                        {{ __('Checkup') }}
                    </a>
                    <a href="{{ route('patients.forms.index', $patient) }}" class="btn btn-secondary btn-sm me-1">
                        <i class="fas fa-file-alt me-1"></i>
                        {{ __('Forms') }}
                    </a>

                    <button type="button" class="btn btn-primary btn-sm" onclick="newPrescription()">
                        <i class="fas fa-prescription-bottle-alt me-1"></i>
                        {{ __('Prescription') }}
                    </button>
                        <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary btn-sm ms-1" onclick="try{localStorage.setItem('prefill_transfer', JSON.stringify({transfer_type:'patient_file',patient_id:{{ $patient->id ?? 0 }},source_type:'patient',source_id:{{ $patient->id ?? 0 }},metadata:{patient_name:@json($patient->full_name ?? (($patient->first_name ?? 'Demo').' '.($patient->last_name ?? 'Patient')))}}));}catch(e){}">
                            <i class="fas fa-share-nodes me-1"></i>
                            {{ __('Share Internally') }}
                        </a>

                </div>

            </div>

            <div class="row">
                <!-- Patient Information -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-id-card me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="avatar bg-primary text-white rounded-circle mx-auto mb-2" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    {{ strtoupper(substr($patient->first_name ?? 'P', 0, 1) . substr($patient->last_name ?? 'A', 0, 1)) }}
                                </div>
                                <h5 class="mb-1">{{ ($patient->first_name ?? 'Demo') . ' ' . ($patient->last_name ?? 'Patient') }}</h5>
                                <span class="badge bg-primary">{{ $patient->patient_id ?? 'P000001' }}</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Age') }}</small>
                                    <div class="fw-bold">{{ $patient->age ?? ($patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '25') }} {{ __('years') }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Gender') }}</small>
                                    <div class="fw-bold">{{ ucfirst($patient->gender ?? 'Male') }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Phone') }}</small>
                                    <div class="fw-bold">{{ $patient->phone ?? '+1-555-0123' }}</div>
                                </div>
                                @if($patient->whatsapp_phone)
                                <div class="col-12">
                                    <small class="text-muted">
                                        <i class="fab fa-whatsapp text-success me-1"></i>
                                        {{ __('WhatsApp') }}
                                    </small>
                                    <div class="fw-bold">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $patient->whatsapp_phone) }}"
                                           target="_blank" class="text-success text-decoration-none">
                                            {{ $patient->whatsapp_phone }}
                                            <i class="fas fa-external-link-alt ms-1 small"></i>
                                        </a>
                                    </div>
                                </div>
                                @endif
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Email') }}</small>
                                    <div class="fw-bold">{{ $patient->email ?? 'demo@patient.com' }}</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">{{ __('Address') }}</small>
                                    <div class="fw-bold">{{ $patient->address ?? '123 Main Street, City, State' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-heartbeat me-2"></i>
                                {{ __('Latest Vital Signs') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Height') }}</small>
                                    <div class="fw-bold">{{ $patient->height ?? '170' }} cm</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Weight') }}</small>
                                    <div class="fw-bold">{{ $patient->weight ?? '70' }} kg</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('BMI') }}</small>
                                    <div class="fw-bold">{{ $patient->bmi ?? '24.2' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">{{ __('Blood Type') }}</small>
                                    <div class="fw-bold">{{ $patient->blood_type ?: 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Medical Images -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-images me-2"></i>
                                {{ __('Medical Images') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success py-2">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                            @endif

                            @php $canEditPatients = auth()->check() && (auth()->user()->canManagePatients() || auth()->user()->hasPermission('patients_edit')); @endphp
                            @if($canEditPatients)
                            <form action="{{ route('patients.images.store', $patient) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                @csrf
                                <div class="input-group mb-2">
                                    <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,application/pdf" multiple required>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-upload me-1"></i>{{ __('Upload') }}
                                    </button>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <input type="text" name="condition_tags" class="form-control" placeholder="{{ __('Condition tags (comma-separated), e.g., Knee, MRI, ACL') }}">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center">
                                        <small class="text-muted">{{ __('Allowed: JPG, PNG, PDF. Max 10MB each.') }}</small>
                                    </div>
                                </div>
                            </form>
                            @endif

                            @php
                                $patientImages = $patient->relationLoaded('images') ? $patient->images : \App\Models\PatientImage::where('patient_id', $patient->id)->latest()->limit(24)->get();
                            @endphp

                            @if($patientImages->count() === 0)
                                <div class="text-center py-4">
                                    <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No images uploaded yet.') }}</p>
                                </div>
                            @else
                                <div class="row g-2">
                                    @foreach($patientImages as $img)
                                        <div class="col-6 col-md-4">
                                            <div class="border rounded p-2 h-100 d-flex flex-column">
                                                @if(str_starts_with($img->mime ?? '', 'image/'))
                                                    <a href="{{ $img->url }}" target="_blank" class="d-block mb-2" title="{{ $img->filename }}">
                                                        <img src="{{ $img->url }}" alt="" class="img-fluid rounded" style="object-fit:cover; width:100%; height:140px;">
                                                    </a>
                                                @else
                                                    <a href="{{ $img->url }}" target="_blank" class="d-flex align-items-center justify-content-center bg-light rounded mb-2" style="height:140px;">
                                                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                    </a>
                                                @endif
                                                @if($img->caption)
                                                    <div class="small mb-1">{{ $img->caption }}</div>
                                                @endif
                                                @if(is_array($img->condition_tags) && count($img->condition_tags))
                                                    <div class="mb-1">
                                                        @foreach($img->condition_tags as $t)
                                                            <span class="badge bg-light text-dark border me-1">#{{ $t }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <div class="d-flex gap-1 mt-auto">
                                                    @if($canEditPatients)
                                                    <form action="{{ route('patients.images.update', [$patient, $img]) }}" method="POST" class="flex-grow-1 d-flex gap-1 flex-wrap">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="text" name="caption" class="form-control form-control-sm" placeholder="{{ __('Add caption') }}" value="{{ $img->caption }}" style="max-width: 45%">
                                                        <input type="text" name="condition_tags" class="form-control form-control-sm" placeholder="{{ __('Tags (comma-separated)') }}" value="{{ is_array($img->condition_tags) ? implode(', ', $img->condition_tags) : '' }}" style="max-width: 45%">
                                                        <button class="btn btn-sm btn-outline-secondary" type="submit" title="{{ __('Save') }}">
                                                            <i class="fas fa-save"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('patients.images.destroy', [$patient, $img]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this image?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Medical Records -->
                <div class="col-lg-8">
                    <!-- Medical History -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-file-medical me-2"></i>
                                {{ __('Medical History') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>{{ $patient->medical_history ?? __('No medical history recorded yet.') }}</p>
                        </div>
                    </div>
                    <!-- Forms Summary -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ __('Forms') }}
                            </h6>
                            <div>
                                <a href="{{ route('patients.forms.index', $patient) }}" class="btn btn-sm btn-outline-secondary me-2">
                                    <i class="fas fa-list me-1"></i> {{ __('View All') }}
                                </a>
                                @if(Auth::user()->canAssignForms())
                                <a href="{{ route('patients.forms.create', $patient) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus me-1"></i> {{ __('Assign Form') }}
                                </a>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                $recentForms = \App\Models\PatientForm::where('patient_id', $patient->id)
                                    ->where('clinic_id', $patient->clinic_id)
                                    ->with('template')
                                    ->orderByDesc('assigned_at')
                                    ->take(5)
                                    ->get();
                            @endphp
                            @if($recentForms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Form') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Assigned') }}</th>
                                                <th>{{ __('Completed') }}</th>
                                                <th class="text-end">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentForms as $f)
                                            @php
                                              $badge = match($f->status){
                                                'completed' => 'success',
                                                'in_progress' => 'info',
                                                default => 'secondary'
                                              };
                                            @endphp
                                            <tr>
                                                <td>{{ $f->template?->name ?? __('Form') }}</td>
                                                <td><span class="badge bg-{{ $badge }}">{{ __(Str::title(str_replace('_',' ', $f->status))) }}</span></td>
                                                <td><small class="text-muted">{{ $f->assigned_at?->format('Y-m-d') ?? '-' }}</small></td>
                                                <td><small class="text-muted">{{ $f->completed_at?->format('Y-m-d') ?? '-' }}</small></td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @if(Auth::user()->canFillForms() && $f->status !== 'completed')
                                                        <a href="{{ route('patients.forms.fill', [$patient, $f]) }}" class="btn btn-outline-primary" title="{{ __('Fill/Continue') }}">
                                                            <i class="fas fa-pen"></i>
                                                        </a>
                                                        @endif
                                                        <a href="{{ route('patients.forms.show', [$patient, $f]) }}" class="btn btn-outline-info" title="{{ __('Open') }}">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($f->status === 'completed')
                                                        <a href="{{ route('patients.forms.pdf', [$patient, $f]) }}?open=1" target="_blank" class="btn btn-outline-success" title="{{ __('Open PDF') }}">
                                                            <i class="fas fa-file-pdf"></i>
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
                                <div class="text-center py-4">
                                    <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-2">{{ __('No forms assigned yet.') }}</p>
                                    @if(Auth::user()->canAssignForms())
                                    <a href="{{ route('patients.forms.create', $patient) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> {{ __('Assign First Form') }}
                                    </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>


                    <!-- Recent Checkups -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-stethoscope me-2"></i>
                                {{ __('Recent Checkups') }}
                            </h6>
                            <a href="{{ route('checkups.create', $patient) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Checkup') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @if($patient->checkups && $patient->checkups->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($patient->checkups as $checkup)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge bg-success me-2">
                                                        {{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y') }}
                                                    </span>
                                                    @if($checkup->recorder)
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-md me-1"></i>
                                                            {{ $checkup->recorder->name }}
                                                        </small>
                                                    @endif
                                                </div>

                                                <div class="row g-2 mb-2">
                                                    @if($checkup->weight)
                                                        <div class="col-auto">
                                                            <small class="text-muted">
                                                                <i class="fas fa-weight me-1"></i>
                                                                <strong>{{ $checkup->weight }} kg</strong>
                                                            </small>
                                                        </div>
                                                    @endif
                                                    @if($checkup->height)
                                                        <div class="col-auto">
                                                            <small class="text-muted">
                                                                <i class="fas fa-ruler-vertical me-1"></i>
                                                                <strong>{{ $checkup->height }} cm</strong>
                                                            </small>
                                                        </div>
                                                    @endif
                                                    @if($checkup->blood_pressure)
                                                        <div class="col-auto">
                                                            <small class="text-muted">
                                                                <i class="fas fa-heartbeat me-1"></i>
                                                                <strong>{{ $checkup->blood_pressure }}</strong>
                                                            </small>
                                                        </div>
                                                    @endif
                                                    @if($checkup->temperature)
                                                        <div class="col-auto">
                                                            <small class="text-muted">
                                                                <i class="fas fa-thermometer-half me-1"></i>
                                                                <strong>{{ $checkup->temperature }}°C</strong>
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($checkup->symptoms)
                                                    <div class="mb-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-notes-medical me-1"></i>
                                                            <strong>{{ __('Symptoms:') }}</strong> {{ Str::limit($checkup->symptoms, 100) }}
                                                        </small>
                                                    </div>
                                                @endif

                                                @if($checkup->notes)
                                                    <div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clipboard me-1"></i>
                                                            {{ Str::limit($checkup->notes, 100) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="ms-3">
                                                <a href="{{ route('checkups.show', [$patient, $checkup]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if($patient->checkups->count() >= 10)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('checkups.index', $patient) }}" class="btn btn-sm btn-outline-secondary">
                                            {{ __('View All Checkups') }}
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-stethoscope fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-3">{{ __('No checkups recorded yet.') }}</p>
                                    <a href="{{ route('checkups.create', $patient) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i>
                                        {{ __('Record First Checkup') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Appointments -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>
                                {{ __('Recent Appointments') }}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="newAppointment()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Appointment') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @if($patient->appointments && $patient->appointments->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($patient->appointments as $appointment)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-info me-2">{{ $appointment->appointment_number }}</span>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_datetime)->format('M d, Y g:i A') }}</small>
                                                </div>
                                                <h6 class="mb-1">{{ $appointment->type ? ucfirst(str_replace('_', ' ', $appointment->type)) : __('Consultation') }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    {{ __('Doctor:') }} {{ $appointment->doctor->first_name ?? 'Unknown' }} {{ $appointment->doctor->last_name ?? '' }}
                                                </p>
                                                @if($appointment->reason)
                                                <p class="mb-0 small text-muted">{{ Str::limit($appointment->reason, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{
                                                    $appointment->status == 'completed' ? 'success' :
                                                    ($appointment->status == 'confirmed' ? 'primary' :
                                                    ($appointment->status == 'cancelled' ? 'danger' : 'secondary'))
                                                }}">
                                                    {{ ucfirst(str_replace('_', ' ', $appointment->status ?? 'scheduled')) }}
                                                </span>
                                                <div class="mt-1">
                                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($patient->appointments->count() >= 5)
                                <div class="text-center mt-3">
                                    <a href="{{ route('appointments.index') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Appointments') }}
                                    </a>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No appointments scheduled yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Prescriptions -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-prescription-bottle-alt me-2"></i>
                                {{ __('Recent Prescriptions') }}
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="newPrescription()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Prescription') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @php
                                $allPrescriptions = collect();
                                if($patient->prescriptions) {
                                    $allPrescriptions = $allPrescriptions->merge($patient->prescriptions);
                                }
                                if($patient->simplePrescriptions) {
                                    $allPrescriptions = $allPrescriptions->merge($patient->simplePrescriptions);
                                }
                                $allPrescriptions = $allPrescriptions->sortByDesc('created_at')->take(5);

                            @endphp


                            @if($allPrescriptions->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($allPrescriptions as $prescription)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-primary me-2">{{ $prescription->prescription_number ?? 'N/A' }}</span>
                                                    <small class="text-muted">{{ $prescription->prescribed_date ? \Carbon\Carbon::parse($prescription->prescribed_date)->format('M d, Y') : ($prescription->created_at ? $prescription->created_at->format('M d, Y') : 'N/A') }}</small>
                                                </div>
                                                <h6 class="mb-1">{{ $prescription->diagnosis ?? __('General Prescription') }}</h6>
                                                <p class="mb-1 text-muted small">
                                                    {{ __('Doctor:') }} {{ $prescription->doctor->first_name ?? 'Unknown' }} {{ $prescription->doctor->last_name ?? '' }}
                                                </p>
                                                @if($prescription->notes)
                                                <p class="mb-0 small text-muted">{{ Str::limit($prescription->notes, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $prescription->status == 'active' ? 'success' : ($prescription->status == 'completed' ? 'info' : 'secondary') }}">
                                                    {{ ucfirst($prescription->status ?? 'active') }}
                                                </span>
                                                <div class="mt-1">
                                                    @if(get_class($prescription) === 'App\Models\SimplePrescription')
                                                        <a href="{{ route('simple-prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="text-center mt-3">
                                    <a href="{{ route('simple-prescriptions.index') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Prescriptions') }}
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-prescription-bottle fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No prescriptions recorded yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Checkups -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-heartbeat me-2"></i>
                                {{ __('Recent Checkups') }}
                            </h6>
                            <a href="{{ route('checkups.index', $patient) }}" class="btn btn-outline-primary me-2">
                            <i class="fas fa-list me-1"></i>
                            {{ __('View All Checkups') }}
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="newCheckup()">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add Checkup') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @if($patient->checkups && $patient->checkups->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($patient->checkups as $checkup)
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y g:i A') }}</small>
                                                    <span class="badge bg-light text-dark ms-2">{{ __('Checkup') }}</span>
                                                </div>
                                                <div class="row g-2 mb-2">
                                                    @if($checkup->weight)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Weight') }}</small>
                                                        <span class="fw-bold">{{ $checkup->weight }} kg</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->blood_pressure)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('BP') }}</small>
                                                        <span class="fw-bold">{{ $checkup->blood_pressure }}</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->heart_rate)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Heart Rate') }}</small>
                                                        <span class="fw-bold">{{ $checkup->heart_rate }} bpm</span>
                                                    </div>
                                                    @endif
                                                    @if($checkup->temperature)
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block">{{ __('Temperature') }}</small>
                                                        <span class="fw-bold">{{ $checkup->temperature }}°C</span>
                                                    </div>
                                                    @endif
                                                </div>
                                                @if($checkup->symptoms)
                                                <p class="mb-1 small"><strong>{{ __('Symptoms:') }}</strong> {{ Str::limit($checkup->symptoms, 100) }}</p>
                                                @endif
                                                @if($checkup->notes)
                                                <p class="mb-0 small text-muted">{{ Str::limit($checkup->notes, 100) }}</p>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted">{{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($patient->checkups->count() >= 10)
                                <div class="text-center mt-3">
                                    <a href="{{ route('checkups.index', $patient) }}" class="btn btn-sm btn-outline-secondary">
                                        {{ __('View All Checkups') }}
                                    </a>
                                </div>
                                @endif
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-heartbeat fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No checkups recorded yet.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Lab Results -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-flask me-2"></i>
                                {{ __('Lab Results') }}
                            </h6>
                            <a href="{{ route('recommendations.lab-requests') }}?patient_id={{ $patient->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('New Lab Request') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success py-2">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
                            @endif

                            @php $canEditPatients = auth()->check() && (auth()->user()->canManagePatients() || auth()->user()->hasPermission('patients_edit')); @endphp
                            @if($canEditPatients)
                            <form action="{{ route('patients.upload', $patient) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                @csrf
                                <input type="hidden" name="category" value="lab_result">
                                <div class="input-group">
                                    <input type="file" name="file" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
                                    <input type="text" name="description" class="form-control" placeholder="{{ __('Description (optional)') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-upload me-1"></i>{{ __('Upload Lab Result') }}
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">{{ __('Allowed: JPG, JPEG, PNG, PDF. Max 10MB.') }}</small>
                            </form>
                            @endif

                            @php
                                $labFiles = \App\Models\PatientFile::byPatient($patient->id)->byCategory('lab_result')->latest()->get();
                            @endphp

                            @if($labFiles->count() === 0)
                                <div class="text-center py-4">
                                    <i class="fas fa-vial fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No lab results recorded yet.') }}</p>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{{ __('File') }}</th>
                                                <th>{{ __('Uploaded') }}</th>
                                                <th class="text-end">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($labFiles as $f)
                                            <tr>
                                                <td>
                                                    <i class="{{ $f->file_icon }} me-2"></i>
                                                    <a href="{{ $f->file_url }}" target="_blank">{{ $f->original_name }}</a>
                                                    <div class="small text-muted">{{ strtoupper($f->file_extension) }} • {{ $f->file_size_human }}</div>
                                                </td>
                                                <td>
                                                    {{ $f->created_at?->format('Y-m-d H:i') }}
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                                                        <a href="{{ $f->file_url }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="fas fa-external-link-alt"></i> {{ __('Open') }}</a>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openAndPrint('{{ $f->file_url }}')"><i class="fas fa-print"></i> {{ __('Print') }}</button>
                                                        @if($canEditPatients)
                                                        <form action="{{ route('patients.files.update', [$patient, $f]) }}" method="POST" class="d-flex gap-1">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="text" name="description" class="form-control form-control-sm" placeholder="{{ __('Add caption') }}" value="{{ $f->description }}" style="max-width:220px;">
                                                            <button class="btn btn-sm btn-outline-secondary" type="submit" title="{{ __('Save') }}"><i class="fas fa-save"></i></button>
                                                        </form>
                                                        <form action="{{ route('patients.files.destroy', [$patient, $f]) }}" method="POST" onsubmit="return confirm('{{ __('Delete this file?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Medical Reports -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-file-medical me-2"></i>
                                {{ __('Medical Reports') }}
                            </h6>
                            <a href="{{ route('patient.blank-report', $patient) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Create Report') }}
                            </a>
                        </div>
                        <div class="card-body">
                            @php
                                $medicalReports = \App\Models\PatientFile::byPatient($patient->id)->byCategory('medical_report')->latest()->get();
                            @endphp

                            @if($medicalReports->count() === 0)
                                <div class="text-center py-4">
                                    <i class="fas fa-file-medical fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">{{ __('No medical reports created yet.') }}</p>
                                    <a href="{{ route('patient.blank-report', $patient) }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i>
                                        {{ __('Create First Report') }}
                                    </a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Report') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Created') }}</th>
                                                <th class="text-end">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($medicalReports as $report)
                                            <tr>
                                                <td>
                                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                                    <a href="{{ $report->file_url }}" target="_blank">{{ $report->original_name }}</a>
                                                    <div class="small text-muted">{{ $report->file_size_human }}</div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{ $report->description ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    <div>{{ $report->created_at?->format('M d, Y') }}</div>
                                                    <div class="small text-muted">{{ $report->created_at?->format('h:i A') }}</div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex gap-1 justify-content-end flex-wrap">
                                                        <a href="{{ $report->file_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-external-link-alt"></i> {{ __('Open') }}
                                                        </a>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openAndPrint('{{ $report->file_url }}')">
                                                            <i class="fas fa-print"></i> {{ __('Print') }}
                                                        </button>
                                                        @if($canEditPatients)
                                                        <form action="{{ route('patients.files.destroy', [$patient, $report]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this report?') }}')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="{{ __('Delete') }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <script>
                    function openAndPrint(url){
                        const w = window.open(url, '_blank');
                        if(!w){ return; }
                        const tryPrint = () => { try { w.focus(); w.print(); } catch(e){} };
                        w.onload = tryPrint;
                        setTimeout(tryPrint, 1200);
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Checkup Modal -->
<div class="modal fade" id="checkupModal" tabindex="-1" aria-labelledby="checkupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkupModalLabel">
                    <i class="fas fa-heartbeat me-2"></i>
                    {{ __('Add New Checkup') }} - {{ $patient->full_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('checkups.store', $patient) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Vital Signs -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-heartbeat me-1"></i>
                                {{ __('Vital Signs') }}
                            </h6>

                            <div class="mb-3">
                                <label for="weight" class="form-label">{{ __('Weight (kg)') }}</label>
                                <input type="number" class="form-control" id="weight" name="weight"
                                       step="0.1" min="1" max="500" placeholder="70.5">
                            </div>

                            <div class="mb-3">
                                <label for="height" class="form-label">{{ __('Height (cm)') }}</label>
                                <input type="number" class="form-control" id="height" name="height"
                                       step="0.1" min="50" max="300" placeholder="175">
                            </div>

                            <div class="mb-3">
                                <label for="blood_pressure" class="form-label">{{ __('Blood Pressure') }}</label>
                                <input type="text" class="form-control" id="blood_pressure" name="blood_pressure"
                                       placeholder="120/80" pattern="\d{2,3}/\d{2,3}">
                                <div class="form-text">{{ __('Format: 120/80') }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="heart_rate" class="form-label">{{ __('Heart Rate (bpm)') }}</label>
                                <input type="number" class="form-control" id="heart_rate" name="heart_rate"
                                       min="30" max="200" placeholder="72">
                            </div>
                        </div>

                        <!-- Additional Measurements -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-thermometer-half me-1"></i>
                                {{ __('Additional Measurements') }}
                            </h6>

                            <div class="mb-3">
                                <label for="temperature" class="form-label">{{ __('Temperature (°C)') }}</label>
                                <input type="number" class="form-control" id="temperature" name="temperature"
                                       step="0.1" min="30" max="45" placeholder="36.5">
                            </div>

                            <div class="mb-3">
                                <label for="respiratory_rate" class="form-label">{{ __('Respiratory Rate (per min)') }}</label>
                                <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate"
                                       min="5" max="50" placeholder="16">
                            </div>

                            <div class="mb-3">
                                <label for="blood_sugar" class="form-label">{{ __('Blood Sugar (mg/dL)') }}</label>
                                <input type="number" class="form-control" id="blood_sugar" name="blood_sugar"
                                       step="0.1" min="20" max="600" placeholder="100">
                            </div>
                        </div>
                    </div>

                    <!-- Template Selection -->
                    @php
                        $patientTemplates = $patient->assigned_checkup_templates;
                    @endphp

                    @if($patientTemplates->count() > 0)
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="checkup_template" class="form-label">{{ __('Checkup Template') }}</label>
                            <select class="form-select" id="checkup_template" name="template_id" onchange="loadTemplateFields()">
                                <option value="">{{ __('Standard Checkup (No Template)') }}</option>
                                @foreach($patientTemplates as $assignment)
                                    <option value="{{ $assignment->template->id }}"
                                            data-template="{{ json_encode($assignment->template->form_sections) }}">
                                        {{ $assignment->template->name }}
                                        @if($assignment->medical_condition) - {{ $assignment->medical_condition }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('Select a specialized checkup template or use standard checkup') }}</small>
                        </div>
                    </div>
                    @endif

                    <!-- Custom Vital Signs -->
                    @php
                        $patientCustomSigns = $patient->assigned_custom_vital_signs;
                    @endphp

                    @if($patientCustomSigns->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-stethoscope me-1"></i>
                                {{ __('Additional Vital Signs') }}
                            </h6>

                            <div class="row">
                                @foreach($patientCustomSigns as $assignment)
                                    @php $sign = $assignment->customVitalSign; @endphp
                                    <div class="col-md-6 mb-3">
                                        <label for="custom_{{ $sign->id }}" class="form-label">
                                            {{ $sign->display_name }}
                                            @if($sign->normal_range)
                                                <small class="text-muted">(Normal: {{ $sign->normal_range }})</small>
                                            @endif
                                            @if($assignment->medical_condition)
                                                <br><small class="text-info">{{ $assignment->medical_condition }}</small>
                                            @endif
                                        </label>

                                        @if($sign->type === 'select')
                                            <select class="form-select" id="custom_{{ $sign->id }}" name="custom_vital_signs[{{ $sign->id }}]">
                                                <option value="">{{ __('Select...') }}</option>
                                                @foreach($sign->options as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="number" class="form-control" id="custom_{{ $sign->id }}"
                                                   name="custom_vital_signs[{{ $sign->id }}]"
                                                   @if($sign->min_value) min="{{ $sign->min_value }}" @endif
                                                   @if($sign->max_value) max="{{ $sign->max_value }}" @endif
                                                   step="0.1" placeholder="{{ $sign->unit ? 'Enter value in ' . $sign->unit : 'Enter value' }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    {{ __('No Custom Vital Signs Assigned') }}
                                </h6>
                                <p class="mb-2">{{ __('This patient has no custom vital signs assigned yet.') }}</p>
                                <a href="{{ route('patients.vital-signs.index', $patient) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-stethoscope me-1"></i>
                                    {{ __('Assign Vital Signs') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Custom Template Fields -->
                    <div id="customTemplateFields" style="display: none;">
                        <!-- Template fields will be loaded here dynamically -->
                    </div>

                    <!-- Clinical Notes -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-notes-medical me-1"></i>
                                {{ __('Clinical Notes') }}
                            </h6>

                            <div class="mb-3">
                                <label for="symptoms" class="form-label">{{ __('Symptoms') }}</label>
                                <textarea class="form-control" id="symptoms" name="symptoms" rows="3"
                                          placeholder="{{ __('Describe any symptoms the patient is experiencing...') }}"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('Clinical Notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"
                                          placeholder="{{ __('Additional observations and notes...') }}"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="recommendations" class="form-label">{{ __('Recommendations') }}</label>
                                <textarea class="form-control" id="recommendations" name="recommendations" rows="3"
                                          placeholder="{{ __('Treatment recommendations and follow-up instructions...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Save Checkup') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Report Date Range Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-file-medical me-2"></i>
                    {{ __('Generate Custom Report') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm" method="GET" action="{{ route('patient.report', $patient) }}">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="report_date_from" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" id="report_date_from" name="date_from"
                                   value="{{ now()->subMonths(6)->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="report_date_to" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" id="report_date_to" name="date_to"
                                   value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="report_format" class="form-label">{{ __('Format') }}</label>
                            <select class="form-select" id="report_format" name="format">
                                <option value="html">{{ __('View in Browser') }}</option>
                                <option value="pdf">{{ __('Download PDF') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-medical me-1"></i>
                        {{ __('Generate Report') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function newPrescription() {
    window.location.href = `/simple-prescriptions/create?patient_id={{ $patient->id ?? 1 }}`;
}

function loadTemplateFields() {
    const templateSelect = document.getElementById('checkup_template');
    const customFieldsContainer = document.getElementById('customTemplateFields');

    if (templateSelect.value) {
        const selectedOption = templateSelect.selectedOptions[0];
        const templateData = JSON.parse(selectedOption.dataset.template || '{}');

        let fieldsHtml = '<div class="row mt-4"><div class="col-12"><h5><i class="fas fa-clipboard-list me-2"></i>Template Fields</h5></div></div>';

        Object.keys(templateData).forEach(sectionKey => {
            const section = templateData[sectionKey];
            fieldsHtml += `<div class="row mt-3"><div class="col-12"><h6 class="text-primary">${section.title || sectionKey}</h6></div></div>`;
            fieldsHtml += '<div class="row">';

            Object.keys(section.fields || {}).forEach(fieldKey => {
                const field = section.fields[fieldKey];
                fieldsHtml += `<div class="col-md-6 mb-3">`;
                fieldsHtml += `<label for="custom_field_${fieldKey}" class="form-label">${field.label}`;
                if (field.required) fieldsHtml += ' <span class="text-danger">*</span>';
                fieldsHtml += '</label>';

                switch (field.type) {
                    case 'select':
                        fieldsHtml += `<select class="form-select" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        fieldsHtml += '<option value="">Select...</option>';
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(option => {
                                fieldsHtml += `<option value="${option}">${option}</option>`;
                            });
                        }
                        fieldsHtml += '</select>';
                        break;
                    case 'textarea':
                        fieldsHtml += `<textarea class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]" rows="3"${field.required ? ' required' : ''}></textarea>`;
                        break;
                    case 'checkbox':
                        fieldsHtml += `<div class="form-check"><input class="form-check-input" type="checkbox" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]" value="1"><label class="form-check-label" for="custom_field_${fieldKey}">${field.label}</label></div>`;
                        break;
                    case 'date':
                        fieldsHtml += `<input type="date" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        break;
                    case 'time':
                        fieldsHtml += `<input type="time" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                        break;
                    case 'number':
                        fieldsHtml += `<input type="number" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"`;
                        if (field.min) fieldsHtml += ` min="${field.min}"`;
                        if (field.max) fieldsHtml += ` max="${field.max}"`;
                        if (field.step) fieldsHtml += ` step="${field.step}"`;
                        fieldsHtml += `${field.required ? ' required' : ''}>`;
                        break;
                    default:
                        fieldsHtml += `<input type="text" class="form-control" id="custom_field_${fieldKey}" name="custom_fields[${fieldKey}]"${field.required ? ' required' : ''}>`;
                }

                fieldsHtml += '</div>';
            });

            fieldsHtml += '</div>';
        });

        customFieldsContainer.innerHTML = fieldsHtml;
        customFieldsContainer.style.display = 'block';
    } else {
        customFieldsContainer.style.display = 'none';
        customFieldsContainer.innerHTML = '';
    }
}

function newAppointment() {
    window.location.href = `/appointments/create?patient_id={{ $patient->id ?? 1 }}`;
}

function newCheckup() {
    const modal = new bootstrap.Modal(document.getElementById('checkupModal'));
    modal.show();
}

function showReportModal() {
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    modal.show();
}

// Handle report form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const params = new URLSearchParams(formData);
    const url = this.action + '?' + params.toString();

    // Open in new tab


    window.open(url, '_blank');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    modal.hide();
});
</script>
@endsection
