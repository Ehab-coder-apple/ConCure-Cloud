@extends('layouts.app')

@section('title', __('Add Medicine'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary me-2"></i>
                        {{ __('Add Medicine') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Add a new medicine to your clinic inventory') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('medicines.import') }}" class="btn btn-success">
                        <i class="fas fa-file-import me-1"></i>
                        {{ __('Import from Excel') }}
                    </a>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Inventory') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Medicine Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('medicines.store') }}">
                                @csrf

                                <div class="row g-3">
                                    <!-- Medicine Name -->
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">{{ __('Medicine Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" 
                                               placeholder="{{ __('e.g., Amoxicillin') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Generic Name -->
                                    <div class="col-md-6">
                                        <label for="generic_name" class="form-label">{{ __('Generic Name') }}</label>
                                        <input type="text" class="form-control @error('generic_name') is-invalid @enderror" 
                                               id="generic_name" name="generic_name" value="{{ old('generic_name') }}" 
                                               placeholder="{{ __('e.g., Amoxicillin Trihydrate') }}">
                                        @error('generic_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Brand Name -->
                                    <div class="col-md-6">
                                        <label for="brand_name" class="form-label">{{ __('Brand Name') }}</label>
                                        <input type="text" class="form-control @error('brand_name') is-invalid @enderror" 
                                               id="brand_name" name="brand_name" value="{{ old('brand_name') }}" 
                                               placeholder="{{ __('e.g., Augmentin') }}">
                                        @error('brand_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Dosage -->
                                    <div class="col-md-6">
                                        <label for="dosage" class="form-label">{{ __('Dosage/Strength') }}</label>
                                        <input type="text" class="form-control @error('dosage') is-invalid @enderror" 
                                               id="dosage" name="dosage" value="{{ old('dosage') }}" 
                                               placeholder="{{ __('e.g., 500mg, 250mg/5ml') }}">
                                        @error('dosage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Form -->
                                    <div class="col-md-6">
                                        <label for="form" class="form-label">{{ __('Medicine Form') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('form') is-invalid @enderror" id="form" name="form" required>
                                            <option value="">{{ __('Select Form') }}</option>
                                            @foreach(\App\Models\Medicine::FORMS as $key => $label)
                                                <option value="{{ $key }}" {{ old('form') == $key ? 'selected' : '' }}>
                                                    {{ __($label) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('form')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-12">
                                        <label for="description" class="form-label">{{ __('Description') }}</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3" 
                                                  placeholder="{{ __('Brief description of the medicine and its uses...') }}">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Side Effects -->
                                    <div class="col-md-6">
                                        <label for="side_effects" class="form-label">{{ __('Side Effects') }}</label>
                                        <textarea class="form-control @error('side_effects') is-invalid @enderror" 
                                                  id="side_effects" name="side_effects" rows="3" 
                                                  placeholder="{{ __('Common side effects...') }}">{{ old('side_effects') }}</textarea>
                                        @error('side_effects')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Contraindications -->
                                    <div class="col-md-6">
                                        <label for="contraindications" class="form-label">{{ __('Contraindications') }}</label>
                                        <textarea class="form-control @error('contraindications') is-invalid @enderror" 
                                                  id="contraindications" name="contraindications" rows="3" 
                                                  placeholder="{{ __('When not to use this medicine...') }}">{{ old('contraindications') }}</textarea>
                                        @error('contraindications')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Settings -->
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ __('Medicine Settings') }}</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="is_frequent" 
                                                                   name="is_frequent" value="1" {{ old('is_frequent') ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_frequent">
                                                                <i class="fas fa-star text-warning me-1"></i>
                                                                {{ __('Mark as Frequent Medicine') }}
                                                            </label>
                                                            <small class="form-text text-muted d-block">
                                                                {{ __('Frequent medicines appear first in prescription forms') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="is_active" 
                                                                   name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_active">
                                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                                {{ __('Active Medicine') }}
                                                            </label>
                                                            <small class="form-text text-muted d-block">
                                                                {{ __('Only active medicines can be prescribed') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                                        {{ __('Cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        {{ __('Add Medicine') }}
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
                                <h6 class="alert-heading">{{ __('Medicine Management Tips') }}</h6>
                                <ul class="mb-0 small">
                                    <li>{{ __('Use clear, standard medicine names') }}</li>
                                    <li>{{ __('Include dosage/strength for accuracy') }}</li>
                                    <li>{{ __('Mark frequently used medicines') }}</li>
                                    <li>{{ __('Add side effects and contraindications') }}</li>
                                    <li>{{ __('Keep medicine information up to date') }}</li>
                                </ul>
                            </div>

                            <div class="alert alert-warning">
                                <h6 class="alert-heading">{{ __('Important') }}</h6>
                                <p class="mb-0 small">
                                    {{ __('Only add medicines that are approved for use in your clinic. Always verify medicine information before adding to inventory.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
