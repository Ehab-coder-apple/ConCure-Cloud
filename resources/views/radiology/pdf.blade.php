<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radiology Request - {{ $radiologyRequest->request_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #2c3e50;
            background: white;
        }

        .prescription-document {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }

        /* Header Styles - Matching Prescription */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #34495e;
            padding-bottom: 10px;
        }

        .header-left {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
        }

        .clinic-logo {
            max-height: 80px;
            max-width: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .header-center {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: middle;
            padding: 0 15px;
        }

        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .clinic-info {
            font-size: 8px;
            color: #555;
            line-height: 1.5;
        }

        .header-right {
            display: table-cell;
            width: 25%;
            text-align: right;
            vertical-align: middle;
        }

        .doctor-info div {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .doctor-info div:nth-child(2) {
            font-size: 10px;
            margin-top: 3px;
            margin-bottom: 2px;
        }

        .doctor-info div:nth-child(3) {
            font-size: 9px;
            margin-top: 2px;
            margin-bottom: 3px;
        }

        .doctor-info div:nth-child(4) {
            font-size: 9px;
        }

        /* Document Title */
        .document-title {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .document-title h2 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .document-title .request-number {
            font-size: 12px;
            font-weight: bold;
            margin-top: 3px;
        }

        .document-title .date {
            font-size: 9px;
            margin-top: 2px;
        }

        /* Section Styles - Matching Prescription */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #e8eef3;
            padding: 8px 12px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid #34495e;
        }

        .section {
            margin-bottom: 15px;
        }

        /* Patient Information */
        .info-value {
            color: #2c3e50;
            font-size: 10px;
            background: #f8f9fa;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
        }

        /* Two Column Layout */
        .two-column {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 5px;
        }

        .column:first-child {
            padding-left: 0;
        }

        .column:last-child {
            padding-right: 0;
        }

        /* Info Grid */
        .info-grid {
            background: #f8f9fa;
            padding: 10px 12px;
            border: 1px solid #dee2e6;
        }

        .info-item {
            margin-bottom: 6px;
            font-size: 10px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item strong {
            color: #34495e;
            font-weight: 600;
            min-width: 80px;
            display: inline-block;
        }

        /* Clinical Notes Box */
        .clinical-box {
            background: #f8f9fa;
            border-left: 3px solid #3498db;
            padding: 10px 12px;
            margin: 8px 0;
            font-size: 10px;
            line-height: 1.5;
        }

        .clinical-box strong {
            color: #2c3e50;
            display: block;
            margin-bottom: 4px;
        }

        /* Tests List */
        .test-list {
            margin-bottom: 0;
        }

        .test-item {
            border: 1px solid #dee2e6;
            background: #ffffff;
            margin-bottom: 6px;
            padding: 6px 10px;
            page-break-inside: avoid;
        }

        .test-name {
            font-weight: bold;
            color: #2c3e50;
            font-size: 11px;
            margin-bottom: 3px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ecf0f1;
        }

        .test-number {
            display: inline-block;
            background: #34495e;
            color: #ffffff;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            border-radius: 50%;
            font-size: 9px;
            font-weight: bold;
            margin-right: 6px;
            vertical-align: middle;
        }

        .test-details {
            font-size: 9px;
            color: #555;
            line-height: 1.4;
            padding-left: 26px;
        }

        .test-details strong {
            color: #34495e;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 5px;
        }

        .badge-urgent {
            background: #e74c3c;
            color: white;
        }

        .badge-contrast {
            background: #f39c12;
            color: white;
        }

        .badge-normal {
            background: #95a5a6;
            color: white;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #95a5a6;
            border-top: 1px solid #ecf0f1;
            padding-top: 8px;
        }

        @page {
            margin: 10mm;
            size: A4;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .prescription-document {
                padding: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-document">
        <!-- Header Section - Table-based (Matching Prescription) -->
        @php
            $clinicLogo = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($radiologyRequest->doctor->clinic_id);
        @endphp

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border-bottom: 2px solid #34495e; padding-bottom: 10px;">
            <tr>
                @if($clinicLogo && file_exists($clinicLogo))
                    <td style="width: 100px; vertical-align: middle; text-align: center;">
                        <img src="{{ $clinicLogo }}"
                             alt="Clinic Logo"
                             class="clinic-logo">
                    </td>
                @endif
                <td style="vertical-align: middle; text-align: center;">
                    <div class="clinic-name">
                        {{ $radiologyRequest->doctor->clinic->name ?? 'Medical Clinic' }}
                    </div>
                    <div class="clinic-info">
                        @if($radiologyRequest->doctor->clinic && $radiologyRequest->doctor->clinic->address)
                            {{ $radiologyRequest->doctor->clinic->address }}<br>
                        @endif
                        @if($radiologyRequest->doctor->clinic && $radiologyRequest->doctor->clinic->phone)
                            Phone: {{ $radiologyRequest->doctor->clinic->phone }}
                        @endif
                        @if($radiologyRequest->doctor->clinic && $radiologyRequest->doctor->clinic->email)
                            &nbsp;&nbsp;|&nbsp;&nbsp;Email: {{ $radiologyRequest->doctor->clinic->email }}
                        @endif
                        <br>
                        <strong>Date:</strong> {{ $radiologyRequest->requested_date->format('F d, Y') }}
                    </div>
                </td>
                <td style="vertical-align: middle; text-align: right; width: 220px;">
                    <div class="doctor-info">
                        <div>Dr. {{ $radiologyRequest->doctor->first_name }} {{ $radiologyRequest->doctor->last_name }}</div>
                        @if($radiologyRequest->doctor->scientific_degree)
                            <div style="font-size: 10px; color: #555; margin-top: 3px; margin-bottom: 2px;">{{ $radiologyRequest->doctor->scientific_degree }}</div>
                        @endif
                        @if($radiologyRequest->doctor->educational_institution)
                            <div style="font-size: 9px; color: #777; margin-top: 2px; margin-bottom: 3px;">{{ $radiologyRequest->doctor->educational_institution }}</div>
                        @endif
                        @if($radiologyRequest->doctor->phone)
                            <div style="font-size: 9px; color: #555;">Phone: {{ $radiologyRequest->doctor->phone }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Document Title -->
        <div class="document-title">
            <h2>RADIOLOGY REQUEST</h2>
            <div class="request-number">{{ $radiologyRequest->request_number }}</div>
            @if($radiologyRequest->due_date)
                <div class="date">Due Date: {{ $radiologyRequest->due_date->format('F d, Y') }}</div>
            @endif
        </div>

        <!-- Patient Information -->
        <div style="margin-bottom: 15px;">
            <div class="section-title">PATIENT INFORMATION</div>
            <div class="info-value" style="padding: 12px 15px;">
                <strong>Name:</strong> {{ $radiologyRequest->patient->first_name }} {{ $radiologyRequest->patient->last_name }}
                &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                <strong>Gender:</strong> {{ ucfirst($radiologyRequest->patient->gender ?? 'Not specified') }}
                &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                <strong>Age:</strong>
                @if($radiologyRequest->patient->date_of_birth)
                    {{ \Carbon\Carbon::parse($radiologyRequest->patient->date_of_birth)->age }} years
                @else
                    Not specified
                @endif
                &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                <strong>ID:</strong> {{ $radiologyRequest->patient->patient_id ?? 'N/A' }}
                @if($radiologyRequest->patient->phone)
                    <br><strong>Phone:</strong> {{ $radiologyRequest->patient->phone }}
                @endif
                @if($radiologyRequest->patient->allergies)
                    <br><strong style="color: #e74c3c;">⚠️ Allergies:</strong> <span style="color: #e74c3c;">{{ $radiologyRequest->patient->allergies }}</span>
                @endif
            </div>
        </div>

        <!-- Clinical Information -->
        <div class="section">
            <div class="section-title">CLINICAL INFORMATION</div>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Priority:</strong> {{ ucfirst($radiologyRequest->priority) }}
                    @if($radiologyRequest->priority === 'urgent')
                        <span class="badge badge-urgent">URGENT</span>
                    @elseif($radiologyRequest->priority === 'stat')
                        <span class="badge badge-urgent">STAT</span>
                    @else
                        <span class="badge badge-normal">NORMAL</span>
                    @endif
                </div>
                @if($radiologyRequest->suspected_diagnosis)
                    <div class="info-item">
                        <strong>Diagnosis:</strong> {{ $radiologyRequest->suspected_diagnosis }}
                    </div>
                @endif
                @if($radiologyRequest->clinical_notes)
                    <div class="info-item">
                        <strong>Clinical Notes:</strong> {{ $radiologyRequest->clinical_notes }}
                    </div>
                @endif
                @if($radiologyRequest->clinical_history)
                    <div class="info-item">
                        <strong>Clinical History:</strong> {{ $radiologyRequest->clinical_history }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Tests Required -->
        <div class="section">
            <div class="section-title">TESTS REQUIRED ({{ $radiologyRequest->tests->count() }} {{ $radiologyRequest->tests->count() === 1 ? 'Test' : 'Tests' }})</div>
            <div class="test-list">
                @foreach($radiologyRequest->tests as $index => $test)
                    <div class="test-item">
                        <div class="test-name">
                            <span class="test-number">{{ $index + 1 }}</span>
                            {{ $test->test_name_display ?? $test->test_name }}
                            @if($test->urgent)
                                <span class="badge badge-urgent">URGENT</span>
                            @endif
                            @if($test->with_contrast)
                                <span class="badge badge-contrast">WITH CONTRAST</span>
                            @endif
                        </div>
                        <div class="test-details">
                            @if($test->test_category)
                                <strong>Category:</strong> {{ ucwords(str_replace('_', ' ', $test->test_category)) }}
                            @endif
                            @if($test->radiologyTest && $test->radiologyTest->estimated_duration_minutes)
                                &nbsp;&nbsp;|&nbsp;&nbsp;<strong>Duration:</strong> {{ $test->radiologyTest->estimated_duration_minutes }} min
                            @endif
                            @if($test->clinical_indication)
                                <br><strong>Indication:</strong> {{ $test->clinical_indication }}
                            @endif
                            @if($test->instructions)
                                <br><strong>Instructions:</strong> {{ $test->instructions }}
                            @endif
                            @if($test->special_requirements)
                                <br><strong>Requirements:</strong> {{ $test->special_requirements }}
                            @endif
                            @if($test->radiologyTest && $test->radiologyTest->preparation_instructions)
                                <br><strong>Preparation:</strong> {{ $test->radiologyTest->preparation_instructions }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Radiology Center & Additional Notes -->
        @if($radiologyRequest->radiology_center_name || $radiologyRequest->notes)
            <div class="two-column">
                @if($radiologyRequest->radiology_center_name)
                    <div class="column">
                        <div class="section">
                            <div class="section-title">RADIOLOGY CENTER</div>
                            <div class="info-grid">
                                @if($radiologyRequest->radiology_center_name)
                                    <div class="info-item">
                                        <strong>Name:</strong> {{ $radiologyRequest->radiology_center_name }}
                                    </div>
                                @endif
                                @if($radiologyRequest->radiology_center_phone)
                                    <div class="info-item">
                                        <strong>Phone:</strong> {{ $radiologyRequest->radiology_center_phone }}
                                    </div>
                                @endif
                                @if($radiologyRequest->radiology_center_email)
                                    <div class="info-item">
                                        <strong>Email:</strong> {{ $radiologyRequest->radiology_center_email }}
                                    </div>
                                @endif
                                @if($radiologyRequest->radiology_center_address)
                                    <div class="info-item">
                                        <strong>Address:</strong> {{ $radiologyRequest->radiology_center_address }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if($radiologyRequest->notes)
                    <div class="column">
                        <div class="section">
                            <div class="section-title">ADDITIONAL NOTES</div>
                            <div class="clinical-box">
                                {{ $radiologyRequest->notes }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            Generated by ConCure Clinic Management System on {{ now()->format('F d, Y 	 g:i A') }}<br>
            This is a computer-generated document and is valid without physical signature.
        </div>
    </div>
</body>
</html>
