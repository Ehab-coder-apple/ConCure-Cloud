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

      <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-users me-2"></i>User Management
          </h6>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">Create and manage master-level users with custom permissions for system administration.</p>

          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="card border-primary">
                <div class="card-body text-center">
                  <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                  <h6 class="card-title">Create Master User</h6>
                  <p class="card-text text-muted small">Add new master-level users with system permissions</p>
                  <a href="{{ route('master.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Master User
                  </a>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="card border-success">
                <div class="card-body text-center">
                  <i class="fas fa-users-cog fa-2x text-success mb-2"></i>
                  <h6 class="card-title">Manage Master Users</h6>
                  <p class="card-text text-muted small">View, edit, and manage master-level users</p>
                  <a href="{{ route('master.users.index') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-users me-1"></i>Manage Master Users
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
                <small class="text-muted">Master Users</small>
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
});
</script>
@endpush

