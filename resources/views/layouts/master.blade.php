<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Master Dashboard') - ConCure SaaS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --master-primary: #dc3545;
            --master-secondary: #6f42c1;
            --master-dark: #212529;
            --master-light: #f8f9fa;
            --master-warning: #ffc107;
            --master-success: #198754;
            --master-info: #0dcaf0;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: #f8f9fa;
            color: var(--master-dark);
        }

        /* Master Navbar */
        .master-navbar {
            background: linear-gradient(45deg, var(--master-primary), var(--master-secondary)) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .master-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }

        .master-navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .master-navbar .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .master-navbar .nav-link.active {
            color: white !important;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
        }

        /* Crown Icon */
        .crown-icon {
            color: #ffd700;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(45deg, var(--master-light), white);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
        }

        /* Buttons */
        .btn-master-primary {
            background: linear-gradient(45deg, var(--master-primary), var(--master-secondary));
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-master-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .btn-outline-master {
            border: 2px solid var(--master-primary);
            color: var(--master-primary);
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-master:hover {
            background: var(--master-primary);
            border-color: var(--master-primary);
            color: white;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--master-primary), var(--master-secondary));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .stats-card .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .stats-card .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        /* Sidebar */
        .master-sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .master-sidebar .nav-link {
            color: var(--master-dark);
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .master-sidebar .nav-link:hover {
            background: rgba(220, 53, 69, 0.1);
            color: var(--master-primary);
        }

        .master-sidebar .nav-link.active {
            background: linear-gradient(45deg, var(--master-primary), var(--master-secondary));
            color: white;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
        }

        /* Tables */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--master-light);
            border: none;
            font-weight: 600;
            color: var(--master-dark);
        }

        /* Badges */
        .badge {
            border-radius: 8px;
            padding: 6px 12px;
            font-weight: 500;
        }

        /* Footer */
        .master-footer {
            background: var(--master-dark);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 15px;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Master Navigation -->
    <nav class="navbar navbar-expand-lg master-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('master.dashboard') }}">
                <i class="fas fa-crown crown-icon me-2"></i>
                ConCure Master
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#masterNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="masterNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}" href="{{ route('master.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('master.clinics*') ? 'active' : '' }}" href="{{ route('master.clinics') }}">
                            <i class="fas fa-hospital me-1"></i>
                            {{ __('Clinics') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('master.activation-codes*') ? 'active' : '' }}" href="{{ route('master.activation-codes') }}">
                            <i class="fas fa-key me-1"></i>
                            {{ __('Activation Codes') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('master.program-features') ? 'active' : '' }}" href="{{ route('master.program-features') }}">
                            <i class="fas fa-star me-1"></i>
                            {{ __('Program Features') }}
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('master.analytics*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar me-1"></i>
                            {{ __('Analytics') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('master.analytics') }}">
                                <i class="fas fa-chart-line me-2"></i>
                                {{ __('System Analytics') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('master.analytics.trials') }}">
                                <i class="fas fa-clock me-2"></i>
                                {{ __('Trial Analytics') }}
                            </a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-crown me-1"></i>
                            {{ session('master_user_name', 'Master Admin') }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('master.settings') }}">
                                <i class="fas fa-cog me-2"></i>
                                {{ __('Settings') }}
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('master.audit-logs') }}">
                                <i class="fas fa-history me-2"></i>
                                {{ __('Audit Logs') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('login') }}" target="_blank">
                                <i class="fas fa-hospital me-2"></i>
                                {{ __('View Tenant Login') }}
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('master.logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        {{ __('Logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="master-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6>ConCure SaaS Master Dashboard</h6>
                    <p class="mb-0">{{ __('Platform management and control center') }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} ConCure. {{ __('All rights reserved.') }}
                    </p>
                    <small class="text-muted">{{ __('Master Dashboard v1.0') }}</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Master Dashboard Scripts -->
    <script>
        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(function(card, index) {
                setTimeout(function() {
                    card.classList.add('fade-in-up');
                }, index * 100);
            });
        });

        // Confirm dangerous actions
        document.addEventListener('click', function(e) {
            if (e.target.closest('[data-confirm]')) {
                const message = e.target.closest('[data-confirm]').getAttribute('data-confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
