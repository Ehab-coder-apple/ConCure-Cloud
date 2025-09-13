@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-whatsapp text-success"></i>
                        {{ __('WhatsApp Configuration') }}
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Provider Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ $status['configured'] ? 'success' : 'warning' }}">
                                    <i class="fab fa-whatsapp"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Provider') }}</span>
                                    <span class="info-box-number">{{ ucfirst($status['provider']) }}</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $status['configured'] ? 'success' : 'warning' }}"
                                             style="width: {{ $status['configured'] ? '100' : '50' }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        {{ $status['configured'] ? __('Configured') : __('Not Configured') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($serverStatus)
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-{{ isset($serverStatus['ready']) && $serverStatus['ready'] ? 'success' : 'danger' }}">
                                    <i class="fas fa-server"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Server Status') }}</span>
                                    <span class="info-box-number">
                                        @if(isset($serverStatus['error']))
                                            {{ __('Offline') }}
                                        @elseif(isset($serverStatus['ready']) && $serverStatus['ready'])
                                            {{ __('Ready') }}
                                        @elseif(isset($serverStatus['hasQR']) && $serverStatus['hasQR'])
                                            {{ __('Needs QR Scan') }}
                                        @else
                                            {{ __('Initializing') }}
                                        @endif
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ isset($serverStatus['ready']) && $serverStatus['ready'] ? 'success' : 'warning' }}" 
                                             style="width: {{ isset($serverStatus['ready']) && $serverStatus['ready'] ? '100' : '70' }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        @if(isset($serverStatus['error']))
                                            {{ $serverStatus['error'] }}
                                        @else
                                            {{ __('Last checked: ') }}{{ now()->format('H:i:s') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Configuration Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>{{ __('Configuration Status') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td><strong>{{ __('Current Provider') }}</strong></td>
                                            <td>
                                                <span class="badge bg-primary">{{ ucfirst($status['provider']) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Twilio Configuration') }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $status['config_check']['twilio'] ? 'success' : 'secondary' }}">
                                                    {{ $status['config_check']['twilio'] ? __('Configured') : __('Not Configured') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Web API Configuration') }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $status['config_check']['web_api'] ? 'success' : 'secondary' }}">
                                                    {{ $status['config_check']['web_api'] ? __('Configured') : __('Not Configured') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Official API Configuration') }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $status['config_check']['official'] ? 'success' : 'secondary' }}">
                                                    {{ $status['config_check']['official'] ? __('Configured') : __('Not Configured') }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>{{ __('Fallback to Web WhatsApp') }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $status['fallback_to_web'] ? 'warning' : 'success' }}">
                                                    {{ $status['fallback_to_web'] ? __('Yes') : __('No') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Automatic WhatsApp Setup Section -->
                    @if(!$status['configured'])
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-magic"></i>
                                        {{ __('Automatic WhatsApp Setup') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        {{ __('Set up WhatsApp Web automatically so all sections of your clinic can send messages directly. This is a one-time setup process.') }}
                                    </div>

                                    <form id="autoSetupForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="setup_phone" class="form-label">{{ __('Your WhatsApp Number') }}</label>
                                                <input type="text" class="form-control" id="setup_phone"
                                                       value="{{ $clinicWhatsApp ?? '' }}"
                                                       placeholder="9647501234567">
                                                <small class="form-text text-muted">
                                                    {{ __('Your clinic\'s WhatsApp number (optional - used for testing)') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fab fa-whatsapp"></i>
                                                    {{ __('Setup WhatsApp Web Automatically') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Setup Progress -->
                                    <div id="setupProgress" class="mt-3" style="display: none;">
                                        <div class="alert alert-success">
                                            <div class="d-flex align-items-center">
                                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <div>
                                                    <strong>{{ __('Setting up WhatsApp...') }}</strong>
                                                    <div id="setupStatus">{{ __('Opening WhatsApp Web...') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress">
                                            <div id="setupProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- QR Code Section (for web provider) -->
                    @if($status['provider'] === 'web' && $status['configured'])
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5>{{ __('WhatsApp Connection') }}</h5>
                            <div class="text-center">
                                @if(isset($serverStatus['hasQR']) && $serverStatus['hasQR'])
                                    <div class="alert alert-warning">
                                        <i class="fas fa-qrcode"></i>
                                        {{ __('WhatsApp needs to be connected. Please scan the QR code.') }}
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="showQRCode()">
                                        <i class="fas fa-qrcode"></i>
                                        {{ __('Show QR Code') }}
                                    </button>
                                @elseif(isset($serverStatus['ready']) && $serverStatus['ready'])
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i>
                                        {{ __('WhatsApp is connected and ready to send messages!') }}
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        {{ __('WhatsApp server is initializing...') }}
                                    </div>
                                    <button type="button" class="btn btn-secondary" onclick="checkStatus()">
                                        <i class="fas fa-refresh"></i>
                                        {{ __('Check Status') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Test Message Section -->
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <h5>{{ __('Test WhatsApp Message') }}</h5>
                            <form id="testForm">
                                <div class="mb-3">
                                    <label for="test_phone" class="form-label">{{ __('Phone Number') }}</label>
                                    <input type="text" class="form-control" id="test_phone"
                                           value="{{ $clinicWhatsApp ?? '9647515662077' }}"
                                           placeholder="9647515662077" required>
                                    <small class="form-text text-muted">
                                        {{ __('Include country code (e.g., 9647501234567 for Iraq)') }}
                                        @if($clinicWhatsApp)
                                            <br><strong>{{ __('Using clinic default WhatsApp number') }}</strong>
                                        @endif
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label for="test_message" class="form-label">{{ __('Test Message') }}</label>
                                    <textarea class="form-control" id="test_message" rows="3" required>Hello! This is a test message from ConCure Clinic Management System. 🏥</textarea>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fab fa-whatsapp"></i>
                                    {{ __('Send Test Message') }}
                                </button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('WhatsApp QR Code') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="qrContent">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Automatic WhatsApp Setup
document.getElementById('autoSetupForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const phoneNumber = document.getElementById('setup_phone').value;
    const progressDiv = document.getElementById('setupProgress');
    const statusDiv = document.getElementById('setupStatus');
    const progressBar = document.getElementById('setupProgressBar');

    // Show progress
    progressDiv.style.display = 'block';
    progressBar.style.width = '20%';
    statusDiv.textContent = 'Initializing WhatsApp setup...';

    // Call setup endpoint
    fetch('/whatsapp/setup', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            phone_number: phoneNumber,
            message: 'WhatsApp has been configured for your clinic management system!'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            progressBar.style.width = '50%';
            statusDiv.textContent = data.message;

            // Open WhatsApp Web automatically
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
                progressBar.style.width = '75%';
                statusDiv.textContent = 'WhatsApp Web opened. Please scan QR code or login...';

                // Start monitoring setup status
                monitorSetupStatus();
            }
        } else {
            statusDiv.textContent = 'Setup failed: ' + data.message;
            progressDiv.className = 'mt-3 alert alert-danger';
        }
    })
    .catch(error => {
        statusDiv.textContent = 'Setup failed: ' + error.message;
        progressDiv.className = 'mt-3 alert alert-danger';
    });
});

// Monitor setup status
function monitorSetupStatus() {
    const statusDiv = document.getElementById('setupStatus');
    const progressBar = document.getElementById('setupProgressBar');
    let attempts = 0;
    const maxAttempts = 20; // 60 seconds total

    const checkInterval = setInterval(() => {
        attempts++;

        fetch('/whatsapp/setup-status')
            .then(response => response.json())
            .then(data => {
                if (data.setup_complete) {
                    progressBar.style.width = '100%';
                    statusDiv.textContent = '✅ WhatsApp setup completed successfully!';

                    setTimeout(() => {
                        location.reload(); // Refresh to show new status
                    }, 2000);

                    clearInterval(checkInterval);
                } else if (data.setup_initiated) {
                    const progress = Math.min(75 + (attempts * 2), 95);
                    progressBar.style.width = progress + '%';
                    statusDiv.textContent = `Waiting for WhatsApp connection... (${data.time_elapsed}s)`;
                }

                if (attempts >= maxAttempts) {
                    progressBar.style.width = '100%';
                    statusDiv.textContent = '⚠️ Setup timeout. Please try again or check manually.';
                    clearInterval(checkInterval);
                }
            })
            .catch(error => {
                console.error('Status check failed:', error);
            });
    }, 3000); // Check every 3 seconds
}

function showQRCode() {
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();

    fetch('/whatsapp/qr')
        .then(response => response.text())
        .then(html => {
            document.getElementById('qrContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('qrContent').innerHTML =
                '<div class="alert alert-danger">Failed to load QR code: ' + error.message + '</div>';
        });
}

function checkStatus() {
    location.reload();
}

document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const phone = document.getElementById('test_phone').value;
    const message = document.getElementById('test_message').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');

    // Validate inputs
    if (!phone.trim()) {
        alert('{{ __("Please enter a phone number") }}');
        return;
    }

    if (!message.trim()) {
        alert('{{ __("Please enter a message") }}');
        return;
    }

    // Clean phone number (remove non-digits and + sign)
    const cleanPhone = phone.replace(/[^0-9]/g, '');

    // Validate phone number length
    if (cleanPhone.length < 10) {
        alert('{{ __("Please enter a valid phone number") }}');
        return;
    }

    // Format phone number for WhatsApp (use the working format)
    let finalPhone = cleanPhone;

    // Ensure it starts with 964 (Iraq country code)
    if (!cleanPhone.startsWith('964')) {
        if (cleanPhone.length === 10) {
            finalPhone = '964' + cleanPhone; // Add Iraq country code
        }
    }

    // Encode message for URL (same as nutrition plan)
    const encodedMessage = encodeURIComponent(message);

    // Create WhatsApp URL (EXACT same logic as nutrition plan)
    let whatsappUrl;
    if (finalPhone && finalPhone.length >= 10) {
        whatsappUrl = `https://wa.me/${finalPhone}?text=${encodedMessage}`;
    } else {
        // Fallback: no phone number (same as nutrition plan)
        whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
    }

    // Log the WhatsApp URL for debugging
    console.log('WhatsApp URL:', whatsappUrl);

    // Open WhatsApp (EXACT same as nutrition plan)
    window.open(whatsappUrl, '_blank');
});


</script>
@endpush
