@extends('layouts.app')

@section('title', __('Appointments'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                    {{ __('Appointments') }}
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Appointment') }}
                </button>
            </div>

            <!-- Calendar View Toggle -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="viewType" id="listView" {{ ($viewType ?? 'list') === 'list' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="listView">
                                    <i class="fas fa-list me-1"></i>
                                    {{ __('List View') }}
                                </label>

                                <input type="radio" class="btn-check" name="viewType" id="calendarView" {{ ($viewType ?? 'list') === 'calendar' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="calendarView">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ __('Calendar View') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('appointments.index') }}" class="row g-2">
                                <div class="col-md-6">
                                    <select class="form-select" name="status">
                                        <option value="">{{ __('All Statuses') }}</option>
                                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>{{ __('Scheduled') }}</option>
                                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" name="date" value="{{ request('date', date('Y-m-d')) }}">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointments List -->
            <div id="listViewContent" class="card" style="display: {{ ($viewType ?? 'list') === 'list' ? 'block' : 'none' }};">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('Today\'s Appointments') }} - {{ date('F d, Y') }}
                        <span class="badge bg-primary ms-2">{{ $appointments->count() ?? 8 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($appointments) && $appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Time') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($appointment->appointment_datetime ?? now())->format('g:i A') }}</div>
                                            <small class="text-muted">{{ $appointment->duration_minutes ?? '30' }} {{ __('min') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($appointment->patient_first_name ?? 'P', 0, 1) . substr($appointment->patient_last_name ?? 'A', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ ($appointment->patient_first_name ?? 'Demo') . ' ' . ($appointment->patient_last_name ?? 'Patient') }}</div>
                                                    <small class="text-muted">{{ $appointment->patient_phone ?? '+1-555-0123' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ ($appointment->doctor_first_name ?? 'Dr. John') . ' ' . ($appointment->doctor_last_name ?? 'Smith') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ ucfirst($appointment->type ?? 'Consultation') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'scheduled' ? 'warning' : ($appointment->status == 'completed' ? 'primary' : 'secondary')) }}">
                                                {{ ucfirst($appointment->status ?? 'Scheduled') }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($appointment->notes ?? 'Regular checkup', 30) }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-success" title="{{ __('Confirm') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" title="{{ __('Cancel') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Demo Appointments -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Time') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">9:00 AM</div>
                                            <small class="text-muted">30 min</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    DP
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Demo Patient</div>
                                                    <small class="text-muted">+1-555-0123</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Dr. John Smith</td>
                                        <td><span class="badge bg-light text-dark">Consultation</span></td>
                                        <td><span class="badge bg-success">Confirmed</span></td>
                                        <td>Regular checkup</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-success" title="{{ __('Confirm') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" title="{{ __('Cancel') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">10:30 AM</div>
                                            <small class="text-muted">45 min</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    JS
                                                </div>
                                                <div>
                                                    <div class="fw-bold">John Smith</div>
                                                    <small class="text-muted">+1-555-0456</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Dr. John Smith</td>
                                        <td><span class="badge bg-light text-dark">Follow-up</span></td>
                                        <td><span class="badge bg-warning">Scheduled</span></td>
                                        <td>Blood pressure check</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-success" title="{{ __('Confirm') }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" title="{{ __('Cancel') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="fw-bold">2:00 PM</div>
                                            <small class="text-muted">30 min</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    SA
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Sarah Ahmed</div>
                                                    <small class="text-muted">+1-555-0789</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Dr. John Smith</td>
                                        <td><span class="badge bg-light text-dark">Emergency</span></td>
                                        <td><span class="badge bg-primary">Completed</span></td>
                                        <td>Urgent care visit</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info" title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success" title="{{ __('Create Prescription') }}">
                                                    <i class="fas fa-prescription-bottle-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendarViewContent" class="card" style="display: {{ ($viewType ?? 'list') === 'calendar' ? 'block' : 'none' }};">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>
                        {{ __('Appointment Calendar') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="appointmentCalendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Appointment Modal -->
<div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-labelledby="newAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newAppointmentModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>
                    {{ __('New Appointment') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                <option value="1">Demo Patient (P000001)</option>
                                <option value="2">John Smith (P000002)</option>
                                <option value="3">Sarah Ahmed (P000003)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="doctor_id" class="form-label">{{ __('Doctor') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">{{ __('Select Doctor') }}</option>
                                <option value="1">Dr. John Smith</option>
                                <option value="2">Dr. Sarah Johnson</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment_date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment_time" class="form-label">{{ __('Time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="appointment_type" class="form-label">{{ __('Type') }}</label>
                            <select class="form-select" id="appointment_type" name="appointment_type">
                                <option value="consultation">{{ __('Consultation') }}</option>
                                <option value="follow_up">{{ __('Follow-up') }}</option>
                                <option value="emergency">{{ __('Emergency') }}</option>
                                <option value="routine_checkup">{{ __('Routine Checkup') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="duration" class="form-label">{{ __('Duration (minutes)') }}</label>
                            <select class="form-select" id="duration" name="duration">
                                <option value="15">15 {{ __('minutes') }}</option>
                                <option value="30" selected>30 {{ __('minutes') }}</option>
                                <option value="45">45 {{ __('minutes') }}</option>
                                <option value="60">60 {{ __('minutes') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ __('Appointment notes or special instructions...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        {{ __('Schedule Appointment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let calendar;

// Initialize calendar when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
});

// Toggle between list and calendar view
document.getElementById('listView').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('listViewContent').style.display = 'block';
        document.getElementById('calendarViewContent').style.display = 'none';
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('view', 'list');
        window.history.pushState({}, '', url);
    }
});

document.getElementById('calendarView').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('listViewContent').style.display = 'none';
        document.getElementById('calendarViewContent').style.display = 'block';
        // Update URL without page reload
        const url = new URL(window.location);
        url.searchParams.set('view', 'calendar');
        window.history.pushState({}, '', url);

        // Initialize or refresh calendar
        if (calendar) {
            calendar.render();
        } else {
            initializeCalendar();
        }
    }
});

function initializeCalendar() {
    const calendarEl = document.getElementById('appointmentCalendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        events: @json($calendarEvents ?? []),
        eventClick: function(info) {
            showAppointmentDetails(info.event);
        },
        eventMouseEnter: function(info) {
            // Show tooltip on hover
            info.el.setAttribute('title',
                info.event.extendedProps.patient + '\n' +
                'Doctor: ' + info.event.extendedProps.doctor + '\n' +
                'Type: ' + info.event.extendedProps.type + '\n' +
                'Status: ' + info.event.extendedProps.status + '\n' +
                'Duration: ' + info.event.extendedProps.duration
            );
        },
        eventDidMount: function(info) {
            // Add custom styling based on status
            info.el.style.cursor = 'pointer';
        }
    });

    calendar.render();
}

function showAppointmentDetails(event) {
    const props = event.extendedProps;
    const startTime = new Date(event.start).toLocaleString();
    const endTime = new Date(event.end).toLocaleString();

    const details = `
        <div class="appointment-details">
            <h6 class="mb-3">${event.title}</h6>
            <p><strong>Doctor:</strong> ${props.doctor}</p>
            <p><strong>Type:</strong> ${props.type}</p>
            <p><strong>Status:</strong> <span class="badge bg-primary">${props.status}</span></p>
            <p><strong>Time:</strong> ${startTime} - ${endTime}</p>
            <p><strong>Duration:</strong> ${props.duration}</p>
            ${props.notes ? `<p><strong>Notes:</strong> ${props.notes}</p>` : ''}
        </div>
    `;

    // Create and show modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ${details}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="/appointments/${event.id}" class="btn btn-primary">View Full Details</a>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();

    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}
</script>
@endpush
@endsection
