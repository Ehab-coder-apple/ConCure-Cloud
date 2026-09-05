@extends('layouts.app')

@section('title', __('Inventory Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        {{ __('Inventory Report') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Sold vs. remaining stock and their financial value') }}</p>
                </div>
                <a href="{{ route('aesthetic.inventory.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Inventory') }}
                </a>
            </div>

            <!-- Period Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.inventory.report') }}" id="reportFilterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Period') }}</label>
                                <select class="form-select" name="period" id="periodSelect">
                                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>{{ __('Monthly (current month)') }}</option>
                                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>{{ __('Weekly (current week)') }}</option>
                                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>{{ __('Custom Period') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3 custom-period-field" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" class="form-control" name="start_date"
                                       value="{{ request('start_date', $startDate->toDateString()) }}">
                            </div>
                            <div class="col-md-3 custom-period-field" style="{{ $period === 'custom' ? '' : 'display:none;' }}">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" class="form-control" name="end_date"
                                       value="{{ request('end_date', $endDate->toDateString()) }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i>
                                    {{ __('Apply') }}
                                </button>
                            </div>
                        </div>
                    </form>
                    <p class="text-muted small mt-3 mb-0">
                        {{ __('Showing data for :start to :end', ['start' => $startDate->format('M d, Y'), 'end' => $endDate->format('M d, Y')]) }}
                    </p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-primary h-100">
                        <div class="card-body">
                            <div class="text-muted small">{{ __('Sold Quantity') }}</div>
                            <h4 class="mb-0">{{ number_format($totals['sold_quantity']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-success h-100">
                        <div class="card-body">
                            <div class="text-muted small">{{ __('Total Sold Value') }}</div>
                            <h4 class="mb-0">{{ $currency }} {{ number_format($totals['total_sold_value'], 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-info h-100">
                        <div class="card-body">
                            <div class="text-muted small">{{ __('Remaining Quantity') }}</div>
                            <h4 class="mb-0">{{ number_format($totals['remaining_quantity']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-secondary h-100">
                        <div class="card-body">
                            <div class="text-muted small">{{ __('Current Stock Value') }}</div>
                            <h4 class="mb-0">{{ $currency }} {{ number_format($totals['current_stock_value'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Table -->
            <div class="card">
                <div class="card-body">
                    @if($rows->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="text-end">{{ __('Sold Quantity') }}</th>
                                        <th class="text-end">{{ __('Total Sold Value') }}</th>
                                        <th class="text-end">{{ __('Remaining Quantity') }}</th>
                                        <th class="text-end">{{ __('Current Stock Value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $row)
                                    <tr>
                                        <td><strong>{{ $row->product->product_name }}</strong></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ \App\Models\AestheticInventory::TYPES[$row->product->type] ?? $row->product->type }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{ number_format($row->sold_quantity) }}</td>
                                        <td class="text-end">{{ $currency }} {{ number_format($row->total_sold_value, 2) }}</td>
                                        <td class="text-end">{{ number_format($row->remaining_quantity) }}</td>
                                        <td class="text-end">{{ $currency }} {{ number_format($row->current_stock_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="2">{{ __('Totals') }}</td>
                                        <td class="text-end">{{ number_format($totals['sold_quantity']) }}</td>
                                        <td class="text-end">{{ $currency }} {{ number_format($totals['total_sold_value'], 2) }}</td>
                                        <td class="text-end">{{ number_format($totals['remaining_quantity']) }}</td>
                                        <td class="text-end">{{ $currency }} {{ number_format($totals['current_stock_value'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0 py-4">{{ __('No products found in inventory.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.getElementById('periodSelect');
    const customFields = document.querySelectorAll('.custom-period-field');

    function toggleCustomFields() {
        const isCustom = periodSelect.value === 'custom';
        customFields.forEach(function (field) {
            field.style.display = isCustom ? '' : 'none';
        });
    }

    periodSelect.addEventListener('change', toggleCustomFields);
    toggleCustomFields();
});
</script>
@endpush
