@extends('layouts.app')

@section('title', __('Profit & Loss Report'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-pie text-success me-2"></i>
                        {{ __('Profit & Loss Report') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.index') }}">{{ __('Finance') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.reports') }}">{{ __('Reports') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('P&L') }}</li>
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
                    <form method="GET" action="{{ route('finance.reports.profit-loss') }}" class="row g-3 align-items-end">
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
                <div class="col-md-3 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                            <h5 class="card-title text-success">{{ __('Total Revenue') }}</h5>
                            <h3 class="text-success">${{ number_format($profitLossData['revenue']['total'], 2) }}</h3>
                            <small class="text-muted">{{ __('All Income Sources') }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-receipt fa-2x"></i>
                            </div>
                            <h5 class="card-title text-danger">{{ __('Total Expenses') }}</h5>
                            <h3 class="text-danger">${{ number_format($profitLossData['expenses']['total'], 2) }}</h3>
                            <small class="text-muted">{{ __('All Operating Costs') }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-{{ $profitLossData['grossProfit'] >= 0 ? 'success' : 'danger' }}">
                        <div class="card-body text-center">
                            <div class="text-{{ $profitLossData['grossProfit'] >= 0 ? 'success' : 'danger' }} mb-2">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <h5 class="card-title text-{{ $profitLossData['grossProfit'] >= 0 ? 'success' : 'danger' }}">
                                {{ __('Gross Profit') }}
                            </h5>
                            <h3 class="text-{{ $profitLossData['grossProfit'] >= 0 ? 'success' : 'danger' }}">
                                ${{ number_format($profitLossData['grossProfit'], 2) }}
                            </h3>
                            <small class="text-muted">{{ __('Revenue - Expenses') }}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-percentage fa-2x"></i>
                            </div>
                            <h5 class="card-title text-info">{{ __('Profit Margin') }}</h5>
                            <h3 class="text-info">{{ number_format($profitLossData['profitMargin'], 1) }}%</h3>
                            <small class="text-muted">{{ __('Profitability Ratio') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue and Expense Breakdown -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar text-success me-2"></i>
                                {{ __('Revenue Breakdown') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($profitLossData['revenue']['byType']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Service Type') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                                <th class="text-end">{{ __('%') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($profitLossData['revenue']['byType'] as $type => $amount)
                                            @php
                                                $percentage = $profitLossData['revenue']['total'] > 0 
                                                    ? ($amount / $profitLossData['revenue']['total']) * 100 
                                                    : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                                                </td>
                                                <td class="text-end text-success fw-bold">
                                                    ${{ number_format($amount, 2) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($percentage, 1) }}%
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-success">
                                                <th>{{ __('Total Revenue') }}</th>
                                                <th class="text-end">${{ number_format($profitLossData['revenue']['total'], 2) }}</th>
                                                <th class="text-end">100%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('No revenue data found for the selected period.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie text-danger me-2"></i>
                                {{ __('Expense Breakdown') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($profitLossData['expenses']['byCategory']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Category') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                                <th class="text-end">{{ __('%') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($profitLossData['expenses']['byCategory'] as $category => $amount)
                                            @php
                                                $percentage = $profitLossData['expenses']['total'] > 0 
                                                    ? ($amount / $profitLossData['expenses']['total']) * 100 
                                                    : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge bg-danger">{{ ucfirst(str_replace('_', ' ', $category)) }}</span>
                                                </td>
                                                <td class="text-end text-danger fw-bold">
                                                    ${{ number_format($amount, 2) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($percentage, 1) }}%
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-danger">
                                                <th>{{ __('Total Expenses') }}</th>
                                                <th class="text-end">${{ number_format($profitLossData['expenses']['total'], 2) }}</th>
                                                <th class="text-end">100%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">{{ __('No expense data found for the selected period.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calculator text-primary me-2"></i>
                                {{ __('Profit & Loss Summary') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-lg">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">{{ __('Total Revenue') }}</td>
                                            <td class="text-end text-success fw-bold fs-5">
                                                ${{ number_format($profitLossData['revenue']['total'], 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Total Expenses') }}</td>
                                            <td class="text-end text-danger fw-bold fs-5">
                                                -${{ number_format($profitLossData['expenses']['total'], 2) }}
                                            </td>
                                        </tr>
                                        <tr class="table-{{ $profitLossData['grossProfit'] >= 0 ? 'success' : 'danger' }}">
                                            <td class="fw-bold fs-4">{{ __('Net Profit/Loss') }}</td>
                                            <td class="text-end fw-bold fs-4">
                                                ${{ number_format($profitLossData['grossProfit'], 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">{{ __('Profit Margin') }}</td>
                                            <td class="text-end text-info fw-bold fs-5">
                                                {{ number_format($profitLossData['profitMargin'], 2) }}%
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-3">{{ __('Report Actions') }}</h6>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>
                                    {{ __('Print Report') }}
                                </button>
                                <a href="{{ route('finance.reports.profit-loss', array_merge(request()->query(), ['format' => 'json'])) }}" 
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
