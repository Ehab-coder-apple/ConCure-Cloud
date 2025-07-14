@if(auth()->check() && auth()->user()->clinic && auth()->user()->clinic->is_trial)
    @php
        $clinic = auth()->user()->clinic;
        $remainingDays = $clinic->getRemainingTrialDays();
    @endphp
    
    @if($remainingDays > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert" style="border-left: 4px solid #ffc107;">
            <div class="d-flex align-items-center">
                <i class="fas fa-clock text-warning me-2"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('Free Trial Active') }}</strong>
                    <span class="ms-2">
                        @if($remainingDays == 1)
                            {{ __('Your trial expires tomorrow!') }}
                        @else
                            {{ __('Your trial expires in :days days', ['days' => $remainingDays]) }}
                        @endif
                    </span>
                </div>
                <div class="ms-3">
                    <a href="{{ route('subscription.plans') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-crown me-1"></i>
                        {{ __('Upgrade Now') }}
                    </a>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif($clinic->isTrialExpired())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert" style="border-left: 4px solid #dc3545;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                <div class="flex-grow-1">
                    <strong>{{ __('Trial Expired') }}</strong>
                    <span class="ms-2">{{ __('Your 7-day free trial has ended. Upgrade to continue using ConCure.') }}</span>
                </div>
                <div class="ms-3">
                    <a href="{{ route('subscription.plans') }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-credit-card me-1"></i>
                        {{ __('Upgrade Now') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
@endif
