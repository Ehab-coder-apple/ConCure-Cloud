<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #212529;
            margin: 0;
            padding: 20px;
            background: #fff;
        }

        .prescription-document {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: bold;
            color: #0d6efd;
        }

        .card-body {
            padding: 12px 14px;
        }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        @media print {
            body { margin: 0; padding: 10px; }
            .prescription-document { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="prescription-document">

    @php
        $clinicLogoPath = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
        $clinicLogoSrc = \App\Helpers\ClinicHelper::getClinicLogoPdfSrc($prescription->clinic_id);
    @endphp

    <!-- ===== HEADER ===== -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px;">
        <tr>
            @if($clinicLogoPath && file_exists($clinicLogoPath))
                <td style="width: 75px; vertical-align: middle; text-align: center; padding-right: 10px;">
                    <img src="{{ $clinicLogoSrc }}" alt="Logo" style="max-height: 65px; max-width: 65px;">
                </td>
            @endif
            <td style="vertical-align: middle; text-align: left;">
                <div style="font-size: 18px; font-weight: bold; color: #0d6efd; margin-bottom: 2px;">
                    {{ $prescription->clinic->name ?? 'ConCure Clinic' }}
                </div>
                <div style="font-size: 10px; color: #6c757d; line-height: 1.4;">
                    @if($prescription->clinic->address ?? false){{ $prescription->clinic->address }}<br>@endif
                    @if($prescription->clinic->phone ?? false)Phone: {{ $prescription->clinic->phone }}@endif
                    @if(($prescription->clinic->phone ?? false) && ($prescription->clinic->email ?? false)) &nbsp;|&nbsp; @endif
                    @if($prescription->clinic->email ?? false)Email: {{ $prescription->clinic->email }}@endif
                </div>
            </td>
            <td style="vertical-align: middle; text-align: right; width: 220px;">
                <div style="font-size: 13px; font-weight: bold; color: #212529; margin-bottom: 2px;">
                    Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}
                </div>
                @if($prescription->doctor->specialization)
                    <div style="font-size: {{ $prescription->doctor->specialization_font_size ?? 10 }}px; color: #495057; margin-bottom: 1px;">{{ $prescription->doctor->specialization }}</div>
                @endif
                @if($prescription->doctor->scientific_degree)
                    <div style="font-size: 9px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->scientific_degree }}</div>
                @endif
                @if($prescription->doctor->medical_degrees)
                    <div style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 9 }}px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->medical_degrees }}</div>
                @endif
                @if($prescription->doctor->educational_institution)
                    <div style="font-size: 9px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->educational_institution }}</div>
                @endif
                @if($prescription->doctor->professional_credentials)
                    <div style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 9 }}px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->professional_credentials }}</div>
                @endif
                @if($prescription->doctor->phone)
                    <div style="font-size: 9px; color: #495057; margin-top: 3px;">Phone: {{ $prescription->doctor->phone }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Prescription Number & Status -->
    <div style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <span style="font-size: 15px; font-weight: bold; color: #0d6efd;">{{ $prescription->prescription_number }}</span><br>
            <span style="font-size: 10px; color: #6c757d;">{{ __('Created on') }} {{ $prescription->created_at->format('F d, Y') }}</span>
        </div>
        <div>
            <span style="background-color: {{ $prescription->status === 'active' ? '#198754' : ($prescription->status === 'completed' ? '#0d6efd' : '#6c757d') }}; color: #fff; padding: 4px 12px; border-radius: 12px; font-size: 10px; font-weight: bold;">
                {{ ucfirst($prescription->status) }}
            </span>
        </div>
    </div>

    <!-- ===== PATIENT INFORMATION ===== -->
    <div class="card">
        <div class="card-header">&#x1f464; Patient Information</div>
        <div class="card-body">
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 12px;">
                        <strong>Name:</strong> {{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}<br>
                        <strong>Patient ID:</strong> {{ $prescription->patient->patient_id ?? 'N/A' }}<br>
                        <strong>Gender:</strong> {{ ucfirst($prescription->patient->gender ?? 'Not specified') }}
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Phone:</strong> {{ $prescription->patient->phone ?? 'Not provided' }}<br>
                        <strong>Date of Birth:</strong>
                        @if($prescription->patient->date_of_birth)
                            {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->format('M d, Y') }}
                        @else
                            Not provided
                        @endif
                        <br>
                        <strong>Age:</strong>
                        @if($prescription->patient->date_of_birth)
                            {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ===== DOCTOR INFORMATION ===== -->
    <div class="card">
        <div class="card-header">&#x1f9d1;&#x200d;&#x2695;&#xfe0f; Doctor Information</div>
        <div class="card-body">
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Doctor:</strong> Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}<br>
                        <strong>Phone:</strong> {{ $prescription->doctor->phone ?? 'Not provided' }}
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <strong>Email:</strong> {{ $prescription->doctor->email ?? 'Not provided' }}<br>
                        <strong>Prescribed Date:</strong> {{ $prescription->prescribed_date->format('F d, Y') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ===== DIAGNOSIS ===== -->
    @if($prescription->diagnosis)
        <div class="card">
            <div class="card-header">&#x1fa7a; Diagnosis</div>
            <div class="card-body">
                <p style="margin: 0; font-size: 11px; line-height: 1.6; direction: rtl; text-align: right;">
                    {{ $prescription->diagnosis }}
                </p>
            </div>
        </div>
    @endif

    <!-- ===== PRESCRIBED MEDICINES ===== -->
    @if($prescription->medicines->count() > 0)
        <div class="card">
            <div class="card-header">&#x1f48a; Prescribed Medicines</div>
            <div class="card-body">
                @foreach($prescription->medicines as $index => $medicine)
                    <div style="margin-bottom: {{ $loop->last ? '0' : '12px' }}; padding: 10px 12px; background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; page-break-inside: avoid;">
                        <div style="font-size: 13px; font-weight: bold; color: #0d6efd; margin-bottom: 8px;">
                            {{ $index + 1 }}. {{ $medicine->medicine_name }}
                        </div>
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 33%; padding: 2px 0;">
                                    <span style="font-size: 9px; color: #6c757d;">Dosage</span><br>
                                    <strong style="font-size: 11px; color: #212529;">{{ $medicine->dosage ?? 'Not specified' }}</strong>
                                </td>
                                <td style="width: 33%; padding: 2px 0;">
                                    <span style="font-size: 9px; color: #6c757d;">Frequency</span><br>
                                    <strong style="font-size: 11px; color: #212529;">{{ $medicine->frequency ?? 'Not specified' }}</strong>
                                </td>
                                <td style="width: 34%; padding: 2px 0;">
                                    <span style="font-size: 9px; color: #6c757d;">Duration</span><br>
                                    <strong style="font-size: 11px; color: #212529;">{{ $medicine->duration ?? 'Not specified' }}</strong>
                                </td>
                            </tr>
                        </table>
                        @if($medicine->instructions)
                            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #dee2e6;">
                                <span style="font-size: 9px; color: #6c757d;">Instructions</span><br>
                                <span style="font-size: 11px; color: #212529; direction: rtl;">
                                    &#x2139;&#xfe0f; {{ $medicine->instructions }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ===== NOTES ===== -->
    @if($prescription->notes)
        <div class="card">
            <div class="card-header">&#x1f4dd; Notes</div>
            <div class="card-body">
                <p style="margin: 0; font-size: 11px; line-height: 1.6; direction: rtl; text-align: right;">
                    {{ $prescription->notes }}
                </p>
            </div>
        </div>
    @endif

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y \a\t g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>

    </div> <!-- Close prescription-document -->

    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
