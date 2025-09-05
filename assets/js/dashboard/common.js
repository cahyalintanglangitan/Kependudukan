// assets/js/dashboard/common.js
// Dashboard-specific common functions

window.DashboardCommon = {
    // Chart default colors untuk semua dashboard pages
    chartColors: {
        primary: '#3498db',
        success: '#2ecc71', 
        warning: '#f39c12',
        purple: '#9b59b6',
        pink: '#e91e63',
        danger: '#e74c3c',
        info: '#17a2b8',
        secondary: '#6c757d'
    },

    // Default chart options untuk semua dashboard charts
    chartDefaults: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#dee2e6',
                borderWidth: 1,
                cornerRadius: 6,
                displayColors: true
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                grid: {
                    color: '#f1f3f4'
                },
                beginAtZero: true
            }
        }
    },

    // Setup filter event listeners (umum untuk dashboard)
    setupFilterEventListeners(loadDataFunction) {
        const filters = [
            'regionTypeFilter',
            'provinceFilter', 
            'sortFilter'
        ];
        
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element) {
                element.addEventListener('change', loadDataFunction);
            }
        });

        // Refresh button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                loadDataFunction();
            });
        }
    },

    // Populate province filter dropdown
    populateProvinceFilter(allProvinces) {
        const select = document.getElementById('provinceFilter');
        if (!select) return;
        
        const currentValue = select.value;
        
        // Clear existing options except first one
        select.innerHTML = '<option value="all">Semua Provinsi</option>';
        
        // Add province options
        if (Array.isArray(allProvinces)) {
            allProvinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.kode || province.id;
                option.textContent = province.wilayah || province.nama;
                select.appendChild(option);
            });
        }
        
        // Restore previous selection if still valid
        if (currentValue && [...select.options].some(opt => opt.value === currentValue)) {
            select.value = currentValue;
        }
    },

    // Update data counter
    updateDataCounter(currentData) {
        const counterElement = document.getElementById('dataCount');
        if (counterElement) {
            const count = Array.isArray(currentData) ? currentData.length : 0;
            counterElement.textContent = count.toLocaleString('id-ID');
        }
    },

    // Show loading spinner
    showLoading(elementIds) {
        const spinnerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i></div>';
        
        if (Array.isArray(elementIds)) {
            elementIds.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.innerHTML = spinnerHTML;
            });
        }
    },

    // Show error state
    showError(elementIds, errorMessage = 'Error') {
        if (Array.isArray(elementIds)) {
            elementIds.forEach(id => {
                const element = document.getElementById(id);
                if (element) element.textContent = errorMessage;
            });
        }
        
        // Reset counter on error
        const counterElement = document.getElementById('dataCount');
        if (counterElement) counterElement.textContent = '0';
    },

    // Destroy chart instance safely
    destroyChart(chartInstance) {
        if (chartInstance && typeof chartInstance.destroy === 'function') {
            chartInstance.destroy();
        }
        return null;
    },

    // Get current filter values
    getCurrentFilters() {
        return {
            region_type: document.getElementById('regionTypeFilter')?.value || 'all',
            province: document.getElementById('provinceFilter')?.value || 'all', 
            sort_by: document.getElementById('sortFilter')?.value || 'total_desc',
            limit: 20
        };
    },

    // Update statistics cards with animation
    updateStatsCards(stats, cardMappings) {
        if (!stats || !Array.isArray(cardMappings)) return;

        cardMappings.forEach(([elementId, statKey, formatter]) => {
            const element = document.getElementById(elementId);
            if (element) {
                const value = stats[statKey] || 0;
                const formattedValue = formatter ? formatter(value) : value.toLocaleString('id-ID');
                
                // Add animation
                element.style.opacity = '0.5';
                setTimeout(() => {
                    element.textContent = formattedValue;
                    element.style.opacity = '1';
                }, 100);
            }
        });
    },

    // Create no data message for charts
    showNoDataChart(canvas, message = 'Tidak ada data untuk ditampilkan') {
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.font = '16px Arial';
        ctx.fillStyle = '#666';
        ctx.textAlign = 'center';
        ctx.fillText(message, canvas.width / 2, canvas.height / 2);
    },

    // Utility: Get chart color by index
    getChartColor(index) {
        const colors = Object.values(this.chartColors);
        return colors[index % colors.length];
    },

    // Utility: Create gradient color
    createGradient(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    }
};