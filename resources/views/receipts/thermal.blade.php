<!DOCTYPE html>
@php
    $isRtl = !empty($is_rtl);
    $widthMm = (int) ($width_mm ?? 80);
    $clinicId = $clinic->id ?? auth()->user()->clinic_id ?? null;
    $logoSrc = $clinicId ? \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($clinicId) : null;
    $bodyWidth = $widthMm . 'mm';
    $contentWidth = ($widthMm - 6) . 'mm';
    $autoPrint = $auto_print ?? true;
@endphp
<html lang="{{ $locale ?? app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? __('Receipt') }} - {{ $reference ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { size: {{ $bodyWidth }} auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            background: #f1f3f5;
            font-family: 'Courier New', 'DejaVu Sans Mono', 'Noto Sans Arabic', monospace;
            color: #000;
        }
        body { padding: 12px 0; }
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            background: #fff; border-bottom: 1px solid #dee2e6;
            padding: 8px 12px; display: flex; gap: 8px; flex-wrap: wrap;
            justify-content: center; align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .toolbar a, .toolbar button {
            border: 1px solid #ced4da; background: #f8f9fa; color: #212529;
            padding: 6px 12px; border-radius: 4px; cursor: pointer;
            text-decoration: none; font-size: 13px;
        }
        .toolbar a.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
        .receipt {
            width: {{ $bodyWidth }}; margin: 12px auto; background: #fff;
            padding: 8px 3mm; box-shadow: 0 1px 3px rgba(0,0,0,.08);
            font-size: 12px; line-height: 1.35; word-wrap: break-word;
        }
        .receipt.w58 { font-size: 11px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .muted { color: #555; font-size: 10px; }
        .bold { font-weight: 700; }
        .divider {
            border: 0; border-top: 1px dashed #000; margin: 6px 0;
        }
        .clinic-logo { max-width: {{ $contentWidth }}; max-height: 22mm; object-fit: contain; }
        .clinic-name { font-size: 14px; font-weight: 700; margin: 4px 0 2px 0; }
        .meta-row { display: flex; justify-content: space-between; gap: 4px; }
        .meta-row .label { font-weight: 600; }
        .meta-row .value { text-align: {{ $isRtl ? 'left' : 'right' }}; word-break: break-word; }
        .block-title {
            text-align: center; font-weight: 700; margin: 4px 0;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .fin-row { display: flex; justify-content: space-between; margin: 2px 0; }
        .fin-total { font-size: 14px; font-weight: 700; border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px; }
        .item-row { margin: 3px 0; }
        .item-name { font-weight: 600; }
        .item-line { display: flex; justify-content: space-between; font-size: 11px; }
        .item-line .muted { color: #555; }
        .qr-wrap { text-align: center; margin-top: 6px; }
        .qr-wrap svg { width: 30mm; height: 30mm; }
        .footer-note { text-align: center; font-size: 11px; margin-top: 6px; }
        .stamp { text-align: center; font-size: 10px; color: #444; margin-top: 4px; }
        @media print {
            html, body { background: #fff !important; }
            body { padding: 0 !important; }
            .toolbar { display: none !important; }
            .receipt { box-shadow: none !important; margin: 0 auto !important; padding: 2mm 3mm !important; }
        }
    </style>
</head>
<body>
    @php
        $widthSwitchUrls = [];
        foreach (\App\Services\ThermalReceiptService::ALLOWED_WIDTHS as $w) {
            $widthSwitchUrls[$w] = request()->fullUrlWithQuery(['width' => $w, 'auto' => 0]);
        }
    @endphp

    <div class="toolbar">
        <button type="button" onclick="window.print()">🖨 {{ __('Print') }}</button>
        @foreach(\App\Services\ThermalReceiptService::ALLOWED_WIDTHS as $w)
            <a href="{{ $widthSwitchUrls[$w] }}" class="{{ $widthMm === $w ? 'active' : '' }}">{{ $w }}mm</a>
        @endforeach
        <button type="button" onclick="window.close()">✕ {{ __('Close') }}</button>
    </div>

    <div class="receipt {{ $widthMm === 58 ? 'w58' : 'w80' }}">
        @if($logoSrc)
            <div class="center"><img src="{{ $logoSrc }}" alt="" class="clinic-logo"></div>
        @endif
        <div class="center clinic-name">{{ $clinic->name ?? config('app.name') }}</div>
        @if(!empty($clinic->address))
            <div class="center muted">{{ $clinic->address }}</div>
        @endif
        @if(!empty($clinic->phone) || !empty($clinic->email))
            <div class="center muted">
                {{ $clinic->phone ?? '' }}
                @if(!empty($clinic->phone) && !empty($clinic->email)) · @endif
                {{ $clinic->email ?? '' }}
            </div>
        @endif

        <hr class="divider">
        <div class="block-title">{{ $title ?? __('Receipt') }}</div>
        <div class="meta-row"><span class="label">{{ __('Ref') }}:</span><span class="value">{{ $reference }}</span></div>
        <div class="meta-row"><span class="label">{{ __('Printed') }}:</span><span class="value">{{ optional($printed_at)->format('Y-m-d H:i') }}</span></div>

        <hr class="divider">
        <div class="meta-row"><span class="label">{{ __('Patient') }}:</span><span class="value bold">{{ $patient->full_name ?? '-' }}</span></div>
        @if(!empty($patient->patient_id))
            <div class="meta-row"><span class="label">{{ __('Patient ID') }}:</span><span class="value">{{ $patient->patient_id }}</span></div>
        @endif
        @if(!empty($patient->date_of_birth))
            <div class="meta-row"><span class="label">{{ __('Age') }}:</span><span class="value">{{ $patient->age }} {{ __('yrs') }}</span></div>
        @endif
        @if(!empty($patient->phone))
            <div class="meta-row"><span class="label">{{ __('Phone') }}:</span><span class="value">{{ $patient->phone }}</span></div>
        @endif

        <hr class="divider">
        @foreach($meta as $row)
            <div class="meta-row"><span class="label">{{ $row['label'] }}:</span><span class="value">{{ $row['value'] }}</span></div>
        @endforeach
        @if(!empty($doctor_name))
            <div class="meta-row"><span class="label">{{ $doctor_label ?? __('Doctor') }}:</span><span class="value">{{ $doctor_name }}</span></div>
        @endif

        @if(!empty($services))
            <hr class="divider">
            @php $currencySym = $financials['currency'] ?? 'IQD'; @endphp
            @foreach($services as $row)
                @if(isset($row['price']))
                    {{-- Show itemized procedure with price --}}
                    <div class="meta-row">
                        <span class="label" style="max-width: 60%;">{{ $row['value'] }}</span>
                        <span class="value">{{ $currencySym }} {{ number_format((float) $row['price'], 2) }}</span>
                    </div>
                @else
                    {{-- Regular meta row --}}
                    <div class="meta-row"><span class="label">{{ $row['label'] }}:</span><span class="value">{{ $row['value'] }}</span></div>
                @endif
            @endforeach
        @endif

        @if(!empty($items))
            <hr class="divider">
            <div class="block-title">{{ $items_title ?? __('Items') }}</div>
            @php $sym = $financials['currency_symbol'] ?? ''; @endphp
            @foreach($items as $row)
                <div class="item-row">
                    <div class="item-name">{{ $row['name'] }}</div>
                    @if(!empty($row['subtitle']))
                        <div class="muted">{{ $row['subtitle'] }}</div>
                    @endif
                    @if(isset($row['qty']) || isset($row['total']))
                        <div class="item-line">
                            <span class="muted">
                                @if(isset($row['qty']) && isset($row['unit_price']))
                                    {{ rtrim(rtrim(number_format((float) $row['qty'], 2), '0'), '.') }} × {{ number_format((float) $row['unit_price'], 2) }}
                                @endif
                            </span>
                            @if(isset($row['total']))
                                <span>{{ $sym }} {{ number_format((float) $row['total'], 2) }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endif

        @if(!empty($financials))
            <hr class="divider">
            <div class="block-title">{{ __('Payment') }}</div>
            <div class="fin-row"><span>{{ __('Method') }}</span><span>{{ $financials['method'] ?? '-' }}</span></div>
            @if(!empty($financials['payment_plan']))
                <div class="fin-row"><span>{{ __('Payment Plan') }}</span><span>{{ $financials['payment_plan'] }}</span></div>
            @endif
            @if(!empty($financials['last_payment_date']))
                <div class="fin-row"><span>{{ __('Last Payment') }}</span><span>{{ $financials['last_payment_date'] }}</span></div>
            @endif
            @if(!empty($financials['receipt_number']))
                <div class="fin-row"><span>{{ __('Receipt #') }}</span><span>{{ $financials['receipt_number'] }}</span></div>
            @endif
            @if(isset($financials['subtotal']))
                <div class="fin-row"><span>{{ __('Subtotal') }}</span><span>{{ $financials['currency_symbol'] }} {{ number_format($financials['subtotal'], 2) }}</span></div>
            @endif
            @if(!empty($financials['discount']))
                <div class="fin-row"><span>{{ __('Discount') }}</span><span>- {{ $financials['currency_symbol'] }} {{ number_format($financials['discount'], 2) }}</span></div>
            @endif
            @if(!empty($financials['tax']))
                <div class="fin-row"><span>{{ __('Tax') }}</span><span>{{ $financials['currency_symbol'] }} {{ number_format($financials['tax'], 2) }}</span></div>
            @endif
            <div class="fin-row"><span>{{ __('Total') }}</span><span>{{ $financials['currency_symbol'] }} {{ number_format($financials['total'], 2) }}</span></div>
            <div class="fin-row"><span>{{ __('Paid') }}</span><span>{{ $financials['currency_symbol'] }} {{ number_format($financials['paid'], 2) }}</span></div>
            <div class="fin-row fin-total"><span>{{ __('Balance') }}</span><span>{{ $financials['currency_symbol'] }} {{ number_format($financials['balance'], 2) }}</span></div>
            @if(!empty($financials['notes']))
                <div class="muted" style="margin-top:4px;">{{ $financials['notes'] }}</div>
            @endif
        @endif

        @if(!empty($qr_svg))
            <hr class="divider">
            <div class="qr-wrap">{!! $qr_svg !!}</div>
            <div class="muted center" style="word-break: break-all;">{{ $qr_payload ?? '' }}</div>
        @endif

        <hr class="divider">
        <div class="footer-note">{{ $thank_you }}</div>
        @if(!empty($printed_by))
            <div class="stamp">{{ __('Printed by') }}: {{ $printed_by }}</div>
        @endif
        <div class="stamp">{{ optional($printed_at)->format('Y-m-d H:i:s') }}</div>
    </div>

    <script>
        (function () {
            var auto = @json($autoPrint);
            if (!auto) return;
            window.addEventListener('load', function () {
                setTimeout(function () { try { window.print(); } catch (e) {} }, 350);
            });
        })();
    </script>
</body>
</html>
