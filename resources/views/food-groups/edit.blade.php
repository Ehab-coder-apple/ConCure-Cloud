@extends('layouts.app')

@section('title', __('Edit Food Group') . ' - ' . $foodGroup->translated_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        {{ __('Edit Food Group') }}
                    </h1>
                    <p class="text-muted">{{ __('Editing') }}: {{ $foodGroup->translated_name }}</p>
                </div>
                <div>
                    <a href="{{ route('food-groups.show', $foodGroup) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Group') }}
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
                    <form action="{{ route('food-groups.update', $foodGroup) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name" class="form-label">
                                {{ __('Group Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $foodGroup->name) }}" 
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
                                           value="{{ old('name_en', $foodGroup->getNameTranslation('en')) }}"
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
                                           value="{{ old('name_ar', $foodGroup->getNameTranslation('ar')) }}" 
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
                                           value="{{ old('name_ku', $foodGroup->getNameTranslation('ku')) }}"
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
                                      placeholder="{{ __('Brief description of this food group...') }}">{{ old('description', $foodGroup->description) }}</textarea>
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
                                              placeholder="{{ __('English description') }}">{{ old('description_en', $foodGroup->getDescriptionTranslation('en')) }}</textarea>
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
                                              placeholder="{{ __('Arabic description') }}">{{ old('description_ar', $foodGroup->getDescriptionTranslation('ar')) }}</textarea>
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
                                              placeholder="{{ __('Kurdish description') }}">{{ old('description_ku', $foodGroup->getDescriptionTranslation('ku')) }}</textarea>
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
                                   value="{{ old('display_order', $foodGroup->display_order ?? 0) }}" 
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
                                       {{ old('is_active', $foodGroup->is_active) ? 'checked' : '' }}>
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
                            <a href="{{ route('food-groups.show', $foodGroup) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                            </a>
                            <div>
                                @if($foodGroup->foods->count() == 0)
                                    <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i> {{ __('Delete Group') }}
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Update Food Group') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($foodGroup->foods->count() > 0)
                <!-- Foods in Group Info -->
                <div class="card shadow mt-4">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ __('This food group contains') }} <strong>{{ $foodGroup->foods->count() }}</strong> {{ __('foods') }}.
                            {{ __('You cannot delete this group until all foods are moved to other groups or deleted.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($foodGroup->foods->count() == 0)
        <!-- Delete Form (Hidden) -->
        <form id="deleteForm" action="{{ route('food-groups.destroy', $foodGroup) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('{{ __("Are you sure you want to delete this food group? This action cannot be undone.") }}')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
