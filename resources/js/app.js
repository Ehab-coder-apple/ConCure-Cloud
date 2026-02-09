import './bootstrap';

// Import Bootstrap JavaScript
import 'bootstrap';

// Import Chart.js
import Chart from 'chart.js/auto';

// Import SweetAlert2
import Swal from 'sweetalert2';

// Make Chart and Swal available globally
window.Chart = Chart;
window.Swal = Swal;

// ConCure Application JavaScript
class ConCureApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupCSRFToken();
        this.setupAlerts();
        this.setupFormValidation();
        this.setupFileUploads();
        this.setupCharts();
        this.setupLanguageSwitcher();
        this.setupTooltips();
        this.setupModals();
    }

    // Setup CSRF token for all AJAX requests
    setupCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
    }

    // Setup auto-hide alerts
    setupAlerts() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            }, 5000);
        });
    }

    // Setup form validation
    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }

    // Setup file upload previews
    setupFileUploads() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', event => {
                this.handleFilePreview(event.target);
            });
        });
    }

    handleFilePreview(input) {
        const file = input.files[0];
        if (!file) return;

        const previewContainer = input.parentElement.querySelector('.file-preview');
        if (!previewContainer) return;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                previewContainer.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    <p class="mt-2 mb-0 text-muted small">${file.name}</p>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-file fa-2x text-primary me-3"></i>
                    <div>
                        <p class="mb-0 fw-bold">${file.name}</p>
                        <p class="mb-0 text-muted small">${this.formatFileSize(file.size)}</p>
                    </div>
                </div>
            `;
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Setup charts
    setupCharts() {
        // Financial Chart
        const financialChart = document.getElementById('financialChart');
        if (financialChart) {
            this.createFinancialChart(financialChart);
        }

        // Patient Stats Chart
        const patientChart = document.getElementById('patientChart');
        if (patientChart) {
            this.createPatientChart(patientChart);
        }
    }

    createFinancialChart(canvas) {
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 15000, 25000, 22000, 30000],
                    borderColor: '#008080',
                    backgroundColor: 'rgba(0, 128, 128, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Expenses',
                    data: [8000, 12000, 10000, 15000, 14000, 18000],
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Financial Overview'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    createPatientChart(canvas) {
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['New Patients', 'Regular Patients', 'Follow-up'],
                datasets: [{
                    data: [30, 50, 20],
                    backgroundColor: [
                        '#008080',
                        '#28a745',
                        '#ffc107'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Patient Distribution'
                    }
                }
            }
        });
    }

    // Setup language switcher
    setupLanguageSwitcher() {
        const languageLinks = document.querySelectorAll('.language-switch');
        languageLinks.forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                const lang = link.dataset.lang;
                this.switchLanguage(lang);
            });
        });
    }

    switchLanguage(lang) {
        // Show loading
        Swal.fire({
            title: 'Switching Language...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Make request to switch language
        window.location.href = `/language/${lang}`;
    }

    // Setup tooltips
    setupTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Setup modals
    setupModals() {
        // Auto-focus first input in modals
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                const firstInput = modal.querySelector('input, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    // Utility methods
    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message
        });
    }

    showConfirm(title, text, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#008080',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, proceed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    }
}

// Initialize ConCure App when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.ConCure = new ConCureApp();
});

// Export for use in other modules
export default ConCureApp;
