<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 15px;
            background: white;
        }

        /* Professional Medical Prescription Styling */
        .prescription-document {
            border: 2px solid #000;
            padding: 20px;
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
        }

        .medical-symbol {
            font-size: 20px;
            color: #e74c3c;
            margin-right: 8px;
        }

        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .clinic-header-table {
            width: 100%;
        }

        .clinic-logo {
            max-height: 70px;
            max-width: 70px;
            object-fit: contain;
        }

        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin: 0 0 3px 0;
            letter-spacing: 0.5px;
        }

        .clinic-info {
            font-size: 8px;
            color: #333;
            margin: 0;
            line-height: 1.4;
        }

        .doctor-header-info {
            text-align: right;
            font-size: 9px;
            line-height: 1.5;
            color: #000;
        }

        .doctor-name-header {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }


        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            background-color: #fff;
            padding: 6px 10px;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid #000;
            border-bottom: 1px solid #000;
        }

        .section-content {
            border: 2px solid #000;
            border-top: none;
            padding: 10px;
            background: #fff;
        }

        .patient-info-line {
            font-size: 9px;
            margin-bottom: 3px;
        }

        .medicines-list {
            margin-bottom: 0;
        }

        .medicine-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        .medicine-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .medicine-number {
            display: inline-block;
            background: #000;
            color: #fff;
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            font-size: 8px;
            font-weight: bold;
            margin-right: 5px;
            vertical-align: middle;
        }

        .medicine-name {
            font-weight: bold;
            color: #000;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .medicine-details-line {
            font-size: 8px;
            color: #333;
            margin-bottom: 3px;
            padding-left: 21px;
        }

        .medicine-instructions {
            margin-top: 3px;
            margin-left: 21px;
            padding: 5px 8px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 8px;
            color: #000;
            line-height: 1.4;
        }

        .instructions-label {
            font-weight: bold;
            color: #856404;
            display: inline;
        }

        .instructions-text {
            direction: rtl;
            display: inline;
        }

        .diagnosis-box, .notes-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            font-size: 9px;
            line-height: 1.5;
            direction: rtl;
            text-align: right;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #999;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

    </style>
</head>
<body>
    <div class="prescription-document">
        <!-- Header -->
        <div class="header">
        @php
	            // Keep PDF parity: only show logo when the local file exists (same condition as PDF view),
	            // but use a browser-friendly src (base64 data URI) for the actual <img>.
	            $clinicLogoPath = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
	            $clinicLogoSrc = \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($prescription->clinic_id);
        @endphp

        <table class="clinic-header-table">
            <tr>
	                @if($clinicLogoPath && file_exists($clinicLogoPath))
                    <td style="width: 80px; vertical-align: top; text-align: left;">
	                        <img src="{{ $clinicLogoSrc }}"
                             alt="Clinic Logo"
                             class="clinic-logo">
                    </td>
                @endif
                <td style="vertical-align: top; text-align: left; padding-left: 10px;">
                    <div class="clinic-name">
                        <span class="medical-symbol">⚕</span>
                        {{ $prescription->clinic->name ?? 'ConCure Clinic' }}
                    </div>
                    <div class="clinic-info">
                        @if($prescription->clinic->email ?? false)
                            Email: {{ $prescription->clinic->email }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align: top; text-align: right; width: 200px;">
                    <div class="doctor-header-info">
                        <div class="doctor-name-header">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</div>
                        @if($prescription->doctor->specialization)
                            <div style="font-size: {{ $prescription->doctor->specialization_font_size ?? 9 }}px; margin-bottom: 1px;">{{ $prescription->doctor->specialization }}</div>
                        @endif
                        @if($prescription->doctor->scientific_degree)
                            <div style="font-size: 8px; margin-bottom: 1px;">{{ $prescription->doctor->scientific_degree }}</div>
                        @endif
                        @if($prescription->doctor->medical_degrees)
                            <div style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 8 }}px; margin-bottom: 1px;">{{ $prescription->doctor->medical_degrees }}</div>
                        @endif
                        @if($prescription->doctor->educational_institution)
                            <div style="font-size: 8px; margin-bottom: 1px;">{{ $prescription->doctor->educational_institution }}</div>
                        @endif
                        @if($prescription->doctor->professional_credentials)
                            <div style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 8 }}px; margin-bottom: 1px;">{{ $prescription->doctor->professional_credentials }}</div>
                        @endif
                        @if($prescription->doctor->phone)
                            <div style="font-size: 8px; margin-top: 3px;">Phone: {{ $prescription->doctor->phone }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>



    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="section-content">
            <div class="patient-info-line">
                <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'N/A') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Age:</strong>
                @if($prescription->patient->date_of_birth)
                    {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years
                @else
                    N/A
                @endif
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Date:</strong> {{ $prescription->prescribed_date->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <!-- Diagnosis -->
    @if($prescription->diagnosis)
        <div class="section">
            <div class="section-title">Diagnosis</div>
            <div class="section-content">
                <div class="diagnosis-box">
                    {{ $prescription->diagnosis }}
                </div>
            </div>
        </div>
    @endif

    <!-- Prescribed Medicines -->
    @if($prescription->medicines->count() > 0)
        <div class="section">
            <div class="section-title">Prescribed Medicines</div>
            <div class="section-content">
                <div class="medicines-list">
                    @foreach($prescription->medicines as $index => $medicine)
                        <div class="medicine-item">
                            <div class="medicine-name">
                                <span class="medicine-number">{{ $index + 1 }}</span>
                                {{ $medicine->medicine_name }}
                            </div>
                            <div class="medicine-details-line">
                                <strong>Dose:</strong> {{ $medicine->dosage ?? 'N/A' }} &nbsp;|&nbsp;
                                <strong>Frequency:</strong> {{ $medicine->frequency ?? 'N/A' }} &nbsp;|&nbsp;
                                <strong>Duration:</strong> {{ $medicine->duration ?? 'N/A' }}
                            </div>
                            @if($medicine->instructions)
                                <div class="medicine-instructions">
                                    <span class="instructions-label">Instructions:</span> <span class="instructions-text">{{ $medicine->instructions }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Notes -->
    @if($prescription->notes)
        <div class="section">
            <div class="section-title">Additional Notes</div>
            <div class="section-content">
                <div class="notes-box">
                    {{ $prescription->notes }}
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y \a\t g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>
    </div> <!-- Close prescription-document -->
</body>
</html>
