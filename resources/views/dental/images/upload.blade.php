@extends('layouts.app')

@section('title', __('Upload Dental Images') . ' - ' . $patient->full_name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-upload text-success me-2"></i>
                        {{ __('Upload Dental Images') }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ __('Patient') }}: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id }})
                    </p>
                </div>
                <div>
                    <a href="{{ url("/dental/patients/{$patient->id}/images") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Images') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ url("/dental/patients/{$patient->id}/images") }}" method="POST" enctype="multipart/form-data" id="upload-form">
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- File Upload -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-file-upload me-2"></i>
                            {{ __('Select Images') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Image Files') }} <span class="text-danger">*</span></label>
                            <input type="file" name="images[]" id="image-input" class="form-control" accept="image/*" multiple required>
                            <small class="text-muted">
                                {{ __('Accepted formats: JPG, PNG, GIF. Max size: 10MB per file. You can select multiple files.') }}
                            </small>
                            @error('images')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="image-preview" class="row g-3 mt-3" style="display: none;">
                            <!-- Previews will be inserted here by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Image Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Image Details') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Title -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="{{ __('e.g., Pre-treatment X-Ray') }}">
                                <small class="text-muted">{{ __('Optional: Will be auto-generated if left empty') }}</small>
                                @error('title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Image Type') }} <span class="text-danger">*</span></label>
                                <select name="image_type" class="form-select" required>
                                    <option value="">{{ __('Select Type') }}</option>
                                    <option value="xray" {{ old('image_type') === 'xray' ? 'selected' : '' }}>{{ __('X-Ray') }}</option>
                                    <option value="intraoral" {{ old('image_type') === 'intraoral' ? 'selected' : '' }}>{{ __('Intraoral Photo') }}</option>
                                    <option value="extraoral" {{ old('image_type') === 'extraoral' ? 'selected' : '' }}>{{ __('Extraoral Photo') }}</option>
                                    <option value="panoramic" {{ old('image_type') === 'panoramic' ? 'selected' : '' }}>{{ __('Panoramic X-Ray') }}</option>
                                    <option value="cbct" {{ old('image_type') === 'cbct' ? 'selected' : '' }}>{{ __('CBCT Scan') }}</option>
                                    <option value="other" {{ old('image_type') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('image_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tooth Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Tooth Number (FDI)') }}</label>
                                <input type="text" name="tooth_number" class="form-control" value="{{ old('tooth_number') }}" placeholder="e.g., 11, 21, 36">
                                <small class="text-muted">{{ __('Optional: FDI notation (11-18, 21-28, 31-38, 41-48)') }}</small>
                                @error('tooth_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Image Date -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Image Date') }}</label>
                                <input type="date" name="image_date" class="form-control" value="{{ old('image_date', date('Y-m-d')) }}">
                                <small class="text-muted">{{ __('Date when the image was taken') }}</small>
                                @error('image_date')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label class="form-label">{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="{{ __('Additional notes about this image...') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label">{{ __('Clinical Notes') }}</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('Clinical observations, findings, etc...') }}">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Link to Dental Chart/Treatment -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-link me-2"></i>
                            {{ __('Link to Records') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Dental Chart -->
                        <div class="mb-3">
                            <label class="form-label">{{ __('Dental Chart') }}</label>
                            <select name="dental_chart_id" class="form-select">
                                <option value="">{{ __('Not Linked') }}</option>
                                @foreach($dentalCharts as $chart)
                                    <option value="{{ $chart->id }}" {{ old('dental_chart_id') == $chart->id ? 'selected' : '' }}>
                                        {{ ucfirst($chart->chart_type) }} - {{ $chart->created_at->format('M d, Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Optional: Link to a dental chart') }}</small>
                        </div>

                        <!-- Dental Treatment -->
                        <div>
                            <label class="form-label">{{ __('Treatment Plan') }}</label>
                            <select name="dental_treatment_id" class="form-select">
                                <option value="">{{ __('Not Linked') }}</option>
                                @foreach($dentalTreatments as $treatment)
                                    <option value="{{ $treatment->id }}" {{ old('dental_treatment_id') == $treatment->id ? 'selected' : '' }}>
                                        {{ $treatment->treatment_number }} - {{ $treatment->procedure_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Optional: Link to a treatment plan') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Upload Guidelines -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Upload Guidelines') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>{{ __('Use high-quality images for better diagnosis') }}</li>
                            <li>{{ __('Ensure proper lighting for intraoral photos') }}</li>
                            <li>{{ __('Label images with correct tooth numbers') }}</li>
                            <li>{{ __('Include date when image was taken') }}</li>
                            <li>{{ __('Add clinical notes for future reference') }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-upload me-1"></i>
                            {{ __('Upload Images') }}
                        </button>
                        <a href="{{ url("/dental/patients/{$patient->id}/images") }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('image-input').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('image-preview');
    previewContainer.innerHTML = '';

    if (this.files.length > 0) {
        previewContainer.style.display = 'flex';

        Array.from(this.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-4 col-sm-6';

                    col.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="Preview ${index + 1}">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small><br>
                                <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                            </div>
                        </div>
                    `;

                    previewContainer.appendChild(col);
                };

                reader.readAsDataURL(file);
            }
        });
    } else {
        previewContainer.style.display = 'none';
    }
});
</script>
@endpush
@endsection

