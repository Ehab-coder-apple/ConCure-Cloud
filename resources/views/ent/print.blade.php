<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ENT Record') }} - {{ $entRecord->patient->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            background: white;
            font-size: 13px;
        }

        .container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }

        .header {
            border-bottom: 2px solid #20b2aa;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #20b2aa;
        }

        .clinic-subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        .record-title {
            font-size: 14px;
            font-weight: bold;
            margin: 12px 0 8px 0;
            color: #20b2aa;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }

        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .col {
            flex: 1;
            min-width: 150px;
        }

        .col-full {
            flex: 0 0 100%;
        }

        .label {
            font-weight: bold;
            color: #666;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .value {
            color: #333;
            padding: 3px 0;
            font-size: 12px;
            line-height: 1.4;
        }

        .section {
            page-break-inside: avoid;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            margin: 8px 0;
            border-collapse: collapse;
        }

        table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #999;
        }

        .print-date {
            text-align: right;
            margin-bottom: 15px;
            font-size: 11px;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
                background: white;
            }

            .container {
                margin: 0;
                padding: 15mm;
                width: 100%;
                height: auto;
            }

            .print-date {
                display: none;
            }

            .section {
                page-break-inside: avoid;
            }

            html, body {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ config('app.name', 'ConCure Clinic') }}</div>
            <div class="clinic-subtitle">{{ __('ENT Record Details') }}</div>
        </div>

        <div class="print-date">{{ __('Printed on') }}: {{ now()->format('Y-m-d H:i') }}</div>

        <!-- Basic Information Section -->
        <div class="section">
            <div class="record-title">{{ __('Basic Information') }}</div>
            <div class="row">
                <div class="col">
                    <div class="label">{{ __('Patient Name') }}</div>
                    <div class="value">{{ $entRecord->patient->full_name }}</div>
                </div>
                <div class="col">
                    <div class="label">{{ __('Patient ID') }}</div>
                    <div class="value">{{ $entRecord->patient->patient_id }}</div>
                </div>
                <div class="col">
                    <div class="label">{{ __('Visit Date') }}</div>
                    <div class="value">{{ $entRecord->visit_date->format('Y-m-d') }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="label">{{ __('Age') }}</div>
                    <div class="value">{{ $entRecord->patient->age ?? 'N/A' }}</div>
                </div>
                <div class="col">
                    <div class="label">{{ __('Gender') }}</div>
                    <div class="value">{{ $entRecord->patient->gender ?? 'N/A' }}</div>
                </div>
                <div class="col">
                    <div class="label">{{ __('Doctor') }}</div>
                    <div class="value">{{ $entRecord->doctor->full_name }}</div>
                </div>
            </div>
        </div>
        
        <!-- Chief Complaint Section -->
        @if($entRecord->chief_complaint)
        <div class="section">
            <div class="record-title">{{ __('Chief Complaint') }}</div>
            <div class="value">{{ $entRecord->chief_complaint }}</div>
        </div>
        @endif

        <!-- Examination Findings Section -->
        <div class="section">
            <div class="record-title">{{ __('Examination Findings') }}</div>
            @if($entRecord->ear_examination)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Ear Examination') }}</div>
                <div class="value">{{ $entRecord->ear_examination }}</div>
            </div>
            @endif
            @if($entRecord->nose_examination)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Nose Examination') }}</div>
                <div class="value">{{ $entRecord->nose_examination }}</div>
            </div>
            @endif
            @if($entRecord->throat_examination)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Throat Examination') }}</div>
                <div class="value">{{ $entRecord->throat_examination }}</div>
            </div>
            @endif
            @if($entRecord->neck_examination)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Neck Examination') }}</div>
                <div class="value">{{ $entRecord->neck_examination }}</div>
            </div>
            @endif
            @if($entRecord->cranial_nerves)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Cranial Nerves') }}</div>
                <div class="value">{{ $entRecord->cranial_nerves }}</div>
            </div>
            @endif
        </div>
        
        <!-- Diagnosis Section -->
        <div class="section">
            <div class="record-title">{{ __('Diagnosis & Treatment') }}</div>
            @if($entRecord->diagnosis)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Diagnosis') }}</div>
                <div class="value">{{ $entRecord->diagnosis }}</div>
            </div>
            @endif
            @if($entRecord->icd10_code)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('ICD-10 Code') }}</div>
                <div class="value">{{ $entRecord->icd10_code }}</div>
            </div>
            @endif
            @if($entRecord->treatment_plan)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Treatment Plan') }}</div>
                <div class="value">{{ $entRecord->treatment_plan }}</div>
            </div>
            @endif
            @if($entRecord->medications)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Medications') }}</div>
                <div class="value">{{ $entRecord->medications }}</div>
            </div>
            @endif
        </div>

        <!-- Follow-up Information -->
        @if($entRecord->followup_date || $entRecord->notes)
        <div class="section">
            <div class="record-title">{{ __('Additional Information') }}</div>
            @if($entRecord->followup_date)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Follow-up Date') }}</div>
                <div class="value">{{ $entRecord->followup_date->format('Y-m-d') }}</div>
            </div>
            @endif
            @if($entRecord->notes)
            <div style="margin-bottom: 6px;">
                <div class="label">{{ __('Notes') }}</div>
                <div class="value">{{ $entRecord->notes }}</div>
            </div>
            @endif
        </div>
        @endif
        
        <!-- Footer -->
        <div class="footer">
            <p>{{ config('app.name', 'ConCure') }} - {{ __('Medical Record') }}</p>
            <p>{{ __('This is an official medical record. Please keep this document safe.') }}</p>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
