@extends('layouts.app')

@section('page-title', isset($dietPlan) ? __('Edit Detailed Nutrition Plan') : __('Create Detailed Nutrition Plan'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-{{ isset($dietPlan) ? 'edit' : 'plus-circle' }} text-{{ isset($dietPlan) ? 'warning' : 'success' }}"></i>
                        {{ isset($dietPlan) ? __('Edit Detailed Nutrition Plan') : __('Create Detailed Nutrition Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Design a comprehensive nutrition plan with specific foods and caloric distribution') }}</p>
                </div>
                <div>
                    <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Plans') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ isset($dietPlan) ? route('nutrition.update', $dietPlan) : route('nutrition.store-flexible') }}" method="POST" id="nutrition-form">
        @csrf
        @if(isset($dietPlan))
            @method('PUT')
        @endif
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Basic Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" 
                                            {{ (old('patient_id', $selectedPatient?->id) == $patient->id) ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->patient_id }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">{{ __('Plan Title') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title', $dietPlan?->title ?? '') }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description', $dietPlan?->description ?? '') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="goal" class="form-label">{{ __('Goal') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('goal') is-invalid @enderror" id="goal" name="goal" required onchange="updateCalorieCalculation()">
                                    <option value="">{{ __('Select Goal') }}</option>
                                    <option value="weight_loss" {{ old('goal', $dietPlan?->goal ?? '') == 'weight_loss' ? 'selected' : '' }}>{{ __('Weight Loss') }}</option>
                                    <option value="weight_gain" {{ old('goal', $dietPlan?->goal ?? '') == 'weight_gain' ? 'selected' : '' }}>{{ __('Weight Gain') }}</option>
                                    <option value="muscle_gain" {{ old('goal', $dietPlan?->goal ?? '') == 'muscle_gain' ? 'selected' : '' }}>{{ __('Muscle Gain') }}</option>
                                    <option value="maintenance" {{ old('goal') == 'maintenance' ? 'selected' : '' }}>{{ __('Maintenance') }}</option>
                                    <option value="diabetic" {{ old('goal') == 'diabetic' ? 'selected' : '' }}>{{ __('Diabetic Management') }}</option>
                                    <option value="other" {{ old('goal') == 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">{{ __('Duration (Days)') }}</label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                       id="duration_days" name="duration_days" value="{{ old('duration_days', 30) }}"
                                       min="1" max="365">
                                @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Activity Level -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="activity_level" class="form-label">{{ __('Activity Level') }} <span class="text-danger">*</span></label>
                                <select class="form-select" id="activity_level" name="activity_level" required onchange="updateCalorieCalculation()">
                                    <option value="sedentary">{{ __('Sedentary (little/no exercise)') }}</option>
                                    <option value="light" selected>{{ __('Light (light exercise 1-3 days/week)') }}</option>
                                    <option value="moderate">{{ __('Moderate (moderate exercise 3-5 days/week)') }}</option>
                                    <option value="active">{{ __('Active (hard exercise 6-7 days/week)') }}</option>
                                    <option value="very_active">{{ __('Very Active (very hard exercise, physical job)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_weight" class="form-label">{{ __('Target Weight (kg)') }}</label>
                                <input type="number" class="form-control" id="target_weight" name="target_weight_quick"
                                       min="30" max="300" step="0.1"
                                       onchange="syncTargetWeights(this); updateCalorieCalculation()"
                                       oninput="syncTargetWeights(this); debounceCalorieCalculation()"
                                       placeholder="{{ __('Optional: for time estimation') }}">
                                <small class="text-muted">{{ __('Leave empty if not applicable') }}</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                       id="end_date" name="end_date" value="{{ old('end_date', $dietPlan?->end_date?->format('Y-m-d') ?? '') }}">
                                @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Daily Nutritional Targets Section -->
                        <hr class="my-4">
                        <h6 class="mb-3">
                            <i class="fas fa-bullseye text-primary me-2"></i>
                            {{ __('Daily Nutritional Targets') }}
                        </h6>

                        <!-- First Row: Calories and Protein -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_calories" class="form-label">
                                    {{ __('Calories') }}
                                    <small class="text-muted">({{ __('Auto-calculated') }})</small>
                                </label>
                                <div class="input-group">
                                    <input type="number" class="form-control bg-light @error('target_calories') is-invalid @enderror"
                                           id="target_calories" name="target_calories" value="{{ old('target_calories', $dietPlan?->target_calories ?? 2000) }}"
                                           min="800" max="4000" step="1" readonly style="font-weight: bold; color: #0d6efd;">
                                    <span class="input-group-text">
                                        <i class="fas fa-calculator text-primary" title="{{ __('Auto-calculated from macronutrients') }}"></i>
                                    </span>
                                </div>
                                <small class="form-text text-muted">
                                    {{ __('Calculated from: Protein (4 cal/g) + Carbs (4 cal/g) + Fat (9 cal/g)') }}
                                </small>
                                @error('target_calories')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_protein" class="form-label">{{ __('Protein (g)') }}</label>
                                <input type="number" class="form-control @error('target_protein') is-invalid @enderror"
                                       id="target_protein" name="target_protein" value="{{ old('target_protein', $dietPlan?->target_protein ?? 150) }}"
                                       min="0" max="500" step="any">
                                @error('target_protein')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Second Row: Carbohydrates and Fat -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_carbs" class="form-label">{{ __('Carbohydrates (g)') }}</label>
                                <input type="number" class="form-control @error('target_carbs') is-invalid @enderror"
                                       id="target_carbs" name="target_carbs" value="{{ old('target_carbs', $dietPlan?->target_carbs ?? 250) }}"
                                       min="0" max="1000" step="any">
                                @error('target_carbs')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_fat" class="form-label">{{ __('Fat (g)') }}</label>
                                <input type="number" class="form-control @error('target_fat') is-invalid @enderror"
                                       id="target_fat" name="target_fat" value="{{ old('target_fat', $dietPlan?->target_fat ?? 65) }}"
                                       min="0" max="300" step="any">
                                @error('target_fat')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('These targets will be used to calculate progress as you add foods to meals.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Weight Management & BMI Tracking -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-weight text-primary"></i>
                            {{ __('Weight Management & BMI Tracking') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- First Row: Current Weight and Target Weight -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="initial_weight" class="form-label">{{ __('Current Weight (kg)') }}</label>
                                <input type="number" class="form-control @error('initial_weight') is-invalid @enderror"
                                       id="initial_weight" name="initial_weight"
                                       value="{{ old('initial_weight', $dietPlan?->initial_weight ?? ($selectedPatient?->weight ?? '')) }}"
                                       min="20" max="500" step="0.1" placeholder="70.5"
                                       oninput="updateBMIDisplay()" onchange="updateBMIDisplay()">
                                @error('initial_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_weight_goal" class="form-label">{{ __('Target Weight (kg)') }}</label>
                                <input type="number" class="form-control @error('target_weight') is-invalid @enderror"
                                       id="target_weight_goal" name="target_weight"
                                       value="{{ old('target_weight', $dietPlan?->target_weight ?? '') }}"
                                       min="20" max="500" step="0.1" placeholder="65.0"
                                       onchange="syncTargetWeights(this); updateCalorieCalculation(); updateBMIDisplay()"
                                       oninput="syncTargetWeights(this); debounceCalorieCalculation(); updateBMIDisplay()">
                                @error('target_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Second Row: Height and Weekly Goal -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="initial_height" class="form-label">{{ __('Height (cm)') }}</label>
                                <input type="number" class="form-control @error('initial_height') is-invalid @enderror"
                                       id="initial_height" name="initial_height"
                                       value="{{ old('initial_height', $dietPlan?->initial_height ?? ($selectedPatient?->height ?? '')) }}"
                                       min="100" max="250" step="0.1" placeholder="170.0"
                                       oninput="updateBMIDisplay()" onchange="updateBMIDisplay()">
                                @error('initial_height')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="weekly_weight_goal" class="form-label">{{ __('Weekly Weight Goal (kg)') }}</label>
                                <select class="form-select @error('weekly_weight_goal') is-invalid @enderror"
                                        id="weekly_weight_goal" name="weekly_weight_goal" onchange="updateCalorieCalculation(); updateBMIDisplay()">
                                    <option value="">{{ __('Select Weekly Goal') }}</option>
                                    <option value="-1.0" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '-1.0' ? 'selected' : '' }}>{{ __('Lose 1.0 kg/week') }}</option>
                                    <option value="-0.75" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '-0.75' ? 'selected' : '' }}>{{ __('Lose 0.75 kg/week') }}</option>
                                    <option value="-0.5" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '-0.5' ? 'selected' : '' }}>{{ __('Lose 0.5 kg/week (Recommended)') }}</option>
                                    <option value="-0.25" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '-0.25' ? 'selected' : '' }}>{{ __('Lose 0.25 kg/week') }}</option>
                                    <option value="0" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '0' ? 'selected' : '' }}>{{ __('Maintain Weight') }}</option>
                                    <option value="0.25" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '0.25' ? 'selected' : '' }}>{{ __('Gain 0.25 kg/week') }}</option>
                                    <option value="0.5" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '0.5' ? 'selected' : '' }}>{{ __('Gain 0.5 kg/week (Recommended)') }}</option>
                                    <option value="0.75" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '0.75' ? 'selected' : '' }}>{{ __('Gain 0.75 kg/week') }}</option>
                                    <option value="1.0" {{ old('weekly_weight_goal', $dietPlan?->weekly_weight_goal ?? '') == '1.0' ? 'selected' : '' }}>{{ __('Gain 1.0 kg/week') }}</option>
                                </select>
                                @error('weekly_weight_goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- BMI Display -->
                        <div class="row" id="bmi-display" style="display: block;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <strong>{{ __('Current BMI') }}</strong><br>
                                            <span id="current-bmi" class="h5 text-primary">--</span><br>
                                            <small id="current-bmi-category" class="text-muted">--</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{ __('Target BMI') }}</strong><br>
                                            <span id="target-bmi" class="h5 text-success">--</span><br>
                                            <small id="target-bmi-category" class="text-muted">--</small>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>{{ __('Weight to Goal') }}</strong><br>
                                            <span id="weight-to-goal" class="h5 text-warning">--</span><br>
                                            <small id="estimated-time" class="text-muted">{{ __('Set weekly goal for estimate') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('Weight and height data will be used to calculate BMI and track progress throughout the nutrition plan.') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Meal Planning Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-utensils me-2"></i>
                            {{ __('Daily Meal Plan') }}
                        </h5>
                        <small class="text-muted">{{ __('Plan your daily meals with specific foods and portions') }}</small>
                    </div>
                    <div class="card-body">

                        <!-- Meal Types Tabs -->
                        <ul class="nav nav-tabs" id="mealTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="breakfast-tab" data-bs-toggle="tab" data-bs-target="#breakfast" type="button" role="tab">
                                    <i class="fas fa-coffee me-1"></i>
                                    {{ __('Breakfast') }}
                                    <span class="badge bg-primary ms-1" id="breakfast-calories">0 cal</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="lunch-tab" data-bs-toggle="tab" data-bs-target="#lunch" type="button" role="tab">
                                    <i class="fas fa-sun me-1"></i>
                                    {{ __('Lunch') }}
                                    <span class="badge bg-primary ms-1" id="lunch-calories">0 cal</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="dinner-tab" data-bs-toggle="tab" data-bs-target="#dinner" type="button" role="tab">
                                    <i class="fas fa-moon me-1"></i>
                                    {{ __('Dinner') }}
                                    <span class="badge bg-primary ms-1" id="dinner-calories">0 cal</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="snacks-tab" data-bs-toggle="tab" data-bs-target="#snacks" type="button" role="tab">
                                    <i class="fas fa-cookie-bite me-1"></i>
                                    {{ __('Snacks') }}
                                    <span class="badge bg-primary ms-1" id="snacks-calories">0 cal</span>
                                </button>
                            </li>
                        </ul>

                        <!-- Meal Content -->
                        <div class="tab-content mt-3" id="mealTabContent">
                            <!-- Breakfast -->
                            <div class="tab-pane fade show active" id="breakfast" role="tabpanel">
                                <div class="meal-section" data-meal="breakfast">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ __('Breakfast Options') }}</h6>
                                        <button type="button" class="btn btn-sm btn-success add-option-btn" data-meal="breakfast">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add Breakfast Option') }}
                                        </button>
                                    </div>
                                    <div class="options-container" id="breakfast-options">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-coffee fa-2x mb-2"></i>
                                            <p>{{ __('No breakfast options added yet. Click "Add Breakfast Option" to start.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lunch -->
                            <div class="tab-pane fade" id="lunch" role="tabpanel">
                                <div class="meal-section" data-meal="lunch">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ __('Lunch Options') }}</h6>
                                        <button type="button" class="btn btn-sm btn-success add-option-btn" data-meal="lunch">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add Lunch Option') }}
                                        </button>
                                    </div>
                                    <div class="options-container" id="lunch-options">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-sun fa-2x mb-2"></i>
                                            <p>{{ __('No lunch options added yet. Click "Add Lunch Option" to start.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dinner -->
                            <div class="tab-pane fade" id="dinner" role="tabpanel">
                                <div class="meal-section" data-meal="dinner">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ __('Dinner Options') }}</h6>
                                        <button type="button" class="btn btn-sm btn-success add-option-btn" data-meal="dinner">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add Dinner Option') }}
                                        </button>
                                    </div>
                                    <div class="options-container" id="dinner-options">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-moon fa-2x mb-2"></i>
                                            <p>{{ __('No dinner options added yet. Click "Add Dinner Option" to start.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Snacks -->
                            <div class="tab-pane fade" id="snacks" role="tabpanel">
                                <div class="meal-section" data-meal="snacks">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">{{ __('Snack Options') }}</h6>
                                        <button type="button" class="btn btn-sm btn-success add-option-btn" data-meal="snacks">
                                            <i class="fas fa-plus me-1"></i>
                                            {{ __('Add Snack Option') }}
                                        </button>
                                    </div>
                                    <div class="options-container" id="snacks-options">
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-cookie-bite fa-2x mb-2"></i>
                                            <p>{{ __('No snack options added yet. Click "Add Snack Option" to start.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Daily Nutrition Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Daily Nutrition Summary') }}</h6>
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-primary mb-1" id="total-calories">0</div>
                                                    <small class="text-muted">{{ __('Calories') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar" id="calories-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="calories-target">Target: 2000</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-success mb-1" id="total-protein">0g</div>
                                                    <small class="text-muted">{{ __('Protein') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" id="protein-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="protein-target">Target: 150g</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-warning mb-1" id="total-carbs">0g</div>
                                                    <small class="text-muted">{{ __('Carbs') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-warning" id="carbs-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="carbs-target">Target: 250g</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-danger mb-1" id="total-fat">0g</div>
                                                    <small class="text-muted">{{ __('Fat') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-danger" id="fat-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="fat-target">Target: 65g</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions and Notes -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list"></i>
                            {{ __('Instructions & Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="instructions" class="form-label">{{ __('Instructions') }}</label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                          id="instructions" name="instructions" rows="4"
                                          placeholder="{{ __('General instructions for following this nutrition plan...') }}">{{ old('instructions') }}</textarea>
                                @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="restrictions" class="form-label">{{ __('Dietary Restrictions') }}</label>
                                <textarea class="form-control @error('restrictions') is-invalid @enderror" 
                                          id="restrictions" name="restrictions" rows="4"
                                          placeholder="{{ __('Foods to avoid, allergies, medical restrictions...') }}">{{ old('restrictions') }}</textarea>
                                @error('restrictions')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($dietPlan))
        <!-- Plan Status (Only for editing) -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-primary"></i>
                            {{ __('Plan Status') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                @foreach(\App\Models\DietPlan::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $dietPlan->status ?? 'active') == $key ? 'selected' : '' }}>
                                    {{ __($label) }}
                                </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        {{ isset($dietPlan) ? __('Update Nutrition Plan') : __('Create Nutrition Plan') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Food Selection Modal -->
<div class="modal fade" id="foodSelectionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Food Item') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Language and Search Row -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="food-language" class="form-label">{{ __('Display Language') }}</label>
                        <select class="form-select" id="food-language">
                            <option value="default">{{ __('Default') }}</option>
                            <option value="en">{{ __('English') }}</option>
                            <option value="ar">{{ __('العربية') }}</option>
                            <option value="ku_bahdini">{{ __('کوردی بادینی') }}</option>
                            <option value="ku_sorani">{{ __('کوردی سۆرانی') }}</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label for="food-search" class="form-label">{{ __('Search Foods') }}</label>
                        <input type="text" class="form-control" id="food-search" placeholder="{{ __('Type to search foods...') }}">
                    </div>
                </div>

                <!-- Food Groups Filter -->
                <div class="mb-3">
                    <label for="food-group-filter" class="form-label">{{ __('Food Group') }}</label>
                    <select class="form-select" id="food-group-filter">
                        <option value="">{{ __('All Groups') }}</option>
                        @if(isset($foodGroups))
                        @foreach($foodGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->translated_name }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <!-- Food Results -->
                <div id="food-results" class="row">
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>{{ __('Start typing to search for foods...') }}</p>
                    </div>
                </div>

                <!-- Selected Food Details -->
                <div id="selected-food-details" class="mt-3" style="display: none;">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title text-primary" id="selected-food-name"></h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="food-quantity" class="form-label">{{ __('Quantity') }}</label>
                                        <input type="number" class="form-control" id="food-quantity" value="100" min="1" step="0.1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="food-unit" class="form-label">{{ __('Unit') }}</label>
                                        <select class="form-select" id="food-unit">
                                            <option value="g">{{ __('grams') }}</option>
                                            <option value="cup">{{ __('cup') }}</option>
                                            <option value="piece">{{ __('piece') }}</option>
                                            <option value="slice">{{ __('slice') }}</option>
                                            <option value="tbsp">{{ __('tablespoon') }}</option>
                                            <option value="tsp">{{ __('teaspoon') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nutrition-preview">
                                        <h6>{{ __('Nutrition per serving:') }}</h6>
                                        <div class="row text-center">
                                            <div class="col-6 mb-2">
                                                <div class="text-primary h6" id="preview-calories">0</div>
                                                <small>{{ __('Calories') }}</small>
                                            </div>
                                            <div class="col-6 mb-2">
                                                <div class="text-success h6" id="preview-protein">0g</div>
                                                <small>{{ __('Protein') }}</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-warning h6" id="preview-carbs">0g</div>
                                                <small>{{ __('Carbs') }}</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-danger h6" id="preview-fat">0g</div>
                                                <small>{{ __('Fat') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="preparation-notes" class="form-label">{{ __('Preparation Notes') }}</label>
                                <textarea class="form-control" id="preparation-notes" rows="2" placeholder="{{ __('Cooking method, seasoning, etc...') }}"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                <button type="button" class="btn btn-primary" id="add-food-to-meal" disabled>{{ __('Add to Meal') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global variables
let currentMeal = '';
let currentOption = 0;
let selectedFood = null;
let mealOptions = {
    breakfast: [],
    lunch: [],
    dinner: [],
    snacks: []
};
let optionCounters = {
    breakfast: 0,
    lunch: 0,
    dinner: 0,
    snacks: 0
};



// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMealPlanning();
    calculateTotalCalories(); // Calculate calories on page load
    updateNutritionTargets();

    // Remove step validation from macronutrient fields
    ['target_protein', 'target_carbs', 'target_fat'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.removeAttribute('step');
            field.setAttribute('step', 'any');
            // Override browser validation
            field.addEventListener('invalid', function(e) {
                e.preventDefault();
                this.setCustomValidity('');
            });
            field.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }
    });

    // Initialize calorie calculation on page load
    updateCalorieCalculation();

    // Add event listeners for real-time calorie calculation
    const calorieCalculationInputs = ['patient_id', 'goal', 'weekly_weight_goal', 'activity_level', 'target_weight', 'target_weight_goal'];
    calorieCalculationInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', updateCalorieCalculation);
            element.addEventListener('input', debounce(updateCalorieCalculation, 500)); // Debounced for input events
        }
    });
});

// Debounce function to prevent too many API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Debounced version of calorie calculation for input events
const debounceCalorieCalculation = debounce(updateCalorieCalculation, 500);

// Sync target weight fields
function syncTargetWeights(changedField) {
    const targetWeight1 = document.getElementById('target_weight');
    const targetWeight2 = document.getElementById('target_weight_goal');

    if (changedField.id === 'target_weight' && targetWeight2) {
        targetWeight2.value = changedField.value;
    } else if (changedField.id === 'target_weight_goal' && targetWeight1) {
        targetWeight1.value = changedField.value;
    }

    console.log('Target weights synced:', {
        field1: targetWeight1?.value,
        field2: targetWeight2?.value
    });
}

// Initialize meal planning functionality
function initializeMealPlanning() {
    // Add option button handlers
    document.querySelectorAll('.add-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const mealType = this.dataset.meal;
            addNewMealOption(mealType);
        });
    });

    // Food search functionality
    const foodSearch = document.getElementById('food-search');
    const foodGroupFilter = document.getElementById('food-group-filter');
    const foodLanguage = document.getElementById('food-language');

    let searchTimeout;
    foodSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchFoods(this.value, foodGroupFilter.value, foodLanguage.value);
        }, 300);
    });

    foodGroupFilter.addEventListener('change', function() {
        searchFoods(foodSearch.value, this.value, foodLanguage.value);
    });

    foodLanguage.addEventListener('change', function() {
        searchFoods(foodSearch.value, foodGroupFilter.value, this.value);
    });

    // Quantity and unit change handlers
    document.getElementById('food-quantity').addEventListener('input', updateNutritionPreview);
    document.getElementById('food-unit').addEventListener('change', updateNutritionPreview);

    // Add food to meal handler
    document.getElementById('add-food-to-meal').addEventListener('click', addFoodToMeal);

    // Target input handlers - only for macronutrients (calories will be auto-calculated)
    ['target_protein', 'target_carbs', 'target_fat'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            calculateTotalCalories();
            updateNutritionTargets();
        });
    });
}

// Calculate total calories from macronutrients
function calculateTotalCalories() {
    // Standard caloric values per gram
    const CALORIES_PER_GRAM = {
        protein: 4,      // 4 calories per gram of protein
        carbs: 4,        // 4 calories per gram of carbohydrates
        fat: 9           // 9 calories per gram of fat
    };

    // Get current macronutrient values
    const protein = parseFloat(document.getElementById('target_protein').value) || 0;
    const carbs = parseFloat(document.getElementById('target_carbs').value) || 0;
    const fat = parseFloat(document.getElementById('target_fat').value) || 0;

    // Calculate total calories
    const totalCalories = Math.round(
        (protein * CALORIES_PER_GRAM.protein) +
        (carbs * CALORIES_PER_GRAM.carbs) +
        (fat * CALORIES_PER_GRAM.fat)
    );

    // Update the calories field
    document.getElementById('target_calories').value = totalCalories;

    // Update the visual feedback
    updateCalorieBreakdown(protein, carbs, fat, totalCalories);

    // Trigger any dependent calculations
    if (typeof updateMealNutritionSummary === 'function') {
        updateMealNutritionSummary();
    }
}

// Update meal nutrition summary (placeholder function)
function updateMealNutritionSummary() {
    // This function can be used to update meal-specific nutrition summaries
    // Currently a placeholder to prevent JavaScript errors
    console.log('Meal nutrition summary updated');
}

// Calculate and update macronutrients from actual meal options data
function updateMacronutrientsFromMeals() {
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

    // Calculate totals from all meal options (using first option of each meal type)
    Object.values(mealOptions).forEach(options => {
        if (options.length > 0) {
            // Use first option for calculation
            const firstOption = options[0];
            totalCalories += firstOption.total_calories || 0;
            totalProtein += firstOption.total_protein || 0;
            totalCarbs += firstOption.total_carbs || 0;
            totalFat += firstOption.total_fat || 0;
        }
    });

    // For flexible meal plans, we use the totals directly (no daily averaging needed)
    if (totalCalories > 0) {
        // Ensure minimum calorie requirement (500 calories minimum)
        const finalCalories = Math.max(totalCalories, 500);

        // If we had to adjust calories, proportionally adjust macronutrients
        let finalProtein = totalProtein;
        let finalCarbs = totalCarbs;
        let finalFat = totalFat;

        if (finalCalories > totalCalories && totalCalories > 0) {
            const scaleFactor = finalCalories / totalCalories;
            finalProtein = Math.round((totalProtein * scaleFactor) * 10) / 10;
            finalCarbs = Math.round((totalCarbs * scaleFactor) * 10) / 10;
            finalFat = Math.round((totalFat * scaleFactor) * 10) / 10;
        }

        // Update the form fields with calculated values
        document.getElementById('target_calories').value = finalCalories;
        document.getElementById('target_protein').value = finalProtein;
        document.getElementById('target_carbs').value = finalCarbs;
        document.getElementById('target_fat').value = finalFat;

        console.log('Auto-updated macronutrients from meals:', {
            originalCalories: totalCalories,
            finalCalories: finalCalories,
            protein: finalProtein,
            carbs: finalCarbs,
            fat: finalFat,
            adjusted: finalCalories > totalCalories
        });
    } else {
        // No meal data - ensure minimum valid values
        const currentCalories = parseInt(document.getElementById('target_calories').value) || 0;
        const currentProtein = parseFloat(document.getElementById('target_protein').value) || 0;
        const currentCarbs = parseFloat(document.getElementById('target_carbs').value) || 0;
        const currentFat = parseFloat(document.getElementById('target_fat').value) || 0;

        // Ensure minimum calories if current value is too low
        if (currentCalories < 500) {
            document.getElementById('target_calories').value = Math.max(currentCalories, 1200);
            // Set reasonable defaults if values are 0
            if (currentProtein === 0) document.getElementById('target_protein').value = 120;
            if (currentCarbs === 0) document.getElementById('target_carbs').value = 150;
            if (currentFat === 0) document.getElementById('target_fat').value = 40;

            console.log('Set minimum valid macronutrient values (no meal data)');
        }
    }
}

// Load existing meal data when editing
function loadExistingMealData() {
    @if(isset($dietPlan) && $dietPlan->meals->count() > 0)
        console.log('Loading existing meal data...');

        // Load meals from server data (grouped by meal_type and option_number)
        // For flexible plans, day_number is null, for traditional plans it's 1
        const existingMeals = @json($dietPlan->meals->where('is_option_based', true)->groupBy(['meal_type', 'option_number']));

        console.log('Raw existing meals data:', existingMeals);

        Object.keys(existingMeals).forEach(mealType => {
            // Map meal types to our structure
            let mappedMealType = mealType;
            if (mealType.startsWith('snack')) {
                mappedMealType = 'snacks';
            }

            // Clear existing options for this meal type
            mealOptions[mappedMealType] = [];

            const mealTypeOptions = existingMeals[mealType];
            Object.keys(mealTypeOptions).forEach(optionNumber => {
                const meals = mealTypeOptions[optionNumber];

                // Create a new meal option
                const mealOption = {
                    foods: [],
                    total_calories: 0,
                    total_protein: 0,
                    total_carbs: 0,
                    total_fat: 0
                };

                // Process all meals for this option (should typically be just one)
                meals.forEach(meal => {
                    meal.foods.forEach(mealFood => {
                        const food = mealFood.food;
                        if (food) {
                            const foodItem = {
                                food_id: food.id,
                                food_name: mealFood.food_name,
                                displayName: mealFood.food_name,
                                quantity: parseFloat(mealFood.quantity),
                                unit: mealFood.unit,
                                preparation_notes: mealFood.preparation_notes || '',
                                calories: Math.round((food.calories * mealFood.quantity) / 100),
                                protein: Math.round(((food.protein * mealFood.quantity) / 100) * 10) / 10,
                                carbs: Math.round(((food.carbohydrates * mealFood.quantity) / 100) * 10) / 10,
                                fat: Math.round(((food.fat * mealFood.quantity) / 100) * 10) / 10
                            };

                            mealOption.foods.push(foodItem);
                            mealOption.total_calories += foodItem.calories;
                            mealOption.total_protein += foodItem.protein;
                            mealOption.total_carbs += foodItem.carbs;
                            mealOption.total_fat += foodItem.fat;
                        }
                    });
                });

                // Add this option to the meal type
                mealOptions[mappedMealType].push(mealOption);
            });
        });

        // Render all meal options
        ['breakfast', 'lunch', 'dinner', 'snacks'].forEach(mealType => {
            renderAllMealOptions(mealType);
        });

        // Update nutrition summary
        updateNutritionSummary();

        console.log('Loaded meal options data:', mealOptions);
    @endif
}

// Update calorie breakdown display
function updateCalorieBreakdown(protein, carbs, fat, totalCalories) {
    // Create or update breakdown display
    let breakdownElement = document.getElementById('calorie-breakdown');
    if (!breakdownElement) {
        // Create breakdown element if it doesn't exist
        const caloriesField = document.getElementById('target_calories').parentElement;
        breakdownElement = document.createElement('div');
        breakdownElement.id = 'calorie-breakdown';
        breakdownElement.className = 'mt-2';
        caloriesField.appendChild(breakdownElement);
    }

    if (totalCalories > 0) {
        const proteinCals = protein * 4;
        const carbsCals = carbs * 4;
        const fatCals = fat * 9;

        const proteinPercent = ((proteinCals / totalCalories) * 100).toFixed(1);
        const carbsPercent = ((carbsCals / totalCalories) * 100).toFixed(1);
        const fatPercent = ((fatCals / totalCalories) * 100).toFixed(1);

        breakdownElement.innerHTML = `
            <div class="row text-center">
                <div class="col-4">
                    <small class="text-primary">
                        <strong>${proteinCals}</strong> cal<br>
                        <span class="text-muted">${proteinPercent}% Protein</span>
                    </small>
                </div>
                <div class="col-4">
                    <small class="text-success">
                        <strong>${carbsCals}</strong> cal<br>
                        <span class="text-muted">${carbsPercent}% Carbs</span>
                    </small>
                </div>
                <div class="col-4">
                    <small class="text-warning">
                        <strong>${fatCals}</strong> cal<br>
                        <span class="text-muted">${fatPercent}% Fat</span>
                    </small>
                </div>
            </div>
        `;
    } else {
        breakdownElement.innerHTML = '<small class="text-muted">{{ __("Enter macronutrient values to see calorie breakdown") }}</small>';
    }
}

// Load food groups
function loadFoodGroups() {
    fetch('/food-groups/api/list')
        .then(response => response.json())
        .then(groups => {
            const select = document.getElementById('food-group-filter');
            select.innerHTML = '<option value="">{{ __("All Food Groups") }}</option>';
            groups.forEach(group => {
                select.innerHTML += `<option value="${group.id}">${group.name}</option>`;
            });

            // Load initial popular foods
            loadInitialFoods();
        })
        .catch(error => console.error('Error loading food groups:', error));
}

// Load initial popular foods when modal opens
function loadInitialFoods() {
    console.log('loadInitialFoods called'); // Debug log

    // Show loading
    document.getElementById('food-results').innerHTML = `
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2 text-muted">{{ __('Loading popular foods...') }}</p>
        </div>
    `;

    // Load popular foods (get first 20 foods without search filter)
    console.log('Fetching foods from:', `{{ route('foods.search') }}?limit=20`); // Debug log
    fetch(`{{ route('foods.search') }}?limit=20`, {
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
            console.log('Received data:', data); // Debug log
            const foods = data.foods || [];
            console.log('Foods array:', foods); // Debug log

            // Debug: Check if any foods have null nutrition values
            foods.forEach((food, index) => {
                if (!food.calories && food.calories !== 0) {
                    console.warn(`Food ${index} missing calories:`, food);
                }
                if (!food.protein && food.protein !== 0) {
                    console.warn(`Food ${index} missing protein:`, food);
                }
                if (!food.carbohydrates && food.carbohydrates !== 0) {
                    console.warn(`Food ${index} missing carbohydrates:`, food);
                }
                if (!food.fat && food.fat !== 0) {
                    console.warn(`Food ${index} missing fat:`, food);
                }
            });
            if (foods.length === 0) {
                // No foods found, show helpful message
                document.getElementById('food-results').innerHTML = `
                    <div class="col-12 text-center text-muted py-4">
                        <i class="fas fa-search fa-2x mb-2"></i>
                        <p>{{ __('No foods available. Start typing to search or select a food group...') }}</p>
                        <small class="text-muted">{{ __('Contact your administrator to add foods to the database.') }}</small>
                    </div>
                `;
            } else {
                displayFoodResults(foods);
            }
        })
        .catch(error => {
            console.error('Error loading initial foods:', error);
            console.error('Error details:', error.message); // More detailed error logging
            document.getElementById('food-results').innerHTML = `
                <div class="col-12 text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>{{ __('Start typing to search for foods or select a food group...') }}</p>
                    <small class="text-danger d-block mt-2">Debug: ${error.message}</small>
                </div>
            `;
        });
}

// Add new meal option
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

    // Remove empty state if it exists
    const container = document.getElementById(`${mealType}-options`);
    const emptyState = container.querySelector('.text-center.text-muted');
    if (emptyState) {
        emptyState.remove();
    }
}

// Render meal option card
function renderMealOption(mealType, option, optionIndex) {
    const container = document.getElementById(`${mealType}-options`);

    const optionCard = document.createElement('div');
    optionCard.className = 'card mb-3 option-card';
    optionCard.dataset.mealType = mealType;
    optionCard.dataset.optionIndex = optionIndex;

    optionCard.innerHTML = `
        <div class="card-header">
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
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option-btn"
                            data-meal-type="${mealType}" data-option-index="${optionIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="foods-list" id="${mealType}-option-${optionIndex}-foods">
                <div class="text-center text-muted py-3">
                    <i class="fas fa-utensils"></i>
                    <p class="mb-0">No foods added yet. Click "Add Food" to start building this option.</p>
                </div>
            </div>
            <div class="option-summary mt-3 p-2 bg-light rounded" id="${mealType}-option-${optionIndex}-summary">
                <strong>Total: 0 calories | 0g protein | 0g carbs | 0g fat</strong>
            </div>
        </div>
    `;

    container.appendChild(optionCard);

    // Add event listeners
    optionCard.querySelector('.add-food-to-option-btn').addEventListener('click', function() {
        currentMeal = mealType;
        currentOption = optionIndex;
        const modal = new bootstrap.Modal(document.getElementById('foodSelectionModal'));
        modal.show();
        clearFoodSelection();
        loadFoodGroups();
        loadInitialFoods(); // Load initial foods when modal opens
    });

    optionCard.querySelector('.remove-option-btn').addEventListener('click', function() {
        removeOption(mealType, optionIndex);
    });
}

// Get meal type display name
function getMealTypeDisplayName(mealType) {
    const names = {
        breakfast: 'Breakfast',
        lunch: 'Lunch',
        dinner: 'Dinner',
        snacks: 'Snacks'
    };
    return names[mealType] || mealType;
}

// Remove option
function removeOption(mealType, optionIndex) {
    if (confirm('Are you sure you want to remove this option?')) {
        mealOptions[mealType].splice(optionIndex, 1);
        renderAllMealOptions(mealType);
    }
}

// Render all meal options for a meal type
function renderAllMealOptions(mealType) {
    const container = document.getElementById(`${mealType}-options`);
    container.innerHTML = '';

    if (mealOptions[mealType].length === 0) {
        const mealIcons = {
            breakfast: 'coffee',
            lunch: 'sun',
            dinner: 'moon',
            snacks: 'cookie-bite'
        };
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-${mealIcons[mealType]} fa-2x mb-2"></i>
                <p>No ${mealType} options added yet. Click "Add ${getMealTypeDisplayName(mealType)} Option" to start.</p>
            </div>
        `;
    } else {
        mealOptions[mealType].forEach((option, index) => {
            renderMealOption(mealType, option, index);
        });
    }
}

// Search for foods
function searchFoods(query, groupId = '', language = 'default') {

    if (query.length < 2 && !groupId) {
        document.getElementById('food-results').innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>{{ __('Start typing to search for foods...') }}</p>
            </div>
        `;
        return;
    }

    // Show loading
    document.getElementById('food-results').innerHTML = `
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ __('Loading...') }}</span>
            </div>
            <p class="mt-2 text-muted">{{ __('Searching for foods...') }}</p>
        </div>
    `;

    // Make AJAX request to search foods with proper authentication
    fetch(`{{ route('foods.search') }}?search=${encodeURIComponent(query)}&food_group_id=${groupId}&language=${language}`, {
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
            console.log('Food search response:', data);
            displayFoodResults(data.foods || []);
        })
        .catch(error => {
            console.error('Error searching foods:', error);
            document.getElementById('food-results').innerHTML = `
                <div class="col-12 text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>{{ __('Error loading foods. Please try again.') }}</p>
                    <small class="text-muted">Error: ${error.message}</small>
                </div>
            `;
        });
}

// Display food search results
function displayFoodResults(foods) {
    const resultsContainer = document.getElementById('food-results');
    const selectedLanguage = document.getElementById('food-language').value;

    if (foods.length === 0) {
        resultsContainer.innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>{{ __('No foods found. Try a different search term.') }}</p>
            </div>
        `;
        return;
    }

    let html = '';
    foods.forEach(food => {
        // Get the food name in the selected language
        let displayName = food.name; // Default name
        if (selectedLanguage !== 'default' && food.name_translations) {
            const translations = typeof food.name_translations === 'string'
                ? JSON.parse(food.name_translations)
                : food.name_translations;
            displayName = translations[selectedLanguage] || food.name;
        }

        // Legacy support for old column names
        if (selectedLanguage === 'ar' && food.name_ar) {
            displayName = food.name_ar;
        } else if ((selectedLanguage === 'ku_bahdini' || selectedLanguage === 'ku_sorani') && food.name_ku) {
            displayName = food.name_ku;
        }

        // Escape quotes for onclick function
        const escapedName = displayName.replace(/'/g, "\\'");
        const escapedOriginalName = food.name.replace(/'/g, "\\'");

        html += `
            <div class="col-md-6 mb-3">
                <div class="card food-card h-100" style="cursor: pointer;" onclick="selectFood(${food.id}, '${escapedOriginalName}', '${escapedName}', ${food.calories}, ${food.protein}, ${food.carbohydrates}, ${food.fat}, '${food.serving_size || '100g'}', ${food.serving_weight || 100})">
                    <div class="card-body">
                        <h6 class="card-title">${displayName}</h6>
                        ${selectedLanguage !== 'default' && displayName !== food.name ?
                            `<small class="text-muted">${food.name}</small><br>` : ''}
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-primary">${food.calories} cal</small>
                            </div>
                            <div class="col-6">
                                <small class="text-success">${food.protein}g protein</small>
                            </div>
                        </div>
                        <small class="text-muted">Per ${food.serving_size || '100g'}</small>
                    </div>
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
}

// Select a food item
function selectFood(id, originalName, displayName, calories, protein, carbs, fat, servingSize, servingWeight) {
    // Handle backward compatibility - if displayName is actually calories (old function signature)
    if (typeof displayName === 'number') {
        // Old signature: selectFood(id, name, calories, protein, carbs, fat)
        servingWeight = servingSize || 100;
        servingSize = fat || '100g';
        fat = carbs;
        carbs = protein;
        protein = calories;
        calories = displayName;
        displayName = originalName;
    }

    selectedFood = {
        id: id,
        name: originalName, // Store original name for database
        displayName: displayName, // Store display name for UI
        calories: parseFloat(calories),
        protein: parseFloat(protein),
        carbs: parseFloat(carbs),
        fat: parseFloat(fat),
        servingSize: servingSize || '100g',
        servingWeight: parseFloat(servingWeight) || 100
    };

    // Update selected food details with display name
    document.getElementById('selected-food-name').textContent = displayName;
    document.getElementById('selected-food-details').style.display = 'block';
    document.getElementById('add-food-to-meal').disabled = false;

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

    // Calculate multiplier based on actual serving weight
    const baseServingWeight = selectedFood.servingWeight || 100; // Use actual serving weight from food data
    let multiplier = quantity / baseServingWeight; // Calculate based on actual serving size

    if (unit === 'cup') multiplier = quantity * 2.4; // Rough conversion
    else if (unit === 'piece') multiplier = quantity * 1.5;
    else if (unit === 'slice') multiplier = quantity * 0.3;
    else if (unit === 'tbsp') multiplier = quantity * 0.15;
    else if (unit === 'tsp') multiplier = quantity * 0.05;

    const calories = Math.round((selectedFood?.calories || 0) * multiplier);
    const protein = Math.round((selectedFood?.protein || 0) * multiplier * 10) / 10;
    const carbs = Math.round((selectedFood?.carbs || selectedFood?.carbohydrates || 0) * multiplier * 10) / 10;
    const fat = Math.round((selectedFood?.fat || 0) * multiplier * 10) / 10;

    document.getElementById('preview-calories').textContent = calories;
    document.getElementById('preview-protein').textContent = protein + 'g';
    document.getElementById('preview-carbs').textContent = carbs + 'g';
    document.getElementById('preview-fat').textContent = fat + 'g';
}

// Add food to current meal option
function addFoodToMeal() {
    if (!selectedFood || !currentMeal || currentOption === undefined) {
        console.error('Missing required data:', { selectedFood, currentMeal, currentOption });
        return;
    }

    // Validate selectedFood has required properties
    if (!selectedFood.id || (!selectedFood.calories && selectedFood.calories !== 0)) {
        console.error('Selected food missing required properties:', selectedFood);
        alert('Error: Selected food is missing required nutrition data. Please try selecting the food again.');
        return;
    }

    const quantity = parseFloat(document.getElementById('food-quantity').value) || 0;
    const unit = document.getElementById('food-unit').value;
    const notes = document.getElementById('preparation-notes').value;

    // Calculate nutrition values using actual serving weight
    const baseServingWeight = selectedFood.servingWeight || 100;
    let multiplier = quantity / baseServingWeight;
    if (unit === 'cup') multiplier = quantity * 2.4;
    else if (unit === 'piece') multiplier = quantity * 1.5;
    else if (unit === 'slice') multiplier = quantity * 0.3;
    else if (unit === 'tbsp') multiplier = quantity * 0.15;
    else if (unit === 'tsp') multiplier = quantity * 0.05;

    // Debug: Log the selected food data
    console.log('Selected food data:', selectedFood);
    console.log('Selected food name:', selectedFood.name);
    console.log('Selected food translated_name:', selectedFood.translated_name);

    const foodItem = {
        food_id: selectedFood.id,
        food_name: selectedFood.translated_name || selectedFood.name || 'Unknown Food', // Use translated name first
        displayName: selectedFood.translated_name || selectedFood.name || 'Unknown Food', // Display name for UI
        quantity: quantity,
        unit: unit,
        preparation_notes: notes,
        calories: Math.round((selectedFood?.calories || 0) * multiplier),
        protein: Math.round((selectedFood?.protein || 0) * multiplier * 10) / 10,
        carbs: Math.round((selectedFood?.carbs || selectedFood?.carbohydrates || 0) * multiplier * 10) / 10,
        fat: Math.round((selectedFood?.fat || 0) * multiplier * 10) / 10
    };

    // Debug: Log the created food item
    console.log('Created food item:', foodItem);

    // Add to meal option
    mealOptions[currentMeal][currentOption].foods.push(foodItem);

    // Update option display
    updateOptionDisplay(currentMeal, currentOption);
    updateNutritionSummary();

    // Clear the current selection but keep modal open for adding more foods
    clearFoodSelection();

    // Show success message
    showSuccessMessage(`${foodItem.displayName} added to ${currentMeal}!`);

    // Reset search to show all foods again
    const foodSearch = document.getElementById('food-search');
    const foodGroupFilter = document.getElementById('food-group-filter');
    const foodLanguage = document.getElementById('food-language');
    searchFoods('', '', foodLanguage.value);
}

// Show success message
function showSuccessMessage(message) {
    // Create or update success message element
    let successDiv = document.getElementById('food-success-message');
    if (!successDiv) {
        successDiv = document.createElement('div');
        successDiv.id = 'food-success-message';
        successDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
        successDiv.style.position = 'relative';

        // Insert after the modal header
        const modalBody = document.querySelector('#foodSelectionModal .modal-body');
        modalBody.insertBefore(successDiv, modalBody.firstChild);
    }

    successDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Auto-hide after 3 seconds
    setTimeout(() => {
        if (successDiv) {
            successDiv.remove();
        }
    }, 3000);
}

// Update option display
function updateOptionDisplay(mealType, optionIndex) {
    const option = mealOptions[mealType][optionIndex];
    const foodsContainer = document.getElementById(`${mealType}-option-${optionIndex}-foods`);
    const summaryContainer = document.getElementById(`${mealType}-option-${optionIndex}-summary`);

    if (option.foods.length === 0) {
        foodsContainer.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-utensils"></i>
                <p class="mb-0">No foods added yet. Click "Add Food" to start building this option.</p>
            </div>
        `;
    } else {
        let html = '';
        let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

        option.foods.forEach((food, foodIndex) => {
            totalCalories += food.calories;
            totalProtein += food.protein;
            totalCarbs += food.carbs;
            totalFat += food.fat;

            html += `
                <div class="food-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                    <div>
                        <strong>${food.displayName}</strong>
                        <div class="text-muted small">${food.quantity}${food.unit} | ${food.calories} cal</div>
                        ${food.preparation_notes ? `<div class="text-info small">${food.preparation_notes}</div>` : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="removeFoodFromOption('${mealType}', ${optionIndex}, ${foodIndex})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });

        foodsContainer.innerHTML = html;

        // Update option totals
        option.total_calories = totalCalories;
        option.total_protein = totalProtein;
        option.total_carbs = totalCarbs;
        option.total_fat = totalFat;

        // Update summary
        summaryContainer.innerHTML = `
            <strong>Total: ${Math.round(totalCalories)} calories | ${totalProtein.toFixed(1)}g protein | ${totalCarbs.toFixed(1)}g carbs | ${totalFat.toFixed(1)}g fat</strong>
        `;
    }
}

// Remove food from option
function removeFoodFromOption(mealType, optionIndex, foodIndex) {
    mealOptions[mealType][optionIndex].foods.splice(foodIndex, 1);
    updateOptionDisplay(mealType, optionIndex);
    updateNutritionSummary();
}

// Update meal display
function updateMealDisplay(meal) {
    const container = document.getElementById(`${meal}-foods`);

    // Check if container exists
    if (!container) {
        console.warn(`Container not found for meal: ${meal}-foods`);
        return;
    }

    const foods = mealFoods[meal];

    if (!foods || foods.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-utensils fa-2x mb-2"></i>
                <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
            </div>
        `;
        return;
    }

    let html = '';
    foods.forEach((food, index) => {
        html += `
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <strong>${food.displayName || food.name}</strong>
                            <br><small class="text-muted">${food.quantity} ${food.unit}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="row text-center">
                                <div class="col-3">
                                    <small class="text-primary">${food.calories} cal</small>
                                </div>
                                <div class="col-3">
                                    <small class="text-success">${food.protein}g P</small>
                                </div>
                                <div class="col-3">
                                    <small class="text-warning">${food.carbs}g C</small>
                                </div>
                                <div class="col-3">
                                    <small class="text-danger">${food.fat}g F</small>
                                </div>
                            </div>
                            ${food.notes ? `<small class="text-muted">${food.notes}</small>` : ''}
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFoodFromMeal('${meal}', ${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // Update meal tab badge
    const totalCalories = foods.reduce((sum, food) => sum + food.calories, 0);
    const mealCaloriesElement = document.getElementById(`${meal}-calories`);
    if (mealCaloriesElement) {
        mealCaloriesElement.textContent = totalCalories + ' cal';
    }
}

// Remove food from meal
function removeFoodFromMeal(meal, index) {
    mealFoods[meal].splice(index, 1);

    // Only update display if container exists
    const container = document.getElementById(`${meal}-foods`);
    if (container) {
        updateMealDisplay(meal);
    }

    updateNutritionSummary();
}



// Update nutrition summary
function updateNutritionSummary() {
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

    // Calculate totals from all meal options (using first option of each meal type for summary)
    Object.keys(mealOptions).forEach(mealType => {
        const options = mealOptions[mealType];
        if (options.length > 0) {
            // Use first option for summary calculation
            const firstOption = options[0];
            totalCalories += firstOption.total_calories || 0;
            totalProtein += firstOption.total_protein || 0;
            totalCarbs += firstOption.total_carbs || 0;
            totalFat += firstOption.total_fat || 0;
        }
    });

    // Update display with current day totals (no averaging)
    document.getElementById('total-calories').textContent = Math.round(totalCalories);
    document.getElementById('total-protein').textContent = (Math.round(totalProtein * 10) / 10) + 'g';
    document.getElementById('total-carbs').textContent = (Math.round(totalCarbs * 10) / 10) + 'g';
    document.getElementById('total-fat').textContent = (Math.round(totalFat * 10) / 10) + 'g';

    // Update progress bars
    const targetCalories = parseFloat(document.getElementById('target_calories').value) || 2000;
    const targetProtein = parseFloat(document.getElementById('target_protein').value) || 150;
    const targetCarbs = parseFloat(document.getElementById('target_carbs').value) || 250;
    const targetFat = parseFloat(document.getElementById('target_fat').value) || 65;

    document.getElementById('calories-progress').style.width = Math.min((totalCalories / targetCalories) * 100, 100) + '%';
    document.getElementById('protein-progress').style.width = Math.min((totalProtein / targetProtein) * 100, 100) + '%';
    document.getElementById('carbs-progress').style.width = Math.min((totalCarbs / targetCarbs) * 100, 100) + '%';
    document.getElementById('fat-progress').style.width = Math.min((totalFat / targetFat) * 100, 100) + '%';
}

// Update nutrition targets display
function updateNutritionTargets() {
    const targetCalories = document.getElementById('target_calories').value || 2000;
    const targetProtein = document.getElementById('target_protein').value || 150;
    const targetCarbs = document.getElementById('target_carbs').value || 250;
    const targetFat = document.getElementById('target_fat').value || 65;

    document.getElementById('calories-target').textContent = `Target: ${targetCalories}`;
    document.getElementById('protein-target').textContent = `Target: ${targetProtein}g`;
    document.getElementById('carbs-target').textContent = `Target: ${targetCarbs}g`;
    document.getElementById('fat-target').textContent = `Target: ${targetFat}g`;

    updateNutritionSummary();
}

// Clear food selection
function clearFoodSelection() {
    selectedFood = null;
    document.getElementById('selected-food-details').style.display = 'none';
    document.getElementById('add-food-to-meal').disabled = true;
    document.getElementById('food-quantity').value = 100;
    document.getElementById('food-unit').value = 'g';
    document.getElementById('preparation-notes').value = '';
    document.getElementById('food-search').value = '';
    document.getElementById('food-group-filter').value = '';

    document.getElementById('food-results').innerHTML = `
        <div class="col-12 text-center text-muted py-4">
            <i class="fas fa-search fa-2x mb-2"></i>
            <p>{{ __('Start typing to search for foods...') }}</p>
        </div>
    `;
}

// BMI Calculation Functions
function calculateBMI(weight, height) {
    if (!weight || !height || weight <= 0 || height <= 0) {
        return null;
    }
    const heightInMeters = height / 100;
    return weight / (heightInMeters * heightInMeters);
}

function getBMICategory(bmi) {
    if (!bmi) return '';
    if (bmi < 18.5) return '{{ __("Underweight") }}';
    if (bmi < 25) return '{{ __("Normal weight") }}';
    if (bmi < 30) return '{{ __("Overweight") }}';
    return '{{ __("Obese") }}';
}

function getBMICategoryClass(bmi) {
    if (!bmi) return 'text-muted';
    if (bmi < 18.5) return 'text-info';
    if (bmi < 25) return 'text-success';
    if (bmi < 30) return 'text-warning';
    return 'text-danger';
}

function updateBMIDisplay() {
    const currentWeight = parseFloat(document.getElementById('initial_weight').value);
    const targetWeight = parseFloat(document.getElementById('target_weight')?.value ||
                                   document.getElementById('target_weight_goal')?.value);
    const height = parseFloat(document.getElementById('initial_height').value);
    const weeklyGoal = parseFloat(document.getElementById('weekly_weight_goal').value);



    const bmiDisplay = document.getElementById('bmi-display');

    if (currentWeight && height) {
        bmiDisplay.style.display = 'block';

        // Calculate current BMI
        const currentBMI = calculateBMI(currentWeight, height);
        const currentBMICategory = getBMICategory(currentBMI);
        const currentBMIClass = getBMICategoryClass(currentBMI);

        document.getElementById('current-bmi').textContent = currentBMI ? currentBMI.toFixed(1) : '--';
        document.getElementById('current-bmi-category').textContent = currentBMICategory;
        document.getElementById('current-bmi-category').className = `text-muted ${currentBMIClass}`;

        // Calculate target BMI if target weight is provided
        if (targetWeight) {
            const targetBMI = calculateBMI(targetWeight, height);
            const targetBMICategory = getBMICategory(targetBMI);
            const targetBMIClass = getBMICategoryClass(targetBMI);

            document.getElementById('target-bmi').textContent = targetBMI ? targetBMI.toFixed(1) : '--';
            document.getElementById('target-bmi-category').textContent = targetBMICategory;
            document.getElementById('target-bmi-category').className = `text-muted ${targetBMIClass}`;

            // Calculate weight difference and estimated time
            const weightDifference = targetWeight - currentWeight;
            const weightToGoalElement = document.getElementById('weight-to-goal');
            const estimatedTimeElement = document.getElementById('estimated-time');

            if (Math.abs(weightDifference) < 0.1) {
                weightToGoalElement.textContent = '{{ __("Goal Achieved") }}';
                weightToGoalElement.className = 'h5 text-success';
                estimatedTimeElement.textContent = '{{ __("Maintain current weight") }}';
            } else {
                const action = weightDifference > 0 ? '{{ __("Gain") }}' : '{{ __("Lose") }}';
                const absWeightDiff = Math.abs(weightDifference);
                weightToGoalElement.textContent = `${action} ${absWeightDiff.toFixed(1)} kg`;
                weightToGoalElement.className = weightDifference > 0 ? 'h5 text-primary' : 'h5 text-warning';

                // Calculate estimated time if weekly goal is set
                if (weeklyGoal && weeklyGoal !== 0) {
                    const weeksToGoal = Math.abs(weightDifference / weeklyGoal);
                    if (weeksToGoal < 52) {
                        estimatedTimeElement.textContent = `{{ __("~") }}${Math.ceil(weeksToGoal)} {{ __("weeks") }}`;
                    } else {
                        const monthsToGoal = Math.ceil(weeksToGoal / 4.33);
                        estimatedTimeElement.textContent = `{{ __("~") }}${monthsToGoal} {{ __("months") }}`;
                    }
                } else {
                    estimatedTimeElement.textContent = '{{ __("Set weekly goal for estimate") }}';
                }
            }
        } else {
            document.getElementById('target-bmi').textContent = '--';
            document.getElementById('target-bmi-category').textContent = '--';
            document.getElementById('weight-to-goal').textContent = '--';
            document.getElementById('estimated-time').textContent = '{{ __("Set target weight") }}';
        }
    } else {
        bmiDisplay.style.display = 'none';
    }
}

// Add event listeners for BMI calculation
document.addEventListener('DOMContentLoaded', function() {
    const weightHeightInputs = ['initial_weight', 'target_weight', 'target_weight_goal', 'initial_height', 'weekly_weight_goal'];
    weightHeightInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateBMIDisplay);
            element.addEventListener('change', updateBMIDisplay);
        }
    });

    // Initial BMI calculation
    updateBMIDisplay();

    // Load existing meal data if editing
    @if(isset($dietPlan) && $dietPlan->meals->count() > 0)
        loadExistingMealData();
    @endif

    // Auto-populate from patient data when patient is selected
    document.getElementById('patient_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // You can add AJAX call here to fetch patient weight/height data
            // For now, we'll rely on the server-side pre-population
            setTimeout(updateBMIDisplay, 100);
        }
    });

    // Form submission handler - MOVED INSIDE DOMContentLoaded
    const formElement = document.getElementById('nutrition-form');
    if (!formElement) {
        console.error('❌ FORM NOT FOUND! ID: nutrition-form');
        alert('Form not found!');
    } else {
        console.log('✅ Form found, attaching handler');
    }

    formElement.addEventListener('submit', function(e) {
    console.log('🚀 FORM SUBMISSION STARTED');
    console.log('mealOptions object:', mealOptions);

    // Calculate and update macronutrients from actual meal data
    updateMacronutrientsFromMeals();

    // Remove any validation constraints that might interfere
    ['target_protein', 'target_carbs', 'target_fat'].forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.removeAttribute('step');
            field.setAttribute('step', 'any');
            // Clear any validation state
            field.setCustomValidity('');
        }
    });

    // Add meal options data to form
    const mealOptionsData = JSON.stringify(mealOptions);

    // Debug: Log the meal options data being submitted
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('Raw mealOptions object:', mealOptions);
    console.log('JSON string length:', mealOptionsData.length);
    console.log('JSON string preview:', mealOptionsData.substring(0, 500) + '...');

    // Check if mealOptions is empty
    const isEmpty = Object.values(mealOptions).every(options => options.length === 0);
    console.log('Is mealOptions empty?', isEmpty);

    if (isEmpty) {
        console.warn('⚠️ WARNING: mealOptions is empty! No meal data will be submitted.');
        alert('Warning: No meal options detected. Please add some foods to meals before submitting.');
    }

    // Debug: Log the structure of the first option with foods
    Object.keys(mealOptions).forEach(mealType => {
        if (mealOptions[mealType].length > 0) {
            console.log(`${mealType} first option:`, mealOptions[mealType][0]);
            if (mealOptions[mealType][0].foods && mealOptions[mealType][0].foods.length > 0) {
                console.log(`${mealType} first food:`, mealOptions[mealType][0].foods[0]);
                console.log(`${mealType} first food keys:`, Object.keys(mealOptions[mealType][0].foods[0]));
            }
        }
    });

    // Additional debugging - check if meal options data is empty
    const totalOptions = Object.values(mealOptions).reduce((total, options) => total + options.length, 0);
    const totalFoods = Object.values(mealOptions).reduce((total, options) => {
        return total + options.reduce((optionTotal, option) => optionTotal + option.foods.length, 0);
    }, 0);
    console.log('Total options across all meals:', totalOptions);
    console.log('Total foods across all options:', totalFoods);

    if (totalOptions === 0) {
        console.warn('WARNING: No meal options added!');
        if (!confirm('No meal options have been added. Do you want to continue creating the nutrition plan?')) {
            e.preventDefault();
            return false;
        }
    }

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'meal_options';
    hiddenInput.value = mealOptionsData;
    this.appendChild(hiddenInput);
    });
});

// Dynamic calorie calculation based on patient goals
function updateCalorieCalculation() {
    console.log('updateCalorieCalculation called');

    const patientId = document.getElementById('patient_id').value;
    const goal = document.getElementById('goal').value;
    const weeklyWeightGoal = document.getElementById('weekly_weight_goal').value;
    const activityLevel = document.getElementById('activity_level').value;
    // Check both target weight fields (there might be two in different sections)
    const targetWeight = document.getElementById('target_weight')?.value ||
                        document.getElementById('target_weight_goal')?.value;

    console.log('Calorie calculation inputs:', {
        patientId, goal, weeklyWeightGoal, activityLevel, targetWeight
    });

    // Only calculate if we have the required data
    if (!patientId || !goal || !activityLevel) {
        console.log('Missing required data for calorie calculation');
        return;
    }

    // Show loading state
    const caloriesField = document.getElementById('target_calories');
    const proteinField = document.getElementById('target_protein');
    const carbsField = document.getElementById('target_carbs');
    const fatField = document.getElementById('target_fat');

    const originalValues = {
        calories: caloriesField.value,
        protein: proteinField.value,
        carbs: carbsField.value,
        fat: fatField.value
    };

    // Show calculating state
    caloriesField.value = 'Calculating...';
    caloriesField.style.backgroundColor = '#f8f9fa';
    caloriesField.disabled = true;

    // Prepare request data
    const requestData = {
        patient_id: patientId,
        goal: goal,
        activity_level: activityLevel
    };

    if (weeklyWeightGoal) {
        requestData.weekly_weight_goal = Math.abs(parseFloat(weeklyWeightGoal));
    }

    if (targetWeight) {
        requestData.target_weight = parseFloat(targetWeight);
    }

    // Make API call to calculate calories
    console.log('Making API call with data:', requestData);
    fetch('/nutrition/calculate-calories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Please log in to calculate calories');
            } else if (response.status === 419) {
                throw new Error('Session expired. Please refresh the page');
            } else if (response.status === 422) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Validation error');
                });
            }
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update calories and macronutrients with smooth transition
            document.getElementById('target_calories').value = data.calories.target_calories;
            document.getElementById('target_protein').value = data.macronutrients.protein.grams;
            document.getElementById('target_carbs').value = data.macronutrients.carbs.grams;
            document.getElementById('target_fat').value = data.macronutrients.fat.grams;

            // Update the visual calorie breakdown display
            updateCalorieBreakdown(
                data.macronutrients.protein.grams,
                data.macronutrients.carbs.grams,
                data.macronutrients.fat.grams,
                data.calories.target_calories
            );

            // Update nutrition targets display
            updateNutritionTargets();

            // Show calculation details
            showCalorieCalculationDetails(data);

            // Visual feedback for successful calculation
            caloriesField.style.backgroundColor = '#d4edda'; // Light green
            setTimeout(() => {
                caloriesField.style.backgroundColor = '';
            }, 1000);

            console.log('Calorie calculation successful:', data);
        } else {
            console.error('Calorie calculation failed:', data.message);
            // Restore original values
            document.getElementById('target_calories').value = originalValues.calories;
            document.getElementById('target_protein').value = originalValues.protein;
            document.getElementById('target_carbs').value = originalValues.carbs;
            document.getElementById('target_fat').value = originalValues.fat;

            // Show error feedback
            caloriesField.style.backgroundColor = '#f8d7da'; // Light red
            setTimeout(() => {
                caloriesField.style.backgroundColor = '';
            }, 2000);

            // Show user-friendly error messages
            if (data.message) {
                if (data.message.includes('complete data')) {
                    console.log('Patient data incomplete for calorie calculation');
                    // Don't show alert for missing data - just log it
                } else {
                    alert('Unable to calculate calories: ' + data.message);
                }
            }
        }
    })
    .catch(error => {
        console.error('Error calculating calories:', error);
        // Restore original values
        document.getElementById('target_calories').value = originalValues.calories;
        document.getElementById('target_protein').value = originalValues.protein;
        document.getElementById('target_carbs').value = originalValues.carbs;
        document.getElementById('target_fat').value = originalValues.fat;

        // Show error feedback
        caloriesField.style.backgroundColor = '#f8d7da'; // Light red
        setTimeout(() => {
            caloriesField.style.backgroundColor = '';
        }, 2000);

        // Show appropriate error message
        if (error.message.includes('log in')) {
            alert('Please log in to calculate calories');
        } else if (error.message.includes('Session expired')) {
            alert('Session expired. Please refresh the page and try again.');
        } else if (error.message.includes('Validation error')) {
            alert('Please check your input data: ' + error.message);
        } else if (!error.message.includes('Failed to fetch')) {
            alert('Error calculating calories: ' + error.message);
        }
    })
    .finally(() => {
        // Re-enable the field and reset style
        document.getElementById('target_calories').disabled = false;
        document.getElementById('target_calories').style.backgroundColor = '';
    });
}

