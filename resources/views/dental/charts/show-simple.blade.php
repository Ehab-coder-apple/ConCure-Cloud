@extends('layouts.app')

@section('title', __('Dental Chart Visualization') . ' - ' . $patient->full_name)

@push('styles')
<style>
    /* Clean Professional Dental Chart UI - Matching Reference Design */
    body {
        background: #f5f5f7;
    }

    .dental-chart-wrapper {
        background: #f5f5f7;
        min-height: 100vh;
        padding: 1.5rem;
    }

    .dental-chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
    }

    .chart-saving-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.76);
        backdrop-filter: blur(2px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        z-index: 50;
    }

    .chart-saving-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    .chart-saving-panel {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
        min-width: 280px;
    }

    .chart-saving-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .chart-saving-text {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .chart-saving-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
    }

    .chart-saving-detail {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .dental-chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.5rem;
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

    .dental-chart-body {
        display: flex;
        min-height: 600px;
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

    .legend-toggle {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
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
        outline: none;
    }

    .legend-search input:focus {
        border-color: #3b82f6;
    }

    .legend-search i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.75rem;
    }

    .legend-filter-icon {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #3b82f6;
        font-size: 0.875rem;
        cursor: pointer;
    }

    .legend-items {
        max-height: calc(100vh - 300px);
        overflow-y: auto;
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
    }

    .legend-item-left {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        flex: 1;
    }

    .legend-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        flex-shrink: 0;
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
        flex-shrink: 0;
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
        min-width: 24px;
        text-align: center;
    }

    /* Main Chart Area */
    .dental-main {
        flex: 1;
        padding: 2rem;
        background: white;
    }

    .dental-toolbar {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }

    /* Selection Counter Bar */
    .selection-counter-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        animation: fadeIn 0.3s ease;
    }

    .selection-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 15px;
    }

    .selection-count strong {
        font-size: 18px;
        font-weight: 700;
    }

    .deselect-all-btn {
        padding: 6px 14px;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .deselect-all-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
    }

    .keyboard-hint {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 13px;
        opacity: 0.9;
    }

    .keyboard-hint kbd {
        background: rgba(255, 255, 255, 0.2);
        padding: 3px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Tooth Details Drawer */
    .tooth-drawer {
	        /* Keep the drawer outside any local stacking/transform/overflow contexts */
	        position: fixed !important;
	        inset: 0 !important;
	        z-index: 2147483000 !important;
    }

    .drawer-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        background: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease;
	        z-index: 2147483000 !important;
    }

    .drawer-content {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 500px !important;
        background: white;
        box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
        animation: slideInFromRight 0.3s ease;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
	        z-index: 2147483001 !important;
    }

    .drawer-header {
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 2px solid #e5e7eb;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        flex-shrink: 0 !important;
        flex-grow: 0 !important;
        min-height: 80px !important;
        height: 80px !important;
        max-height: 80px !important;
        width: 100% !important;
        position: relative !important;
        z-index: 10 !important;
        max-height: 80px !important;
        width: 100%;
        position: relative;
        z-index: 2;
        margin-bottom: 0 !important;
    }

    .drawer-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        display: flex !important;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        color: white;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .drawer-title i {
        font-size: 1.5rem;
    }

    .drawer-title span {
        color: white;
        font-weight: 600;
    }

    .drawer-header-actions {
        display: flex !important;
        gap: 0.5rem;
        align-items: center;
        flex-shrink: 0;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .drawer-action-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        padding: 0 16px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
    }

    .drawer-action-btn:hover {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .drawer-action-btn.active {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
    }

    .drawer-action-btn.active::after {
        content: ' (Editing)';
        margin-left: 4px;
        font-size: 12px;
        opacity: 0.9;
    }

    .drawer-action-btn i {
        font-size: 14px;
    }

    .drawer-close-btn {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-size: 18px;
    }

    .drawer-close-btn:hover {
        background: #ef4444;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .drawer-close-btn i {
        font-size: 18px;
    }

    /* Drawer Tabs */
    .drawer-tabs {
        display: flex !important;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        flex-shrink: 0 !important;
        position: relative;
        z-index: 1;
        margin-top: 0;
        height: 48px !important;
        min-height: 48px !important;
        max-height: 48px !important;
    }

    .drawer-tab {
        flex: 1;
        padding: 0.5rem 1rem;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 100%;
    }

    .drawer-tab:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .drawer-tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: white;
    }

    /* Drawer Footer */
    .drawer-footer {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .btn-cancel {
        flex: 1;
        padding: 0.75rem 1.5rem;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #374151;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover:not(:disabled) {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-cancel:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-save {
        flex: 1;
        padding: 0.75rem 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-save:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-save:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .drawer-body {
        flex: 1 1 auto !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 1.5rem;
        background: white;
        min-height: 0 !important;
    }

    .detail-section {
        margin-bottom: 1.5rem;
    }

    .detail-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .detail-value {
        font-size: 1rem;
        color: #1f2937;
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .condition-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        margin-right: 0.5rem;
    }

    .condition-badge i {
        font-size: 1rem;
    }

    .no-data {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.875rem;
    }

    /* Edit Mode Form Elements */
    .edit-input, .edit-select, .edit-textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    .edit-input:focus, .edit-select:focus, .edit-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .edit-textarea {
        min-height: 100px;
        resize: vertical;
        font-family: inherit;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .checkbox-item:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }

    .checkbox-item input[type="checkbox"] {
        cursor: pointer;
    }

    .checkbox-item.checked {
        background: #eff6ff;
        border-color: #3b82f6;
    }

    /* Treatment History Timeline */
    .history-timeline {
        position: relative;
        padding-left: 2rem;
    }

    .history-item {
        position: relative;
        padding-bottom: 1.5rem;
        border-left: 2px solid #e5e7eb;
        padding-left: 1.5rem;
        margin-bottom: 1rem;
    }

    .history-item:last-child {
        border-left-color: transparent;
    }

    .history-dot {
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #667eea;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }

    .history-date {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .history-action {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .history-details {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .history-user {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 0.25rem;
    }

    @keyframes slideInFromRight {
        from {
            transform: translateX(100%);
        }
        to {
            transform: translateX(0);
        }
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
        color: #374151;
    }

    .condition-selector i {
        color: #9ca3af;
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
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .apply-btn:hover:not(:disabled) {
        background: #2563eb;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        transform: translateY(-1px);
    }

    .apply-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .apply-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .clear-btn {
        padding: 0.5rem 1.5rem;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 0.5rem;
    }

    .clear-btn:hover:not(:disabled) {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        transform: translateY(-1px);
    }

    .clear-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .clear-btn:active:not(:disabled) {
        transform: translateY(0);
    }

    .drawer-action-btn:disabled,
    .drawer-close-btn:disabled {
        opacity: 0.6 !important;
        cursor: not-allowed !important;
    }

    /* Jaw Sections */
    .jaw-section {
        margin-bottom: 3rem;
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

    .teeth-container {
        display: flex;
        justify-content: center;
        gap: 3rem;
    }

    .teeth-grid {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
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
        background: #eff6ff;
    }

    .tooth-visual {
        position: relative;
        cursor: pointer;
        transition: transform 0.15s;
        z-index: 1;
    }

    .tooth-visual:hover {
        transform: translateY(-2px);
        z-index: 10000;
    }

    /* Anatomical Tooth SVG */
    .tooth-svg {
        width: 46px;
        height: 70px;
        filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.08));
        overflow: visible;
    }

    /* Premium 3D Tooth Styling */
    .tooth-shape-main {
        fill: url(#toothGradient);
        stroke: #DDE2E8;
        stroke-width: 1;
        transition: all 0.2s ease;
        filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.08));
    }

    .tooth-highlight {
        fill: url(#highlightGradient);
        opacity: 0.6;
        transition: opacity 0.2s ease;
    }

    .tooth-shadow {
        fill: url(#shadowGradient);
        opacity: 0.3;
        transition: opacity 0.2s ease;
    }

    .tooth-visual:hover .tooth-shape-main {
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.12));
    }

    .tooth-visual:hover .tooth-highlight {
        opacity: 0.8;
    }

    /* Selected state */
    .tooth-visual.selected .tooth-shape-main {
        fill: url(#toothGradientSelected);
        stroke: #3b82f6;
        stroke-width: 1.5;
        filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.3));
    }

    .tooth-visual.selected .tooth-highlight {
        opacity: 0.9;
    }

    /* Condition Indicators - Small Colored Dots */
    .condition-indicator {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        z-index: 5;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .condition-indicator:hover {
        transform: scale(1.3);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
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

    .condition-indicator.bottom-left {
        bottom: 8px;
        left: 8px;
    }

    /* Remove icon on condition indicator */
    .condition-indicator::after {
        content: '×';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 10px;
        font-weight: bold;
        opacity: 0;
        transition: opacity 0.2s ease;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }

    .condition-indicator:hover::after {
        opacity: 1;
    }

    /* Legend filter: dim non-matching teeth */
    .tooth-item.legend-dimmed {
        opacity: 0.2;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .tooth-item.legend-highlighted {
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .tooth-item.legend-highlighted .tooth-visual {
        filter: drop-shadow(0 0 8px rgba(59, 130, 246, 0.4));
    }

    /* Tooth Info Button */
    .tooth-info-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 20px;
        height: 20px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s ease;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .tooth-visual:hover .tooth-info-btn {
        opacity: 1;
    }

    .tooth-info-btn:hover {
        background: #2563eb;
        transform: scale(1.1);
    }

    /* Tooth Checkbox */
    .tooth-checkbox {
        width: 16px;
        height: 16px;
        border: 2px solid #d1d5db;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tooth-checkbox:hover {
        border-color: #9ca3af;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .tooth-checkbox.checked {
        background: #3b82f6;
        border-color: #3b82f6;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2);
    }

    .tooth-checkbox.checked::after {
        content: '✓';
        color: white;
        font-size: 12px;
        font-weight: bold;
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
        font-weight: bold;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* Tooltip */
    .tooth-tooltip {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        padding: 0.75rem 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease, transform 0.2s ease;
        z-index: 10001;
        min-width: 150px;
    }

    /* Upper jaw tooltip - appears above tooth, needs extra space to clear the badge above */
    .tooth-item.upper-jaw .tooth-tooltip {
        bottom: calc(100% + 2.75rem);
    }

    /* Lower jaw tooltip - appears below tooth, needs extra space to clear the badge below */
    .tooth-item.lower-jaw .tooth-tooltip {
        top: calc(100% + 2.75rem);
    }

    .tooth-visual:hover .tooth-tooltip {
        opacity: 1;
        pointer-events: auto;
    }

    .tooltip-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.25rem;
    }

    .tooltip-condition {
        font-size: 0.75rem;
        color: #374151;
        margin-bottom: 0.25rem;
        font-weight: 500;
    }

    .tooltip-date {
        font-size: 0.7rem;
        color: #9ca3af;
        margin-top: 0.5rem;
        font-style: italic;
        border-top: 1px solid #f3f4f6;
        padding-top: 0.25rem;
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
    .condition-periodontal { background: #ec4899; }
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

        .teeth-container {
            flex-direction: column;
            gap: 1rem;
        }
    }

    @media (max-width: 768px) {
        .teeth-grid {
            gap: 0.5rem;
        }

        .tooth-svg {
            width: 38px;
            height: 58px;
        }
    }
</style>
@endpush

@section('content')
<div class="dental-chart-wrapper">
    <div class="dental-chart-card">
        <div class="chart-saving-overlay" id="chartSavingOverlay" aria-hidden="true">
            <div class="chart-saving-panel">
                <div class="chart-saving-icon">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div class="chart-saving-text">
                    <div class="chart-saving-title" id="chartSavingTitle">Saving changes...</div>
                    <div class="chart-saving-detail" id="chartSavingDetail">Please wait while the chart is updated.</div>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="dental-chart-header">
            <div class="dental-chart-title">
                <i class="fas fa-tooth"></i>
                <span>Dental Chart Visualization</span>
            </div>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.location.href='{{ route('dental.charts.show', ['patient' => $patient, 'dentalChart' => $dentalChart, 'view' => 'detailed']) }}'">
                    <i class="fas fa-eye me-1"></i> Detailed View
                </button>
                <div style="display: flex; gap: 0.5rem;">
                    <button style="background: none; border: none; color: #6b7280; cursor: pointer; padding: 0.5rem;">
                        <i class="fas fa-star"></i>
                    </button>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px;"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="dental-chart-body">
            <!-- Sidebar Legend -->
            <div class="dental-sidebar">
                <div class="legend-header">
                    <div class="legend-title">Condition Legend</div>
                    <button class="legend-toggle">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <div class="legend-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search" id="legendSearch">
                    <i class="fas fa-filter legend-filter-icon"></i>
                </div>

                <div class="legend-items">
                    @php
                        $conditionIndicatorPositions = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
                        $normalizeConditions = function ($record) {
                            if (!$record) {
                                return [];
                            }

                            $conditions = is_array($record->conditions) ? $record->conditions : [];

                            if (empty($conditions) && !empty($record->primary_condition)) {
                                $conditions = [$record->primary_condition];
                            }

                            $conditions = array_values(array_unique(array_filter($conditions, function ($condition) {
                                return $condition !== null && $condition !== '';
                            })));

                            if (count($conditions) > 1) {
                                $conditions = array_values(array_filter($conditions, function ($condition) {
                                    return $condition !== 'healthy';
                                }));
                            }

                            if (empty($conditions) && !empty($record->primary_condition)) {
                                $conditions = [$record->primary_condition];
                            }

                            return $conditions;
                        };

                        $conditionCounts = [];
                        foreach($dentalChart->toothRecords as $record) {
                            foreach($normalizeConditions($record) as $conditionKey) {
                                $conditionCounts[$conditionKey] = ($conditionCounts[$conditionKey] ?? 0) + 1;
                            }
                        }
                    @endphp

                    @foreach(\App\Models\DentalToothRecord::CONDITIONS as $key => $condition)
                        <div class="legend-item" data-condition="{{ $key }}">
                            <div class="legend-item-left">
                                <div class="legend-checkbox" onclick="toggleLegendCheckbox(this)"></div>
                                <div class="legend-color condition-{{ $key }}"></div>
                                <div class="legend-label">{{ $condition['name'] }}</div>
                            </div>
                            @if(isset($conditionCounts[$key]))
                                <div class="legend-count">{{ $conditionCounts[$key] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Main Chart Area -->
            <div class="dental-main">
                <!-- Toolbar -->
                <div class="dental-toolbar">
                    <div class="condition-selector">
                        <i class="fas fa-search"></i>
                        <span>Select Condition</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>

                    <button id="bulkApplyBtn" class="apply-btn" style="background-color: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.2s; opacity: 0.5;" disabled>
                        <i class="fas fa-check"></i> Apply
                    </button>

                    <button id="bulkClearBtn" class="clear-btn" style="background-color: #ef4444; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.2s; opacity: 0.5; margin-left: 0.5rem;" disabled>
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>

                <!-- Selection Counter Bar -->
                <div class="selection-counter-bar" style="display: none;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span class="selection-count">
                            <i class="fas fa-tooth"></i>
                            <strong>0</strong> teeth selected
                        </span>
                        <button class="deselect-all-btn" onclick="deselectAllTeeth()">
                            <i class="fas fa-times-circle"></i> Deselect All
                        </button>
                    </div>
                    <div class="keyboard-hint">
                        <i class="fas fa-keyboard"></i>
                        Press <kbd>Esc</kbd> to deselect
                    </div>
                </div>

                @php
                    $toothNumbers = $dentalChart->tooth_numbers;
                    $toothRecords = $dentalChart->toothRecords->keyBy('tooth_number');
                @endphp

                <!-- Tooth Details Modal/Drawer -->
                <div id="toothDetailsDrawer" class="tooth-drawer" style="display: none;">
                    <div class="drawer-overlay" onclick="closeToothDrawer()"></div>
                    <div class="drawer-content">
                        <div class="drawer-header" style="display: flex !important; visibility: visible !important; opacity: 1 !important; height: 80px !important; min-height: 80px !important; max-height: 80px !important; flex-shrink: 0 !important; padding: 24px !important; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; color: white !important; width: 100% !important; position: relative !important; z-index: 100 !important; box-sizing: border-box !important; align-items: center !important; justify-content: space-between !important;">
                            <h3 class="drawer-title" style="display: flex !important; visibility: visible !important; opacity: 1 !important; margin: 0 !important; font-size: 20px !important; line-height: 32px !important; color: white !important; align-items: center !important; gap: 12px !important; flex: 1 !important; height: 32px !important;">
                                <i class="fas fa-tooth" style="color: white !important; font-size: 24px !important; display: inline-block !important;"></i>
                                <span id="drawerToothNumber" style="color: white !important; font-weight: 600 !important; display: inline-block !important;">Tooth Details</span>
                            </h3>
                            <div class="drawer-header-actions" style="display: flex !important; visibility: visible !important; opacity: 1 !important; gap: 8px !important; align-items: center !important; height: 40px !important;">
                                <button class="drawer-action-btn" id="editModeBtn" onclick="toggleEditMode()" title="Edit Details" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important; background: rgba(255, 255, 255, 0.2) !important; border: none !important; color: white !important; padding: 0 16px !important; height: 40px !important; border-radius: 8px !important; cursor: pointer !important; align-items: center !important; justify-content: center !important; white-space: nowrap !important;">
                                    <i class="fas fa-edit" style="color: white !important; display: inline-block !important;"></i>
                                    <span style="margin-left: 6px; font-size: 13px; font-weight: 500; color: white !important; display: inline-block !important;">Edit</span>
                                </button>
                                <button class="drawer-close-btn" id="drawerCloseBtn" onclick="closeToothDrawer()" title="Close" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important; background: rgba(255, 255, 255, 0.2) !important; border: none !important; color: white !important; width: 40px !important; height: 40px !important; border-radius: 8px !important; cursor: pointer !important; align-items: center !important; justify-content: center !important;">
                                    <i class="fas fa-times" style="color: white !important; font-size: 18px !important; display: inline-block !important;"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <div class="drawer-tabs">
                            <button class="drawer-tab active" onclick="switchDrawerTab('details')" data-tab="details">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                            <button class="drawer-tab" onclick="switchDrawerTab('history')" data-tab="history">
                                <i class="fas fa-history"></i> History
                            </button>
                        </div>

                        <div class="drawer-body" id="drawerBody">
                            <!-- Content will be populated by JavaScript -->
                        </div>

                        <!-- Save/Cancel buttons for edit mode -->
                        <div class="drawer-footer" id="drawerFooter" style="display: none;">
                            <button class="btn-cancel" id="drawerCancelBtn" onclick="cancelEdit()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button class="btn-save" id="drawerSaveBtn" onclick="saveToothDetails()">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Shared SVG Gradient Definitions -->
                <svg width="0" height="0" style="position:absolute;">
                    <defs>
                        <linearGradient id="toothGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#F4F6F9;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#E9EDF2;stop-opacity:1" />
                        </linearGradient>
                        <radialGradient id="highlightGradient" cx="35%" cy="25%">
                            <stop offset="0%" style="stop-color:#FFFFFF;stop-opacity:0.8" />
                            <stop offset="100%" style="stop-color:#FFFFFF;stop-opacity:0" />
                        </radialGradient>
                        <linearGradient id="shadowGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#000000;stop-opacity:0" />
                            <stop offset="70%" style="stop-color:#000000;stop-opacity:0.1" />
                            <stop offset="100%" style="stop-color:#000000;stop-opacity:0.15" />
                        </linearGradient>
                        <linearGradient id="toothGradientSelected" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#DBEAFE;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#BFDBFE;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#93C5FD;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>

                <!-- Upper Jaw -->
                <div class="jaw-section">
                    <div class="jaw-header">
                        <div class="jaw-label">Upper Jaw</div>
                        <div class="jaw-label">Upper Jaw</div>
                    </div>

                    <div class="teeth-container">
                        <!-- Upper Right -->
                        <div class="teeth-grid">
                            @foreach(array_reverse($toothNumbers['upper_right']) as $toothNum)
                                @php
                                    $record = $toothRecords->get($toothNum);
                                    $conditionsToShow = $record ? $normalizeConditions($record) : [];
                                    $hasCondition = count($conditionsToShow) > 0;
                                    $conditionsJson = json_encode($conditionsToShow);
                                @endphp
                                <div class="tooth-item upper-jaw">
                                    <div class="tooth-number-badge {{ $hasCondition ? 'has-condition' : '' }}">
                                        {{ $toothNum }}
                                    </div>
                                    <div class="tooth-visual" onclick="selectTooth(this)">
                                        <svg class="tooth-svg" viewBox="0 0 48 80">
                                            <path class="tooth-shape-main" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                            <ellipse class="tooth-highlight" cx="20" cy="22" rx="6" ry="8"/>
                                            <path class="tooth-shadow" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                        </svg>
                                        @if($hasCondition)
                                            @foreach($conditionsToShow as $index => $condition)
                                                @if($index < count($conditionIndicatorPositions))
                                                    @php
                                                        $conditionLabel = \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition);
                                                    @endphp
                                                    @if($condition === 'healthy')
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             title="{{ $conditionLabel }}"></div>
                                                    @else
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             onclick="removeCondition(event, '{{ $toothNum }}', '{{ $condition }}', '{{ addslashes($conditionsJson) }}')"
                                                             title="Click to remove {{ $conditionLabel }}"></div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="tooth-info-btn" onclick="event.stopPropagation(); openToothDrawer('{{ $toothNum }}');" title="View Details">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="tooth-tooltip">
                                            <div class="tooltip-title">Tooth #{{ $toothNum }}</div>
                                            @if($hasCondition)
                                                @foreach($conditionsToShow as $condition)
                                                    <div class="tooltip-condition">{{ \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition) }}</div>
                                                @endforeach
                                            @elseif($record)
                                                <div class="tooltip-condition">{{ $record->primary_condition_display }}</div>
                                            @else
                                                <div class="tooltip-condition">Healthy</div>
                                            @endif
                                            @if($record)
                                                @if($record->notes)
                                                    <div class="tooltip-condition">{{ Str::limit($record->notes, 40) }}</div>
                                                @endif
                                                <div class="tooltip-date">Last updated: {{ $record->updated_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="tooth-checkbox" onclick="toggleToothCheckbox(this)"></div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Upper Left -->
                        <div class="teeth-grid">
                            @foreach($toothNumbers['upper_left'] as $toothNum)
                                @php
                                    $record = $toothRecords->get($toothNum);
                                    $conditionsToShow = $record ? $normalizeConditions($record) : [];
                                    $hasCondition = count($conditionsToShow) > 0;
                                    $conditionsJson = json_encode($conditionsToShow);
                                @endphp
                                <div class="tooth-item upper-jaw">
                                    <div class="tooth-number-badge {{ $hasCondition ? 'has-condition' : '' }}">
                                        {{ $toothNum }}
                                    </div>
                                    <div class="tooth-visual" onclick="selectTooth(this)">
                                        <svg class="tooth-svg" viewBox="0 0 48 80">
                                            <path class="tooth-shape-main" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                            <ellipse class="tooth-highlight" cx="20" cy="22" rx="6" ry="8"/>
                                            <path class="tooth-shadow" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                        </svg>
                                        @if($hasCondition)
                                            @foreach($conditionsToShow as $index => $condition)
                                                @if($index < count($conditionIndicatorPositions))
                                                    @php
                                                        $conditionLabel = \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition);
                                                    @endphp
                                                    @if($condition === 'healthy')
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             title="{{ $conditionLabel }}"></div>
                                                    @else
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             onclick="removeCondition(event, '{{ $toothNum }}', '{{ $condition }}', '{{ addslashes($conditionsJson) }}')"
                                                             title="Click to remove {{ $conditionLabel }}"></div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="tooth-info-btn" onclick="event.stopPropagation(); openToothDrawer('{{ $toothNum }}');" title="View Details">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="tooth-tooltip">
                                            <div class="tooltip-title">Tooth #{{ $toothNum }}</div>
                                            @if($hasCondition)
                                                @foreach($conditionsToShow as $condition)
                                                    <div class="tooltip-condition">{{ \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition) }}</div>
                                                @endforeach
                                            @elseif($record)
                                                <div class="tooltip-condition">{{ $record->primary_condition_display }}</div>
                                            @else
                                                <div class="tooltip-condition">Healthy</div>
                                            @endif
                                            @if($record)
                                                @if($record->notes)
                                                    <div class="tooltip-condition">{{ Str::limit($record->notes, 40) }}</div>
                                                @endif
                                                <div class="tooltip-date">Last updated: {{ $record->updated_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="tooth-checkbox" onclick="toggleToothCheckbox(this)"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Lower Jaw -->
                <div class="jaw-section">
                    <div class="jaw-header">
                        <div class="jaw-label">Lower Jaw</div>
                        <div class="jaw-label">Lower Jaw</div>
                    </div>

                    <div class="teeth-container">
                        <!-- Lower Right -->
                        <div class="teeth-grid">
                            @foreach(array_reverse($toothNumbers['lower_right']) as $toothNum)
                                @php
                                    $record = $toothRecords->get($toothNum);
                                    $conditionsToShow = $record ? $normalizeConditions($record) : [];
                                    $hasCondition = count($conditionsToShow) > 0;
                                    $conditionsJson = json_encode($conditionsToShow);
                                @endphp
                                <div class="tooth-item lower-jaw">
                                    <div class="tooth-checkbox" onclick="toggleToothCheckbox(this)"></div>
                                    <div class="tooth-visual" onclick="selectTooth(this)">
                                        <svg class="tooth-svg" viewBox="0 0 48 80">
                                            <path class="tooth-shape-main" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                            <ellipse class="tooth-highlight" cx="20" cy="22" rx="6" ry="8"/>
                                            <path class="tooth-shadow" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                        </svg>
                                        @if($hasCondition)
                                            @foreach($conditionsToShow as $index => $condition)
                                                @if($index < count($conditionIndicatorPositions))
                                                    @php
                                                        $conditionLabel = \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition);
                                                    @endphp
                                                    @if($condition === 'healthy')
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             title="{{ $conditionLabel }}"></div>
                                                    @else
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             onclick="removeCondition(event, '{{ $toothNum }}', '{{ $condition }}', '{{ addslashes($conditionsJson) }}')"
                                                             title="Click to remove {{ $conditionLabel }}"></div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="tooth-info-btn" onclick="event.stopPropagation(); openToothDrawer('{{ $toothNum }}');" title="View Details">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="tooth-tooltip">
                                            <div class="tooltip-title">Tooth #{{ $toothNum }}</div>
                                            @if($hasCondition)
                                                @foreach($conditionsToShow as $condition)
                                                    <div class="tooltip-condition">{{ \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition) }}</div>
                                                @endforeach
                                            @elseif($record)
                                                <div class="tooltip-condition">{{ $record->primary_condition_display }}</div>
                                            @else
                                                <div class="tooltip-condition">Healthy</div>
                                            @endif
                                            @if($record)
                                                @if($record->notes)
                                                    <div class="tooltip-condition">{{ Str::limit($record->notes, 40) }}</div>
                                                @endif
                                                <div class="tooltip-date">Last updated: {{ $record->updated_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="tooth-number-badge {{ $hasCondition ? 'has-condition' : '' }}">
                                        {{ $toothNum }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Lower Left -->
                        <div class="teeth-grid">
                            @foreach($toothNumbers['lower_left'] as $toothNum)
                                @php
                                    $record = $toothRecords->get($toothNum);
                                    $conditionsToShow = $record ? $normalizeConditions($record) : [];
                                    $hasCondition = count($conditionsToShow) > 0;
                                    $conditionsJson = json_encode($conditionsToShow);
                                @endphp
                                <div class="tooth-item lower-jaw">
                                    <div class="tooth-checkbox" onclick="toggleToothCheckbox(this)"></div>
                                    <div class="tooth-visual" onclick="selectTooth(this)">
                                        <svg class="tooth-svg" viewBox="0 0 48 80">
                                            <path class="tooth-shape-main" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                            <ellipse class="tooth-highlight" cx="20" cy="22" rx="6" ry="8"/>
                                            <path class="tooth-shadow" d="M 24 8 C 20 8 16 11 16 16 C 16 20 17 24 17 28 L 17 38 C 17 40 16 42 14 46 C 12 50 12 54 14 58 C 15 60 17 62 19 64 L 22 74 C 22 76 23 78 24 78 C 25 78 26 76 26 74 L 29 64 C 31 62 33 60 34 58 C 36 54 36 50 34 46 C 32 42 31 40 31 38 L 31 28 C 31 24 32 20 32 16 C 32 11 28 8 24 8 Z"/>
                                        </svg>
                                        @if($hasCondition)
                                            @foreach($conditionsToShow as $index => $condition)
                                                @if($index < count($conditionIndicatorPositions))
                                                    @php
                                                        $conditionLabel = \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition);
                                                    @endphp
                                                    @if($condition === 'healthy')
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             title="{{ $conditionLabel }}"></div>
                                                    @else
                                                        <div class="condition-indicator {{ $conditionIndicatorPositions[$index] }} condition-{{ $condition }}"
                                                             onclick="removeCondition(event, '{{ $toothNum }}', '{{ $condition }}', '{{ addslashes($conditionsJson) }}')"
                                                             title="Click to remove {{ $conditionLabel }}"></div>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                        <div class="tooth-info-btn" onclick="event.stopPropagation(); openToothDrawer('{{ $toothNum }}');" title="View Details">
                                            <i class="fas fa-info"></i>
                                        </div>
                                        <div class="tooth-tooltip">
                                            <div class="tooltip-title">Tooth #{{ $toothNum }}</div>
                                            @if($hasCondition)
                                                @foreach($conditionsToShow as $condition)
                                                    <div class="tooltip-condition">{{ \App\Models\DentalToothRecord::CONDITIONS[$condition]['name'] ?? ucfirst($condition) }}</div>
                                                @endforeach
                                            @elseif($record)
                                                <div class="tooltip-condition">{{ $record->primary_condition_display }}</div>
                                            @else
                                                <div class="tooltip-condition">Healthy</div>
                                            @endif
                                            @if($record)
                                                @if($record->notes)
                                                    <div class="tooltip-condition">{{ Str::limit($record->notes, 40) }}</div>
                                                @endif
                                                <div class="tooltip-date">Last updated: {{ $record->updated_at->format('M d, Y') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="tooth-number-badge {{ $hasCondition ? 'has-condition' : '' }}">
                                        {{ $toothNum }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Global state for dental chart
    const dentalChartState = {
        selectedTeeth: new Set(),
        selectedConditions: new Set(), // Support multiple conditions
        patientId: {{ $patient->id }},
        chartId: {{ $dentalChart->id }},
        isSaving: false
    };

    function cacheButtonHtml(button) {
        if (button && !button.dataset.defaultHtml) {
            button.dataset.defaultHtml = button.innerHTML;
        }
    }

    function setButtonBusyState(button, isBusy, busyHtml = '<i class="fas fa-spinner fa-spin"></i> Working...') {
        if (!button) return;

        cacheButtonHtml(button);
        button.disabled = isBusy;

        if (isBusy) {
            button.innerHTML = busyHtml;
        } else {
            button.innerHTML = button.dataset.defaultHtml;
        }
    }

    function setChartSavingOverlay(isActive, title = 'Saving changes...', detail = 'Please wait while the chart is updated.') {
        const overlay = document.getElementById('chartSavingOverlay');
        const titleEl = document.getElementById('chartSavingTitle');
        const detailEl = document.getElementById('chartSavingDetail');

        if (titleEl) {
            titleEl.textContent = title;
        }

        if (detailEl) {
            detailEl.textContent = detail;
        }

        if (overlay) {
            overlay.classList.toggle('active', isActive);
            overlay.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        }
    }

    function setDrawerBusyState(isBusy, saveLabel = '<i class="fas fa-spinner fa-spin"></i> Saving...') {
        const saveBtn = document.getElementById('drawerSaveBtn');
        const cancelBtn = document.getElementById('drawerCancelBtn');
        const editBtn = document.getElementById('editModeBtn');
        const closeBtn = document.getElementById('drawerCloseBtn');

        setButtonBusyState(saveBtn, isBusy, saveLabel);

        [cancelBtn, editBtn, closeBtn].forEach(button => {
            if (!button) return;
            button.disabled = isBusy;
        });
    }

    function isInteractionLocked() {
        return dentalChartState.isSaving;
    }

    // Legend filter state: track which conditions are checked
    const legendFilterState = {
        activeConditions: new Set()
    };

    // Toggle legend checkbox and filter teeth on the chart
    function toggleLegendCheckbox(element) {
        if (isInteractionLocked()) return;
        element.classList.toggle('checked');
        element.closest('.legend-item').classList.toggle('active');

        const conditionKey = element.closest('.legend-item').getAttribute('data-condition');
        if (!conditionKey) return;

        if (element.classList.contains('checked')) {
            legendFilterState.activeConditions.add(conditionKey);
        } else {
            legendFilterState.activeConditions.delete(conditionKey);
        }

        applyLegendFilter();
    }

    // Apply legend filter: highlight matching teeth, dim the rest
    function applyLegendFilter() {
        const allTeeth = document.querySelectorAll('.tooth-item');
        const activeConditions = legendFilterState.activeConditions;

        // If no conditions checked, reset all teeth to normal
        if (activeConditions.size === 0) {
            allTeeth.forEach(tooth => {
                tooth.classList.remove('legend-dimmed', 'legend-highlighted');
            });
            return;
        }

        allTeeth.forEach(tooth => {
            const indicators = tooth.querySelectorAll('.condition-indicator');
            const toothConditions = new Set();

            indicators.forEach(indicator => {
                const classes = indicator.className.split(' ');
                const condClass = classes.find(c => c.startsWith('condition-') && c !== 'condition-indicator');
                if (condClass) {
                    toothConditions.add(condClass.replace('condition-', ''));
                }
            });

            // Teeth with no condition indicators are implicitly healthy
            if (toothConditions.size === 0) {
                toothConditions.add('healthy');
            }

            // Check if this tooth has any of the active filter conditions
            let matches = false;
            for (const cond of activeConditions) {
                if (toothConditions.has(cond)) {
                    matches = true;
                    break;
                }
            }

            if (matches) {
                tooth.classList.add('legend-highlighted');
                tooth.classList.remove('legend-dimmed');
            } else {
                tooth.classList.add('legend-dimmed');
                tooth.classList.remove('legend-highlighted');
            }
        });
    }

    // Toggle tooth checkbox
    function toggleToothCheckbox(element) {
        if (isInteractionLocked()) return;
        event.stopPropagation();
        element.classList.toggle('checked');

        // Get the tooth number from the parent tooth-item
        const toothItem = element.closest('.tooth-item');
        const toothBadge = toothItem.querySelector('.tooth-number-badge');
        const toothNum = toothBadge.textContent.trim();

        if (element.classList.contains('checked')) {
            dentalChartState.selectedTeeth.add(toothNum);
        } else {
            dentalChartState.selectedTeeth.delete(toothNum);
        }

        updateApplyButtonState();
    }

    // Select tooth and open drawer
    let lastClickTime = 0;
    let lastClickedTooth = null;

    function selectTooth(element) {
        if (isInteractionLocked()) return;
        event.stopPropagation();

        // Get the tooth number
        const toothItem = element.closest('.tooth-item');
        const toothBadge = toothItem.querySelector('.tooth-number-badge');
        const toothNum = toothBadge.textContent.trim();

        // Detect double-click to open drawer
        const currentTime = new Date().getTime();
        const timeDiff = currentTime - lastClickTime;

        if (timeDiff < 300 && lastClickedTooth === toothNum) {
            // Double-click detected - open drawer
            openToothDrawer(toothNum);
            lastClickTime = 0;
            lastClickedTooth = null;
            return;
        }

        lastClickTime = currentTime;
        lastClickedTooth = toothNum;

        // Single click - toggle selection
        element.classList.toggle('selected');

        if (element.classList.contains('selected')) {
            dentalChartState.selectedTeeth.add(toothNum);
            // Also check the checkbox
            const checkbox = toothItem.querySelector('.tooth-checkbox');
            if (checkbox && !checkbox.classList.contains('checked')) {
                checkbox.classList.add('checked');
            }
        } else {
            dentalChartState.selectedTeeth.delete(toothNum);
            // Also uncheck the checkbox
            const checkbox = toothItem.querySelector('.tooth-checkbox');
            if (checkbox && checkbox.classList.contains('checked')) {
                checkbox.classList.remove('checked');
            }
        }

        updateApplyButtonState();
    }

    // Update condition selector - toggle condition in/out of selection
    function updateConditionSelector(condition) {
        if (isInteractionLocked()) return;
        if (dentalChartState.selectedConditions.has(condition)) {
            dentalChartState.selectedConditions.delete(condition);
        } else {
            dentalChartState.selectedConditions.add(condition);
        }
        updateConditionSelectorDisplay();
        updateApplyButtonState();
    }

    // Update condition selector display
    function updateConditionSelectorDisplay() {
        const selector = document.querySelector('.condition-selector');
        if (!selector) return;

        const count = dentalChartState.selectedConditions.size;
        let displayText = 'Select Condition(s)';

        if (count === 1) {
            const key = Array.from(dentalChartState.selectedConditions)[0];
            displayText = getConditionName(key);
        } else if (count > 1) {
            displayText = `${count} Conditions Selected`;
        }

        selector.innerHTML = `<i class="fas fa-search"></i><span>${displayText}</span><i class="fas fa-chevron-down"></i>`;
    }

    // Get condition name from CONDITIONS constant
    function getConditionName(key) {
        const conditions = {
            'healthy': 'Healthy',
            'caries': 'Caries (Cavity)',
            'filling': 'Filling (Restoration)',
            'crown': 'Crown',
            'root_canal': 'Root Canal Treatment',
            'extraction': 'Extraction (Missing)',
            'implant': 'Implant',
            'bridge': 'Bridge',
            'fracture': 'Fracture',
            'periodontal': 'Gingival/Periodontal Issue',
            'other': 'Other'
        };
        return conditions[key] || key;
    }

    // Update apply and clear button states + selection counter
    function updateApplyButtonState() {
        const applyBtn = document.getElementById('bulkApplyBtn');
        const clearBtn = document.getElementById('bulkClearBtn');
        const selectionBar = document.querySelector('.selection-counter-bar');
        const selectionCount = document.querySelector('.selection-count strong');

        const teethCount = dentalChartState.selectedTeeth.size;

        if (dentalChartState.isSaving) {
            if (applyBtn) {
                applyBtn.disabled = true;
                applyBtn.style.opacity = '0.6';
            }

            if (clearBtn) {
                clearBtn.disabled = true;
                clearBtn.style.opacity = '0.6';
            }

            if (selectionBar && selectionCount) {
                if (teethCount > 0) {
                    selectionBar.style.display = 'flex';
                    selectionCount.textContent = teethCount;
                } else {
                    selectionBar.style.display = 'none';
                }
            }

            return;
        }

        // Apply button: enabled when teeth AND conditions are selected
        if (applyBtn) {
            if (teethCount > 0 && dentalChartState.selectedConditions.size > 0) {
                applyBtn.disabled = false;
                applyBtn.style.opacity = '1';
            } else {
                applyBtn.disabled = true;
                applyBtn.style.opacity = '0.5';
            }
        }

        // Clear button: enabled when teeth are selected (regardless of conditions)
        if (clearBtn) {
            if (teethCount > 0) {
                clearBtn.disabled = false;
                clearBtn.style.opacity = '1';
            } else {
                clearBtn.disabled = true;
                clearBtn.style.opacity = '0.5';
            }
        }

        // Selection counter bar
        if (selectionBar && selectionCount) {
            if (teethCount > 0) {
                selectionBar.style.display = 'flex';
                selectionCount.textContent = teethCount;
            } else {
                selectionBar.style.display = 'none';
            }
        }
    }

    // Deselect all teeth
    function deselectAllTeeth() {
        if (isInteractionLocked()) return;
        dentalChartState.selectedTeeth.clear();
        document.querySelectorAll('.tooth-checkbox.checked').forEach(cb => cb.classList.remove('checked'));
        document.querySelectorAll('.tooth-visual.selected').forEach(tv => tv.classList.remove('selected'));
        updateApplyButtonState();
        showNotification('All teeth deselected', 'info');
    }

    // Apply condition to selected teeth
    async function applyCondition() {
        if (isInteractionLocked()) return;

        if (dentalChartState.selectedTeeth.size === 0) {
            showNotification('Please select at least one tooth', 'warning');
            return;
        }

        if (dentalChartState.selectedConditions.size === 0) {
            showNotification('Please select at least one condition', 'warning');
            return;
        }

        dentalChartState.isSaving = true;
        let completedSuccessfully = false;
        const applyBtn = document.getElementById('bulkApplyBtn');
        const clearBtn = document.getElementById('bulkClearBtn');
        setButtonBusyState(applyBtn, true, '<i class="fas fa-spinner fa-spin"></i> Applying...');
        if (clearBtn) {
            clearBtn.disabled = true;
            clearBtn.style.opacity = '0.6';
        }
        setChartSavingOverlay(
            true,
            'Applying conditions...',
            `Updating ${dentalChartState.selectedTeeth.size} selected tooth/teeth.`
        );
        updateApplyButtonState();

        try {
            const conditionsArray = Array.from(dentalChartState.selectedConditions);
            const primaryCondition = conditionsArray[0]; // First selected condition is primary

            const promises = Array.from(dentalChartState.selectedTeeth).map(toothNum => {
                return fetch(`/dental/patients/${dentalChartState.patientId}/charts/${dentalChartState.chartId}/tooth-record`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tooth_number: toothNum,
                        primary_condition: primaryCondition,
                        conditions: conditionsArray,
                        severity: 'moderate',
                        notes: ''
                    })
                });
            });

            const responses = await Promise.all(promises);
            const allSuccess = responses.every(r => r.ok);

            if (allSuccess) {
                completedSuccessfully = true;
                // Clear selections
                dentalChartState.selectedTeeth.clear();
                dentalChartState.selectedConditions.clear();
                updateConditionSelectorDisplay();
                document.querySelectorAll('.tooth-checkbox.checked').forEach(cb => cb.classList.remove('checked'));
                document.querySelectorAll('.tooth-visual.selected').forEach(tv => tv.classList.remove('selected'));

                // Show success message
                showNotification('Conditions applied successfully!', 'success');
                setButtonBusyState(applyBtn, true, '<i class="fas fa-check"></i> Applied');
                setChartSavingOverlay(true, 'Conditions applied', 'Refreshing the chart...');

                // Reload the page after a short delay
                setTimeout(() => location.reload(), 900);
            } else {
                showNotification('Failed to apply conditions. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error applying condition:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            if (!completedSuccessfully) {
                dentalChartState.isSaving = false;
                setChartSavingOverlay(false);
                setButtonBusyState(applyBtn, false);
                updateApplyButtonState();
            }
        }
    }

    // Enhanced notification system with icons
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;

        // Icon based on type
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-exclamation-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };

        // Colors based on type
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 18px;">${icons[type] || icons.info}</span>
                <span>${message}</span>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 14px 20px;
            background-color: ${colors[type] || colors.info};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            min-width: 250px;
            max-width: 400px;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Legend search functionality
    (function initLegendSearch() {
        const searchInput = document.getElementById('legendSearch');
        if (!searchInput) return;

        function filterLegend() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const legendItems = document.querySelectorAll('.legend-item');

            legendItems.forEach(item => {
                const labelEl = item.querySelector('.legend-label') || item.querySelector('.legend-name');
                if (!labelEl) return;
                const label = labelEl.textContent.toLowerCase();
                item.style.display = label.includes(searchTerm) ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterLegend);
        searchInput.addEventListener('keyup', filterLegend);
        searchInput.addEventListener('change', filterLegend);
    })();

    // Initialize condition selector dropdown with multi-select support
    document.querySelector('.condition-selector')?.addEventListener('click', function(e) {
        if (isInteractionLocked()) return;

        e.stopPropagation();

        const conditions = {
            'healthy': 'Healthy',
            'caries': 'Caries (Cavity)',
            'filling': 'Filling (Restoration)',
            'crown': 'Crown',
            'root_canal': 'Root Canal Treatment',
            'extraction': 'Extraction (Missing)',
            'implant': 'Implant',
            'bridge': 'Bridge',
            'fracture': 'Fracture',
            'periodontal': 'Gingival/Periodontal Issue',
            'other': 'Other'
        };

        const menu = document.createElement('div');
        menu.className = 'condition-dropdown-menu';
        menu.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 280px;
            margin-top: 4px;
            padding: 8px 0;
        `;

        // Add header
        const header = document.createElement('div');
        header.style.cssText = `
            padding: 8px 16px;
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 4px;
        `;
        header.textContent = 'Select Conditions (Multiple)';
        menu.appendChild(header);

        Object.entries(conditions).forEach(([key, name]) => {
            const item = document.createElement('div');
            item.style.cssText = `
                padding: 10px 16px;
                cursor: pointer;
                transition: background-color 0.15s;
                display: flex;
                align-items: center;
                gap: 10px;
            `;

            // Checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = dentalChartState.selectedConditions.has(key);
            checkbox.style.cssText = `
                width: 16px;
                height: 16px;
                cursor: pointer;
                accent-color: #3b82f6;
            `;

            // Label
            const label = document.createElement('span');
            label.textContent = name;
            label.style.cssText = `
                flex: 1;
                cursor: pointer;
                user-select: none;
            `;

            item.appendChild(checkbox);
            item.appendChild(label);

            item.onmouseover = () => item.style.backgroundColor = '#f9fafb';
            item.onmouseout = () => item.style.backgroundColor = 'transparent';

            item.onclick = (e) => {
                e.stopPropagation();
                checkbox.checked = !checkbox.checked;
                updateConditionSelector(key);
            };

            menu.appendChild(item);
        });

        // Add "Done" button
        const doneBtn = document.createElement('div');
        doneBtn.textContent = 'Done';
        doneBtn.style.cssText = `
            margin: 8px 16px 4px;
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.15s;
        `;
        doneBtn.onmouseover = () => doneBtn.style.backgroundColor = '#2563eb';
        doneBtn.onmouseout = () => doneBtn.style.backgroundColor = '#3b82f6';
        doneBtn.onclick = (e) => {
            e.stopPropagation();
            menu.remove();
        };
        menu.appendChild(doneBtn);

        // Position relative to selector
        const selector = document.querySelector('.condition-selector');
        selector.parentElement.style.position = 'relative';
        selector.parentElement.appendChild(menu);

        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!selector.contains(e.target) && !menu.contains(e.target)) {
                    menu.remove();
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 0);
    });

    // Remove individual condition from a tooth
    async function removeCondition(event, toothNumber, conditionToRemove, allConditionsJson) {
        event.stopPropagation(); // Prevent tooth selection

        if (isInteractionLocked()) return;

        const conditionName = getConditionName(conditionToRemove);

        if (!confirm(`Remove ${conditionName} from tooth #${toothNumber}?`)) {
            return;
        }

        try {
            // Parse all current conditions from the passed JSON
            let allConditions = [];
            try {
                allConditions = JSON.parse(allConditionsJson);
            } catch (e) {
                console.error('Failed to parse conditions:', e);
                // Fallback: get from DOM
                const toothElement = Array.from(document.querySelectorAll('.tooth-number-badge'))
                    .find(badge => badge.textContent.trim() === toothNumber)
                    ?.closest('.tooth-item');

                if (toothElement) {
                    const conditionIndicators = toothElement.querySelectorAll('.condition-indicator');
                    allConditions = Array.from(conditionIndicators).map(indicator => {
                        const classes = indicator.className.split(' ');
                        const conditionClass = classes.find(c => c.startsWith('condition-'));
                        return conditionClass ? conditionClass.replace('condition-', '') : null;
                    }).filter(c => c);
                }
            }

            // Remove the specific condition
            const newConditions = allConditions.filter(c => c !== conditionToRemove);

            // If no conditions left, set to healthy
            if (newConditions.length === 0) {
                newConditions.push('healthy');
            }

            const newPrimaryCondition = newConditions[0];

            // Update the tooth record
            const updateResponse = await fetch(`/dental/patients/${dentalChartState.patientId}/charts/${dentalChartState.chartId}/tooth-record`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tooth_number: toothNumber,
                    primary_condition: newPrimaryCondition,
                    conditions: newConditions,
                    severity: null,
                    notes: ''
                })
            });

            if (updateResponse.ok) {
                showNotification(`${conditionName} removed successfully!`, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                const errorData = await updateResponse.json();
                console.error('Server error:', errorData);
                showNotification('Failed to remove condition. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error removing condition:', error);
            showNotification('An error occurred. Please try again.', 'error');
        }
    }

    // Clear conditions from selected teeth
    async function clearConditions() {
        if (isInteractionLocked()) return;

        if (dentalChartState.selectedTeeth.size === 0) {
            showNotification('Please select at least one tooth', 'warning');
            return;
        }

        if (!confirm(`Are you sure you want to clear all conditions from ${dentalChartState.selectedTeeth.size} tooth/teeth?`)) {
            return;
        }

        dentalChartState.isSaving = true;
        let completedSuccessfully = false;
        const clearBtn = document.getElementById('bulkClearBtn');
        const applyBtn = document.getElementById('bulkApplyBtn');
        setButtonBusyState(clearBtn, true, '<i class="fas fa-spinner fa-spin"></i> Clearing...');
        if (applyBtn) {
            applyBtn.disabled = true;
            applyBtn.style.opacity = '0.6';
        }
        setChartSavingOverlay(
            true,
            'Clearing conditions...',
            `Resetting ${dentalChartState.selectedTeeth.size} selected tooth/teeth to healthy.`
        );
        updateApplyButtonState();

        try {
            const promises = Array.from(dentalChartState.selectedTeeth).map(toothNum => {
                return fetch(`/dental/patients/${dentalChartState.patientId}/charts/${dentalChartState.chartId}/tooth-record`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        tooth_number: toothNum,
                        primary_condition: 'healthy',
                        conditions: ['healthy'],
                        severity: null,
                        notes: ''
                    })
                });
            });

            const responses = await Promise.all(promises);
            const allSuccess = responses.every(r => r.ok);

            if (allSuccess) {
                completedSuccessfully = true;
                // Clear selections
                dentalChartState.selectedTeeth.clear();
                dentalChartState.selectedConditions.clear();
                updateConditionSelectorDisplay();
                document.querySelectorAll('.tooth-checkbox.checked').forEach(cb => cb.classList.remove('checked'));
                document.querySelectorAll('.tooth-visual.selected').forEach(tv => tv.classList.remove('selected'));

                // Show success message
                showNotification('Conditions cleared successfully!', 'success');
                setButtonBusyState(clearBtn, true, '<i class="fas fa-check"></i> Cleared');
                setChartSavingOverlay(true, 'Conditions cleared', 'Refreshing the chart...');

                // Reload page to show updated chart
                setTimeout(() => {
                    window.location.reload();
                }, 900);
            } else {
                showNotification('Failed to clear some conditions. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Error clearing conditions:', error);
            showNotification('An error occurred. Please try again.', 'error');
        } finally {
            if (!completedSuccessfully) {
                dentalChartState.isSaving = false;
                setChartSavingOverlay(false);
                setButtonBusyState(clearBtn, false);
                updateApplyButtonState();
            }
        }
    }

    // Initialize apply button
    document.getElementById('bulkApplyBtn')?.addEventListener('click', applyCondition);

    // Initialize clear button
    document.getElementById('bulkClearBtn')?.addEventListener('click', clearConditions);

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (isInteractionLocked()) {
            return;
        }

        // Escape key - deselect all teeth
        if (e.key === 'Escape' || e.key === 'Esc') {
            if (dentalChartState.selectedTeeth.size > 0) {
                deselectAllTeeth();
            }
        }

        // Ctrl/Cmd + A - select all teeth (prevent default browser select all)
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            selectAllTeeth();
        }

        // Delete/Backspace - clear conditions from selected teeth
        if ((e.key === 'Delete' || e.key === 'Backspace') && dentalChartState.selectedTeeth.size > 0) {
            // Only if not typing in an input field
            if (!e.target.matches('input, textarea')) {
                e.preventDefault();
                if (confirm(`Clear conditions from ${dentalChartState.selectedTeeth.size} selected teeth?`)) {
                    clearConditions();
                }
            }
        }
    });

    // Select all teeth function
    function selectAllTeeth() {
        if (isInteractionLocked()) return;

        const allTeethElements = document.querySelectorAll('.tooth-item');
        allTeethElements.forEach(toothItem => {
            const badge = toothItem.querySelector('.tooth-number-badge');
            const checkbox = toothItem.querySelector('.tooth-checkbox');
            const visual = toothItem.querySelector('.tooth-visual');

            if (badge && checkbox && visual) {
                const toothNum = badge.textContent.trim();
                dentalChartState.selectedTeeth.add(toothNum);
                checkbox.classList.add('checked');
                visual.classList.add('selected');
            }
        });
        updateApplyButtonState();
        showNotification(`All ${dentalChartState.selectedTeeth.size} teeth selected`, 'info');
    }

    // Initialize state
    updateApplyButtonState();

    // Tooth Details Drawer State
    let currentDrawerTooth = null;
    let currentDrawerMode = 'view'; // 'view' or 'edit'
    let currentDrawerTab = 'details'; // 'details' or 'history'
	    const DRAWER_DEBUG = false;
	    const dlog = (...args) => { if (DRAWER_DEBUG) console.log(...args); };

    // Tooth Details Drawer Functions
    function openToothDrawer(toothNumber) {
	        if (isInteractionLocked()) return;
	        dlog('Opening drawer for tooth:', toothNumber);

        currentDrawerTooth = toothNumber;
        currentDrawerMode = 'view';
        currentDrawerTab = 'details';

	        const drawer = document.getElementById('toothDetailsDrawer');
	        // Ensure the drawer is a direct child of <body> so it is not affected by
	        // any parent transforms/overflow/stacking contexts (common cause of “hidden header”).
	        if (drawer && drawer.parentElement !== document.body) {
	            document.body.appendChild(drawer);
	        }
        const drawerTitle = document.getElementById('drawerToothNumber');
        const editBtn = document.getElementById('editModeBtn');
        const drawerHeader = drawer.querySelector('.drawer-header');

	        dlog('Drawer elements:', { drawer, drawerTitle, editBtn, drawerHeader });

        // Set tooth number in title
        if (drawerTitle) {
            drawerTitle.textContent = `Tooth #${toothNumber}`;
	            dlog('Set drawer title to:', drawerTitle.textContent);
        }

        // Make sure header is visible
        if (drawerHeader) {
            drawerHeader.style.display = 'flex';
            drawerHeader.style.visibility = 'visible';
            drawerHeader.style.opacity = '1';
	            dlog('Header display set to flex');
        }

        // Reset tabs
        document.querySelectorAll('.drawer-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector('[data-tab="details"]').classList.add('active');

        // Reset edit button
        if (editBtn) {
            editBtn.classList.remove('active');
            editBtn.style.display = 'flex';
            editBtn.style.visibility = 'visible';
            editBtn.style.opacity = '1';
	            dlog('Edit button display set to flex');
        }

        // Hide footer
        document.getElementById('drawerFooter').style.display = 'none';

        // Load details tab
        loadDetailsTab(toothNumber);

        // Show drawer
        drawer.style.display = 'block';

        // Reset scroll position to top to ensure header is visible
        const drawerContent = drawer.querySelector('.drawer-content');
        if (drawerContent) {
            drawerContent.scrollTop = 0;
	            dlog('Drawer content scroll reset to:', drawerContent.scrollTop);
        }
        const drawerBody = document.getElementById('drawerBody');
        if (drawerBody) {
            drawerBody.scrollTop = 0;
        }

        document.body.style.overflow = 'hidden';

	        // Optional debug: enable by setting DRAWER_DEBUG = true
	        if (DRAWER_DEBUG) {
	            setTimeout(() => {
	                if (!drawerHeader) return;
	                const rect = drawerHeader.getBoundingClientRect();
	                const computedStyle = window.getComputedStyle(drawerHeader);
	                console.log('=== HEADER DEBUG ===');
	                console.log('Header rect:', rect);
	                console.log('Header computed position:', computedStyle.position);
	            }, 50);
	        }

	        dlog('Drawer opened successfully');
    }

    function loadDetailsTab(toothNumber) {
        const drawerBody = document.getElementById('drawerBody');
        const toothRecords = @json($toothRecords);
        const record = toothRecords[toothNumber];

        // Build drawer content based on mode
        let content = '';

        if (currentDrawerMode === 'view') {
            // VIEW MODE
            // Conditions Section
            content += '<div class="detail-section">';
            content += '<div class="detail-label">Conditions</div>';
            if (record && record.conditions && record.conditions.length > 0) {
                const conditions = @json(\App\Models\DentalToothRecord::CONDITIONS);
                record.conditions.forEach(condition => {
                    if (condition !== 'healthy' && conditions[condition]) {
                        const conditionData = conditions[condition];
                        content += `
                            <div class="condition-badge" style="background: ${conditionData.color}20; color: ${conditionData.color}; border: 1px solid ${conditionData.color};">
                                <span>${conditionData.icon}</span>
                                <span>${conditionData.name}</span>
                            </div>
                        `;
                    }
                });
            } else {
                content += '<div class="no-data">No conditions recorded (Healthy)</div>';
            }
            content += '</div>';
        } else {
            // EDIT MODE
            // Conditions Section
            content += '<div class="detail-section">';
            content += '<div class="detail-label">Conditions</div>';
            content += '<div class="checkbox-group">';
            const conditions = @json(\App\Models\DentalToothRecord::CONDITIONS);
            const currentConditions = record && record.conditions ? record.conditions : [];
            Object.keys(conditions).forEach(conditionKey => {
                if (conditionKey !== 'healthy') {
                    const conditionData = conditions[conditionKey];
                    const isChecked = currentConditions.includes(conditionKey);
                    content += `
                        <label class="checkbox-item ${isChecked ? 'checked' : ''}" onclick="toggleConditionCheckbox(this)">
                            <input type="checkbox" name="conditions[]" value="${conditionKey}" ${isChecked ? 'checked' : ''}>
                            <span>${conditionData.icon} ${conditionData.name}</span>
                        </label>
                    `;
                }
            });
            content += '</div>';
            content += '</div>';
        }

        // Surfaces Affected Section
        content += '<div class="detail-section">';
        content += '<div class="detail-label">Surfaces Affected</div>';
        if (currentDrawerMode === 'view') {
            if (record && record.surfaces_affected && record.surfaces_affected.length > 0) {
                content += '<div class="detail-value">' + record.surfaces_affected.join(', ').toUpperCase() + '</div>';
            } else {
                content += '<div class="no-data">No surfaces specified</div>';
            }
        } else {
            // Edit mode - checkboxes for surfaces
            content += '<div class="checkbox-group">';
            const surfaces = ['mesial', 'distal', 'occlusal', 'buccal', 'lingual'];
            const currentSurfaces = record && record.surfaces_affected ? record.surfaces_affected : [];
            surfaces.forEach(surface => {
                const isChecked = currentSurfaces.includes(surface);
                content += `
                    <label class="checkbox-item ${isChecked ? 'checked' : ''}" onclick="toggleConditionCheckbox(this)">
                        <input type="checkbox" name="surfaces[]" value="${surface}" ${isChecked ? 'checked' : ''}>
                        <span>${surface.charAt(0).toUpperCase() + surface.slice(1)}</span>
                    </label>
                `;
            });
            content += '</div>';
        }
        content += '</div>';

        // Severity Section
        content += '<div class="detail-section">';
        content += '<div class="detail-label">Severity</div>';
        if (currentDrawerMode === 'view') {
            if (record && record.severity) {
                const severityColors = {
                    'mild': '#10b981',
                    'moderate': '#f59e0b',
                    'severe': '#ef4444'
                };
                const color = severityColors[record.severity] || '#6b7280';
                content += `<div class="detail-value" style="color: ${color}; font-weight: 600;">${record.severity.toUpperCase()}</div>`;
            } else {
                content += '<div class="no-data">Not specified</div>';
            }
        } else {
            // Edit mode - dropdown
            const currentSeverity = record && record.severity ? record.severity : '';
            content += `
                <select class="edit-select" name="severity" id="severitySelect">
                    <option value="">Not specified</option>
                    <option value="mild" ${currentSeverity === 'mild' ? 'selected' : ''}>Mild</option>
                    <option value="moderate" ${currentSeverity === 'moderate' ? 'selected' : ''}>Moderate</option>
                    <option value="severe" ${currentSeverity === 'severe' ? 'selected' : ''}>Severe</option>
                </select>
            `;
        }
        content += '</div>';

        // Notes Section
        content += '<div class="detail-section">';
        content += '<div class="detail-label">Notes</div>';
        if (currentDrawerMode === 'view') {
            if (record && record.notes) {
                content += '<div class="detail-value">' + record.notes + '</div>';
            } else {
                content += '<div class="no-data">No notes</div>';
            }
        } else {
            // Edit mode - textarea
            const currentNotes = record && record.notes ? record.notes : '';
            content += `<textarea class="edit-textarea" name="notes" id="notesTextarea" placeholder="Enter treatment notes...">${currentNotes}</textarea>`;
        }
        content += '</div>';

        // Last Updated Section (only in view mode)
        if (currentDrawerMode === 'view') {
            content += '<div class="detail-section">';
            content += '<div class="detail-label">Last Updated</div>';
            if (record && record.updated_at) {
                const date = new Date(record.updated_at);
                content += '<div class="detail-value">' + date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) + '</div>';
            } else {
                content += '<div class="no-data">Never updated</div>';
            }
            content += '</div>';

            // Created By Section
            if (record && record.creator) {
                content += '<div class="detail-section">';
                content += '<div class="detail-label">Created By</div>';
                content += '<div class="detail-value">' + (record.creator.first_name + ' ' + record.creator.last_name) + '</div>';
                content += '</div>';
            }
        }

        drawerBody.innerHTML = content;
    }

    function loadHistoryTab(toothNumber) {
        const drawerBody = document.getElementById('drawerBody');
        const toothRecords = @json($toothRecords);
        const record = toothRecords[toothNumber];

        let content = '<div class="history-timeline">';

        // For now, show current record as history item
        // TODO: Fetch actual history from backend
        if (record && record.updated_at) {
            content += '<div class="history-item">';
            content += '<div class="history-dot"></div>';
            content += '<div class="history-date">' + new Date(record.updated_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) + '</div>';
            content += '<div class="history-action">Record Updated</div>';

            if (record.conditions && record.conditions.length > 0) {
                const conditions = @json(\App\Models\DentalToothRecord::CONDITIONS);
                content += '<div class="history-details">Conditions: ';
                const conditionNames = record.conditions
                    .filter(c => c !== 'healthy')
                    .map(c => conditions[c] ? conditions[c].name : c)
                    .join(', ');
                content += conditionNames || 'Healthy';
                content += '</div>';
            }

            if (record.creator) {
                content += '<div class="history-user">By: ' + record.creator.first_name + ' ' + record.creator.last_name + '</div>';
            }
            content += '</div>';
        }

        if (record && record.created_at && record.created_at !== record.updated_at) {
            content += '<div class="history-item">';
            content += '<div class="history-dot"></div>';
            content += '<div class="history-date">' + new Date(record.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) + '</div>';
            content += '<div class="history-action">Record Created</div>';
            if (record.creator) {
                content += '<div class="history-user">By: ' + record.creator.first_name + ' ' + record.creator.last_name + '</div>';
            }
            content += '</div>';
        }

        if (!record) {
            content += '<div class="no-data">No history available for this tooth</div>';
        }

        content += '</div>';
        drawerBody.innerHTML = content;
    }

    function switchDrawerTab(tabName) {
        if (isInteractionLocked()) return;
        currentDrawerTab = tabName;

        // Update tab buttons
        document.querySelectorAll('.drawer-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Load tab content
        if (tabName === 'details') {
            loadDetailsTab(currentDrawerTooth);
        } else if (tabName === 'history') {
            loadHistoryTab(currentDrawerTooth);
            // Hide edit button and footer when viewing history
            document.getElementById('editModeBtn').style.display = 'none';
            document.getElementById('drawerFooter').style.display = 'none';
        }

        // Show edit button only on details tab
        if (tabName === 'details') {
            document.getElementById('editModeBtn').style.display = 'flex';
        }
    }

    function toggleEditMode() {
        if (isInteractionLocked()) return;
        if (currentDrawerMode === 'view') {
            currentDrawerMode = 'edit';
            document.getElementById('editModeBtn').classList.add('active');
            document.getElementById('drawerFooter').style.display = 'flex';
            loadDetailsTab(currentDrawerTooth);
        } else {
            cancelEdit();
        }
    }

    function cancelEdit() {
        if (isInteractionLocked()) return;
        currentDrawerMode = 'view';
        document.getElementById('editModeBtn').classList.remove('active');
        document.getElementById('drawerFooter').style.display = 'none';
        loadDetailsTab(currentDrawerTooth);
    }

    function toggleConditionCheckbox(label) {
        if (isInteractionLocked()) return;
        const checkbox = label.querySelector('input[type="checkbox"]');
        if (checkbox.checked) {
            label.classList.add('checked');
        } else {
            label.classList.remove('checked');
        }
    }

    async function saveToothDetails() {
	        if (!currentDrawerTooth || isInteractionLocked()) return;

	        dentalChartState.isSaving = true;
	        let completedSuccessfully = false;
	        setDrawerBusyState(true);
	        updateApplyButtonState();

	        const drawer = document.getElementById('toothDetailsDrawer');
	        const scope = drawer || document;

	        // Collect form data (scoped to the drawer so we don't accidentally pick up other page checkboxes)
	        let conditions = Array.from(scope.querySelectorAll('input[name="conditions[]"]:checked'))
	            .map(cb => cb.value);

	        // If user checked multiple conditions, ignore `healthy`
	        if (conditions.includes('healthy') && conditions.length > 1) {
	            conditions = conditions.filter(c => c !== 'healthy');
	        }

	        const primaryCondition = conditions[0] || null;

	        const surfaces = Array.from(scope.querySelectorAll('input[name="surfaces[]"]:checked'))
	            .map(cb => cb.value);

	        const severity = document.getElementById('severitySelect')?.value || null;
	        const notes = document.getElementById('notesTextarea')?.value || '';

	        // Validate
	        if (!primaryCondition) {
	            dentalChartState.isSaving = false;
	            setDrawerBusyState(false);
	            updateApplyButtonState();
	            showNotification('Please select at least one condition', 'warning');
	            return;
	        }

	        try {
	            const response = await fetch(`/dental/patients/{{ $patient->id }}/charts/{{ $dentalChart->id }}/tooth-record`, {
	                method: 'POST',
	                headers: {
	                    'Content-Type': 'application/json',
	                    'Accept': 'application/json',
	                    'X-Requested-With': 'XMLHttpRequest',
	                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
	                },
	                body: JSON.stringify({
	                    tooth_number: currentDrawerTooth,
	                    primary_condition: primaryCondition,
	                    conditions: conditions,
	                    surfaces_affected: surfaces,
	                    severity: severity,
	                    notes: notes
	                })
	            });

	            const contentType = response.headers.get('content-type') || '';
	            let data = null;
	            if (contentType.includes('application/json')) {
	                data = await response.json();
	            } else {
	                const text = await response.text();
	                data = { message: text };
	            }

	            if (!response.ok || data?.success === false) {
	                showNotification(data?.message || 'Failed to save tooth details', 'error');
	                return;
	            }

	            completedSuccessfully = true;
	            setDrawerBusyState(true, '<i class="fas fa-check"></i> Saved');
	            showNotification('Tooth details saved successfully!', 'success');
	            // Reload the page to show updated chart + history
	            setTimeout(() => window.location.reload(), 800);
	        } catch (error) {
	            console.error('Error saving tooth details:', error);
	            showNotification('An error occurred while saving', 'error');
	        } finally {
	            if (!completedSuccessfully) {
	                dentalChartState.isSaving = false;
	                setDrawerBusyState(false);
	                updateApplyButtonState();
	            }
	        }
    }

    function closeToothDrawer() {
	        if (isInteractionLocked()) return;
        const drawer = document.getElementById('toothDetailsDrawer');
        drawer.style.display = 'none';
        document.body.style.overflow = '';
        currentDrawerTooth = null;
        currentDrawerMode = 'view';
        currentDrawerTab = 'details';
    }

    // Close drawer with Escape key
    document.addEventListener('keydown', function(e) {
        if (isInteractionLocked()) {
            return;
        }

        if (e.key === 'Escape' || e.key === 'Esc') {
            const drawer = document.getElementById('toothDetailsDrawer');
            if (drawer.style.display === 'block') {
                if (currentDrawerMode === 'edit') {
                    if (confirm('Discard unsaved changes?')) {
                        closeToothDrawer();
                    }
                } else {
                    closeToothDrawer();
                }
            }
        }
    });

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection

