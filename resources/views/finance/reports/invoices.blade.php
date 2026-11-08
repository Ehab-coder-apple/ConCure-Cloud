@extends('layouts.app')

@section('title', __('Invoices Report'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>{{ __('Invoices Report') }}</h1>
            <p class="text-muted mb-0">{{ __('Filter invoices by period, category, item type, and user.') }}</p>
        </div>
        <a href="{{ route('finance.reports') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>{{ __('Back to Reports') }}</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('finance.reports.invoices') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="date_from" class="form-label">{{ __('From Date') }}</label>
                    <input type="date" id="date_from" name="date_from" class="form-control" value="{{ $filters['date_from']->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">{{ __('To Date') }}</label>
                    <input type="date" id="date_to" name="date_to" class="form-control" value="{{ $filters['date_to']->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">{{ __('Category') }}</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ $filters['category'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="item_type" class="form-label">{{ __('Item Type') }}</label>
                    <select id="item_type" name="item_type" class="form-select">
                        <option value="">{{ __('All Items') }}</option>
                        @foreach(\App\Models\InvoiceItem::ITEM_TYPES as $value => $label)
                            <option value="{{ $value }}" {{ $filters['item_type'] === $value ? 'selected' : '' }}>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">{{ __('User') }}</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach($users as $reportUser)
                            <option value="{{ $reportUser->id }}" {{ (string) $filters['user_id'] === (string) $reportUser->id ? 'selected' : '' }}>{{ $reportUser->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-search me-1"></i>{{ __('Filter') }}</button>
                </div>
            </form>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('finance.reports.invoices.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i>{{ __('Export to Excel') }}
                </a>
                <a href="{{ route('finance.reports.invoices.print', request()->query()) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>{{ __('Print / PDF') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3"><div class="card border-primary"><div class="card-body text-center"><div class="text-muted small">{{ __('Invoices') }}</div><div class="h4 mb-0">{{ $summary['count'] }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-secondary"><div class="card-body text-center"><div class="text-muted small">{{ __('Subtotal') }}</div><div class="h5 mb-0">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($summary['subtotal'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-secondary"><div class="card-body text-center"><div class="text-muted small">{{ __('Tax') }}</div><div class="h5 mb-0">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($summary['tax_amount'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-secondary"><div class="card-body text-center"><div class="text-muted small">{{ __('Discount') }}</div><div class="h5 mb-0">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($summary['discount_amount'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-success"><div class="card-body text-center"><div class="text-muted small">{{ __('Total') }}</div><div class="h5 mb-0 text-success">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($summary['total_amount'], 2), '0'), '.') }}</div></div></div></div>
        <div class="col-md-2 col-6 mb-3"><div class="card border-danger"><div class="card-body text-center"><div class="text-muted small">{{ __('Balance Due') }}</div><div class="h5 mb-0 text-danger">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($summary['balance'], 2), '0'), '.') }}</div></div></div></div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>{{ __('Invoices') }}</h6>
            <span class="badge bg-primary">{{ $invoices->total() }}</span>
        </div>
        <div class="card-body">
            @if($invoices->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-invoice fa-2x mb-3"></i>
                    <p class="mb-0">{{ __('No invoices found for the selected filters.') }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice #') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Patient') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Item Type(s)') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Total') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                @php
                                    $category = $invoice->dentalTreatment ? __('Dental') : __(ucfirst(str_replace('_', ' ', $invoice->source_module ?: 'finance')));
                                    $itemTypes = $invoice->items->pluck('item_type')->filter()->unique()->map(fn ($t) => __(ucfirst(str_replace('_', ' ', $t))))->implode(', ');
                                @endphp
                                <tr>
                                    <td><a href="{{ route('finance.invoices.print', $invoice) }}" target="_blank">{{ $invoice->invoice_number }}</a></td>
                                    <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $invoice->patient ? $invoice->patient->full_name : '-' }}</td>
                                    <td><span class="badge bg-light text-dark">{{ $category }}</span></td>
                                    <td>{{ $itemTypes ?: '-' }}</td>
                                    <td>{{ $invoice->creator ? $invoice->creator->full_name : '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ __(ucfirst(str_replace('_', ' ', $invoice->status))) }}</span></td>
                                    <td>{{ $currencySymbol }}{{ rtrim(rtrim(number_format($invoice->total_amount, 2), '0'), '.') }}</td>
                                    <td>{{ $currencySymbol }}{{ rtrim(rtrim(number_format($invoice->paid_amount, 2), '0'), '.') }}</td>
                                    <td class="{{ $invoice->balance > 0 ? 'text-danger fw-semibold' : 'text-success' }}">{{ $currencySymbol }}{{ rtrim(rtrim(number_format($invoice->balance, 2), '0'), '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
