@extends('layouts.app')

@push('styles')
<style>
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

    /* Action buttons styling */
    .btn-group .btn {
        margin-right: 1px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
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
                                        <th>{{ __('Remaining') }}</th>
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
                                            <strong>{{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->total_amount ?? 0, 2), '0'), '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'sent' => 'info',
                                                    'paid' => 'success',
                                                    'partial_paid' => 'warning',
                                                    'overdue' => 'danger',
                                                    'cancelled' => 'dark'
                                                ];
                                                $color = $statusColors[$invoice->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ $invoice->status === 'partial_paid' ? 'Partially Paid' : ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($invoice->status === 'partial_paid' || ($invoice->paid_amount > 0 && $invoice->balance > 0))
                                                <span class="text-danger fw-bold">
                                                    {{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->balance ?? 0, 2), '0'), '.') }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    Paid: {{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->paid_amount ?? 0, 2), '0'), '.') }}
                                                </small>
                                            @elseif($invoice->status === 'paid')
                                                <span class="text-success">
                                                    <i class="fas fa-check-circle"></i> {{ __('Fully Paid') }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-xs btn-outline-primary" title="{{ __('View Invoice') }}"
                                                        onclick="viewInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary" title="{{ __('Edit Invoice') }}"
                                                        onclick="editInvoice({{ $invoice->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="{{ route('finance.invoices.print', $invoice->id) }}" class="btn btn-xs btn-outline-success" title="{{ __('Print Invoice') }}" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="{{ route('finance.invoices.pdf', $invoice->id) }}" class="btn btn-xs btn-primary" title="{{ __('Download PDF') }}">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>

                                                <!-- Status Change Buttons -->
                                                @if($invoice->status === 'draft')
                                                    <form method="POST" action="{{ route('finance.invoices.mark-sent', $invoice) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-info"
                                                                title="{{ __('Mark as Sent') }}"
                                                                onclick="return confirm('{{ __('Mark this invoice as sent?') }}')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($invoice->status, ['draft', 'sent', 'overdue', 'partial_paid']))
                                                    <button type="button" class="btn btn-xs btn-outline-success"
                                                            title="{{ $invoice->status === 'partial_paid' ? __('Add Payment') : __('Mark as Paid') }}"
                                                            onclick="showMarkAsPaidModal({{ $invoice->id }}, '{{ $invoice->invoice_number }}', {{ $invoice->balance }})">
                                                        <i class="fas fa-{{ $invoice->status === 'partial_paid' ? 'plus-circle' : 'check-circle' }}"></i>
                                                    </button>
                                                @endif

                                                @if(in_array($invoice->status, ['draft', 'sent', 'overdue', 'partial_paid']))
                                                    <form method="POST" action="{{ route('finance.invoices.mark-cancelled', $invoice) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline-warning"
                                                                title="{{ __('Cancel Invoice') }}"
                                                                onclick="return confirm('{{ __('Are you sure you want to cancel this invoice?') }}')">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Delete Button -->
                                                @if(auth()->user()->hasPermission('finance_delete') || $invoice->created_by === auth()->id())
                                                    @if($invoice->status !== 'paid')
                                                        <form method="POST" action="{{ route('finance.invoices.destroy', $invoice) }}" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-outline-danger"
                                                                    title="{{ __('Delete') }}"
                                                                    onclick="return confirm('{{ __('Are you sure you want to delete this invoice? This action cannot be undone.') }}')">
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

            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-spa text-primary me-2"></i>{{ __('Aesthetic Department Invoices') }}
                        </h5>
                        <small class="text-muted">{{ __('Aesthetic invoices are now visible here for finance follow-up and payment review.') }}</small>
                    </div>
                    <a href="{{ route('aesthetic.invoices.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>{{ __('Open Aesthetic Invoices') }}
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($aestheticInvoices) && $aestheticInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Session') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Due Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Remaining') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($aestheticInvoices as $invoice)
                                        <tr>
                                            <td>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">{{ __('Aesthetic') }}</span>
                                            </td>
                                            <td>
                                                @if($invoice->patient)
                                                    {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                                                @else
                                                    <span class="text-muted">{{ __('No Patient') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($invoice->session)
                                                    <div class="fw-semibold">{{ $invoice->session->session_context }}</div>
                                                    <small class="text-muted">{{ __('Session #:number', ['number' => $invoice->session->session_number]) }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : '-' }}</td>
                                            <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                                            <td>
                                                <strong>{{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->total_amount ?? 0, 2), '0'), '.') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status_color }}">
                                                    {{ $invoice->status_display }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(($invoice->balance ?? 0) > 0)
                                                    <span class="text-danger fw-bold">
                                                        {{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->balance ?? 0, 2), '0'), '.') }}
                                                    </span>
                                                    @if(($invoice->paid_amount ?? 0) > 0)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ __('Paid: :amount', ['amount' => ($currencySymbol ?? '$') . rtrim(rtrim(number_format($invoice->paid_amount ?? 0, 2), '0'), '.')]) }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i> {{ __('Fully Paid') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-inline-flex gap-2 flex-wrap">
                                                    <a href="{{ route('aesthetic.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View Invoice') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="{{ __('Print') }}">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="{{ route('aesthetic.invoices.invoice', [$invoice, 'auto' => 1]) }}" target="_blank">{{ __('Regular Invoice') }}</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="{{ route('aesthetic.invoices.thermal-receipt', [$invoice, 'width' => 80]) }}" target="_blank">{{ __('Thermal Receipt 80mm') }}</a></li>
                                                            <li><a class="dropdown-item" href="{{ route('aesthetic.invoices.thermal-receipt', [$invoice, 'width' => 58]) }}" target="_blank">{{ __('Thermal Receipt 58mm') }}</a></li>
                                                        </ul>
                                                    </div>
                                                    @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                                        <a href="{{ route('aesthetic.invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-info" title="{{ __('Edit Invoice') }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($aestheticInvoices, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $aestheticInvoices->appends(request()->except('aesthetic_page'))->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice-dollar fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">{{ __('No aesthetic invoices found') }}</h6>
                            <p class="text-muted mb-3">{{ __('Aesthetic invoices will appear here so finance staff can review them without leaving the finance page.') }}</p>
                            <a href="{{ route('aesthetic.invoices.create') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>{{ __('Create Aesthetic Invoice') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-4" id="medicine-sales-section">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-pills text-success me-2"></i>{{ __('Medicine Sales') }}
                        </h5>
                        <small class="text-muted">{{ __('Completed medicine sale invoices are listed here for finance review.') }}</small>
                    </div>
                    @if(auth()->user()->canAccessModule('medicines'))
                    <a href="{{ route('medicines.sales.create') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-plus me-1"></i>{{ __('New Medicine Sale') }}
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(isset($medicineSaleInvoices) && $medicineSaleInvoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Cashier') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Remaining') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medicineSaleInvoices as $invoice)
                                        @php
                                            $remaining = max(0, (float) $invoice->total - (float) $invoice->paid_amount);
                                            $isPaid = $remaining <= 0.009;
                                            $isPartial = !$isPaid && (float) $invoice->paid_amount > 0;
                                        @endphp
                                        <tr data-invoice-row="{{ $invoice->id }}">
                                            <td>
                                                <strong>{{ $invoice->invoice_number }}</strong>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-2">{{ __('Medicine') }}</span>
                                            </td>
                                            <td>
                                                @if($invoice->patient)
                                                    {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}
                                                @else
                                                    <span class="text-muted">{{ __('Walk-in') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $invoice->user->full_name ?? $invoice->user->username ?? '—' }}</td>
                                            <td>{{ $invoice->sold_at ? $invoice->sold_at->format('M d, Y H:i') : '-' }}</td>
                                            <td id="invoice-amount-{{ $invoice->id }}">
                                                <strong class="js-invoice-total">{{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($invoice->total ?? 0, 2), '0'), '.') }}</strong>
                                                @if(($invoice->paid_amount ?? 0) > 0)
                                                    <br>
                                                    <small class="text-muted js-invoice-paid">{{ __('Paid: :amount', ['amount' => ($currencySymbol ?? '$') . rtrim(rtrim(number_format($invoice->paid_amount ?? 0, 2), '0'), '.')]) }}</small>
                                                @endif
                                            </td>
                                            <td id="invoice-status-{{ $invoice->id }}">
                                                @if($invoice->status === 'cancelled')
                                                    <span class="badge bg-dark">{{ __('Cancelled') }}</span>
                                                @elseif($invoice->status === 'returned')
                                                    <span class="badge bg-info text-dark">{{ __('Returned') }}</span>
                                                @elseif($invoice->status === 'partially_returned')
                                                    <span class="badge bg-info text-dark">{{ __('Partially Returned') }}</span>
                                                @elseif($isPaid)
                                                    <span class="badge bg-success">{{ __('Paid') }}</span>
                                                @elseif($isPartial)
                                                    <span class="badge bg-warning text-dark">{{ __('Partial Paid') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unpaid') }}</span>
                                                @endif
                                            </td>
                                            <td id="invoice-remaining-{{ $invoice->id }}">
                                                @if($remaining > 0)
                                                    <span class="text-danger fw-semibold">{{ $currencySymbol ?? '$' }}{{ rtrim(rtrim(number_format($remaining, 2), '0'), '.') }}</span>
                                                @else
                                                    <span class="text-success"><i class="fas fa-check-circle"></i> {{ __('Fully Paid') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-inline-flex gap-2 flex-wrap align-items-center">
                                                    <a href="{{ route('medicines.sales.show', $invoice) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View Sale') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('medicines.sales.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Download PDF') }}">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="{{ route('medicines.sales.thermal', ['invoice' => $invoice, 'width' => 80]) }}" target="_blank" class="btn btn-sm btn-outline-success" title="{{ __('Print Receipt') }}">
                                                        <i class="fas fa-print"></i>
                                                    </a>

                                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isClinicAdmin() || auth()->user()->role === 'pharmacist')
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" {{ $invoice->isMutable() ? '' : 'disabled' }}>
                                                                {{ __('Actions') }}
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item js-open-return-modal" href="#" data-invoice-id="{{ $invoice->id }}">
                                                                        <i class="fas fa-undo me-2"></i>{{ __('Return Items') }}
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item js-open-edit-modal" href="#" data-invoice-id="{{ $invoice->id }}">
                                                                        <i class="fas fa-edit me-2"></i>{{ __('Edit Invoice') }}
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger js-open-cancel-modal" href="#" data-invoice-id="{{ $invoice->id }}">
                                                                        <i class="fas fa-ban me-2"></i>{{ __('Cancel Invoice') }}
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($medicineSaleInvoices, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $medicineSaleInvoices->appends(request()->except('medicine_page'))->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-pills fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">{{ __('No medicine sales found') }}</h6>
                            <p class="text-muted mb-3">{{ __('Completed medicine sale invoices will appear here for finance tracking.') }}</p>
                            @if(auth()->user()->canAccessModule('medicines'))
                            <a href="{{ route('medicines.sales.create') }}" class="btn btn-outline-success">
                                <i class="fas fa-plus me-1"></i>{{ __('Create Medicine Sale') }}
                            </a>
                            @endif
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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_amount" class="form-label">{{ __('Payment Amount') }} ({{ $currencySymbol ?? '$' }})</label>
                            <input type="number" name="payment_amount" id="payment_amount" class="form-control"
                                   step="0.01" min="0" value="0" placeholder="{{ __('Amount paid now') }}">
                            <div class="form-text text-muted">{{ __('Leave 0 if no payment at creation') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">{{ __('Payment Method') }}</label>
                            <select name="payment_method" id="payment_method" class="form-select">
                                <option value="cash" selected>{{ __('Cash') }}</option>
                                <option value="card">{{ __('Credit/Debit Card') }}</option>
                                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                <option value="check">{{ __('Check') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
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

                    <!-- Payment Tracking Section -->
                    <div class="card mb-3 bg-light">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill-wave"></i> {{ __('Payment Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_payment_amount" class="form-label">{{ __('Payment Amount') }}</label>
                                        <input type="number" class="form-control" id="edit_payment_amount" name="payment_amount"
                                               step="0.01" min="0" placeholder="0.00">
                                        <div class="form-text">{{ __('Enter amount to record a payment') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_payment_method" class="form-label">{{ __('Payment Method') }}</label>
                                        <select class="form-select" id="edit_payment_method" name="payment_method">
                                            <option value="">{{ __('Select Method') }}</option>
                                            <option value="cash">{{ __('Cash') }}</option>
                                            <option value="card">{{ __('Card') }}</option>
                                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                            <option value="check">{{ __('Check') }}</option>
                                            <option value="other">{{ __('Other') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="edit_payment_date" class="form-label">{{ __('Payment Date') }}</label>
                                        <input type="date" class="form-control" id="edit_payment_date" name="payment_date"
                                               value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('Current Balance: ') }}<strong id="edit_current_balance">{{ $currencySymbol ?? '$' }}0.00</strong>
                                            <span class="ms-3">{{ __('Total Paid: ') }}<strong id="edit_total_paid">{{ $currencySymbol ?? '$' }}0.00</strong></span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                        <span id="edit-subtotal">{{ $currencySymbol ?? '$' }}0</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('Tax') }} (<span id="edit-tax-rate">0</span>%):</span>
                                        <span id="edit-tax-amount">{{ $currencySymbol ?? '$' }}0</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>{{ __('Total') }}:</span>
                                        <span id="edit-total">{{ $currencySymbol ?? '$' }}0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-success" onclick="showWhatsAppShare()" style="background-color:#25D366;border-color:#25D366;">
                    <i class="fab fa-whatsapp"></i> {{ __('WhatsApp') }}
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

<!-- WhatsApp Invoice Modal -->
<div class="modal fade" id="whatsappInvoiceModal" tabindex="-1" aria-labelledby="whatsappInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color:#25D366;color:#fff;">
                <h5 class="modal-title" id="whatsappInvoiceModalLabel">
                    <i class="fab fa-whatsapp me-2"></i>{{ __('Send Invoice via WhatsApp') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="whatsappInvoiceForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="whatsapp-recipient" class="form-label">{{ __('Recipient WhatsApp Number') }}</label>
                        <input type="tel" class="form-control" id="whatsapp-recipient" name="phone"
                               placeholder="9647XXXXXXXXX" required>
                        <div class="form-text">{{ __('International format without + or leading zeros (e.g. 9647XXXXXXXXX).') }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="whatsapp-message" class="form-label">{{ __('Message') }}</label>
                        <textarea class="form-control" id="whatsapp-message" name="message" rows="6"></textarea>
                        <div class="form-text">{{ __('A link to the invoice PDF will be appended automatically.') }}</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('Invoice Details:') }}</strong>
                        <div id="whatsapp-invoice-details" class="mt-2">
                            <!-- Invoice details will be populated here -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success" style="background-color:#25D366;border-color:#25D366;">
                        <i class="fab fa-whatsapp me-2"></i>{{ __('Open WhatsApp') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- ─── Medicine Sale: Return / Edit / Cancel modals ─── -->
<div class="modal fade" id="medicineReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-undo me-2"></i>{{ __('Return Items') }} <small class="text-muted" id="return-invoice-number"></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="return-modal-alert" class="alert alert-danger d-none"></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Medicine') }}</th>
                                <th>{{ __('Sold Qty') }}</th>
                                <th>{{ __('Remaining') }}</th>
                                <th style="width:140px;">{{ __('Return Qty') }}</th>
                            </tr>
                        </thead>
                        <tbody id="return-items-body"></tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Reason for Return') }} <span class="text-danger">*</span></label>
                    <select class="form-select" id="return-reason-code">
                        <option value="">{{ __('Select a reason...') }}</option>
                        <option value="expired_medication">{{ __('Expired Medication') }}</option>
                        <option value="wrong_medication_dispensed">{{ __('Wrong Medication Dispensed') }}</option>
                        <option value="patient_cancelled_refused">{{ __('Patient Cancelled/Refused') }}</option>
                        <option value="defective_product">{{ __('Defective Product') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Notes (optional)') }}</label>
                    <textarea class="form-control" id="return-notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-danger" id="submit-return-btn" disabled>
                    <i class="fas fa-undo me-1"></i>{{ __('Submit Return') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="medicineEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>{{ __('Edit Invoice') }} <small class="text-muted" id="edit-invoice-number"></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="edit-modal-alert" class="alert alert-danger d-none"></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Medicine') }}</th>
                                <th style="width:120px;">{{ __('Quantity') }}</th>
                                <th style="width:140px;">{{ __('Unit Price') }}</th>
                                <th>{{ __('Line Total') }}</th>
                            </tr>
                        </thead>
                        <tbody id="edit-items-body"></tbody>
                    </table>
                </div>
                <small class="text-muted">{{ __('Set a quantity of 0 to remove a line entirely. Stock is adjusted automatically by the difference.') }}</small>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Discount') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit-discount">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Tax') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit-tax">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Paid Amount') }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit-paid-amount">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="submit-edit-btn">
                    <i class="fas fa-save me-1"></i>{{ __('Save Changes') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="medicineCancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>{{ __('Cancel Invoice') }} <small class="text-muted" id="cancel-invoice-number"></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="cancel-modal-alert" class="alert alert-danger d-none"></div>
                <p class="text-muted">{{ __('This voids the invoice entirely and returns all remaining (not already returned) items back into stock. This cannot be undone.') }}</p>
                <div class="mb-3">
                    <label class="form-label">{{ __('Cancellation Reason') }} <span class="text-danger">*</span></label>
                    <select class="form-select" id="cancel-reason-code">
                        <option value="">{{ __('Select a reason...') }}</option>
                        <option value="expired_medication">{{ __('Expired Medication') }}</option>
                        <option value="wrong_medication_dispensed">{{ __('Wrong Medication Dispensed') }}</option>
                        <option value="patient_cancelled_refused">{{ __('Patient Cancelled/Refused') }}</option>
                        <option value="defective_product">{{ __('Defective Product') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Notes (optional)') }}</label>
                    <textarea class="form-control" id="cancel-notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-danger" id="submit-cancel-btn" disabled>
                    <i class="fas fa-ban me-1"></i>{{ __('Confirm Cancellation') }}
                </button>
            </div>
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
// Currency symbol for JavaScript
const currencySymbol = '{{ $currencySymbol ?? "$" }}';
// Format amount: show decimals only if needed
function formatAmount(value) {
    const n = parseFloat(value) || 0;
    const s = (Math.round(n * 100) / 100).toFixed(2);
    return s.replace(/\.?0+$/, '');
}

document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 1;

    // Initialize Select2 for patient dropdown with smart search
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("Select Patient") }}',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#createInvoiceModal'),
        language: {
            noResults: function() {
                return '{{ __("No patients found") }}';
            },
            searching: function() {
                return '{{ __("Searching...") }}';
            }
        }
    });

    // Initialize Select2 for edit patient dropdown
    $('#edit_patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: '{{ __("Select Patient") }}',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#editInvoiceModal'),
        language: {
            noResults: function() {
                return '{{ __("No patients found") }}';
            },
            searching: function() {
                return '{{ __("Searching...") }}';
            }
        }
    });

    // Reset Select2 when modal is closed
    $('#createInvoiceModal').on('hidden.bs.modal', function () {
        $('#patient_id').val(null).trigger('change');
    });

    $('#editInvoiceModal').on('hidden.bs.modal', function () {
        $('#edit_patient_id').val(null).trigger('change');
    });

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

        // Set patient using Select2
        $('#edit_patient_id').val(invoice.patient_id).trigger('change');

        document.getElementById('edit_due_date').value = invoice.due_date;
        document.getElementById('edit_notes').value = invoice.notes || '';

        // Update payment information
        const totalAmount = parseFloat(invoice.total_amount) || 0;
        const balance = parseFloat(invoice.balance) || 0;
        const totalPaid = totalAmount - balance;

        document.getElementById('edit_current_balance').textContent = currencySymbol + formatAmount(balance);
        document.getElementById('edit_total_paid').textContent = currencySymbol + formatAmount(totalPaid);

        // Clear payment fields
        document.getElementById('edit_payment_amount').value = '';
        document.getElementById('edit_payment_method').value = '';
        document.getElementById('edit_payment_date').value = new Date().toISOString().split('T')[0];

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
                               value="${item ? formatAmount(item.quantity * item.unit_price) : '0'}">
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

    // Normalise a phone number into the digits-only E.164 form expected by wa.me.
    function normalizeWhatsAppPhone(raw) {
        let p = (raw || '').replace(/[^0-9]/g, '');
        if (p.startsWith('00')) p = p.substring(2);
        if (!p.startsWith('964') && p.length === 10) p = '964' + p;
        return p;
    }

    // Build the default WhatsApp message body from an invoice payload.
    function buildWhatsAppInvoiceMessage(inv, publicUrl) {
        const clinicName = '{{ auth()->user()->clinic->name ?? "Clinic" }}';
        const statusLabel = (inv.status || '').charAt(0).toUpperCase() + (inv.status || '').slice(1);
        const lines = [
            '🧾 {{ __("Invoice") }}',
            '',
            `👤 {{ __("Patient") }}: ${inv.patient_name || ''}`,
            `📄 {{ __("Invoice #") }}: ${inv.invoice_number || ''}`,
            `💰 {{ __("Amount") }}: ${currencySymbol}${formatAmount(inv.total_amount || 0)}`,
            `📌 {{ __("Status") }}: ${statusLabel}`,
        ];
        if (publicUrl) {
            lines.push('');
            lines.push(`📎 {{ __("PDF") }}: ${publicUrl}`);
        }
        lines.push('');
        lines.push('📱 {{ __("Generated by ConCure Clinic Management System") }}');
        return lines.join('\n');
    }

    // Show WhatsApp share modal for the currently-edited invoice.
    window.showWhatsAppShare = function() {
        const invoiceIdInput = document.querySelector('#editInvoiceModal input[name="invoice_id"]');
        if (!invoiceIdInput || !invoiceIdInput.value) {
            alert('{{ __("Please save the invoice first before sharing.") }}');
            return;
        }
        const invoiceId = invoiceIdInput.value;

        // Fetch invoice details + public PDF URL in parallel
        Promise.all([
            fetch(`{{ route('finance.invoices.email-form', ':id') }}`.replace(':id', invoiceId)).then(r => r.json()),
            fetch(`{{ route('finance.invoices.public-pdf-url', ':id') }}`.replace(':id', invoiceId)).then(r => r.json()).catch(() => ({ success: false })),
        ])
        .then(([formData, urlData]) => {
            if (!formData.success) {
                alert('{{ __("Error loading invoice details.") }}');
                return;
            }
            const inv = formData.invoice;
            const publicUrl = (urlData && urlData.success) ? urlData.public_url : '';

            document.getElementById('whatsapp-recipient').value = normalizeWhatsAppPhone(inv.patient_whatsapp || '');
            document.getElementById('whatsapp-message').value = buildWhatsAppInvoiceMessage(inv, publicUrl);

            const statusClass = inv.status === 'paid' ? 'success' : (inv.status === 'overdue' ? 'danger' : 'warning');
            const statusLabel = (inv.status || '').charAt(0).toUpperCase() + (inv.status || '').slice(1);
            document.getElementById('whatsapp-invoice-details').innerHTML = `
                <div><strong>{{ __('Invoice:') }}</strong> ${inv.invoice_number}</div>
                <div><strong>{{ __('Patient:') }}</strong> ${inv.patient_name}</div>
                <div><strong>{{ __('Amount:') }}</strong> ${currencySymbol}${formatAmount(inv.total_amount)}</div>
                <div><strong>{{ __('Status:') }}</strong> <span class="badge bg-${statusClass}">${statusLabel}</span></div>
            `;

            document.getElementById('whatsappInvoiceForm').dataset.invoiceId = invoiceId;
            new bootstrap.Modal(document.getElementById('whatsappInvoiceModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("Error loading invoice details.") }}');
        });
    };

    // WhatsApp form submission: open wa.me (or wa.me composer if phone empty) in a new tab.
    document.getElementById('whatsappInvoiceForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const phone = normalizeWhatsAppPhone(document.getElementById('whatsapp-recipient').value);
        const message = document.getElementById('whatsapp-message').value;
        const encoded = encodeURIComponent(message);

        const url = phone
            ? `https://wa.me/${phone}?text=${encoded}`
            : `https://wa.me/?text=${encoded}`;

        window.open(url, '_blank');

        bootstrap.Modal.getInstance(document.getElementById('whatsappInvoiceModal')).hide();
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
            totalInput.value = formatAmount(total);
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

        if (subtotalElement) subtotalElement.textContent = currencySymbol + formatAmount(subtotal);
        if (taxRateElement) taxRateElement.textContent = taxRate;
        if (taxAmountElement) taxAmountElement.textContent = currencySymbol + formatAmount(taxAmount);
        if (totalElement) totalElement.textContent = currencySymbol + formatAmount(finalTotal);
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

    // Show Mark as Paid Modal
    window.showMarkAsPaidModal = function(invoiceId, invoiceNumber, balance) {
        document.getElementById('mark-paid-invoice-id').value = invoiceId;
        document.getElementById('mark-paid-invoice-number').textContent = invoiceNumber;
        document.getElementById('mark-paid-balance').textContent = currencySymbol + formatAmount(balance);
        document.getElementById('paid_amount').value = parseFloat(balance).toFixed(2);
        document.getElementById('paid_amount').max = parseFloat(balance).toFixed(2);

        const modal = new bootstrap.Modal(document.getElementById('markAsPaidModal'));
        modal.show();
    };

});
</script>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const currencySymbol = @json($currencySymbol ?? '$');
    let activeInvoiceId = null;
    let activeItems = [];

    function fmt(n) {
        return currencySymbol + (Math.round(n * 100) / 100).toFixed(2).replace(/\.00$/, '');
    }

    function fetchItems(invoiceId) {
        return fetch(`/medicines/sales/${invoiceId}/items`, {
            headers: { 'Accept': 'application/json' },
        }).then(r => r.json());
    }

    function showAlert(elId, message) {
        const el = document.getElementById(elId);
        el.textContent = message;
        el.classList.remove('d-none');
    }

    function hideAlert(elId) {
        document.getElementById(elId).classList.add('d-none');
    }

    // ── Live badge/total patch on the ledger row, no page reload ──
    function patchInvoiceRow(invoice) {
        const amountCell = document.getElementById(`invoice-amount-${invoice.id}`);
        const statusCell = document.getElementById(`invoice-status-${invoice.id}`);
        const remainingCell = document.getElementById(`invoice-remaining-${invoice.id}`);

        if (amountCell) {
            amountCell.querySelector('.js-invoice-total').textContent = fmt(invoice.total);
            const paidEl = amountCell.querySelector('.js-invoice-paid');
            if (paidEl) paidEl.textContent = '{{ __("Paid") }}: ' + fmt(invoice.paid_amount);
        }

        if (statusCell) {
            let badge = '<span class="badge bg-secondary">{{ __("Unpaid") }}</span>';
            if (invoice.status === 'cancelled') {
                badge = '<span class="badge bg-dark">{{ __("Cancelled") }}</span>';
            } else if (invoice.status === 'returned') {
                badge = '<span class="badge bg-info text-dark">{{ __("Returned") }}</span>';
            } else if (invoice.status === 'partially_returned') {
                badge = '<span class="badge bg-info text-dark">{{ __("Partially Returned") }}</span>';
            } else if (invoice.balance <= 0.009) {
                badge = '<span class="badge bg-success">{{ __("Paid") }}</span>';
            } else if (invoice.paid_amount > 0) {
                badge = '<span class="badge bg-warning text-dark">{{ __("Partial Paid") }}</span>';
            }
            statusCell.innerHTML = badge;
        }

        if (remainingCell) {
            remainingCell.innerHTML = invoice.balance > 0
                ? `<span class="text-danger fw-semibold">${fmt(invoice.balance)}</span>`
                : `<span class="text-success"><i class="fas fa-check-circle"></i> {{ __('Fully Paid') }}</span>`;
        }

        // Disable the Actions dropdown toggle once the invoice is voided.
        if (invoice.status === 'cancelled') {
            const row = document.querySelector(`tr[data-invoice-row="${invoice.id}"]`);
            if (row) {
                const toggle = row.querySelector('.dropdown-toggle');
                if (toggle) toggle.setAttribute('disabled', 'disabled');
            }
        }
    }

    function postJson(url, method, body) {
        return fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        }).then(async (r) => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(data.message || '{{ __('Request failed.') }}');
            return data;
        });
    }

    // ── Return Items modal ──
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.js-open-return-modal');
        if (!trigger) return;
        e.preventDefault();

        activeInvoiceId = trigger.dataset.invoiceId;
        hideAlert('return-modal-alert');
        document.getElementById('return-reason-code').value = '';
        document.getElementById('return-notes').value = '';
        document.getElementById('submit-return-btn').disabled = true;
        document.getElementById('return-items-body').innerHTML = `<tr><td colspan="4" class="text-center text-muted">{{ __('Loading...') }}</td></tr>`;

        const modal = new bootstrap.Modal(document.getElementById('medicineReturnModal'));
        modal.show();

        fetchItems(activeInvoiceId).then(({ invoice, items }) => {
            document.getElementById('return-invoice-number').textContent = `#${invoice.invoice_number}`;
            activeItems = items;

            const rows = items
                .filter(i => i.remaining_returnable > 0)
                .map(i => `
                    <tr data-transaction-id="${i.transaction_id}" data-max="${i.remaining_returnable}">
                        <td>${i.medicine_name}${i.medicine_dosage ? ' - ' + i.medicine_dosage : ''}</td>
                        <td>${i.quantity}</td>
                        <td>${i.remaining_returnable}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm js-return-qty"
                                   min="0" max="${i.remaining_returnable}" step="0.01" value="0">
                        </td>
                    </tr>
                `).join('');

            document.getElementById('return-items-body').innerHTML = rows
                || `<tr><td colspan="4" class="text-center text-muted">{{ __('Nothing left to return on this invoice.') }}</td></tr>`;
        });
    });

    function updateReturnSubmitState() {
        const reasonSelected = document.getElementById('return-reason-code').value !== '';
        document.getElementById('submit-return-btn').disabled = !reasonSelected;
    }
    document.getElementById('return-reason-code').addEventListener('change', updateReturnSubmitState);

    document.getElementById('submit-return-btn').addEventListener('click', function () {
        hideAlert('return-modal-alert');
        const reasonCode = document.getElementById('return-reason-code').value;
        if (!reasonCode) return;

        const items = Array.from(document.querySelectorAll('#return-items-body tr[data-transaction-id]'))
            .map(row => ({
                transaction_id: parseInt(row.dataset.transactionId, 10),
                quantity: parseFloat(row.querySelector('.js-return-qty').value || '0'),
            }))
            .filter(i => i.quantity > 0);

        if (items.length === 0) {
            showAlert('return-modal-alert', '{{ __('Enter a quantity greater than 0 for at least one item.') }}');
            return;
        }

        postJson(`/medicines/sales/${activeInvoiceId}/returns`, 'POST', {
            reason_code: reasonCode,
            notes: document.getElementById('return-notes').value || null,
            items,
        }).then(({ invoice }) => {
            patchInvoiceRow(invoice);
            bootstrap.Modal.getInstance(document.getElementById('medicineReturnModal')).hide();
        }).catch(err => showAlert('return-modal-alert', err.message));
    });

    // ── Edit Invoice modal ──
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.js-open-edit-modal');
        if (!trigger) return;
        e.preventDefault();

        activeInvoiceId = trigger.dataset.invoiceId;
        hideAlert('edit-modal-alert');
        document.getElementById('edit-items-body').innerHTML = `<tr><td colspan="4" class="text-center text-muted">{{ __('Loading...') }}</td></tr>`;

        const modal = new bootstrap.Modal(document.getElementById('medicineEditModal'));
        modal.show();

        fetchItems(activeInvoiceId).then(({ invoice, items }) => {
            document.getElementById('edit-invoice-number').textContent = `#${invoice.invoice_number}`;
            document.getElementById('edit-discount').value = invoice.discount;
            document.getElementById('edit-tax').value = invoice.tax;
            document.getElementById('edit-paid-amount').value = invoice.paid_amount;

            document.getElementById('edit-items-body').innerHTML = items.map(i => `
                <tr data-transaction-id="${i.transaction_id}">
                    <td>${i.medicine_name}${i.medicine_dosage ? ' - ' + i.medicine_dosage : ''}</td>
                    <td><input type="number" class="form-control form-control-sm js-edit-qty" min="0" step="0.01" value="${i.quantity}"></td>
                    <td><input type="number" class="form-control form-control-sm js-edit-price" min="0" step="0.01" value="${i.unit_price}"></td>
                    <td class="js-edit-line-total">${fmt(i.total_amount)}</td>
                </tr>
            `).join('');
        });
    });

    document.getElementById('submit-edit-btn').addEventListener('click', function () {
        hideAlert('edit-modal-alert');

        const items = Array.from(document.querySelectorAll('#edit-items-body tr[data-transaction-id]'))
            .map(row => ({
                transaction_id: parseInt(row.dataset.transactionId, 10),
                quantity: parseFloat(row.querySelector('.js-edit-qty').value || '0'),
                unit_price: parseFloat(row.querySelector('.js-edit-price').value || '0'),
            }));

        postJson(`/medicines/sales/${activeInvoiceId}`, 'PUT', {
            items,
            discount: parseFloat(document.getElementById('edit-discount').value || '0'),
            tax: parseFloat(document.getElementById('edit-tax').value || '0'),
            paid_amount: parseFloat(document.getElementById('edit-paid-amount').value || '0'),
        }).then(({ invoice }) => {
            patchInvoiceRow(invoice);
            bootstrap.Modal.getInstance(document.getElementById('medicineEditModal')).hide();
        }).catch(err => showAlert('edit-modal-alert', err.message));
    });

    // ── Cancel Invoice modal ──
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.js-open-cancel-modal');
        if (!trigger) return;
        e.preventDefault();

        activeInvoiceId = trigger.dataset.invoiceId;
        hideAlert('cancel-modal-alert');
        document.getElementById('cancel-reason-code').value = '';
        document.getElementById('cancel-notes').value = '';
        document.getElementById('submit-cancel-btn').disabled = true;

        fetchItems(activeInvoiceId).then(({ invoice }) => {
            document.getElementById('cancel-invoice-number').textContent = `#${invoice.invoice_number}`;
        });

        new bootstrap.Modal(document.getElementById('medicineCancelModal')).show();
    });

    document.getElementById('cancel-reason-code').addEventListener('change', function () {
        document.getElementById('submit-cancel-btn').disabled = this.value === '';
    });

    document.getElementById('submit-cancel-btn').addEventListener('click', function () {
        hideAlert('cancel-modal-alert');
        const reasonCode = document.getElementById('cancel-reason-code').value;
        if (!reasonCode) return;

        postJson(`/medicines/sales/${activeInvoiceId}/cancel`, 'POST', {
            reason_code: reasonCode,
            notes: document.getElementById('cancel-notes').value || null,
        }).then(({ invoice }) => {
            patchInvoiceRow(invoice);
            bootstrap.Modal.getInstance(document.getElementById('medicineCancelModal')).hide();
        }).catch(err => showAlert('cancel-modal-alert', err.message));
    });
})();
</script>

