<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription - {{ $prescription->prescription_number }}</title>
    <style>
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #212529;
            margin: 0;
            padding: 12px;
            background: #fff;
        }

        @page {
            margin: 10mm;
            size: A4;
        }

        /* Card-style section - mPDF compatible (no border-radius, no overflow) */
        .card {
            border: 1px solid #dee2e6;
            margin-bottom: 12px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: bold;
            color: #0d6efd;
        }

        .card-body {
            padding: 10px 12px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 7px;
            color: #6c757d;
        }
    </style>
</head>
<body>

    @php
        $clinicLogo = \App\Helpers\ClinicHelper::getClinicLogoPdfPath($prescription->clinic_id);
    @endphp

    <!-- ===== HEADER ===== -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; border-bottom: 2px solid #0d6efd; padding-bottom: 8px;">
        <tr>
            @if($clinicLogo && file_exists($clinicLogo))
                <td style="width: 75px; vertical-align: middle; text-align: center; padding-right: 10px;">
                    <img src="{{ $clinicLogo }}" alt="Logo" style="max-height: 65px; max-width: 65px;">
                </td>
            @endif
            <td style="vertical-align: middle; text-align: left;">
                <div style="font-size: 16px; font-weight: bold; color: #0d6efd; margin-bottom: 2px;">
                    {{ $prescription->clinic->name ?? 'ConCure Clinic' }}
                </div>
                <div style="font-size: 8px; color: #6c757d; line-height: 1.4;">
                    @if($prescription->clinic->address ?? false){{ $prescription->clinic->address }}<br>@endif
                    @if($prescription->clinic->phone ?? false)Phone: {{ $prescription->clinic->phone }}@endif
                    @if(($prescription->clinic->phone ?? false) && ($prescription->clinic->email ?? false)) &nbsp;|&nbsp; @endif
                    @if($prescription->clinic->email ?? false)Email: {{ $prescription->clinic->email }}@endif
                </div>
            </td>
            <td style="vertical-align: middle; text-align: right; width: 210px;">
                <div style="font-size: 12px; font-weight: bold; color: #212529; margin-bottom: 2px;">
                    Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}
                </div>
                @if($prescription->doctor->specialization)
                    <div style="font-size: {{ $prescription->doctor->specialization_font_size ?? 9 }}px; color: #495057; margin-bottom: 1px;">{{ $prescription->doctor->specialization }}</div>
                @endif
                @if($prescription->doctor->scientific_degree)
                    <div style="font-size: 8px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->scientific_degree }}</div>
                @endif
                @if($prescription->doctor->medical_degrees)
                    <div style="font-size: {{ $prescription->doctor->medical_degrees_font_size ?? 8 }}px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->medical_degrees }}</div>
                @endif
                @if($prescription->doctor->educational_institution)
                    <div style="font-size: 8px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->educational_institution }}</div>
                @endif
                @if($prescription->doctor->professional_credentials)
                    <div style="font-size: {{ $prescription->doctor->professional_credentials_font_size ?? 8 }}px; color: #6c757d; margin-bottom: 1px;">{{ $prescription->doctor->professional_credentials }}</div>
                @endif
                @if($prescription->doctor->phone)
                    <div style="font-size: 8px; color: #495057; margin-top: 3px;">Phone: {{ $prescription->doctor->phone }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Prescription Number & Status -->
    <table style="width: 100%; margin-bottom: 12px;">
        <tr>
            <td style="vertical-align: middle;">
                <span style="font-size: 13px; font-weight: bold; color: #0d6efd;">{{ $prescription->prescription_number }}</span><br>
                <span style="font-size: 8px; color: #6c757d;">{{ __('Created on') }} {{ $prescription->created_at->format('F d, Y') }}</span>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <span style="background-color: {{ $prescription->status === 'active' ? '#198754' : ($prescription->status === 'completed' ? '#0d6efd' : '#6c757d') }}; color: #fff; padding: 3px 10px; font-size: 9px; font-weight: bold;">
                    {{ ucfirst($prescription->status) }}
                </span>
            </td>
        </tr>
    </table>

    <!-- ===== PATIENT INFORMATION ===== -->
    <div class="card">
        <div class="card-header">Patient Information</div>
        <div class="card-body">
            <table style="width: 100%; font-size: 9px;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding-right: 10px;">
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
        <div class="card-header">Doctor Information</div>
        <div class="card-body">
            <table style="width: 100%; font-size: 9px;">
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
            <div class="card-header">Diagnosis</div>
            <div class="card-body">
                <div style="font-size: 9px; line-height: 1.6; direction: rtl; text-align: right;">
                    {{ $prescription->diagnosis }}
                </div>
            </div>
        </div>
    @endif

    <!-- ===== PRESCRIBED MEDICINES ===== -->
    @if($prescription->medicines->count() > 0)
        <div class="card">
            <div class="card-header">Prescribed Medicines</div>
            <div class="card-body">
                @foreach($prescription->medicines as $index => $medicine)
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: {{ $loop->last ? '0' : '10px' }}; background-color: #f8f9fa; border: 1px solid #e9ecef; page-break-inside: avoid;">
                        <tr>
                            <td style="padding: 8px 10px;">
                                <!-- Medicine Name -->
                                <div style="font-size: 11px; font-weight: bold; color: #0d6efd; margin-bottom: 6px;">
                                    {{ $index + 1 }}. {{ $medicine->medicine_name }}
                                </div>
                                <!-- Dosage / Frequency / Duration -->
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 33%; padding: 2px 0;">
                                            <span style="font-size: 8px; color: #6c757d;">Dosage</span><br>
                                            <strong style="font-size: 9px; color: #212529;">{{ $medicine->dosage ?? 'Not specified' }}</strong>
                                        </td>
                                        <td style="width: 33%; padding: 2px 0;">
                                            <span style="font-size: 8px; color: #6c757d;">Frequency</span><br>
                                            <strong style="font-size: 9px; color: #212529;">{{ $medicine->frequency ?? 'Not specified' }}</strong>
                                        </td>
                                        <td style="width: 34%; padding: 2px 0;">
                                            <span style="font-size: 8px; color: #6c757d;">Duration</span><br>
                                            <strong style="font-size: 9px; color: #212529;">{{ $medicine->duration ?? 'Not specified' }}</strong>
                                        </td>
                                    </tr>
                                </table>
                                <!-- Instructions -->
                                @if($medicine->instructions)
                                    <div style="margin-top: 6px; padding-top: 6px; border-top: 1px solid #dee2e6;">
                                        <span style="font-size: 8px; color: #6c757d;">Instructions</span><br>
                                        <span style="font-size: 9px; color: #212529; direction: rtl;">
                                            {{ $medicine->instructions }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ===== NOTES ===== -->
    @if($prescription->notes)
        <div class="card">
            <div class="card-header">Notes</div>
            <div class="card-body">
                <div style="font-size: 9px; line-height: 1.6; direction: rtl; text-align: right;">
                    {{ $prescription->notes }}
                </div>
            </div>
        </div>
    @endif

    <!-- ===== FOOTER ===== -->
    <div class="footer">
        Generated by ConCure Clinic Management System on {{ now()->format('d/m/Y \a\t g:i A') }}<br>
        This is a computer-generated prescription and is valid without physical signature.
    </div>

</body>
</html>
