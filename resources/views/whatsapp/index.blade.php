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

                    <!-- Meta WhatsApp Cloud API Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                @php
                                    $metaIsConfigured = !empty($metaConfig['configured']);
                                @endphp
                                <span class="info-box-icon bg-{{ $metaIsConfigured ? 'success' : 'warning' }}">
                                    <i class="fab fa-whatsapp"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('Meta WhatsApp Cloud API') }}</span>
                                    <span class="info-box-number">
                                        {{ $metaIsConfigured ? __('Connected') : __('Not Configured') }}
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar bg-{{ $metaIsConfigured ? 'success' : 'warning' }}"
                                             style="width: {{ $metaIsConfigured ? '100' : '30' }}%"></div>
                                    </div>
                                    <span class="progress-description">
                                        @if($metaIsConfigured)
                                            {{ $metaConfig['verified_name'] ?? $metaConfig['phone_display'] ?? __('Configured') }}
                                            @if(!empty($metaConfig['configured_at']))
                                                — {{ __('since') }} {{ \Carbon\Carbon::parse($metaConfig['configured_at'])->format('M d, Y') }}
                                            @endif
                                        @else
                                            {{ __('Enter your credentials below to connect') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($metaIsConfigured && !empty($metaConfig['phone_display']))
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ __('WhatsApp Number') }}</span>
                                    <span class="info-box-number">{{ $metaConfig['phone_display'] }}</span>
                                    <span class="progress-description">
                                        {{ $metaConfig['verified_name'] ?? __('Verified') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Meta WhatsApp Cloud API Configuration -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">
                                        <i class="fab fa-whatsapp"></i>
                                        {{ __('Meta WhatsApp Cloud API Configuration') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        {{ __('Free official WhatsApp API from Meta. 1,000 free conversations per month per phone number. No extra server needed.') }}
                                    </div>

                                    @if($metaIsConfigured)
                                    <div class="alert alert-success mb-3">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>{{ __('Connected') }}</strong> —
                                        {{ $metaConfig['verified_name'] ?? '' }}
                                        {{ !empty($metaConfig['phone_display']) ? '(' . $metaConfig['phone_display'] . ')' : '' }}
                                    </div>
                                    @endif

                                    <form id="metaConfigForm">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="meta_phone_number_id" class="form-label">{{ __('Phone Number ID') }}</label>
                                                <input type="text" class="form-control" id="meta_phone_number_id" name="meta_phone_number_id"
                                                       value="{{ $metaConfig['phone_number_id'] ?? '' }}"
                                                       placeholder="123456789012345" required>
                                                <small class="form-text text-muted">
                                                    {{ __('Your Phone Number ID from Meta Developer Dashboard') }}
                                                </small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="meta_access_token" class="form-label">{{ __('Permanent Access Token') }}</label>
                                                <input type="password" class="form-control" id="meta_access_token" name="meta_access_token"
                                                       placeholder="{{ $metaIsConfigured ? '••••••••••••••••' : 'EAAxxxxxxx...' }}" {{ $metaIsConfigured ? '' : 'required' }}>
                                                <small class="form-text text-muted">
                                                    {{ __('Your permanent access token from Meta Business Settings') }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success" id="btnSaveMeta">
                                                <i class="fas fa-save"></i>
                                                {{ $metaIsConfigured ? __('Update Configuration') : __('Save & Connect') }}
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Configuration Status -->
                                    <div id="metaConfigStatus" class="mt-3" style="display: none;"></div>

                                    <!-- Help Section -->
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#metaHelp">
                                            <i class="fas fa-question-circle"></i> {{ __('How to get Meta WhatsApp credentials?') }}
                                        </button>
                                        <div class="collapse mt-2" id="metaHelp">
                                            <div class="card card-body">
                                                <ol>
                                                    <li>{{ __('Go to') }} <a href="https://developers.facebook.com/" target="_blank">developers.facebook.com</a> {{ __('and create/log in to your account') }}</li>
                                                    <li>{{ __('Create a new app (type: Business) or use an existing one') }}</li>
                                                    <li>{{ __('Add the "WhatsApp" product to your app') }}</li>
                                                    <li>{{ __('In the WhatsApp section, go to "API Setup"') }}</li>
                                                    <li>{{ __('Copy your Phone Number ID (shown under the test number)') }}</li>
                                                    <li>{{ __('Generate a permanent access token from Business Settings → System Users') }}</li>
                                                    <li>{{ __('Paste both values above and click Save & Connect') }}</li>
                                                </ol>
                                                <div class="alert alert-warning mt-2 mb-0">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <strong>{{ __('Important:') }}</strong> {{ __('The temporary token from API Setup expires in 24 hours. For production, create a permanent token via System Users in Business Settings.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

@endsection

@push('scripts')
<script>
// Meta WhatsApp Cloud API Configuration Form
document.getElementById('metaConfigForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const phoneNumberId = document.getElementById('meta_phone_number_id').value;
    const accessToken = document.getElementById('meta_access_token').value;
    const statusDiv = document.getElementById('metaConfigStatus');
    const btn = document.getElementById('btnSaveMeta');

    if (!phoneNumberId.trim()) {
        alert('{{ __("Please enter the Phone Number ID") }}');
        return;
    }
    if (!accessToken.trim()) {
        alert('{{ __("Please enter the Access Token") }}');
        return;
    }

    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Verifying & saving...") }}';
    statusDiv.style.display = 'block';
    statusDiv.className = 'mt-3 alert alert-info';
    statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("Verifying credentials with Meta API...") }}';

    fetch('/whatsapp/configure/meta', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            meta_phone_number_id: phoneNumberId,
            meta_access_token: accessToken
        })
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> {{ __("Update Configuration") }}';
        if (data.success) {
            statusDiv.className = 'mt-3 alert alert-success';
            let info = '<i class="fas fa-check-circle"></i> ' + data.message;
            if (data.phone_display) info += '<br><strong>{{ __("Number") }}:</strong> ' + data.phone_display;
            if (data.verified_name) info += '<br><strong>{{ __("Business") }}:</strong> ' + data.verified_name;
            statusDiv.innerHTML = info;
            setTimeout(() => { location.reload(); }, 2500);
        } else {
            statusDiv.className = 'mt-3 alert alert-danger';
            statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (data.message || '{{ __("Failed to save configuration") }}');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> {{ __("Save & Connect") }}';
        statusDiv.className = 'mt-3 alert alert-danger';
        statusDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ __("Failed to save configuration:") }} ' + error.message;
    });
});


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


</script>
@endpush
