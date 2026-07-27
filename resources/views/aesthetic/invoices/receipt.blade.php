<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Receipt') }} - {{ $aestheticInvoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 13px; }
        .receipt-container { max-width: 80mm; margin: 0 auto; padding: 10px; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .receipt-container { max-width: 100%; padding: 5px; }
        }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="text-center mb-3">
            <h4 class="mb-1">{{ config('app.name', 'ConCure') }}</h4>
            <small class="text-muted">{{ __('Aesthetic Treatment Receipt') }}</small>
        </div>

        <div class="divider"></div>

        <p class="mb-1"><strong>{{ __('Invoice') }}:</strong> {{ $aestheticInvoice->invoice_number }}</p>
        <p class="mb-1"><strong>{{ __('Date') }}:</strong> {{ $aestheticInvoice->invoice_date->format('M d, Y') }}</p>
        @if($aestheticInvoice->due_date)
            <p class="mb-1"><strong>{{ __('Due') }}:</strong> {{ $aestheticInvoice->due_date->format('M d, Y') }}</p>
        @endif

        <div class="divider"></div>

        <!-- Patient -->
        <p class="mb-1"><strong>{{ __('Patient') }}:</strong></p>
        <p class="mb-1">{{ $aestheticInvoice->patient->first_name }} {{ $aestheticInvoice->patient->last_name }}</p>
        @if($aestheticInvoice->patient->phone)
            <p class="mb-1">{{ $aestheticInvoice->patient->phone }}</p>
        @endif

        <div class="divider"></div>

        <!-- Items -->
        <table class="table table-sm mb-2" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th class="text-center">{{ __('Qty') }}</th>
                    <th class="text-end">{{ __('Amt') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aestheticInvoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Totals -->
        <p class="mb-1 d-flex justify-content-between"><span>{{ __('Subtotal') }}</span><span>{{ number_format($aestheticInvoice->subtotal, 2) }}</span></p>
        @if($aestheticInvoice->tax_amount > 0)
            <p class="mb-1 d-flex justify-content-between"><span>{{ __('Tax') }} ({{ number_format($aestheticInvoice->tax_rate, 0) }}%)</span><span>{{ number_format($aestheticInvoice->tax_amount, 2) }}</span></p>
        @endif
        @if($aestheticInvoice->discount_amount > 0)
            <p class="mb-1 d-flex justify-content-between"><span>{{ __('Discount') }}</span><span>-{{ number_format($aestheticInvoice->discount_amount, 2) }}</span></p>
        @endif
        <p class="mb-1 d-flex justify-content-between fw-bold"><span>{{ __('Total') }}</span><span>{{ number_format($aestheticInvoice->total_amount, 2) }}</span></p>
        <p class="mb-1 d-flex justify-content-between"><span>{{ __('Paid') }}</span><span>{{ number_format($aestheticInvoice->paid_amount, 2) }}</span></p>
        @if($aestheticInvoice->balance > 0)
            <p class="mb-1 d-flex justify-content-between fw-bold"><span>{{ __('Balance') }}</span><span>{{ number_format($aestheticInvoice->balance, 2) }}</span></p>
        @else
            <p class="mb-1 text-center text-success fw-bold">{{ __('PAID IN FULL') }}</p>
        @endif

        <div class="divider"></div>

        <!-- Footer -->
        <div class="text-center">
            <p class="mb-0 small text-muted">{{ __('Thank you for choosing us!') }}</p>
            <p class="mb-0 small text-muted">{{ config('app.name', 'ConCure') }}</p>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="fas fa-print me-1"></i>{{ __('Print') }}
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-secondary ms-2">
                {{ __('Close') }}
            </button>
        </div>
    </div>
</body>
</html>
