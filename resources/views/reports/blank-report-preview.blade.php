@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Preview Panel -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        {{ __('Report Preview') }}
                    </h5>
                    <span class="badge bg-light text-dark">{{ __('Preview Mode') }}</span>
                </div>
                <div class="card-body p-4" style="background-color: #f5f5f5;">
                    <!-- PDF-like Preview -->
                    <div class="bg-white shadow-sm p-5" style="max-width: 800px; margin: 0 auto; min-height: 1000px;">
                        <!-- Header -->
                        <div style="text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 15px; margin-bottom: 30px;">
                            @if($clinic->logo)
                                <img src="{{ asset('storage/' . $clinic->logo) }}" alt="Clinic Logo" style="max-height: 60px; margin-bottom: 10px;">
                            @endif
                            <h1 style="color: #007bff; margin: 0 0 10px 0; font-size: 24px;">{{ $clinic->name ?? 'Medical Report' }}</h1>
                            @if($clinic->address || $clinic->phone || $clinic->email)
                            <div style="text-align: center; margin-bottom: 5px; color: #666; font-size: 11px;">
                                @if($clinic->address){{ $clinic->address }}@endif
                                @if($clinic->phone) | Tel: {{ $clinic->phone }}@endif
                                @if($clinic->email) | Email: {{ $clinic->email }}@endif
                            </div>
                            @endif
                            <div style="text-align: center; margin-bottom: 5px; color: #666; font-size: 11px;">
                                <strong>Date:</strong> {{ $generated_date->format('F d, Y') }}
                            </div>
                        </div>

                        @if(isset($report_title))
                        <!-- Report Title -->
                        <div style="text-align: center; margin-bottom: 20px; padding: 10px; background-color: #e3f2fd; border-left: 4px solid #2196F3;">
                            <h2 style="margin: 0; color: #1976D2; font-size: 18px;">{{ $report_title }}</h2>
                        </div>
                        @endif

                        <!-- Patient Information -->
                        <div style="margin-bottom: 25px;">
                            <div style="background-color: #007bff; color: white; padding: 10px; font-weight: bold; font-size: 14px; margin-bottom: 15px;">
                                Patient Information
                            </div>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 30%; padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Patient ID:</td>
                                    <td style="width: 70%; padding: 8px; border: 1px solid #dee2e6;">{{ $patient->patient_id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Full Name:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $patient->full_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Date of Birth:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">
                                        @if($patient->date_of_birth)
                                            {{ $patient->date_of_birth->format('F d, Y') }} ({{ $patient->date_of_birth->age }} years old)
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Gender:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ ucfirst($patient->gender ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Phone:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $patient->phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Address:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $patient->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Doctor Information -->
                        <div style="margin-bottom: 25px;">
                            <div style="background-color: #007bff; color: white; padding: 10px; font-weight: bold; font-size: 14px; margin-bottom: 15px;">
                                Doctor Information
                            </div>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 30%; padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Doctor Name:</td>
                                    <td style="width: 70%; padding: 8px; border: 1px solid #dee2e6;">{{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">Specialization:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $doctor->specialization ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px; font-weight: bold; background-color: #f8f9fa; border: 1px solid #dee2e6;">License Number:</td>
                                    <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $doctor->license_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Notes Section -->
                        <div style="margin-bottom: 25px;">
                            <div style="background-color: #007bff; color: white; padding: 10px; font-weight: bold; font-size: 14px; margin-bottom: 15px;">
                                Notes / Special Information
                            </div>
                            <div style="border: 1px solid #dee2e6; min-height: 300px; padding: 15px; background-color: #fafafa;">
                                <div style="white-space: pre-wrap; line-height: 1.8; color: #333;">{{ $notes }}</div>
                            </div>
                        </div>

                        <!-- Footer with Signatures -->
                        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #dee2e6;">
                            <div style="display: flex; justify-content: space-between;">
                                <div style="width: 45%; text-align: center;">
                                    <div style="border-top: 1px solid #333; margin-top: 60px; padding-top: 5px;">
                                        <strong>Doctor's Signature</strong>
                                    </div>
                                </div>
                                <div style="width: 45%; text-align: center;">
                                    <div style="border-top: 1px solid #333; margin-top: 60px; padding-top: 5px;">
                                        <strong>Date</strong>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
                                <p>This medical report will be saved to patient records.</p>
                                <p>Generated on {{ $generated_date->format('F d, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4">
            <!-- Report Details Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
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
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-success text-white">
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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>
                                {{ __('Save & Download PDF') }}
                            </button>

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
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
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
    .card-header,
    .col-lg-4,
    nav,
    .navbar,
    footer {
        display: none !important;
    }

    .col-lg-8 {
        width: 100% !important;
        max-width: 100% !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .card-body {
        padding: 0 !important;
        background-color: white !important;
    }
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

