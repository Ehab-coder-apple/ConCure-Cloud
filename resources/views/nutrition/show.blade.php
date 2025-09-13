@extends('layouts.app')

@section('page-title', $dietPlan->title)

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-apple-alt text-success"></i>
                        {{ $dietPlan->title }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Plan Number:') }} {{ $dietPlan->plan_number }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('nutrition.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Plans') }}
                    </a>
                    @if($dietPlan->canBeModified())
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Plan') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('nutrition.edit.enhanced', $dietPlan) }}">
                                <i class="fas fa-utensils me-2"></i>
                                {{ __('Enhanced Editor') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('nutrition.edit', $dietPlan) }}">
                                <i class="fas fa-file-alt me-2"></i>
                                {{ __('Basic Editor') }}
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('nutrition.create.enhanced') }}?edit={{ $dietPlan->id }}" class="btn btn-success">
                        <i class="fas fa-utensils me-1"></i>
                        {{ __('Manage Meals') }}
                    </a>
                    @endif
                    <a href="{{ route('nutrition.weight-tracking', $dietPlan) }}" class="btn btn-info">
                        <i class="fas fa-weight me-1"></i>
                        {{ __('Weight Tracking') }}
                    </a>
                    <button type="button" class="btn btn-success" onclick="shareOnWhatsApp()">
                        <i class="fab fa-whatsapp me-1"></i>
                        {{ __('Send via WhatsApp') }}
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i>
                            {{ __('Export') }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header">{{ __('Daily Format') }}</h6></li>
                            <li><a class="dropdown-item" href="{{ route('nutrition.pdf', $dietPlan) }}">
                                <i class="fas fa-file-pdf me-2"></i>
                                {{ __('Daily PDF') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('nutrition.word', $dietPlan) }}">
                                <i class="fas fa-file-word me-2"></i>
                                {{ __('Daily Word') }}
                            </a></li>

                        </ul>
                    </div>
                    <a href="{{ route('nutrition.word', $dietPlan) }}" class="btn btn-primary">
                        <i class="fas fa-file-word me-1"></i>
                        {{ __('Download Word') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Plan Overview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        {{ __('Plan Overview') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Patient:') }}</strong><br>
                            <span class="text-primary">{{ $dietPlan->patient->first_name }} {{ $dietPlan->patient->last_name }}</span><br>
                            <small class="text-muted">{{ $dietPlan->patient->patient_id }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Doctor:') }}</strong><br>
                            <span class="text-primary">Dr. {{ $dietPlan->doctor->first_name }} {{ $dietPlan->doctor->last_name }}</span>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Goal:') }}</strong><br>
                            <span class="badge bg-info">{{ $dietPlan->goal_display }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>{{ __('Status:') }}</strong><br>
                            <span class="{{ $dietPlan->status_badge_class }}">{{ $dietPlan->status_display }}</span>
                        </div>
                    </div>
                    
                    @if($dietPlan->description)
                    <div class="mb-3">
                        <strong>{{ __('Description:') }}</strong><br>
                        <p class="mb-0">{{ $dietPlan->description }}</p>
                    </div>
                    @endif
                    
                    @if($dietPlan->goal_description)
                    <div class="mb-3">
                        <strong>{{ __('Goal Description:') }}</strong><br>
                        <p class="mb-0">{{ $dietPlan->goal_description }}</p>
                    </div>
                    @endif
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
                        @if($dietPlan->target_calories)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-primary mb-1">{{ number_format($dietPlan->target_calories) }}</h4>
                                <small class="text-muted">{{ __('Calories/day') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($dietPlan->target_protein)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success mb-1">{{ number_format($dietPlan->target_protein) }}g</h4>
                                <small class="text-muted">{{ __('Protein') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($dietPlan->target_carbs)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-warning mb-1">{{ number_format($dietPlan->target_carbs) }}g</h4>
                                <small class="text-muted">{{ __('Carbohydrates') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($dietPlan->target_fat)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info mb-1">{{ number_format($dietPlan->target_fat) }}g</h4>
                                <small class="text-muted">{{ __('Fat') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Weight Tracking Progress -->
            @if($dietPlan->initial_weight || $dietPlan->current_weight || $dietPlan->target_weight)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-weight text-primary"></i>
                        {{ __('Weight Management Progress') }}
                    </h6>
                    <a href="{{ route('nutrition.weight-tracking', $dietPlan) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-chart-line me-1"></i>
                        {{ __('View Details') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($dietPlan->initial_weight)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-info mb-1">{{ number_format($dietPlan->initial_weight, 1) }}</h4>
                                <small class="text-muted">{{ __('Initial Weight (kg)') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($dietPlan->current_weight)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-success mb-1">{{ number_format($dietPlan->current_weight, 1) }}</h4>
                                <small class="text-muted">{{ __('Current Weight (kg)') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($dietPlan->target_weight)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="text-warning mb-1">{{ number_format($dietPlan->target_weight, 1) }}</h4>
                                <small class="text-muted">{{ __('Target Weight (kg)') }}</small>
                            </div>
                        </div>
                        @endif

                        @if($dietPlan->total_weight_change)
                        <div class="col-md-3 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="{{ $dietPlan->total_weight_change > 0 ? 'text-primary' : 'text-danger' }} mb-1">
                                    {{ $dietPlan->total_weight_change > 0 ? '+' : '' }}{{ number_format($dietPlan->total_weight_change, 1) }}
                                </h4>
                                <small class="text-muted">{{ __('Weight Change (kg)') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- BMI Information -->
                    @if($dietPlan->initial_bmi || $dietPlan->current_bmi)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2">{{ __('BMI Progress') }}</h6>
                        </div>
                        @if($dietPlan->initial_bmi)
                        <div class="col-md-4 mb-2">
                            <strong>{{ __('Initial BMI') }}:</strong>
                            <span class="badge bg-secondary">{{ number_format($dietPlan->initial_bmi, 1) }}</span>
                            <small class="text-muted">
                                @if($dietPlan->initial_bmi < 18.5) ({{ __('Underweight') }})
                                @elseif($dietPlan->initial_bmi < 25) ({{ __('Normal') }})
                                @elseif($dietPlan->initial_bmi < 30) ({{ __('Overweight') }})
                                @else ({{ __('Obese') }})
                                @endif
                            </small>
                        </div>
                        @endif

                        @if($dietPlan->current_bmi)
                        <div class="col-md-4 mb-2">
                            <strong>{{ __('Current BMI') }}:</strong>
                            <span class="badge bg-success">{{ number_format($dietPlan->current_bmi, 1) }}</span>
                            <small class="text-muted">
                                @if($dietPlan->current_bmi < 18.5) ({{ __('Underweight') }})
                                @elseif($dietPlan->current_bmi < 25) ({{ __('Normal') }})
                                @elseif($dietPlan->current_bmi < 30) ({{ __('Overweight') }})
                                @else ({{ __('Obese') }})
                                @endif
                            </small>
                        </div>
                        @endif

                        @if($dietPlan->target_bmi)
                        <div class="col-md-4 mb-2">
                            <strong>{{ __('Target BMI') }}:</strong>
                            <span class="badge bg-warning">{{ number_format($dietPlan->target_bmi, 1) }}</span>
                            <small class="text-muted">
                                @if($dietPlan->target_bmi < 18.5) ({{ __('Underweight') }})
                                @elseif($dietPlan->target_bmi < 25) ({{ __('Normal') }})
                                @elseif($dietPlan->target_bmi < 30) ({{ __('Overweight') }})
                                @else ({{ __('Obese') }})
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Progress Bar -->
                    @if($dietPlan->weight_progress_percentage)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>{{ __('Progress to Goal') }}</h6>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ min($dietPlan->weight_progress_percentage, 100) }}%">
                                    {{ number_format($dietPlan->weight_progress_percentage, 1) }}%
                                </div>
                            </div>
                            @if($dietPlan->isWeightGoalAchieved())
                            <div class="text-center mt-2">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-trophy me-1"></i>
                                    {{ __('Goal Achieved!') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Instructions -->
            @if($dietPlan->instructions)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-list"></i>
                        {{ __('Instructions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $dietPlan->instructions }}</p>
                </div>
            </div>
            @endif

            <!-- Restrictions -->
            @if($dietPlan->restrictions)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        {{ __('Dietary Restrictions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $dietPlan->restrictions }}</p>
                </div>
            </div>
            @endif

            <!-- Meal Plan -->
            @if($dietPlan->meals->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-utensils"></i>
                        @if($dietPlan->meals->where('is_option_based', true)->count() > 0)
                            {{ __('Flexible Meal Plan') }}
                            <small class="text-muted">{{ __('- Choose from the options below') }}</small>
                        @else
                            {{ __('Daily Meal Plan') }}
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $isFlexiblePlan = $dietPlan->meals->where('is_option_based', true)->count() > 0;

                        if ($isFlexiblePlan) {
                            // Group by meal type and option for flexible plans
                            $mealsByType = $dietPlan->meals->where('is_option_based', true)->groupBy('meal_type');
                        } else {
                            // Group by day for traditional plans
                            $mealsByDay = $dietPlan->meals->groupBy('day_number');
                        }
                    @endphp

                    @if($isFlexiblePlan)
                        {{-- Flexible meal plan display --}}
                        @foreach($mealsByType as $mealType => $meals)
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-{{ $mealType === 'breakfast' ? 'coffee' : ($mealType === 'lunch' ? 'sun' : ($mealType === 'dinner' ? 'moon' : 'cookie-bite')) }} me-2"></i>
                                {{ ucfirst($mealType === 'snack_1' ? 'snacks' : $mealType) }} Options
                            </h6>

                            @foreach($meals->sortBy('option_number') as $meal)
                            <div class="meal-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-list-ol me-1 text-muted"></i>
                                            {{ $meal->option_description ?: 'Option ' . $meal->option_number }}
                                        </h6>
                                        @if($meal->instructions)
                                        <p class="text-muted small mb-2">{{ $meal->instructions }}</p>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $meal->meal_type === 'breakfast' ? 'warning' : ($meal->meal_type === 'lunch' ? 'success' : ($meal->meal_type === 'dinner' ? 'primary' : 'info')) }}">
                                        {{ ucfirst($meal->meal_type === 'snack_1' ? 'snacks' : $meal->meal_type) }}
                                    </span>
                                </div>

                                @if($meal->foods->count() > 0)
                                <div class="foods-list">
                                    <div class="row">
                                        @foreach($meal->foods as $food)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $food->food_name }}</strong>
                                                    @if($food->preparation_notes)
                                                    <br><small class="text-muted">{{ $food->preparation_notes }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-light text-dark">
                                                    {{ $food->quantity }} {{ $food->unit }}
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    @else
                        {{-- Traditional daily meal plan display --}}
                        @foreach($mealsByDay as $dayNumber => $dayMeals)
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-calendar-day me-2"></i>
                                {{ __('Day') }} {{ $dayNumber }}
                            </h6>

                            @foreach($dayMeals->sortBy('suggested_time') as $meal)
                            <div class="meal-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-clock me-1 text-muted"></i>
                                            {{ $meal->suggested_time ? \Carbon\Carbon::parse($meal->suggested_time)->format('g:i A') : '' }}
                                            - {{ $meal->meal_name ?: ucfirst($meal->meal_type) }}
                                        </h6>
                                        @if($meal->instructions)
                                        <p class="text-muted small mb-2">{{ $meal->instructions }}</p>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $meal->meal_type === 'breakfast' ? 'warning' : ($meal->meal_type === 'lunch' ? 'success' : ($meal->meal_type === 'dinner' ? 'primary' : 'info')) }}">
                                        {{ ucfirst($meal->meal_type) }}
                                    </span>
                                </div>

                                @if($meal->foods->count() > 0)
                                <div class="foods-list">
                                    <div class="row">
                                        @foreach($meal->foods as $food)
                                        <div class="col-md-6 mb-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $food->food_name }}</strong>
                                                    @if($food->preparation_notes)
                                                    <br><small class="text-muted">{{ $food->preparation_notes }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-light text-dark">
                                                    {{ $food->quantity }} {{ $food->unit }}
                                                </span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Plan Details -->
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
                        <strong>{{ __('Start Date:') }}</strong><br>
                        <span class="text-primary">{{ \Carbon\Carbon::parse($dietPlan->start_date)->format('M d, Y') }}</span>
                    </div>
                    
                    @if($dietPlan->end_date)
                    <div class="mb-3">
                        <strong>{{ __('End Date:') }}</strong><br>
                        <span class="text-primary">{{ \Carbon\Carbon::parse($dietPlan->end_date)->format('M d, Y') }}</span>
                    </div>
                    @endif
                    
                    @if($dietPlan->duration_days)
                    <div class="mb-3">
                        <strong>{{ __('Duration:') }}</strong><br>
                        <span class="text-primary">{{ $dietPlan->duration_days }} {{ __('days') }}</span>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>{{ __('Created:') }}</strong><br>
                        <span class="text-muted">{{ $dietPlan->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    
                    @if($dietPlan->updated_at != $dietPlan->created_at)
                    <div class="mb-3">
                        <strong>{{ __('Last Updated:') }}</strong><br>
                        <span class="text-muted">{{ $dietPlan->updated_at->format('M d, Y g:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Progress -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i>
                        {{ __('Progress') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($dietPlan->start_date && $dietPlan->end_date)
                        @php
                            $startDate = \Carbon\Carbon::parse($dietPlan->start_date);
                            $endDate = \Carbon\Carbon::parse($dietPlan->end_date);
                            $today = \Carbon\Carbon::now();
                            $totalDays = $startDate->diffInDays($endDate);
                            $daysPassed = $startDate->diffInDays($today);
                            $progress = $totalDays > 0 ? min(100, max(0, ($daysPassed / $totalDays) * 100)) : 0;
                        @endphp
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>{{ __('Plan Progress') }}</small>
                                <small>{{ number_format($progress, 1) }}%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-primary">{{ max(0, $daysPassed) }}</div>
                                    <small class="text-muted">{{ __('Days Passed') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-info">{{ max(0, $totalDays - $daysPassed) }}</div>
                                    <small class="text-muted">{{ __('Days Remaining') }}</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('No end date specified for this plan.') }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i>
                        {{ __('Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($dietPlan->canBeModified())
                        <a href="{{ route('nutrition.edit', $dietPlan) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Plan') }}
                        </a>

                        <a href="{{ route('nutrition.create.enhanced') }}?edit={{ $dietPlan->id }}" class="btn btn-success">
                            <i class="fas fa-utensils me-1"></i>
                            {{ __('Manage Meals') }}
                        </a>
                        @endif
                        
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="shareOnWhatsApp()">
                            <i class="fab fa-whatsapp me-1"></i>
                            {{ __('Send via WhatsApp') }}
                        </button>

                        <a href="{{ route('nutrition.word', $dietPlan) }}" class="btn btn-primary w-100">
                            <i class="fas fa-file-word me-1"></i>
                            {{ __('Download Word') }}
                        </a>
                        
                        <a href="{{ route('nutrition.create') }}?patient_id={{ $dietPlan->patient_id }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Plan for Patient') }}
                        </a>
                        
                        <hr>
                        
                        @if($dietPlan->canBeModified())
                        <form action="{{ route('nutrition.destroy', $dietPlan) }}" method="POST" 
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this nutrition plan?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                {{ __('Delete Plan') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function shareOnWhatsApp() {
    // Get nutrition plan data
    const patientName = "{{ $dietPlan->patient->full_name }}";
    const planTitle = "{{ $dietPlan->title }}";
    const planNumber = "{{ $dietPlan->plan_number }}";
    const doctorName = "{{ $dietPlan->doctor ? $dietPlan->doctor->first_name . ' ' . $dietPlan->doctor->last_name : 'Unknown' }}";
    const targetCalories = "{{ $dietPlan->target_calories }}";
    const targetProtein = "{{ $dietPlan->target_protein }}";
    const targetCarbs = "{{ $dietPlan->target_carbs }}";
    const targetFat = "{{ $dietPlan->target_fat }}";

    // Check if this is a flexible meal plan
    const isFlexiblePlan = {{ $dietPlan->meals()->where('is_option_based', true)->exists() ? 'true' : 'false' }};

    // Build meal summary
    let mealSummary = "";

    if (isFlexiblePlan) {
        // Flexible meal plan format
        mealSummary += "\n🔄 *Flexible Meal Plan - Choose one option from each meal:*\n";

        @php
            // Group meals by type and option for flexible plans
            $mealsByType = [];
            $mealTypes = ['breakfast', 'lunch', 'dinner', 'snack_1'];
            $mealTypeNames = [
                'breakfast' => 'Breakfast',
                'lunch' => 'Lunch',
                'dinner' => 'Dinner',
                'snack_1' => 'Snack'
            ];

            foreach ($mealTypes as $mealType) {
                $mealsByType[$mealType] = [];
            }

            foreach ($dietPlan->meals->where('is_option_based', true) as $meal) {
                $mealType = $meal->meal_type;
                if (in_array($mealType, $mealTypes)) {
                    $mealsByType[$mealType][] = $meal;
                }
            }
        @endphp

        @foreach($mealTypes as $mealType)
            @if(count($mealsByType[$mealType]) > 0)
                mealSummary += "\n*{{ $mealTypeNames[$mealType] }} Options:*\n";
                @foreach($mealsByType[$mealType] as $index => $meal)
                    mealSummary += "📋 *Option {{ $meal->option_number }}:*\n";
                    @foreach($meal->foods as $mealFood)
                        mealSummary += "  • {{ $mealFood->food_name }} - {{ $mealFood->quantity }}{{ $mealFood->unit }}\n";
                    @endforeach
                    mealSummary += "\n";
                @endforeach
            @endif
        @endforeach

        mealSummary += "💡 *Instructions:* Choose one option from each meal type for each day. You can mix and match different options throughout the week for variety!\n";
    } else {
        // Regular meal plan format
        @foreach(['breakfast', 'lunch', 'dinner', 'snack'] as $mealType)
            @php $meals = $dietPlan->meals->where('meal_type', $mealType)->where('is_option_based', false); @endphp
            @if($meals->count() > 0)
                mealSummary += "\n*{{ ucfirst($mealType) }}:*\n";
                @foreach($meals as $meal)
                    @foreach($meal->foods as $mealFood)
                        mealSummary += "• {{ $mealFood->food_name }} - {{ $mealFood->quantity }}{{ $mealFood->unit }}\n";
                    @endforeach
                @endforeach
            @endif
        @endforeach
    }

    // Create WhatsApp message
    const message = `🍎 *Nutrition Plan*

👤 *Patient:* ${patientName}
📋 *Plan:* ${planTitle}
🔢 *Plan #:* ${planNumber}
👨‍⚕️ *Doctor:* Dr. ${doctorName}

📊 *Daily Targets:*
🔥 Calories: ${targetCalories} kcal
🥩 Protein: ${targetProtein}g
🍞 Carbs: ${targetCarbs}g
🥑 Fat: ${targetFat}g

🍽️ *Meal Plan:*${mealSummary}

📱 Generated by ConCure Clinic Management System`;

    // Encode message for URL
    const encodedMessage = encodeURIComponent(message);

    // Get patient's WhatsApp number if available
    const patientWhatsApp = "{{ $dietPlan->patient->whatsapp_phone ? preg_replace('/[^0-9]/', '', $dietPlan->patient->whatsapp_phone) : '' }}";

    // Create WhatsApp URL
    let whatsappUrl;
    if (patientWhatsApp) {
        whatsappUrl = `https://wa.me/${patientWhatsApp}?text=${encodedMessage}`;
    } else {
        whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
    }

    // Open WhatsApp
    window.open(whatsappUrl, '_blank');
}
</script>
@endpush
