@extends('master.layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-success me-2"></i>
                Financial Dashboard
            </h1>
            <p class="text-muted mb-0">Master SaaS Financial Overview</p>
        </div>
        <div>
            <a href="{{ route('master.finance.invoices') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i>
                Manage Invoices
            </a>
            <a href="{{ route('master.finance.expenses') }}" class="btn btn-outline-danger ms-2">
                <i class="fas fa-receipt me-1"></i>
                Manage Expenses
            </a>
            <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                <i class="fas fa-file-invoice me-1"></i>
                Create Invoice
            </button>
            <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#createReceiptModal">
                <i class="fas fa-receipt me-1"></i>
                Record Payment
            </button>
            <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="fas fa-money-bill-wave me-1"></i>
                Record Expense
            </button>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.finance.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="period" class="form-label">Time Period</label>
                    <select class="form-select" id="period" name="period" onchange="toggleCustomDates()">
                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="quarter" {{ $period == 'quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="semester" {{ $period == 'semester' ? 'selected' : '' }}>This Semester</option>
                        <option value="year" {{ $period == 'year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="customFromDiv" style="display: {{ $period == 'custom' ? 'block' : 'none' }}">
                    <label for="from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $from->format('Y-m-d') }}">
                </div>
                <div class="col-md-3" id="customToDiv" style="display: {{ $period == 'custom' ? 'block' : 'none' }}">
                    <label for="to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $to->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>
                        Apply Filter
                    </button>
                    <a href="{{ route('master.finance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Period Display -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-calendar me-2"></i>
        <strong>Showing data for:</strong> {{ $from->format('M d, Y') }} - {{ $to->format('M d, Y') }}
        ({{ $from->diffInDays($to) + 1 }} days)
    </div>

    <!-- Financial Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Revenue -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currencySymbol }}{{ number_format($stats['total_revenue'], 2) }}
                            </div>
                            <small class="text-muted">From {{ $stats['payment_count'] }} payments <span class="badge bg-info" style="font-size: 0.6rem;">Multi-Currency</span></small>
                        </div>
                        <div class="icon-circle bg-success text-white">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expected Revenue -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Expected Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currencySymbol }}{{ number_format($stats['expected_revenue'], 2) }}
                            </div>
                            <small class="text-muted">Collection: {{ number_format($stats['collection_rate'], 1) }}%</small>
                        </div>
                        <div class="icon-circle bg-warning text-white">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Charges -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Service Charges</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currencySymbol }}{{ number_format($stats['service_charges'], 2) }}
                            </div>
                            <small class="text-muted">Additional fees</small>
                        </div>
                        <div class="icon-circle bg-info text-white">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Net Profit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currencySymbol }}{{ number_format($stats['net_profit'], 2) }}
                            </div>
                            <small class="text-muted">Expenses (IQD): {{ number_format($stats['total_expenses'], 2) }}</small>
                        </div>
                        <div class="icon-circle bg-primary text-white">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant & User Statistics -->
    <div class="alert alert-warning mb-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Note:</strong> Financial metrics exclude demo clinics. Tenant and user statistics show paying tenants only.
    </div>

    <div class="row mb-4">
        <!-- Total Tenants (Paying) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-dark h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Paying Tenants</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['total_tenants']) }}
                    </div>
                    <small class="text-success">+{{ $tenantStats['new_tenants'] }} this period</small>
                </div>
            </div>
        </div>

        <!-- Active Tenants (Paying) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['active_tenants']) }}
                    </div>
                    <small class="text-muted">Subscribed</small>
                </div>
            </div>
        </div>

        <!-- Inactive Tenants (Paying) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-danger h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Inactive</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['inactive_tenants']) }}
                    </div>
                    <small class="text-muted">Suspended</small>
                </div>
            </div>
        </div>

        <!-- Demo Tenants (Separate) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Demo Clinics</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['demo_tenants']) }}
                    </div>
                    <small class="text-muted">
                        {{ number_format($tenantStats['active_demos']) }} active
                        @if($tenantStats['new_demos'] > 0)
                            <span class="text-success">(+{{ $tenantStats['new_demos'] }} new)</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Total Users (Paying Tenants Only) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['total_users']) }}
                    </div>
                    <small class="text-muted">Paying clinics</small>
                </div>
            </div>
        </div>

        <!-- Active Users (Paying Tenants Only) -->
        <div class="col-lg-2 col-md-4 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Users</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($tenantStats['active_users']) }}
                    </div>
                    <small class="text-muted">Enabled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart & Recent Payments -->
    <div class="row mb-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-chart-area me-2"></i>
                        Revenue Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0 text-primary">
                        <i class="fas fa-clock me-2"></i>
                        Recent Payments
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($recentReceipts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReceipts as $receipt)
                                @php
                                    // Check if currency field exists (after migration)
                                    $receiptCurrency = isset($receipt->currency) ? $receipt->currency : 'USD';
                                    $receiptSymbol = App\Models\MasterInvoice::getCurrencySymbolStatic($receiptCurrency);
                                @endphp
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">{{ $receipt->clinic_name }}</h6>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editPayment({{ $receipt->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deletePayment({{ $receipt->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ Carbon\Carbon::parse($receipt->paid_at)->format('M d, Y h:i A') }}
                                            </small>
                                            @if(isset($receipt->note) && $receipt->note)
                                                <small class="text-muted d-block mt-1">{{ $receipt->note }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-success ms-3">
                                            {{ $receiptSymbol }}{{ number_format($receipt->amount, 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No recent payments</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-danger">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Recent Expenses
                        <span class="badge bg-secondary ms-2">IQD</span>
                    </h6>
                    <a href="{{ route('master.finance.expenses') }}" class="btn btn-sm btn-outline-secondary">
                        View all
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentExpenses->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Recorded By</th>
                                        <th class="text-end">Amount (IQD)</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentExpenses as $expense)
                                        <tr>
                                            <td>{{ optional($expense->expense_date)->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $expenseCategories[$expense->category] ?? ucfirst($expense->category) }}
                                                </span>
                                            </td>
                                            <td>{{ $expense->description }}</td>
                                            <td>
                                                @if($expense->creator)
                                                    {{ trim($expense->creator->first_name . ' ' . $expense->creator->last_name) ?: '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-danger">
                                                {{ number_format($expense->amount, 2) }}
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick='editExpense(@json($expense))' title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteExpense({{ $expense->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No expenses recorded yet. Click <strong>Record Expense</strong> above to add one.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="createInvoiceForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>
                        Create Invoice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="invoice_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                            <select class="form-select" id="invoice_clinic_id" name="clinic_id" required>
                                <option value="">Select Clinic</option>
                                @foreach(App\Models\Clinic::where('is_demo', false)->orderBy('name')->get() as $clinic)
                                    <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="invoice_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="invoice_currency" name="currency" required>
                                <option value="USD">US Dollar ($)</option>
                                <option value="IQD">Iraqi Dinar (IQD)</option>
                                <option value="JOD">Jordanian Dinar (JD)</option>
                                <option value="EGP">Egyptian Pound (EGP)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="invoice_due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="invoice_due_date" name="due_date" required>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Items <span class="text-danger">*</span></label>
                        <div id="invoiceItemsContainer">
                            <div class="invoice-item row mb-2">
                                <div class="col-md-5">
                                    <input type="text" class="form-control" name="items[0][description]" placeholder="Description" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control item-quantity" name="items[0][quantity]" placeholder="Qty" min="1" value="1" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control item-price" name="items[0][unit_price]" placeholder="Unit Price" min="0" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-item" disabled>
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addInvoiceItem">
                            <i class="fas fa-plus me-1"></i> Add Item
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="invoice_tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="invoice_tax_rate" name="tax_rate" min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="invoice_discount" class="form-label">Discount Amount</label>
                            <input type="number" class="form-control" id="invoice_discount" name="discount_amount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control" id="invoice_subtotal" readonly value="{{ $currencySymbol }}0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="invoice_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="invoice_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPaymentForm">
                @csrf
                <input type="hidden" id="edit_payment_id" name="payment_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_payment_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_clinic_id" name="clinic_id" required>
                            <option value="">Select Clinic</option>
                            @foreach(App\Models\Clinic::where('is_demo', false)->orderBy('name')->get() as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_currency" name="currency" required>
                            <option value="USD">US Dollar ($)</option>
                            <option value="IQD">Iraqi Dinar (IQD)</option>
                            <option value="JOD">Jordanian Dinar (JD)</option>
                            <option value="EGP">Egyptian Pound (EGP)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="edit_payment_currency_symbol">$</span>
                            <input type="number" class="form-control" id="edit_payment_amount" name="amount" min="0.01" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_paid_at" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_payment_paid_at" name="paid_at" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_payment_note" class="form-label">Note</label>
                        <textarea class="form-control" id="edit_payment_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="createReceiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordPaymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-receipt me-2"></i>
                        Record Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_clinic_id" name="clinic_id" required>
                            <option value="">Select Clinic</option>
                            @foreach(App\Models\Clinic::where('is_demo', false)->orderBy('name')->get() as $clinic)
                                <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_currency" name="currency" required>
                            <option value="USD">US Dollar ($)</option>
                            <option value="IQD">Iraqi Dinar (IQD)</option>
                            <option value="JOD">Jordanian Dinar (JD)</option>
                            <option value="EGP">Egyptian Pound (EGP)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" id="payment_currency_symbol">$</span>
                            <input type="number" class="form-control" id="payment_amount" name="amount" min="0.01" step="0.01" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="payment_paid_at" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="payment_paid_at" name="paid_at" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="payment_note" class="form-label">Note</label>
                        <textarea class="form-control" id="payment_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Expense Modal (IQD only, super-admin only) -->
<div class="modal fade" id="createExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordExpenseForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2 text-danger"></i>
                        Record Expense
                        <span class="badge bg-secondary ms-2">IQD</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="expense_category" name="category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expense_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expense_description" name="description" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="expense_amount" class="form-label">Amount (IQD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">IQD</span>
                                <input type="number" class="form-control" id="expense_amount" name="amount" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="expense_payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="expense_payment_method" name="payment_method">
                            <option value="">-</option>
                            @foreach($expensePaymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="expense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="expense_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editExpenseForm">
                @csrf
                <input type="hidden" id="edit_expense_id" name="expense_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Expense
                        <span class="badge bg-secondary ms-2">IQD</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_expense_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_expense_category" name="category" required>
                            @foreach($expenseCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_description" class="form-label">Description <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_expense_description" name="description" maxlength="255" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_amount" class="form-label">Amount (IQD) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">IQD</span>
                                <input type="number" class="form-control" id="edit_expense_amount" name="amount" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_expense_date" name="expense_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="edit_expense_payment_method" name="payment_method">
                            <option value="">-</option>
                            @foreach($expensePaymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_expense_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_expense_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
function toggleCustomDates() {
    const period = document.getElementById('period').value;
    const customFromDiv = document.getElementById('customFromDiv');
    const customToDiv = document.getElementById('customToDiv');

    if (period === 'custom') {
        customFromDiv.style.display = 'block';
        customToDiv.style.display = 'block';
    } else {
        customFromDiv.style.display = 'none';
        customToDiv.style.display = 'none';
    }
}

// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($revenueChart['labels']) !!},
        datasets: [
            {
                label: 'Revenue',
                data: {!! json_encode($revenueChart['revenue']) !!},
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            },
            {
                label: 'Expenses',
                data: {!! json_encode($revenueChart['expenses']) !!},
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '{{ $currencySymbol }}' + context.parsed.y.toFixed(2);
                        return label;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '{{ $currencySymbol }}' + value.toFixed(0);
                    }
                }
            }
        }
    }
});

// Currency symbols map
const currencySymbols = {
    'USD': '$',
    'IQD': 'IQD ',
    'JOD': 'JD ',
    'EGP': 'EGP '
};

// Update currency symbol when currency changes (Payment form)
document.getElementById('payment_currency').addEventListener('change', function() {
    const symbol = currencySymbols[this.value] || '$';
    document.getElementById('payment_currency_symbol').textContent = symbol;
});

// Update currency symbol when currency changes (Invoice form)
document.getElementById('invoice_currency').addEventListener('change', function() {
    calculateInvoiceSubtotal();
});

// Invoice Item Management
let itemIndex = 1;

document.getElementById('addInvoiceItem').addEventListener('click', function() {
    const container = document.getElementById('invoiceItemsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'invoice-item row mb-2';
    newItem.innerHTML = `
        <div class="col-md-5">
            <input type="text" class="form-control" name="items[${itemIndex}][description]" placeholder="Description" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" placeholder="Qty" min="1" value="1" required>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control item-price" name="items[${itemIndex}][unit_price]" placeholder="Unit Price" min="0" step="0.01" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newItem);
    itemIndex++;
    updateRemoveButtons();
});

document.getElementById('invoiceItemsContainer').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.invoice-item').remove();
        updateRemoveButtons();
        calculateInvoiceSubtotal();
    }
});

function updateRemoveButtons() {
    const items = document.querySelectorAll('.invoice-item');
    items.forEach((item, index) => {
        const removeBtn = item.querySelector('.remove-item');
        if (items.length === 1) {
            removeBtn.disabled = true;
        } else {
            removeBtn.disabled = false;
        }
    });
}

// Calculate subtotal
document.getElementById('invoiceItemsContainer').addEventListener('input', function(e) {
    if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
        calculateInvoiceSubtotal();
    }
});

function calculateInvoiceSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('.invoice-item').forEach(item => {
        const qty = parseFloat(item.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(item.querySelector('.item-price').value) || 0;
        subtotal += qty * price;
    });

    // Get selected currency
    const currency = document.getElementById('invoice_currency').value || 'USD';
    const symbol = currencySymbols[currency] || '$';

    document.getElementById('invoice_subtotal').value = symbol + subtotal.toFixed(2);
}

// Create Invoice Form
document.getElementById('createInvoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {
        clinic_id: formData.get('clinic_id'),
        currency: formData.get('currency'),
        due_date: formData.get('due_date'),
        tax_rate: formData.get('tax_rate'),
        discount_amount: formData.get('discount_amount'),
        notes: formData.get('notes'),
        items: []
    };

    // Collect items
    document.querySelectorAll('.invoice-item').forEach((item, index) => {
        data.items.push({
            description: formData.get(`items[${index}][description]`),
            quantity: parseFloat(formData.get(`items[${index}][quantity]`)),
            unit_price: parseFloat(formData.get(`items[${index}][unit_price]`))
        });
    });

    fetch('{{ route("master.finance.invoice.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData.get('_token')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert('Invoice created successfully');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error creating invoice: ' + error.message);
        console.error('Full error:', error);
    });
});

// Record Payment Form
document.getElementById('recordPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route("master.finance.payment.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token')
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Payment recorded successfully');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error recording payment');
        console.error(error);
    });
});

// Store payments data for JavaScript access
const paymentsData = @json($recentReceipts);

// Update currency symbol when currency changes (Edit Payment form)
document.getElementById('edit_payment_currency').addEventListener('change', function() {
    const symbol = currencySymbols[this.value] || '$';
    document.getElementById('edit_payment_currency_symbol').textContent = symbol;
});

// Edit Payment
function editPayment(paymentId) {
    const payment = paymentsData.find(p => p.id === paymentId);
    if (!payment) {
        alert('Payment not found');
        return;
    }

    // Populate edit form
    document.getElementById('edit_payment_id').value = payment.id;
    document.getElementById('edit_payment_clinic_id').value = payment.clinic_id;
    document.getElementById('edit_payment_currency').value = payment.currency || 'USD';
    document.getElementById('edit_payment_amount').value = payment.amount;
    document.getElementById('edit_payment_method').value = payment.method || '';
    document.getElementById('edit_payment_paid_at').value = payment.paid_at ? payment.paid_at.split(' ')[0] : '';
    document.getElementById('edit_payment_note').value = payment.note || '';

    // Update currency symbol
    const symbol = currencySymbols[payment.currency || 'USD'] || '$';
    document.getElementById('edit_payment_currency_symbol').textContent = symbol;

    new bootstrap.Modal(document.getElementById('editPaymentModal')).show();
}

// Submit Edit Payment Form
document.getElementById('editPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const paymentId = document.getElementById('edit_payment_id').value;
    const formData = new FormData(this);

    fetch(`/master/finance/payment/${paymentId}/update`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            clinic_id: formData.get('clinic_id'),
            currency: formData.get('currency'),
            amount: formData.get('amount'),
            payment_method: formData.get('payment_method'),
            paid_at: formData.get('paid_at'),
            note: formData.get('note')
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Payment updated successfully');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error updating payment');
        console.error(error);
    });
});

// Delete Payment
function deletePayment(paymentId) {
    if (!confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
        return;
    }

    fetch(`/master/finance/payment/${paymentId}/delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Server error');
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error deleting payment: ' + error.message);
        console.error('Full error:', error);
    });
}

// ===== Master Expenses =====

// Record Expense Form
document.getElementById('recordExpenseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {
        category: formData.get('category'),
        description: formData.get('description'),
        amount: formData.get('amount'),
        expense_date: formData.get('expense_date'),
        payment_method: formData.get('payment_method') || null,
        notes: formData.get('notes') || null,
    };

    fetch('{{ route("master.finance.expense.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData.get('_token')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json().then(j => ({ ok: response.ok, json: j })))
    .then(({ ok, json }) => {
        if (ok && json.success) {
            alert('Expense recorded successfully');
            location.reload();
        } else {
            alert('Error: ' + (json.message || 'Failed to record expense'));
        }
    })
    .catch(error => {
        alert('Error recording expense: ' + error.message);
        console.error(error);
    });
});

// Open Edit Expense modal
function editExpense(expense) {
    document.getElementById('edit_expense_id').value = expense.id;
    document.getElementById('edit_expense_category').value = expense.category;
    document.getElementById('edit_expense_description').value = expense.description;
    document.getElementById('edit_expense_amount').value = expense.amount;
    document.getElementById('edit_expense_date').value = expense.expense_date
        ? String(expense.expense_date).split('T')[0].split(' ')[0]
        : '';
    document.getElementById('edit_expense_payment_method').value = expense.payment_method || '';
    document.getElementById('edit_expense_notes').value = expense.notes || '';
    new bootstrap.Modal(document.getElementById('editExpenseModal')).show();
}

// Submit Edit Expense
document.getElementById('editExpenseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_expense_id').value;
    const formData = new FormData(this);
    const data = {
        category: formData.get('category'),
        description: formData.get('description'),
        amount: formData.get('amount'),
        expense_date: formData.get('expense_date'),
        payment_method: formData.get('payment_method') || null,
        notes: formData.get('notes') || null,
    };

    fetch(`/master/finance/expense/${id}/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData.get('_token')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json().then(j => ({ ok: response.ok, json: j })))
    .then(({ ok, json }) => {
        if (ok && json.success) {
            alert('Expense updated successfully');
            location.reload();
        } else {
            alert('Error: ' + (json.message || 'Failed to update expense'));
        }
    })
    .catch(error => {
        alert('Error updating expense: ' + error.message);
        console.error(error);
    });
});

// Delete Expense
function deleteExpense(id) {
    if (!confirm('Delete this expense? This cannot be undone.')) return;

    fetch(`/master/finance/expense/${id}/delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json().then(j => ({ ok: response.ok, json: j })))
    .then(({ ok, json }) => {
        if (ok && json.success) {
            alert(json.message);
            location.reload();
        } else {
            alert('Error: ' + (json.message || 'Failed to delete expense'));
        }
    })
    .catch(error => {
        alert('Error deleting expense: ' + error.message);
        console.error(error);
    });
}
</script>

<style>
.icon-circle {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.border-left-success {
    border-left: 0.25rem solid #28a745 !important;
}

.border-left-warning {
    border-left: 0.25rem solid #ffc107 !important;
}

.border-left-info {
    border-left: 0.25rem solid #17a2b8 !important;
}

.border-left-primary {
    border-left: 0.25rem solid #007bff !important;
}

.border-left-dark {
    border-left: 0.25rem solid #343a40 !important;
}

.border-left-danger {
    border-left: 0.25rem solid #dc3545 !important;
}
</style>
@endsection
