/**
 * ConCure Smart Search Utility
 * 
 * Provides consistent search functionality across the application with:
 * - Minimum input length validation
 * - Debounced search (300ms default)
 * - Loading states
 * - Empty state handling
 * - Error handling
 */

class SmartSearch {
    /**
     * Create a new SmartSearch instance
     * 
     * @param {Object} options Configuration options
     * @param {string} options.inputSelector - CSS selector for search input
     * @param {string} options.url - AJAX endpoint URL
     * @param {Function} options.onResults - Callback function to handle results
     * @param {string} options.resultsSelector - CSS selector for results container (optional)
     * @param {number} options.minLength - Minimum search length (default: 1)
     * @param {number} options.debounceDelay - Debounce delay in ms (default: 300)
     * @param {Object} options.additionalParams - Additional parameters to send with request
     * @param {Function} options.onError - Error callback (optional)
     * @param {Function} options.onLoading - Loading state callback (optional)
     */
    constructor(options) {
        this.input = document.querySelector(options.inputSelector);
        this.url = options.url;
        this.onResults = options.onResults;
        this.resultsContainer = options.resultsSelector ? document.querySelector(options.resultsSelector) : null;
        this.minLength = options.minLength || 1;
        this.debounceDelay = options.debounceDelay || 300;
        this.additionalParams = options.additionalParams || {};
        this.onError = options.onError || this.defaultErrorHandler.bind(this);
        this.onLoading = options.onLoading || this.defaultLoadingHandler.bind(this);
        
        this.searchTimeout = null;
        this.currentRequest = null;
        
        if (this.input) {
            this.init();
        }
    }

    /**
     * Initialize event listeners
     */
    init() {
        this.input.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });
        
        // Show placeholder on focus if empty
        this.input.addEventListener('focus', () => {
            if (this.input.value.trim().length === 0) {
                this.showEmptyState();
            }
        });
    }

    /**
     * Handle input changes with debouncing
     */
    handleInput(value) {
        clearTimeout(this.searchTimeout);
        
        // Cancel any pending request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        const searchTerm = value.trim();
        
        // Check minimum length
        if (searchTerm.length < this.minLength) {
            this.showEmptyState();
            return;
        }
        
        // Debounce the search
        this.searchTimeout = setTimeout(() => {
            this.performSearch(searchTerm);
        }, this.debounceDelay);
    }

    /**
     * Perform the actual search
     */
    async performSearch(searchTerm) {
        // Show loading state
        this.onLoading(true);
        
        try {
            // Build URL with parameters
            const params = new URLSearchParams({
                search: searchTerm,
                q: searchTerm, // Support both parameters
                ...this.additionalParams
            });
            
            const url = `${this.url}?${params.toString()}`;
            
            // Create AbortController for this request
            const controller = new AbortController();
            this.currentRequest = controller;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                signal: controller.signal
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            // Hide loading state
            this.onLoading(false);
            
            // Handle results
            this.onResults(data, searchTerm);
            
        } catch (error) {
            if (error.name === 'AbortError') {
                // Request was cancelled, ignore
                return;
            }
            
            this.onLoading(false);
            this.onError(error, searchTerm);
        } finally {
            this.currentRequest = null;
        }
    }

    /**
     * Default loading handler
     */
    defaultLoadingHandler(isLoading) {
        if (!this.resultsContainer) return;
        
        if (isLoading) {
            this.resultsContainer.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Searching...</p>
                </div>
            `;
        }
    }

    /**
     * Default error handler
     */
    defaultErrorHandler(error, searchTerm) {
        console.error('Search error:', error);
        
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    An error occurred while searching. Please try again.
                </div>
            `;
        }
    }

    /**
     * Show empty state message
     */
    showEmptyState() {
        if (!this.resultsContainer) return;
        
        this.resultsContainer.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2"></i>
                <p>Start typing to search (minimum ${this.minLength} character${this.minLength > 1 ? 's' : ''})...</p>
            </div>
        `;
    }

    /**
     * Update additional parameters
     */
    updateParams(params) {
        this.additionalParams = { ...this.additionalParams, ...params };
    }

    /**
     * Trigger search programmatically
     */
    search(term) {
        this.input.value = term;
        this.handleInput(term);
    }

    /**
     * Clear search
     */
    clear() {
        this.input.value = '';
        this.showEmptyState();
    }
}

/**
 * Simple debounce utility function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { SmartSearch, debounce };
}

