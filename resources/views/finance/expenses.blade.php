@extends('layouts.app')

@section('title', __('Expenses'))

@push('styles')
<style>
    /* Force proper layout to prevent sidebar overlap */
    body .main-content {
        margin-left: 290px !important;
        margin-top: 60px !important;
        min-height: calc(100vh - 60px) !important;
        transition: margin-left 0.3s ease !important;
    }

    /* Responsive table */
    .table-responsive {
        overflow-x: auto;
    }

    /* Extra small buttons - unified sizing */
    .btn-xs,
    .btn-xs.btn-outline-primary,
    .btn-xs.btn-outline-secondary,
    .btn-xs.btn-outline-info,
    .btn-xs.btn-outline-success,
    .btn-xs.btn-outline-danger,
    .btn-xs.btn-primary,
    .btn-xs.btn-secondary,
    .btn-xs.btn-info,
    .btn-xs.btn-success,
    .btn-xs.btn-danger {
        padding: 0 !important;
        font-size: 0.75rem !important;
        line-height: 1 !important;
        border-radius: 0.25rem !important;
        min-width: 32px !important;
        width: 32px !important;
        height: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
        border-width: 1px !important;
        margin: 0 !important;
        flex-shrink: 0 !important;
    }

    /* Action buttons styling */
    .btn-group {
        display: inline-flex !important;
        align-items: center !important;
        gap: 2px !important;
        flex-wrap: nowrap !important;
    }

    .btn-group .btn,
    .btn-group .btn-xs {
        margin: 0 !important;
        flex-shrink: 0 !important;
    }

    /* Override any Bootstrap button group styles */
    .btn-group > .btn:not(:first-child),
    .btn-group > .btn-group:not(:first-child) {
        margin-left: 0 !important;
    }

    .btn-group > .btn:not(:last-child):not(.dropdown-toggle),
    .btn-group > .btn-group:not(:last-child) > .btn {
        border-top-right-radius: 0.25rem !important;
        border-bottom-right-radius: 0.25rem !important;
    }

    /* Handle inline forms within button groups */
    .btn-group form.d-inline {
        display: inline-flex !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .btn-group form.d-inline .btn {
        margin: 0 !important;
    }

    /* Compact button group */
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 1px;
    }

    /* Print specific styling */
    .print-only {
        display: none;
    }

    @media print {
        .print-only {
            display: block;
        }
        .no-print {
            display: none;
        }
    }

    /* Additional content wrapper fix */
    body .content-wrapper {
        padding: 1rem 0 !important;
    }

    /* Ensure proper positioning */
    #app .main-content {
        margin-left: 290px !important;
        margin-top: 60px !important;
    }

    /* Mobile responsive fixes */
    @media (max-width: 991.98px) {
        body .main-content,
        #app .main-content {
            margin-left: 0 !important;
        }

        .expenses-page-wrapper {
            margin-left: 0 !important;
            margin-top: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="expenses-page-wrapper" style="margin-left: 290px; margin-top: 60px; padding: 1rem 1.5rem; min-height: calc(100vh - 60px);">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-receipt text-danger me-2"></i>
                        {{ __('Expenses') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.index') }}">{{ __('Finance') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Expenses') }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Add Expense') }}
                    </button>
                    <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Finance') }}
                    </a>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.expenses') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">{{ __('All Statuses') }}</option>
                                @foreach(\App\Models\Expense::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">{{ __('Category') }}</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
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
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Filter') }}
                            </button>
                            <a href="{{ route('finance.expenses') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.expenses') }}" class="row g-3">
                        <!-- Preserve existing filters -->
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('date_from'))
                            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                        @endif
                        @if(request('date_to'))
                            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                        @endif

                        <div class="col-md-10">
                            <input type="text" class="form-control" name="search"
                                   placeholder="{{ __('Search by expense number, description, or vendor name...') }}"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Search') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expenses Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Expense List') }}
                        <span class="badge bg-secondary ms-2">{{ $expenses->total() }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if($expenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Expense #') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Created By') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                    <tr>
                                        <td>
                                            <strong>{{ $expense->expense_number }}</strong>
                                            @if($expense->is_recurring)
                                                <span class="badge bg-info ms-1">{{ __('Recurring') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $expense->description }}</div>
                                            @if($expense->vendor_name)
                                                <small class="text-muted">{{ __('Vendor') }}: {{ $expense->vendor_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $expense->category_display }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">{{ $currencySymbol ?? '$' }}{{ number_format($expense->amount, 2) }}</strong>
                                            @if($expense->payment_method)
                                                <br><small class="text-muted">{{ $expense->payment_method_display }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                                        <td>
                                            <span class="{{ $expense->status_badge_class }}">
                                                {{ $expense->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($expense->creator)
                                                {{ $expense->creator->first_name }} {{ $expense->creator->last_name }}
                                            @else
                                                <span class="text-muted">{{ __('Unknown') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- View Button -->
                                                <button type="button" class="btn btn-xs btn-outline-primary"
                                                        title="{{ __('View Details') }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewExpenseModal{{ $expense->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <!-- Edit Button -->
                                                @if(auth()->user()->hasPermission('finance_edit') || $expense->created_by === auth()->id())
                                                    @if($expense->status !== 'approved')
                                                        <button type="button" class="btn btn-xs btn-outline-secondary"
                                                                title="{{ __('Edit') }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editExpenseModal{{ $expense->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                @endif

                                                <!-- Print Button -->
                                                <button type="button" class="btn btn-xs btn-outline-info"
                                                        title="{{ __('Print') }}"
                                                        onclick="printExpense({{ $expense->id }})">
                                                    <i class="fas fa-print"></i>
                                                </button>

                                                @if($expense->hasReceiptFile())
                                                    <a href="{{ $expense->receipt_file_url }}" target="_blank"
                                                       class="btn btn-xs btn-outline-info" title="{{ __('View Receipt') }}">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                @endif

                                                @if($expense->canBeApproved() && auth()->user()->hasPermission('finance_approve'))
                                                    <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-success"
                                                                title="{{ __('Approve') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to approve this expense?') }}')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('finance.expenses.reject', $expense) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-danger"
                                                                title="{{ __('Reject') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to reject this expense?') }}')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Delete Button (for admins or creators) -->
                                                @if(auth()->user()->hasPermission('finance_delete') || $expense->created_by === auth()->id())
                                                    <form method="POST" action="{{ route('finance.expenses.destroy', $expense) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                                                title="{{ __('Delete') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to delete this expense? This action cannot be undone.') }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $expenses->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No expenses found') }}</h5>
                            <p class="text-muted">{{ __('No expenses match your current filters.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="fas fa-plus me-1"></i>
                                {{ __('Add First Expense') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addExpenseModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    {{ __('Add New Expense') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="description" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror"
                                   id="description" name="description" value="{{ old('description') }}" required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol ?? '$' }}</span>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                       id="amount" name="amount" value="{{ old('amount') }}"
                                       step="0.01" min="0" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="category" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="expense_date" class="form-label">{{ __('Expense Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expense_date') is-invalid @enderror"
                                   id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                            @error('expense_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                <option value="">{{ __('Select Payment Method') }}</option>
                                @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="vendor_name" class="form-label">{{ __('Vendor Name') }}</label>
                            <input type="text" class="form-control @error('vendor_name') is-invalid @enderror"
                                   id="vendor_name" name="vendor_name" value="{{ old('vendor_name') }}">
                            @error('vendor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="receipt_number" class="form-label">{{ __('Receipt Number') }}</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror"
                                   id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}">
                            @error('receipt_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="receipt_file" class="form-label">{{ __('Receipt File') }}</label>
                            <input type="file" class="form-control @error('receipt_file') is-invalid @enderror"
                                   id="receipt_file" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">{{ __('Accepted formats: PDF, JPG, PNG. Max size: 5MB') }}</div>
                            @error('receipt_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input @error('is_recurring') is-invalid @enderror"
                                       type="checkbox" id="is_recurring" name="is_recurring" value="1"
                                       {{ old('is_recurring') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_recurring">
                                    {{ __('This is a recurring expense') }}
                                </label>
                                @error('is_recurring')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12" id="recurring_frequency_group" style="display: none;">
                            <label for="recurring_frequency" class="form-label">{{ __('Recurring Frequency') }}</label>
                            <select class="form-select @error('recurring_frequency') is-invalid @enderror"
                                    id="recurring_frequency" name="recurring_frequency">
                                <option value="">{{ __('Select Frequency') }}</option>
                                @foreach(\App\Models\Expense::RECURRING_FREQUENCIES as $key => $label)
                                    <option value="{{ $key }}" {{ old('recurring_frequency') == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('recurring_frequency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Add Expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Expense Modals -->
@foreach($expenses as $expense)
<div class="modal fade" id="viewExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="viewExpenseModalLabel{{ $expense->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewExpenseModalLabel{{ $expense->id }}">
                    <i class="fas fa-eye me-2"></i>
                    {{ __('Expense Details') }} - {{ $expense->expense_number }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Description') }}</label>
                        <p class="form-control-plaintext">{{ $expense->description }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Amount') }}</label>
                        <p class="form-control-plaintext">{{ $currencySymbol ?? '$' }}{{ number_format($expense->amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Category') }}</label>
                        <p class="form-control-plaintext">{{ $expense->category_display }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Expense Date') }}</label>
                        <p class="form-control-plaintext">{{ $expense->expense_date->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Payment Method') }}</label>
                        <p class="form-control-plaintext">{{ $expense->payment_method_display }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Status') }}</label>
                        <p class="form-control-plaintext">
                            <span class="{{ $expense->status_badge_class }}">{{ $expense->status_display }}</span>
                        </p>
                    </div>
                    @if($expense->vendor_name)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Vendor Name') }}</label>
                        <p class="form-control-plaintext">{{ $expense->vendor_name }}</p>
                    </div>
                    @endif
                    @if($expense->receipt_number)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Receipt Number') }}</label>
                        <p class="form-control-plaintext">{{ $expense->receipt_number }}</p>
                    </div>
                    @endif
                    @if($expense->notes)
                    <div class="col-12">
                        <label class="form-label fw-bold">{{ __('Notes') }}</label>
                        <p class="form-control-plaintext">{{ $expense->notes }}</p>
                    </div>
                    @endif
                    @if($expense->is_recurring)
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Recurring') }}</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-info">{{ __('Yes') }} - {{ $expense->recurring_frequency_display }}</span>
                        </p>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Created By') }}</label>
                        <p class="form-control-plaintext">
                            @if($expense->creator)
                                {{ $expense->creator->first_name }} {{ $expense->creator->last_name }}
                            @else
                                <span class="text-muted">{{ __('Unknown') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ __('Created At') }}</label>
                        <p class="form-control-plaintext">{{ $expense->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    {{ __('Close') }}
                </button>
                @if($expense->hasReceiptFile())
                    <a href="{{ $expense->receipt_file_url }}" target="_blank" class="btn btn-info">
                        <i class="fas fa-file-alt me-1"></i>
                        {{ __('View Receipt') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Edit Expense Modals -->
@foreach($expenses as $expense)
@if((auth()->user()->hasPermission('finance_edit') || $expense->created_by === auth()->id()) && $expense->status !== 'approved')
<div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="editExpenseModalLabel{{ $expense->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editExpenseModalLabel{{ $expense->id }}">
                    <i class="fas fa-edit me-2"></i>
                    {{ __('Edit Expense') }} - {{ $expense->expense_number }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('finance.expenses.update', $expense) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_description_{{ $expense->id }}" class="form-label">{{ __('Description') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_description_{{ $expense->id }}"
                                   name="description" value="{{ $expense->description }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_amount_{{ $expense->id }}" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol ?? '$' }}</span>
                                <input type="number" class="form-control" id="edit_amount_{{ $expense->id }}"
                                       name="amount" value="{{ $expense->amount }}" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_category_{{ $expense->id }}" class="form-label">{{ __('Category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_category_{{ $expense->id }}" name="category" required>
                                @foreach(\App\Models\Expense::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}" {{ $expense->category == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_expense_date_{{ $expense->id }}" class="form-label">{{ __('Expense Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_expense_date_{{ $expense->id }}"
                                   name="expense_date" value="{{ $expense->expense_date->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_payment_method_{{ $expense->id }}" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_payment_method_{{ $expense->id }}" name="payment_method" required>
                                @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                                    <option value="{{ $key }}" {{ $expense->payment_method == $key ? 'selected' : '' }}>
                                        {{ __($label) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_vendor_name_{{ $expense->id }}" class="form-label">{{ __('Vendor Name') }}</label>
                            <input type="text" class="form-control" id="edit_vendor_name_{{ $expense->id }}"
                                   name="vendor_name" value="{{ $expense->vendor_name }}">
                        </div>

                        <div class="col-md-6">
                            <label for="edit_receipt_number_{{ $expense->id }}" class="form-label">{{ __('Receipt Number') }}</label>
                            <input type="text" class="form-control" id="edit_receipt_number_{{ $expense->id }}"
                                   name="receipt_number" value="{{ $expense->receipt_number }}">
                        </div>

                        <div class="col-md-12">
                            <label for="edit_receipt_file_{{ $expense->id }}" class="form-label">{{ __('Receipt File') }}</label>
                            @if($expense->hasReceiptFile())
                                <div class="mb-2">
                                    <small class="text-muted">{{ __('Current file') }}:
                                        <a href="{{ $expense->receipt_file_url }}" target="_blank">{{ __('View Current Receipt') }}</a>
                                    </small>
                                </div>
                            @endif
                            <input type="file" class="form-control" id="edit_receipt_file_{{ $expense->id }}"
                                   name="receipt_file" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">{{ __('Leave empty to keep current file. Accepted formats: PDF, JPG, PNG. Max size: 5MB') }}</div>
                        </div>

                        <div class="col-md-12">
                            <label for="edit_notes_{{ $expense->id }}" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="edit_notes_{{ $expense->id }}"
                                      name="notes" rows="3">{{ $expense->notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Update Expense') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(request('new') || session('open_add_expense') || $errors->has('description') || $errors->has('amount') || $errors->has('category') || $errors->has('expense_date') || $errors->has('payment_method'))
        var el = document.getElementById('addExpenseModal');
        if (el) { var m = new bootstrap.Modal(el); m.show(); }
    @endif
});

// Print expense function
function printExpense(expenseId) {
    // Create a new window for printing
    const printWindow = window.open('', '_blank');

    // Get expense data from the view modal
    const modal = document.getElementById('viewExpenseModal' + expenseId);
    if (!modal) return;

    const modalBody = modal.querySelector('.modal-body');
    const expenseTitle = modal.querySelector('.modal-title').textContent;

    // Create print content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${expenseTitle}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .row { display: flex; flex-wrap: wrap; margin-bottom: 15px; }
                .col { flex: 1; min-width: 200px; margin-right: 20px; }
                .label { font-weight: bold; color: #333; }
                .value { margin-top: 5px; }
                .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                .bg-success { background-color: #28a745; color: white; }
                .bg-warning { background-color: #ffc107; color: black; }
                .bg-danger { background-color: #dc3545; color: white; }
                .bg-info { background-color: #17a2b8; color: white; }
                .bg-secondary { background-color: #6c757d; color: white; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>${expenseTitle}</h2>
                <p>Printed on: ${new Date().toLocaleDateString()}</p>
            </div>
            <div class="content">
                ${modalBody.innerHTML}
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide recurring frequency field
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurringFrequencyGroup = document.getElementById('recurring_frequency_group');

    function toggleRecurringFrequency() {
        if (isRecurringCheckbox.checked) {
            recurringFrequencyGroup.style.display = 'block';
        } else {
            recurringFrequencyGroup.style.display = 'none';
        }
    }

    isRecurringCheckbox.addEventListener('change', toggleRecurringFrequency);

    // Initialize on page load
    toggleRecurringFrequency();
});
</script>
@endpush
