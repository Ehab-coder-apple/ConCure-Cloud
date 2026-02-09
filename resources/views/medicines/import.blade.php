@extends('layouts.app')

@section('title', __('Import Medicines'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-import text-primary me-2"></i>
                        {{ __('Import Medicines') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Import medicines from Excel or CSV file') }}</p>
                </div>
                <div>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Inventory') }}
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
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>{{ __('Import Errors') }}</h6>
                    <pre class="mb-0 small">{{ session('import_errors') }}</pre>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Instructions Card -->
                <div class="col-lg-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
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
                                        <a href="{{ route('medicines.import.template', ['sample' => 1]) }}" class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> {{ __('Download Excel Template with Sample Data') }}
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <a href="{{ route('medicines.import.template', ['sample' => 0]) }}" class="btn btn-outline-success">
                                            <i class="fas fa-file-excel"></i> {{ __('Download Empty Excel Template') }}
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-primary">{{ __('Step 2: Prepare Your Data') }}</h6>
                                    <ul class="text-muted small">
                                        <li>{{ __('Name and Form columns are required') }}</li>
                                        <li>{{ __('Valid forms: tablet, capsule, syrup, injection, cream, ointment, drops, inhaler, patch, suppository, other') }}</li>
                                        <li>{{ __('Use true/false for is_frequent and is_active columns') }}</li>
                                        <li>{{ __('Duplicate medicines (same name, dosage, form) will be skipped') }}</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="font-weight-bold text-primary">{{ __('Step 3: Upload Your File') }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ __('Use the upload form below to import your medicines. Supported formats: Excel (.xlsx, .xls) and CSV (.csv).') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload Form Card -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-upload me-2"></i>
                                {{ __('Upload Medicine File') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('medicines.import.process') }}" enctype="multipart/form-data" id="importForm">
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
                                            <button type="submit" class="btn btn-primary w-100" id="importBtn">
                                                <i class="fas fa-upload me-1"></i>
                                                {{ __('Import Medicines') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Import Tips Card -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                {{ __('Import Tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Download the template first to ensure correct format') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Required fields: Name and Form') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Duplicate medicines will be automatically skipped') }}
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Import errors will be displayed for review') }}
                                </li>
                                <li class="mb-0">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ __('Large files are processed in batches for better performance') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update file input label when file is selected
document.getElementById('file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || '{{ __("Choose file...") }}';
    const label = document.querySelector('.custom-file-label');
    label.textContent = fileName;
});

// Show loading state on form submission
document.getElementById('importForm').addEventListener('submit', function() {
    const btn = document.getElementById('importBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>{{ __("Importing...") }}';
});
</script>
@endsection
