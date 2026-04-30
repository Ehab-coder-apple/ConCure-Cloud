@extends('master.layouts.app')

@section('title', 'Master Expenses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-money-bill-wave text-danger me-2"></i>
                Master Expenses
                <span class="badge bg-secondary ms-2">IQD</span>
            </h1>
            <p class="text-muted mb-0">Platform operational costs (super-admin only).</p>
        </div>
        <div>
            <a href="{{ route('master.finance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Finance
            </a>
            <button type="button" class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="fas fa-plus me-1"></i> Record Expense
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('master.finance.expenses') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All categories</option>
                        @foreach($expenseCategories as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['category'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="from" class="form-label">From</label>
                    <input type="date" class="form-control" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label">To</label>
                    <input type="date" class="form-control" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply
                    </button>
                    <a href="{{ route('master.finance.expenses') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
        <div>
            <i class="fas fa-info-circle me-2 text-info"></i>
            Showing <strong>{{ $expenses->total() }}</strong> expense(s) for the current filter.
        </div>
        <div>
            <span class="text-muted me-2">Total:</span>
            <strong class="text-danger">IQD {{ number_format($totalForFilter, 2) }}</strong>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            @if($expenses->count() > 0)
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Payment Method</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount (IQD)</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenses as $expense)
                                <tr>
                                    <td>{{ optional($expense->expense_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $expenseCategories[$expense->category] ?? ucfirst($expense->category) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $expense->description }}
                                        @if($expense->notes)
                                            <small class="d-block text-muted">{{ $expense->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->payment_method)
                                            {{ $expensePaymentMethods[$expense->payment_method] ?? ucfirst(str_replace('_', ' ', $expense->payment_method)) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->creator)
                                            {{ trim($expense->creator->first_name . ' ' . $expense->creator->last_name) ?: '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        {{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick='editExpense(@json($expense))' title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteExpense({{ $expense->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $expenses->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No expenses match the current filter.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('master.finance._expense-modals')
@endsection
