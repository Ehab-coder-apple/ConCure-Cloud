<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'ku']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ConCure') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        :root {
            --primary-color: {{ $primaryColor ?? '#008080' }};
            --primary-dark: {{ $primaryColor ? 'color-mix(in srgb, ' . $primaryColor . ' 80%, black)' : '#006666' }};
            --primary-light: {{ $primaryColor ? 'color-mix(in srgb, ' . $primaryColor . ' 20%, white)' : '#e6f7f7' }};
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 128, 128, 0.25);
        }
        
        /* Language switcher styles moved to bottom of CSS */

        /* Sidebar Layout Overrides */
        :root {
            --sidebar-width: 290px;
            --topbar-height: 60px;
            --sidebar-bg: #1e293b;
            --sidebar-text: #cbd5e1;
            --sidebar-hover: #334155;
            --sidebar-active: #0ea5e9;
        }

        /* Reset body styles for sidebar layout */
        body {
            margin: 0;
            font-family: 'Figtree', sans-serif;
            background-color: #f8fafc;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
            padding: 0.5rem 0;
        }

        .sidebar-brand:hover {
            color: white;
            transform: scale(1.02);
        }

        .sidebar-brand i {
            font-size: 1.8rem;
            margin-right: 0.75rem;
            color: #0ea5e9;
            align-self: flex-start;
            margin-top: 0.1rem;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-line-1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
        }

        .brand-line-2 {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            margin-top: -2px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--sidebar-text);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .sidebar-user {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 1.2rem;
            color: white;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: white;
            font-size: 1rem;
        }

        .user-role {
            font-size: 0.85rem;
            color: var(--sidebar-text);
            opacity: 0.8;
        }

        /* Navigation Styles */
        .sidebar-nav {
            padding: 1rem 0;
            padding-bottom: 120px; /* Add space for footer */
            min-height: calc(100vh - 200px);
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: white;
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: white;
        }

        .nav-icon {
            width: 22px;
            text-align: center;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .nav-text {
            flex: 1;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Submenu Styles */
        .has-submenu > .nav-link {
            position: relative;
        }

        .submenu-arrow {
            font-size: 0.75rem;
            transition: transform 0.2s ease;
        }

        .has-submenu.open .submenu-arrow {
            transform: rotate(90deg);
        }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.2);
            margin-bottom: 1rem; /* Add margin to prevent footer overlap */
        }

        .submenu-item {
            list-style: none;
        }

        .submenu-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem 0.5rem 3rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .submenu-link:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .submenu-link.active {
            background: var(--sidebar-active);
            color: white;
        }

        .submenu-icon {
            width: 18px;
            text-align: center;
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--sidebar-bg);
            z-index: 1001;
        }

        .logout-btn {
            width: 100%;
            background: none;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--sidebar-text);
            padding: 0.75rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: white;
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        /* Topbar Styles */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 999;
            transition: left 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .topbar-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            margin-right: 1rem;
            transition: all 0.2s ease;
            display: none;
        }

        .sidebar-toggle-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            margin-right: 120px; /* Make room for language switcher */
        }

        .topbar-user {
            display: flex;
            align-items: center;
        }

        .topbar-user .user-name {
            margin-right: 0.75rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .topbar-user .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            font-size: 1rem;
        }

        @media (max-width: 991.98px) {
            .topbar-right {
                margin-right: 60px; /* Less margin on mobile */
            }
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            min-height: calc(100vh - var(--topbar-height));
            transition: margin-left 0.3s ease;
        }

        .content-wrapper {
            padding: 1.5rem;
        }

        /* Footer Styles */
        .main-footer {
            margin-left: var(--sidebar-width);
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        /* Mobile Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .sidebar-overlay.show {
                opacity: 1;
                visibility: visible;
            }

            .topbar {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .main-footer {
                margin-left: 0;
            }

            .sidebar-toggle-btn {
                display: block;
            }

            body.sidebar-open {
                overflow: hidden;
            }
        }

        /* Language Switcher Adjustments */
        .language-switcher {
            position: fixed;
            top: 15px;
            right: 20px;
            z-index: 1100;
        }

        .language-switcher .btn {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: #374151;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }

        .language-switcher .btn:hover {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 991.98px) {
            .language-switcher {
                position: fixed;
                top: 15px;
                right: 15px;
                z-index: 1100;
            }

            .language-switcher .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
        }
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <!-- Language Switcher -->
    <div class="language-switcher">
        <div class="dropdown">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-globe me-1"></i>
                {{ strtoupper(app()->getLocale()) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach($supportedLanguages as $lang)
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === $lang ? 'active' : '' }}" 
                           href="{{ route('language.switch', $lang) }}">
                            @switch($lang)
                                @case('en')
                                    <i class="fas fa-flag-usa me-2"></i> English
                                    @break
                                @case('ar')
                                    <i class="fas fa-flag me-2"></i> العربية
                                    @break
                                @case('ku')
                                    <i class="fas fa-flag me-2"></i> کوردی
                                    @break
                            @endswitch
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div id="app">
        @auth
            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <!-- Sidebar Header -->
                <div class="sidebar-header">
                    <div class="sidebar-brand">
                        <i class="fas fa-clinic-medical text-primary"></i>
                        <div class="brand-text">
                            <div class="brand-line-1">ConCure</div>
                            <div class="brand-line-2">Clinic Management</div>
                        </div>
                    </div>
                    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- User Info -->
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::user()->full_name }}</div>
                        <div class="user-role">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <!-- Dashboard -->
                        @if(Auth::user()->hasPermission('dashboard_view'))
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <span class="nav-text">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Patient Management -->
                        @if(Auth::user()->canAccessSection('patients'))
                        <li class="nav-item">
                            <a href="{{ route('patients.index') }}" class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <span class="nav-text">{{ __('Patients') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Prescriptions -->
                        @if(Auth::user()->canAccessSection('prescriptions'))
                        <li class="nav-item">
                            <a href="{{ route('simple-prescriptions.index') }}" class="nav-link {{ request()->routeIs('simple-prescriptions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-prescription-bottle-alt"></i>
                                <span class="nav-text">{{ __('Prescriptions') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Lab Requests -->
                        @if(Auth::user()->hasPermission('prescriptions_create'))
                        <li class="nav-item">
                            <a href="{{ route('recommendations.lab-requests') }}" class="nav-link {{ request()->routeIs('recommendations.lab-requests*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-flask"></i>
                                <span class="nav-text">{{ __('Lab Requests') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Radiology Requests -->
                        @if(Auth::user()->canViewRadiologyRequests())
                        <li class="nav-item">
                            <a href="{{ route('recommendations.radiology.index') }}" class="nav-link {{ request()->routeIs('recommendations.radiology.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-x-ray"></i>
                                <span class="nav-text">{{ __('Radiology Requests') }}</span>
                            </a>
                        </li>
                        @endif



                        <!-- Nutrition Plans -->
                        @if(Auth::user()->canAccessSection('nutrition'))
                        <li class="nav-item">
                            <a href="{{ route('nutrition.index') }}" class="nav-link {{ request()->routeIs('nutrition.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-apple-alt"></i>
                                <span class="nav-text">{{ __('Nutrition Plans') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Food Composition Database -->
                        @if(Auth::user()->canAccessSection('nutrition') || Auth::user()->hasPermission('manage-food-composition'))
                        <li class="nav-item">
                            <a href="{{ route('foods.index') }}" class="nav-link {{ request()->routeIs('foods.*') || request()->routeIs('food-groups.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <span class="nav-text">{{ __('Food Database') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Appointments -->
                        @if(Auth::user()->canAccessSection('appointments'))
                        <li class="nav-item">
                            <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <span class="nav-text">{{ __('Appointments') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Inventory -->
                        @if(Auth::user()->canAccessSection('medicines'))
                        <li class="nav-item">
                            <a href="{{ route('medicines.index') }}" class="nav-link {{ request()->routeIs('medicines.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-pills"></i>
                                <span class="nav-text">{{ __('Medicines') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Finance -->
                        @if(Auth::user()->canAccessSection('finance'))
                        <li class="nav-item">
                            <a href="{{ route('finance.index') }}" class="nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-dollar-sign"></i>
                                <span class="nav-text">{{ __('Finance') }}</span>
                            </a>
                        </li>
                        @endif

                        <!-- Administration -->
                        @if(Auth::user()->canAccessSection('users') || Auth::user()->canAccessSection('settings') || Auth::user()->role === 'admin')
                        <li class="nav-item has-submenu {{ request()->routeIs(['users.*', 'settings.*', 'external-labs.*', 'whatsapp.*', 'admin.custom-vital-signs.*', 'admin.checkup-templates.*']) ? 'active' : '' }}">
                            <a href="#" class="nav-link submenu-toggle">
                                <i class="nav-icon fas fa-cogs"></i>
                                <span class="nav-text">{{ __('Administration') }}</span>
                                <i class="submenu-arrow fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu">
                                @if(Auth::user()->canAccessSection('users'))
                                <li class="submenu-item">
                                    <a href="{{ route('users.index') }}" class="submenu-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fas fa-user-cog"></i>
                                        <span class="submenu-text">{{ __('Users') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(in_array(Auth::user()->role, ['admin', 'program_owner']))
                                <li class="submenu-item">
                                    <a href="{{ route('external-labs.index') }}" class="submenu-link {{ request()->routeIs('external-labs.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fas fa-flask"></i>
                                        <span class="submenu-text">{{ __('External Labs') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'doctor' || Auth::user()->role === 'program_owner')
                                <li class="submenu-item">
                                    <a href="{{ route('admin.custom-vital-signs.index') }}" class="submenu-link {{ request()->routeIs('admin.custom-vital-signs.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fas fa-stethoscope"></i>
                                        <span class="submenu-text">{{ __('Custom Vital Signs') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'doctor' || Auth::user()->role === 'program_owner')
                                <li class="submenu-item">
                                    <a href="{{ route('admin.checkup-templates.index') }}" class="submenu-link {{ request()->routeIs('admin.checkup-templates.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fas fa-clipboard-list"></i>
                                        <span class="submenu-text">{{ __('Checkup Templates') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->role === 'admin')
                                <li class="submenu-item">
                                    <a href="{{ route('whatsapp.index') }}" class="submenu-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fab fa-whatsapp"></i>
                                        <span class="submenu-text">{{ __('WhatsApp') }}</span>
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->canAccessSection('settings'))
                                <li class="submenu-item">
                                    <a href="{{ route('settings.index') }}" class="submenu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                                        <i class="submenu-icon fas fa-cog"></i>
                                        <span class="submenu-text">{{ __('Settings') }}</span>
                                    </a>
                                </li>
                                @endif
                                {{-- Subscription menu removed - no longer needed --}}
                            </ul>
                        </li>
                        @endif
                    </ul>
                </nav>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer">
                    <form method="POST" action="{{ route('welcome.logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>{{ __('Logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mobile Sidebar Overlay -->
            <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

            <!-- Top Bar -->
            <div class="topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle-btn d-lg-none" id="sidebarToggleBtn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="page-title">
                        @yield('page-title', 'Dashboard')
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="topbar-user">
                        <span class="user-name d-none d-md-inline">{{ Auth::user()->full_name }}</span>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} {{ $companyName ?? 'Connect Pure' }}. All rights reserved.
                    </p>
                </div>
                <div class="footer-right">
                    <p class="mb-0">
                        <i class="fas fa-clinic-medical text-primary me-1"></i>
                        {{ config('app.name') }} - Clinic Management System
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const submenuToggles = document.querySelectorAll('.submenu-toggle');

            // Mobile sidebar toggle
            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function() {
                    sidebar.classList.add('show');
                    sidebarOverlay.classList.add('show');
                    document.body.classList.add('sidebar-open');
                });
            }

            // Close sidebar
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }

            // Overlay click to close
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }

            // Submenu toggles
            submenuToggles.forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const parent = this.parentElement;
                    const submenu = parent.querySelector('.submenu');

                    if (parent.classList.contains('open')) {
                        parent.classList.remove('open');
                        submenu.style.maxHeight = '0';
                    } else {
                        // Close other open submenus
                        document.querySelectorAll('.nav-item.has-submenu.open').forEach(function(item) {
                            if (item !== parent) {
                                item.classList.remove('open');
                                item.querySelector('.submenu').style.maxHeight = '0';
                            }
                        });

                        parent.classList.add('open');
                        submenu.style.maxHeight = submenu.scrollHeight + 'px';
                    }
                });
            });

            // Auto-open active submenu
            const activeSubmenu = document.querySelector('.nav-item.has-submenu.active');
            if (activeSubmenu) {
                activeSubmenu.classList.add('open');
                const submenu = activeSubmenu.querySelector('.submenu');
                if (submenu) {
                    submenu.style.maxHeight = submenu.scrollHeight + 'px';
                }
            }

            // Close sidebar on window resize for desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 992) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
