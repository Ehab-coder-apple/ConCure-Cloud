<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Lab Request') }} - {{ $labRequest->request_number }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .container { max-width: none; }
        }
        
        .header-section {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-section {
            margin-bottom: 1.5rem;
        }
        
        .tests-section {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .signature-section {
            margin-top: 3rem;
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Print Button -->
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-1"></i>
                {{ __('Print') }}
            </button>
            <button onclick="window.close()" class="btn btn-secondary ms-2">
                {{ __('Close') }}
            </button>
        </div>

        <!-- Header -->
        <div class="header-section">
            @php
                $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($labRequest->patient->clinic_id);
                $clinicName = $labRequest->patient->clinic->name ?? 'ConCure Clinic';
            @endphp

            <!-- Clinic Header with Logo -->
            <div class="d-flex align-items-center justify-content-center mb-3">
                @if($clinicLogo)
                    <img src="{{ $clinicLogo }}" alt="Clinic Logo" style="max-height: 80px; max-width: 80px; object-fit: cover; margin-right: 15px; border-radius: 8px; border: 1px solid #e9ecef; padding: 2px;">
                @endif
                <div class="text-center">
                    <h3 class="text-primary mb-1">{{ $clinicName }}</h3>
                    <p class="text-muted mb-0">{{ __('Laboratory Test Request') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-primary">{{ $labRequest->request_number }}</h4>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-1"><strong>{{ __('Date') }}:</strong> {{ $labRequest->created_at->format('M d, Y') }}</p>
                    <p class="mb-1"><strong>{{ __('Priority') }}:</strong>
                        <span class="badge {{ $labRequest->priority === 'urgent' ? 'bg-warning' : ($labRequest->priority === 'stat' ? 'bg-danger' : 'bg-secondary') }}">
                            {{ strtoupper($labRequest->priority) }}
                        </span>
                    </p>
                    @if($labRequest->due_date)
                        <p class="mb-0"><strong>{{ __('Due Date') }}:</strong> {{ $labRequest->due_date->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Patient Information -->
        <div class="info-section">
            <div class="row">
                <div class="col-md-6">
                    <h5>{{ __('Patient Information') }}</h5>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ __('Name') }}:</strong></td>
                            <td>{{ $labRequest->patient->first_name }} {{ $labRequest->patient->last_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Patient ID') }}:</strong></td>
                            <td>{{ $labRequest->patient->patient_id }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Date of Birth') }}:</strong></td>
                            <td>{{ $labRequest->patient->date_of_birth ? $labRequest->patient->date_of_birth->format('M d, Y') : __('Not specified') }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Gender') }}:</strong></td>
                            <td>{{ $labRequest->patient->gender ? ucfirst($labRequest->patient->gender) : __('Not specified') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>{{ __('Requesting Physician') }}</h5>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>{{ __('Doctor') }}:</strong></td>
                            <td>{{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>{{ __('Request Date') }}:</strong></td>
                            <td>{{ $labRequest->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($labRequest->lab_name)
                        <tr>
                            <td><strong>{{ __('Laboratory') }}:</strong></td>
                            <td>{{ $labRequest->lab_name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Clinical Notes -->
        @if($labRequest->clinical_notes)
        <div class="info-section">
            <h5>{{ __('Clinical Notes') }}</h5>
            <div class="bg-light p-3 rounded">
                {{ $labRequest->clinical_notes }}
            </div>
        </div>
        @endif

        <!-- Tests Required -->
        <div class="tests-section">
            <h5>{{ __('Tests Required') }}</h5>
            @if($labRequest->tests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 40%">{{ __('Test Name') }}</th>
                                <th style="width: 35%">{{ __('Instructions') }}</th>
                                <th style="width: 20%">{{ __('Result') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labRequest->tests as $index => $test)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $test->test_name }}</strong></td>
                                <td>{{ $test->instructions ?: __('No special instructions') }}</td>
                                <td style="border-left: 1px solid #dee2e6;">
                                    <!-- Space for lab results -->
                                    <div style="height: 40px;"></div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">{{ __('No tests specified') }}</p>
            @endif
        </div>

        <!-- Additional Notes -->
        @if($labRequest->notes)
        <div class="info-section">
            <h5>{{ __('Additional Notes') }}</h5>
            <div class="bg-light p-3 rounded">
                {{ $labRequest->notes }}
            </div>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="border-top pt-2 mt-4">
                        <p class="mb-0"><strong>{{ __('Physician Signature') }}</strong></p>
                        <p class="text-muted small">{{ $labRequest->doctor->first_name }} {{ $labRequest->doctor->last_name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border-top pt-2 mt-4">
                        <p class="mb-0"><strong>{{ __('Date') }}</strong></p>
                        <p class="text-muted small">{{ $labRequest->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4 text-muted small">
            <p>{{ __('This is a computer-generated lab request.') }}</p>
        </div>
    </div>

    <!-- Auto-print script -->
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
