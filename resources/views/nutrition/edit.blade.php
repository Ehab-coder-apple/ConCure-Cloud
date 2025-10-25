@extends('layouts.app')

@section('page-title', __('Edit Nutrition Plan') . ' - ' . $dietPlan->title)

@push('styles')
<style>
.table td, .table th {
    color: #333 !important;
}
.table .badge {
    color: #fff !important;
}
.meal-section {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}
.meal-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}
.food-item {
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}
</style>
@endpush

@section('content')
<div class="container" style="margin-top: 80px;">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit text-warning"></i>
                        {{ __('Edit Nutrition Plan') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Editing plan:') }} {{ $dietPlan->title }}</p>
                    <small class="text-muted">{{ __('Plan Number:') }} {{ $dietPlan->plan_number }}</small>
                </div>
                <div>
                    <a href="{{ route('nutrition.show', $dietPlan) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Plan') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('nutrition.update', $dietPlan) }}" method="POST" id="nutrition-form">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Plan Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
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
                                <select class="form-select @error('patient_id') is-invalid @enderror"
                                        id="patient_id" name="patient_id" required>
                                    <option value="">{{ __('Select Patient') }}</option>
                                    @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $dietPlan->patient_id) == $patient->id ? 'selected' : '' }}>
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
                                       id="title" name="title" value="{{ old('title', $dietPlan->title) }}" required
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
                                      placeholder="{{ __('Describe the nutrition plan objectives and approach...') }}">{{ old('description', $dietPlan->description) }}</textarea>
                            @error('description')
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
                            {{ __('Daily Nutritional Targets') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                            <div>
                                <label for="activity_level_calc" class="form-label mb-1 small">{{ __('Activity Level') }}</label>
                                <select class="form-select form-select-sm" id="activity_level_calc" style="min-width:220px">
                                    <option value="sedentary">{{ __('Sedentary (little/no exercise)') }}</option>
                                    <option value="light" selected>{{ __('Light (1-3 days/week)') }}</option>
                                    <option value="moderate">{{ __('Moderate (3-5 days/week)') }}</option>
                                    <option value="active">{{ __('Active (6-7 days/week)') }}</option>
                                    <option value="very_active">{{ __('Very Active (physical job)') }}</option>
                                </select>
                            </div>
                            <button type="button" id="btn-auto-calc-targets" class="btn btn-sm btn-primary mt-4">
                                <i class="fas fa-calculator me-1"></i>{{ __('Auto-calculate from patient') }}
                            </button>
                            <small class="text-muted ms-1">{{ __('Uses age, gender, height, weight + goal to split calories, protein, carbs, and fat.') }}</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_calories" class="form-label">{{ __('Target Calories') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_calories') is-invalid @enderror"
                                           id="target_calories" name="target_calories"
                                           value="{{ old('target_calories', $dietPlan->target_calories) }}"
                                           min="500" max="5000" step="50"
                                           placeholder="{{ __('e.g., 2000') }}">
                                    <span class="input-group-text">kcal</span>
                                    @error('target_calories')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_protein" class="form-label">{{ __('Target Protein') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_protein') is-invalid @enderror"
                                           id="target_protein" name="target_protein"
                                           value="{{ old('target_protein', $dietPlan->target_protein) }}"
                                           min="0" max="500" step="5"
                                           placeholder="{{ __('e.g., 150') }}">
                                    <span class="input-group-text">g</span>
                                    @error('target_protein')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_carbs" class="form-label">{{ __('Target Carbohydrates') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_carbs') is-invalid @enderror"
                                           id="target_carbs" name="target_carbs"
                                           value="{{ old('target_carbs', $dietPlan->target_carbs) }}"
                                           min="0" max="1000" step="10"
                                           placeholder="{{ __('e.g., 250') }}">
                                    <span class="input-group-text">g</span>
                                    @error('target_carbs')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_fat" class="form-label">{{ __('Target Fat') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control @error('target_fat') is-invalid @enderror"
                                           id="target_fat" name="target_fat"
                                           value="{{ old('target_fat', $dietPlan->target_fat) }}"
                                           min="0" max="300" step="5"
                                           placeholder="{{ __('e.g., 70') }}">
                                    <span class="input-group-text">g</span>
                                    @error('target_fat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                      placeholder="{{ __('Detailed instructions for following the nutrition plan...') }}">{{ old('instructions', $dietPlan->instructions) }}</textarea>
                            @error('instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="restrictions" class="form-label">{{ __('Dietary Restrictions') }}</label>
                            <textarea class="form-control @error('restrictions') is-invalid @enderror"
                                      id="restrictions" name="restrictions" rows="3"
                                      placeholder="{{ __('List any dietary restrictions, allergies, or foods to avoid...') }}">{{ old('restrictions', $dietPlan->restrictions) }}</textarea>
                            @error('restrictions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Current Meals -->
                @if($dietPlan->meals->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-utensils"></i>
                            {{ __('Current Meals') }}
                            <span class="badge badge-info ms-2">{{ $dietPlan->meals->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ __('This plan currently has') }} <strong>{{ $dietPlan->meals->count() }}</strong> {{ __('meals configured.') }}
                            {{ __('To modify individual meals and foods, use the detailed nutrition plan editor.') }}
                        </div>

                        @php
                            $mealsByDay = $dietPlan->getMealsByDay();
                        @endphp

                        @foreach($mealsByDay as $day => $meals)
                        <div class="meal-section">
                            <div class="meal-header">
                                <strong>{{ __('Day') }} {{ $day }}</strong>
                            </div>
                            <div class="p-3">
                                @foreach($meals as $meal)
                                <div class="food-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $meal->meal_name_display }}</h6>
                                            @if($meal->suggested_time)
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> {{ $meal->suggested_time_formatted }}
                                                </small>
                                            @endif
                                            @if($meal->instructions)
                                                <p class="mb-2 text-muted small">{{ $meal->instructions }}</p>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                {{ number_format($meal->total_calories, 0) }} kcal
                                            </small>
                                        </div>
                                    </div>

                                    @if($meal->foods->count() > 0)
                                    <div class="mt-2">
                                        <small class="text-muted">{{ __('Foods:') }}</small>
                                        <ul class="list-unstyled mb-0 ms-3">
                                            @foreach($meal->foods as $food)
                                            <li class="small">
                                                {{ $food->food_name_display }} - {{ $food->quantity_formatted }}
                                                <span class="text-muted">({{ number_format($food->calories, 0) }} kcal)</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="mt-3">
                            <a href="{{ route('nutrition.create.enhanced') }}?edit={{ $dietPlan->id }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i>
                                {{ __('Edit Detailed Meal Plan') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Plan Details Sidebar -->
            <div class="col-lg-4">
                <!-- Plan Goal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-target"></i>
                            {{ __('Plan Goal') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="goal" class="form-label">{{ __('Primary Goal') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('goal') is-invalid @enderror"
                                    id="goal" name="goal" required>
                                <option value="">{{ __('Select Goal') }}</option>
                                @foreach(\App\Models\DietPlan::GOALS as $key => $label)
                                <option value="{{ $key }}" {{ old('goal', $dietPlan->goal) == $key ? 'selected' : '' }}>
                                    {{ __($label) }}
                                </option>
                                @endforeach
                            </select>
                            @error('goal')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="goal_description" class="form-label">{{ __('Goal Description') }}</label>
                            <textarea class="form-control @error('goal_description') is-invalid @enderror"
                                      id="goal_description" name="goal_description" rows="3"
                                      placeholder="{{ __('Describe specific goals and expectations...') }}">{{ old('goal_description', $dietPlan->goal_description) }}</textarea>
                            @error('goal_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Plan Timeline -->
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
                                   id="start_date" name="start_date"
                                   value="{{ old('start_date', $dietPlan->start_date?->format('Y-m-d')) }}" required>
                            @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date"
                                   value="{{ old('end_date', $dietPlan->end_date?->format('Y-m-d')) }}">
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('Leave empty for ongoing plans') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="duration_days" class="form-label">{{ __('Duration (Days)') }}</label>
                            <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                   id="duration_days" name="duration_days"
                                   value="{{ old('duration_days', $dietPlan->duration_days) }}"
                                   min="1" max="365"
                                   placeholder="{{ __('e.g., 30') }}">
                            @error('duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Plan Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i>
                            {{ __('Plan Status') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status" required>
                                @foreach(\App\Models\DietPlan::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $dietPlan->status) == $key ? 'selected' : '' }}>
                                    {{ __($label) }}
                                </option>
                                @endforeach
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="{{ __('Additional notes about the plan...') }}">{{ old('notes', $dietPlan->notes) }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i>
                                {{ __('Update Plan') }}
                            </button>
                            <a href="{{ route('nutrition.show', $dietPlan) }}" class="btn btn-outline-secondary">
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

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate end date when duration changes
    $('#duration_days').on('change', function() {
        const startDate = $('#start_date').val();
        const duration = parseInt($(this).val());

        if (startDate && duration > 0) {
            const start = new Date(startDate);
            const end = new Date(start.getTime() + (duration * 24 * 60 * 60 * 1000));
            const endDateString = end.toISOString().split('T')[0];
            $('#end_date').val(endDateString);
        }
    });

    // Auto-calculate duration when dates change
    $('#start_date, #end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            $('#duration_days').val(diffDays);
        }
    });

    // Form validation
    $('#nutrition-form').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['patient_id', 'title', 'goal', 'start_date', 'status'];

        requiredFields.forEach(function(field) {
            const input = $('#' + field);
            if (!input.val()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });

        // Validate dates
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());

        if ($('#end_date').val() && endDate <= startDate) {
            $('#end_date').addClass('is-invalid');
            alert('{{ __("End date must be after start date") }}');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });

    // Remove validation classes on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Goal-based suggestions
    $('#goal').on('change', function() {
        const goal = $(this).val();

        switch(goal) {
            case 'weight_loss':
                $('#target_calories').val('1500');
                $('#target_protein').val('120');
                $('#target_carbs').val('150');
                $('#target_fat').val('50');
                break;
            case 'weight_gain':
                $('#target_calories').val('2500');
                $('#target_protein').val('150');
                $('#target_carbs').val('300');
                $('#target_fat').val('80');
                break;
            case 'muscle_gain':
                $('#target_calories').val('2200');
                $('#target_protein').val('180');
                $('#target_carbs').val('220');
                $('#target_fat').val('70');
                break;
            case 'maintenance':
                $('#target_calories').val('2000');
                $('#target_protein').val('100');
                $('#target_carbs').val('250');
                $('#target_fat').val('65');
                break;
        }
    });

    // Auto-calc daily targets from patient profile
    function weeklyGoalBy(goal) {
        switch(goal) {
            case 'weight_loss': return -0.5; // kg/week
            case 'weight_gain': return 0.5;
            case 'muscle_gain': return 0.25;
            default: return 0; // maintenance/diabetic/other
        }
    }

    // Clamp and round value to the input's step/min/max
    function clampAndRoundToStep(value, $input) {
        let v = parseFloat(value);
        if (isNaN(v)) return '';
        const min = parseFloat($input.attr('min'));
        const max = parseFloat($input.attr('max'));
        let stepAttr = $input.attr('step') || '1';
        let step = (stepAttr === 'any') ? 1 : parseFloat(stepAttr);
        if (!step || isNaN(step)) step = 1;
        let rounded = Math.round(v / step) * step;
        if (!isNaN(min)) rounded = Math.max(rounded, min);
        if (!isNaN(max)) rounded = Math.min(rounded, max);
        const decimals = ((step.toString().split('.')[1]) || '').length;
        return decimals > 0 ? rounded.toFixed(decimals) : Math.round(rounded).toString();
    }

    // Normalize any pre-filled values to the nearest valid step on load
    ['#target_calories','#target_protein','#target_carbs','#target_fat'].forEach(function(sel){
        const $el = $(sel);
        if ($el.length && $el.val()) {
            $el.val(clampAndRoundToStep($el.val(), $el));
        }
    });



    $('#btn-auto-calc-targets').on('click', function() {
        const patientId = $('#patient_id').val();
        const goal = $('#goal').val();
        const activity = $('#activity_level_calc').val() || 'light';
        if (!patientId) { alert('{{ __('Please select a patient first') }}'); return; }
        if (!goal) { alert('{{ __('Please choose a goal first') }}'); return; }

        const payload = {
            patient_id: patientId,
            goal: goal,
            activity_level: activity,
            weekly_weight_goal: weeklyGoalBy(goal)
        };

        // Visual feedback
        const btn = $(this);
        const prev = btn.html();
        btn.prop('disabled', true).html('<span class=\"spinner-border spinner-border-sm me-2\"></span>{{ __('Calculating') }}...');

        fetch('/nutrition/calculate-calories', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
            },
            body: JSON.stringify(payload)
        }).then(r => r.json())
          .then(data => {
              if (!data || data.success === false) throw data;
              const $cal = $('#target_calories');
              const $pro = $('#target_protein');
              const $car = $('#target_carbs');
              const $fat = $('#target_fat');
              $cal.val(clampAndRoundToStep(data.calories.target_calories, $cal));
              $pro.val(clampAndRoundToStep(data.macronutrients.protein.grams, $pro));
              $car.val(clampAndRoundToStep(data.macronutrients.carbs.grams, $car));
              $fat.val(clampAndRoundToStep(data.macronutrients.fat.grams, $fat));
          })
          .catch(err => {
              console.error('Auto-calc failed', err);
              if (err && err.message) alert(err.message);
              else alert('{{ __('Unable to calculate targets. Please ensure patient profile has weight, height, date of birth and gender.') }}');
          })
          .finally(() => { btn.prop('disabled', false).html(prev); });
    });


    // Auto-run calculation when patient/goal/activity changes
    $('#patient_id, #goal, #activity_level_calc').on('change', function() {
        $('#btn-auto-calc-targets').trigger('click');
    });

    // If targets are empty on load, try auto-calc once
    if (!$('#target_calories').val()) {
        setTimeout(() => { if (!$('#target_calories').val()) { $('#btn-auto-calc-targets').click(); } }, 300);
    }

});
</script>
@endpush
