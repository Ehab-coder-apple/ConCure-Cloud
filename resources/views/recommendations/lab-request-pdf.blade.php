<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Request #{{ $labRequest->request_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 15px;
            background: #fff;
        }

        .header {
            text-align: center;
            border: 3px solid #2c5aa0;
            padding: 15px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
        }

        .header h1 {
            color: #2c5aa0;
            margin: 0 0 8px 0;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header h2 {
            color: #495057;
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .request-number {
            background: #2c5aa0;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin-bottom: 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            background: #f8f9fa;
        }

        .info-section h3 {
            color: #2c5aa0;
            margin: 0 0 12px 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 5px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 6px 15px 6px 0;
            vertical-align: top;
            width: 50%;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .info-value {
            color: #000;
            font-size: 12px;
            font-weight: 500;
            border-bottom: 1px dotted #adb5bd;
            padding-bottom: 2px;
            min-height: 18px;
        }
        
        /* Dynamic tests section sizing */
        .tests-section[data-count="1"],
        .tests-section[data-count="2"],
        .tests-section[data-count="3"] {
            padding: 15px;
            margin-bottom: 20px;
        }

        .tests-section[data-count="4"],
        .tests-section[data-count="5"] {
            padding: 12px;
            margin-bottom: 15px;
        }

        .tests-section[data-count="6"],
        .tests-section[data-count="7"] {
            padding: 10px;
            margin-bottom: 12px;
        }

        .tests-section[data-count="8"],
        .tests-section[data-count="9"],
        .tests-section[data-count="10"] {
            padding: 8px;
            margin-bottom: 10px;
        }

        .tests-section[data-count="11"],
        .tests-section[data-count="12"],
        .tests-section[data-count="13"],
        .tests-section[data-count="14"],
        .tests-section[data-count="15"],
        .tests-section[data-count="16"],
        .tests-section[data-count="17"],
        .tests-section[data-count="18"],
        .tests-section[data-count="19"],
        .tests-section[data-count="20"] {
            padding: 6px;
            margin-bottom: 8px;
        }

        .tests-section {
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        
        /* Dynamic sizing based on number of tests */
        .tests-table[data-count="1"] th,
        .tests-table[data-count="2"] th,
        .tests-table[data-count="3"] th,
        .tests-table[data-count="1"] td,
        .tests-table[data-count="2"] td,
        .tests-table[data-count="3"] td {
            padding: 12px;
            font-size: 12px;
        }

        .tests-table[data-count="4"] th,
        .tests-table[data-count="5"] th,
        .tests-table[data-count="4"] td,
        .tests-table[data-count="5"] td {
            padding: 8px;
            font-size: 11px;
        }

        .tests-table[data-count="6"] th,
        .tests-table[data-count="7"] th,
        .tests-table[data-count="6"] td,
        .tests-table[data-count="7"] td {
            padding: 6px;
            font-size: 10px;
        }

        .tests-table[data-count="8"] th,
        .tests-table[data-count="9"] th,
        .tests-table[data-count="10"] th,
        .tests-table[data-count="8"] td,
        .tests-table[data-count="9"] td,
        .tests-table[data-count="10"] td {
            padding: 4px;
            font-size: 9px;
        }

        /* For 11+ tests - ultra compact */
        .tests-table[data-count="11"] th,
        .tests-table[data-count="12"] th,
        .tests-table[data-count="13"] th,
        .tests-table[data-count="14"] th,
        .tests-table[data-count="15"] th,
        .tests-table[data-count="16"] th,
        .tests-table[data-count="17"] th,
        .tests-table[data-count="18"] th,
        .tests-table[data-count="19"] th,
        .tests-table[data-count="20"] th,
        .tests-table[data-count="11"] td,
        .tests-table[data-count="12"] td,
        .tests-table[data-count="13"] td,
        .tests-table[data-count="14"] td,
        .tests-table[data-count="15"] td,
        .tests-table[data-count="16"] td,
        .tests-table[data-count="17"] td,
        .tests-table[data-count="18"] td,
        .tests-table[data-count="19"] td,
        .tests-table[data-count="20"] td {
            padding: 3px;
            font-size: 8px;
        }

        /* Dynamic result cell heights */
        .tests-table[data-count="1"] .result-cell,
        .tests-table[data-count="2"] .result-cell,
        .tests-table[data-count="3"] .result-cell {
            height: 40px;
        }

        .tests-table[data-count="4"] .result-cell,
        .tests-table[data-count="5"] .result-cell {
            height: 30px;
        }

        .tests-table[data-count="6"] .result-cell,
        .tests-table[data-count="7"] .result-cell {
            height: 25px;
        }

        .tests-table[data-count="8"] .result-cell,
        .tests-table[data-count="9"] .result-cell,
        .tests-table[data-count="10"] .result-cell {
            height: 20px;
        }

        .tests-table[data-count="11"] .result-cell,
        .tests-table[data-count="12"] .result-cell,
        .tests-table[data-count="13"] .result-cell,
        .tests-table[data-count="14"] .result-cell,
        .tests-table[data-count="15"] .result-cell,
        .tests-table[data-count="16"] .result-cell,
        .tests-table[data-count="17"] .result-cell,
        .tests-table[data-count="18"] .result-cell,
        .tests-table[data-count="19"] .result-cell,
        .tests-table[data-count="20"] .result-cell {
            height: 15px;
        }

        .tests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border: 2px solid #2c5aa0;
            border-radius: 6px;
            overflow: hidden;
        }

        .tests-table th,
        .tests-table td {
            border: 1px solid #2c5aa0;
            text-align: left;
            vertical-align: top;
        }

        .tests-table th {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 11px;
        }

        .tests-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .tests-table tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .tests-table td {
            position: relative;
        }

        .tests-table .test-number {
            background: #e9ecef;
            font-weight: bold;
            text-align: center;
            color: #495057;
        }

        .tests-table .test-name {
            font-weight: bold;
            color: #2c5aa0;
        }

        .tests-table .result-cell {
            background: #fff;
            border-left: 3px solid #28a745 !important;
            position: relative;
        }

        .tests-table .result-cell::before {
            content: "Result:";
            position: absolute;
            top: 2px;
            left: 5px;
            font-size: 8px;
            color: #6c757d;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .notes-section {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #f39c12;
            border-left: 6px solid #e67e22;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .notes-section h4 {
            color: #d35400;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-normal {
            background: #28a745;
            color: white;
        }

        .priority-urgent {
            background: #ffc107;
            color: #000;
        }

        .priority-stat {
            background: #dc3545;
            color: white;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #6c757d;
            color: white;
        }

        .status-in-progress {
            background: #17a2b8;
            color: white;
        }

        .status-completed {
            background: #28a745;
            color: white;
        }
        
        .signature-section {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .signature-box {
            display: inline-block;
            width: 45%;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
            text-align: center;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 10px;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .priority-normal { background-color: #6c757d; color: white; }
        .priority-urgent { background-color: #ffc107; color: black; }
        .priority-stat { background-color: #dc3545; color: white; }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #ffc107; color: black; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    @php
        $clinicId = auth()->user()->clinic_id ?? $labRequest->doctor->clinic_id ?? 2;
        $clinicInfo = \App\Helpers\ClinicHelper::getClinicInfo($clinicId);
        $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($clinicId);
    @endphp

    <div style="border: 3px solid #2c5aa0; padding: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <!-- Left Section: Logo -->
                @if($clinicLogo)
                    <td style="width: 25%; vertical-align: middle; text-align: left; padding-right: 15px;">
                        <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                             alt=""
                             style="max-height: 110px; max-width: 115px; object-fit: contain; border-radius: 6px; border: 1px solid #dee2e6; padding: 2px; background: white;">
                    </td>
                @else
                    <td style="width: 25%;"></td>
                @endif

                <!-- Middle Section: Clinic Name -->
                <td style="width: 50%; vertical-align: middle; text-align: center;">
                    <h1 style="color: #2c5aa0; margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-family: 'Times New Roman', serif;">{{ $clinicInfo['name'] }}</h1>
                </td>

                <!-- Right Section: Request Number -->
                <td style="width: 25%; vertical-align: middle; text-align: right; padding-left: 15px;">
                    <div style="background: #2c5aa0; color: white; padding: 8px 16px; border-radius: 20px; font-size: 16px; font-weight: bold; display: inline-block; letter-spacing: 1px;">{{ $labRequest->request_number }}</div>
                </td>
            </tr>
        </table>
    </div>



    <!-- Patient Information & Requesting Physician Side by Side -->
    <div style="margin-bottom: 15px; display: table; width: 100%; border-collapse: separate; border-spacing: 10px 0;">
        <!-- Patient Information -->
        <div style="display: table-cell; width: 50%; border: 2px solid #dee2e6; border-radius: 6px; padding: 12px; background: #f8f9fa; vertical-align: top;">
            <h3 style="color: #2c5aa0; margin: 0 0 8px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 3px;">Patient Information</h3>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Patient Name:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->patient->full_name }}</span>
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Patient ID:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->patient->patient_id }}</span>
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Date of Birth:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->patient->date_of_birth->format('M d, Y') }}</span>
            </div>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Gender:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ ucfirst($labRequest->patient->gender) }}</span>
            </div>
        </div>

        <!-- Requesting Physician -->
        <div style="display: table-cell; width: 50%; border: 2px solid #dee2e6; border-radius: 6px; padding: 12px; background: #f8f9fa; vertical-align: top;">
            <h3 style="color: #2c5aa0; margin: 0 0 8px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 3px;">Requesting Physician</h3>
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Doctor:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">Dr. {{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}</span>
            </div>
            @if($labRequest->due_date)
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Due Date:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->due_date->format('M d, Y') }}</span>
            </div>
            @endif
            @if($labRequest->lab_name)
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Laboratory:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->lab_name }}</span>
            </div>
            @endif
            <div style="margin-bottom: 4px;">
                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Clinic:</span>
                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ auth()->user()->clinic->name ?? 'ConCure Clinic' }}</span>
            </div>
        </div>
    </div>

    @if($labRequest->clinical_notes)
    <div style="margin-bottom: 15px; border: 2px solid #f39c12; border-left: 6px solid #e67e22; padding: 15px; border-radius: 6px; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
        <h4 style="color: #d35400; margin: 0 0 10px 0; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Clinical Notes</h4>
        <p style="margin-bottom: 0; font-style: italic; line-height: 1.4;">{{ $labRequest->clinical_notes }}</p>
    </div>
    @endif

    <!-- Tests Required -->
    <div style="margin-bottom: 15px; border: 2px solid #dee2e6; border-radius: 6px; padding: 15px; background: #f8f9fa;">
        <h3 style="color: #2c5aa0; margin: 0 0 12px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 5px;">
            Tests Required
            @if(isset($isMultiPage) && $isMultiPage && isset($pageNumber) && isset($totalPages))
                <span style="float: right; font-size: 12px; color: #6c757d; font-weight: normal;">Page {{ $pageNumber }} of {{ $totalPages }}</span>
            @endif
        </h3>

        @if(isset($testChunks) && $testChunks->count() > 0)
            @foreach($testChunks as $pageIndex => $testsChunk)
                @if($pageIndex > 0)
                    <div style="page-break-before: always;"></div>

                    <!-- Repeat header for new page -->
                    <div style="border: 3px solid #2c5aa0; padding: 12px; margin-bottom: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 8px;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <!-- Left Section: Logo -->
                                @if($clinicLogo)
                                    <td style="width: 25%; vertical-align: middle; text-align: left; padding-right: 15px;">
                                        <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                                             alt=""
                                             style="max-height: 110px; max-width: 115px; object-fit: contain; border-radius: 6px; border: 1px solid #dee2e6; padding: 2px; background: white;">
                                    </td>
                                @else
                                    <td style="width: 25%;"></td>
                                @endif

                                <!-- Middle Section: Clinic Name -->
                                <td style="width: 50%; vertical-align: middle; text-align: center;">
                                    <h1 style="color: #2c5aa0; margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-family: 'Times New Roman', serif;">{{ $clinicInfo['name'] }}</h1>
                                </td>

                                <!-- Right Section: Request Number -->
                                <td style="width: 25%; vertical-align: middle; text-align: right; padding-left: 15px;">
                                    <div style="background: #2c5aa0; color: white; padding: 8px 16px; border-radius: 20px; font-size: 16px; font-weight: bold; display: inline-block; letter-spacing: 1px;">{{ $labRequest->request_number }} - Page {{ $pageIndex + 1 }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Repeat patient info for new page -->
                    <div style="margin-bottom: 15px; display: table; width: 100%; border-collapse: separate; border-spacing: 10px 0;">
                        <div style="display: table-cell; width: 50%; border: 2px solid #dee2e6; border-radius: 6px; padding: 12px; background: #f8f9fa; vertical-align: top;">
                            <h3 style="color: #2c5aa0; margin: 0 0 8px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 3px;">Patient Information</h3>
                            <div style="margin-bottom: 4px;">
                                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Patient Name:</span>
                                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->patient->full_name }}</span>
                            </div>
                            <div style="margin-bottom: 4px;">
                                <span style="font-weight: bold; color: #495057; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Patient ID:</span>
                                <span style="color: #000; font-size: 12px; font-weight: 500; margin-left: 8px;">{{ $labRequest->patient->patient_id }}</span>
                            </div>
                        </div>
                        <div style="display: table-cell; width: 50%; border: 2px solid #dee2e6; border-radius: 6px; padding: 12px; background: #f8f9fa; vertical-align: top;">
                            <h3 style="color: #2c5aa0; margin: 0 0 8px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 3px;">Tests Required - Page {{ $pageIndex + 1 }}</h3>
                        </div>
                    </div>
                @endif

                <table style="width: 100%; border-collapse: collapse; border: 2px solid #2c5aa0; border-radius: 6px; overflow: hidden; margin-bottom: {{ $pageIndex < $testChunks->count() - 1 ? '30px' : '15px' }};">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);">
                            <th style="width: 8%; border: 1px solid #2c5aa0; text-align: center; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 5px;">#</th>
                            <th style="width: 42%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Test Name</th>
                            <th style="width: 30%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Instructions</th>
                            <th style="width: 20%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($testsChunk as $index => $test)
                        <tr style="background-color: {{ $loop->even ? '#f8f9fa' : '#ffffff' }};">
                            <td style="border: 1px solid #2c5aa0; text-align: center; vertical-align: top; background: #e9ecef; font-weight: bold; color: #495057; padding: 8px 5px;">{{ ($pageIndex * 6) + $index + 1 }}</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; font-weight: bold; color: #2c5aa0; padding: 8px;">{{ $test->test_name }}</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px;">{{ $test->instructions ?: 'Fasting required' }}</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; background: #fff; border-left: 3px solid #28a745; padding: 8px; position: relative;">
                                <!-- Space for lab results -->
                            </td>
                        </tr>
                        @endforeach

                        @if($testsChunk->count() < 6)
                            @for($i = $testsChunk->count(); $i < 6; $i++)
                            <tr style="background-color: {{ $i % 2 == 1 ? '#f8f9fa' : '#ffffff' }};">
                                <td style="border: 1px solid #2c5aa0; text-align: center; vertical-align: top; background: #e9ecef; font-weight: bold; color: #495057; padding: 8px 5px;">{{ ($pageIndex * 6) + $i + 1 }}</td>
                                <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px; color: #6c757d; font-style: italic;">-</td>
                                <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px; color: #6c757d; font-style: italic;">-</td>
                                <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; background: #fff; border-left: 3px solid #28a745; padding: 8px;">
                                    <!-- Space for lab results -->
                                </td>
                            </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            @endforeach
        @elseif($labRequest->tests->count() > 0)
            <table style="width: 100%; border-collapse: collapse; border: 2px solid #2c5aa0; border-radius: 6px; overflow: hidden;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3d72 100%);">
                        <th style="width: 8%; border: 1px solid #2c5aa0; text-align: center; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 5px;">#</th>
                        <th style="width: 42%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Test Name</th>
                        <th style="width: 30%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Instructions</th>
                        <th style="width: 20%; border: 1px solid #2c5aa0; text-align: left; vertical-align: top; color: white; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 11px; padding: 10px 8px;">Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labRequest->tests->take(6) as $index => $test)
                    <tr style="background-color: {{ $loop->even ? '#f8f9fa' : '#ffffff' }};">
                        <td style="border: 1px solid #2c5aa0; text-align: center; vertical-align: top; background: #e9ecef; font-weight: bold; color: #495057; padding: 8px 5px;">{{ $index + 1 }}</td>
                        <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; font-weight: bold; color: #2c5aa0; padding: 8px;">{{ $test->test_name }}</td>
                        <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px;">{{ $test->instructions ?: 'Fasting required' }}</td>
                        <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; background: #fff; border-left: 3px solid #28a745; padding: 8px; position: relative;">
                            <!-- Space for lab results -->
                        </td>
                    </tr>
                    @endforeach

                    @if($labRequest->tests->count() < 6)
                        @for($i = $labRequest->tests->count(); $i < 6; $i++)
                        <tr style="background-color: {{ $i % 2 == 1 ? '#f8f9fa' : '#ffffff' }};">
                            <td style="border: 1px solid #2c5aa0; text-align: center; vertical-align: top; background: #e9ecef; font-weight: bold; color: #495057; padding: 8px 5px;">{{ $i + 1 }}</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px; color: #6c757d; font-style: italic;">-</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; padding: 8px; color: #6c757d; font-style: italic;">-</td>
                            <td style="border: 1px solid #2c5aa0; text-align: left; vertical-align: top; background: #fff; border-left: 3px solid #28a745; padding: 8px;">
                                <!-- Space for lab results -->
                            </td>
                        </tr>
                        @endfor
                    @endif
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #6c757d;">
                <em>No tests specified for this request.</em>
            </div>
        @endif
    </div>



    <!-- Signature Section -->
    <div class="signature-section" style="margin-top: 30px; border-top: 3px solid #2c5aa0; padding-top: 20px;">
        <div style="display: table; width: 100%;">
            <div style="display: table-cell; width: 50%; padding-right: 20px;">
                <div style="border-bottom: 2px solid #000; height: 50px; margin-bottom: 10px;"></div>
                <div style="text-align: center;">
                    <strong style="color: #2c5aa0; text-transform: uppercase; font-size: 12px;">Physician Signature</strong><br>
                    <small style="color: #495057;">Dr. {{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}</small><br>
                    <small style="color: #6c757d;">{{ auth()->user()->clinic->name ?? 'ConCure Clinic' }}</small>
                </div>
            </div>
            <div style="display: table-cell; width: 50%; padding-left: 20px;">
                <div style="border-bottom: 2px solid #000; height: 50px; margin-bottom: 10px;"></div>
                <div style="text-align: center;">
                    <strong style="color: #2c5aa0; text-transform: uppercase; font-size: 12px;">Date & Time</strong><br>
                    <small style="color: #495057;">{{ $labRequest->created_at->format('M d, Y') }}</small><br>
                    <small style="color: #6c757d;">{{ $labRequest->created_at->format('H:i') }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-top: 30px; text-align: center; border-top: 1px solid #dee2e6; padding-top: 15px; color: #6c757d; font-size: 10px;">
        <p style="margin: 0 0 5px 0;"><strong>{{ auth()->user()->clinic->name ?? 'ConCure Clinic' }}</strong> - Laboratory Request Form</p>
        <p style="margin: 0;">Generated on {{ now()->format('M d, Y \a\t H:i') }} | Request #{{ $labRequest->request_number }}</p>
        <p style="margin: 5px 0 0 0; font-style: italic;">This is a computer-generated document. No signature is required for processing.</p>
    </div>
</body>
</html>
