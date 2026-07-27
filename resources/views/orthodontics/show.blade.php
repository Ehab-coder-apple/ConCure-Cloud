@extends('layouts.app')

@section('title', __('Orthodontic Case Details'))

@push('styles')
<style>
/* Visual Tooth Chart Styles */
.tooth-arch-container {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    margin-bottom: 15px;
}

.tooth-row {
    position: relative;
}

.tooth-item {
    cursor: pointer;
    transition: transform 0.2s ease;
    text-align: center;
}

.tooth-item:hover {
    transform: translateY(-5px);
}

.tooth-box {
    width: 45px;
    height: 55px;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 8px 8px 15px 15px;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tooth-box:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #0d6efd;
}

.tooth-number {
    font-size: 10px;
    font-weight: bold;
    color: #6c757d;
    margin-bottom: 2px;
}

.tooth-status-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-top: 4px;
}

.tooth-label {
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
    font-weight: 600;
}

/* Primary teeth styling */
.primary-tooth {
    border-color: #0dcaf0;
    background: linear-gradient(135deg, #ffffff 0%, #e0f7ff 100%);
    border-width: 2px;
}

.primary-tooth .tooth-number {
    color: #0987a0;
    font-weight: 700;
    font-size: 12px;
}

.primary-tooth:hover {
    border-color: #0987a0;
}

/* Dentition toggle button styling */
#dentitionToggle .btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
}

#dentitionToggle .btn-outline-primary:hover {
    background-color: #0d6efd;
    color: white;
}

#dentitionToggle .btn-check:checked + .btn-outline-primary {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

/* Tooth Status Styles - Premium Colors */
.tooth-box.status-bracket-placed {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border-color: #0b5ed7;
    color: white;
}

.tooth-box.status-bracket-placed .tooth-number {
    color: rgba(255,255,255,0.9);
}

.tooth-box.status-bracket-placed .tooth-status-indicator {
    background: white;
    width: 16px;
    height: 16px;
}

.tooth-box.status-bracket-placed::after {
    content: '\f0c8';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    bottom: 8px;
    font-size: 14px;
    color: white;
}

.tooth-box.status-missing-bracket {
    background: white;
    border: 2px dashed #dc3545;
    animation: pulse-border 2s infinite;
}

.tooth-box.status-missing-bracket::after {
    content: '!';
    position: absolute;
    bottom: 8px;
    font-size: 18px;
    color: #dc3545;
    font-weight: bold;
}

@keyframes pulse-border {
    0%, 100% { border-color: #dc3545; }
    50% { border-color: #ff6b7a; }
}

.tooth-box.status-band {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: 3px solid #495057;
    color: white;
}

.tooth-box.status-band .tooth-number {
    color: rgba(255,255,255,0.9);
}

.tooth-box.status-band::after {
    content: '\f1ec';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    bottom: 8px;
    font-size: 12px;
    color: #ffc107;
}

.tooth-box.status-elastic-attachment {
    background: linear-gradient(135deg, #20c997 0%, #17a589 100%);
    border-color: #17a589;
    color: white;
}

.tooth-box.status-elastic-attachment .tooth-number {
    color: rgba(255,255,255,0.9);
}

.tooth-box.status-elastic-attachment .tooth-status-indicator {
    background: #ffc107;
    width: 12px;
    height: 12px;
}

.tooth-box.status-extraction-space {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    border-color: #343a40;
    opacity: 0.6;
    color: white;
}

.tooth-box.status-extraction-space .tooth-number {
    color: rgba(255,255,255,0.7);
}

.tooth-box.status-extraction-space::after {
    content: '\f00d';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: rgba(255,255,255,0.8);
}

/* Legend Styles */
.tooth-legend {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 10px;
}

.legend-box {
    width: 35px;
    height: 40px;
    border-radius: 6px 6px 12px 12px;
    display: inline-block;
    border: 2px solid #dee2e6;
    position: relative;
}

.legend-box.status-bracket-placed {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border-color: #0b5ed7;
}

.legend-box.status-missing-bracket {
    background: white;
    border: 2px dashed #dc3545;
}

.legend-box.status-band {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: 3px solid #495057;
}

.legend-box.status-elastic-attachment {
    background: linear-gradient(135deg, #20c997 0%, #17a589 100%);
    border-color: #17a589;
}

.legend-box.status-extraction-space {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    border-color: #343a40;
    opacity: 0.6;
}

.legend-box.status-normal {
    background: white;
    border: 2px solid #dee2e6;
}

.legend-item {
    font-size: 13px;
    padding: 5px 0;
}

/* Modal Status Options */
.tooth-status-option {
    cursor: pointer;
    transition: all 0.3s ease;
}

.tooth-status-option:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd !important;
}

.tooth-status-option input[type="radio"]:checked + label {
    font-weight: bold;
}

.status-preview {
    width: 40px;
    height: 45px;
    border-radius: 6px 6px 12px 12px;
    display: inline-block;
    border: 2px solid #dee2e6;
    flex-shrink: 0;
}

.status-preview.status-bracket-placed {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    border-color: #0b5ed7;
}

.status-preview.status-missing-bracket {
    background: white;
    border: 2px dashed #dc3545;
}

.status-preview.status-band {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border: 3px solid #495057;
}

.status-preview.status-elastic-attachment {
    background: linear-gradient(135deg, #20c997 0%, #17a589 100%);
    border-color: #17a589;
}

.status-preview.status-extraction-space {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    border-color: #343a40;
    opacity: 0.6;
}

.status-preview.status-normal {
    background: white;
    border: 2px solid #dee2e6;
}

.cursor-pointer {
    cursor: pointer;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tooth-box {
        width: 38px;
        height: 48px;
    }

    .tooth-row {
        gap: 1px !important;
    }

    .tooth-number {
        font-size: 9px;
    }

    .tooth-label {
        font-size: 10px;
    }
}

/* Treatment Timeline Styles */
.treatment-timeline-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.timeline-stepper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    padding: 40px 20px;
}

.timeline-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.timeline-step:hover .step-circle {
    transform: scale(1.15);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.3);
}

.timeline-connector {
    position: absolute;
    top: 40px;
    left: 50%;
    right: -50%;
    height: 4px;
    background: #dee2e6;
    z-index: 0;
}

.timeline-step:last-child .timeline-connector {
    display: none;
}

.timeline-connector.completed {
    background: linear-gradient(90deg, #198754 0%, #20c997 100%);
}

.step-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 4px solid transparent;
}

/* Pending Phase */
.timeline-step.pending .step-circle {
    background: #e9ecef;
    color: #adb5bd;
    border-color: #dee2e6;
}

/* Completed Phase */
.timeline-step.completed .step-circle {
    background: linear-gradient(135deg, #198754 0%, #20c997 100%);
    color: white;
    border-color: #198754;
}

.timeline-step.completed .step-circle::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 28px;
}

/* Current/Active Phase */
.timeline-step.current .step-circle {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-color: #0d6efd;
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.6);
        transform: scale(1.05);
    }
}

.timeline-step.current .step-circle::before {
    content: '';
    position: absolute;
    top: -8px;
    left: -8px;
    right: -8px;
    bottom: -8px;
    border-radius: 50%;
    border: 3px solid rgba(13, 110, 253, 0.3);
    animation: pulse-ring 2s infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(0.95);
        opacity: 1;
    }
    100% {
        transform: scale(1.05);
        opacity: 0;
    }
}

.step-label {
    margin-top: 15px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    color: #6c757d;
    max-width: 140px;
    line-height: 1.3;
}

.timeline-step.completed .step-label {
    color: #198754;
}

.timeline-step.current .step-label {
    color: #0d6efd;
    font-weight: 700;
}

.step-number {
    font-size: 24px;
    font-weight: bold;
}

.timeline-step.completed .step-number {
    display: none;
}

.timeline-step.current .step-number {
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.step-date {
    font-size: 11px;
    color: #6c757d;
    margin-top: 5px;
    font-weight: 400;
}

.timeline-step.current .step-date {
    color: #0d6efd;
    font-weight: 600;
}

.timeline-quick-update {
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    margin-top: 30px;
}

/* Mobile Responsive */
@media (max-width: 992px) {
    .timeline-stepper {
        flex-direction: column;
        padding: 20px 10px;
    }

    .timeline-step {
        width: 100%;
        flex-direction: row;
        justify-content: flex-start;
        margin-bottom: 30px;
        text-align: left;
    }

    .timeline-step:last-child {
        margin-bottom: 0;
    }

    .timeline-connector {
        top: 80px;
        left: 40px;
        right: auto;
        width: 4px;
        height: calc(100% + 30px);
    }

    .step-circle {
        width: 70px;
        height: 70px;
        font-size: 28px;
        margin-right: 20px;
    }

    .step-label {
        text-align: left;
        margin-top: 0;
        max-width: none;
    }

    .step-info {
        flex: 1;
    }
}

@media (max-width: 576px) {
    .step-circle {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }

    .step-label {
        font-size: 13px;
    }

    .step-number {
        font-size: 20px;
    }
}

/* Photo Gallery Enhanced Styles */
.photo-gallery-card {
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.photo-card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
}

.photo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border-color: #0d6efd;
}

