@extends('layouts.app')

@section('title', $food->translated_name)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-apple-alt text-primary"></i>
                        {{ $food->translated_name }}
                    </h1>
                    <p class="text-muted mb-0">
                        {{ $food->foodGroup ? $food->foodGroup->translated_name : __('No group assigned') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('foods.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('Back to Foods') }}
                    </a>
                    @can('manage-food-composition')
                        @if($food->is_custom)
                            <a href="{{ route('foods.edit', $food) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> {{ __('Edit Food') }}
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

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
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('Name') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $food->name }}
                        </div>
                    </div>

                    @if($food->name_translations)
                        <hr>
                        <h6 class="font-weight-bold text-primary">{{ __('Translations') }}</h6>
                        @foreach($food->name_translations as $locale => $translation)
                            <div class="row mb-2">
                                <div class="col-sm-4">
                                    <strong>
                                        @if($locale === 'en') {{ __('English') }}
                                        @elseif($locale === 'ar') {{ __('Arabic') }}
                                        @elseif($locale === 'ku') {{ __('Kurdish') }}
                                        @else {{ ucfirst($locale) }}
                                        @endif:
                                    </strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $translation }}
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-sm-4">
                            <strong>{{ __('Food Group') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            @if($food->foodGroup)
                                <span class="badge badge-info">{{ $food->foodGroup->translated_name }}</span>
                            @else
                                <span class="text-muted">{{ __('No group assigned') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-sm-4">
                            <strong>{{ __('Type') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            @if($food->is_custom)
                                <span class="badge badge-success">{{ __('Custom Food') }}</span>
                            @else
                                <span class="badge badge-primary">{{ __('Standard Food') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-sm-4">
                            <strong>{{ __('Status') }}:</strong>
                        </div>
                        <div class="col-sm-8">
                            @if($food->is_active)
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($food->description || $food->description_translations)
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <strong>{{ __('Description') }}:</strong>
                            </div>
                            <div class="col-sm-8">
                                {{ $food->translated_description ?? $food->description ?? __('No description available') }}
                            </div>
                        </div>

                        @if($food->description_translations)
                            <div class="mt-3">
                                <h6 class="font-weight-bold text-primary">{{ __('Description Translations') }}</h6>
                                @foreach($food->description_translations as $locale => $translation)
                                    <div class="row mb-2">
                                        <div class="col-sm-4">
                                            <strong>
                                                @if($locale === 'en') {{ __('English') }}
                                                @elseif($locale === 'ar') {{ __('Arabic') }}
                                                @elseif($locale === 'ku') {{ __('Kurdish') }}
                                                @else {{ ucfirst($locale) }}
                                                @endif:
                                            </strong>
                                        </div>
                                        <div class="col-sm-8">
                                            {{ $translation }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
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
                        <div class="col-6">
                            <div class="text-center p-3 border rounded mb-3">
                                <h4 class="text-primary mb-0">{{ number_format($food->calories, 1) }}</h4>
                                <small class="text-muted">{{ __('Calories') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded mb-3">
                                <h4 class="text-success mb-0">{{ number_format($food->protein, 1) }}g</h4>
                                <small class="text-muted">{{ __('Protein') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded mb-3">
                                <h4 class="text-warning mb-0">{{ number_format($food->carbohydrates, 1) }}g</h4>
                                <small class="text-muted">{{ __('Carbohydrates') }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded mb-3">
                                <h4 class="text-info mb-0">{{ number_format($food->fat, 1) }}g</h4>
                                <small class="text-muted">{{ __('Fat') }}</small>
                            </div>
                        </div>
                    </div>

                    @if($food->fiber || $food->sugar || $food->sodium)
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">{{ __('Additional Nutrients') }}</h6>
                        
                        @if($food->fiber)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('Fiber') }}:</span>
                                <strong>{{ number_format($food->fiber, 1) }}g</strong>
                            </div>
                        @endif

                        @if($food->sugar)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('Sugar') }}:</span>
                                <strong>{{ number_format($food->sugar, 1) }}g</strong>
                            </div>
                        @endif

                        @if($food->sodium)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ __('Sodium') }}:</span>
                                <strong>{{ number_format($food->sodium, 1) }}mg</strong>
                            </div>
                        @endif
                    @endif

                    @if($food->serving_size)
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Typical Serving Size') }}:</span>
                            <strong>{{ $food->serving_size }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Nutrition Calculator -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator"></i> {{ __('Nutrition Calculator') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="quantity">{{ __('Quantity') }}</label>
                                <input type="number" class="form-control" id="quantity" value="100" min="1" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit">{{ __('Unit') }}</label>
                                <select class="form-control" id="unit">
                                    <option value="g">{{ __('Grams (g)') }}</option>
                                    <option value="kg">{{ __('Kilograms (kg)') }}</option>
                                    <option value="mg">{{ __('Milligrams (mg)') }}</option>
                                    <option value="cup">{{ __('Cup') }}</option>
                                    <option value="tbsp">{{ __('Tablespoon') }}</option>
                                    <option value="tsp">{{ __('Teaspoon') }}</option>
                                    <option value="serving">{{ __('Serving') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" onclick="calculateNutrition()">
                                    <i class="fas fa-calculator"></i> {{ __('Calculate') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="nutritionResults" class="mt-3" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> {{ __('Calculated Nutrition') }}</h6>
                            <div id="calculatedValues"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($food->is_custom && $food->creator)
        <!-- Creation Info -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <small class="text-muted">
                            {{ __('Created by') }}: {{ $food->creator->first_name }} {{ $food->creator->last_name }} 
                            {{ __('on') }} {{ $food->created_at->format('M d, Y \a\t g:i A') }}
                            @if($food->updated_at != $food->created_at)
                                | {{ __('Last updated') }}: {{ $food->updated_at->format('M d, Y \a\t g:i A') }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function calculateNutrition() {
    const quantity = document.getElementById('quantity').value;
    const unit = document.getElementById('unit').value;
    
    if (!quantity || quantity <= 0) {
        alert('{{ __("Please enter a valid quantity") }}');
        return;
    }

    // Show loading
    const resultsDiv = document.getElementById('nutritionResults');
    const valuesDiv = document.getElementById('calculatedValues');
    valuesDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Calculating...") }}';
    resultsDiv.style.display = 'block';

    // Make AJAX request
    fetch('{{ route("foods.calculate-nutrition", $food) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            quantity: parseFloat(quantity),
            unit: unit
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const nutrition = data.nutrition;
            valuesDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <strong>{{ __('Calories') }}:</strong> ${nutrition.calories}
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Protein') }}:</strong> ${nutrition.protein}g
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Carbs') }}:</strong> ${nutrition.carbohydrates}g
                    </div>
                    <div class="col-md-3">
                        <strong>{{ __('Fat') }}:</strong> ${nutrition.fat}g
                    </div>
                </div>
            `;
        } else {
            valuesDiv.innerHTML = '<span class="text-danger">{{ __("Error calculating nutrition") }}</span>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        valuesDiv.innerHTML = '<span class="text-danger">{{ __("Error calculating nutrition") }}</span>';
    });
}
</script>
@endpush
