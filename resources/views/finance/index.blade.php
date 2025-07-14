@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-dollar-sign text-primary"></i>
                    Finance Dashboard
                </h1>
                <div>
                    <a href="{{ route('finance.invoices') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-file-invoice"></i> Invoices
                    </a>
                    <a href="{{ route('finance.expenses') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-receipt"></i> Expenses
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Monthly Revenue</h6>
                            <h2 class="mb-0">${{ number_format($monthlyRevenue, 2) }}</h2>
                            <small>{{ now()->format('F Y') }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Monthly Expenses</h6>
                            <h2 class="mb-0">${{ number_format($monthlyExpenses, 2) }}</h2>
                            <small>{{ now()->format('F Y') }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-pie fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-{{ $monthlyProfit >= 0 ? 'primary' : 'warning' }} text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Monthly Profit</h6>
                            <h2 class="mb-0">${{ number_format($monthlyProfit, 2) }}</h2>
                            <small>Revenue - Expenses</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-{{ $monthlyProfit >= 0 ? 'arrow-up' : 'arrow-down' }} fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Outstanding</h6>
                            <h2 class="mb-0">${{ number_format($outstandingInvoices, 2) }}</h2>
                            <small>Unpaid invoices</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Invoices</h5>
                    <h2 class="text-primary">{{ number_format($totalInvoices) }}</h2>
                    @if($overdueInvoices > 0)
                    <p class="text-danger mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ $overdueInvoices }} overdue
                    </p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Pending Expenses</h5>
                    <h2 class="text-warning">{{ number_format($pendingExpenseCount) }}</h2>
                    <p class="text-muted mb-0">
                        ${{ number_format($pendingExpenses, 2) }} total
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Profit Margin</h5>
                    @php
                        $profitMargin = $monthlyRevenue > 0 ? ($monthlyProfit / $monthlyRevenue) * 100 : 0;
                    @endphp
                    <h2 class="text-{{ $profitMargin >= 0 ? 'success' : 'danger' }}">
                        {{ number_format($profitMargin, 1) }}%
                    </h2>
                    <p class="text-muted mb-0">This month</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Invoices -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-file-invoice text-primary"></i>
                        Recent Invoices
                    </h6>
                    <a href="{{ route('finance.invoices') }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentInvoices as $invoice)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $invoice->invoice_number }}</h6>
                                <p class="mb-1 text-muted small">
                                    {{ $invoice->patient->full_name }}
                                </p>
                                <span class="{{ $invoice->status_badge_class }}">
                                    {{ $invoice->status_display }}
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">${{ number_format($invoice->total_amount, 2) }}</div>
                                <small class="text-muted">
                                    {{ $invoice->invoice_date->format('M d') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-file-invoice fa-2x mb-2"></i>
                        <p class="mb-0">No recent invoices</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-receipt text-danger"></i>
                        Recent Expenses
                    </h6>
                    <a href="{{ route('finance.expenses') }}" class="btn btn-sm btn-outline-secondary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentExpenses as $expense)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $expense->description }}</h6>
                                <p class="mb-1 text-muted small">
                                    {{ $expense->category_display }}
                                    @if($expense->vendor_name)
                                        • {{ $expense->vendor_name }}
                                    @endif
                                </p>
                                <span class="{{ $expense->status_badge_class }}">
                                    {{ $expense->status_display }}
                                </span>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger">${{ number_format($expense->amount, 2) }}</div>
                                <small class="text-muted">
                                    {{ $expense->expense_date->format('M d') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-3 text-center text-muted">
                        <i class="fas fa-receipt fa-2x mb-2"></i>
                        <p class="mb-0">No recent expenses</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-plus-circle"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                                <i class="fas fa-file-invoice-dollar d-block mb-1"></i>
                                <small>Create Invoice</small>
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button type="button" class="btn btn-danger btn-lg w-100" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="fas fa-receipt d-block mb-1"></i>
                                <small>Add Expense</small>
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('finance.reports') }}" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-chart-bar d-block mb-1"></i>
                                <small>View Reports</small>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary btn-lg w-100">
                                <i class="fas fa-cog d-block mb-1"></i>
                                <small>Finance Settings</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('finance.invoices.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="patient_id" class="form-label">Patient *</label>
                            <select class="form-select" id="patient_id" name="patient_id" required>
                                <option value="">Select Patient</option>
                                <!-- Patients will be loaded via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date" 
                                   value="{{ now()->addDays(30)->format('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                   min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="discount_rate" class="form-label">Discount Rate (%)</label>
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" 
                                   min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="discount_amount" class="form-label">Discount Amount ($)</label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount" 
                                   min="0" step="0.01" value="0">
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Items *</label>
                        <div id="invoiceItems">
                            <div class="row invoice-item mb-2">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="items[0][description]" 
                                           placeholder="Description" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][quantity]" 
                                           placeholder="Qty" min="1" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="items[0][unit_price]" 
                                           placeholder="Price" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="items[0][item_type]" required>
                                        <option value="consultation">Consultation</option>
                                        <option value="procedure">Procedure</option>
                                        <option value="medication">Medication</option>
                                        <option value="lab_test">Lab Test</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="terms" class="form-label">Terms</label>
                            <textarea class="form-control" id="terms" name="terms" rows="3" 
                                      placeholder="Payment terms and conditions..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Add/Remove invoice items
let itemIndex = 1;

document.getElementById('addItem').addEventListener('click', function() {
    const container = document.getElementById('invoiceItems');
    const newItem = document.querySelector('.invoice-item').cloneNode(true);
    
    // Update input names
    newItem.querySelectorAll('input, select').forEach(input => {
        const name = input.name.replace(/\[\d+\]/, `[${itemIndex}]`);
        input.name = name;
        input.value = input.type === 'number' && input.placeholder === 'Qty' ? '1' : '';
    });
    
    container.appendChild(newItem);
    itemIndex++;
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        const items = document.querySelectorAll('.invoice-item');
        if (items.length > 1) {
            e.target.closest('.invoice-item').remove();
        }
    }
});
</script>
@endpush
@endsection
