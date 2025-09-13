@extends('layouts.app')

@section('title', __('Import Patients'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-import text-primary me-2"></i>
                        {{ __('Import Patients') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Bulk import patients from Excel or CSV files') }}</p>
                </div>
                <div>
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Patients') }}
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('import_errors'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>{{ __('Import Errors:') }}</strong>
                    <pre class="mt-2 mb-0" style="white-space: pre-wrap;">{{ session('import_errors') }}</pre>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Import Instructions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Import Instructions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-primary">{{ __('Step 1: Download Template') }}</h6>
                            
                            <!-- Excel Downloads Only -->
                            <div class="mb-3">
                                <a href="{{ route('patients.import.template', ['sample' => 1]) }}" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> {{ __('Download Excel Template with Sample Data') }}
                                </a>
                            </div>
                            <div class="mb-3">
                                <a href="{{ route('patients.import.template', ['sample' => 0]) }}" class="btn btn-outline-success">
                                    <i class="fas fa-file-excel"></i> {{ __('Download Empty Excel Template') }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-primary">{{ __('Step 2: Prepare Your Data') }}</h6>
                            <ul class="text-muted small">
                                <li>{{ __('First Name and Last Name columns are required') }}</li>
                                <li>{{ __('Date format: YYYY-MM-DD (e.g., 1985-03-15)') }}</li>
                                <li>{{ __('Gender: male or female') }}</li>
                                <li>{{ __('Phone numbers: include country code (e.g., +9647501234567)') }}</li>
                                <li>{{ __('Height in cm, Weight in kg') }}</li>
                                <li>{{ __('Boolean fields: true/false, yes/no, 1/0') }}</li>
                                <li>{{ __('Duplicate patients (same name + phone) will be skipped') }}</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="font-weight-bold text-primary">{{ __('Step 3: Upload Your File') }}</h6>
                            <p class="text-muted small mb-0">
                                {{ __('Use the upload form below to import your patients. Supported formats: Excel (.xlsx, .xls) and CSV (.csv).') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-upload me-2"></i>
                        {{ __('Upload Patients File') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('patients.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="file" class="form-label font-weight-bold">
                                        {{ __('Select File') }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="custom-file">
                                        <input type="file" 
                                               class="custom-file-input @error('file') is-invalid @enderror" 
                                               id="file" 
                                               name="file" 
                                               accept=".xlsx,.xls,.csv"
                                               required>
                                        <label class="custom-file-label" for="file">{{ __('Choose file...') }}</label>
                                    </div>
                                    @error('file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Maximum file size: 10MB.') }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-block" id="importBtn">
                                        <i class="fas fa-upload me-2"></i>
                                        {{ __('Import Patients') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Tips -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        {{ __('Import Tips') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">{{ __('✓ Best Practices') }}</h6>
                            <ul class="text-muted small">
                                <li>{{ __('Use the provided Excel template for best results') }}</li>
                                <li>{{ __('Test with a small batch first (5-10 patients)') }}</li>
                                <li>{{ __('Ensure phone numbers include country codes') }}</li>
                                <li>{{ __('Use consistent date formats (YYYY-MM-DD)') }}</li>
                                <li>{{ __('Fill required fields: First Name, Last Name') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning">{{ __('⚠ Common Issues') }}</h6>
                            <ul class="text-muted small">
                                <li>{{ __('Empty rows will be skipped automatically') }}</li>
                                <li>{{ __('Invalid dates will cause row to be skipped') }}</li>
                                <li>{{ __('Duplicate patients (same name + phone) will be skipped') }}</li>
                                <li>{{ __('Invalid email formats will cause validation errors') }}</li>
                                <li>{{ __('BMI will be calculated automatically if height/weight provided') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update file input label when file is selected
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Show loading state on form submit
    $('#importForm').on('submit', function() {
        $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>{{ __("Importing...") }}');
    });

    // Auto-hide alerts after 10 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 10000);
});
</script>
@endpush
@endsection
