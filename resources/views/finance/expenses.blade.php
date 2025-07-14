@extends('layouts.app')

@section('title', __('Expenses'))

@section('content')
<div class="container-fluid">
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
                                            <strong class="text-danger">${{ number_format($expense->amount, 2) }}</strong>
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
                                                @if($expense->hasReceiptFile())
                                                    <a href="{{ $expense->receipt_file_url }}" target="_blank" 
                                                       class="btn btn-sm btn-outline-info" title="{{ __('View Receipt') }}">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                @endif
                                                
                                                @if($expense->canBeApproved() && auth()->user()->role === 'admin')
                                                    <form method="POST" action="{{ route('finance.expenses.approve', $expense) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" 
                                                                title="{{ __('Approve') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to approve this expense?') }}')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('finance.expenses.reject', $expense) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                title="{{ __('Reject') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to reject this expense?') }}')">
                                                            <i class="fas fa-times"></i>
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
                                <span class="input-group-text">$</span>
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
@endsection

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
