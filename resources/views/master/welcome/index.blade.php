@extends('layouts.master-welcome')

@section('title', 'ConCure Master Control - Platform Management')

@section('content')
<div class="master-container">
    <div class="container">
        <div class="master-card">
            <!-- Navigation Breadcrumb -->
            <div class="text-center py-3" style="background: rgba(220, 53, 69, 0.1); border-bottom: 1px solid rgba(220, 53, 69, 0.2);">
                <div class="container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0" style="background: none;">
                            <li class="breadcrumb-item">
                                <a href="{{ route('main.welcome') }}" class="text-decoration-none text-danger">
                                    <i class="fas fa-home me-1"></i>ConCure Platform
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-danger" aria-current="page">
                                <i class="fas fa-crown me-1"></i>Master Control Panel
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-content">
                    <div class="master-badge">
                        <i class="fas fa-crown me-1"></i>
                        Master Control Panel
                    </div>
                    <div class="hero-title">
                        ConCure
                        <div style="font-size: 1.5rem; font-weight: 500; margin-top: 0.5rem;">
                            Platform Management
                        </div>
                    </div>
                    <p class="hero-subtitle">
                        Comprehensive platform management for healthcare technology leaders. 
                        Control clinics, manage users, monitor analytics, and scale your healthcare network.
                    </p>
                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                        <a href="{{ route('master.welcome.register') }}" class="btn btn-primary-master">
                            <i class="fas fa-user-shield me-2"></i>
                            Register as Program Owner
                        </a>
                        <a href="{{ route('master.welcome.login') }}" class="btn btn-outline-master">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Access Control Panel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="container py-5">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <h2 class="display-5 fw-bold text-dark mb-3">Platform Management Features</h2>
                        <p class="lead text-muted">Advanced tools for healthcare platform administrators</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Clinic Management</h4>
                            <p class="text-muted">Oversee all registered clinics, manage subscriptions, monitor usage, and provide support across your healthcare network.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-users-cog"></i>
                            </div>
                            <h4 class="fw-bold mb-3">User Administration</h4>
                            <p class="text-muted">Manage platform users, assign roles, control permissions, and maintain security across all clinic instances.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Analytics & Insights</h4>
                            <p class="text-muted">Comprehensive analytics dashboard with usage metrics, revenue tracking, and performance insights.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Security & Compliance</h4>
                            <p class="text-muted">Advanced security monitoring, audit logs, compliance reporting, and data protection management.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h4 class="fw-bold mb-3">System Configuration</h4>
                            <p class="text-muted">Platform-wide settings, feature toggles, system maintenance, and configuration management.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Support Management</h4>
                            <p class="text-muted">Integrated support system, ticket management, and direct communication with clinic administrators.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">500+</span>
                                <div class="stat-label">Active Clinics</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">50K+</span>
                                <div class="stat-label">Healthcare Providers</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">1M+</span>
                                <div class="stat-label">Patients Served</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">99.9%</span>
                                <div class="stat-label">Platform Uptime</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Levels Section -->
            <div class="container py-5">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <h2 class="display-6 fw-bold text-dark mb-3">Access Levels</h2>
                        <p class="lead text-muted">Different roles for different responsibilities</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon" style="background: linear-gradient(135deg, #dc2626, #ef4444);">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Program Owner</h4>
                            <p class="text-muted mb-3">Full platform control with all administrative privileges.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i>Clinic Management</li>
                                <li><i class="fas fa-check text-success me-2"></i>User Administration</li>
                                <li><i class="fas fa-check text-success me-2"></i>Financial Reports</li>
                                <li><i class="fas fa-check text-success me-2"></i>System Configuration</li>
                                <li><i class="fas fa-check text-success me-2"></i>Team Management</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon" style="background: linear-gradient(135deg, #0ea5e9, #06b6d4);">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Platform Admin</h4>
                            <p class="text-muted mb-3">Administrative access with operational control.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i>Clinic Monitoring</li>
                                <li><i class="fas fa-check text-success me-2"></i>User Support</li>
                                <li><i class="fas fa-check text-success me-2"></i>Analytics Access</li>
                                <li><i class="fas fa-check text-success me-2"></i>Audit Logs</li>
                                <li><i class="fas fa-times text-muted me-2"></i>Financial Access</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Support Agent</h4>
                            <p class="text-muted mb-3">Customer support and basic monitoring access.</p>
                            <ul class="list-unstyled text-start">
                                <li><i class="fas fa-check text-success me-2"></i>Support Tickets</li>
                                <li><i class="fas fa-check text-success me-2"></i>Basic Analytics</li>
                                <li><i class="fas fa-check text-success me-2"></i>User Assistance</li>
                                <li><i class="fas fa-times text-muted me-2"></i>System Config</li>
                                <li><i class="fas fa-times text-muted me-2"></i>User Management</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="container py-5">
                <div class="row text-center">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="display-6 fw-bold text-dark mb-3">Ready to Manage Your Healthcare Platform?</h2>
                        <p class="lead text-muted mb-4">
                            Take control of your healthcare technology platform with comprehensive management tools 
                            designed for scale, security, and efficiency.
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="{{ route('master.welcome.register') }}" class="btn btn-primary-master btn-lg">
                                <i class="fas fa-user-shield me-2"></i>
                                Register as Program Owner
                            </a>
                            <a href="{{ route('master.welcome.login') }}" class="btn btn-outline-master btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Access Control Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section mt-4">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-md-start text-center">
                        <p class="mb-0">&copy; {{ date('Y') }} ConCure Master Control. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <p class="mb-0">
                            <a href="{{ route('welcome.index') }}" class="text-white text-decoration-none me-3">
                                <i class="fas fa-hospital me-1"></i>Clinic Portal
                            </a>
                            <a href="#" class="text-white text-decoration-none me-3">Security Policy</a>
                            <a href="#" class="text-white text-decoration-none me-3">Terms of Service</a>
                            <a href="#" class="text-white text-decoration-none">Enterprise Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
</script>
@endpush
