<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Report - {{ $patient->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .patient-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .patient-info-row {
            display: table-row;
        }
        
        .patient-info-cell {
            display: table-cell;
            padding: 8px;
            border-bottom: 1px solid #eee;
            width: 25%;
        }
        
        .patient-info-cell strong {
            color: #007bff;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }
        
        .summary-stat h3 {
            margin: 0;
            color: #007bff;
            font-size: 18px;
        }
        
        .summary-stat p {
            margin: 5px 0 0 0;
            color: #666;
        }
        
        .vital-signs {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .vital-sign {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            width: 16.66%;
        }
        
        .vital-sign h6 {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }
        
        .vital-sign small {
            color: #666;
            font-size: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #007bff;
        }
        
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }
        
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Patient Medical Report</h1>
        <p><strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})</p>
        <p>Report Period: {{ Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</p>
        <p>Generated on: {{ now()->format('M d, Y g:i A') }}</p>
    </div>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="patient-info">
            <div class="patient-info-row">
                <div class="patient-info-cell">
                    <strong>Patient ID:</strong><br>
                    {{ $patient->patient_id }}
                </div>
                <div class="patient-info-cell">
                    <strong>Full Name:</strong><br>
                    {{ $patient->full_name }}
                </div>
                <div class="patient-info-cell">
                    <strong>Date of Birth:</strong><br>
                    {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}
                    @if($patient->date_of_birth)
                        ({{ $patient->age }} years old)
                    @endif
                </div>
                <div class="patient-info-cell">
                    <strong>Gender:</strong><br>
                    {{ ucfirst($patient->gender ?? 'N/A') }}
                </div>
            </div>
            <div class="patient-info-row">
                <div class="patient-info-cell">
                    <strong>Phone:</strong><br>
                    {{ $patient->phone ?? 'N/A' }}
                </div>
                <div class="patient-info-cell">
                    <strong>Email:</strong><br>
                    {{ $patient->email ?? 'N/A' }}
                </div>
                <div class="patient-info-cell" colspan="2">
                    <strong>Address:</strong><br>
                    {{ $patient->address ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="section">
        <div class="section-title">Report Summary</div>
        <div class="summary-stats">
            <div class="summary-stat">
                <h3>{{ $reportData['summary']['total_checkups'] }}</h3>
                <p>Checkups</p>
            </div>
            <div class="summary-stat">
                <h3>{{ $reportData['summary']['total_prescriptions'] }}</h3>
                <p>Prescriptions</p>
            </div>
            <div class="summary-stat">
                <h3>{{ $reportData['summary']['total_appointments'] }}</h3>
                <p>Appointments</p>
            </div>
        </div>
    </div>

    <!-- Latest Vital Signs -->
    @if($reportData['latest_checkup'])
    <div class="section">
        <div class="section-title">Latest Vital Signs ({{ $reportData['latest_checkup']->checkup_date->format('M d, Y') }})</div>
        <div class="vital-signs">
            @if($reportData['latest_checkup']->weight)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->weight }} kg</h6>
                <small>Weight</small>
            </div>
            @endif
            
            @if($reportData['latest_checkup']->height)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->height }} cm</h6>
                <small>Height</small>
            </div>
            @endif
            
            @if($reportData['latest_checkup']->blood_pressure)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->blood_pressure }}</h6>
                <small>Blood Pressure</small>
            </div>
            @endif
            
            @if($reportData['latest_checkup']->heart_rate)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->heart_rate }} bpm</h6>
                <small>Heart Rate</small>
            </div>
            @endif
            
            @if($reportData['latest_checkup']->temperature)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->temperature }}°C</h6>
                <small>Temperature</small>
            </div>
            @endif
            
            @if($reportData['latest_checkup']->blood_sugar)
            <div class="vital-sign">
                <h6>{{ $reportData['latest_checkup']->blood_sugar }} mg/dL</h6>
                <small>Blood Sugar</small>
            </div>
            @endif
        </div>

        <!-- Custom Vital Signs -->
        @if($reportData['latest_checkup']->hasCustomVitalSigns())
        <div style="margin-top: 15px;">
            <strong>Additional Vital Signs:</strong>
            <div class="vital-signs" style="margin-top: 10px;">
                @foreach($reportData['latest_checkup']->custom_vital_signs_with_config as $customSign)
                <div class="vital-sign">
                    <h6>{{ $customSign['formatted_value'] }}</h6>
                    <small>{{ $customSign['config']->name }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- BMI History -->
    @if(count($reportData['bmi_history']) > 0)
    <div class="section">
        <div class="section-title">BMI History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Height (cm)</th>
                    <th>BMI</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['bmi_history'] as $bmi)
                <tr>
                    <td>{{ Carbon\Carbon::parse($bmi['date'])->format('M d, Y') }}</td>
                    <td>{{ $bmi['weight'] }}</td>
                    <td>{{ $bmi['height'] }}</td>
                    <td>{{ $bmi['bmi'] }}</td>
                    <td>
                        @if($bmi['bmi'] < 18.5)
                            <span class="badge badge-info">Underweight</span>
                        @elseif($bmi['bmi'] < 25)
                            <span class="badge badge-success">Normal</span>
                        @elseif($bmi['bmi'] < 30)
                            <span class="badge badge-warning">Overweight</span>
                        @else
                            <span class="badge badge-danger">Obese</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Checkups -->
    @if($reportData['checkups']->count() > 0)
    <div class="section page-break">
        <div class="section-title">Recent Checkups</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Vital Signs</th>
                    <th>Custom Signs</th>
                    <th>Symptoms</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['checkups']->take(15) as $checkup)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($checkup->checkup_date)->format('M d, Y') }}</td>
                    <td>
                        @if($checkup->blood_pressure)
                            <div><strong>BP:</strong> {{ $checkup->blood_pressure }}</div>
                        @endif
                        @if($checkup->heart_rate)
                            <div><strong>HR:</strong> {{ $checkup->heart_rate }} bpm</div>
                        @endif
                        @if($checkup->weight)
                            <div><strong>Weight:</strong> {{ $checkup->weight }} kg</div>
                        @endif
                    </td>
                    <td>
                        @if($checkup->hasCustomVitalSigns())
                            @foreach($checkup->custom_vital_signs_with_config as $customSign)
                                <div><strong>{{ $customSign['config']->name }}:</strong> {{ $customSign['formatted_value'] }}</div>
                            @endforeach
                        @else
                            None
                        @endif
                    </td>
                    <td>{{ Str::limit($checkup->symptoms ?? 'None', 40) }}</td>
                    <td>{{ Str::limit($checkup->notes ?? 'None', 40) }}</td>
                    <td>{{ $checkup->recorder->first_name ?? 'Unknown' }} {{ $checkup->recorder->last_name ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Prescriptions -->
    @if($reportData['prescriptions']->count() > 0)
    <div class="section">
        <div class="section-title">Recent Prescriptions</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Medicines</th>
                    <th>Instructions</th>
                    <th>Prescribed By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData['prescriptions']->take(10) as $prescription)
                <tr>
                    <td>{{ $prescription->created_at->format('M d, Y') }}</td>
                    <td>
                        @foreach($prescription->medicines as $medicine)
                            <div>{{ $medicine->name }}</div>
                        @endforeach
                    </td>
                    <td>{{ Str::limit($prescription->instructions ?? 'No instructions', 40) }}</td>
                    <td>{{ $prescription->prescriber->first_name ?? 'Unknown' }} {{ $prescription->prescriber->last_name ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated by ConCure Clinic Management System on {{ now()->format('M d, Y g:i A') }}</p>
        <p>Confidential Medical Information - For authorized personnel only</p>
    </div>
</body>
</html>
