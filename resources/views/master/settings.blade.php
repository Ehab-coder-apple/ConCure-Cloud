@extends('layouts.master')

@section('title', __('System Settings'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-cog text-secondary me-2"></i>
                        {{ __('System Settings') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('master.dashboard') }}">{{ __('Master Dashboard') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Settings') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Platform Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-server me-2"></i>
                                {{ __('Platform Settings') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="platformSettingsForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="platform_name" class="form-label">{{ __('Platform Name') }}</label>
                                    <input type="text" class="form-control" id="platform_name" name="platform_name" 
                                           value="{{ $settings['platform_name'] ?? 'ConCure SaaS' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="platform_version" class="form-label">{{ __('Platform Version') }}</label>
                                    <input type="text" class="form-control" id="platform_version" name="platform_version" 
                                           value="{{ $settings['platform_version'] ?? '1.0.0' }}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="max_clinics" class="form-label">{{ __('Maximum Clinics') }}</label>
                                    <input type="number" class="form-control" id="max_clinics" name="max_clinics" 
                                           value="{{ $settings['max_clinics'] ?? 1000 }}" min="1">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    {{ __('Save Platform Settings') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Default Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-sliders-h me-2"></i>
                                {{ __('Default Settings') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="defaultSettingsForm">
                                @csrf
                                <div class="mb-3">
                                    <label for="default_subscription_months" class="form-label">{{ __('Default Subscription (Months)') }}</label>
                                    <select class="form-select" id="default_subscription_months" name="default_subscription_months">
                                        <option value="1" {{ ($settings['default_subscription_months'] ?? 12) == 1 ? 'selected' : '' }}>1 {{ __('Month') }}</option>
                                        <option value="3" {{ ($settings['default_subscription_months'] ?? 12) == 3 ? 'selected' : '' }}>3 {{ __('Months') }}</option>
                                        <option value="6" {{ ($settings['default_subscription_months'] ?? 12) == 6 ? 'selected' : '' }}>6 {{ __('Months') }}</option>
                                        <option value="12" {{ ($settings['default_subscription_months'] ?? 12) == 12 ? 'selected' : '' }}>12 {{ __('Months') }}</option>
                                        <option value="24" {{ ($settings['default_subscription_months'] ?? 12) == 24 ? 'selected' : '' }}>24 {{ __('Months') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="activation_code_expiry_days" class="form-label">{{ __('Activation Code Expiry (Days)') }}</label>
                                    <input type="number" class="form-control" id="activation_code_expiry_days" name="activation_code_expiry_days" 
                                           value="{{ $settings['activation_code_expiry_days'] ?? 30 }}" min="1" max="365">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    {{ __('Save Default Settings') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ __('System Information') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-database fa-2x text-primary mb-2"></i>
                                        <h6>{{ __('Database') }}</h6>
                                        <p class="text-muted">SQLite</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-code fa-2x text-success mb-2"></i>
                                        <h6>{{ __('Framework') }}</h6>
                                        <p class="text-muted">Laravel {{ app()->version() }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-server fa-2x text-info mb-2"></i>
                                        <h6>{{ __('PHP Version') }}</h6>
                                        <p class="text-muted">{{ PHP_VERSION }}</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                        <h6>{{ __('Server Time') }}</h6>
                                        <p class="text-muted">{{ now()->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('platformSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;
    
    fetch('{{ route("master.settings.update") }}', {
        method: 'PATCH',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('{{ __("Platform settings saved successfully!") }}');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('defaultSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> {{ __("Saving...") }}';
    submitBtn.disabled = true;
    
    fetch('{{ route("master.settings.update") }}', {
        method: 'PATCH',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('{{ __("Default settings saved successfully!") }}');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("An error occurred. Please try again.") }}');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endsection
