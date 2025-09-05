// assets/js/main.js - Core JavaScript utilities

/**
 * Main Application Class
 */
class MainApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupGlobalEventListeners();
        this.initMobileMenu();
        this.setupErrorHandling();
    }

    setupGlobalEventListeners() {
        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.onPageVisible();
            }
        });
    }

    setupErrorHandling() {
        // Global error handler
        window.addEventListener('error', (e) => {
            console.error('Global JavaScript error:', e.error);
            this.showNotification('Terjadi kesalahan pada aplikasi', 'error');
        });

        // Handle unhandled promise rejections
        window.addEventListener('unhandledrejection', (e) => {
            console.error('Unhandled promise rejection:', e.reason);
            this.showNotification('Terjadi kesalahan saat memuat data', 'error');
        });
    }

    initMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        // Create mobile menu button
        const mobileMenuBtn = document.createElement('button');
        mobileMenuBtn.className = 'mobile-menu-btn';
        mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        mobileMenuBtn.setAttribute('aria-label', 'Toggle menu');
        
        // Add mobile menu styles
        mobileMenuBtn.style.cssText = `
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #3498db;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            display: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        `;

        document.body.appendChild(mobileMenuBtn);

        // Show/hide mobile menu based on screen size
        const mediaQuery = window.matchMedia('(max-width: 768px)');
        const handleMobileMenu = (e) => {
            if (e.matches) {
                mobileMenuBtn.style.display = 'block';
            } else {
                mobileMenuBtn.style.display = 'none';
                sidebar.classList.remove('active');
            }
        };

        mediaQuery.addListener(handleMobileMenu);
        handleMobileMenu(mediaQuery);

        // Toggle sidebar on mobile
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            const isOpen = sidebar.classList.contains('active');
            mobileMenuBtn.setAttribute('aria-expanded', isOpen);
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnMenuBtn = mobileMenuBtn.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnMenuBtn && window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    handleResize() {
        // Force chart resize if charts exist
        if (window.Chart) {
            // Chart.js v3+ uses Chart.registry instead of Chart.instances
            try {
                // Get all chart instances from the DOM
                const chartCanvases = document.querySelectorAll('canvas[id*="Chart"], canvas[class*="chart"]');
                chartCanvases.forEach(canvas => {
                    // Get chart instance from canvas
                    const chart = Chart.getChart(canvas);
                    if (chart && typeof chart.resize === 'function') {
                        chart.resize();
                    }
                });
            } catch (error) {
                console.warn('Chart resize error:', error);
            }
        }
    }

    onPageVisible() {
        // Refresh data when page becomes visible again
        if (typeof window.refreshData === 'function') {
            window.refreshData();
        }
    }

    // Utility function for debouncing
    debounce(func, wait) {
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

    // Show notification/toast
    showNotification(message, type = 'info', duration = 5000) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close" aria-label="Close notification">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        // Add notification styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 400px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        // Set background color based on type
        const colors = {
            success: '#27ae60',
            error: '#e74c3c',
            warning: '#f39c12',
            info: '#3498db'
        };
        notification.style.backgroundColor = colors[type] || colors.info;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            this.hideNotification(notification);
        });

        // Auto remove
        setTimeout(() => {
            this.hideNotification(notification);
        }, duration);
    }

    hideNotification(notification) {
        if (notification && document.body.contains(notification)) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300);
        }
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || icons.info;
    }
}

/**
 * Utility Functions
 */
window.Utils = {
    // Format number with Indonesian locale
    formatNumber: (num) => {
        if (num == null || isNaN(num)) return '0';
        return new Intl.NumberFormat('id-ID').format(num);
    },

    // Format percentage
    formatPercent: (num, decimals = 1) => {
        if (num == null || isNaN(num)) return '0%';
        return new Intl.NumberFormat('id-ID', { 
            style: 'percent', 
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num / 100);
    },

    // Truncate text
    truncateText: (text, maxLength = 20) => {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    },

    // Generate random ID
    generateId: () => {
        return '_' + Math.random().toString(36).substr(2, 9);
    },

    // Deep clone object
    deepClone: (obj) => {
        return JSON.parse(JSON.stringify(obj));
    },

    // Check if element is in viewport
    isInViewport: (element) => {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    },

    // Smooth scroll to element
    scrollToElement: (element, offset = 0) => {
        const elementPosition = element.offsetTop;
        const offsetPosition = elementPosition - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    },

    // Get query parameters
    getQueryParams: () => {
        const params = {};
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        
        for (const [key, value] of urlParams) {
            params[key] = value;
        }
        
        return params;
    },

    // Set query parameter
    setQueryParam: (key, value) => {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.replaceState({}, '', url);
    }
};

// Initialize main app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mainApp = new MainApp();
});

