<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            margin-bottom: 15px;
        }

        .clinic-header-table {
            width: 100%;
            margin-bottom: 15px;
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
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0 0 5px 0;
        }

        .clinic-info {
            font-size: 10px;
            color: #666;
            margin: 0;
        }

        .header-divider {
            border-bottom: 2px solid #0d6efd;
            margin-bottom: 15px;
        }

        .prescription-header {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 12px;
            border-left: 3px solid #0d6efd;
        }

        .prescription-number {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 3px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 4px 10px 4px 0;
            vertical-align: top;
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .info-value {
            color: #333;
            font-size: 9px;
            line-height: 1.3;
        }
        
        .section {
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        
        .medicines-list {
            margin-bottom: 10px;
        }

        .medicine-item {
            border: 1px solid #dee2e6;
            border-radius: 3px;
            margin-bottom: 6px;
            padding: 8px;
            background: #fafafa;
        }

        .medicine-name {
            font-weight: bold;
            color: #0d6efd;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .medicine-details {
            display: table;
            width: 100%;
        }

        .medicine-detail-row {
            display: table-row;
        }

        .medicine-detail-cell {
            display: table-cell;
            padding: 1px 8px 1px 0;
            vertical-align: top;
            width: 33.33%;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 8px;
            margin-bottom: 1px;
        }

        .detail-value {
            color: #333;
            font-size: 9px;
        }

        .medicine-instructions {
            margin-top: 4px;
            padding-top: 4px;
            border-top: 1px solid #dee2e6;
            font-size: 8px;
            color: #555;
        }
        
        .diagnosis-box, .notes-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 8px;
            margin-bottom: 8px;
            font-size: 9px;
            line-height: 1.3;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

        .doctor-signature {
            margin-top: 15px;
            text-align: right;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 150px;
            margin: 15px 0 3px auto;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 3px;
            padding: 6px;
            margin-top: 10px;
            font-size: 7px;
            line-height: 1.2;
        }

        .warning-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 2px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @php
            $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($prescription->clinic_id);
        @endphp

        <table class="clinic-header-table">
            <tr>
                @if($clinicLogo)
                    <td style="width: 80px; vertical-align: top; text-align: center;">
                        <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                             alt="Clinic Logo"
                             class="clinic-logo">
                    </td>
                @endif
                <td style="vertical-align: top; text-align: {{ $clinicLogo ? 'left' : 'center' }}; {{ $clinicLogo ? 'padding-left: 15px;' : '' }}">
                    <div class="clinic-name">{{ $prescription->clinic->name ?? 'ConCure Clinic' }}</div>
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
            </tr>
        </table>
        <div class="header-divider"></div>
    </div>

    <!-- Prescription Header -->
    <div class="prescription-header">
        <div class="prescription-number">Prescription #{{ $prescription->prescription_number }}</div>
        <div>Date: {{ $prescription->prescribed_date->format('F d, Y') }}</div>
    </div>

    <!-- Patient and Doctor Information -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="info-label">Patient Information</div>
                <div class="info-value">
                    <strong>{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</strong><br>
                    Patient ID: {{ $prescription->patient->patient_id }}<br>
                    @if($prescription->patient->date_of_birth)
                        DOB: {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->format('M d, Y') }}<br>
                    @endif
                    Gender: {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}<br>
                    @if($prescription->patient->phone)
                        Phone: {{ $prescription->patient->phone }}
                    @endif
                </div>
            </div>
            <div class="info-cell">
                <div class="info-label">Doctor Information</div>
                <div class="info-value">
                    <strong>Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</strong><br>
                    @if($prescription->doctor->phone)
                        Phone: {{ $prescription->doctor->phone }}<br>
                    @endif
                    @if($prescription->doctor->email)
                        Email: {{ $prescription->doctor->email }}
                    @endif
                </div>
            </div>
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
                        <div class="medicine-name">{{ $index + 1 }}. {{ $medicine->medicine_name }}</div>
                        <div class="medicine-details">
                            <div class="medicine-detail-row">
                                <div class="medicine-detail-cell">
                                    <div class="detail-label">Dosage:</div>
                                    <div class="detail-value">{{ $medicine->dosage ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-cell">
                                    <div class="detail-label">Frequency:</div>
                                    <div class="detail-value">{{ $medicine->frequency ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-cell">
                                    <div class="detail-label">Duration:</div>
                                    <div class="detail-value">{{ $medicine->duration ?? 'Not specified' }}</div>
                                </div>
                            </div>
                        </div>
                        @if($medicine->instructions)
                            <div class="medicine-instructions">
                                <strong>Instructions:</strong> {{ $medicine->instructions }}
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

    <!-- Warning Box -->
    <div class="warning-box">
        <div class="warning-title">Important Instructions:</div>
        • Please follow the prescribed dosage and timing strictly<br>
        • Do not stop medication without consulting your doctor<br>
        • Contact your doctor immediately if you experience any adverse reactions<br>
        • Keep medicines out of reach of children
    </div>

    <!-- Doctor Signature -->
    <div class="doctor-signature">
        <div class="signature-line"></div>
        <div style="font-size: 9px;">Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</div>
        <div style="font-size: 7px; color: #666;">Digital Signature</div>
    </div>

    <!-- Footer -->
    <div class="footer">
        Generated by ConCure Clinic Management System on {{ now()->format('F d, Y \a\t g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>
</body>
</html>
