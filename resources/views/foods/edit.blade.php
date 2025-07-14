@extends('layouts.app')

@section('title', __('Edit Food') . ' - ' . $food->translated_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-primary"></i>
                        {{ __('Edit Food') }}
                    </h1>
                    <p class="text-muted">{{ __('Editing') }}: {{ $food->translated_name }}</p>
                </div>
                <div>
                    <a href="{{ route('foods.show', $food) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Food') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('foods.update', $food) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> {{ __('Basic Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                {{ __('Food Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $food->name) }}" 
                                   required>
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
                                           value="{{ old('name_en', $food->getNameTranslation('en')) }}">
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
                                           value="{{ old('name_ar', $food->getNameTranslation('ar')) }}" 
                                           dir="rtl">
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
                                           value="{{ old('name_ku', $food->getNameTranslation('ku')) }}">
                                    @error('name_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="food_group_id" class="form-label">
                                {{ __('Food Group') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-control @error('food_group_id') is-invalid @enderror" 
                                    id="food_group_id" 
                                    name="food_group_id" 
                                    required>
                                <option value="">{{ __('Select Food Group') }}</option>
                                @foreach($foodGroups as $group)
                                    <option value="{{ $group->id }}" 
                                            {{ old('food_group_id', $food->food_group_id) == $group->id ? 'selected' : '' }}>
                                        {{ $group->translated_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('food_group_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $food->description) }}</textarea>
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
                                              rows="2">{{ old('description_en', $food->getDescriptionTranslation('en')) }}</textarea>
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
                                              dir="rtl">{{ old('description_ar', $food->getDescriptionTranslation('ar')) }}</textarea>
                                    @error('description_ar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="description_ku" class="form-label small">{{ __('Kurdish') }}</label>
                                    <textarea class="form-control @error('description_ku') is-invalid @enderror" 
                                              id="description_ku" 
                                              name="description_ku" 
                                              rows="2">{{ old('description_ku', $food->getDescriptionTranslation('ku')) }}</textarea>
                                    @error('description_ku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nutritional Information -->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-pie"></i> {{ __('Nutritional Information') }}
                            <small class="text-muted">({{ __('per 100g') }})</small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="calories" class="form-label">
                                        {{ __('Calories') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('calories') is-invalid @enderror" 
                                           id="calories" 
                                           name="calories" 
                                           value="{{ old('calories', $food->calories) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="9999" 
                                           required>
                                    @error('calories')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="protein" class="form-label">
                                        {{ __('Protein (g)') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('protein') is-invalid @enderror" 
                                           id="protein" 
                                           name="protein" 
                                           value="{{ old('protein', $food->protein) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="999" 
                                           required>
                                    @error('protein')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="carbohydrates" class="form-label">
                                        {{ __('Carbohydrates (g)') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('carbohydrates') is-invalid @enderror" 
                                           id="carbohydrates" 
                                           name="carbohydrates" 
                                           value="{{ old('carbohydrates', $food->carbohydrates) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="999" 
                                           required>
                                    @error('carbohydrates')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fat" class="form-label">
                                        {{ __('Fat (g)') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('fat') is-invalid @enderror" 
                                           id="fat" 
                                           name="fat" 
                                           value="{{ old('fat', $food->fat) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="999" 
                                           required>
                                    @error('fat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fiber" class="form-label">{{ __('Fiber (g)') }}</label>
                                    <input type="number" 
                                           class="form-control @error('fiber') is-invalid @enderror" 
                                           id="fiber" 
                                           name="fiber" 
                                           value="{{ old('fiber', $food->fiber) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="999">
                                    @error('fiber')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sugar" class="form-label">{{ __('Sugar (g)') }}</label>
                                    <input type="number" 
                                           class="form-control @error('sugar') is-invalid @enderror" 
                                           id="sugar" 
                                           name="sugar" 
                                           value="{{ old('sugar', $food->sugar) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="999">
                                    @error('sugar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sodium" class="form-label">{{ __('Sodium (mg)') }}</label>
                                    <input type="number" 
                                           class="form-control @error('sodium') is-invalid @enderror" 
                                           id="sodium" 
                                           name="sodium" 
                                           value="{{ old('sodium', $food->sodium) }}" 
                                           step="0.1" 
                                           min="0" 
                                           max="99999">
                                    @error('sodium')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="serving_size" class="form-label">{{ __('Serving Size') }}</label>
                                    <input type="text" 
                                           class="form-control @error('serving_size') is-invalid @enderror" 
                                           id="serving_size" 
                                           name="serving_size" 
                                           value="{{ old('serving_size', $food->serving_size) }}" 
                                           placeholder="e.g., 100g, 1 cup, 1 piece">
                                    @error('serving_size')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('foods.show', $food) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ __('Cancel') }}
                            </a>
                            <div>
                                @if($food->is_custom)
                                    <button type="button" class="btn btn-danger me-2" onclick="confirmDelete()">
                                        <i class="fas fa-trash"></i> {{ __('Delete Food') }}
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('Update Food') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($food->is_custom)
        <!-- Delete Form (Hidden) -->
        <form id="deleteForm" action="{{ route('foods.destroy', $food) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('{{ __("Are you sure you want to delete this food? This action cannot be undone.") }}')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush
