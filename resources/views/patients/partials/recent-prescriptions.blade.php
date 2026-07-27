@php
    $allPrescriptions = collect();

    if ($patient->prescriptions) {
        $allPrescriptions = $allPrescriptions->merge($patient->prescriptions);
    }

    if ($patient->simplePrescriptions) {
        $allPrescriptions = $allPrescriptions->merge($patient->simplePrescriptions);
    }

    $allPrescriptions = $allPrescriptions
        ->sortByDesc(fn ($prescription) => optional($prescription->prescribed_date ?? $prescription->created_at)->timestamp ?? 0)
        ->take(5)
        ->values();
@endphp

@if($allPrescriptions->count() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div id="prescriptionHistoryStatus" class="text-muted prescription-history-status">
            @if($allPrescriptions->count() > 1)
                {{ __('Showing the most recent prescription. Expand history to view older prescriptions.') }}
            @else
                {{ __('Only one prescription is available in this history.') }}
            @endif
        </div>
        <button
            type="button"
            id="prescriptionHistoryToggle"
            class="btn btn-sm btn-outline-primary {{ $allPrescriptions->count() > 1 ? '' : 'd-none' }}"
            aria-expanded="false"
        >
            <i class="fas fa-angle-down me-1"></i>
            <span>{{ __('Show Full History') }}</span>
        </button>
    </div>

    <div id="patientPrescriptionHistory" data-total="{{ $allPrescriptions->count() }}">
        <div class="list-group list-group-flush">
            @foreach($allPrescriptions as $prescription)
                @php $isHistoryItem = !$loop->first; @endphp
                <div class="list-group-item border-0 px-0 prescription-history-record {{ $isHistoryItem ? 'd-none' : '' }}" data-prescription-history-item="{{ $isHistoryItem ? 'true' : 'false' }}">
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
                                @if($prescription instanceof \App\Models\SimplePrescription)
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
    </div>
@else
    <div class="text-center py-4">
        <i class="fas fa-prescription-bottle fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">{{ __('No prescriptions recorded yet.') }}</p>
    </div>
@endif