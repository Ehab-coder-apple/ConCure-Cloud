@extends('layouts.app')

@section('title', __('Upload Form Template'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Upload Form Template') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Supported types') }}: {{ implode(', ', $allowedExtensions) }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('forms.templates.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Templates') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Template Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('forms.templates.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">{{ __('Template Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="category" class="form-label">{{ __('Category') }}</label>
                                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}" placeholder="{{ __('e.g., Consent, Intake, Insurance') }}">
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="description" class="form-label">{{ __('Description') }}</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="{{ __('Brief description of the template...') }}">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="file" class="form-label">{{ __('File') }} <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".doc,.docx,.xls,.xlsx" required>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted d-block mt-1">{{ __('Max size') }}: {{ number_format(config('app.concure.max_file_size') / 1024, 1) }} MB</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('forms.templates.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i>
                                        {{ __('Upload Template') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                {{ __('Tips') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">{{ __('Template Upload Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Use clear names (e.g., Consent Form v1)') }}</li>
                                    <li>{{ __('Supported types: Word (doc, docx) and Excel (xls, xlsx)') }}</li>
                                    <li>{{ __('You can replace the file later when updating') }}</li>
                                    <li>{{ __('Keep a copy of original form in your records') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

