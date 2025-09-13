<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ConCure Master')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fc;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .topbar {
            background: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .border-left-primary {
            border-left: 0.25rem solid var(--primary-color) !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .icon-circle {
            height: 2.5rem;
            width: 2.5rem;
            border-radius: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        .font-weight-bold {
            font-weight: 700 !important;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-500 {
            color: #858796 !important;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-crown fa-2x mb-2" style="color: white;"></i>
            <h4>ConCure Master</h4>
            <small style="color: rgba(255, 255, 255, 0.7);">SaaS Management</small>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.dashboard') ? 'active' : '' }}" 
                   href="{{ route('master.dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.clinics.*') ? 'active' : '' }}" 
                   href="{{ route('master.clinics.index') }}">
                    <i class="fas fa-hospital me-2"></i>
                    Clinics
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.users.*') ? 'active' : '' }}" 
                   href="{{ route('master.users.index') }}">
                    <i class="fas fa-users me-2"></i>
                    Users
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.subscriptions.*') ? 'active' : '' }}" 
                   href="{{ route('master.subscriptions.index') }}">
                    <i class="fas fa-credit-card me-2"></i>
                    Subscriptions
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.reports') ? 'active' : '' }}" 
                   href="{{ route('master.reports') }}">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reports
                </a>
            </div>
            
            <div class="nav-item">
                <a class="nav-link {{ request()->routeIs('master.settings') ? 'active' : '' }}" 
                   href="{{ route('master.settings') }}">
                    <i class="fas fa-cog me-2"></i>
                    Settings
                </a>
            </div>
            
            <hr style="border-color: rgba(255, 255, 255, 0.1); margin: 1rem;">
            
            <div class="nav-item">
                <a class="nav-link" href="{{ url('/') }}" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>
                    View Main Site
                </a>
            </div>
            
            <div class="nav-item">
                <form method="POST" action="{{ route('master.logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Welcome back, {{ auth()->user()->full_name }}</h5>
                <small class="text-muted">Super Administrator</small>
            </div>
            <div>
                <span class="badge bg-primary me-2">
                    <i class="fas fa-shield-alt me-1"></i>
                    Master Access
                </span>
                <span class="text-muted">{{ now()->format('M d, Y H:i') }}</span>
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="container-fluid px-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
