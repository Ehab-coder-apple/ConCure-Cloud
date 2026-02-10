<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Treatment Plan - {{ $dentalTreatment->treatment_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #008080;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #008080;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #008080;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 35%;
            color: #555;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #333; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .badge-secondary { background-color: #6c757d; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .cost-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .cost-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .cost-label {
            display: table-cell;
            font-weight: bold;
            width: 70%;
        }
        .cost-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }
        .total-row {
            border-top: 2px solid #008080;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dental Treatment Plan</h1>
        <p><strong>Treatment Number:</strong> {{ $dentalTreatment->treatment_number }}</p>
        <p><strong>Date:</strong> {{ now()->format('F d, Y') }}</p>
    </div>

    <!-- Patient Information -->
    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Patient Name:</div>
                <div class="info-value">{{ $dentalTreatment->patient->full_name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Patient ID:</div>
                <div class="info-value">{{ $dentalTreatment->patient->patient_id ?? 'N/A' }}</div>
            </div>
            @if($dentalTreatment->patient->phone)
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $dentalTreatment->patient->phone }}</div>
            </div>
            @endif
            @if($dentalTreatment->patient->gender)
            <div class="info-row">
                <div class="info-label">Gender:</div>
                <div class="info-value">{{ ucfirst($dentalTreatment->patient->gender) }}</div>
            </div>
            @endif
            @if($dentalTreatment->patient->date_of_birth)
            <div class="info-row">
                <div class="info-label">Age:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($dentalTreatment->patient->date_of_birth)->age }} years</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Treatment Details -->
    <div class="section">
        <div class="section-title">Treatment Details</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Procedure:</div>
                <div class="info-value">{{ $dentalTreatment->procedure_name }}</div>
            </div>
            @if($dentalTreatment->procedure_code)
            <div class="info-row">
                <div class="info-label">Procedure Code:</div>
                <div class="info-value">{{ $dentalTreatment->procedure_code }}</div>
            </div>
            @endif
            @if($dentalTreatment->tooth_number)
            <div class="info-row">
                <div class="info-label">Primary Tooth:</div>
                <div class="info-value">{{ $dentalTreatment->tooth_number }}</div>
            </div>
            @endif
            @if($dentalTreatment->tooth_numbers && count($dentalTreatment->tooth_numbers) > 0)
            <div class="info-row">
                <div class="info-label">Affected Teeth:</div>
                <div class="info-value">{{ implode(', ', $dentalTreatment->tooth_numbers) }}</div>
            </div>
            @endif
            @if($dentalTreatment->surfaces_affected && count($dentalTreatment->surfaces_affected) > 0)
            <div class="info-row">
                <div class="info-label">Surfaces Affected:</div>
                <div class="info-value">{{ implode(', ', array_map('strtoupper', $dentalTreatment->surfaces_affected)) }}</div>
            </div>
            @endif
            @if($dentalTreatment->diagnosis)
            <div class="info-row">
                <div class="info-label">Diagnosis:</div>
                <div class="info-value">{{ $dentalTreatment->diagnosis }}</div>
            </div>
            @endif
            @if($dentalTreatment->icd10_code)
            <div class="info-row">
                <div class="info-label">ICD-10 Code:</div>
                <div class="info-value">{{ $dentalTreatment->icd10_code }}</div>
            </div>
            @endif
            @if($dentalTreatment->description)
            <div class="info-row">
                <div class="info-label">Description:</div>
                <div class="info-value">{{ $dentalTreatment->description }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Status & Priority -->
    <div class="section">
        <div class="section-title">Status & Priority</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    @php
                        $statusClass = match($dentalTreatment->status) {
                            'completed' => 'badge-success',
                            'in_progress' => 'badge-primary',
                            'cancelled' => 'badge-danger',
                            default => 'badge-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $dentalTreatment->status_display }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Priority:</div>
                <div class="info-value">
                    @php
                        $priorityClass = match($dentalTreatment->priority) {
                            'urgent' => 'badge-danger',
                            'high' => 'badge-warning',
                            'medium' => 'badge-secondary',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $priorityClass }}">{{ $dentalTreatment->priority_display }}</span>
                </div>
            </div>
            @if($dentalTreatment->severity)
            <div class="info-row">
                <div class="info-label">Severity:</div>
                <div class="info-value">{{ ucfirst($dentalTreatment->severity) }}</div>
            </div>
            @endif
            @if($dentalTreatment->scheduled_date)
            <div class="info-row">
                <div class="info-label">Scheduled Date:</div>
                <div class="info-value">{{ $dentalTreatment->scheduled_date->format('F d, Y') }}</div>
            </div>
            @endif
            @if($dentalTreatment->completed_date)
            <div class="info-row">
                <div class="info-label">Completed Date:</div>
                <div class="info-value">{{ $dentalTreatment->completed_date->format('F d, Y') }}</div>
            </div>
            @endif
            @if($dentalTreatment->estimated_duration_minutes)
            <div class="info-row">
                <div class="info-label">Estimated Duration:</div>
                <div class="info-value">{{ $dentalTreatment->estimated_duration_minutes }} minutes</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Assigned Personnel -->
    @if($dentalTreatment->assignedDoctor || $dentalTreatment->performedBy)
    <div class="section">
        <div class="section-title">Assigned Personnel</div>
        <div class="info-grid">
            @if($dentalTreatment->assignedDoctor)
            <div class="info-row">
                <div class="info-label">Assigned Doctor:</div>
                <div class="info-value">{{ $dentalTreatment->assignedDoctor->full_name }}</div>
            </div>
            @endif
            @if($dentalTreatment->performedBy)
            <div class="info-row">
                <div class="info-label">Performed By:</div>
                <div class="info-value">{{ $dentalTreatment->performedBy->full_name }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Cost & Payment Information -->
    <div class="section">
        <div class="section-title">Cost & Payment Information</div>
        <div class="cost-summary">
            @if($dentalTreatment->estimated_cost)
            <div class="cost-row">
                <div class="cost-label">Estimated Cost:</div>
                <div class="cost-value">{{ $dentalTreatment->currency ?? 'USD' }} {{ number_format($dentalTreatment->estimated_cost, 2) }}</div>
            </div>
            @endif
            @if($dentalTreatment->actual_cost)
            <div class="cost-row">
                <div class="cost-label">Actual Cost:</div>
                <div class="cost-value">{{ $dentalTreatment->currency ?? 'USD' }} {{ number_format($dentalTreatment->actual_cost, 2) }}</div>
            </div>
            @endif
            <div class="cost-row">
                <div class="cost-label">Payment Status:</div>
                <div class="cost-value">
                    @php
                        $paymentClass = match($dentalTreatment->payment_status) {
                            'paid' => 'badge-success',
                            'partial' => 'badge-warning',
                            default => 'badge-danger',
                        };
                    @endphp
                    <span class="badge {{ $paymentClass }}">{{ $dentalTreatment->payment_status_display }}</span>
                </div>
            </div>
            @if($dentalTreatment->paid_amount > 0)
            <div class="cost-row">
                <div class="cost-label">Amount Paid:</div>
                <div class="cost-value">{{ $dentalTreatment->currency ?? 'USD' }} {{ number_format($dentalTreatment->paid_amount, 2) }}</div>
            </div>
            @endif
            @if($dentalTreatment->remaining_balance > 0)
            <div class="cost-row total-row">
                <div class="cost-label">Remaining Balance:</div>
                <div class="cost-value" style="color: #dc3545;">{{ $dentalTreatment->currency ?? 'USD' }} {{ number_format($dentalTreatment->remaining_balance, 2) }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Notes -->
    @if($dentalTreatment->notes || $dentalTreatment->post_treatment_notes)
    <div class="section">
        <div class="section-title">Notes</div>
        @if($dentalTreatment->notes)
        <div style="margin-bottom: 15px;">
            <strong>Treatment Notes:</strong>
            <p style="margin: 5px 0; padding: 10px; background-color: #f8f9fa; border-left: 3px solid #008080;">
                {{ $dentalTreatment->notes }}
            </p>
        </div>
        @endif
        @if($dentalTreatment->post_treatment_notes)
        <div>
            <strong>Post-Treatment Notes:</strong>
            <p style="margin: 5px 0; padding: 10px; background-color: #f8f9fa; border-left: 3px solid #008080;">
                {{ $dentalTreatment->post_treatment_notes }}
            </p>
        </div>
        @endif
    </div>
    @endif

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
        <p>&copy; {{ now()->year }} ConCure Clinic Management System</p>
    </div>
</body>
</html>

