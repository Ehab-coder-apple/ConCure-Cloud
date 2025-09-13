@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-invoice text-primary"></i>
                    {{ __('Invoices') }}
                </h1>
                <div>
                    <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Finance') }}
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                        <i class="fas fa-plus"></i> {{ __('New Invoice') }}
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.invoices') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">{{ __('Date From') }}</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">{{ __('Date To') }}</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> {{ __('Filter') }}
                                </button>
                                <a href="{{ route('finance.invoices') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> {{ __('Clear') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="card">
                <div class="card-body">
                    @if(isset($invoices) && $invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                        </td>
                                        <td>
                                            @if($invoice->patient)
                                                {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                                            @else
                                                <span class="text-muted">{{ __('No Patient') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : '-' }}</td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                                        <td>
                                            <strong>${{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'sent' => 'info',
                                                    'paid' => 'success',
                                                    'overdue' => 'danger',
                                                    'cancelled' => 'dark'
                                                ];
                                                $color = $statusColors[$invoice->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary btn-sm" title="{{ __('View Invoice') }}"
                                                        onclick="viewInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" title="{{ __('Edit Invoice') }}"
                                                        onclick="editInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="{{ route('finance.invoices.print', $invoice->id) }}" class="btn btn-outline-success btn-sm" title="{{ __('Print Invoice') }}" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="{{ route('finance.invoices.pdf', $invoice->id) }}" class="btn btn-primary btn-sm" title="{{ __('Download PDF') }}">
                                                    <i class="fas fa-file-pdf"></i> {{ __('PDF') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($invoices, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $invoices->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No invoices found') }}</h5>
                            <p class="text-muted">{{ __('Create your first invoice to get started.') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                                <i class="fas fa-plus"></i> {{ __('Create Invoice') }}
                            </button>
                        </div>
                    @endif
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
                <h5 class="modal-title">{{ __('Create New Invoice') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createInvoiceForm" method="POST" action="{{ route('finance.invoices.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select" required data-live-search="true">
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @if(isset($patients) && $patients->count() > 0)
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}"
                                                    data-tokens="{{ $patient->first_name }} {{ $patient->last_name }} {{ $patient->patient_id }}">
                                                {{ $patient->first_name }} {{ $patient->last_name }}
                                                @if($patient->patient_id)
                                                    (ID: {{ $patient->patient_id }})
                                                @endif
                                                @if($patient->phone)
                                                    - {{ $patient->phone }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>{{ __('No patients found') }}</option>
                                    @endif
                                </select>
                                @if(isset($patients) && $patients->count() == 0)
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ __('No active patients found. Please add patients first.') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <!-- Invoice Items -->
                    <div class="mb-3">
                        <label class="form-label">{{ __('Invoice Items') }}</label>
                        <div id="invoice-items">
                            <div class="invoice-item border p-3 mb-2">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" name="items[0][description]" class="form-control" placeholder="{{ __('Description') }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="{{ __('Qty') }}" min="1" value="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="{{ __('Unit Price') }}" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="items[0][item_type]" class="form-select" required>
                                            <option value="consultation">{{ __('Consultation') }}</option>
                                            <option value="procedure">{{ __('Procedure') }}</option>
                                            <option value="medication">{{ __('Medication') }}</option>
                                            <option value="lab_test">{{ __('Lab Test') }}</option>
                                            <option value="other">{{ __('Other') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('Add Item') }}
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" form="createInvoiceForm" class="btn btn-primary">{{ __('Create Invoice') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Invoice') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editInvoiceForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_invoice_id" name="invoice_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select name="patient_id" id="edit_patient_id" class="form-select" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @if(isset($patients) && $patients->count() > 0)
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}">
                                                {{ $patient->first_name }} {{ $patient->last_name }}
                                                @if($patient->patient_id) (ID: {{ $patient->patient_id }}) @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_due_date" class="form-label">{{ __('Due Date') }}</label>
                                <input type="date" class="form-control" id="edit_due_date" name="due_date">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Invoice Items') }}</label>
                        <div id="edit-invoice-items">
                            <!-- Items will be loaded here -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addEditInvoiceItem()">
                            <i class="fas fa-plus"></i> {{ __('Add Item') }}
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Subtotal') }}:</span>
                                        <span id="edit-subtotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Tax') }} (<span id="edit-tax-rate">0</span>%):</span>
                                        <span id="edit-tax-amount">$0.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>{{ __('Total') }}:</span>
                                        <span id="edit-total">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" onclick="showEmailModal()">
                    <i class="fas fa-envelope"></i> {{ __('Email') }}
                </button>
                <button type="button" class="btn btn-info" onclick="printInvoice()">
                    <i class="fas fa-print"></i> {{ __('Print') }}
                </button>
                <button type="submit" form="editInvoiceForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('Update Invoice') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email Invoice Modal -->
<div class="modal fade" id="emailInvoiceModal" tabindex="-1" aria-labelledby="emailInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="emailInvoiceModalLabel">
                    <i class="fas fa-envelope me-2"></i>{{ __('Email Invoice') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="emailInvoiceForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email-recipient" class="form-label">{{ __('Recipient Email') }}</label>
                        <input type="email" class="form-control" id="email-recipient" name="email" required>
                        <div class="form-text">{{ __('Email address where the invoice will be sent') }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="email-subject" class="form-label">{{ __('Subject') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <input type="text" class="form-control" id="email-subject" name="subject"
                               placeholder="{{ __('Invoice from') }} {{ auth()->user()->clinic->name ?? 'Clinic' }}">
                    </div>

                    <div class="mb-3">
                        <label for="email-message" class="form-label">{{ __('Custom Message') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                        <textarea class="form-control" id="email-message" name="message" rows="4"
                                  placeholder="{{ __('Add a personal message to include with the invoice...') }}"></textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="attach-pdf" name="attach_pdf" checked>
                            <label class="form-check-label" for="attach-pdf">
                                {{ __('Attach PDF invoice') }}
                            </label>
                            <div class="form-text">{{ __('Include a PDF copy of the invoice as an attachment') }}</div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('Invoice Details:') }}</strong>
                        <div id="email-invoice-details" class="mt-2">
                            <!-- Invoice details will be populated here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('Send Email') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@push('styles')
<style>
/* Hide number input spinners */
.no-spinners::-webkit-outer-spin-button,
.no-spinners::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.no-spinners[type=number] {
    -moz-appearance: textfield;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;
    
    // Add new invoice item
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('invoice-items');
        const newItem = document.querySelector('.invoice-item').cloneNode(true);
        
        // Update input names with new index
        newItem.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', `[${itemIndex}]`));
                input.value = input.type === 'number' && input.placeholder === 'Qty' ? '1' : '';
            }
        });
        
        container.appendChild(newItem);
        itemIndex++;
    });
    
    // Remove invoice item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const items = document.querySelectorAll('.invoice-item');
            if (items.length > 1) {
                e.target.closest('.invoice-item').remove();
            }
        }
    });

    // Invoice View and Edit Functions
    window.viewInvoice = function(invoiceId) {
        // Open invoice in print view (read-only)
        window.open(`/finance/invoices/${invoiceId}/print`, '_blank');
    };

    window.editInvoice = function(invoiceId) {
        // Fetch invoice data and open edit modal
        fetch(`/finance/invoices/${invoiceId}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEditModal(data.invoice);
                new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
            } else {
                alert('{{ __("Error loading invoice data:") }} ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error loading invoice data. Please try again.") }}');
        });
    };

    // Populate edit modal with invoice data
    function populateEditModal(invoice) {
        document.getElementById('edit_invoice_id').value = invoice.id;
        document.getElementById('edit_patient_id').value = invoice.patient_id;
        document.getElementById('edit_due_date').value = invoice.due_date;
        document.getElementById('edit_notes').value = invoice.notes || '';

        // Clear existing items
        const itemsContainer = document.getElementById('edit-invoice-items');
        itemsContainer.innerHTML = '';

        // Add invoice items
        invoice.items.forEach((item, index) => {
            addEditInvoiceItem(item, index);
        });

        // Update totals
        calculateEditTotals();
    }

    // Add invoice item to edit modal
    window.addEditInvoiceItem = function(item = null, index = null) {
        const container = document.getElementById('edit-invoice-items');
        const itemIndex = index !== null ? index : container.children.length;

        const itemHtml = `
            <div class="invoice-item border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <input type="text" class="form-control" name="items[${itemIndex}][description]"
                               value="${item ? item.description : ''}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Type') }}</label>
                        <select class="form-select" name="items[${itemIndex}][item_type]">
                            <option value="consultation" ${item && item.item_type === 'consultation' ? 'selected' : ''}>Consultation</option>
                            <option value="procedure" ${item && item.item_type === 'procedure' ? 'selected' : ''}>Procedure</option>
                            <option value="medication" ${item && item.item_type === 'medication' ? 'selected' : ''}>Medication</option>
                            <option value="lab_test" ${item && item.item_type === 'lab_test' ? 'selected' : ''}>Lab Test</option>
                            <option value="other" ${!item || item.item_type === 'other' ? 'selected' : ''}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">{{ __('Qty') }}</label>
                        <input type="text" class="form-control no-spinners quantity-input" name="items[${itemIndex}][quantity]"
                               value="${item ? item.quantity : '1'}" pattern="[0-9]+" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('Unit Price') }}</label>
                        <input type="number" class="form-control price-input" name="items[${itemIndex}][unit_price]"
                               value="${item ? item.unit_price : ''}" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ __('Total') }}</label>
                        <input type="text" class="form-control item-total" readonly
                               value="${item ? (item.quantity * item.unit_price).toFixed(2) : '0.00'}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', itemHtml);
        calculateEditTotals();
    };

    // Event delegation for remove buttons and input changes in edit modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const button = e.target.closest('.remove-item-btn');
            console.log('Remove button clicked', button);

            const items = document.querySelectorAll('#edit-invoice-items .invoice-item');
            console.log('Total items:', items.length);

            if (items.length > 1) {
                const itemToRemove = button.closest('.invoice-item');
                console.log('Item to remove:', itemToRemove);

                if (itemToRemove) {
                    itemToRemove.remove();
                    calculateEditTotals();
                    console.log('Item removed successfully');
                } else {
                    console.error('Could not find invoice-item parent');
                }
            } else {
                alert('{{ __("Cannot remove the last item. At least one item is required.") }}');
            }
        }
    });

    // Event delegation for input changes (quantity and price)
    document.addEventListener('input', function(e) {
        if (e.target.matches('.quantity-input')) {
            // Filter out non-numeric characters for quantity
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
            calculateEditTotals();
        } else if (e.target.matches('.price-input')) {
            calculateEditTotals();
        }
    });

    // Also handle change events as backup
    document.addEventListener('change', function(e) {
        if (e.target.matches('.quantity-input') || e.target.matches('.price-input')) {
            calculateEditTotals();
        }
    });

    // Remove invoice item from edit modal (keeping for backward compatibility)
    window.removeEditInvoiceItem = function(button) {
        console.log('Remove button clicked via function', button);
        const items = document.querySelectorAll('#edit-invoice-items .invoice-item');
        console.log('Total items:', items.length);

        if (items.length > 1) {
            const itemToRemove = button.closest('.invoice-item');
            console.log('Item to remove:', itemToRemove);

            if (itemToRemove) {
                itemToRemove.remove();
                calculateEditTotals();
                console.log('Item removed successfully');
            } else {
                console.error('Could not find invoice-item parent');
            }
        } else {
            alert('{{ __("Cannot remove the last item. At least one item is required.") }}');
        }
    };

    // Print invoice function
    window.printInvoice = function() {
        const invoiceIdInput = document.querySelector('#editInvoiceModal input[name="invoice_id"]');
        if (invoiceIdInput && invoiceIdInput.value) {
            const invoiceId = invoiceIdInput.value;
            const printUrl = `{{ route('finance.invoices.print', ':id') }}`.replace(':id', invoiceId);
            window.open(printUrl, '_blank');
        } else {
            alert('{{ __("Please save the invoice first before printing.") }}');
        }
    };

    // Show email modal function
    window.showEmailModal = function() {
        const invoiceIdInput = document.querySelector('#editInvoiceModal input[name="invoice_id"]');
        if (invoiceIdInput && invoiceIdInput.value) {
            const invoiceId = invoiceIdInput.value;

            // Fetch invoice details for email modal
            fetch(`{{ route('finance.invoices.email-form', ':id') }}`.replace(':id', invoiceId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate email form
                        document.getElementById('email-recipient').value = data.invoice.patient_email || '';
                        document.getElementById('email-subject').value = `Invoice ${data.invoice.invoice_number} from {{ auth()->user()->clinic->name ?? 'Clinic' }}`;

                        // Populate invoice details
                        document.getElementById('email-invoice-details').innerHTML = `
                            <div><strong>{{ __('Invoice:') }}</strong> ${data.invoice.invoice_number}</div>
                            <div><strong>{{ __('Patient:') }}</strong> ${data.invoice.patient_name}</div>
                            <div><strong>{{ __('Amount:') }}</strong> $${parseFloat(data.invoice.total_amount).toFixed(2)}</div>
                            <div><strong>{{ __('Status:') }}</strong> <span class="badge bg-${data.invoice.status === 'paid' ? 'success' : (data.invoice.status === 'overdue' ? 'danger' : 'warning')}">${data.invoice.status.charAt(0).toUpperCase() + data.invoice.status.slice(1)}</span></div>
                        `;

                        // Store invoice ID for sending
                        document.getElementById('emailInvoiceForm').dataset.invoiceId = invoiceId;

                        // Show email modal
                        new bootstrap.Modal(document.getElementById('emailInvoiceModal')).show();
                    } else {
                        alert('{{ __("Error loading invoice details.") }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('{{ __("Error loading invoice details.") }}');
                });
        } else {
            alert('{{ __("Please save the invoice first before emailing.") }}');
        }
    };

    // Email form submission
    document.getElementById('emailInvoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const invoiceId = this.dataset.invoiceId;
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;

        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Sending...") }}';

        // Send email
        fetch(`{{ route('finance.invoices.email', ':id') }}`.replace(':id', invoiceId), {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.status === 419) {
                // CSRF token expired - show user-friendly message
                alert('{{ __("Your session has expired. Please refresh the page and try again.") }}');
                if (confirm('{{ __("Would you like to refresh the page now?") }}')) {
                    window.location.reload();
                }
                throw new Error('CSRF token expired');
            }
            return response;
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                alert('{{ __("Invoice sent successfully!") }}');

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('emailInvoiceModal')).hide();

                // Reset form
                document.getElementById('emailInvoiceForm').reset();

                // Refresh invoice list if needed
                location.reload();
            } else {
                alert('{{ __("Error:") }} ' + (data.message || 'Unknown error occurred'));
                console.error('Server error:', data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Network error. Please check your connection and try again.") }}');
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    });

    // Calculate totals for edit modal
    function calculateEditTotals() {
        const items = document.querySelectorAll('#edit-invoice-items .invoice-item');
        let subtotal = 0;

        items.forEach(item => {
            const quantityInput = item.querySelector('input[name*="[quantity]"]');
            const unitPriceInput = item.querySelector('input[name*="[unit_price]"]');
            const totalInput = item.querySelector('.item-total');

            // Get values and ensure they're valid numbers
            const quantity = Math.max(0, parseInt(quantityInput.value) || 0);
            const unitPrice = Math.max(0, parseFloat(unitPriceInput.value) || 0);
            const total = quantity * unitPrice;

            // Update the total field for this item
            totalInput.value = total.toFixed(2);
            subtotal += total;
        });

        // Calculate tax and final total
        const taxRate = 0; // You can make this configurable
        const taxAmount = subtotal * (taxRate / 100);
        const finalTotal = subtotal + taxAmount;

        // Update the summary section
        const subtotalElement = document.getElementById('edit-subtotal');
        const taxRateElement = document.getElementById('edit-tax-rate');
        const taxAmountElement = document.getElementById('edit-tax-amount');
        const totalElement = document.getElementById('edit-total');

        if (subtotalElement) subtotalElement.textContent = '$' + subtotal.toFixed(2);
        if (taxRateElement) taxRateElement.textContent = taxRate;
        if (taxAmountElement) taxAmountElement.textContent = '$' + taxAmount.toFixed(2);
        if (totalElement) totalElement.textContent = '$' + finalTotal.toFixed(2);
    }

    // Handle edit form submission
    document.getElementById('editInvoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const invoiceId = document.getElementById('edit_invoice_id').value;
        const formData = new FormData(this);

        // Add method spoofing for PUT request
        formData.append('_method', 'PUT');

        fetch(`/finance/invoices/${invoiceId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __("Invoice updated successfully!") }}');
                bootstrap.Modal.getInstance(document.getElementById('editInvoiceModal')).hide();
                location.reload(); // Refresh the page to show updated data
            } else {
                alert('{{ __("Error updating invoice:") }} ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error updating invoice. Please try again.") }}');
        });
    });

});
</script>
@endpush
