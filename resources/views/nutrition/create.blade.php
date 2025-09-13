@extends('layouts.app')

@section('page-title', __('Create Nutrition Plan'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle text-success"></i>
                        {{ __('Create Nutrition Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Design a personalized nutrition plan for your patient') }}</p>
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

    <form action="{{ route('nutrition.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
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
                                       id="title" name="title" value="{{ old('title') }}" required
                                       placeholder="{{ __('e.g., Weight Loss Plan for John Doe') }}">
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3"
                                      placeholder="{{ __('Describe the nutrition plan objectives and approach...') }}">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="goal" class="form-label">{{ __('Primary Goal') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('goal') is-invalid @enderror" id="goal" name="goal" required>
                                    <option value="">{{ __('Select Goal') }}</option>
                                    <option value="weight_loss" {{ old('goal') === 'weight_loss' ? 'selected' : '' }}>{{ __('Weight Loss') }}</option>
                                    <option value="weight_gain" {{ old('goal') === 'weight_gain' ? 'selected' : '' }}>{{ __('Weight Gain') }}</option>
                                    <option value="muscle_gain" {{ old('goal') === 'muscle_gain' ? 'selected' : '' }}>{{ __('Muscle Gain') }}</option>
                                    <option value="diabetic" {{ old('goal') === 'diabetic' ? 'selected' : '' }}>{{ __('Diabetic Management') }}</option>
                                    <option value="maintenance" {{ old('goal') === 'maintenance' ? 'selected' : '' }}>{{ __('Weight Maintenance') }}</option>
                                    <option value="health_improvement" {{ old('goal') === 'health_improvement' ? 'selected' : '' }}>{{ __('Health Improvement') }}</option>
                                    <option value="other" {{ old('goal') === 'other' ? 'selected' : '' }}>{{ __('Other') }}</option>
                                </select>
                                @error('goal')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="duration_days" class="form-label">{{ __('Duration (Days)') }}</label>
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror" 
                                       id="duration_days" name="duration_days" value="{{ old('duration_days') }}" 
                                       min="1" max="365" placeholder="{{ __('e.g., 30') }}">
                                @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="goal_description" class="form-label">{{ __('Goal Description') }}</label>
                            <textarea class="form-control @error('goal_description') is-invalid @enderror" 
                                      id="goal_description" name="goal_description" rows="2"
                                      placeholder="{{ __('Specific details about the goal...') }}">{{ old('goal_description') }}</textarea>
                            @error('goal_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Nutritional Targets -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bullseye"></i>
                            {{ __('Nutritional Targets') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_calories" class="form-label">{{ __('Daily Calories') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_calories') is-invalid @enderror" 
                                           id="target_calories" name="target_calories" value="{{ old('target_calories') }}" 
                                           min="500" max="5000" step="50" placeholder="{{ __('e.g., 2000') }}">
                                    <span class="input-group-text">kcal</span>
                                </div>
                                @error('target_calories')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_protein" class="form-label">{{ __('Daily Protein') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_protein') is-invalid @enderror" 
                                           id="target_protein" name="target_protein" value="{{ old('target_protein') }}" 
                                           min="0" max="500" step="5" placeholder="{{ __('e.g., 120') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                                @error('target_protein')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_carbs" class="form-label">{{ __('Daily Carbohydrates') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_carbs') is-invalid @enderror" 
                                           id="target_carbs" name="target_carbs" value="{{ old('target_carbs') }}" 
                                           min="0" max="1000" step="10" placeholder="{{ __('e.g., 250') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                                @error('target_carbs')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_fat" class="form-label">{{ __('Daily Fat') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_fat') is-invalid @enderror" 
                                           id="target_fat" name="target_fat" value="{{ old('target_fat') }}" 
                                           min="0" max="300" step="5" placeholder="{{ __('e.g., 70') }}">
                                    <span class="input-group-text">g</span>
                                </div>
                                @error('target_fat')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions and Restrictions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-clipboard-list"></i>
                            {{ __('Instructions & Restrictions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="instructions" class="form-label">{{ __('Instructions') }}</label>
                            <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                      id="instructions" name="instructions" rows="4"
                                      placeholder="{{ __('Detailed instructions for following the nutrition plan...') }}">{{ old('instructions') }}</textarea>
                            @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="restrictions" class="form-label">{{ __('Dietary Restrictions') }}</label>
                            <textarea class="form-control @error('restrictions') is-invalid @enderror" 
                                      id="restrictions" name="restrictions" rows="3"
                                      placeholder="{{ __('Foods to avoid, allergies, medical restrictions...') }}">{{ old('restrictions') }}</textarea>
                            @error('restrictions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Timeline -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt"></i>
                            {{ __('Plan Timeline') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">{{ __('Start Date') }} <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   id="end_date" name="end_date" value="{{ old('end_date') }}">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Leave empty for ongoing plans') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Templates -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-magic"></i>
                            {{ __('Quick Templates') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">

                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyMuscleGainTemplate()">
                                <i class="fas fa-dumbbell me-1"></i>
                                {{ __('Muscle Gain') }}
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="applyDiabeticTemplate()">
                                <i class="fas fa-heartbeat me-1"></i>
                                {{ __('Diabetic') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Create Nutrition Plan') }}
                            </button>
                            <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
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

@push('scripts')
<script>
// Template functions

function applyMuscleGainTemplate() {
    document.getElementById('goal').value = 'muscle_gain';
    document.getElementById('target_calories').value = '2500';
    document.getElementById('target_protein').value = '180';
    document.getElementById('target_carbs').value = '300';
    document.getElementById('target_fat').value = '80';
    document.getElementById('duration_days').value = '60';
    document.getElementById('instructions').value = 'Eat protein with every meal. Time carbohydrates around workouts. Stay hydrated and get adequate rest.';
    document.getElementById('restrictions').value = 'Limit processed foods and empty calories. Focus on whole foods and lean proteins.';
}

function applyDiabeticTemplate() {
    document.getElementById('goal').value = 'diabetic';
    document.getElementById('target_calories').value = '1800';
    document.getElementById('target_protein').value = '100';
    document.getElementById('target_carbs').value = '180';
    document.getElementById('target_fat').value = '60';
    document.getElementById('duration_days').value = '90';
    document.getElementById('instructions').value = 'Monitor blood sugar regularly. Eat at consistent times. Choose complex carbohydrates over simple sugars.';
    document.getElementById('restrictions').value = 'Avoid sugary foods, refined carbohydrates, and high-glycemic foods. Limit saturated fats.';
}

// Auto-calculate end date when duration changes
document.getElementById('duration_days').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    const duration = parseInt(this.value);
    
    if (startDate && duration) {
        const start = new Date(startDate);
        const end = new Date(start.getTime() + (duration * 24 * 60 * 60 * 1000));
        document.getElementById('end_date').value = end.toISOString().split('T')[0];
    }
});
</script>
@endpush
@endsection
