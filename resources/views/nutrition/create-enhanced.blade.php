@extends('layouts.app')

@section('page-title', isset($dietPlan) ? __('Edit Detailed Nutrition Plan') : __('Create Detailed Nutrition Plan'))

@section('content')
<div id="nutrition-create-enhanced" class="container">
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
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required onchange="updateCalorieCalculation()">
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                            {{ (old('patient_id', $selectedPatient?->id) == $patient->id) ? 'selected' : '' }}>
                                        {{ trim(($patient->first_name . ' ' . $patient->last_name)) ?: ($patient->phone ?? 'Unknown') }} ({{ $patient->patient_id }})
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
                                       id="duration_days" name="duration_days" value="{{ old('duration_days', 7) }}"
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
                                           id="target_calories" name="target_calories" value="{{ old('target_calories', $dietPlan?->target_calories ?? '') }}"
                                           placeholder="{{ __('Select a patient to calculate') }}"
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
                                       id="target_protein" name="target_protein" value="{{ old('target_protein', $dietPlan?->target_protein ?? '') }}"
                                       placeholder="{{ __('Select a patient to calculate') }}"
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
                                       id="target_carbs" name="target_carbs" value="{{ old('target_carbs', $dietPlan?->target_carbs ?? '') }}"
                                       placeholder="{{ __('Select a patient to calculate') }}"
                                       min="0" max="1000" step="any">
                                @error('target_carbs')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_fat" class="form-label">{{ __('Fat (g)') }}</label>
                                <input type="number" class="form-control @error('target_fat') is-invalid @enderror"
                                       id="target_fat" name="target_fat" value="{{ old('target_fat', $dietPlan?->target_fat ?? '') }}"
                                       placeholder="{{ __('Select a patient to calculate') }}"
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
                            <!-- Auto-generate toolbar -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label for="auto-suggest-language" class="form-label mb-0 me-2">{{ __('Suggestions Language') }}</label>
                                    <select class="form-select form-select-sm" id="auto-suggest-language" style="width:auto;min-width:160px;">
                                        <option value="default">{{ __('Default') }}</option>
                                        <option value="en">{{ __('English') }}</option>
                                        <option value="ar">{{ __('العربية') }}</option>
                                        <option value="ku_bahdini">{{ __('کوردی بادینی') }}</option>
                                        <option value="ku_sorani">{{ __('کوردی سۆرانی') }}</option>
                                    </select>
                                </div>
                                <button type="button" id="auto-generate-plan-btn" class="btn btn-primary">
                                    <i class="fas fa-magic me-1"></i> {{ __('Auto-Generate Meal Plan') }}
                                </button>
                            </div>


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
                                    <div class="options-container meal-options-grid" id="breakfast-options">
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
                                    <div class="options-container meal-options-grid" id="lunch-options">
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
                                    <div class="options-container meal-options-grid" id="dinner-options">
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
                                    <div class="options-container meal-options-grid" id="snacks-options">
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
                                                    <small class="text-muted" id="calories-target">Target: —</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-success mb-1" id="total-protein">0g</div>
                                                    <small class="text-muted">{{ __('Protein') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-success" id="protein-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="protein-target">Target: —</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-warning mb-1" id="total-carbs">0g</div>
                                                    <small class="text-muted">{{ __('Carbs') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-warning" id="carbs-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="carbs-target">Target: —</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="nutrition-stat">
                                                    <div class="h4 text-danger mb-1" id="total-fat">0g</div>
                                                    <small class="text-muted">{{ __('Fat') }}</small>
                                                    <div class="progress mt-1" style="height: 6px;">
                                                        <div class="progress-bar bg-danger" id="fat-progress" style="width: 0%"></div>
                                                    </div>
                                                    <small class="text-muted" id="fat-target">Target: —</small>
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
                    @if(isset($dietPlan))
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            {{ __('Update Nutrition Plan') }}
                        </button>
                        <button type="submit" name="save_as_new" value="1" class="btn btn-outline-primary">
                            <i class="fas fa-clone me-1"></i>
                            {{ __('Save As New Nutrition Plan') }}
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            {{ __('Create Nutrition Plan') }}
                        </button>
                    @endif
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
                        <div class="voice-input-wrapper">
                            <input type="text" class="form-control" id="food-search" placeholder="{{ __('Type or 🎤 speak to search foods...') }}">
                            <button type="button" class="btn-voice" title="{{ __('Voice search') }}"><i class="fas fa-microphone"></i></button>
                        </div>
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
                                        <input type="number" class="form-control" id="food-quantity" value="1" min="0.1" step="0.1">
                                    </div>
                                    <div class="mb-3">
                                        <label for="food-unit" class="form-label">{{ __('Unit') }}</label>
                                        <select class="form-select" id="food-unit" onchange="updateNutritionPreview()">
                                            <option value="serving" selected>{{ __('serving') }}</option>
                                            <option value="piece">{{ __('piece') }}</option>
                                            <option value="g">{{ __('grams') }}</option>
                                            <option value="cup">{{ __('cup') }}</option>
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
let selectedFood = null; // Keep for backward compatibility
let selectedFoods = []; // NEW: Array for multiple selection
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
    // Only calculate when patient data is available
    updateNutritionTargets();
    const pidEl = document.getElementById('patient_id');
    if (pidEl && pidEl.value) {
        updateCalorieCalculation();
    }

    // Auto-generate meal plan handlers
    const autoBtn = document.getElementById('auto-generate-plan-btn');
    const autoLang = document.getElementById('auto-suggest-language');
    if (autoBtn) {
        autoBtn.addEventListener('click', () => autoGenerateMealPlan());
    }
    if (autoLang) {
        autoLang.addEventListener('change', () => {
            // If there are targets filled, regenerate to reflect language
            const tc = document.getElementById('target_calories').value;
            if (tc) autoGenerateMealPlan(true);
        });
    }

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
        // Use option-based meals if present; otherwise fall back to all meals (backward compatibility)
        const mealsOptionBased = @json($dietPlan->meals->where('is_option_based', true)->groupBy(['meal_type', 'option_number']));
        const mealsAll = @json($dietPlan->meals->groupBy(['meal_type', 'option_number']));
        const existingMeals = Object.keys(mealsOptionBased).length ? mealsOptionBased : mealsAll;

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
                    option_number: parseInt(optionNumber),
                    option_description: meals[0]?.option_description || `Option ${optionNumber}`,
                    foods: [],
                    total_calories: 0,
                    total_protein: 0,
                    total_carbs: 0,
                    total_fat: 0
                };

                // Process all meals for this option (should typically be just one)
                meals.forEach(meal => {
                    meal.foods.forEach(mealFood => {
                        // The server resolves a `food` match by name for entries saved
                        // without a food_id (e.g. auto-suggested/typed foods), so this
                        // is populated whenever nutrition data is available at all.
                        const food = mealFood.food;
                        if (food) {
                            // Unit-aware reconstruction of existing meal foods
                            const qty = parseFloat(mealFood.quantity) || 0;
                            const unit = (mealFood.unit || 'g').toLowerCase();
                            const servingWeight = parseFloat(food.serving_weight || 0) || 100;
                            const gramsPerPiece = food.grams_per_piece ? parseFloat(food.grams_per_piece) : null;

                            let grams = 0;
                            switch (unit) {
                                case 'kg': grams = qty * 1000; break;
                                case 'g': grams = qty; break;
                                case 'mg': grams = qty / 1000; break;
                                case 'ml': grams = qty; break; // assume 1ml ~= 1g
                                case 'l': grams = qty * 1000; break;
                                case 'cup': grams = qty * 240; break;
                                case 'tbsp': grams = qty * 15; break;
                                case 'tsp': grams = qty * 5; break;
                                case 'serving': grams = servingWeight * qty; break;
                                case 'piece': grams = (gramsPerPiece || servingWeight) * qty; break;
                                case 'slice': grams = servingWeight * qty; break;
                                default: grams = qty; break; // fallback treat as grams
                            }
                            const multiplier = grams / 100;

                            const foodItem = {
                                food_id: food.id,
                                food_name: mealFood.food_name,
                                displayName: mealFood.food_name,
                                quantity: qty,
                                unit: unit,
                                serving_weight: servingWeight,
                                grams_per_piece: gramsPerPiece,
                                preparation_notes: mealFood.preparation_notes || '',
                                calories: Math.round(((food.calories || 0) * multiplier)),
                                protein: Math.round(((food.protein || 0) * multiplier) * 10) / 10,
                                carbs: Math.round(((food.carbohydrates || 0) * multiplier) * 10) / 10,
                                fat: Math.round(((food.fat || 0) * multiplier) * 10) / 10
                            };

                            mealOption.foods.push(foodItem);
                            mealOption.total_calories += foodItem.calories;
                            mealOption.total_protein += foodItem.protein;
                            mealOption.total_carbs += foodItem.carbs;
                            mealOption.total_fat += foodItem.fat;
                        } else {
                            // Custom food without linked master record - still show it with 0 macros
                            const qty = parseFloat(mealFood.quantity) || 0;
                            const unit = (mealFood.unit || 'g').toLowerCase();
                            const foodItem = {
                                food_id: null,
                                food_name: mealFood.food_name || 'Custom Food',
                                displayName: mealFood.food_name || 'Custom Food',
                                quantity: qty,
                                unit: unit,
                                preparation_notes: mealFood.preparation_notes || '',
                                calories: 0,
                                protein: 0,
                                carbs: 0,
                                fat: 0
                            };
                            mealOption.foods.push(foodItem);
                        }
                    });
                });

                // Add this option to the meal type
                optionCounters[mappedMealType] = Math.max(optionCounters[mappedMealType] || 0, parseInt(optionNumber));
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
    optionCard.className = 'meal-option-card option-card';
    optionCard.dataset.mealType = mealType;
    optionCard.dataset.optionIndex = optionIndex;

    optionCard.innerHTML = `
        <div class="meal-option-header">
            <h6 class="meal-option-title">
                <span class="meal-icon"><i class="fas fa-list-ol"></i></span>
                <span title="${option.option_description}">${option.option_description}</span>
            </h6>
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
        <div class="foods-list" id="${mealType}-option-${optionIndex}-foods">
            <div class="text-center text-muted py-3">
                <i class="fas fa-utensils"></i>
                <p class="mb-0">No foods added yet. Click "Add Food" to start building this option.</p>
            </div>
        </div>
        <div class="option-summary mt-2" id="${mealType}-option-${optionIndex}-summary">
            <strong>Total: 0 calories | 0g protein | 0g carbs | 0g fat</strong>
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

    // If option already has foods (editing case), render them now
    if (option.foods && option.foods.length > 0) {
        updateOptionDisplay(mealType, optionIndex);
    }
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

    // Smart search: minimum 1 character required (unless filtering by group)
    if (query.length < 1 && !groupId) {
        document.getElementById('food-results').innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>{{ __('Start typing to search for foods (minimum 1 character)...') }}</p>
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
    // Add cache-busting parameter to prevent browser caching
    const cacheBuster = Date.now();
    fetch(`{{ route('foods.search') }}?search=${encodeURIComponent(query)}&food_group_id=${groupId}&language=${language}&_=${cacheBuster}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
        },
        credentials: 'same-origin',
        cache: 'no-store'
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

        // Use data attributes to avoid issues with special characters (Arabic, Kurdish, etc.)
        const escapedDisplayName = displayName.replace(/"/g, '&quot;');
        const escapedOriginalName = food.name.replace(/"/g, '&quot;');
        const escapedServingSize = (food.serving_size || '100g').replace(/"/g, '&quot;');

        // Calculate nutrition per serving for display
        // Database stores values per 100g, but we want to show per serving
        const servingWeight = food.grams_per_piece || food.serving_weight || 100;
        const servingMultiplier = servingWeight / 100;
        const displayCalories = Math.round(food.calories * servingMultiplier);
        const displayProtein = Math.round(food.protein * servingMultiplier * 10) / 10;

        // Determine display text for serving
        let servingText = food.serving_size || '100g';
        if (food.grams_per_piece && (!food.serving_size || !food.serving_size.includes('piece'))) {
            servingText = `1 piece`;
        } else if (!food.serving_size || food.serving_size === '100g') {
            servingText = `${servingWeight}g`;
        }

        // Build weight information text - ALWAYS show weight
        let weightInfo = '';
        if (food.grams_per_piece) {
            weightInfo = `<div class="text-center mt-1"><small class="text-info"><i class="fas fa-balance-scale me-1"></i>1 piece = ${food.grams_per_piece}g</small></div>`;
        } else {
            // Always show serving weight, even if it's 100g
            weightInfo = `<div class="text-center mt-1"><small class="text-info"><i class="fas fa-balance-scale me-1"></i>1 serving = ${servingWeight}g</small></div>`;
        }

        // Add per 100g reference for patient to calculate any weight
        const cal100g = Math.round(food.calories);
        const protein100g = Math.round(food.protein * 10) / 10;

        html += `
            <div class="col-md-6 mb-3">
                <div class="card food-card h-100" style="cursor: pointer;"
                     data-food-id="${food.id}"
                     data-food-name="${escapedOriginalName}"
                     data-food-display-name="${escapedDisplayName}"
                     data-food-calories="${food.calories}"
                     data-food-protein="${food.protein}"
                     data-food-carbs="${food.carbohydrates}"
                     data-food-fat="${food.fat}"
                     data-food-serving-size="${escapedServingSize}"
                     data-food-serving-weight="${food.serving_weight || 100}"
                     data-food-grams-per-piece="${food.grams_per_piece || ''}">
                    <div class="card-body">
                        <h6 class="card-title mb-2">${displayName}</h6>
                        ${selectedLanguage !== 'default' && displayName !== food.name ?
                            `<small class="text-muted d-block mb-2">${food.name}</small>` : ''}

                        <div class="row text-center mb-2">
                            <div class="col-6">
                                <small class="text-primary"><strong>${displayCalories}</strong> cal</small>
                            </div>
                            <div class="col-6">
                                <small class="text-success"><strong>${displayProtein}g</strong> protein</small>
                            </div>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block"><strong>Per ${servingText}</strong></small>
                        </div>
                        ${weightInfo}
                        <div class="text-center mt-2 pt-2 border-top">
                            <small class="text-secondary" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i>100g = ${cal100g} cal | ${protein100g}g protein
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;

    // Add click event listeners to all food cards
    resultsContainer.querySelectorAll('.food-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const id = parseInt(this.dataset.foodId);
            const originalName = this.dataset.foodName;
            const displayName = this.dataset.foodDisplayName;
            const calories = parseFloat(this.dataset.foodCalories);
            const protein = parseFloat(this.dataset.foodProtein);
            const carbs = parseFloat(this.dataset.foodCarbs);
            const fat = parseFloat(this.dataset.foodFat);
            const servingSize = this.dataset.foodServingSize;
            const servingWeight = parseFloat(this.dataset.foodServingWeight);
            const gramsPerPiece = this.dataset.foodGramsPerPiece ? parseFloat(this.dataset.foodGramsPerPiece) : null;

            selectFood(id, originalName, displayName, calories, protein, carbs, fat, servingSize, servingWeight, gramsPerPiece, this);
        });
    });
}

// Select a food item - NOW SUPPORTS MULTIPLE SELECTION
function selectFood(id, originalName, displayName, calories, protein, carbs, fat, servingSize, servingWeight, gramsPerPiece, cardElement) {
    // Handle backward compatibility - if gramsPerPiece is actually the cardElement (old signature)
    if (gramsPerPiece && typeof gramsPerPiece === 'object' && gramsPerPiece.classList) {
        cardElement = gramsPerPiece;
        gramsPerPiece = null;
    }

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

    // Initialize selectedFoods array if it doesn't exist
    if (typeof selectedFoods === 'undefined') {
        window.selectedFoods = [];
    }

    // Toggle selection (add or remove)
    const idx = selectedFoods.findIndex(f => f.id === id);
    const card = cardElement || event.currentTarget;

    if (idx >= 0) {
        // Remove from selection
        selectedFoods.splice(idx, 1);
        card.classList.remove('border-primary');
    } else {
        // Add to selection
        const foodObj = {
            id: id,
            name: originalName,
            displayName: displayName,
            calories: parseFloat(calories),
            protein: parseFloat(protein),
            carbs: parseFloat(carbs),
            fat: parseFloat(fat),
            servingSize: servingSize || '100g',
            servingWeight: parseFloat(servingWeight) || 100,
            gramsPerPiece: gramsPerPiece ? parseFloat(gramsPerPiece) : null
        };
        selectedFoods.push(foodObj);
        card.classList.add('border-primary');
    }

    // Keep selectedFood for backward compatibility (use last selected)
    selectedFood = selectedFoods.length > 0 ? selectedFoods[selectedFoods.length - 1] : null;

    // Update UI
    const count = selectedFoods.length;
    const detailsEl = document.getElementById('selected-food-details');
    const nameEl = document.getElementById('selected-food-name');
    const addBtn = document.getElementById('add-food-to-meal');

    if (count > 0) {
        detailsEl.style.display = 'block';

        // Show food names instead of just count
        if (count === 1) {
            nameEl.textContent = selectedFoods[0].displayName;
        } else if (count === 2) {
            nameEl.textContent = `${selectedFoods[0].displayName} + ${selectedFoods[1].displayName}`;
        } else if (count === 3) {
            nameEl.textContent = `${selectedFoods[0].displayName}, ${selectedFoods[1].displayName} + ${selectedFoods[2].displayName}`;
        } else {
            // For 4+ items, show first 2 and count
            nameEl.textContent = `${selectedFoods[0].displayName}, ${selectedFoods[1].displayName} + ${count - 2} more`;
        }

        addBtn.disabled = false;
    } else {
        detailsEl.style.display = 'none';
        addBtn.disabled = true;
    }

    // Update nutrition preview
    updateNutritionPreview();
}

// Update nutrition preview based on quantity and unit - SUPPORTS MULTIPLE FOODS
function updateNutritionPreview() {
    if (!selectedFoods || selectedFoods.length === 0) {
        // Clear preview if no foods selected
        document.getElementById('preview-calories').textContent = '0';
        document.getElementById('preview-protein').textContent = '0g';
        document.getElementById('preview-carbs').textContent = '0g';
        document.getElementById('preview-fat').textContent = '0g';
        return;
    }

    const quantity = parseFloat(document.getElementById('food-quantity').value) || 0;
    const unit = document.getElementById('food-unit').value;

    // Calculate totals for all selected foods
    let totalCalories = 0;
    let totalProtein = 0;
    let totalCarbs = 0;
    let totalFat = 0;

    selectedFoods.forEach(food => {
        // Calculate multiplier using proper unit conversion
        let multiplier;

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
                const servingGrams = (food.servingWeight ?? 100) * quantity;
                multiplier = servingGrams / 100;
                break;
            case 'piece':
                const perPiece = food.gramsPerPiece ?? food.servingWeight ?? 100;
                const pieceGrams = perPiece * quantity;
                multiplier = pieceGrams / 100;
                break;
            case 'g':
            default:
                multiplier = quantity / 100;
                break;
        }

        totalCalories += (food.calories || 0) * multiplier;
        totalProtein += (food.protein || 0) * multiplier;
        totalCarbs += (food.carbs || 0) * multiplier;
        totalFat += (food.fat || 0) * multiplier;
    });

    document.getElementById('preview-calories').textContent = Math.round(totalCalories);
    document.getElementById('preview-protein').textContent = (Math.round(totalProtein * 10) / 10) + 'g';
    document.getElementById('preview-carbs').textContent = (Math.round(totalCarbs * 10) / 10) + 'g';
    document.getElementById('preview-fat').textContent = (Math.round(totalFat * 10) / 10) + 'g';
}

// Add food to current meal option - NOW SUPPORTS MULTIPLE FOODS
function addFoodToMeal() {
    if (!selectedFoods || selectedFoods.length === 0 || !currentMeal || currentOption === undefined) {
        console.error('Missing required data:', { selectedFoods, currentMeal, currentOption });
        alert('Please select at least one food item.');
        return;
    }

    const quantity = parseFloat(document.getElementById('food-quantity').value) || 0;
    const unit = document.getElementById('food-unit').value;
    const notes = document.getElementById('preparation-notes').value;

    if (!quantity || quantity <= 0) {
        alert('Please enter a valid quantity.');
        return;
    }

    let addedCount = 0;
    let addedNames = [];

    // Add each selected food
    selectedFoods.forEach(food => {
        // Validate food has required properties
        if (!food.id || (!food.calories && food.calories !== 0)) {
            console.error('Food missing required properties:', food);
            return;
        }

        // Calculate nutrition values using proper unit conversion
        let multiplier;

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
                const servingGrams = (food.servingWeight ?? 100) * quantity;
                multiplier = servingGrams / 100;
                break;
            case 'piece':
                const perPiece = food.gramsPerPiece ?? food.servingWeight ?? 100;
                const pieceGrams = perPiece * quantity;
                multiplier = pieceGrams / 100;
                break;
            case 'g':
            default:
                multiplier = quantity / 100;
                break;
        }

        const foodItem = {
            food_id: food.id,
            food_name: food.displayName || food.name || 'Unknown Food',
            displayName: food.displayName || food.name || 'Unknown Food',
            quantity: quantity,
            unit: unit,
            serving_weight: food.servingWeight || 100,
            grams_per_piece: food.gramsPerPiece || null,
            preparation_notes: notes,
            calories: Math.round((food.calories || 0) * multiplier),
            protein: Math.round((food.protein || 0) * multiplier * 10) / 10,
            carbs: Math.round((food.carbs || 0) * multiplier * 10) / 10,
            fat: Math.round((food.fat || 0) * multiplier * 10) / 10
        };

        // Add to meal option
        mealOptions[currentMeal][currentOption].foods.push(foodItem);
        addedCount++;
        addedNames.push(food.displayName || food.name);
    });

    // Update option display
    updateOptionDisplay(currentMeal, currentOption);
    updateNutritionSummary();

    // Clear the current selection but keep modal open for adding more foods
    clearFoodSelection();

    // Show success message
    const message = addedCount === 1
        ? `${addedNames[0]} added to ${currentMeal}!`
        : `${addedCount} foods added to ${currentMeal}!`;
    showSuccessMessage(message);

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
        let html = '<div class="food-line-list">';
        let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

        option.foods.forEach((food, foodIndex) => {
            totalCalories += food.calories;
            totalProtein += food.protein;
            totalCarbs += food.carbs;
            totalFat += food.fat;

            // Format quantity display with proper spacing and weight information
            let quantityDisplay = '';

            if (food.unit === 'serving') {
                // Show: "1 serving (150g) | 90 cal"
                const servingWeight = food.serving_weight || 100;
                quantityDisplay = `${food.quantity} ${food.unit} (${servingWeight}g)`;
            } else if (food.unit === 'piece') {
                // Show: "1 piece (25g) | 34 cal"
                const pieceWeight = food.grams_per_piece || food.serving_weight || 100;
                quantityDisplay = `${food.quantity} ${food.unit} (${pieceWeight}g)`;
            } else if (['g', 'kg', 'mg', 'ml', 'l'].includes(food.unit)) {
                // Show: "50g | 37 cal" (no space for weight units)
                quantityDisplay = `${food.quantity}${food.unit}`;
            } else {
                // Show: "1 cup | 240 cal" (space for other units)
                quantityDisplay = `${food.quantity} ${food.unit}`;
            }

            const titleText = food.preparation_notes ? `${food.displayName} - ${food.preparation_notes}` : food.displayName;

            html += `
                <div class="food-line">
                    <span class="food-name" title="${titleText}">${food.displayName}</span>
                    <span class="food-qty">
                        ${quantityDisplay} | ${food.calories} cal
                        <button type="button" class="btn-remove-food" title="Remove"
                                onclick="removeFoodFromOption('${mealType}', ${optionIndex}, ${foodIndex})">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                </div>
            `;
        });

        html += '</div>';
        foodsContainer.innerHTML = html;

        // Update option totals
        option.total_calories = totalCalories;
        option.total_protein = totalProtein;
        option.total_carbs = totalCarbs;
        option.total_fat = totalFat;

        // Update summary (styled to match the meal-option-macros row used on the plan dashboard)
        summaryContainer.classList.add('meal-option-macros');
        summaryContainer.innerHTML = `
            <span>Cal: <strong>${Math.round(totalCalories)}</strong></span>
            <span>P: <strong>${totalProtein.toFixed(1)}g</strong></span>
            <span>C: <strong>${totalCarbs.toFixed(1)}g</strong></span>
            <span>F: <strong>${totalFat.toFixed(1)}g</strong></span>
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
    const targetCalories = parseFloat(document.getElementById('target_calories').value) || 0;
    const targetProtein = parseFloat(document.getElementById('target_protein').value) || 0;
    const targetCarbs = parseFloat(document.getElementById('target_carbs').value) || 0;
    const targetFat = parseFloat(document.getElementById('target_fat').value) || 0;

    const caloriesPct = targetCalories > 0 ? Math.min((totalCalories / targetCalories) * 100, 100) : 0;
    const proteinPct = targetProtein > 0 ? Math.min((totalProtein / targetProtein) * 100, 100) : 0;
    const carbsPct = targetCarbs > 0 ? Math.min((totalCarbs / targetCarbs) * 100, 100) : 0;
    const fatPct = targetFat > 0 ? Math.min((totalFat / targetFat) * 100, 100) : 0;

    document.getElementById('calories-progress').style.width = caloriesPct + '%';
    document.getElementById('protein-progress').style.width = proteinPct + '%';
    document.getElementById('carbs-progress').style.width = carbsPct + '%';
    document.getElementById('fat-progress').style.width = fatPct + '%';
}

// Update nutrition targets display
function updateNutritionTargets() {
    const pidEl = document.getElementById('patient_id');
    const noPatient = !(pidEl && pidEl.value);
    const msg = 'Select a patient to calculate';

    const targetCalories = (document.getElementById('target_calories').value || '').trim();
    const targetProtein = (document.getElementById('target_protein').value || '').trim();
    const targetCarbs = (document.getElementById('target_carbs').value || '').trim();
    const targetFat = (document.getElementById('target_fat').value || '').trim();

    const caloriesLabel = targetCalories || (noPatient ? msg : '—');
    const proteinLabel = targetProtein ? `${targetProtein}g` : (noPatient ? msg : '—');
    const carbsLabel = targetCarbs ? `${targetCarbs}g` : (noPatient ? msg : '—');
    const fatLabel = targetFat ? `${targetFat}g` : (noPatient ? msg : '—');

    document.getElementById('calories-target').textContent = `Target: ${caloriesLabel}`;
    document.getElementById('protein-target').textContent = `Target: ${proteinLabel}`;
    document.getElementById('carbs-target').textContent = `Target: ${carbsLabel}`;
    document.getElementById('fat-target').textContent = `Target: ${fatLabel}`;

    updateNutritionSummary();
}

// Clear food selection - UPDATED FOR MULTIPLE SELECTION
function clearFoodSelection() {
    selectedFood = null;
    selectedFoods = []; // Clear the array

    // Remove border from all food cards
    document.querySelectorAll('.food-card').forEach(card => {
        card.classList.remove('border-primary');
    });

    document.getElementById('selected-food-details').style.display = 'none';
    document.getElementById('add-food-to-meal').disabled = true;
    document.getElementById('food-quantity').value = 100;
    document.getElementById('food-unit').value = 'g';
    document.getElementById('preparation-notes').value = '';

    // Don't clear search and filter - keep them so user can continue selecting
    // document.getElementById('food-search').value = '';
    // document.getElementById('food-group-filter').value = '';

    // Don't clear results - keep showing foods so user can select more
    // document.getElementById('food-results').innerHTML = `
    //     <div class="col-12 text-center text-muted py-4">
    //         <i class="fas fa-search fa-2x mb-2"></i>
    //         <p>{{ __('Start typing to search for foods...') }}</p>
    //     </div>
    // `;
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


// Persist and auto-restore Weight Management & BMI preferences (localStorage)
(function() {
    const CURRENT_USER_ID = @json(auth()->id());

    function wmKey() {
        const patientId = document.getElementById('patient_id')?.value || 'none';
        return `cc:wm_bmi:${CURRENT_USER_ID || 'anon'}:${patientId}`;
    }

    function getVal(id) {
        const el = document.getElementById(id);
        if (!el) return null;
        if (el.type === 'number') return el.value !== '' ? parseFloat(el.value) : null;
        return el.value ?? null;
    }

    function setVal(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        if (value === null || value === undefined || value === '') return;
        el.value = value;
    }

    function collectPrefs() {
        return {
            initial_weight: getVal('initial_weight'),
            target_weight_goal: getVal('target_weight_goal') ?? getVal('target_weight'),
            initial_height: getVal('initial_height'),
            weekly_weight_goal: getVal('weekly_weight_goal'),
            // Optional future controls if present in the UI
            period: document.getElementById('weight_period')?.value || null,
            date_from: document.getElementById('weight_date_from')?.value || null,
            date_to: document.getElementById('weight_date_to')?.value || null,
        };
    }

    function applyPrefs(prefs) {
        if (!prefs) return;
        setVal('initial_weight', prefs.initial_weight);
        setVal('target_weight_goal', prefs.target_weight_goal);
        setVal('initial_height', prefs.initial_height);
        setVal('weekly_weight_goal', prefs.weekly_weight_goal);
        if (document.getElementById('weight_period') && prefs.period !== undefined) {
            document.getElementById('weight_period').value = prefs.period;
        }
        if (document.getElementById('weight_date_from') && prefs.date_from) {
            document.getElementById('weight_date_from').value = prefs.date_from;
        }
        if (document.getElementById('weight_date_to') && prefs.date_to) {
            document.getElementById('weight_date_to').value = prefs.date_to;
        }
    }

    function savePrefs() {
        try {
            localStorage.setItem(wmKey(), JSON.stringify(collectPrefs()));
        } catch (e) {
            // ignore storage errors
        }
    }

    function loadPrefs() {
        try {
            const raw = localStorage.getItem(wmKey());
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Load and apply last-used preferences before computing BMI
        const existing = loadPrefs();
        if (existing) {
            applyPrefs(existing);
        }

        // Save on any changes to relevant controls
        ['initial_weight','target_weight_goal','initial_height','weekly_weight_goal','weight_period','weight_date_from','weight_date_to']
            .forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                ['input','change'].forEach(evt => el.addEventListener(evt, savePrefs));
            });

        // When patient changes, switch key namespace and try to restore
        const patientEl = document.getElementById('patient_id');
        if (patientEl) {
            patientEl.addEventListener('change', function() {
                setTimeout(function() {
                    const prefs = loadPrefs();
                    if (prefs) applyPrefs(prefs);
                    if (typeof updateBMIDisplay === 'function') updateBMIDisplay();
                }, 0);
            });
        }

        // Recompute BMI with restored values
        if (typeof updateBMIDisplay === 'function') updateBMIDisplay();
    });
})();

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
    if (!patientId || !activityLevel) {
        console.log('Missing required data for calorie calculation');
        return;
    }

    // If goal is not selected yet, default to maintenance temporarily
    const effectiveGoal = goal || 'maintenance';
    if (!goal) {
        const goalSelect = document.getElementById('goal');
        // Add a subtle hint once
        let hint = document.getElementById('goal-fallback-hint');
        if (!hint && goalSelect) {
            hint = document.createElement('div');
            hint.id = 'goal-fallback-hint';
            hint.className = 'form-text text-muted mt-1';
            hint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Using Maintenance temporarily. Choose a goal to refine calories/macros.';
            goalSelect.parentElement.appendChild(hint);
        }
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
        goal: effectiveGoal,
        activity_level: activityLevel
    };

    // Always include weekly_weight_goal when a value is selected, including 0 (keep sign: negative = loss, positive = gain)
    if (weeklyWeightGoal !== null && weeklyWeightGoal !== undefined && weeklyWeightGoal !== '') {
        const w = parseFloat(weeklyWeightGoal);
        if (!Number.isNaN(w)) {
            requestData.weekly_weight_goal = w;
        }
    }

    // Include target weight when provided
    if (targetWeight !== null && targetWeight !== undefined && targetWeight !== '') {
        const tw = parseFloat(targetWeight);
        if (!Number.isNaN(tw)) {
            requestData.target_weight = tw;
        }
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

            // Ensure calories field is populated even if API value missing
            const tc = document.getElementById('target_calories');
            if (!tc.value || isNaN(parseFloat(tc.value))) {
                calculateTotalCalories();
            }

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

            // Fallback: compute from macros if value is empty
            const tc = document.getElementById('target_calories');
            if (!tc.value || isNaN(parseFloat(tc.value))) {
                calculateTotalCalories();
            }

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

        // Fallback: compute from macros if value is empty
        const tc = document.getElementById('target_calories');
        if (!tc.value || isNaN(parseFloat(tc.value))) {
            calculateTotalCalories();
        }

        // Show error feedback
        caloriesField.style.backgroundColor = '#f8d7da'; // Light red
        setTimeout(() => {
            caloriesField.style.backgroundColor = '';
        }, 2000);

        // Show appropriate error message (suppress popup for incomplete patient data)
        const em = (error && error.message) ? error.message : '';
        if (em.includes('complete data') || em.includes('Unable to calculate calories')) {
            console.warn('Calorie calculation skipped: patient profile incomplete (age, gender, height, weight). Save patient details first.');
            // Optionally show a non-blocking hint near the calories field
            let hint = document.getElementById('calorie-missing-profile-hint');
            if (!hint) {
              hint = document.createElement('div');
              hint.id = 'calorie-missing-profile-hint';
              hint.className = 'form-text text-muted';
              hint.style.marginTop = '6px';
              document.getElementById('target_calories').parentElement.appendChild(hint);
            }
            hint.innerHTML = '<i class="fas fa-info-circle me-1"></i>' +
              'To auto-calculate, please complete and save the patient profile (age, gender, height, weight).';
        } else if (em.includes('log in')) {
            alert('Please log in to calculate calories');
        } else if (em.includes('Session expired')) {
            alert('Session expired. Please refresh the page and try again.');
        } else if (em.includes('Validation error')) {
            alert('Please check your input data: ' + em);
        } else if (!em.includes('Failed to fetch')) {
            alert('Error calculating calories: ' + em);
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

// Auto-generate meal plan from targets
function autoGenerateMealPlan(silent = false) {
    const patientId = document.getElementById('patient_id')?.value;
    const language = document.getElementById('auto-suggest-language')?.value || 'default';
    const goal = document.getElementById('goal')?.value || '';
    const activity = document.getElementById('activity_level')?.value || '';
    const weekly = document.getElementById('weekly_weight_goal')?.value || '';

    if (!patientId) {
        alert('{{ __('Please select a patient first') }}');
        return;
    }

    // Read current targets (allow empty; backend can compute from patient)
    const payload = {
        patient_id: patientId,
        language: language,
        goal: goal || undefined,
        activity_level: activity || undefined,
        weekly_weight_goal: weekly || undefined,
        target_calories: parseFloat(document.getElementById('target_calories')?.value) || undefined,
        target_protein: parseFloat(document.getElementById('target_protein')?.value) || undefined,
        target_carbs: parseFloat(document.getElementById('target_carbs')?.value) || undefined,
        target_fat: parseFloat(document.getElementById('target_fat')?.value) || undefined,
        restrictions: [],
        report_missing: true,
    };

    // Simple loading state on button
    const btn = document.getElementById('auto-generate-plan-btn');
    const prevHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Generating...') }}';
    }

    fetch("{{ route('nutrition.auto-generate-plan') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(async resp => {
        let data = null;
        try { data = await resp.json(); } catch(e) {}
        if (!resp.ok) {
            throw { httpStatus: resp.status, data };
        }
        return data;
    })
    .then(data => {
        if (!data || !data.success) {
            if (data && data.code === 'MISSING_DATA') {
                // Render a helpful warning with a list of missing items
                const msgs = Array.isArray(data.missing_human) ? data.missing_human : (data.missing || []);
                const warn = document.createElement('div');
                warn.className = 'alert alert-warning mt-2';
                warn.innerHTML = `<strong>{{ __('Missing information to generate the plan') }}:</strong>` +
                    (msgs.length ? `<ul class="mb-1">${msgs.map(m=>`<li>${m}</li>`).join('')}</ul>` : '') +
                    (Array.isArray(data.suggestions) && data.suggestions.length ? `<div class="small text-muted">${data.suggestions.join('<br>')}</div>` : '');
                const toolbar = document.getElementById('auto-generate-plan-btn')?.closest('.mb-3') || document.body;
                toolbar.parentElement?.insertBefore(warn, toolbar.nextSibling);
                setTimeout(()=>warn.remove(), 8000);
                // Stop normal success path
                return;
            }
            throw new Error(data?.message || 'Failed to generate plan');
        }
        if (!data.meal_options) {
            throw new Error('No meal options returned');
        }
        // Apply generated options to UI state
        applyGeneratedMealOptions(data.meal_options);
        if (!silent) {
            // Simple success toast
            const ok = document.createElement('div');
            ok.className = 'alert alert-success mt-2';
            ok.innerHTML = '<i class="fas fa-magic me-1"></i>{{ __('Suggested meal plan added. You can edit anything before saving.') }}';
            const cardBody = document.querySelector('#mealTabContent')?.parentElement;
            if (cardBody) {
                cardBody.insertBefore(ok, cardBody.firstChild.nextSibling);
                setTimeout(() => ok.remove(), 3000);
            }
        }
    })
    .catch(err => {
        console.error('Auto-generate failed:', err);
        if (err && err.data && err.data.code === 'MISSING_DATA') {
            // Already shown a warning above; do nothing
            return;
        }
        alert('{{ __('Could not auto-generate the meal plan') }}: ' + (err?.message || err));
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = prevHtml;
        }
    });
}

function applyGeneratedMealOptions(generated) {
    // Replace current options with generated suggestions
    ['breakfast','lunch','dinner','snacks'].forEach(meal => {
        mealOptions[meal] = Array.isArray(generated[meal]) ? generated[meal] : [];
        optionCounters[meal] = mealOptions[meal].length || 0;
        renderAllMealOptions(meal);
    });
    // Recompute per-meal calorie badges if function exists
    if (typeof updateMealTotals === 'function') {
        updateMealTotals();
    }
}


</script>
@endpush

@push('styles')
<style>
/* ---- Compact meal option grid (mirrors nutrition/show.blade.php) ---- */
#nutrition-create-enhanced .meal-options-grid {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
    min-height: 0;
}
@media (min-width: 768px) {
    #nutrition-create-enhanced .meal-options-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 1200px) {
    #nutrition-create-enhanced .meal-options-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

#nutrition-create-enhanced .meal-option-card {
    border: 1px solid #E5E7EB;
    border-radius: 0.5rem;
    padding: 0.9rem 1rem;
    background: #fff;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
#nutrition-create-enhanced .meal-option-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-color: #20B2AA;
}
#nutrition-create-enhanced .meal-option-card .meal-option-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #E5E7EB;
    flex-wrap: wrap;
}
#nutrition-create-enhanced .meal-option-card .meal-option-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    min-width: 0;
}
#nutrition-create-enhanced .meal-option-card .meal-option-title .meal-icon {
    width: 28px;
    height: 28px;
    min-width: 28px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F3F4F6;
    color: #6B7280;
    font-size: 0.8rem;
}
#nutrition-create-enhanced .meal-option-card .meal-option-title span:not(.meal-icon) {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 160px;
}
#nutrition-create-enhanced .food-line-list {
    columns: 1;
    column-gap: 1.25rem;
    flex: 1;
}
@media (min-width: 480px) {
    #nutrition-create-enhanced .food-line-list { columns: 2; }
}
#nutrition-create-enhanced .food-line {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 0.5rem;
    padding: 0.3rem 0;
    border-bottom: 1px solid #F3F4F6;
    break-inside: avoid;
}
#nutrition-create-enhanced .food-line:last-child { border-bottom: none; }
#nutrition-create-enhanced .food-line .food-name {
    font-size: 0.85rem;
    font-weight: 500;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
#nutrition-create-enhanced .food-line .food-qty {
    font-size: 0.78rem;
    color: #6B7280;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
#nutrition-create-enhanced .btn-remove-food {
    border: none;
    background: transparent;
    color: #DC3545;
    font-size: 0.72rem;
    line-height: 1;
    padding: 0.1rem 0.2rem;
    cursor: pointer;
}
#nutrition-create-enhanced .btn-remove-food:hover { color: #a71d2a; }

#nutrition-create-enhanced .meal-option-macros,
#nutrition-create-enhanced .option-summary {
    margin-top: 0.6rem;
    padding-top: 0.5rem;
    border-top: 1px solid #E5E7EB;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: #6B7280;
}
#nutrition-create-enhanced .meal-option-macros span strong,
#nutrition-create-enhanced .option-summary span strong { color: #374151; }

#nutrition-create-enhanced .options-container {
    min-height: 200px;
}
</style>
@endpush

@include('partials.voice-input')

@endsection
