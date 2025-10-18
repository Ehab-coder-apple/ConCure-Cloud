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

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;           /* prevent horizontal scroll */
            scrollbar-gutter: stable;      /* avoid layout shift from scrollbar */
        }
        *, *::before, *::after { box-sizing: border-box; }
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
            --sidebar-width: 250px; /* fixed sidebar width */
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
            overflow-x: hidden;            /* redundant safeguard */
        }
        .main-content, .content-wrapper {
            max-width: 100%;
            overflow-x: hidden;            /* ensure children cannot overflow horizontally */
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
            /* Keep content offset from the fixed sidebar */
            margin-inline-start: var(--sidebar-width);
            margin-left: var(--sidebar-width); /* LTR fallback to avoid underlay in some browsers */
            margin-top: calc(var(--topbar-height) + 8px); /* add breathing room below fixed topbar */
            min-height: calc(100vh - var(--topbar-height) - 8px);
            transition: margin-inline-start 0.3s ease;
            position: relative;
            z-index: 1;
            box-sizing: border-box;
            padding: 1rem 1.25rem; /* consistent interior spacing */
            max-width: 100%;
        }

        .content-wrapper {
            padding: 0; /* padding handled by .main-content */
            max-width: none; /* fill available width */
            margin: 0; /* no centering wrapper */
            width: 100%;
            box-sizing: border-box;
        }

        /* Force proper spacing for main content */
        @media (min-width: 992px) {
            .main-content,
            /* Increase specificity to beat any page-level overrides */
            #app > .main-content,
            body > #app > .main-content {
                margin-inline-start: var(--sidebar-width) !important;
                margin-left: var(--sidebar-width) !important; /* explicit LTR fallback */
                width: calc(100% - var(--sidebar-width)) !important; /* fill beside sidebar */
                padding-left: 40px !important; padding-right: 30px !important; box-sizing: border-box;
                max-width: none !important;
            }
            /* Hard fallback in case a legacy build renders content directly as .content-wrapper */
            body:not(:has(.main-content)) #app > .content-wrapper {
                margin-left: var(--sidebar-width) !important;
                width: calc(100% - var(--sidebar-width)) !important;
            }
            .main-footer {
                margin-inline-start: var(--sidebar-width) !important;
                margin-left: var(--sidebar-width) !important; /* explicit LTR fallback */
                width: calc(100% - var(--sidebar-width)) !important;
            }
            .topbar {
                left: var(--sidebar-width) !important;
            }
            [dir='rtl'] .topbar {
                right: var(--sidebar-width) !important; left: auto !important;
            }
            [dir='rtl'] .main-content,
            [dir='rtl'] body > #app > .main-content {
                margin-right: var(--sidebar-width) !important; margin-left: 0 !important;
                width: calc(100% - var(--sidebar-width)) !important;
                padding-left: 40px !important; padding-right: 30px !important;
            }
            [dir='rtl'] .main-footer { margin-right: var(--sidebar-width) !important; }
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
                width: 100%;
                padding-left: 24px; padding-right: 24px; box-sizing: border-box;
            }

            .main-footer {
                margin-left: 0;
            }

            /* When sidebar is open on small screens, push content/topbar/footer so they are not under it */
            body.sidebar-open .main-content {
                margin-left: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
            }
            [dir='rtl'] body.sidebar-open .main-content {
                margin-left: 0;
                margin-right: var(--sidebar-width);
                width: calc(100% - var(--sidebar-width));
            }
            body.sidebar-open .topbar {
                left: var(--sidebar-width);
            }
            [dir='rtl'] body.sidebar-open .topbar {
                left: auto;
                right: var(--sidebar-width);
            }
            body.sidebar-open .main-footer {
                margin-left: var(--sidebar-width);
            }
            [dir='rtl'] body.sidebar-open .main-footer {
                margin-left: 0;
                margin-right: var(--sidebar-width);
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

        /* Professional Nutrition Page Layout - Fixed Overlay Issue */
        .container[style*="margin-top: 80px"] {
            /* Nutrition pages container: keep only safe horizontal padding; no vertical shift */
            padding-left: 30px !important;
            padding-right: 30px !important;
            position: relative !important;
            z-index: 1 !important;
            background: white !important;
        }

        /* Target nutrition page body content */
        body:has(.container[style*="margin-top: 80px"]) .container {
            /* Use global centering via .content-wrapper; keep only visual/background */
            position: relative !important;
            z-index: 1 !important;
            background: white !important;
        }

        /* Alternative targeting for nutrition pages */
        .container:has(.fas.fa-apple-alt) {
            /* Use global centering via .content-wrapper; keep only visual/background */
            position: relative !important;
            z-index: 1 !important;
            background: white !important;
        }

        /* Nutrition show page: center the main container within content area */
        .page-nutrition .container[style*="margin-top: 80px"] {
            margin-left: auto !important;
            margin-right: auto !important;
        }


        /* Fix any overlay issues */
        .container[style*="margin-top: 80px"] *,
        .container:has(.fas.fa-apple-alt) * {
            position: relative !important;
            z-index: auto !important;
        }

        /* Ensure clickable elements are interactive - Fixed for modals */
        .container[style*="margin-top: 80px"] button,
        .container[style*="margin-top: 80px"] .btn,
        .container[style*="margin-top: 80px"] a,
        .container[style*="margin-top: 80px"] input,
        .container[style*="margin-top: 80px"] select,
        .container:has(.fas.fa-apple-alt) button,
        .container:has(.fas.fa-apple-alt) .btn,
        .container:has(.fas.fa-apple-alt) a,
        .container:has(.fas.fa-apple-alt) input,
        .container:has(.fas.fa-apple-alt) select {
            pointer-events: auto !important;
            z-index: 10 !important;
            position: relative !important;
        }

        /* Ensure modal buttons work properly */
        .modal button,
        .modal .btn,
        .modal a,
        .modal input,
        .modal select {
            pointer-events: auto !important;
            z-index: auto !important;
            position: relative !important;
        }

        /* Specific fix for food selection modal */
        #foodSelectionModal button,
        #foodSelectionModal .btn,
        #foodSelectionModal .food-card {
            pointer-events: auto !important;
            cursor: pointer !important;
            z-index: auto !important;
        }

        /* Professional Card Spacing for Nutrition Pages */
        .container[style*="margin-top: 80px"] .card,
        .container:has(.fas.fa-apple-alt) .card {
            margin-bottom: 25px !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid rgba(0, 0, 0, 0.05) !important;
        }

        /* Professional Card Headers */
        .container[style*="margin-top: 80px"] .card-header,
        .container:has(.fas.fa-apple-alt) .card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
            padding: 20px 25px !important;
            border-radius: 12px 12px 0 0 !important;
        }

        /* Professional Card Bodies */
        .container[style*="margin-top: 80px"] .card-body,
        .container:has(.fas.fa-apple-alt) .card-body {
            padding: 25px !important;
        }

        /* Professional Row Spacing */
        .container[style*="margin-top: 80px"] .row,
        .container:has(.fas.fa-apple-alt) .row {
            margin-left: -15px !important;
            margin-right: -15px !important;
            margin-bottom: 20px !important;
        }

        .container[style*="margin-top: 80px"] .row > [class*="col"],
        .container:has(.fas.fa-apple-alt) .row > [class*="col"] {
            padding-left: 15px !important;
            padding-right: 15px !important;
            margin-bottom: 15px !important;
        }

        /* Professional Button Styling and Alignment */
        .container[style*="margin-top: 80px"] .btn,
        .container:has(.fas.fa-apple-alt) .btn {
            margin-right: 8px !important;
            margin-bottom: 8px !important;
            border-radius: 6px !important;
            padding: 8px 16px !important;
            font-weight: 500 !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-height: 38px !important;
            white-space: nowrap !important;
        }

        /* Button group containers */
        .container[style*="margin-top: 80px"] .btn-group,
        .container[style*="margin-top: 80px"] .d-flex,
        .container:has(.fas.fa-apple-alt) .btn-group,
        .container:has(.fas.fa-apple-alt) .d-flex {
            gap: 8px !important;
            flex-wrap: wrap !important;
            align-items: center !important;
        }

        /* Specific button adjustments for nutrition pages */
        .container[style*="margin-top: 80px"] .btn-sm,
        .container:has(.fas.fa-apple-alt) .btn-sm {
            padding: 6px 12px !important;
            font-size: 13px !important;
            min-height: 32px !important;
        }

        .container[style*="margin-top: 80px"] .btn-lg,
        .container:has(.fas.fa-apple-alt) .btn-lg {
            padding: 12px 24px !important;
            font-size: 16px !important;
            min-height: 44px !important;
        }

        /* Button icons spacing */
        .container[style*="margin-top: 80px"] .btn i,
        .container:has(.fas.fa-apple-alt) .btn i {
            margin-right: 6px !important;
        }

        .container[style*="margin-top: 80px"] .btn i:last-child,
        .container:has(.fas.fa-apple-alt) .btn i:last-child {
            margin-right: 0 !important;
            margin-left: 6px !important;
        }

        /* Action button row styling */
        .container[style*="margin-top: 80px"] .row .col-auto,
        .container:has(.fas.fa-apple-alt) .row .col-auto {
            padding-left: 4px !important;
            padding-right: 4px !important;
        }

        /* Top action buttons container */
        .container[style*="margin-top: 80px"] .d-flex.justify-content-between,
        .container[style*="margin-top: 80px"] .d-flex.justify-content-end,
        .container:has(.fas.fa-apple-alt) .d-flex.justify-content-between,
        .container:has(.fas.fa-apple-alt) .d-flex.justify-content-end {
            margin-bottom: 20px !important;
            padding: 15px 0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        /* Enhanced button color schemes */
        .container[style*="margin-top: 80px"] .btn-primary,
        .container:has(.fas.fa-apple-alt) .btn-primary {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 1px 3px rgba(59, 130, 246, 0.2) !important;
        }

        .container[style*="margin-top: 80px"] .btn-primary:hover,
        .container:has(.fas.fa-apple-alt) .btn-primary:hover {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
        }

        .container[style*="margin-top: 80px"] .btn-success,
        .container:has(.fas.fa-apple-alt) .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            box-shadow: 0 1px 3px rgba(16, 185, 129, 0.2) !important;
        }

        .container[style*="margin-top: 80px"] .btn-success:hover,
        .container:has(.fas.fa-apple-alt) .btn-success:hover {
            background-color: #059669 !important;
            border-color: #059669 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3) !important;
        }

        .container[style*="margin-top: 80px"] .btn-warning,
        .container:has(.fas.fa-apple-alt) .btn-warning {
            background-color: #f59e0b !important;
            border-color: #f59e0b !important;
            color: white !important;
            box-shadow: 0 1px 3px rgba(245, 158, 11, 0.2) !important;
        }

        .container[style*="margin-top: 80px"] .btn-warning:hover,
        .container:has(.fas.fa-apple-alt) .btn-warning:hover {
            background-color: #d97706 !important;
            border-color: #d97706 !important;
            color: white !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(245, 158, 11, 0.3) !important;
        }

        .container[style*="margin-top: 80px"] .btn-info,
        .container:has(.fas.fa-apple-alt) .btn-info {
            background-color: #06b6d4 !important;
            border-color: #06b6d4 !important;
            box-shadow: 0 1px 3px rgba(6, 182, 212, 0.2) !important;
        }

        .container[style*="margin-top: 80px"] .btn-info:hover,
        .container:has(.fas.fa-apple-alt) .btn-info:hover {
            background-color: #0891b2 !important;
            border-color: #0891b2 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(6, 182, 212, 0.3) !important;
        }

        .container[style*="margin-top: 80px"] .btn-outline-secondary,
        .container:has(.fas.fa-apple-alt) .btn-outline-secondary {
            border-color: #6b7280 !important;
            color: #6b7280 !important;
            background-color: white !important;
            box-shadow: 0 1px 3px rgba(107, 114, 128, 0.1) !important;
        }

        .container[style*="margin-top: 80px"] .btn-outline-secondary:hover,
        .container:has(.fas.fa-apple-alt) .btn-outline-secondary:hover {
            background-color: #6b7280 !important;
            border-color: #6b7280 !important;
            color: white !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(107, 114, 128, 0.2) !important;
        }

        /* Professional Page Header */
        .container[style*="margin-top: 80px"] h1,
        .container[style*="margin-top: 80px"] h2,
        .container:has(.fas.fa-apple-alt) h1,
        .container:has(.fas.fa-apple-alt) h2 {
            margin-bottom: 25px !important;
            padding-bottom: 15px !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }

        /* Professional Form Spacing */
        .container[style*="margin-top: 80px"] .form-group,
        .container[style*="margin-top: 80px"] .mb-3,
        .container:has(.fas.fa-apple-alt) .form-group,
        .container:has(.fas.fa-apple-alt) .mb-3 {
            margin-bottom: 20px !important;
        }

        /* Professional Table Spacing */
        .container[style*="margin-top: 80px"] .table,
        .container:has(.fas.fa-apple-alt) .table {
            margin-bottom: 25px !important;
        }

        /* Remove any problematic overlays or backdrops */
        .modal-backdrop,
        .overlay,
        .backdrop {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
        }

        /* Ensure body doesn't have modal-open class stuck */
        body.modal-open {
            overflow: auto !important;
            padding-right: 0 !important;
        }

        /* Fix any stuck modal states - but allow new modals to show */
        .modal.show.stuck {
            display: none !important;
        }

        /* Ensure modals can show properly */
        .modal {
            z-index: 1055 !important;
        }

        .modal-backdrop {
            z-index: 1050 !important;
        }


            /* Ensure Select2 dropdown appears above fixed topbar and not clipped */
            .select2-container { z-index: 1200 !important; }
            .select2-container .select2-dropdown { z-index: 1201 !important; }
            /* Make Select2 fill width in the New Appointment modal */
            #newAppointmentModal .select2-container { width: 100% !important; }
            /* Hide Select2 search box inside New Appointment modal (Patient/Doctor) */
            #newAppointmentModal .select2-search--dropdown,
            #newAppointmentModal .select2-search { display: none !important; }
            /* Utility for explicitly hidden search via dropdownCssClass */
            .select2-dropdown.no-search .select2-search--dropdown { display: none !important; }
            /* Ensure the original selects transformed by Select2 are fully hidden */
            select.select2-hidden-accessible { display: none !important; position: absolute !important; left: -9999px !important; height: 0 !important; width: 0 !important; }

            /* Hard-hide the native selects for Patient/Doctor inside the New Appointment modal */
            #newAppointmentModal select#patient_id,
            #newAppointmentModal select#doctor_id {
                position: absolute !important;
                left: -10000px !important;
                width: 1px !important;
                height: 1px !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            /* Ensure Select2 dropdown in New Appointment modal floats and doesn't take layout space */
            #newAppointmentModal .select2-container { position: relative !important; }
            #newAppointmentModal .select2-container .select2-dropdown { position: absolute !important; }


        /* Ensure modal content is clickable */
        .modal-content {
            position: relative !important;
            z-index: 1056 !important;
        }

        /* Ensure nutrition page content is fully interactive */
        .container[style*="margin-top: 80px"],
        .container:has(.fas.fa-apple-alt) {
            pointer-events: auto !important;
            user-select: auto !important;
        }

        /* Mobile responsive with professional spacing */
        @media (max-width: 768px) {
            .container[style*="margin-top: 80px"],
            .container:has(.fas.fa-apple-alt),
            body:has(.container[style*="margin-top: 80px"]) .container {
                margin-left: 20px !important;
                margin-right: 20px !important;
                max-width: calc(100vw - 40px) !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
                padding-top: 20px !important;
                padding-bottom: 20px !important;
            }

            .container[style*="margin-top: 80px"] .card,
            .container:has(.fas.fa-apple-alt) .card {
                margin-bottom: 20px !important;
            }

            .container[style*="margin-top: 80px"] .card-body,
            .container:has(.fas.fa-apple-alt) .card-body {
                padding: 20px !important;
            }

            /* Mobile button adjustments */
            .container[style*="margin-top: 80px"] .btn,
            .container:has(.fas.fa-apple-alt) .btn {
                margin-right: 6px !important;
                margin-bottom: 6px !important;
                padding: 8px 12px !important;
                font-size: 13px !important;
                min-height: 36px !important;
            }

            .container[style*="margin-top: 80px"] .d-flex,
            .container:has(.fas.fa-apple-alt) .d-flex {
                flex-wrap: wrap !important;
                gap: 6px !important;
            }

            /* Stack buttons vertically on very small screens */
            @media (max-width: 480px) {
                .container[style*="margin-top: 80px"] .btn,
                .container:has(.fas.fa-apple-alt) .btn {
                    width: 100% !important;
                    margin-right: 0 !important;
                    margin-bottom: 8px !important;
                    justify-content: center !important;
                }

                .container[style*="margin-top: 80px"] .d-flex.justify-content-between,
                .container[style*="margin-top: 80px"] .d-flex.justify-content-end,
                .container:has(.fas.fa-apple-alt) .d-flex.justify-content-between,
                .container:has(.fas.fa-apple-alt) .d-flex.justify-content-end {
                    flex-direction: column !important;


                    align-items: stretch !important;
                }
            }
        }

        /* Nutrition pages: ensure offset so content never sits under sidebar */
        body:has(.container:has(.fas.fa-apple-alt)) .main-content {
            margin-inline-start: var(--sidebar-width) !important;
            margin-left: var(--sidebar-width) !important; /* LTR fallback */
        }
        [dir='rtl'] body:has(.container:has(.fas.fa-apple-alt)) .main-content {
            margin-right: var(--sidebar-width) !important;
        }


            /* Nutrition pages: ensure extra clearance under fixed topbar */
            body.page-nutrition .main-content {
                margin-top: calc(var(--topbar-height) + 10px) !important;
                padding-top: 12px !important;
            }

            /* Nutrition: enforce correct sidebar offset; plus fallback if wrapper is missing */
            @media (min-width: 992px) {
                body.page-nutrition .main-content {
                    margin-left: var(--sidebar-width) !important;
                    width: calc(100% - var(--sidebar-width)) !important;
                }
                /* Fallback: if for any reason .main-content is not in the DOM, push #nutrition-show itself */
                body.page-nutrition:not(:has(.main-content)) #nutrition-show.container {
                    margin-left: var(--sidebar-width) !important;
                    width: calc(100% - var(--sidebar-width)) !important;
                }
            }
            @media (max-width: 991.98px) {
                /* Keep mobile behavior consistent when the sidebar is open */
                body.page-nutrition.sidebar-open .main-content {
                    margin-left: var(--sidebar-width) !important;
                    width: calc(100% - var(--sidebar-width)) !important;
                }
                /* Fallback for mobile if wrapper is missing and sidebar is opened */
                body.page-nutrition.sidebar-open:not(:has(.main-content)) #nutrition-show.container {
                    margin-left: var(--sidebar-width) !important;
                    width: calc(100% - var(--sidebar-width)) !important;
                }
            }

            /* Nutrition: contain internal layout to avoid any horizontal overflow or overlap */
            .page-nutrition .main-content,
            .page-nutrition #nutrition-show { overflow-x: hidden; }

            /* Ensure the nutrition container never exceeds available space beside the sidebar */
            .page-nutrition #nutrition-show.container { max-width: 100% !important; width: 100% !important; }

            /* Prevent flex/grid columns from forcing overflow; keep cards within bounds */
            .page-nutrition #nutrition-show .row > [class^="col-"],
            .page-nutrition #nutrition-show .row > [class*=" col-"],
            .page-nutrition #nutrition-show [class^="col-"],
            .page-nutrition #nutrition-show [class*=" col-"] { min-width: 0 !important; max-width: 100% !important; }
            .page-nutrition #nutrition-show .card { max-width: 100% !important; }

            /* Button groups should wrap instead of pushing horizontally */
            .page-nutrition #nutrition-show .btn-group,
            .page-nutrition #nutrition-show .d-flex { flex-wrap: wrap !important; }



        /* Dashboard Statistics Cards Styling */
        .card.bg-primary, .card.bg-secondary, .card.bg-success, .card.bg-info, .card.bg-warning, .card.bg-danger {
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        }

        .card.bg-primary:hover, .card.bg-secondary:hover, .card.bg-success:hover,
        .card.bg-info:hover, .card.bg-warning:hover, .card.bg-danger:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
        }

        /* Adjust statistical icon sizes */
        .card .fa-2x {
            font-size: 1.5em !important; /* Reduced from default 2em */
        }

        /* Specific adjustments for revenue card dollar sign */
        .card.bg-secondary .fa-dollar-sign.fa-2x {
            font-size: 1.3em !important;
        }

        /* Statistical card typography improvements */
        .card .card-title {
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            opacity: 0.9 !important;
        }

        .card h2 {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            margin-bottom: 0.25rem !important;
        }

        .card small {
            font-size: 0.8rem !important;
            opacity: 0.8 !important;
        }

        /* Icon container improvements */
        .card .align-self-center {
            opacity: 0.7 !important;
        }

        /* Responsive adjustments for statistics cards */
        @media (max-width: 768px) {
            .card .fa-2x {
                font-size: 1.3em !important;
            }

            .card.bg-secondary .fa-dollar-sign.fa-2x {
                font-size: 1.1em !important;
            }

            .card h2 {
                font-size: 1.6rem !important;
            }

            .card .card-title {
                font-size: 0.85rem !important;
            }
        }

        @media (max-width: 576px) {
            .card .fa-2x {
                font-size: 1.2em !important;
            }

            .card.bg-secondary .fa-dollar-sign.fa-2x {
                font-size: 1.0em !important;
            }

            .card h2 {
                font-size: 1.4rem !important;
            }
        }
        /* Unified stat-cards (Total Requests, Pending, Completed, Urgent) */
        .stat-cards .card { border: 0; border-radius: 12px; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.12); }
        .stat-cards .card-body{ padding: 12px 14px; }
        .stat-cards .card .card-title{ margin-bottom: 4px; font-weight: 700; }
        .stat-cards .card h2{ font-weight: 800; }
        .stat-cards .card .align-self-center{ opacity: .85; }
        .stat-cards .card.bg-warning{ color: #fff; }
        .stat-cards .card.bg-secondary{ color: #fff; }
        /* Remove any inner separator lines/pseudo elements */
        .stat-cards .card-body hr{ display:none !important; }
        .stat-cards .card-body, .stat-cards .card-body > *, .stat-cards h2, .stat-cards h6{ border:0 !important; }
        .stat-cards .card-body::before, .stat-cards .card-body::after, .stat-cards h2::before, .stat-cards h2::after{ display:none !important; content:none !important; }
        @media (max-width: 576px){ .stat-cards .card { border-radius: 10px; } }
        /* Extra-strong removal of any decorative lines inside stat cards */
        .stat-cards .card *,
        .stat-cards .card::before,
        .stat-cards .card::after{ background-image:none !important; text-decoration:none !important; }
        .stat-cards .card *{ border-bottom:0 !important; }
        .stat-cards .card .divider, .stat-cards .card .separator{ display:none !important; }


        /* Shared compact embossed stat cards (used on Dashboard and Nutrition index) */
        .dashboard-stats .card { position: relative; overflow: hidden; width: 90%; margin-inline: auto; border-radius: 14px; border:1px solid rgba(255,255,255,.28); background-image: linear-gradient(145deg, rgba(255,255,255,.20), rgba(0,0,0,.03)); box-shadow: 0 18px 40px rgba(0,0,0,.22), 0 8px 18px rgba(0,0,0,.14), inset 0 4px 12px rgba(255,255,255,.32), inset 0 -4px 12px rgba(0,0,0,.18); }
        .dashboard-stats .card-body hr{display:none!important;}
        .dashboard-stats .card-body, .dashboard-stats .card-body>*, .dashboard-stats h2, .dashboard-stats h6{border:0!important;}
        .dashboard-stats .card-body::before, .dashboard-stats .card-body::after, .dashboard-stats h2::before, .dashboard-stats h2::after{display:none!important; content:none!important;}
        .dashboard-stats .card::before, .dashboard-stats .card::after{content:''; position:absolute; inset:0; border-radius:14px; pointer-events:none;}
        .dashboard-stats .card::before{background: radial-gradient(120% 60% at 0% 0%, rgba(255,255,255,.48), rgba(255,255,255,0) 60%); filter: blur(8px); opacity:.55;}
        .dashboard-stats .card::after{background: radial-gradient(120% 60% at 100% 100%, rgba(0,0,0,.20), rgba(0,0,0,0) 60%); filter: blur(10px); opacity:.45;}
        .dashboard-stats .card-body{padding: 0 8px 2px;}
        .dashboard-stats h2{font-size:1.4rem; margin:0;}
        .dashboard-stats .card-title{font-size:1.18rem; font-weight:800; letter-spacing:.2px; margin-bottom:1px;}
        .dashboard-stats small{font-size:0.68rem;}
        .dashboard-stats .fa-2x{font-size:0.95rem!important;}


        /* Hard kill any decorative lines inside stat-cards to match Finance style */
        .stat-cards .card { position: relative; overflow: hidden; }
        .stat-cards hr,
        .stat-cards .card hr { display: none !important; height: 0 !important; }
        .stat-cards .border-bottom,
        .stat-cards .card .border-bottom { border-bottom: 0 !important; }
        .stat-cards .card::before,
        .stat-cards .card::after,
        .stat-cards .card-body::before,
        .stat-cards .card-body::after,
        .stat-cards .card-body > *::before,
        .stat-cards .card-body > *::after { content: none !important; display: none !important; }
        .stat-cards .card *,
        .stat-cards .card-body *,
        .stat-cards .card h2,
        .stat-cards .card small { text-decoration: none !important; background-image: none !important; }
        /* Finance-style cards: ensure absolutely no inner lines/guides inside stat cards */
        .stat-cards hr,
        .stat-cards .card hr { display:none !important; }
        .stat-cards .card *,
        .stat-cards .card h1,
        .stat-cards .card h2,
        .stat-cards .card h3,
        .stat-cards .card h4,
        .stat-cards .card h5,
        .stat-cards .card h6,
        .stat-cards .card small { border:0 !important; background-image:none !important; text-decoration:none !important; box-shadow:none !important; }
        .stat-cards .card *::before,
        .stat-cards .card *::after { content:none !important; display:none !important; border:0 !important; background:none !important; box-shadow:none !important; }
        /* Cancel page-level header rule on inner flex rows inside stat cards */
        .stat-cards .d-flex.justify-content-between { border-bottom: 0 !important; padding: 0 !important; margin-bottom: 0 !important; }
        .container:has(.fas.fa-apple-alt) .stat-cards .d-flex.justify-content-between { border-bottom: 0 !important; padding: 0 !important; margin-bottom: 0 !important; }
        .container[style*="margin-top: 80px"] .stat-cards .d-flex.justify-content-between { border-bottom: 0 !important; padding: 0 !important; margin-bottom: 0 !important; }


        /* Override page-level header underline on stat cards (Dashboard/Nutrition) */
        .stat-cards h1,
        .stat-cards h2,
        .stat-cards .card h1,
        .stat-cards .card h2 { border-bottom: 0 !important; padding-bottom: 0 !important; margin-bottom: .25rem !important; }


    </style>
    @if(request()->boolean('layout-debug'))
    <style>
      html, body, .main-content, .content-wrapper { overflow-x: visible !important; scrollbar-gutter: auto !important; }
      .layout-debug-badge { position: fixed; bottom: 12px; inset-inline-end: 12px; z-index: 2000; background: #f59e0b; color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,.18); }
    </style>
    @endif


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    @stack('styles')
</head>
@php
    $bodyClasses = [];
    if (request()->routeIs('dashboard')) $bodyClasses[] = 'page-dashboard';
    if (request()->routeIs('messages.*')) $bodyClasses[] = 'page-messages';
    if (request()->routeIs('nutrition.*')) $bodyClasses[] = 'page-nutrition';
    if (request()->routeIs('foods.*') || request()->routeIs('food-groups.*')) $bodyClasses[] = 'page-foods';
    $bodyClassAttr = implode(' ', $bodyClasses);
@endphp
<body class="{{ $bodyClassAttr }}">
    <!-- Language Switcher -->
    <div class="language-switcher">
        <div class="dropdown">
            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-globe me-1"></i>
                {{ strtoupper(app()->getLocale()) }}
            </button>
            @php
                // Build a safe list of language codes, regardless of how it's provided
                $langs = [];
                if (isset($supportedLanguages) && is_array($supportedLanguages)) {
                    $langs = array_keys($supportedLanguages) === range(0, count($supportedLanguages) - 1)
                        ? $supportedLanguages // already a flat list of codes
                        : array_keys($supportedLanguages); // associative: take keys as codes
                } else {
                    $langs = array_keys(config('concure.supported_languages', ['en' => 'English', 'ar' => 'العربية', 'ku' => 'کوردی']));
                }
            @endphp
            <ul class="dropdown-menu dropdown-menu-end">
                @foreach($langs as $lang)
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
                                @default
                                    {{ strtoupper($lang) }}
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

                            <!-- Messages -->
                            <li class="nav-item">
                                <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-inbox"></i>
                                    <span class="nav-text">{{ __('Messages') }}</span>
                                    <span class="badge bg-danger ms-auto" id="sidebarUnread">0</span>
                                </a>
                            </li>


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
                                <span class="badge bg-danger ms-auto" id="sidebarAppointmentsPending" style="display: none;">0</span>
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
                    <form method="POST" action="{{ route('logout') }}">
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
                        @hasSection('page-title')
                            @yield('page-title')
                        @endif
                    </div>
                </div>
                <div class="topbar-right">
                    <div class="dropdown me-3" id="appointmentsBellWrap">
                        <a href="#" class="text-secondary" id="appointmentsBell" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger" id="appointmentsBellBadge" style="display:none;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="appointmentsBell" style="min-width: 320px;">
                            <div class="p-2 border-bottom">
                                <strong>Upcoming Appointments</strong>
                                <small class="text-muted d-block">Auto-updates</small>
                            </div>
                            <div class="p-2" id="appointmentsBellContent">
                                <div class="text-center text-muted small py-3">No upcoming appointments</div>
                            </div>
                        </div>
                    </div>

                    <div class="topbar-user">
                        <span class="user-name d-none d-md-inline">{{ Auth::user()->full_name }}</span>
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    </div>
                        @php($__layoutDebug = request()->boolean('layout-debug'))
                        @if(config('app.debug') || $__layoutDebug)
                            <button id="layoutDebugToggle" class="btn btn-sm {{ $__layoutDebug ? 'btn-warning' : 'btn-outline-secondary' }} ms-3" title="Toggle layout debug">
                                <i class="fas fa-ruler-combined me-1"></i>{{ $__layoutDebug ? 'Debug ON' : 'Debug OFF' }}
                            </button>
                        @endif

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

    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Sidebar JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fix any stuck modal/overlay states
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
            document.body.style.paddingRight = '0';

            // Remove any stuck modal backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop, .overlay, .backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Hide any stuck modals
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                modal.classList.remove('show');
                modal.style.display = 'none';
            });

            // Ensure all Bootstrap modals sit above the fixed topbar by moving them under <body>
            try {
                document.querySelectorAll('.modal').forEach(function(modalEl){
                    if (modalEl.parentElement && modalEl.parentElement.tagName !== 'BODY') {
                        document.body.appendChild(modalEl);
                    }
                });
            } catch (e) { /* no-op */ }


            // Debug: Add click event debugging for Add Food buttons
            setTimeout(() => {
                const addFoodButtons = document.querySelectorAll('.add-food-to-option-btn, #add-food-to-meal');
                console.log('Found Add Food buttons:', addFoodButtons.length);

                addFoodButtons.forEach((btn, index) => {
                    console.log(`Button ${index}:`, btn.id || btn.className, 'Disabled:', btn.disabled);

                    // Add debug click listener
                    btn.addEventListener('click', function(e) {
                        console.log('Add Food button clicked:', this.id || this.className);
                        console.log('Event:', e);
                        console.log('Button disabled:', this.disabled);
                        console.log('Pointer events:', window.getComputedStyle(this).pointerEvents);
                    }, true); // Use capture phase to catch early
                });
            }, 1000);

            // Debug: Check for JavaScript errors
            window.addEventListener('error', function(e) {
                console.error('JavaScript Error:', e.error, 'at', e.filename + ':' + e.lineno);
            });

            // Debug: Check Bootstrap availability
            setTimeout(() => {
                console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
                console.log('jQuery available:', typeof $ !== 'undefined');

                // Check if food selection modal exists
                const foodModal = document.getElementById('foodSelectionModal');
                console.log('Food selection modal found:', !!foodModal);

                if (foodModal) {
                    console.log('Modal classes:', foodModal.className);
                    console.log('Modal display:', window.getComputedStyle(foodModal).display);
                }
            }, 500);

            const sidebar = document.getElementById('sidebar');
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const submenuToggles = document.querySelectorAll('.submenu-toggle');


            // Global layout offset guard: FORCE the sidebar offset on desktop; remove heuristics
            (function(){
                function getSidebarWidth(){
                    try {
                        var sb = document.querySelector('.sidebar');
                        var w = sb && sb.offsetWidth ? sb.offsetWidth : 250;
                        return (w && w > 0) ? Math.round(w) : 250;
                    } catch(_) { return 250; }
                }
                function setInline(enable){
                    var w = getSidebarWidth();
                    var mc = document.querySelector('.main-content');
                    var cw = document.querySelector('.content-wrapper');
                    var topbar = document.querySelector('.topbar');
                    var footer = document.querySelector('.main-footer');
                    if(enable){
                        if(mc){ mc.style.marginLeft = w + 'px'; mc.style.width = 'calc(100% - ' + w + 'px)'; }
                        else if(cw){ cw.style.marginLeft = w + 'px'; cw.style.width = 'calc(100% - ' + w + 'px)'; }
                        if(topbar){ topbar.style.left = w + 'px'; }
                        if(footer){ footer.style.marginLeft = w + 'px'; footer.style.width = 'calc(100% - ' + w + 'px)'; }
                        document.body.classList.add('layout-force-offset');
                    } else {
                        if(mc){ mc.style.marginLeft = ''; mc.style.width = ''; }
                        if(cw){ cw.style.marginLeft = ''; cw.style.width = ''; }
                        if(topbar){ topbar.style.left = ''; }
                        if(footer){ footer.style.marginLeft = ''; footer.style.width = ''; }
                        document.body.classList.remove('layout-force-offset');
                    }
                }
                function applyGuard(){
                    try {
                        var enable = window.innerWidth >= 992; // desktop and above
                        setInline(enable);
                    } catch(_) {}
                }
                function debounce(fn, ms){ let t; return function(){ clearTimeout(t); t = setTimeout(fn, ms); }; }
                var debouncedApply = debounce(applyGuard, 100);
                // Initial run
                applyGuard();
                // On resize and visibility changes
                window.addEventListener('resize', debouncedApply);
                document.addEventListener('visibilitychange', debouncedApply);
                // React to sidebar/body class changes
                try {
                    var mo = new MutationObserver(debouncedApply);
                    mo.observe(document.body, { attributes: true, attributeFilter: ['class'] });
                    if(sidebar){ mo.observe(sidebar, { attributes: true, attributeFilter: ['class', 'style'] }); }
                } catch(_) {}
                // Also run after explicit sidebar open/close actions
                ['click','transitionend'].forEach(function(evt){
                    if(sidebarToggleBtn) sidebarToggleBtn.addEventListener(evt, debouncedApply, true);
                    if(sidebarToggle) sidebarToggle.addEventListener(evt, debouncedApply, true);
                    if(sidebarOverlay) sidebarOverlay.addEventListener(evt, debouncedApply, true);
                });
            })();

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

	            // Sidebar unread badge for Messages
	            async function refreshSidebarUnread() {
	                try {
	                    const badge = document.getElementById('sidebarUnread');
	                    if (!badge) return;
	                    const res = await fetch('/messages/unread-count', { headers: { 'Accept': 'application/json' } });
	                    if (!res.ok) return;
	                    const data = await res.json();
	                    badge.textContent = data.unread ?? 0;
	                } catch (_) {}
	            }
	            refreshSidebarUnread();
	            setInterval(refreshSidebarUnread, 20000);

            // Sidebar pending appointments badge for doctor
            async function refreshSidebarAppointmentsPending() {
                try {
                    const badge = document.getElementById('sidebarAppointmentsPending');
                    if (!badge) return;
                    const res = await fetch('/appointments/pending-count', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    const count = data.count ?? 0;
                    badge.textContent = count;
                    // Show only for doctors with count>0, otherwise hide to avoid noise
                    if (count > 0) { badge.style.display = 'inline-block'; } else { badge.style.display = 'none'; }
                } catch (_) {}
            }
            refreshSidebarAppointmentsPending();
            setInterval(refreshSidebarAppointmentsPending, 20000);
            // Topbar bell: upcoming appointments dropdown
            async function refreshAppointmentsBell() {
                try {
                    const badge = document.getElementById('appointmentsBellBadge');
                    const wrap = document.getElementById('appointmentsBellWrap');
                    const content = document.getElementById('appointmentsBellContent');
                    if (!wrap || !content) return;
                    const res = await fetch('/appointments/upcoming-summary', { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    const myCount = data.my_count ?? 0;
                    if (badge) {
                        badge.textContent = myCount;
                        badge.style.display = myCount > 0 ? 'inline-block' : 'none';
                    }

                    const my = Array.isArray(data.my) ? data.my : [];
                    const clinic = Array.isArray(data.clinic) ? data.clinic : [];
                    let html = '';
                    if (my.length === 0 && clinic.length === 0) {
                        html = '<div class="text-center text-muted small py-3">No upcoming appointments</div>';
                    } else {
                        if (my.length > 0) {
                            html += '<div class="mb-2"><div class="small text-muted">My upcoming</div><ul class="list-group list-group-flush">' +
                                my.map(r => `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">#${r.appointment_number || r.id} - ${r.patient || ''}</div>
                                        <div class="small text-muted">${r.when || ''}</div>
                                    </div>
                                    <a class="btn btn-sm btn-outline-primary" href="/appointments/${r.id}">Open</a>
                                </li>`).join('') + '</ul></div>';
                        }
                        if (clinic.length > 0 && ({{ Auth::check() && Auth::user() && in_array(Auth::user()->role, ['admin', 'program_owner']) ? 'true' : 'false' }})) {
                            html += '<div class="mt-2"><div class="small text-muted">Clinic upcoming</div><ul class="list-group list-group-flush">' +
                                clinic.map(r => `<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">#${r.appointment_number || r.id} - ${r.patient || ''}</div>
                                        <div class="small text-muted">${r.when || ''} · ${r.doctor || ''}</div>
                                    </div>
                                    <a class="btn btn-sm btn-outline-secondary" href="/appointments/${r.id}">Open</a>
                                </li>`).join('') + '</ul></div>';
                        }
                    }
                    content.innerHTML = html;
                } catch (_) {}
            }
            refreshAppointmentsBell();
            setInterval(refreshAppointmentsBell, 30000);


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
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('layoutDebugToggle');
        if(btn){
          btn.addEventListener('click', function(e){
            e.preventDefault();
            var url = new URL(window.location.href);
            if(url.searchParams.has('layout-debug')){
              url.searchParams.delete('layout-debug');
            }else{
              url.searchParams.set('layout-debug','1');
            }
            window.location.href = url.toString();
          });
        }
        // Show a small badge when debug is active
        var params = new URLSearchParams(window.location.search);
        if(params.get('layout-debug') === '1'){
          var b = document.createElement('div');
          b.className = 'layout-debug-badge';
          b.textContent = 'Layout Debug ON';
          document.body.appendChild(b);
        }
      });
    </script>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-append a red star to labels for required fields when missing
      document.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(el){
        if(!el.id) return;
        var lbl = document.querySelector('label[for="'+CSS.escape(el.id)+'"]');
        if(lbl && !lbl.querySelector('.text-danger')){
          var star = document.createElement('span');
          star.className = 'text-danger';
          star.textContent = ' *';
          lbl.appendChild(star);
        }
      });

      // Client-side required validation for forms that opt-in
      document.querySelectorAll('form.needs-validation').forEach(function(form){
        form.setAttribute('novalidate','novalidate');
        form.addEventListener('submit', function(e){
          var missing = [];
          var fields = form.querySelectorAll('input[required], select[required], textarea[required]');
          fields.forEach(function(f){
            var value = (f.type === 'checkbox' || f.type === 'radio')
                        ? (form.querySelectorAll('[name="'+CSS.escape(f.name)+'"]:checked').length ? 'x' : '')
                        : (f.value || '').trim();
            if(!value){
              var label = f.id ? (form.querySelector('label[for="'+CSS.escape(f.id)+'"]').textContent || f.name) : f.name;
              label = label.replace('*','').trim();
              if(missing.indexOf(label) === -1) missing.push(label);
            }
          });
          if(missing.length){
            e.preventDefault(); e.stopPropagation();
            var existing = form.querySelector('.client-required-alert');
            if(existing) existing.remove();
            var div = document.createElement('div');
            div.className = 'alert alert-danger client-required-alert';
            div.innerHTML = '<strong>Please fill the following required fields:</strong><ul class="mb-0">'+ missing.map(function(m){return '<li>'+m+'</li>';}).join('') +'</ul>';
            form.prepend(div);
            var first = form.querySelector('input[required], select[required], textarea[required]');
            if(first) first.scrollIntoView({behavior:'smooth', block:'center'});
            alert('Please fill the required fields:\n- ' + missing.join('\n- '));
          }
        }, {capture:true});
      });
    });
    </script>

    @stack('scripts')
</body>
</html>
