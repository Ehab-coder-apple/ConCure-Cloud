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
          <p class="text-muted mb-2">This is a placeholder settings page. We can add editable settings here as needed.</p>
          <ul class="mb-0">
            <li>App Name: <span class="text-muted">{{ config('app.name') }}</span></li>
            <li>Environment: <span class="text-muted">{{ app()->environment() }}</span></li>
            <li>Timezone: <span class="text-muted">{{ config('app.timezone') }}</span></li>
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

