<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #212529;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 12mm;
        }
    </style>
</head>
<body>

@php
    $clinicLogo = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
@endphp

<!-- HEADER -->
<table style="width: 100%; border-bottom: 2px solid #0d6efd; margin-bottom: 10px;" cellpadding="4" cellspacing="0">
    <tr>
        @if($clinicLogo && file_exists($clinicLogo))
            <td style="width: 70px; vertical-align: middle; text-align: center;">
                <img src="{{ $clinicLogo }}" style="max-height: 60px; max-width: 60px;">
            </td>
        @endif
        <td style="vertical-align: middle;">
            <span style="font-size: 16px; font-weight: bold; color: #0d6efd;">{{ $prescription->clinic->name ?? 'ConCure Clinic' }}</span><br>
            @if($prescription->clinic->address ?? false)<span style="font-size: 8px; color: #6c757d;">{{ $prescription->clinic->address }}</span><br>@endif
            <span style="font-size: 8px; color: #6c757d;">
                @if($prescription->clinic->phone ?? false)Phone: {{ $prescription->clinic->phone }}@endif
                @if(($prescription->clinic->phone ?? false) && ($prescription->clinic->email ?? false)) | @endif
                @if($prescription->clinic->email ?? false)Email: {{ $prescription->clinic->email }}@endif
            </span>
        </td>
        <td style="vertical-align: middle; text-align: right; width: 200px;">
            <span style="font-size: 12px; font-weight: bold; color: #212529;">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</span><br>
            @if($prescription->doctor->specialization)
                <span style="font-size: {{ $prescription->doctor->specialization_font_size ?? 9 }}px; color: #495057;">{{ $prescription->doctor->specialization }}</span><br>
            @endif
            @if($prescription->doctor->scientific_degree)
                <span style="font-size: 8px; color: #6c757d;">{{ $prescription->doctor->scientific_degree }}</span><br>
            @endif
            @if($prescription->doctor->medical_degrees)
                <span style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 8 }}px; color: #6c757d;">{{ $prescription->doctor->medical_degrees }}</span><br>
            @endif
            @if($prescription->doctor->educational_institution)
                <span style="font-size: 8px; color: #6c757d;">{{ $prescription->doctor->educational_institution }}</span><br>
            @endif
            @if($prescription->doctor->professional_credentials)
                <span style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 8 }}px; color: #6c757d;">{{ $prescription->doctor->professional_credentials }}</span><br>
            @endif
            @if($prescription->doctor->phone)
                <span style="font-size: 8px; color: #495057;">Phone: {{ $prescription->doctor->phone }}</span>
            @endif
        </td>
    </tr>
</table>

<!-- PRESCRIPTION NUMBER & STATUS -->
<table style="width: 100%; margin-bottom: 10px;" cellpadding="2" cellspacing="0">
    <tr>
        <td style="vertical-align: middle;">
            <span style="font-size: 13px; font-weight: bold; color: #0d6efd;">{{ $prescription->prescription_number }}</span><br>
            <span style="font-size: 8px; color: #6c757d;">Created on {{ $prescription->created_at->format('F d, Y') }}</span>
        </td>
        <td style="vertical-align: middle; text-align: right;">
            <span style="background-color: {{ $prescription->status === 'active' ? '#198754' : ($prescription->status === 'completed' ? '#0d6efd' : '#6c757d') }}; color: #fff; padding: 3px 10px; font-size: 9px; font-weight: bold;">{{ ucfirst($prescription->status) }}</span>
        </td>
    </tr>
</table>

