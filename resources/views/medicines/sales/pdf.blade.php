<!DOCTYPE html>
@php
    $clinicId = $clinic->id ?? null;
    $logoSrc = $clinicId ? \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($clinicId) : null;
    $sym = $currency ?? '';
    $balance = (float) $invoice->total - (float) $invoice->paid_amount;
@endphp
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ __('Sale Invoice') }} - {{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; font-size: 12px; }
        .header { border-bottom: 2px solid #c0392b; padding-bottom: 8px; margin-bottom: 12px; }
        .header-table { width: 100%; }
        .header-table td { vertical-align: top; }
        .clinic-name { font-size: 18px; font-weight: 700; color: #2c3e50; }
        .clinic-meta { font-size: 11px; color: #555; }
        .doc-title { font-size: 16px; font-weight: 700; text-align: right; color: #c0392b; }
        .doc-meta { font-size: 11px; text-align: right; }
        .logo { max-height: 60px; max-width: 140px; }
        h2 { font-size: 14px; margin: 14px 0 6px; color: #2c3e50; }
        table.party { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.party td { padding: 4px 6px; border: 1px solid #e0e0e0; }
        table.party td.label { background: #f7f7f7; font-weight: 600; width: 22%; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.items th, table.items td { border: 1px solid #ccc; padding: 6px 8px; }
        table.items th { background: #f5f5f5; text-align: left; font-size: 11px; }
        table.items td.num, table.items th.num { text-align: right; }
        table.totals { width: 45%; float: right; border-collapse: collapse; margin-top: 8px; }
        table.totals td { padding: 4px 8px; border: 1px solid #e0e0e0; }
        table.totals td.label { background: #f7f7f7; font-weight: 600; width: 55%; }
        table.totals td.num { text-align: right; }
        table.totals tr.grand td { background: #c0392b; color: #fff; font-weight: 700; }
        .notes { clear: both; font-size: 11px; color: #555; padding-top: 14px; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width:60%;">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" class="logo" alt="">
                    @endif
                    <div class="clinic-name">{{ $clinic->name ?? config('app.name') }}</div>
                    <div class="clinic-meta">
                        @if(!empty($clinic->address)){{ $clinic->address }}<br>@endif
                        @if(!empty($clinic->phone)){{ __('Phone') }}: {{ $clinic->phone }}@endif
                        @if(!empty($clinic->email)) · {{ $clinic->email }}@endif
                    </div>
                </td>
                <td style="width:40%; text-align:right;">
                    <div class="doc-title">{{ __('Sale Invoice') }}</div>
                    <div class="doc-meta">
                        <strong>{{ __('Invoice') }}:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>{{ __('Date') }}:</strong> {{ optional($invoice->sold_at)->format('Y-m-d H:i') }}<br>
                        <strong>{{ __('Cashier') }}:</strong>
                        {{ optional($invoice->user)->full_name ?? optional($invoice->user)->username ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="party">
        <tr>
            <td class="label">{{ __('Patient') }}</td>
            <td>
                @if($invoice->patient)
                    {{ trim(($invoice->patient->first_name ?? '') . ' ' . ($invoice->patient->last_name ?? '')) }}
                    @if(!empty($invoice->patient->patient_id))
                        ({{ $invoice->patient->patient_id }})
                    @endif
                @else
                    {{ __('Walk-in') }}
                @endif
            </td>
            <td class="label">{{ __('Payment Method') }}</td>
            <td>{{ ucfirst((string) ($invoice->payment_method ?? '-')) }}</td>
        </tr>
    </table>

    <h2>{{ __('Items') }}</h2>
    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th>{{ __('Medicine') }}</th>
                <th class="num" style="width:12%;">{{ __('Quantity') }}</th>
                <th class="num" style="width:18%;">{{ __('Unit Price') }}</th>
                <th class="num" style="width:18%;">{{ __('Line Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        {{ $item->medicine->name ?? __('Unknown') }}
                        @if($item->medicine && $item->medicine->dosage)
                            <span style="color:#888;">— {{ $item->medicine->dosage }}</span>
                        @endif
                    </td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td class="num">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num">{{ number_format((float) $item->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">{{ __('Subtotal') }}</td>
            <td class="num">{{ $sym }} {{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        @if((float) $invoice->discount > 0)
            <tr>
                <td class="label">{{ __('Discount') }}</td>
                <td class="num">- {{ $sym }} {{ number_format((float) $invoice->discount, 2) }}</td>
            </tr>
        @endif
        @if((float) $invoice->tax > 0)
            <tr>
                <td class="label">{{ __('Tax') }}</td>
                <td class="num">{{ $sym }} {{ number_format((float) $invoice->tax, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td class="label">{{ __('Grand Total') }}</td>
            <td class="num">{{ $sym }} {{ number_format((float) $invoice->total, 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Paid') }}</td>
            <td class="num">{{ $sym }} {{ number_format((float) $invoice->paid_amount, 2) }}</td>
        </tr>
        @if(abs($balance) > 0.001)
            <tr>
                <td class="label">{{ $balance > 0 ? __('Balance Due') : __('Change') }}</td>
                <td class="num">{{ $sym }} {{ number_format(abs($balance), 2) }}</td>
            </tr>
        @endif
    </table>

    @if($invoice->notes)
        <div class="notes"><strong>{{ __('Notes') }}:</strong> {{ $invoice->notes }}</div>
    @endif

    <div class="footer">{{ __('Thank you for your purchase') }} — {{ now()->format('Y-m-d H:i') }}</div>
</body>
</html>