.photo-card.selected {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
}

.photo-card img {
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-card:hover img {
    transform: scale(1.05);
}

.photo-select-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    width: 28px;
    height: 28px;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-card:hover .photo-select-checkbox,
.photo-card.selected .photo-select-checkbox {
    opacity: 1;
}

.photo-badge {
    font-size: 10px;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.photo-type-badge {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.view-type-badge {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.stage-badge {
    background: linear-gradient(135deg, #20c997 0%, #17a589 100%);
}

.photo-actions {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    display: flex;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-card:hover .photo-actions {
    opacity: 1;
}

.compare-toolbar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border: 2px solid #dee2e6;
}

.timeline-month-header {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 10px 10px 0 0;
    font-weight: 600;
    margin-top: 20px;
}

.timeline-photo-grid {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 0 0 10px 10px;
    margin-bottom: 20px;
}

.nav-tabs .nav-link {
    border-radius: 10px 10px 0 0;
    font-weight: 600;
    color: #6c757d;
    border: 2px solid transparent;
    margin-right: 5px;
}

.nav-tabs .nav-link:hover {
    color: #0d6efd;
    border-color: #e9ecef #e9ecef #dee2e6;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
    font-weight: 700;
}

.comparison-image {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.comparison-label {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    padding: 8px 15px;
    border-radius: 8px;
    font-weight: 600;
    margin-bottom: 10px;
    display: inline-block;
}

@media (max-width: 768px) {
    .photo-select-checkbox {
        opacity: 1;
    }

    .photo-actions {
        opacity: 1;
    }
}

/* Payment Timeline Styles */
.payment-timeline {
    position: relative;
    padding: 15px 0;
}

.payment-timeline-item {
    position: relative;
    padding-left: 45px;
    padding-bottom: 25px;
}

.payment-timeline-item:last-child {
    padding-bottom: 0;
}

.payment-timeline-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 30px;
    bottom: -10px;
    width: 2px;
    background: #e9ecef;
}

.payment-timeline-item:last-child::before {
    display: none;
}

.payment-timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 2;
}

.payment-timeline-marker.paid {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.payment-timeline-marker.paid::after {
    content: '\f00c';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.payment-timeline-marker.pending {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: white;
}

.payment-timeline-marker.pending::after {
    content: '\f017';
    font-family: 'Font Awesome 5 Free';
    font-weight: 400;
}

.payment-timeline-marker.overdue {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.payment-timeline-marker.overdue::after {
    content: '\f071';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
}

.payment-timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px 15px;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.payment-timeline-content:hover {
    background: #e9ecef;
    transform: translateX(3px);
}

.payment-timeline-item.paid .payment-timeline-content {
    border-left-color: #28a745;
}

.payment-timeline-item.pending .payment-timeline-content {
    border-left-color: #ffc107;
}

.payment-timeline-item.overdue .payment-timeline-content {
    border-left-color: #dc3545;
}

.payment-timeline-month {
    font-weight: 700;
    font-size: 14px;
    color: #495057;
    margin-bottom: 4px;
}

.payment-timeline-amount {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.payment-timeline-amount.paid {
    color: #28a745;
}

.payment-timeline-amount.pending {
    color: #6c757d;
}

.payment-timeline-amount.overdue {
    color: #dc3545;
}

.payment-timeline-status {
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.payment-timeline-details {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
}

.installment-badge {
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 4px;
    font-weight: 600;
    margin-left: 8px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-teeth me-2 text-primary"></i>
                        {{ __('Orthodontic Case') }} #{{ $orthodonticCase->case_number }}
                    </h1>
                    <p class="text-muted mb-0">{{ $orthodonticCase->patient->full_name }}</p>
                </div>
                <div>
                    <a href="{{ route('orthodontics.index') }}" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-print me-1"></i>{{ __('Print') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('orthodontics.invoice', [$orthodonticCase, 'auto' => 1]) }}" target="_blank">
                                    <i class="fas fa-file-invoice me-2"></i>{{ __('Regular Invoice') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('orthodontics.receipt', [$orthodonticCase, 'width' => 80]) }}" target="_blank">
                                    <i class="fas fa-receipt me-2"></i>{{ __('Thermal Receipt 80mm') }}
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('orthodontics.receipt', [$orthodonticCase, 'width' => 58]) }}" target="_blank">
                                    <i class="fas fa-receipt me-2"></i>{{ __('Thermal Receipt 58mm') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a href="{{ route('orthodontics.edit', $orthodonticCase) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>
                        {{ __('Edit Case') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Case Overview -->
        <div class="col-md-8">
            <!-- Patient & Treatment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>{{ __('Case Information') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">{{ __('Case Number') }}:</dt>
                                <dd class="col-sm-7">{{ $orthodonticCase->case_number }}</dd>

                                <dt class="col-sm-5">{{ __('Patient') }}:</dt>
                                <dd class="col-sm-7">
                                    <a href="{{ route('patients.show', $orthodonticCase->patient) }}">
                                        {{ $orthodonticCase->patient->full_name }}
                                    </a>
                                </dd>

                                <dt class="col-sm-5">{{ __('Doctor') }}:</dt>
                                <dd class="col-sm-7">{{ $orthodonticCase->doctor->full_name }}</dd>

                                <dt class="col-sm-5">{{ __('Treatment Type') }}:</dt>
                                <dd class="col-sm-7">{{ \App\Models\OrthodonticCase::TREATMENT_TYPES[$orthodonticCase->treatment_type] ?? $orthodonticCase->treatment_type }}</dd>

                                <dt class="col-sm-5">{{ __('Malocclusion') }}:</dt>
                                <dd class="col-sm-7">
                                    {{ $orthodonticCase->malocclusion_class ? (\App\Models\OrthodonticCase::MALOCCLUSION_CLASSES[$orthodonticCase->malocclusion_class] ?? $orthodonticCase->malocclusion_class) : 'N/A' }}
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">{{ __('Start Date') }}:</dt>
                                <dd class="col-sm-7">{{ $orthodonticCase->start_date->format('Y-m-d') }}</dd>

                                <dt class="col-sm-5">{{ __('Duration') }}:</dt>
                                <dd class="col-sm-7">{{ $orthodonticCase->estimated_duration_months }} {{ __('months') }}</dd>

                                <dt class="col-sm-5">{{ __('Current Phase') }}:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info">
                                        {{ \App\Models\OrthodonticCase::PHASES[$orthodonticCase->current_phase] ?? $orthodonticCase->current_phase }}
                                    </span>
                                </dd>

                                <dt class="col-sm-5">{{ __('Status') }}:</dt>
                                <dd class="col-sm-7">
                                    @if($orthodonticCase->status === 'active')
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @elseif($orthodonticCase->status === 'completed')
                                        <span class="badge bg-info">{{ __('Completed') }}</span>
                                    @elseif($orthodonticCase->status === 'paused')
                                        <span class="badge bg-warning">{{ __('Paused') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('Cancelled') }}</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-5">{{ __('Created') }}:</dt>
                                <dd class="col-sm-7">{{ $orthodonticCase->created_at->format('Y-m-d') }}</dd>
                            </dl>
                        </div>
                    </div>

                    @if($orthodonticCase->diagnosis)
                        <hr>
                        <div>
                            <strong>{{ __('Diagnosis') }}:</strong>
                            <p class="mb-0 mt-2">{{ $orthodonticCase->diagnosis }}</p>
                        </div>
                    @endif

                    @if($orthodonticCase->treatment_objectives)
                        <hr>
                        <div>
                            <strong>{{ __('Treatment Objectives') }}:</strong>
                            <p class="mb-0 mt-2">{{ $orthodonticCase->treatment_objectives }}</p>
                        </div>
                    @endif

                    @if($orthodonticCase->notes)
                        <hr>
                        <div>
                            <strong>{{ __('Notes') }}:</strong>
                            <p class="mb-0 mt-2">{{ $orthodonticCase->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Treatment Progress Timeline -->
            <div class="card mb-4 treatment-timeline-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-route me-2"></i>{{ __('Treatment Progress Timeline') }}</h6>
                            <small class="text-muted">{{ __('Track your orthodontic journey through each treatment phase') }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="phaseUpdateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-edit me-1"></i>{{ __('Quick Update') }}
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="phaseUpdateDropdown">
                                @foreach(\App\Models\OrthodonticCase::TREATMENT_PHASES as $key => $label)
                                    <li>
                                        <a class="dropdown-item phase-update-option {{ $orthodonticCase->current_phase === $key ? 'active' : '' }}"
                                           href="#"
                                           data-phase="{{ $key }}"
                                           data-label="{{ $label }}">
                                            @if($orthodonticCase->current_phase === $key)
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                            @else
                                                <i class="far fa-circle me-2"></i>
                                            @endif
                                            {{ __($label) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Timeline Stepper -->
                    <div class="timeline-stepper">
                        @php
                            $phases = \App\Models\OrthodonticCase::TREATMENT_PHASES;
                            $currentPhase = $orthodonticCase->current_phase ?? 'bonding';
                            $phaseKeys = array_keys($phases);
                            $currentIndex = array_search($currentPhase, $phaseKeys);
                            $currentIndex = $currentIndex !== false ? $currentIndex : 0;
                        @endphp

                        @foreach($phases as $key => $label)
                            @php
                                $index = array_search($key, $phaseKeys);
                                $isCompleted = $index < $currentIndex;
                                $isCurrent = $key === $currentPhase;
                                $isPending = $index > $currentIndex;

                                $stepClass = 'pending';
                                if ($isCompleted) $stepClass = 'completed';
                                if ($isCurrent) $stepClass = 'current';
                            @endphp

                            <div class="timeline-step {{ $stepClass }}"
                                 data-phase="{{ $key }}"
                                 data-label="{{ $label }}"
                                 data-index="{{ $index }}">

                                <!-- Connector Line -->
                                @if(!$loop->last)
                                    <div class="timeline-connector {{ $isCompleted ? 'completed' : '' }}"></div>
                                @endif

                                <!-- Step Circle -->
                                <div class="step-circle">
                                    <span class="step-number">{{ $index + 1 }}</span>
                                </div>

                                <!-- Step Info -->
                                <div class="step-info">
                                    <div class="step-label">{{ __($label) }}</div>
                                    @if($isCurrent)
                                        <div class="step-date">
                                            <i class="fas fa-clock me-1"></i>{{ __('Current Phase') }}
                                        </div>
                                    @elseif($isCompleted)
                                        <div class="step-date">
                                            <i class="fas fa-check me-1"></i>{{ __('Completed') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Phase Description -->
                    <div class="timeline-quick-update">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    {{ __('Current Phase') }}:
                                    <strong class="text-primary" id="currentPhaseLabel">
                                        {{ \App\Models\OrthodonticCase::TREATMENT_PHASES[$orthodonticCase->current_phase] ?? 'Not Set' }}
                                    </strong>
                                </h6>
                                <p class="mb-0 text-muted small" id="phaseDescription">
                                    @if($orthodonticCase->current_phase === 'bonding')
                                        {{ __('Initial bracket placement and appliance installation phase.') }}
                                    @elseif($orthodonticCase->current_phase === 'alignment')
                                        {{ __('Aligning teeth and leveling the arches using light, flexible wires.') }}
                                    @elseif($orthodonticCase->current_phase === 'space_closure')
                                        {{ __('Closing extraction spaces or gaps between teeth.') }}
                                    @elseif($orthodonticCase->current_phase === 'finishing')
                                        {{ __('Fine-tuning tooth positions and achieving ideal occlusion.') }}
                                    @elseif($orthodonticCase->current_phase === 'retention')
                                        {{ __('Maintaining treatment results with retainers after appliance removal.') }}
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    {{ __('Updated') }}: {{ $orthodonticCase->updated_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Assessment -->
            @if($orthodonticCase->skeletal_class || $orthodonticCase->overjet || $orthodonticCase->overbite || $orthodonticCase->midline || $orthodonticCase->crowding || $orthodonticCase->crossbite || $orthodonticCase->open_bite)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-stethoscope me-2"></i>{{ __('Clinical Assessment') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($orthodonticCase->skeletal_class)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Skeletal Class') }}</small>
                                <strong>{{ \App\Models\OrthodonticCase::SKELETAL_CLASSES[$orthodonticCase->skeletal_class] ?? $orthodonticCase->skeletal_class }}</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->overjet !== null)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Overjet') }}</small>
                                <strong>{{ $orthodonticCase->overjet }} mm</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->overbite !== null)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Overbite') }}</small>
                                <strong>{{ $orthodonticCase->overbite }} mm</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->midline)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Midline') }}</small>
                                <strong>{{ \App\Models\OrthodonticCase::MIDLINE_OPTIONS[$orthodonticCase->midline] ?? $orthodonticCase->midline }}</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->crowding)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Crowding') }}</small>
                                <strong>{{ \App\Models\OrthodonticCase::CROWDING_LEVELS[$orthodonticCase->crowding] ?? $orthodonticCase->crowding }}</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->crossbite)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Crossbite') }}</small>
                                <strong>{{ \App\Models\OrthodonticCase::CROSSBITE_OPTIONS[$orthodonticCase->crossbite] ?? $orthodonticCase->crossbite }}</strong>
                            </div>
                        </div>
                        @endif

                        @if($orthodonticCase->open_bite !== null)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block">{{ __('Open Bite') }}</small>
                                <strong>{{ $orthodonticCase->open_bite }} mm</strong>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Orthodontic Appliance Map (Visual Tooth Chart) -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-tooth me-2"></i>{{ __('Orthodontic Appliance Map') }}</h6>
                        <small class="text-muted">{{ __('Click on any tooth to update its orthodontic status') }}</small>
                    </div>
                    <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group" id="dentitionToggle">
                        <input type="radio" class="btn-check" name="dentition_type" id="dentition_permanent" value="permanent"
                            {{ $orthodonticCase->patient->age > 12 ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary" for="dentition_permanent">
                            <i class="fas fa-teeth me-1"></i>{{ __('Permanent') }}
                        </label>
                        <input type="radio" class="btn-check" name="dentition_type" id="dentition_primary" value="primary"
                            {{ $orthodonticCase->patient->age <= 12 ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary" for="dentition_primary">
                            <i class="fas fa-baby me-1"></i>{{ __('Primary') }}
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Permanent Dentition (32 teeth) -->
                    <div id="permanent-dentition" class="dentition-view" style="display: {{ $orthodonticCase->patient->age > 12 ? 'block' : 'none' }};">
                        <!-- Upper Arch (Teeth 1-16) -->
                        <div class="mb-4">
                            <h6 class="text-center text-muted mb-3">{{ __('Upper Arch') }}</h6>
                            <div class="tooth-arch-container upper-arch">
                                <div class="tooth-row d-flex justify-content-center gap-2 flex-wrap">
                                    @for($i = 1; $i <= 16; $i++)
                                        <div class="tooth-item" data-tooth-number="{{ $i }}" data-dentition="permanent" data-bs-toggle="modal" data-bs-target="#toothStatusModal">
                                            <div class="tooth-box" id="tooth-{{ $i }}">
                                                <div class="tooth-number">{{ $i }}</div>
                                                <div class="tooth-status-indicator"></div>
                                            </div>
                                            <div class="tooth-label">
                                                @if($i <= 8)
                                                    {{ 9 - $i }}
                                                @else
                                                    {{ $i - 8 }}
                                                @endif
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <!-- Lower Arch (Teeth 17-32) -->
                        <div class="mb-4">
                            <h6 class="text-center text-muted mb-3">{{ __('Lower Arch') }}</h6>
                            <div class="tooth-arch-container lower-arch">
                                <div class="tooth-row d-flex justify-content-center gap-2 flex-wrap">
                                    @for($i = 32; $i >= 17; $i--)
                                        <div class="tooth-item" data-tooth-number="{{ $i }}" data-dentition="permanent" data-bs-toggle="modal" data-bs-target="#toothStatusModal">
                                            <div class="tooth-box" id="tooth-{{ $i }}">
                                                <div class="tooth-number">{{ $i }}</div>
                                                <div class="tooth-status-indicator"></div>
                                            </div>
                                            <div class="tooth-label">
                                                @if($i >= 25)
                                                    {{ $i - 24 }}
                                                @else
                                                    {{ 25 - $i }}
                                                @endif
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Dentition (20 teeth) -->
                    <div id="primary-dentition" class="dentition-view" style="display: {{ $orthodonticCase->patient->age <= 12 ? 'block' : 'none' }};">
                        <!-- Upper Primary Arch (A-J / 51-55, 61-65) -->
                        <div class="mb-4">
                            <h6 class="text-center text-muted mb-3">{{ __('Upper Arch (Primary)') }}</h6>
                            <div class="tooth-arch-container upper-arch">
                                <div class="tooth-row d-flex justify-content-center gap-2 flex-wrap">
                                    @php
                                        $primaryUpper = [
                                            ['letter' => 'E', 'number' => 55, 'position' => '5'],
                                            ['letter' => 'D', 'number' => 54, 'position' => '4'],
                                            ['letter' => 'C', 'number' => 53, 'position' => '3'],
                                            ['letter' => 'B', 'number' => 52, 'position' => '2'],
                                            ['letter' => 'A', 'number' => 51, 'position' => '1'],
                                            ['letter' => 'A', 'number' => 61, 'position' => '1'],
                                            ['letter' => 'B', 'number' => 62, 'position' => '2'],
                                            ['letter' => 'C', 'number' => 63, 'position' => '3'],
                                            ['letter' => 'D', 'number' => 64, 'position' => '4'],
                                            ['letter' => 'E', 'number' => 65, 'position' => '5'],
                                        ];
                                    @endphp
                                    @foreach($primaryUpper as $tooth)
                                        <div class="tooth-item" data-tooth-number="{{ $tooth['number'] }}" data-dentition="primary" data-bs-toggle="modal" data-bs-target="#toothStatusModal">
                                            <div class="tooth-box primary-tooth" id="tooth-{{ $tooth['number'] }}">
                                                <div class="tooth-number">{{ $tooth['letter'] }}</div>
                                                <div class="tooth-status-indicator"></div>
                                            </div>
                                            <div class="tooth-label">{{ $tooth['position'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Lower Primary Arch (K-T / 71-75, 81-85) -->
                        <div class="mb-4">
                            <h6 class="text-center text-muted mb-3">{{ __('Lower Arch (Primary)') }}</h6>
                            <div class="tooth-arch-container lower-arch">
                                <div class="tooth-row d-flex justify-content-center gap-2 flex-wrap">
                                    @php
                                        $primaryLower = [
                                            ['letter' => 'T', 'number' => 85, 'position' => '5'],
                                            ['letter' => 'S', 'number' => 84, 'position' => '4'],
                                            ['letter' => 'R', 'number' => 83, 'position' => '3'],
                                            ['letter' => 'Q', 'number' => 82, 'position' => '2'],
                                            ['letter' => 'P', 'number' => 81, 'position' => '1'],
                                            ['letter' => 'K', 'number' => 71, 'position' => '1'],
                                            ['letter' => 'L', 'number' => 72, 'position' => '2'],
                                            ['letter' => 'M', 'number' => 73, 'position' => '3'],
                                            ['letter' => 'N', 'number' => 74, 'position' => '4'],
                                            ['letter' => 'O', 'number' => 75, 'position' => '5'],
                                        ];
                                    @endphp
                                    @foreach($primaryLower as $tooth)
                                        <div class="tooth-item" data-tooth-number="{{ $tooth['number'] }}" data-dentition="primary" data-bs-toggle="modal" data-bs-target="#toothStatusModal">
                                            <div class="tooth-box primary-tooth" id="tooth-{{ $tooth['number'] }}">
                                                <div class="tooth-number">{{ $tooth['letter'] }}</div>
                                                <div class="tooth-status-indicator"></div>
                                            </div>
                                            <div class="tooth-label">{{ $tooth['position'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="tooth-legend mt-4 pt-3 border-top">
                        <h6 class="mb-3">{{ __('Legend') }}</h6>
                        <div class="row g-2">
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-bracket-placed"></div>
                                    <span class="ms-2">{{ __('Bracket Placed') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-missing-bracket"></div>
                                    <span class="ms-2">{{ __('Missing Bracket') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-band"></div>
                                    <span class="ms-2">{{ __('Band') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-elastic-attachment"></div>
                                    <span class="ms-2">{{ __('Elastic Attachment') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-extraction-space"></div>
                                    <span class="ms-2">{{ __('Extraction Space') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="legend-item d-flex align-items-center">
                                    <div class="legend-box status-normal"></div>
                                    <span class="ms-2">{{ __('Normal') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visits Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>{{ __('Visits') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVisitModal">
                        <i class="fas fa-plus me-1"></i>{{ __('Add Visit') }}
                    </button>
                </div>
                <div class="card-body">
                    @if($orthodonticCase->visits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Visit #') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Procedures') }}</th>
                                        <th>{{ __('Next Appointment') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orthodonticCase->visits as $visit)
                                    <tr>
                                        <td>{{ $visit->visit_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-info">{{ $visit->visit_number }}</span></td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($visit->visit_type) }}</span>
                                        </td>
                                        <td>{{ Str::limit($visit->procedures_performed, 50) }}</td>
                                        <td>
                                            @if($visit->next_appointment_date)
                                                {{ $visit->next_appointment_date->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">{{ __('Not scheduled') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary view-visit-btn"
                                                    data-visit-id="{{ $visit->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewVisitModal">
                                                <i class="fas fa-eye me-1"></i>{{ __('View') }}
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted mb-2">{{ __('No visits recorded yet.') }}</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVisitModal">
                                <i class="fas fa-plus me-1"></i>{{ __('Record First Visit') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Enhanced Photos Section with Tabs -->
            <div class="card mb-4 photo-gallery-card">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-0">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-camera-retro me-2"></i>{{ __('Clinical Photo Gallery') }}</h6>
                        <small class="text-muted">{{ __('Track treatment progress with before, during, and after photos') }}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                        <i class="fas fa-upload me-1"></i>{{ __('Upload Photo') }}
                    </button>
                </div>
                <div class="card-body">
                    @if($orthodonticCase->photos->count() > 0)
                        <!-- Compare Photos Toolbar -->
                        <div class="compare-toolbar" id="compareToolbar" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <strong><span id="selectedCount">0</span> {{ __('photos selected for comparison') }}</strong>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary me-2" id="compareBtn" disabled>
                                        <i class="fas fa-columns me-1"></i>{{ __('Compare Selected') }}
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" id="clearSelectionBtn">
                                        <i class="fas fa-times me-1"></i>{{ __('Clear') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabbed Gallery -->
                        <ul class="nav nav-tabs mb-3" id="photoGalleryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-photos" type="button">
                                    <i class="fas fa-th me-1"></i>{{ __('All Photos') }}
                                    <span class="badge bg-secondary ms-1">{{ $orthodonticCase->photos->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="before-tab" data-bs-toggle="tab" data-bs-target="#before-photos" type="button">
                                    <i class="fas fa-backward me-1"></i>{{ __('Before Treatment') }}
                                    <span class="badge bg-secondary ms-1">{{ $orthodonticCase->photos->where('stage', 'before')->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress-photos" type="button">
                                    <i class="fas fa-sync me-1"></i>{{ __('Progress') }}
                                    <span class="badge bg-secondary ms-1">{{ $orthodonticCase->photos->where('stage', 'progress')->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="after-tab" data-bs-toggle="tab" data-bs-target="#after-photos" type="button">
                                    <i class="fas fa-forward me-1"></i>{{ __('After Treatment') }}
                                    <span class="badge bg-secondary ms-1">{{ $orthodonticCase->photos->where('stage', 'after')->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline-photos" type="button">
                                    <i class="fas fa-calendar-alt me-1"></i>{{ __('Timeline View') }}
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="photoGalleryTabContent">
                            <!-- All Photos Tab -->
                            <div class="tab-pane fade show active" id="all-photos" role="tabpanel">
                                <div class="row">
                                    @foreach($orthodonticCase->photos as $photo)
                                        @include('orthodontics.partials.photo-card', ['photo' => $photo])
                                    @endforeach
                                </div>
                            </div>

                            <!-- Before Treatment Tab -->
                            <div class="tab-pane fade" id="before-photos" role="tabpanel">
                                <div class="row">
                                    @forelse($orthodonticCase->photos->where('stage', 'before') as $photo)
                                        @include('orthodontics.partials.photo-card', ['photo' => $photo])
                                    @empty
                                        <div class="col-12 text-center py-4">
                                            <i class="fas fa-image text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">{{ __('No before treatment photos uploaded yet.') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Progress Tab -->
                            <div class="tab-pane fade" id="progress-photos" role="tabpanel">
                                <div class="row">
                                    @forelse($orthodonticCase->photos->where('stage', 'progress') as $photo)
                                        @include('orthodontics.partials.photo-card', ['photo' => $photo])
                                    @empty
                                        <div class="col-12 text-center py-4">
                                            <i class="fas fa-images text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">{{ __('No progress photos uploaded yet.') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- After Treatment Tab -->
                            <div class="tab-pane fade" id="after-photos" role="tabpanel">
                                <div class="row">
                                    @forelse($orthodonticCase->photos->where('stage', 'after') as $photo)
                                        @include('orthodontics.partials.photo-card', ['photo' => $photo])
                                    @empty
                                        <div class="col-12 text-center py-4">
                                            <i class="fas fa-check-circle text-muted" style="font-size: 48px;"></i>
                                            <p class="text-muted mt-3">{{ __('No after treatment photos uploaded yet.') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Timeline View Tab -->
                            <div class="tab-pane fade" id="timeline-photos" role="tabpanel">
                                @php
                                    $photosByMonth = $orthodonticCase->photos->sortByDesc('photo_date')->groupBy(function($photo) {
                                        return $photo->photo_date->format('F Y');
                                    });
                                @endphp

                                @forelse($photosByMonth as $month => $photos)
                                    <div class="timeline-month-header">
                                        <i class="fas fa-calendar me-2"></i>{{ $month }}
                                        <span class="badge bg-light text-dark ms-2">{{ $photos->count() }} {{ __('photos') }}</span>
                                    </div>
                                    <div class="timeline-photo-grid">
                                        <div class="row">
                                            @foreach($photos as $photo)
                                                @include('orthodontics.partials.photo-card', ['photo' => $photo])
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <i class="fas fa-calendar-times text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-3">{{ __('No photos to display in timeline view.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-camera text-muted" style="font-size: 64px;"></i>
                            <p class="text-muted mt-3 mb-2">{{ __('No photos uploaded yet.') }}</p>
                            <p class="text-muted small">{{ __('Start documenting treatment progress by uploading clinical photos') }}</p>
                            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPhotoModal">
                                <i class="fas fa-upload me-2"></i>{{ __('Upload First Photo') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>{{ __('Financial Summary') }}</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">{{ __('Total Cost') }}:</dt>
                        <dd class="col-sm-6 text-end">
                            <strong>{{ number_format($orthodonticCase->total_cost, 2) }} {{ $orthodonticCase->currency }}</strong>
                        </dd>

                        <dt class="col-sm-6">{{ __('Paid Amount') }}:</dt>
                        <dd class="col-sm-6 text-end text-success">
                            {{ number_format($orthodonticCase->paid_amount, 2) }} {{ $orthodonticCase->currency }}
                        </dd>

                        <dt class="col-sm-6">{{ __('Balance') }}:</dt>
                        <dd class="col-sm-6 text-end text-danger">
                            {{ number_format($orthodonticCase->balance, 2) }} {{ $orthodonticCase->currency }}
                        </dd>

                        <dt class="col-sm-6">{{ __('Payment Plan') }}:</dt>
                        <dd class="col-sm-6 text-end">
                            <span class="badge bg-info">
                                {{ \App\Models\OrthodonticCase::PAYMENT_PLANS[$orthodonticCase->payment_plan] ?? $orthodonticCase->payment_plan }}
                            </span>
                        </dd>
                    </dl>

                    <hr>

                    @php
                        $paymentPercentage = $orthodonticCase->total_cost > 0 ? ($orthodonticCase->paid_amount / $orthodonticCase->total_cost * 100) : 0;
                    @endphp
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">{{ __('Payment Progress') }}</span>
                            <span class="text-muted small">{{ number_format($paymentPercentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                 role="progressbar"
                                 style="width: {{ min($paymentPercentage, 100) }}%;"
                                 aria-valuenow="{{ $paymentPercentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_format($paymentPercentage, 0) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Timeline -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>{{ __('Payment Timeline') }}</h6>
                        <small class="text-muted">{{ __('Installment schedule and history') }}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus me-1"></i>{{ __('Add') }}
                    </button>
                </div>
                <div class="card-body">
                    @php
                        // Calculate installment plan
                        $startDate = $orthodonticCase->start_date;
                        $totalCost = $orthodonticCase->total_cost;
                        $paidAmount = $orthodonticCase->paid_amount;
                        $balance = $orthodonticCase->balance;
                        $estimatedMonths = $orthodonticCase->estimated_duration_months ?? 12;
                        $paymentPlan = $orthodonticCase->payment_plan;

                        // Get all payments grouped by month
                        $paymentsByMonth = $orthodonticCase->payments->groupBy(function($payment) {
                            return $payment->payment_date->format('Y-m');
                        });

                        // Generate timeline based on payment plan
                        $timeline = [];

                        if ($paymentPlan === 'monthly' && $estimatedMonths > 0) {
                            $monthlyAmount = $totalCost / $estimatedMonths;
                            $remainingBalance = $balance;
                            $cumulativePaid = 0;

                            for ($i = 0; $i < $estimatedMonths; $i++) {
                                $monthDate = $startDate->copy()->addMonths($i);
                                $monthKey = $monthDate->format('Y-m');

                                $paymentsInMonth = $paymentsByMonth->get($monthKey);
                                $paidInMonth = $paymentsInMonth ? $paymentsInMonth->sum('amount') : 0;

                                $cumulativePaid += $paidInMonth;
                                $expectedCumulative = $monthlyAmount * ($i + 1);

                                // Determine status
                                $status = 'pending';

                                if ($paidInMonth > 0) {
                                    // This month has a payment
                                    $status = 'paid';
                                } elseif ($cumulativePaid >= $totalCost) {
                                    // Already fully paid, skip future pending months
                                    continue;
                                } elseif ($monthDate->isPast() && $cumulativePaid < $expectedCumulative) {
                                    $status = 'overdue';
                                } elseif ($cumulativePaid >= $expectedCumulative) {
                                    // Ahead of schedule, skip this month
                                    continue;
                                }

                                $timeline[] = [
                                    'month' => $monthDate->format('F Y'),
                                    'month_short' => $monthDate->format('M Y'),
                                    'date' => $monthDate,
                                    'expected_amount' => $monthlyAmount,
                                    'paid_amount' => $paidInMonth,
                                    'status' => $status,
                                    'payments' => $paymentsInMonth,
                                    'installment_number' => $i + 1,
                                ];
                            }

                            // Add remaining balance if any (for irregular payments)
                            if ($balance > 0) {
                                // Calculate how many months remaining
                                $paidMonths = count(array_filter($timeline, fn($item) => $item['status'] === 'paid'));
                                $remainingMonths = max(1, $estimatedMonths - $paidMonths);
                                $monthlyRemaining = $balance / $remainingMonths;

                                // Add future installments for remaining balance
                                $nextMonth = now()->startOfMonth()->addMonth();
                                for ($i = 0; $i < $remainingMonths; $i++) {
                                    $monthDate = $nextMonth->copy()->addMonths($i);
                                    $monthKey = $monthDate->format('Y-m');

                                    // Skip if already exists
                                    if (collect($timeline)->contains(fn($item) => $item['date']->format('Y-m') === $monthKey)) {
                                        continue;
                                    }

                                    $timeline[] = [
                                        'month' => $monthDate->format('F Y'),
                                        'month_short' => $monthDate->format('M Y'),
                                        'date' => $monthDate,
                                        'expected_amount' => $monthlyRemaining,
                                        'paid_amount' => 0,
                                        'status' => 'pending',
                                        'payments' => collect([]),
                                        'installment_number' => count($timeline) + 1,
                                    ];
                                }
                            }
                        } else {
                            // For custom or full payment plans, show actual payments
                            foreach ($orthodonticCase->payments->sortBy('payment_date') as $payment) {
                                $monthKey = $payment->payment_date->format('Y-m');

                                if (!collect($timeline)->contains('month_key', $monthKey)) {
                                    $timeline[] = [
                                        'month' => $payment->payment_date->format('F Y'),
                                        'month_short' => $payment->payment_date->format('M Y'),
                                        'date' => $payment->payment_date,
                                        'expected_amount' => $payment->amount,
                                        'paid_amount' => $payment->amount,
                                        'status' => 'paid',
                                        'payments' => collect([$payment]),
                                        'month_key' => $monthKey,
                                    ];
                                }
                            }

                            // Show pending balance if any
                            if ($balance > 0) {
                                $timeline[] = [
                                    'month' => __('Remaining Balance'),
                                    'month_short' => __('Balance'),
                                    'date' => now(),
                                    'expected_amount' => $balance,
                                    'paid_amount' => 0,
                                    'status' => 'pending',
                                    'payments' => collect([]),
                                ];
                            }
                        }
                    @endphp

                    @if(count($timeline) > 0)
                        <div class="payment-timeline">
                            @foreach($timeline as $item)
                                <div class="payment-timeline-item {{ $item['status'] }}">
                                    <div class="payment-timeline-marker {{ $item['status'] }}"></div>
                                    <div class="payment-timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="payment-timeline-month">
                                                {{ $item['month_short'] }}
                                                @if(isset($item['installment_number']))
                                                    <span class="installment-badge bg-secondary text-white">
                                                        #{{ $item['installment_number'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                @if($item['status'] === 'paid')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>{{ __('Paid') }}
                                                    </span>
                                                @elseif($item['status'] === 'overdue')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ __('Overdue') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i>{{ __('Pending') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="payment-timeline-amount {{ $item['status'] }}">
                                            @if($item['status'] === 'paid')
                                                {{ number_format($item['paid_amount'], 2) }} {{ $orthodonticCase->currency }}
                                            @else
                                                {{ number_format($item['expected_amount'], 2) }} {{ $orthodonticCase->currency }}
                                            @endif
                                        </div>

                                        @if($item['payments'] && $item['payments']->count() > 0)
                                            <div class="payment-timeline-details">
                                                @foreach($item['payments'] as $payment)
                                                    <div class="d-flex justify-content-between text-muted small">
                                                        <span>
                                                            <i class="fas fa-credit-card me-1"></i>
                                                            {{ $payment->payment_method_display }}
                                                        </span>
                                                        <span>{{ $payment->payment_date->format('M d') }}</span>
                                                    </div>
                                                    @if($payment->receipt_number)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-receipt me-1"></i>
                                                            {{ $payment->receipt_number }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($item['status'] === 'pending')
                                            <div class="payment-timeline-details">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ __('Due') }}: {{ $item['date']->format('M d, Y') }}
                                                </small>
                                            </div>
                                        @elseif($item['status'] === 'overdue')
                                            <div class="payment-timeline-details text-danger">
                                                <small>
                                                    <i class="fas fa-exclamation-circle me-1"></i>
                                                    {{ __('Was due') }}: {{ $item['date']->format('M d, Y') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3 mb-2">{{ __('No payment schedule available.') }}</p>
                            <p class="text-muted small">{{ __('Add a payment to start tracking installments') }}</p>
                            <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                                <i class="fas fa-plus me-1"></i>{{ __('Record First Payment') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Visit Modal -->
<div class="modal fade" id="addVisitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Visit') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('orthodontics.visits.store', $orthodonticCase) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="visit_date" class="form-label">{{ __('Visit Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="visit_date" id="visit_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="visit_type" class="form-label">{{ __('Visit Type') }} <span class="text-danger">*</span></label>
                            <select name="visit_type" id="visit_type" class="form-select" required>
                                <option value="adjustment">{{ __('Adjustment') }}</option>
                                <option value="emergency">{{ __('Emergency') }}</option>
                                <option value="review">{{ __('Review') }}</option>
                                <option value="final">{{ __('Final') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="procedures_performed" class="form-label">{{ __('Procedures Performed') }}</label>
                        <textarea name="procedures_performed" id="procedures_performed" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="observations" class="form-label">{{ __('Observations') }}</label>
                        <textarea name="observations" id="observations" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="patient_concerns" class="form-label">{{ __('Patient Concerns') }}</label>
                        <textarea name="patient_concerns" id="patient_concerns" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="oral_hygiene_status" class="form-label">{{ __('Oral Hygiene Status') }}</label>
                            <select name="oral_hygiene_status" id="oral_hygiene_status" class="form-select">
                                <option value="">{{ __('Select Status') }}</option>
                                <option value="excellent">{{ __('Excellent') }}</option>
                                <option value="good">{{ __('Good') }}</option>
                                <option value="fair">{{ __('Fair') }}</option>
                                <option value="poor">{{ __('Poor') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="appliance_condition" class="form-label">{{ __('Appliance Condition') }}</label>
                            <input type="text" name="appliance_condition" id="appliance_condition" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="broken_brackets" id="broken_brackets" class="form-check-input" value="1">
                            <label for="broken_brackets" class="form-check-label">{{ __('Broken Brackets') }}</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="next_appointment_date" class="form-label">{{ __('Next Appointment Date') }}</label>
                            <input type="date" name="next_appointment_date" id="next_appointment_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="instructions_given" class="form-label">{{ __('Instructions Given') }}</label>
                        <textarea name="instructions_given" id="instructions_given" rows="2" class="form-control"></textarea>
                    </div>

                    <!-- Clinical Mechanics Section -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3"><i class="fas fa-cog me-2"></i>{{ __('Clinical Mechanics') }}</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="upper_wire" class="form-label">{{ __('Upper Wire') }}</label>
                                <input type="text" name="upper_wire" id="upper_wire" class="form-control" placeholder="e.g., 0.14 NiTi, 16x22 SS">
                                <small class="text-muted">{{ __('Wire specification for upper arch') }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lower_wire" class="form-label">{{ __('Lower Wire') }}</label>
                                <input type="text" name="lower_wire" id="lower_wire" class="form-control" placeholder="e.g., 0.16 NiTi, 17x25 SS">
                                <small class="text-muted">{{ __('Wire specification for lower arch') }}</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="elastic_type" class="form-label">{{ __('Elastic Type') }}</label>
                                <input type="text" name="elastic_type" id="elastic_type" class="form-control" placeholder="e.g., Class II, 1/8\" 4oz">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="power_chain" class="form-label">{{ __('Power Chain') }}</label>
                                <input type="text" name="power_chain" id="power_chain" class="form-control" placeholder="e.g., Upper 3-3, Closed">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="coil_spring" class="form-label">{{ __('Coil Spring') }}</label>
                                <input type="text" name="coil_spring" id="coil_spring" class="form-control" placeholder="e.g., Open coil 14-15">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="visit_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="visit_notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Visit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Visit Details Modal -->
<div class="modal fade" id="viewVisitModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Visit Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="visitDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Tooth Status Modal -->
<div class="modal fade" id="toothStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Update Tooth Status') }} - <span id="modalToothNumber"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="selectedToothNumber" value="">

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('Select Orthodontic Status') }}</label>

                    @foreach(\App\Models\OrthodonticCase::TOOTH_STATUSES as $key => $value)
                    <div class="form-check mb-2 p-3 border rounded tooth-status-option" data-status="{{ $key }}">
                        <input class="form-check-input" type="radio" name="tooth_status" id="status_{{ $key }}" value="{{ $key }}">
                        <label class="form-check-label w-100 cursor-pointer" for="status_{{ $key }}">
                            <div class="d-flex align-items-center">
                                <div class="status-preview status-{{ str_replace('_', '-', $key) }} me-3"></div>
                                <div>
                                    <strong>{{ __($value) }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($key === 'bracket_placed')
                                            {{ __('Standard orthodontic bracket attached') }}
                                        @elseif($key === 'missing_bracket')
                                            {{ __('Bracket was lost or not placed') }}
                                        @elseif($key === 'band')
                                            {{ __('Orthodontic band placed around tooth') }}
                                        @elseif($key === 'elastic_attachment')
                                            {{ __('Elastic button or attachment placed') }}
                                        @elseif($key === 'extraction_space')
                                            {{ __('Tooth extracted or space maintained') }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach

                    <div class="form-check mb-2 p-3 border rounded tooth-status-option" data-status="clear">
                        <input class="form-check-input" type="radio" name="tooth_status" id="status_clear" value="">
                        <label class="form-check-label w-100 cursor-pointer" for="status_clear">
                            <div class="d-flex align-items-center">
                                <div class="status-preview status-normal me-3"></div>
                                <div>
                                    <strong>{{ __('Clear Status') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ __('Remove any special status from this tooth') }}</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saveToothStatus">{{ __('Save Status') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Photo Modal -->
<div class="modal fade" id="addPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Upload Photo') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('orthodontics.photos.store', $orthodonticCase) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="photo" class="form-label">{{ __('Photo') }} <span class="text-danger">*</span></label>
                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">{{ __('Max size: 5MB. Formats: JPG, PNG') }}</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="photo_type" class="form-label">{{ __('Photo Type') }} <span class="text-danger">*</span></label>
                            <select name="photo_type" id="photo_type" class="form-select" required>
                                @foreach(\App\Models\OrthodonticPhoto::PHOTO_TYPES as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="view_type" class="form-label">{{ __('View Type') }} <span class="text-danger">*</span></label>
                            <select name="view_type" id="view_type" class="form-select" required>
                                @foreach(\App\Models\OrthodonticPhoto::VIEW_TYPES as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stage" class="form-label">{{ __('Stage') }} <span class="text-danger">*</span></label>
                            <select name="stage" id="stage" class="form-select" required>
                                @foreach(\App\Models\OrthodonticPhoto::STAGES as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="photo_date" class="form-label">{{ __('Photo Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="photo_date" id="photo_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="photo_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="photo_notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Upload Photo') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Photo Comparison Modal -->
<div class="modal fade" id="photoComparisonModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-columns me-2"></i>{{ __('Side-by-Side Photo Comparison') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Photo 1 -->
                    <div class="col-md-6 mb-3">
                        <div class="comparison-label">
                            <i class="fas fa-image me-2"></i>{{ __('Photo') }} 1
                        </div>
                        <div id="comparisonPhoto1Container" class="text-center">
                            <img id="comparisonPhoto1" src="" class="img-fluid comparison-image" alt="Photo 1" style="max-height: 500px;">
                            <div class="mt-3">
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto1Type"></div>
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto1View"></div>
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto1Stage"></div>
                                <div class="text-muted small mt-2" id="comparisonPhoto1Date"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Photo 2 -->
                    <div class="col-md-6 mb-3">
                        <div class="comparison-label">
                            <i class="fas fa-image me-2"></i>{{ __('Photo') }} 2
                        </div>
                        <div id="comparisonPhoto2Container" class="text-center">
                            <img id="comparisonPhoto2" src="" class="img-fluid comparison-image" alt="Photo 2" style="max-height: 500px;">
                            <div class="mt-3">
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto2Type"></div>
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto2View"></div>
                                <div class="badge bg-light text-dark mb-2" id="comparisonPhoto2Stage"></div>
                                <div class="text-muted small mt-2" id="comparisonPhoto2Date"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('Compare clinical photos side-by-side to evaluate treatment progress and changes over time.') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Single Photo View Modal -->
<div class="modal fade" id="photoViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoViewTitle">{{ __('Photo View') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="photoViewImage" src="" class="img-fluid" alt="Photo" style="max-height: 600px; border-radius: 10px;">
                <div class="mt-3" id="photoViewInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Record Payment') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('orthodontics.payments.store', $orthodonticCase) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" step="0.01" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                @foreach(\App\Models\OrthodonticPayment::PAYMENT_METHODS as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_type" class="form-label">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                            <select name="payment_type" id="payment_type" class="form-select" required>
                                @foreach(\App\Models\OrthodonticPayment::PAYMENT_TYPES as $key => $value)
                                    <option value="{{ $key }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="installment_number" class="form-label">{{ __('Installment Number') }}</label>
                            <input type="number" name="installment_number" id="installment_number" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="receipt_number" class="form-label">{{ __('Receipt Number') }}</label>
                            <input type="text" name="receipt_number" id="receipt_number" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" id="payment_notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Payment') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle View Visit button clicks
    const viewVisitButtons = document.querySelectorAll('.view-visit-btn');

    viewVisitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const visitId = this.getAttribute('data-visit-id');
            loadVisitDetails(visitId);
        });
    });

    function loadVisitDetails(visitId) {
        const contentDiv = document.getElementById('visitDetailsContent');

        // Show loading spinner
        contentDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

        // Fetch visit details
        fetch(`{{ route('orthodontics.show', $orthodonticCase) }}/visits/${visitId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayVisitDetails(data.visit);
                } else {
                    showError('Failed to load visit details.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred while loading visit details.');
            });
    }

    function displayVisitDetails(visit) {
        const contentDiv = document.getElementById('visitDetailsContent');

        let html = `
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Visit Date</small>
                        <strong>${formatDate(visit.visit_date)}</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Visit Number</small>
                        <strong><span class="badge bg-info">#${visit.visit_number}</span></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block">Visit Type</small>
                        <strong><span class="badge bg-secondary">${capitalizeFirst(visit.visit_type)}</span></strong>
                    </div>
                </div>
            </div>
        `;

        // Clinical Information
        if (visit.procedures_performed || visit.observations || visit.patient_concerns) {
            html += `
                <div class="mb-3">
                    <h6 class="border-bottom pb-2"><i class="fas fa-clipboard-list me-2"></i>Clinical Information</h6>
                    <div class="row">
            `;

            if (visit.procedures_performed) {
                html += `
                    <div class="col-md-12 mb-2">
                        <small class="text-muted d-block">Procedures Performed</small>
                        <p class="mb-0">${visit.procedures_performed}</p>
                    </div>
                `;
            }

            if (visit.observations) {
                html += `
                    <div class="col-md-12 mb-2">
                        <small class="text-muted d-block">Observations</small>
                        <p class="mb-0">${visit.observations}</p>
                    </div>
                `;
            }

            if (visit.patient_concerns) {
                html += `
                    <div class="col-md-12 mb-2">
                        <small class="text-muted d-block">Patient Concerns</small>
                        <p class="mb-0">${visit.patient_concerns}</p>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;
        }

        // Clinical Mechanics
        if (visit.upper_wire || visit.lower_wire || visit.elastic_type || visit.power_chain || visit.coil_spring) {
            html += `
                <div class="mb-3">
                    <h6 class="border-bottom pb-2"><i class="fas fa-cog me-2"></i>Clinical Mechanics</h6>
                    <div class="row">
            `;

            if (visit.upper_wire) {
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="border rounded p-3">
                            <small class="text-muted d-block">Upper Wire</small>
                            <strong>${visit.upper_wire}</strong>
                        </div>
                    </div>
                `;
            }

            if (visit.lower_wire) {
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="border rounded p-3">
                            <small class="text-muted d-block">Lower Wire</small>
                            <strong>${visit.lower_wire}</strong>
                        </div>
                    </div>
                `;
            }

            if (visit.elastic_type) {
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-3">
                            <small class="text-muted d-block">Elastic Type</small>
                            <strong>${visit.elastic_type}</strong>
                        </div>
                    </div>
                `;
            }

            if (visit.power_chain) {
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-3">
                            <small class="text-muted d-block">Power Chain</small>
                            <strong>${visit.power_chain}</strong>
                        </div>
                    </div>
                `;
            }

            if (visit.coil_spring) {
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-3">
                            <small class="text-muted d-block">Coil Spring</small>
                            <strong>${visit.coil_spring}</strong>
                        </div>
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;
        }

        // Additional Details
        html += `
            <div class="mb-3">
                <h6 class="border-bottom pb-2"><i class="fas fa-info-circle me-2"></i>Additional Details</h6>
                <div class="row">
        `;

        if (visit.oral_hygiene_status) {
            html += `
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-3">
                        <small class="text-muted d-block">Oral Hygiene Status</small>
                        <strong>${capitalizeFirst(visit.oral_hygiene_status)}</strong>
                    </div>
                </div>
            `;
        }

        if (visit.appliance_condition) {
            html += `
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-3">
                        <small class="text-muted d-block">Appliance Condition</small>
                        <strong>${visit.appliance_condition}</strong>
                    </div>
                </div>
            `;
        }

        if (visit.broken_brackets) {
            html += `
                <div class="col-md-12 mb-2">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i><strong>Broken Brackets Reported</strong>
                    </div>
                </div>
            `;
        }

        if (visit.next_appointment_date) {
            html += `
                <div class="col-md-6 mb-2">
                    <div class="border rounded p-3">
                        <small class="text-muted d-block">Next Appointment</small>
                        <strong>${formatDate(visit.next_appointment_date)}</strong>
                    </div>
                </div>
            `;
        }

        html += `
                </div>
            </div>
        `;

        if (visit.instructions_given) {
            html += `
                <div class="mb-3">
                    <small class="text-muted d-block">Instructions Given</small>
                    <p class="mb-0">${visit.instructions_given}</p>
                </div>
            `;
        }

        if (visit.notes) {
            html += `
                <div class="mb-3">
                    <small class="text-muted d-block">Notes</small>
                    <p class="mb-0">${visit.notes}</p>
                </div>
            `;
        }

        contentDiv.innerHTML = html;
    }

    function showError(message) {
        const contentDiv = document.getElementById('visitDetailsContent');
        contentDiv.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>${message}
            </div>
        `;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ============================================
    // VISUAL TOOTH CHART FUNCTIONALITY
    // ============================================

    // Initialize tooth states from database
    let toothStates = @json($orthodonticCase->tooth_states ?? []);
    let currentToothNumber = null;

    // Apply initial states to teeth on page load
    function applyToothStates() {
        Object.keys(toothStates).forEach(toothNumber => {
            const status = toothStates[toothNumber];
            if (status) {
                applyToothStatus(toothNumber, status);
            }
        });
    }

    // Apply visual status to a tooth
    function applyToothStatus(toothNumber, status) {
        const toothBox = document.getElementById(`tooth-${toothNumber}`);
        if (!toothBox) return;

        // Remove all status classes
        toothBox.classList.remove(
            'status-bracket-placed',
            'status-missing-bracket',
            'status-band',
            'status-elastic-attachment',
            'status-extraction-space'
        );

        // Add new status class if not empty
        if (status) {
            toothBox.classList.add(`status-${status.replace(/_/g, '-')}`);
        }
    }

    // Handle tooth click to open modal
    // Primary teeth letter mapping (FDI notation)
    const primaryTeethLetters = {
        '51': 'A', '52': 'B', '53': 'C', '54': 'D', '55': 'E',
        '61': 'A', '62': 'B', '63': 'C', '64': 'D', '65': 'E',
        '71': 'K', '72': 'L', '73': 'M', '74': 'N', '75': 'O',
        '81': 'P', '82': 'Q', '83': 'R', '84': 'S', '85': 'T'
    };

    document.querySelectorAll('.tooth-item').forEach(item => {
        item.addEventListener('click', function() {
            currentToothNumber = this.getAttribute('data-tooth-number');
            const dentitionType = this.getAttribute('data-dentition');
            const currentStatus = toothStates[currentToothNumber] || '';

            // Update modal title with appropriate identifier
            let toothIdentifier;
            if (dentitionType === 'primary') {
                const letter = primaryTeethLetters[currentToothNumber] || currentToothNumber;
                toothIdentifier = `Tooth ${letter} (${currentToothNumber})`;
            } else {
                toothIdentifier = `Tooth #${currentToothNumber}`;
            }

            document.getElementById('modalToothNumber').textContent = toothIdentifier;
            document.getElementById('selectedToothNumber').value = currentToothNumber;

            // Pre-select current status
            document.querySelectorAll('input[name="tooth_status"]').forEach(radio => {
                radio.checked = (radio.value === currentStatus);
            });
        });
    });

    // Handle status selection highlight
    document.querySelectorAll('.tooth-status-option').forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Save tooth status
    document.getElementById('saveToothStatus').addEventListener('click', function() {
        const selectedStatus = document.querySelector('input[name="tooth_status"]:checked');

        if (!selectedStatus) {
            alert('Please select a status');
            return;
        }

        const status = selectedStatus.value;
        const toothNumber = currentToothNumber;

        // Update local state
        if (status === '') {
            delete toothStates[toothNumber];
        } else {
            toothStates[toothNumber] = status;
        }

        // Apply visual update immediately
        applyToothStatus(toothNumber, status);

        // Save to database via AJAX
        saveToothChartToDatabase();

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('toothStatusModal'));
        modal.hide();
    });

    // Save tooth chart to database
    function saveToothChartToDatabase() {
        const saveButton = document.getElementById('saveToothStatus');
        const originalText = saveButton.textContent;
        saveButton.disabled = true;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        fetch('{{ route("orthodontics.tooth-chart.update", $orthodonticCase) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                tooth_states: toothStates
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification (optional - you can add a toast notification here)
                console.log('Tooth chart saved successfully');
            } else {
                alert('Failed to save: ' + (data.message || 'Unknown error'));
                console.error('Save failed:', data);
            }
        })
        .catch(error => {
            console.error('Error saving tooth chart:', error);
            alert('An error occurred while saving. Please try again.');
        })
        .finally(() => {
            saveButton.disabled = false;
            saveButton.textContent = originalText;
        });
    }

    // Initialize tooth states on page load
    applyToothStates();

    // ============================================
    // DENTITION TOGGLE FUNCTIONALITY
    // ============================================

    // Handle dentition type toggle (Permanent vs Primary)
    document.querySelectorAll('input[name="dentition_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedType = this.value;

            // Toggle visibility
            if (selectedType === 'permanent') {
                document.getElementById('permanent-dentition').style.display = 'block';
                document.getElementById('primary-dentition').style.display = 'none';
            } else {
                document.getElementById('permanent-dentition').style.display = 'none';
                document.getElementById('primary-dentition').style.display = 'block';
            }

            // Re-apply tooth states after toggle
            setTimeout(() => {
                applyToothStates();
            }, 100);
        });
    });

    // ============================================
    // TREATMENT TIMELINE FUNCTIONALITY
    // ============================================

    const phaseDescriptions = {
        'bonding': 'Initial bracket placement and appliance installation phase.',
        'alignment': 'Aligning teeth and leveling the arches using light, flexible wires.',
        'space_closure': 'Closing extraction spaces or gaps between teeth.',
        'finishing': 'Fine-tuning tooth positions and achieving ideal occlusion.',
        'retention': 'Maintaining treatment results with retainers after appliance removal.'
    };

    // Handle Quick Update dropdown selections
    document.querySelectorAll('.phase-update-option').forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();

            const phase = this.getAttribute('data-phase');
            const label = this.getAttribute('data-label');

            // Show confirmation
            if (confirm(`Update treatment phase to: ${label}?`)) {
                updateTreatmentPhase(phase, label);
            }
        });
    });

    // Handle timeline step clicks
    document.querySelectorAll('.timeline-step').forEach(step => {
        step.addEventListener('click', function() {
            const phase = this.getAttribute('data-phase');
            const label = this.getAttribute('data-label');

            // Show confirmation
            if (confirm(`Update treatment phase to: ${label}?\n\nThis will mark this phase as the current active phase.`)) {
                updateTreatmentPhase(phase, label);
            }
        });
    });

    // Update treatment phase via AJAX
    function updateTreatmentPhase(phase, label) {
        // Show loading state
        const currentPhaseLabel = document.getElementById('currentPhaseLabel');
        const originalText = currentPhaseLabel.textContent;
        currentPhaseLabel.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        fetch('{{ route("orthodontics.phase.update", $orthodonticCase) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                current_phase: phase
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the timeline visually
                updateTimelineVisual(phase);

                // Update the current phase label
                currentPhaseLabel.textContent = label;

                // Update phase description
                const phaseDescElement = document.getElementById('phaseDescription');
                if (phaseDescElement && phaseDescriptions[phase]) {
                    phaseDescElement.textContent = phaseDescriptions[phase];
                }

                // Update dropdown active states
                document.querySelectorAll('.phase-update-option').forEach(opt => {
                    const optPhase = opt.getAttribute('data-phase');
                    const icon = opt.querySelector('i');

                    if (optPhase === phase) {
                        opt.classList.add('active');
                        icon.className = 'fas fa-check-circle text-success me-2';
                    } else {
                        opt.classList.remove('active');
                        icon.className = 'far fa-circle me-2';
                    }
                });

                // Show success message (optional)
                showNotification('success', data.message || 'Treatment phase updated successfully!');
            } else {
                currentPhaseLabel.textContent = originalText;
                showNotification('error', data.message || 'Failed to update phase');
            }
        })
        .catch(error => {
            console.error('Error updating phase:', error);
            currentPhaseLabel.textContent = originalText;
            showNotification('error', 'An error occurred while updating the phase');
        });
    }

    // Update timeline visual states
    function updateTimelineVisual(newPhase) {
        const allSteps = document.querySelectorAll('.timeline-step');
        const phaseKeys = Array.from(allSteps).map(step => step.getAttribute('data-phase'));
        const newIndex = phaseKeys.indexOf(newPhase);

        allSteps.forEach((step, index) => {
            const connector = step.querySelector('.timeline-connector');

            // Remove all state classes
            step.classList.remove('completed', 'current', 'pending');

            // Add appropriate class
            if (index < newIndex) {
                step.classList.add('completed');
                if (connector) connector.classList.add('completed');
            } else if (index === newIndex) {
                step.classList.add('current');
                if (connector) connector.classList.remove('completed');
            } else {
                step.classList.add('pending');
                if (connector) connector.classList.remove('completed');
            }
        });
    }

    // Show notification (simple alert-based, can be upgraded to toast)
    function showNotification(type, message) {
        // Simple implementation - you can replace with a proper toast notification
        if (type === 'success') {
            console.log('✓ ' + message);
        } else {
            console.error('✗ ' + message);
            alert(message);
        }
    }

    // ============================================
    // PHOTO COMPARISON FUNCTIONALITY
    // ============================================

    let selectedPhotos = [];
    const maxSelection = 2;

    // Handle photo checkbox selection
    document.querySelectorAll('.photo-select-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const photoId = this.getAttribute('data-photo-id');
            const photoCard = document.querySelector(`.photo-card[data-photo-id="${photoId}"]`);

            if (this.checked) {
                if (selectedPhotos.length >= maxSelection) {
                    // Deselect the oldest selection
                    const oldestCheckbox = document.querySelector(`.photo-select-checkbox[data-photo-id="${selectedPhotos[0].id}"]`);
                    const oldestCard = document.querySelector(`.photo-card[data-photo-id="${selectedPhotos[0].id}"]`);
                    if (oldestCheckbox) oldestCheckbox.checked = false;
                    if (oldestCard) oldestCard.classList.remove('selected');
                    selectedPhotos.shift();
                }

                selectedPhotos.push({
                    id: photoId,
                    path: this.getAttribute('data-photo-path'),
                    date: this.getAttribute('data-photo-date'),
                    type: this.getAttribute('data-photo-type'),
                    viewType: this.getAttribute('data-view-type'),
                    stage: this.getAttribute('data-stage')
                });

                photoCard.classList.add('selected');
            } else {
                selectedPhotos = selectedPhotos.filter(p => p.id !== photoId);
                photoCard.classList.remove('selected');
            }

            updateCompareToolbar();
        });
    });

    // Update compare toolbar visibility and state
    function updateCompareToolbar() {
        const toolbar = document.getElementById('compareToolbar');
        const compareBtn = document.getElementById('compareBtn');
        const selectedCount = document.getElementById('selectedCount');

        if (selectedPhotos.length > 0) {
            toolbar.style.display = 'block';
            selectedCount.textContent = selectedPhotos.length;
            compareBtn.disabled = selectedPhotos.length !== 2;
        } else {
            toolbar.style.display = 'none';
        }
    }

    // Handle compare button click
    document.getElementById('compareBtn')?.addEventListener('click', function() {
        if (selectedPhotos.length === 2) {
            showPhotoComparison(selectedPhotos[0], selectedPhotos[1]);
        }
    });

    // Handle clear selection button
    document.getElementById('clearSelectionBtn')?.addEventListener('click', function() {
        document.querySelectorAll('.photo-select-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        document.querySelectorAll('.photo-card').forEach(card => {
            card.classList.remove('selected');
        });

        selectedPhotos = [];
        updateCompareToolbar();
    });

    // Show photo comparison modal
    function showPhotoComparison(photo1, photo2) {
        // Set Photo 1
        document.getElementById('comparisonPhoto1').src = photo1.path;
        document.getElementById('comparisonPhoto1Type').textContent = photo1.type.toUpperCase();
        document.getElementById('comparisonPhoto1View').textContent = photo1.viewType.toUpperCase();
        document.getElementById('comparisonPhoto1Stage').textContent = photo1.stage.toUpperCase();
        document.getElementById('comparisonPhoto1Date').textContent = photo1.date;

        // Set Photo 2
        document.getElementById('comparisonPhoto2').src = photo2.path;
        document.getElementById('comparisonPhoto2Type').textContent = photo2.type.toUpperCase();
        document.getElementById('comparisonPhoto2View').textContent = photo2.viewType.toUpperCase();
        document.getElementById('comparisonPhoto2Stage').textContent = photo2.stage.toUpperCase();
        document.getElementById('comparisonPhoto2Date').textContent = photo2.date;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('photoComparisonModal'));
        modal.show();
    }

    // Single photo view
    window.viewSinglePhoto = function(src, title, date) {
        document.getElementById('photoViewImage').src = src;
        document.getElementById('photoViewTitle').textContent = title;
        document.getElementById('photoViewInfo').innerHTML = `
            <div class="badge bg-light text-dark">
                <i class="fas fa-calendar me-2"></i>${date}
            </div>
        `;
    };

    // Handle photo image clicks for quick view
    document.querySelectorAll('.photo-image').forEach(img => {
        img.addEventListener('click', function() {
            const src = this.getAttribute('data-photo-src');
            const info = this.getAttribute('data-photo-info');
            const date = this.closest('.photo-card').querySelector('.fa-calendar-day')?.parentElement.textContent.trim() || '';
            viewSinglePhoto(src, info, date);
        });
    });
});
</script>
@endpush
