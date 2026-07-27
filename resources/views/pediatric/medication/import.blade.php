@extends('layouts.app')

@section('title', __('Import Pediatric Drugs'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-file-import me-2 text-success"></i>
                        {{ __('Import Pediatric Drugs') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Upload Excel, CSV, or JSON file to bulk import drugs, forms, and dosage rules') }}</p>
                </div>
                <a href="{{ route('pediatric.medication.drug-admin') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Back to Drug Database') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>{{ __('Upload File') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pediatric.medication.import.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Select Excel, CSV, or JSON file') }}</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv,.json,.txt" required>
                            <div class="form-text">{{ __('Supported formats: .xlsx, .xls, .csv, .json') }}</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-eye me-2"></i>{{ __('Preview Import') }}
                        </button>
                    </form>
                    <hr>
                    <a href="{{ route('pediatric.medication.import.template') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-download me-2"></i>{{ __('Download Excel Template') }}
                    </a>
                    <div class="form-text text-center mt-1">{{ __('Use this template to fill in your drug data') }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('File Format') }}</h5>
                </div>
                <div class="card-body" style="font-size:0.85rem;">
                    <p><strong>Required columns:</strong></p>
                    <ul>
                        <li><code>generic_name</code> — Drug name (required)</li>
                        <li><code>mg_per_kg_min</code> — Minimum mg/kg dose (required)</li>
                        <li><code>mg_per_kg_max</code> — Maximum mg/kg dose (required)</li>
                    </ul>
                    <p><strong>Optional columns:</strong></p>
                    <ul>
                        <li><code>brand_name</code>, <code>category</code>, <code>description</code></li>
                        <li><code>form</code> (syrup/tablet/drops), <code>concentration</code> (e.g. 120mg/5ml)</li>
                        <li><code>concentration_mg</code>, <code>concentration_per_ml</code></li>
                        <li><code>max_daily_mg</code>, <code>frequency_per_day</code>, <code>frequency_hours</code></li>
                        <li><code>min_age_months</code>, <code>max_age_months</code>, <code>notes</code></li>
                    </ul>
                    <p class="mb-0"><strong>CSV example:</strong></p>
                    <pre class="bg-light p-2 rounded small">generic_name,brand_name,mg_per_kg_min,mg_per_kg_max,max_daily_mg,frequency_per_day,form,concentration,concentration_mg,concentration_per_ml
Paracetamol,Calpol,10,15,4000,4,syrup,120mg/5ml,120,5</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