<!-- PATIENT INFORMATION -->
<table style="width: 100%; border: 1px solid #dee2e6; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; font-weight: bold; color: #0d6efd;">Patient Information</td>
    </tr>
    <tr>
        <td style="width: 50%; padding: 8px 10px; font-size: 9px; vertical-align: top;">
            <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}<br>
            <strong>Patient ID:</strong> {{ $prescription->patient->patient_id ?? 'N/A' }}<br>
            <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}
        </td>
        <td style="width: 50%; padding: 8px 10px; font-size: 9px; vertical-align: top;">
            <strong>Phone:</strong> {{ $prescription->patient->phone ?? 'Not provided' }}<br>
            <strong>Date of Birth:</strong> @if($prescription->patient->date_of_birth){{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->format('M d, Y') }}@else Not provided @endif<br>
            <strong>Age:</strong> @if($prescription->patient->date_of_birth){{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years @else N/A @endif
        </td>
    </tr>
</table>

<!-- DOCTOR INFORMATION -->
<table style="width: 100%; border: 1px solid #dee2e6; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="2" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; font-weight: bold; color: #0d6efd;">Doctor Information</td>
    </tr>
    <tr>
        <td style="width: 50%; padding: 8px 10px; font-size: 9px; vertical-align: top;">
            <strong>Doctor:</strong> Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}<br>
            <strong>Phone:</strong> {{ $prescription->doctor->phone ?? 'Not provided' }}
        </td>
        <td style="width: 50%; padding: 8px 10px; font-size: 9px; vertical-align: top;">
            <strong>Email:</strong> {{ $prescription->doctor->email ?? 'Not provided' }}<br>
            <strong>Prescribed Date:</strong> {{ $prescription->prescribed_date->format('F d, Y') }}
        </td>
    </tr>
</table>

<!-- DIAGNOSIS -->
@if($prescription->diagnosis)
<table style="width: 100%; border: 1px solid #dee2e6; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; font-weight: bold; color: #0d6efd;">Diagnosis</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 9px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->diagnosis }}</td>
    </tr>
</table>
@endif

<!-- PRESCRIBED MEDICINES -->
@if($prescription->medicines->count() > 0)
<table style="width: 100%; border: 1px solid #dee2e6; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; font-weight: bold; color: #0d6efd;">Prescribed Medicines</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px;">
            @foreach($prescription->medicines as $index => $medicine)
            <table style="width: 100%; background-color: #f8f9fa; border: 1px solid #e9ecef; margin-bottom: {{ $loop->last ? '0' : '8px' }};" cellpadding="4" cellspacing="0">
                <tr>
                    <td colspan="3" style="font-size: 11px; font-weight: bold; color: #0d6efd; padding: 6px 8px; border-bottom: 1px solid #e9ecef;">{{ $index + 1 }}. {{ $medicine->medicine_name }}</td>
                </tr>
                <tr>
                    <td style="width: 33%; padding: 4px 8px;">
                        <span style="font-size: 8px; color: #6c757d;">Dosage</span><br>
                        <strong style="font-size: 9px;">{{ $medicine->dosage ?? 'Not specified' }}</strong>
                    </td>
                    <td style="width: 33%; padding: 4px 8px;">
                        <span style="font-size: 8px; color: #6c757d;">Frequency</span><br>
                        <strong style="font-size: 9px;">{{ $medicine->frequency ?? 'Not specified' }}</strong>
                    </td>
                    <td style="width: 34%; padding: 4px 8px;">
                        <span style="font-size: 8px; color: #6c757d;">Duration</span><br>
                        <strong style="font-size: 9px;">{{ $medicine->duration ?? 'Not specified' }}</strong>
                    </td>
                </tr>
                @if($medicine->instructions)
                <tr>
                    <td colspan="3" style="padding: 4px 8px; border-top: 1px solid #dee2e6;">
                        <span style="font-size: 8px; color: #6c757d;">Instructions</span><br>
                        <span style="font-size: 9px; direction: rtl;">{{ $medicine->instructions }}</span>
                    </td>
                </tr>
                @endif
            </table>
            @endforeach
        </td>
    </tr>
</table>
@endif

<!-- NOTES -->
@if($prescription->notes)
<table style="width: 100%; border: 1px solid #dee2e6; margin-bottom: 10px;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 6px 10px; font-size: 11px; font-weight: bold; color: #0d6efd;">Notes</td>
    </tr>
    <tr>
        <td style="padding: 8px 10px; font-size: 9px; line-height: 1.6; direction: rtl; text-align: right;">{{ $prescription->notes }}</td>
    </tr>
</table>
@endif

<!-- FOOTER -->
<table style="width: 100%; border-top: 1px solid #dee2e6; margin-top: 15px;" cellpadding="4" cellspacing="0">
    <tr>
        <td style="text-align: center; font-size: 7px; color: #6c757d;">
            Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y \a\t g:i A') }}<br>
            This is a computer-generated prescription and is valid without physical signature.
        </td>
    </tr>
</table>

</body>
</html>