// Show calorie calculation details
function showCalorieCalculationDetails(data) {
    // Create or update the calculation details display
    let detailsElement = document.getElementById('calorie-calculation-details');
    if (!detailsElement) {
        detailsElement = document.createElement('div');
        detailsElement.id = 'calorie-calculation-details';
        detailsElement.className = 'alert alert-info mt-3';

        // Insert after the nutritional targets card
        const nutritionalTargetsCard = document.querySelector('.card-header h6').closest('.card');
        nutritionalTargetsCard.parentNode.insertBefore(detailsElement, nutritionalTargetsCard.nextSibling);
    }

    let timeToGoalHtml = '';
    if (data.time_to_goal) {
        timeToGoalHtml = `
            <div class="col-md-4">
                <strong>{{ __('Time to Goal') }}</strong><br>
                <span class="text-primary">${data.time_to_goal.weeks} weeks (${data.time_to_goal.months} months)</span><br>
                <small class="text-muted">${data.time_to_goal.weight_difference}kg to go</small>
            </div>
        `;
    }

    detailsElement.innerHTML = `
        <div class="row">
            <div class="col-md-12 mb-2">
                <h6 class="mb-2">
                    <i class="fas fa-calculator text-primary"></i>
                    {{ __('Calorie Calculation for') }} ${data.patient.name}
                </h6>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <strong>{{ __('BMR') }}</strong><br>
                <span class="text-info">${data.calories.bmr} cal/day</span><br>
                <small class="text-muted">{{ __('Base metabolism') }}</small>
            </div>
            <div class="col-md-3">
                <strong>{{ __('TDEE') }}</strong><br>
                <span class="text-warning">${data.calories.tdee} cal/day</span><br>
                <small class="text-muted">{{ __('With activity') }}</small>
            </div>
            <div class="col-md-3">
                <strong>{{ __('Target Calories') }}</strong><br>
                <span class="text-success">${data.calories.target_calories} cal/day</span><br>
                <small class="text-muted">{{ __('For your goal') }}</small>
            </div>
            ${timeToGoalHtml}
        </div>
        ${data.recommendations.length > 0 ? `
            <div class="mt-3">
                <strong>{{ __('Recommendations:') }}</strong>
                <ul class="mb-0 mt-1">
                    ${data.recommendations.map(rec => `<li class="small">${rec}</li>`).join('')}
                </ul>
            </div>
        ` : ''}
    `;
}

// Basic debugging to ensure script loads
console.log('✅ Nutrition plan JavaScript loaded successfully');
console.log('Current mealOptions:', mealOptions);

// Test if form exists
const form = document.getElementById('nutrition-form');
console.log('Form element found:', !!form);
if (form) {
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
}

</script>
@endpush

@push('styles')
<style>
.option-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.option-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-color: #20B2AA;
}

.food-item {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.food-item:hover {
    background: #e9ecef;
}

.option-summary {
    background: linear-gradient(135deg, #20B2AA 0%, #17a2b8 100%);
    color: white;
    border-radius: 6px;
}

.options-container {
    min-height: 200px;
}
</style>
@endpush

@endsection
