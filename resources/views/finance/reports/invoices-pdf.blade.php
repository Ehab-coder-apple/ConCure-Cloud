<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Invoices Report') }}</title>
    <style>
        @page {
            size: a4 {{ ($orientation ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait' }};
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 18px;
            color: #007bff;
            margin: 0 0 4px 0;
        }
        .subtitle {
            color: #666;
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px 6px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        .summary-table td {
            border: none;
            padding: 4px 10px 4px 0;
        }
        .summary-table .label {
            color: #666;
        }
        .text-right {
            text-align: right;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #198754;
        }
    </style>
</head>
<body>
    <h1>{{ __('Invoices Report') }}</h1>
    <div class="subtitle">
        {{ __('Period') }}: {{ $filters['date_from']->format('M d, Y') }} - {{ $filters['date_to']->format('M d, Y') }}
        @if($filters['category']) | {{ __('Category') }}: {{ ucfirst(str_replace('_', ' ', $filters['category'])) }} @endif
        @if($filters['item_type']) | {{ __('Item') }}: {{ ucfirst(str_replace('_', ' ', $filters['item_type'])) }} @endif
    </div>

    <table class="summary-table">
        <tr>
            <td class="label">{{ __('Invoices') }}:</td><td><strong>{{ $summary['count'] }}</strong></td>
            <td class="label">{{ __('Subtotal') }}:</td><td>{{ $currencySymbol }}{{ number_format($summary['subtotal'], 2) }}</td>
            <td class="label">{{ __('Tax') }}:</td><td>{{ $currencySymbol }}{{ number_format($summary['tax_amount'], 2) }}</td>
            <td class="label">{{ __('Discount') }}:</td><td>{{ $currencySymbol }}{{ number_format($summary['discount_amount'], 2) }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('Total') }}:</td><td><strong>{{ $currencySymbol }}{{ number_format($summary['total_amount'], 2) }}</strong></td>
            <td class="label">{{ __('Paid') }}:</td><td>{{ $currencySymbol }}{{ number_format($summary['paid_amount'], 2) }}</td>
            <td class="label">{{ __('Balance Due') }}:</td><td class="text-danger">{{ $currencySymbol }}{{ number_format($summary['balance'], 2) }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>{{ __('Invoice #') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Patient') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Item Type(s)') }}</th>
                <th>{{ __('Created By') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="text-right">{{ __('Total') }}</th>
                <th class="text-right">{{ __('Paid') }}</th>
                <th class="text-right">{{ __('Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->date ? $invoice->date->format('Y-m-d') : '-' }}</td>
                    <td>{{ $invoice->patient_name ?: '-' }}</td>
                    <td>{{ $invoice->category_label }}</td>
                    <td>{{ $invoice->item_types ?: '-' }}</td>
                    <td>{{ $invoice->user_name ?: '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</td>
                    <td class="text-right">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="text-right {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($invoice->balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="10" style="text-align:center;">{{ __('No invoices found for the selected filters.') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="color:#999; font-size:10px;">{{ __('Generated on') }} {{ now()->format('M d, Y H:i') }}</div>
</body>
</html>
