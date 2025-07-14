@extends('layouts.app')

@section('page-title', __('Create Weight Loss Nutrition Plan'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-weight text-success"></i>
                        {{ __('Create Weight Loss Nutrition Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Design a specialized weight loss nutrition plan for your patient') }}</p>
                </div>
                <div>
                    <a href="{{ route('nutrition.templates') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Templates') }}
                    </a>
                    <a href="{{ route('nutrition.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-list me-1"></i>
                        {{ __('All Plans') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        {{ __('Weight Loss Template Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-success">{{ $template['title'] }}</h6>
                            <p class="text-muted mb-2">{{ $template['description'] }}</p>
                            <div class="mb-2">
                                <strong>{{ __('Instructions:') }}</strong>
                                <p class="text-muted mb-0">{{ $template['instructions'] }}</p>
                            </div>
                            <div class="mb-0">
                                <strong>{{ __('Restrictions:') }}</strong>
                                <p class="text-muted mb-0">{{ $template['restrictions'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-success mb-1">{{ $template['target_calories'] }}</div>
                                        <small class="text-muted">{{ __('Calories/day') }}</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-success mb-1">{{ $template['duration_days'] }}</div>
                                        <small class="text-muted">{{ __('Days') }}</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-success mb-1">{{ $template['target_protein'] }}g</div>
                                        <small class="text-muted">{{ __('Protein') }}</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-success mb-1">{{ $template['target_carbs'] }}g</div>
                                        <small class="text-muted">{{ __('Carbs') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TEST: This should be visible -->
    <div class="alert alert-danger text-center mb-3" style="font-size: 18px; font-weight: bold; border: 3px solid red;">
        🔴 WEEKLY MEAL PLANNING PAGE LOADED - DAY BUTTONS SHOULD BE BELOW 🔴
    </div>

    <form action="{{ route('nutrition.store') }}" method="POST">
        @csrf
        <input type="hidden" name="template_type" value="weight_loss">
        <input type="hidden" name="goal" value="{{ $template['goal'] }}">
        <input type="hidden" name="target_calories" value="{{ $template['target_calories'] }}">
        <input type="hidden" name="target_protein" value="{{ $template['target_protein'] }}">
        <input type="hidden" name="target_carbs" value="{{ $template['target_carbs'] }}">
        <input type="hidden" name="target_fat" value="{{ $template['target_fat'] }}">
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Patient Information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label">{{ __('Patient') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
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
                                       id="title" name="title" value="{{ old('title', $template['title']) }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label">{{ __('Description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3">{{ old('description', $template['description']) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">{{ __('Duration (Days)') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                       id="duration_days" name="duration_days" value="{{ old('duration_days', $template['duration_days']) }}" 
                                       min="1" max="365" required>
                                @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="instructions" class="form-label">{{ __('Instructions') }}</label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                          id="instructions" name="instructions" rows="3">{{ old('instructions', $template['instructions']) }}</textarea>
                                @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="restrictions" class="form-label">{{ __('Dietary Restrictions') }}</label>
                                <textarea class="form-control @error('restrictions') is-invalid @enderror" 
                                          id="restrictions" name="restrictions" rows="2">{{ old('restrictions', $template['restrictions']) }}</textarea>
                                @error('restrictions')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nutritional Targets -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bullseye"></i>
                            {{ __('Nutritional Targets') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="calories" class="form-label">{{ __('Daily Calories') }}</label>
                            <input type="number" class="form-control @error('calories') is-invalid @enderror" 
                                   id="calories" name="calories" value="{{ old('calories', $template['target_calories']) }}" 
                                   min="800" max="4000">
                            @error('calories')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="protein" class="form-label">{{ __('Protein (g)') }}</label>
                            <input type="number" class="form-control @error('protein') is-invalid @enderror" 
                                   id="protein" name="protein" value="{{ old('protein', $template['target_protein']) }}" 
                                   min="0" max="500">
                            @error('protein')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="carbs" class="form-label">{{ __('Carbohydrates (g)') }}</label>
                            <input type="number" class="form-control @error('carbs') is-invalid @enderror" 
                                   id="carbs" name="carbs" value="{{ old('carbs', $template['target_carbs']) }}" 
                                   min="0" max="1000">
                            @error('carbs')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fat" class="form-label">{{ __('Fat (g)') }}</label>
                            <input type="number" class="form-control @error('fat') is-invalid @enderror" 
                                   id="fat" name="fat" value="{{ old('fat', $template['target_fat']) }}" 
                                   min="0" max="300">
                            @error('fat')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-success">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                {{ __('These values are pre-filled based on the weight loss template. You can adjust them as needed for the specific patient.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Weight Loss Plan') }}
                            </button>
                            <a href="{{ route('nutrition.templates') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                {{ __('Cancel') }}
                            </a>
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
                                            <button class="nav-link active" id="day1-breakfast-tab" data-bs-target="#day1-breakfast" type="button">
                                                <i class="fas fa-coffee me-1"></i>
                                                {{ __('Breakfast') }}
                                                <span class="badge bg-primary ms-1" id="day1-breakfast-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-lunch-tab" data-bs-target="#day1-lunch" type="button">
                                                <i class="fas fa-sun me-1"></i>
                                                {{ __('Lunch') }}
                                                <span class="badge bg-primary ms-1" id="day1-lunch-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-dinner-tab" data-bs-target="#day1-dinner" type="button">
                                                <i class="fas fa-moon me-1"></i>
                                                {{ __('Dinner') }}
                                                <span class="badge bg-primary ms-1" id="day1-dinner-calories">0 cal</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="day1-snacks-tab" data-bs-target="#day1-snacks" type="button">
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

                        <!-- Nutrition Summary -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ __('Daily Average Nutrition Summary') }}</h6>
                                        <small class="text-muted">{{ __('Average daily nutrition across all 7 days') }}</small>
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <div class="h5 mb-0" id="current-calories">0</div>
                                                <small class="text-muted">{{ __('Calories') }}</small>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-success" id="calories-progress" style="width: 0%"></div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 mb-0" id="current-protein">0g</div>
                                                <small class="text-muted">{{ __('Protein') }}</small>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-info" id="protein-progress" style="width: 0%"></div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 mb-0" id="current-carbs">0g</div>
                                                <small class="text-muted">{{ __('Carbs') }}</small>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-warning" id="carbs-progress" style="width: 0%"></div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="h5 mb-0" id="current-fat">0g</div>
                                                <small class="text-muted">{{ __('Fat') }}</small>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-danger" id="fat-progress" style="width: 0%"></div>
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
                        <label for="food-language" class="form-label">{{ __('Language') }}</label>
                        <select class="form-select" id="food-language">
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

                <!-- Food Group Filter -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="food-group-filter" class="form-label">{{ __('Food Group') }}</label>
                        <select class="form-select" id="food-group-filter">
                            <option value="">{{ __('All Food Groups') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Food Results -->
                <div class="row">
                    <div class="col-12">
                        <div id="food-results" class="row">
                            <div class="col-12 text-center text-muted py-4">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p>{{ __('Search for foods to add to your meal plan') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Food Details -->
                <div id="selected-food-details" style="display: none;">
                    <hr>
                    <h6>{{ __('Selected Food') }}: <span id="selected-food-name"></span></h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label for="food-quantity" class="form-label">{{ __('Quantity') }}</label>
                            <input type="number" class="form-control" id="food-quantity" value="100" min="1" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label for="food-unit" class="form-label">{{ __('Unit') }}</label>
                            <select class="form-select" id="food-unit">
                                <option value="g">{{ __('Grams') }}</option>
                                <option value="kg">{{ __('Kilograms') }}</option>
                                <option value="ml">{{ __('Milliliters') }}</option>
                                <option value="l">{{ __('Liters') }}</option>
                                <option value="cup">{{ __('Cup') }}</option>
                                <option value="tbsp">{{ __('Tablespoon') }}</option>
                                <option value="tsp">{{ __('Teaspoon') }}</option>
                                <option value="piece">{{ __('Piece') }}</option>
                                <option value="slice">{{ __('Slice') }}</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Nutrition Preview') }}</label>
                            <div class="small">
                                <div>{{ __('Calories') }}: <span id="preview-calories">0</span></div>
                                <div>{{ __('Protein') }}: <span id="preview-protein">0g</span></div>
                                <div>{{ __('Carbs') }}: <span id="preview-carbs">0g</span></div>
                                <div>{{ __('Fat') }}: <span id="preview-fat">0g</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <label for="preparation-notes" class="form-label">{{ __('Preparation Notes') }}</label>
                            <input type="text" class="form-control" id="preparation-notes" placeholder="{{ __('e.g., grilled, steamed, raw...') }}">
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

@push('styles')
<style>
/* Day Navigation Styling */
#dayTabs .nav-link {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    min-width: 80px;
    text-align: center;
    transition: all 0.3s ease;
}

#dayTabs .nav-link:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

#dayTabs .nav-link.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

#dayTabs .nav-link.active .badge {
    background-color: white !important;
    color: #0d6efd !important;
}

/* Responsive day navigation */
@media (max-width: 768px) {
    #dayTabs {
        flex-wrap: wrap;
    }

    #dayTabs .nav-item {
        flex: 1 1 calc(25% - 0.5rem);
        margin-bottom: 0.5rem;
    }

    #dayTabs .nav-link {
        font-size: 0.875rem;
        padding: 0.5rem 0.25rem;
        min-width: auto;
    }
}

@media (max-width: 576px) {
    #dayTabs .nav-item {
        flex: 1 1 calc(33.333% - 0.5rem);
    }

    #dayTabs .nav-link {
        font-size: 0.75rem;
        padding: 0.375rem 0.25rem;
    }
}

/* Meal tabs styling within days */
.day-content .nav-tabs .nav-link {
    border-radius: 6px 6px 0 0;
    margin-right: 2px;
}

.day-content .nav-tabs .nav-link.active {
    background-color: #f8f9fa;
    border-color: #dee2e6 #dee2e6 #f8f9fa;
}
</style>
@endpush

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

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeMealPlanning();
    updateNutritionTargets();
    updateDayNavigationButtons();
});

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

    // Search functionality
    document.getElementById('food-search').addEventListener('input', debounce(searchFoods, 300));
    document.getElementById('food-group-filter').addEventListener('change', searchFoods);
    document.getElementById('food-language').addEventListener('change', searchFoods);

    // Quantity and unit change handlers
    document.getElementById('food-quantity').addEventListener('input', updateNutritionPreview);
    document.getElementById('food-unit').addEventListener('change', updateNutritionPreview);

    // Add food to meal handler
    document.getElementById('add-food-to-meal').addEventListener('click', addFoodToMeal);

    // Target input handlers
    ['calories', 'protein', 'carbs', 'fat'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', updateNutritionTargets);
        }
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
}

// Load food groups
function loadFoodGroups() {
    fetch('/api/food-groups')
        .then(response => response.json())
        .then(groups => {
            const select = document.getElementById('food-group-filter');
            select.innerHTML = '<option value="">{{ __("All Food Groups") }}</option>';
            groups.forEach(group => {
                select.innerHTML += `<option value="${group.id}">${group.name}</option>`;
            });
        })
        .catch(error => console.error('Error loading food groups:', error));
}

// Search foods
function searchFoods() {
    const search = document.getElementById('food-search').value;
    const groupId = document.getElementById('food-group-filter').value;
    const language = document.getElementById('food-language').value;

    if (search.length < 2 && !groupId) {
        document.getElementById('food-results').innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>{{ __('Search for foods to add to your meal plan') }}</p>
            </div>
        `;
        return;
    }

    const params = new URLSearchParams({
        search: search,
        group_id: groupId,
        language: language,
        limit: 20
    });

    fetch(`/foods/search?${params}`)
        .then(response => response.json())
        .then(foods => displayFoodResults(foods))
        .catch(error => {
            console.error('Error searching foods:', error);
            document.getElementById('food-results').innerHTML = `
                <div class="col-12 text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>{{ __('Error loading foods. Please try again.') }}</p>
                </div>
            `;
        });
}

// Display food search results
function displayFoodResults(foods) {
    const resultsContainer = document.getElementById('food-results');

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
        // Get the appropriate name based on language
        const language = document.getElementById('food-language').value;
        let displayName = food.name;

        // Check if food has name_translations
        if (food.name_translations) {
            const translations = typeof food.name_translations === 'string'
                ? JSON.parse(food.name_translations)
                : food.name_translations;

            if (translations[language]) {
                displayName = translations[language];
            }
        }

        // Legacy support for old column names
        if (language === 'ar' && food.name_ar) {
            displayName = food.name_ar;
        } else if ((language === 'ku_bahdini' || language === 'ku_sorani') && food.name_ku) {
            displayName = food.name_ku;
        }

        html += `
            <div class="col-md-6 mb-2">
                <div class="card food-card h-100" style="cursor: pointer;" onclick="selectFood(${food.id}, '${food.name}', '${displayName}', ${food.calories}, ${food.protein}, ${food.carbohydrates}, ${food.fat})">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">${displayName}</h6>
                        <small class="text-muted">
                            ${food.calories} cal, ${food.protein}g protein, ${food.carbohydrates}g carbs, ${food.fat}g fat (per 100g)
                        </small>
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

// Update nutrition preview
function updateNutritionPreview() {
    if (!selectedFood) return;

    const quantity = parseFloat(document.getElementById('food-quantity').value) || 0;
    const unit = document.getElementById('food-unit').value;

    // Calculate multiplier based on unit
    let multiplier = quantity / 100; // Default for grams
    if (unit === 'kg') multiplier = quantity * 10;
    else if (unit === 'ml' || unit === 'l') multiplier = quantity / 100;
    else if (unit === 'cup') multiplier = quantity * 2.4;
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

    // Calculate multiplier based on unit
    let multiplier = quantity / 100;
    if (unit === 'kg') multiplier = quantity * 10;
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
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${food.displayName}</h6>
                                    <small class="text-muted">${food.quantity} ${food.unit}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">${food.calories} cal</div>
                                    <small class="text-muted">${food.protein}g protein, ${food.carbs}g carbs, ${food.fat}g fat</small>
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

// Update nutrition summary
function updateNutritionSummary() {
    let totalCalories = 0, totalProtein = 0, totalCarbs = 0, totalFat = 0;

    // Calculate totals from all days and meals
    Object.values(weeklyMealFoods).forEach(dayMeals => {
        Object.values(dayMeals).forEach(foods => {
            foods.forEach(food => {
                totalCalories += food.calories;
                totalProtein += food.protein;
                totalCarbs += food.carbs;
                totalFat += food.fat;
            });
        });
    });

    // Calculate daily averages
    const avgCalories = Math.round(totalCalories / 7);
    const avgProtein = Math.round((totalProtein / 7) * 10) / 10;
    const avgCarbs = Math.round((totalCarbs / 7) * 10) / 10;
    const avgFat = Math.round((totalFat / 7) * 10) / 10;

    // Update display
    document.getElementById('current-calories').textContent = avgCalories;
    document.getElementById('current-protein').textContent = avgProtein + 'g';
    document.getElementById('current-carbs').textContent = avgCarbs + 'g';
    document.getElementById('current-fat').textContent = avgFat + 'g';

    // Update progress bars
    updateNutritionTargets();
}

// Update nutrition targets and progress
function updateNutritionTargets() {
    const targetCalories = parseFloat(document.getElementById('calories')?.value) || 0;
    const targetProtein = parseFloat(document.getElementById('protein')?.value) || 0;
    const targetCarbs = parseFloat(document.getElementById('carbs')?.value) || 0;
    const targetFat = parseFloat(document.getElementById('fat')?.value) || 0;

    const currentCalories = parseFloat(document.getElementById('current-calories')?.textContent) || 0;
    const currentProtein = parseFloat(document.getElementById('current-protein')?.textContent) || 0;
    const currentCarbs = parseFloat(document.getElementById('current-carbs')?.textContent) || 0;
    const currentFat = parseFloat(document.getElementById('current-fat')?.textContent) || 0;

    // Update progress bars
    if (targetCalories > 0) {
        const caloriesProgress = Math.min((currentCalories / targetCalories) * 100, 100);
        document.getElementById('calories-progress').style.width = caloriesProgress + '%';
    }

    if (targetProtein > 0) {
        const proteinProgress = Math.min((currentProtein / targetProtein) * 100, 100);
        document.getElementById('protein-progress').style.width = proteinProgress + '%';
    }

    if (targetCarbs > 0) {
        const carbsProgress = Math.min((currentCarbs / targetCarbs) * 100, 100);
        document.getElementById('carbs-progress').style.width = carbsProgress + '%';
    }

    if (targetFat > 0) {
        const fatProgress = Math.min((currentFat / targetFat) * 100, 100);
        document.getElementById('fat-progress').style.width = fatProgress + '%';
    }
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
            <p>{{ __('Search for foods to add to your meal plan') }}</p>
        </div>
    `;
}

// Debounce function
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

// Form submission handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Add weekly meal data to form
            const weeklyMealData = JSON.stringify(weeklyMealFoods);
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'weekly_meal_data';
            hiddenInput.value = weeklyMealData;
            this.appendChild(hiddenInput);
        });
    }
});
</script>
@endpush
@endsection
