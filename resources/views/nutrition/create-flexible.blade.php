@extends('layouts.app')

@section('title', __('Create Flexible Nutrition Plan'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-utensils me-2"></i>
                            {{ __('Create Flexible Nutrition Plan') }}
                        </h5>
                        <div>
                            <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('Back to Plans') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('Flexible Meal Plan') }}</strong><br>
                        {{ __('Create meal options for each meal type. Patients can choose any option throughout the week for variety and flexibility.') }}
                    </div>

                    <form method="POST" action="{{ route('nutrition.store-flexible') }}" id="flexibleNutritionForm">
                        @csrf

                        <!-- Basic Plan Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" 
                                        id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" 
                                                {{ old('patient_id', $selectedPatient?->id) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} - {{ $patient->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="title" class="form-label">{{ __('Plan Title') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="goal" class="form-label">{{ __('Goal') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('goal') is-invalid @enderror" id="goal" name="goal" required>
                                    <option value="">{{ __('Select Goal') }}</option>
                                    @foreach(\App\Models\DietPlan::GOALS as $key => $value)
                                        <option value="{{ $key }}" {{ old('goal') == $key ? 'selected' : '' }}>
                                            {{ __($value) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('goal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="target_calories" class="form-label">{{ __('Target Calories/Day') }}</label>
                                <input type="number" class="form-control @error('target_calories') is-invalid @enderror" 
                                       id="target_calories" name="target_calories" value="{{ old('target_calories') }}" 
                                       min="500" max="5000" step="50">
                                @error('target_calories')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="duration_days" class="form-label">{{ __('Duration (Days)') }}</label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                       id="duration_days" name="duration_days" value="{{ old('duration_days', 30) }}" 
                                       min="1" max="365">
                                @error('duration_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Meal Options Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-list-alt me-2"></i>
                                    {{ __('Meal Options') }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Meal Type Tabs -->
                                <ul class="nav nav-tabs" id="mealTypeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="breakfast-tab" data-bs-toggle="tab" 
                                                data-bs-target="#breakfast-options" type="button" role="tab">
                                            <i class="fas fa-coffee me-1"></i>
                                            {{ __('Breakfast Options') }}
                                            <span class="badge bg-primary ms-1" id="breakfast-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="lunch-tab" data-bs-toggle="tab" 
                                                data-bs-target="#lunch-options" type="button" role="tab">
                                            <i class="fas fa-sun me-1"></i>
                                            {{ __('Lunch Options') }}
                                            <span class="badge bg-primary ms-1" id="lunch-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="dinner-tab" data-bs-toggle="tab" 
                                                data-bs-target="#dinner-options" type="button" role="tab">
                                            <i class="fas fa-moon me-1"></i>
                                            {{ __('Dinner Options') }}
                                            <span class="badge bg-primary ms-1" id="dinner-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="snacks-tab" data-bs-toggle="tab" 
                                                data-bs-target="#snacks-options" type="button" role="tab">
                                            <i class="fas fa-cookie-bite me-1"></i>
                                            {{ __('Snack Options') }}
                                            <span class="badge bg-primary ms-1" id="snacks-count">0</span>
                                        </button>
                                    </li>
                                </ul>

                                <!-- Meal Options Content -->
                                <div class="tab-content mt-3" id="mealOptionsContent">
                                    <!-- Breakfast Options -->
                                    <div class="tab-pane fade show active" id="breakfast-options" role="tabpanel">
                                        <div class="meal-options-section" data-meal-type="breakfast">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">{{ __('Breakfast Options') }}</h6>
                                                <button type="button" class="btn btn-sm btn-success add-option-btn" 
                                                        data-meal-type="breakfast">
                                                    <i class="fas fa-plus me-1"></i>
                                                    {{ __('Add Breakfast Option') }}
                                                </button>
                                            </div>
                                            <div class="options-container" id="breakfast-options-container">
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-coffee fa-2x mb-2"></i>
                                                    <p>{{ __('No breakfast options added yet. Click "Add Breakfast Option" to start.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lunch Options -->
                                    <div class="tab-pane fade" id="lunch-options" role="tabpanel">
                                        <div class="meal-options-section" data-meal-type="lunch">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">{{ __('Lunch Options') }}</h6>
                                                <button type="button" class="btn btn-sm btn-success add-option-btn" 
                                                        data-meal-type="lunch">
                                                    <i class="fas fa-plus me-1"></i>
                                                    {{ __('Add Lunch Option') }}
                                                </button>
                                            </div>
                                            <div class="options-container" id="lunch-options-container">
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-sun fa-2x mb-2"></i>
                                                    <p>{{ __('No lunch options added yet. Click "Add Lunch Option" to start.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dinner Options -->
                                    <div class="tab-pane fade" id="dinner-options" role="tabpanel">
                                        <div class="meal-options-section" data-meal-type="dinner">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">{{ __('Dinner Options') }}</h6>
                                                <button type="button" class="btn btn-sm btn-success add-option-btn" 
                                                        data-meal-type="dinner">
                                                    <i class="fas fa-plus me-1"></i>
                                                    {{ __('Add Dinner Option') }}
                                                </button>
                                            </div>
                                            <div class="options-container" id="dinner-options-container">
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-moon fa-2x mb-2"></i>
                                                    <p>{{ __('No dinner options added yet. Click "Add Dinner Option" to start.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Snack Options -->
                                    <div class="tab-pane fade" id="snacks-options" role="tabpanel">
                                        <div class="meal-options-section" data-meal-type="snacks">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">{{ __('Snack Options') }}</h6>
                                                <button type="button" class="btn btn-sm btn-success add-option-btn" 
                                                        data-meal-type="snacks">
                                                    <i class="fas fa-plus me-1"></i>
                                                    {{ __('Add Snack Option') }}
                                                </button>
                                            </div>
                                            <div class="options-container" id="snacks-options-container">
                                                <div class="text-center text-muted py-4">
                                                    <i class="fas fa-cookie-bite fa-2x mb-2"></i>
                                                    <p>{{ __('No snack options added yet. Click "Add Snack Option" to start.') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions and Notes -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="instructions" class="form-label">{{ __('Instructions') }}</label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                          id="instructions" name="instructions" rows="4" 
                                          placeholder="{{ __('General instructions for the patient...') }}">{{ old('instructions') }}</textarea>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="restrictions" class="form-label">{{ __('Dietary Restrictions') }}</label>
                                <textarea class="form-control @error('restrictions') is-invalid @enderror" 
                                          id="restrictions" name="restrictions" rows="4" 
                                          placeholder="{{ __('Any dietary restrictions or allergies...') }}">{{ old('restrictions') }}</textarea>
                                @error('restrictions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('nutrition.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Flexible Plan') }}
                            </button>
                        </div>

                        <!-- Hidden field to store meal options data -->
                        <input type="hidden" name="meal_options" id="meal_options_data">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Food Selection Modal -->
<div class="modal fade" id="foodSelectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Food to Option') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Food Search Interface -->
                <div id="food-selection-content">
                    <!-- Search Controls -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="food-search" class="form-label">Search Foods</label>
                            <input type="text" class="form-control" id="food-search"
                                   placeholder="Type to search foods..." autocomplete="off">
                        </div>
                        <div class="col-md-3">
                            <label for="food-group-filter" class="form-label">Food Group</label>
                            <select class="form-select" id="food-group-filter">
                                <option value="">All Groups</option>
                                @foreach(\App\Models\FoodGroup::active()->ordered()->get() as $group)
                                    <option value="{{ $group->id }}">{{ $group->translated_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="food-language" class="form-label">Language</label>
                            <select class="form-select" id="food-language">
                                <option value="default">Default</option>
                                <option value="en">English</option>
                                <option value="ar">Arabic</option>
                                <option value="ku">Kurdish</option>
                            </select>
                        </div>
                    </div>

                    <!-- Food Results -->
                    <div class="row" id="food-results">
                        <div class="col-12 text-center text-muted py-4">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>Start typing to search for foods...</p>
                        </div>
                    </div>

                    <!-- Selected Food Details -->
                    <div id="selected-food-details" style="display: none;">
                        <hr>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Selected Food: <span id="selected-food-name"></span></h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="food-quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="food-quantity"
                                               value="100" min="0.1" step="0.1">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="food-unit" class="form-label">Unit</label>
                                        <select class="form-select" id="food-unit">
                                            <option value="g">grams (g)</option>
                                            <option value="kg">kilograms (kg)</option>
                                            <option value="mg">milligrams (mg)</option>
                                            <option value="cup">cup</option>
                                            <option value="tbsp">tablespoon</option>
                                            <option value="tsp">teaspoon</option>
                                            <option value="serving">serving</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="food-notes" class="form-label">Notes (Optional)</label>
                                        <input type="text" class="form-control" id="food-notes"
                                               placeholder="e.g., cooked, raw">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <h6>Nutrition Preview</h6>
                                <div id="nutrition-preview">
                                    <small class="text-muted">
                                        <div>Calories: <span id="preview-calories">0</span></div>
                                        <div>Protein: <span id="preview-protein">0</span>g</div>
                                        <div>Carbs: <span id="preview-carbs">0</span>g</div>
                                        <div>Fat: <span id="preview-fat">0</span>g</div>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="add-food-to-option" disabled>
                                <i class="fas fa-plus me-1"></i>
                                Add Food to Option
                            </button>
                            <button type="button" class="btn btn-secondary" id="clear-selection">
                                Clear Selection
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.option-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: all 0.2s ease;
}

.option-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.option-header {
    background-color: #f8f9fa;
    padding: 12px 15px;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
}

.option-body {
    padding: 15px;
}

.food-item {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 8px;
    display: flex;
    justify-content: between;
    align-items: center;
}

.food-item:last-child {
    margin-bottom: 0;
}

.food-info {
    flex: 1;
}

.food-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 2px;
}

.food-details {
    font-size: 0.85em;
    color: #6c757d;
}

.food-actions {
    margin-left: 10px;
}

.option-summary {
    background-color: #e3f2fd;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 0.9em;
    color: #1976d2;
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 30px;
    color: #6c757d;
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}

/* Food Selection Modal Styles */
.food-card {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
    cursor: pointer;
}

.food-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.food-card.border-primary {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.nutrition-info {
    font-size: 0.8rem;
}

#selected-food-details {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
}

#nutrition-preview {
    background-color: #e3f2fd;
    padding: 10px;
    border-radius: 6px;
    border-left: 4px solid #2196f3;
}

#foodSelectionModal .food-item {
    background-color: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #20B2AA;
    margin-bottom: 8px;
}
</style>
@endpush

@push('scripts')
<script>
// Global variables for flexible meal options
let mealOptions = {
    breakfast: [],
    lunch: [],
    dinner: [],
    snacks: []
};

let currentMealType = '';
let currentOptionIndex = -1;
let optionCounters = {
    breakfast: 0,
    lunch: 0,
    dinner: 0,
    snacks: 0
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeFlexibleMealPlanning();

    // Initialize food search when modal is shown
    const foodModal = document.getElementById('foodSelectionModal');
    if (foodModal) {
        foodModal.addEventListener('shown.bs.modal', function() {
            if (!document.getElementById('food-search').hasAttribute('data-initialized')) {
                initializeFoodSearch();
                document.getElementById('food-search').setAttribute('data-initialized', 'true');
            }
        });

        // Clear selection when modal is hidden
        foodModal.addEventListener('hidden.bs.modal', function() {
            clearFoodSelection();
        });
    }
});

function initializeFlexibleMealPlanning() {
    // Add event listeners for adding new options
    document.querySelectorAll('.add-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mealType = this.dataset.mealType;
            addNewMealOption(mealType);
        });
    });

    // Initialize food search functionality
    initializeFoodSearch();
}

function addNewMealOption(mealType) {
    optionCounters[mealType]++;
    const optionNumber = optionCounters[mealType];

    // Create new option object
    const newOption = {
        option_number: optionNumber,
        meal_type: mealType,
        option_description: `Option ${optionNumber}`,
        foods: [],
        total_calories: 0,
        total_protein: 0,
        total_carbs: 0,
        total_fat: 0
    };

    // Add to meal options
    mealOptions[mealType].push(newOption);

    // Render the option card
    renderMealOption(mealType, newOption, mealOptions[mealType].length - 1);

    // Update counter badge
    updateOptionCounter(mealType);

    // Remove empty state if it exists
    const container = document.getElementById(`${mealType}-options-container`);
    const emptyState = container.querySelector('.text-center.text-muted');
    if (emptyState) {
        emptyState.remove();
    }
}

function renderMealOption(mealType, option, optionIndex) {
    const container = document.getElementById(`${mealType}-options-container`);

    const optionCard = document.createElement('div');
    optionCard.className = 'option-card';
    optionCard.dataset.mealType = mealType;
    optionCard.dataset.optionIndex = optionIndex;

    optionCard.innerHTML = `
        <div class="option-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">${option.option_description}</h6>
                    <small class="text-muted">${getMealTypeDisplayName(mealType)}</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary add-food-to-option-btn"
                            data-meal-type="${mealType}" data-option-index="${optionIndex}">
                        <i class="fas fa-plus me-1"></i>
                        Add Food
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary edit-option-btn"
                            data-meal-type="${mealType}" data-option-index="${optionIndex}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn"
                            data-meal-type="${mealType}" data-option-index="${optionIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="option-body">
            <div class="foods-list" id="${mealType}-option-${optionIndex}-foods">
                <div class="empty-state">
                    <i class="fas fa-utensils"></i>
                    <p>No foods added yet. Click "Add Food" to start building this option.</p>
                </div>
            </div>
            <div class="option-summary mt-3" id="${mealType}-option-${optionIndex}-summary">
                Total: 0 calories | 0g protein | 0g carbs | 0g fat
            </div>
        </div>
    `;

    container.appendChild(optionCard);

    // Add event listeners
    optionCard.querySelector('.add-food-to-option-btn').addEventListener('click', function() {
        openFoodSelectionModal(mealType, optionIndex);
    });

    optionCard.querySelector('.edit-option-btn').addEventListener('click', function() {
        editOptionDescription(mealType, optionIndex);
    });

    optionCard.querySelector('.remove-option-btn').addEventListener('click', function() {
        removeMealOption(mealType, optionIndex);
    });
}

function getMealTypeDisplayName(mealType) {
    const names = {
        breakfast: 'Breakfast',
        lunch: 'Lunch',
        dinner: 'Dinner',
        snacks: 'Snacks'
    };
    return names[mealType] || mealType;
}

function updateOptionCounter(mealType) {
    const badge = document.getElementById(`${mealType}-count`);
    badge.textContent = mealOptions[mealType].length;
}

function openFoodSelectionModal(mealType, optionIndex) {
    console.log('Opening food selection modal for:', mealType, optionIndex);
    currentMealType = mealType;
    currentOptionIndex = optionIndex;

    // Clear previous selection
    clearFoodSelection();

    // Initialize food search if not already done
    const foodSearchInput = document.getElementById('food-search');
    if (foodSearchInput && !foodSearchInput.hasAttribute('data-initialized')) {
        console.log('Initializing food search...');
        initializeFoodSearch();
        foodSearchInput.setAttribute('data-initialized', 'true');
    }

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('foodSelectionModal'));
    modal.show();
}

// Search for foods
function searchFoods() {
    const search = document.getElementById('food-search').value;
    const groupId = document.getElementById('food-group-filter').value;
    const language = document.getElementById('food-language').value;

    if (search.length < 2 && !groupId) {
        document.getElementById('food-results').innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>Start typing to search for foods...</p>
            </div>
        `;
        return;
    }

    // Show loading
    document.getElementById('food-results').innerHTML = `
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Searching for foods...</p>
        </div>
    `;

    // Build search parameters
    const params = new URLSearchParams({
        search: search,
        food_group_id: groupId,
        language: language,
        limit: 20
    });

    // Make AJAX request
    fetch(`{{ route('foods.search') }}?${params}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        displayFoodResults(data.foods || []);
    })
    .catch(error => {
        console.error('Error searching foods:', error);
        document.getElementById('food-results').innerHTML = `
            <div class="col-12 text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p>Error loading foods. Please try again.</p>
                <small class="text-muted">Error: ${error.message}</small>
            </div>
        `;
    });
}

function editOptionDescription(mealType, optionIndex) {
    const option = mealOptions[mealType][optionIndex];
    const newDescription = prompt('Enter option description:', option.option_description);

    if (newDescription && newDescription.trim()) {
        option.option_description = newDescription.trim();

        // Update the display
        const optionCard = document.querySelector(`[data-meal-type="${mealType}"][data-option-index="${optionIndex}"]`);
        const titleElement = optionCard.querySelector('.option-header h6');
        titleElement.textContent = option.option_description;
    }
}

function removeMealOption(mealType, optionIndex) {
    if (confirm('Are you sure you want to remove this meal option?')) {
        // Remove from array
        mealOptions[mealType].splice(optionIndex, 1);

        // Re-render all options for this meal type
        renderAllMealOptions(mealType);

        // Update counter
        updateOptionCounter(mealType);
    }
}

function renderAllMealOptions(mealType) {
    const container = document.getElementById(`${mealType}-options-container`);
    container.innerHTML = '';

    if (mealOptions[mealType].length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-${getMealTypeIcon(mealType)} fa-2x mb-2"></i>
                <p>No ${mealType} options added yet. Click "Add ${getMealTypeDisplayName(mealType)} Option" to start.</p>
            </div>
        `;
    } else {
        mealOptions[mealType].forEach((option, index) => {
            renderMealOption(mealType, option, index);
        });
    }
}

function getMealTypeIcon(mealType) {
    const icons = {
        breakfast: 'coffee',
        lunch: 'sun',
        dinner: 'moon',
        snacks: 'cookie-bite'
    };
    return icons[mealType] || 'utensils';
}

// Food selection functionality
let selectedFood = null;
let searchTimeout = null;

function initializeFoodSearch() {
    // Initialize search functionality
    const foodSearch = document.getElementById('food-search');
    const foodGroupFilter = document.getElementById('food-group-filter');
    const foodLanguage = document.getElementById('food-language');
    const foodQuantity = document.getElementById('food-quantity');
    const foodUnit = document.getElementById('food-unit');
    const addFoodBtn = document.getElementById('add-food-to-option');
    const clearSelectionBtn = document.getElementById('clear-selection');

    // Search event listeners
    foodSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchFoods();
        }, 300);
    });

    foodGroupFilter.addEventListener('change', searchFoods);
    foodLanguage.addEventListener('change', searchFoods);

    // Quantity/unit change listeners
    foodQuantity.addEventListener('input', updateNutritionPreview);
    foodUnit.addEventListener('change', updateNutritionPreview);

    // Button listeners
    addFoodBtn.addEventListener('click', addFoodToOption);
    clearSelectionBtn.addEventListener('click', clearFoodSelection);

    console.log('Food search initialized');
}

// Display food search results
function displayFoodResults(foods) {
    const resultsContainer = document.getElementById('food-results');

    if (foods.length === 0) {
        resultsContainer.innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>No foods found. Try a different search term.</p>
            </div>
        `;
        return;
    }

    let html = '';
    foods.forEach(food => {
        const displayName = food.translated_name || food.name;
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card food-card h-100" style="cursor: pointer;"
                     onclick="selectFood(${food.id}, '${food.name}', '${displayName}', ${food.calories}, ${food.protein}, ${food.carbohydrates}, ${food.fat})">
                    <div class="card-body p-3">
                        <h6 class="card-title mb-2">${displayName}</h6>
                        <small class="text-muted d-block mb-2">${food.group || 'No Group'}</small>
                        <div class="nutrition-info">
                            <small class="text-muted">
                                <div>Calories: ${food.calories}/100g</div>
                                <div>Protein: ${food.protein}g</div>
                                <div>Carbs: ${food.carbohydrates}g</div>
                                <div>Fat: ${food.fat}g</div>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
}

// Select a food item
function selectFood(id, originalName, displayName, calories, protein, carbs, fat) {
    selectedFood = {
        id: id,
        name: originalName,
        displayName: displayName,
        calories: calories,
        protein: protein,
        carbs: carbs,
        fat: fat
    };

    // Update selected food details
    document.getElementById('selected-food-name').textContent = displayName;
    document.getElementById('selected-food-details').style.display = 'block';
    document.getElementById('add-food-to-option').disabled = false;

    // Highlight selected food
    document.querySelectorAll('.food-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    event.currentTarget.classList.add('border-primary');

    // Update nutrition preview
    updateNutritionPreview();
}

// Update nutrition preview based on quantity and unit
function updateNutritionPreview() {
    if (!selectedFood) return;

    const quantity = parseFloat(document.getElementById('food-quantity').value) || 0;
    const unit = document.getElementById('food-unit').value;

    // Convert to grams for calculation
    let multiplier = quantity / 100; // Default for grams

    switch(unit) {
        case 'kg':
            multiplier = (quantity * 1000) / 100;
            break;
        case 'mg':
            multiplier = (quantity / 1000) / 100;
            break;
        case 'cup':
            multiplier = (quantity * 240) / 100; // Approximate
            break;
        case 'tbsp':
            multiplier = (quantity * 15) / 100;
            break;
        case 'tsp':
            multiplier = (quantity * 5) / 100;
            break;
        case 'serving':
            multiplier = quantity / 100; // Assume 100g serving
            break;
    }

    const calories = Math.round(selectedFood.calories * multiplier);
    const protein = Math.round(selectedFood.protein * multiplier * 10) / 10;
    const carbs = Math.round(selectedFood.carbs * multiplier * 10) / 10;
    const fat = Math.round(selectedFood.fat * multiplier * 10) / 10;

    document.getElementById('preview-calories').textContent = calories;
    document.getElementById('preview-protein').textContent = protein;
    document.getElementById('preview-carbs').textContent = carbs;
    document.getElementById('preview-fat').textContent = fat;
}

// Add selected food to current option
function addFoodToOption() {
    if (!selectedFood || !currentMealType || currentOptionIndex === null) {
        alert('Please select a food and ensure an option is selected.');
        return;
    }

    const quantity = parseFloat(document.getElementById('food-quantity').value);
    const unit = document.getElementById('food-unit').value;
    const notes = document.getElementById('food-notes').value;

    if (!quantity || quantity <= 0) {
        alert('Please enter a valid quantity.');
        return;
    }

    // Calculate nutrition values
    let multiplier = quantity / 100; // Default for grams

    switch(unit) {
        case 'kg':
            multiplier = (quantity * 1000) / 100;
            break;
        case 'mg':
            multiplier = (quantity / 1000) / 100;
            break;
        case 'cup':
            multiplier = (quantity * 240) / 100;
            break;
        case 'tbsp':
            multiplier = (quantity * 15) / 100;
            break;
        case 'tsp':
            multiplier = (quantity * 5) / 100;
            break;
        case 'serving':
            multiplier = quantity / 100;
            break;
    }

    const foodItem = {
        id: selectedFood.id,
        name: selectedFood.name,
        displayName: selectedFood.displayName,
        quantity: quantity,
        unit: unit,
        notes: notes,
        calories: Math.round(selectedFood.calories * multiplier),
        protein: Math.round(selectedFood.protein * multiplier * 10) / 10,
        carbs: Math.round(selectedFood.carbs * multiplier * 10) / 10,
        fat: Math.round(selectedFood.fat * multiplier * 10) / 10
    };

    // Add to meal option
    mealOptions[currentMealType][currentOptionIndex].foods.push(foodItem);

    // Update option display
    updateOptionDisplay(currentMealType, currentOptionIndex);

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('foodSelectionModal'));
    modal.hide();

    // Clear selection
    clearFoodSelection();
}

// Clear food selection
function clearFoodSelection() {
    selectedFood = null;
    const selectedDetails = document.getElementById('selected-food-details');
    const addButton = document.getElementById('add-food-to-option');
    const searchInput = document.getElementById('food-search');
    const quantityInput = document.getElementById('food-quantity');
    const unitSelect = document.getElementById('food-unit');
    const notesInput = document.getElementById('food-notes');

    if (selectedDetails) selectedDetails.style.display = 'none';
    if (addButton) addButton.disabled = true;
    if (searchInput) searchInput.value = '';
    if (quantityInput) quantityInput.value = '100';
    if (unitSelect) unitSelect.value = 'g';
    if (notesInput) notesInput.value = '';

    // Clear highlights
    document.querySelectorAll('.food-card').forEach(card => {
        card.classList.remove('border-primary');
    });

    // Reset results
    const resultsContainer = document.getElementById('food-results');
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>Start typing to search for foods...</p>
            </div>
        `;
    }
}

// Update option display after adding food
function updateOptionDisplay(mealType, optionIndex) {
    const option = mealOptions[mealType][optionIndex];
    const foodsContainer = document.getElementById(`${mealType}-option-${optionIndex}-foods`);
    const summaryContainer = document.getElementById(`${mealType}-option-${optionIndex}-summary`);

    if (option.foods.length === 0) {
        foodsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-utensils"></i>
                <p>No foods added yet. Click "Add Food" to start building this option.</p>
            </div>
        `;
        summaryContainer.textContent = 'Total: 0 calories | 0g protein | 0g carbs | 0g fat';
        return;
    }

    // Display foods
    let foodsHtml = '';
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

    option.foods.forEach((food, foodIndex) => {
        totalCalories += food.calories;
        totalProtein += food.protein;
        totalCarbs += food.carbs;
        totalFat += food.fat;

        foodsHtml += `
            <div class="food-item d-flex justify-content-between align-items-center mb-2">
                <div>
                    <strong>${food.displayName}</strong>
                    <small class="text-muted d-block">${food.quantity}${food.unit} | ${food.calories} cal</small>
                    ${food.notes ? `<small class="text-info">${food.notes}</small>` : ''}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="removeFoodFromOption('${mealType}', ${optionIndex}, ${foodIndex})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
    });

    foodsContainer.innerHTML = foodsHtml;

    // Update summary
    summaryContainer.textContent = `Total: ${Math.round(totalCalories)} calories | ${totalProtein.toFixed(1)}g protein | ${totalCarbs.toFixed(1)}g carbs | ${totalFat.toFixed(1)}g fat`;

    // Update option totals
    option.total_calories = totalCalories;
    option.total_protein = totalProtein;
    option.total_carbs = totalCarbs;
    option.total_fat = totalFat;
}

// Remove food from option
function removeFoodFromOption(mealType, optionIndex, foodIndex) {
    if (confirm('Are you sure you want to remove this food?')) {
        mealOptions[mealType][optionIndex].foods.splice(foodIndex, 1);
        updateOptionDisplay(mealType, optionIndex);
    }
}



// Form submission
document.getElementById('flexibleNutritionForm').addEventListener('submit', function(e) {
    // Prepare meal options data for submission
    document.getElementById('meal_options_data').value = JSON.stringify(mealOptions);
});
</script>
@endpush
