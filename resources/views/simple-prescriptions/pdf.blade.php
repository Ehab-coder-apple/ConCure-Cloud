<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', 'DejaVu Serif', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
            padding: 20px;
            background: white;
        }

        /* Professional Medical Prescription Styling */
        .prescription-document {
            border: 2px solid #2c3e50;
            border-radius: 10px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .medical-symbol {
            font-size: 24px;
            color: #e74c3c;
            margin-right: 10px;
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
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 8px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .clinic-info {
            font-size: 11px;
            color: #555;
            margin: 0;
            line-height: 1.3;
        }

        .header-divider {
            border-bottom: 3px double #2c3e50;
            margin-bottom: 20px;
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
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 13px;
            line-height: 1.4;
            font-weight: 600;
            text-align: center;
            padding: 5px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        
        .section {
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #34495e;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .medicines-list {
            margin-bottom: 5px;
        }

        /* Dynamic sizing based on number of medicines */
        .medicines-list[data-count="1"] .medicine-item,
        .medicines-list[data-count="2"] .medicine-item,
        .medicines-list[data-count="3"] .medicine-item {
            padding: 12px 15px;
            margin-bottom: 10px;
        }

        .medicines-list[data-count="4"] .medicine-item,
        .medicines-list[data-count="5"] .medicine-item {
            padding: 8px 12px;
            margin-bottom: 6px;
        }

        .medicines-list[data-count="6"] .medicine-item,
        .medicines-list[data-count="7"] .medicine-item {
            padding: 6px 10px;
            margin-bottom: 4px;
        }

        .medicines-list[data-count="8"] .medicine-item,
        .medicines-list[data-count="9"] .medicine-item,
        .medicines-list[data-count="10"] .medicine-item {
            padding: 4px 8px;
            margin-bottom: 3px;
        }

        /* For 11+ medicines - ultra compact */
        .medicines-list[data-count="11"] .medicine-item,
        .medicines-list[data-count="12"] .medicine-item,
        .medicines-list[data-count="13"] .medicine-item,
        .medicines-list[data-count="14"] .medicine-item,
        .medicines-list[data-count="15"] .medicine-item,
        .medicines-list[data-count="16"] .medicine-item,
        .medicines-list[data-count="17"] .medicine-item,
        .medicines-list[data-count="18"] .medicine-item,
        .medicines-list[data-count="19"] .medicine-item,
        .medicines-list[data-count="20"] .medicine-item {
            padding: 3px 6px;
            margin-bottom: 2px;
        }

        .medicine-item {
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }



        /* Dynamic font sizing for medicine names */
        .medicines-list[data-count="1"] .medicine-name,
        .medicines-list[data-count="2"] .medicine-name,
        .medicines-list[data-count="3"] .medicine-name {
            font-size: 13px;
            margin-bottom: 8px;
            padding-bottom: 4px;
        }

        .medicines-list[data-count="4"] .medicine-name,
        .medicines-list[data-count="5"] .medicine-name {
            font-size: 11px;
            margin-bottom: 5px;
            padding-bottom: 3px;
        }

        .medicines-list[data-count="6"] .medicine-name,
        .medicines-list[data-count="7"] .medicine-name {
            font-size: 10px;
            margin-bottom: 4px;
            padding-bottom: 2px;
        }

        .medicines-list[data-count="8"] .medicine-name,
        .medicines-list[data-count="9"] .medicine-name,
        .medicines-list[data-count="10"] .medicine-name {
            font-size: 9px;
            margin-bottom: 3px;
            padding-bottom: 2px;
        }

        .medicines-list[data-count="11"] .medicine-name,
        .medicines-list[data-count="12"] .medicine-name,
        .medicines-list[data-count="13"] .medicine-name,
        .medicines-list[data-count="14"] .medicine-name,
        .medicines-list[data-count="15"] .medicine-name,
        .medicines-list[data-count="16"] .medicine-name,
        .medicines-list[data-count="17"] .medicine-name,
        .medicines-list[data-count="18"] .medicine-name,
        .medicines-list[data-count="19"] .medicine-name,
        .medicines-list[data-count="20"] .medicine-name {
            font-size: 8px;
            margin-bottom: 2px;
            padding-bottom: 1px;
        }

        .medicine-name {
            font-weight: bold;
            color: #2c3e50;
            text-transform: capitalize;
            border-bottom: 1px solid #ecf0f1;
        }

        .medicine-details {
            display: table;
            width: 100%;
            margin-top: 3px;
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
            color: #7f8c8d;
            font-size: 10px;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Dynamic sizing for detail values */
        .medicines-list[data-count="1"] .detail-value,
        .medicines-list[data-count="2"] .detail-value,
        .medicines-list[data-count="3"] .detail-value {
            font-size: 11px;
            padding: 4px 6px;
        }

        .medicines-list[data-count="4"] .detail-value,
        .medicines-list[data-count="5"] .detail-value {
            font-size: 9px;
            padding: 3px 4px;
        }

        .medicines-list[data-count="6"] .detail-value,
        .medicines-list[data-count="7"] .detail-value {
            font-size: 8px;
            padding: 2px 3px;
        }

        .medicines-list[data-count="8"] .detail-value,
        .medicines-list[data-count="9"] .detail-value,
        .medicines-list[data-count="10"] .detail-value {
            font-size: 7px;
            padding: 1px 2px;
        }

        .medicines-list[data-count="11"] .detail-value,
        .medicines-list[data-count="12"] .detail-value,
        .medicines-list[data-count="13"] .detail-value,
        .medicines-list[data-count="14"] .detail-value,
        .medicines-list[data-count="15"] .detail-value,
        .medicines-list[data-count="16"] .detail-value,
        .medicines-list[data-count="17"] .detail-value,
        .medicines-list[data-count="18"] .detail-value,
        .medicines-list[data-count="19"] .detail-value,
        .medicines-list[data-count="20"] .detail-value {
            font-size: 6px;
            padding: 1px;
        }

        .detail-value {
            color: #2c3e50;
            line-height: 1.2;
            font-weight: 600;
            text-align: center;
            background: #f8f9fa;
            border-radius: 3px;
            border: 1px solid #e9ecef;
            margin-bottom: 2px;
        }

        .medicine-instructions {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #bdc3c7;
            font-size: 10px;
            color: #34495e;
            font-style: italic;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
        }
        
        .diagnosis-box, .notes-box {
            background: #ffffff;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            font-size: 11px;
            line-height: 1.5;
            border-left: 4px solid #3498db;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #34495e;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            font-style: italic;
        }

        .doctor-signature {
            margin-top: 30px;
            text-align: right;
            padding: 15px 0;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            width: 200px;
            margin: 20px 0 8px auto;
        }


    </style>
</head>
<body>
    <div class="prescription-document">
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
            </tr>
        </table>
        <div class="header-divider"></div>
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
                        Email: {{ $prescription->doctor->email }}<br>
                    @endif
                    <strong>Date: {{ $prescription->prescribed_date->format('F d, Y') }}</strong>
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
            <div class="medicines-list" data-count="{{ $prescription->medicines->count() }}">
                @foreach($prescription->medicines as $index => $medicine)
                    <div class="medicine-item">
                        <div class="medicine-name">{{ $index + 1 }}. {{ $medicine->medicine_name }}</div>

                        {{-- Show values for all medicines without labels --}}
                        <div class="medicine-details">
                            <div class="medicine-detail-row">
                                <div class="medicine-detail-cell">
                                    <div class="detail-value">{{ $medicine->dosage ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-cell">
                                    <div class="detail-value">{{ $medicine->frequency ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-cell">
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
    </div> <!-- Close prescription-document -->
</body>
</html>
