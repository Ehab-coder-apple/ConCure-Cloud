<!-- Extend Trial Modal -->
<div class="modal fade" id="extendTrialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock me-2"></i>
                    {{ __('Extend Trial Period') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendTrialForm">
                <div class="modal-body">
                    <input type="hidden" id="extend_trial_clinic_id">
                    <div class="mb-3">
                        <label for="extend_trial_days" class="form-label">{{ __('Extend trial by (days)') }}</label>
                        <select class="form-select" id="extend_trial_days" required>
                            <option value="3">3 {{ __('days') }}</option>
                            <option value="7" selected>7 {{ __('days') }}</option>
                            <option value="14">14 {{ __('days') }}</option>
                            <option value="30">30 {{ __('days') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('This will extend the trial period from the current expiration date.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-clock me-1"></i>
                        {{ __('Extend Trial') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Convert Trial Modal -->
<div class="modal fade" id="convertTrialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-crown me-2"></i>
                    {{ __('Convert Trial to Paid Subscription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="convertTrialForm">
                <div class="modal-body">
                    <input type="hidden" id="convert_trial_clinic_id">
                    <div class="mb-3">
                        <label for="convert_trial_plan_type" class="form-label">{{ __('Plan Type') }}</label>
                        <select class="form-select" id="convert_trial_plan_type" required>
                            <option value="basic">{{ __('Basic Plan') }} - $29/month</option>
                            <option value="professional" selected>{{ __('Professional Plan') }} - $59/month</option>
                            <option value="enterprise">{{ __('Enterprise Plan') }} - $99/month</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="convert_trial_months" class="form-label">{{ __('Subscription Duration') }}</label>
                        <select class="form-select" id="convert_trial_months" required>
                            <option value="1">1 {{ __('month') }}</option>
                            <option value="3">3 {{ __('months') }}</option>
                            <option value="6">6 {{ __('months') }}</option>
                            <option value="12" selected>12 {{ __('months') }}</option>
                            <option value="24">24 {{ __('months') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ __('This will convert the trial to a paid subscription and remove trial limitations.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-crown me-1"></i>
                        {{ __('Convert to Paid') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Extend Subscription Modal -->
<div class="modal fade" id="extendSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus me-2"></i>
                    {{ __('Extend Subscription') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="extendSubscriptionForm">
                <div class="modal-body">
                    <input type="hidden" id="extend_clinic_id">
                    <div class="mb-3">
                        <label for="extend_months" class="form-label">{{ __('Extend subscription by (months)') }}</label>
                        <select class="form-select" id="extend_months" required>
                            <option value="1">1 {{ __('month') }}</option>
                            <option value="3">3 {{ __('months') }}</option>
                            <option value="6" selected>6 {{ __('months') }}</option>
                            <option value="12">12 {{ __('months') }}</option>
                            <option value="24">24 {{ __('months') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ __('This will extend the subscription from the current expiration date.') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i>
                        {{ __('Extend Subscription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
