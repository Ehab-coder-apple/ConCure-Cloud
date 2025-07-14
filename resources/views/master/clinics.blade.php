@extends('layouts.master')

@section('title', __('Clinic Management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-hospital text-primary me-2"></i>
                        {{ __('Clinic Management') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Clinics') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add New Clinic') }}
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('master.clinics') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Clinics') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('Name, email, activation code...') }}">
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
                            <label for="subscription" class="form-label">{{ __('Subscription') }}</label>
                            <select class="form-select" id="subscription" name="subscription">
                                <option value="">{{ __('All Subscriptions') }}</option>
                                <option value="trial" {{ request('subscription') == 'trial' ? 'selected' : '' }}>{{ __('Active Trials') }}</option>
                                <option value="expired_trial" {{ request('subscription') == 'expired_trial' ? 'selected' : '' }}>{{ __('Expired Trials') }}</option>
                                <option value="active" {{ request('subscription') == 'active' ? 'selected' : '' }}>{{ __('Active Subscription') }}</option>
                                <option value="expired" {{ request('subscription') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                                <option value="expiring" {{ request('subscription') == 'expiring' ? 'selected' : '' }}>{{ __('Expiring Soon') }}</option>
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

            <!-- Clinics Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Registered Clinics') }}
                        <span class="badge bg-primary ms-2">{{ $clinics->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($clinics) && $clinics->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Clinic') }}</th>
                                        <th>{{ __('Administrator') }}</th>
                                        <th>{{ __('Usage') }}</th>
                                        <th>{{ __('Trial/Subscription') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clinics as $clinic)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $clinic->name }}</div>
                                                <small class="text-muted">{{ $clinic->email }}</small>
                                                <br>
                                                <small class="font-monospace text-info">{{ $clinic->activation_code }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($clinic->admin_first_name)
                                                <div>
                                                    <div class="fw-bold">{{ $clinic->admin_first_name }} {{ $clinic->admin_last_name }}</div>
                                                    <small class="text-muted">{{ $clinic->admin_email }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('Not activated') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><i class="fas fa-users text-info me-1"></i> {{ $clinic->user_count ?? 0 }}/{{ $clinic->max_users }} {{ __('users') }}</div>
                                                <div><i class="fas fa-user-injured text-success me-1"></i> {{ $clinic->patient_count ?? 0 }} {{ __('patients') }}</div>
                                                @if(isset($clinic->prescription_count))
                                                <div><i class="fas fa-prescription-bottle-alt text-primary me-1"></i> {{ $clinic->prescription_count ?? 0 }} {{ __('prescriptions') }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($clinic->is_trial)
                                                @php
                                                    $trialExpiry = \Carbon\Carbon::parse($clinic->trial_expires_at);
                                                    $isTrialExpired = $trialExpiry->isPast();
                                                    $trialDaysLeft = $isTrialExpired ? 0 : $trialExpiry->diffInDays();
                                                @endphp
                                                <div>
                                                    <span class="badge bg-{{ $isTrialExpired ? 'danger' : ($trialDaysLeft <= 2 ? 'warning' : 'info') }}">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ __('Trial') }}
                                                    </span>
                                                    <br>
                                                    <small class="text-{{ $isTrialExpired ? 'danger' : ($trialDaysLeft <= 2 ? 'warning' : 'muted') }}">
                                                        @if($isTrialExpired)
                                                            {{ __('Expired') }} {{ $trialExpiry->diffForHumans() }}
                                                        @else
                                                            {{ __('Expires in') }} {{ $trialDaysLeft }} {{ __('days') }}
                                                        @endif
                                                    </small>
                                                </div>
                                            @elseif($clinic->subscription_expires_at)
                                                @php
                                                    $expiryDate = \Carbon\Carbon::parse($clinic->subscription_expires_at);
                                                    $isExpired = $expiryDate->isPast();
                                                    $isExpiringSoon = $expiryDate->diffInDays() <= 30 && !$isExpired;
                                                @endphp
                                                <div>
                                                    <span class="badge bg-{{ $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'success') }}">
                                                        <i class="fas fa-crown me-1"></i>
                                                        {{ __('Paid') }}
                                                    </span>
                                                    <br>
                                                    <small class="text-{{ $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'muted') }}">
                                                        {{ $expiryDate->format('M d, Y') }}
                                                        @if($isExpired)
                                                            ({{ __('Expired') }})
                                                        @elseif($isExpiringSoon)
                                                            ({{ __('Expires in') }} {{ $expiryDate->diffInDays() }} {{ __('days') }})
                                                        @endif
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('No subscription') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $clinic->is_active ? 'success' : 'secondary' }}">
                                                {{ $clinic->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-{{ $clinic->is_active ? 'warning' : 'success' }}"
                                                        onclick="toggleClinicStatus({{ $clinic->id }}, {{ $clinic->is_active ? 'false' : 'true' }})"
                                                        title="{{ $clinic->is_active ? __('Deactivate') : __('Activate') }}">
                                                    <i class="fas fa-{{ $clinic->is_active ? 'pause' : 'play' }}"></i>
                                                </button>

                                                @if($clinic->is_trial)
                                                    <button type="button" class="btn btn-outline-warning"
                                                            onclick="extendTrial({{ $clinic->id }})"
                                                            title="{{ __('Extend Trial') }}">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success"
                                                            onclick="convertTrial({{ $clinic->id }})"
                                                            title="{{ __('Convert to Paid') }}">
                                                        <i class="fas fa-crown"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="extendSubscription({{ $clinic->id }})"
                                                            title="{{ __('Extend Subscription') }}">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </button>
                                                @endif

                                                <button type="button" class="btn btn-outline-info"
                                                        onclick="viewClinicDetails({{ $clinic->id }})"
                                                        title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="deleteClinic({{ $clinic->id }})"
                                                        title="{{ __('Delete Clinic') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(method_exists($clinics, 'links'))
                            <div class="card-footer">
                                {{ $clinics->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hospital fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Clinics Found') }}</h5>
                            <p class="text-muted">{{ __('Start by generating an activation code for your first clinic.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Add First Clinic') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Code Modal -->
<div class="modal fade" id="generateCodeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>
                    {{ __('Generate Clinic Activation Code') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateCodeForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="clinic_name" class="form-label">{{ __('Clinic Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="clinic_name" name="clinic_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">{{ __('Admin Email') }} <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_first_name" class="form-label">{{ __('Admin First Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="admin_first_name" name="admin_first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_last_name" class="form-label">{{ __('Admin Last Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="admin_last_name" name="admin_last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="max_users" class="form-label">{{ __('Maximum Users') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_users" name="max_users" min="1" max="1000" value="10" required>
                        </div>
                        <div class="col-md-6">
                            <label for="subscription_months" class="form-label">{{ __('Subscription (Months)') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="subscription_months" name="subscription_months" required>
                                <option value="1">1 {{ __('Month') }}</option>
                                <option value="3">3 {{ __('Months') }}</option>
                                <option value="6">6 {{ __('Months') }}</option>
                                <option value="12" selected>12 {{ __('Months') }}</option>
                                <option value="24">24 {{ __('Months') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-1"></i>
                        {{ __('Generate Code') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Subscription Modal -->
<div class="modal fade" id="extendSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>
                    {{ __('Extend Subscription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendSubscriptionForm">
                @csrf
                <input type="hidden" id="extend_clinic_id" name="clinic_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="extend_months" class="form-label">{{ __('Extend by (Months)') }} <span class="text-danger">*</span></label>
                        <select class="form-select" id="extend_months" name="months" required>
                            <option value="1">1 {{ __('Month') }}</option>
                            <option value="3">3 {{ __('Months') }}</option>
                            <option value="6" selected>6 {{ __('Months') }}</option>
                            <option value="12">12 {{ __('Months') }}</option>
                            <option value="24">24 {{ __('Months') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i>
                        {{ __('Extend Subscription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Trial Modal -->
<div class="modal fade" id="extendTrialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock me-2"></i>
                    {{ __('Extend Trial Period') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendTrialForm">
                <div class="modal-body">
                    <input type="hidden" id="extend_trial_clinic_id">
                    <div class="mb-3">
                        <label for="extend_trial_days" class="form-label">{{ __('Extend trial by (days)') }}</label>
                        <select class="form-select" id="extend_trial_days" required>
                            <option value="3">3 {{ __('days') }}</option>
                            <option value="7" selected>7 {{ __('days') }}</option>
                            <option value="14">14 {{ __('days') }}</option>
                            <option value="30">30 {{ __('days') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('This will extend the trial period from the current expiration date.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-clock me-1"></i>
                        {{ __('Extend Trial') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Convert Trial Modal -->
<div class="modal fade" id="convertTrialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-crown me-2"></i>
                    {{ __('Convert Trial to Paid Subscription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="convertTrialForm">
                <div class="modal-body">
                    <input type="hidden" id="convert_trial_clinic_id">
                    <div class="mb-3">
                        <label for="convert_trial_plan_type" class="form-label">{{ __('Plan Type') }}</label>
                        <select class="form-select" id="convert_trial_plan_type" required>
                            <option value="basic">{{ __('Basic Plan') }} - $29/month</option>
                            <option value="professional" selected>{{ __('Professional Plan') }} - $59/month</option>
                            <option value="enterprise">{{ __('Enterprise Plan') }} - $99/month</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="convert_trial_months" class="form-label">{{ __('Subscription Duration') }}</label>
                        <select class="form-select" id="convert_trial_months" required>
                            <option value="1">1 {{ __('month') }}</option>
                            <option value="3">3 {{ __('months') }}</option>
                            <option value="6">6 {{ __('months') }}</option>
                            <option value="12" selected>12 {{ __('months') }}</option>
                            <option value="24">24 {{ __('months') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ __('This will convert the trial to a paid subscription and remove trial limitations.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-crown me-1"></i>
                        {{ __('Convert to Paid') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleClinicStatus(clinicId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this clinic?`)) {
        fetch(`{{ route('master.clinic.toggle-status', '') }}/${clinicId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred. Please try again.") }}');
        });
    }
}

function extendSubscription(clinicId) {
    document.getElementById('extend_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('extendSubscriptionModal')).show();
}

function viewClinicDetails(clinicId) {
    // Navigate to clinic details page
    window.location.href = `/master/clinics/${clinicId}`;
}

function deleteClinic(clinicId) {
    if (confirm('{{ __("Are you sure you want to delete this clinic?") }}\n\n{{ __("Warning: This action cannot be undone. The clinic and all its associated data will be permanently removed.") }}\n\n{{ __("Note: Clinics with existing users, patients, or medical records cannot be deleted.") }}')) {

        // Show loading state
        const deleteBtn = document.querySelector(`button[onclick="deleteClinic(${clinicId})"]`);
        const originalContent = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;

        fetch(`{{ route('master.clinics.delete', '') }}/${clinicId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(data.message);

                // Remove the row from the table
                const row = deleteBtn.closest('tr');
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();

                    // Check if table is empty
                    const tbody = document.querySelector('table tbody');
                    if (tbody.children.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                }, 300);
            } else {
                // Show error message
                alert(data.message);

                // Restore button state
                deleteBtn.innerHTML = originalContent;
                deleteBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred while deleting the clinic. Please try again.") }}');

            // Restore button state
            deleteBtn.innerHTML = originalContent;
            deleteBtn.disabled = false;
        });
    }
}

// Generate Code Form
document.getElementById('generateCodeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Generating...") }}';
    submitBtn.disabled = true;
    
    fetch('{{ route("master.generate-code") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`{{ __("Activation code generated successfully!") }}\n\n{{ __("Code") }}: ${data.activation_code}`);
            bootstrap.Modal.getInstance(document.getElementById('generateCodeModal')).hide();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Extend Subscription Form
document.getElementById('extendSubscriptionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const clinicId = document.getElementById('extend_clinic_id').value;
    const months = document.getElementById('extend_months').value;
    
    fetch(`{{ route('master.clinic.extend-subscription', '') }}/${clinicId}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ months: parseInt(months) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('{{ __("Subscription extended successfully!") }}');
            bootstrap.Modal.getInstance(document.getElementById('extendSubscriptionModal')).hide();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    });
});

// Trial Management Functions
function extendTrial(clinicId) {
    document.getElementById('extend_trial_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('extendTrialModal')).show();
}

function convertTrial(clinicId) {
    document.getElementById('convert_trial_clinic_id').value = clinicId;
    new bootstrap.Modal(document.getElementById('convertTrialModal')).show();
}

// Extend Trial Form
document.getElementById('extendTrialForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const clinicId = document.getElementById('extend_trial_clinic_id').value;
    const days = document.getElementById('extend_trial_days').value;

    fetch(`/master/clinics/${clinicId}/extend-trial`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ days: parseInt(days) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('extendTrialModal')).hide();
            location.reload();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    });
});

// Convert Trial Form
document.getElementById('convertTrialForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const clinicId = document.getElementById('convert_trial_clinic_id').value;
    const months = document.getElementById('convert_trial_months').value;
    const planType = document.getElementById('convert_trial_plan_type').value;

    fetch(`/master/clinics/${clinicId}/convert-trial`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            months: parseInt(months),
            plan_type: planType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('convertTrialModal')).hide();
            location.reload();
        } else {
            alert(data.error || 'An error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    });
});
</script>
@endsection
