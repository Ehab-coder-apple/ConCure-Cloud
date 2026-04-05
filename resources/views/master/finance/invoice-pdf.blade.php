<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 5px 0;
        }
        
        .company-info {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0 10px 0;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .info-table td {
            vertical-align: top;
            padding: 5px;
        }
        
        .bill-to {
            font-size: 11px;
        }
        
        .bill-to strong {
            font-size: 12px;
            display: block;
            margin-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        .items-table td {
            border: 1px solid #dee2e6;
            padding: 8px 10px;
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-table {
            width: 100%;
            margin-top: 20px;
        }
        
        .summary-table td {
            padding: 5px 10px;
            font-size: 11px;
        }
        
        .summary-table .label {
            text-align: right;
            width: 70%;
            font-weight: bold;
        }
        
        .summary-table .amount {
            text-align: right;
            width: 30%;
        }
        
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 13px;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        
        .status-partial {
            background-color: #ffc107;
            color: #333;
        }
        
        .status-overdue {
            background-color: #dc3545;
            color: white;
        }
        
        .status-sent {
            background-color: #17a2b8;
            color: white;
        }
        
        .status-draft {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <div class="company-name">ConCure Master</div>
                    <div class="company-info">SaaS Management Platform</div>
                    <div class="company-info">Billing Invoice</div>
                </td>
                <td style="width: 50%;">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="section-title">Bill To:</div>
                <div class="bill-to">
                    <strong>{{ $invoice->clinic->name }}</strong>
                    @if($invoice->clinic->address)
                        {{ $invoice->clinic->address }}<br>
                    @endif
                    @if($invoice->clinic->city)
                        {{ $invoice->clinic->city }}@if($invoice->clinic->state), {{ $invoice->clinic->state }}@endif<br>
                    @endif
                    @if($invoice->clinic->phone)
                        Phone: {{ $invoice->clinic->phone }}<br>
                    @endif
                    @if($invoice->clinic->email)
                        Email: {{ $invoice->clinic->email }}
                    @endif
                </div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="section-title">Invoice Details:</div>
                <div class="bill-to">
                    <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}<br>
                    <strong>Status:</strong>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ strtoupper($invoice->status) }}
                    </span><br>
                    @if($invoice->payment_date)
                        <strong>Payment Date:</strong> {{ $invoice->payment_date->format('M d, Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th class="text-center" style="width: 15%;">Quantity</th>
                <th class="text-right" style="width: 17.5%;">Unit Price</th>
                <th class="text-right" style="width: 17.5%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->tax_rate > 0)
            <tr>
                <td class="label">Tax ({{ $invoice->tax_rate }}%):</td>
                <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
        @endif
        @if($invoice->discount_amount > 0)
            <tr>
                <td class="label">Discount:</td>
                <td class="amount">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="total-row">
            <td class="label">TOTAL:</td>
            <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        @if($invoice->paid_amount > 0)
            <tr>
                <td class="label">Paid:</td>
                <td class="amount" style="color: #28a745;">-{{ $currencySymbol }}{{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">BALANCE DUE:</td>
                <td class="amount">{{ $currencySymbol }}{{ number_format($invoice->balance, 2) }}</td>
            </tr>
        @endif
    </table>

    @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes:</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <div>Thank you for your business!</div>
        <div style="margin-top: 10px;">ConCure Master - SaaS Management Platform</div>
    </div>
</body>
</html>
