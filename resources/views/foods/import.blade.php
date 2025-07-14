@extends('layouts.app')

@section('title', __('Import Foods'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ __('Import Foods') }}</h1>
                    <p class="text-muted">{{ __('Upload a spreadsheet to import multiple foods at once') }}</p>
                </div>
                <div>
                    <a href="{{ route('foods.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Foods') }}
                    </a>
                </div>
            </div>

            <!-- Import Instructions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> {{ __('Import Instructions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-primary">{{ __('Step 1: Download Template') }}</h6>
                            <p class="text-muted mb-3">{{ __('Download the Excel template with the correct format and sample data.') }}</p>
                            
                            <!-- Excel Downloads Only -->
                            <div class="mb-3">
                                <a href="{{ route('foods.import.template', ['sample' => 1]) }}" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> {{ __('Download Excel Template with Sample Data') }}
                                </a>
                            </div>
                            <div class="mb-3">
                                <a href="{{ route('foods.import.template', ['sample' => 0]) }}" class="btn btn-outline-success">
                                    <i class="fas fa-file-excel"></i> {{ __('Download Empty Excel Template') }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-primary">{{ __('Step 2: Prepare Your Data') }}</h6>
                            <ul class="text-muted small">
                                <li>{{ __('Name and Calories columns are required') }}</li>
                                <li>{{ __('Multilingual support: Use name_en, name_ar, name_ku_bahdini, name_ku_sorani for translations') }}</li>
                                <li>{{ __('Description translations: Use description_en, description_ar, description_ku_bahdini, description_ku_sorani') }}</li>
                                <li>{{ __('Food groups will be created automatically if they don\'t exist') }}</li>
                                <li>{{ __('Duplicate foods (same name and calories) will be skipped') }}</li>
                                <li>{{ __('Save your file as Excel (.xlsx) or CSV (.csv) format') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-upload"></i> {{ __('Upload Food List') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="alert alert-danger" role="alert">
                            <h6><i class="fas fa-exclamation-triangle"></i> {{ __('Import Errors') }}</h6>
                            <pre class="mb-0 small">{{ session('import_errors') }}</pre>
                        </div>
                    @endif

                    <form action="{{ route('foods.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
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
                                        <i class="fas fa-upload"></i> {{ __('Import Foods') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expected Format Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table"></i> {{ __('Expected File Format') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> {{ __('Multilingual Support') }}</h6>
                            <p class="mb-0 small">{{ __('The import now supports multilingual food names and descriptions in English, Arabic, and Kurdish. You can provide translations in separate columns.') }}</p>
                        </div>

                        <div style="overflow-x: auto;">
                            <table class="table table-bordered table-sm" style="min-width: 1200px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>name</th>
                                        <th>name_en</th>
                                        <th>name_ar</th>
                                        <th>name_ku</th>
                                        <th>food_group</th>
                                        <th>calories</th>
                                        <th>protein</th>
                                        <th>carbs</th>
                                        <th>fat</th>
                                        <th>fiber</th>
                                        <th>description</th>
                                        <th>description_en</th>
                                        <th>description_ar</th>
                                        <th>description_ku</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Chicken Breast</td>
                                        <td>Chicken Breast</td>
                                        <td>صدر دجاج</td>
                                        <td>سنگی مریشک</td>
                                        <td>Proteins</td>
                                        <td>165</td>
                                        <td>31</td>
                                        <td>0</td>
                                        <td>3.6</td>
                                        <td>0</td>
                                        <td>Lean protein</td>
                                        <td>Lean protein source</td>
                                        <td>مصدر بروتين قليل الدهون</td>
                                        <td>سەرچاوەی پرۆتینی کەم چەوری</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>{{ __('Required columns:') }}</strong> name, calories<br>
                            <strong>{{ __('Optional columns:') }}</strong> name_en, name_ar, name_ku, food_group, protein, carbohydrates, fat, fiber, sugar, sodium, serving_size, serving_weight, description, description_en, description_ar, description_ku<br>
                            <strong>{{ __('Multilingual:') }}</strong> {{ __('Use name_en/name_ar/name_ku for food names in different languages') }}<br>
                            <strong>{{ __('Serving Size Examples:') }}</strong> 100g, 1 cup, 2 pieces, 1 tbsp, 1 slice, 1 medium, 1 handful<br>
                            <strong>{{ __('Note:') }}</strong> {{ __('Nutritional values can be for any serving size - specify the serving size in the serving_size column.') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update file input label when file is selected
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });

    // Auto-refresh CSRF token every 30 minutes to prevent expiration
    setInterval(function() {
        $.get('{{ route("csrf-token") }}').done(function(data) {
            $('meta[name="csrf-token"]').attr('content', data.token);
            $('input[name="_token"]').val(data.token);
        }).fail(function() {
            console.log('Failed to refresh CSRF token');
        });
    }, 30 * 60 * 1000); // 30 minutes

    // Show loading state on form submit
    $('#importForm').on('submit', function(e) {
        // Check if file is selected
        if (!$('#food_file')[0].files.length) {
            e.preventDefault();
            alert('{{ __("Please select a file to import.") }}');
            return false;
        }

        // Refresh CSRF token before submit to prevent 419 errors
        $.get('{{ route("csrf-token") }}').done(function(data) {
            $('input[name="_token"]').val(data.token);
            $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("Importing...") }}');
        }).fail(function() {
            // If CSRF refresh fails, show error
            e.preventDefault();
            alert('{{ __("Session expired. Please refresh the page and try again.") }}');
            return false;
        });
    });

    // Add warning when page is about to expire
    let warningShown = false;
    setTimeout(function() {
        if (!warningShown) {
            warningShown = true;
            if (confirm('{{ __("Your session will expire soon. Click OK to refresh the page and continue.") }}')) {
                window.location.reload();
            }
        }
    }, 7 * 60 * 60 * 1000); // 7 hours (1 hour before 8-hour expiration)
});
</script>
@endpush
