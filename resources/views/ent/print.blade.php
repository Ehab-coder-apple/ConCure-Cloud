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
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            border-bottom: 3px solid #20b2aa;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #20b2aa;
        }
        
        .record-title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #20b2aa;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .col {
            flex: 1;
        }
        
        .label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .value {
            color: #333;
            padding: 5px 0;
        }
        
        .section {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            margin: 10px 0;
        }
        
        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
        }
        
        .print-date {
            text-align: right;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }
        
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .container {
                padding: 0;
                margin: 0;
            }
            .print-date {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ config('app.name', 'ConCure Clinic') }}</div>
            <div style="font-size: 12px; color: #666; margin-top: 5px;">{{ __('ENT Record Details') }}</div>
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
            </div>
            <div class="row">
                <div class="col">
                    <div class="label">{{ __('Doctor') }}</div>
                    <div class="value">{{ $entRecord->doctor->full_name }}</div>
                </div>
                <div class="col">
                    <div class="label">{{ __('Visit Date') }}</div>
                    <div class="value">{{ $entRecord->visit_date->format('Y-m-d') }}</div>
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
            <div>
                <div class="label">{{ __('Ear Examination') }}</div>
                <div class="value">{{ $entRecord->ear_examination }}</div>
            </div>
            @endif
            @if($entRecord->nose_examination)
            <div>
                <div class="label">{{ __('Nose Examination') }}</div>
                <div class="value">{{ $entRecord->nose_examination }}</div>
            </div>
            @endif
            @if($entRecord->throat_examination)
            <div>
                <div class="label">{{ __('Throat Examination') }}</div>
                <div class="value">{{ $entRecord->throat_examination }}</div>
            </div>
            @endif
            @if($entRecord->neck_examination)
            <div>
                <div class="label">{{ __('Neck Examination') }}</div>
                <div class="value">{{ $entRecord->neck_examination }}</div>
            </div>
            @endif
            @if($entRecord->cranial_nerves)
            <div>
                <div class="label">{{ __('Cranial Nerves') }}</div>
                <div class="value">{{ $entRecord->cranial_nerves }}</div>
            </div>
            @endif
        </div>
        
        <!-- Diagnosis Section -->
        <div class="section">
            <div class="record-title">{{ __('Diagnosis & Treatment') }}</div>
            @if($entRecord->diagnosis)
            <div>
                <div class="label">{{ __('Diagnosis') }}</div>
                <div class="value">{{ $entRecord->diagnosis }}</div>
            </div>
            @endif
            @if($entRecord->icd10_code)
            <div>
                <div class="label">{{ __('ICD-10 Code') }}</div>
                <div class="value"><code>{{ $entRecord->icd10_code }}</code></div>
            </div>
            @endif
            @if($entRecord->treatment_plan)
            <div>
                <div class="label">{{ __('Treatment Plan') }}</div>
                <div class="value">{{ $entRecord->treatment_plan }}</div>
            </div>
            @endif
            @if($entRecord->medications)
            <div>
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
            <div>
                <div class="label">{{ __('Follow-up Date') }}</div>
                <div class="value">{{ $entRecord->followup_date->format('Y-m-d') }}</div>
            </div>
            @endif
            @if($entRecord->notes)
            <div>
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
