@extends('master.layouts.app')

@section('title', 'Subscription Payments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-receipt text-success me-2"></i>
                Subscription Payments
            </h1>
            <p class="text-muted mb-0">Clinic subscription payments (excludes demo clinics).</p>
        </div>
        <div>
            <a href="{{ route('master.finance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Finance
            </a>
            <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#createReceiptModal">
                <i class="fas fa-plus me-1"></i> Record Payment
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.finance.payments') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="clinic_id" class="form-label">Clinic</label>
                    <select class="form-select" id="clinic_id" name="clinic_id">
                        <option value="">All clinics</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}" {{ (string)($filters['clinic_id'] ?? '') === (string)$clinic->id ? 'selected' : '' }}>
                                {{ $clinic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="city" class="form-label">City</label>
                    <select class="form-select" id="city" name="city">
                        <option value="">All cities</option>
                        @foreach(($cityOptions ?? []) as $city)
                            <option value="{{ $city }}" {{ ($filters['city'] ?? '') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="currency" class="form-label">Currency</label>
                    <select class="form-select" id="currency" name="currency">
                        <option value="">All</option>
                        @foreach(['USD' => 'USD', 'IQD' => 'IQD', 'JOD' => 'JOD', 'EGP' => 'EGP'] as $code => $label)
                            <option value="{{ $code }}" {{ ($filters['currency'] ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from" class="form-label">From</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label for="to" class="form-label">To</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100" title="Apply">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
            @if(array_filter($filters ?? []))
                <div class="mt-2">
                    <a href="{{ route('master.finance.payments') }}" class="text-muted small">
                        <i class="fas fa-times me-1"></i> Clear filters
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <i class="fas fa-info-circle me-2 text-info"></i>
            Showing <strong>{{ $payments->total() }}</strong> payment(s) for the current filter.
        </div>
        <div>
            @if(empty($totalsByCurrency))
                <span class="text-muted">No totals.</span>
            @else
                @foreach($totalsByCurrency as $currency => $total)
                    @php $sym = App\Models\MasterInvoice::getCurrencySymbolStatic($currency); @endphp
                    <span class="badge bg-success me-1">{{ $sym }} {{ number_format((float)$total, 2) }} <small class="opacity-75">{{ $currency }}</small></span>
                @endforeach
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Clinic</th>
                                <th>City</th>
                                <th>Method</th>
                                <th>Notes</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php $sym = App\Models\MasterInvoice::getCurrencySymbolStatic($payment->currency ?? 'USD'); @endphp
                                <tr>
                                    <td>{{ optional($payment->paid_at)->format('M d, Y') }}</td>
                                    <td>{{ optional($payment->clinic)->name ?: '—' }}</td>
                                    <td>
                                        @if($payment->city)
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-map-marker-alt me-1 text-muted"></i>{{ $payment->city }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->method ? ucfirst(str_replace('_', ' ', $payment->method)) : '-' }}</td>
                                    <td><small class="text-muted">{{ $payment->notes }}</small></td>
                                    <td class="text-end fw-semibold text-success">
                                        {{ $sym }} {{ number_format((float)$payment->amount, 2) }}
                                        <small class="text-muted d-block">{{ $payment->currency }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick='editPaymentRow(@json($payment))' title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deletePayment({{ $payment->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No payments match the current filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('master.finance._payment-modals')
@endsection
