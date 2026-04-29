<!DOCTYPE html>
@php
    $isRtl = !empty($is_rtl);
    $clinicId = $clinic->id ?? null;
    $logoSrc = $clinicId ? \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($clinicId) : null;
@endphp
<html lang="{{ $locale ?? app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? __('Receipt') }} · {{ $clinic->name ?? config('app.name') }}</title>
    <style>
        :root {
            --bg: #f1f5f9;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --accent: #0d6efd;
            --good: #16a34a;
            --warn: #d97706;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: var(--bg); color: var(--ink); }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
                'Noto Sans Arabic', 'Noto Kufi Arabic', 'Geeza Pro', 'Tahoma', sans-serif;
            font-size: 15px; line-height: 1.55;
            min-height: 100vh;
            padding: 16px;
        }
        html[dir="rtl"] body { line-height: 1.7; letter-spacing: 0; }
        bdi, .num { unicode-bidi: isolate; direction: ltr; font-variant-numeric: tabular-nums; }
        .wrap { max-width: 480px; margin: 0 auto; }
        .verified-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: #ecfdf5; color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 4px 10px; border-radius: 999px;
            font-size: 12px; font-weight: 600;
            margin-bottom: 12px;
        }
        .verified-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--good); }
        .card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15,23,42,.06), 0 8px 24px rgba(15,23,42,.05);
            overflow: hidden;
        }
        .clinic-head {
            text-align: center;
            padding: 24px 16px 16px;
            border-bottom: 1px solid var(--line);
        }
        .clinic-logo { max-height: 64px; max-width: 70%; object-fit: contain; margin-bottom: 8px; }
        .clinic-name { font-size: 18px; font-weight: 700; margin: 0; }
        .clinic-meta { color: var(--muted); font-size: 13px; margin-top: 4px; }
        .doc-title {
            text-align: center;
            font-size: 13px; letter-spacing: 1px; text-transform: uppercase;
            color: var(--muted); margin-top: 16px;
        }
        .doc-ref {
            text-align: center;
            font-family: 'SF Mono', Menlo, Consolas, monospace;
            font-weight: 700;
            font-size: 18px;
            margin: 4px 0 16px 0;
        }
        .section {
            padding: 14px 18px;
            border-top: 1px solid var(--line);
        }
        .section h3 {
            margin: 0 0 8px 0;
            font-size: 12px; font-weight: 600; letter-spacing: .5px;
            color: var(--muted); text-transform: uppercase;
        }
        .row { display: flex; justify-content: space-between; gap: 8px; padding: 4px 0; }
        .row .lbl { color: var(--muted); }
        .row .val { font-weight: 600; text-align: end; word-break: break-word; }
        .fin .row { padding: 6px 0; }
        .fin .row.balance {
            font-size: 18px;
            border-top: 1px dashed var(--line);
            margin-top: 8px; padding-top: 12px;
        }
        .fin .row.balance .val { color: var(--warn); }
        .fin .row.balance.paid .val { color: var(--good); }
        .footer {
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            padding: 16px;
        }
        .footer .verified-by { font-weight: 600; color: var(--ink); }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0b1220; --card: #111827; --ink: #f1f5f9;
                --muted: #94a3b8; --line: #1f2937;
            }
            .verified-pill { background: rgba(22,163,74,.12); color: #6ee7b7; border-color: rgba(22,163,74,.3); }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="verified-pill">
        <span class="dot"></span> {{ __('Verified Receipt') }}
    </div>

    <div class="card">
        <div class="clinic-head">
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="" class="clinic-logo">
            @endif
            <h1 class="clinic-name">{{ $clinic->name ?? config('app.name') }}</h1>
            @if(!empty($clinic->address))
                <div class="clinic-meta">{{ $clinic->address }}</div>
            @endif
            @if(!empty($clinic->phone) || !empty($clinic->email))
                <div class="clinic-meta">
                    @if(!empty($clinic->phone))<bdi>{{ $clinic->phone }}</bdi>@endif
                    @if(!empty($clinic->phone) && !empty($clinic->email)) · @endif
                    {{ $clinic->email ?? '' }}
                </div>
            @endif

            <div class="doc-title">{{ $title ?? __('Receipt') }}</div>
            <div class="doc-ref"><bdi>{{ $reference }}</bdi></div>
        </div>

        <div class="section">
            <h3>{{ __('Patient') }}</h3>
            <div class="row"><span class="lbl">{{ __('Name') }}</span><span class="val">{{ $patient->full_name ?? '-' }}</span></div>
            @if(!empty($patient->date_of_birth))
                <div class="row"><span class="lbl">{{ __('Age') }}</span><span class="val"><bdi>{{ $patient->age }}</bdi> {{ __('yrs') }}</span></div>
            @endif
        </div>

        <div class="section">
            <h3>{{ __('Details') }}</h3>
            @foreach($meta as $row)
                <div class="row"><span class="lbl">{{ $row['label'] }}</span><span class="val">{{ $row['value'] }}</span></div>
            @endforeach
            @if(!empty($doctor_name))
                <div class="row"><span class="lbl">{{ $doctor_label ?? __('Doctor') }}</span><span class="val">{{ $doctor_name }}</span></div>
            @endif
        </div>

        @if(!empty($services))
            <div class="section">
                <h3>{{ __('Services') }}</h3>
                @foreach($services as $row)
                    <div class="row"><span class="lbl">{{ $row['label'] }}</span><span class="val">{{ $row['value'] }}</span></div>
                @endforeach
            </div>
        @endif

        @if(!empty($financials))
            @php
                $isFullyPaid = ($financials['balance'] ?? 0) <= 0 && ($financials['paid'] ?? 0) > 0;
            @endphp
            <div class="section fin">
                <h3>{{ __('Payment') }}</h3>
                <div class="row"><span class="lbl">{{ __('Method') }}</span><span class="val">{{ $financials['method'] ?? '-' }}</span></div>
                @if(!empty($financials['receipt_number']))
                    <div class="row"><span class="lbl">{{ __('Receipt #') }}</span><span class="val"><bdi>{{ $financials['receipt_number'] }}</bdi></span></div>
                @endif
                <div class="row"><span class="lbl">{{ __('Total') }}</span><span class="val"><bdi>{{ $financials['currency_symbol'] }} {{ number_format($financials['total'], 2) }}</bdi></span></div>
                <div class="row"><span class="lbl">{{ __('Paid') }}</span><span class="val"><bdi>{{ $financials['currency_symbol'] }} {{ number_format($financials['paid'], 2) }}</bdi></span></div>
                <div class="row balance {{ $isFullyPaid ? 'paid' : '' }}">
                    <span class="lbl">{{ $isFullyPaid ? __('Status') : __('Balance Due') }}</span>
                    <span class="val">
                        @if($isFullyPaid)
                            ✓ {{ __('Fully Paid') }}
                        @else
                            <bdi>{{ $financials['currency_symbol'] }} {{ number_format($financials['balance'], 2) }}</bdi>
                        @endif
                    </span>
                </div>
            </div>
        @endif

        <div class="footer">
            <div class="verified-by">{{ $thank_you }}</div>
            <div style="margin-top:6px;">
                {{ __('Verified at') }}: <bdi>{{ optional($printed_at)->format('Y-m-d H:i') }}</bdi>
            </div>
        </div>
    </div>

    <div class="footer" style="padding: 12px 4px;">
        {{ __('Powered by') }} {{ config('app.name', 'ConCure') }}
    </div>
</div>
</body>
</html>