@endpush

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" aria-labelledby="markAsPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAsPaidModalLabel">{{ __('Mark Invoice as Paid') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markAsPaidForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="mark-paid-invoice-id" name="invoice_id">

                    <div class="mb-3">
                        <strong>{{ __('Invoice:') }}</strong> <span id="mark-paid-invoice-number"></span><br>
                        <strong>{{ __('Balance Due:') }}</strong> <span id="mark-paid-balance"></span>
                    </div>

                    <div class="mb-3">
                        <label for="paid_amount" class="form-label">{{ __('Payment Amount') }}</label>
                        <input type="number" class="form-control" id="paid_amount" name="paid_amount"
                               step="0.01" min="0" required>
                        <div class="form-text">{{ __('Enter the amount received for this invoice.') }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">{{ __('Payment Method') }}</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash" selected>{{ __('Cash') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                            <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                            <option value="check">{{ __('Check') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('Mark as Paid') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle Mark as Paid form submission
document.getElementById('markAsPaidForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const invoiceId = document.getElementById('mark-paid-invoice-id').value;
    const formData = new FormData(this);

    // Set the form action dynamically
    this.action = `/finance/invoices/${invoiceId}/mark-paid`;

    // Submit the form normally (not AJAX since we want to redirect)
    this.submit();
});
</script>

