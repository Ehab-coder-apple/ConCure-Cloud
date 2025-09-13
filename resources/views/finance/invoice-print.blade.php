<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - Print</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                margin: 0;
                padding: 15px;
            }

            .page-break {
                page-break-before: always;
            }

            .container-fluid {
                max-width: 100% !important;
                padding: 0 !important;
            }

            .invoice-header {
                margin-bottom: 20px !important;
                padding-bottom: 15px !important;
            }

            .info-section {
                padding: 10px !important;
                margin-bottom: 10px !important;
            }
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            background: #fff;
        }

        .invoice-header {
            border-bottom: 4px solid #007bff;
            padding-bottom: 25px;
            margin-bottom: 35px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            padding: 25px;
        }

        .clinic-logo {
            max-height: 90px;
            max-width: 140px;
            object-fit: contain;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .clinic-name {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 12px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .clinic-info {
            font-size: 1rem;
            color: #5a6c7d;
            line-height: 1.6;
            margin-bottom: 4px;
        }

        .clinic-info i {
            color: #007bff;
            width: 16px;
            text-align: center;
        }

        .invoice-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #007bff;
            margin-bottom: 12px;
            text-shadow: 1px 1px 2px rgba(0,123,255,0.2);
            letter-spacing: 2px;
        }

        .invoice-number {
            font-size: 1.2rem;
            color: #6c757d;
            font-weight: 600;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        
        .info-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            min-height: 160px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
        }

        .info-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border-radius: 10px 10px 0 0;
        }

        .info-section h5 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }

        .info-section .small {
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .table {
            margin-bottom: 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 12px;
            font-size: 0.9rem;
        }

        .table td {
            border-color: #e9ecef;
            padding: 12px;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .total-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }

        .total-section .row {
            margin-bottom: 8px;
        }

        .total-section .row:last-child {
            border-top: 2px solid #007bff;
            padding-top: 10px;
            margin-top: 10px;
        }

        .status-badge {
            font-size: 1.1rem;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 8px;
            padding: 25px;
        }

        .invoice-footer p {
            margin-bottom: 8px;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .invoice-footer small {
            color: #6c757d;
            font-style: italic;
        }

        .print-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: 1px solid #e9ecef;
            z-index: 1000;
        }

        .print-actions .btn {
            min-width: 100px;
            font-weight: 600;
        }

        @media print {
            .print-actions {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button Only -->
    <div class="no-print">
        <a href="{{ route('finance.invoices') }}" class="btn btn-secondary" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
            <i class="fas fa-arrow-left me-2"></i>
            {{ __('Back') }}
        </a>
    </div>

    <div class="container-fluid">
        @php
            $clinicId = $invoice->clinic_id ?? auth()->user()->clinic_id ?? 2;
            $clinicLogo = \App\Http\Controllers\SettingsController::getClinicLogo($clinicId);
        @endphp

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <!-- Left Section: Logo -->
                <div class="col-3">
                    @if($clinicLogo)
                        <img src="{{ $clinicLogo }}" alt="" class="clinic-logo">
                    @endif
                </div>

                <!-- Middle Section: Clinic Name -->
                <div class="col-6 text-center">
                    <h1 class="clinic-name mb-0">{{ $invoice->clinic->name }}</h1>
                </div>

                <!-- Right Section: Invoice -->
                <div class="col-3 text-end">
                    <div style="background: #007bff; color: white; padding: 8px 16px; border-radius: 20px; font-size: 16px; font-weight: bold; display: inline-block; letter-spacing: 1px; margin-bottom: 8px;">{{ $invoice->invoice_number }}</div>
                    <div>
                        <span class="badge status-badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="row mb-3">
            <div class="col-6">
                <div class="info-section" style="height: 100%;">
                    <h5 class="mb-2">{{ __('Bill To:') }}</h5>
                    @if($invoice->patient)
                        <h6 class="mb-1">{{ $invoice->patient->first_name }} {{ $invoice->patient->last_name }}</h6>
                        @if($invoice->patient->phone)
                            <p class="mb-1 small"><i class="fas fa-phone me-1"></i>{{ $invoice->patient->phone }}</p>
                        @endif
                        @if($invoice->patient->email)
                            <p class="mb-1 small"><i class="fas fa-envelope me-1"></i>{{ $invoice->patient->email }}</p>
                        @endif
                        @if($invoice->patient->address)
                            <p class="mb-0 small"><i class="fas fa-map-marker-alt me-1"></i>{{ $invoice->patient->address }}</p>
                        @endif
                    @else
                        <p class="text-muted">{{ __('No patient assigned') }}</p>
                    @endif
                </div>
            </div>
            <div class="col-6">
                <div class="info-section" style="height: 100%;">
                    <h5 class="mb-2">{{ __('Invoice Details:') }}</h5>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="p-1"><strong>{{ __('Invoice Date:') }}</strong></td>
                            <td class="p-1">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="p-1"><strong>{{ __('Due Date:') }}</strong></td>
                            <td class="p-1">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : __('N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="p-1"><strong>{{ __('Payment Method:') }}</strong></td>
                            <td class="p-1">{{ $invoice->payment_method ? ucfirst($invoice->payment_method) : __('N/A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th class="text-center">{{ __('Quantity') }}</th>
                        <th class="text-end">{{ __('Unit Price') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->description }}</strong>
                            @if($item->item_type)
                                <br><small class="text-muted">{{ ucfirst($item->item_type) }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="row">
            <div class="col-md-6">
                @if($invoice->notes)
                <div class="info-section">
                    <h6>{{ __('Notes:') }}</h6>
                    <p>{{ $invoice->notes }}</p>
                </div>
                @endif
                
                @if($invoice->terms)
                <div class="info-section">
                    <h6>{{ __('Terms & Conditions:') }}</h6>
                    <p>{{ $invoice->terms }}</p>
                </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="total-section">
                    <div class="row mb-2">
                        <div class="col-6"><strong>{{ __('Subtotal:') }}</strong></div>
                        <div class="col-6 text-end">${{ number_format($invoice->subtotal, 2) }}</div>
                    </div>
                    
                    @if($invoice->discount_amount > 0)
                    <div class="row mb-2">
                        <div class="col-6">{{ __('Discount:') }}</div>
                        <div class="col-6 text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</div>
                    </div>
                    @endif
                    
                    @if($invoice->tax_amount > 0)
                    <div class="row mb-2">
                        <div class="col-6">{{ __('Tax') }} ({{ number_format($invoice->tax_rate, 1) }}%):</div>
                        <div class="col-6 text-end">${{ number_format($invoice->tax_amount, 2) }}</div>
                    </div>
                    @endif
                    
                    <hr>
                    <div class="row mb-2">
                        <div class="col-6"><h5>{{ __('Total Amount:') }}</h5></div>
                        <div class="col-6 text-end"><h5>${{ number_format($invoice->total_amount, 2) }}</h5></div>
                    </div>
                    
                    @if($invoice->paid_amount > 0)
                    <div class="row mb-2">
                        <div class="col-6">{{ __('Paid Amount:') }}</div>
                        <div class="col-6 text-end text-success">${{ number_format($invoice->paid_amount, 2) }}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>{{ __('Balance Due:') }}</strong></div>
                        <div class="col-6 text-end"><strong class="text-{{ $invoice->balance > 0 ? 'danger' : 'success' }}">${{ number_format($invoice->balance, 2) }}</strong></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>{{ __('Thank you for your business!') }}</strong></p>
            <p class="mb-2">{{ __('We appreciate your trust in our services.') }}</p>
            <small>{{ __('Generated on') }} {{ now()->format('M d, Y \a\t g:i A') }}</small>
        </div>

        <!-- Print Actions (positioned at bottom right like browser print dialog) -->
        <div class="no-print print-actions">
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>{{ __('Cancel') }}
                </button>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>{{ __('Print') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
