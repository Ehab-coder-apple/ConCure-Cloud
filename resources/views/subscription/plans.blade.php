@extends('layouts.app')

@section('title', __('Subscription Plans'))

@section('content')
<div class="container-fluid">
    <!-- Trial Status -->
    @if($clinic->is_trial)
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-warning me-3 fa-2x"></i>
                        <div>
                            <h5 class="mb-1">{{ __('Free Trial Status') }}</h5>
                            <p class="mb-0">{{ $clinic->getTrialStatusMessage() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4 text-primary mb-3">{{ __('Choose Your Plan') }}</h1>
                <p class="lead text-muted">{{ __('Select the perfect plan for your clinic') }}</p>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        @foreach($plans as $index => $plan)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 {{ isset($plan['popular']) ? 'border-primary shadow-lg' : 'shadow' }}">
                    @if(isset($plan['popular']))
                        <div class="card-header bg-primary text-white text-center">
                            <i class="fas fa-star me-1"></i>
                            {{ __('Most Popular') }}
                        </div>
                    @endif
                    
                    <div class="card-body text-center">
                        <h3 class="card-title text-primary">{{ $plan['name'] }}</h3>
                        <div class="mb-4">
                            <span class="display-4 text-dark">{{ $plan['price'] }}</span>
                            <span class="text-muted">/ {{ $plan['period'] }}</span>
                        </div>
                        
                        <ul class="list-unstyled mb-4">
                            @foreach($plan['features'] as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <div class="card-footer bg-transparent">
                        <div class="d-grid">
                            <a href="{{ route('subscription.upgrade', ['plan' => strtolower(str_replace(' Plan', '', $plan['name']))]) }}" 
                               class="btn {{ isset($plan['popular']) ? 'btn-primary' : 'btn-outline-primary' }} btn-lg">
                                {{ __('Choose Plan') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h4 class="text-primary mb-3">{{ __('Why Upgrade?') }}</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h6>{{ __('Multi-User Access') }}</h6>
                            <p class="text-muted small">{{ __('Add doctors, nurses, and staff') }}</p>
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                            <h6>{{ __('Data Security') }}</h6>
                            <p class="text-muted small">{{ __('Enterprise-grade security') }}</p>
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                            <h6>{{ __('Priority Support') }}</h6>
                            <p class="text-muted small">{{ __('24/7 dedicated support') }}</p>
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-sync-alt fa-2x text-primary mb-2"></i>
                            <h6>{{ __('Regular Updates') }}</h6>
                            <p class="text-muted small">{{ __('Latest features & improvements') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <p class="text-muted">
            {{ __('Questions about our plans?') }} 
            <a href="mailto:sales@concure.com">{{ __('Contact Sales') }}</a>
        </p>
    </div>
</div>
@endsection
