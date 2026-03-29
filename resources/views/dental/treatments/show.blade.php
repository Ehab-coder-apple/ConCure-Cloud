@extends('layouts.app')

@section('title', __('Treatment Plan Details'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        {{ __('Treatment Plan') }} #{{ $dentalTreatment->treatment_number }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Created') }} {{ $dentalTreatment->created_at->format('M d, Y') }}
                        @if($dentalTreatment->creator)
                            {{ __('by') }} {{ $dentalTreatment->creator->name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ url('/dental/treatments') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to List') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/edit") }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit') }}
                        </a>
                        <div class="btn-group">
                            <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i>
                                {{ __('PDF') }}
                            </a>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}">
                                        <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf?template=custom") }}">
                                        <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Patient Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        {{ __('Patient Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Name') }}:</strong>
                                <a href="{{ route('patients.show', $dentalTreatment->patient) }}" class="text-decoration-none">
                                    {{ $dentalTreatment->patient->full_name }}
                                </a>
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Patient ID') }}:</strong> {{ $dentalTreatment->patient->patient_id }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Age') }}:</strong> {{ $dentalTreatment->patient->age }} {{ __('years') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Phone') }}:</strong> {{ $dentalTreatment->patient->phone ?? '-' }}
                            </p>
                            <p class="mb-2">
                                <strong>{{ __('Gender') }}:</strong> {{ ucfirst($dentalTreatment->patient->gender ?? '-') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tooth me-2"></i>
                        {{ __('Treatment Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Procedure') }}:</strong><br>
                                <span class="fs-5">{{ $dentalTreatment->procedure_name }}</span>
                                @if($dentalTreatment->procedure_code)
                                    <span class="badge bg-secondary">{{ $dentalTreatment->procedure_code }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>{{ __('Tooth Number(s)') }}:</strong><br>
                                @if($dentalTreatment->tooth_number)
                                    <span class="badge bg-primary fs-6">#{{ $dentalTreatment->tooth_number }}</span>
                                @endif
                                @if($dentalTreatment->tooth_numbers && count($dentalTreatment->tooth_numbers) > 0)
                                    @foreach($dentalTreatment->tooth_numbers as $tooth)
                                        <span class="badge bg-secondary">#{{ $tooth }}</span>
                                    @endforeach
                                @endif
                                @if(!$dentalTreatment->tooth_number && (!$dentalTreatment->tooth_numbers || count($dentalTreatment->tooth_numbers) == 0))
                                    <span class="text-muted">{{ __('Not specified') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($dentalTreatment->surfaces_affected && count($dentalTreatment->surfaces_affected) > 0)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Surfaces Affected') }}:</strong><br>
                                    @foreach($dentalTreatment->surfaces_affected as $surface)
                                        <span class="badge bg-info">{{ $surface }}</span>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->diagnosis)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Diagnosis') }}:</strong><br>
                                    {{ $dentalTreatment->diagnosis }}
                                    @if($dentalTreatment->icd10_code)
                                        <span class="badge bg-secondary">{{ $dentalTreatment->icd10_code }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->description)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2">
                                    <strong>{{ __('Description') }}:</strong><br>
                                    {{ $dentalTreatment->description }}
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($dentalTreatment->notes)
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-0">
                                    <strong>{{ __('Notes') }}:</strong><br>
                                    {{ $dentalTreatment->notes }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cost & Payment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>
                        {{ __('Cost & Payment') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>{{ __('Estimated Cost') }}:</strong><br>
                                <span class="fs-5 text-primary">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->estimated_cost ?? 0, 2) }}</span>
                            </p>
                        </div>
                        @if($dentalTreatment->actual_cost)
                            <div class="col-md-4">
                                <p class="mb-2">
                                    <strong>{{ __('Actual Cost') }}:</strong><br>
                                    <span class="fs-5">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->actual_cost, 2) }}</span>
                                </p>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>{{ __('Payment Status') }}:</strong><br>
                                <span class="badge bg-{{ $dentalTreatment->payment_status === 'paid' ? 'success' : ($dentalTreatment->payment_status === 'partial' ? 'warning' : 'danger') }} fs-6">
                                    {{ ucfirst($dentalTreatment->payment_status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($dentalTreatment->paid_amount > 0 || $dentalTreatment->remaining_balance > 0)
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>{{ __('Amount Paid') }}:</strong>
                                    {{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->paid_amount ?? 0, 2) }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0">
                                    <strong>{{ __('Remaining Balance') }}:</strong>
                                    <span class="text-danger">{{ $dentalTreatment->currency }} {{ number_format($dentalTreatment->remaining_balance ?? 0, 2) }}</span>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline & Dates -->
            @if($dentalTreatment->scheduled_date || $dentalTreatment->completed_at || $dentalTreatment->estimated_duration_minutes)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            {{ __('Timeline') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($dentalTreatment->scheduled_date)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Scheduled Date') }}:</strong><br>
                                        {{ $dentalTreatment->scheduled_date->format('M d, Y H:i') }}
                                    </p>
                                </div>
                            @endif
                            @if($dentalTreatment->completed_at)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Completed Date') }}:</strong><br>
                                        {{ $dentalTreatment->completed_at->format('M d, Y H:i') }}
                                    </p>
                                </div>
                            @endif
                            @if($dentalTreatment->estimated_duration_minutes)
                                <div class="col-md-4">
                                    <p class="mb-2">
                                        <strong>{{ __('Estimated Duration') }}:</strong><br>
                                        {{ $dentalTreatment->estimated_duration_minutes }} {{ __('minutes') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Canal Worksheet (Endodontic) -->
            @if($dentalTreatment->tooth_number || ($dentalTreatment->tooth_numbers && count($dentalTreatment->tooth_numbers) > 0))
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-teeth me-2"></i>
                        {{ __('Canal Worksheet (Endodontic)') }}
                    </h6>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <button type="button" class="btn btn-sm btn-primary" id="openCanalWorksheetBtn">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Open Worksheet') }}
                        </button>
                    @endif
                </div>
                <div class="card-body" id="canalTreatmentsList">
                    <div class="text-center py-3" id="canalLoading">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 text-muted">{{ __('Loading canal data...') }}</span>
                    </div>
                    <div id="canalDataContainer" style="display:none;"></div>
                    <p class="text-muted mb-0" id="noCanalData" style="display:none;">
                        <i class="fas fa-info-circle me-1"></i>
                        {{ __('No canal treatment data recorded yet. Click "Open Worksheet" to start documenting.') }}
                    </p>
                </div>
            </div>
            @endif

            <!-- Dental Lab Requests -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-flask me-2"></i>
                        {{ __('Dental Lab Requests') }}
                    </h6>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ route('dental.lab-requests.create', ['dental_treatment_id' => $dentalTreatment->id, 'patient_id' => $dentalTreatment->patient_id]) }}"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Send to Lab') }}
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($dentalTreatment->dentalLabRequests && $dentalTreatment->dentalLabRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Request #') }}</th>
                                        <th>{{ __('Work Type') }}</th>
                                        <th>{{ __('Lab') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dentalTreatment->dentalLabRequests as $labRequest)
                                        <tr>
                                            <td>
                                                <small>{{ $labRequest->request_number }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $labRequest->work_type_display }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $labRequest->externalLab->name ?? __('Not assigned') }}</small>
                                            </td>
                                            <td>
                                                <span class="{{ $labRequest->status_badge_class }}">
                                                    {{ $labRequest->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    @if($labRequest->due_date)
                                                        {{ $labRequest->due_date->format('M d, Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('dental.lab-requests.show', $labRequest) }}"
                                                   class="btn btn-xs btn-info" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('No lab requests for this treatment yet.') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Status Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>{{ __('Status') }}:</strong><br>
                        <span class="badge {{ $dentalTreatment->status_badge_class }} fs-6">
                            {{ ucfirst(str_replace('_', ' ', $dentalTreatment->status)) }}
                        </span>
                    </p>
                    <p class="mb-3">
                        <strong>{{ __('Priority') }}:</strong><br>
                        <span class="badge bg-{{ $dentalTreatment->priority === 'urgent' ? 'danger' : ($dentalTreatment->priority === 'high' ? 'warning' : ($dentalTreatment->priority === 'medium' ? 'info' : 'secondary')) }} fs-6">
                            {{ ucfirst($dentalTreatment->priority) }}
                        </span>
                    </p>
                    @if($dentalTreatment->severity)
                        <p class="mb-0">
                            <strong>{{ __('Severity') }}:</strong><br>
                            <span class="badge bg-secondary fs-6">{{ ucfirst($dentalTreatment->severity) }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <!-- Assigned Personnel -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>
                        {{ __('Assigned Personnel') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($dentalTreatment->assignedDoctor)
                        <p class="mb-2">
                            <strong>{{ __('Assigned Doctor') }}:</strong><br>
                            {{ $dentalTreatment->assignedDoctor->name }}
                        </p>
                    @endif
                    @if($dentalTreatment->performedBy)
                        <p class="mb-0">
                            <strong>{{ __('Performed By') }}:</strong><br>
                            {{ $dentalTreatment->performedBy->name }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Dental Chart Link -->
            @if($dentalTreatment->dentalChart)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            {{ __('Related Dental Chart') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>{{ __('Chart Type') }}:</strong> {{ ucfirst($dentalTreatment->dentalChart->chart_type) }}
                        </p>
                        <p class="mb-3">
                            <strong>{{ __('Created') }}:</strong> {{ $dentalTreatment->dentalChart->created_at->format('M d, Y') }}
                        </p>
                        <a href="{{ url("/dental/patients/{$dentalTreatment->patient_id}/charts/{$dentalTreatment->dental_chart_id}") }}"
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('View Dental Chart') }}
                        </a>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($dentalTreatment->status !== 'completed')
                            <form method="POST" action="{{ url("/dental/treatments/{$dentalTreatment->id}/complete") }}" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('{{ __('Mark this treatment as completed?') }}')">
                                    <i class="fas fa-check me-1"></i>
                                    {{ __('Mark as Completed') }}
                                </button>
                            </form>
                        @endif

                        <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/edit") }}" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Treatment') }}
                        </a>

                        <div class="btn-group w-100 mb-2">
                            <a href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}" class="btn btn-outline-primary" target="_blank" style="flex: 1;">
                                <i class="fas fa-file-pdf me-1"></i>
                                {{ __('Download PDF') }}
                            </a>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf") }}">
                                        <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url("/dental/treatments/{$dentalTreatment->id}/pdf?template=custom") }}">
                                        <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        @if(in_array(auth()->user()->role, ['admin', 'program_owner']))
                            <form method="POST" action="{{ url("/dental/treatments/{$dentalTreatment->id}") }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this treatment plan?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash me-1"></i>
                                    {{ __('Delete Treatment') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
<!-- Canal Worksheet Modal -->
<div class="modal fade" id="canalWorksheetModal" tabindex="-1" aria-labelledby="canalWorksheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="canalWorksheetModalLabel">
                    <i class="fas fa-teeth me-2"></i>{{ __('Canal Worksheet') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="canalWorksheetBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">{{ __('Loading worksheet...') }}</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="saveCanalWorksheetBtn">
                    <i class="fas fa-save me-1"></i>{{ __('Save All Canals') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const treatmentId = {{ $dentalTreatment->id }};
    const worksheetUrl = `{{ url('/dental/treatments') }}/${treatmentId}/canals`;
    let worksheetData = null;

    // Load canal summary on page load
    loadCanalSummary();

    function loadCanalSummary() {
        const loading = document.getElementById('canalLoading');
        const container = document.getElementById('canalDataContainer');
        const noData = document.getElementById('noCanalData');
        if (!loading) return;

        fetch(worksheetUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            worksheetData = data;
            if (data.existing_canals && Object.keys(data.existing_canals).length > 0) {
                container.style.display = 'block';
                let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0">';
                html += '<thead><tr><th>{{ __("Tooth") }}</th><th>{{ __("Canal") }}</th><th>{{ __("WL (mm)") }}</th><th>{{ __("MAF") }}</th><th>{{ __("Taper") }}</th><th>{{ __("Status") }}</th></tr></thead><tbody>';
                for (const [tooth, canals] of Object.entries(data.existing_canals)) {
                    for (const [name, canal] of Object.entries(canals)) {
                        html += `<tr>
                            <td><span class="badge bg-primary">#${tooth}</span></td>
                            <td><strong>${canal.canal_name}</strong></td>
                            <td>${canal.working_length ?? '-'}</td>
                            <td>${canal.master_apical_file ?? '-'}</td>
                            <td>${canal.taper ?? '-'}</td>
                            <td>${getStatusBadge(canal.status)}</td>
                        </tr>`;
                    }
                }
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                noData.style.display = 'block';
            }
        })
        .catch(() => { loading.style.display = 'none'; if(noData) noData.style.display = 'block'; });
    }

    function getStatusBadge(status) {
        const colors = { not_started: 'secondary', located: 'info', instrumented: 'warning', obturated: 'primary', completed: 'success' };
        const labels = { not_started: 'Not Started', located: 'Located', instrumented: 'Instrumented', obturated: 'Obturated', completed: 'Completed' };
        return `<span class="badge bg-${colors[status] || 'secondary'}">${labels[status] || status}</span>`;
    }

    // Open worksheet modal
    const openBtn = document.getElementById('openCanalWorksheetBtn');
    if (openBtn) {
        openBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('canalWorksheetModal'));
            modal.show();
            loadWorksheetForm();
        });
    }

    function loadWorksheetForm() {
        const body = document.getElementById('canalWorksheetBody');
        if (worksheetData) { renderWorksheetForm(body, worksheetData); return; }
        fetch(worksheetUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.json())
        .then(data => { worksheetData = data; renderWorksheetForm(body, data); })
        .catch(() => { body.innerHTML = '<div class="alert alert-danger">Failed to load worksheet data.</div>'; });
    }

    function buildSelect(name, options, selected, placeholder) {
        let html = `<select class="form-select form-select-sm" name="${name}"><option value="">${placeholder}</option>`;
        if (Array.isArray(options)) {
            options.forEach(o => { html += `<option value="${o}" ${selected === o ? 'selected' : ''}>${o}</option>`; });
        } else {
            for (const [k, v] of Object.entries(options)) { html += `<option value="${k}" ${selected === k ? 'selected' : ''}>${v}</option>`; }
        }
        return html + '</select>';
    }

    function buildCanalRow(toothNum, canalName, existing, options, editable) {
        const wl = existing.working_length ?? '';
        const maf = existing.master_apical_file ?? '';
        const cone = existing.master_cone_size ?? '';
        const taper = existing.taper ?? '';
        const irr = existing.irrigation_protocol ?? '';
        const obt = existing.obturation_technique ?? '';
        const seal = existing.sealer_type ?? '';
        const st = existing.status ?? 'not_started';
        const notes = existing.notes ?? '';

        return `<tr data-tooth="${toothNum}" data-canal="${canalName}">
            <td>${editable ? `<input type="text" class="form-control form-control-sm canal-name-input" value="${canalName}" placeholder="Canal name">` : `<strong>${canalName}</strong>`}</td>
            <td><input type="number" class="form-control form-control-sm" name="working_length" value="${wl}" step="0.5" min="0" max="50" placeholder="mm"></td>
            <td>${buildSelect('master_apical_file', options.maf_sizes, maf, 'MAF')}</td>
            <td>${buildSelect('master_cone_size', options.maf_sizes, cone, 'Cone')}</td>
            <td>${buildSelect('taper', options.tapers, taper, 'Taper')}</td>
            <td>${buildSelect('irrigation_protocol', options.irrigation_protocols, irr, 'Protocol')}</td>
            <td>${buildSelect('obturation_technique', options.obturation_techniques, obt, 'Technique')}</td>
            <td>${buildSelect('sealer_type', options.sealers, seal, 'Sealer')}</td>
            <td>${buildSelect('status', options.statuses, st, 'Status')}</td>
            <td><input type="text" class="form-control form-control-sm" name="notes" value="${notes}" placeholder="Notes"></td>
        </tr>`;
    }

    function renderWorksheetForm(body, data) {
        if (!data.tooth_numbers || data.tooth_numbers.length === 0) {
            body.innerHTML = '<div class="alert alert-warning">No teeth specified for this treatment.</div>';
            return;
        }
        let html = '';
        data.tooth_numbers.forEach(toothNum => {
            const stdCanals = data.standard_canals[toothNum] || [];
            const existing = data.existing_canals[toothNum] || {};
            html += `<div class="card mb-3"><div class="card-header bg-light"><h6 class="mb-0"><i class="fas fa-tooth me-2"></i>Tooth #${toothNum}</h6></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0 canal-table" data-tooth="${toothNum}"><thead><tr>
                <th>Canal</th><th>WL (mm)</th><th>MAF</th><th>Cone</th><th>Taper</th><th>Irrigation</th><th>Obturation</th><th>Sealer</th><th>Status</th><th>Notes</th>
            </tr></thead><tbody>`;
            let rendered = new Set();
            if (stdCanals.length > 0) {
                stdCanals.forEach(sc => {
                    html += buildCanalRow(toothNum, sc.canal_name, existing[sc.canal_name] || {}, data.options, false);
                    rendered.add(sc.canal_name);
                });
            }
            for (const [cn, cd] of Object.entries(existing)) {
                if (!rendered.has(cn)) html += buildCanalRow(toothNum, cn, cd, data.options, false);
            }
            if (stdCanals.length === 0 && Object.keys(existing).length === 0) {
                html += buildCanalRow(toothNum, '', {}, data.options, true);
            }
            html += `</tbody></table></div></div><div class="card-footer"><button type="button" class="btn btn-sm btn-outline-primary add-canal-row" data-tooth="${toothNum}"><i class="fas fa-plus me-1"></i>Add Canal</button></div></div>`;
        });
        body.innerHTML = html;
        body.querySelectorAll('.add-canal-row').forEach(btn => {
            btn.addEventListener('click', function() {
                const tooth = this.dataset.tooth;
                const tbody = body.querySelector(`table[data-tooth="${tooth}"] tbody`);
                const tr = document.createElement('tr');
                tr.innerHTML = buildCanalRow(tooth, '', {}, data.options, true).replace(/^<tr[^>]*>/, '').replace(/<\/tr>$/, '');
                tr.dataset.tooth = tooth;
                tr.dataset.canal = '';
                tbody.appendChild(tr);
            });
        });
    }

    // Save all canals
    document.getElementById('saveCanalWorksheetBtn')?.addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        const canals = [];
        document.querySelectorAll('#canalWorksheetBody .canal-table tbody tr').forEach(row => {
            const tooth = row.dataset.tooth;
            let canalName = row.dataset.canal;
            const nameInput = row.querySelector('.canal-name-input');
            if (nameInput) canalName = nameInput.value.trim();
            if (!canalName) return;

            canals.push({
                tooth_number: tooth,
                canal_name: canalName,
                working_length: row.querySelector('[name="working_length"]')?.value || null,
                master_apical_file: row.querySelector('[name="master_apical_file"]')?.value || null,
                master_cone_size: row.querySelector('[name="master_cone_size"]')?.value || null,
                taper: row.querySelector('[name="taper"]')?.value || null,
                irrigation_protocol: row.querySelector('[name="irrigation_protocol"]')?.value || null,
                obturation_technique: row.querySelector('[name="obturation_technique"]')?.value || null,
                sealer_type: row.querySelector('[name="sealer_type"]')?.value || null,
                status: row.querySelector('[name="status"]')?.value || 'not_started',
                notes: row.querySelector('[name="notes"]')?.value || null,
            });
        });

        if (canals.length === 0) {
            alert('No canal data to save. Please fill in at least one canal.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Save All Canals';
            return;
        }

        fetch(worksheetUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ canals })
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Save All Canals';
            if (data.success) {
                worksheetData = null;
                loadCanalSummary();
                bootstrap.Modal.getInstance(document.getElementById('canalWorksheetModal'))?.hide();
                alert('Canal treatments saved successfully!');
            } else {
                alert(data.message || 'Failed to save.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Save All Canals';
            alert('An error occurred while saving.');
        });
    });
});
</script>
@endpush