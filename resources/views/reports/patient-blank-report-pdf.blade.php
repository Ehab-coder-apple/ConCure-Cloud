<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blank Report - {{ $patient->full_name }}</title>
    <style>
        @page {
            margin: 12mm;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .header-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: left;
        }

        .header-logo img {
            max-height: 60px;
            max-width: 75px;
            display: block;
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding-left: 10px;
        }

        .header h1 {
            color: #007bff;
            margin: 0 0 5px 0;
            font-size: 18px;
        }

        .clinic-info {
            text-align: center;
            margin-bottom: 3px;
            color: #666;
            font-size: 9px;
        }

        .info-section {
            margin-bottom: 12px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 30%;
            padding: 5px 8px;
            font-weight: bold;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            padding: 5px 8px;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }

        .section-title {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 8px;
            margin-top: 12px;
        }

        .notes-area {
            border: 1px solid #dee2e6;
            min-height: 150px;
            padding: 10px;
            background-color: #fafafa;
            margin-bottom: 12px;
            font-size: 9px;
            line-height: 1.6;
        }

        .notes-lines {
            line-height: 1.8;
        }

        .notes-line {
            border-bottom: 1px solid #ccc;
            height: 18px;
            margin-bottom: 3px;
        }

        .footer {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 2px solid #dee2e6;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 15px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 5px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 35px;
            padding-top: 5px;
            text-align: center;
            font-size: 9px;
        }

        .date-box {
            margin-top: 12px;
            padding: 8px;
            border: 1px solid #dee2e6;
            font-size: 9px;
        }

        img {
            max-height: 45px !important;
            display: block;
            margin: 0 auto 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    @php
        $logoBase64 = null;

        // Get logo from settings table (not clinic->logo column)
        $logoPath = DB::table('settings')
            ->where('clinic_id', $clinic->id)
            ->where('key', 'clinic_logo')
            ->value('value');

        if ($logoPath) {
            // Try multiple possible paths
            $possiblePaths = [
                storage_path('app/public/' . $logoPath),
                public_path('storage/' . $logoPath),
                storage_path('app/public/' . ltrim(str_replace(['storage/', 'public/'], '', $logoPath), '/')),
            ];

            foreach ($possiblePaths as $fullPath) {
                if (file_exists($fullPath)) {
                    try {
                        $imageData = file_get_contents($fullPath);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $fullPath);
                        finfo_close($finfo);
                        $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        break; // Found the logo, stop searching
                    } catch (\Exception $e) {
                        // Continue to next path
                    }
                }
            }
        }
    @endphp

    <div class="header">
        <!-- Logo on the left -->
        @if($logoBase64)
        <div class="header-logo">
            <img src="{{ $logoBase64 }}" alt="Clinic Logo">
        </div>
        @endif

        <!-- Clinic info in the center -->
        <div class="header-content">
            <h1 style="font-size: 20px; margin: 5px 0;">{{ $clinic->name ?? 'Medical Report' }}</h1>
            @if($clinic->address || $clinic->phone || $clinic->email)
            <div class="clinic-info" style="font-size: 10px;">
                @if($clinic->address){{ $clinic->address }}@endif
                @if($clinic->phone) | Tel: {{ $clinic->phone }}@endif
                @if($clinic->email) | Email: {{ $clinic->email }}@endif
            </div>
            @endif
            <div class="clinic-info" style="font-size: 10px;">
                <strong>Date:</strong> {{ isset($generated_date) ? $generated_date->format('F d, Y') : now()->format('F d, Y') }}
            </div>
        </div>
    </div>

    @if(isset($report_title))
    <!-- Report Title -->
    <div style="text-align: center; margin-bottom: 20px; padding: 10px; background-color: #e3f2fd; border-left: 4px solid #2196F3;">
        <h2 style="margin: 0; color: #1976D2; font-size: 18px;">{{ $report_title }}</h2>
    </div>
    @endif

    <!-- Patient Information -->
    <div class="section-title">Patient Information</div>
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Patient ID:</div>
                <div class="info-value">{{ $patient->patient_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value">{{ $patient->full_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Birth:</div>
                <div class="info-value">
                    @if($patient->date_of_birth)
                        {{ $patient->date_of_birth->format('F d, Y') }}
                        ({{ $patient->date_of_birth->age }} years old)
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Gender:</div>
                <div class="info-value">{{ ucfirst($patient->gender ?? 'N/A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $patient->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value">{{ $patient->address ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Doctor Information -->
    <div class="section-title">Doctor Information</div>
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Doctor Name:</div>
                <div class="info-value">{{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Specialization:</div>
                <div class="info-value">{{ $doctor->specialization ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">License Number:</div>
                <div class="info-value">{{ $doctor->license_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="section-title">Notes / Special Information</div>
    <div class="notes-area">
        @if(isset($notes) && !empty($notes))
            <div style="white-space: pre-wrap; line-height: 1.6; color: #333; font-size: 9px;">{{ $notes }}</div>
        @else
            <div class="notes-lines">
                @for($i = 0; $i < 8; $i++)
                    <div class="notes-line"></div>
                @endfor
            </div>
        @endif
    </div>

    <!-- Footer with Signatures -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Doctor's Signature</strong>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Date</strong>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
            @if(isset($notes) && !empty($notes))
                <p>This medical report was generated and saved to patient records.</p>
            @else
                <p>This is a blank medical report form. Please fill in the required information.</p>
            @endif
            <p>Generated on {{ isset($generated_date) ? $generated_date->format('F d, Y \a\t g:i A') : now()->format('F d, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>

