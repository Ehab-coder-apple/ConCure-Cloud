@extends('layouts.app')

@section('content')
<style>
    .btn-xs,
    .btn-xs.btn-outline-primary,
    .btn-xs.btn-outline-secondary,
    .btn-xs.btn-outline-info,
    .btn-xs.btn-outline-success,
    .btn-xs.btn-outline-danger,
    .btn-xs.btn-outline-warning,
    .btn-xs.btn-primary,
    .btn-xs.btn-secondary,
    .btn-xs.btn-info,
    .btn-xs.btn-success,
    .btn-xs.btn-danger,
    .btn-xs.btn-warning {
        padding: 0 !important;
        font-size: 0.75rem !important;
        line-height: 1.2 !important;
        border-radius: 0.2rem !important;
        min-width: 32px !important;
        width: 32px !important;
        height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
        border-width: 1px !important;
    }

    .btn-group {
        display: inline-flex !important;
        gap: 2px !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
    }

    .btn-group form.d-inline {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .btn-group .btn {
        margin: 0 !important;
        flex-shrink: 0 !important;
    }
</style>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-receipt text-success"></i>
                    {{ __('Receipts') }}
                </h1>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createReceiptModal">
                    <i class="fas fa-plus"></i> {{ __('Add Receipt') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.receipts') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="{{ __('Receipt number, description, payer...') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ __('Status') }}</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="category" class="form-label">{{ __('Category') }}</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">{{ __('All Categories') }}</option>
                                    @foreach(\App\Models\Receipt::CATEGORIES as $key => $value)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ __($value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipts Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($receipts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Receipt #') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Payer') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipts as $receipt)
                                        <tr>
                                            <td>
                                                <strong>{{ $receipt->receipt_number }}</strong>
                                                @if($receipt->reference_number)
                                                    <br><small class="text-muted">Ref: {{ $receipt->reference_number }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $receipt->receipt_date->format('M d, Y') }}</td>
                                            <td>
                                                {{ $receipt->description }}
                                                @if($receipt->notes)
                                                    <br><small class="text-muted">{{ Str::limit($receipt->notes, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $receipt->category_name }}</td>
                                            <td><strong>{{ $currencySymbol }}{{ number_format($receipt->amount, 2) }}</strong></td>
                                            <td>{{ $receipt->payer_name ?? '-' }}</td>
                                            <td>{{ $receipt->payment_method_name }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger'
                                                    ];
                                                    $color = $statusColors[$receipt->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($receipt->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $receipt->creator->name }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-xs btn-outline-primary" title="{{ __('View Receipt') }}"
                                                            onclick="viewReceipt({{ $receipt->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    @if($receipt->status !== 'approved')
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" title="{{ __('Edit Receipt') }}"
                                                                onclick="editReceipt({{ $receipt->id }})">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif

                                                    @if($receipt->hasReceiptFile())
                                                        <a href="{{ $receipt->receipt_file_url }}" class="btn btn-xs btn-outline-info" title="{{ __('View File') }}" target="_blank">
                                                            <i class="fas fa-file"></i>
                                                        </a>
                                                    @endif

                                                    @if(auth()->user()->hasPermission('finance_delete') || $receipt->created_by === auth()->id())
                                                        @if($receipt->status !== 'approved')
                                                            <form method="POST" action="{{ route('finance.receipts.destroy', $receipt) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                                                        title="{{ __('Delete') }}"
                                                                        onclick="return confirm('{{ __('Are you sure you want to delete this receipt? This action cannot be undone.') }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $receipts->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No receipts found') }}</h5>
                            <p class="text-muted">{{ __('Start by creating your first receipt.') }}</p>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createReceiptModal">
                                <i class="fas fa-plus"></i> {{ __('Add Receipt') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Receipt Modal -->
<div class="modal fade" id="createReceiptModal" tabindex="-1" aria-labelledby="createReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createReceiptModalLabel">{{ __('Add New Receipt') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('finance.receipts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="description" name="description" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">{{ __('Amount') }} ({{ $currencySymbol }}) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach(\App\Models\Receipt::CATEGORIES as $key => $value)
                                        <option value="{{ $key }}">{{ __($value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receipt_date" class="form-label">{{ __('Receipt Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="receipt_date" name="receipt_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">{{ __('Select Payment Method') }}</option>
                                    @foreach(\App\Models\Receipt::PAYMENT_METHODS as $key => $value)
                                        <option value="{{ $key }}">{{ __($value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payer_name" class="form-label">{{ __('Payer Name') }}</label>
                                <input type="text" class="form-control" id="payer_name" name="payer_name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">{{ __('Reference Number') }}</label>
                                <input type="text" class="form-control" id="reference_number" name="reference_number"
                                       placeholder="{{ __('Invoice #, Check #, etc.') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="receipt_file" class="form-label">{{ __('Receipt File') }}</label>
                                <input type="file" class="form-control" id="receipt_file" name="receipt_file"
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">{{ __('Upload scanned receipt or document (PDF, JPG, PNG, max 5MB)') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Add Receipt') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Receipt function
    window.viewReceipt = function(receiptId) {
        // For now, just show an alert. You can implement a view modal later
        alert('View receipt functionality - Receipt ID: ' + receiptId);
    };

    // Edit Receipt function
    window.editReceipt = function(receiptId) {
        // For now, just show an alert. You can implement an edit modal later
        alert('Edit receipt functionality - Receipt ID: ' + receiptId);
    };
});
</script>
@endsection
