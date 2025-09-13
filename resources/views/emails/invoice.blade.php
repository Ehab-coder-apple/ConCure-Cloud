<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        
        .email-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .clinic-name {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .invoice-title {
            font-size: 1.8rem;
            color: #007bff;
            margin: 20px 0;
        }
        
        .invoice-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .invoice-details td {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .invoice-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .total-section {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        
        .total-row.final {
            font-size: 1.2rem;
            font-weight: bold;
            border-top: 2px solid #007bff;
            padding-top: 10px;
            margin-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .status-draft { background: #6c757d; color: white; }
        .status-sent { background: #ffc107; color: #212529; }
        .status-paid { background: #28a745; color: white; }
        .status-overdue { background: #dc3545; color: white; }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            color: #6c757d;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="clinic-name">{{ $invoice->clinic->name }}</div>
            <div class="invoice-title">Invoice {{ $invoice->invoice_number }}</div>
            <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
        </div>

        <!-- Greeting -->
        <p>Dear {{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }},</p>
        
        <p>We hope this email finds you well. Please find attached your invoice for the services provided.</p>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <table>
                <tr>
                    <td>Invoice Date:</td>
                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                </tr>
                @if($invoice->due_date)
                <tr>
                    <td>Due Date:</td>
                    <td>{{ $invoice->due_date->format('M d, Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td>Patient:</td>
                    <td>{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</td>
                </tr>
                @if($invoice->patient->patient_id)
                <tr>
                    <td>Patient ID:</td>
                    <td>{{ $invoice->patient->patient_id }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Invoice Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->item_type)
                            <br><small style="color: #6c757d;">{{ ucfirst($item->item_type) }}</small>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 0) }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            
            @if($invoice->discount_amount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span style="color: #28a745;">-${{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            
            @if($invoice->tax_amount > 0)
            <div class="total-row">
                <span>Tax ({{ number_format($invoice->tax_rate, 1) }}%):</span>
                <span>${{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            
            <div class="total-row final">
                <span>Total Amount:</span>
                <span>${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            
            @if($invoice->paid_amount > 0)
            <div class="total-row">
                <span>Paid Amount:</span>
                <span style="color: #28a745;">${{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Balance Due:</span>
                <span style="color: {{ $invoice->balance > 0 ? '#dc3545' : '#28a745' }};">${{ number_format($invoice->balance, 2) }}</span>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div style="text-align: center; margin: 30px 0;">
            @if(isset($viewUrl))
                <a href="{{ $viewUrl }}" class="btn">View Invoice Online</a>
            @endif
            @if(isset($downloadUrl))
                <a href="{{ $downloadUrl }}" class="btn btn-secondary">Download PDF</a>
            @endif
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for choosing {{ $invoice->clinic->name }}!</strong></p>
            <p>If you have any questions about this invoice, please contact us.</p>
            
            @if($invoice->clinic->phone)
                <p>Phone: {{ $invoice->clinic->phone }}</p>
            @endif
            @if($invoice->clinic->email)
                <p>Email: {{ $invoice->clinic->email }}</p>
            @endif
            
            <small>This is an automated email. Please do not reply directly to this message.</small>
        </div>
    </div>
</body>
</html>
