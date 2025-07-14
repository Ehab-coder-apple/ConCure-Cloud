@extends('layouts.app')

@section('title', __('Upgrade Subscription'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="fas fa-crown fa-2x mb-3"></i>
                    <h2 class="mb-0">{{ __('Upgrade to ConCure Pro') }}</h2>
                    <p class="mb-0 opacity-75">{{ __('Continue enjoying all features') }}</p>
                </div>
                
                <div class="card-body p-5">
                    @if($clinic->is_trial)
                        <div class="alert alert-warning mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-warning me-3 fa-2x"></i>
                                <div>
                                    <h6 class="mb-1">{{ __('Trial Status') }}</h6>
                                    <p class="mb-0">{{ $clinic->getTrialStatusMessage() }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('subscription.process-upgrade') }}">
                        @csrf
                        
                        <!-- Plan Selection -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">{{ __('Selected Plan') }}</h5>
                            <div class="card border-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ ucfirst($plan) }} Plan</h6>
                                            <p class="text-muted mb-0">
                                                @if($plan === 'basic')
                                                    {{ __('Perfect for small clinics') }}
                                                @elseif($plan === 'professional')
                                                    {{ __('Most popular choice') }}
                                                @else
                                                    {{ __('Enterprise-grade features') }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <h4 class="text-primary mb-0">
                                                @if($plan === 'basic')
                                                    $29
                                                @elseif($plan === 'professional')
                                                    $59
                                                @else
                                                    $99
                                                @endif
                                                <small class="text-muted">/month</small>
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="plan" value="{{ $plan }}">
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">{{ __('Payment Method') }}</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                        <label class="form-check-label" for="credit_card">
                                            <i class="fas fa-credit-card text-primary me-2"></i>
                                            {{ __('Credit Card') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label" for="bank_transfer">
                                            <i class="fas fa-university text-primary me-2"></i>
                                            {{ __('Bank Transfer') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Features Included -->
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">{{ __('What\'s Included') }}</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Unlimited patients') }}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Multi-user access') }}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Prescription management') }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Nutrition planning') }}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Lab request management') }}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            {{ __('Priority support') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    {{ __('I agree to the') }} 
                                    <a href="#" class="text-primary">{{ __('Terms of Service') }}</a> 
                                    {{ __('and') }} 
                                    <a href="#" class="text-primary">{{ __('Privacy Policy') }}</a>
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="{{ route('subscription.plans') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                {{ __('Back to Plans') }}
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-2"></i>
                                {{ __('Complete Upgrade') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-shield-alt text-success me-1"></i>
                    {{ __('Secure payment processing') }}
                </p>
                <small class="text-muted">
                    {{ __('Questions? Contact us at') }} 
                    <a href="mailto:support@concure.com">support@concure.com</a>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
