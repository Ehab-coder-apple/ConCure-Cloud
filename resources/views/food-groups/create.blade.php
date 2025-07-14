@extends('layouts.app')

@section('title', __('Add New Food Group'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus text-primary"></i>
                        {{ __('Add New Food Group') }}
                    </h1>
                    <p class="text-muted">{{ __('Create a new food category or group') }}</p>
                </div>
                <div>
                    <a href="{{ route('food-groups.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Food Groups') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> {{ __('Food Group Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('food-groups.store') }}" method="POST">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name" class="form-label">
                                {{ __('Group Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   placeholder="{{ __('e.g., Vegetables, Fruits, Proteins') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multilingual Names -->
                        <div class="form-group">
                            <label class="form-label">{{ __('Multilingual Names') }}</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="name_en" class="form-label small">{{ __('English') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           id="name_en" 
                                           name="name_en" 
                                           value="{{ old('name_en') }}"
                                           placeholder="{{ __('English name') }}">
                                    @error('name_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="name_ar" class="form-label small">{{ __('Arabic') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name_ar') is-invalid @enderror" 
                                           id="name_ar" 
                                           name="name_ar" 
                                           value="{{ old('name_ar') }}" 
                                           dir="rtl"
                                           placeholder="{{ __('Arabic name') }}">
                                    @error('name_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="name_ku" class="form-label small">{{ __('Kurdish') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name_ku') is-invalid @enderror" 
                                           id="name_ku" 
                                           name="name_ku" 
                                           value="{{ old('name_ku') }}"
                                           placeholder="{{ __('Kurdish name') }}">
                                    @error('name_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                {{ __('Provide translations for the food group name in different languages.') }}
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="{{ __('Brief description of this food group...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Multilingual Descriptions -->
                        <div class="form-group">
                            <label class="form-label">{{ __('Multilingual Descriptions') }}</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="description_en" class="form-label small">{{ __('English') }}</label>
                                    <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                              id="description_en" 
                                              name="description_en" 
                                              rows="2"
                                              placeholder="{{ __('English description') }}">{{ old('description_en') }}</textarea>
                                    @error('description_en')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="description_ar" class="form-label small">{{ __('Arabic') }}</label>
                                    <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                              id="description_ar" 
                                              name="description_ar" 
                                              rows="2" 
                                              dir="rtl"
                                              placeholder="{{ __('Arabic description') }}">{{ old('description_ar') }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="description_ku" class="form-label small">{{ __('Kurdish') }}</label>
                                    <textarea class="form-control @error('description_ku') is-invalid @enderror" 
                                              id="description_ku" 
                                              name="description_ku" 
                                              rows="2"
                                              placeholder="{{ __('Kurdish description') }}">{{ old('description_ku') }}</textarea>
                                    @error('description_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Display Order -->
                        <div class="form-group">
                            <label for="display_order" class="form-label">{{ __('Display Order') }}</label>
                            <input type="number" 
                                   class="form-control @error('display_order') is-invalid @enderror" 
                                   id="display_order" 
                                   name="display_order" 
                                   value="{{ old('display_order', 0) }}" 
                                   min="0"
                                   placeholder="{{ __('Order for sorting (0 = first)') }}">
                            @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                {{ __('Lower numbers appear first in lists. Leave as 0 for automatic ordering.') }}
                            </small>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" 
                                       class="form-check-input @error('is_active') is-invalid @enderror" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('Active') }}
                                </label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                {{ __('Only active food groups will be available for selection when adding foods.') }}
                            </small>
                        </div>

                        <!-- Submit Buttons -->
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('food-groups.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('Save Food Group') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill multilingual fields if main field is filled
    $('#name').on('blur', function() {
        const mainName = $(this).val();
        if (mainName && !$('#name_en').val()) {
            $('#name_en').val(mainName);
        }
    });

    $('#description').on('blur', function() {
        const mainDescription = $(this).val();
        if (mainDescription && !$('#description_en').val()) {
            $('#description_en').val(mainDescription);
        }
    });
});
</script>
@endpush
