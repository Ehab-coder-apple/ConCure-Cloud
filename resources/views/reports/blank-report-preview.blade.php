@extends('layouts.app')

@section('content')
<div class="container-fluid no-print">
    <div class="row">
        <!-- Preview Panel -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center no-print">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        {{ __('Report Preview') }}
                    </h5>
                    <span class="badge bg-light text-dark">{{ __('Preview Mode') }}</span>
                </div>
                <div class="card-body p-4" style="background-color: #f5f5f5;">
                    <!-- PDF-like Preview - This is what gets printed -->
                    <div class="bg-white shadow-sm p-4 print-content" id="printableArea" style="max-width: 800px; margin: 0 auto; min-height: auto;">
                        <!-- Header -->
                        <div class="report-header" style="text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 8px; margin-bottom: 12px;">
                            @if($clinic->logo)
                                @php
                                    $logoPath = 'storage/' . $clinic->logo;
                                    $logoExists = file_exists(public_path($logoPath));
                                @endphp
                                @if($logoExists)
                                    <img src="{{ asset($logoPath) }}" alt="Clinic Logo" class="clinic-logo" style="max-height: 45px; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;">
                                @endif
                            @endif
                            <h1 class="clinic-name" style="color: #007bff; margin: 0 0 5px 0; font-size: 18px;">{{ $clinic->name ?? 'Medical Report' }}</h1>
                            @if($clinic->address || $clinic->phone || $clinic->email)
                            <div class="clinic-info" style="text-align: center; margin-bottom: 2px; color: #666; font-size: 9px;">
                                @if($clinic->address){{ $clinic->address }}@endif
                                @if($clinic->phone) | Tel: {{ $clinic->phone }}@endif
                                @if($clinic->email) | Email: {{ $clinic->email }}@endif
                            </div>
                            @endif
                            <div class="report-date" style="text-align: center; margin-bottom: 2px; color: #666; font-size: 9px;">
                                <strong>Date:</strong> {{ $generated_date->format('F d, Y') }}
                            </div>
                        </div>

                        @if(isset($report_title))
                        <!-- Report Title -->
                        <div class="report-title-section" style="text-align: center; margin-bottom: 10px; padding: 6px; background-color: #e3f2fd; border-left: 4px solid #2196F3;">
                            <h2 style="margin: 0; color: #1976D2; font-size: 14px;">{{ $report_title }}</h2>
                        </div>
                        @endif

                        <!-- Patient Information -->
                        <div class="patient-info-section" style="margin-bottom: 10px;">
                            <div class="section-header" style="background-color: #007bff; color: white; padding: 4px 8px; font-weight: bold; font-size: 11px; margin-bottom: 6px;">
                                Patient Information
                            </div>
                            <table class="info-table" style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <tr>
                                    <td style="width: 30%; padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Patient ID:</td>
                                    <td style="width: 70%; padding: 3px 6px; border: 1px solid #dee2e6;">{{ $patient->patient_id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Full Name:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ $patient->full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Date of Birth:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">
                                        @if($patient->date_of_birth)
                                            {{ $patient->date_of_birth->format('F d, Y') }} ({{ $patient->date_of_birth->age }} years old)
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Gender:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ ucfirst($patient->gender ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Phone:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ $patient->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Address:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ $patient->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Doctor Information -->
                        <div class="doctor-info-section" style="margin-bottom: 10px;">
                            <div class="section-header" style="background-color: #007bff; color: white; padding: 4px 8px; font-weight: bold; font-size: 11px; margin-bottom: 6px;">
                                Doctor Information
                            </div>
                            <table class="info-table" style="width: 100%; border-collapse: collapse; font-size: 10px;">
                                <tr>
                                    <td style="width: 30%; padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Doctor Name:</td>
                                    <td style="width: 70%; padding: 3px 6px; border: 1px solid #dee2e6;">{{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Specialization:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ $doctor->specialization ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px 6px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">License Number:</td>
                                    <td style="padding: 3px 6px; border: 1px solid #dee2e6;">{{ $doctor->license_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Notes Section -->
                        <div class="notes-section" style="margin-bottom: 10px;">
                            <div class="section-header" style="background-color: #007bff; color: white; padding: 4px 8px; font-weight: bold; font-size: 11px; margin-bottom: 6px;">
                                Notes / Special Information
                            </div>
                            <div class="notes-content" style="border: 1px solid #dee2e6; min-height: 140px; padding: 8px; background-color: #fafafa;">
                                <div style="white-space: pre-wrap; line-height: 1.5; color: #333; font-size: 10px;">{{ $notes }}</div>
                            </div>
                        </div>

                        <!-- Footer with Signatures -->
                        <div class="signature-section" style="margin-top: 12px; padding-top: 10px; border-top: 2px solid #dee2e6;">
                            <div style="display: flex; justify-content: space-between;">
                                <div style="width: 45%; text-align: center;">
                                    <div style="border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 10px;">
                                        <strong>Doctor's Signature</strong>
                                    </div>
                                </div>
                                <div style="width: 45%; text-align: center;">
                                    <div style="border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 10px;">
                                        <strong>Date</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="footer-info" style="margin-top: 10px; text-align: center; font-size: 8px; color: #666;">
                                <p style="margin: 2px 0;">This medical report will be saved to patient records.</p>
                                <p style="margin: 2px 0;">Generated on {{ $generated_date->format('F d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4 no-print">
            <!-- Report Details Card -->
            <div class="card shadow-sm mb-3 no-print">
                <div class="card-header bg-info text-white no-print">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Report Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ __('Patient') }}:</dt>
                        <dd class="col-sm-7">{{ $patient->full_name }}</dd>

                        <dt class="col-sm-5">{{ __('Patient ID') }}:</dt>
                        <dd class="col-sm-7">{{ $patient->patient_id }}</dd>

                        <dt class="col-sm-5">{{ __('Report Type') }}:</dt>
                        <dd class="col-sm-7">{{ $report_title }}</dd>

                        <dt class="col-sm-5">{{ __('Doctor') }}:</dt>
                        <dd class="col-sm-7">{{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }}</dd>

                        <dt class="col-sm-5">{{ __('Date') }}:</dt>
                        <dd class="col-sm-7">{{ $generated_date->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow-sm mb-3 no-print">
                <div class="card-header bg-success text-white no-print">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        {{ __('Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('patient.blank-report.generate', $patient) }}" method="POST" id="saveReportForm">
                        @csrf
                        <input type="hidden" name="report_title" value="{{ $report_title }}">
                        <input type="hidden" name="notes" value="{{ $notes }}">

                        <input type="hidden" name="template" id="templateField" value="">
                        <div class="d-grid gap-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-success btn-lg" style="flex: 1;" onclick="document.getElementById('templateField').value=''">
                                    <i class="fas fa-save me-2"></i>
                                    {{ __('Save & Download PDF') }}
                                </button>
                                <button type="button" class="btn btn-success btn-lg dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('templateField').value=''; document.getElementById('saveReportForm').submit();">
                                            <i class="fas fa-file-pdf me-2"></i>{{ __('Default PDF') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('templateField').value='custom'; document.getElementById('saveReportForm').action='{{ route('patient.blank-report.generate', $patient) }}?template=custom'; document.getElementById('saveReportForm').submit();">
                                            <i class="fas fa-image me-2"></i>{{ __('Custom Template PDF') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-primary" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>
                                {{ __('Print Preview') }}
                            </button>

                            <a href="{{ route('patient.blank-report', $patient) }}?report_title={{ urlencode($report_title) }}&notes={{ urlencode($notes) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-2"></i>
                                {{ __('Edit Report') }}
                            </a>

                            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-2"></i>
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card shadow-sm no-print">
                <div class="card-header bg-warning text-dark no-print">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        {{ __('What Happens Next?') }}
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>{{ __('PDF will be generated with your notes') }}</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>{{ __('Saved to patient files automatically') }}</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>{{ __('Downloaded to your computer') }}</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>{{ __('Accessible from patient files section') }}</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Page setup */
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide everything first */
    body * {
        visibility: hidden;
    }

    /* Show only the printable area and its children */
    #printableArea,
    #printableArea * {
        visibility: visible;
    }

    /* Position printable area at top left */
    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
    }

    /* Ensure proper display for different elements */
    #printableArea div {
        display: block;
    }

    #printableArea table {
        display: table;
        width: 100%;
    }

    #printableArea tr {
        display: table-row;
    }

    #printableArea td,
    #printableArea th {
        display: table-cell;
    }

    #printableArea img {
        display: block;
    }

    /* Font sizes */
    #printableArea h1 {
        font-size: 18px !important;
    }

    #printableArea h2 {
        font-size: 14px !important;
    }

    #printableArea table {
        font-size: 10px !important;
    }

    #printableArea td,
    #printableArea th {
        padding: 3px 6px !important;
    }

    /* Spacing adjustments */
    #printableArea .section-header {
        padding: 4px 8px !important;
        font-size: 11px !important;
        margin-bottom: 6px !important;
    }

    #printableArea .patient-info-section,
    #printableArea .doctor-info-section,
    #printableArea .notes-section {
        margin-bottom: 8px !important;
    }

    #printableArea .notes-content {
        min-height: 120px !important;
        padding: 8px !important;
    }

    #printableArea .signature-section {
        margin-top: 10px !important;
        padding-top: 8px !important;
    }

    #printableArea img {
        max-height: 45px !important;
    }

    /* Prevent page breaks */
    #printableArea table,
    #printableArea .signature-section {
        page-break-inside: avoid !important;
    }
}

/* Screen view - ensure logo is visible */
img[alt="Clinic Logo"] {
    display: block !important;
    max-height: 60px;
    margin: 0 auto 10px;
}
</style>

<script>
// Auto-submit form and show loading state
document.getElementById('saveReportForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Generating PDF...") }}';
});

// Preserve form data when editing
window.addEventListener('beforeunload', function(e) {
    // Store form data in sessionStorage
    sessionStorage.setItem('report_title', '{{ $report_title }}');
    sessionStorage.setItem('report_notes', '{{ $notes }}');
});
</script>
@endsection

