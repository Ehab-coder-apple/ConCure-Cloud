@extends('layouts.app')

@section('title', __('Prescriptions'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-prescription-bottle-alt text-primary me-2"></i>
                    {{ __('Prescriptions') }}
                </h1>
                <div>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-pills me-1"></i>
                        {{ __('Medicine Inventory') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPrescriptionModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('New Prescription') }}
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('prescriptions.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('Patient name, prescription ID...') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ __('Date From') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
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

            <!-- Prescriptions List -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Prescriptions List') }}
                        <span class="badge bg-primary ms-2">{{ $prescriptions->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($prescriptions) && $prescriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Prescription ID') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Doctor') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Medications') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescriptions as $prescription)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $prescription->prescription_number ?? 'RX-' . date('Y') . '-' . str_pad($prescription->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($prescription->patient->first_name ?? 'P', 0, 1) . substr($prescription->patient->last_name ?? 'A', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ ($prescription->patient->first_name ?? 'Demo') . ' ' . ($prescription->patient->last_name ?? 'Patient') }}</div>
                                                    <small class="text-muted">{{ $prescription->patient->patient_id ?? 'P000001' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ ($prescription->doctor->first_name ?? 'Dr.') . ' ' . ($prescription->doctor->last_name ?? 'Smith') }}</td>
                                        <td>{{ $prescription->created_at ? \Carbon\Carbon::parse($prescription->created_at)->format('M d, Y') : now()->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $prescription->medicines_count ?? 3 }} {{ __('medications') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $prescription->status == 'active' ? 'success' : ($prescription->status == 'completed' ? 'primary' : 'secondary') }}">
                                                {{ ucfirst($prescription->status ?? 'Active') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('prescriptions.show', $prescription->id) }}" class="btn btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-success" title="{{ __('Print PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                                <a href="{{ route('prescriptions.edit', $prescription->id) }}" class="btn btn-outline-secondary" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(isset($prescriptions) && method_exists($prescriptions, 'links'))
                            <div class="card-footer">
                                {{ $prescriptions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-prescription-bottle-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Prescriptions Found') }}</h5>
                            <p class="text-muted">{{ __('Start by creating your first prescription.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPrescriptionModal">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Create First Prescription') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Prescription Modal -->
<div class="modal fade" id="newPrescriptionModal" tabindex="-1" aria-labelledby="newPrescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newPrescriptionModalLabel">
                    <i class="fas fa-prescription-bottle-alt me-2"></i>
                    {{ __('New Prescription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('prescriptions.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">{{ __('Select Patient') }}</option>
                                <option value="1">Demo Patient (P000001)</option>
                                <option value="2">John Smith (P000002)</option>
                                <option value="3">Sarah Ahmed (P000003)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="diagnosis" class="form-label">{{ __('Diagnosis') }}</label>
                            <input type="text" class="form-control" id="diagnosis" name="diagnosis" placeholder="{{ __('Primary diagnosis...') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="prescription_date" class="form-label">{{ __('Prescription Date') }}</label>
                            <input type="date" class="form-control" id="prescription_date" name="prescription_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="{{ __('Additional instructions or notes...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        {{ __('Create Prescription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
