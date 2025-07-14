@extends('layouts.app')

@section('title', __('Financial Reports'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        {{ __('Financial Reports') }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.index') }}">{{ __('Finance') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('Reports') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('finance.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    {{ __('Back to Finance') }}
                </a>
            </div>

            <!-- Report Overview Cards -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-calendar-month fa-2x"></i>
                            </div>
                            <h5 class="card-title">{{ __('Current Month') }}</h5>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Revenue') }}</small>
                                    <div class="fw-bold text-success">${{ number_format($currentMonth['revenue'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Expenses') }}</small>
                                    <div class="fw-bold text-danger">${{ number_format($currentMonth['expenses'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Profit') }}</small>
                                    <div class="fw-bold {{ $currentMonth['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($currentMonth['profit'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <h5 class="card-title">{{ __('Previous Month') }}</h5>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Revenue') }}</small>
                                    <div class="fw-bold text-success">${{ number_format($previousMonth['revenue'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Expenses') }}</small>
                                    <div class="fw-bold text-danger">${{ number_format($previousMonth['expenses'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Profit') }}</small>
                                    <div class="fw-bold {{ $previousMonth['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($previousMonth['profit'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-calendar-year fa-2x"></i>
                            </div>
                            <h5 class="card-title">{{ __('Year to Date') }}</h5>
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Revenue') }}</small>
                                    <div class="fw-bold text-success">${{ number_format($yearToDate['revenue'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Expenses') }}</small>
                                    <div class="fw-bold text-danger">${{ number_format($yearToDate['expenses'], 2) }}</div>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted">{{ __('Profit') }}</small>
                                    <div class="fw-bold {{ $yearToDate['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($yearToDate['profit'], 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Types -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-exchange-alt text-primary me-2"></i>
                                {{ __('Cash Flow Report') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                {{ __('Track money coming in and going out of your clinic over time. Monitor cash flow patterns and identify trends.') }}
                            </p>
                            <div class="mb-3">
                                <h6 class="text-primary">{{ __('Features:') }}</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Daily cash flow tracking') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Income vs expenses comparison') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Net cash flow calculation') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Custom date ranges') }}</li>
                                </ul>
                            </div>
                            <a href="{{ route('finance.reports.cash-flow') }}" class="btn btn-primary">
                                <i class="fas fa-chart-line me-1"></i>
                                {{ __('View Cash Flow Report') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-pie text-success me-2"></i>
                                {{ __('Profit & Loss Report') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                {{ __('Analyze your clinic\'s profitability with detailed revenue and expense breakdowns by category and service type.') }}
                            </p>
                            <div class="mb-3">
                                <h6 class="text-success">{{ __('Features:') }}</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Revenue by service type') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Expenses by category') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Profit margin calculation') }}</li>
                                    <li><i class="fas fa-check text-success me-2"></i>{{ __('Period comparisons') }}</li>
                                </ul>
                            </div>
                            <a href="{{ route('finance.reports.profit-loss') }}" class="btn btn-success">
                                <i class="fas fa-chart-pie me-1"></i>
                                {{ __('View P&L Report') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Report Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-download text-info me-2"></i>
                                {{ __('Quick Report Actions') }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('finance.reports.cash-flow', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->endOfMonth()->format('Y-m-d')]) }}" 
                                       class="btn btn-outline-primary btn-lg w-100">
                                        <i class="fas fa-calendar-month d-block mb-1"></i>
                                        <small>{{ __('This Month Cash Flow') }}</small>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('finance.reports.profit-loss', ['date_from' => now()->startOfMonth()->format('Y-m-d'), 'date_to' => now()->endOfMonth()->format('Y-m-d')]) }}" 
                                       class="btn btn-outline-success btn-lg w-100">
                                        <i class="fas fa-chart-pie d-block mb-1"></i>
                                        <small>{{ __('This Month P&L') }}</small>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('finance.reports.cash-flow', ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
                                       class="btn btn-outline-info btn-lg w-100">
                                        <i class="fas fa-calendar-year d-block mb-1"></i>
                                        <small>{{ __('Year to Date Cash Flow') }}</small>
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('finance.reports.profit-loss', ['date_from' => now()->startOfYear()->format('Y-m-d'), 'date_to' => now()->format('Y-m-d')]) }}" 
                                       class="btn btn-outline-warning btn-lg w-100">
                                        <i class="fas fa-chart-bar d-block mb-1"></i>
                                        <small>{{ __('Year to Date P&L') }}</small>
                                    </a>
                                </div>
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
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .btn-lg {
        padding: 1rem;
    }
    .btn-lg i {
        font-size: 1.5rem;
    }
</style>
@endpush
