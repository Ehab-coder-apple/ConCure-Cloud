@extends('layouts.app')

@section('title', __('Clinic Activation Required'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-hospital text-warning me-2"></i>
                        {{ __('Clinic Activation Required') }}
                    </h4>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-building fa-4x text-warning mb-3"></i>
                        <h5>{{ __('Your clinic requires activation') }}</h5>
                        <p class="text-muted">
                            {{ __('Your clinic has been registered but needs to be activated before you can access the full system features.') }}
                        </p>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Please contact ConCure support to activate your clinic subscription.') }}
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-envelope me-2"></i>{{ __('Email Support') }}</h6>
                                    <p class="mb-0">support@concure.com</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-phone me-2"></i>{{ __('Phone Support') }}</h6>
                                    <p class="mb-0">+1 (555) 123-4567</p>
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
