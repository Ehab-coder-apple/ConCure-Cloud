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

