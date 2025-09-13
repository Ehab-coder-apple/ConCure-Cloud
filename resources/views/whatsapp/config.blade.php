@extends('layouts.app')

@section('title', __('WhatsApp Configuration'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-whatsapp text-success"></i>
                        {{ __('WhatsApp Integration Setup') }}
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Current Status -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> {{ __('Current Status') }}</h5>
                        <p><strong>{{ __('Provider') }}:</strong> <span class="badge badge-primary">{{ $currentProvider ?? 'Not Configured' }}</span></p>
                        <p><strong>{{ __('Status') }}:</strong> 
                            @if($isConfigured ?? false)
                                <span class="badge badge-success">{{ __('✅ Ready to Send') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('⚠️ Configuration Required') }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Provider Selection Tabs -->
                    <ul class="nav nav-tabs" id="providerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="twilio-tab" data-bs-toggle="tab" data-bs-target="#twilio" type="button" role="tab">
                                <i class="fas fa-cloud"></i> {{ __('Twilio (Recommended)') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="meta-tab" data-bs-toggle="tab" data-bs-target="#meta" type="button" role="tab">
                                <i class="fab fa-facebook"></i> {{ __('Meta Business API') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="wppconnect-tab" data-bs-toggle="tab" data-bs-target="#wppconnect" type="button" role="tab">
                                <i class="fas fa-server"></i> {{ __('WPPConnect (Free)') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="chatapi-tab" data-bs-toggle="tab" data-bs-target="#chatapi" type="button" role="tab">
                                <i class="fas fa-comments"></i> {{ __('ChatAPI') }}
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="providerTabsContent">
                        
                        <!-- Twilio Configuration -->
                        <div class="tab-pane fade show active" id="twilio" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>{{ __('Twilio WhatsApp API Setup') }}</h5>
                                    <p class="text-muted">{{ __('Easy setup, reliable delivery, great for desktop applications.') }}</p>
                                    
                                    <form id="twilioForm">
                                        <div class="form-group">
                                            <label for="twilio_sid">{{ __('Account SID') }}</label>
                                            <input type="text" class="form-control" id="twilio_sid" name="twilio_sid" 
                                                   placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                            <small class="form-text text-muted">{{ __('Found in your Twilio Console') }}</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="twilio_token">{{ __('Auth Token') }}</label>
                                            <input type="password" class="form-control" id="twilio_token" name="twilio_token" 
                                                   placeholder="Your auth token">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="twilio_from">{{ __('From Number') }}</label>
                                            <input type="text" class="form-control" id="twilio_from" name="twilio_from" 
                                                   value="whatsapp:+14155238886" placeholder="whatsapp:+14155238886">
                                            <small class="form-text text-muted">{{ __('Use sandbox number for testing') }}</small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> {{ __('Save & Test Twilio') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>{{ __('Quick Setup Guide') }}</h6>
                                            <ol class="small">
                                                <li>{{ __('Go to') }} <a href="https://www.twilio.com/whatsapp" target="_blank">twilio.com/whatsapp</a></li>
                                                <li>{{ __('Create free account') }}</li>
                                                <li>{{ __('Get Account SID & Auth Token') }}</li>
                                                <li>{{ __('Use sandbox for testing') }}</li>
                                                <li>{{ __('Enter credentials here') }}</li>
                                            </ol>
                                            <p class="small text-success">
                                                <i class="fas fa-dollar-sign"></i> {{ __('$15 free credit included!') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Meta Configuration -->
                        <div class="tab-pane fade" id="meta" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>{{ __('Meta WhatsApp Business API') }}</h5>
                                    <p class="text-muted">{{ __('Official WhatsApp API from Meta (Facebook).') }}</p>
                                    
                                    <form id="metaForm">
                                        <div class="form-group">
                                            <label for="meta_access_token">{{ __('Access Token') }}</label>
                                            <input type="password" class="form-control" id="meta_access_token" name="meta_access_token" 
                                                   placeholder="EAAxxxxxxxxxxxxxxxx">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="meta_phone_id">{{ __('Phone Number ID') }}</label>
                                            <input type="text" class="form-control" id="meta_phone_id" name="meta_phone_id" 
                                                   placeholder="123456789012345">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> {{ __('Save & Test Meta API') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>{{ __('Setup Requirements') }}</h6>
                                            <ul class="small">
                                                <li>{{ __('Meta Business Account') }}</li>
                                                <li>{{ __('WhatsApp Business API access') }}</li>
                                                <li>{{ __('Verified business') }}</li>
                                                <li>{{ __('Phone number approval') }}</li>
                                            </ul>
                                            <p class="small text-warning">
                                                <i class="fas fa-exclamation-triangle"></i> {{ __('Complex setup process') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WPPConnect Configuration -->
                        <div class="tab-pane fade" id="wppconnect" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>{{ __('WPPConnect (Self-hosted)') }}</h5>
                                    <p class="text-muted">{{ __('Free, open-source WhatsApp Web API. Perfect for desktop applications.') }}</p>
                                    
                                    <form id="wppconnectForm">
                                        <div class="form-group">
                                            <label for="wppconnect_url">{{ __('Server URL') }}</label>
                                            <input type="url" class="form-control" id="wppconnect_url" name="wppconnect_url" 
                                                   value="http://localhost:21465" placeholder="http://localhost:21465">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="wppconnect_session">{{ __('Session Name') }}</label>
                                            <input type="text" class="form-control" id="wppconnect_session" name="wppconnect_session" 
                                                   value="clinic_session" placeholder="clinic_session">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-qrcode"></i> {{ __('Setup WPPConnect') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>{{ __('Installation') }}</h6>
                                            <pre class="small bg-dark text-light p-2 rounded">npm install -g @wppconnect-team/wppconnect-server
wppconnect-server --port 21465</pre>
                                            <p class="small text-success">
                                                <i class="fas fa-heart"></i> {{ __('100% Free & Open Source!') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ChatAPI Configuration -->
                        <div class="tab-pane fade" id="chatapi" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5>{{ __('ChatAPI.com') }}</h5>
                                    <p class="text-muted">{{ __('Third-party WhatsApp API service with simple setup.') }}</p>
                                    
                                    <form id="chatapiForm">
                                        <div class="form-group">
                                            <label for="chatapi_url">{{ __('API URL') }}</label>
                                            <input type="url" class="form-control" id="chatapi_url" name="chatapi_url" 
                                                   placeholder="https://api.chat-api.com/instance123456">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="chatapi_token">{{ __('API Token') }}</label>
                                            <input type="password" class="form-control" id="chatapi_token" name="chatapi_token" 
                                                   placeholder="your_api_token">
                                        </div>
                                        
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save"></i> {{ __('Save & Test ChatAPI') }}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>{{ __('Pricing') }}</h6>
                                            <p class="small">{{ __('Starting from $20/month') }}</p>
                                            <p class="small">{{ __('Includes unlimited messages') }}</p>
                                            <a href="https://chat-api.com" target="_blank" class="btn btn-sm btn-outline-primary">
                                                {{ __('Visit ChatAPI.com') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h5>{{ __('Test WhatsApp Integration') }}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="test_phone">{{ __('Test Phone Number') }}</label>
                                    <input type="tel" class="form-control" id="test_phone" placeholder="+964xxxxxxxxx">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="test_message">{{ __('Test Message') }}</label>
                                    <input type="text" class="form-control" id="test_message" 
                                           value="{{ __('Test message from clinic management system!') }}">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success" id="testWhatsApp">
                            <i class="fab fa-whatsapp"></i> {{ __('Send Test Message') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test WhatsApp functionality
    document.getElementById('testWhatsApp').addEventListener('click', function() {
        const phone = document.getElementById('test_phone').value;
        const message = document.getElementById('test_message').value;
        
        if (!phone.trim()) {
            alert('{{ __("Please enter a phone number") }}');
            return;
        }
        
        // Send test message
        fetch('/whatsapp/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                phone: phone,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('{{ __("✅ Test message sent successfully!") }}');
                if (data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank');
                }
            } else {
                alert('{{ __("❌ Test failed:") }} ' + data.message);
            }
        })
        .catch(error => {
            alert('{{ __("Error:") }} ' + error.message);
        });
    });
});
</script>
@endpush
