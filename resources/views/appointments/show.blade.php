@extends('layouts.app')

@section('title', __('Appointment Details'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        {{ __('Appointment Details') }}
                    </h1>
                    <p class="text-muted mb-0">{{ $appointment->appointment_number }}</p>
                </div>
                <div>
                    <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit Appointment') }}
                    </a>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Appointments') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Appointment Information -->
                <div class="col-lg-8">
	                    @php
	                        $apptDateTime = null;
	                        if (!empty($appointment->appointment_datetime)) {
	                            $apptDateTime = \Carbon\Carbon::parse($appointment->appointment_datetime);
	                        } elseif (!empty($appointment->appointment_date)) {
	                            $apptDateTime = \Carbon\Carbon::parse(trim($appointment->appointment_date . ' ' . ($appointment->appointment_time ?? '00:00:00')));
	                        }

	                        $apptDateStr = $apptDateTime ? $apptDateTime->format('F d, Y') : '-';
	                        $apptTimeStr = $apptDateTime ? $apptDateTime->format('g:i A') : '-';
	                        $durationMinutes = $appointment->duration_minutes ?? $appointment->duration ?? null;
	                    @endphp

                    <!-- Appointment Header -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ __('Appointment Information') }}
                                    </h6>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <span class="badge bg-{{
                                        $appointment->status == 'completed' ? 'success' :
                                        ($appointment->status == 'cancelled' ? 'danger' :
                                        ($appointment->status == 'confirmed' ? 'primary' : 'secondary'))
                                    }} fs-6">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Appointment Number') }}</small>
                                    <h5 class="mb-0">{{ $appointment->appointment_number }}</h5>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Date & Time') }}</small>
                                    <div class="fw-bold">
	                                        {{ $apptDateStr }}
	                                        <span class="text-primary">{{ $apptTimeStr }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Duration') }}</small>
	                                    <div class="fw-bold">{{ $durationMinutes ?? '-' }} {{ __('minutes') }}</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Type') }}</small>
                                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $appointment->type ?? 'Consultation')) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

	                    @if($receipt)
	                    <!-- Payment Information -->
	                    <div class="card shadow-sm mb-4">
	                        <div class="card-header bg-light">
	                            <div class="d-flex justify-content-between align-items-center">
	                                <h6 class="mb-0">
	                                    <i class="fas fa-receipt me-2"></i>
	                                    {{ __('Payment Information') }}
	                                </h6>
	                                <span class="badge bg-success">{{ __('Paid') }}</span>
	                            </div>
	                        </div>
	                        <div class="card-body">
	                            <div class="row">
	                                <div class="col-md-6 mb-3">
	                                    <small class="text-muted d-block">{{ __('Amount Collected') }}</small>
	                                    <h5 class="mb-0">{{ $currencySymbol ?? '$' }}{{ number_format((float) $receipt->amount, 2) }}</h5>
	                                </div>

	                                <div class="col-md-6 mb-3">
	                                    <small class="text-muted d-block">{{ __('Payment Method') }}</small>
	                                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }}</div>
	                                </div>

	                                <div class="col-md-6 mb-3">
	                                    <small class="text-muted d-block">{{ __('Receipt Number') }}</small>
	                                    <div class="fw-bold">{{ $receipt->receipt_number }}</div>
	                                </div>

	                                @if(!empty($receipt->notes))
	                                <div class="col-12 mb-0">
	                                    <small class="text-muted d-block">{{ __('Payment Notes') }}</small>
	                                    <div class="text-break">{{ $receipt->notes }}</div>
	                                </div>
	                                @endif
	                            </div>

	                            <div class="mt-3">
	                                <a href="{{ route('appointments.receipt-pdf', $appointment->id) }}" class="btn btn-primary" target="_blank">
	                                    <i class="fas fa-print me-2"></i>
	                                    {{ __('Print Receipt') }}
	                                </a>
	                            </div>
	                        </div>
	                    </div>
	                    @endif

                    <!-- Patient Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Patient Name') }}</small>
                                    <div class="fw-bold">{{ $appointment->patient_first_name }} {{ $appointment->patient_last_name }}</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Patient ID') }}</small>
                                    <div class="fw-bold">{{ $appointment->patient_id }}</div>
                                </div>

                                @if($appointment->patient_phone)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Phone') }}</small>
                                    <div class="fw-bold">{{ $appointment->patient_phone }}</div>
                                </div>
                                @endif

                                @if($appointment->patient_email)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Email') }}</small>
                                    <div class="fw-bold">{{ $appointment->patient_email }}</div>
                                </div>
                                @endif

                                @if($appointment->date_of_birth)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Age') }}</small>
                                    @php
                                        $patient = \App\Models\Patient::where('id', $appointment->patient_id)->first();
                                    @endphp
                                    <div class="fw-bold">{{ $patient ? $patient->age_formatted : \Carbon\Carbon::parse($appointment->date_of_birth)->age . ' years' }}</div>
                                </div>
                                @endif

                                @if($appointment->gender)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Gender') }}</small>
                                    <div class="fw-bold">{{ ucfirst($appointment->gender) }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-md me-2"></i>
                                {{ __('Doctor Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Doctor Name') }}</small>
                                    <div class="fw-bold">Dr. {{ $appointment->doctor_first_name }} {{ $appointment->doctor_last_name }}</div>
                                </div>

                                @if($appointment->doctor_phone)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted d-block">{{ __('Phone') }}</small>
                                    <div class="fw-bold">{{ $appointment->doctor_phone }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Additional Information -->
                    @if($appointment->notes || $appointment->diagnosis || $appointment->treatment)
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                {{ __('Notes & Details') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($appointment->notes)
                            <div class="mb-3">
                                <small class="text-muted d-block">{{ __('Appointment Notes') }}</small>
                                <p class="mb-0">{{ $appointment->notes }}</p>
                            </div>
                            @endif

                            @if($appointment->diagnosis)
                            <div class="mb-3">
                                <small class="text-muted d-block">{{ __('Diagnosis') }}</small>
                                <p class="mb-0">{{ $appointment->diagnosis }}</p>
                            </div>
                            @endif

                            @if($appointment->treatment)
                            <div class="mb-0">
                                <small class="text-muted d-block">{{ __('Treatment') }}</small>
                                <p class="mb-0">{{ $appointment->treatment }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-cogs me-2"></i>
                                {{ __('Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>
                                    {{ __('Edit Appointment') }}
                                </a>

                                @if($appointment->status == 'scheduled')
                                <button type="button" class="btn btn-outline-success" onclick="updateStatus('confirmed')">
                                    <i class="fas fa-check me-2"></i>
                                    {{ __('Confirm Appointment') }}
                                </button>
                                @endif

                                @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                <button type="button" class="btn btn-outline-info" onclick="updateStatus('in_progress')">
                                    <i class="fas fa-play me-2"></i>
                                    {{ __('Start Appointment') }}
                                </button>
                                @endif

                                @if(in_array($appointment->status, ['scheduled', 'confirmed', 'in_progress']))
                                <button type="button" class="btn btn-outline-success" onclick="updateStatus('completed')">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ __('Complete Appointment') }}
                                </button>
                                @endif

                                @if(in_array($appointment->status, ['scheduled', 'confirmed']))
                                <button type="button" class="btn btn-outline-danger" onclick="updateStatus('cancelled')">
                                    <i class="fas fa-times me-2"></i>
                                    {{ __('Cancel Appointment') }}
                                </button>
                                @endif

                                <button type="button" class="btn btn-success" onclick="shareAppointmentWhatsApp()">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    {{ __('Send via WhatsApp') }}
                                </button>

                                @if(auth()->user()->canAccessModule('whatsapp'))
                                <button type="button" class="btn btn-outline-success" id="sendReminderBtn" onclick="sendWhatsAppReminder()">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    {{ __('Send WhatsApp Reminder') }}
                                </button>
                                @endif

                                <button type="button" class="btn btn-outline-danger" onclick="deleteAppointment()">
                                    <i class="fas fa-trash me-2"></i>
                                    {{ __('Delete Appointment') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Summary -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>
                                {{ __('Summary') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Created By') }}</small>
                                <div>{{ $appointment->creator_first_name ?? 'System' }} {{ $appointment->creator_last_name ?? '' }}</div>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted">{{ __('Created Date') }}</small>
                                <div>{{ \Carbon\Carbon::parse($appointment->created_at)->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete this appointment? This action cannot be undone.') }}</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('The patient will need to be notified of the cancellation.') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('Delete Appointment') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateStatus(status) {
    fetch(`/appointments/{{ $appointment->id }}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteAppointment() {
    document.getElementById('deleteForm').action = `/appointments/{{ $appointment->id }}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function shareAppointmentWhatsApp() {
  const patientName = "{{ trim(($appointment->patient_first_name ?? '') . ' ' . ($appointment->patient_last_name ?? '')) }}";
  const apptNumber = "{{ $appointment->appointment_number }}";
	  const dateStr = "{{ $apptDateStr }}";
	  const timeStr = "{{ $apptTimeStr }}";
  const doctorName = "{{ trim('Dr. ' . ($appointment->doctor_first_name ?? '') . ' ' . ($appointment->doctor_last_name ?? '')) }}";

  const message = `📅 {{ __('Appointment Reminder') }}\n\n` +
                  `👤 {{ __('Patient') }}: ${patientName}\n` +
                  `📄 {{ __('Appointment #') }}: ${apptNumber}\n` +
                  `${doctorName ? '👨‍⚕️ {{ __('Doctor') }}: ' + doctorName + '\n' : ''}` +
                  `🗓 {{ __('Date') }}: ${dateStr}\n` +
                  `🕒 {{ __('Time') }}: ${timeStr}\n` +
                  `\n📱 {{ __('Generated by ConCure Clinic Management System') }}`;

  const encoded = encodeURIComponent(message);
  const patientWhatsApp = "{{ $appointment->patient_phone ? preg_replace('/[^0-9]/', '', $appointment->patient_phone) : '' }}";
  const url = patientWhatsApp ? `https://wa.me/${patientWhatsApp}?text=${encoded}` : `https://wa.me/?text=${encoded}`;
  window.open(url, '_blank');
}

function sendWhatsAppReminder() {
    const btn = document.getElementById('sendReminderBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Sending...") }}';

    fetch('{{ route("notifications.send-reminder") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            type: '{{ $appointment->type === "follow_up" ? "follow_up_reminder" : "appointment_reminder" }}',
            reference_id: {{ $appointment->id }}
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check me-2"></i>{{ __("Sent!") }}';
            btn.classList.replace('btn-outline-success', 'btn-success');
            setTimeout(() => { btn.innerHTML = originalHtml; btn.classList.replace('btn-success', 'btn-outline-success'); }, 3000);
        } else {
            btn.innerHTML = '<i class="fas fa-times me-2"></i>' + (data.message || '{{ __("Failed") }}');
            setTimeout(() => { btn.innerHTML = originalHtml; }, 3000);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        alert('{{ __("Network error. Please try again.") }}');
    });
}

</script>
@endsection
