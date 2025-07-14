@extends('layouts.app')

@section('title', __('Subscription Expired'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card text-danger me-2"></i>
                        {{ __('Subscription Expired') }}
                    </h4>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-calendar-times fa-4x text-danger mb-3"></i>
                        <h5>{{ __('Your clinic subscription has expired') }}</h5>
                        <p class="text-muted">
                            {{ __('Your clinic subscription has expired and you no longer have access to the system. Please renew your subscription to continue using ConCure.') }}
                        </p>
                    </div>

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('Please contact ConCure support to renew your subscription and restore access to your clinic management system.') }}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-envelope me-2"></i>{{ __('Email') }}</h6>
                                    <p class="mb-0">billing@concure.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-phone me-2"></i>{{ __('Phone') }}</h6>
                                    <p class="mb-0">+1 (555) 123-4567</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-globe me-2"></i>{{ __('Website') }}</h6>
                                    <p class="mb-0">www.concure.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
