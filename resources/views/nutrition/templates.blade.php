@extends('layouts.app')

@section('page-title', __('Nutrition Plan Templates'))

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-clipboard-list text-info"></i>
                        {{ __('Nutrition Plan Templates') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Choose from specialized nutrition plan templates') }}</p>
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

    <!-- Template Cards -->
    <div class="row">


        <!-- Muscle Gain Template -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-dumbbell me-2"></i>
                        {{ __('Muscle Gain Plan') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-primary">{{ __('Target Goals:') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-primary me-2"></i>{{ __('Build lean muscle') }}</li>
                            <li><i class="fas fa-check text-primary me-2"></i>{{ __('Increase strength') }}</li>
                            <li><i class="fas fa-check text-primary me-2"></i>{{ __('Improve recovery') }}</li>
                            <li><i class="fas fa-check text-primary me-2"></i>{{ __('Optimize protein intake') }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-primary">{{ __('Plan Features:') }}</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-primary">2500</div>
                                    <small class="text-muted">{{ __('Calories/day') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-primary">60</div>
                                    <small class="text-muted">{{ __('Days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ __('High-protein nutrition plan designed to support muscle growth and recovery with optimal timing around workouts.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('nutrition.create.muscle-gain') }}" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create Muscle Gain Plan') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Diabetic Template -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        {{ __('Diabetic Plan') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-warning">{{ __('Target Goals:') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-warning me-2"></i>{{ __('Control blood sugar') }}</li>
                            <li><i class="fas fa-check text-warning me-2"></i>{{ __('Stable glucose levels') }}</li>
                            <li><i class="fas fa-check text-warning me-2"></i>{{ __('Prevent complications') }}</li>
                            <li><i class="fas fa-check text-warning me-2"></i>{{ __('Maintain healthy weight') }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-warning">{{ __('Plan Features:') }}</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-warning">1800</div>
                                    <small class="text-muted">{{ __('Calories/day') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-warning">90</div>
                                    <small class="text-muted">{{ __('Days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ __('Carefully balanced nutrition plan for managing blood sugar levels with low-glycemic foods and consistent meal timing.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('nutrition.create.diabetic') }}" class="btn btn-warning w-100">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create Diabetic Plan') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Custom Plan -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        {{ __('Custom Plan') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-secondary">{{ __('Features:') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-secondary me-2"></i>{{ __('Fully customizable') }}</li>
                            <li><i class="fas fa-check text-secondary me-2"></i>{{ __('Any goal type') }}</li>
                            <li><i class="fas fa-check text-secondary me-2"></i>{{ __('Flexible duration') }}</li>
                            <li><i class="fas fa-check text-secondary me-2"></i>{{ __('Custom meal plans') }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-secondary">{{ __('Perfect for:') }}</h6>
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">{{ __('Unique dietary needs, special conditions, or personalized goals') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ __('Create a completely customized nutrition plan tailored to specific patient needs and preferences.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-grid gap-2">
                        <a href="{{ route('nutrition.create.enhanced') }}" class="btn btn-primary w-100">
                            <i class="fas fa-utensils me-1"></i>
                            {{ __('Create Detailed Plan') }}
                        </a>
                        <a href="{{ route('nutrition.create') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('Create Basic Plan') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weight Gain Template -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ __('Weight Gain Plan') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-info">{{ __('Target Goals:') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-info me-2"></i>{{ __('Healthy weight gain') }}</li>
                            <li><i class="fas fa-check text-info me-2"></i>{{ __('Increase muscle mass') }}</li>
                            <li><i class="fas fa-check text-info me-2"></i>{{ __('Improve appetite') }}</li>
                            <li><i class="fas fa-check text-info me-2"></i>{{ __('Nutrient-dense foods') }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-info">{{ __('Plan Features:') }}</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-info">2800</div>
                                    <small class="text-muted">{{ __('Calories/day') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-info">45</div>
                                    <small class="text-muted">{{ __('Days') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ __('High-calorie nutrition plan with nutrient-dense foods to promote healthy weight gain and muscle development.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('nutrition.create') }}?goal=weight_gain" class="btn btn-info w-100">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create Weight Gain Plan') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Maintenance Template -->
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100 border-dark">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>
                        {{ __('Maintenance Plan') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-dark">{{ __('Target Goals:') }}</h6>
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-dark me-2"></i>{{ __('Maintain current weight') }}</li>
                            <li><i class="fas fa-check text-dark me-2"></i>{{ __('Balanced nutrition') }}</li>
                            <li><i class="fas fa-check text-dark me-2"></i>{{ __('Healthy lifestyle') }}</li>
                            <li><i class="fas fa-check text-dark me-2"></i>{{ __('Long-term wellness') }}</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-dark">{{ __('Plan Features:') }}</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-dark">2000</div>
                                    <small class="text-muted">{{ __('Calories/day') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded">
                                    <div class="fw-bold text-dark">∞</div>
                                    <small class="text-muted">{{ __('Ongoing') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">
                            {{ __('Balanced nutrition plan for maintaining current weight and promoting overall health and wellness.') }}
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('nutrition.create') }}?goal=maintenance" class="btn btn-dark w-100">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Create Maintenance Plan') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
