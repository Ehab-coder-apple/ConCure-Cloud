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
                                    <h5>{{ __('WPPConnect — Free WhatsApp Integration') }}</h5>
                                    <p class="text-muted">{{ __('Use your own WhatsApp number to send automated messages at zero cost.') }}</p>

                                    <!-- Step 1: Server URL -->
                                    <form id="wppconnectForm">
                                        <div class="form-group mb-3">
                                            <label for="wppconnect_url">{{ __('WPPConnect Server URL') }}</label>
                                            <input type="url" class="form-control" id="wppconnect_url" name="wppconnect_url"
                                                   value="{{ auth()->user()->clinic->settings['whatsapp']['wppconnect_url'] ?? 'http://localhost:21465' }}"
                                                   placeholder="http://localhost:21465">
                                            <small class="form-text text-muted">{{ __('The URL where your WPPConnect server is running') }}</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="wppconnect_session">{{ __('Session Name (optional)') }}</label>
                                            <input type="text" class="form-control" id="wppconnect_session" name="wppconnect_session"
                                                   value="{{ auth()->user()->clinic->settings['whatsapp']['wppconnect_session'] ?? '' }}"
                                                   placeholder="{{ __('Auto-generated if empty') }}">
                                        </div>

                                        <button type="submit" class="btn btn-info" id="btnSaveWppconnect">
                                            <i class="fas fa-save"></i> {{ __('Save & Connect') }}
                                        </button>
                                    </form>

                                    <!-- Step 2: QR Code -->
                                    <div id="wppconnectQrSection" class="mt-4 d-none">
                                        <hr>
                                        <h5><i class="fas fa-qrcode"></i> {{ __('Scan QR Code') }}</h5>
                                        <p class="text-muted">{{ __('Open WhatsApp on your phone → Settings → Linked Devices → Link a Device → Scan this code') }}</p>

                                        <div id="wppconnectQrContainer" class="text-center py-3">
                                            <div class="spinner-border text-success" role="status">
                                                <span class="sr-only">{{ __('Loading...') }}</span>
                                            </div>
                                            <p class="mt-2">{{ __('Generating QR code...') }}</p>
                                        </div>

                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnRefreshQr">
                                            <i class="fas fa-sync-alt"></i> {{ __('Refresh QR Code') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnCheckStatus">
                                            <i class="fas fa-check-circle"></i> {{ __('Check Connection') }}
                                        </button>
                                    </div>

                                    <!-- Status -->
                                    <div id="wppconnectStatusBox" class="mt-3 d-none">
                                        <div class="alert" id="wppconnectStatusAlert"></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6><i class="fas fa-heart text-danger"></i> {{ __('100% Free') }}</h6>
                                            <p class="small">{{ __('No monthly fees. No per-message charges. Uses your own WhatsApp number.') }}</p>
                                            <hr>
                                            <h6>{{ __('How it works') }}</h6>
                                            <ol class="small">
                                                <li>{{ __('Save the server URL above') }}</li>
                                                <li>{{ __('Scan the QR code with your phone') }}</li>
                                                <li>{{ __('Appointment reminders are sent automatically!') }}</li>
                                            </ol>
                                            <hr>
                                            <h6>{{ __('Server Installation') }}</h6>
                                            <pre class="small bg-dark text-light p-2 rounded" style="white-space:pre-wrap">npm install -g @wppconnect-team/server
npx wppconnect-server</pre>
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
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const hdrs = {'Content-Type':'application/json','X-CSRF-TOKEN':csrf};

    // Test WhatsApp
    document.getElementById('testWhatsApp').addEventListener('click', function() {
        const phone = document.getElementById('test_phone').value;
        const message = document.getElementById('test_message').value;
        if (!phone.trim()) { alert('{{ __("Please enter a phone number") }}'); return; }
        fetch('/whatsapp/test',{method:'POST',headers:hdrs,body:JSON.stringify({phone,message})})
            .then(r=>r.json()).then(data=>{
                if(data.success){alert('{{ __("✅ Test message sent successfully!") }}');if(data.whatsapp_url)window.open(data.whatsapp_url,'_blank');}
                else alert('{{ __("❌ Test failed:") }} '+data.message);
            }).catch(e=>alert('{{ __("Error:") }} '+e.message));
    });

    // ── WPPConnect ──
    const qrSection = document.getElementById('wppconnectQrSection');
    const qrContainer = document.getElementById('wppconnectQrContainer');
    const statusBox = document.getElementById('wppconnectStatusBox');
    const statusAlert = document.getElementById('wppconnectStatusAlert');

    function showStatus(msg, type){
        statusBox.classList.remove('d-none');
        statusAlert.className='alert alert-'+type;
        statusAlert.innerHTML=msg;
    }

    // Save WPPConnect config
    document.getElementById('wppconnectForm').addEventListener('submit', function(e){
        e.preventDefault();
        const btn = document.getElementById('btnSaveWppconnect');
        btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> {{ __("Saving...") }}';
        fetch('/whatsapp/configure/wppconnect',{method:'POST',headers:hdrs,body:JSON.stringify({
            wppconnect_url: document.getElementById('wppconnect_url').value,
            wppconnect_session: document.getElementById('wppconnect_session').value
        })}).then(r=>r.json()).then(data=>{
            btn.disabled=false; btn.innerHTML='<i class="fas fa-save"></i> {{ __("Save & Connect") }}';
            if(data.success){
                showStatus('<i class="fas fa-check-circle"></i> '+data.message,'success');
                qrSection.classList.remove('d-none');
                loadQrCode();
            } else {
                showStatus('<i class="fas fa-times-circle"></i> '+data.message,'danger');
            }
        }).catch(e=>{btn.disabled=false;btn.innerHTML='<i class="fas fa-save"></i> {{ __("Save & Connect") }}';showStatus(e.message,'danger');});
    });

    // Load QR code
    function loadQrCode(){
        qrContainer.innerHTML='<div class="spinner-border text-success"></div><p class="mt-2">{{ __("Generating QR code...") }}</p>';
        fetch('/whatsapp/wppconnect/qr',{headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
            .then(r=>r.json()).then(data=>{
                if(data.connected){
                    qrContainer.innerHTML='<div class="alert alert-success"><i class="fas fa-check-circle fa-2x"></i><br>{{ __("WhatsApp is connected!") }}</div>';
                    showStatus('{{ __("✅ WhatsApp is connected and ready to send messages.") }}','success');
                } else if(data.qrcode){
                    qrContainer.innerHTML='<img src="'+data.qrcode+'" alt="QR Code" style="max-width:300px" class="border rounded p-2">';
                } else {
                    qrContainer.innerHTML='<p class="text-warning">{{ __("Could not generate QR code. Try refreshing.") }}</p>';
                }
            }).catch(e=>{qrContainer.innerHTML='<p class="text-danger">'+e.message+'</p>';});
    }

    document.getElementById('btnRefreshQr')?.addEventListener('click', loadQrCode);
    document.getElementById('btnCheckStatus')?.addEventListener('click', function(){
        fetch('/whatsapp/wppconnect/status',{headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
            .then(r=>r.json()).then(data=>{
                if(data.connected){showStatus('<i class="fas fa-check-circle"></i> '+data.message,'success');}
                else{showStatus('<i class="fas fa-exclamation-triangle"></i> '+data.message,'warning');}
            }).catch(e=>showStatus(e.message,'danger'));
    });

    // Auto-show QR section if already configured
    @if(isset(auth()->user()->clinic->settings['whatsapp']['provider']) && auth()->user()->clinic->settings['whatsapp']['provider'] === 'wppconnect')
        qrSection.classList.remove('d-none');
        loadQrCode();
    @endif
});
</script>
@endpush
