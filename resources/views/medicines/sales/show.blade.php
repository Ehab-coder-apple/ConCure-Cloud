@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>
                        {{ __('Sale Invoice') }} — {{ $invoice->invoice_number }}
                    </h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-print me-1"></i>{{ __('Print') }}
                        </button>
                        <a href="{{ route('medicines.sales.create') }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-cash-register me-1"></i>{{ __('New Sale') }}
                        </a>
                        <a href="{{ route('medicines.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Date') }}:</strong>
                            {{ $invoice->sold_at->format('M d, Y H:i') }}
                            <br>
                            <strong>{{ __('Cashier') }}:</strong>
                            {{ $invoice->user->full_name ?? $invoice->user->username ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Patient') }}:</strong>
                            @if($invoice->patient)
                                {{ trim(($invoice->patient->first_name ?? '') . ' ' . ($invoice->patient->last_name ?? '')) }}
                            @else
                                <span class="text-muted">{{ __('Walk-in') }}</span>
                            @endif
                            <br>
                            <strong>{{ __('Payment') }}:</strong>
                            <span class="badge bg-info text-dark">{{ __(ucfirst($invoice->payment_method ?? '-')) }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Medicine') }}</th>
                                    <th class="text-end">{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Unit Price') }}</th>
                                    <th class="text-end">{{ __('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $i => $item)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            {{ $item->medicine->name ?? __('Unknown') }}
                                            @if($item->medicine && $item->medicine->dosage)
                                                <span class="text-muted small">— {{ $item->medicine->dosage }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                                        <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ number_format((float) $item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            @if($invoice->notes)
                                <strong>{{ __('Notes') }}:</strong>
                                <p class="text-muted mb-0">{{ $invoice->notes }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>{{ __('Subtotal') }}</th>
                                    <td class="text-end">{{ number_format((float) $invoice->subtotal, 2) }}</td>
                                </tr>
                                @if((float) $invoice->discount > 0)
                                    <tr>
                                        <th>{{ __('Discount') }}</th>
                                        <td class="text-end">- {{ number_format((float) $invoice->discount, 2) }}</td>
                                    </tr>
                                @endif
                                @if((float) $invoice->tax > 0)
                                    <tr>
                                        <th>{{ __('Tax') }}</th>
                                        <td class="text-end">{{ number_format((float) $invoice->tax, 2) }}</td>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <th>{{ __('Grand Total') }}</th>
                                    <td class="text-end fw-bold">{{ number_format((float) $invoice->total, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Paid') }}</th>
                                    <td class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }}</td>
                                </tr>
                                @php $balance = (float) $invoice->total - (float) $invoice->paid_amount; @endphp
                                @if(abs($balance) > 0.001)
                                    <tr class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                        <th>{{ $balance > 0 ? __('Balance Due') : __('Change') }}</th>
                                        <td class="text-end fw-bold">{{ number_format(abs($balance), 2) }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
