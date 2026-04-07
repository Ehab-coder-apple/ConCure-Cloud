<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>

@php
    $clinicLogo = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
@endphp

<!-- HEADER -->
<table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 8px;" cellpadding="4" cellspacing="0">
    <tr>
        @if($clinicLogo && file_exists($clinicLogo))
            <td style="width: 70px; vertical-align: middle; text-align: center;">
                <img src="{{ $clinicLogo }}" style="max-height: 60px; max-width: 60px;">
            </td>
        @endif
        <td style="vertical-align: middle;">
            <span style="font-size: 14px; font-weight: bold; color: #000;">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</span><br>
            @if($prescription->doctor->email)
                <span style="font-size: 9px; color: #333;">Email: {{ $prescription->doctor->email }}</span>
            @endif
        </td>
        <td style="vertical-align: middle; text-align: right; width: 220px;">
            <span style="font-size: 13px; font-weight: bold; color: #000;">{{ $prescription->clinic->name ?? 'ConCure Clinic' }}</span><br>
            @if($prescription->doctor->specialization)
                <span style="font-size: {{ $prescription->doctor->specialization_font_size ?? 9 }}px; color: #333;">{{ $prescription->doctor->specialization }}</span><br>
            @endif
            @if($prescription->doctor->scientific_degree)
                <span style="font-size: 9px; color: #333;">{{ $prescription->doctor->scientific_degree }}</span><br>
            @endif
            @if($prescription->doctor->medical_degrees)
                <span style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 9 }}px; color: #333;">{{ $prescription->doctor->medical_degrees }}</span><br>
            @endif
            @if($prescription->doctor->educational_institution)
                <span style="font-size: 9px; color: #333;">{{ $prescription->doctor->educational_institution }}</span><br>
            @endif
            @if($prescription->doctor->professional_credentials)
                <span style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 9 }}px; color: #333;">{{ $prescription->doctor->professional_credentials }}</span><br>
            @endif
            @if($prescription->doctor->phone)
                <span style="font-size: 9px; color: #333;">Phone: {{ $prescription->doctor->phone }}</span>
            @endif
        </td>
    </tr>
</table>

<!-- PATIENT INFORMATION -->
<table style="width: 100%; border: 2px solid #000; margin-bottom: 12px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">PATIENT INFORMATION</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 10px;">
            <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'N/A') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Age:</strong> {{ $prescription->patient->age_formatted ?? 'N/A' }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Weight:</strong> {{ $prescription->patient->latest_weight_kg ?? $prescription->patient->weight ?? 'N/A' }} kg
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Height:</strong> {{ $prescription->patient->height ?? 'N/A' }} cm
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Date:</strong> {{ $prescription->prescribed_date ? $prescription->prescribed_date->format('d/m/Y') : date('d/m/Y') }}
        </td>
    </tr>
</table>

<!-- DIAGNOSIS -->
@if($prescription->diagnosis)
<table style="width: 100%; border: 2px solid #000; margin-bottom: 12px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">DIAGNOSIS</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 10px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->diagnosis }}</td>
    </tr>
</table>
@endif

<!-- PRESCRIBED MEDICINES -->
@if($prescription->medicines->count() > 0)
<table style="width: 100%; border: 2px solid #000; margin-bottom: 12px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">PRESCRIBED MEDICINES</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 10px;">
            @foreach($prescription->medicines as $index => $medicine)
                <span style="font-weight: bold;">{{ $index + 1 }}. {{ $medicine->medicine_name }}</span><br>
                <span style="padding-left: 15px; font-size: 9px;">
                    @if($medicine->dosage)<strong>Dose:</strong> {{ $medicine->dosage }}@endif
                    @if($medicine->frequency) | <strong>Frequency:</strong> {{ $medicine->frequency }}@endif
                    @if($medicine->duration) | <strong>Duration:</strong> {{ $medicine->duration }}@endif
                </span>
                @if($medicine->instructions)
                    <br><span style="padding-left: 15px; font-size: 9px;"><strong>Instructions:</strong> {{ $medicine->instructions }}</span>
                @endif
                @if(!$loop->last)<br><br>@endif
            @endforeach
        </td>
    </tr>
</table>
@endif

<!-- ADDITIONAL NOTES -->
@if($prescription->notes)
<table style="width: 100%; border: 2px solid #000; margin-bottom: 12px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding: 6px 10px; border-bottom: 1px solid #000; font-size: 12px; font-weight: bold;">ADDITIONAL NOTES</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 10px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->notes }}</td>
    </tr>
</table>
@endif

<!-- FOOTER -->
<table style="width: 100%; margin-top: 20px;" cellpadding="4" cellspacing="0">
    <tr>
        <td style="text-align: center; font-size: 8px; color: #666;">
            Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y') }} at {{ now()->format('g:i A') }}<br>
            This is a computer-generated prescription and is valid without physical signature.
        </td>
    </tr>
</table>

</body>
</html>
