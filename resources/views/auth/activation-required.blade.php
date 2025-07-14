@extends('layouts.app')

@section('title', __('Account Activation Required'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        {{ __('Account Activation Required') }}
                    </h4>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-clock fa-4x text-warning mb-3"></i>
                        <h5>{{ __('Your account requires activation') }}</h5>
                        <p class="text-muted">
                            {{ __('Your account has been created but needs to be activated by an administrator before you can access the system.') }}
                        </p>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('Please contact your clinic administrator or system administrator to activate your account.') }}
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
