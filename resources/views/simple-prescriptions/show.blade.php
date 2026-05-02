@extends('layouts.app')

@section('title', __('Prescription Details'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-prescription me-2"></i>
                            {{ __('Prescription Details') }}
                        </h5>
                        <div class="btn-group" role="group">
                            <div class="btn-group">
                                <a href="{{ route('simple-prescriptions.print', $prescription->id) }}"
                                   class="btn btn-success btn-sm" target="_blank" title="{{ __('Print') }}">
                                    <i class="fas fa-print"></i>
                                </a>
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Print</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.print', $prescription->id) }}" target="_blank">
                                            <i class="fas fa-print me-2"></i>{{ __('Browser Print') }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.thermal', [$prescription->id, 'width' => 80]) }}" target="_blank">
                                            <i class="fas fa-receipt me-2"></i>{{ __('Thermal 80mm') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.thermal', [$prescription->id, 'width' => 58]) }}" target="_blank">
                                            <i class="fas fa-receipt me-2"></i>{{ __('Thermal 58mm') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('simple-prescriptions.pdf', $prescription->id) }}"
                                   class="btn btn-danger btn-sm" title="{{ __('Default PDF') }}">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.pdf', $prescription->id) }}">
                                            <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.pdf', [$prescription->id, 'template' => 'custom']) }}">
                                            <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-success btn-sm" title="{{ __('Send via WhatsApp') }}" onclick="shareSimplePrescriptionWhatsApp()">
                                <i class="fab fa-whatsapp"></i>
                            </button>

                            <a href="{{ route('messages.index') }}?prefill_transfer={{ urlencode(base64_encode(json_encode([
                                 'transfer_type' => 'prescription',
                                 'patient_id' => $prescription->patient->id ?? 0,
                                 'source_type' => 'prescription',
                                 'source_id' => $prescription->id ?? 0,
                                 'metadata' => [
                                   'patient_name' => ($prescription->patient->first_name ?? 'Demo').' '.($prescription->patient->last_name ?? 'Patient'),
                                   'prescription_number' => $prescription->prescription_number ?? '',
                                   'simple' => true
                                 ]
                               ]))) }}"
                               class="btn btn-warning btn-sm" title="{{ __('Share Internally') }}"
                               onclick="try{var v=JSON.stringify({
                                 transfer_type:'prescription',
                                 patient_id: {{ $prescription->patient->id ?? 0 }},
                                 source_type:'prescription',
                                 source_id: {{ $prescription->id ?? 0 }},
                                 metadata:{ patient_name:@json(($prescription->patient->first_name ?? 'Demo').' '.($prescription->patient->last_name ?? 'Patient')), prescription_number:@json($prescription->prescription_number ?? ''), simple:true }
                               }); localStorage.setItem('prefill_transfer', v); sessionStorage.setItem('prefill_transfer', v);}catch(e){}">
                                <i class="fas fa-share-nodes"></i>
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isClinicAdmin() || $prescription->doctor_id === auth()->id())
                            <a href="{{ route('simple-prescriptions.edit', $prescription->id) }}"
                               class="btn btn-light btn-sm" title="{{ __('Edit Prescription') }}">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endif

                            @if((auth()->user()->role === 'pharmacist' || auth()->user()->isSuperAdmin() || auth()->user()->isClinicAdmin()) && !$prescription->is_dispensed && $prescription->status === 'active')
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#dispenseModal" title="{{ __('Dispense & Create Sale') }}">
                                <i class="fas fa-pills me-1"></i>{{ __('Dispense') }}
                            </button>
                            @endif

                            <a href="{{ route('simple-prescriptions.index') }}"
                               class="btn btn-outline-light btn-sm" title="{{ __('Back to Prescriptions') }}">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Prescription Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4 class="text-primary">{{ $prescription->prescription_number }}</h4>
                            <p class="text-muted mb-0">{{ __('Created on') }} {{ $prescription->created_at->format('F d, Y') }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge bg-{{ $prescription->status === 'active' ? 'success' : ($prescription->status === 'completed' ? 'primary' : 'secondary') }} fs-6">
                                {{ ucfirst($prescription->status) }}
                            </span>
                            @if($prescription->is_dispensed)
                            <br><br>
                            <div class="alert alert-info mb-0 d-inline-block">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>{{ __('Dispensed') }}</strong><br>
                                <small>
                                    {{ __('on') }} {{ $prescription->dispensed_at->format('M d, Y \a\t H:i') }}<br>
                                    {{ __('by') }} {{ $prescription->dispenser->first_name ?? 'Unknown' }} {{ $prescription->dispenser->last_name ?? '' }}<br>
                                    {{ __('Ref') }}: {{ $prescription->dispense_reference }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-injured text-primary me-2"></i>
                                {{ __('Patient Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Name') }}:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}<br>
                                    <strong>{{ __('Patient ID') }}:</strong> {{ $prescription->patient->patient_id }}<br>
                                    <strong>{{ __('Gender') }}:</strong> {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}<br>
                                    <strong>{{ __('Age') }}:</strong> {{ $prescription->patient->age_formatted ?? 'N/A' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ __('Phone') }}:</strong> {{ $prescription->patient->phone ?? 'Not provided' }}<br>
                                    <strong>{{ __('Email') }}:</strong> {{ $prescription->patient->email ?? 'Not provided' }}<br>
                                    <strong>{{ __('Weight') }}:</strong> {{ $prescription->patient->latest_weight_kg ?? 'N/A' }} kg<br>
                                    <strong>{{ __('Height') }}:</strong> {{ $prescription->patient->latest_height ?? 'N/A' }} cm
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-md text-primary me-2"></i>
                                {{ __('Doctor Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Doctor') }}:</strong> Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}<br>
                                    <strong>{{ __('Phone') }}:</strong> {{ $prescription->doctor->phone ?? 'Not provided' }}
                                </div>
                                <div class="col-md-6">
                                    <strong>{{ __('Email') }}:</strong> {{ $prescription->doctor->email ?? 'Not provided' }}<br>
                                    <strong>{{ __('Prescribed Date') }}:</strong> {{ $prescription->prescribed_date->format('F d, Y') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnosis -->
                    @if($prescription->diagnosis)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-stethoscope text-primary me-2"></i>
                                    {{ __('Diagnosis') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $prescription->diagnosis }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Medicines -->
                    @if($prescription->medicines->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-pills text-primary me-2"></i>
                                    {{ __('Prescribed Medicines') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                @foreach($prescription->medicines as $index => $medicine)
                                    <div class="medicine-item mb-4 p-3 border rounded {{ $loop->last ? 'mb-0' : '' }}" style="background-color: #f8f9fa;">
                                        <div class="medicine-header mb-3">
                                            <h6 class="text-primary mb-0">
                                                <i class="fas fa-capsules me-2"></i>
                                                {{ $index + 1 }}. {{ $medicine->medicine_name }}
                                            </h6>
                                        </div>
                                        <div class="row" style="display: flex; gap: 15px;">
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Dosage') }}</small>
                                                    <strong class="text-dark">{{ $medicine->dosage ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Frequency') }}</small>
                                                    <strong class="text-dark">{{ $medicine->frequency ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="flex: 1;">
                                                <div class="medicine-detail">
                                                    <small class="text-muted d-block">{{ __('Duration') }}</small>
                                                    <strong class="text-dark">{{ $medicine->duration ?? __('Not specified') }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @if($medicine->instructions)
                                            <div class="mt-3 pt-3 border-top">
                                                <small class="text-muted d-block mb-1">{{ __('Instructions') }}</small>
                                                <div class="text-dark">
                                                    <i class="fas fa-info-circle text-info me-1"></i>
                                                    {{ $medicine->instructions }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($prescription->notes)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-sticky-note text-primary me-2"></i>
                                    {{ __('Notes') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $prescription->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <a href="{{ route('simple-prescriptions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>
                            {{ __('Back to Prescriptions') }}
                        </a>
                        <div class="d-flex flex-wrap gap-1">
                            <div class="btn-group">
                                <a href="{{ route('simple-prescriptions.print', $prescription->id) }}"
                                   class="btn btn-success btn-sm" target="_blank">
                                    <i class="fas fa-print me-1"></i>
                                    {{ __('Print') }}
                                </a>
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Print</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.print', $prescription->id) }}" target="_blank">
                                            <i class="fas fa-print me-2"></i>{{ __('Browser Print') }}
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.thermal', [$prescription->id, 'width' => 80]) }}" target="_blank">
                                            <i class="fas fa-receipt me-2"></i>{{ __('Thermal 80mm') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.thermal', [$prescription->id, 'width' => 58]) }}" target="_blank">
                                            <i class="fas fa-receipt me-2"></i>{{ __('Thermal 58mm') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('simple-prescriptions.pdf', $prescription->id) }}"
                                   class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    {{ __('PDF') }}
                                </a>
                                <button type="button" class="btn btn-danger btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.pdf', $prescription->id) }}">
                                            <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('simple-prescriptions.pdf', [$prescription->id, 'template' => 'custom']) }}">
                                            <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <a href="{{ route('simple-prescriptions.edit', $prescription->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                {{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
/* Ensure buttons stay horizontal and compact */
.card-header .btn-group {
    white-space: nowrap;
}

.card-header .btn-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    min-width: 36px;
    border-radius: 0;
}

.card-header .btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.card-header .btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.card-header .btn i {
    font-size: 0.875rem;
}

/* Responsive adjustments - keep horizontal on mobile */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .card-header .btn-group {
        display: flex;
        justify-content: center;
    }

    .card-header .btn-group .btn {
        padding: 0.375rem 0.75rem;
        min-width: 40px;
    }
}

/* Extra small screens - make buttons slightly larger for touch */
@media (max-width: 576px) {
    .card-header .btn-group .btn {
        padding: 0.5rem 0.75rem;
        font-size: 1rem;
        min-width: 44px;
    }

    .card-header .btn i {
        font-size: 1rem;
    }
}
</style>

<!-- Dispense Modal -->
@if((auth()->user()->role === 'pharmacist' || auth()->user()->isSuperAdmin() || auth()->user()->isClinicAdmin()) && !$prescription->is_dispensed && $prescription->status === 'active')
<div class="modal fade" id="dispenseModal" tabindex="-1" aria-labelledby="dispenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="dispenseModalLabel">
                    <i class="fas fa-pills me-2"></i>{{ __('Dispense Prescription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('simple-prescriptions.dispense', $prescription) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('This will:') }}</strong>
                        <ul class="mb-0 mt-2">
                            <li>{{ __('Create sale transactions for all medicines') }}</li>
                            <li>{{ __('Automatically reduce inventory stock') }}</li>
                            <li>{{ __('Mark prescription as completed') }}</li>
                            <li>{{ __('Generate sales receipt') }}</li>
                        </ul>
                    </div>

                    @if($prescription->medicines && $prescription->medicines->count() > 0)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>{{ __('Medicines to Dispense') }}</strong>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($prescription->medicines as $med)
                                <li class="mb-2">
                                    <i class="fas fa-pills text-primary me-2"></i>
                                    <strong>{{ $med->medicine_name }}</strong>
                                    @if($med->quantity)
                                        <span class="badge bg-secondary">{{ $med->quantity }} {{ __('units') }}</span>
                                    @endif
                                    @if($med->dosage)
                                        <br><small class="text-muted ms-4">{{ $med->dosage }}</small>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                            <option value="">{{ __('Select payment method') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                            <option value="credit">{{ __('Credit') }}</option>
                            <option value="insurance">{{ __('Insurance') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="print_after_dispense" name="print_after_dispense" value="1" checked>
                        <label class="form-check-label" for="print_after_dispense">
                            <i class="fas fa-print me-1"></i>{{ __('Print thermal receipt after dispensing') }}
                        </label>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ __('Important') }}:</strong> {{ __('This action cannot be undone. Make sure all medicines are available in inventory before proceeding.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check me-1"></i>{{ __('Dispense & Create Sale') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
@if(session('auto_print_url'))
(function () {
    try {
        var url = @json(session('auto_print_url'));
        if (url) { window.open(url, '_blank', 'noopener'); }
    } catch (e) { /* popup blocker; ignore */ }
})();
@endif

function shareSimplePrescriptionWhatsApp() {
  const patientName = "{{ ($prescription->patient->first_name ?? '') . ' ' . ($prescription->patient->last_name ?? '') }}".trim();
  const patientId = "{{ $prescription->patient->patient_id ?? '' }}";
  const rxNumber = "{{ $prescription->prescription_number ?? '' }}";
  const doctorName = "{{ ($prescription->doctor->first_name ?? '') . ' ' . ($prescription->doctor->last_name ?? '') }}".trim();

  let meds = "";
  @if($prescription->medicines && $prescription->medicines->count() > 0)
    meds += "\n\n💊 {{ __('Medications') }}:\n";
    @foreach($prescription->medicines as $m)
      meds += "• {{ addslashes($m->medicine_name) }}" +
              ("{{ $m->dosage ? ' — ' . addslashes($m->dosage) : '' }}") +
              ("{{ $m->frequency ? ' · ' . addslashes($m->frequency) : '' }}") +
              ("{{ $m->duration ? ' · ' . addslashes($m->duration) : '' }}") +
              ("{{ $m->instructions ? ' · ' . addslashes($m->instructions) : '' }}") + "\n";
    @endforeach
  @endif

  const message = `🧾 {{ __('Prescription') }}\n\n` +
                  `👤 {{ __('Patient') }}: ${patientName} ${patientId ? '('+patientId+')' : ''}\n` +
                  `${rxNumber ? '📄 {{ __('Prescription #') }}: ' + rxNumber + '\n' : ''}` +
                  `${doctorName ? '👨‍⚕️ {{ __('Doctor') }}: ' + doctorName + '\n' : ''}` +
                  meds +
                  `\n📱 {{ __('Generated by ConCure Clinic Management System') }}`;

  const encoded = encodeURIComponent(message);
  const patientWhatsApp = "{{ ($prescription->patient && $prescription->patient->whatsapp_phone) ? preg_replace('/[^0-9]/','', $prescription->patient->whatsapp_phone) : (($prescription->patient && $prescription->patient->phone) ? preg_replace('/[^0-9]/','', $prescription->patient->phone) : '') }}";
  const url = patientWhatsApp ? `https://wa.me/${patientWhatsApp}?text=${encoded}` : `https://wa.me/?text=${encoded}`;
  window.open(url, '_blank');
}
</script>
@endpush

@endsection
