// Auto-logout script loaded
console.log('🔒 Auto-logout script loaded');

/**
 * ConCure Auto-Logout Security Module
 *
 * Automatically logs out users after a period of inactivity to protect
 * sensitive medical data. Includes activity tracking, warning dialogs,
 * and graceful session management.
 */

class AutoLogout {
    constructor() {
        this.config = {
            enabled: true,
            timeoutMinutes: 10,
            warningMinutes: 2,
            keepaliveInterval: 60,
            timeoutSeconds: 600,
            warningSeconds: 120,
        };
        
        this.inactivityTimer = null;
        this.warningTimer = null;
        this.keepaliveTimer = null;
        this.warningDialog = null;
        this.countdownInterval = null;
        this.lastActivity = Date.now();
        this.isWarningShown = false;
        this.isPageVisible = true;
        this.pauseReasons = new Set();
        
        // Bind methods to preserve context
        this.handleActivity = this.handleActivity.bind(this);
        this.handleVisibilityChange = this.handleVisibilityChange.bind(this);
        this.showWarning = this.showWarning.bind(this);
        this.performLogout = this.performLogout.bind(this);
        this.stayLoggedIn = this.stayLoggedIn.bind(this);
        
        this.init();
    }

    /**
     * Initialize the auto-logout system
     */
    async init() {
        try {
            console.log('🔒 Auto-logout: Starting initialization...');

            // Load configuration from server
            await this.loadConfig();

            if (!this.config.enabled) {
                console.log('⚠️ Auto-logout is disabled in configuration');
                return;
            }

            console.log('✅ Auto-logout initialized successfully:', {
                timeout: this.config.timeoutMinutes + ' minutes (' + this.config.timeoutSeconds + ' seconds)',
                warning: this.config.warningMinutes + ' minutes before (' + this.config.warningSeconds + ' seconds)',
                keepalive: this.config.keepaliveInterval + ' seconds',
            });

            // Set up activity tracking
            this.setupActivityTracking();
            console.log('✅ Activity tracking enabled');

            // Set up page visibility tracking
            this.setupVisibilityTracking();
            console.log('✅ Page visibility tracking enabled');

            // Start inactivity timer
            this.resetInactivityTimer();
            console.log('✅ Inactivity timer started');

            // Start keep-alive pings
            this.startKeepAlive();
            console.log('✅ Keep-alive pings started');

            // Try to preserve form data on logout
            this.setupFormDataPreservation();
            console.log('✅ Form data preservation enabled');

            console.log('🎉 Auto-logout is now active and monitoring for inactivity');

        } catch (error) {
            console.error('❌ Failed to initialize auto-logout:', error);
        }
    }

    /**
     * Load configuration from server
     */
    async loadConfig() {
        try {
            console.log('📡 Loading auto-logout config from server...');
            const response = await fetch('/session/config', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                console.log('📥 Server config received:', data);
                this.config = { ...this.config, ...data };
            } else {
                console.warn('⚠️ Server config request failed with status:', response.status);
                console.log('📋 Using default config');
            }
        } catch (error) {
            console.warn('⚠️ Failed to load server config, using defaults:', error.message);
        }
    }

    /**
     * Set up activity event listeners
     */
    setupActivityTracking() {
        const events = ['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
        
        // Throttle activity handler to avoid excessive calls
        let throttleTimeout = null;
        const throttledHandler = () => {
            if (!throttleTimeout) {
                throttleTimeout = setTimeout(() => {
                    this.handleActivity();
                    throttleTimeout = null;
                }, 1000); // Throttle to once per second
            }
        };
        
        events.forEach(event => {
            document.addEventListener(event, throttledHandler, { passive: true });
        });
    }

    /**
     * Set up page visibility tracking
     */
    setupVisibilityTracking() {
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
    }

    /**
     * Handle page visibility changes
     */
    handleVisibilityChange() {
        this.isPageVisible = !document.hidden;

        if (this.isPaused()) {
            return;
        }
        
        if (this.isPageVisible) {
            // Page became visible - check session status
            this.checkSessionStatus();
        }
    }

    /**
     * Handle user activity
     */
    handleActivity() {
        this.lastActivity = Date.now();

        if (this.isPaused()) {
            return;
        }
        
        // Reset inactivity timer
        this.resetInactivityTimer();
        
        // Hide warning if shown
        if (this.isWarningShown) {
            this.hideWarning();
        }
    }

    /**
     * Reset the inactivity timer
     */
    resetInactivityTimer() {
        if (this.isPaused()) {
            return;
        }

        // Clear existing timers
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
        }
        
        // Calculate warning time (timeout - warning period)
        const warningTime = (this.config.timeoutSeconds - this.config.warningSeconds) * 1000;
        const logoutTime = this.config.timeoutSeconds * 1000;

        // Set warning timer
        this.warningTimer = setTimeout(() => {
            this.showWarning();
        }, warningTime);

        // Set logout timer
        this.inactivityTimer = setTimeout(() => {
            this.performLogout('inactivity');
        }, logoutTime);
    }

