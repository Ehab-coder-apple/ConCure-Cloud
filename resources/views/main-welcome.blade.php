<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ConCure - Healthcare Management Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #008080;
            --primary-dark: #006666;
            --primary-light: #4db8b8;
            --secondary-color: #f8f9fa;
            --accent-color: #ff6b6b;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --master-primary: #dc3545;
            --master-secondary: #6f42c1;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            height: 100vh;
            color: var(--text-dark);
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        .main-container {
            height: 100vh;
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            overflow: hidden;
            margin: 0;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 249, 250, 0.9) 100%);
            padding: 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            flex-shrink: 0;
        }

        .hero-title-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .hero-logo {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--primary-color);
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.4;
        }

        .promotional-content {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid rgba(0, 128, 128, 0.1);
        }

        .promotional-content:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }

        .promotional-inner {
            padding: 2rem 1.5rem;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .promotional-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .promotional-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .promotional-text {
            font-size: 0.95rem;
            color: var(--text-dark);
            line-height: 1.5;
            margin-bottom: 1rem;
            font-weight: 400;
        }

        .promotional-text .highlight {
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .promotional-content:hover .highlight {
            color: var(--primary-dark);
            text-shadow: 0 2px 4px rgba(0, 128, 128, 0.2);
        }

        .promotional-features {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .feature-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }

        .access-cards {
            padding: 1rem 1.5rem;
            flex: 1;
            display: flex;
            align-items: center;
        }

        .access-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
        }

        .access-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .clinic-card {
            border-left: 5px solid var(--primary-color);
        }

        .clinic-card .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 1.2rem;
        }

        .master-card {
            border-left: 5px solid var(--master-primary);
        }

        .master-card .card-header {
            background: linear-gradient(135deg, var(--master-primary), var(--master-secondary));
            color: white;
            border: none;
            padding: 1.2rem;
        }

        .card-icon {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .card-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            margin-bottom: 0;
        }

        .card-body {
            padding: 1.2rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin-bottom: 1rem;
        }

        .feature-list li {
            padding: 0.3rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list i {
            width: 20px;
            color: var(--primary-color);
        }

        .master-card .feature-list i {
            color: var(--master-primary);
        }

        .btn-clinic {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-clinic:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 128, 128, 0.3);
        }

        .btn-master {
            background: linear-gradient(135deg, var(--master-primary), var(--master-secondary));
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-master:hover {
            background: linear-gradient(135deg, var(--master-secondary), var(--master-primary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .footer-section {
            background: rgba(0, 0, 0, 0.05);
            padding: 0.8rem 1.5rem;
            text-align: center;
            color: var(--text-light);
            flex-shrink: 0;
            font-size: 0.85rem;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Viewport optimizations for single-page experience */
        @media (max-height: 800px) {
            .hero-section {
                padding: 1rem 1.5rem;
            }

            .hero-title {
                font-size: 2.4rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
            }

            .card-body {
                padding: 1rem;
            }

            .feature-list li {
                padding: 0.2rem 0;
                font-size: 0.85rem;
            }

            .footer-section {
                padding: 0.5rem 1.5rem;
                font-size: 0.8rem;
            }

            .promotional-inner {
                padding: 1.5rem 1rem;
            }

            .promotional-text {
                font-size: 0.9rem;
            }

            .promotional-title {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 0.25rem 0;
            }

            .hero-title-container {
                flex-direction: column;
                gap: 0.5rem;
                margin-bottom: 0.3rem;
            }

            .hero-logo {
                width: 45px;
                height: 45px;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            .hero-section {
                padding: 1rem;
            }

            .access-cards {
                padding: 0.5rem;
            }

            .card-body {
                padding: 0.8rem;
            }

            .feature-list li {
                font-size: 0.8rem;
                padding: 0.15rem 0;
            }

            .footer-section {
                padding: 0.4rem;
                font-size: 0.75rem;
            }

            .promotional-inner {
                padding: 1rem 0.8rem;
            }

            .promotional-text {
                font-size: 0.85rem;
                line-height: 1.4;
            }

            .promotional-title {
                font-size: 1.1rem;
            }

            .promotional-features {
                gap: 0.3rem;
            }

            .feature-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }

            /* Stack promotional content on mobile */
            .promotional-content {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="container">
            <div class="welcome-card">
                <!-- Floating Background Shapes -->
                <div class="floating-shapes">
                    <div class="shape">
                        <i class="fas fa-hospital fa-3x"></i>
                    </div>
                    <div class="shape">
                        <i class="fas fa-user-md fa-2x"></i>
                    </div>
                    <div class="shape">
                        <i class="fas fa-heartbeat fa-4x"></i>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="hero-section">
                    <div class="hero-title-container">
                        <!-- ConCure Logo -->
                        <svg class="hero-logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <!-- Medical Cross Background Circle -->
                            <circle cx="50" cy="50" r="45" fill="#008080" stroke="#006666" stroke-width="2"/>

                            <!-- Medical Cross -->
                            <rect x="42" y="25" width="16" height="50" fill="white" rx="2"/>
                            <rect x="25" y="42" width="50" height="16" fill="white" rx="2"/>

                            <!-- Heart Symbol -->
                            <path d="M35 35 C35 30, 40 25, 45 30 C50 25, 55 30, 55 35 C55 40, 50 50, 45 55 C40 50, 35 40, 35 35 Z"
                                  fill="#ff6b6b" opacity="0.8"/>

                            <!-- Stethoscope curve -->
                            <path d="M30 65 Q35 70, 40 65 Q45 60, 50 65"
                                  stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <circle cx="30" cy="65" r="3" fill="white"/>
                            <circle cx="50" cy="65" r="3" fill="white"/>
                        </svg>

                        <div class="hero-title">
                            ConCure
                        </div>
                    </div>
                    <p class="hero-subtitle">
                        Comprehensive Healthcare Management Platform - Choose your access level to get started with our powerful clinic management and platform administration tools.
                    </p>
                </div>

                <!-- Access Cards Section -->
                <div class="access-cards">
                    <div class="row g-3">
                        <!-- Clinic Management Portal -->
                        <div class="col-lg-4">
                            <div class="card access-card clinic-card h-100">
                                <div class="card-header text-center">
                                    <div class="card-icon">
                                        <i class="fas fa-hospital"></i>
                                    </div>
                                    <h4 class="card-title">Clinic Management</h4>
                                    <p class="card-subtitle">Healthcare Providers & Staff</p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-primary mb-2">For Healthcare Professionals</h5>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check me-2"></i>Patient Management & Digital Prescriptions</li>
                                        <li><i class="fas fa-check me-2"></i>Appointment Scheduling & Lab Requests</li>
                                        <li><i class="fas fa-check me-2"></i>Nutrition Planning & Multi-language Support</li>
                                    </ul>
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('welcome.index') }}" class="btn btn-clinic">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            Access Clinic Portal
                                        </a>
                                        <small class="text-muted text-center mt-2">
                                            For doctors, nurses, and clinic staff
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Master Control Panel -->
                        <div class="col-lg-4">
                            <div class="card access-card master-card h-100">
                                <div class="card-header text-center">
                                    <div class="card-icon">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <h4 class="card-title">Master Control</h4>
                                    <p class="card-subtitle">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        For Platform Owners
                                    </p>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-danger mb-2">
                                        <i class="fas fa-crown me-2"></i>For Platform Owners Only
                                    </h5>
                                    <ul class="feature-list">
                                        <li><i class="fas fa-check me-2"></i>Multi-Clinic Management & User Control</li>
                                        <li><i class="fas fa-check me-2"></i>Analytics, Reporting & System Monitoring</li>
                                        <li><i class="fas fa-check me-2"></i>Activation Codes & Platform Configuration</li>
                                    </ul>
                                    <div class="d-grid gap-2">
                                        <a href="{{ url('/master/login') }}" class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>
                                            Open Master Dashboard
                                        </a>
                                        <small class="text-center mt-2 text-muted">
                                            Super Admin only. Use your super admin credentials to sign in.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Promotional Content -->
                        <div class="col-lg-4">
                            <div class="promotional-content h-100">
                                <div class="promotional-inner">
                                    <div class="promotional-icon">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <h4 class="promotional-title">Why Choose ConCure?</h4>
                                    <p class="promotional-text">
                                        <span class="highlight">Next-generation clinical platform</span> for healthcare professionals — manage patients, prescribe digitally, schedule appointments, and plan nutrition with <span class="highlight">multilingual support</span>.
                                    </p>
                                    <p class="promotional-text">
                                        Simplify workflows, connect labs, and deliver <span class="highlight">smarter, faster care</span> — all in one secure system.
                                    </p>
                                    <div class="promotional-features">
                                        <div class="feature-badge">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Secure & Reliable
                                        </div>
                                        <div class="feature-badge">
                                            <i class="fas fa-globe me-1"></i>
                                            Multilingual
                                        </div>
                                        <div class="feature-badge">
                                            <i class="fas fa-mobile-alt me-1"></i>
                                            Mobile Ready
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer-section">
                    <div class="row">
                        <div class="col-md-6 text-md-start text-center">
                            <p class="mb-0">&copy; {{ date('Y') }} ConCure Healthcare Platform. All rights reserved.</p>
                        </div>
                        <div class="col-md-6 text-md-end text-center">
                            <p class="mb-0">
                                <a href="#" class="text-muted text-decoration-none me-3">Documentation</a>
                                <a href="#" class="text-muted text-decoration-none me-3">Support</a>
                                <a href="#" class="text-muted text-decoration-none">Contact</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
