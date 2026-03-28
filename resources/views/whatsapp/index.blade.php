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

                    <!-- Twilio Configuration Section -->
                    @if(!$status['config_check']['twilio'])
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cog"></i>
                                        {{ __('Twilio WhatsApp Configuration') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        {{ __('Configure Twilio to send WhatsApp messages programmatically. This is the recommended method for production use.') }}
                                    </div>

                                    <form id="twilioConfigForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="twilio_sid" class="form-label">{{ __('Twilio Account SID') }}</label>
                                                <input type="text" class="form-control" id="twilio_sid" name="twilio_sid"
                                                       placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                                                <small class="form-text text-muted">
                                                    {{ __('Your Twilio Account SID from the Twilio Console') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="twilio_token" class="form-label">{{ __('Twilio Auth Token') }}</label>
                                                <input type="password" class="form-control" id="twilio_token" name="twilio_token"
                                                       placeholder="********************************" required>
                                                <small class="form-text text-muted">
                                                    {{ __('Your Twilio Auth Token from the Twilio Console') }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="twilio_from" class="form-label">{{ __('Twilio WhatsApp Number') }}</label>
                                                <input type="text" class="form-control" id="twilio_from" name="twilio_from"
                                                       value="whatsapp:+14155238886" placeholder="whatsapp:+14155238886" required>
                                                <small class="form-text text-muted">
                                                    {{ __('Your Twilio WhatsApp-enabled phone number (format: whatsapp:+1234567890)') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                                <button type="submit" class="btn btn-info">
                                                    <i class="fas fa-save"></i>
                                                    {{ __('Save Twilio Configuration') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Configuration Status -->
                                    <div id="twilioConfigStatus" class="mt-3" style="display: none;"></div>

                                    <!-- Help Section -->
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#twilioHelp">
                                            <i class="fas fa-question-circle"></i> {{ __('How to get Twilio credentials?') }}
                                        </button>
                                        <div class="collapse mt-2" id="twilioHelp">
                                            <div class="card card-body">
                                                <ol>
                                                    <li>{{ __('Sign up for a Twilio account at') }} <a href="https://www.twilio.com/try-twilio" target="_blank">https://www.twilio.com/try-twilio</a></li>
                                                    <li>{{ __('Go to the Twilio Console Dashboard') }}</li>
                                                    <li>{{ __('Copy your Account SID and Auth Token') }}</li>
                                                    <li>{{ __('Enable WhatsApp on your Twilio number or use the Twilio Sandbox') }}</li>
                                                    <li>{{ __('For sandbox, use: whatsapp:+14155238886') }}</li>
                                                    <li>{{ __('Paste the credentials above and click Save') }}</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- WPPConnect (Free) Configuration Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-server"></i>
                                        {{ __('WPPConnect (Free) — Self-Hosted WhatsApp') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-success">
                                        <i class="fas fa-heart text-danger"></i>
                                        {{ __('100% Free — No monthly fees. No per-message charges. Uses your own WhatsApp number.') }}
                                    </div>

                                    <form id="wppconnectForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="wppconnect_url" class="form-label">{{ __('WPPConnect Server URL') }}</label>
                                                <input type="url" class="form-control" id="wppconnect_url" name="wppconnect_url"
                                                       value="{{ auth()->user()->clinic->settings['whatsapp']['wppconnect_url'] ?? 'http://localhost:21465' }}"
                                                       placeholder="http://localhost:21465" required>
                                                <small class="form-text text-muted">
                                                    {{ __('The URL where your WPPConnect server is running') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="wppconnect_session" class="form-label">{{ __('Session Name (optional)') }}</label>
                                                <input type="text" class="form-control" id="wppconnect_session" name="wppconnect_session"
                                                       value="{{ auth()->user()->clinic->settings['whatsapp']['wppconnect_session'] ?? '' }}"
                                                       placeholder="{{ __('Auto-generated if empty') }}">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success" id="btnSaveWppconnect">
                                            <i class="fas fa-save"></i>
                                            {{ __('Save & Connect WPPConnect') }}
                                        </button>
                                    </form>

                                    <!-- QR Code Section -->
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
                                        <div class="text-center mt-2">
                                            <button type="button" class="btn btn-outline-success btn-sm" id="btnRefreshQr">
                                                <i class="fas fa-sync-alt"></i> {{ __('Refresh QR') }}
                                            </button>
                                            <button type="button" class="btn btn-outline-info btn-sm" id="btnCheckStatus">
                                                <i class="fas fa-check-circle"></i> {{ __('Check Connection') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Status -->
                                    <div id="wppconnectStatusBox" class="mt-3 d-none">
                                        <div class="alert" id="wppconnectStatusAlert"></div>
                                    </div>

                                    <!-- Help Section -->
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#wppconnectHelp">
                                            <i class="fas fa-question-circle"></i> {{ __('How to set up WPPConnect?') }}
                                        </button>
                                        <div class="collapse mt-2" id="wppconnectHelp">
                                            <div class="card card-body">
                                                <h6>{{ __('Server Installation') }}</h6>
                                                <pre class="bg-dark text-light p-2 rounded" style="white-space:pre-wrap">npm install -g @wppconnect-team/server
npx wppconnect-server</pre>
                                                <ol class="mt-2">
                                                    <li>{{ __('Install and run WPPConnect server on your machine or VPS') }}</li>
                                                    <li>{{ __('Enter the server URL above and click Save & Connect') }}</li>
                                                    <li>{{ __('Scan the QR code with your phone') }}</li>
                                                    <li>{{ __('Appointment reminders will be sent automatically!') }}</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

	                    <!-- Bulk Message to Patients -->
	                    <div class="row mt-5">
	                        <div class="col-md-10 offset-md-1">
	                            <h5 class="mb-3"><i class="fab fa-whatsapp text-success me-1"></i>{{ __('Send WhatsApp to Patients (Multi‑select)') }}</h5>
	                            <div class="card">
	                                <div class="card-body">
	                                    <form id="bulkForm">
	                                        <div class="row g-3 align-items-end">
	                                            <div class="col-md-4">
	                                                <label class="form-label d-block">{{ __('Patient status') }}</label>
	                                                <div class="d-flex gap-3">
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" checked>
	                                                        <label class="form-check-label" for="statusActive">{{ __('Active') }}</label>
	                                                    </div>
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive">
	                                                        <label class="form-check-label" for="statusInactive">{{ __('Inactive') }}</label>
	                                                    </div>
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="status" id="statusAll" value="all">
	                                                        <label class="form-check-label" for="statusAll">{{ __('All') }}</label>
	                                                    </div>
	                                                </div>
	                                                <small class="text-muted">{{ __('Only patients with a WhatsApp number are listed') }}</small>
	                                            </div>
	                                            <div class="col-md-4">
	                                                <label class="form-label d-block">{{ __('Type') }}</label>
	                                                <div class="d-flex gap-3">
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="whType" id="typeBoth" value="both" checked>
	                                                        <label class="form-check-label" for="typeBoth">{{ __('New or Updated') }}</label>
	                                                    </div>
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="whType" id="typeNew" value="new">
	                                                        <label class="form-check-label" for="typeNew">{{ __('New Registrations') }}</label>
	                                                    </div>
	                                                    <div class="form-check">
	                                                        <input class="form-check-input" type="radio" name="whType" id="typeUpdated" value="updated">
	                                                        <label class="form-check-label" for="typeUpdated">{{ __('Recently Updated') }}</label>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                            <div class="col-md-4">
	                                                <label class="form-label" for="sinceDate">{{ __('Since') }}</label>
	                                                <input type="date" class="form-control" id="sinceDate" value="{{ now()->subDays(30)->toDateString() }}">
	                                                <small class="text-muted">{{ __('Default is last 30 days') }}</small>
	                                            </div>
	                                        </div>
	                                        <div class="row g-3 align-items-end mt-2">
	                                            <div class="col-md-4">
	                                                <button type="button" class="btn btn-outline-primary" id="loadPatientsBtn">
	                                                    <i class="fas fa-users"></i> {{ __('Load Patients') }}
	                                                </button>
	                                            </div>
	                                            <div class="col-md-8 text-end">
	                                                <small id="patientsCount" class="text-muted"></small>
	                                            </div>
	                                        </div>

	                                        <div class="row mt-3">
	                                            <div class="col-12">
	                                                <label for="patientsSelect" class="form-label">{{ __('Select Patients') }}</label>
	                                                <div class="input-group mb-2">
	                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
	                                                    <input type="text" id="patientsFilter" class="form-control" placeholder="{{ __('Search name or phone') }}">
	                                                </div>
	                                                <select id="patientsSelect" class="form-select" multiple size="10"></select>
                                                <div class="form-text text-muted mb-2">{{ __('Tip: Click items to toggle selection. Use Select All/Clear. Hold Ctrl (Windows) or ⌘ Command (Mac) to select multiple.') }}</div>

	                                                <div id="noPatientsHint" class="text-muted small mt-2" style="display:none;">{{ __('No patients matched your filter') }}</div>
	                                                <div class="mt-2 d-flex gap-2">
	                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">{{ __('Select All') }}</button>
	                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllBtn">{{ __('Clear') }}</button>
	                                                </div>
	                                            </div>
	                                        </div>

	                                        <div class="row mt-3">
	                                            <div class="col-12">
	                                                <label for="bulk_message" class="form-label">{{ __('Message') }}</label>
	                                                <textarea id="bulk_message" class="form-control" rows="4" placeholder="{{ __('Type your message once…') }}" required></textarea>
	                                            </div>
	                                        </div>

	                                        <div class="mt-3 d-flex align-items-center gap-3">
	                                            <button type="submit" class="btn btn-success" id="bulkSendBtn">
	                                                <i class="fab fa-whatsapp"></i> {{ __('Send to Selected') }}
	                                            </button>
	                                            <small class="text-muted" id="bulkFeedback"></small>
	                                        </div>
	                                    </form>

	                                    <div id="bulkResults" class="mt-3" style="display:none;"></div>
	                                </div>
	                            </div>
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
// Twilio Configuration Form
document.getElementById('twilioConfigForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const twilioSid = document.getElementById('twilio_sid').value;
    const twilioToken = document.getElementById('twilio_token').value;
    const twilioFrom = document.getElementById('twilio_from').value;
    const statusDiv = document.getElementById('twilioConfigStatus');

    // Show loading
    statusDiv.style.display = 'block';
    statusDiv.className = 'mt-3 alert alert-info';
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Saving configuration...") }}';

    // Call save endpoint
    fetch('/whatsapp/configure/twilio', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            twilio_sid: twilioSid,
            twilio_token: twilioToken,
            twilio_from: twilioFrom
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.className = 'mt-3 alert alert-success';
            statusDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;

            // Reload page after 2 seconds to show updated status
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            statusDiv.className = 'mt-3 alert alert-danger';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || '{{ __("Failed to save configuration") }}');
        }
    })
    .catch(error => {
        statusDiv.className = 'mt-3 alert alert-danger';
        statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ __("Failed to save configuration:") }} ' + error.message;
    });
});

// QR Code display function (if needed for other purposes)
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


const patientsFilter = document.getElementById('patientsFilter');
const noPatientsHint = document.getElementById('noPatientsHint');
const sinceDateInput = document.getElementById('sinceDate');

let patientsCache = [];

// ---- Bulk messaging helpers ----
const patientsSelect = document.getElementById('patientsSelect');
const loadPatientsBtn = document.getElementById('loadPatientsBtn');
const patientsCount = document.getElementById('patientsCount');
const bulkFeedback = document.getElementById('bulkFeedback');

function getSelectedStatus(){
  return document.querySelector('input[name="status"]:checked')?.value || 'active';
}
function getSelectedType(){
  return document.querySelector('input[name="whType"]:checked')?.value || 'both';
}
function getSinceDate(){
  const v = sinceDateInput?.value;
  if(v) return v;
  const d = new Date(); d.setDate(d.getDate()-30);
  return d.toISOString().slice(0,10);
}

function renderPatients(list){
  patientsCache = Array.isArray(list) ? list : [];
  applyFilter();
}

function applyFilter(){
  const term = (patientsFilter?.value || '').trim().toLowerCase();
  const selectedIds = new Set(Array.from(patientsSelect.selectedOptions).map(o=>parseInt(o.value)));
  const filtered = term
    ? patientsCache.filter(p => (p.name||'').toLowerCase().includes(term) || (String(p.phone||'')).toLowerCase().includes(term))
    : patientsCache;
  patientsSelect.innerHTML = '';
  filtered.forEach(p => {
    const opt = document.createElement('option');
    opt.value = p.id;
    opt.textContent = `${p.name} — ${p.phone}`;
    if(selectedIds.has(p.id)) opt.selected = true;
    patientsSelect.appendChild(opt);
  });
  patientsCount.textContent = `${patientsCache.length} ${patientsCache.length===1?'patient':'patients'} {{ __('loaded') }}${term?` • ${filtered.length} {{ __('shown') }}`:''}`;
  if(noPatientsHint){ noPatientsHint.style.display = filtered.length===0 ? 'block' : 'none'; }
}

function loadPatients(){
  patientsCount.textContent = '{{ __('Loading...') }}';
  const qs = new URLSearchParams({ status: getSelectedStatus(), type: getSelectedType(), since: getSinceDate() }).toString();
  fetch(`/whatsapp/patients?${qs}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.success){
        renderPatients(data.patients);
        if((data.patients?.length||0)===0){
          // Show diagnostic counts and offer quick "Show all"
          const c = data.meta?.counts || {}; const f = data.meta?.filters || {};
          const msg = `0 {{ __('matched') }} • ${c.after_status??0} {{ __('after status') }} • ${c.with_whatsapp??0} {{ __('with WhatsApp') }}`;
          patientsCount.textContent = msg;
          // Add quick button once
          if(!document.getElementById('showAllBtn')){
            const btn = document.createElement('button');
            btn.id = 'showAllBtn'; btn.type='button';
            btn.className = 'btn btn-sm btn-outline-secondary ms-2';
            btn.textContent = '{{ __('Show all (ignore dates)') }}';
            btn.addEventListener('click', ()=>{
              const both = document.getElementById('typeBoth'); if(both){ both.checked = true; }
              if(sinceDateInput){ sinceDateInput.value = '1970-01-01'; }
              loadPatients();
            });
            patientsCount.parentElement?.appendChild(btn);
          }
        }
      } else { patientsCount.textContent = '{{ __('Failed to load') }}'; }
    })
    .catch(()=>{ patientsCount.textContent = '{{ __('Failed to load') }}'; });
}
loadPatientsBtn?.addEventListener('click', loadPatients);
// auto-load on page open
if(document.readyState==='loading'){
  document.addEventListener('DOMContentLoaded', ()=> setTimeout(loadPatients, 0));
}else{ setTimeout(loadPatients, 0); }

// filter as you type
patientsFilter?.addEventListener('input', applyFilter);

// reload list when status/type/date filter changes
Array.from(document.querySelectorAll('input[name="status"]')).forEach(r=> r.addEventListener('change', loadPatients));
Array.from(document.querySelectorAll('input[name="whType"]')).forEach(r=> r.addEventListener('change', loadPatients));
sinceDateInput?.addEventListener('change', loadPatients);

// Toggle selection on click (no need for Ctrl/Cmd)
patientsSelect?.addEventListener('mousedown', (e)=>{
  const target = e.target;
  if(target && target.tagName && target.tagName.toLowerCase()==='option'){
    e.preventDefault();
    const opt = target; const was = opt.selected;
    opt.selected = !was; // toggle
    patientsSelect.focus();
    const ev = new Event('change', {bubbles:true});
    patientsSelect.dispatchEvent(ev);
  }
});

document.getElementById('selectAllBtn')?.addEventListener('click', ()=>{
  Array.from(patientsSelect.options).forEach(o=>o.selected=true);
});

document.getElementById('clearAllBtn')?.addEventListener('click', ()=>{
  Array.from(patientsSelect.options).forEach(o=>o.selected=false);
});

document.getElementById('bulkForm')?.addEventListener('submit', function(e){
  e.preventDefault();
  const ids = Array.from(patientsSelect.selectedOptions).map(o=>parseInt(o.value));
  const message = document.getElementById('bulk_message').value.trim();
  if(ids.length===0){ alert('{{ __('Please select at least one patient') }}'); return; }
  if(!message){ alert('{{ __('Please enter a message') }}'); return; }
  bulkFeedback.textContent = '{{ __('Sending...') }}';
  fetch('/whatsapp/broadcast', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    body: JSON.stringify({ patient_ids: ids, message })
  }).then(r=>r.json()).then(data=>{
    if(!data.success){ bulkFeedback.textContent = '{{ __('Failed to send') }}'; return; }
    const sent = data.sent?.length||0; const pending = data.pending?.length||0; const failed = data.failed?.length||0;
    bulkFeedback.textContent = `{{ __('Done') }}: ${sent} {{ __('sent') }}, ${pending} {{ __('need manual WhatsApp open') }}, ${failed} {{ __('failed') }}`;
    const resultsDiv = document.getElementById('bulkResults');
    resultsDiv.style.display = 'block';
    let html = '';
    if(pending>0){
      html += '<div class="alert alert-warning"><strong>{{ __('Manual action required') }}:</strong> {{ __('Open WhatsApp for these patients') }}:</div>';
      html += '<ul class="list-group mb-3">';
      data.pending.forEach(p=>{
        html += `<li class="list-group-item d-flex justify-content-between align-items-center">${p.name} — ${p.phone}<a class="btn btn-sm btn-outline-success" target="_blank" href="${p.url}"><i class="fab fa-whatsapp"></i> {{ __('Open') }}</a></li>`;
      });
      html += '</ul>';
    }
    if(failed>0){
      html += '<div class="alert alert-danger"><strong>{{ __('Failed') }}:</strong></div><ul class="list-group">';
      data.failed.forEach(p=>{ html += `<li class="list-group-item">${p.name} — ${p.phone} <span class="text-danger">${p.error||''}</span></li>`; });
      html += '</ul>';
    }
    resultsDiv.innerHTML = html || '<div class="alert alert-success">{{ __('All messages sent successfully') }}</div>';
  }).catch(()=>{ bulkFeedback.textContent='{{ __('Failed to send') }}'; });
});


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

// ── WPPConnect ──
const wppQrSection = document.getElementById('wppconnectQrSection');
const wppQrContainer = document.getElementById('wppconnectQrContainer');
const wppStatusBox = document.getElementById('wppconnectStatusBox');
const wppStatusAlert = document.getElementById('wppconnectStatusAlert');

function showWppStatus(msg, type){
    if(wppStatusBox) wppStatusBox.classList.remove('d-none');
    if(wppStatusAlert){ wppStatusAlert.className='alert alert-'+type; wppStatusAlert.innerHTML=msg; }
}

// Save WPPConnect config
document.getElementById('wppconnectForm')?.addEventListener('submit', function(e){
    e.preventDefault();
    const btn = document.getElementById('btnSaveWppconnect');
    btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> {{ __("Saving...") }}';
    fetch('/whatsapp/configure/wppconnect',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({
        wppconnect_url: document.getElementById('wppconnect_url').value,
        wppconnect_session: document.getElementById('wppconnect_session').value
    })}).then(r=>r.json()).then(data=>{
        btn.disabled=false; btn.innerHTML='<i class="fas fa-save"></i> {{ __("Save & Connect WPPConnect") }}';
        if(data.success){
            showWppStatus('<i class="fas fa-check-circle"></i> '+data.message,'success');
            if(wppQrSection){ wppQrSection.classList.remove('d-none'); loadWppQrCode(); }
        } else {
            showWppStatus('<i class="fas fa-times-circle"></i> '+(data.message||'Error'),'danger');
        }
    }).catch(e=>{ btn.disabled=false; btn.innerHTML='<i class="fas fa-save"></i> {{ __("Save & Connect WPPConnect") }}'; showWppStatus(e.message,'danger'); });
});

// Load QR code
function loadWppQrCode(){
    if(!wppQrContainer) return;
    wppQrContainer.innerHTML='<div class="spinner-border text-success"></div><p class="mt-2">{{ __("Generating QR code...") }}</p>';
    fetch('/whatsapp/wppconnect/qr',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}})
        .then(r=>r.json()).then(data=>{
            if(data.connected){
                wppQrContainer.innerHTML='<div class="alert alert-success"><i class="fas fa-check-circle fa-2x"></i><br>{{ __("WhatsApp is connected!") }}</div>';
                showWppStatus('{{ __("✅ WhatsApp is connected and ready to send messages.") }}','success');
            } else if(data.qrcode){
                wppQrContainer.innerHTML='<img src="'+data.qrcode+'" alt="QR Code" style="max-width:300px" class="border rounded p-2">';
            } else {
                wppQrContainer.innerHTML='<p class="text-warning">{{ __("Could not generate QR code. Try refreshing.") }}</p>';
            }
        }).catch(e=>{wppQrContainer.innerHTML='<p class="text-danger">'+e.message+'</p>';});
}

document.getElementById('btnRefreshQr')?.addEventListener('click', loadWppQrCode);
document.getElementById('btnCheckStatus')?.addEventListener('click', function(){
    fetch('/whatsapp/wppconnect/status',{headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}})
        .then(r=>r.json()).then(data=>{
            if(data.connected){showWppStatus('<i class="fas fa-check-circle"></i> '+data.message,'success');}
            else{showWppStatus('<i class="fas fa-exclamation-triangle"></i> '+data.message,'warning');}
        }).catch(e=>showWppStatus(e.message,'danger'));
});

// Auto-show QR section if WPPConnect is already configured
@if(isset(auth()->user()->clinic->settings['whatsapp']['provider']) && auth()->user()->clinic->settings['whatsapp']['provider'] === 'wppconnect')
    if(wppQrSection){ wppQrSection.classList.remove('d-none'); loadWppQrCode(); }
@endif


</script>
@endpush
