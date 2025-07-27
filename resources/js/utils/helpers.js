// Common utilities and helper functions

/**
 * Format currency in Indonesian Rupiah
 * @param {number} amount
 * @returns {string}
 */
export function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

/**
 * Format date to Indonesian locale
 * @param {Date|string} date
 * @returns {string}
 */
export function formatDate(date) {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    }).format(dateObj);
}

/**
 * Format date to short format
 * @param {Date|string} date
 * @returns {string}
 */
export function formatDateShort(date) {
    const dateObj = typeof date === 'string' ? new Date(date) : date;
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(dateObj);
}

/**
 * Calculate difference between two dates in days
 * @param {Date|string} startDate
 * @param {Date|string} endDate
 * @returns {number}
 */
export function calculateDayDifference(startDate, endDate) {
    const start = typeof startDate === 'string' ? new Date(startDate) : startDate;
    const end = typeof endDate === 'string' ? new Date(endDate) : endDate;
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end date
}

/**
 * Debounce function
 * @param {Function} func
 * @param {number} wait
 * @returns {Function}
 */
export function debounce(func, wait) {
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

/**
 * Show loading state on button
 * @param {HTMLElement} button
 */
export function showButtonLoading(button) {
    button.disabled = true;
    button.classList.add('loading');
    button.dataset.originalText = button.textContent;
    button.textContent = 'Memproses...';
}

/**
 * Hide loading state on button
 * @param {HTMLElement} button
 */
export function hideButtonLoading(button) {
    button.disabled = false;
    button.classList.remove('loading');
    button.textContent = button.dataset.originalText || button.textContent;
}

/**
 * Show notification
 * @param {string} message
 * @param {string} type - success, error, warning, info
 */
export function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} fade-in`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 24px;
        border-radius: 8px;
        z-index: 1000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;
    notification.textContent = message;

    // Add to DOM
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

/**
 * Confirm dialog
 * @param {string} message
 * @param {string} title
 * @returns {Promise<boolean>}
 */
export function confirmDialog(message, title = 'Konfirmasi') {
    return new Promise((resolve) => {
        const result = window.confirm(`${title}\n\n${message}`);
        resolve(result);
    });
}

/**
 * Validate email format
 * @param {string} email
 * @returns {boolean}
 */
export function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate required fields
 * @param {Object} data
 * @param {Array} requiredFields
 * @returns {Object}
 */
export function validateRequired(data, requiredFields) {
    const errors = {};

    requiredFields.forEach(field => {
        if (!data[field] || data[field].toString().trim() === '') {
            errors[field] = 'Field ini wajib diisi';
        }
    });

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

/**
 * Make HTTP request with error handling
 * @param {string} url
 * @param {Object} options
 * @returns {Promise}
 */
export async function makeRequest(url, options = {}) {
    try {
        const csrfToken = getCSRFToken();

        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            let errorData = {};
            try {
                errorData = await response.json();
            } catch (e) {
                errorData.message = response.statusText || `HTTP error! status: ${response.status}`;
            }

            const error = new Error(errorData.message || `HTTP error! status: ${response.status}`);
            error.status = response.status;
            error.data = errorData;
            throw error;
        }

        // Handle different content types
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            return { success: true };
        }
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

/**
 * Animate element
 * @param {HTMLElement} element
 * @param {string} animationClass
 * @param {number} duration
 */
export function animateElement(element, animationClass, duration = 600) {
    element.classList.add(animationClass);

    setTimeout(() => {
        element.classList.remove(animationClass);
    }, duration);
}

/**
 * Get CSRF token
 * @returns {string}
 */
export function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

/**
 * Store data in localStorage with expiry
 * @param {string} key
 * @param {any} data
 * @param {number} expiryMinutes
 */
export function setLocalStorageWithExpiry(key, data, expiryMinutes = 60) {
    const now = new Date();
    const item = {
        data: data,
        expiry: now.getTime() + (expiryMinutes * 60 * 1000)
    };
    localStorage.setItem(key, JSON.stringify(item));
}

/**
 * Get data from localStorage with expiry check
 * @param {string} key
 * @returns {any|null}
 */
export function getLocalStorageWithExpiry(key) {
    const itemStr = localStorage.getItem(key);
    if (!itemStr) return null;

    try {
        const item = JSON.parse(itemStr);
        const now = new Date();

        if (now.getTime() > item.expiry) {
            localStorage.removeItem(key);
            return null;
        }

        return item.data;
    } catch (error) {
        localStorage.removeItem(key);
        return null;
    }
}
