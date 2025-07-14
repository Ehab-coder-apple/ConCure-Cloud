@extends('layouts.master')

@section('title', __('Activation Codes'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-key text-warning me-2"></i>
                        {{ __('Activation Codes') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Activation Codes') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Generate New Code') }}
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('master.activation-codes') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">{{ __('Search Codes') }}</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="{{ __('Code, clinic name, email...') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>{{ __('Used') }}</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="created_by" class="form-label">{{ __('Created By') }}</label>
                            <select class="form-select" id="created_by" name="created_by">
                                <option value="">{{ __('All Creators') }}</option>
                                @if(isset($creators))
                                    @foreach($creators as $creator)
                                        <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>
                                            {{ $creator->first_name }} {{ $creator->last_name }}
                                        </option>
                                    @endforeach
                                @endif
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

            <!-- Activation Codes Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Generated Activation Codes') }}
                        <span class="badge bg-primary ms-2">{{ $activationCodes->total() ?? 0 }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($activationCodes) && $activationCodes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Clinic Details') }}</th>
                                        <th>{{ __('Subscription') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activationCodes as $code)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-bold font-monospace">{{ $code->code }}</div>
                                                <small class="text-muted">{{ $code->max_users }} {{ __('max users') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $code->clinic_name }}</div>
                                                <small class="text-muted">{{ $code->admin_email }}</small>
                                                <br>
                                                <small class="text-info">{{ $code->admin_first_name }} {{ $code->admin_last_name }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="badge bg-info">{{ $code->subscription_months }} {{ __('months') }}</span>
                                                <br>
                                                <small class="text-muted">{{ __('Expires') }}: <span class="expiry-date">{{ \Carbon\Carbon::parse($code->expires_at)->format('M d, Y') }}</span></small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $isExpired = \Carbon\Carbon::parse($code->expires_at)->isPast();
                                                $isUsed = $code->is_used;
                                            @endphp
                                            @if($isUsed)
                                                <span class="badge bg-success">{{ __('Used') }}</span>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($code->used_at)->format('M d, Y') }}</small>
                                            @elseif($isExpired)
                                                <span class="badge bg-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $code->creator_first_name }} {{ $code->creator_last_name }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($code->created_at)->format('M d, Y H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if(!$isUsed && !$isExpired)
                                                    <button type="button" class="btn btn-outline-info" 
                                                            onclick="copyCode('{{ $code->code }}')"
                                                            title="{{ __('Copy Code') }}">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning" 
                                                            onclick="extendCode('{{ $code->id }}')"
                                                            title="{{ __('Extend Expiry') }}">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewCodeDetails('{{ $code->id }}')"
                                                        title="{{ __('View Details') }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if(!$isUsed)
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="deleteCode('{{ $code->id }}')"
                                                            title="{{ __('Delete Code') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(method_exists($activationCodes, 'links'))
                            <div class="card-footer">
                                {{ $activationCodes->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Activation Codes Found') }}</h5>
                            <p class="text-muted">{{ __('Generate your first activation code to start onboarding clinics.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateCodeModal">
                                <i class="fas fa-plus me-2"></i>
                                {{ __('Generate First Code') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Code Details Modal -->
<div class="modal fade" id="codeDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    {{ __('Activation Code Details') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">{{ __('Code') }}:</td>
                                <td><code id="detailsCode"></code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">{{ __('Clinic Name') }}:</td>
                                <td id="detailsClinicName"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">{{ __('Admin Email') }}:</td>
                                <td id="detailsAdminEmail"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">{{ __('Admin Name') }}:</td>
                                <td id="detailsAdminName"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">{{ __('Subscription') }}:</td>
                                <td id="detailsSubscription"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">{{ __('Expires') }}:</td>
                                <td id="detailsExpiry"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">{{ __('Status') }}:</td>
                                <td id="detailsStatus"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" onclick="copyCodeFromDetails()">
                    <i class="fas fa-copy me-1"></i>
                    {{ __('Copy Code') }}
                </button>
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
                    {{ __('Generate Activation Code') }}
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

<script>
// Cache buster: {{ now()->timestamp }}
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('{{ __("Activation code copied to clipboard!") }}');
    });
}

function copyCodeFromDetails() {
    const code = document.getElementById('detailsCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        alert('{{ __("Activation code copied to clipboard!") }}');
    });
}

function extendCode(codeId) {
    if (confirm('{{ __("Are you sure you want to extend this activation code by 30 days?") }}')) {

        // Show loading state
        const extendBtn = document.querySelector(`button[onclick="extendCode('${codeId}')"]`);
        const originalContent = extendBtn.innerHTML;
        extendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        extendBtn.disabled = true;

        fetch(`{{ route('master.activation-codes.extend', '') }}/${codeId}`, {
            method: 'PATCH',
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

                // Update the expiry date in the table if possible
                const row = extendBtn.closest('tr');
                const expiryCell = row.querySelector('.expiry-date');
                if (expiryCell && data.new_expiry) {
                    const newDate = new Date(data.new_expiry);
                    expiryCell.textContent = newDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                }

                // Restore button state
                extendBtn.innerHTML = originalContent;
                extendBtn.disabled = false;
            } else {
                // Show error message
                alert(data.message);

                // Restore button state
                extendBtn.innerHTML = originalContent;
                extendBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("An error occurred while extending the activation code. Please try again.") }}');

            // Restore button state
            extendBtn.innerHTML = originalContent;
            extendBtn.disabled = false;
        });
    }
}

function viewCodeDetails(codeId) {
    // Find the row data
    const row = document.querySelector(`button[onclick="viewCodeDetails('${codeId}')"]`).closest('tr');
    const cells = row.querySelectorAll('td');

    // Extract data from the row
    const code = cells[0].querySelector('.fw-bold').textContent.trim();
    const clinicName = cells[1].querySelector('.fw-bold').textContent.trim();
    const adminEmail = cells[1].querySelector('.text-muted').textContent.trim();
    const adminName = cells[1].querySelector('.text-info').textContent.trim();
    const subscriptionMonths = cells[2].querySelector('.badge').textContent.trim();
    const expiryDate = cells[2].querySelector('.text-muted').textContent.replace('Expires: ', '').trim();
    const status = cells[3].querySelector('.badge').textContent.trim();

    // Populate modal with details
    document.getElementById('detailsCode').textContent = code;
    document.getElementById('detailsClinicName').textContent = clinicName;
    document.getElementById('detailsAdminEmail').textContent = adminEmail;
    document.getElementById('detailsAdminName').textContent = adminName;
    document.getElementById('detailsSubscription').textContent = subscriptionMonths;
    document.getElementById('detailsExpiry').textContent = expiryDate;
    document.getElementById('detailsStatus').textContent = status;

    // Show the modal
    new bootstrap.Modal(document.getElementById('codeDetailsModal')).show();
}

function deleteCode(codeId) {
    if (confirm('{{ __("Are you sure you want to delete this activation code?") }}\n\n{{ __("Warning: This action cannot be undone. If the code is associated with an unactivated clinic, the clinic will also be deleted.") }}')) {

        // Show loading state
        const deleteBtn = document.querySelector(`button[onclick="deleteCode('${codeId}')"]`);
        const originalContent = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        deleteBtn.disabled = true;

        fetch(`{{ route('master.activation-codes.delete', '') }}/${codeId}`, {
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
            alert('{{ __("An error occurred while deleting the activation code. Please try again.") }}');

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
</script>
@endsection
