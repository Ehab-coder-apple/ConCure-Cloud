<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #0d6efd; padding-bottom: 12px; margin-bottom: 18px; }
        .title { font-size: 22px; font-weight: bold; color: #0d6efd; margin: 0; text-align: right; }
        .muted { color: #666; font-size: 11px; }
        .clinic-name { font-size: 18px; font-weight: bold; margin: 0 0 2px 0; }
        .box { border: 1px solid #dee2e6; border-radius: 6px; padding: 12px; margin-bottom: 12px; background: #f8f9fa; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 4px 0; }
        .label { width: 140px; font-weight: bold; }
        .amount { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    @php
        $clinicId = $clinic->id ?? auth()->user()->clinic_id ?? null;
        $clinicLogoSrc = \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($clinicId);
        $dt = $appointmentDateTime ? \Carbon\Carbon::parse($appointmentDateTime) : null;
    @endphp

    <div class="header">
        <table>
            <tr>
                <td style="width: 25%;">
                    @if($clinicLogoSrc)
                        <img src="{{ $clinicLogoSrc }}" alt="Clinic Logo" style="max-height: 80px; max-width: 120px; object-fit: contain;">
                    @endif
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="clinic-name">{{ $clinic->name ?? 'Clinic' }}</div>
                    <div class="muted">
                        @if(!empty($clinic->address)) {{ $clinic->address }}<br> @endif
                        @if(!empty($clinic->phone)) {{ $clinic->phone }} @endif
                        @if(!empty($clinic->email)) &nbsp;|&nbsp; {{ $clinic->email }} @endif
                    </div>
                </td>
                <td style="width: 25%; text-align: right;">
                    <div class="title">RECEIPT</div>
                    <div class="muted">No: {{ $receipt->receipt_number }}</div>
                    <div class="muted">Date: {{ optional($receipt->receipt_date)->format('Y-m-d') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table>
            <tr><td class="label">Patient</td><td>{{ trim(($appointment->patient_first_name ?? '') . ' ' . ($appointment->patient_last_name ?? '')) }}</td></tr>
            @if(!empty($appointment->patient_id))
                <tr><td class="label">Patient ID</td><td>{{ $appointment->patient_id }}</td></tr>
            @endif
            @if(!empty($appointment->patient_phone))
                <tr><td class="label">Phone</td><td>{{ $appointment->patient_phone }}</td></tr>
            @endif
        </table>
    </div>

    <div class="box">
        <table>
            <tr><td class="label">Appointment</td><td>{{ $appointment->appointment_number }}</td></tr>
            <tr>
                <td class="label">Date & Time</td>
                <td>{{ $dt ? $dt->format('Y-m-d g:i A') : '-' }}</td>
            </tr>
            @if(!empty($appointment->doctor_first_name) || !empty($appointment->doctor_last_name))
                <tr><td class="label">Doctor</td><td>Dr. {{ trim(($appointment->doctor_first_name ?? '') . ' ' . ($appointment->doctor_last_name ?? '')) }}</td></tr>
            @endif
            <tr><td class="label">Service</td><td>{{ $serviceDescription }}</td></tr>
        </table>
    </div>

    <div class="box" style="background: #ffffff;">
        <table>
            <tr>
                <td class="label">Amount Paid</td>
                <td class="amount">{{ $currencySymbol }} {{ number_format((float) $receipt->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Payment Method</td>
                <td>{{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }}</td>
            </tr>
            @if(!empty($receipt->notes))
                <tr>
                    <td class="label">Notes</td>
                    <td>{{ $receipt->notes }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="muted" style="text-align:center; margin-top: 18px;">
        Thank you.
    </div>
</body>
</html>
