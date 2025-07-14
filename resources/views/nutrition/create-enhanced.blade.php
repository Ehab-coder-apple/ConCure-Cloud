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

    <form action="{{ isset($dietPlan) ? route('nutrition.update', $dietPlan) : route('nutrition.store') }}" method="POST" id="nutrition-form">
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
                                <select class="form-select @error('goal') is-invalid @enderror" id="goal" name="goal" required>
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
                                       min="0" max="500" step="5">
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
                                       min="0" max="1000" step="5">
                                @error('target_carbs')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_fat" class="form-label">{{ __('Fat (g)') }}</label>
                                <input type="number" class="form-control @error('target_fat') is-invalid @enderror"
                                       id="target_fat" name="target_fat" value="{{ old('target_fat', $dietPlan?->target_fat ?? 65) }}"
                                       min="0" max="300" step="5">
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
                                       min="20" max="500" step="0.1" placeholder="70.5">
                                @error('initial_weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="target_weight" class="form-label">{{ __('Target Weight (kg)') }}</label>
                                <input type="number" class="form-control @error('target_weight') is-invalid @enderror"
                                       id="target_weight" name="target_weight"
                                       value="{{ old('target_weight', $dietPlan?->target_weight ?? '') }}"
                                       min="20" max="500" step="0.1" placeholder="65.0">
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
                                       min="100" max="250" step="0.1" placeholder="170.0">
                                @error('initial_height')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="weekly_weight_goal" class="form-label">{{ __('Weekly Weight Goal (kg)') }}</label>
                                <select class="form-select @error('weekly_weight_goal') is-invalid @enderror"
                                        id="weekly_weight_goal" name="weekly_weight_goal">
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
                        <div class="row" id="bmi-display" style="display: none;">
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
                                            <small class="text-muted">{{ __('Set weekly goal for estimate') }}</small>
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
                            <i class="fas fa-calendar-week me-2"></i>
                            {{ __('Weekly Meal Plan') }}
                        </h5>
                        <small class="text-muted">{{ __('Plan meals for each day of the week with specific foods and portions') }}</small>
                    </div>
                    <div class="card-body">
                        <!-- Day Selection Navigation -->
                        <div class="alert alert-warning mb-4" style="border: 3px solid #ff6b35; background-color: #fff3cd; padding: 20px;">
                            <h3 class="alert-heading text-center" style="color: #d63384; font-weight: bold;">
                                <i class="fas fa-calendar-week me-2"></i>
                                {{ __('🗓️ SELECT DAY TO PLAN MEALS') }}
                            </h3>
                            <p class="mb-3 text-center" style="font-size: 16px; font-weight: 500;">{{ __('👇 Click on any day below to start planning meals for that day') }}</p>

                            <!-- Day Navigation Buttons -->
                            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4" style="padding: 15px; background-color: #f8f9fa; border-radius: 10px;">
                                <button class="btn btn-success active" id="day1-tab" data-bs-target="#day1" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 1') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day1-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day2-tab" data-bs-target="#day2" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 2') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day2-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day3-tab" data-bs-target="#day3" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 3') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day3-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day4-tab" data-bs-target="#day4" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 4') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day4-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day5-tab" data-bs-target="#day5" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 5') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day5-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day6-tab" data-bs-target="#day6" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 6') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day6-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                                <button class="btn btn-outline-success" id="day7-tab" data-bs-target="#day7" type="button"
                                        style="min-width: 100px; height: 80px; font-size: 14px; font-weight: bold; border: 2px solid #198754;">
                                    <i class="fas fa-calendar-day me-1" style="font-size: 16px;"></i>
                                    {{ __('DAY 7') }}
                                    <br>
                                    <small><span class="badge bg-light text-dark" id="day7-total-calories" style="font-size: 11px;">0 cal</span></small>
                                </button>
                            </div>

                            <!-- Navigation Arrows -->
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-sm btn-secondary" id="prevDayBtn" onclick="navigateDay(-1)" disabled>
                                    <i class="fas fa-chevron-left me-1"></i>
                                    {{ __('Previous Day') }}
                                </button>
                                <span class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Click day buttons above to switch between days') }}
                                </span>
                                <button type="button" class="btn btn-sm btn-secondary" id="nextDayBtn" onclick="navigateDay(1)">
                                    {{ __('Next Day') }}
                                    <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Day Content -->
                        <div class="tab-content" id="dayTabContent">
                            <!-- Day 1 -->
                            <div class="tab-pane fade show active" id="day1" role="tabpanel">
                                <div class="day-content" data-day="1">
                                    <h6 class="mb-3">{{ __('Day 1 - Meal Plan') }}</h6>

                                    <!-- Meal Types Tabs for Day 1 -->
                                    <ul class="nav nav-tabs" id="day1MealTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="day1-breakfast-tab" data-bs-toggle="tab" data-bs-target="#day1-breakfast" type="button" role="tab">
                                                <i class="fas fa-coffee me-1"></i>
                                                {{ __('Breakfast') }}
                                                <span class="badge bg-primary ms-1" id="day1-breakfast-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-lunch-tab" data-bs-toggle="tab" data-bs-target="#day1-lunch" type="button" role="tab">
                                                <i class="fas fa-sun me-1"></i>
                                                {{ __('Lunch') }}
                                                <span class="badge bg-primary ms-1" id="day1-lunch-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-dinner-tab" data-bs-toggle="tab" data-bs-target="#day1-dinner" type="button" role="tab">
                                                <i class="fas fa-moon me-1"></i>
                                                {{ __('Dinner') }}
                                                <span class="badge bg-primary ms-1" id="day1-dinner-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-snacks-tab" data-bs-toggle="tab" data-bs-target="#day1-snacks" type="button" role="tab">
                                                <i class="fas fa-cookie-bite me-1"></i>
                                                {{ __('Snacks') }}
                                                <span class="badge bg-primary ms-1" id="day1-snacks-calories">0 cal</span>
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Day 1 Meal Content -->
                                    <div class="tab-content mt-3" id="day1MealTabContent">
                                        <!-- Day 1 Breakfast -->
                                        <div class="tab-pane fade show active" id="day1-breakfast" role="tabpanel">
                                            <div class="meal-section" data-day="1" data-meal="breakfast">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Breakfast Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="1" data-meal="breakfast">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day1-breakfast-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day 1 Lunch -->
                                        <div class="tab-pane fade" id="day1-lunch" role="tabpanel">
                                            <div class="meal-section" data-day="1" data-meal="lunch">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Lunch Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="1" data-meal="lunch">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day1-lunch-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day 1 Dinner -->
                                        <div class="tab-pane fade" id="day1-dinner" role="tabpanel">
                                            <div class="meal-section" data-day="1" data-meal="dinner">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Dinner Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="1" data-meal="dinner">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day1-dinner-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day 1 Snacks -->
                                        <div class="tab-pane fade" id="day1-snacks" role="tabpanel">
                                            <div class="meal-section" data-day="1" data-meal="snacks">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Snack Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="1" data-meal="snacks">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day1-snacks-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Days 2-7 -->
                            @for($day = 2; $day <= 7; $day++)
                            <div class="tab-pane fade" id="day{{ $day }}" role="tabpanel">
                                <div class="day-content" data-day="{{ $day }}">
                                    <h6 class="mb-3">{{ __('Day :day - Meal Plan', ['day' => $day]) }}</h6>

                                    <!-- Meal Types Tabs for Day {{ $day }} -->
                                    <ul class="nav nav-tabs" id="day{{ $day }}MealTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="day{{ $day }}-breakfast-tab" data-bs-toggle="tab" data-bs-target="#day{{ $day }}-breakfast" type="button" role="tab">
                                                <i class="fas fa-coffee me-1"></i>
                                                {{ __('Breakfast') }}
                                                <span class="badge bg-primary ms-1" id="day{{ $day }}-breakfast-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day{{ $day }}-lunch-tab" data-bs-toggle="tab" data-bs-target="#day{{ $day }}-lunch" type="button" role="tab">
                                                <i class="fas fa-sun me-1"></i>
                                                {{ __('Lunch') }}
                                                <span class="badge bg-primary ms-1" id="day{{ $day }}-lunch-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day{{ $day }}-dinner-tab" data-bs-toggle="tab" data-bs-target="#day{{ $day }}-dinner" type="button" role="tab">
                                                <i class="fas fa-moon me-1"></i>
                                                {{ __('Dinner') }}
                                                <span class="badge bg-primary ms-1" id="day{{ $day }}-dinner-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day{{ $day }}-snacks-tab" data-bs-toggle="tab" data-bs-target="#day{{ $day }}-snacks" type="button" role="tab">
                                                <i class="fas fa-cookie-bite me-1"></i>
                                                {{ __('Snacks') }}
                                                <span class="badge bg-primary ms-1" id="day{{ $day }}-snacks-calories">0 cal</span>
                                            </button>
                                        </li>
                                    </ul>

                                    <!-- Day {{ $day }} Meal Content -->
                                    <div class="tab-content mt-3" id="day{{ $day }}MealTabContent">
                                        <!-- Day {{ $day }} Breakfast -->
                                        <div class="tab-pane fade show active" id="day{{ $day }}-breakfast" role="tabpanel">
                                            <div class="meal-section" data-day="{{ $day }}" data-meal="breakfast">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Breakfast Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="{{ $day }}" data-meal="breakfast">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day{{ $day }}-breakfast-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day {{ $day }} Lunch -->
                                        <div class="tab-pane fade" id="day{{ $day }}-lunch" role="tabpanel">
                                            <div class="meal-section" data-day="{{ $day }}" data-meal="lunch">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Lunch Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="{{ $day }}" data-meal="lunch">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day{{ $day }}-lunch-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day {{ $day }} Dinner -->
                                        <div class="tab-pane fade" id="day{{ $day }}-dinner" role="tabpanel">
                                            <div class="meal-section" data-day="{{ $day }}" data-meal="dinner">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Dinner Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="{{ $day }}" data-meal="dinner">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day{{ $day }}-dinner-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Day {{ $day }} Snacks -->
                                        <div class="tab-pane fade" id="day{{ $day }}-snacks" role="tabpanel">
                                            <div class="meal-section" data-day="{{ $day }}" data-meal="snacks">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">{{ __('Snack Foods') }}</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-food-btn" data-day="{{ $day }}" data-meal="snacks">
                                                        <i class="fas fa-plus me-1"></i>
                                                        {{ __('Add Food') }}
                                                    </button>
                                                </div>
                                                <div class="foods-container" id="day{{ $day }}-snacks-foods">
                                                    <div class="text-center text-muted py-4">
                                                        <i class="fas fa-utensils fa-2x mb-2"></i>
                                                        <p>{{ __('No foods added yet. Click "Add Food" to start building this meal.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endfor
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
                        {{ __('Create Nutrition Plan') }}
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="add-food-to-meal" disabled>{{ __('Add to Meal') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Global variables
let currentDay = 1;
let currentMeal = '';
let selectedFood = null;
let weeklyMealFoods = {};

// Initialize weekly meal structure
for (let day = 1; day <= 7; day++) {
    weeklyMealFoods[day] = {
        breakfast: [],
        lunch: [],
        dinner: [],
        snacks: []
    };
}

// Navigate between days
function navigateDay(direction) {
    const currentActiveTab = document.querySelector('[id$="-tab"].active');
    if (!currentActiveTab) return;

    const currentDayId = currentActiveTab.id;
    const currentDayNumber = parseInt(currentDayId.replace('day', '').replace('-tab', ''));
    const newDayNumber = currentDayNumber + direction;

    if (newDayNumber >= 1 && newDayNumber <= 7) {
        const newTab = document.getElementById(`day${newDayNumber}-tab`);
        if (newTab) {
            // Simulate click to trigger the tab change
            newTab.click();
        }
    }
}

// Update day navigation button states
function updateDayNavigationButtons() {
    const currentActiveTab = document.querySelector('[id$="-tab"].active');
    if (!currentActiveTab) return;

    const currentDayId = currentActiveTab.id;
    const currentDayNumber = parseInt(currentDayId.replace('day', '').replace('-tab', ''));

    const prevBtn = document.getElementById('prevDayBtn');
    const nextBtn = document.getElementById('nextDayBtn');

    if (prevBtn) {
        prevBtn.disabled = currentDayNumber <= 1;
        prevBtn.classList.toggle('disabled', currentDayNumber <= 1);
    }

    if (nextBtn) {
        nextBtn.disabled = currentDayNumber >= 7;
        nextBtn.classList.toggle('disabled', currentDayNumber >= 7);
    }
}

// Update day total calories
function updateDayTotal(day) {
    let dayTotalCalories = 0;

    // Sum calories from all meals for this day
    Object.values(weeklyMealFoods[day]).forEach(mealFoods => {
        mealFoods.forEach(food => {
            dayTotalCalories += food.calories;
        });
    });

    // Update day tab badge
    document.getElementById(`day${day}-total-calories`).textContent = dayTotalCalories + ' cal';
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMealPlanning();
    calculateTotalCalories(); // Calculate calories on page load
    updateNutritionTargets();
    updateDayNavigationButtons();
});

// Initialize meal planning functionality
function initializeMealPlanning() {
    // Add food button handlers
    document.querySelectorAll('.add-food-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentDay = parseInt(this.dataset.day);
            currentMeal = this.dataset.meal;
            const modal = new bootstrap.Modal(document.getElementById('foodSelectionModal'));
            modal.show();
            clearFoodSelection();
            loadFoodGroups();
        });
    });

    // Day tab click handlers (only for day selection buttons)
    document.querySelectorAll('[id^="day"][id$="-tab"]:not([id*="-breakfast"]):not([id*="-lunch"]):not([id*="-dinner"]):not([id*="-snacks"])').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetDay = this.getAttribute('data-bs-target');

            // Hide all day content
            document.querySelectorAll('#dayTabContent > .tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Show target day content
            const targetPane = document.querySelector(targetDay);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }

            // Remove active class from all day buttons
            document.querySelectorAll('[id^="day"][id$="-tab"]:not([id*="-breakfast"]):not([id*="-lunch"]):not([id*="-dinner"]):not([id*="-snacks"])').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('btn-outline-success');
                btn.classList.remove('btn-success');
            });

            // Add active class to clicked button
            this.classList.add('active');
            this.classList.remove('btn-outline-success');
            this.classList.add('btn-success');

            updateDayNavigationButtons();
            updateNutritionSummary(); // Update summary for the newly selected day
        });
    });

    // Meal tab click handlers (for meal tabs within each day)
    document.querySelectorAll('[id*="-breakfast-tab"], [id*="-lunch-tab"], [id*="-dinner-tab"], [id*="-snacks-tab"]').forEach(tab => {
        tab.addEventListener('click', function() {
            const targetMeal = this.getAttribute('data-bs-target');
            const dayId = this.id.split('-')[0]; // Extract day from id like "day1-breakfast-tab"

            // Hide all meal content for this day
            document.querySelectorAll(`#${dayId}MealTabContent .tab-pane`).forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Show target meal content
            const targetPane = document.querySelector(targetMeal);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }

            // Remove active class from all meal tabs for this day
            document.querySelectorAll(`#${dayId}MealTabs .nav-link`).forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active class to clicked meal tab
            this.classList.add('active');
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
    updateMealNutritionSummary();
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

    const calories = Math.round(selectedFood.calories * multiplier);
    const protein = Math.round(selectedFood.protein * multiplier * 10) / 10;
    const carbs = Math.round(selectedFood.carbs * multiplier * 10) / 10;
    const fat = Math.round(selectedFood.fat * multiplier * 10) / 10;

    document.getElementById('preview-calories').textContent = calories;
    document.getElementById('preview-protein').textContent = protein + 'g';
    document.getElementById('preview-carbs').textContent = carbs + 'g';
    document.getElementById('preview-fat').textContent = fat + 'g';
}

// Add food to current meal
function addFoodToMeal() {
    if (!selectedFood || !currentMeal || !currentDay) return;

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

    const foodItem = {
        id: selectedFood.id,
        name: selectedFood.name, // Original name for database
        displayName: selectedFood.displayName || selectedFood.name, // Display name for UI
        quantity: quantity,
        unit: unit,
        notes: notes,
        calories: Math.round(selectedFood.calories * multiplier),
        protein: Math.round(selectedFood.protein * multiplier * 10) / 10,
        carbs: Math.round(selectedFood.carbs * multiplier * 10) / 10,
        fat: Math.round(selectedFood.fat * multiplier * 10) / 10
    };

    // Add to meal for specific day
    weeklyMealFoods[currentDay][currentMeal].push(foodItem);

    // Update UI
    updateMealDisplay(currentDay, currentMeal);
    updateNutritionSummary();

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('foodSelectionModal'));
    modal.hide();
}

// Update meal display
function updateMealDisplay(day, meal) {
    const container = document.getElementById(`day${day}-${meal}-foods`);
    const foods = weeklyMealFoods[day][meal];

    if (foods.length === 0) {
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
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFoodFromMeal(${day}, '${meal}', ${index})">
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
    document.getElementById(`day${day}-${meal}-calories`).textContent = totalCalories + ' cal';

    // Update day total
    updateDayTotal(day);
}

// Remove food from meal
function removeFoodFromMeal(day, meal, index) {
    weeklyMealFoods[day][meal].splice(index, 1);
    updateMealDisplay(day, meal);
    updateNutritionSummary();
}

// Get currently active day
function getCurrentActiveDay() {
    // Find the active day tab
    const activeTab = document.querySelector('.btn.active[data-bs-target]');
    if (activeTab) {
        const target = activeTab.getAttribute('data-bs-target');
        return target.replace('#', ''); // Remove # to get day name (e.g., 'day1')
    }
    return 'day1'; // Default to day1 if no active tab found
}

// Update nutrition summary
function updateNutritionSummary() {
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

    // Get the currently active day
    const activeDay = getCurrentActiveDay();

    // Calculate totals from current day's meals only
    if (weeklyMealFoods[activeDay]) {
        Object.values(weeklyMealFoods[activeDay]).forEach(foods => {
            foods.forEach(food => {
                totalCalories += food.calories;
                totalProtein += food.protein;
                totalCarbs += food.carbs;
                totalFat += food.fat;
            });
        });
    }

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

    document.getElementById('calories-progress').style.width = Math.min((avgCalories / targetCalories) * 100, 100) + '%';
    document.getElementById('protein-progress').style.width = Math.min((avgProtein / targetProtein) * 100, 100) + '%';
    document.getElementById('carbs-progress').style.width = Math.min((avgCarbs / targetCarbs) * 100, 100) + '%';
    document.getElementById('fat-progress').style.width = Math.min((avgFat / targetFat) * 100, 100) + '%';
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
    const targetWeight = parseFloat(document.getElementById('target_weight').value);
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
    const weightHeightInputs = ['initial_weight', 'target_weight', 'initial_height', 'weekly_weight_goal'];
    weightHeightInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateBMIDisplay);
            element.addEventListener('change', updateBMIDisplay);
        }
    });

    // Initial BMI calculation
    updateBMIDisplay();

    // Auto-populate from patient data when patient is selected
    document.getElementById('patient_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            // You can add AJAX call here to fetch patient weight/height data
            // For now, we'll rely on the server-side pre-population
            setTimeout(updateBMIDisplay, 100);
        }
    });
});

// Form submission handler
document.getElementById('nutrition-form').addEventListener('submit', function(e) {
    // Add weekly meal data to form
    const weeklyMealData = JSON.stringify(weeklyMealFoods);
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'weekly_meal_data';
    hiddenInput.value = weeklyMealData;
    this.appendChild(hiddenInput);
});
</script>
@endpush
@endsection
