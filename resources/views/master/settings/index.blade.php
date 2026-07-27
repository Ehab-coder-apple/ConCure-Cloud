@extends('master.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cogs me-2"></i>System Settings</h1>
    <a href="{{ route('master.dashboard') }}" class="btn btn-outline-secondary">
      <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="row">
    <div class="col-lg-8">
      @if(auth()->user()?->isSuperAdmin())
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">General</h6></div>
        <div class="card-body">
          <p class="text-muted mb-3">Configure system-wide settings for the master admin interface.</p>

          <!-- Timezone Setting -->
          <form id="timezoneForm">
            @csrf
            <div class="row align-items-end">
              <div class="col-md-8">
                <label for="timezone" class="form-label">
                  <i class="fas fa-globe me-1"></i>
                  Master Admin Timezone
                </label>
                <select class="form-select" id="timezone" name="timezone" required>
                  @foreach($timezones as $value => $label)
                    <option value="{{ $value }}" {{ $masterTimezone == $value ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted">
                  This timezone will be used for all super admin activities and timestamps.
                  Current time: <span id="currentTime">{{ now()->format('Y-m-d H:i:s') }}</span>
                </small>
              </div>
              <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                  <i class="fas fa-save me-1"></i>
                  Update Timezone
                </button>
              </div>
            </div>
          </form>

          <hr class="my-4">

          <h6 class="text-muted mb-2">System Information</h6>
          <ul class="mb-0">
            <li>App Name: <span class="text-muted">{{ config('app.name') }}</span></li>
            <li>Environment: <span class="text-muted">{{ app()->environment() }}</span></li>
            <li>Current Timezone: <span class="text-muted">{{ config('app.timezone') }}</span></li>
          </ul>
        </div>
      </div>
      @endif

      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-image me-2"></i>Application Branding
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Upload the application logo used on the cover and page headers of generated PDFs (e.g. the Features List).
          </p>

          <div class="row align-items-start">
            <div class="col-md-4 text-center mb-3 mb-md-0">
              @if($brandingLogoUrl)
                <img src="{{ $brandingLogoUrl }}" alt="Current logo"
                     style="max-width: 100%; max-height: 140px; background: #f8f9fc; border: 1px solid #e3e6f0; border-radius: 6px; padding: 10px;">
                <div class="small text-muted mt-2">Current logo</div>
              @else
                <div class="d-flex flex-column align-items-center justify-content-center text-muted"
                     style="height: 140px; background: #f8f9fc; border: 1px dashed #d1d3e2; border-radius: 6px;">
                  <i class="fas fa-image fa-2x mb-2"></i>
                  <div class="small">No logo uploaded</div>
                </div>
              @endif
            </div>
            <div class="col-md-8">
              <form action="{{ route('master.settings.update-branding-logo') }}"
                    method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                  <label for="logo" class="form-label">
                    <i class="fas fa-upload me-1"></i>Upload Logo
                  </label>
                  <input type="file" class="form-control" id="logo" name="logo"
                         accept="image/png,image/jpeg,image/webp" required>
                  <small class="text-muted">
                    PNG, JPG or WebP. Maximum 2&nbsp;MB. A square PNG with transparent background is recommended.
                  </small>
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Logo
                  </button>
                  @if($brandingLogoUrl)
                    <button type="button" class="btn btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#removeLogoModal">
                      <i class="fas fa-trash me-1"></i> Remove
                    </button>
                  @endif
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      @if($brandingLogoUrl)
        <div class="modal fade" id="removeLogoModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Remove Branding Logo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                Are you sure you want to remove the current branding logo? PDFs will fall back to the text wordmark.
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('master.settings.delete-branding-logo') }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Remove Logo
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      @endif

      <!-- Contract Template Management -->
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3 bg-warning text-dark">
          <h6 class="m-0 font-weight-bold">
            <i class="fas fa-file-contract me-2"></i>Default Contract Template
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            Manage the default contract template used when creating new tenant clinics.
            This template can be customized per clinic during creation.
          </p>

          <form id="contractTemplateForm">
            @csrf
            <div class="mb-3">
              <label for="contract_template" class="form-label fw-bold">Contract Terms and Conditions</label>
              <textarea class="form-control font-monospace" id="contract_template" name="contract_template"
                        rows="20" style="font-size: 0.875rem;">{{ $defaultContractTemplate }}</textarea>
              <small class="text-muted">
                This template will be used as the default when creating new tenant clinics with contract requirements.
              </small>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Save Template
              </button>
              <button type="button" class="btn btn-outline-secondary" id="resetTemplateBtn">
                <i class="fas fa-undo me-1"></i> Reset to Default
              </button>
              <button type="button" class="btn btn-outline-info" id="previewTemplateBtn">
                <i class="fas fa-eye me-1"></i> Preview
              </button>
            </div>
          </form>

          <div id="templateMessage" class="mt-3" style="display: none;"></div>
        </div>
      </div>

      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users me-2"></i>User Management
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">Create and manage scoped Super Admin accounts with clinic allocations.</p>

          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="card border-primary">
                <div class="card-body text-center">
                  <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                  <h6 class="card-title">Create Super Admin</h6>
                  <p class="card-text text-muted small">Add a scoped Super Admin and assign the clinics they can manage</p>
                  <a href="{{ route('master.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Super Admin
                  </a>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card border-success">
                <div class="card-body text-center">
                  <i class="fas fa-users-cog fa-2x text-success mb-2"></i>
                  <h6 class="card-title">Manage Super Admins</h6>
                  <p class="card-text text-muted small">View, edit, and manage scoped Super Admin accounts</p>
                  <a href="{{ route('master.users.index') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-users me-1"></i>Manage Super Admins
                  </a>
                </div>
              </div>
            </div>
          </div>

          @php
            $userStats = [
              'total' => \App\Models\User::where('role', 'master_admin')->count(),
              'active' => \App\Models\User::where('role', 'master_admin')->where('is_active', true)->count(),
              'inactive' => \App\Models\User::where('role', 'master_admin')->where('is_active', false)->count(),
              'total_clinics' => \App\Models\Clinic::count(),
            ];
          @endphp

          <div class="row mt-3">
            <div class="col-md-3">
              <div class="text-center p-2 bg-light rounded">
                <h5 class="text-primary mb-1">{{ $userStats['total'] }}</h5>
                <small class="text-muted">Super Admins</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="text-center p-2 bg-light rounded">
                <h5 class="text-success mb-1">{{ $userStats['active'] }}</h5>
                <small class="text-muted">Active</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="text-center p-2 bg-light rounded">
                <h5 class="text-warning mb-1">{{ $userStats['inactive'] }}</h5>
                <small class="text-muted">Inactive</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="text-center p-2 bg-light rounded">
                <h5 class="text-info mb-1">{{ $userStats['total_clinics'] }}</h5>
                <small class="text-muted">Total Clinics</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Subscriptions</h6></div>
        <div class="card-body">
          <p class="mb-0 text-muted">Subscription management is active. Configure plans under <a href="{{ route('master.plans.index') }}">Plans</a>.</p>
        </div>
      </div>

      <!-- SQL Data Import -->
      <div class="card shadow-sm mb-4">
        <div class="card-header py-3 bg-warning text-dark">
          <h6 class="m-0 font-weight-bold">
            <i class="fas fa-database me-2"></i>SQL Data Import
          </h6>
        </div>
        <div class="card-body">
          <div class="alert alert-warning small mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <strong>Caution:</strong> This tool executes raw SQL statements directly on the database.
            Only use verified and trusted SQL files. Destructive statements (DROP, TRUNCATE, ALTER) are blocked.
            All statements run inside a transaction — if any statement fails, everything is rolled back.
          </div>

          <form id="sqlImportForm" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="import_clinic_id" class="form-label fw-bold">
                <i class="fas fa-clinic-medical me-1"></i>Target Clinic
              </label>
              <select class="form-select" id="import_clinic_id" name="clinic_id" required>
                <option value="">— Select Clinic —</option>
                @foreach($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }} (ID: {{ $clinic->id }})</option>
                @endforeach
              </select>
              <small class="text-muted">Choose which clinic this data belongs to.</small>
            </div>

            <div class="mb-3">
              <label for="sql_file" class="form-label fw-bold">
                <i class="fas fa-file-upload me-1"></i>SQL File
              </label>
              <input type="file" class="form-control" id="sql_file" name="sql_file" required>
              <small class="text-muted">Max 50MB. Only <code>.sql</code> files with INSERT/UPDATE statements.</small>
            </div>

            <button type="submit" class="btn btn-warning" id="sqlImportBtn">
              <i class="fas fa-upload me-1"></i>Import SQL Data
            </button>
          </form>

          <div id="sqlImportResult" class="mt-3" style="display:none;"></div>
        </div>
      </div>

      <!-- WhatsApp Setup Guide -->
      <div class="card shadow-sm mt-4">
        <div class="card-header py-3 bg-success text-white">
          <h6 class="m-0 font-weight-bold">
            <i class="fab fa-whatsapp me-2"></i>WhatsApp Setup Guide
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">Follow these steps to generate WhatsApp credentials for each clinic using the <strong>Meta WhatsApp Cloud API</strong> (free — 1,000 conversations/month per number).</p>

          <h6 class="fw-bold mb-2"><i class="fas fa-1 me-1 text-primary"></i> Step 1: Create a Meta Business Account</h6>
          <p class="small mb-2">If you don't already have one, create a free Meta Business account.</p>
          <a href="https://business.facebook.com/" target="_blank" class="btn btn-sm btn-outline-primary mb-3">
            <i class="fas fa-external-link-alt me-1"></i>business.facebook.com
          </a>

          <h6 class="fw-bold mb-2"><i class="fas fa-2 me-1 text-primary"></i> Step 2: Create a Meta Developer App</h6>
          <ol class="small mb-2">
            <li>Go to the Meta Developers portal</li>
            <li>Click <strong>"Create App"</strong> → choose <strong>"Other"</strong> → then <strong>"Business"</strong> type</li>
            <li>Name it (e.g., "ConCure WhatsApp") and link your Business Account</li>
          </ol>
          <a href="https://developers.facebook.com/apps/" target="_blank" class="btn btn-sm btn-outline-primary mb-3">
            <i class="fas fa-external-link-alt me-1"></i>developers.facebook.com/apps
          </a>

          <h6 class="fw-bold mb-2"><i class="fas fa-3 me-1 text-primary"></i> Step 3: Add the WhatsApp Product</h6>
          <ol class="small mb-2">
            <li>Inside your app, click <strong>"Add Products"</strong></li>
            <li>Find <strong>"WhatsApp"</strong> and click <strong>"Set Up"</strong></li>
            <li>Select your Business Account when prompted</li>
          </ol>

          <h6 class="fw-bold mb-2"><i class="fas fa-4 me-1 text-primary"></i> Step 4: Add a Phone Number</h6>
          <ol class="small mb-2">
            <li>Go to <strong>WhatsApp → API Setup</strong> in the left menu</li>
            <li>Click <strong>"Add phone number"</strong> to register a clinic's number</li>
            <li>Verify the number via SMS or voice call</li>
            <li>Copy the <strong>Phone Number ID</strong> shown below the number</li>
          </ol>
          <div class="alert alert-info small py-2 mb-3">
            <i class="fas fa-info-circle me-1"></i>
            You can add <strong>up to 250 phone numbers</strong> per Business Account — one per clinic.
          </div>

          <h6 class="fw-bold mb-2"><i class="fas fa-5 me-1 text-primary"></i> Step 5: Generate a Permanent Access Token</h6>
          <ol class="small mb-2">
            <li>Go to <strong>Business Settings → System Users</strong></li>
            <li>Create a System User (type: <strong>Admin</strong>) if you don't have one</li>
            <li>Click <strong>"Generate New Token"</strong></li>
            <li>Select your app and grant these permissions:
              <ul>
                <li><code>whatsapp_business_messaging</code></li>
                <li><code>whatsapp_business_management</code></li>
              </ul>
            </li>
            <li>Copy the generated token — <strong>this is your Access Token</strong></li>
          </ol>
          <a href="https://business.facebook.com/settings/system-users/" target="_blank" class="btn btn-sm btn-outline-primary mb-3">
            <i class="fas fa-external-link-alt me-1"></i>Business Settings → System Users
          </a>

          <div class="alert alert-warning small py-2 mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <strong>Important:</strong> The temporary token from "API Setup" expires in 24 hours. Always use a <strong>permanent token</strong> from System Users for production.
          </div>

          <h6 class="fw-bold mb-2"><i class="fas fa-6 me-1 text-primary"></i> Step 6: Enter Credentials in ConCure</h6>
          <ol class="small mb-2">
            <li>Go to <strong>Clinics → Edit Clinic</strong></li>
            <li>Find the <strong>"WhatsApp Configuration"</strong> card on the right sidebar</li>
            <li>Paste the <strong>Phone Number ID</strong> and <strong>Access Token</strong></li>
            <li>Click <strong>"Save & Connect"</strong> — credentials are verified automatically</li>
          </ol>

          <div class="alert alert-success small py-2 mb-0">
            <i class="fas fa-check-circle me-1"></i>
            <strong>Done!</strong> The clinic can now send WhatsApp messages to patients. Repeat steps 4–6 for each clinic.
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">About</h6></div>
        <div class="card-body">
          <p class="mb-2">Master settings page for administrators.</p>
          <p class="small text-muted mb-0">Version: {{ app()->version() }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current time every second
    function updateCurrentTime() {
        const now = new Date();
        const formatted = now.getFullYear() + '-' +
                         String(now.getMonth() + 1).padStart(2, '0') + '-' +
                         String(now.getDate()).padStart(2, '0') + ' ' +
                         String(now.getHours()).padStart(2, '0') + ':' +
                         String(now.getMinutes()).padStart(2, '0') + ':' +
                         String(now.getSeconds()).padStart(2, '0');
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.textContent = formatted;
        }
    }

    // Update time immediately and then every second
    updateCurrentTime();
    setInterval(updateCurrentTime, 1000);

    // Handle timezone form submission
    const timezoneForm = document.getElementById('timezoneForm');
    if (timezoneForm) {
        timezoneForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Updating...';

            fetch('{{ route("master.settings.update-timezone") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(this),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    timezoneForm.parentElement.insertBefore(alertDiv, timezoneForm);

                    // Reload page after 1 second to apply new timezone
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    timezoneForm.parentElement.insertBefore(alertDiv, timezoneForm);

                    // Re-enable button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    An error occurred while updating the timezone.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                timezoneForm.parentElement.insertBefore(alertDiv, timezoneForm);

                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // SQL Import form handler with background polling
    const sqlForm = document.getElementById('sqlImportForm');
    if (sqlForm) {
        sqlForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('sqlImportBtn');
            const resultDiv = document.getElementById('sqlImportResult');
            const clinicSelect = document.getElementById('import_clinic_id');
            const fileInput = document.getElementById('sql_file');
            const originalBtn = btn.innerHTML;
            let phaseLabel = 'Preparing';
            const autoLogoutPauseReason = 'sql-import';

            const pauseAutoLogout = () => {
                if (window.autoLogout && typeof window.autoLogout.pause === 'function') {
                    window.autoLogout.pause(autoLogoutPauseReason);
                }
            };

            const resumeAutoLogout = () => {
                if (window.autoLogout && typeof window.autoLogout.resume === 'function') {
                    window.autoLogout.resume(autoLogoutPauseReason);
                }
            };

            if (!confirm('Are you sure you want to import this SQL file? This will execute SQL statements directly on the database for the selected clinic.')) {
                return;
            }

            pauseAutoLogout();
            btn.disabled = true;
            resultDiv.style.display = 'none';

            // Elapsed timer
            let seconds = 0;
            const timer = setInterval(() => {
                seconds++;
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                const timeStr = mins > 0 ? `${mins}m ${secs}s` : `${secs}s`;
                btn.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${phaseLabel}... (${timeStr})`;
            }, 1000);

            try {
                const request = await buildSqlImportRequest(this, clinicSelect, fileInput);
                phaseLabel = 'Uploading';
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Uploading...';

                const response = await fetch(request.url, request.options);
                const data = await response.json();
                const status = response.status;

                if (data.background && data.status_url) {
                    // Background job started — poll for status
                    phaseLabel = 'Processing';
                    pollImportStatus(data.status_url, btn, resultDiv, originalBtn, timer, resumeAutoLogout);
                } else if (data.success) {
                    clearInterval(timer);
                    resumeAutoLogout();
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'mt-3 alert alert-success';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + data.message;
                    btn.disabled = false;
                    btn.innerHTML = originalBtn;
                } else {
                    clearInterval(timer);
                    resumeAutoLogout();
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'mt-3 alert alert-danger';
                    let msg = '<i class="fas fa-times-circle me-1"></i>' + data.message;
                    if (data.error) msg += '<br><small class="text-muted">' + data.error + '</small>';
                    resultDiv.innerHTML = msg;
                    btn.disabled = false;
                    btn.innerHTML = originalBtn;
                }
            } catch (error) {
                clearInterval(timer);
                resumeAutoLogout();
                resultDiv.style.display = 'block';
                resultDiv.className = 'mt-3 alert alert-danger';
                resultDiv.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + (error?.message || 'Upload failed. Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalBtn;
            }
        });
    }

    function getCsrfToken(form) {
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (metaToken) {
            return metaToken;
        }

        const formToken = form?.querySelector('input[name="_token"]')?.value;
        if (formToken) {
            return formToken;
        }

        throw new Error('CSRF token not found on page. Please refresh and try again.');
    }

    async function buildSqlImportRequest(form, clinicSelect, fileInput) {
        const csrfToken = getCsrfToken(form);
        const file = fileInput?.files?.[0];
        const clinicId = clinicSelect?.value;
        const importUrl = new URL('{{ route("master.settings.import-sql") }}', window.location.origin);

        if (!clinicId) {
            throw new Error('Please select a clinic.');
        }

        if (!file) {
            throw new Error('Please choose a SQL file.');
        }

        if (typeof CompressionStream === 'function') {
            importUrl.searchParams.set('clinic_id', clinicId);

            return {
                url: importUrl.toString(),
                options: {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/octet-stream',
                        'X-Sql-Import-Encoding': 'gzip',
                        'X-Sql-File-Name': encodeURIComponent(file.name),
                    },
                    body: await gzipFileForImport(file),
                }
            };
        }

        const formData = new FormData(form);

        return {
            url: importUrl.toString(),
            options: {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            }
        };
    }

    async function gzipFileForImport(file) {
        const compressedStream = file.stream().pipeThrough(new CompressionStream('gzip'));
        return await new Response(compressedStream).blob();
    }

    function pollImportStatus(statusUrl, btn, resultDiv, originalBtn, timer, onComplete) {
        let pollCount = 0;
        const maxPolls = 300; // 10 minutes at 2s intervals

        const poller = setInterval(() => {
            pollCount++;
            if (pollCount > maxPolls) {
                clearInterval(poller);
                clearInterval(timer);
                onComplete?.();
                resultDiv.style.display = 'block';
                resultDiv.className = 'mt-3 alert alert-warning';
                resultDiv.innerHTML = '<i class="fas fa-clock me-1"></i>Import is still running in the background. Check logs for results.';
                btn.disabled = false;
                btn.innerHTML = originalBtn;
                return;
            }

            fetch(statusUrl, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, data }) => {
                if (!ok && data.status !== 'unknown') {
                    clearInterval(poller);
                    clearInterval(timer);
                    onComplete?.();
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'mt-3 alert alert-danger';
                    resultDiv.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + (data.message || 'Unable to read import status.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtn;
                    return;
                }

                if (data.status === 'completed') {
                    clearInterval(poller);
                    clearInterval(timer);
                    onComplete?.();
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'mt-3 alert alert-success';
                    resultDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i>' + data.message;
                    btn.disabled = false;
                    btn.innerHTML = originalBtn;
                } else if (data.status === 'failed') {
                    clearInterval(poller);
                    clearInterval(timer);
                    onComplete?.();
                    resultDiv.style.display = 'block';
                    resultDiv.className = 'mt-3 alert alert-danger';
                    resultDiv.innerHTML = '<i class="fas fa-times-circle me-1"></i>' + data.message;
                    btn.disabled = false;
                    btn.innerHTML = originalBtn;
                }
                // else status is 'running' or 'queued' — keep polling
            })
            .catch(() => { /* ignore poll errors, keep trying */ });
        }, 2000); // Poll every 2 seconds
    }

    // Contract Template Management
    const contractForm = document.getElementById('contractTemplateForm');
    const contractTextarea = document.getElementById('contract_template');
    const resetBtn = document.getElementById('resetTemplateBtn');
    const previewBtn = document.getElementById('previewTemplateBtn');
    const messageDiv = document.getElementById('templateMessage');

    // Save template
    if (contractForm) {
        contractForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = contractForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            fetch('{{ route('master.settings.update-contract-template') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    contract_template: contractTextarea.value
                })
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    messageDiv.className = 'mt-3 alert alert-success';
                    messageDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + data.message;
                } else {
                    messageDiv.className = 'mt-3 alert alert-danger';
                    messageDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + data.message;
                }
                messageDiv.style.display = 'block';
                setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                messageDiv.className = 'mt-3 alert alert-danger';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Failed to save template.';
                messageDiv.style.display = 'block';
            });
        });
    }

    // Reset to default
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (!confirm('Reset contract template to default? Any customizations will be lost.')) {
                return;
            }

            const btn = this;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Resetting...';

            fetch('{{ route('master.settings.reset-contract-template') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.success) {
                    contractTextarea.value = data.template;
                    messageDiv.className = 'mt-3 alert alert-success';
                    messageDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + data.message;
                } else {
                    messageDiv.className = 'mt-3 alert alert-danger';
                    messageDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + data.message;
                }
                messageDiv.style.display = 'block';
                setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                messageDiv.className = 'mt-3 alert alert-danger';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Failed to reset template.';
                messageDiv.style.display = 'block';
            });
        });
    }

    // Preview template
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const previewWindow = window.open('', 'Contract Preview', 'width=800,height=600');
            previewWindow.document.write(`
                <html>
                <head>
                    <title>Contract Preview</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 40px; line-height: 1.6; }
                        h1 { color: #333; }
                        pre { white-space: pre-wrap; word-wrap: break-word; }
                    </style>
                </head>
                <body>
                    <h1>Contract Preview</h1>
                    <hr>
                    <pre>${contractTextarea.value}</pre>
                </body>
                </html>
            `);
        });
    }
});
</script>
@endpush

