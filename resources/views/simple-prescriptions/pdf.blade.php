<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'amiri', 'dejavu sans', sans-serif;
            font-size: 11px;
            line-height: 1.6;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
            background: white;
        }

        /* Professional Medical Prescription Styling */
        .prescription-document {
            border: 3px solid #2c3e50;
            padding: 25px;
            background: #ffffff;
            max-width: 800px;
            margin: 0 auto;
        }

        .medical-symbol {
            font-size: 24px;
            color: #e74c3c;
            margin-right: 10px;
        }
        
        .header {
            margin-bottom: 10px;
        }

        .clinic-header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .clinic-logo {
            max-height: 80px;
            max-width: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            padding: 1px;
        }

        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 5px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .clinic-info {
            font-size: 9px;
            color: #555;
            margin: 0;
            line-height: 1.5;
        }

        .header-divider {
            border-bottom: 2px solid #2c3e50;
            margin: 15px 0;
        }


        
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .info-cell {
            padding: 0;
            vertical-align: top;
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            color: #ffffff;
            background: #34495e;
            padding: 6px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0;
        }

        .info-value {
            color: #2c3e50;
            font-size: 10px;
            line-height: 1.6;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-top: none;
        }
        
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #ffffff;
            background: #34495e;
            padding: 8px 12px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .medicines-list {
            margin-bottom: 0;
        }

        .medicine-item {
            border: 1px solid #dee2e6;
            background: #ffffff;
            margin-bottom: 10px;
            padding: 12px 15px;
            page-break-inside: avoid;
        }

        .medicine-number {
            display: inline-block;
            background: #34495e;
            color: #ffffff;
            width: 22px;
            height: 22px;
            line-height: 22px;
            text-align: center;
            border-radius: 50%;
            font-size: 10px;
            font-weight: bold;
            margin-right: 8px;
            vertical-align: middle;
        }

        .medicine-name {
            font-weight: bold;
            color: #2c3e50;
            font-size: 12px;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
        }

        .medicine-name-arabic {
            direction: rtl;
            text-align: right;
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #555;
        }

        .medicine-details {
            width: 100%;
            margin-top: 8px;
            border-collapse: separate;
            border-spacing: 5px 0;
        }

        .medicine-detail-cell {
            padding: 8px 10px;
            vertical-align: top;
            width: 33.33%;
            border: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .detail-label {
            font-weight: bold;
            color: #34495e;
            font-size: 8px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
        }

        .detail-value {
            color: #2c3e50;
            font-size: 11px;
            line-height: 1.6;
            font-weight: 600;
            font-family: 'amiri', 'dejavusans', sans-serif;
        }

        .medicine-instructions {
            margin-top: 10px;
            padding: 10px 12px;
            background: #fff9e6;
            border-left: 3px solid #f39c12;
            font-size: 10px;
            color: #2c3e50;
            line-height: 1.6;
            direction: ltr;
            text-align: left;
        }

        .instructions-label {
            font-weight: bold;
            color: #d68910;
            display: inline;
        }

        .instructions-text {
            direction: rtl;
            display: inline;
        }

        .diagnosis-box, .notes-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            font-size: 11px;
            line-height: 1.7;
            border-left: 4px solid #3498db;
            direction: rtl;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }

        .doctor-signature {
            margin-top: 25px;
            text-align: right;
            padding: 0;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            width: 180px;
            margin: 15px 0 5px auto;
        }

        .signature-name {
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
        }

        .signature-note {
            font-size: 8px;
            color: #6c757d;
            font-style: italic;
        }

    </style>
