/**
 * ConCure Bootstrap JavaScript
 * 
 * This file loads the necessary JavaScript libraries and sets up
 * global configurations for the ConCure application.
 */

import axios from 'axios';
window.axios = axios;

// Set default axios configuration
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token setup
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Global error handler for axios
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response) {
            // Handle different HTTP status codes
            switch (error.response.status) {
                case 401:
                    // Unauthorized - redirect to login
                    window.location.href = '/login';
                    break;
                case 403:
                    // Forbidden
                    console.error('Access denied:', error.response.data.message);
                    break;
                case 419:
                    // CSRF token mismatch
                    console.error('CSRF token mismatch. Please refresh the page.');
                    break;
                case 422:
                    // Validation errors
                    console.error('Validation errors:', error.response.data.errors);
                    break;
                case 500:
                    // Server error
                    console.error('Server error:', error.response.data.message);
                    break;
                default:
                    console.error('HTTP Error:', error.response.status, error.response.data);
            }
        } else if (error.request) {
            // Network error
            console.error('Network error:', error.request);
        } else {
            // Other error
            console.error('Error:', error.message);
        }
        
        return Promise.reject(error);
    }
);

// Global utility functions
window.ConCureUtils = {
    // Format currency
    formatCurrency: (amount, currency = 'USD') => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Format date
    formatDate: (date, locale = 'en-US') => {
        return new Intl.DateTimeFormat(locale).format(new Date(date));
    },

    // Format file size
    formatFileSize: (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Debounce function
    debounce: (func, wait, immediate) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },

    // Throttle function
    throttle: (func, limit) => {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Generate random ID
    generateId: (length = 8) => {
        return Math.random().toString(36).substring(2, length + 2);
    },

    // Copy to clipboard
    copyToClipboard: async (text) => {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.error('Failed to copy text: ', err);
            return false;
        }
    },

    // Validate email
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validate phone number
    isValidPhone: (phone) => {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    },

    // Get URL parameters
    getUrlParameter: (name) => {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    // Set URL parameter
    setUrlParameter: (name, value) => {
        const url = new URL(window.location);
        url.searchParams.set(name, value);
        window.history.pushState({}, '', url);
    },

    // Remove URL parameter
    removeUrlParameter: (name) => {
        const url = new URL(window.location);
        url.searchParams.delete(name);
        window.history.pushState({}, '', url);
    }
};

// Global event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit forms with data-auto-submit attribute
    const autoSubmitForms = document.querySelectorAll('[data-auto-submit]');
    autoSubmitForms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                form.submit();
            });
        });
    });

    // Auto-focus first input in modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });

    // Confirm delete buttons
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.dataset.confirmDelete || 'Are you sure you want to delete this item?';
            if (confirm(message)) {
                if (this.tagName === 'FORM') {
                    this.submit();
                } else if (this.closest('form')) {
                    this.closest('form').submit();
                } else if (this.href) {
                    window.location.href = this.href;
                }
            }
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert[data-auto-hide]');
    alerts.forEach(alert => {
        const delay = parseInt(alert.dataset.autoHide) || 5000;
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, delay);
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Service Worker registration for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('SW registered: ', registration);
            })
            .catch((registrationError) => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
