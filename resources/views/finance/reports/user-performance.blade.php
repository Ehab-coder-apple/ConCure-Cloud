@extends('layouts.app')

@section('title', __('Per User Financial Report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-users text-info me-2"></i>{{ __('Per User Financial Report') }}</h1>
            <p class="text-muted mb-0">{{ __('Revenue, collections, and expenses grouped by responsible user or assigned provider.') }}</p>
        </div>
        <a href="{{ route('finance.reports') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to Reports') }}</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.reports.user-performance') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $dateFrom->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="user_id" class="form-label">{{ __('User / Assigned Person') }}</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach($users as $reportUser)
                            <option value="{{ $reportUser->id }}" {{ (string) $selectedUserId === (string) $reportUser->id ? 'selected' : '' }}>{{ $reportUser->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="module" class="form-label">{{ __('Module') }}</label>
                    <select id="module" name="module" class="form-select">
                        <option value="">{{ __('All Modules') }}</option>
                        @foreach($reportData['available_modules'] as $module)
                            <option value="{{ $module }}" {{ $selectedModule === $module ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <div class="alert alert-info mt-3 mb-0 small"><i class="fas fa-info-circle me-2"></i>{{ $reportData['attribution_note'] }}</div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3"><div class="card border-primary"><div class="card-body text-center"><div class="text-muted small">{{ __('People') }}</div><div class="h4 mb-0">{{ $reportData['summary']['people_count'] }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-success"><div class="card-body text-center"><div class="text-muted small">{{ __('Billed') }}</div><div class="h4 mb-0 text-success">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($reportData['summary']['billed_revenue'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-info"><div class="card-body text-center"><div class="text-muted small">{{ __('Collected') }}</div><div class="h4 mb-0 text-info">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($reportData['summary']['collected_payments'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-secondary"><div class="card-body text-center"><div class="text-muted small">{{ __('Receipts') }}</div><div class="h4 mb-0">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($reportData['summary']['other_receipts'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-danger"><div class="card-body text-center"><div class="text-muted small">{{ __('Expenses') }}</div><div class="h4 mb-0 text-danger">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($reportData['summary']['expenses'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-dark"><div class="card-body text-center"><div class="text-muted small">{{ __('Net') }}</div><div class="h4 mb-0 {{ $reportData['summary']['net_total'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($reportData['summary']['net_total'], 2), '0'), '.') }}</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-table me-2 text-info"></i>{{ __('User Financial Breakdown') }}</h6>
            <span class="badge bg-info">{{ count($reportData['rows']) }}</span>
        </div>
        <div class="card-body">
            @if(empty($reportData['rows']))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-users-slash fa-2x mb-3"></i>
                    <p class="mb-0">{{ __('No financial activity found for the selected filters.') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Modules') }}</th>
                                <th>{{ __('Billed') }}</th>
                                <th>{{ __('Collected') }}</th>
                                <th>{{ __('Receipts') }}</th>
                                <th>{{ __('Total Revenue') }}</th>
                                <th>{{ __('Expenses') }}</th>
                                <th>{{ __('Net') }}</th>
                                <th>{{ __('Activity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['rows'] as $row)
                                <tr>
                                    <td><div class="fw-semibold">{{ $row['user_name'] }}</div><small class="text-muted text-capitalize">{{ str_replace('_', ' ', $row['role']) }}</small></td>
                                    <td>
                                        @foreach($row['modules'] as $module)
                                            <span class="badge bg-light text-dark border me-1 mb-1">{{ $module['label'] }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-success fw-semibold">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['billed_revenue'], 2), '0'), '.') }}</td>
                                    <td class="text-info fw-semibold">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['collected_payments'], 2), '0'), '.') }}</td>
                                    <td>{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['other_receipts'], 2), '0'), '.') }}</td>
                                    <td class="fw-semibold">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['total_revenue'], 2), '0'), '.') }}</td>
                                    <td class="text-danger fw-semibold">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['expenses'], 2), '0'), '.') }}</td>
                                    <td class="fw-semibold {{ $row['net_total'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($row['net_total'], 2), '0'), '.') }}</td>
                                    <td><small class="text-muted">{{ __('Invoices') }}: {{ $row['invoice_count'] }}<br>{{ __('Receipts') }}: {{ $row['receipt_count'] }}<br>{{ __('Expenses') }}: {{ $row['expense_count'] }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection