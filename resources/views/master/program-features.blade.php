@extends('layouts.master')

@section('title', __('Program Features'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        {{ __('ConCure Program Features') }}
                    </h1>
                    <p class="text-muted mb-0">{{ __('Comprehensive Clinical Management System Features & Benefits') }}</p>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print me-1"></i>
                        {{ __('Print Features') }}
                    </button>
                    <a href="{{ route('master.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back to Dashboard') }}
                    </a>
                </div>
            </div>

            <!-- Print Header (only visible when printing) -->
            <div class="print-only text-center mb-4">
                <h1 class="display-4 text-primary mb-2">ConCure</h1>
                <h2 class="h4 text-secondary mb-3">{{ __('Clinical Management System') }}</h2>
                <p class="lead">{{ __('Complete Features & Benefits Overview') }}</p>
                <hr class="my-4">
            </div>

            <!-- System Overview -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="feature-card-header" style="padding: 0.75rem 1.25rem; background-color: #0d6efd !important; color: #ffffff !important; border-bottom: 1px solid rgba(0,0,0,.125); border-radius: 15px 15px 0 0;">
                            <h5 class="feature-card-title" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #ffffff !important;">
                                <i class="fas fa-hospital feature-card-icon" style="margin-right: 0.5rem; color: #ffffff !important;"></i>
                                {{ __('ConCure Clinical Management System Overview') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <p class="lead">{{ __('ConCure is a comprehensive, multi-tenant SaaS clinical management platform designed to streamline healthcare operations for clinics of all sizes.') }}</p>
                                    <p>{{ __('Built with modern web technologies and featuring multi-language support, ConCure provides healthcare professionals with powerful tools to manage patients, prescriptions, appointments, nutrition planning, and laboratory requests efficiently.') }}</p>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">{{ __('Key Highlights:') }}</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('Multi-language Support (English, Arabic, Kurdish)') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('Progressive Web App (PWA) Ready') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('Role-based Access Control') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('AI-Powered Medical Advisory') }}</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-primary">{{ __('Technical Features:') }}</h6>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('SQLite Database for Reliability') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('Responsive Design') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('RTL Language Support') }}</li>
                                                <li><i class="fas fa-check text-success me-2"></i>{{ __('Comprehensive Audit Logging') }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Categories -->
            <div class="row">
                @foreach($featureCategories as $categoryKey => $category)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 border-{{ $category['color'] }}">
                        @php
                            $headerStyle = '';
                            $textColor = '#ffffff';
                            switch($category['color']) {
                                case 'primary':
                                    $headerStyle = 'background-color: #0d6efd !important;';
                                    break;
                                case 'success':
                                    $headerStyle = 'background-color: #198754 !important;';
                                    break;
                                case 'info':
                                    $headerStyle = 'background-color: #0dcaf0 !important;';
                                    break;
                                case 'warning':
                                    $headerStyle = 'background-color: #ffc107 !important;';
                                    $textColor = '#000000';
                                    break;
                                case 'danger':
                                    $headerStyle = 'background-color: #dc3545 !important;';
                                    break;
                                case 'secondary':
                                    $headerStyle = 'background-color: #6c757d !important;';
                                    break;
                                case 'teal':
                                    $headerStyle = 'background-color: #20c997 !important;';
                                    break;
                                case 'purple':
                                    $headerStyle = 'background-color: #6f42c1 !important;';
                                    break;
                                case 'dark':
                                    $headerStyle = 'background-color: #212529 !important;';
                                    break;
                                default:
                                    $headerStyle = 'background-color: #0d6efd !important;';
                            }
                        @endphp
                        <div class="feature-card-header" style="padding: 0.75rem 1.25rem; {{ $headerStyle }} color: {{ $textColor }} !important; border-bottom: 1px solid rgba(0,0,0,.125); border-radius: 15px 15px 0 0;">
                            <h6 class="feature-card-title" style="margin: 0; font-size: 1rem; font-weight: 600; color: {{ $textColor }} !important;">
                                <i class="{{ $category['icon'] }} feature-card-icon" style="margin-right: 0.5rem; color: {{ $textColor }} !important;"></i>
                                {{ $category['title'] }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($category['features'] as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-{{ $category['color'] }} me-2"></i>
                                    <small>{{ $feature }}</small>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>



            <!-- Benefits Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="feature-card-header" style="padding: 0.75rem 1.25rem; background-color: #198754 !important; color: #ffffff !important; border-bottom: 1px solid rgba(0,0,0,.125); border-radius: 15px 15px 0 0;">
                            <h5 class="feature-card-title" style="margin: 0; font-size: 1.25rem; font-weight: 600; color: #ffffff !important;">
                                <i class="fas fa-thumbs-up feature-card-icon" style="margin-right: 0.5rem; color: #ffffff !important;"></i>
                                {{ __('Key Benefits for Healthcare Providers') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-success">{{ __('Operational Benefits:') }}</h6>
                                    <ul>
                                        <li>{{ __('Streamlined patient management workflow') }}</li>
                                        <li>{{ __('Reduced paperwork and administrative burden') }}</li>
                                        <li>{{ __('Improved appointment scheduling efficiency') }}</li>
                                        <li>{{ __('Enhanced prescription accuracy and tracking') }}</li>
                                        <li>{{ __('Comprehensive nutrition planning capabilities') }}</li>
                                        <li>{{ __('Integrated laboratory request management') }}</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-success">{{ __('Technical Benefits:') }}</h6>
                                    <ul>
                                        <li>{{ __('Multi-language support for diverse patient populations') }}</li>
                                        <li>{{ __('Progressive Web App for mobile accessibility') }}</li>
                                        <li>{{ __('Role-based security and access control') }}</li>
                                        <li>{{ __('AI-powered medical advisory assistance') }}</li>
                                        <li>{{ __('Comprehensive reporting and analytics') }}</li>
                                        <li>{{ __('Reliable SQLite database with audit logging') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Information -->
            <div class="row mt-5 print-only">
                <div class="col-12">
                    <div class="text-center border-top pt-4">
                        <p class="text-muted mb-1">{{ __('ConCure Clinical Management System') }}</p>
                        <p class="text-muted mb-1">{{ __('Generated on') }}: {{ now()->format('F d, Y \a\t H:i') }}</p>
                        <p class="text-muted mb-0">{{ __('For more information, contact your system administrator') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Print Styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        break-inside: avoid;
        margin-bottom: 1rem;
    }
    
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .card-header.bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .card-header.bg-success {
        background-color: #198754 !important;
        color: white !important;
    }

    .card-header.bg-info {
        background-color: #0dcaf0 !important;
        color: white !important;
    }

    .card-header.bg-warning {
        background-color: #ffc107 !important;
        color: black !important;
    }

    .card-header.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .card-header.bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
    
    .text-primary { color: #0d6efd !important; }
    .text-success { color: #198754 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-secondary { color: #6c757d !important; }
    
    .bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }
    .bg-success {
        background-color: #198754 !important;
        color: white !important;
    }
    .bg-info {
        background-color: #0dcaf0 !important;
        color: white !important;
    }
    .bg-warning {
        background-color: #ffc107 !important;
        color: black !important;
    }
    .bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .h1, .h2, .h3, .h4, .h5, .h6 {
        color: #000 !important;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
}

/* Screen Styles */
@media screen {
    .print-only {
        display: none !important;
    }

    /* Ensure proper colors on screen */
    .card-header.bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .card-header.bg-success {
        background-color: #198754 !important;
        color: white !important;
    }

    .card-header.bg-info {
        background-color: #0dcaf0 !important;
        color: white !important;
    }

    .card-header.bg-warning {
        background-color: #ffc107 !important;
        color: black !important;
    }

    .card-header.bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .card-header.bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
}

/* Custom color for teal */
.text-teal { color: #20c997 !important; }
.bg-teal {
    background-color: #20c997 !important;
    color: white !important;
}
.border-teal { border-color: #20c997 !important; }

/* Custom color for purple */
.text-purple { color: #6f42c1 !important; }
.bg-purple {
    background-color: #6f42c1 !important;
    color: white !important;
}
.border-purple { border-color: #6f42c1 !important; }

/* Custom color for dark */
.bg-dark {
    background-color: #212529 !important;
    color: white !important;
}

/* Force all background colors to work properly */
.bg-primary { background-color: #0d6efd !important; }
.bg-success { background-color: #198754 !important; }
.bg-info { background-color: #0dcaf0 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-secondary { background-color: #6c757d !important; }

/* Override master layout card-header styles specifically for feature cards */
.feature-card-header {
    background: none !important;
    background-color: inherit !important;
    background-image: none !important;
}

.feature-card-header.feature-card-header {
    background: inherit !important;
    background-image: none !important;
}

.feature-card-header h6,
.feature-card-header .feature-card-title {
    color: inherit !important;
}

.feature-card-header i,
.feature-card-header .feature-card-icon {
    color: inherit !important;
}

/* Very specific overrides to beat master layout */
div.feature-card-header {
    background: inherit !important;
    background-image: none !important;
}

div.feature-card-header h6 {
    color: inherit !important;
}

div.feature-card-header i {
    color: inherit !important;
}

/* Ensure card headers with custom colors have proper text color */
.card-header.bg-teal {
    background-color: #20c997 !important;
    color: white !important;
}

.card-header.bg-purple {
    background-color: #6f42c1 !important;
    color: white !important;
}

/* General text contrast fixes */
.card-header h5,
.card-header h6 {
    color: inherit !important;
}

/* Force proper text colors for all card headers */
.card-header {
    color: #000 !important;
}

.card-header.bg-primary,
.card-header.bg-primary h5,
.card-header.bg-primary h6 {
    color: white !important;
}

.card-header.bg-success,
.card-header.bg-success h5,
.card-header.bg-success h6 {
    color: white !important;
}

.card-header.bg-info,
.card-header.bg-info h5,
.card-header.bg-info h6 {
    color: white !important;
}

.card-header.bg-warning,
.card-header.bg-warning h5,
.card-header.bg-warning h6 {
    color: black !important;
}

.card-header.bg-danger,
.card-header.bg-danger h5,
.card-header.bg-danger h6 {
    color: white !important;
}

.card-header.bg-secondary,
.card-header.bg-secondary h5,
.card-header.bg-secondary h6 {
    color: white !important;
}

.card-header.bg-teal,
.card-header.bg-teal h5,
.card-header.bg-teal h6 {
    color: white !important;
}

.card-header.bg-purple,
.card-header.bg-purple h5,
.card-header.bg-purple h6 {
    color: white !important;
}

.card-header.bg-dark,
.card-header.bg-dark h5,
.card-header.bg-dark h6 {
    color: white !important;
}
</style>
@endpush
@endsection
