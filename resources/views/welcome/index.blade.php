@extends('layouts.welcome')

@section('title', 'ConCure - Modern Clinic Management System')

@section('content')
<div class="welcome-container">
    <div class="container">
        <div class="welcome-card">
            <!-- Navigation Breadcrumb -->
            <div class="text-center py-3" style="background: rgba(0, 128, 128, 0.1); border-bottom: 1px solid rgba(0, 128, 128, 0.2);">
                <div class="container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center mb-0" style="background: none;">
                            <li class="breadcrumb-item">
                                <a href="{{ route('main.welcome') }}" class="text-decoration-none text-primary">
                                    <i class="fas fa-home me-1"></i>ConCure Platform
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-primary" aria-current="page">
                                <i class="fas fa-hospital me-1"></i>Clinic Management Portal
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="hero-section">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-8 mx-auto">
                            <div class="hero-title">
                                ConCure
                                <div style="font-size: 1.5rem; font-weight: 500; margin-top: 0.5rem;">
                                    Clinic Management
                                </div>
                            </div>
                            <p class="hero-subtitle">
                                Streamline your healthcare practice with our comprehensive clinic management solution. 
                                Manage patients, prescriptions, appointments, and more - all in one powerful platform.
                            </p>
                            <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                                <a href="{{ route('welcome.register') }}" class="btn btn-primary-custom">
                                    <i class="fas fa-rocket me-2"></i>
                                    Start Free Trial
                                </a>
                                <a href="{{ route('welcome.login') }}" class="btn btn-outline-custom">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Sign In
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="container py-5">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <h2 class="display-5 fw-bold text-dark mb-3">Everything You Need to Manage Your Clinic</h2>
                        <p class="lead text-muted">Powerful features designed specifically for healthcare professionals</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Patient Management</h4>
                            <p class="text-muted">Complete patient records, medical history, and demographic information management with multilingual support.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-prescription-bottle-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Digital Prescriptions</h4>
                            <p class="text-muted">Create, manage, and print digital prescriptions with medicine inventory integration and PDF generation.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Appointment Scheduling</h4>
                            <p class="text-muted">Efficient appointment booking and management system with calendar integration and reminders.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-flask"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Lab Requests</h4>
                            <p class="text-muted">Streamlined laboratory test requests with external lab integration and result tracking.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-apple-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Nutrition Planning</h4>
                            <p class="text-muted">Comprehensive nutrition plans for weight management, muscle gain, and specialized dietary needs.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4 class="fw-bold mb-3">PWA Ready</h4>
                            <p class="text-muted">Progressive Web App technology for mobile-first experience and offline capabilities.</p>
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
                                <div class="stat-label">Healthcare Providers</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">50K+</span>
                                <div class="stat-label">Patients Managed</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">99.9%</span>
                                <div class="stat-label">Uptime</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-item">
                                <span class="stat-number">24/7</span>
                                <div class="stat-label">Support</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="container py-5">
                <div class="row text-center">
                    <div class="col-lg-8 mx-auto">
                        <h2 class="display-6 fw-bold text-dark mb-3">Ready to Transform Your Clinic?</h2>
                        <p class="lead text-muted mb-4">
                            Join thousands of healthcare professionals who trust ConCure for their clinic management needs.
                            Get started with ConCure today and streamline your clinic operations.
                        </p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="{{ route('welcome.register') }}" class="btn btn-primary-custom btn-lg">
                                <i class="fas fa-rocket me-2"></i>
                                Get Started
                            </a>
                            <a href="#features" class="btn btn-outline-custom btn-lg">
                                <i class="fas fa-info-circle me-2"></i>
                                Learn More
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
                        <p class="mb-0">&copy; {{ date('Y') }} ConCure. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end text-center">
                        <p class="mb-0">
                            <a href="{{ route('main.welcome') }}" class="text-white text-decoration-none me-3">
                                <i class="fas fa-home me-1"></i>Platform Home
                            </a>
                            <a href="#" class="text-white text-decoration-none me-3">Privacy Policy</a>
                            <a href="#" class="text-white text-decoration-none me-3">Terms of Service</a>
                            <a href="#" class="text-white text-decoration-none">Support</a>
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
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

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
