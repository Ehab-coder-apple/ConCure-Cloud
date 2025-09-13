<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Request #{{ $labRequest->request_number }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
            .container { max-width: none; }
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 15px;
            background: #fff;
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #2c5aa0; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px;">
            🖨️ Print Lab Request
        </button>
    </div>

    <!-- Professional Header -->
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
                        <img src="{{ $clinicLogo }}"
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
    @if($isMultiPage)
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
                                    <img src="{{ $clinicLogo }}"
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
                        <h3 style="color: #2c5aa0; margin: 0 0 8px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 3px;">Tests Required - Page {{ $pageIndex + 1 }} of {{ $totalPages }}</h3>
                    </div>
                </div>
            @endif

            <div style="margin-bottom: 15px; border: 2px solid #dee2e6; border-radius: 6px; padding: 15px; background: #f8f9fa;">
                @if($pageIndex == 0)
                    <h3 style="color: #2c5aa0; margin: 0 0 12px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 5px;">
                        Tests Required
                        @if($totalPages > 1)
                            <span style="float: right; font-size: 12px; color: #6c757d; font-weight: normal;">Page 1 of {{ $totalPages }}</span>
                        @endif
                    </h3>
                @endif

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
            </div>
        @endforeach
    @else
        <div style="margin-bottom: 15px; border: 2px solid #dee2e6; border-radius: 6px; padding: 15px; background: #f8f9fa;">
            <h3 style="color: #2c5aa0; margin: 0 0 12px 0; font-size: 16px; font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #2c5aa0; padding-bottom: 5px;">Tests Required</h3>
            @if($labRequest->tests->count() > 0)
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
    @endif


    <!-- Signature Section -->
    <div style="margin-top: 30px; border-top: 3px solid #2c5aa0; padding-top: 20px;">
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

