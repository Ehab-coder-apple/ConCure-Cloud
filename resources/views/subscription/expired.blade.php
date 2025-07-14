@extends('layouts.app')

@section('title', __('Trial Expired'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-danger text-white text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h2 class="mb-0">{{ __('Trial Expired') }}</h2>
                </div>
                <div class="card-body text-center py-5">
                    <h4 class="text-muted mb-4">{{ __('Your 7-day free trial has ended') }}</h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-calendar-times fa-2x text-danger mb-2"></i>
                                <h6>{{ __('Trial Started') }}</h6>
                                <p class="text-muted">{{ $clinic->trial_started_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h6>{{ __('Trial Duration') }}</h6>
                                <p class="text-muted">7 {{ __('days') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                                <h6>{{ __('Expired On') }}</h6>
                                <p class="text-muted">{{ $clinic->trial_expires_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('To continue using ConCure and access all your clinic data, please choose a subscription plan below.') }}
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-lg me-md-2">
                            <i class="fas fa-crown me-2"></i>
                            {{ __('View Subscription Plans') }}
                        </a>
                        <a href="{{ route('subscription.upgrade', ['plan' => 'professional']) }}" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card me-2"></i>
                            {{ __('Upgrade Now') }}
                        </a>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            {{ __('Need help? Contact our support team at') }} 
                            <a href="mailto:support@concure.com">support@concure.com</a>
                        </small>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('logout') }}" class="btn btn-outline-secondary"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
