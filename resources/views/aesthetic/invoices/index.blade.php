@extends('layouts.app')

@section('title', __('Aesthetic Invoices'))

@php($currency = \DB::table('settings')->where('clinic_id', auth()->user()->clinic_id)->where('key', 'currency')->value('value') ?? 'USD')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                        {{ __('Aesthetic Invoices') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Manage billing and payments for aesthetic treatments') }}</p>
                </div>
                <a href="{{ route('aesthetic.invoices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    {{ __('New Invoice') }}
                </a>
            </div>

            <!-- Revenue Stats -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                            <h4 class="mb-1">{{ $currency }} {{ number_format($stats['total_revenue'], 2) }}</h4>
                            <small class="text-muted">{{ __('Total Revenue') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                            <h4 class="mb-1">{{ $currency }} {{ number_format($stats['outstanding'], 2) }}</h4>
                            <small class="text-muted">{{ __('Outstanding') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                            <h4 class="mb-1">{{ $stats['total'] }}</h4>
                            <small class="text-muted">{{ __('Total Invoices') }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                            <h4 class="mb-1">{{ $stats['paid'] }}</h4>
                            <small class="text-muted">{{ __('Paid') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('aesthetic.invoices.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Search') }}</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="{{ __('Invoice # or patient name...') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Status') }}</label>
                                <select class="form-select" name="status">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach(\App\Models\AestheticInvoice::STATUSES as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ __('Patient') }}</label>
                                <select class="form-select" name="patient_id">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->first_name }} {{ $patient->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Date Range') }}</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                                    <span class="input-group-text">to</span>
                                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>{{ __('Filter') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="card">
                <div class="card-body">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice #') }}</th>
                                        <th>{{ __('Patient') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Paid') }}</th>
                                        <th>{{ __('Balance') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number }}</strong>
                                            @if($invoice->session)
                                                <small class="d-block text-muted">{{ $invoice->session->patientPackage->package->name ?? '-' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</strong>
                                        </td>
                                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                        <td><strong>{{ $currency }} {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                        <td>{{ $currency }} {{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td>
                                            @if($invoice->balance > 0)
                                                <span class="text-danger">{{ $currency }} {{ number_format($invoice->balance, 2) }}</span>
                                            @else
                                                <span class="text-success">{{ $currency }} 0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $invoice->status_color }}">
                                                {{ $invoice->status_display }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('aesthetic.invoices.show', $invoice) }}"
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('aesthetic.invoices.receipt', $invoice) }}" target="_blank"
                                                   class="btn btn-sm btn-outline-secondary" title="{{ __('Receipt') }}">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if(!in_array($invoice->status, ['paid', 'cancelled']))
                                                    <a href="{{ route('aesthetic.invoices.edit', $invoice) }}"
                                                       class="btn btn-sm btn-outline-info" title="{{ __('Edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {{ $invoices->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('No invoices found') }}</h5>
                            <p class="text-muted">{{ __('Create your first invoice to start billing patients.') }}</p>
                            <a href="{{ route('aesthetic.invoices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>{{ __('Create First Invoice') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
