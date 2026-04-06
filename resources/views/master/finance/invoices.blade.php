@extends('layouts.master')

@section('title', 'Master Invoices')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice me-2"></i>
            Master Invoices
        </h1>
        <a href="{{ route('master.finance.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Clinic</label>
                    <select name="clinic_id" class="form-select">
                        <option value="">All Clinics</option>
                        @foreach(App\Models\Clinic::where('is_demo', false)->orderBy('name')->get() as $clinic)
                            <option value="{{ $clinic->id }}" {{ request('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                {{ $clinic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0 text-primary">
                <i class="fas fa-list me-2"></i>
                All Invoices ({{ $invoices->total() }})
            </h6>
        </div>
        <div class="card-body p-0">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Clinic</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                @php
                                    $symbol = $invoice->getCurrencySymbol();
                                @endphp
                                <tr>
                                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                    <td>{{ $invoice->clinic ? $invoice->clinic->name : 'N/A' }}</td>
                                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $symbol }}{{ number_format($invoice->total_amount, 2) }}</td>
                                    <td class="text-success">{{ $symbol }}{{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td class="text-danger">{{ $symbol }}{{ number_format($invoice->balance, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                                            {{ strtoupper($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('master.finance.invoice.print', $invoice) }}" class="btn btn-info" target="_blank" title="Print">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button type="button" class="btn btn-success" onclick="openRecordPaymentModal({{ $invoice->id }})" title="Record Payment">
                                                <i class="fas fa-dollar-sign"></i>
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="editInvoice({{ $invoice->id }})" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" onclick="deleteInvoice({{ $invoice->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $invoices->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No invoices found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editInvoiceForm">
                @csrf
                <input type="hidden" id="edit_invoice_id" name="invoice_id">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Edit Invoice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_invoice_clinic_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_invoice_clinic_id" name="clinic_id" required>
                                <option value="">Select Clinic</option>
                                @foreach(App\Models\Clinic::where('is_demo', false)->orderBy('name')->get() as $clinic)
                                    <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_invoice_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_invoice_currency" name="currency" required>
                                <option value="USD">US Dollar ($)</option>
                                <option value="IQD">Iraqi Dinar (IQD)</option>
                                <option value="JOD">Jordanian Dinar (JD)</option>
                                <option value="EGP">Egyptian Pound (EGP)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_invoice_due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="edit_invoice_due_date" name="due_date" required>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-3">
                        <label class="form-label">Invoice Items <span class="text-danger">*</span></label>
                        <div id="editInvoiceItemsContainer"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addEditInvoiceItem">
                            <i class="fas fa-plus me-1"></i> Add Item
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_invoice_tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="edit_invoice_tax_rate" name="tax_rate" min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_invoice_discount" class="form-label">Discount Amount</label>
                            <input type="number" class="form-control" id="edit_invoice_discount" name="discount_amount" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control" id="edit_invoice_subtotal" readonly value="$0.00">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_invoice_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_invoice_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordInvoicePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recordInvoicePaymentForm">
                @csrf
                <input type="hidden" id="invoice_payment_id" name="invoice_id">
                <div class="modal-header">
                    <h5 class="modal-title">Record Invoice Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Invoice:</strong> <span id="invoice_number_display"></span><br>
                        <strong>Total:</strong> <span id="invoice_total_display"></span><br>
                        <strong>Paid:</strong> <span id="invoice_paid_display"></span><br>
                        <strong>Balance:</strong> <span id="invoice_balance_display"></span>
                    </div>

                    <div class="mb-3">
                        <label for="invoice_payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="invoice_payment_amount" name="amount" min="0.01" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="invoice_payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="invoice_payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="check">Check</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="invoice_payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="invoice_payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
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

<script>
// Store invoices data for JavaScript access
const invoicesData = @json($invoices->count() > 0 ? $invoices->items() : []);

function openRecordPaymentModal(invoiceId) {
    const invoice = invoicesData.find(inv => inv.id === invoiceId);
    if (!invoice) return;

    const symbol = invoice.currency === 'USD' ? '$' : (invoice.currency === 'IQD' ? 'IQD ' : (invoice.currency === 'JOD' ? 'JD ' : 'EGP '));

    document.getElementById('invoice_payment_id').value = invoiceId;
    document.getElementById('invoice_number_display').textContent = invoice.invoice_number;
    document.getElementById('invoice_total_display').textContent = symbol + parseFloat(invoice.total_amount).toFixed(2);
    document.getElementById('invoice_paid_display').textContent = symbol + parseFloat(invoice.paid_amount).toFixed(2);
    document.getElementById('invoice_balance_display').textContent = symbol + parseFloat(invoice.balance).toFixed(2);

    // Set max amount to balance
    document.getElementById('invoice_payment_amount').max = invoice.balance;
    document.getElementById('invoice_payment_amount').value = invoice.balance;

    new bootstrap.Modal(document.getElementById('recordInvoicePaymentModal')).show();
}

// Record Invoice Payment Form
document.getElementById('recordInvoicePaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const invoiceId = document.getElementById('invoice_payment_id').value;
    const formData = new FormData(this);

    fetch(`/master/finance/invoice/${invoiceId}/record-payment`, {
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

function editInvoice(invoiceId) {
    const invoice = invoicesData.find(inv => inv.id === invoiceId);
    if (!invoice) return;

    // Populate edit form
    document.getElementById('edit_invoice_id').value = invoice.id;
    document.getElementById('edit_invoice_clinic_id').value = invoice.clinic_id;
    document.getElementById('edit_invoice_currency').value = invoice.currency;
    document.getElementById('edit_invoice_due_date').value = invoice.due_date;
    document.getElementById('edit_invoice_tax_rate').value = invoice.tax_rate || 0;
    document.getElementById('edit_invoice_discount').value = invoice.discount_amount || 0;
    document.getElementById('edit_invoice_notes').value = invoice.notes || '';

    // Clear existing items
    document.getElementById('editInvoiceItemsContainer').innerHTML = '';

    // Add invoice items
    if (invoice.items && invoice.items.length > 0) {
        invoice.items.forEach((item, index) => {
            addEditInvoiceItem(item.description, item.quantity, item.unit_price, index);
        });
    } else {
        addEditInvoiceItem('', 1, 0, 0);
    }

    editItemIndex = invoice.items ? invoice.items.length : 1;
    updateEditRemoveButtons();
    calculateEditInvoiceSubtotal();

    new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
}

function deleteInvoice(invoiceId) {
    if (!confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
        return;
    }

    const invoice = invoicesData.find(inv => inv.id === invoiceId);

    fetch(`/master/finance/invoice/${invoiceId}/delete`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error deleting invoice');
        console.error(error);
    });
}

// Edit Invoice Item Management
let editItemIndex = 1;

const currencySymbols = {
    'USD': '$',
    'IQD': 'IQD ',
    'JOD': 'JD ',
    'EGP': 'EGP '
};

function addEditInvoiceItem(description = '', quantity = 1, price = 0, index = null) {
    const idx = index !== null ? index : editItemIndex++;
    const container = document.getElementById('editInvoiceItemsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'edit-invoice-item row mb-2';
    newItem.innerHTML = `
        <div class="col-md-5">
            <input type="text" class="form-control" name="items[${idx}][description]" placeholder="Description" value="${description}" required>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control edit-item-quantity" name="items[${idx}][quantity]" placeholder="Qty" min="1" value="${quantity}" required>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control edit-item-price" name="items[${idx}][unit_price]" placeholder="Unit Price" min="0" step="0.01" value="${price}" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-sm edit-remove-item">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(newItem);
}

document.getElementById('addEditInvoiceItem').addEventListener('click', function() {
    addEditInvoiceItem();
    updateEditRemoveButtons();
});

document.getElementById('editInvoiceItemsContainer').addEventListener('click', function(e) {
    if (e.target.closest('.edit-remove-item')) {
        e.target.closest('.edit-invoice-item').remove();
        updateEditRemoveButtons();
        calculateEditInvoiceSubtotal();
    }
});

function updateEditRemoveButtons() {
    const items = document.querySelectorAll('.edit-invoice-item');
    items.forEach((item) => {
        const removeBtn = item.querySelector('.edit-remove-item');
        if (items.length === 1) {
            removeBtn.disabled = true;
        } else {
            removeBtn.disabled = false;
        }
    });
}

// Update currency symbol when currency changes (Edit Invoice form)
document.getElementById('edit_invoice_currency').addEventListener('change', function() {
    calculateEditInvoiceSubtotal();
});

// Calculate subtotal for edit form
document.getElementById('editInvoiceItemsContainer').addEventListener('input', function(e) {
    if (e.target.classList.contains('edit-item-quantity') || e.target.classList.contains('edit-item-price')) {
        calculateEditInvoiceSubtotal();
    }
});

function calculateEditInvoiceSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('.edit-invoice-item').forEach(item => {
        const qty = parseFloat(item.querySelector('.edit-item-quantity').value) || 0;
        const price = parseFloat(item.querySelector('.edit-item-price').value) || 0;
        subtotal += qty * price;
    });

    const currency = document.getElementById('edit_invoice_currency').value || 'USD';
    const symbol = currencySymbols[currency] || '$';

    document.getElementById('edit_invoice_subtotal').value = symbol + subtotal.toFixed(2);
}

// Submit Edit Invoice Form
document.getElementById('editInvoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const invoiceId = document.getElementById('edit_invoice_id').value;
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
    document.querySelectorAll('.edit-invoice-item').forEach((item, index) => {
        const inputs = item.querySelectorAll('input');
        data.items.push({
            description: inputs[0].value,
            quantity: parseFloat(inputs[1].value),
            unit_price: parseFloat(inputs[2].value)
        });
    });

    fetch(`/master/finance/invoice/${invoiceId}/update`, {
        method: 'PUT',
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
            alert('Invoice updated successfully');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error updating invoice: ' + error.message);
        console.error('Full error:', error);
    });
});
</script>
@endsection
