<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        /* Screen styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .print-actions {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #0d6efd;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0b5ed7;
        }
        
        .btn-success {
            background: #198754;
            color: white;
        }
        
        .btn-success:hover {
            background: #157347;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5c636a;
        }
        
        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
                font-size: 12px;
            }
            
            .print-container {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }
            
            .print-actions {
                display: none !important;
            }
            
            .print-header h1 {
                font-size: 18px;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        /* Prescription styles (same as PDF) */
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        
        .clinic-info {
            font-size: 14px;
            color: #666;
        }
        
        .prescription-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #0d6efd;
        }
        
        .prescription-number {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 25px;
        }
        
        .info-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-value {
            color: #333;
            line-height: 1.5;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .medicines-list {
            margin-bottom: 20px;
        }

        .medicine-item {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
            background: #fafafa;
        }

        .medicine-name {
            font-weight: bold;
            color: #0d6efd;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .medicine-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 10px;
        }

        .medicine-detail-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            border-left: 3px solid #0d6efd;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .detail-value {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }

        .medicine-instructions {
            margin-top: 12px;
            padding: 12px;
            background: white;
            border-radius: 6px;
            border-left: 3px solid #28a745;
            font-size: 13px;
            color: #555;
        }

        @media print {
            .medicine-details {
                display: table;
                width: 100%;
            }

            .medicine-detail-item {
                display: table-cell;
                width: 33.33%;
                padding: 8px;
                margin: 0;
            }
        }
        
        .diagnosis-box, .notes-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #666;
        }
        
        .doctor-signature {
            margin-top: 40px;
            text-align: right;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 30px 0 10px auto;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 25px;
        }
        
        .warning-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Print Actions (Hidden when printing) -->
        <div class="print-actions no-print">
            <h1 class="print-header">Prescription Ready to Print</h1>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Prescription
            </button>
            <a href="{{ route('simple-prescriptions.pdf', $prescription->id) }}" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('simple-prescriptions.show', $prescription->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Prescription
            </a>
        </div>

        <!-- Prescription Content (Same as PDF template) -->
        <div class="header">
            @php
                $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($prescription->clinic_id);
            @endphp

            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 15px;">
                @if($clinicLogo)
                    <img src="{{ $clinicLogo }}" alt="Clinic Logo" style="max-height: 80px; max-width: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #e9ecef; padding: 2px;">
                @endif
                <div style="text-align: {{ $clinicLogo ? 'left' : 'center' }};">
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
                </div>
            </div>
        </div>

        <div class="prescription-header">
            <div class="prescription-number">Prescription #{{ $prescription->prescription_number }}</div>
            <div>Date: {{ $prescription->prescribed_date->format('F d, Y') }}</div>
        </div>

        <div class="info-grid">
            <div class="info-section">
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
            <div class="info-section">
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

        @if($prescription->diagnosis)
            <div class="section">
                <div class="section-title">Diagnosis</div>
                <div class="diagnosis-box">
                    {{ $prescription->diagnosis }}
                </div>
            </div>
        @endif

        @if($prescription->medicines->count() > 0)
            <div class="section">
                <div class="section-title">Prescribed Medicines</div>
                <div class="medicines-list">
                    @foreach($prescription->medicines as $index => $medicine)
                        <div class="medicine-item">
                            <div class="medicine-name">{{ $index + 1 }}. {{ $medicine->medicine_name }}</div>
                            <div class="medicine-details">
                                <div class="medicine-detail-item">
                                    <div class="detail-label">Dosage</div>
                                    <div class="detail-value">{{ $medicine->dosage ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-item">
                                    <div class="detail-label">Frequency</div>
                                    <div class="detail-value">{{ $medicine->frequency ?? 'Not specified' }}</div>
                                </div>
                                <div class="medicine-detail-item">
                                    <div class="detail-label">Duration</div>
                                    <div class="detail-value">{{ $medicine->duration ?? 'Not specified' }}</div>
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

        @if($prescription->notes)
            <div class="section">
                <div class="section-title">Additional Notes</div>
                <div class="notes-box">
                    {{ $prescription->notes }}
                </div>
            </div>
        @endif

        <div class="warning-box">
            <div class="warning-title">Important Instructions:</div>
            • Please follow the prescribed dosage and timing strictly<br>
            • Do not stop medication without consulting your doctor<br>
            • Contact your doctor immediately if you experience any adverse reactions<br>
            • Keep medicines out of reach of children
        </div>

        <div class="doctor-signature">
            <div class="signature-line"></div>
            <div><strong>Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}</strong></div>
            <div style="font-size: 12px; color: #666;">Digital Signature</div>
        </div>

        <div class="footer">
            Generated by ConCure Clinic Management System on {{ now()->format('F d, Y \a\t g:i A') }}<br>
            This is a computer-generated prescription and is valid without physical signature.
        </div>
    </div>

    <script>
        // Auto-focus print dialog when page loads
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                if (window.location.search.includes('auto-print=1')) {
                    window.print();
                }
            }, 500);
        });
    </script>
</body>
</html>
