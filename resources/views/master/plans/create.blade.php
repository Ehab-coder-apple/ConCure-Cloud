@extends('master.layouts.app')

@section('title', 'New Plan')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-layer-group me-2"></i>Create Plan</h1>
    <a href="{{ route('master.plans.index') }}" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('master.plans.store') }}">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name') }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Monthly Price</label>
            <input name="monthly_price" type="number" step="0.01" class="form-control" value="{{ old('monthly_price', 0) }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Yearly Price</label>
            <input name="yearly_price" type="number" step="0.01" class="form-control" value="{{ old('yearly_price') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Max Users</label>
            <input name="max_users" type="number" class="form-control" value="{{ old('max_users') }}" placeholder="Leave blank for unlimited">
          </div>
          <div class="col-md-6">
            <label class="form-label">Features (comma separated)</label>
            <input name="features" class="form-control" value="{{ old('features') }}" placeholder="e.g. prescriptions,appointments,reports">
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" selected>Active</option>
              <option value="0">Inactive</option>
            </select>
          </div>
        </div>
        <div class="mt-4">
          <button class="btn btn-primary"><i class="fas fa-save me-2"></i>Create</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

