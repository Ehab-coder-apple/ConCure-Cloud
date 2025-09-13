<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Radiology Request') }} - {{ $radiologyRequest->request_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #2c3e50;
            margin: 0;
            padding: 15px;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header-left h1 {
            color: #2c3e50;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .header-left .subtitle {
            color: #7f8c8d;
            font-size: 10px;
            font-weight: 500;
        }

        .header-right {
            text-align: right;
            font-size: 10px;
        }

        .header-right .request-number {
            font-size: 14px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 2px;
        }

        .header-right .date {
            color: #7f8c8d;
        }

        .two-column {
            display: flex;
            gap: 15px;
            margin-bottom: 12px;
        }

        .column {
            flex: 1;
        }

        .section {
            border: 1px solid #ecf0f1;
            border-radius: 4px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 6px 12px;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-content {
            padding: 10px 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 15px;
            margin-bottom: 8px;
        }

        .info-item {
            display: flex;
            align-items: baseline;
        }

        .info-label {
            font-weight: 600;
            color: #34495e;
            min-width: 70px;
            font-size: 10px;
        }

        .info-value {
            color: #2c3e50;
            flex: 1;
        }

        .priority-badge, .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-normal { background: #95a5a6; color: white; }
        .priority-urgent { background: #f39c12; color: white; }
        .priority-stat { background: #e74c3c; color: white; }

        .test-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .test-item {
            border: 1px solid #ecf0f1;
            border-radius: 3px;
            padding: 8px;
            background: #fafbfc;
            break-inside: avoid;
        }

        .test-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 11px;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .test-badges {
            display: flex;
            gap: 3px;
        }

        .test-badge {
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-urgent { background: #e74c3c; color: white; }
        .badge-contrast { background: #f39c12; color: white; }

        .test-details {
            font-size: 9px;
            color: #7f8c8d;
            line-height: 1.2;
        }

        .test-details strong {
            color: #34495e;
        }

        .clinical-text {
            background: #f8f9fa;
            border-left: 3px solid #3498db;
            padding: 8px 10px;
            margin: 6px 0;
            font-size: 10px;
            line-height: 1.4;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #95a5a6;
            border-top: 1px solid #ecf0f1;
            padding-top: 10px;
        }

        .urgent-text {
            color: #e74c3c;
            font-weight: 600;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
                font-size: 10px;
            }
            .section {
                break-inside: avoid;
                margin-bottom: 8px;
            }
            .test-item {
                break-inside: avoid;
            }
            .header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }
        }

        @page {
            margin: 0.5in;
            size: A4;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>{{ __('RADIOLOGY REQUEST') }}</h1>
            <div class="subtitle">{{ __('Imaging & Diagnostic Services') }}</div>
        </div>
        <div class="header-right">
            <div class="request-number">{{ $radiologyRequest->request_number }}</div>
            <div class="date">{{ $radiologyRequest->requested_date->format('M d, Y') }}</div>
            @if($radiologyRequest->due_date)
            <div class="date">{{ __('Due') }}: {{ $radiologyRequest->due_date->format('M d, Y') }}</div>
            @endif
        </div>
    </div>

    <!-- Patient & Clinical Information -->
    <div class="two-column">
        <div class="column">
            <div class="section">
                <div class="section-header">{{ __('Patient Information') }}</div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">{{ __('Name') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->patient->full_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ __('ID') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->patient->patient_id }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ __('Age') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->patient->age }}y</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ __('Gender') }}:</span>
                            <span class="info-value">{{ ucfirst($radiologyRequest->patient->gender) }}</span>
                        </div>
                        @if($radiologyRequest->patient->phone)
                        <div class="info-item">
                            <span class="info-label">{{ __('Phone') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->patient->phone }}</span>
                        </div>
                        @endif
                        @if($radiologyRequest->patient->email)
                        <div class="info-item">
                            <span class="info-label">{{ __('Email') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->patient->email }}</span>
                        </div>
                        @endif
                    </div>
                    @if($radiologyRequest->patient->allergies)
                    <div class="info-item">
                        <span class="info-label">{{ __('Allergies') }}:</span>
                        <span class="info-value urgent-text">⚠️ {{ $radiologyRequest->patient->allergies }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="column">
            <div class="section">
                <div class="section-header">{{ __('Clinical Information') }}</div>
                <div class="section-content">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">{{ __('Doctor') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->doctor->full_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ __('Priority') }}:</span>
                            <span class="priority-badge priority-{{ $radiologyRequest->priority }}">{{ $radiologyRequest->priority_display }}</span>
                        </div>
                    </div>

                    @if($radiologyRequest->suspected_diagnosis)
                    <div class="info-item" style="margin-bottom: 6px;">
                        <span class="info-label">{{ __('Diagnosis') }}:</span>
                        <span class="info-value">{{ $radiologyRequest->suspected_diagnosis }}</span>
                    </div>
                    @endif

                    @if($radiologyRequest->clinical_notes)
                    <div class="clinical-text">
                        <strong>{{ __('Clinical Notes') }}:</strong><br>
                        {{ $radiologyRequest->clinical_notes }}
                    </div>
                    @endif

                    @if($radiologyRequest->clinical_history)
                    <div class="clinical-text">
                        <strong>{{ __('Clinical History') }}:</strong><br>
                        {{ $radiologyRequest->clinical_history }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Tests Required -->
    <div class="section">
        <div class="section-header">{{ __('Tests Required') }} ({{ $radiologyRequest->tests->count() }} {{ $radiologyRequest->tests->count() === 1 ? 'test' : 'tests' }})</div>
        <div class="section-content">
            <div class="test-grid">
                @foreach($radiologyRequest->tests as $index => $test)
                <div class="test-item">
                    <div class="test-name">
                        <span>{{ $index + 1 }}. {{ $test->test_name_display }}</span>
                        <div class="test-badges">
                            @if($test->urgent)
                            <span class="test-badge badge-urgent">{{ __('URGENT') }}</span>
                            @endif
                            @if($test->with_contrast)
                            <span class="test-badge badge-contrast">{{ __('CONTRAST') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($test->radiologyTest)
                    <div class="test-details">
                        <strong>{{ __('Category') }}:</strong> {{ ucwords(str_replace('_', ' ', $test->test_category)) }}
                        @if($test->radiologyTest->estimated_duration_minutes)
                        • <strong>{{ __('Duration') }}:</strong> {{ $test->estimated_duration }}
                        @endif
                    </div>
                    @endif

                    @if($test->clinical_indication)
                    <div class="test-details">
                        <strong>{{ __('Indication') }}:</strong> {{ $test->clinical_indication }}
                    </div>
                    @endif

                    @if($test->instructions)
                    <div class="test-details">
                        <strong>{{ __('Instructions') }}:</strong> {{ $test->instructions }}
                    </div>
                    @endif

                    @if($test->special_requirements)
                    <div class="test-details">
                        <strong>{{ __('Requirements') }}:</strong> {{ $test->special_requirements }}
                    </div>
                    @endif

                    @if($test->radiologyTest && $test->radiologyTest->preparation_instructions)
                    <div class="test-details">
                        <strong>{{ __('Preparation') }}:</strong> {{ $test->radiologyTest->preparation_instructions }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    @if($radiologyRequest->radiology_center_name || $radiologyRequest->radiology_center_phone || $radiologyRequest->radiology_center_email || $radiologyRequest->notes)
    <div class="two-column">
        @if($radiologyRequest->radiology_center_name || $radiologyRequest->radiology_center_phone || $radiologyRequest->radiology_center_email)
        <div class="column">
            <div class="section">
                <div class="section-header">{{ __('Radiology Center') }}</div>
                <div class="section-content">
                    <div class="info-grid">
                        @if($radiologyRequest->radiology_center_name)
                        <div class="info-item">
                            <span class="info-label">{{ __('Name') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->radiology_center_name }}</span>
                        </div>
                        @endif
                        @if($radiologyRequest->radiology_center_phone)
                        <div class="info-item">
                            <span class="info-label">{{ __('Phone') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->radiology_center_phone }}</span>
                        </div>
                        @endif
                        @if($radiologyRequest->radiology_center_whatsapp)
                        <div class="info-item">
                            <span class="info-label">{{ __('WhatsApp') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->radiology_center_whatsapp }}</span>
                        </div>
                        @endif
                        @if($radiologyRequest->radiology_center_email)
                        <div class="info-item">
                            <span class="info-label">{{ __('Email') }}:</span>
                            <span class="info-value">{{ $radiologyRequest->radiology_center_email }}</span>
                        </div>
                        @endif
                    </div>
                    @if($radiologyRequest->radiology_center_address)
                    <div class="info-item">
                        <span class="info-label">{{ __('Address') }}:</span>
                        <span class="info-value">{{ $radiologyRequest->radiology_center_address }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($radiologyRequest->notes)
        <div class="column">
            <div class="section">
                <div class="section-header">{{ __('Additional Notes') }}</div>
                <div class="section-content">
                    <div class="clinical-text">
                        {{ $radiologyRequest->notes }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>{{ __('Generated by ConCure Clinic Management System') }} • {{ now()->format('M d, Y g:i A') }}</div>
        <div style="margin-top: 3px;">{{ __('This is a computer-generated document and does not require a signature') }}</div>
    </div>
</body>
</html>
