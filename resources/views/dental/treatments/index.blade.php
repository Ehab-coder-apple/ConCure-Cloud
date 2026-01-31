@extends('layouts.app')

@section('title', __('Dental Treatments'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        {{ __('Dental Treatments') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage treatment plans and procedures') }}</p>
                </div>
                <div>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ url('/dental/treatments/create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            {{ __('New Treatment Plan') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ url('/dental/treatments') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select name="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>{{ __('Planned') }}</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Priority') }}</label>
                            <select name="priority" class="form-select">
                                <option value="">{{ __('All Priorities') }}</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>{{ __('Urgent') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Payment Status') }}</label>
                            <select name="payment_status" class="form-select">
                                <option value="">{{ __('All Payment Statuses') }}</option>
                                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>{{ __('Unpaid') }}</option>
                                <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">{{ __('Search') }}</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="{{ __('Patient name, treatment #') }}" value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatments List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Treatment Plans') }} ({{ $treatments->total() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($treatments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Treatment #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Procedure') }}</th>
                                        <th>{{ __('Tooth') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        <th>{{ __('Payment') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treatments as $treatment)
                                        <tr>
                                            <td>
                                                <strong>{{ $treatment->treatment_number }}</strong>
                                            </td>
                                            <td>
                                                <a href="{{ route('patients.show', $treatment->patient) }}" class="text-decoration-none">
                                                    {{ $treatment->patient->full_name }}
                                                </a>
                                                <br>
                                                <small class="text-muted">{{ $treatment->patient->patient_id }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $treatment->procedure_name }}</strong>
                                                @if($treatment->procedure_code)
                                                    <br><small class="text-muted">{{ $treatment->procedure_code }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($treatment->tooth_number)
                                                    <span class="badge bg-secondary">#{{ $treatment->tooth_number }}</span>
                                                @elseif($treatment->tooth_numbers && count($treatment->tooth_numbers) > 0)
                                                    <span class="badge bg-secondary">{{ count($treatment->tooth_numbers) }} teeth</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $treatment->status_badge_class }}">
                                                    {{ ucfirst(str_replace('_', ' ', $treatment->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $treatment->priority === 'urgent' ? 'danger' : ($treatment->priority === 'high' ? 'warning' : ($treatment->priority === 'medium' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($treatment->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $treatment->currency }} {{ number_format($treatment->estimated_cost ?? 0, 2) }}</strong>
                                                @if($treatment->actual_cost)
                                                    <br><small class="text-muted">Actual: {{ $treatment->currency }} {{ number_format($treatment->actual_cost, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $treatment->payment_status === 'paid' ? 'success' : ($treatment->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($treatment->payment_status) }}
                                                </span>
                                                @if($treatment->remaining_balance > 0)
                                                    <br><small class="text-muted">Due: {{ $treatment->currency }} {{ number_format($treatment->remaining_balance, 2) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $treatment->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ url("/dental/treatments/{$treatment->id}") }}" class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $treatments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-procedures fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No Treatment Plans Found') }}</h5>
                            <p class="text-muted">{{ __('Create your first treatment plan to get started') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

