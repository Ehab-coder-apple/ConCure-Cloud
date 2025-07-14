@extends('layouts.app')

@section('title', __('Edit Medicine'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary me-2"></i>
                        {{ __('Edit Medicine') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Update medicine information in your clinic inventory') }}</p>
                </div>
                <div>
                    <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye me-1"></i>
                        {{ __('View Medicine') }}
                    </a>
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Inventory') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-pills me-2"></i>
                                {{ __('Medicine Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('medicines.update', $medicine) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Medicine Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">{{ __('Medicine Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $medicine->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Generic Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="generic_name" class="form-label">{{ __('Generic Name') }}</label>
                                        <input type="text" class="form-control @error('generic_name') is-invalid @enderror" 
                                               id="generic_name" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}">
                                        @error('generic_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Brand Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="brand_name" class="form-label">{{ __('Brand Name') }}</label>
                                        <input type="text" class="form-control @error('brand_name') is-invalid @enderror" 
                                               id="brand_name" name="brand_name" value="{{ old('brand_name', $medicine->brand_name) }}">
                                        @error('brand_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Dosage -->
                                    <div class="col-md-6 mb-3">
                                        <label for="dosage" class="form-label">{{ __('Dosage/Strength') }}</label>
                                        <input type="text" class="form-control @error('dosage') is-invalid @enderror" 
                                               id="dosage" name="dosage" value="{{ old('dosage', $medicine->dosage) }}" 
                                               placeholder="{{ __('e.g., 500mg, 10ml, 250mg/5ml') }}">
                                        @error('dosage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Form -->
                                    <div class="col-md-6 mb-3">
                                        <label for="form" class="form-label">{{ __('Form') }} <span class="text-danger">*</span></label>
                                        <select class="form-select @error('form') is-invalid @enderror" id="form" name="form" required>
                                            <option value="">{{ __('Select form...') }}</option>
                                            @foreach(\App\Models\Medicine::FORMS as $key => $value)
                                                <option value="{{ $key }}" {{ old('form', $medicine->form) == $key ? 'selected' : '' }}>
                                                    {{ __($value) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('form')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div class="col-12 mb-3">
                                        <label for="description" class="form-label">{{ __('Description') }}</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="3" 
                                                  placeholder="{{ __('Brief description of the medicine...') }}">{{ old('description', $medicine->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Side Effects -->
                                    <div class="col-md-6 mb-3">
                                        <label for="side_effects" class="form-label">{{ __('Side Effects') }}</label>
                                        <textarea class="form-control @error('side_effects') is-invalid @enderror" 
                                                  id="side_effects" name="side_effects" rows="3" 
                                                  placeholder="{{ __('Common side effects...') }}">{{ old('side_effects', $medicine->side_effects) }}</textarea>
                                        @error('side_effects')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Contraindications -->
                                    <div class="col-md-6 mb-3">
                                        <label for="contraindications" class="form-label">{{ __('Contraindications') }}</label>
                                        <textarea class="form-control @error('contraindications') is-invalid @enderror" 
                                                  id="contraindications" name="contraindications" rows="3" 
                                                  placeholder="{{ __('When not to use this medicine...') }}">{{ old('contraindications', $medicine->contraindications) }}</textarea>
                                        @error('contraindications')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Settings -->
                                    <div class="col-12 mb-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title mb-3">{{ __('Medicine Settings') }}</h6>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="is_frequent" 
                                                                   name="is_frequent" value="1" 
                                                                   {{ old('is_frequent', $medicine->is_frequent) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_frequent">
                                                                {{ __('Frequently Used') }}
                                                            </label>
                                                            <small class="form-text text-muted d-block">
                                                                {{ __('Mark as frequently used for quick access') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="is_active" 
                                                                   name="is_active" value="1" 
                                                                   {{ old('is_active', $medicine->is_active) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="is_active">
                                                                {{ __('Active') }}
                                                            </label>
                                                            <small class="form-text text-muted d-block">
                                                                {{ __('Available for prescriptions') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>
                                            {{ __('Cancel') }}
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>
                                            {{ __('Update Medicine') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Medicine Info Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('Current Medicine Info') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Medicine Name') }}</small>
                                <div class="fw-bold">{{ $medicine->name }}</div>
                            </div>
                            
                            @if($medicine->generic_name)
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Generic Name') }}</small>
                                <div>{{ $medicine->generic_name }}</div>
                            </div>
                            @endif

                            @if($medicine->brand_name)
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Brand Name') }}</small>
                                <div>{{ $medicine->brand_name }}</div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <small class="text-muted">{{ __('Form') }}</small>
                                <div>{{ $medicine->form_display }}</div>
                            </div>

                            @if($medicine->dosage)
                            <div class="mb-3">
                                <small class="text-muted">{{ __('Dosage/Strength') }}</small>
                                <div>{{ $medicine->dosage }}</div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <small class="text-muted">{{ __('Status') }}</small>
                                <div>
                                    <span class="badge bg-{{ $medicine->is_active ? 'success' : 'secondary' }}">
                                        {{ $medicine->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                    @if($medicine->is_frequent)
                                        <span class="badge bg-info ms-1">{{ __('Frequent') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-0">
                                <small class="text-muted">{{ __('Created') }}</small>
                                <div>{{ $medicine->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
