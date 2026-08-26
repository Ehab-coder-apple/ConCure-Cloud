<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .prescription-document {
            max-width: 800px;
            margin: 0 auto;
        }
        @media print {
            body { margin: 0; padding: 15px; }
            .prescription-document { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="prescription-document">

    @php
        $clinicLogoPath = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
        $clinicLogoSrc = \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($prescription->clinic_id);
    @endphp

    <!-- HEADER -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 2px solid #000; padding-bottom: 8px;">
        <tr>
            @if($clinicLogoPath && file_exists($clinicLogoPath))
                <td style="width: 75px; vertical-align: middle; text-align: center;">
                    <img src="{{ $clinicLogoSrc }}" alt="Logo" style="max-height: 65px; max-width: 65px;">
                </td>
            @endif
            <td style="vertical-align: middle;">
                <div style="font-size: 14px; font-weight: bold; color: #000; margin-bottom: 2px;">
                    Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}
                </div>
                @if($prescription->doctor->email)
                    <div style="font-size: 9px; color: #333;">Email: {{ $prescription->doctor->email }}</div>
                @endif
            </td>
            <td style="vertical-align: middle; text-align: right; width: 220px;">
                <div style="font-size: 13px; font-weight: bold; color: #000; margin-bottom: 2px;">
                    {{ $prescription->clinic->name ?? 'ConCure Clinic' }}
                </div>
                @if($prescription->doctor->specialization)
                    <div style="font-size: {{ $prescription->doctor->specialization_font_size ?? 10 }}px; color: #333;">{{ $prescription->doctor->specialization }}</div>
                @endif
                @if($prescription->doctor->scientific_degree)
                    <div style="font-size: 9px; color: #333;">{{ $prescription->doctor->scientific_degree }}</div>
                @endif
                @if($prescription->doctor->medical_degrees)
                    <div style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 9 }}px; color: #333;">{{ $prescription->doctor->medical_degrees }}</div>
                @endif
                @if($prescription->doctor->educational_institution)
                    <div style="font-size: 9px; color: #333;">{{ $prescription->doctor->educational_institution }}</div>
                @endif
                @if($prescription->doctor->professional_credentials)
                    <div style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 9 }}px; color: #333;">{{ $prescription->doctor->professional_credentials }}</div>
                @endif
                @if($prescription->doctor->phone)
                    <div style="font-size: 9px; color: #333;">Phone: {{ $prescription->doctor->phone }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- PATIENT INFORMATION -->
    <div style="border: 2px solid #000; margin-bottom: 12px;">
        <div style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">PATIENT INFORMATION</div>
        <div style="padding: 8px 10px; font-size: 10px;">
            <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'N/A') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ $prescription->patient->age_formatted ?? 'N/A' }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Weight:</strong> {{ $prescription->patient->latest_weight_kg ?? $prescription->patient->weight ?? 'N/A' }} kg
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Height:</strong> {{ $prescription->patient->latest_height ?? $prescription->patient->height ?? 'N/A' }} cm
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Date:</strong> {{ $prescription->prescribed_date->format('d/m/Y') }}
            @if($prescription->visit_type)
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Visit Type:</strong> {{ \App\Models\SimplePrescription::VISIT_TYPES[$prescription->visit_type] ?? ucfirst($prescription->visit_type) }}
            @endif
        </div>
    </div>

    <!-- DIAGNOSIS -->
    @if($prescription->diagnosis)
        <div style="border: 2px solid #000; margin-bottom: 12px;">
            <div style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">DIAGNOSIS</div>
            <div style="padding: 8px 10px; font-size: 10px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->diagnosis }}</div>
        </div>
    @endif

    <!-- PRESCRIBED MEDICINES -->
    @if($prescription->medicines->count() > 0)
        <div style="border: 2px solid #000; margin-bottom: 12px;">
            <div style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">PRESCRIBED MEDICINES</div>
            <div style="padding: 8px 10px; font-size: 10px;">
                @foreach($prescription->medicines as $index => $medicine)
                    <span style="font-weight: bold;">{{ $index + 1 }}. {{ $medicine->medicine_name }}</span><br>
                    <span style="padding-left: 15px; font-size: 9px;">
                        @if($medicine->type)<strong>Type:</strong> {{ ucfirst($medicine->type) }} |@endif
                        @if($medicine->dosage)<strong>Dose:</strong> {{ $medicine->dosage }}@endif
                        @if($medicine->frequency) | <strong>Frequency:</strong> {{ $medicine->frequency }}@endif
                        @if($medicine->duration) | <strong>Duration:</strong> {{ $medicine->duration }}@endif
                        @if($medicine->quantity) | <strong>Qty:</strong> {{ $medicine->quantity }}@endif
                    </span>
                    @if($medicine->instructions)
                        <br><span style="padding-left: 15px; font-size: 9px;"><strong>Instructions:</strong> {{ $medicine->instructions }}</span>
                    @endif
                    @if(!$loop->last)<br><br>@endif
                @endforeach
            </div>
        </div>
    @endif

    <!-- ADDITIONAL NOTES -->
    @if($prescription->notes)
        <div style="border: 2px solid #000; margin-bottom: 12px;">
            <div style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">ADDITIONAL NOTES</div>
            <div style="padding: 8px 10px; font-size: 10px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->notes }}</div>
        </div>
    @endif

    <!-- FOOTER -->
    <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #666;">
        Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y') }} at {{ now()->format('g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>

    </div>

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
