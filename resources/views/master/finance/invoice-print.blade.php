<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            @page {
                margin: 1cm;
            }
        }
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-table th {
            background-color: #f8f9fa;
        }
        .total-row {
            font-weight: bold;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    @php
        $currencySymbol = $invoice->getCurrencySymbol();
    @endphp

    <div class="container my-4">
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('master.finance.invoice.pdf', $invoice) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('master.finance.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="invoice-title">ConCure Master</h1>
                    <p class="mb-1">SaaS Management</p>
                    <p class="mb-0 text-muted">Billing Invoice</p>
                </div>
                <div class="col-md-6 text-end">
                    <h2>INVOICE</h2>
                    <p class="mb-1"><strong>#{{ $invoice->invoice_number }}</strong></p>
                    <p class="mb-1">Date: {{ $invoice->invoice_date->format('M d, Y') }}</p>
                    <p class="mb-0">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Bill To:</h5>
                <p class="mb-1"><strong>{{ $invoice->clinic->name }}</strong></p>
                @if($invoice->clinic->address)
                    <p class="mb-1">{{ $invoice->clinic->address }}</p>
                @endif
                @if($invoice->clinic->city)
                    <p class="mb-1">{{ $invoice->clinic->city }}@if($invoice->clinic->state), {{ $invoice->clinic->state }}@endif</p>
                @endif
                @if($invoice->clinic->phone)
                    <p class="mb-1">Phone: {{ $invoice->clinic->phone }}</p>
                @endif
                @if($invoice->clinic->email)
                    <p class="mb-0">Email: {{ $invoice->clinic->email }}</p>
                @endif
            </div>
            <div class="col-md-6 text-end">
                <h5>Invoice Summary:</h5>
                <p class="mb-1">
                    Status: 
                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </p>
                @if($invoice->payment_date)
                    <p class="mb-0">Paid: {{ $invoice->payment_date->format('M d, Y') }}</p>
                @endif
            </div>
        </div>

        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 15%;">Quantity</th>
                    <th class="text-end" style="width: 17.5%;">Unit Price</th>
                    <th class="text-end" style="width: 17.5%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">{{ $currencySymbol }}{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                    <td class="text-end">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_rate > 0)
                    <tr>
                        <td colspan="3" class="text-end"><strong>Tax ({{ $invoice->tax_rate }}%):</strong></td>
                        <td class="text-end">{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="3" class="text-end"><strong>Discount:</strong></td>
                        <td class="text-end">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                    <td class="text-end">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @if($invoice->paid_amount > 0)
                    <tr>
                        <td colspan="3" class="text-end"><strong>Paid:</strong></td>
                        <td class="text-end text-success">-{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" class="text-end"><strong>BALANCE DUE:</strong></td>
                        <td class="text-end">{{ $currencySymbol }}{{ number_format($invoice->balance, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>

        @if($invoice->notes)
            <div class="mt-4">
                <h6>Notes:</h6>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif

        <div class="mt-5 text-center text-muted">
            <p>Thank you for your business!</p>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
