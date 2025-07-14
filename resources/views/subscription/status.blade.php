@extends('layouts.app')

@section('title', __('Subscription Status'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Subscription Status') }}
                    </h4>
                </div>
                
                <div class="card-body">
                    @if($clinic->is_trial)
                        <div class="text-center mb-4">
                            <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                            <h3 class="text-warning">{{ __('Free Trial Active') }}</h3>
                            <p class="text-muted">{{ $clinic->getTrialStatusMessage() }}</p>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="fas fa-play-circle fa-2x text-success mb-2"></i>
                                    <h6>{{ __('Trial Started') }}</h6>
                                    <p class="text-muted">{{ $clinic->trial_started_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                                    <h6>{{ __('Days Remaining') }}</h6>
                                    <p class="text-muted">{{ $clinic->getRemainingTrialDays() }} {{ __('days') }}</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="p-3">
                                    <i class="fas fa-calendar-times fa-2x text-danger mb-2"></i>
                                    <h6>{{ __('Trial Expires') }}</h6>
                                    <p class="text-muted">{{ $clinic->trial_expires_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        @if($clinic->getRemainingTrialDays() <= 2)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ __('Your trial is expiring soon! Upgrade now to continue using ConCure.') }}
                            </div>
                        @endif

                        <div class="text-center">
                            <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-lg me-2">
                                <i class="fas fa-crown me-1"></i>
                                {{ __('View Plans') }}
                            </a>
                            <a href="{{ route('subscription.upgrade', ['plan' => 'professional']) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-1"></i>
                                {{ __('Upgrade Now') }}
                            </a>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h3 class="text-success">{{ __('Active Subscription') }}</h3>
                            <p class="text-muted">{{ __('Your subscription is active and up to date') }}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 text-center">
                                <div class="p-3">
                                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                    <h6>{{ __('Status') }}</h6>
                                    <p class="text-success">{{ ucfirst($clinic->subscription_status ?? 'Active') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="p-3">
                                    <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                    <h6>{{ __('Expires On') }}</h6>
                                    <p class="text-muted">
                                        {{ $clinic->subscription_expires_at ? $clinic->subscription_expires_at->format('M d, Y') : 'Never' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
