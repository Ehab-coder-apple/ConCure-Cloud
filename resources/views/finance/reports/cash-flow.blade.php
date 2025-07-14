@extends('layouts.app')

@section('title', __('Cash Flow Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-exchange-alt text-primary me-2"></i>
                        {{ __('Cash Flow Report') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.index') }}">{{ __('Finance') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.reports') }}">{{ __('Reports') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Cash Flow') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('finance.reports') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Reports') }}
                </a>
            </div>

            <!-- Date Range Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('finance.reports.cash-flow') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ $dateFrom->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ $dateTo->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>
                                {{ __('Generate Report') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-arrow-up fa-2x"></i>
                            </div>
                            <h5 class="card-title text-success">{{ __('Total Inflows') }}</h5>
                            <h3 class="text-success">${{ number_format($cashFlowData['totalInflows'], 2) }}</h3>
                            <small class="text-muted">{{ __('Revenue & Income') }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-arrow-down fa-2x"></i>
                            </div>
                            <h5 class="card-title text-danger">{{ __('Total Outflows') }}</h5>
                            <h3 class="text-danger">${{ number_format($cashFlowData['totalOutflows'], 2) }}</h3>
                            <small class="text-muted">{{ __('Expenses & Costs') }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-{{ $cashFlowData['netCashFlow'] >= 0 ? 'success' : 'danger' }}">
                        <div class="card-body text-center">
                            <div class="text-{{ $cashFlowData['netCashFlow'] >= 0 ? 'success' : 'danger' }} mb-2">
                                <i class="fas fa-{{ $cashFlowData['netCashFlow'] >= 0 ? 'plus' : 'minus' }}-circle fa-2x"></i>
                            </div>
                            <h5 class="card-title text-{{ $cashFlowData['netCashFlow'] >= 0 ? 'success' : 'danger' }}">
                                {{ __('Net Cash Flow') }}
                            </h5>
                            <h3 class="text-{{ $cashFlowData['netCashFlow'] >= 0 ? 'success' : 'danger' }}">
                                ${{ number_format($cashFlowData['netCashFlow'], 2) }}
                            </h3>
                            <small class="text-muted">{{ __('Inflows - Outflows') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cash Flow Details -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-arrow-up text-success me-2"></i>
                                {{ __('Cash Inflows') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($cashFlowData['inflows']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cashFlowData['inflows'] as $inflow)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($inflow->date)->format('M d, Y') }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($inflow->amount, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <th>{{ __('Total Inflows') }}</th>
                                                <th class="text-end">${{ number_format($cashFlowData['totalInflows'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('No cash inflows found for the selected period.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-arrow-down text-danger me-2"></i>
                                {{ __('Cash Outflows') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($cashFlowData['outflows']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Date') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cashFlowData['outflows'] as $outflow)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($outflow->date)->format('M d, Y') }}</td>
                                                <td class="text-end text-danger fw-bold">
                                                    ${{ number_format($outflow->amount, 2) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-danger">
                                                <th>{{ __('Total Outflows') }}</th>
                                                <th class="text-end">${{ number_format($cashFlowData['totalOutflows'], 2) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('No cash outflows found for the selected period.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-3">{{ __('Report Actions') }}</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>
                                    {{ __('Print Report') }}
                                </button>
                                <a href="{{ route('finance.reports.cash-flow', array_merge(request()->query(), ['format' => 'json'])) }}" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-download me-1"></i>
                                    {{ __('Export Data') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .breadcrumb, .card:first-child {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush
