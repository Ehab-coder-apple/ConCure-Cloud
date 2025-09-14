@extends('master.layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-layer-group me-2"></i>Subscription Plans</h1>
    <a href="{{ route('master.plans.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>New Plan</a>
  </div>

  @if(!empty($missingTable))
    <div class="alert alert-warning d-flex align-items-center" role="alert">
      <i class="fas fa-database me-2"></i>
      <div>
        Subscription plans table is not migrated yet. Please run migrations on the server, then clear caches.
        <div class="small mt-2">
          <code>php artisan migrate</code> &nbsp; then &nbsp; <code>php artisan optimize:clear</code>
        </div>
      </div>
    </div>
  @endif


  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Monthly</th>
            <th>Yearly</th>
            <th>Max Users</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($plans as $plan)
            <tr>
              <td>{{ $plan->name }}</td>
              <td>${{ number_format($plan->monthly_price,2) }}</td>
              <td>{{ $plan->yearly_price ? '$'.number_format($plan->yearly_price,2) : '-' }}</td>
              <td>{{ $plan->max_users ?? 'Unlimited' }}</td>
              <td>
                @if($plan->is_active)
                  <span class="badge bg-success">Active</span>
                @else
                  <span class="badge bg-secondary">Inactive</span>
                @endif
              </td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('master.plans.edit', $plan) }}"><i class="fas fa-edit"></i></a>
                <form action="{{ route('master.plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No plans yet.</td></tr>
          @endforelse
        </tbody>
      </table>
      <div class="mt-3">{{ $plans->links() }}</div>
    </div>
  </div>
</div>
@endsection

