@extends('layouts.app')

@section('title', __('Invoice :number', ['number' => $aestheticInvoice->invoice_number]))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                        {{ __('Invoice') }} {{ $aestheticInvoice->invoice_number }}
                    </h1>
                    <p class="text-muted mb-0">{{ $aestheticInvoice->invoice_date->format('M d, Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('aesthetic.invoices.receipt', $aestheticInvoice) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-print me-1"></i>{{ __('Print') }}
                    </a>
                    <a href="{{ route('aesthetic.invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                    </a>
                </div>
            </div>

            <!-- Status Banner -->
            <div class="alert alert-{{ $aestheticInvoice->status_color }} d-flex justify-content-between align-items-center mb-4">
                <div>
                    <span class="badge bg-white text-dark me-2 fs-6">{{ $aestheticInvoice->status_display }}</span>
                    <span class="text-white">{{ __('Status') }}</span>
                </div>
                <div class="text-white">
                    @if($aestheticInvoice->status === 'paid')
                        <i class="fas fa-check-circle me-1"></i>{{ __('Paid on :date', ['date' => $aestheticInvoice->paid_at?->format('M d, Y') ?? 'N/A']) }}
                    @elseif($aestheticInvoice->status === 'partial')
                        <i class="fas fa-hourglass-half me-1"></i>{{ __('Balance Due: ') }} {{ number_format($aestheticInvoice->balance, 2) }}
                    @elseif($aestheticInvoice->status === 'overdue')
                        <i class="fas fa-exclamation-circle me-1"></i>{{ __('Overdue since :date', ['date' => $aestheticInvoice->due_date?->format('M d, Y') ?? 'N/A']) }}
                    @endif
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>{{ __('Patient') }}</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $aestheticInvoice->patient->first_name }} {{ $aestheticInvoice->patient->last_name }}</h5>
                            <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $aestheticInvoice->patient->phone ?? '-' }}</p>
                            <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $aestheticInvoice->patient->email ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('Invoice Details') }}</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>{{ __('Invoice #') }}:</strong> {{ $aestheticInvoice->invoice_number }}</p>
                            <p class="mb-1"><strong>{{ __('Date') }}:</strong> {{ $aestheticInvoice->invoice_date->format('M d, Y') }}</p>
                            @if($aestheticInvoice->due_date)
                                <p class="mb-1"><strong>{{ __('Due Date') }}:</strong> {{ $aestheticInvoice->due_date->format('M d, Y') }}</p>
                            @endif
                            <p class="mb-1"><strong>{{ __('Created By') }}:</strong> {{ $aestheticInvoice->creator?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>{{ __('Items') }}</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th class="text-center">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Unit Price') }}</th>
                                <th class="text-end">{{ __('Discount') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aestheticInvoice->items as $item)
                            <tr>
                                <td>
                                    {{ $item->description }}
                                    @if($item->treatment)
                                        <br><small class="text-muted">{{ $item->treatment->name }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">{{ number_format($item->discount, 2) }}</td>
                                <td class="text-end"><strong>{{ number_format($item->total_price, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>{{ __('Subtotal') }}</strong></td>
                                <td class="text-end"><strong>{{ number_format($aestheticInvoice->subtotal, 2) }}</strong></td>
                            </tr>
                            @if($aestheticInvoice->tax_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end">{{ __('Tax') }} ({{ number_format($aestheticInvoice->tax_rate, 2) }}%)</td>
                                <td class="text-end">{{ number_format($aestheticInvoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($aestheticInvoice->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="text-end text-danger">{{ __('Discount') }}</td>
                                <td class="text-end text-danger">-{{ number_format($aestheticInvoice->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end fs-5"><strong>{{ __('Total') }}</strong></td>
                                <td class="text-end fs-5"><strong>{{ number_format($aestheticInvoice->total_amount, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">{{ __('Paid') }}</td>
                                <td class="text-end text-success">{{ number_format($aestheticInvoice->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fs-5"><strong>{{ __('Balance') }}</strong></td>
                                <td class="text-end fs-5">
                                    <strong class="{{ $aestheticInvoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($aestheticInvoice->balance, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($aestheticInvoice->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>{{ __('Notes') }}</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $aestheticInvoice->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Payment & Actions -->
            @if(!in_array($aestheticInvoice->status, ['paid', 'cancelled']))
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>{{ __('Record Payment') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('aesthetic.invoices.mark-paid', $aestheticInvoice) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Amount') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $clinicCurrency ?? '$' }}</span>
                                    <input type="number" step="0.01" min="0.01" max="{{ $aestheticInvoice->balance }}"
                                           class="form-control" name="amount" value="{{ number_format($aestheticInvoice->balance, 2) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Payment Method') }}</label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Payment Date') }}</label>
                                <input type="date" class="form-control" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i>{{ __('Record Payment') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between gap-2">
                <div>
                    @if($aestheticInvoice->status === 'draft')
                        <form method="POST" action="{{ route('aesthetic.invoices.send', $aestheticInvoice) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info me-2">
                                <i class="fas fa-paper-plane me-1"></i>{{ __('Send') }}
                            </button>
                        </form>
                    @endif
                    @if(!in_array($aestheticInvoice->status, ['paid', 'cancelled']))
                        <form method="POST" action="{{ route('aesthetic.invoices.cancel', $aestheticInvoice) }}" class="d-inline"
                              onsubmit="return confirm('{{ __('Cancel this invoice?') }}')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-ban me-1"></i>{{ __('Cancel') }}
                            </button>
                        </form>
                    @endif
                </div>
                <form method="POST" action="{{ route('aesthetic.invoices.destroy', $aestheticInvoice) }}" class="d-inline"
                      onsubmit="return confirm('{{ __('Delete this invoice?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-trash-alt me-1"></i>{{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
