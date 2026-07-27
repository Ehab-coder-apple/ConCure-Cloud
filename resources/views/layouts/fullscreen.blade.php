<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ConCure') }} - @yield('page-title', 'Manage Tests')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
        }
        
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .main-header h1 {
            margin: 0;
            font-weight: 600;
        }
        
        .main-header .subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .btn {
            border-radius: 6px;
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .badge {
            font-size: 0.75rem;
        }
        
        .toast-container {
            z-index: 9999;
        }
    </style>

    @stack('styles')
</head>

<body>
    <!-- Main Header -->
    <div class="main-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-cogs me-2"></i>
                        @yield('page-title', 'Manage Radiology Tests')
                    </h1>
                    <div class="subtitle">
                        @yield('page-subtitle', 'Create custom tests and manage your radiology test database')
                    </div>
                </div>
                <div>
                    <a href="{{ $backUrl ?? route('recommendations.radiology.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ $backText ?? __('Back to Radiology') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (if needed) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>
</html>
