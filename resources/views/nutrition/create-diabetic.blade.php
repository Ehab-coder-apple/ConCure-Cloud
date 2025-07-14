@extends('layouts.app')

@section('page-title', __('Create Diabetic Nutrition Plan'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-heartbeat text-warning"></i>
                        {{ __('Create Diabetic Nutrition Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Design a specialized diabetic nutrition plan for your patient') }}</p>
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
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        {{ __('Diabetic Template Information') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-warning">{{ $template['title'] }}</h6>
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
                                        <div class="h4 text-warning mb-1">{{ $template['target_calories'] }}</div>
                                        <small class="text-muted">{{ __('Calories/day') }}</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-warning mb-1">{{ $template['duration_days'] }}</div>
                                        <small class="text-muted">{{ __('Days') }}</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-warning mb-1">{{ $template['target_protein'] }}g</div>
                                        <small class="text-muted">{{ __('Protein') }}</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded">
                                        <div class="h4 text-warning mb-1">{{ $template['target_carbs'] }}g</div>
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

    <form action="{{ route('nutrition.store') }}" method="POST">
        @csrf
        <input type="hidden" name="template_type" value="diabetic">
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

                        <div class="alert alert-warning">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ __('These values are pre-filled based on the diabetic template. Please adjust them according to the patient\'s specific diabetic condition and medical requirements.') }}
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Diabetic Plan') }}
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
    </form>
</div>
@endsection
