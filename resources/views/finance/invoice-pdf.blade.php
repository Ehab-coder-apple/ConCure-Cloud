<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        .clinic-logo {
            max-height: 80px;
            max-width: 120px;
            object-fit: contain;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin: 0 0 5px 0;
        }
        
        .clinic-info {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }
        
        .invoice-title {
            font-size: 36px;
            font-weight: bold;
            color: #007bff;
            text-align: right;
            margin: 0;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            text-align: right;
            margin: 5px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            float: right;
        }
        
        .status-paid { background-color: #d4edda; color: #155724; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-overdue { background-color: #f8d7da; color: #721c24; }
        .status-draft { background-color: #e2e3e5; color: #383d41; }
        
        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 8px 0;
            vertical-align: top;
        }
        
        .info-table .label {
            font-weight: bold;
            width: 120px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background-color: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .totals-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 6px 0;
        }
        
        .totals-table .label {
            text-align: left;
            font-weight: bold;
        }
        
        .totals-table .amount {
            text-align: right;
            width: 120px;
        }
        
        .total-row {
            border-top: 2px solid #007bff;
            font-size: 16px;
            font-weight: bold;
        }
        
        .notes-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 11px;
            color: #666;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    @php
        $clinicId = $invoice->clinic_id ?? auth()->user()->clinic_id ?? 2;
        $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($clinicId);
    @endphp

    <!-- Header -->
    <div class="header">
        <table class="header-table">
            <tr>
                <!-- Left Section: Logo -->
                @if($clinicLogo)
                    <td style="width: 25%; vertical-align: middle; text-align: left; padding-right: 15px;">
                        <img src="{{ public_path('storage/' . str_replace('storage/', '', $clinicLogo)) }}"
                             alt=""
                             style="max-height: 110px; max-width: 115px; object-fit: contain; border-radius: 6px; border: 1px solid #dee2e6; padding: 2px; background: white;">
                    </td>
                @else
                    <td style="width: 25%;"></td>
                @endif

                <!-- Middle Section: Clinic Name -->
                <td style="width: 50%; vertical-align: middle; text-align: center;">
                    <h1 style="color: #007bff; margin: 0; font-size: 28px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; font-family: 'DejaVu Sans', Arial, sans-serif;">{{ $invoice->clinic->name ?? 'Clinic Name' }}</h1>
                </td>

                <!-- Right Section: Invoice -->
                <td style="width: 25%; vertical-align: middle; text-align: right; padding-left: 15px;">
                    <div style="background: #007bff; color: white; padding: 8px 16px; border-radius: 20px; font-size: 16px; font-weight: bold; display: inline-block; letter-spacing: 1px; margin-bottom: 8px;">{{ $invoice->invoice_number }}</div>
                    <div style="text-align: right;">
                        <span class="status-badge status-{{ $invoice->status }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Invoice Information -->
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-section">
                    <h3 style="margin-top: 0; color: #007bff;">Bill To:</h3>
                    @if($invoice->patient)
                        <strong>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</strong><br>
                        @if($invoice->patient->patient_id)
                            Patient ID: {{ $invoice->patient->patient_id }}<br>
                        @endif
                        @if($invoice->patient->phone)
                            Phone: {{ $invoice->patient->phone }}<br>
                        @endif
                        @if($invoice->patient->email)
                            Email: {{ $invoice->patient->email }}<br>
                        @endif
                        @if($invoice->patient->address)
                            Address: {{ $invoice->patient->address }}
                        @endif
                    @else
                        <em>No patient assigned</em>
                    @endif
                </div>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="info-section">
                    <h3 style="margin-top: 0; color: #007bff;">Invoice Details:</h3>
                    <table class="info-table">
                        <tr>
                            <td class="label">Invoice Date:</td>
                            <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                        </tr>
                        @if($invoice->due_date)
                        <tr>
                            <td class="label">Due Date:</td>
                            <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                        </tr>
                        @endif
                        @if($invoice->payment_method)
                        <tr>
                            <td class="label">Payment Method:</td>
                            <td>{{ ucfirst($invoice->payment_method) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Invoice Items -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 17.5%; text-align: right;">Unit Price</th>
                <th style="width: 17.5%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->items && $invoice->items->count() > 0)
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->item_type)
                            <br><small style="color: #666;">{{ ucfirst($item->item_type) }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #666; font-style: italic;">
                        No items added to this invoice
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Totals -->
    <div style="width: 50%; float: right;">
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="label">Discount:</td>
                    <td class="amount" style="color: #28a745;">-${{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="label">Tax ({{ number_format($invoice->tax_rate, 1) }}%):</td>
                    <td class="amount">${{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                
                <tr class="total-row">
                    <td class="label">Total Amount:</td>
                    <td class="amount">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                
                @if($invoice->paid_amount > 0)
                <tr>
                    <td class="label">Paid Amount:</td>
                    <td class="amount" style="color: #28a745;">${{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Balance Due:</td>
                    <td class="amount" style="color: {{ $invoice->balance > 0 ? '#dc3545' : '#28a745' }};">
                        ${{ number_format($invoice->balance, 2) }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    
    <div class="clearfix"></div>

    <!-- Notes and Terms -->
    @if($invoice->notes || $invoice->terms)
    <div class="notes-section">
        @if($invoice->notes)
        <div style="margin-bottom: 20px;">
            <div class="notes-title">Notes:</div>
            <div>{{ $invoice->notes }}</div>
        </div>
        @endif
        
        @if($invoice->terms)
        <div>
            <div class="notes-title">Terms & Conditions:</div>
            <div>{{ $invoice->terms }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for your business!</strong></p>
        <p>Generated on {{ now()->format('M d, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