    /**
     * Show warning dialog before auto-logout
     */
    showWarning() {
        if (this.isWarningShown) return;

        this.isWarningShown = true;

        // Create warning dialog
        const dialog = document.createElement('div');
        dialog.className = 'auto-logout-warning-overlay';
        dialog.innerHTML = `
            <div class="auto-logout-warning-dialog">
                <div class="auto-logout-warning-header">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <h4>Session Timeout Warning</h4>
                </div>
                <div class="auto-logout-warning-body">
                    <p>You will be automatically logged out due to inactivity in:</p>
                    <div class="auto-logout-countdown">
                        <span id="autoLogoutCountdown">${this.config.warningSeconds}</span> seconds
                    </div>
                    <p class="text-muted small">This is a security measure to protect sensitive medical data.</p>
                </div>
                <div class="auto-logout-warning-footer">
                    <button type="button" class="btn btn-primary" id="stayLoggedInBtn">
                        <i class="fas fa-check"></i> Stay Logged In
                    </button>
                    <button type="button" class="btn btn-secondary" id="logoutNowBtn">
                        <i class="fas fa-sign-out-alt"></i> Logout Now
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(dialog);
        this.warningDialog = dialog;

        // Add event listeners
        document.getElementById('stayLoggedInBtn').addEventListener('click', this.stayLoggedIn);
        document.getElementById('logoutNowBtn').addEventListener('click', () => this.performLogout('manual'));

        // Start countdown
        this.startCountdown();

        // Play alert sound (optional)
        this.playAlertSound();
    }

    /**
     * Hide warning dialog
     */
    hideWarning() {
        if (!this.isWarningShown) return;

        this.isWarningShown = false;

        if (this.warningDialog) {
            this.warningDialog.remove();
            this.warningDialog = null;
        }

        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    /**
     * Start countdown timer in warning dialog
     */
    startCountdown() {
        let remainingSeconds = this.config.warningSeconds;
        const countdownElement = document.getElementById('autoLogoutCountdown');

        this.countdownInterval = setInterval(() => {
            remainingSeconds--;

            if (countdownElement) {
                countdownElement.textContent = remainingSeconds;

                // Change color as time runs out
                if (remainingSeconds <= 10) {
                    countdownElement.style.color = '#dc3545'; // Red
                } else if (remainingSeconds <= 30) {
                    countdownElement.style.color = '#ffc107'; // Yellow
                }
            }

            if (remainingSeconds <= 0) {
                clearInterval(this.countdownInterval);
            }
        }, 1000);
    }

    /**
     * Play alert sound
     */
    playAlertSound() {
        try {
            // Create a simple beep sound using Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            // Silently fail if audio not supported
            console.debug('Audio alert not available:', error);
        }
    }

    /**
     * User clicked "Stay Logged In"
     */
    stayLoggedIn() {
        this.hideWarning();
        this.handleActivity();

        // Send keep-alive ping immediately
        this.sendKeepAlive();
    }

    /**
     * Pause auto-logout timers and session keep-alive traffic.
     */
    pause(reason = 'manual') {
        this.pauseReasons.add(reason);
        this.hideWarning();
        this.stopAllTimers();
        console.log('⏸️ Auto-logout paused:', reason);
    }

    /**
     * Resume auto-logout timers and keep-alive traffic.
     */
    resume(reason = 'manual') {
        if (this.pauseReasons.has(reason)) {
            this.pauseReasons.delete(reason);
        } else if (reason === 'all') {
            this.pauseReasons.clear();
        }

        if (this.isPaused()) {
            return;
        }

        this.hideWarning();
        this.lastActivity = Date.now();
        this.resetInactivityTimer();
        this.startKeepAlive();
        console.log('▶️ Auto-logout resumed:', reason);
    }

    /**
     * Check whether auto-logout is currently paused.
     */
    isPaused() {
        return this.pauseReasons.size > 0;
    }

    /**
     * Perform logout
     */
    async performLogout(reason = 'inactivity') {
        if (this.isPaused() && reason === 'inactivity') {
            console.log('⏸️ Auto-logout inactivity timer ignored while paused');
            return;
        }

        console.log('Performing auto-logout due to:', reason);

        // Hide warning if shown
        this.hideWarning();

        // Stop all timers
        this.stopAllTimers();

        // Save form data before logout
        this.saveFormData();

        try {
            // Call auto-logout endpoint
            const response = await fetch('/session/auto-logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ reason }),
            });

            const data = await response.json();

            // Redirect to login page
            window.location.href = data.redirect_url || '/login';

        } catch (error) {
            console.error('Auto-logout failed:', error);
            // Force redirect anyway
            window.location.href = '/login';
        }
    }

    /**
     * Start keep-alive pings
     */
    startKeepAlive() {
        if (this.isPaused() || this.keepaliveTimer) {
            return;
        }

        // Send keep-alive ping at regular intervals
        this.keepaliveTimer = setInterval(() => {
            if (this.isPaused()) {
                return;
            }

            // Only send if page is visible and user was recently active
            const timeSinceActivity = Date.now() - this.lastActivity;
            const activityThreshold = 5 * 60 * 1000; // 5 minutes

            if (this.isPageVisible && timeSinceActivity < activityThreshold) {
                this.sendKeepAlive();
            }
        }, this.config.keepaliveInterval * 1000);
    }

    /**
     * Send keep-alive ping to server
     */
    async sendKeepAlive() {
        if (this.isPaused()) {
            return;
        }

        try {
            // Get fresh CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!csrfToken) {
                console.error('❌ CSRF token not found in meta tag');
                return;
            }

            console.log('📡 Sending keep-alive ping...');

            const response = await fetch('/session/keep-alive', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin', // Include cookies
                body: JSON.stringify({}), // Send empty JSON body
            });

            if (!response.ok) {
                console.warn('⚠️ Keep-alive failed:', response.status, response.statusText);

                if (response.status === 419) {
                    console.error('❌ CSRF token mismatch - token may have expired');
                    // Try to get a fresh CSRF token
                    try {
                        const tokenResponse = await fetch('/csrf-token');
                        const tokenData = await tokenResponse.json();
                        document.querySelector('meta[name="csrf-token"]').setAttribute('content', tokenData.token);
                        console.log('✅ CSRF token refreshed');
                    } catch (e) {
                        console.error('❌ Failed to refresh CSRF token:', e);
                    }
                    return;
                }

                if (response.status === 401) {
                    // Session expired on server
                    console.warn('⚠️ Session expired on server');
                    this.performLogout('session_expired');
                }
            } else {
                const data = await response.json();
                console.log('✅ Keep-alive successful:', data.timestamp);
            }
        } catch (error) {
            console.error('❌ Keep-alive request failed:', error);
        }
    }

    /**
     * Check session status on server
     */
    async checkSessionStatus() {
        if (this.isPaused()) {
            return;
        }

        try {
            const response = await fetch('/session/status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok || response.status === 401) {
                // Session expired
                this.performLogout('session_expired');
            }
        } catch (error) {
            console.error('Session status check failed:', error);
        }
    }

    /**
     * Stop all timers
     */
    stopAllTimers() {
        if (this.inactivityTimer) {
            clearTimeout(this.inactivityTimer);
            this.inactivityTimer = null;
        }
        if (this.warningTimer) {
            clearTimeout(this.warningTimer);
            this.warningTimer = null;
        }
        if (this.keepaliveTimer) {
            clearInterval(this.keepaliveTimer);
            this.keepaliveTimer = null;
        }
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
            this.countdownInterval = null;
        }
    }

    /**
     * Set up form data preservation
     */
    setupFormDataPreservation() {
        // Save form data periodically
        setInterval(() => {
            this.saveFormData();
        }, 30000); // Every 30 seconds
    }

    /**
     * Save form data to localStorage
     */
    saveFormData() {
        try {
            const forms = document.querySelectorAll('form[data-preserve="true"]');
            const formData = {};

            forms.forEach((form, index) => {
                const formId = form.id || `form_${index}`;
                const data = {};

                // Get all input, textarea, and select elements
                const elements = form.querySelectorAll('input, textarea, select');
                elements.forEach(element => {
                    if (element.name && element.type !== 'password') {
                        if (element.type === 'checkbox' || element.type === 'radio') {
                            data[element.name] = element.checked;
                        } else {
                            data[element.name] = element.value;
                        }
                    }
                });

                if (Object.keys(data).length > 0) {
                    formData[formId] = {
                        data: data,
                        url: window.location.href,
                        timestamp: Date.now(),
                    };
                }
            });

            if (Object.keys(formData).length > 0) {
                localStorage.setItem('concure_preserved_forms', JSON.stringify(formData));
            }
        } catch (error) {
            console.debug('Form data preservation failed:', error);
        }
    }

    /**
     * Restore form data from localStorage
     */
    static restoreFormData() {
        try {
            const preserved = localStorage.getItem('concure_preserved_forms');
            if (!preserved) return;

            const formData = JSON.parse(preserved);
            const currentUrl = window.location.href;

            Object.entries(formData).forEach(([formId, formInfo]) => {
                // Only restore if on the same page and data is less than 1 hour old
                const age = Date.now() - formInfo.timestamp;
                if (formInfo.url === currentUrl && age < 3600000) {
                    const form = document.getElementById(formId);
                    if (form) {
                        Object.entries(formInfo.data).forEach(([name, value]) => {
                            const element = form.querySelector(`[name="${name}"]`);
                            if (element) {
                                if (element.type === 'checkbox' || element.type === 'radio') {
                                    element.checked = value;
                                } else {
                                    element.value = value;
                                }
                            }
                        });

                        // Show notification
                        console.log('Form data restored from previous session');
                    }
                }
            });

            // Clear old preserved data
            localStorage.removeItem('concure_preserved_forms');
        } catch (error) {
            console.debug('Form data restoration failed:', error);
        }
    }
}

// CSS styles for warning dialog
const styles = `
<style>
.auto-logout-warning-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

.auto-logout-warning-dialog {
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    animation: slideIn 0.3s ease;
}

.auto-logout-warning-header {
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.auto-logout-warning-header i {
    font-size: 2rem;
}

.auto-logout-warning-header h4 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.auto-logout-warning-body {
    padding: 2rem 1.5rem;
    text-align: center;
}

.auto-logout-countdown {
    font-size: 3rem;
    font-weight: bold;
    color: #0ea5e9;
    margin: 1rem 0;
    font-family: 'Courier New', monospace;
}

.auto-logout-warning-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.auto-logout-warning-footer .btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
`;

// Inject styles
if (!document.getElementById('auto-logout-styles')) {
    const styleElement = document.createElement('div');
    styleElement.id = 'auto-logout-styles';
    styleElement.innerHTML = styles;
    document.head.appendChild(styleElement);
}

// Initialize auto-logout when DOM is ready
console.log('🔒 Auto-logout: Checking document ready state:', document.readyState);

if (document.readyState === 'loading') {
    console.log('🔒 Auto-logout: Waiting for DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🔒 Auto-logout: DOMContentLoaded fired, creating instance...');
        try {
            window.autoLogout = new AutoLogout();
            console.log('🔒 Auto-logout: Instance created successfully');
            // Try to restore form data
            AutoLogout.restoreFormData();
        } catch (error) {
            console.error('❌ Failed to create AutoLogout instance:', error);
        }
    });
} else {
    console.log('🔒 Auto-logout: DOM already ready, creating instance immediately...');
    try {
        window.autoLogout = new AutoLogout();
        console.log('🔒 Auto-logout: Instance created successfully');
        // Try to restore form data
        AutoLogout.restoreFormData();
    } catch (error) {
        console.error('❌ Failed to create AutoLogout instance:', error);
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AutoLogout;
}


