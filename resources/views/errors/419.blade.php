@extends('layouts.guest')

@section('title', __('Session Expired'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        {{ __('Session Expired') }}
                    </h4>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                        <h5 class="text-dark">{{ __('Your session has expired') }}</h5>
                        <p class="text-muted">
                            {{ __('This usually happens when the page has been open for too long or there was a connection issue.') }}
                        </p>
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>{{ __('What to do next:') }}</h6>
                        <ul class="list-unstyled mb-0 text-start">
                            <li class="mb-2">
                                <i class="fas fa-refresh me-2 text-primary"></i>
                                {{ __('Click the "Try Again" button below to refresh the page') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-save me-2 text-success"></i>
                                {{ __('Your form data has been automatically saved and will be restored') }}
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-shield-alt me-2 text-info"></i>
                                {{ __('This is a security feature to protect your data') }}
                            </li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button onclick="window.location.reload()" class="btn btn-primary btn-lg">
                            <i class="fas fa-refresh me-2"></i>
                            {{ __('Try Again') }}
                        </button>
                        <a href="{{ route('clinic.activate.form') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            {{ __('Start Over') }}
                        </a>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            {{ __('If you continue to experience issues, please contact our support team.') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-body text-center">
                    <h6 class="text-muted">{{ __('Need Help?') }}</h6>
                    <p class="text-muted mb-2">{{ __('Our support team is here to help you with activation.') }}</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="mailto:support@concure.com" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>
                            {{ __('Email Support') }}
                        </a>
                        <a href="tel:+1-555-CONCURE" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>
                            {{ __('Call Support') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh after 10 seconds if user doesn't click
setTimeout(function() {
    if (confirm('{{ __("Auto-refresh in 5 seconds. Click OK to refresh now or Cancel to wait.") }}')) {
        window.location.reload();
    } else {
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    }
}, 10000);
</script>
@endsection
