@extends('layouts.app')

@section('title', __('Prescription History'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-history me-2 text-primary"></i>
                        {{ __('Pediatric Prescription History') }}
                    </h1>
                </div>
                <a href="{{ route('pediatric.medication.calculator') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-calculator me-1"></i> {{ __('Calculator') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Patient Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pediatric.medication.history') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('Select Patient') }}</label>
                    <select name="patient_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- {{ __('Select Patient') }} --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ ($selectedPatient && $selectedPatient->id == $p->id) ? 'selected' : '' }}>
                            {{ $p->first_name }} {{ $p->last_name }} ({{ $p->patient_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($selectedPatient && $prescriptions->count() > 0)
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('Prescriptions') }}</span>
            <div class="d-flex gap-2">
                <a href="{{ route('pediatric.medication.print', ['patient_id' => $selectedPatient->id]) }}" class="btn btn-outline-success btn-sm" title="Print today's prescriptions">
                    <i class="fas fa-print me-1"></i> {{ __("Print Today's Rx") }}
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" id="printSelectedBtn" disabled onclick="printSelected()">
                    <i class="fas fa-print me-1"></i> {{ __('Print Selected') }}
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAllHist" title="Select all"></th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Drug') }}</th>
                            <th>{{ __('Form') }}</th>
                            <th>{{ __('Dose') }}</th>
                            <th>{{ __('Frequency') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Weight') }}</th>
                            <th>{{ __('Safety') }}</th>
                            <th>{{ __('By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prescriptions as $rx)
                        <tr>
                            <td><input type="checkbox" class="rx-select" value="{{ $rx->id }}"></td>
                            <td>{{ $rx->created_at->format('d M Y') }}</td>
                            <td><strong>{{ $rx->drug->generic_name }}</strong></td>
                            <td>{{ $rx->form->display_label }}</td>
                            <td>
                                {{ $rx->dose_mg }} mg
                                @if($rx->dose_ml)
                                <span class="text-muted">({{ $rx->dose_ml }} ml)</span>
                                @endif
                            </td>
                            <td>{{ $rx->frequency_per_day }}x/day</td>
                            <td>{{ $rx->duration_days ? $rx->duration_days . ' days' : '--' }}</td>
                            <td>{{ $rx->patient_weight_kg }} kg</td>
                            <td>
                                <span class="badge" style="background: {{ $rx->safety_color }}; color: white;">
                                    {{ $rx->safety_icon ?? '' }} {{ strtoupper($rx->safety_status) }}
                                </span>
                                @if($rx->override_reason)
                                <br><small class="text-danger" title="{{ $rx->override_reason }}">Override: {{ Str::limit($rx->override_reason, 30) }}</small>
                                @endif
                            </td>
                            <td>{{ $rx->creator->name ?? '--' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @elseif($selectedPatient)
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        {{ __('No pediatric prescriptions found for this patient.') }}
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllHist');
    const printBtn = document.getElementById('printSelectedBtn');
    if (!selectAll) return;

    function updatePrintBtn() {
        const checked = document.querySelectorAll('.rx-select:checked');
        if (printBtn) printBtn.disabled = checked.length === 0;
    }

    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.rx-select').forEach(cb => cb.checked = this.checked);
        updatePrintBtn();
    });

    document.querySelectorAll('.rx-select').forEach(cb => {
        cb.addEventListener('change', updatePrintBtn);
    });
});

function printSelected() {
    const ids = Array.from(document.querySelectorAll('.rx-select:checked')).map(cb => cb.value);
    if (ids.length === 0) return alert('Please select at least one prescription.');
    window.open(`{{ route('pediatric.medication.print') }}?ids=${ids.join(',')}`, '_blank');
}
</script>
@endpush
@endsection

