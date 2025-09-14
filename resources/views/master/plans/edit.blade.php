@extends('master.layouts.app')

@section('title', 'Edit Plan - ' . $plan->name)

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-layer-group me-2"></i>Edit Plan</h1>
    <a href="{{ route('master.plans.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('master.plans.update', $plan) }}">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Monthly Price</label>
            <input name="monthly_price" type="number" step="0.01" class="form-control" value="{{ old('monthly_price', $plan->monthly_price) }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Yearly Price</label>
            <input name="yearly_price" type="number" step="0.01" class="form-control" value="{{ old('yearly_price', $plan->yearly_price) }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Max Users</label>
            <input name="max_users" type="number" class="form-control" value="{{ old('max_users', $plan->max_users) }}" placeholder="Leave blank for unlimited">
          </div>
          <div class="col-md-6">
            <label class="form-label">Features (comma separated)</label>
            <input name="features" class="form-control" value="{{ old('features', is_array($plan->features) ? implode(',', $plan->features) : '') }}" placeholder="e.g. prescriptions,appointments,reports">
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" {{ old('is_active', $plan->is_active) ? 'selected' : '' }}>Active</option>
              <option value="0" {{ old('is_active', $plan->is_active) ? '' : 'selected' }}>Inactive</option>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

