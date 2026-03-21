@extends('layouts.app')

@section('title', __('Dental Chart') . ' - ' . $patient->full_name)

@push('styles')
<style>
    /* === Force parent containers to never clip the dental chart === */
    .main-content,
    .content-wrapper {
        overflow-x: visible !important;
    }
    /* Reduce excessive padding on the main content area for dental page */
    .main-content {
        padding-left: 15px !important;
        padding-right: 15px !important;
    }

    /* Root Layout */
    .dental-chart-modern {
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        min-height: 100vh;
    }

    .dental-header-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .dental-chart-container-modern {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .jaw-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .quadrant-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        text-align: center;
    }

    /* Clean Modern Dental Chart - Inspired by Professional UI */
    .dental-chart-wrapper {
        background: #f5f5f7;
        min-height: 100vh;
        padding: 2rem;
    }

    .dental-chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .dental-chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .dental-chart-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }

    .dental-chart-title i {
        color: #6b7280;
    }

    .dental-chart-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dental-chart-body {
        display: flex;
    }

    /* Sidebar Legend */
    .dental-sidebar {
        width: 260px;
        border-right: 1px solid #e5e7eb;
        padding: 1.5rem;
        background: #fafbfc;
    }

    .legend-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .legend-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
    }

    .legend-search {
        position: relative;
        margin-bottom: 1rem;
    }

    .legend-search input {
        width: 100%;
        padding: 0.5rem 0.75rem 0.5rem 2rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .legend-search i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.625rem 0.75rem;
        margin-bottom: 0.25rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .legend-item:hover {
        background: #f3f4f6;
    }

    .legend-item.active {
        background: #eff6ff;
        border-left: 3px solid #3b82f6;
    }

    .legend-item-left {
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .legend-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
    }

    .legend-checkbox.checked {
        background: #3b82f6;
        border-color: #3b82f6;
        position: relative;
    }

    .legend-checkbox.checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 10px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .legend-label {
        font-size: 0.875rem;
        color: #374151;
    }

    .legend-count {
        font-size: 0.75rem;
        color: #9ca3af;
        background: #f3f4f6;
        padding: 0.125rem 0.5rem;
        border-radius: 12px;
    }

    /* Main Chart Area */
    .dental-main {
        flex: 1;
        padding: 2rem;
    }

    .dental-toolbar {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .condition-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .tooth-type-icons {
        display: flex;
        gap: 0.5rem;
    }

    .tooth-type-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tooth-type-icon:hover {
        background: #f3f4f6;
    }

    .tooth-type-icon.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .apply-btn {
        margin-left: auto;
        padding: 0.5rem 1.5rem;
        background: #3b82f6;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
    }

    .apply-btn:hover {
        background: #2563eb;
    }

    /* Jaw Sections */
    .jaw-section {
        margin-bottom: 2.5rem;
    }

    .jaw-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .jaw-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .teeth-grid {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* Tooth Item - Clean Minimalist Design */
    .tooth-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        position: relative;
    }

    .tooth-number-badge {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 50%;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        position: relative;
        z-index: 2;
    }

    .tooth-number-badge.has-condition {
        border-color: #3b82f6;
    }

    .tooth-visual {
        position: relative;
        cursor: pointer;
        transition: transform 0.15s;
    }

    .tooth-visual:hover {
        transform: translateY(-2px);
    }

    .tooth-svg {
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.05));
    }

    /* Simplified Tooth SVG */
    .tooth-shape {
        fill: #ffffff;
        stroke: #d1d5db;
        stroke-width: 2;
        transition: all 0.15s;
    }

    .tooth-visual:hover .tooth-shape {
        stroke: #9ca3af;
    }

    .tooth-visual.selected .tooth-shape {
        fill: #dbeafe;
        stroke: #3b82f6;
        stroke-width: 2.5;
    }

    /* Condition Indicators - Small Colored Dots */
    .condition-indicator {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .condition-indicator.top-right {
        top: 8px;
        right: 8px;
    }

    .condition-indicator.top-left {
        top: 8px;
        left: 8px;
    }

    .condition-indicator.bottom-right {
        bottom: 8px;
        right: 8px;
    }

    /* Tooth Checkbox */
    .tooth-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .tooth-checkbox:hover {
        border-color: #9ca3af;
    }

    .tooth-checkbox.checked {
        background: #3b82f6;
        border-color: #3b82f6;
        position: relative;
    }

    .tooth-checkbox.checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-size: 11px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Tooltip */
    .tooth-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 0.5rem;
        padding: 0.75rem 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.15s;
        z-index: 100;
    }

    .tooth-visual:hover .tooth-tooltip {
        opacity: 1;
    }

    .tooltip-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .tooltip-condition {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .tooltip-date {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.25rem;
    }

    /* Condition Colors */
    .condition-healthy { background: #10b981; }
    .condition-caries { background: #ef4444; }
    .condition-filling { background: #3b82f6; }
    .condition-root_canal { background: #8b5cf6; }
    .condition-crown { background: #f59e0b; }
    .condition-extraction { background: #6b7280; }
    .condition-implant { background: #06b6d4; }
    .condition-fracture { background: #dc2626; }
    .condition-gingival { background: #ec4899; }
    .condition-bridge { background: #f97316; }
    .condition-other { background: #94a3b8; }

    /* Responsive */
    @media (max-width: 1024px) {
        .dental-chart-body {
            flex-direction: column;
        }

        .dental-sidebar {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }
    }

    .tooth-grid {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: center;
        align-items: flex-start;
        gap: 2px;
        width: 100%;
    }

    .tooth-grid .tooth-wrapper {
        flex: 1 1 0 !important;
        min-width: 0 !important;
        max-width: 60px;
        width: 0;
    }

    .tooth-grid .tooth-svg {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        max-width: 55px;
    }

    .tooth-wrapper {
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .tooth-wrapper:hover {
        transform: translateY(-4px) scale(1.05);
    }

    /* Prevent hover-scale on touch devices to avoid layout shift */
    @media (hover: none) and (pointer: coarse) {
        .tooth-wrapper:hover {
            transform: none;
        }
        .tooth-wrapper:active {
            transform: scale(0.95);
        }
    }

    .tooth-wrapper:hover .tooth-svg {
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.15));
    }

    .tooth-svg {
        display: block;
        transition: all 0.2s ease;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.08));
    }

    /* Healthy tooth styling — make the shape visible against white background */
    .tooth-wrapper .tooth-svg path.tooth-healthy {
        fill: url(#healthyToothGradient);
        stroke: #9ca3af;
        stroke-width: 1.8;
    }
    .tooth-wrapper:hover .tooth-svg path.tooth-healthy {
        stroke: #6b7280;
    }

    .tooth-number-label {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
    }

    .tooth-condition-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 2px solid white;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Tooltip Styles */
    .tooth-tooltip {
        position: fixed;
        background: rgba(17, 24, 39, 0.95);
        backdrop-filter: blur(8px);
        color: white;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
        max-width: 280px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .tooth-tooltip.show {
        opacity: 1;
    }

    .tooth-tooltip-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .tooth-tooltip-item {
        margin-bottom: 0.25rem;
        font-size: 0.8rem;
        opacity: 0.9;
    }

    /* Drawer Styles */
    .tooth-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 480px;
        height: 100vh;
        background: white;
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1050;
        overflow-y: auto;
    }

    .tooth-drawer.open {
        transform: translateX(0);
    }

    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        z-index: 1040;
    }

    .drawer-overlay.show {
        opacity: 1;
        pointer-events: all;
    }

    .drawer-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .drawer-body {
        padding: 1.5rem;
    }

    .drawer-footer {
        padding: 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        position: sticky;
        bottom: 0;
    }

    /* Legend — always horizontal bar above chart */
    .legend-sidebar {
        background: white;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.25rem 0.75rem;
        margin-bottom: 1rem;
    }

    .legend-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-right: 0.5rem;
        white-space: nowrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        margin-bottom: 0;
        transition: background 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
        font-size: 0.8rem;
    }

    .legend-item:hover {
        background: #f3f4f6;
    }

    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        margin-right: 0.5rem;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .legend-name {
        font-size: 0.8rem;
        color: #4b5563;
        flex: 1;
    }

    .legend-count {
        font-size: 0.75rem;
        color: #9ca3af;
        background: #f3f4f6;
        padding: 0.125rem 0.5rem;
        border-radius: 12px;
    }

    /* Toolbar */
    .dental-toolbar {
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .toolbar-section {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .chart-type-toggle {
        display: inline-flex;
        background: #f3f4f6;
        border-radius: 8px;
        padding: 0.25rem;
    }

    .chart-type-btn {
        padding: 0.5rem 1rem;
        border: none;
        background: transparent;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .chart-type-btn.active {
        background: white;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* === Dental page: override parent layout constraints === */
    /* The .main-content and .content-wrapper have overflow-x:hidden and heavy padding.
       We must override them so the chart is never clipped. */
    .dental-chart-modern {
        /* Counteract parent padding to reclaim full width */
        margin-left: -1.25rem;
        margin-right: -1.25rem;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    /* Override the container-fluid px-4 padding for the dental page */
    .dental-chart-modern .container-fluid.px-4 {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    /* Responsive — tablets and smaller */
    @media (max-width: 1199.98px) {
        .dental-chart-container-modern {
            padding: 0.5rem;
        }

        .dental-toolbar {
            padding: 0.5rem 0.75rem;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .tooth-condition-badge {
            width: 16px;
            height: 16px;
            font-size: 8px;
            top: -5px;
            right: -5px;
        }

        .tooth-drawer {
            width: 380px;
        }

        /* Let teeth shrink freely on tablets */
        .tooth-grid .tooth-wrapper {
            max-width: none;
        }
        .tooth-grid .tooth-svg {
            max-width: none;
        }
    }

    /* Responsive — small tablets / phones */
    @media (max-width: 767.98px) {
        .tooth-drawer {
            width: 100%;
        }

        .dental-header-card {
            padding: 0.75rem 1rem;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .dental-chart-modern {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }

        .dental-header-card,
        .dental-chart-container-modern,
        .legend-sidebar,
        .dental-toolbar {
            background: #1f2937;
            color: #f3f4f6;
        }

        .jaw-section {
            background: #111827;
        }
    }

    /* ─── Print Styles ─── */
    @media print {
        /* Hide app shell: sidebar, topbar, footer, overlays */
        .sidebar,
        .sidebar-overlay,
        .topbar,
        .main-footer,
        .sidebar-toggle-btn,
        .layout-debug-badge {
            display: none !important;
        }

        /* Remove sidebar offset from main content */
        .main-content {
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-inline-start: 0 !important;
            margin-top: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* Hide non-printable UI elements */
        .dental-toolbar,
        .tooth-drawer,
        .btn,
        .alert-dismissible .btn-close {
            display: none !important;
        }

        /* Show header card action buttons area but hide actual buttons */
        .dental-header-card .d-flex.gap-2 {
            display: none !important;
        }

        /* Reset backgrounds for printing */
        .dental-chart-modern {
            background: white !important;
            min-height: auto !important;
        }

        .dental-header-card,
        .dental-chart-container-modern,
        .legend-sidebar,
        .jaw-section {
            background: white !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        /* Ensure chart fits on page */
        .dental-chart-modern,
        .container-fluid {
            padding: 0.5rem !important;
        }

        /* Keep tooth visuals visible */
        .tooth-modern svg,
        .tooth-modern img {
            print-color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
        }

        /* Condition color badges should print with colors */
        .condition-healthy,
        .condition-cavity,
        .condition-filling,
        .condition-crown,
        .condition-extraction,
        .condition-implant,
        .condition-bridge,
        .condition-root-canal,
        .condition-missing,
        .condition-other {
            print-color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
        }

        /* Page setup */
        @page {
            size: landscape;
            margin: 1cm;
        }

        body {
            background: white !important;
            overflow: visible !important;
        }
    }
</style>
@endpush

@section('content')
<div class="dental-chart-modern">
    <div class="container-fluid px-4 py-4">
        <!-- Header Card -->
        <div class="dental-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1 d-flex align-items-center">
                        <i class="fas fa-tooth me-2 text-primary"></i>
                        {{ __('Dental Chart') }}
                    </h1>
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-user me-1"></i>
                        <strong>{{ $patient->full_name }}</strong>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar me-1"></i>
                        {{ $dentalChart->created_at->format('M d, Y') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ url("/dental/patients/{$patient->id}/charts") }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        {{ __('Back') }}
                    </a>
                    @if(in_array(auth()->user()->role, ['doctor', 'assistant', 'admin', 'program_owner', 'dental_dept']))
                        <a href="{{ url("/dental/patients/{$patient->id}/charts/{$dentalChart->id}") }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>
                            {{ __('Edit Mode') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="dental-toolbar" x-data="{ viewMode: 'view', chartType: '{{ $dentalChart->chart_type }}' }">
            <div class="toolbar-section">
                <div class="chart-type-toggle">
                    <button class="chart-type-btn" :class="{ 'active': chartType === 'adult' }" disabled>
                        <i class="fas fa-user me-1"></i>
                        {{ __('Adult') }}
                    </button>
                    <button class="chart-type-btn" :class="{ 'active': chartType === 'pediatric' }" disabled>
                        <i class="fas fa-child me-1"></i>
                        {{ __('Pediatric') }}
                    </button>
                </div>
                <span class="badge bg-primary">
                    {{ ucfirst($dentalChart->chart_type) }} {{ __('Dentition') }}
                </span>
            </div>
            <div class="toolbar-section">
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>
                    {{ __('Print') }}
                </button>
                <a href="{{ route('dental.charts.pdf', [$patient, $dentalChart]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i>
                    {{ __('Export PDF') }}
                </a>
            </div>
        </div>

        <!-- Condition Legend Bar (horizontal, full-width) -->
        <div class="legend-sidebar">
            <div class="legend-title">
                <i class="fas fa-list-ul me-2"></i>
                {{ __('Condition Legend') }}
            </div>
            @php
                $conditionCounts = [];
                foreach($dentalChart->toothRecords as $record) {
                    $cond = $record->primary_condition;
                    $conditionCounts[$cond] = ($conditionCounts[$cond] ?? 0) + 1;
                }
            @endphp
            @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                <div class="legend-item" data-condition="{{ $key }}">
                    <div class="legend-color" style="background-color: {{ $condition['color'] }};"></div>
                    <div class="legend-name">{{ $condition['name'] }}</div>
                    @if(isset($conditionCounts[$key]))
                        <div class="legend-count">{{ $conditionCounts[$key] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Dental Chart (full width) -->
        <div class="dental-chart-container-modern"
                     x-data="dentalChartApp()"
                     x-init="init()"
                     @keydown.escape.window="closeDrawer()">

                    @php
                        $toothNumbers = $dentalChart->tooth_numbers;
                        $toothRecords = $dentalChart->toothRecords->keyBy('tooth_number');
                    @endphp

                    {{-- Shared SVG gradient definition for healthy teeth --}}
                    <svg width="0" height="0" style="position:absolute">
                        <defs>
                            <linearGradient id="healthyToothGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f8f9fa"/>
                                <stop offset="40%" stop-color="#e9ecef"/>
                                <stop offset="100%" stop-color="#dee2e6"/>
                            </linearGradient>
                        </defs>
                    </svg>

                    <!-- Upper Jaw -->
                    <div class="jaw-section">
                        <div class="jaw-title">
                            <i class="fas fa-chevron-up me-2"></i>
                            {{ __('Upper Jaw') }}
                        </div>
                        <div class="row">
                            <!-- Upper Right Quadrant -->
                            <div class="col-6">
                                <div class="quadrant-label">{{ __('Right') }}</div>
                                <div class="tooth-grid">
                                    @foreach($toothNumbers['upper_right'] as $toothNum)
                                        @php
                                            $record = $toothRecords->get($toothNum);
                                            $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                            $condition = $record ? $record->primary_condition : 'healthy';
                                            $isHealthy = ($condition === 'healthy');
                                            $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                        @endphp
                                        <div class="tooth-wrapper"
                                             @click="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseenter="!isTouchDevice && showTooltip($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseleave="hideTooltip()"
                                             @touchend.prevent="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})">
                                            <svg class="tooth-svg" viewBox="0 0 60 80">
                                                <path class="{{ $isHealthy ? 'tooth-healthy' : '' }}"
                                                      d="M 30 8 C 26 8 22 11 22 16 C 22 20 23 24 23 28 L 23 38 C 23 40 22 42 20 46 C 18 50 18 54 20 58 C 21 60 23 62 25 64 L 28 74 C 28 76 29 78 30 78 C 31 78 32 76 32 74 L 35 64 C 37 62 39 60 40 58 C 42 54 42 50 40 46 C 38 42 37 40 37 38 L 37 28 C 37 24 38 20 38 16 C 38 11 34 8 30 8 Z"
                                                      fill="{{ $isHealthy ? 'url(#healthyToothGradient)' : $color }}" stroke="{{ $isHealthy ? '#9ca3af' : '#333' }}" stroke-width="{{ $isHealthy ? '1.8' : '1.5' }}"/>
                                                <ellipse cx="27" cy="22" rx="5" ry="7" fill="rgba(255,255,255,0.3)"/>
                                                <text x="30" y="45" text-anchor="middle" font-size="12" font-weight="600" fill="{{ $textColor }}">
                                                    {{ $toothNum }}
                                                </text>
                                            </svg>
                                            @if($record && !$isHealthy)
                                                <div class="tooth-condition-badge" style="background-color: {{ $color }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['icon'] ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Upper Left Quadrant -->
                            <div class="col-6">
                                <div class="quadrant-label">{{ __('Left') }}</div>
                                <div class="tooth-grid">
                                    @foreach($toothNumbers['upper_left'] as $toothNum)
                                        @php
                                            $record = $toothRecords->get($toothNum);
                                            $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                            $condition = $record ? $record->primary_condition : 'healthy';
                                            $isHealthy = ($condition === 'healthy');
                                            $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                        @endphp
                                        <div class="tooth-wrapper"
                                             @click="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseenter="!isTouchDevice && showTooltip($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseleave="hideTooltip()"
                                             @touchend.prevent="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})">
                                            <svg class="tooth-svg" viewBox="0 0 60 80">
                                                <path class="{{ $isHealthy ? 'tooth-healthy' : '' }}"
                                                      d="M 30 8 C 26 8 22 11 22 16 C 22 20 23 24 23 28 L 23 38 C 23 40 22 42 20 46 C 18 50 18 54 20 58 C 21 60 23 62 25 64 L 28 74 C 28 76 29 78 30 78 C 31 78 32 76 32 74 L 35 64 C 37 62 39 60 40 58 C 42 54 42 50 40 46 C 38 42 37 40 37 38 L 37 28 C 37 24 38 20 38 16 C 38 11 34 8 30 8 Z"
                                                      fill="{{ $isHealthy ? 'url(#healthyToothGradient)' : $color }}" stroke="{{ $isHealthy ? '#9ca3af' : '#333' }}" stroke-width="{{ $isHealthy ? '1.8' : '1.5' }}"/>
                                                <ellipse cx="27" cy="22" rx="5" ry="7" fill="rgba(255,255,255,0.3)"/>
                                                <text x="30" y="45" text-anchor="middle" font-size="12" font-weight="600" fill="{{ $textColor }}">
                                                    {{ $toothNum }}
                                                </text>
                                            </svg>
                                            @if($record && !$isHealthy)
                                                <div class="tooth-condition-badge" style="background-color: {{ $color }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['icon'] ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lower Jaw -->
                    <div class="jaw-section">
                        <div class="jaw-title">
                            <i class="fas fa-chevron-down me-2"></i>
                            {{ __('Lower Jaw') }}
                        </div>
                        <div class="row">
                            <!-- Lower Right Quadrant -->
                            <div class="col-6">
                                <div class="quadrant-label">{{ __('Right') }}</div>
                                <div class="tooth-grid">
                                    @foreach($toothNumbers['lower_right'] as $toothNum)
                                        @php
                                            $record = $toothRecords->get($toothNum);
                                            $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                            $condition = $record ? $record->primary_condition : 'healthy';
                                            $isHealthy = ($condition === 'healthy');
                                            $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                        @endphp
                                        <div class="tooth-wrapper"
                                             @click="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseenter="!isTouchDevice && showTooltip($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseleave="hideTooltip()"
                                             @touchend.prevent="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})">
                                            <svg class="tooth-svg" viewBox="0 0 60 80" style="transform: scaleY(-1);">
                                                <path class="{{ $isHealthy ? 'tooth-healthy' : '' }}"
                                                      d="M 30 8 C 26 8 22 11 22 16 C 22 20 23 24 23 28 L 23 38 C 23 40 22 42 20 46 C 18 50 18 54 20 58 C 21 60 23 62 25 64 L 28 74 C 28 76 29 78 30 78 C 31 78 32 76 32 74 L 35 64 C 37 62 39 60 40 58 C 42 54 42 50 40 46 C 38 42 37 40 37 38 L 37 28 C 37 24 38 20 38 16 C 38 11 34 8 30 8 Z"
                                                      fill="{{ $isHealthy ? 'url(#healthyToothGradient)' : $color }}" stroke="{{ $isHealthy ? '#9ca3af' : '#333' }}" stroke-width="{{ $isHealthy ? '1.8' : '1.5' }}"/>
                                                <ellipse cx="27" cy="22" rx="5" ry="7" fill="rgba(255,255,255,0.3)"/>
                                                <text x="30" y="45" text-anchor="middle" font-size="12" font-weight="600" fill="{{ $textColor }}" transform="scale(1, -1) translate(0, -90)">
                                                    {{ $toothNum }}
                                                </text>
                                            </svg>
                                            @if($record && !$isHealthy)
                                                <div class="tooth-condition-badge" style="background-color: {{ $color }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['icon'] ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Lower Left Quadrant -->
                            <div class="col-6">
                                <div class="quadrant-label">{{ __('Left') }}</div>
                                <div class="tooth-grid">
                                    @foreach($toothNumbers['lower_left'] as $toothNum)
                                        @php
                                            $record = $toothRecords->get($toothNum);
                                            $color = $record ? $record->primary_condition_color : '#FFFFFF';
                                            $condition = $record ? $record->primary_condition : 'healthy';
                                            $isHealthy = ($condition === 'healthy');
                                            $textColor = in_array($condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff';
                                        @endphp
                                        <div class="tooth-wrapper"
                                             @click="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseenter="!isTouchDevice && showTooltip($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})"
                                             @mouseleave="hideTooltip()"
                                             @touchend.prevent="handleToothTap($event, '{{ $toothNum }}', {{ $record ? json_encode($record) : 'null' }})">
                                            <svg class="tooth-svg" viewBox="0 0 60 80" style="transform: scaleY(-1);">
                                                <path class="{{ $isHealthy ? 'tooth-healthy' : '' }}"
                                                      d="M 30 8 C 26 8 22 11 22 16 C 22 20 23 24 23 28 L 23 38 C 23 40 22 42 20 46 C 18 50 18 54 20 58 C 21 60 23 62 25 64 L 28 74 C 28 76 29 78 30 78 C 31 78 32 76 32 74 L 35 64 C 37 62 39 60 40 58 C 42 54 42 50 40 46 C 38 42 37 40 37 38 L 37 28 C 37 24 38 20 38 16 C 38 11 34 8 30 8 Z"
                                                      fill="{{ $isHealthy ? 'url(#healthyToothGradient)' : $color }}" stroke="{{ $isHealthy ? '#9ca3af' : '#333' }}" stroke-width="{{ $isHealthy ? '1.8' : '1.5' }}"/>
                                                <ellipse cx="27" cy="22" rx="5" ry="7" fill="rgba(255,255,255,0.3)"/>
                                                <text x="30" y="45" text-anchor="middle" font-size="12" font-weight="600" fill="{{ $textColor }}" transform="scale(1, -1) translate(0, -90)">
                                                    {{ $toothNum }}
                                                </text>
                                            </svg>
                                            @if($record && !$isHealthy)
                                                <div class="tooth-condition-badge" style="background-color: {{ $color }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['icon'] ?? '' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tooltip (Hidden by default) -->
                    <div class="tooth-tooltip" x-ref="tooltip" x-show="tooltipVisible" x-cloak>
                        <div class="tooth-tooltip-title" x-text="tooltipData.title"></div>
                        <div class="tooth-tooltip-item" x-show="tooltipData.condition" x-text="tooltipData.condition"></div>
                        <div class="tooth-tooltip-item" x-show="tooltipData.surfaces" x-text="tooltipData.surfaces"></div>
                        <div class="tooth-tooltip-item" x-show="tooltipData.updated" x-text="tooltipData.updated"></div>
                    </div>
                </div>
            </div>

    <!-- Tooth Records Details -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        {{ __('Tooth Records Details') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($dentalChart->toothRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Tooth #') }}</th>
                                        <th>{{ __('Primary Condition') }}</th>
                                        <th>{{ __('All Conditions') }}</th>
                                        <th>{{ __('Surfaces') }}</th>
                                        <th>{{ __('Severity') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dentalChart->toothRecords->sortBy('tooth_number') as $record)
                                        <tr>
                                            <td><strong>{{ $record->tooth_number }}</strong></td>
                                            <td>
                                                <span class="badge" style="background-color: {{ $record->primary_condition_color }}; color: {{ in_array($record->primary_condition, ['healthy', 'filling', 'crown', 'implant', 'bridge', 'periodontal', 'other']) ? '#000' : '#fff' }};">
                                                    {{ \App\Models\DentalToothRecord::CONDITIONS[$record->primary_condition]['name'] ?? $record->primary_condition }}
                                                </span>
                                            </td>
                                            <td>
                                                @foreach($record->conditions ?? [] as $cond)
                                                    <span class="badge bg-secondary me-1">{{ \App\Models\DentalToothRecord::CONDITIONS[$cond]['name'] ?? $cond }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $record->surfaces_display }}</td>
                                            <td>
                                                @if($record->severity)
                                                    <span class="badge bg-{{ $record->severity === 'mild' ? 'success' : ($record->severity === 'moderate' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($record->severity) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $record->notes ?? '-' }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('No tooth records in this chart') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- General Notes -->
    @if($dentalChart->general_notes)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i>
                            {{ __('General Notes') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $dentalChart->general_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Drawer Overlay -->
    <div class="drawer-overlay" x-show="drawerOpen" @click="closeDrawer()" x-cloak></div>

    <!-- Tooth Details Drawer -->
    <div class="tooth-drawer" :class="{ 'open': drawerOpen }" x-cloak>
        <div class="drawer-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-tooth me-2"></i>
                    <span x-text="'Tooth #' + selectedTooth"></span>
                </h5>
                <button type="button" class="btn-close" @click="closeDrawer()"></button>
            </div>
        </div>

        <div class="drawer-body">
            <form @submit.prevent="saveToothRecord()">
                <!-- Primary Condition -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('Primary Condition') }}</label>
                    <select class="form-select" x-model="formData.primary_condition" required>
                        <option value="">{{ __('Select condition') }}</option>
                        @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                            <option value="{{ $key }}">{{ $condition['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- All Conditions (Multi-select) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('All Conditions') }}</label>
                    <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                        @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                       value="{{ $key }}"
                                       :id="'cond-' + '{{ $key }}'"
                                       x-model="formData.conditions">
                                <label class="form-check-label" :for="'cond-' + '{{ $key }}'">
                                    <span style="color: {{ $condition['color'] }};">{{ $condition['icon'] }}</span>
                                    {{ $condition['name'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Surfaces Affected -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('Surfaces Affected') }}</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(\App\Models\DentalToothRecord::SURFACES as $key => $surface)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox"
                                       value="{{ $key }}"
                                       :id="'surf-' + '{{ $key }}'"
                                       x-model="formData.surfaces_affected">
                                <label class="form-check-label" :for="'surf-' + '{{ $key }}'">
                                    {{ $key }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">
                        O=Occlusal, M=Mesial, D=Distal, B=Buccal, F=Facial, L=Lingual, P=Palatal
                    </small>
                </div>

                <!-- Severity -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('Severity') }}</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="severity" id="severity-mild" value="mild" x-model="formData.severity">
                        <label class="btn btn-outline-success" for="severity-mild">{{ __('Mild') }}</label>

                        <input type="radio" class="btn-check" name="severity" id="severity-moderate" value="moderate" x-model="formData.severity">
                        <label class="btn btn-outline-warning" for="severity-moderate">{{ __('Moderate') }}</label>

                        <input type="radio" class="btn-check" name="severity" id="severity-severe" value="severe" x-model="formData.severity">
                        <label class="btn btn-outline-danger" for="severity-severe">{{ __('Severe') }}</label>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">{{ __('Notes') }}</label>
                    <textarea class="form-control" rows="4" x-model="formData.notes"
                              placeholder="{{ __('Enter any additional notes about this tooth...') }}"></textarea>
                </div>

                <!-- Last Updated -->
                <div class="mb-4" x-show="formData.updated_at">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        {{ __('Last updated') }}: <span x-text="formData.updated_at"></span>
                    </small>
                </div>
            </form>
        </div>

        <div class="drawer-footer">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" @click="closeDrawer()">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary flex-fill" @click="saveToothRecord()" :disabled="saving">
                    <span x-show="!saving">
                        <i class="fas fa-save me-1"></i>
                        {{ __('Save Changes') }}
                    </span>
                    <span x-show="saving">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        {{ __('Saving...') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dentalChartApp() {
    return {
        drawerOpen: false,
        selectedTooth: null,
        formData: {
            primary_condition: '',
            conditions: [],
            surfaces_affected: [],
            severity: null,
            notes: '',
            updated_at: null
        },
        tooltipVisible: false,
        tooltipData: {
            title: '',
            condition: '',
            surfaces: '',
            updated: ''
        },
        saving: false,

        isTouchDevice: false,

        init() {
            // Detect touch capability
            this.isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

            // On touch devices, dismiss tooltip when tapping outside a tooth
            if (this.isTouchDevice) {
                document.addEventListener('touchstart', (e) => {
                    if (!e.target.closest('.tooth-wrapper')) {
                        this.hideTooltip();
                    }
                }, { passive: true });
            }

            console.log('Dental Chart App initialized (touch=' + this.isTouchDevice + ')');
        },

        openDrawer(toothNumber, record) {
            this.selectedTooth = toothNumber;

            // Populate form with existing data or defaults
            if (record) {
                this.formData = {
                    primary_condition: record.primary_condition || '',
                    conditions: record.conditions || [],
                    surfaces_affected: record.surfaces_affected || [],
                    severity: record.severity || null,
                    notes: record.notes || '',
                    updated_at: record.updated_at ? new Date(record.updated_at).toLocaleString() : null
                };
            } else {
                // Reset form for new tooth record
                this.formData = {
                    primary_condition: 'healthy',
                    conditions: [],
                    surfaces_affected: [],
                    severity: null,
                    notes: '',
                    updated_at: null
                };
            }

            this.drawerOpen = true;
            this.hideTooltip();
        },

        closeDrawer() {
            this.drawerOpen = false;
            this.selectedTooth = null;
        },

        handleToothTap(event, toothNumber, record) {
            // On touch devices: first tap shows tooltip, second tap opens drawer
            if (this.isTouchDevice && !this.drawerOpen) {
                if (this.tooltipVisible && this.tooltipData.title === `Tooth #${toothNumber}`) {
                    // Tooltip already showing for this tooth — open drawer
                    this.hideTooltip();
                    this.openDrawer(toothNumber, record);
                } else {
                    // First tap — show tooltip
                    event.preventDefault();
                    event.stopPropagation();
                    this.showTooltip(event, toothNumber, record);
                }
                return;
            }
            // Desktop: click opens drawer directly
            this.openDrawer(toothNumber, record);
        },

        showTooltip(event, toothNumber, record) {
            if (this.drawerOpen) return; // Don't show tooltip when drawer is open

            const tooltip = this.$refs.tooltip;
            const rect = event.currentTarget.getBoundingClientRect();

            // Position tooltip — ensure it stays within viewport
            let left = rect.left + (rect.width / 2);
            let top = rect.top - 10;

            // Clamp horizontally
            left = Math.max(100, Math.min(left, window.innerWidth - 100));
            // If too close to top, show below instead
            if (top < 80) {
                top = rect.bottom + 10;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';

            // Populate tooltip data
            this.tooltipData.title = `Tooth #${toothNumber}`;

            if (record && record.primary_condition) {
                const conditions = @json(\App\Models\DentalToothRecord::CONDITIONS);
                this.tooltipData.condition = `Condition: ${conditions[record.primary_condition]?.name || record.primary_condition}`;

                if (record.surfaces_affected && record.surfaces_affected.length > 0) {
                    this.tooltipData.surfaces = `Surfaces: ${record.surfaces_affected.join(', ')}`;
                } else {
                    this.tooltipData.surfaces = '';
                }

                if (record.updated_at) {
                    this.tooltipData.updated = `Updated: ${new Date(record.updated_at).toLocaleDateString()}`;
                } else {
                    this.tooltipData.updated = '';
                }
            } else {
                this.tooltipData.condition = 'Condition: Healthy';
                this.tooltipData.surfaces = '';
                this.tooltipData.updated = '';
            }

            this.tooltipVisible = true;
        },

        hideTooltip() {
            this.tooltipVisible = false;
        },

        async saveToothRecord() {
            if (!this.selectedTooth) return;

            this.saving = true;

            try {
                const response = await fetch(`/dental/patients/{{ $patient->id }}/charts/{{ $dentalChart->id }}/tooth-record`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tooth_number: this.selectedTooth,
                        primary_condition: this.formData.primary_condition,
                        conditions: this.formData.conditions,
                        surfaces_affected: this.formData.surfaces_affected,
                        severity: this.formData.severity,
                        notes: this.formData.notes
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to save tooth record');
                }

                const data = await response.json();

                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Tooth record saved successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('Tooth record saved successfully');
                }

                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);

            } catch (error) {
                console.error('Error saving tooth record:', error);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to save tooth record. Please try again.'
                    });
                } else {
                    alert('Failed to save tooth record. Please try again.');
                }
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endpush

