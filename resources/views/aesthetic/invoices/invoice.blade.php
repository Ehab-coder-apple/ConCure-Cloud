<!DOCTYPE html>
@php
    $invoice = $aestheticInvoice;
    $clinic = $invoice->clinic;
    $currency = DB::table('settings')->where('clinic_id', $invoice->clinic_id)->where('key', 'currency')->value('value') ?? 'USD';
    $paymentPercentage = $invoice->total_amount > 0 ? min(100, ($invoice->paid_amount / $invoice->total_amount) * 100) : 0;
    $logoSrc = $clinic?->id ? \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($clinic->id) : null;
@endphp
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Aesthetic Invoice') }} - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; margin: 0; background: #f3f4f6; }
        .toolbar { position: sticky; top: 0; background: #fff; border-bottom: 1px solid #ddd; padding: 10px; text-align: center; }
        .toolbar button, .toolbar a { padding: 8px 14px; border-radius: 4px; border: 1px solid #ccc; background: #f8f9fa; color: #222; text-decoration: none; cursor: pointer; margin: 0 3px; }
        .invoice { max-width: 850px; margin: 18px auto; background: #fff; padding: 28px; box-shadow: 0 1px 6px rgba(0,0,0,.12); }
        .header { display: table; width: 100%; border-bottom: 3px solid #008080; padding-bottom: 14px; margin-bottom: 18px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { text-align: right; }
        .logo { max-height: 72px; max-width: 180px; object-fit: contain; }
        h1 { margin: 0; color: #008080; font-size: 24px; }
        h2 { font-size: 15px; margin: 18px 0 8px; color: #008080; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .muted { color: #666; font-size: 12px; }
        .grid { display: table; width: 100%; margin-bottom: 10px; }
        .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 15px; }
        .row { display: table; width: 100%; padding: 4px 0; }
        .label { display: table-cell; font-weight: bold; color: #444; width: 42%; }
        .value { display: table-cell; text-align: right; }
        .summary { background: #f8fbfb; border: 1px solid #d7eeee; border-radius: 6px; padding: 14px; margin-top: 8px; }
        .amount-total { font-size: 18px; font-weight: bold; color: #008080; }
        .amount-paid { color: #198754; font-weight: bold; }
        .amount-balance { color: #dc3545; font-weight: bold; }
        .progress { height: 14px; background: #e9ecef; border-radius: 99px; overflow: hidden; margin-top: 6px; }
        .progress-bar { height: 14px; background: #008080; width: {{ $paymentPercentage }}%; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background: #008080; color: #fff; text-align: left; }
        .right { text-align: right; }
        .footer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #ddd; text-align: center; font-size: 11px; color: #777; }
        @media print { body { background: #fff; } .toolbar { display: none; } .invoice { margin: 0; max-width: none; box-shadow: none; padding: 12mm; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨 {{ __('Print Invoice') }}</button>
        <a href="{{ route('aesthetic.invoices.thermal-receipt', [$invoice, 'width' => 80, 'auto' => 0]) }}">{{ __('Thermal 80mm') }}</a>
        <a href="{{ route('aesthetic.invoices.thermal-receipt', [$invoice, 'width' => 58, 'auto' => 0]) }}">{{ __('Thermal 58mm') }}</a>
        <button onclick="window.close()">✕ {{ __('Close') }}</button>
    </div>

    <div class="invoice">
        <div class="header">
            <div class="header-left">
                @if($logoSrc)<img src="{{ $logoSrc }}" class="logo" alt="">@endif
                <div><strong>{{ $clinic->name ?? config('app.name') }}</strong></div>
                <div class="muted">{{ $clinic->address ?? '' }}</div>
                <div class="muted">{{ $clinic->phone ?? '' }} {{ $clinic->email ? ' · '.$clinic->email : '' }}</div>
            </div>
            <div class="header-right">
                <h1>{{ __('Aesthetic Invoice') }}</h1>
                <div><strong>{{ __('Invoice') }}:</strong> {{ $invoice->invoice_number }}</div>
                <div class="muted">{{ __('Printed') }}: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="grid">
            <div class="col">
                <h2>{{ __('Patient Information') }}</h2>
                <div class="row"><span class="label">{{ __('Patient') }}</span><span class="value">{{ $invoice->patient->full_name ?? '-' }}</span></div>
                <div class="row"><span class="label">{{ __('Patient ID') }}</span><span class="value">{{ $invoice->patient->patient_id ?? '-' }}</span></div>
                <div class="row"><span class="label">{{ __('Phone') }}</span><span class="value">{{ $invoice->patient->phone ?? '-' }}</span></div>
            </div>
            <div class="col">
                <h2>{{ __('Invoice Details') }}</h2>
                <div class="row"><span class="label">{{ __('Invoice Date') }}</span><span class="value">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</span></div>
                @if($invoice->due_date)
                    <div class="row"><span class="label">{{ __('Due Date') }}</span><span class="value">{{ $invoice->due_date->format('Y-m-d') }}</span></div>
                @endif
                <div class="row"><span class="label">{{ __('Status') }}</span><span class="value">{{ $invoice->status_display }}</span></div>
                <div class="row"><span class="label">{{ __('Issued By') }}</span><span class="value">{{ $invoice->creator?->full_name_with_title ?? $invoice->creator?->full_name ?? '-' }}</span></div>
            </div>
        </div>

        <h2>{{ __('Items') }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="right">{{ __('Qty') }}</th>
                    <th class="right">{{ __('Unit Price') }}</th>
                    <th class="right">{{ __('Discount') }}</th>
                    <th class="right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->discount, 2) }}</td>
                    <td class="right">{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <h2>{{ __('Financial Summary') }}</h2>
        <div class="summary">
            <div class="row"><span class="label">{{ __('Subtotal') }}</span><span class="value">{{ number_format($invoice->subtotal, 2) }} {{ $currency }}</span></div>
            @if($invoice->tax_amount > 0)
                <div class="row"><span class="label">{{ __('Tax') }} ({{ number_format($invoice->tax_rate, 2) }}%)</span><span class="value">{{ number_format($invoice->tax_amount, 2) }} {{ $currency }}</span></div>
            @endif
            @if($invoice->discount_amount > 0)
                <div class="row"><span class="label">{{ __('Discount') }}</span><span class="value">-{{ number_format($invoice->discount_amount, 2) }} {{ $currency }}</span></div>
            @endif
            <div class="row"><span class="label">{{ __('Total') }}</span><span class="value amount-total">{{ number_format($invoice->total_amount, 2) }} {{ $currency }}</span></div>
            <div class="row"><span class="label">{{ __('Paid Amount') }}</span><span class="value amount-paid">{{ number_format($invoice->paid_amount, 2) }} {{ $currency }}</span></div>
            <div class="row"><span class="label">{{ __('Balance') }}</span><span class="value amount-balance">{{ number_format($invoice->balance, 2) }} {{ $currency }}</span></div>
            <div class="muted">{{ __('Payment Progress') }}: {{ number_format($paymentPercentage, 1) }}%</div>
            <div class="progress"><div class="progress-bar"></div></div>
        </div>

        @if($invoice->notes)
            <h2>{{ __('Notes') }}</h2>
            <p>{{ $invoice->notes }}</p>
        @endif

        <div class="footer">{{ __('Thank you') }} — {{ $clinic->name ?? config('app.name') }}</div>
    </div>
    @if($autoPrint)<script>window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 350); });</script>@endif
</body>
</html>
