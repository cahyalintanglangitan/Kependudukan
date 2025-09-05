// assets/js/api.js - API handling utilities

/**
 * API Handler Class
 */
class APIHandler {
    constructor(baseURL = '') {
        this.baseURL = baseURL || window.API_BASE_URL || './api/';
        this.defaultHeaders = {
            'Content-Type': 'application/json',
        };
        this.cache = new Map();
        this.requestQueue = new Map();
    }

    /**
     * Generic request method
     */
    async request(endpoint, options = {}) {
        const url = this.baseURL + endpoint;
        const cacheKey = this.getCacheKey(url, options);
        
        // Check cache first for GET requests
        if ((!options.method || options.method === 'GET') && this.cache.has(cacheKey)) {
            const cached = this.cache.get(cacheKey);
            if (Date.now() - cached.timestamp < 300000) { // 5 minutes cache
                return cached.data;
            }
        }

        // Check if same request is already in progress
        if (this.requestQueue.has(cacheKey)) {
            return this.requestQueue.get(cacheKey);
        }

        const config = {
            headers: { ...this.defaultHeaders, ...options.headers },
            ...options
        };

        const requestPromise = this.executeRequest(url, config, cacheKey);
        this.requestQueue.set(cacheKey, requestPromise);

        try {
            const result = await requestPromise;
            this.requestQueue.delete(cacheKey);
            return result;
        } catch (error) {
            this.requestQueue.delete(cacheKey);
            throw error;
        }
    }

    async executeRequest(url, config, cacheKey) {
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            // Cache successful GET requests
            if (!config.method || config.method === 'GET') {
                this.cache.set(cacheKey, {
                    data: data,
                    timestamp: Date.now()
                });
            }

            return data;
            
        } catch (error) {
            console.error(`API Request failed for ${url}:`, error);
            
            // Try to provide meaningful error messages
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Koneksi jaringan bermasalah. Periksa koneksi internet Anda.');
            } else if (error.message.includes('500')) {
                throw new Error('Server mengalami masalah. Silakan coba lagi nanti.');
            } else if (error.message.includes('404')) {
                throw new Error('Data tidak ditemukan.');
            }
            
            throw error;
        }
    }

    getCacheKey(url, options) {
        return `${url}_${JSON.stringify(options)}`;
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        return this.request(url, {
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
    }

    /**
     * Specialized methods for each data type
     */
    async getDisabilitasData(filters = {}) {
        return this.get('disabilitas.php', filters);
    }

    async getAktaCeraiData(filters = {}) {
        return this.get('akta/cerai.php', filters);
    }

    async getAktaLahirData(filters = {}) {
        return this.get('akta/lahir.php', filters);
    }

    async getAktaMatiData(filters = {}) {
        return this.get('akta/mati.php', filters);
    }

    async getDemografiData(filters = {}) {
        return this.get('demografi.php', filters);
    }

    async getKelompokUmurData(filters = {}) {
        return this.get('kelompok_umur.php', filters);
    }

    async getKepalaKeluargaData(filters = {}) {
        return this.get('kepala_keluarga.php', filters);
    }

    async getAktaKelahiranData(filters = {}) {
    const defaultFilters = {
        region_type: 'all',
        province: 'all', 
        sort_by: 'total_desc',
        limit: 20
    };
    return this.get('akta.php', { ...defaultFilters, ...filters });
}

async getPendidikanData(filters = {}) {
    const defaultFilters = {
        region_type: 'all',
        province: 'all',
        sort_by: 'total_desc',
        limit: 20
    };
    return this.get('pendidikan.php', { ...defaultFilters, ...filters });
}

async getPekerjaanData(filters = {}) {
    const defaultFilters = {
        region_type: 'all',
        province: 'all',
        sort_by: 'total_desc',
        limit: 20
    };
    return this.get('pekerjaan.php', { ...defaultFilters, ...filters });
}
}

// Global API instance
window.API = new APIHandler();

// Chart.js global configuration
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#7f8c8d';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.elements.bar.borderRadius = 4;
    Chart.defaults.elements.bar.borderSkipped = false;
    
    // Global chart options
    window.CHART_OPTIONS = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: 'white',
                bodyColor: 'white',
                borderColor: '#ddd',
                borderWidth: 1,
                cornerRadius: 6,
                displayColors: true,
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    maxRotation: 45
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f8f9fa'
                }
            }
        },
        interaction: {
            mode: 'nearest',
            intersect: false
        }
    };
}

// Global chart colors
window.CHART_COLORS = {
    primary: '#3498db',
    success: '#2ecc71',
    warning: '#f39c12',
    danger: '#e74c3c',
    purple: '#9b59b6',
    pink: '#e91e63',
    info: '#17a2b8',
    dark: '#343a40',
    light: '#f8f9fa'
};

// Global refresh function for when page becomes visible
window.refreshData = function() {
    if (window.API) {
        window.API.clearCache();
    }
    
    // Trigger refresh event
    window.dispatchEvent(new CustomEvent('dataRefresh'));
};