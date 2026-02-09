<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nutrition Plan (mPDF Simple)</title>
    <style>
        @page { margin: 15mm 12mm; size: A4; }
        body { font-family: "DejaVu Sans", "Amiri", dejavu, sans-serif; font-size: 11px; color: #222; }
        .header { border-bottom: 2px solid #20B2AA; padding-bottom: 6px; margin-bottom: 10px; }
        .title { font-size: 16px; color: #20B2AA; margin: 0 0 6px 0; text-align: center; }
        .info { font-size: 10px; margin: 0 0 8px 0; }
        .meal { margin: 10px 0; page-break-inside: avoid; }
        .meal-title { font-weight: bold; font-size: 13px; margin: 0 0 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 10px; }
        th { background: #f5f5f5; text-align: left; }
        .rtl th, .rtl td { text-align: right; }
    </style>
</head>
@php $rtl = !empty($isArabicOutput) && $isArabicOutput; @endphp
<body class="{{ $rtl ? 'rtl' : '' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
    <div class="header">
        <div class="title">{{ __('Nutrition Plan') }} #{{ $dietPlan->plan_number }}</div>
        <div class="info">
            {{ $dietPlan->patient->name ?? '' }}
            @if(!empty($dietPlan->doctor) && !empty($dietPlan->doctor->name))
                &nbsp; | &nbsp; {{ $dietPlan->doctor->name }}
            @endif
        </div>
    </div>

    @foreach($dietPlan->meals as $meal)
        <div class="meal">
            <div class="meal-title">{{ $meal->meal_name ?? ucfirst($meal->meal_type) }}</div>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Food') }}</th>
                        <th style="width: 22%">{{ __('Quantity') }}</th>
                        <th style="width: 18%">{{ __('Unit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meal->foods as $mf)
                        @php
                            $name = $mf->food_name ?? '';
                            $qty  = $mf->quantity ?? '';
                            $unit = $mf->unit ?? '';
                            if ($name === '' && !empty($mf->food) && method_exists($mf->food, 'getNameInLanguage') && !empty($isArabicOutput)) {
                                $tr = $mf->food->getNameInLanguage('ar');
                                if (!empty($tr)) { $name = $tr; }
                            }
                        @endphp
                        @if(trim($name.$qty.$unit) !== '')
                        <tr>
                            <td>{{ $name }}</td>
                            <td>{{ $qty }}</td>
                            <td>{{ $unit }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>

