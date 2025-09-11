// assets/js/dashboard/demografi.js
// Demografi page specific logic

class DemografiDashboard {
    constructor() {
        // Use chart colors from DashboardCommon
        this.chartColors = {
            lakiLaki: window.DashboardCommon.chartColors.primary,    // Blue
            perempuan: window.DashboardCommon.chartColors.pink       // Pink
        };

        this.currentData = [];
        this.filteredData = [];
        this.allProvinces = [];
        this.barChart = null;
        this.pieChart = null;
        
        // Table pagination properties
        this.currentPage = 1;
        this.itemsPerPage = 25;
        this.searchTerm = '';
        this.tableSortBy = 'total_desc';
        
        this.init();
    }

    init() {
        // Setup filter event listeners
        window.DashboardCommon.setupFilterEventListeners(() => this.loadData());
        
        // Setup table controls
        this.setupTableControls();
        
        // Initial data load
        this.loadData();
        
        // Listen for global refresh events
        window.addEventListener('dataRefresh', () => this.loadData());
        
        // Setup export button
        const exportBtn = document.getElementById('exportBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportData());
        }
    }

    setupTableControls() {
        // Search input
        const searchInput = document.getElementById('searchWilayah');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase();
                this.currentPage = 1; // Reset to first page
                this.filterAndDisplayData();
            });
        }

        // Sort select for table (menggunakan ID yang berbeda dari filter utama)
        const sortSelect = document.getElementById('sortFilterTable');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.tableSortBy = e.target.value;
                this.currentPage = 1; // Reset to first page
                this.filterAndDisplayData();
            });
        }

        // Refresh button khusus untuk table
        const refreshTableBtn = document.getElementById('refreshTableBtn');
        if (refreshTableBtn) {
            refreshTableBtn.addEventListener('click', () => {
                this.loadData();
            });
        }
    }

    filterAndDisplayData() {
        // Filter data based on search term
        this.filteredData = this.currentData.filter(item => 
            item.wilayah.toLowerCase().includes(this.searchTerm) ||
            item.kode.toLowerCase().includes(this.searchTerm)
        );

        // Sort filtered data
        this.sortFilteredData();

        // Update table display
        this.populateDataTable();
        
        // Update data counter in filter area
        this.updateDataCounter();
    }

    sortFilteredData() {
        if (!this.tableSortBy) this.tableSortBy = 'total_desc';
        
        this.filteredData.sort((a, b) => {
            const [field, order] = this.tableSortBy.split('_');
            let aVal, bVal;

            switch (field) {
                case 'kode':
                    aVal = parseFloat(a.kode) || 0;
                    bVal = parseFloat(b.kode) || 0;
                    break;
                case 'wilayah':
                    aVal = a.wilayah || '';
                    bVal = b.wilayah || '';
                    break;
                case 'laki':
                    aVal = a.laki_laki || 0;
                    bVal = b.laki_laki || 0;
                    break;
                case 'perempuan':
                    aVal = a.perempuan || 0;
                    bVal = b.perempuan || 0;
                    break;
                case 'total':
                    aVal = a.total || (a.laki_laki + a.perempuan) || 0;
                    bVal = b.total || (b.laki_laki + b.perempuan) || 0;
                    break;
                default:
                    aVal = a.wilayah || '';
                    bVal = b.wilayah || '';
            }

            if (typeof aVal === 'string') {
                return order === 'asc' ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
            } else {
                return order === 'asc' ? aVal - bVal : bVal - aVal;
            }
        });
    }

    async loadData() {
        try {
            // Show loading state
            const loadingElements = ['statLakiLaki', 'statPerempuan', 'statTotal'];
            window.DashboardCommon.showLoading(loadingElements);

            // Get current filter values
            const filters = window.DashboardCommon.getCurrentFilters();

            // API call to demografi endpoint
            const result = await window.API.getDemografiData(filters);

            if (result.success) {
                this.currentData = result.data || [];
                this.allProvinces = result.provinces || [];
                
                // Update UI components
                this.updateStats(result.stats);
                window.DashboardCommon.populateProvinceFilter(this.allProvinces);
                
                // Initialize filtered data and table
                this.filterAndDisplayData();
                
                // Create charts with slight delay for smooth transition
                setTimeout(() => this.createCharts(), 100);
                
                // Show success notification
                if (window.mainApp && window.mainApp.showNotification) {
                    window.mainApp.showNotification(
                        `Data berhasil dimuat: ${this.currentData.length} wilayah`, 
                        'success', 
                        3000
                    );
                }
                
                console.log('Demografi data loaded:', this.currentData.length, 'items');
            } else {
                throw new Error(result.error?.message || 'Gagal memuat data');
            }

        } catch (error) {
            console.error('Error loading demografi data:', error);
            
            // Show error notification
            if (window.mainApp && window.mainApp.showNotification) {
                window.mainApp.showNotification(
                    error.message || 'Gagal memuat data demografi', 
                    'error'
                );
            }
            
            // Update UI error state
            window.DashboardCommon.showError(
                ['statLakiLaki', 'statPerempuan', 'statTotal'],
                'Error'
            );
        }
    }

    updateStats(stats) {
        if (!stats) {
            console.warn('No stats data received');
            return;
        }

        // Define stat card mappings for demografi
        const cardMappings = [
            ['statLakiLaki', 'total_laki_laki'],
            ['statPerempuan', 'total_perempuan'],
            ['statTotal', 'grand_total']
        ];
        
        // Use common function to update stats
        window.DashboardCommon.updateStatsCards(stats, cardMappings);
    }

    updateDataCounter() {
        const counterElement = document.getElementById('dataCount');
        if (counterElement) {
            const activeTabCount = this.filteredData.length;
            counterElement.textContent = activeTabCount.toLocaleString('id-ID');
        }
    }

    createCharts() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }
        
        this.createBarChart();
        this.createPieChart();
    }

    createBarChart() {
        const canvas = document.getElementById('barChart');
        if (!canvas) {
            console.warn('Bar chart canvas not found');
            return;
        }
        
        // Destroy existing chart
        this.barChart = window.DashboardCommon.destroyChart(this.barChart);
        
        // Prepare data (limit to 15 for readability)
        const chartData = this.currentData.slice(0, 15);
        
        if (chartData.length === 0) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        try {
            const ctx = canvas.getContext('2d');
            
            this.barChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => {
                        const cleanName = item.wilayah
                            .replace(/^(KAB\.|KOTA|KABUPATEN|PROVINSI)\s*/i, '');
                        return window.Utils ? 
                            window.Utils.truncateText(cleanName, 25) : 
                            cleanName.substring(0, 25) + (cleanName.length > 25 ? '...' : '');
                    }),
                    datasets: [
                        {
                            label: 'Laki-laki',
                            data: chartData.map(item => item.laki_laki || 0),
                            backgroundColor: this.chartColors.lakiLaki,
                            borderRadius: 4
                        },
                        {
                            label: 'Perempuan',
                            data: chartData.map(item => item.perempuan || 0),
                            backgroundColor: this.chartColors.perempuan,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    ...window.DashboardCommon.chartDefaults,
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0,
                                font: { size: 10 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f3f4' },
                            ticks: {
                                callback: function(value) {
                                    return window.Utils ? 
                                        window.Utils.formatNumber(value) : 
                                        value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { 
                            display: false,
                        },
                        tooltip: {
                            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    const formattedValue = window.Utils ? 
                                        window.Utils.formatNumber(context.parsed.y) : 
                                        context.parsed.y.toLocaleString('id-ID');
                                    return `${context.dataset.label}: ${formattedValue}`;
                                }
                            }
                        }
                    }
                }
            });

            console.log('Bar chart created successfully');
        } catch (error) {
            console.error('Error creating bar chart:', error);
        }
    }

    createPieChart() {
        const canvas = document.getElementById('pieChart');
        if (!canvas) {
            console.warn('Pie chart canvas not found');
            return;
        }
        
        // Destroy existing chart
        this.pieChart = window.DashboardCommon.destroyChart(this.pieChart);
        
        // Calculate totals for pie chart
        const totals = this.currentData.reduce((acc, item) => {
            acc.lakiLaki += item.laki_laki || 0;
            acc.perempuan += item.perempuan || 0;
            return acc;
        }, { lakiLaki: 0, perempuan: 0 });

        if (Object.values(totals).every(val => val === 0)) {
            window.DashboardCommon.showNoDataChart(canvas);
            return;
        }

        try {
            const ctx = canvas.getContext('2d');
            
            this.pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [totals.lakiLaki, totals.perempuan],
                        backgroundColor: [
                            this.chartColors.lakiLaki,
                            this.chartColors.perempuan
                        ],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    ...window.DashboardCommon.chartDefaults,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            ...window.DashboardCommon.chartDefaults.plugins.tooltip,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    const formattedValue = window.Utils ? 
                                        window.Utils.formatNumber(context.parsed) : 
                                        context.parsed.toLocaleString('id-ID');
                                    return `${context.label}: ${formattedValue} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            console.log('Pie chart created successfully');
        } catch (error) {
            console.error('Error creating pie chart:', error);
        }
    }

    populateDataTable() {
        const tableContainer = document.getElementById('demografiTable');
        if (!tableContainer) {
            console.warn('Table container not found');
            return;
        }

        if (this.filteredData.length === 0) {
            tableContainer.innerHTML = '<p class="text-center">Tidak ada data untuk ditampilkan</p>';
            return;
        }

        // Calculate pagination
        const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = Math.min(startIndex + this.itemsPerPage, this.filteredData.length);
        const paginatedData = this.filteredData.slice(startIndex, endIndex);

        let tableHTML = `
            <div class="table-controls">
                <div class="per-page-selector">
                    <label for="itemsPerPage">Tampilkan:</label>
                    <select id="itemsPerPage" onchange="window.demografiDashboard.changeItemsPerPage(this.value)">
                        <option value="10" ${this.itemsPerPage === 10 ? 'selected' : ''}>10 data</option>
                        <option value="25" ${this.itemsPerPage === 25 ? 'selected' : ''}>25 data</option>
                        <option value="50" ${this.itemsPerPage === 50 ? 'selected' : ''}>50 data</option>
                        <option value="100" ${this.itemsPerPage === 100 ? 'selected' : ''}>100 data</option>
                    </select>
                </div>
                <div class="pagination-info">
                    Menampilkan ${startIndex + 1}-${Math.min(endIndex, this.filteredData.length)} dari ${this.filteredData.length} data
                </div>
            </div>
        `;

        tableHTML += '<table class="data-table"><thead><tr>';
        tableHTML += '<th>Kode Wilayah</th>';
        tableHTML += '<th>Wilayah</th>';
        tableHTML += '<th class="number">Laki-laki</th>';
        tableHTML += '<th class="number">Perempuan</th>';
        tableHTML += '<th class="number">Total</th>';
        tableHTML += '<th class="percentage">% Laki-laki</th>';
        tableHTML += '<th class="percentage">% Perempuan</th>';
        tableHTML += '</tr></thead><tbody>';

        paginatedData.forEach(item => {
            const total = item.total || (item.laki_laki + item.perempuan);
            const percentLaki = total > 0 ? ((item.laki_laki / total) * 100).toFixed(1) : 0;
            const percentPerempuan = total > 0 ? ((item.perempuan / total) * 100).toFixed(1) : 0;

            // Determine percentage classes for styling
            let percentLakiClass = 'low';
            let percentPerempuanClass = 'low';
            
            const numPercentLaki = parseFloat(percentLaki);
            const numPercentPerempuan = parseFloat(percentPerempuan);
            
            if (numPercentLaki >= 52) percentLakiClass = 'high';
            else if (numPercentLaki >= 48) percentLakiClass = 'medium';
            
            if (numPercentPerempuan >= 52) percentPerempuanClass = 'high';
            else if (numPercentPerempuan >= 48) percentPerempuanClass = 'medium';

            tableHTML += '<tr>';
            tableHTML += `<td>${item.kode}</td>`;
            tableHTML += `<td>${item.wilayah}</td>`;
            tableHTML += `<td class="number">${(item.laki_laki || 0).toLocaleString('id-ID')}</td>`;
            tableHTML += `<td class="number">${(item.perempuan || 0).toLocaleString('id-ID')}</td>`;
            tableHTML += `<td class="number">${total.toLocaleString('id-ID')}</td>`;
            tableHTML += `<td class="percentage ${percentLakiClass}">${percentLaki}%</td>`;
            tableHTML += `<td class="percentage ${percentPerempuanClass}">${percentPerempuan}%</td>`;
            tableHTML += '</tr>';
        });

        tableHTML += '</tbody></table>';

        // Add pagination controls if needed
        if (totalPages > 1) {
            tableHTML += '<div class="pagination-controls">';

            // Previous button
            if (this.currentPage > 1) {
                tableHTML += `<button class="pagination-btn" onclick="window.demografiDashboard.goToPage(${this.currentPage - 1})">
                    <i class="fas fa-chevron-left"></i> Sebelumnya
                </button>`;
            }

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
            const endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage + 1 < maxVisiblePages) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === this.currentPage ? 'active' : '';
                tableHTML += `<button class="pagination-btn page-number ${activeClass}" onclick="window.demografiDashboard.goToPage(${i})">${i}</button>`;
            }

            // Next button
            if (this.currentPage < totalPages) {
                tableHTML += `<button class="pagination-btn" onclick="window.demografiDashboard.goToPage(${this.currentPage + 1})">
                    Selanjutnya <i class="fas fa-chevron-right"></i>
                </button>`;
            }

            tableHTML += '</div>';
        }

        tableContainer.innerHTML = tableHTML;
    }

    // Add methods for pagination control
    changeItemsPerPage(newItemsPerPage) {
        this.itemsPerPage = parseInt(newItemsPerPage);
        this.currentPage = 1; // Reset to first page
        this.populateDataTable();
    }

    goToPage(pageNumber) {
        const totalPages = Math.ceil(this.filteredData.length / this.itemsPerPage);
        if (pageNumber >= 1 && pageNumber <= totalPages) {
            this.currentPage = pageNumber;
            this.populateDataTable();
        }
    }

    // Public method to refresh data
    refresh() {
        this.loadData();
    }

    // Public method to export data
    exportData() {
        if (window.Utils && window.Utils.exportToCSV) {
            // Use filtered data for export
            const dataToExport = this.filteredData.length > 0 ? this.filteredData : this.currentData;
            
            // Prepare data for export
            const exportData = dataToExport.map(item => ({
                'Kode': item.kode,
                'Wilayah': item.wilayah,
                'Laki-laki': item.laki_laki,
                'Perempuan': item.perempuan,
                'Total': item.total || (item.laki_laki + item.perempuan),
                'Persen_Laki_laki': item.total > 0 ? ((item.laki_laki / (item.total || (item.laki_laki + item.perempuan))) * 100).toFixed(1) + '%' : '0%',
                'Persen_Perempuan': item.total > 0 ? ((item.perempuan / (item.total || (item.laki_laki + item.perempuan))) * 100).toFixed(1) + '%' : '0%'
            }));
            
            const filename = this.searchTerm ? `data-demografi-${this.searchTerm}` : 'data-demografi';
            window.Utils.exportToCSV(exportData, filename);
            
            // Show notification
            if (window.mainApp && window.mainApp.showNotification) {
                window.mainApp.showNotification(
                    `Data berhasil diekspor: ${exportData.length} baris`, 
                    'success', 
                    3000
                );
            }
        } else {
            console.warn('Export utility not available');
            alert('Fitur export belum tersedia');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const initDemografi = () => {
        // Wait for dependencies
        if (window.DashboardCommon && window.API) {
            try {
                const dashboard = new DemografiDashboard();
                
                // Make instance available globally for debugging
                window.demografiDashboard = dashboard;
                
                console.log('Demografi dashboard initialized');
            } catch (error) {
                console.error('Error initializing demografi dashboard:', error);
            }
        } else {
            console.log('Waiting for dependencies...');
            setTimeout(initDemografi, 100);
        }
    };
    
    initDemografi();
});