</head>
<body>
    <div class="prescription-document">
        <!-- Header -->
        <div class="header">
        @php
            $clinicLogo = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
        @endphp

        <table class="clinic-header-table">
            <tr>
                @if($clinicLogo && file_exists($clinicLogo))
                    <td style="width: 80px; vertical-align: top; text-align: center;">
                        <img src="{{ $clinicLogo }}"
                             alt="Clinic Logo"
                             class="clinic-logo">
                    </td>
                @endif
                <td style="vertical-align: top; text-align: {{ $clinicLogo ? 'left' : 'center' }}; {{ $clinicLogo ? 'padding-left: 15px;' : '' }}">
                    <div class="clinic-name">
                        <span class="medical-symbol">⚕</span>
                        {{ $prescription->clinic->name ?? 'ConCure Clinic' }}
                    </div>
                    <div class="clinic-info">
                        @if($prescription->clinic->address ?? false)
                            {{ $prescription->clinic->address }}<br>
                        @endif
                        @if($prescription->clinic->phone ?? false)
                            Phone: {{ $prescription->clinic->phone }} |
                        @endif
                        @if($prescription->clinic->email ?? false)
                            Email: {{ $prescription->clinic->email }}
                        @endif
                    </div>
                </td>
                <td style="vertical-align: top; text-align: right; width: 250px; padding: 10px;">
                    <div style="font-size: 9px; color: #2c3e50; line-height: 1.6;">
                        <strong style="font-size: 10px;">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</strong><br>
                        @if($prescription->doctor->phone)
                            Phone: {{ $prescription->doctor->phone }}<br>
                        @endif
                        @if($prescription->doctor->email)
                            Email: {{ $prescription->doctor->email }}<br>
                        @endif
                        <strong>Date: {{ $prescription->prescribed_date->format('F d, Y') }}</strong>
                    </div>
                </td>
            </tr>
        </table>
        <div class="header-divider"></div>
    </div>



    <!-- Patient Information -->
    <div style="margin-bottom: 15px;">
        <div class="info-label">Patient Information</div>
        <div class="info-value" style="padding: 12px 15px;">
            <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}
            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}
            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Age:</strong>
            @if($prescription->patient->date_of_birth)
                {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years
            @else
                Not specified
            @endif
        </div>
    </div>

    <!-- Diagnosis -->
    @if($prescription->diagnosis)
        <div class="section">
            <div class="section-title">Diagnosis</div>
            <div class="diagnosis-box">
                {{ $prescription->diagnosis }}
            </div>
        </div>
    @endif

    <!-- Prescribed Medicines -->
    @if($prescription->medicines->count() > 0)
        <div class="section">
            <div class="section-title">Prescribed Medicines</div>
            <div class="medicines-list">
                @foreach($prescription->medicines as $index => $medicine)
                    <div class="medicine-item">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 25%; vertical-align: top; padding: 8px;">
                                    <div class="medicine-name">
                                        <span class="medicine-number">{{ $index + 1 }}</span>
                                        {{ $medicine->medicine_name }}
                                    </div>
                                </td>
                                <td style="width: 25%; vertical-align: top; padding: 8px; border: 1px solid #dee2e6; background: #f8f9fa;">
                                    <span class="detail-label">Dosage</span>
                                    <div class="detail-value" style="text-align: center;">{{ $medicine->dosage ?? 'Not specified' }}</div>
                                </td>
                                <td style="width: 25%; vertical-align: top; padding: 8px; border: 1px solid #dee2e6; background: #f8f9fa;">
                                    <span class="detail-label">Frequency</span>
                                    <div class="detail-value" style="text-align: center;">{{ $medicine->frequency ?? 'Not specified' }}</div>
                                </td>
                                <td style="width: 25%; vertical-align: top; padding: 8px; border: 1px solid #dee2e6; background: #f8f9fa;">
                                    <span class="detail-label">Duration</span>
                                    <div class="detail-value" style="text-align: center;">{{ $medicine->duration ?? 'Not specified' }}</div>
                                </td>
                            </tr>
                        </table>
                        @if($medicine->instructions)
                            <div class="medicine-instructions">
                                <span class="instructions-label">Instructions:</span> <span class="instructions-text">{{ $medicine->instructions }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Notes -->
    @if($prescription->notes)
        <div class="section">
            <div class="section-title">Additional Notes</div>
            <div class="notes-box">
                {{ $prescription->notes }}
            </div>
        </div>
    @endif



    <!-- Doctor Signature -->
    <div class="doctor-signature">
        <div class="signature-line"></div>
        <div class="signature-name">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</div>
        <div class="signature-note">Digital Signature</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Generated by ConCure Clinic Management System on {{ now()->format('F d, Y \a\t g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>
    </div> <!-- Close prescription-document -->
</body>
</html>
